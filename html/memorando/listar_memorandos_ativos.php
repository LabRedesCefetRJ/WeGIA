<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit;
}

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($_SESSION['id_pessoa'], 3, 1);

require_once ROOT . "/controle/memorando/MemorandoControle.php";

$memorando = new MemorandoControle;
$memorando->listarTodos();

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";
?>

<!DOCTYPE html>

<html class="fixed">

<head>
    <!-- Basic -->
    <meta charset="UTF-8">

    <title>Caixa de entrada</title>

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

    <!-- Specific Page Vendor CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/select2/select2.css" />
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/theme.css" />

    <!-- Skin CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>/assets/stylesheets/skins/default.css" />

    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/theme-custom.css">

    <!-- Head Libs -->
    <script src="<?php echo WWW; ?>assets/vendor/modernizr/modernizr.js"></script>

    <!-- Vendor -->
    <script src="<?php echo WWW; ?>assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

    <!-- Specific Page Vendor -->
    <script src="<?php echo WWW; ?>assets/vendor/jquery-autosize/jquery.autosize.js"></script>

    <!-- Theme Base, Components and Settings -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.js"></script>

    <!-- Theme Custom -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.custom.js"></script>

    <!-- Theme Initialization Files -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.init.js"></script>


    <!-- javascript functions -->
    <script src="<?php echo WWW; ?>Functions/onlyNumbers.js"></script>
    <script src="<?php echo WWW; ?>Functions/onlyChars.js"></script>
    <script src="<?php echo WWW; ?>Functions/mascara.js"></script>

    <!-- printThis -->
    <script src="<?php echo WWW; ?>assets/vendor/jasonday-printThis-f73ca19/printThis.js"></script>


    <!-- jquery functions -->

    <script>
        $(function() {
            var memorando = <?php echo $_SESSION['memorando'] ?>;
            $.each(memorando, function(i, item) {
                $("#tabela")
                    .append($("<tr id=" + item.id_memorando + ">")
                        .append($("<td>")
                            .text(item.id_memorando))
                        .append($("<td>")
                            .html("<a href=<?php echo WWW; ?>html/memorando/listar_despachos.php?id_memorando=" + item.id_memorando + " id=memorando>" + item.titulo + "</a>"))
                        .append($("<td>")
                            .text(item.data.substr(8, 2) + "/" + item.data.substr(5, 2) + "/" + item.data.substr(0, 4) + " " + item.data.substr(10)))
                        .append($("<td id=opcoes_" + item.id_memorando + ">")
                            .html("<a href=<?php echo WWW; ?>controle/control.php?nomeClasse=MemorandoControle&metodo=alterarIdStatusMemorando&id_memorando=" + item.id_memorando + "&id_status_memorando=3&modulo=memorando id=naolido" + item.id_memorando + "><img src=<?php echo WWW; ?>img/nao-lido.png width=25px height=25px title='Não Lido'></a> <a href=<?php echo WWW; ?>controle/control.php?nomeClasse=MemorandoControle&metodo=alterarIdStatusMemorando&id_memorando=" + item.id_memorando + "&id_status_memorando=4&modulo=memorando id=importante" + item.id_memorando + "><img src=<?php echo WWW; ?>img/importante.png width=25px height=25px title='Importante'></a> <a href=<?php echo WWW; ?>controle/control.php?nomeClasse=MemorandoControle&metodo=alterarIdStatusMemorando&id_memorando=" + item.id_memorando + "&id_status_memorando=5&modulo=memorando id=pendente" + item.id_memorando + "><img src=<?php echo WWW; ?>img/pendente.png width=25px height=25px title='Pendente'></a> <a href=<?php echo WWW; ?>html/memorando/listar_despachos.php?id_memorando=" + item.id_memorando + "&id_status_memorando=5&modulo=memorando id=impressora" + item.id_memorando + "><img src=<?php echo WWW; ?>img/imp.png width=24px height=24px title='Imprimir'></a>")));

                if (item.id_status_memorando == 4) {
                    document.getElementById(item.id_memorando).style.backgroundColor = '#ffa0a0d4';
                    $("#importante" + item.id_memorando).html("<a href=<?php echo WWW; ?>controle/control.php?nomeClasse=MemorandoControle&metodo=alterarIdStatusMemorando&id_memorando=" + item.id_memorando + "&id_status_memorando=1&modulo=memorando id=importante" + item.id_memorando + "><img src=<?php echo WWW; ?>img/importante.png width=25px height=25px title='Importante'></a>");
                }

                if (item.id_status_memorando == 5) {
                    document.getElementById(item.id_memorando).style.backgroundColor = "rgba(249, 255, 160, 0.9)";
                    $("#pendente" + item.id_memorando).html("<a href=<?php echo WWW; ?>controle/control.php?nomeClasse=MemorandoControle&metodo=alterarIdStatusMemorando&id_memorando=" + item.id_memorando + "&id_status_memorando=1&modulo=memorando id=pendente" + item.id_memorando + "><img src=<?php echo WWW; ?>img/pendente.png width=25px height=25px title='Pendente'></a>");
                }

                if (item.id_status_memorando == 3) {
                    document.getElementById(item.id_memorando).style.backgroundColor = "rgba(195, 230, 255, 0.83)";
                    $("#naolido" + item.id_memorando).html("<a href=<?php echo WWW; ?>controle/control.php?nomeClasse=MemorandoControle&metodo=alterarIdStatusMemorando&id_memorando=" + item.id_memorando + "&id_status_memorando=1&modulo=memorando class=naolido><img src='<?php echo WWW; ?>img/lido.png' width=25px height=25px title='Lido'></a>");
                }
                if (item.id_status_memorando == 2) {
                    $("#naolido" + item.id).html("<a href=<?php echo WWW; ?>controle/control.php?nomeClasse=MemorandoControle&metodo=alterarIdStatusMemorando&id_memorando=" + item.id_memorando + "&id_status_memorando=3&modulo=memorando class=naolido><img src='<?php echo WWW; ?>img/nao-lido.png' width=25px height=25px title='Não lido'></a>");
                }
                if (item.id_pessoa == item.id_destinatario) {
                    $("#opcoes_" + item.id_memorando).append("<a href=<?php echo WWW; ?>controle/control.php?nomeClasse=MemorandoControle&metodo=alterarIdStatusMemorando&id_memorando=" + item.id_memorando + "&id_status_memorando=6&modulo=memorando><img src='<?php echo WWW; ?>img/arquivar.png' width=25px height=25px title='Arquivar memorando'></a>")
                }

            });

            $("#header").load("<?php echo WWW; ?>html/header.php");
            $(".menuu").load("<?php echo WWW; ?>html/menu.php");
        });
    </script>

    <style type="text/css">
        .select {
            position: absolute;
            width: 235px;
        }

        .panel-body {
            margin-bottom: 15px;
        }

        img {
            margin-left: 10px;
        }

        /* print styles*/
        @media print {
            .printable {
                display: block;
            }

            .screen {
                display: none;
            }
        }
    </style>
