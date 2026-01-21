<?php
date_default_timezone_set("America/Sao_Paulo");
require_once "./html/seguranca/security_headers.php";
require_once "./html/seguranca/sessionStart.php";
session_start();
if (isset($_SESSION['usuario'])) {
	header("Location: ./html/home.php");
}
setcookie("PHPSESSID", "", 0, "/");
session_destroy();
// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once "html/personalizacao_display.php";
?>
<!doctype html>
<html>
<head>
	<title><?php display_campo("Titulo", "str"); ?> - <?php display_campo("Subtitulo", "str"); ?></title>
	<meta charset="UTF-8" />
	<link rel="icon" href="<?php display_campo("Logo", "file"); ?>" type="image/x-icon">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<!-- Web Fonts  -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
	<!-- font inter -->
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
	<!-- font montserrat -->
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital@1&display=swap" rel="stylesheet">
	<!-- normalize / reset CSS -->
	<link rel="stylesheet" href="./css/normalize.css" />
	<link rel="stylesheet" href="./css/reset.css" />
	<!-- Vendor CSS -->
	<link rel="stylesheet" href="./assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="./assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="./assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="./assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<!---->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="./assets/vendor/jquery/jquery.min.js"></script>
	<!-- Skin CSS -->
	<link rel="stylesheet" href="./assets/stylesheets/skins/default.css" />
	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="./css/index-theme.css" />
	<!-- Head Libs -->
	<script src="./assets/vendor/modernizr/modernizr.js"></script>
	<!-- jQuery Mask -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>
	<?php
	if (isset($_GET['erro'])) {
		$erro = trim(filter_var($_GET['erro'], FILTER_SANITIZE_SPECIAL_CHARS));
	} else {
		$erro = "";
	}
	?>
	<script>
		const erro = '<?= htmlspecialchars($erro); ?>';
		if (erro == 'erro') {
			alert('Senha e/ou cpf inválido');
		}
	</script>
	<script>
		$(document).ready(function() {
			$("#login").keypress(function(event) {
				const isNumber = /^[0-9]$/i.test(event.key);
				if (isNumber)
					$("#login").mask("000.000.000-00");
				else
					$("#login").unmask();
			})
		});
	</script>

</head>

<body>
	<div class="container-fluid">
		<div class="row cabecalho">
			<div class="col-md-1 main-menu-logo">
				<a class="logo pull-left">
					<img src="<?php display_campo("Logo", "file"); ?>" height="50" />
				</a>
			</div>
			<div class="col-md-4 descricao header-description">
				<div>
					<div class="lar"><?php display_campo("Titulo", "str"); ?></div>
					<div class="wegia"><?php display_campo("Subtitulo", "str"); ?></div>
				</div>
			</div>
			<div class="col col-md-3 formulario">
				<form action="./html/login.php" method="POST" enctype="multipart/form-data" class="login">
					<div class="form-group mb-lg form-group-login"><!--login-->
						<div class="input-group input-group-icon"><!--icone-->
							<input id="login" name="cpf" type="text" class="form-control input-lg" placeholder="Usuário" />
							<span class="input-group-addon">
								<span class="icon icon-lg">
									<i class="fa fa-user"></i>
								</span>
							</span>
						</div>
					</div>
			</div>
			<div class="col col-md-3 formulario pass-form">
				<div class="form-group mb-lg form-group-login"><!--login-->
					<div class="input-group input-group-icon"><!--icone-->
						<input name="pwd" type="password" class="form-control input-lg form-input-pass" placeholder="Senha" />
						<span class="input-group-addon">
							<span class="icon icon-lg">
								<i class="fa fa-lock"></i>
							</span>
						</span>
					</div>
					<!-- AINDA NÃO IMPLEMENTADO 
						<a href="./html/esqueceu_senha.php">Esqueceu sua Senha?</a> -->
				</div>
			</div>
			<div class="col-md-1">
				<input type="submit" value="Entrar" class="btn btn-primary hidden-xs entrar"></input>
			</div>
			<!-- <div class="col-md-1">
					<div class="col-sm-3 text-right">
					</div>
				</div> -->
			</form>
		</div>
	</div>
	<div class="container" id="main-container-index">
		<div class="row corpo">
			<div class="col-md-8 carrosel">
				<div class="inferior">
					<div class="carouselLogin">
						<div id="myCarousel" class="carousel slide" data-ride="carousel">
							<div class="carousel-inner index-logo">
								<!-- start: carrossel -->
								<?php display_carrossel("Carrossel"); ?>
								<!-- end: carrossel -->
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-4 informacao">
				<div class="text">
					<div class="text-inner-paragraph">
						<?php display_campo("Conheça", "txt"); ?>
					</div>
					<div class="text-inner-paragraph">
						<?php display_campo("Objetivo", "txt"); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/index.html" width="200" height="60" style="border:none;"></iframe>
	</div>
	<div class="container-fluid">
		<div class="footer row" style="background-color: black">
			<div class="col-md-8">
				<p style="color: white; margin-left: 10px; margin-top: 8px;"><?php display_campo("Rodapé", "str"); ?></p>
			</div>
			<div class="col-md-4">
				<div class="pull-right">
					<a href="https://github.com/nilsonmori/WeGIA" target="_blank">
						<span class="fa fa-github-square" style="color: white"></span></a>
					<a href="https://www.facebook.com/wegiasoftware" target="_blank">
						<span class="fa fa-facebook-square" style="color: white"></span></a>
					<a href="https://www.wegia.org" target="_blank">
						<span class="fa fa-globe" style="color: white"></span></a>
				</div>
			</div>
		</div>
		<!-- Vendor -->
		<script src="./assets/vendor/select2/select2.js"></script>
		<script src="./assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="./assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="./assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
		<script src="./assets/vendor/nanoscroller/nanoscroller.js"></script>
		<!-- Theme Base, Components and Settings -->
		<script src="./assets/javascripts/theme.js"></script>
		<!-- Theme Custom -->
		<script src="./assets/javascripts/theme.custom.js"></script>
		<!-- Theme Initialization Files -->
		<script src="./assets/javascripts/theme.init.js"></script>
		<!-- Examples -->
		<script src="./assets/javascripts/tables/examples.datatables.default.js"></script>
		<script src="./assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
		<script src="./assets/javascripts/tables/examples.datatables.tabletools.js"></script>
</body>

</html>