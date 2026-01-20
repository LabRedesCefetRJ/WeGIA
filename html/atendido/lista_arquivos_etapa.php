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

<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th>Arquivo</th>
            <th>Data upload</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($arquivos as $arq): 
        $ext = strtolower($arq['arquivo_extensao'] ?? pathinfo($arq['arquivo_nome'], PATHINFO_EXTENSION));
        $icone = match($ext) {
            'pdf' => 'file-pdf-o',
            'doc', 'docx' => 'file-word-o',
            'jpg', 'jpeg', 'png' => 'file-image-o',
            default => 'file-o'
        };
    ?>
        <tr>
            <td>
                <i class="fa fa-<?= $icone ?> mr-2 text-muted"></i>
                <?= htmlspecialchars($arq['arquivo_nome']) ?>
            </td>
            <td><?= date('d/m/Y H:i', strtotime($arq['data_upload'])) ?></td>
            <td class="text-center">
                <a class="btn btn-xs btn-success"
                   href="download_arquivo_etapa.php?id=<?= (int)$arq['id'] ?>"
                   title="Baixar arquivo">
                    <i class="fa fa-download" aria-hidden="true"></i>
                </a>
                &nbsp;
                <form method="post" action="../../controle/control.php" style="display:inline;" 
                      onsubmit="return confirm('Remover <?= htmlspecialchars($arq['arquivo_nome']) ?>?\nEsta ação não pode ser desfeita.');">
                    <input type="hidden" name="nomeClasse" value="ArquivoEtapaControle">
                    <input type="hidden" name="metodo" value="excluir">
                    <input type="hidden" name="id_arquivo" value="<?= (int)$arq['id'] ?>">
                    <input type="hidden" name="id_etapa" value="<?= $idEtapa ?>">
                    <input type="hidden" name="id_processo" value="<?= $_GET['id_processo'] ?? 0 ?>">
                    <button type="submit" class="btn btn-xs btn-danger" title="Excluir arquivo">
                        <i class="fa fa-trash" aria-hidden="true"></i>
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
