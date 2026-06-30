<?php
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
	<!-- Skin CSS -->
	<link rel="stylesheet" href="./assets/stylesheets/skins/default.css" />
	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="./css/index-theme.css" />

	<!-- Head Libs -->
	<script src="./assets/vendor/modernizr/modernizr.js"></script>
	<script src="./assets/vendor/jquery/jquery.min.js"></script>
	<script src="./assets/vendor/bootstrap/js/bootstrap.min.js"></script>
	<!-- jQuery Mask -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>
	<?php
	$erro = '';
	$login_error_modal = null;
	if (isset($_GET['erro'])) {
		$erro = trim(filter_var($_GET['erro'], FILTER_SANITIZE_SPECIAL_CHARS));
	}

	$login_error_map = [
		'erro' => [
			'type' => 'error',
			'iconPrefix' => 'fa',
			'icon' => 'fa-times-circle',
			'title' => 'Falha no acesso',
			'message' => 'CPF e/ou senha inválidos. Verifique os dados informados e tente novamente.',
			'action' => 'Entendi',
		],
		'usuario_inativo' => [
			'type' => 'warning',
				'iconPrefix' => 'glyphicon',
				'icon' => 'glyphicon-ban-circle',
			'title' => 'Usuário inativo',
				'message' => 'Este acesso pertence a um usuário inativo. A senha não foi validada porque o cadastro está bloqueado.',
			'action' => 'Entendi',
		],
		'dados_invalidos' => [
			'type' => 'warning',
				'iconPrefix' => 'fa',
			'icon' => 'fa-exclamation-triangle',
			'title' => 'Campos obrigatórios',
			'message' => 'Preencha todos os campos obrigatórios antes de continuar.',
			'action' => 'Entendi',
		],
		'acesso_revogado' => [
			'type' => 'error',
				'iconPrefix' => 'fa',
			'icon' => 'fa-ban',
			'title' => 'Acesso revogado',
			'message' => 'Seu acesso foi revogado pelo administrador. Caso isso pareça incorreto, solicite uma revisão do cadastro.',
			'action' => 'Entendi',
		],
	];

	if (isset($login_error_map[$erro])) {
		$login_error_modal = $login_error_map[$erro];
	}
	?>
	<?php if ($login_error_modal !== null) : ?>
		<script>
			window.loginErrorModalData = <?= json_encode($login_error_modal, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
		</script>
	<?php endif; ?>
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

		$(function() {
			if (window.loginErrorModalData) {
				var $modal = $('#loginErrorModal');
				$modal.addClass('login-error--' + window.loginErrorModalData.type);
				$modal.find('.login-error-icon')
					.attr('class', 'login-error-icon ' + (window.loginErrorModalData.iconPrefix || 'fa') + ' ' + window.loginErrorModalData.icon)
					.empty();
				$modal.find('.login-error-title').text(window.loginErrorModalData.title);
				$modal.find('.login-error-message').text(window.loginErrorModalData.message);
				$modal.find('.btn-login-error').text(window.loginErrorModalData.action || 'Entendi');
				$modal.on('shown.bs.modal', function() {
					$modal.find('.modal-close-button').trigger('focus');
				});
				$modal.modal({
					backdrop: true,
					keyboard: true,
					show: true
				});
			}
		});
	</script>

</head>  

<body>
	<div class="modal fade login-error-modal" id="loginErrorModal" tabindex="-1" role="dialog" aria-labelledby="loginErrorModalTitle" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="modal-close-button" data-dismiss="modal" aria-label="Fechar">
						<span aria-hidden="true">&times;</span>
					</button>
					<div class="text-center login-error-top">
						<div class="login-error-icon fa fa-exclamation-circle" aria-hidden="true"></div>
						<h4 class="login-error-title" id="loginErrorModalTitle">Atenção</h4>
					</div>
				</div>
				<div class="modal-body text-center">
					<p class="login-error-message">Verifique as informações e tente novamente.</p>
				</div>
				<div class="modal-footer text-center">
					<button type="button" class="btn btn-login-error" data-dismiss="modal">Entendi</button>
				</div>
			</div>
		</div>
	</div>
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
