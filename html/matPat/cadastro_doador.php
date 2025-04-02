<?php
session_start();
if (!isset($_SESSION['usuario'])) {
	header("Location: ../index.php");
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
	$resultado = mysqli_query($conexao, "SELECT * FROM permissao WHERE id_cargo=$id_cargo and id_recurso=23");
	if (!is_bool($resultado) and mysqli_num_rows($resultado)) {
		$permissao = mysqli_fetch_array($resultado);
		if ($permissao['id_acao'] < 3) {
			$msg = "Você não tem as permissões necessárias para essa página.";
			header("Location: ". WWW ."home.php?msg_c=$msg");
		}
		$permissao = $permissao['id_acao'];
	} else {
		$permissao = 1;
		$msg = "Você não tem as permissões necessárias para essa página.";
		header("Location: ". WWW ."home.php?msg_c=$msg");
	}
} else {
	$permissao = 1;
	$msg = "Você não tem as permissões necessárias para essa página.";
	header("Location: ". WWW ."home.php?msg_c=$msg");
}
// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";
?>

<!doctype html>
<html class="fixed">

<head>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Cadastro de Doador</title>

	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">

	<!-- Theme CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="<?= WWW ?>assets/vendor/modernizr/modernizr.js"></script>

	<!-- Javascript functions -->

	<script src="<?= WWW ?>assets/vendor/jquery/jquery.min.js"></script>

	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

	<!-- Functions -->
	<script src="<?= WWW ?>Functions/mascara.js"></script>
	<script src="<?= WWW ?>Functions/onlyNumbers.js"></script>
	<script src="<?= WWW ?>Functions/onlyChars.js"></script>
	<script src="<?= WWW ?>Functions/testaCPF.js"></script>
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

		function FormataCnpj(campo, teclapres) {
			var tecla = teclapres.keyCode;
			var vr = new String(campo.value);
			vr = vr.replace(".", "");
			vr = vr.replace("/", "");
			vr = vr.replace("-", "");
			tam = vr.length + 1;
			if (tecla != 14) {
				if (tam == 3)
					campo.value = vr.substr(0, 2) + '.';
				if (tam == 6)
					campo.value = vr.substr(0, 2) + '.' + vr.substr(2, 5) + '.';
				if (tam == 10)
					campo.value = vr.substr(0, 2) + '.' + vr.substr(2, 3) + '.' + vr.substr(6, 3) + '/';
				if (tam == 15)
					campo.value = vr.substr(0, 2) + '.' + vr.substr(2, 3) + '.' + vr.substr(6, 3) + '/' + vr.substr(9, 4) + '-' + vr.substr(13, 2);
			}
		}

		function validarCNPJ(cnpj) {

			cnpj = cnpj.replace(/[^\d]+/g, '');

			if (cnpj == '') return false;
			if (cnpj.length != 14)
				return false;
			// Elimina CNPJs invalidos conhecidos
			if (cnpj == "00000000000000" ||
				cnpj == "11111111111111" ||
				cnpj == "22222222222222" ||
				cnpj == "33333333333333" ||
				cnpj == "44444444444444" ||
				cnpj == "55555555555555" ||
				cnpj == "66666666666666" ||
				cnpj == "77777777777777" ||
				cnpj == "88888888888888" ||
				cnpj == "99999999999999")
				return false;
			// Valida DVs
			tamanho = cnpj.length - 2
			numeros = cnpj.substring(0, tamanho);
			digitos = cnpj.substring(tamanho);
			soma = 0;
			pos = tamanho - 7;
			for (i = tamanho; i >= 1; i--) {
				soma += numeros.charAt(tamanho - i) * pos--;
				if (pos < 2)
					pos = 9;
			}
			resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
			if (resultado != digitos.charAt(0))
				return false;
			tamanho = tamanho + 1;
			numeros = cnpj.substring(0, tamanho);
			soma = 0;
			pos = tamanho - 7;
			for (i = tamanho; i >= 1; i--) {
				soma += numeros.charAt(tamanho - i) * pos--;
				if (pos < 2)
					pos = 9;
			}
			resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
			if (resultado != digitos.charAt(1))
				return false;
			return true;
		}

		function exibirCNPJ(cnpj) {
			if (!validarCNPJ(cnpj)) {
				$('#cnpjInvalido').show();
				document.getElementById("enviar").disabled = true;
			} else {
				$('#cnpjInvalido').hide();
				document.getElementById("enviar").disabled = false;
			}
		}
	</script>
	<script type="text/javascript">
		function validar() {
			var cnpj = document.getElementById("cnpj");
			var cpf = document.getElementById("NCPF");
			if (cnpj.value.length == 0 && cpf.value.length == 0) {
				alert("Preencha o campo CNPJ ou o campo CPF");
				return false;
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
							<li><span>Cadastro</span></li>
							<li><span>Doador</span></li>
						</ol>

						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->
				<div class="row">
					<div class="col-md-4 col-lg-2" style=" visibility: hidden;"></div>
					<div class="col-md-8 col-lg-8">
						<div class="tabs">
							<ul class="nav nav-tabs tabs-primary">
								<li class="active">
									<a href="#overview" data-toggle="tab">Cadastro de Doador</a>
								</li>
							</ul>
							<div class="tab-content">
								<div id="overview" class="tab-pane active">
									<form class="doador" method="post" action="<?= WWW ?>controle/control.php" onsubmit="return validar()" autocomplete="off">
										<input type="hidden" name="nomeClasse" value="OrigemControle">
										<input type="hidden" name="metodo" value="incluir">
										<fieldset>
											<h4 class="mb-xlg">Doador</h4>
											<div class="form-group">
												<label class="col-md-3 control-label" for="profileFirstName">Nome</label>
												<div class="col-md-6">
													<input type="text" class="form-control" name="nome" id="nome" required>
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label" for="profileCompany">Número do CNPJ</label>
												<div class="col-md-6">
													<input type="text" name="cnpj" id="cnpj" onkeyup="FormataCnpj(this,event)" onblur="validarCNPJ(this.value)" maxlength="18" class="form-control input-md" ng-model="cadastro.cnpj" placeholder="Ex: 77.777.777/7777-77" onkeypress="return Onlynumbers(event)">
												</div>
											</div>

											<div class="form-group">
												<label class="col-md-3 control-label" for="profileCompany"></label>
												<div class="col-md-6">
													<p id="cnpjInvalido" style="display: none; color: #b30000">CNPJ INVÁLIDO!</p>
												</div>
											</div>

											<div class="form-group">
												<label class="col-md-3 control-label" for="profileCompany">Número do CPF</label>
												<div class="col-md-6">
													<input type="text" class="form-control" id="NCPF" name="cpf" placeholder="Ex: 222.222.222-22" maxlength="14" onblur="validarCPF(this.value)" onkeypress="return Onlynumbers(event)" onkeyup="mascara('###.###.###-##',this,event)">
												</div>
											</div>

											<div class="form-group">
												<label class="col-md-3 control-label" for="profileCompany"></label>
												<div class="col-md-6">
													<p id="cpfInvalido" style="display: none; color: #b30000">CPF INVÁLIDO!</p>
												</div>
											</div>

											<div class="form-group">
												<label class="col-md-3 control-label" for="profileCompany">Telefone</label>
												<div class="col-md-6">
													<input type="text" class="form-control" minlength="12" name="telefone" id="telefone" id="profileCompany" placeholder="Ex: (22)99999-9999" onkeypress="return Onlynumbers(event)" onkeyup="mascara('(##)#####-####',this,event)" required>
												</div>
											</div>
											<input type="hidden" name="nomeClasse" value="OrigemControle">
											<input type="hidden" name="metodo" value="incluir">

											<div class="row">
												<div class="col-md-9 col-md-offset-3">
													<button id="enviar" class="btn btn-primary" type="submit">Enviar</button>
													<input type="reset" class="btn btn-default">
													<a href="cadastro_entrada.php" color: white; text-decoration: none;>
														<button type="button" class="btn btn-info">Voltar</button>
													</a>
													<a href="listar_origem.php" style="color: white; text-decoration:none;"><button class="btn btn-success" type="button">Listar doadores</button></a>
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

	<!-- Vendor -->

	<script src="<?= WWW ?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="<?= WWW ?>assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="<?= WWW ?>assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="<?= WWW ?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

	<script type="text/javascript">
	</script>
	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/matPat.html" width="200" height="60" style="border:none;"></iframe>
	</div>

</body>

</html>