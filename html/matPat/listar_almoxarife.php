<?php
session_start();
$config_path = '../../config.php';
require_once $config_path;
require_once ROOT . '/html/permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 91);

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';

// Adiciona a Função display_campo($nome_campo, $tipo_campo)

require_once ROOT . "/html/personalizacao_display.php";
?>

<!doctype html>
<html class="fixed">

<head>
	<?php
	include_once ROOT . '/dao/Conexao.php';
	include_once ROOT . '/dao/AlmoxarifadoDAO.php';

	if (!isset($_SESSION['almoxarife'])) {
		header('Location: ' . WWW . 'controle/control.php?metodo=listarTodos&nomeClasse=AlmoxarifeControle&nextPage=' . WWW . 'html/matPat/listar_almoxarife.php');
	} else {
		$almoxarife = $_SESSION['almoxarife'];
		unset($_SESSION['almoxarife']);
	}
	?>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Almoxarife</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap/css/bootstrap.css" />
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

	<!-- jquery functions -->
	<script>
		$(function() {
			$("#header").load("<?= WWW ?>html/header.php");
			$(".menuu").load("<?= WWW ?>html/menu.php");
		});
	</script>

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
					<h2>Almoxarife</h2>

					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Informações Almoxarife</span></li>
						</ol>

						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->

				<section class="panel">
					<header class="panel-heading">

						<h2 class="panel-title">Almoxarife</h2>
					</header>
					<div class="panel-body">
						<table class="table table-bordered table-striped mb-none" id="datatable-default">
							<thead>
								<tr>
									<th>Funcionário</th>
									<th>Almoxarifado</th>
									<th>Data de Registro</th>
									<th>Ação</th>
								</tr>
							</thead>
							<tbody id="tabela">
								<?php foreach (json_decode($almoxarife, true) as $item): ?>
									<tr>
										<td><?= htmlspecialchars($item['descricao_funcionario']) ?></td>
										<td><?= htmlspecialchars($item['descricao_almoxarifado']) ?></td>
										<td><?php $dataRegistro = new DateTime($item['data_registro']); echo $dataRegistro->format('d/m/Y à\s H:i');?></td>
										<td>
											<form method="POST" action="<?= WWW ?>controle/control.php">
												<input type="hidden" name="metodo" value="excluir">
												<input type="hidden" name="nomeClasse" value="AlmoxarifeControle">
												<input type="hidden" name="id_almoxarife" value="<?= (int)$item['id_almoxarife'] ?>">
												<?= Csrf::inputField() ?>
												<button type="submit" style="border:none;background:none;cursor:pointer;" title="Excluir">
													<i class="fas fa-trash-alt"></i>
												</button>
											</form>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div><br>
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
</body>

</html>