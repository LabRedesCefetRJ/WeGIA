<?php
require_once '../../dao/Conexao.php';
require_once '../../dao/EtapaArquivoDAO.php';

$idEtapa = (int)($_GET['id_etapa'] ?? 0);
if ($idEtapa <= 0) {
    echo '<div class="alert alert-danger">Etapa inválida.</div>';
    exit;
}

$pdo = Conexao::connect();
$dao = new EtapaArquivoDAO($pdo);
$arquivos = $dao->listarPorEtapa($idEtapa);

if (empty($arquivos)) {
    echo '<div class="alert alert-info">Nenhum arquivo anexado a esta etapa.</div>';
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
                   href="download_arquivo_etapa.php?id=<?= (int)$arq['id'] ?>"
                   title="Baixar arquivo">
                    <i class="fa fa-download" aria-hidden="true"></i>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
