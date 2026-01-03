<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: " . "../../index.php");
    exit(401);
} else {
    session_regenerate_id();
}

//verificação da permissão do usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 14);

require_once '../../dao/Conexao.php';
require_once '../../dao/PaArquivoDAO.php';

$idProcesso = (int)($_GET['id_processo'] ?? 0);
if ($idProcesso <= 0) {
    echo '<div class="alert alert-danger">Processo inválido.</div>';
    exit;
}

$pdo = Conexao::connect();
$dao = new PaArquivoDAO($pdo);
$arquivos = $dao->listarPorProcesso($idProcesso);

if (empty($arquivos)) {
    echo '<div class="alert alert-info">Nenhum arquivo anexado a este processo.</div>';
    exit;
}
?>

<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>Arquivo</th>
            <th>Data upload</th>
            <th>Ação</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($arquivos as $arq): ?>
        <tr>
            <td><?= htmlspecialchars($arq['arquivo_nome']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($arq['data_upload'])) ?></td>
            <td class="text-center">
                <a class="btn btn-xs btn-success"
                   href="download_arquivo_processo.php?id=<?= (int)$arq['id'] ?>"
                   title="Baixar arquivo">
                    <i class="fa fa-download" aria-hidden="true"></i>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
