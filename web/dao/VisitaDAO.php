<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once ROOT . "/dao/Conexao.php";
require_once ROOT . "/classes/Visita.php";
require_once ROOT . "/classes/Visitado.php";
require_once ROOT . "/dao/SelecaoParagrafoDAO.php";

class VisitaDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        is_null($pdo) ? $this->pdo = Conexao::connect() : $this->pdo = $pdo;
    }

    public function incluir($visita)
    {
        $ids = $this->incluirMultiplos(
            $visita->getId_Visitante(),
            [$visita->getId_Visitado()],
            $visita->getDescricao() ?? ''
        );

        return $ids[0] ?? null;
    }

    public function incluirMultiplos($idsVisitante, array $idsVisitado, string $descricao = '')
    {
        $idsVisitante = array_values(array_unique(array_map('intval', (array) $idsVisitante)));
        $idsVisitado = array_values(array_unique(array_map('intval', $idsVisitado)));

        if (empty($idsVisitante) || empty($idsVisitado)) {
            return [];
        }

        $this->validarVisitantesNaoVisitados($idsVisitante, $idsVisitado);

        $this->pdo->beginTransaction();

        try {
            $horarioEntrada = date('Y-m-d H:i:s');

            $sqlVisita = "INSERT INTO visita (id_visitante, id_visitado, horario_entrada, status, descricao) VALUES (:id_visitante, :id_visitado, :horario_entrada, 'ativo', :descricao)";

            $stmtVisita = $this->pdo->prepare($sqlVisita);

            $idsVisitaInseridas = [];

            foreach ($idsVisitante as $idVisitante) {
                foreach ($idsVisitado as $idVisitado) {
                    $stmtVisita->bindValue(':id_visitante', $idVisitante, PDO::PARAM_INT);
                    $stmtVisita->bindValue(':id_visitado', $idVisitado, PDO::PARAM_INT);
                    $stmtVisita->bindValue(':horario_entrada', $horarioEntrada);

                    if ($descricao === '') {
                        $stmtVisita->bindValue(':descricao', null, PDO::PARAM_NULL);
                    } else {
                        $stmtVisita->bindValue(':descricao', $descricao, PDO::PARAM_STR);
                    }

                    $stmtVisita->execute();

                    $idsVisitaInseridas[] = $this->pdo->lastInsertId();
                }
            }

            $this->pdo->commit();
            return $idsVisitaInseridas;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function validarVisitantesNaoVisitados(array $idsVisitante, array $idsVisitado): void
    {
        $placeholdersVisitante = implode(',', array_fill(0, count($idsVisitante), '?'));
        $placeholdersVisitado = implode(',', array_fill(0, count($idsVisitado), '?'));

        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM visitante vt
             INNER JOIN visitado vd ON vd.id_pessoa = vt.id_pessoa
             WHERE vt.id_visitante IN ($placeholdersVisitante)
               AND vd.id_visitado IN ($placeholdersVisitado)"
        );

        $indice = 1;
        foreach ($idsVisitante as $idVisitante) {
            $stmt->bindValue($indice++, $idVisitante, PDO::PARAM_INT);
        }
        foreach ($idsVisitado as $idVisitado) {
            $stmt->bindValue($indice++, $idVisitado, PDO::PARAM_INT);
        }

        $stmt->execute();

        if ((int) $stmt->fetchColumn() > 0) {
            throw new InvalidArgumentException('Uma pessoa não pode ser visitante e visitado na mesma visita.', 412);
        }
    }

    public function encerrar($idVisita)
    {
        return $this->encerrarMultiplos([$idVisita]);
    }

    public function encerrarMultiplos(array $idsVisita)
    {
        $idsVisita = array_values(array_unique(array_map('intval', $idsVisita)));

        if (empty($idsVisita)) {
            return 0;
        }

        $this->pdo->beginTransaction();

        try {
            $horarioSaida = date('Y-m-d H:i:s');

            $placeholders = implode(',', array_fill(0, count($idsVisita), '?'));

            $sqlVisita = "UPDATE visita SET horario_saida = ?, status = 'inativo' WHERE status = 'ativo' AND id_visita IN ($placeholders)";

            $stmtVisita = $this->pdo->prepare($sqlVisita);

            $stmtVisita->bindValue(1, $horarioSaida);
            $indice = 2;
            foreach ($idsVisita as $idVisita) {
                $stmtVisita->bindValue($indice++, $idVisita, PDO::PARAM_INT);
            }

            $stmtVisita->execute();

            $linhasAfetadas = $stmtVisita->rowCount();

            $this->pdo->commit();
            return $linhasAfetadas;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function listarTodos()
    {
        $visitas = array();

        $consulta = $this->pdo->prepare("
            SELECT
                visita.id_visita,
                visita.horario_entrada,
                visita.descricao,
                pessoa_visitante.nome AS visitante_nome,
                pessoa_visitante.sobrenome AS visitante_sobrenome,
                pessoa_visitante.cpf AS visitante_cpf,
                pessoa_visitante.imagem AS visitante_imagem,
                COALESCE(pessoa_visitado.nome, pet.nome, setor.descricao) AS visitado_nome,
                pessoa_visitado.sobrenome AS visitado_sobrenome,
                pessoa_visitado.cpf AS visitado_cpf,
                pessoa_visitado.imagem AS visitado_imagem

            FROM visita

            INNER JOIN visitante
                ON visita.id_visitante = visitante.id_visitante

            INNER JOIN pessoa AS pessoa_visitante
                ON visitante.id_pessoa = pessoa_visitante.id_pessoa

            INNER JOIN visitado
                ON visita.id_visitado = visitado.id_visitado

            LEFT JOIN pessoa AS pessoa_visitado
                ON pessoa_visitado.id_pessoa = visitado.id_pessoa

            LEFT JOIN pet
                ON pet.id_pet = visitado.id_pet

            LEFT JOIN setor
                ON setor.id_setor = visitado.id_setor

            WHERE visita.status = 'ativo'
            ORDER BY visita.horario_entrada DESC
        "); 

        $consulta->execute();

        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $visitas[] = array(
                'id_visita' => htmlspecialchars($linha['id_visita'] ?? '', ENT_QUOTES, 'UTF-8'),
                'horario_entrada' => htmlspecialchars($linha['horario_entrada'] ?? '', ENT_QUOTES, 'UTF-8'),
                'descricao' => htmlspecialchars($linha['descricao'] ?? '', ENT_QUOTES, 'UTF-8'),
                'visitante_nome' => htmlspecialchars($linha['visitante_nome'] ?? '', ENT_QUOTES, 'UTF-8'),
                'visitante_sobrenome' => htmlspecialchars($linha['visitante_sobrenome'] ?? '', ENT_QUOTES, 'UTF-8'),
                'visitante_cpf' => htmlspecialchars($linha['visitante_cpf'] ?? '', ENT_QUOTES, 'UTF-8'),
                'visitante_imagem' => $linha['visitante_imagem'] ?? '',
                'visitado_nome' => htmlspecialchars($linha['visitado_nome'] ?? 'Instituição', ENT_QUOTES, 'UTF-8'),
                'visitado_sobrenome' => htmlspecialchars($linha['visitado_sobrenome'] ?? '', ENT_QUOTES, 'UTF-8'),
                'visitado_cpf' => htmlspecialchars($linha['visitado_cpf'] ?? '', ENT_QUOTES, 'UTF-8'),
                'visitado_imagem' => $linha['visitado_imagem'] ?? ''
            );
        }

        return $visitas;
    }

    public function listarTodosAgrupados()
    {
        $consulta = $this->pdo->prepare("
            SELECT
                visita.id_visita,
                visita.id_visitante,
                visita.id_visitado,
                visita.horario_entrada,
                visita.descricao,
                pessoa_visitante.nome AS visitante_nome,
                pessoa_visitante.sobrenome AS visitante_sobrenome,
                pessoa_visitante.cpf AS visitante_cpf,
                COALESCE(pessoa_visitado.nome, pet.nome, setor.descricao) AS visitado_nome,
                pessoa_visitado.sobrenome AS visitado_sobrenome,
                pessoa_visitado.cpf AS visitado_cpf,
                pet.sexo AS pet_sexo,
                pet_especie.descricao AS pet_especie,
                pet_raca.descricao AS pet_raca,
                pet_cor.descricao AS pet_cor,
                CASE
                    WHEN visitado.id_pessoa IS NOT NULL THEN 'pessoa'
                    WHEN visitado.id_pet IS NOT NULL THEN 'pet'
                    WHEN visitado.id_setor IS NOT NULL THEN 'setor'
                    ELSE 'instituicao'
                END AS visitado_tipo

            FROM visita

            INNER JOIN visitante
                ON visita.id_visitante = visitante.id_visitante

            INNER JOIN pessoa AS pessoa_visitante
                ON visitante.id_pessoa = pessoa_visitante.id_pessoa

            INNER JOIN visitado
                ON visita.id_visitado = visitado.id_visitado

            LEFT JOIN pessoa AS pessoa_visitado
                ON pessoa_visitado.id_pessoa = visitado.id_pessoa

            LEFT JOIN pet
                ON pet.id_pet = visitado.id_pet

            LEFT JOIN pet_especie
                ON pet_especie.id_pet_especie = pet.id_pet_especie

            LEFT JOIN pet_raca
                ON pet_raca.id_pet_raca = pet.id_pet_raca

            LEFT JOIN pet_cor
                ON pet_cor.id_pet_cor = pet.id_pet_cor

            LEFT JOIN setor
                ON setor.id_setor = visitado.id_setor

            WHERE visita.status = 'ativo'
            ORDER BY visita.horario_entrada DESC, visita.id_visita ASC
        ");

        $consulta->execute();

        $grupos = [];
        $nomeInstituicao = null;

        foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $chave = $linha['horario_entrada'] . '|' . ($linha['descricao'] ?? '');

            if (!isset($grupos[$chave])) {
                $grupos[$chave] = [
                    'ids_visita' => [],
                    'horario_entrada' => $linha['horario_entrada'],
                    'descricao' => $linha['descricao'] ?? '',
                    'visitantes' => [],
                    'visitados' => [],
                ];
            }

            $grupos[$chave]['ids_visita'][] = (int) $linha['id_visita'];

            $grupos[$chave]['visitantes'][(int) $linha['id_visitante']] = [
                'nome' => $linha['visitante_nome'],
                'sobrenome' => $linha['visitante_sobrenome'],
                'tipo' => 'pessoa',
                'identificador' => Visitado::identificadorPessoa($linha['visitante_cpf'] ?? null),
            ];

            switch ($linha['visitado_tipo']) {
                case 'pessoa':
                    $identificadorVisitado = Visitado::identificadorPessoa($linha['visitado_cpf'] ?? null);
                    break;
                case 'pet':
                    $identificadorVisitado = Visitado::identificadorPet($linha['pet_especie'], $linha['pet_raca'], $linha['pet_cor'], $linha['pet_sexo']);
                    break;
                default:
                    $identificadorVisitado = Visitado::IDENTIFICADOR_VAZIO;
            }

            $nomeVisitado = $linha['visitado_nome'];

            if ($linha['visitado_tipo'] === 'instituicao') {
                $nomeInstituicao ??= trim((string) SelecaoParagrafoDAO::getSelecao(SelecaoParagrafo::Titulo));
                $nomeVisitado = $nomeInstituicao !== '' ? $nomeInstituicao : 'Instituição';
            }

            $grupos[$chave]['visitados'][(int) $linha['id_visitado']] = [
                'nome' => $nomeVisitado ?? 'Instituição',
                'sobrenome' => $linha['visitado_sobrenome'],
                'tipo' => $linha['visitado_tipo'],
                'identificador' => $identificadorVisitado,
            ];
        }

        $escapar = function ($item) {
            return [
                'nome' => htmlspecialchars($item['nome'] ?? '', ENT_QUOTES, 'UTF-8'),
                'sobrenome' => htmlspecialchars($item['sobrenome'] ?? '', ENT_QUOTES, 'UTF-8'),
                'tipo' => htmlspecialchars($item['tipo'] ?? '', ENT_QUOTES, 'UTF-8'),
                'identificador' => htmlspecialchars($item['identificador'] ?? '', ENT_QUOTES, 'UTF-8'),
            ];
        };

        $resultado = [];
        foreach ($grupos as $grupo) {
            $resultado[] = [
                'ids_visita' => $grupo['ids_visita'],
                'horario_entrada' => htmlspecialchars($grupo['horario_entrada'] ?? '', ENT_QUOTES, 'UTF-8'),
                'descricao' => htmlspecialchars($grupo['descricao'] ?? '', ENT_QUOTES, 'UTF-8'),
                'visitantes' => array_map($escapar, array_values($grupo['visitantes'])),
                'visitados' => array_map($escapar, array_values($grupo['visitados'])),
            ];
        }

        return $resultado;
    }
}