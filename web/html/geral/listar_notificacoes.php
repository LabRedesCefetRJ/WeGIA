<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once ROOT . '/dao/NotificacaoDAO.php';
require_once ROOT . "/html/personalizacao_display.php";

$dao = new NotificacaoDAO();
$notificacoes = $dao->listarPorUsuario((int) $_SESSION['id_pessoa']);

$naoVisualizadas = [];
$visualizadas = [];

foreach ($notificacoes as $n) {
    if ((int) $n['visualizada'] === 1) {
        $visualizadas[] = $n;
    } else {
        $naoVisualizadas[] = $n;
    }
}
?>

<!doctype html>
<html class="fixed">
<head>
    <meta charset="UTF-8">
    <title>Notificações</title>

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

	<!-- Specific Page Vendor CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/select2/select2.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

	<!-- Theme CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="<?= WWW ?>assets/vendor/modernizr/modernizr.js"></script>
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">

	<!-- Vendor -->
	<script src="<?= WWW ?>assets/vendor/jquery/jquery.min.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="<?= WWW ?>assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="<?= WWW ?>assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="<?= WWW ?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

	<!-- Specific Page Vendor -->
	<script src="<?= WWW ?>assets/vendor/jquery-autosize/jquery.autosize.js"></script>

	<!-- Theme Base, Components and Settings -->
	<script src="<?= WWW ?>assets/javascripts/theme.js"></script>

	<!-- Theme Custom -->
	<script src="<?= WWW ?>assets/javascripts/theme.custom.js"></script>

	<!-- Theme Initialization Files -->
	<script src="<?= WWW ?>assets/javascripts/theme.init.js"></script>

	<!-- javascript functions -->
	<script src="<?= WWW ?>Functions/onlyNumbers.js"></script>
	<script src="<?= WWW ?>Functions/onlyChars.js"></script>
	<script src="<?= WWW ?>Functions/enviar_dados.js"></script>
	<script src="<?= WWW ?>Functions/mascara.js"></script>

    <script>
        $(function() {
            $("#header").load("<?= WWW ?>html/header.php");
            $(".menuu").load("<?= WWW ?>html/menu.php");
        });

        function marcarComoLida(idNotificacao) {
            $.ajax({
                url: "<?= WWW ?>controle/control.php",
                method: "POST",
                dataType: "json",
                data: {
                    nomeClasse: "NotificacaoControle",
                    metodo: "marcarComoVisualizada",
                    id_notificacao: idNotificacao
                },
                success: function(resposta) {
                    if (resposta.sucesso) {
                        location.reload();
                    } else {
                        alert(resposta.mensagem || "Erro ao marcar notificação.");
                    }
                }
            });
        }

        function marcarTodasComoLidas() {
            $.ajax({
                url: "<?= WWW ?>controle/control.php",
                method: "POST",
                dataType: "json",
                data: {
                    nomeClasse: "NotificacaoControle",
                    metodo: "marcarTodasComoVisualizadas"
                },
                success: function(resposta) {
                    if (resposta.sucesso) {
                        location.reload();
                    } else {
                        alert(resposta.mensagem || "Erro ao marcar notificações.");
                    }
                }
            });
        }
    </script>
    <style>
        .table-responsive {
            border: none;
            margin-top: 0;
            overflow-x: visible;
        }

        .tabela-notificacoes {
            width: 100% !important;
            table-layout: fixed;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .tabela-notificacoes th {
            background-color: #f7f8fa;
            color: #333;
            font-weight: 600;
            vertical-align: middle !important;
        }

        .tabela-notificacoes th,
        .tabela-notificacoes td {
            padding: 13px 12px !important;
            vertical-align: middle !important;
            overflow-wrap: anywhere;
            word-break: normal;
            border-color: #e3e6e8 !important;
        }

        .tabela-notificacoes th:nth-child(1),
        .tabela-notificacoes td:nth-child(1) {
            width: 15%;
        }

        .tabela-notificacoes th:nth-child(2),
        .tabela-notificacoes td:nth-child(2) {
            width: 12%;
        }

        .tabela-notificacoes th:nth-child(3),
        .tabela-notificacoes td:nth-child(3) {
            width: 43%;
        }

        .tabela-notificacoes th:nth-child(4),
        .tabela-notificacoes td:nth-child(4) {
            width: 14%;
            white-space: nowrap;
        }

        .tabela-notificacoes th:nth-child(5),
        .tabela-notificacoes td:nth-child(5) {
            width: 16%;
            text-align: center;
        }

        .tabela-notificacoes td:nth-child(5) .btn {
            display: inline-block;
            margin: 2px;
            white-space: normal;
        }

        .tabela-notificacoes tbody tr {
            transition: background-color 0.15s ease;
        }

        .tabela-notificacoes tbody tr:hover {
            background-color: #f4f9fc !important;
        }

        #tabela-notificacoes-novas tbody tr td {
            font-weight: 500;
        }

        .dataTables_wrapper {
            padding: 18px 15px 8px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-top: 0;
        }

        .dataTables_wrapper > .row:first-child {
            display: flex;
            align-items: center;
            margin-bottom: 14px;
        }

        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 0;
            color: #666;
            font-weight: 400;
        }

        .dataTables_wrapper .dataTables_filter {
            text-align: right;
        }

        .dataTables_wrapper .dataTables_filter label {
            justify-content: flex-end;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            display: inline-block;
            height: 34px;
            margin-left: 0;
            padding: 6px 10px;
            color: #555;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
        }

        .dataTables_wrapper .dataTables_length select {
            width: 68px;
        }

        .dataTables_wrapper .dataTables_filter input {
            width: 220px;
        }

        .dataTables_wrapper .pagination {
            margin: 10px 0 0;
        }

        .dataTables_wrapper .dataTables_info {
            padding-top: 18px;
            color: #666;
        }

        .nav-tabs {
            margin-top: 20px;
            border-bottom-color: #ddd;
        }

        .nav-tabs > li > a {
            padding: 11px 18px;
            color: #666;
            background-color: #f7f7f7;
            border: 1px solid #ddd;
            border-bottom-color: transparent;
        }

        .nav-tabs > li.active > a,
        .nav-tabs > li.active > a:hover,
        .nav-tabs > li.active > a:focus {
            color: #333;
            font-weight: 600;
            background-color: #fff;
            border-top: 3px solid #0088cc;
        }

        .panel-body {
            padding-top: 20px;
        }

        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
            }

            .tabela-notificacoes {
                min-width: 850px;
            }

            .dataTables_wrapper > .row:first-child {
                display: block;
            }

            .dataTables_wrapper .dataTables_filter {
                margin-top: 10px;
                text-align: left;
            }

            .dataTables_wrapper .dataTables_filter label {
                justify-content: flex-start;
            }

            .dataTables_wrapper .dataTables_filter input {
                width: 100%;
            }
        }
    </style>
