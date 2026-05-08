<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE)
	session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: " . WWW . "index.php");
	exit();
} else {
	session_regenerate_id();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

permissao($_SESSION['id_pessoa'], 91, 7);

extract($_SESSION);
$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

require_once ROOT . "/html/personalizacao_display.php";

$recurso = mysqli_query($conexao, "SELECT * FROM recurso WHERE recurso.descricao LIKE 'Módulo %'");

require_once '../geral/msg.php';
?>
<!doctype html>
<html class="fixed" lang="pt-br">

<head>
	<!-- Basic -->
	<meta charset="utf-8">

	<title>Editar Visualização de Módulos</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
	<!-- Vendor CSS -->
	<link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
	<link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

	<!-- Specific Page Vendor CSS -->
	<link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/select2/select2.css" />
	<link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

	<!-- Theme CSS -->
	<link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="<?php echo WWW; ?>assets/vendor/modernizr/modernizr.js"></script>

	<!-- Vendor -->
	<script src="<?php echo WWW; ?>assets/vendor/jquery/jquery.min.js"></script>
	<script src="<?php echo WWW; ?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="<?php echo WWW; ?>assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="<?php echo WWW; ?>assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="<?php echo WWW; ?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="<?php echo WWW; ?>assets/vendor/magnific-popup/magnific-popup.js"></script>
	<script src="<?php echo WWW; ?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

	<!-- Specific Page Vendor -->
	<script src="<?php echo WWW; ?>assets/vendor/jquery-autosize/jquery.autosize.js"></script>

	<!-- Theme Base, Components and Settings -->
	<script src="<?php echo WWW; ?>assets/javascripts/theme.js"></script>

	<!-- Theme Custom -->
	<script src="<?php echo WWW; ?>assets/javascripts/theme.custom.js"></script>

	<!-- Theme Initialization Files -->
	<script src="<?php echo WWW; ?>assets/javascripts/theme.init.js"></script>


	<!-- javascript functions -->
	<script src="<?php echo WWW; ?>Functions/onlyNumbers.js"></script>
	<script src="<?php echo WWW; ?>Functions/onlyChars.js"></script>
	<script src="<?php echo WWW; ?>Functions/mascara.js"></script>

	<!-- Estoque CSS -->
	<link rel="stylesheet" href="../../css/estoque.css">

	<script type="text/javascript">
		$(function() {
			$("#header").load("<?php echo WWW; ?>html/header.php");
			$(".menuu").load("<?php echo WWW; ?>html/menu.php");
		});
	</script>

	<!-- Almoxarife -->
</head>

<body>
	<section class="body">

		<!-- start: header -->
		<header id="header" class="header">

			<!-- end: search & user box -->
		</header>
		<!-- end: header -->
		<div class="inner-wrapper">
			<!-- start: sidebar -->
			<aside id="sidebar-left" class="sidebar-left menuu"></aside>
			<!-- end: sidebar -->

			<section role="main" class="content-body">
				<header class="page-header">
					<h2>Módulos Visíveis</h2>

					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="../home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Módulos Visíveis</span></li>
						</ol>

						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->


				<div class="row">
					<div class="col-md-4 col-lg-12" style="visibility: hidden;"></div>
					<div class="col-md-8 col-lg-8">
						<!-- Caso haja uma mensagem do sistema -->
						<?php getMsg(); ?>
						<div class="tabs">
							<ul class="nav nav-tabs tabs-primary">
								<li class="active">
									<a href="#overview" data-toggle="tab">Módulos Visíveis</a>
								</li>
							</ul>
							<div class="tab-content">
								<div id="overview" class="tab-pane active">
									<fieldset>
										<form method="post" id="formulario" action="<?php echo (WWW . 'controle/control.php'); ?>">
											<?php
											if (isset($_GET['msg_c'])) {
												$msg = filter_input(INPUT_GET, 'msg_c', FILTER_SANITIZE_SPECIAL_CHARS);
												echo ('<div class="alert alert-success" role="alert">
												' . htmlspecialchars($msg) . '
											  </div>');
											}
											if ($permissao == 1) {
												echo (htmlspecialchars($msg) . " - " . $permissao);
											} else {
											?>
												<div class="form-group">
													<label class="col-md-3 control-label" for="inputSuccess">Módulos</label>
													<div class="col-md-6">
														<?php
														while ($row = $recurso->fetch_array(MYSQLI_NUM)) {
															echo "<div class='checkbox'> <label><input id='recurso_" . $row[0] . "' class='recurso' name='recurso[]' type='checkbox' value=" . $row[0] . ">" . $row[1] . "</label> </div>";
														}
														?>
													</div>
												</div>

												<input type="hidden" name="nomeClasse" value="ModuloControle">
												<input type="hidden" name="metodo" value="alterar_modulos_visiveis">
												<input type="hidden" name="nextPage" value="<?php echo (WWW . 'html/geral/modulos_visiveis.php'); ?>">
												<div class="row">
													<div class="col-md-9 col-md-offset-3">
														<button id="editar" class="btn btn-primary" onclick="editar_modulo()" type="button">Editar</button>
														<input id="salvar" class="btn btn-primary" value="Salvar" type="hidden" />
													</div>
												</div>
											<?php
											}
											?>
										</form>
									</fieldset>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- end: page -->
			</section>
		</div>
		<aside id="sidebar-right" class="sidebar-right">
			<div class="nano">
				<div class="nano-content">
					<a href="#" class="mobile-close visible-xs">
						Collapse <i class="fa fa-chevron-right"></i>
					</a>
				</div>
			</div>
		</aside>
	</section>

	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/conf.html" width="200" height="60" style="border:none;"></iframe>
	</div>

</body>
<script>
	$(document).ready(function() {
		setTimeout(function() {
			$(".alert").fadeOut();
		}, 3000);
	});

	function verificar_modulos() {
		url = '../../dao/verificar_modulos_visiveis.php';
		$.ajax({
			type: "POST",
			url: url,
			success: function(response) {
				var recursos = JSON.parse(response);
				$(".recurso").prop("checked", false).attr("disabled", true);
				for (recurso of recursos) {
					$("#recurso_" + recurso).prop("checked", true).attr("disabled", true);
				}
			},
			dataType: 'text'
		})
	}


	function editar_modulo() {
		$(".recurso").attr("disabled", false);
		$("#editar").attr("id", "cancelar");
		$("#cancelar").attr("class", "btn btn-danger").attr("onclick", "cancelar_edicao()").text("Cancelar");
		$("#salvar").attr("type", "submit");
	}

	function cancelar_edicao() {
		$(".recurso").attr("disabled", true);
		$("#cancelar").attr("id", "editar");
		$("#editar").attr("class", "btn btn-primary").attr("onclick", "editar_modulo()").text("Editar");
		$("#salvar").attr("type", "hidden");
	}
	$(document).ready(function() {
		verificar_modulos();
	});
</script>
<script src="../geral/msg.js"></script>

</html>