</head>

<body>
    <section class="body">
        <!-- start: header -->
        <div id="header"></div>
        <!-- end: header -->
        <div class="inner-wrapper">
            <!-- start: sidebar -->
            <aside id="sidebar-left" class="sidebar-left menuu"></aside>
            <!-- end: sidebar -->
            <section role="main" class="content-body">
                <header class="page-header">
                    <h2>Caixa de entrada</h2>
                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li>
                                <a href="<?php echo WWW; ?>html/home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li><span>Caixa de Entrada</span></li>
                        </ol>
                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>
                <!-- start: page -->
                <section class="panel">
                    <?php
                    if (isset($_GET['msg'])) {
                        if ($_GET['msg'] == 'success') {
                            echo ('<div class="alert alert-success"><i class="fas fa-check mr-md"></i><a href="#" class="close" onclick="closeMsg()" data-dismiss="alert" aria-label="close">&times;</a>' . $_GET["sccd"] . "</div>");
                        }
                    }
                    ?>

                    <div id="myModal">
                        <header class="panel-heading">
                            <h2 class="panel-title">Caixa de entrada</h2>
                        </header>
                        <div class="panel-body">
                            <button style="margin-bottom: 0px !important;" class="mb-xs mt-xs mr-xs btn btn-default" id="btnPrint">Imprimir</button>
                            <br><br>

                            <table class="table table-bordered table-striped mb-none" id="datatable-default">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Título</th>
                                        <th>Data</th>
                                        <th>Opções</th>
                                    </tr>
                                </thead>
                                <tbody id="tabela">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="printable"></div>
                </section>
            </section>
        </div>
    </section>

    <!-- end: page -->
    <!-- Vendor -->
    <script src="<?php echo WWW; ?>assets/vendor/select2/select2.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>

    <!-- Theme Base, Components and Settings -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.js"></script>

    <!-- Theme Custom -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.custom.js"></script>

    <!-- Theme Initialization Files -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.init.js"></script>
    <!-- Examples -->
    <script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.default.js"></script>
    <script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
    <script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.tabletools.js"></script>
    <script type="text/javascript">
        $(function() {
            $("#btnPrint").click(function() {
                //get the modal box content and load it into the printable div
                if ((typeof(impressao) == "undefined") || impressao != 1) {
                    $("#myModal a").removeAttr("href");
                    $(".printable").html($("#myModal").html());
                }
                $(".printable").printThis();
                var impressao = 1;
                $("#myModal").hide();
            });
        });
    </script>

    <div align="right">
        <iframe src="https://www.wegia.org/software/footer/memorando.html" width="200" height="60" style="border:none;"></iframe>
    </div>
</body>

</html>