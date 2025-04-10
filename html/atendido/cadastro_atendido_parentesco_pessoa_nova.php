<?php
require_once "../personalizacao_display.php";
require_once "../../classes/Atendido.php";
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


if (!isset($_SESSION['usuario'])) {
	header("Location: " . WWW . "index.php");
}

$pdo = Conexao::connect();

$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$id_pessoa = $_SESSION['id_pessoa'];
$stmt = mysqli_prepare($conexao, "SELECT * FROM funcionario WHERE id_pessoa=?");
mysqli_stmt_bind_param($stmt, 'i', $id_pessoa);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
if (!is_null($resultado)) {
	$id_cargo = mysqli_fetch_array($resultado);
	if (!is_null($id_cargo)) {
		$id_cargo = $id_cargo['id_cargo'];
	}
	$stmt = mysqli_prepare($conexao, "SELECT * FROM permissao WHERE id_cargo=? and id_recurso=12");
	mysqli_stmt_bind_param($stmt, 'i', $id_cargo);
	mysqli_stmt_execute($stmt);
	$resultado = mysqli_stmt_get_result($stmt);
	if (!is_bool($resultado) and mysqli_num_rows($resultado)) {
		$permissao = mysqli_fetch_array($resultado);
		if ($permissao['id_acao'] < 7) {
			$msg = "Você não tem as permissões necessárias para essa página.";
			header("Location: " . WWW . "html/home.php?msg_c=$msg");
		}
		$permissao = $permissao['id_acao'];
	} else {
		$permissao = 1;
		$msg = "Você não tem as permissões necessárias para essa página.";
		header("Location: " . WWW . "html/home.php?msg_c=$msg");
	}
} else {
	$permissao = 1;
	$msg = "Você não tem as permissões necessárias para essa página.";
	header("Location: " . WWW . "html/home.php?msg_c=$msg");
}

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$intTipo = $mysqli->query("SELECT * FROM atendido_tipo");
$intStatus = $mysqli->query("SELECT * FROM atendido_status");

$cpf = $_GET['cpf'];

$dataNascimentoMaxima = Atendido::getDataNascimentoMaxima();
$dataNascimentoMinima = Atendido::getDataNascimentoMinima();

$cpfDigitado = $_SESSION['cpf_digitado'];
$parentescoPrevio = $_SESSION['parentesco_previo'];

