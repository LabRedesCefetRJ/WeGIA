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
		$resultado = mysqli_query($conexao, "SELECT * FROM permissao WHERE id_cargo=$id_cargo and id_recurso=22");
		if(!is_bool($resultado) and mysqli_num_rows($resultado)){
			$permissao = mysqli_fetch_array($resultado);
			if($permissao['id_acao'] < 7){
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
	if (!isset($_GET['id_produto'])){
        header("Location: listar_produto.php");
    }
	// Adiciona a Função display_campo($nome_campo, $tipo_campo)
	require_once "personalizacao_display.php";

    include_once "./geral/msg.php";
    
    $pdo = Conexao::connect();
    $idProduto = $_GET['id_produto'];
    $query = $pdo->query("SELECT p.id_produto, p.preco, p.descricao,p.codigo, p.id_categoria_produto, c.descricao_categoria, p.id_unidade, u.descricao_unidade 
    FROM produto p 
    INNER JOIN categoria_produto c ON p.id_categoria_produto = c.id_categoria_produto 
    INNER JOIN unidade u ON p.id_unidade = u.id_unidade 
    WHERE p.id_produto = $idProduto;");
    $item = $query->fetch(PDO::FETCH_ASSOC);
    $query = $pdo->query("SELECT qtd FROM estoque WHERE id_produto=$idProduto;");
    $item['qtd'] = ($query->fetch(PDO::FETCH_ASSOC))['qtd'];
?>
<!doctype html>
<html class="fixed">
<head>
<?php 
  include_once '../dao/Conexao.php';
  include_once '../dao/ProdutoDAO.php';

  if (!isset($_GET['id_produto'])){
	  header("Location: ./listar_produto.php");
  }
  if(!isset($_SESSION['produtos'])){
    header('Location: ../controle/control.php?metodo=listarTodos&nomeClasse=ProdutoControle&nextPage=../html/remover_produto.php?id_produto='.$_GET['id_produto']);
  }
  extract($_REQUEST);
?>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Restaurar Produto</title>
		
	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.css" />
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
    
	<!-- Atualizacao CSS -->
	<link rel="stylesheet" href="../css/atualizacao.css" />

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
		
	<!-- Specific Page Vendor -->
	<script src="../assets/vendor/jquery-autosize/jquery.autosize.js"></script>
		
	<!-- Theme Base, Components and Settings -->
	<script src="../assets/javascripts/theme.js"></script>
		
	<!-- Theme Custom -->
	<script src="../assets/javascripts/theme.custom.js"></script>
		
	<!-- Theme Initialization Files -->
	<script src="../assets/javascripts/theme.init.js"></script>

	<!-- javascript functions -->
	<script src="../Functions/onlyNumbers.js"></script>
	<script src="../Functions/onlyChars.js"></script>
	<script src="../Functions/enviar_dados.js"></script>
	<script src="../Functions/mascara.js"></script>
		
	<!-- jquery functions -->
	<script>
        const id_produto = <?= $id_produto?>;
        $(function(){

        })

        function cancelar(){
            window.location.replace('../html/cadastrar_produto.php');
        }

        function submitForm(){
            $('#form').append($(`<input name="id_produto" value="${id_produto}" readonly hidden />`)).submit();
        }
	</script>
	<script>
        $(function () {
            $("#header").load("header.php");
            $(".menuu").load("menu.php");
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
				<h2>Restaurar Produto</h2>
			
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li>
							<a href="home.php">
								<i class="fa fa-home"></i>
							</a>
						</li>
						<li><span>Restaurar Produto</span></li>
					</ol>
			
					<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>

			<!-- start: page -->
		
			<section class="panel">
				<?php getMsg();?>
				<header class="panel-heading">
					<div class="panel-actions">
						<a href="#" class="fa fa-caret-down"></a>
					</div>
			
					<h2 class="panel-title">Restaurar Produto</h2>
				</header>
				<div class="panel-body">
					<div>
                        <p class="text-justify">A descrição inserida já existe como produto oculto, deseja restaurá-lo?</p>
                        <div class="panel-body" style="display: flex;">
                                <ul class="nav nav-children" id="info" style="padding-right: 20px;">
                                    <li>Nome: </li>
                                    <li>Categoria: </li>
                                    <li>Unidade: </li>
                                    <li>Codigo: </li>
                                    <li>Valor: </li>
                                    <li>Quantidade: </li>
                                </ul>
                                <ul class="nav nav-children" id="info">
                                    <?php
                                        echo("<li id='nome'>".$item['descricao']."</li>
                                        <li id='Categoria'>".$item['descricao_categoria']."</li>
                                        <li id='Unidade'>".$item['descricao_unidade']."</li>
                                        <li id='Codigo'>".$item['codigo']."</li>
                                        <li id='Valor'>R$ ".$item['preco']."</li>
                                        <li id='Quantidade'>".($item['qtd'] ? $item['qtd'] : 0 )."</li>");
                                    ?>
                                    
                                </ul>
                            </div>
                        <br>
						<form action="./restaurar_produto_desocultar.php" method="post" id="form">
							<div id="replace">
							</div>
							<br>
						</form>
						<div class="center-content">
							<button class="btn btn-primary sm-rm" onclick="submitForm()">Restaurar</button><button class="btn btn-danger" onclick="cancelar()">Cancelar</button>
						</div>
                    </div>
				</div>
                <br>
			</section>
            
			<!-- end: page -->
	
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