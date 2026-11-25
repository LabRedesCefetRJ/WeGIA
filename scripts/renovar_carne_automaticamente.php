<?php
/** Daily CLI job: php scripts/renovar_carne_automaticamente.php */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$repositoryDirectory = dirname(__DIR__);
$webDirectory = $repositoryDirectory . '/web';
$contributionDirectory = $webDirectory . '/html/contribuicao';
chdir($webDirectory);

require_once $webDirectory . '/config.php';
require_once $contributionDirectory . '/dao/ConexaoDAO.php';
require_once $contributionDirectory . '/dao/ContribuicaoLogDAO.php';
require_once $contributionDirectory . '/dao/SocioDAO.php';
require_once $contributionDirectory . '/dao/GatewayPagamentoDAO.php';
require_once $contributionDirectory . '/dao/MeioPagamentoDAO.php';
require_once $contributionDirectory . '/model/ContribuicaoLogCollection.php';
require_once $contributionDirectory . '/service/PagarMeCarneService.php';
require_once $webDirectory . '/classes/Util.php';
require_once $webDirectory . '/controle/EmailControle.php';

$pdo = ConexaoDAO::conectar();
$log = static function (string $message): void {
    error_log('[renovar_carne] ' . $message);
};

// Locate the configured boleto book medium instead of assuming its database ID.
$medium = $pdo->query("SELECT id FROM contribuicao_meioPagamento WHERE LOWER(meio) LIKE 'carn%' AND status = 1 ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$medium) {
    $log('Nenhum meio de pagamento de carnê ativo encontrado.');
    exit(1);
}
$mediumId = (int) $medium['id'];