</head>

<body>
<section class="body">
    <div id="header"></div>

    <div class="inner-wrapper">
        <aside id="sidebar-left" class="sidebar-left menuu"></aside>

        <section role="main" class="content-body">
            <header class="page-header">
                <h2>Notificações</h2>

                <div class="right-wrapper pull-right">
                    <ol class="breadcrumbs">
                        <li>
                            <a href="<?= WWW ?>html/home.php">
                                <i class="fa fa-home"></i>
                            </a>
                        </li>
                        <li><span>Notificações</span></li>
                    </ol>

                    <a class="sidebar-right-toggle">
                        <i class="fa fa-chevron-left"></i>
                    </a>
                </div>
            </header>

            <section class="panel">
                <div class="panel-body">
                    <button class="btn btn-primary" onclick="marcarTodasComoLidas()">
                        Marcar todas como lidas
                    </button>

                    <br><br>

                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#novas" data-toggle="tab">
                                Novas
                                <?php if (count($naoVisualizadas) > 0): ?>
                                    <span class="badge"><?= count($naoVisualizadas) ?></span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <li>
                            <a href="#visualizadas" data-toggle="tab">
                                Visualizadas
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">

                        <div id="novas" class="tab-pane active">
                            <div class="table-responsive">
                                <table
                                    id="tabela-notificacoes-novas"
                                    class="table table-bordered table-striped table-hover tabela-notificacoes"
                                >
                                    <thead>
                                        <tr>
                                            <th>Módulo</th>
                                            <th>Título</th>
                                            <th>Mensagem</th>
                                            <th>Data</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php if (!empty($naoVisualizadas)): ?>
                                            <?php foreach ($naoVisualizadas as $n): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($n['recurso']) ?></td>
                                                    <td><?= htmlspecialchars($n['titulo']) ?></td>
                                                    <td><?= htmlspecialchars($n['mensagem']) ?></td>
                                                    <td><?= htmlspecialchars($n['data_criacao']) ?></td>
                                                    <td>
                                                        <?php if (!empty($n['link'])): ?>
                                                            <a class="btn btn-default btn-sm" href="<?= WWW . htmlspecialchars($n['link']) ?>">
                                                                Abrir
                                                            </a>
                                                        <?php endif; ?>

                                                        <button
                                                            class="btn btn-success btn-sm"
                                                            onclick="marcarComoLida(<?= (int) $n['id_notificacao'] ?>)"
                                                        >
                                                            Marcar como lida
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5">Nenhuma notificação nova.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>


                        <div id="visualizadas" class="tab-pane">
                            <div class="table-responsive">
                                <table
                                    id="tabela-notificacoes-visualizadas"
                                    class="table table-bordered table-striped table-hover tabela-notificacoes"
                                >
                                    <thead>
                                        <tr>
                                            <th>Módulo</th>
                                            <th>Título</th>
                                            <th>Mensagem</th>
                                            <th>Data</th>
                                            <th>Ação</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php if (!empty($visualizadas)): ?>
                                            <?php foreach ($visualizadas as $n): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($n['recurso']) ?></td>
                                                    <td><?= htmlspecialchars($n['titulo']) ?></td>
                                                    <td><?= htmlspecialchars($n['mensagem']) ?></td>
                                                    <td><?= htmlspecialchars($n['data_criacao']) ?></td>
                                                    <td>
                                                        <?php if (!empty($n['link'])): ?>
                                                            <a class="btn btn-default btn-sm" href="<?= WWW . htmlspecialchars($n['link']) ?>">
                                                                Abrir
                                                            </a>
                                                        <?php else: ?>
                                                            -
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5">Nenhuma notificação visualizada.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </section>
        </section>
    </div>
