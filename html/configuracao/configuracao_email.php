<?php
	session_start();
	if(!isset($_SESSION['usuario'])){
		header ("Location: ../../index.php");
	}

	// Verifica Permissão do Usuário
	require_once '../permissao/permissao.php';
	permissao($_SESSION['id_pessoa'], 9);
	
	// Inclui display de Campos
	require_once "../personalizacao_display.php";

	// Adiciona o Sistema de Mensagem
	require_once "../geral/msg.php";

	require_once "../../dao/Conexao.php";
	require_once "../geral/servico_email.php";
	
	$pdo = Conexao::connect();
	$emailService = new EmailService($pdo);

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		try {
			// Validar dados usando o EmailService
			$erros = $emailService->validarConfiguracao($_POST);
			
			if (!empty($erros)) {
				$_SESSION['mensagem'] = 'Erros de validação: ' . implode(', ', $erros);
				$_SESSION['tipo'] = 'error';
			} else {
				// Processar e salvar dados usando o EmailService
				$config = $emailService->processarDadosFormulario($_POST);
				$emailService->salvarConfiguracoesBanco($config);
				
				$_SESSION['mensagem'] = 'Configurações de email salvas com sucesso!';
				$_SESSION['tipo'] = 'success';
				header("Location: configuracao_email.php");
				exit;
			}
		} catch (Exception $e) {
			$_SESSION['mensagem'] = 'Erro ao salvar configurações: ' . $e->getMessage();
			$_SESSION['tipo'] = 'error';
		}
	}

	// Obter configurações atuais usando o EmailService
	$smtpConfig = $emailService->obterConfiguracoesBanco();
	
	//valores padrão
	$smtp_host = $smtpConfig['smtp_host'] ?? '';
	$smtp_port = $smtpConfig['smtp_port'] ?? '587';
	$smtp_username = $smtpConfig['smtp_user'] ?? '';
	$smtp_password = $smtpConfig['smtp_password'] ?? '';
	$smtp_encryption = $smtpConfig['smtp_secure'] ?? 'tls';
	$smtp_from_email = $smtpConfig['smtp_from_email'] ?? '';
	$smtp_from_name = $smtpConfig['smtp_from_name'] ?? '';
	$smtp_enabled = ($smtpConfig && $smtpConfig['smtp_ativo'] == 1);
?>
<!doctype html>
<html class="fixed">
<head>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Configurações de Email</title>

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
	<link rel="icon" href="<?php display_campo("Logo",'file');?>" type="image/x-icon">
	
	<!-- Theme CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
	
	<!-- Skin CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
	
	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
	
	<!-- Head Libs -->
	<script src="../../assets/vendor/modernizr/modernizr.js"></script>

	<!-- Configuração CSS -->
	<link rel="stylesheet" href="../../css/configuracao.css" />
	
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
		$(function () {
	      $("#header").load("../header.php");
	      $(".menuu").load("../menu.php");
	    });	
    </script>

    <style>
		.btn{
			width: auto;
			min-width: 120px;
		}
		.form-group {
			margin-bottom: 20px;
		}
		.config-section {
			background: #f8f9fa;
			padding: 20px;
			border-radius: 5px;
			margin-bottom: 20px;
		}
		.test-email-section {
			background: #e3f2fd;
			padding: 15px;
			border-radius: 5px;
			border-left: 4px solid #2196f3;
		}
	</style>

