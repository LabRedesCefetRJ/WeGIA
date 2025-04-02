<?php
   session_start();
   if(!isset($_SESSION['usuario'])){
      header ("Location: ../index.php");
   }

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
	$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$id_pessoa = $_SESSION['id_pessoa'];
	$resultado = mysqli_query($conexao, "SELECT * FROM funcionario WHERE id_pessoa=$id_pessoa");
	if(!is_null($resultado)){
		$id_cargo = mysqli_fetch_array($resultado);
		if(!is_null($id_cargo)){
			$id_cargo = $id_cargo['id_cargo'];
		}
		$resultado = mysqli_query($conexao, "SELECT * FROM permissao WHERE id_cargo=$id_cargo and id_recurso=24");
		if(!is_bool($resultado) and mysqli_num_rows($resultado)){
			$permissao = mysqli_fetch_array($resultado);
			if($permissao['id_acao'] < 5){
        $msg = "Você não tem as permissões necessárias para essa página.";
        header("Location: ./home.php?msg_c=$msg");
			}
			$permissao = $permissao['id_acao'];
		}else{
        	$permissao = 1;
          $msg = "Você não tem as permissões necessárias para essa página.";
          header("Location: ./home.php?msg_c=$msg");
		}	
	}else{
		$permissao = 1;
    $msg = "Você não tem as permissões necessárias para essa página.";
    header("Location: ./home.php?msg_c=$msg");
	}	
	
	// Adiciona a Função display_campo($nome_campo, $tipo_campo)
	require_once "personalizacao_display.php";
?>
<!doctype html>
<html class="fixed">
   <head>
      <?php
         	include_once '../dao/Conexao.php';
          	include_once '../dao/TipoSaidaDAO.php';
          
          	if(!isset($_SESSION['tipo_saida'])){
            	header('Location: ../controle/control.php?metodo=listarTodos&nomeClasse=TipoSaidaControle&nextPage=../html/listar_tipoSaida.php');
          	}
          	if(isset($_SESSION['tipo_saida'])){
            	$tipo = $_SESSION['tipo_saida'];
            	unset($_SESSION['tipo_saida']);
            }
      ?>
      <!-- Basic -->
      <meta charset="UTF-8">
      <title>Informaçoes</title>
      <!-- Mobile Metas -->
      <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
      <!-- Vendor CSS -->
      <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.css" />
      <link rel="stylesheet" href="../assets/vendor/font-awesome/css/font-awesome.css" />
      <link rel="stylesheet" href="../assets/vendor/magnific-popup/magnific-popup.css" />
      <link rel="stylesheet" href="../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
      <!-- Specific Page Vendor CSS -->
      <link rel="stylesheet" href="../assets/vendor/select2/select2.css" />
      <link rel="stylesheet" href="../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
      <link rel="icon" href="<?php display_campo("Logo",'file');?>" type="image/x-icon" id="logo-icon">

      <!-- Theme CSS -->
      <link rel="stylesheet" href="../assets/stylesheets/theme.css" />
      <!-- Skin CSS -->
      <link rel="stylesheet" href="../assets/stylesheets/skins/default.css" />
      <!-- Theme Custom CSS -->
      <link rel="stylesheet" href="../assets/stylesheets/theme-custom.css">
      <!-- Head Libs -->
      <script src="../assets/vendor/modernizr/modernizr.js"></script>
      <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
      <!-- Vendor -->
      <script src="../assets/vendor/jquery/jquery.min.js"></script>
      <script src="../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
      <script src="../assets/vendor/bootstrap/js/bootstrap.js"></script>
      <script src="../assets/vendor/nanoscroller/nanoscroller.js"></script>
      <script src="../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
      <script src="../assets/vendor/magnific-popup/magnific-popup.js"></script>
      <script src="../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
      <!-- javascript functions -->
      <script src="../Functions/onlyNumbers.js"></script>
      <script src="../Functions/onlyChars.js"></script>
      <script src="../Functions/enviar_dados.js"></script>
      <script src="../Functions/mascara.js"></script>
      <!-- jquery functions -->
      <script>
         function excluir(id){
         	window.location.replace('../controle/Control.php?metodo=excluir&nomeClasse=TipoSaidaControle&id_tipo='+id);
         }
      </script>
      <script>
         $(function(){
         	var tipo = <?php 
            echo $tipo; 
            ?>;
         
         	$.each(tipo, function(i,item){
         
         		$('#tabela')
         			.append($('<tr />')
         				.append($('<td />')
         					.text(item.descricao))
         				.append($('<td />')
         					.attr('onclick','excluir("'+item.id_tipo+'")')
         					.html('<i class="fas fa-trash-alt"></i>')));
         	});
         });
         $(function () {
            $("#header").load("header.php");
            $(".menuu").load("menu.php");
          });
      </script>
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
            <h2>Informaçoes</h2>
            <div class="right-wrapper pull-right">
               <ol class="breadcrumbs">
                  <li>
                     <a href="home.php">
                     <i class="fa fa-home"></i>
                     </a>
                  </li>
                  <li><span>Informações Funcionario</span></li>
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
               <h2 class="panel-title">Tipo</h2>
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
            </div>
            <br>
         </section>
      <!-- end: page -->
      <!-- Vendor -->
      <!-- Specific Page Vendor -->
      <script src="../assets/vendor/select2/select2.js"></script>
      <script src="../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
      <script src="../assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
      <script src="../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
      <!-- Theme Base, Components and Settings -->
      <script src="../assets/javascripts/theme.js"></script>
      <!-- Theme Custom -->
      <script src="../assets/javascripts/theme.custom.js"></script>
      <!-- Theme Initialization Files -->
      <script src="../assets/javascripts/theme.init.js"></script>
      <!-- Examples -->
      <script src="../assets/javascripts/tables/examples.datatables.default.js"></script>
      <script src="../assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
      <script src="../assets/javascripts/tables/examples.datatables.tabletools.js"></script>
   </body>
</html>