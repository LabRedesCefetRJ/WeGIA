<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE)
	session_start();

$config_path = "config.php";
if (file_exists($config_path)) {
	require_once($config_path);
} else {
	while (true) {
		$config_path = "../" . $config_path;
		if (file_exists($config_path)) break;
	}
	require_once($config_path);
}

if (!isset($_SESSION['usuario'])) {
	header("Location:  " . WWW . "html/index.php");
	exit();
}else{
	session_regenerate_id();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

permissao($_SESSION['id_pessoa'], 23, 5);

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";
?>
<!doctype html>
<html class="fixed">

<head>
	<?php
	include_once ROOT . '/dao/Conexao.php';
	include_once ROOT . '/dao/EntradaDAO.php';

	if (!isset($_SESSION['entrada'])) {
		header('Location: ' . WWW . 'controle/control.php?metodo=listarTodosComProdutos&nomeClasse=EntradaControle&nextPage=' . WWW . 'html/listar_entrada.php');
	}
	if (isset($_SESSION['entrada'])) {
		$entrada = $_SESSION['entrada'];

		unset($_SESSION['entrada']);
	}
	?>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Informações De Entradas Dos Almoxarifados</title>

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

	<!-- javascript functions -->
	<script src="<?= WWW ?>Functions/onlyNumbers.js"></script>
	<script src="<?= WWW ?>Functions/onlyChars.js"></script>
	<script src="<?= WWW ?>Functions/enviar_dados.js"></script>
	<script src="<?= WWW ?>Functions/mascara.js"></script>

	<!-- jquery functions -->
	<script>
		function listarId(id) {
			window.location.href = '<?= WWW ?>controle/control.php?metodo=listarId&nomeClasse=IentradaControle&nextPage=<?= WWW ?>html/matPat/listar_Ientrada.php&id_entrada=' + id;
		}
	</script>
	<script>
		$(function() {

			var entrada = <?php
							echo $entrada;
							?>;

			$.each(entrada, function(i, item) {
				$('#tabela')
					.append($('<tr style="cursor:pointer"/>')
						.attr('onclick', 'listarId("' + item.id_entrada + '")')
						.append($('<td />')
							.text(item.descricao_almoxarifado))
						.append($('<td />')
							.text(item.nome_origem))
						.append($('<td />')
							.text(item.descricao))
						.append($('<td />')
							.text(item.nome))
						.append($('<td />')
							.text(item.valor_total))
						.append($('<td />')
							.text(item.data))
						.append($('<td />')
							.text(item.hora)))
			})
		});
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
					<h2>Informações de Entradas Dos Almoxarifados</h2>

					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="<?= WWW ?>html/home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Informações Entrada</span></li>
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

						<h2 class="panel-title">Entradas</h2>

						<span style="color:red">Para mais informações, clique em uma entrada(*)</span>
					</header>
					<div class="panel-body">
						<table class="table table-bordered table-striped mb-none" id="datatable-default">
							<thead>
								<tr>
									<th>Almoxarifado</th>
									<th>Origem</th>
									<th>Tipo</th>
									<th>Resposável</th>
									<th>Valor Total</th>
									<th>Data</th>
									<th>Hora</th>
								</tr>
							</thead>
							<tbody id="tabela">
							</tbody>
						</table>
					</div><br>
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