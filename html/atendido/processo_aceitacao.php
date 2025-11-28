<?php
session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: ../index.php");
	exit();
}

require_once '../../dao/Conexao.php';
require_once '../../dao/ProcessoAceitacaoDAO.php';
require_once "../personalizacao_display.php";

$pdo = Conexao::connect();
$processoDAO = new ProcessoAceitacaoDAO($pdo);
$processosAtivos = $processoDAO->listarProcessosAtivos();

$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);
$error = $_SESSION['mensagem_erro'] ?? '';
unset($_SESSION['mensagem_erro']);
?>
<!DOCTYPE html>
<html>

<head>

	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Cadastro de Atendido</title>

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
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon">

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
	<script src="<?php echo WWW; ?>Functions/testaCPF.js"></script>

	<!-- jquery functions -->
	<script>
		function validarCPF(strCPF) {
			if (!testaCPF(strCPF)) {
				$('#cpfInvalido').show();
				document.getElementById("enviar").disabled = true;
			} else {
				$('#cpfInvalido').hide();
				document.getElementById("enviar").disabled = false;
			}
		}

		$(function() {
			$("#header").load("../header.php");
			$(".menuu").load("../menu.php");
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
								<a href="../home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Cadastro</span></li>
							<li><span>Atendido</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- inserir página nova aqui -->
				<section class="container mt-3">

					<h2>Processo de Aceitação</h2>

					<?php if ($msg): ?>
						<div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
					<?php endif; ?>

					<?php if ($error): ?>
						<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
					<?php endif; ?>

					<div class="card mb-4">
						<div class="card-body">
							<form method="POST" action="../../controle/control.php" enctype="multipart/form-data">
								<input type="hidden" name="nomeClasse" value="ProcessoAceitacaoControle" />
								<input type="hidden" name="metodo" value="incluir" />

								<div class="form-group">
									<label>Nome <span class="text-danger">*</span></label>
									<input type="text" name="nome" class="form-control" required />
								</div>
								<div class="form-group">
									<label>Sobrenome <span class="text-danger">*</span></label>
									<input type="text" name="sobrenome" class="form-control" required />
								</div>
								<div class="form-group">
									<label>CPF <span class="text-danger">*</span></label>
									<input type="text" name="cpf" maxlength="14" placeholder="000.000.000-00" onkeypress="return onlyNumbers(event);" class="form-control" required />
								</div>
								<button type="submit" class="btn btn-primary">Cadastrar Processo</button>
							</form>
						</div>
					</div>

					<div class="card">
						<div class="card-header bg-info text-white">Processos</div>
						<div class="card-body p-0">
							<?php if (empty($processosAtivos)): ?>
								<div class="alert alert-warning text-center m-3">Nenhum processo ativo encontrado.</div>
							<?php else: ?>
								<table class="table table-striped table-bordered mb-0">
									<thead class="thead-light">
										<tr>
											<th>Nome</th>
											<th>CPF</th>
											<th>Status</th>
											<th>Etapas</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($processosAtivos as $processo): ?>
											<tr>
												<td><?= htmlspecialchars($processo['nome'] . ' ' . $processo['sobrenome']) ?></td>
												<td><?= htmlspecialchars($processo['cpf']) ?></td>
												<td><?= htmlspecialchars($processo['status']) ?></td>
												<td>
													<a href="etapa_processo.php?id=<?= (int)$processo['id'] ?>" class="btn btn-sm btn-primary">
														<i class="fa fa-edit"></i>
													</a>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							<?php endif; ?>
						</div>
					</div>


		</div>

	</section>

	</section>
	</div>

	<aside id="sidebar-right" class="sidebar-right">
		<div class="nano">
			<div class="nano-content">
				<a href="#" class="mobile-close visible-xs">Collapse <i class="fa fa-chevron-right"></i></a>
			</div>
		</div>
	</aside>
	</section>
	<!-- Vendor -->

	<script src="../../assets/vendor/jquery/jquery.js"></script>
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
	<style type="text/css">
		.obrig {
			color: rgb(255, 0, 0);
		}
	</style>
	<script>
		function onlyNumbers(evt) {
			var charCode = (evt.which) ? evt.which : evt.keyCode;
			if (charCode > 31 && (charCode < 48 || charCode > 57)) {
				return false;
			}
			return true;
		}
	</script>
</body>

</html>