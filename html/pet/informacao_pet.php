<?php

	ini_set('display_errors',1);
	ini_set('display_startup_erros',1);
	error_reporting(E_ALL);

	session_start();
	if (!isset($_SESSION['usuario'])) {
		header("Location: ../index.php");
		exit;
	}

	// Caminho seguro para config.php
	$max_niveis = 10;
	$config_path = "config.php";
	$tentativas = 0;

	while (!file_exists($config_path) && $tentativas < $max_niveis) {
		$config_path = "../" . $config_path;
		$tentativas++;
	}

	$config_realpath = realpath($config_path);

	if ($config_realpath && file_exists($config_realpath)) {
		require_once($config_realpath);
	} else {
		die("Arquivo de configuração não encontrado ou acesso inválido.");
	}

	$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	if (!$conexao) {
		die("Erro na conexão com o banco de dados.");
	}

	$id_pessoa = $_SESSION['id_pessoa'];

	// Consulta segura usando prepared statements
	$stmt = mysqli_prepare($conexao, "SELECT * FROM funcionario WHERE id_pessoa = ?");
	mysqli_stmt_bind_param($stmt, "i", $id_pessoa);
	mysqli_stmt_execute($stmt);
	$resultado = mysqli_stmt_get_result($stmt);

	if ($resultado && mysqli_num_rows($resultado) > 0) {
		$id_cargo_row = mysqli_fetch_assoc($resultado);
		$id_cargo = $id_cargo_row['id_cargo'];

		$query = "SELECT * FROM permissao p 
		          JOIN acao a ON(p.id_acao=a.id_acao) 
		          JOIN recurso r ON(p.id_recurso=r.id_recurso) 
		          WHERE id_cargo = ? AND a.descricao = ? AND r.descricao = ?";
		
		$stmt = mysqli_prepare($conexao, $query);
		$acao_desc = 'LER, GRAVAR E EXECUTAR';
		$recurso_desc = 'Informações Pet';
		mysqli_stmt_bind_param($stmt, "iss", $id_cargo, $acao_desc, $recurso_desc);
		mysqli_stmt_execute($stmt);
		$resultado = mysqli_stmt_get_result($stmt);

		if ($resultado && mysqli_num_rows($resultado) > 0) {
			$permissao = mysqli_fetch_assoc($resultado);
			if ($permissao['id_acao'] < 5) {
				$msg = "Você não tem as permissões necessárias para essa página.";
				header("Location: ../home.php?msg_c=" . urlencode($msg));
				exit;
			}
			$permissao = $permissao['id_acao'];
		} else {
			$permissao = 1;
			$msg = "Você não tem as permissões necessárias para essa página.";
			header("Location: ../home.php?msg_c=" . urlencode($msg));
			exit;
		}
	} else {
		$permissao = 1;
		$msg = "Você não tem as permissões necessárias para essa página.";
		header("Location: ../../home.php?msg_c=" . urlencode($msg));
		exit;
	}

	// Adiciona a Função display_campo($nome_campo, $tipo_campo)
	require_once "../personalizacao_display.php";
	require_once ROOT . "/controle/pet/PetControle.php";

?>



<!doctype html>
<html class="fixed">
<head>

	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Informações Pets</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo",'file');?>" type="image/x-icon" id="logo-icon">

	<!-- Specific Page Vendor CSS -->
	<link rel="stylesheet" href="../../assets/vendor/select2/select2.css" />
	<link rel="stylesheet" href="../../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

	<!-- Theme CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css" />

	<!-- Head Libs -->
	<script src="../../assets/vendor/modernizr/modernizr.js"></script>
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
		
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
	<!-- jquery functions -->
	<script>
		$(function() {
		var pet =<?php
			$response = new PetControle;
			$response->listarTodos();
			echo $_SESSION['pets'];?>;
		$.each(pet, function(i, item) {
			$("#tabela")
				.append($("<tr onclick=irPg('"+item.id+"') id='"+item.id+"' class='tabble-row'>")
					.append($("<td>")
						.text(item.nome))
					.append($("<td>")
						.text(item.raca))
					.append($("<td>")
						.text(item.cor))
					.append($("<td/>")
					.html('<i class="glyphicon glyphicon-pencil"></i>')));
			});
		});		

		function irPg(idPet){
			localStorage.setItem('id_pet',idPet);
			window.location.href = "./profile_pet.php?id_pet="+idPet;
		}
		
		$(function () {
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
					<h2>Informações</h2>

					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li><a href="../index.php"> <i class="fa fa-home"></i>
							</a></li>
							<li><span>Informações Pets</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->

				</header>

				<!-- start: page -->
				<section class="panel">
					<header class="panel-heading">
						<div class="panel-actions">
							<a href="#" class="fa fa-caret-down"></a>
						</div>

						<h2 class="panel-title">Pets</h2>
					</header>
					<div class="panel-body">
						<table class="table table-bordered table-striped mb-none"
							id="datatable-default">
							<thead>
								<tr>
									<th>Nome</th>
									<th>Raça</th>
									<th>Cor</th>
									<th>Ação</th>
								</tr>
							</thead>
							<tbody id="tabela">

							</tbody>
						</table>
					</div>
					<br>
				</section>
				<!-- end: page -->

				<!-- Vendor -->
		<script src="../../assets/vendor/select2/select2.js"></script>
		<script src="../../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="../../assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="../../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
		
		<!-- Theme Base, Components and Settings -->
		<script src="../../assets/javascripts/theme.js"></script>
		
		<!-- Theme Custom -->
		<script src="../../assets/javascripts/theme.custom.js"></script>
		
		<!-- Theme Initialization Files -->
		<script src="../../assets/javascripts/theme.init.js"></script>


		<!-- Examples -->
		<script src="../../assets/javascripts/tables/examples.datatables.default.js"></script>
		<script src="../../assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
		<script src="../../assets/javascripts/tables/examples.datatables.tabletools.js"></script>
		
		<div align="right">
	  		<iframe src="https://www.wegia.org/software/footer/pet.html" width="200" height="60" style="border:none;"></iframe>
  		</div>
	</body>
</html>

