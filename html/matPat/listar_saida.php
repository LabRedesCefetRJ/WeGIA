<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE)
	session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: " . WWW . "html/index.php");
	exit();
} else {
	session_regenerate_id();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

permissao($_SESSION['id_pessoa'], 24, 5);

// Incluindo arquivo de personalização de display
require_once ROOT . "/html/personalizacao_display.php";

include_once ROOT . '/dao/Conexao.php';
include_once ROOT . '/dao/SaidaDAO.php';

// Validando sessão de saída
if (!isset($_SESSION['saida'])) {
	header('Location: ' . WWW . 'controle/control.php?metodo=listarTodos&nomeClasse=SaidaControle&nextPage=' . WWW . 'html/matPat/listar_saida.php');
} else {
	$saida = $_SESSION['saida'];
	unset($_SESSION['saida']);
}
?>
<!doctype html>
<html class="fixed">

<head>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Informações de Saídas dos Almoxarifados</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />

	<!-- Specific Page Vendor CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/select2/select2.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

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

	<!-- javascript functions -->
	<script src="<?= WWW ?>Functions/onlyNumbers.js"></script>
	<script src="<?= WWW ?>Functions/onlyChars.js"></script>
	<script src="<?= WWW ?>Functions/enviar_dados.js"></script>
	<script src="<?= WWW ?>Functions/mascara.js"></script>

	<!-- jquery functions -->
	<script>
		function listarId(id) {
			window.location.href = '<?= WWW ?>controle/control.php?metodo=listarId&nomeClasse=IsaidaControle&nextPage=<?= WWW ?>html/matPat/listar_Isaida.php&id_saida=' + id;
		}
	</script>
	<script>
		$(function() {
			var saida = <?php
						echo $saida;
						?>;

			$.each(saida, function(i, item) {
				$('#tabela')
					.append($('<tr style="cursor:pointer" />')
						.attr('onclick', 'listarId("' + item.id_saida + '")')
						.append($('<td />')
							.text(item.descricao_almoxarifado))
						.append($('<td />')
							.text(item.nome_destino))
						.append($('<td />')
							.text(item.descricao))
						.append($('<td />')
							.text(item.nome))
						.append($('<td />')
							.text(item.valor_total))
						.append($('<td />')
							.text(item.data))
						.append($('<td />')
							.text(item.hora)
						)
					)
			})
		});
		$(function() {
			$("#header").load("../header.php");
			$(".menuu").load("../menu.php");
		});
	</script>
</head>

<body>
	<section class="body">
		<div id="header"></div>
		<!-- end: header -->
		<div class="inner-wrapper">
			<!-- start: sidebar -->
			<aside id="sidebar-left" class="sidebar-left menuu"></aside>

			<!-- end: sidebar -->
			<section role="main" class="content-body">
				<header class="page-header">
					<h2>Informações de Saídas dos Almoxarifados</h2>

					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="<?= WWW ?>html/home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Informações de Saídas</span></li>
						</ol>

						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->

				<section class="panel">
					<header class="panel-heading">
						<div class="panel-actions">
							<a href="#" class="fa fa-caret-down"></a>
						</div>
						<h2 class="panel-title">Saídas</h2>

						<span style="color:red">Para mais informações, clique em uma saída(*)</span>
					</header>
					<!-- start: page -->

					<div class="panel-body">
						<table class="table table-bordered table-striped mb-none" id="datatable-default">
							<thead>
								<tr>
									<th>Almoxarifado</th>
									<th>Destino</th>
									<th>Tipo</th>
									<th>Responsável</th>
									<th>Valor Total</th>
									<th>Data</th>
									<th>Hora</th>
								</tr>
							</thead>
							<tbody id="tabela">
							</tbody>
						</table>
					</div><br>

					<!-- verificar -->
				</section>
				<!-- verificar -->
			</section>
		</div>
	</section>
	<!-- end: page -->

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