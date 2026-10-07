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

$modeloBrasileiro = 'd/m/Y';
$dataInicioFormatada = !empty($dataInicio) ? date_format(date_create($dataInicio), $modeloBrasileiro) : null;
$dataFimFormatada = !empty($dataFim) ? date_format(date_create($dataFim), $modeloBrasileiro) : null;
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


    <script>
        var homeIcon;

        window.onbeforeprint = function() {

            homeIcon = $('#home-icon').children();

            $('#home-icon').empty();

            $('#home-icon').append(
                $('<span />').text(
                    "<?php display_campo("Titulo", "str"); ?>"
                )
            );
        }

        window.onafterprint = function() {

            $('#home-icon').empty();

            $('#home-icon').append(homeIcon);
        };
    </script>

    <style>
    .produto-relatorio {
        margin-bottom: 8px;
    }

    .produto-resumo {
        padding: 10px 8px;
        border-bottom: 1px solid #ddd;
    }

    @media print {
        .print-hide,
        .print-button {
            display: none !important;
        }      

        .categoria-relatorio {
            display: block !important;
        }

        .categoria-relatorio .panel-heading {
            display: block !important;
        }

        .categoria-relatorio .panel-collapse {
            display: block !important;
            height: auto !important;
            visibility: visible !important;
        }

        .produto-relatorio {
            display: block !important;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .produto-relatorio .detalhes-produto {
            display: block !important;
            height: auto !important;
            visibility: visible !important;
        }

        .produto-relatorio table {
            page-break-inside: auto;
        }

        .produto-relatorio tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .produto-relatorio thead {
            display: table-header-group;
        }
    }
</style>
</head>
<body>
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
                    <div class="descricao">
                        <h2>Geração de Grupo</h2>
                        <button style="float: right;" class="mb-xs mt-xs mr-xs btn btn-default print-button" onclick="window.print();">Imprimir</button>
                    </div>
                    <table class="table table-striped">
                        <thead class="thead-dark"><tr><th scope="col" colspan="7" style="font-size: large;">INFORMAÇÕES DO GRUPO</th></tr></thead>
                        <tbody>
                            <tr><td>GRUPO: <?= htmlspecialchars($nomeGrupo) ?></td></tr>
                            <tr><td>ALMOXARIFADO: <?= htmlspecialchars($nomeAlmoxarifado) ?></td></tr>
                            <tr><td>
                                PERÍODO DO RELATÓRIO:
                                <?php
                                if (empty($dataInicio) && empty($dataFim)) {
                                    echo 'TODAS AS DATAS';
                                } elseif (!empty($dataInicio) && !empty($dataFim)) {
                                    echo '<br>A partir do dia: ' . htmlspecialchars($dataInicioFormatada) . '<br>Até: ' . htmlspecialchars($dataFimFormatada);
                                } elseif (!empty($dataInicio)) {
                                    echo 'A partir do dia: ' . htmlspecialchars($dataInicioFormatada);
                                } elseif (!empty($dataFim)) {
                                    echo 'Até: ' . htmlspecialchars($dataFimFormatada);
                                }
                                ?>
                            </td></tr>
                        </tbody>
                    </table>
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
                                        <button type="button" class="btn btn-default btn-sm" data-toggle="collapse" data-target="#<?= $idCategoria ?>">Ver produtos <i class="fa fa-chevron-down"></i></button>
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
                                    <?php } ?>
                                </div>
                            </div>
                        </section>
                        <?php } ?>
                    </div>
                </section>
            </div>
        </section>
    <div align="right">
        <iframe src="https://www.wegia.org/software/footer/matPat.html" width="200" height="60" style="border:none;"></iframe>
    </div>
</body>

</html>
