<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

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
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'personalizacao_display.php';

permissao($_SESSION['id_pessoa'], 5, 5);
?>
<!doctype html>
<html class="fixed">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Plantões</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="../../assets/vendor/select2/select2.css" />
    <link rel="stylesheet" href="../../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
    <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
    <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
    <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css" />
    <link rel="stylesheet" href="./style/equipe_plantao_historico.css" />

    <link rel="icon" href="<?php display_campo('Logo', 'file'); ?>" type="image/x-icon" id="logo-icon">

    <script src="../../assets/vendor/modernizr/modernizr.js"></script>
    <script src="../../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
    <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
    <script src="../../assets/vendor/select2/select2.js"></script>
    <script src="../../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
    <script src="../../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
    <script src="../../assets/javascripts/theme.js"></script>
    <script src="../../assets/javascripts/theme.custom.js"></script>
    <script src="../../assets/javascripts/theme.init.js"></script>

    <script>
        $(function() {
            $("#header").load("../header.php");
            $(".menuu").load("../menu.php");
        });

        window.plantaoHistoricoConfig = {
            endpoint: '../../controle/control.php',
            nomeClasse: 'SaudeEquipePlantaoHistoricoControle'
        };
    </script>
</head>
<body>
<section class="body">
    <div id="header"></div>

    <div class="inner-wrapper">
        <aside id="sidebar-left" class="sidebar-left menuu"></aside>

        <section role="main" class="content-body">
            <header class="page-header">
                <h2>Histórico de Plantões</h2>
                <div class="right-wrapper pull-right">
                    <ol class="breadcrumbs">
                        <li><a href="../index.php"><i class="fa fa-home"></i></a></li>
                        <li><span>Módulo Saúde</span></li>
                        <li><span>Equipe</span></li>
                        <li><span>Histórico de Plantões</span></li>
                    </ol>
                    <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                </div>
            </header>

            <div id="historicoMessage" class="alert alert-info alert-inline" role="alert"></div>

            <section class="panel">
                <header class="panel-heading">
                    <h2 class="panel-title">Escalas salvas</h2>
                </header>
                <div class="panel-body">
                    <div class="historico-acoes">
                        <button id="btnAtualizarHistorico" class="btn btn-default btn-sm" type="button">
                            <i class="fa fa-refresh"></i> Atualizar histórico
                        </button>
                    </div>

                    <table class="table table-bordered table-striped mb-none" id="datatable-historico-plantao">
                        <thead>
                            <tr>
                                <th>Período</th>
                                <th>Dias com escala</th>
                                <th>Turnos definidos</th>
                                <th>Status</th>
                                <th>Última atualização</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="historicoPlantaoBody">
                            <tr>
                                <td colspan="6">Carregando histórico...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <div align="right">
                <iframe src="https://www.wegia.org/software/footer/saude.html" width="200" height="60" style="border:none; margin-top: 30px;"></iframe>
            </div>
        </section>
    </div>
</section>

<script src="./script/equipe_plantao_historico.js"></script>
</body>
</html>
