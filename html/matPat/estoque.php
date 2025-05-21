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

	require_once ROOT . '/dao/EstoqueDAO.php';
	$_SESSION['estoque'] = (new EstoqueDAO)->ListarTodos();
	if(!isset($_SESSION['estoque']))
	{
		header('Location: '. WWW .'controle/control.php?metodo=listartodos&nomeClasse=EstoqueControle&nextPage='. WWW .'html/matPat/estoque.php');
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

	require_once ROOT . "/dao/Conexao.php";

	require_once ROOT . '/Functions/permissao/permissao.php';

	define('PERMISSAO', permissaoUsuario($_SESSION['id_pessoa'], 2));
	?>
<!doctype html>
<html class="fixed">
<head>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Estoque</title>
		
	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
  	<!-- Vendor CSS -->
  	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
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

	<!-- CSS Estoque -->

	<link rel="stylesheet" href="<?= WWW ?>html/estoque/estoque.css">
		
	<!-- jquery functions -->
   	<script>
	$(function(){
		let estoque=<?= $_SESSION["estoque"] ?> ;
		<?php unset($_SESSION['estoque']); ?>

		$.each(estoque,function(i,item){
			$("#tabela")
				.append($("<tr class='item "+item.descricao_almoxarifado+" "+item.categoria+" "+(item.qtd <= 0 ? 'itemSemEstoque' : '')+"'>")
					.append($("<td>")
						.text(item.codigo))
					.append($("<td>")
						.text(item.descricao))
					.append($("<td>")
						.text(item.categoria))
					.append($("<td class='align-right'>")
						.text(item.qtd))
					.append($('<td />')
						.text(item.descricao_almoxarifado || "Nenhum")));
		});
		$('#mostrarZerado').prop('checked', false);
		
	});
	$(function () {
        $("#header").load("<?= WWW ?>html/header.php");
        $(".menuu").load("<?= WWW ?>html/menu.php");
    });

	var homeIcon = null;
	var Selecao = null;

	// Antes do navegador imprimir a página
	window.onbeforeprint = function(event) {
		// Retira a paginação para que todos os registros sejam exibidos
		let categ = $('#categ').val();
		let almox = $('#almox').val();
		let mZero = $('#mostrarZerado').prop('checked') ? "Mostrando produtos fora de estoque" : "Mostrando apenas produtos em estoque";
		$('#datatable-default').DataTable().destroy();
		$('#datatable-default').DataTable({
			"order":[[1, 'asc']],
			aLengthMenu: [
				[-1],
				["Mostrar Todos"]
			],
    		iDisplayLength: -1
		});
		homeIcon = $('#home-icon').children();
		Selecao = $('#selecao').children();
		$('#home-icon').empty();
		$('#selecao').empty();
		$('#home-icon').append($('<span />').text("<?php display_campo("Titulo","str");?>"));
		$('#selecao').html("<h2>Estoque</h2>Almoxarifado: "+almox+"</br>Categoria: "+categ+"</br>"+mZero+"</br>");
		filterItem();
		$('.datatables-header').children(0).hide()
		$('#print-btn')
	}

	// Depois do navegador imprimir ou cancelar a impressão da página
	window.onafterprint = function(event) { 
		// Recria a tabela com paginação
		$('#datatable-default').DataTable().destroy();
		$('#datatable-default').DataTable({
			"order":[[1, 'asc']],
			aLengthMenu: [
				[10, 25, 50, 100, -1],
				[10, 25, 50, 100, "Tudo"]
			],
    		iDisplayLength: 10
		});
		filterItem();
		$('#home-icon').empty();
		$('#selecao').empty();
		$('#home-icon').append(homeIcon);
		$('#selecao').append(Selecao);
		filterItem();
		$('.datatables-header').children(0).show()
	};
	var filtro = {
		almoxarifado: "todos",
		categoria: "todas",
		verZeros: false
	}

	function filterItem (){
		let table = $('#datatable-default').DataTable();
		let almox = filtro.almoxarifado == 'todos' ? '' : filtro.almoxarifado ;
		let categ = filtro.categoria == 'todas' ? '' : filtro.categoria ;

		table.search( `${almox} ${categ}` ).draw();
	}

	function selectAlmoxarifado(value){
		filtro.almoxarifado = value;
		filterItem();
	}

	function selectCategoria(value){
		filtro.categoria = value;
		filterItem();
	}

	function setMostrarZeros(){
		let pageLen = $('#datatable-default').DataTable().page.len();
		$('#datatable-default').DataTable().destroy();
		$('#datatable-default').DataTable({
			"order":[[1, 'asc']],
			aLengthMenu: [
				[-1],
				["Mostrar Todos"]
			],
    		iDisplayLength: -1
		});
		filtro.verZeros = $('#mostrarZerado').prop('checked');
		if (filtro.verZeros){
			$('.itemSemEstoque').show();
		}else{
			$('.itemSemEstoque').hide();
		}
		$('#datatable-default').DataTable().destroy();
		$('#datatable-default').DataTable({
			"order":[[1, 'asc']],
			aLengthMenu: [
				[10, 25, 50, 100, -1],
				[10, 25, 50, 100, "Tudo"]
			],
    		iDisplayLength: pageLen
		});
	}
	</script>
	
	<style type="text/css">
		/*.table{
			z-index: 0;
		}
		.text-right{
			z-index: 1;
		}*/
		.select{
			/*z-index: 2;*/
			/*float: left;*/
			position: absolute;
			width: 235px;
		}*/
		.select-table-filter{
			width: 140px;
			float: left;
		}
	</style>
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
				<header class="page-header print-hide">
					<h2>Estoque</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li id="home-icon">
								<a href="<?= WWW ?>html/home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Estoque</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>
				<!-- start: page -->
				<section class="panel" >
					<header class="panel-heading">
						<h2 class="panel-title">Estoque</h2>
					</header>
					<div class="panel-body">
						<div id="selecao">
							<span>Almoxarifado: </span>
							<select class="select-table-filter form-control mb-md" data-table="order-table" oninput="selectAlmoxarifado(this.value)" id="almox">
								<option selected value="todos">Todos</option>
								<?php
									$pdo = Conexao::connect();
									$res = $pdo->query("select descricao_almoxarifado, id_almoxarifado from almoxarifado;");
									$almoxarifado = $res->fetchAll(PDO::FETCH_ASSOC);
									$almoxarifado = JSON_decode(filtrarAlmoxarifado($_SESSION['id_pessoa'], JSON_encode($almoxarifado)));
									foreach ($almoxarifado as $value){
										echo('
										<option value="'.$value->descricao_almoxarifado.'">'.$value->descricao_almoxarifado.'</option>
										');
									}
									?>
									<option value="nenhum">Nenhum</option>
							</select>
								<span>Categoria: </span>
							<select class="select-table-filter form-control mb-md" data-table="order-table" oninput="selectCategoria(this.value)" id="categ">
								<option selected value="todas">Todas</option>
								<?php
									$pdo = Conexao::connect();
									$res = $pdo->query("select descricao_categoria from categoria_produto;");
									$almoxarifado = $res->fetchAll(PDO::FETCH_ASSOC);
									foreach ($almoxarifado as $value){
										echo('
										<option value="'.$value['descricao_categoria'].'">'.$value['descricao_categoria'].'</option>
										');
									}
									?>
							</select>
							<div>
								<label for="mostrarZerado">Exibir produtos fora de estoque: </label>
								<input type="checkbox" name="mostrarZerado" id="mostrarZerado" oninput="setMostrarZeros()">
							</div>
						</div>
						<div class="select" >
	  					</div>
	  					<button style="float: right;" class="mb-xs mt-xs mr-xs btn btn-default print-hide" onclick="window.print();">Imprimir</button>
	  					<br><br>
		  					
						<p class="print-hide">Atenção: Produtos de um determinado almoxarifado só serão exibidos caso o usuário esteja cadastrado como almoxarife.</p>
						<table class="table table-bordered table-striped mb-none" id="datatable-default">
							<thead>
								<tr>
									<th>Código</th>
									<th>Produto</th>
									<th>Categoria</th>
									<th>Quantidade</th>
									<th>Almoxarifado</th>
								</tr>
							</thead>
							<tbody id="tabela">
							</tbody>
						</table>
					</div>
				</section>
			</section>
		</div>
	</section>
	<!-- end: page -->
	<!-- Vendor -->
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
	<div align="right">
	<iframe src="https://www.wegia.org/software/footer/matPat.html" width="200" height="60" style="border:none;"></iframe>
	</div>
	</body>
</html>
