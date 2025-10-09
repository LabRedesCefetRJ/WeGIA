<?php
session_start();
if (!isset($_SESSION['usuario'])) {
	header("Location: ../../index.php");
}

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
$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$id_pessoa = $_SESSION['id_pessoa'];
$resultado = mysqli_query($conexao, "SELECT * FROM funcionario WHERE id_pessoa=$id_pessoa");
if (!is_null($resultado)) {
	$id_cargo = mysqli_fetch_array($resultado);
	if (!is_null($id_cargo)) {
		$id_cargo = $id_cargo['id_cargo'];
	}
	$resultado = mysqli_query($conexao, "SELECT * FROM permissao WHERE id_cargo=$id_cargo and id_recurso=11");
	if (!is_bool($resultado) and mysqli_num_rows($resultado)) {
		$permissao = mysqli_fetch_array($resultado);
		if ($permissao['id_acao'] < 7) {
			$msg = "Você não tem as permissões necessárias para essa página.";
			header("Location: ../home.php?msg_c=$msg");
		}
		$permissao = $permissao['id_acao'];
	} else {
		$permissao = 1;
		$msg = "Você não tem as permissões necessárias para essa página.";
		header("Location: ../home.php?msg_c=$msg");
	}
} else {
	$permissao = 1;
	$msg = "Você não tem as permissões necessárias para essa página.";
	header("Location: ../home.php?msg_c=$msg");
}
// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once "../personalizacao_display.php";

// Funções de mensagem
require_once "../geral/msg.php";
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
?>
<!doctype html>
<html class="fixed">

<head>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Adicionar Tipo</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Web Fonts  -->
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
	<link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

	<!-- Theme CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="../../assets/vendor/modernizr/modernizr.js"></script>

	<!-- Vendor -->
	<script src="../../assets/vendor/jquery/jquery.min.js"></script>
	<script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
	<script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

	<!-- Specific Page Vendor -->
	<script src="../../assets/vendor/jquery-autosize/jquery.autosize.js"></script>

	<!-- Theme Base, Components and Settings -->
	<script src="../../assets/javascripts/theme.js"></script>

	<!-- Theme Custom -->
	<script src="../../assets/javascripts/theme.custom.js"></script>

	<!-- Theme Initialization Files -->
	<script src="../../assets/javascripts/theme.init.js"></script>

	<!-- javascript functions -->
	<script src="../../Functions/onlyNumbers.js"></script>
	<script src="../../Functions/onlyChars.js"></script>
	<script src="../../Functions/mascara.js"></script>

	<!-- jquery functions -->
	<script>
		document.write('<a href="' + document.referrer + '"></a>');
	</script>

	<script type="text/javascript">
		$(function() {
			$("#header").load("../header.php");
			$(".menuu").load("../menu.php");
		});
	</script>

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
					<h2>Cadastro de Tipo</h2>

					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Adicionar Tipo</span></li>
						</ol>

						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->
				<div class="row">
					<div class="col-md-4 col-lg-2" style="visibility: hidden;"></div>
					<div class="col-md-8 col-lg-8">
						<?php sessionMsg(); ?>
						<div class="tabs">
							<ul class="nav nav-tabs tabs-primary">
								<li class="active">
									<a href="#overview" data-toggle="tab">Inserir Tipo
									</a>
								</li>
							</ul>
							<div class="tab-content">
								<div id="overview" class="tab-pane active">
									<fieldset>
										<form method="post" id="formulario" action="../../controle/control.php">
											<?php
											if ($permissao == 1) {
												echo ($msg);
											} else {
											?>
												<div class="form-group"><br>
													<label class="col-md-3 control-label">Tipo</label>
													<div class="col-md-8">
														<input type="text" class="form-control" name="tipo" id="descricao_tipo" required>
													</div>
												</div><br />
												<input type="hidden" name="nomeClasse" value="QuadroHorarioControle">
												<?=Csrf::inputField()?>
												<input type="hidden" name="metodo" value="adicionarTipo">
												<input type="hidden" name="nextPage" value="<?= WWW . "html/" . basename(__DIR__) . "/" . basename(__FILE__); ?>">
												<div class="row">
													<div class="col-md-9 col-md-offset-3">
														<button id="enviar" class="btn btn-primary" type="submit">Enviar</button>
														<input type="reset" class="btn btn-default">

														<?php if(!$_SESSION['btnVoltar']):?>
														<button class="btn btn-info" type="button" onclick="window.history.back()">Voltar</button>
														<?php else:
															if (isset($_SESSION['btnVoltar'])){
																unset($_SESSION['btnVoltar']);
															}	
														?>
														<button class="btn btn-info" type="button" onclick="window.history.go(-2)">Voltar</button>
														<?php endif?>

														<a href="listar_tipo_quadro_horario.php" style="color: white; text-decoration:none;">
															<button class="btn btn-success" type="button">Listar tipos</button>
														</a>
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
	<!-- Complemento opcional às funções de mensagem -->
	<script src="../geral/msg.js"></script>
</body>
</html>