</head>
<body>
	<section class="body">
		<div id="header"></div>
	        <!-- end: header -->
		<div class="inner-wrapper">
			<!-- start: sidebar -->
			<aside id="sidebar-left" class="sidebar-left menuu"></aside>
			<!-- end: sidebar -->
			<section role="main" class="content-body">
				<header class="page-header">
					<h2>Configurações de Email SMTP</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="../home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Configurações</span></li>
							<li><span>Email SMTP</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>
                <!--start: page-->
				
                <!-- Caso haja uma mensagem do sistema -->
				<?php displayMsg(); getMsgSession("mensagem","tipo");?>

				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								<div class="panel-actions">
									<a href="#" class="fa fa-caret-down"></a>
								</div>
								<h2 class="panel-title">Configurações SMTP</h2>
							</header>
							<div class="panel-body">
								<form method="POST" action="">
									<div class="config-section">
										<h4><i class="fa fa-cog"></i> Configurações Gerais</h4>
										
										<div class="form-group">
											<label class="control-label">
												<input type="checkbox" name="smtp_enabled" value="1" <?= $smtp_enabled ? 'checked' : '' ?>>
												Habilitar envio de emails via SMTP
											</label>
											<p class="help-block">Marque esta opção para ativar o envio de emails através do servidor SMTP configurado.</p>
										</div>
									</div>

									<div class="config-section">
										<h4><i class="fa fa-server"></i> Configurações do Servidor SMTP</h4>
										
										<div class="row">
											<div class="col-md-8">
												<div class="form-group">
													<label class="control-label">Servidor SMTP (Host)</label>
													<input type="text" class="form-control" name="smtp_host" value="<?= htmlspecialchars($smtp_host) ?>" placeholder="smtp.gmail.com">
													<p class="help-block">Endereço do servidor SMTP (ex: smtp.gmail.com, smtp.outlook.com)</p>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="control-label">Porta</label>
													<input type="number" class="form-control" name="smtp_port" value="<?= htmlspecialchars($smtp_port) ?>" placeholder="587">
													<p class="help-block">Porta do servidor (587 para TLS, 465 para SSL)</p>
												</div>
											</div>
										</div>

										<div class="form-group">
											<label class="control-label">Criptografia</label>
											<select class="form-control" name="smtp_encryption">
												<option value="tls" <?= $smtp_encryption === 'tls' ? 'selected' : '' ?>>TLS</option>
												<option value="ssl" <?= $smtp_encryption === 'ssl' ? 'selected' : '' ?>>SSL</option>
												<option value="" <?= $smtp_encryption === '' ? 'selected' : '' ?>>Nenhuma</option>
											</select>
											<p class="help-block">Tipo de criptografia utilizada pelo servidor</p>
										</div>
									</div>

									<div class="config-section">
										<h4><i class="fa fa-user"></i> Autenticação</h4>
										
										<div class="form-group">
											<label class="control-label">Usuário/Email</label>
											<input type="email" class="form-control" name="smtp_username" value="<?= htmlspecialchars($smtp_username) ?>" placeholder="seu-email@gmail.com">
											<p class="help-block">Email usado para autenticação no servidor SMTP</p>
										</div>

										<div class="form-group">
											<label class="control-label">Senha</label>
											<input type="password" class="form-control" name="smtp_password" value="<?= htmlspecialchars($smtp_password) ?>" placeholder="Senha ou senha de aplicsmtp_ativo">
											<p class="help-block">Senha do email ou senha de aplicativo(recomendado para Gmail)</p>
										</div>
									</div>

									<div class="config-section">
										<h4><i class="fa fa-envelope"></i> Configurações do Remetente</h4>
										
										<div class="form-group">
											<label class="control-label">Email do Remetente</label>
											<input type="email" class="form-control" name="smtp_from_email" value="<?= htmlspecialchars($smtp_from_email) ?>" placeholder="noreply@suaorganizacao.org">
											<p class="help-block">Email que aparecerá como remetente das mensagens</p>
										</div>

										<div class="form-group">
											<label class="control-label">Nome do Remetente</label>
											<input type="text" class="form-control" name="smtp_from_name" value="<?= htmlspecialchars($smtp_from_name) ?>" placeholder="Sua Organização">
											<p class="help-block">Nome que aparecerá como remetente das mensagens</p>
										</div>
									</div>

									<div class="form-group">
										<button type="submit" class="btn btn-primary">
											<i class="fa fa-save"></i> Salvar Configurações
										</button>
									</div>
								</form>
							</div>
						</section>

						<!-- Seção de teste de email -->
						<section class="panel">
							<header class="panel-heading">
								<div class="panel-actions">
									<a href="#" class="fa fa-caret-down"></a>
								</div>
								<h2 class="panel-title">Teste de Configuração</h2>
							</header>
							<div class="panel-body">
								<div class="test-email-section">
									<h4><i class="fa fa-paper-plane"></i> Enviar Email de Teste</h4>
									<p>Use esta função para testar se as configurações SMTP estão funcionando corretamente.</p>
									
									<form id="testEmailForm">
										<div class="row">
											<div class="col-md-8">
												<div class="form-group">
													<label class="control-label">Email de Destino</label>
													<input type="email" class="form-control" id="test_email" placeholder="email@exemplo.com" required>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="control-label">&nbsp;</label>
													<button type="submit" class="btn btn-info form-control" <?= !$smtp_enabled ? 'disabled' : '' ?>>
														<i class="fa fa-paper-plane"></i> Enviar Teste
													</button>
												</div>
											</div>
										</div>
									</form>
									
									<div id="testResult" style="display: none;"></div>
									
									<?php if (!$smtp_enabled): ?>
									<div class="alert alert-warning">
										<i class="fa fa-warning"></i> O envio de emails está desabilitado. Habilite o SMTP acima para testar.
									</div>
									<?php endif; ?>
								</div>
							</div>
						</section>
					</div>
				</div>

			</section>
		</div>
	</section>

	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/conf.html" width="200" height="60" style="border:none;"></iframe>
	</div>

</body>

<script>
// Teste de email
$('#testEmailForm').on('submit', function(e) {
	e.preventDefault();
	
	const email = $('#test_email').val();
	const button = $(this).find('button[type="submit"]');
	const result = $('#testResult');
	
	// Desabilitar botão e mostrar loading
	button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando...');
	result.hide();
	
	$.ajax({
		url: 'test_email.php',
		method: 'POST',
		data: { email: email },
		dataType: 'json',
		success: function(response) {
			if (response.success) {
				result.html('<div class="alert alert-success"><i class="fa fa-check"></i> ' + response.message + '</div>');
			} else {
				result.html('<div class="alert alert-danger"><i class="fa fa-times"></i> ' + response.message + '</div>');
			}
			result.show();
		},
		error: function() {
			result.html('<div class="alert alert-danger"><i class="fa fa-times"></i> Erro ao enviar email de teste.</div>');
			result.show();
		},
		complete: function() {
			button.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Enviar Teste');
		}
	});
});
</script>

<!-- Adiciona função de fechar mensagem e tirá-la da url -->
<script src="../geral/msg.js"></script>
</html>