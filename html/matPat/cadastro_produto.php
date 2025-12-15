<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION['usuario'])) {
	header("Location: " . WWW . "html/index.php");
	exit();
} else {
	session_regenerate_id();
}

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($_SESSION['id_pessoa'], 22, 3);

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";

// Adiciona Função de mensagem
require_once ROOT . "/html/geral/msg.php";
?>

<!doctype html>
<html class="fixed">
<?php
include_once ROOT . '/dao/Conexao.php';
include_once ROOT . '/dao/CategoriaDAO.php';
include_once ROOT . '/dao/UnidadeDAO.php';

if (!isset($_SESSION['unidade'])) {
	header('Location: ' . WWW . 'controle/control.php?metodo=listarTodos&nomeClasse=UnidadeControle&nextPage=../html/matPat/cadastro_produto.php');
}
if (!isset($_SESSION['categoria'])) {
	header('Location: ' . WWW . 'controle/control.php?metodo=listarTodos&nomeClasse=CategoriaControle&nextPage=../html/matPat/cadastro_produto.php');
}
if (isset($_SESSION['categoria']) && isset($_SESSION['unidade'])) {
	extract($_SESSION);

	unset($_SESSION['unidade']);
	unset($_SESSION['categoria']);
}

$dadosForm = $_SESSION['form_produto'];

header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
?>

<head>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Cadastro de Produto</title>
	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Web Fonts  -->
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

	<!-- Theme CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="<?= WWW ?>assets/vendor/modernizr/modernizr.js"></script>

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
	<script src="<?= WWW ?>Functions/mascara.js"></script>
	<script src="https://igorescobar.github.io/jQuery-Mask-Plugin/js/jquery.mask.min.js"></script>

	<!-- jquery functions -->
	<script>
		$(function() {
			const categoria = <?php
								echo $categoria;
								?>;
			const unidade = <?php
							echo $unidade;
							?>;
			$.each(categoria, function(i, item) {
				if (atualizarSelect('id_categoria') == item.id_categoria_produto) {
					$('#id_categoria').append('<option value="' + item.id_categoria_produto + '" selected>' + item.descricao_categoria + '</option>');
				} else {
					$('#id_categoria').append('<option value="' + item.id_categoria_produto + '">' + item.descricao_categoria + '</option>');
				}
			})
			$.each(unidade, function(i, item) {
				if (atualizarSelect('id_unidade') == item.id_unidade) {
					$('#id_unidade').append('<option value="' + item.id_unidade + '" selected>' + item.descricao_unidade + '</option>');
				} else {
					$('#id_unidade').append('<option value="' + item.id_unidade + '">' + item.descricao_unidade + '</option>');
				}
			})
		});
	</script>
	<script type="text/javascript">
		function validar() {
			const id_categoria = document.getElementyById("id_categoria").value;
			const id_unidade = document.getElementyById("id_unidade").value;
			if (id_categoria == "blank") {
				alert("Selecione uma categoria");
				document.getElementyById("id_categoria").focus();
				return false;
			} else if (id_unidade == "blank") {
				alert("Selecione uma unidade");
				document.getElementyById("id_unidade").focus();
				return false;
			}
		}
		$('.dinheiro').mask('#.##0,00', {
			reverse: true
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
					<h2>Cadastro</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="<?= WWW ?>html/home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Adicionar produtos</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->
				<div class="row">
					<div class="col-md-4 col-lg-2" style="visibility: hidden;"></div>
					<div class="col-md-8 col-lg-8">
						<?php getMsg(); ?>
						<div class="tabs">
							<ul class="nav nav-tabs tabs-primary">
								<li class="active">
									<a href="#overview" data-toggle="tab">Cadastro de Produto</a>
								</li>
							</ul>
							<div class="tab-content">
								<div id="overview" class="tab-pane active">
									<form id="formulario" action="<?= WWW ?>controle/control.php" onsubmit="return validar()" autocomplete="off">
										<fieldset>
											<div class="form-group"><br>
												<label class="col-md-3 control-label">Nome do produto</label>
												<div class="col-md-8">
													<input type="text" class="form-control" name="descricao" id="produto" onchange="addSessionStorage(this)" value="" required>
												</div>
											</div>

											<div class="form-group">
												<label class="col-md-3 control-label" for="inputSuccess">Categoria</label>
												<a href="<?= WWW ?>html/matPat/adicionar_categoria.php">
													<i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i>
												</a>
												<div class="col-md-6">
													<select name="id_categoria" id="id_categoria" class="form-control input-lg mb-md" onchange="addSessionStorage(this)">
														<option selected disabled value="blank">Selecionar</option>
													</select>
												</div>
											</div>

											<div class="form-group">
												<label class="col-md-3 control-label" for="id_unidade">Unidade</label>
												<a href="<?= WWW ?>html/matPat/adicionar_unidade.php"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
												<div class="col-md-6">
													<select name="id_unidade" id="id_unidade" class="form-control input-lg mb-md" onchange="addSessionStorage(this)">
														<option selected disabled value="blank">Selecionar</option>


													</select>
												</div>
											</div>

											<div class="form-group">
												<label class="col-md-3 control-label" for="codigo">Código</label>
												<div class="col-md-8">
													<input type="number" name="codigo" class="form-control" id="codigo" onchange="addSessionStorage(this)" required>

													<input type="hidden" name="nomeClasse" value="ProdutoControle">

													<input type="hidden" name="metodo" value="incluir">
												</div>
											</div>

											<div class="form-group">
												<label class="col-md-3 control-label" for="profileCompany">Valor</label>
												<div class="col-md-8">
													<input type="number" name="preco" class="form-control" id="valor-form" step="any" placeholder="Ex: 22.00" onchange="addSessionStorage(this)" required>

													<input type="hidden" name="nomeClasse" value="ProdutoControle">

													<input type="hidden" name="metodo" value="incluir">

												</div>
											</div>

											<div class="panel-footer">
												<div class="row">
													<div class="col-md-9 col-md-offset-3">
														<button type="submit" class="btn btn-primary" onclick="limparSessionStorage()">Enviar</button>
														<input type="reset" class="btn btn-default" onclick="limparSessionStorage()">
														<button class="btn btn-info" type="button" onclick="history.back()">
															Voltar
														</button>
														<a href="<?= WWW ?>html/matPat/listar_produto.php" style="color: white; text-decoration:none;"> <button class="btn btn-success" type="button">Listar Produto</button></a>
													</div>
												</div>
											</div>
										</fieldset>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- end: page -->
			</section>
		</div>

	</section>

	<script src="<?= WWW ?>assets/vendor/jquery/jquery.js"></script>
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

	<!-- MSG Script -->
	<script src="<?= WWW ?>html/geral/msg.js"></script>

	<!-- Input content restoration -->
	<script type="text/javascript" defer>
		//Xablau
		function addSessionStorage(el) {
			sessionStorage.setItem(el.id, el.value);
		}

		function atualizarSelect(id) {
			return sessionStorage.getItem(id) || 'blank';
		}

		function atualizarInput(id) {
			document.getElementById(id).value = sessionStorage.getItem(id) || '';
		}

		function limparSessionStorage() {
			sessionStorage.clear();
		}
		document.addEventListener("DOMContentLoaded", () => {
			atualizarInput("produto");
			atualizarSelect("id_categoria");
			atualizarSelect("id_unidade");
			atualizarInput("codigo");
			atualizarInput("valor-form");
		})
	</script>

	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/matPat.html" width="200" height="60" style="border:none;"></iframe>
	</div>
</body>

</html>