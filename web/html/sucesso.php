<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
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
session_start();
if (!isset($_SESSION['usuario'])) {
	header("Location: ../index.php");
}

extract($_REQUEST);



// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once "personalizacao_display.php";

?>
<!doctype html>
<html class="fixed">

<head>

	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Home</title>
	<meta name="keywords" content="HTML5 Admin Template" />
	<meta name="description" content="Porto Admin - Responsive HTML5 Template">
	<meta name="author" content="okler.net">

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Web Fonts  -->
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
	<link rel="stylesheet" href="../assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />

	<!--Página Css que não interfere no estilo de oputras páginas do sistema-->
	<link rel="stylesheet" href="../css/home-theme.css" />

	<!-- Specific Page Vendor CSS -->
	<link rel="stylesheet" href="../assets/vendor/jquery-ui/css/ui-lightness/jquery-ui-1.10.4.custom.css" />
	<link rel="stylesheet" href="../assets/vendor/bootstrap-multiselect/bootstrap-multiselect.css" />
	<link rel="stylesheet" href="../assets/vendor/morris/morris.css" />

	<!-- Theme CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/theme.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon">

	<!-- Skin CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="../assets/vendor/modernizr/modernizr.js"></script>
	<script src="../Functions/lista.js"></script>
	<!-- Vendor -->
	<script src="../assets/vendor/jquery/jquery.min.js"></script>

	<script type="text/javascript">
		$(function() {
			$("#header").load("<?php echo WWW; ?>html/header.php");
			$(".menuu").load("<?php echo WWW; ?>html/menu.php");
		});
	</script>

</head>

<body>
	<section class="body">

		<!-- start: header -->
		<header class="header" id="header">

		</header>


		<div class="inner-wrapper">
			<!-- start: sidebar -->
			<aside id="sidebar-left" class="sidebar-left menuu">

			</aside>
			<!-- end: sidebar -->


			<header class="page-header">
				<h2>Sucesso!</h2>

				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li>
							<a href="home.php">
								<i class="fa fa-home"></i>
							</a>
						</li>
						<li><span>Sucesso</span></li>
					</ol>

					<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<section role="main" class="content-body">

				<div class="alert alert-success" style="font-size: 15px;"><i class="fas fa-check mr-md"></i><?php echo $_SESSION['msg']; ?><br><a href=<?php echo "'" . $_SESSION['link'] . "'"; ?>><?php echo $_SESSION['proxima']; ?></a>
				</div>
			</section>

			<!-- Vendor -->
			<script src="../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
			<script src="../assets/vendor/bootstrap/js/bootstrap.js"></script>
			<script src="../assets/vendor/nanoscroller/nanoscroller.js"></script>
			<script src="../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
			<script src="../assets/vendor/magnific-popup/magnific-popup.js"></script>
			<script src="../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

			<!-- Specific Page Vendor -->
			<script src="../assets/vendor/jquery-ui/js/jquery-ui-1.10.4.custom.js"></script>
			<script src="../assets/vendor/jquery-ui-touch-punch/jquery.ui.touch-punch.js"></script>
			<script src="../assets/vendor/jquery-appear/jquery.appear.js"></script>
			<script src="../assets/vendor/bootstrap-multiselect/bootstrap-multiselect.js"></script>
			<script src="../assets/vendor/jquery-easypiechart/jquery.easypiechart.js"></script>
			<script src="../assets/vendor/flot/jquery.flot.js"></script>
			<script src="../assets/vendor/flot-tooltip/jquery.flot.tooltip.js"></script>
			<script src="../assets/vendor/flot/jquery.flot.pie.js"></script>
			<script src="../assets/vendor/flot/jquery.flot.categories.js"></script>
			<script src="../assets/vendor/flot/jquery.flot.resize.js"></script>
			<script src="../assets/vendor/jquery-sparkline/jquery.sparkline.js"></script>
			<script src="../assets/vendor/raphael/raphael.js"></script>
			<script src="../assets/vendor/morris/morris.js"></script>
			<script src="../assets/vendor/gauge/gauge.js"></script>
			<script src="../assets/vendor/snap-svg/snap.svg.js"></script>
			<script src="../assets/vendor/liquid-meter/liquid.meter.js"></script>
			<script src="../assets/vendor/jqvmap/jquery.vmap.js"></script>
			<script src="../assets/vendor/jqvmap/data/jquery.vmap.sampledata.js"></script>
			<script src="../assets/vendor/jqvmap/maps/jquery.vmap.world.js"></script>
			<script src="../assets/vendor/jqvmap/maps/continents/jquery.vmap.africa.js"></script>
			<script src="../assets/vendor/jqvmap/maps/continents/jquery.vmap.asia.js"></script>
			<script src="../assets/vendor/jqvmap/maps/continents/jquery.vmap.australia.js"></script>
			<script src="../assets/vendor/jqvmap/maps/continents/jquery.vmap.europe.js"></script>
			<script src="../assets/vendor/jqvmap/maps/continents/jquery.vmap.north-america.js"></script>
			<script src="../assets/vendor/jqvmap/maps/continents/jquery.vmap.south-america.js"></script>

			<!-- Theme Base, Components and Settings -->
			<script src="../assets/javascripts/theme.js"></script>

			<!-- Theme Custom -->
			<script src="../assets/javascripts/theme.custom.js"></script>

			<!-- Theme Initialization Files -->
			<script src="../assets/javascripts/theme.init.js"></script>


			<!-- Examples -->
			<script src="../assets/javascripts/dashboard/examples.dashboard.js"></script>
</body>

</html>