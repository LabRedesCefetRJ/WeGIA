<?php
	session_start();
	
	$config_path = '../../config.php';
	require_once $config_path;
	
	if(!isset($_SESSION['usuario'])){
		header ("Location:  ". WWW ."html/index.php");
	}
	// Adiciona a Função display_campo($nome_campo, $tipo_campo)
	require_once ROOT . "/html/personalizacao_display.php";
?>

<!doctype html>
<html class="fixed">
<head>
<?php
  include_once ROOT . '/dao/Conexao.php';
  include_once ROOT . '/dao/IentradaDAO.php';
  

  if(!isset($_SESSION['ientrada'])){
    header('Location: ' . WWW . 'controle/control.php?metodo=listarId&nomeClasse=IentradaControle&nextPage='. WWW . 'html/matPat/listar_Ientrada.php');
  }
  if(isset($_SESSION['ientrada'])){
		$ientrada = $_SESSION['ientrada'];
	}
	if(!isset($_SESSION['entradaUnica'])){
    header('Location: ' . WWW . 'controle/control.php?metodo=listarId&nomeClasse=IentradaControle&nextPage='. WWW . 'html/matPat/listar_Ientrada.php');
  }
  if(isset($_SESSION['entradaUnica'])){
		$entrada = $_SESSION['entradaUnica'];
	}
?>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Informações Detalhadas de Entrada</title>
		
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
		

		async function getEntrada(){
			return await <?php echo $entrada; ?>;
		}
		

		document.addEventListener("DOMContentLoaded", async () => {
			const container = document.getElementById("containerInformacoesDeEntrada");

			let entrada = await getEntrada();

			entrada = entrada[0];

			const campos = [
				{ label: "Almoxarifado", valor: entrada.descricao_almoxarifado },
				{ label: "Origem", valor: entrada.nome_origem },
				{ label: "Tipo", valor: entrada.descricao },
				{ label: "Responsável", valor: entrada.nome},
				{ label: "Valor Total", valor: entrada.valor_total },
				{ label: "Data", valor: entrada.data },
				{ label: "Hora", valor: entrada.hora }
			];

			campos.forEach(campo => {
				const div = document.createElement("div");
				div.className = "linha-informacao";

				const span = document.createElement("span");
				span.className = "campoDeTexto";
				span.textContent = `${campo.label}:`;

				const texto = document.createElement("p");
				texto.textContent = campo.valor;

				div.appendChild(span);
				div.appendChild(texto);
				container.appendChild(div);
			});
		});
		

		$(function(){
			var ientrada= <?php 
				echo $ientrada; 
				?>;

			$.each(ientrada, function(i,item){

				$('#tabela')
					.append($('<tr />')
						.append($('<td />')
							.text(item.descricao))
						.append($('<td />')
							.text(item.qtd))
						.append($('<td />')
							.text(item.valor_unitario))
						.append($('<td />')
							.text(item.valor_unitario*item.qtd)))
					});
		});
		$(function () {
	      $("#header").load("<?= WWW ?>html/header.php");
	      $(".menuu").load("<?= WWW ?>html/menu.php");
	    });

		$(document).ready(function() {
			$('#datatable-default').DataTable({
				paging: false,          
				searching: false,       
				info: false,         
				lengthChange: false,    
				ordering: false         
			});
		});
	</script>
	<style>
		.linha-informacao {
			margin-bottom: 10px;
		}

		.linha-informacao span {
			font-weight: bold;
			font-size: 13px;
			display: inline-block;
			min-width: 140px;
		}

		.linha-informacao p {
			display: inline;
			font-size: 13px;
			margin: 0;
		}

		@media (max-width: 768px) {
			.linha-informacao span,
			.linha-informacao p {
			display: block;
			font-size: 14px;
			}
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
				<header class="page-header">
					<h2>Informações Detalhadas de Entrada</h2>
				
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="<?= WWW ?>html/home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Informações Detalhadas Entrada</span></li>
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
						<h2 class="panel-title">Entrada Detalhada</h2>
					</header>
					<div class="panel-body">
						<div id="containerInformacoesDeEntrada" class="container"></div>

						<table class="table table-bordered table-striped mb-none" id="datatable-default">
							<thead>
								<tr>
									<th>Produto</th>
									<th>Quantidade</th>
									<th>Valor Unitario</th>
									<th>Valor Total</th>
								</tr>
							</thead>
							<tbody id="tabela">	
							</tbody>
						</table>
					</div><br>
				</section>
			</section>
		</div>
	</section>
	<!-- end: page -->
			
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