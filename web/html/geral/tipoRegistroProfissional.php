<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: " . WWW . "index.php");
    exit();
} else {
    session_regenerate_id();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($_SESSION['id_pessoa'], 11, 1);

require_once ROOT . "/html/personalizacao_display.php";
?>
<!doctype html>
<html class="fixed" lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Tipos de Registros Profissionais</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

    <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

    <link rel="stylesheet" href="../../assets/vendor/select2/select2.css" />
    <link rel="stylesheet" href="../../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

    <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
    <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
    <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">

    <script src="../../assets/vendor/modernizr/modernizr.js"></script>

    <script src="../../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
    <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

    <script src="<?php echo WWW; ?>Functions/onlyNumbers.js"></script>
    <script src="<?php echo WWW; ?>Functions/onlyChars.js"></script>
    <script src="<?php echo WWW; ?>Functions/mascara.js"></script>
    <script src="<?php echo WWW; ?>Functions/tiposRegistrosProfissionais.js"></script>

    <script type="text/javascript">
        $(function() {
            $("#header").load("<?php echo WWW; ?>html/header.php");
            $(".menuu").load("<?php echo WWW; ?>html/menu.php");
        });
    </script>
</head>

<body>
    <section class="body">
        <header id="header"></header>
        <div class="inner-wrapper">
            <aside id="sidebar-left" class="sidebar-left menuu"></aside>

            <section role="main" class="content-body">
                <header class="page-header">
                    <h2>Tipos de Registros Profissionais</h2>
                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li>
                                <a href="../home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li><span>Páginas</span></li>
                            <li><span>Tipos de Registros Profissionais</span></li>
                        </ol>
                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>

                <div class="row">
                    <section class="panel">
                        <header class="panel-heading">
                            <div class="panel-actions">
                                <a href="#" class="fa fa-caret-down"></a>
                            </div>
                            <h2 class="panel-title">Tipos de Registros Profissionais</h2>
                        </header>
                        <div class="panel-body">
                            <div id="alerta-mensagem"></div>

                            <div class="row mb-md">
                                <div class="col-md-3 pull-left">
                                    <label for="select_status" class="control-label"><strong>Filtrar por Status:</strong></label>
                                    <select id="select_status" name="select_status" class="form-control" onchange="carregarTabelaTiposRegistros()">
                                        <option value="1" selected>Ativos</option>
                                        <option value="0">Inativos</option>
                                    </select>
                                </div>
                            </div>

                            <table class="table text-center table-bordered table-striped mb-none" id="datatable-default">
                                <thead>
                                    <tr>
                                        <th class="text-center">ID</th>
                                        <th class="text-center">Descrição</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="tabela-tipos-registros">
                                    </tbody>
                            </table>
                        </div>
                        <div class="panel-footer clearfix">
                            <button onclick="adicionarTipoRegistro()" class="btn btn-primary pull-right">
                                <i class="fa fa-plus"></i> Adicionar Tipo de Registro Profissional
                            </button>
                        </div>
                    </section>
                </div>
            </section>
        </div>

        <div align="right">
            <iframe src="https://www.wegia.org/software/footer/conf.html" width="200" height="60" style="border:none;"></iframe>
        </div>
    </section>

    <script src="../../assets/vendor/select2/select2.js"></script>
    <script src="../../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
    <script src="../../assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
    <script src="../../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>

    <script src="../../assets/javascripts/theme.js"></script>
    <script src="../../assets/javascripts/theme.custom.js"></script>
    <script src="../../assets/javascripts/theme.init.js"></script>

    <script>
        $(document).ready(function() {
            carregarTabelaTiposRegistros();
        });
    </script>
</body>
</html>