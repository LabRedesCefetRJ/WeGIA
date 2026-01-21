<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE)
	session_start();

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

if (!isset($_SESSION['usuario'])) {
	header("Location: " . WWW . "index.php");
	exit();
} else {
	session_regenerate_id();
}

$id_pessoa = filter_var($_SESSION['id_pessoa'], FILTER_SANITIZE_NUMBER_INT);

if (!$id_pessoa || $id_pessoa < 1) {
	http_response_code(403);
	echo json_encode(['erro' => 'Falha na autenticação, o id da pessoa informado não é válido.']);
	exit();
}

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($id_pessoa, 91, 1);

try {
	require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';
	$sql = 'SELECT p.nome, p.id_pessoa as id_pessoa, c.cargo as nome_cargo from pessoa p join funcionario f on f.id_pessoa = p.id_pessoa join cargo c on f.id_cargo = c.id_cargo';

	$pdo = Conexao::connect();

	$query = $pdo->query($sql);
	$funcionarios = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
	require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
	Util::tratarException($e);
	exit();
}

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';

?>
<!doctype html>
<html class="fixed">

<head>

	<!-- Basic -->
	<meta charset="UTF-8">
	<title>Alterar senha</title>
	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
	<!-- Vendor CSS -->
	<link rel="stylesheet" href=<?= WWW . "assets/vendor/bootstrap/css/bootstrap.css" ?> />
	<link rel="stylesheet" href=<?= WWW . "assets/vendor/font-awesome/css/font-awesome.css" ?> />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
	<link rel="stylesheet" href=<?= WWW . "assets/vendor/magnific-popup/magnific-popup.css" ?> />
	<link rel="stylesheet" href=<?= WWW . "assets/vendor/bootstrap-datepicker/css/datepicker3.css" ?> />
	<link rel="stylesheet" href=<?= WWW . "css/error.css" ?> />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

	<!-- Specific Page Vendor CSS -->
	<link rel="stylesheet" href=<?= WWW . "assets/vendor/select2/select2.css" ?> />
	<link rel="stylesheet" href=<?= WWW . "assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" ?> />

	<!-- Theme CSS -->
	<link rel="stylesheet" href=<?= WWW . "assets/stylesheets/theme.css" ?> />

	<!-- Skin CSS -->
	<link rel="stylesheet" href=<?= WWW . "assets/stylesheets/skins/default.css" ?> />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href=<?= WWW . "assets/stylesheets/theme-custom.css" ?>>

	<!-- Head Libs -->
	<script src=<?= WWW . "assets/vendor/modernizr/modernizr.js" ?>></script>

	<!-- Vendor -->
	<script src=<?= WWW . "assets/vendor/jquery/jquery.min.js" ?>></script>
	<script src=<?= WWW . "assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js" ?>></script>
	<script src=<?= WWW . "assets/vendor/bootstrap/js/bootstrap.js" ?>></script>
	<script src=<?= WWW . "assets/vendor/nanoscroller/nanoscroller.js" ?>></script>
	<script src=<?= WWW . "assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js" ?>></script>
	<script src=<?= WWW . "assets/vendor/magnific-popup/magnific-popup.js" ?>></script>
	<script src=<?= WWW . "assets/vendor/jquery-placeholder/jquery.placeholder.js" ?>></script>

	<!-- Specific Page Vendor -->
	<script src=<?= WWW . "assets/vendor/jquery-autosize/jquery.autosize.js" ?>></script>

	<!-- Theme Base, Components and Settings -->
	<script src=<?= WWW . "assets/javascripts/theme.js" ?>></script>

	<!-- Theme Custom -->
	<script src=<?= WWW . "assets/javascripts/theme.custom.js" ?>></script>

	<!-- Theme Initialization Files -->
	<script src=<?= WWW . "assets/javascripts/theme.init.js" ?>></script>

	<!-- javascript functions -->
	<script src=<?= WWW . "Functions/onlyNumbers.js" ?>></script>
	<script src=<?= WWW . "Functions/onlyChars.js" ?>></script>
	<script src=<?= WWW . "Functions/mascara.js" ?>></script>

	<script type="text/javascript">
		$(function() {
			$("#header").load("<?php echo WWW; ?>html/header.php");
			$(".menuu").load("<?php echo WWW; ?>html/menu.php");
		});
	</script>

	<script>
		$(function() {
			const verificacao = '<?= isset($_GET['verificacao']) ? htmlspecialchars($_GET['verificacao']) : '0' ?>';

			switch (verificacao) {
				case '0':
					break;
				case '1':
					alert("Confirmação de senha não coincide com nova senha");
					break;
				case '2':
					alert("Senha antiga está errada");
					break;
				case '3':
					alert("Senha alterada com sucesso!");
					break;
				default:
					alert("O valor informado para a verificação não é válido.");
			}
		});
	</script>

