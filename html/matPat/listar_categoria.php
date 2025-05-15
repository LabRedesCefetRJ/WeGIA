<?php
	session_start();

	$config_path = "config.php";
	if(file_exists($config_path)){
		require_once($config_path);
	}else{
		while(true){
			$config_path = "../" . $config_path;
			if(file_exists($config_path)) break;
		}
		require_once($config_path);
	}

	if (!isset($_SESSION['usuario'])) {
		header("Location: ". WWW ."html/index.php");
	}

	$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$id_pessoa = $_SESSION['id_pessoa'];
	$resultado = mysqli_query($conexao, "SELECT * FROM funcionario WHERE id_pessoa=$id_pessoa");
	if(!is_null($resultado)){
		$id_cargo = mysqli_fetch_array($resultado);
		if(!is_null($id_cargo)){
			$id_cargo = $id_cargo['id_cargo'];
		}
		$resultado = mysqli_query($conexao, "SELECT * FROM permissao WHERE id_cargo=$id_cargo and id_recurso=22");
		if(!is_bool($resultado) and mysqli_num_rows($resultado)){
			$permissao = mysqli_fetch_array($resultado);
			if($permissao['id_acao'] < 5){
        $msg = "Você não tem as permissões necessárias para essa página.";
        header("Location: ". WWW ."html/home.php?msg_c=$msg");
			}
			$permissao = $permissao['id_acao'];
		}else{
        	$permissao = 1;
          $msg = "Você não tem as permissões necessárias para essa página.";
          header("Location: ". WWW ."html/home.php?msg_c=$msg");
		}	
	}else{
		$permissao = 1;
    $msg = "Você não tem as permissões necessárias para essa página.";
    header("Location: ". WWW ."html/home.php?msg_c=$msg");
	}	
	
	// Adiciona a Função display_campo($nome_campo, $tipo_campo)
	require_once ROOT . "/html/personalizacao_display.php";
?>

<!doctype html>
<html class="fixed">
<head>
<?php
	include_once ROOT . '/dao/Conexao.php';
  	include_once ROOT . '/dao/CategoriaDAO.php';

	if(!isset($_SESSION['categoria'])){
		header('Location: ' . WWW . 'controle/control.php?metodo=listarTodos&nomeClasse=CategoriaControle&nextPage='.WWW.'/html/matPat/listar_categoria.php');
	}
	if(isset($_SESSION['msg'])){
		$msg = $_SESSION['msg'];
	}
	if(isset($_SESSION['categoria'])){
		$categoria = $_SESSION['categoria'];
	    unset($_SESSION['categoria']);  
	}
?>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Informações</title>
		
	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo",'file');?>" type="image/x-icon" id="logo-icon">

	<!-- Specific Page Vendor CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/select2/select2.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

	<!-- Theme CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="<?= WWW ?>assets/vendor/modernizr/modernizr.js"></script>
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
		
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
	<script src="<?= WWW ?>Functions/enviar_dados.js"></script>
	<script src="<?= WWW ?>Functions/mascara.js"></script>
		
	<!-- jquery functions -->
	<script>

	function excluir(id){
		window.location.replace('<?= WWW ?>controle/control.php?metodo=excluir&nomeClasse=CategoriaControle&id_categoria_produto='+id);
	}
	function editar(id){
		window.location.replace('<?= WWW ?>html/matPat/editar_categoria.php?id_categoria='+id);
	}
	</script>
	<script>
		$(function(){
			var categoria= <?php 
				echo $categoria; 
				?>;

			$.each(categoria, function(i,item){

				$('#tabela')
					.append($('<tr />')
						.append($('<td />')
							.text(item.descricao_categoria))
						.append($('<td />')
							.html(`<i class="fas fa-trash-alt" onclick="excluir(${item.id_categoria_produto})"></i> <i class="fas fa-pencil-alt" onclick="editar(${item.id_categoria_produto})"></i>`))
						);
			});
		});
		$(function () {
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
						<h2>Informações Categoria</h2>
					
						<div class="right-wrapper pull-right">
							<ol class="breadcrumbs">
								<li>
									<a href="<?= WWW ?>html/home.php">
										<i class="fa fa-home"></i>
									</a>
								</li>
								<li><span>Informações Categoria</span></li>
							</ol>
					
							<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
						</div>
					</header>

					<!-- start: page -->
					
						<section class="panel">
							<header class="panel-heading">
								<div class="panel-actions">
									<a href="#" class="fa fa-caret-down"></a>
								</div>
						
								<h2 class="panel-title">Categoria</h2>
							</header>
							<div class="panel-body">
								<table class="table table-bordered table-striped mb-none" id="datatable-default">
									<thead>
										<tr>
											<th>Nome</th>
											<th>Ação</th>
										</tr>
									</thead>
									<tbody id="tabela">	
									</tbody>
								</table>
							</div><br>
						</section>
					<!-- end: page -->

		<!-- Vendor -->

		
		<!-- Specific Page Vendor -->
		<script src="<?= WWW ?>assets/vendor/select2/select2.js"></script>
		<script src="<?= WWW ?>assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="<?= WWW ?>assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="<?= WWW ?>assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
		
		<!-- Theme Base, Components and Settings -->
		<script src="<?= WWW ?>assets/javascripts/theme.js"></script>
		
		<!-- Theme Custom -->
		<script src="<?= WWW ?>assets/javascripts/theme.custom.js"></script>
		
		<!-- Theme Initialization Files -->
		<script src="<?= WWW ?>assets/javascripts/theme.init.js"></script>


		<!-- Examples -->
		<script src="<?= WWW ?>assets/javascripts/tables/examples.datatables.default.js"></script>
		<script src="<?= WWW ?>assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
		<script src="<?= WWW ?>assets/javascripts/tables/examples.datatables.tabletools.js"></script>
	</body>
</html>