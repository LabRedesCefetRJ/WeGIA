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

foreach($notificacoes as $n) {
    if((int)$n['visualizada'] === 1) {
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
                <header class="panel-heading">
                    <h2 class="panel-title">Minhas notificações</h2>
                </header>

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

                    <div class="tab-content" style="padding-top: 20px;">

                        <div id="novas" class="tab-pane active">
                            <table class="table table-bordered table-striped mb-none">
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
                                                        onclick="marcarComoLida(<?= (int)$n['id_notificacao'] ?>)"
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

                        <div id="visualizadas" class="tab-pane">
                            <table class="table table-bordered table-striped mb-none">
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
            </section>
        </section>
    </div>
</section>
                <!-- end: page -->

				<!-- Vendor -->

				<!-- Specific Page Vendor -->
				<script src="<?= WWW ?>assets/vendor/select2/select2.js"></script>
				<script src="<?= WWW ?>assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
				<script src="<?= WWW ?>assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
				<script src="<?= WWW ?>assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>

				<!-- Theme Base, Components and Settings -->
				<script src="<?= WWW ?>assets/javascripts/theme.js"></script>

				<!-- Theme Custom -->
				<script src="<?= WWW ?>assets/javascripts/theme.custom.js"></script>

				<!-- Theme Initialization Files -->
				<script src="<?= WWW ?>assets/javascripts/theme.init.js"></script>

				<!-- Examples -->
				<script src="<?= WWW ?>assets/javascripts/tables/examples.datatables.default.js"></script>
				<script src="<?= WWW ?>assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
				<script src="<?= WWW ?>assets/javascripts/tables/examples.datatables.tabletools.js"></script>
				<div align="right">
					<iframe src="https://www.wegia.org/software/footer/matPat.html" width="200" height="60" style="border:none;"></iframe>
				</div>
</body>
</html>