?>
<!doctype html>
<html class="fixed">

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
	<script src="../../Functions/onlyNumbers.js"></script>
	<script src="../../Functions/onlyChars.js"></script>
	<script src="../../Functions/enviar_dados.js"></script>
	<script src="../../Functions/mascara.js"></script>
	<script src="../../Functions/lista.js"></script>
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

		function desabilitar_cpf() {

			if ($("#nao_cpf").prop("checked")) {
				document.getElementById("cpf").readOnly = true;
				document.getElementById("enviar").disabled = false;
				document.getElementById("imgCpf").style.display = "none";
			} else {
				document.getElementById("cpf").readOnly = false;
				document.getElementById("enviar").disabled = true;
				document.getElementById("imgCpf").style.display = "block";
			}
		}

		function desabilitar_rg() {

			if ($("#nao_rg").prop("checked")) {
				document.getElementById("rg").readOnly = true;
				document.getElementById("enviar").disabled = false;
				document.getElementById("imgRg").style.display = "none";
			} else {
				document.getElementById("rg").readOnly = false;
				document.getElementById("enviar").disabled = true;
				document.getElementById("imgRg").style.display = "block";
			}
		}



		$(function() {
			$("#header").load("../header.php");
			$(".menuu").load("../menu.php");
		});

		// $(document).ready(function(){
		// 	$('#form-cadastro').on("submit", function(event){
		// 		event.preventDefault();

		// 		var dados = $("#form-cadastro").serialize();
		// 		alert(dados);
		// 	}) 
		// });
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

				<!-- start: page -->
				<!-- <div class="row">
					<div class="col-md-4 col-lg-3">
						<section class="panel">
							<div class="panel-body">
								<div class="thumb-info mb-md">
									<?php
									if ($_SERVER['REQUEST_METHOD'] == 'POST') {
										if (isset($_FILES['imgperfil'])) {
											$image = file_get_contents($_FILES['imgperfil']['tmp_name']);
											$_SESSION['imagem'] = $image;
											echo '<img src="data:image/gif;base64,' . base64_encode($image) . '" class="rounded img-responsive" alt="John Doe">';
										}
									} else {
									?>
											<img src="../../img/semfoto.png" class="rounded img-responsive" alt="John Doe">
									<?php
									}
									?>
									
								</div>
								<div class="widget-toggle-expand mb-md">
									<div class="widget-header">
										<div class="widget-content-expanded">
											<ul class="simple-todo-list">
											</ul>
										</div>
									</div>
								</div>
								<h6 class="text-muted"></h6>
							</div>
						</section>
					</div> -->

				<div class="col-md-8 col-lg-12">
					<div class="tabs">
						<ul class="nav nav-tabs tabs-primary">
							<li class="active">
								<a href="#overview" data-toggle="tab">Cadastro de Atendido</a>

							</li>

						</ul>
						<div class="tab-content">
							<div id="overview" class="tab-pane active">
								<form action='familiar_cadastrar_pessoa_nova.php' method='post' id='funcionarioDepForm'>
									<div class="modal-body" style="padding: 15px 40px">
										<div class="form-group" style="display: grid;">
											<h4 class="mb-xlg">Informações Pessoais</h4>
											<h5 class="obrig">Campos Obrigatórios(*)</h5>
											<div class="form-group">
												<label class="col-md-3 control-label" for="profileFirstName">Nome<sup class="obrig">*</sup></label>
												<div class="col-md-8">
												<input type="text" class="form-control" name="nome" id="profileFirstName" id="nome" onkeypress="return Onlychars(event)" required>
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Sobrenome<sup class="obrig">*</sup></label>
												<div class="col-md-8">
												<input type="text" class="form-control" name="sobrenome" id="sobrenome" onkeypress="return Onlychars(event)" required>
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label" for="profileLastName">Sexo<sup class="obrig">*</sup></label>
												<div class="col-md-8">
												<label><input type="radio" name="sexo" id="radio" id="M" value="m" style="margin-top: 10px; margin-left: 15px;" onclick="return exibir_reservista()" required><i class="fa fa-male" style="font-size: 20px;"></i></label>
												<label><input type="radio" name="sexo" id="radio" id="F" value="f" style="margin-top: 10px; margin-left: 15px;" onclick="return esconder_reservista()"><i class="fa fa-female" style="font-size: 20px;"></i> </label>
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label" for="telefone">Telefone</label>
												<div class="col-md-8">
												<input type="text" class="form-control" maxlength="14" minlength="14" name="telefone" id="telefone" placeholder="Ex: (22)99999-9999" onkeypress="return Onlynumbers(event)" onkeyup="mascara('(##)#####-####',this,event)">
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label" for="profileCompany">Nascimento<sup class="obrig">*</sup></label>
												<div class="col-md-8">
												<input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="nascimento" id="nascimento" max="<?php echo date('Y-m-d'); ?>" required>
												</div>
											</div>
											<hr class="dotted short">
											<h4 class="mb-xlg doch4">Documentação</h4>
											<div class="form-group">
												<label class="col-md-3 control-label" for="cpf">Número do CPF<sup class="obrig">*</sup></label>
												<div class="col-md-6">
												<input type="text" class="form-control" id="cpf" name="cpf" placeholder="Ex: 222.222.222-22" maxlength="14" onblur="validarCPF(this.value)" onkeypress="return Onlynumbers(event)" onkeyup="mascara('###.###.###-##',this,event)" value="<?php echo $cpfDigitado; ?>" readonly>
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label" for="profileCompany"></label>
												<div class="col-md-6">
												<p id="cpfFamiliarInvalido" style="display: none; color: #b30000">CPF INVÁLIDO!</p>
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label" for="parentesco">Parentesco<sup class="obrig">*</sup></label>
												<div class="col-md-6" style="display: flex;">
													<select name="id_parentesco" id="parentesco">
														<option selected disabled>Selecionar...</option>
														<?php
														foreach ($pdo->query("SELECT * FROM atendido_parentesco ORDER BY parentesco ASC;")->fetchAll(PDO::FETCH_ASSOC) as $item) {
															if($item["idatendido_parentesco"]  == $parentescoPrevio) {
																echo("<option value='" . $item["idatendido_parentesco"] . "' selected>" . htmlspecialchars($item["parentesco"]) . "</option>");
															}
															else {
																echo("<option value='" . $item["idatendido_parentesco"] . "' >" . htmlspecialchars($item["parentesco"]) . "</option>");
															}
														}
														?>
													</select>
													<a onclick="adicionarParentesco()" style="margin: 0 20px;"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label" for="profileCompany">Número do RG</label>
												<div class="col-md-6">
												<input type="text" class="form-control" name="rg" id="rg" onkeypress="return Onlynumbers(event)" placeholder="Ex: 22.222.222-2" onkeyup="mascara('##.###.###-#',this,event)">
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label" for="profileCompany">Órgão Emissor</label>
												<div class="col-md-6">
												<input type="text" class="form-control" name="orgao_emissor" id="profileCompany" id="orgao_emissor" onkeypress="return Onlychars(event)">
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label" for="profileCompany">Data de expedição</label>
												<div class="col-md-6">
												<input type="date" class="form-control" maxlength="10" placeholder="dd/mm/aaaa" id="profileCompany" name="data_expedicao" id="data_expedicaoD" max="<?php echo date('Y-m-d'); ?>">
												</div>
											</div>
											<input type="hidden" name="idatendido" value="<?= $_GET['idatendido']; ?>" readonly>
											<div class="modal-footer">
												<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
												<input type="submit" id="cadastrarFamiliar" value="Enviar" class="btn btn-primary">
											</div>
										</div>
									</div>
								</form>

							<!-- <div class="panel-footer">
								<div class="row">
									<div class="col-md-9 col-md-offset-3">
										<input type="hidden" name="nomeClasse" value="AtendidoControle">
										<input type="hidden" name="cpf" value="<?php echo htmlspecialchars($cpf) ?>">
										<input type="hidden" name="metodo" value="incluir">
										<input id="enviar" type="submit" class="btn btn-primary" value="Enviar" onclick="validarInterno()">
									</div>
								</div>
							</div> -->
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
		// Exibe a imagem selecionada no input file:
		function readURL(input) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();

				reader.onload = function(e) {
					$('#img-selection')
						.attr('src', e.target.result);
				};

				reader.readAsDataURL(input.files[0]);
			}
		}

		$('#form-cadastro').submit(function() {
			let imgForm = document.getElementById("imgform");
			document.getElementById("form-cadastro").append(imgForm);
			return true;
		});

		function funcao1() {
			var send = $("#enviar");
			var cpfs = [{
				"cpf": "admin",
				"id": "1"
			}, {
				"cpf": "12487216166",
				"id": "2"
			}];
			var cpf_atendido = $("#cpf").val();
			var cpf_atendido_correto = cpf_atendido.replace(".", "");
			var cpf_atendido_correto1 = cpf_atendido_correto.replace(".", "");
			var cpf_atendido_correto2 = cpf_atendido_correto1.replace(".", "");
			var cpf_atendido_correto3 = cpf_atendido_correto2.replace("-", "");
			var apoio = 0;
			var cpfs1 = [{
				"cpf": "06512358716"
			}, {
				"cpf": ""
			}, {
				"cpf": "01027049702"
			}, {
				"cpf": "18136521719"
			}, {
				"cpf": "57703212539"
			}, {
				"cpf": "48913397480"
			}, {
				"cpf": "19861411364"
			}, {
				"cpf": "26377548508"
			}, {
				"cpf": "Luiza1ni"
			}, {
				"cpf": "Luiza2ni"
			}, {
				"cpf": "63422141154"
			}, {
				"cpf": "21130377008"
			}, {
				"cpf": "luiza3ni"
			}, {
				"cpf": "jiwdfhni"
			}, {
				"cpf": "Joaoni"
			}, {
				"cpf": "luiza4ni"
			}, {
				"cpf": "luiza5ni"
			}, {
				"cpf": "luiza6ni"
			}, {
				"cpf": "teste1ni"
			}, {
				"cpf": "luiza7ni"
			}, {
				"cpf": "luiza8ni"
			}, {
				"cpf": "luiza9ni"
			}];
			$.each(cpfs, function(i, item) {
				if (item.cpf == cpf_atendido_correto3) {
					alert("Cadastro não realizado! O CPF informado já está cadastrado no sistema");
					apoio = 1;
					send.attr('disabled', 'disabled');
				}
			});
			$.each(cpfs1, function(i, item) {
				if (item.cpf == cpf_atendido_correto3) {
					alert("Cadastro não realizado! O CPF informado já está cadastrado no sistema");
					apoio = 1;
					$("#formulario").submit();
				}
			});
			if (apoio == 0) {
				alert("Cadastrado com sucesso!");
			}
		}

		function validarInterno() {
			var btn = $("#enviar");
			var cpf_cadastrado = ([{
				"cpf": "admin",
				"id": "1"
			}]);
			var cpf = (($("#cpf").val()).replaceAll(".", "")).replaceAll("-", "");
			console.log(this);
			$.each(cpf_cadastrado, function(i, item) {
				if (item.cpf == cpf) {
					alert("Cadastro não realizado! O CPF informado já está cadastrado no sistema");
					btn.attr('disabled', 'disabled');
					return false;
				}
			})
			if ($("#telefone") = null) {
				$("#telefone") = "";
			};

		}

		function gerarTipo() {
			url = '../../dao/exibir_tipo_atendido.php';
			$.ajax({
				data: '',
				type: "POST",
				url: url,
				async: true,
				success: function(response) {
					var descricao = response;
					$('#intTipo').empty();
					$('#intTipo').append('<option selected disabled>Selecionar</option>');
					$.each(descricao, function(i, item) {
						$('#intTipo').append('<option value="' + item.idatendido_tipo + '">' + item.descricao + '</option>');
					});
				},
				dataType: 'json'
			});
		}

		function adicionar_tipo() {
			url = '../../dao/adicionar_tipo_atendido.php';
			var tipo = window.prompt("Cadastre um Novo Tipo:");
			if (!tipo) {
				return
			}
			tipo = tipo.trim();
			if (tipo == '') {
				return
			}

			data = 'tipo=' + tipo;

			console.log(data);
			$.ajax({
				type: "POST",
				url: url,
				data: data,
				success: function(response) {
					gerarTipo();
				},
				dataType: 'text'
			})
		}

		function gerarStatus() {
			url = '../../dao/exibir_status_atendido.php';
			$.ajax({
				data: '',
				type: "POST",
				url: url,
				async: true,
				success: function(response) {
					var status = response;
					$('#intStatus').empty();
					$('#intStatus').append('<option selected disabled>Selecionar</option>');
					$.each(status, function(i, item) {
						$('#intStatus').append('<option value="' + item.idatendido_status + '">' + item.status + '</option>');
					});
				},
				dataType: 'json'
			});
		}

		function adicionar_status() {
			url = '../../dao/adicionar_status_atendido.php';
			var status = window.prompt("Cadastre um Novo Status:");
			if (!status) {
				return
			}
			status = status.trim();
			if (status == '') {
				return
			}

			data = 'status=' + status;

			console.log(data);
			$.ajax({
				type: "POST",
				url: url,
				data: data,
				success: function(response) {
					gerarStatus();
				},
				dataType: 'text'
			})
		}
	</script>
</body>

</html>