<?php

	ini_set('display_errors',1);
	ini_set('display_startup_erros',1);
	error_reporting(E_ALL);

	session_start();
	if(!isset($_SESSION['usuario'])){
		header ("Location: ../index.php");
		exit;
	}
    if(!isset($_SESSION['saudepet'])){
		header('Location: ../../controle/control.php?metodo=listarTodos&modulo=pet&nomeClasse=controleSaudePet&nextPage=../html/pet/informacao_saude_pet.php');
		exit;
	}
	$config_path = "config.php";
	if(file_exists($config_path)){
		require_once($config_path);
	}else{
		$max_depth = 10;
		$current_depth = 0;
		while($current_depth < $max_depth){
			$config_path = "../" . $config_path;
			if(file_exists($config_path)) {
				require_once($config_path);
				break;
			}
			$current_depth++;
		}
		if($current_depth >= $max_depth){
			die("Arquivo de configuração não encontrado.");
		}
	}
	
	require_once "../../dao/Conexao.php";
	$pdo = Conexao::connect();

	try {
		$id_pessoa = $_SESSION['id_pessoa'];
		
		$stmt = $pdo->prepare("SELECT id_cargo FROM funcionario WHERE id_pessoa = ?");
		$stmt->execute([$id_pessoa]);
		$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if ($resultado) {
			$id_cargo = $resultado['id_cargo'];
	
			$stmt = $pdo->prepare(
				"SELECT p.id_acao FROM permissao p 
				JOIN acao a ON p.id_acao = a.id_acao 
				JOIN recurso r ON p.id_recurso = r.id_recurso 
				WHERE p.id_cargo = ? 
				AND a.descricao = 'LER, GRAVAR E EXECUTAR' 
				AND r.descricao = 'Saúde Pet'"
			);
			$stmt->execute([$id_cargo]);
			$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if ($resultado && $resultado['id_acao'] >= 5) {
				$permissao = $resultado['id_acao'];
			} else {
				$msg = "Você não tem as permissões necessárias para essa página.";
				header("Location: ../home.php?msg_c=" . urlencode($msg));
				exit;
			}
		} else {
			$permissao = 1;
			$msg = "Você não tem as permissões necessárias para essa página.";
			header("Location: ../home.php?msg_c=" . urlencode($msg));
			exit;
		}
	} catch (PDOException $e) {
		error_log("Erro de banco de dados: " . $e->getMessage());
		header("Location: ../error.php?msg=Erro ao acessar o banco de dados");
		exit;
	}	

	// Adiciona a Função display_campo($nome_campo, $tipo_campo)
	require_once "../personalizacao_display.php";

?>


<!doctype html>
<html class="fixed">
<head>

	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Informações Saúde Pet</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo",'file');?>" type="image/x-icon" id="logo-icon">

	<!-- Specific Page Vendor CSS -->
	<link rel="stylesheet" href="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/select2/select2.css" />
	<link rel="stylesheet" href="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

	<!-- Theme CSS -->
	<link rel="stylesheet" href="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/modernizr/modernizr.js"></script>
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
		
	<!-- Vendor -->
	<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/jquery/jquery.min.js"></script>
	<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/magnific-popup/magnific-popup.js"></script>
	<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
		
	<!-- Specific Page Vendor -->
	<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/jquery-autosize/jquery.autosize.js"></script>
		
	<!-- Theme Base, Components and Settings -->
	<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/theme.js"></script>
		
	<!-- Theme Custom -->
	<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/theme.custom.js"></script>
		
	<!-- Theme Initialization Files -->
	<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/theme.init.js"></script>

	<!-- javascript functions -->
	<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>Functions/onlyNumbers.js"></script>
	<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>Functions/onlyChars.js"></script>
	<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>Functions/enviar_dados.js"></script>
	<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>Functions/mascara.js"></script>
	<!-- jquery fu/nctions -->
	<script>
		
		$(function() {
			var pacientes = <?php echo $_SESSION['saudepet'];?> ;
			<?php unset($_SESSION['saudepet']); ?>;
			$.each(pacientes, function(i, item) {
				$("#tabela")
				.append($("<tr onclick=irPg('"+item.id_pet+"') id='"+item.id_pet+"' class='tabble-row'>")
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
			window.location.href = "./profile_pet.php?id_pet="+ encodeURIComponent(idPet);
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
							<li><span>Informações Saúde Pet</span></li>
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

						<h2 class="panel-title">Pets Cadastrados</h2>
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
		<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/select2/select2.js"></script>
		<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="../../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
		
		<!-- Theme Base, Components and Settings -->
		<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/theme.js"></script>
		
		<!-- Theme Custom -->
		<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/theme.custom.js"></script>
		
		<!-- Theme Initialization Files -->
		<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/theme.init.js"></script>


		<!-- Examples -->
		<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/tables/examples.datatables.default.js"></script>
		<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
		<script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/tables/examples.datatables.tabletools.js"></script>
		
		<div align="right">
	        <iframe src="https://www.wegia.org/software/footer/pet.html" width="200" height="60" style="border:none;"></iframe>
        </div>
	</body>
</html>

