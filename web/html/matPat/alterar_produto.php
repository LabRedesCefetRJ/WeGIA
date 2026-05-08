<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if(session_status() === PHP_SESSION_NONE)
	session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: " . WWW . "html/index.php");
	exit();
}else{
	session_regenerate_id();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

permissao($_SESSION['id_pessoa'], 22, 3);

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";

include_once ROOT . '/dao/Conexao.php';
include_once ROOT . '/dao/CategoriaDAO.php';
include_once ROOT . '/dao/UnidadeDAO.php';
include_once ROOT . '/dao/ProdutoDAO.php';

if (!isset($_SESSION['unidade'])) {
	extract($_REQUEST);
	header('Location: ' . WWW . 'controle/control.php?metodo=listarTodos&nomeClasse=UnidadeControle&nextPage=../html/matPat/alterar_produto.php?id_produto=' . htmlspecialchars($id_produto));
}
if (!isset($_SESSION['categoria'])) {
	extract($_REQUEST);
	header('Location: ' . WWW . 'controle/control.php?metodo=listarTodos&nomeClasse=CategoriaControle&nextPage=../html/matPat/alterar_produto.php?id_produto=' . htmlspecialchars($id_produto));
}
if (!isset($_SESSION['produto'])) {
	extract($_REQUEST);
	header('Location: ' . WWW . 'controle/control.php?metodo=listarId&nomeClasse=ProdutoControle&nextPage=' . WWW . 'html/matPat/alterar_produto.php?id_produto=' . htmlspecialchars($id_produto) . '&id_produto=' . htmlspecialchars($id_produto));
}

if (isset($_SESSION['produto']) && isset($_SESSION['categoria']) && isset($_SESSION['unidade'])) {
	$produto = $_SESSION['produto'];
	$unidade = $_SESSION['unidade'];
	$categoria = $_SESSION['categoria'];
	$vars = $_SESSION;
	unset($_SESSION['produto']);
	unset($_SESSION['categoria']);
	unset($_SESSION['unidade']);
}
?>
<!doctype html>
<html class="fixed">

<head>

	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Alterar Produto</title>

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

	<script src="<?= WWW ?>assets/vendor/jquery/jquery.min.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="<?= WWW ?>assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="<?= WWW ?>assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="<?= WWW ?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
	<script type="text/javascript">
		function editar_produto() {

			$("#produto").prop('disabled', false);
			$("#id_categoria").prop('disabled', false);
			$("#id_unidade").prop('disabled', false);
			$("#codigo").prop('disabled', false);
			$("#valor").prop('disabled', false);

			$("#botaoEditarIP").html('Cancelar');
			$("#botaoSalvarIP").prop('disabled', false);
			$("#botaoEditarIP").removeAttr('onclick');
			$("#botaoEditarIP").attr('onclick', "return cancelar_produto()");

		}

		function cancelar_produto() {

			$("#produto").prop('disabled', true);
			$("#id_categoria").prop('disabled', true);
			$("#id_unidade").prop('disabled', true);
			$("#codigo").prop('disabled', true);
			$("#valor").prop('disabled', true);

			$("#botaoEditarIP").html('Editar');
			$("#botaoSalvarIP").prop('disabled', true);
			$("#botaoEditarIP").removeAttr('onclick');
			$("#botaoEditarIP").attr('onclick', "return editar_produto()");

		}
		$(function() {
			$("#header").load("<?= WWW ?>html/header.php");
			$(".menuu").load("<?= WWW ?>html/menu.php");

			var produtos = <?php echo $produto; ?>;
			var categoria = <?php echo $categoria; ?>;
			var unidade = <?php echo $unidade; ?>;

			$("#produto").prop('disabled', true);
			$("#id_categoria").prop('disabled', true);
			$("#id_unidade").prop('disabled', true);
			$("#codigo").prop('disabled', true);
			$("#valor").prop('disabled', true);
			$("#botaoEditarIP").html('Editar');
			$("#botaoSalvarIP").prop('disabled', true);

			$.each(produtos, function(i, item) {
				$("#nextPage")
					.val('<?= WWW ?>html/matPat/alterar_produto.php?id_produto=' + item.id_produto);
				$('#id_produto')
					.val(item.id_produto)
				$('#nome')
					.text(item.descricao)
				$('#Categoria')
					.text(item.descricao_categoria)
				$('#Unidade')
					.text(item.descricao_unidade)
				$('#Codigo')
					.text(item.codigo)
				$('#Valor')
					.text(item.preco)
				$('#produto')
					.val(item.descricao)
				$('#codigo')
					.val(item.codigo)
				$('#valor')
					.val(item.preco)
			})


			$.each(categoria, function(i, item) {
				if (produtos[0].id_categoria_produto == item.id_categoria_produto) {
					$('#id_categoria').append('<option value="' + item.id_categoria_produto + '" selected>' + item.descricao_categoria + '</option>');
				} else {
					$('#id_categoria').append('<option value="' + item.id_categoria_produto + '">' + item.descricao_categoria + '</option>');
				}
			})

			$.each(unidade, function(i, item) {
				if (produtos[0].id_unidade == item.id_unidade) {
					$('#id_unidade').append('<option value="' + item.id_unidade + '" selected>' + item.descricao_unidade + '</option>');
				} else {
					$('#id_unidade').append('<option value="' + item.id_unidade + '">' + item.descricao_unidade + '</option>');
				}
			})
		});
	</script>

</head>

<body>
	<div id="header"></div>
	<!-- end: header -->
	<div class="inner-wrapper">
		<!-- start: sidebar -->
		<aside id="sidebar-left" class="sidebar-left menuu"></aside>
		<!-- end: sidebar -->
		<section role="main" class="content-body">
			<header class="page-header">
				<h2>Alterar Produto</h2>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li>
							<a href="<?= WWW ?>html/home.php">
								<i class="fa fa-home"></i>
							</a>
						</li>
						<li><span>Páginas</span></li>
						<li><span>Alterar Produto</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>

			<!-- start: page -->
			<div class="row">
				<div class="col-md-4 col-lg-3" style="width: 230px;">
					<section class="panel">
						<div class="panel-body" style="display:none;">
							<div class="thumb-info mb-md"></div>
						</div>
					</section>
				</div>
				<div class="col-md-8 col-lg-6">
					<div class="tabs">
						<ul class="nav nav-tabs tabs-primary">
							<li class="active">
								<a href="#overview" data-toggle="tab">Visão Geral</a>
							</li>

							<li>
								<a href="#edit" data-toggle="tab">Editar Dados</a>
							</li>
						</ul>

						<div class="tab-content">
							<div id="overview" class="tab-pane active">
								<div>
									<section class="panel">
										<header class="panel-heading">
											<div class="panel-actions">
												<a href="#" class="fa fa-caret-down"></a>
											</div>
											<h2 class="panel-title">Visão Geral</h2>
										</header>

										<div class="panel-body" style="display: flex;">
											<ul class="nav nav-children" id="info" style="padding-right: 20px;">
												<li>Nome: </li>
												<li>Categoria: </li>
												<li>Unidade: </li>
												<li>Codigo: </li>
												<li>Valor: </li>
											</ul>
											<ul class="nav nav-children" id="info">
												<li id="nome"></li>
												<li id="Categoria"></li>
												<li id="Unidade"></li>
												<li id="Codigo"></li>
												<li id="Valor"></li>
											</ul>
										</div>
									</section>
								</div>
							</div>

							<div id="edit" class="tab-pane">
								<form id="formulario" action="<?= WWW ?>controle/control.php">
									<input type="hidden" name="nomeClasse" value="ProdutoControle">
									<input type="hidden" name="metodo" value="alterarProduto">
									<input type="hidden" name="id_produto" id="id_produto">
									<input type="hidden" name="nextPage" id="nextPage">
									<fieldset>
										<div class="form-group"><br>
											<label class="col-md-3 control-label">Nome do produto</label>
											<div class="col-md-8">
												<input type="text" class="form-control" name="descricao" id="produto">
											</div>
										</div>

										<div class="form-group">
											<label class="col-md-3 control-label" for="inputSuccess">Categoria</label>
											<a href="<?= WWW ?>html/matPat/adicionar_categoria.php">
												<i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i>
											</a>
											<div class="col-md-6">
												<select name="id_categoria" id="id_categoria" class="form-control input-lg mb-md">
												</select>
											</div>
										</div>

										<div class="form-group">
											<label class="col-md-3 control-label">Unidade</label>
											<a href="<?= WWW ?>html/matPat/adicionar_unidade.php"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
											<div class="col-md-6">
												<select name="id_unidade" id="id_unidade" class="form-control input-lg mb-md">
												</select>
											</div>
										</div>

										<div class="form-group">
											<label class="col-md-3 control-label" for="profileCompany">Código</label>
											<div class="col-md-8">
												<input type="text" name="codigo" class="form-control" minlength="11" id="codigo" id="profileCompany">
											</div>
										</div>

										<div class="form-group">
											<label class="col-md-3 control-label" for="profileCompany">Valor</label>
											<div class="col-md-8">
												<input type="text" name="preco" class="form-control" id="valor" id="profileCompany" maxlength="13" placeholder="Ex: 22.00" onkeypress="return Onlynumbers(event)">

											</div>
										</div>

										<div class="panel-footer">
											<div class="row">
												<div class="col-md-9 col-md-offset-3">
													<button type="button" class="btn btn-primary" id="botaoEditarIP" onclick="return editar_produto()">Editar</button>
													<input type="submit" class="btn btn-primary" disabled="true" value="Salvar" id="botaoSalvarIP">
													<input type="reset" class="btn btn-default">
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
		</section>
	</div>
	</section>
</body>

</html>