$stmtMembers = $pdo->prepare("SELECT s.id_socio, p.email, st.tipo AS socio_tipo, c.final_due,
        (SELECT c2.valor FROM contribuicao_log c2 WHERE c2.id_socio=s.id_socio AND c2.id_meio_pagamento=:medium AND c2.data_vencimento <= c.final_due ORDER BY c2.data_vencimento DESC, c2.id DESC LIMIT 1) AS amount,
        (SELECT c3.id_gateway FROM contribuicao_log c3 WHERE c3.id_socio=s.id_socio AND c3.id_meio_pagamento=:medium2 AND c3.data_vencimento <= c.final_due ORDER BY c3.data_vencimento DESC, c3.id DESC LIMIT 1) AS gateway_id
    FROM socio s JOIN pessoa p ON p.id_pessoa=s.id_pessoa
    JOIN socio_tipo st ON st.id_sociotipo=s.id_sociotipo
    JOIN (SELECT id_socio, MAX(data_vencimento) AS final_due FROM contribuicao_log WHERE id_meio_pagamento=:medium3 GROUP BY id_socio) c ON c.id_socio=s.id_socio
    JOIN socio_status ss ON ss.id_sociostatus=s.id_sociostatus AND LOWER(TRIM(ss.status))='ativo'
    WHERE s.deletado=0 AND s.auto_renovation=1
      AND c.final_due < CURDATE() AND p.email IS NOT NULL AND p.email <> ''");
$stmtMembers->execute([':medium' => $mediumId, ':medium2' => $mediumId, ':medium3' => $mediumId]);
$members = $stmtMembers->fetchAll(PDO::FETCH_ASSOC);

foreach ($members as $member) {
    $socioId = (int) $member['id_socio'];
    $endedDate = $member['final_due'];
    $socioType = mb_strtolower((string) $member['socio_tipo'], 'UTF-8');
    if (str_contains($socioType, 'casual')) {
        continue;
    }
    $intervalMonths = match (true) {
        str_contains($socioType, 'mensal') => 1,
        str_contains($socioType, 'bimestral') => 2,
        str_contains($socioType, 'trimestral') => 3,
        str_contains($socioType, 'semestral') => 6,
        default => 0,
    };
    if ($intervalMonths === 0) {
        $log("Periodicidade não reconhecida para socio {$socioId}: {$member['socio_tipo']}");
        continue;
    }
    try {
        // Unique key makes concurrent or repeated daily runs safe per ended carnet.
        $claim = $pdo->prepare("INSERT IGNORE INTO socio_carnet_renewal (id_socio, carnet_final_due_date, status) VALUES (?, ?, 'processing')");
        $claim->execute([$socioId, $endedDate]);
        if ($claim->rowCount() === 0) {
            continue;
        }

        $socio = (new SocioDAO($pdo))->buscarPorId($socioId);
        $gatewayId = (int) ($member['gateway_id'] ?? 0);
        if ($gatewayId < 1) {
            $gatewayFallback = $pdo->query("SELECT id FROM contribuicao_gatewayPagamento WHERE LOWER(TRIM(plataforma))='pagarme' AND status=1 ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            $gatewayId = (int) ($gatewayFallback['id'] ?? 0);
        }
        $gatewayRow = $pdo->prepare('SELECT id, plataforma, endPoint, private_token, public_token, status FROM contribuicao_gatewayPagamento WHERE id=?');
        $gatewayRow->execute([$gatewayId]);
        $gatewayData = $gatewayRow->fetch(PDO::FETCH_ASSOC);
        $mediumObject = (new MeioPagamentoDAO())->buscarPorId($mediumId);
        if (!$socio || !$gatewayData || !$mediumObject || !filter_var($member['email'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Dados do sócio, e-mail ou gateway inválidos.');
        }
        $socio->setEmail($member['email']);
        $pdo->beginTransaction();
        $gateway = new GatewayPagamento($gatewayData['plataforma'], $gatewayData['endPoint'], $gatewayData['private_token'], $gatewayData['public_token'], $gatewayData['status'], $gatewayId);
        $logs = new ContribuicaoLogCollection();
        $firstDue = new DateTimeImmutable($endedDate);
        $firstDue = $firstDue->modify('first day of next month');
        $day = (int) (new DateTimeImmutable($endedDate))->format('d');
        $generationDate = (new DateTimeImmutable('today'))->format('Y-m-d');
        $dao = new ContribuicaoLogDAO($pdo);
        for ($i = 0; $i < 12; $i += $intervalMonths) {
            $date = $firstDue->modify('+' . $i . ' months');
            $date = $date->setDate((int)$date->format('Y'), (int)$date->format('m'), min($day, (int)$date->format('t')));
            $contribution = (new ContribuicaoLog())
                ->setValor((float)$member['amount'])
                ->setCodigo(bin2hex(random_bytes(8)))
                ->setDataGeracao($generationDate)
                ->setDataVencimento($date->format('Y-m-d'))
                ->setSocio($socio)
                ->setGatewayPagamento($gateway)
                ->setMeioPagamento($mediumObject)
                ->setAgradecimento('Agradecemos por continuar contribuindo com nossa instituição.');
            $dao->criar($contribution);
            $logs->add($contribution);
        }

        // This application currently implements carnet generation for Pagar.me.
        if (strcasecmp((string) $gatewayData['plataforma'], 'PagarMe') !== 0) {
            throw new RuntimeException('Serviço de carnê não encontrado para o gateway.');
        }
        $serviceFile = $contributionDirectory . '/service/PagarMeCarneService.php';
        require_once $serviceFile;
        $serviceClass = 'PagarMeCarneService';
        $result = (new $serviceClass($pdo))->gerarCarne($logs);
        if (!$result || empty($result['contribuicoes'])) {
            throw new RuntimeException('O serviço não retornou o carnê.');
        }
        foreach ($result['contribuicoes'] as $contribution) {
            $dao->alterarCodigoPorId($contribution->getCodigo(), $contribution->getId());
        }
        $pdo->commit();
        $first = $result['contribuicoes']->getIterator()->current();
        $link = WWW . 'html/contribuicao/controller/control.php?nomeClasse=ContribuicaoLogController&metodo=downloadPdfPorId&id=' . rawurlencode($first->getUuid());
        $email = new EmailControle($pdo);
        $name = htmlspecialchars($socio->getNome(), ENT_QUOTES, 'UTF-8');
        $safeLink = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
        $installments = (int) ceil(12 / $intervalMonths);
        $body = "<p>Olá, {$name}.</p><p>Agradecemos por sua contribuição e por apoiar nossa instituição. Seu novo carnê de {$installments} parcelas está disponível no link abaixo.</p><p><a href=\"{$safeLink}\">Acessar meu carnê</a></p><p>Esperamos contar com você nesta nova etapa. Muito obrigado!</p>";
        $sent = $email->enviarEmail($member['email'], 'Seu novo carnê de contribuições', $body, $socio->getNome());
        $status = !empty($sent['success']) ? 'sent' : 'generated';
        $pdo->prepare('UPDATE socio_carnet_renewal SET status=? WHERE id_socio=? AND carnet_final_due_date=?')->execute([$status, $socioId, $endedDate]);
        if ($status !== 'sent') {
            $log("Falha no envio para socio {$socioId}: " . ($sent['message'] ?? 'erro desconhecido'));
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $pdo->prepare("UPDATE socio_carnet_renewal SET status='failed' WHERE id_socio=? AND carnet_final_due_date=? AND status='processing'")->execute([$socioId, $endedDate]);
        $log("Falha no processamento de socio {$socioId}: " . $e->getMessage());
    }
}
