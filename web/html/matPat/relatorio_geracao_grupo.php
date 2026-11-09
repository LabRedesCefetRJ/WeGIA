<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

Util::definirFusoHorario();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
session_regenerate_id();

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

permissao($_SESSION['id_pessoa'], 25, 5);

require_once ROOT . '/html/personalizacao_display.php';

if (!isset($_SESSION['relatorio_grupo'])) {
    header('Location: ' . WWW . 'html/matPat/relatorio.php');
    exit;
}

$dadosRelatorio = $_SESSION['relatorio_grupo'];

$produtosPorCategoria = $dadosRelatorio['produtos_por_categoria'] ?? [];
$entradasPorProduto = $dadosRelatorio['entradas_por_produto'] ?? [];
$saidasPorProduto = $dadosRelatorio['saidas_por_produto'] ?? [];
$nomeGrupo = $dadosRelatorio['nome_grupo'] ?? 'Não registrado';
$nomeAlmoxarifado = $dadosRelatorio['nome_almoxarifado'] ?? 'Não registrado';
$dataInicio = $dadosRelatorio['data_inicio'] ?? null;
$dataFim = $dadosRelatorio['data_fim'] ?? null;

require_once ROOT . '/html/relatorios/RelatorioMatPat.php';

?>
<!doctype html>

<html class="fixed">

<head>
    <meta charset="UTF-8">
    <title>Geração de Relatório</title>

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"
    />

    <!-- Web Fonts -->
    <link
        href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light"
        rel="stylesheet"
        type="text/css"
    >

    <!-- Vendor CSS -->
    <link
        rel="stylesheet"
        href="<?= WWW ?>assets/vendor/bootstrap/css/bootstrap.css"
    />

    <link
        rel="stylesheet"
        href="<?= WWW ?>assets/vendor/font-awesome/css/font-awesome.css"
    />

    <link
        rel="stylesheet"
        href="https://use.fontawesome.com/releases/v6.1.1/css/all.css"
    >

    <link
        rel="stylesheet"
        href="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.css"
    />

    <link
        rel="stylesheet"
        href="<?= WWW ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css"
    />

    <link
        rel="icon"
        href="<?php display_campo("Logo", 'file'); ?>"
        type="image/x-icon"
    >

    <!-- Theme CSS -->
    <link
        rel="stylesheet"
        href="<?= WWW ?>assets/stylesheets/theme.css"
    />

    <!-- Skin CSS -->
    <link
        rel="stylesheet"
        href="<?= WWW ?>assets/stylesheets/skins/default.css"
    />

    <!-- Theme Custom CSS -->
    <link
        rel="stylesheet"
        href="<?= WWW ?>assets/stylesheets/theme-custom.css"
    >

    <!-- Atualização CSS -->
    <link
        rel="stylesheet"
        href="<?= WWW ?>css/atualizacao.css"
    />

    <!-- Head Libs -->
    <script src="<?= WWW ?>assets/vendor/modernizr/modernizr.js"></script>

    <!-- Vendor -->
    <script src="<?= WWW ?>assets/vendor/jquery/jquery.min.js"></script>

    <script src="<?= WWW ?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>

    <script src="<?= WWW ?>assets/vendor/bootstrap/js/bootstrap.js"></script>

    <script src="<?= WWW ?>assets/vendor/nanoscroller/nanoscroller.js"></script>

    <script src="<?= WWW ?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>

    <script src="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.js"></script>

    <script src="<?= WWW ?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

    <!-- Theme -->
    <script src="<?= WWW ?>assets/javascripts/theme.js"></script>

    <script src="<?= WWW ?>assets/javascripts/theme.custom.js"></script>

    <script src="<?= WWW ?>assets/javascripts/theme.init.js"></script>


    <script type="text/javascript">
        $(function() {
            $("#header").load("<?= WWW ?>html/header.php");
            $(".menuu").load("<?= WWW ?>html/menu.php");
        });
    </script>

    <link rel="stylesheet" href="<?= WWW ?>css/relatorio-matpat.css">