</section>

<!-- Specific Page Vendor -->
<script src="<?= WWW ?>assets/vendor/select2/select2.js"></script>
<script src="<?= WWW ?>assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
<script src="<?= WWW ?>assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
<script src="<?= WWW ?>assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>

<!-- Examples -->
<script src="<?= WWW ?>assets/javascripts/tables/examples.datatables.default.js"></script>
<script src="<?= WWW ?>assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
<script src="<?= WWW ?>assets/javascripts/tables/examples.datatables.tabletools.js"></script>

<script>
    $(document).ready(function () {
        const configuracaoTabela = {
            pageLength: 5,
            lengthMenu: [
                [5, 10, 25, 50],
                [5, 10, 25, 50]
            ],
            order: [[3, "desc"]],
            autoWidth: false,
            language: {
                emptyTable: "Nenhuma notificação encontrada.",
                info: "Mostrando _START_ até _END_ de _TOTAL_ notificações",
                infoEmpty: "Nenhuma notificação encontrada",
                infoFiltered: "(filtradas de _MAX_ notificações)",
                lengthMenu: "Mostrar _MENU_ notificações",
                loadingRecords: "Carregando...",
                processing: "Processando...",
                search: "Pesquisar:",
                zeroRecords: "Nenhuma notificação encontrada.",
                paginate: {
                    first: "Primeira",
                    last: "Última",
                    next: "Próxima",
                    previous: "Anterior"
                }
            }
        };

        <?php if (!empty($naoVisualizadas)): ?>
            $("#tabela-notificacoes-novas").DataTable(
                $.extend(true, {}, configuracaoTabela)
            );
        <?php endif; ?>

        <?php if (!empty($visualizadas)): ?>
            $("#tabela-notificacoes-visualizadas").DataTable(
                $.extend(true, {}, configuracaoTabela)
            );
        <?php endif; ?>
    });
</script>

<div class="text-right">
    <iframe src="https://www.wegia.org/software/footer/matPat.html" width="200" height="60" style="border: none;"></iframe>
</div>
</body>
</html>