</head>

<body>
	<section class="body">
		<!-- start: header -->
		<header id="header">
		</header>
		<!-- end: header -->

		<div class="inner-wrapper">
			<!-- start: sidebar -->
			<aside id="sidebar-left" class="sidebar-left menuu">
			</aside>

			<section role="main" class="content-body">
				<header class="page-header">
					<h2>Configurar senha funcionário</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="<?= WWW ?>html/home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Alterar senha</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->
				<div class="row">
					<div class="col-md-4 col-lg-3">
						<section class="panel"></section>
					</div>
					<div class="col-md-8 col-lg-8">
						<div class="tabs">
							<ul class="nav nav-tabs tabs-primary">
								<li class="active">
									<a href="#overview" data-toggle="tab">Alterar senha</a>
								</li>
							</ul>
							<div class="tab-content">
								<div id="overview" class="tab-pane active">
									<div>
										<h3 id="erro">Selecione um funcionário para modificar a senha</h3>
									</div>
									<form class="form-horizontal" method="post" id="password-form" action="<?php echo (WWW . 'controle/control.php'); ?>">
										<fieldset>
											<div class="form-group">
												<label class="col-md-3 control-label">Funcionário:
												</label>
												<div class="col-md-6">
													<select name="id_pessoa" id="id_pessoa" class="form-control mb-md">
														<option selected disabled>Selecionar</option>
														<?php
														foreach ($funcionarios as $funcionario) {
															echo "<option value=" . htmlspecialchars($funcionario['id_pessoa']) . ">" . htmlspecialchars($funcionario['nome']) . " - " . htmlspecialchars($funcionario['nome_cargo']) . "</option>";
														}
														?>
													</select>
													</label>

												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Nova senha:
												</label>
												<div class="col-md-6">
													<input type="password" id="nova_senha" name="nova_senha" class="form-control" required><br />
													</label>
													<div id="password-div"></div>
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Confirmar senha:
												</label>
												<div class="col-md-6">
													<input type="password" name="confirmar_senha" class="form-control" required><br />
													</label>

												</div>
											</div>
										</fieldset>
										<!-- CSRF -->
										<?= Csrf::inputField() ?>
										<input type="hidden" name="nomeClasse" value="FuncionarioControle">
										<input type="hidden" name="metodo" value="alterarSenha">
										<input type="hidden" name="redir" value="geral/configurar_senhas.php">
										<a href="editar_permissoes.php" class="btn btn-danger">Voltar</a>
										<input type="submit" name="alterar" value="Alterar" class="btn btn-primary">
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

	<!-- Vendor -->
	<script src=<?= WWW . "assets/vendor/select2/select2.js" ?>></script>
	<script src=<?= WWW . "assets/vendor/jquery-datatables/media/js/jquery.dataTables.js" ?>></script>
	<script src=<?= WWW . "assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js" ?>></script>
	<script src=<?= WWW . "assets/vendor/jquery-datatables-bs3/assets/js/datatables.js" ?>></script>

	<!-- Theme Base, Components and Settings -->
	<script src=<?= WWW . "assets/javascripts/theme.js" ?>></script>

	<!-- Theme Custom -->
	<script src=<?= WWW . "assets/javascripts/theme.custom.js" ?>></script>

	<!-- Theme Initialization Files -->
	<script src=<?= WWW . "assets/javascripts/theme.init.js"?>></script>

	<script src=<?= WWW . "Functions/password_policy.js"?>></script>

	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/conf.html" width="200" height="60" style="border:none;"></iframe>
	</div>

</body>

</html>