<script src="<?= WWW ?>assets/javascripts/relatorio-matpat.js" defer></script>
</head>
<body class="relatorio-matpat">
    <section class="body">
        <div id="header" class="print-hide"></div>
        <div class="inner-wrapper">
            <aside id="sidebar-left" class="sidebar-left menuu print-hide"></aside>
            <section role="main" class="content-body">
                <header class="page-header print-hide">
                    <h2>Geração de Relatório</h2>
                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li id="home-icon"><a href="<?= WWW ?>html/home.php"><i class="fa fa-home"></i></a></li>
                            <li><span>Páginas</span></li>
                            <li><span>Geração de Relatório</span></li>
                        </ol>
                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>
                <div class="tab-content">

                    <?php
                    $resumo = [
                        'Grupo' => $nomeGrupo,
                        'Almoxarifado' => $nomeAlmoxarifado,
                        'Período' => RelatorioMatPat::periodo($dataInicio, $dataFim),
                    ];
                    ob_start();
                    ?>
                    <div class="panel-group">
                        <?php if (empty($produtosPorCategoria)) { ?>
                            <div class="alert alert-info">Nenhum produto encontrado para este grupo.</div>
                        <?php } ?>
                        <?php $indiceCategoria = 0; ?>
                        <?php foreach ($produtosPorCategoria as $categoria => $produtosCategoria) {
                            $idCategoria = 'categoria-' . $indiceCategoria;
                            $indiceCategoria++;
                        ?>
                        <section class="panel panel-default categoria-relatorio">
                            <header class="panel-heading">
                                <div class="row">
                                    <div class="col-md-10">
                                        <h4 class="panel-title"><?= htmlspecialchars($categoria) ?></h4>
                                        <small><?= count($produtosCategoria) ?> <?= count($produtosCategoria) === 1 ? 'produto' : 'produtos' ?></small>
                                    </div>
                                    <div class="col-md-2 text-right">
                                        <button type="button" class="btn btn-default btn-sm print-hide" data-toggle="collapse" data-target="#<?= $idCategoria ?>">Ver produtos <i class="fa fa-chevron-down"></i></button>
                                    </div>
                                </div>
                            </header>
                            <div id="<?= $idCategoria ?>" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <?php foreach ($produtosCategoria as $produto) {
                                        $idProduto = (int) $produto['id_produto'];
                                        $entradas = $entradasPorProduto[$idProduto] ?? [];
                                        $saidas = $saidasPorProduto[$idProduto] ?? [];
                                        $idCollapse = 'produto-' . $idProduto;
                                    ?>
                                        <div class="produto-relatorio">
                                            <div class="produto-resumo">
                                                <div class="row">
                                                    <div class="col-md-9">
                                                        <strong style="font-size: 16px;"><?= htmlspecialchars($produto['produto']) ?></strong><br>
                                                        <small>
                                                            <strong>Unidade:</strong> <?= !empty($produto['unidade']) ? htmlspecialchars($produto['unidade']) : 'Não registrado' ?> |
                                                            <strong>Estoque:</strong> <?= htmlspecialchars($produto['estoque_atual']) ?> |
                                                            <strong>Entradas:</strong> <?= htmlspecialchars($produto['total_entradas']) ?> |
                                                            <strong>Saídas:</strong> <?= htmlspecialchars($produto['total_saidas']) ?>
                                                        </small>
                                                    </div>
                                                    <div class="col-md-3 text-right">
                                                        <button type="button" class="btn btn-default btn-sm btn-detalhes print-hide" data-toggle="collapse" data-target="#<?= $idCollapse ?>" aria-expanded="false" aria-controls="<?= $idCollapse ?>">Ver detalhes <i class="fa fa-chevron-down"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="<?= $idCollapse ?>" class="collapse detalhes-produto">

                                                <table class="table table-striped">
                                                    <thead class="thead-dark">
                                                        <tr><th colspan="3" style="font-size: large;">ENTRADAS</th></tr>
                                                        <tr><th style="width: 45%;">DATA</th><th style="width: 45%;">TIPO</th><th>QTD</th></tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (empty($entradas)) { ?>
                                                            <tr><td colspan="3">Nenhuma entrada registrada no período.</td></tr>
                                                        <?php } ?>
                                                        <?php foreach ($entradas as $entrada) {
                                                            if (!empty($entrada['data_entrada']) && !empty($entrada['hora_entrada'])) {
                                                                $dataEntrada = date('d/m/Y H:i', strtotime($entrada['data_entrada'] . ' ' . $entrada['hora_entrada']));
                                                            } elseif (!empty($entrada['data_entrada'])) {
                                                                $dataEntrada = date('d/m/Y', strtotime($entrada['data_entrada']));
                                                            } else {
                                                                $dataEntrada = 'Não registrado';
                                                            }
                                                            $tipoEntrada = !empty($entrada['descricao_tipo_entrada']) ? trim($entrada['descricao_tipo_entrada']) : 'Não registrado';
                                                            $classeEntrada = Util::getClassePorTipo($tipoEntrada);
                                                        ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($dataEntrada) ?></td>
                                                                <td><span class="badge <?= htmlspecialchars($classeEntrada) ?>"><?= htmlspecialchars($tipoEntrada) ?></span></td>
                                                                <td><?= htmlspecialchars($entrada['quantidade_entrada']) ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>

                                                <table class="table table-striped">
                                                    <thead class="thead-dark">
                                                        <tr><th colspan="3" style="font-size: large;">SAÍDAS</th></tr>
                                                        <tr><th style="width: 45%;">DATA</th><th style="width: 45%;">TIPO</th><th>QTD</th></tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (empty($saidas)) { ?>
                                                            <tr><td colspan="3">Nenhuma saída registrada no período.</td></tr>
                                                        <?php } ?>
                                                        <?php foreach ($saidas as $saida) {
                                                            if (!empty($saida['data_saida']) && !empty($saida['hora_saida'])) {
                                                                $dataSaida = date('d/m/Y H:i', strtotime($saida['data_saida'] . ' ' . $saida['hora_saida']));
                                                            } elseif (!empty($saida['data_saida'])) {
                                                                $dataSaida = date('d/m/Y', strtotime($saida['data_saida']));
                                                            } else {
                                                                $dataSaida = 'Não registrado';
                                                            }
                                                            $tipoSaida = !empty($saida['descricao_tipo_saida']) ? trim($saida['descricao_tipo_saida']) : 'Não registrado';
                                                            $classeSaida = Util::getClassePorTipo($tipoSaida);
                                                        ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($dataSaida) ?></td>
                                                                <td><span class="badge <?= htmlspecialchars($classeSaida) ?>"><?= htmlspecialchars($tipoSaida) ?></span></td>
                                                                <td><?= htmlspecialchars($saida['quantidade_saida']) ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </section>
                        <?php } ?>
                    </div>
                    <?php
                    $relatorio = new RelatorioMatPat($resumo, ob_get_clean(), 'Relatório de Grupo', 'grupo');
                    echo $relatorio->getHtml();
                    ?>
                </div>
                </section>
            </div>
        </section>
    <div align="right" class="print-hide">
        <iframe src="https://www.wegia.org/software/footer/matPat.html" width="200" height="60" style="border:none;"></iframe>
    </div>
</body>

</html>
