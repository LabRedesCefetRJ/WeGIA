<?php
	require_once "./seguranca/sessionStart.php";
	session_start();
	if(!isset($_SESSION['usuario'])){
		header ("Location: ../index.php");
		exit();
	}

	// Adiciona a Função display_campo($nome_campo, $tipo_campo)
	require_once ROOT . "/html/personalizacao_display.php";

	// Funções de display de mensagens
	require_once ROOT . "/html/geral/msg.php";
?>

<!doctype html>
<html class="fixed">
<head>

	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Home</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Web Fonts  -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css" SamSite="Strict"><!--integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">-->
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />

	<!--Página Css que não interfere no estilo de oputras páginas do sistema-->
	<link rel="stylesheet" href="<?= WWW ?>css/home-theme.css" />

	<!-- Specific Page Vendor CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/jquery-ui/css/ui-lightness/jquery-ui-1.10.4.custom.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap-multiselect/bootstrap-multiselect.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/morris/morris.css" />

	<!-- Theme CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme.css" />
	<link rel="icon" href="<?php display_campo("Logo",'file');?>" type="image/x-icon">

	<!-- Skin CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="<?= WWW ?>assets/vendor/modernizr/modernizr.js"></script>
	<script src="<?= WWW ?>Functions/lista.js"></script>
	<!-- Vendor -->
	<script src="<?= WWW ?>assets/vendor/jquery/jquery.min.js"></script>

	<style>
		div#category-row > a:not(.visivel){ /* Faz com que apenas os selecionados por verificar_modulos se tornem visíveis*/
			display: none !important;
		}
	</style>

	<script type="text/javascript">
		$(function () {
	      $("#header").load("./header.php");
	      $(".menuu").load("./menu.php");
		  $(".category-item").on( "click", function() {
			avancarCamada("first", "first", "second", true);
		});

		//Quando o back-container for clicado, verifica em qual camada está
		$(".back-container").on( "click", function() {
			if(document.getElementById("back-container")){
				voltarCamada("first", 0, 0, true);
			}

			if(document.getElementById("back-container-second")){
				voltarCamada("second", "1", "first", false);
			}

			if(document.getElementById("back-container-third")){
				voltarCamada("third", "2", "second", false);
			}
		});
		
		$(".category-item-second").on("click", function() {
			avancarCamada("second", "second", "third", false);
		});

		$(".category-item-third").on("click", function() {
			avancarCamada("third", "third", "fourth", false);
		});

		function voltarCamada (numCamada, numRemoveIn, proxBackCont, primCam){
			//Verifica se será direcionado para a primeira camada (home)
			if(primCam){
				$("#category-row").removeClass("hidden");
				$(".collapse").removeClass("in");
				$(".back-container").addClass("hidden");
			}
			else{
				//numCamada diz qual será próxima camada
				camada = ".category-row-"+numCamada;
				$(camada).removeClass("hidden");

				//numRemoveIn diz qual removeIn deve ser executado
				if(numRemoveIn !== "1" && numRemoveIn !== 0){
					removeIn = ".removeIn-"+numRemoveIn;
					$(removeIn).removeClass("in");
				}
				else{
					$(".removeIn").removeClass("in");
				}
				//proxBackCont diz qual deve ser o próximo id do back container
				if(proxBackCont != "first" && proxBackCont != 0){
					backContainer = "back-container-"+proxBackCont;
					$(".back-container").attr("id", backContainer);	
				}
				else{
					$(".back-container").attr("id", "back-container");
				}
			}
		}

		function avancarCamada (rowAtual, proxBackCont, proximaRow, primIcon){
			//primIcon verifica se o ícone é da primeira camada (home)
			if(primIcon){
				$("#category-row").addClass("hidden");
				$(".back-container").removeClass("hidden");
			}
			else{
				//esconde a row atual
				atual = ".category-row-"+rowAtual;
				$(atual).addClass("hidden");

				//muda o id do back-container
				proxBC = "back-container-"+proxBackCont;
				$(".back-container").attr("id", proxBC);

				//faz aparecer a próxima row
				proxima = ".category-row-"+proximaRow;
				$(proxima).removeClass("hidden");
			}
		}
		/*Exemplo sobre as camadas: o usuário está na home e clica no botão de sócios. Os botões
		que aparecerem em seguida farão parte da segunda camada (category-row-second).
		Caso o usuário clique em outro botão que não seja um link para outra página, ele irá para a terceira camada (category-row-third).*/
	});

	$(document).ready(function(){
	verificar_modulos();
	})
	function verificar_modulos(){
		let url = "<?= WWW ?>dao/verificar_modulos_visiveis.php";
		$.ajax({
			type: "POST",
			url: url,
			success: function(response){
			var visiveis = JSON.parse(response);
			for(visivel of visiveis){
			$("#home"+visivel).addClass("visivel");
			}
		},
	dataType: 'text'
	});
	}

	</script>
	<script>

		var itemState = {}
		
		function getState(id){
			let state = itemState[id];
			if (state)
				return state;
			return false;
		}

		function setState(id, state){
			itemState[id] = state;
		}

		function openItem(id){
			console.log(id, !getState(id));
			if (!getState(id)){
				$('html, body').animate({
					scrollTop: $(id).height()
				}, 2);
				setState(id, true);
			}else{
				setState(id, false);
			}
		}
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
					<h2>Home</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="./home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Início</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->
				<div class="row" id="category-row" >

				<!-- Recebe mensagem se houver -->
				<?php displayMsg(); sessionMsg();?>

				<?php
					if(isset($_GET['msg_c'])){
						$msg = $_GET['msg_c'];
						echo('<div class="alert alert-danger alerta_c" role="alert">
						'. htmlspecialchars($msg) .'
					  </div>');
					}
				?>
					<a id="home1" href="#">
						<div class="col-lg-2 col-md-8 i category-item" data-toggle="collapse" href="#pessoas" >
							<i class="far fa-address-book"></i>
							<h4>Pessoas</h4>
						</div>
					</a>

					<a id="home6" href="#">
						<div class="col-lg-2 col-md-8 i category-item" data-toggle="collapse" href="#pet">
							<i  class="fa fa-paw"></i>
							<h4>Pet</h4>
						</div>
					</a>

					<a id="home2" href="#">
						<div class="col-lg-2 col-md-8 i category-item" data-toggle="collapse" href="#material">
							<i  class="fa fa-cubes"></i>
							<h4>Material e Patrimônio</h4>
						</div>
					</a>
					<!--onclick="window.location.href = '../memorando/envio.php'"-->
					<a id="home3" href="#">
						<div class="col-lg-2 col-md-8 i category-item" data-toggle="collapse" href="#memorando">
							<i  class="fa fa-book"></i>
							<h4>Memorando
							</h4>
						</div>
					</a>

					<a id="home4" href="#">
						<div class="col-lg-2 col-md-8 i category-item" data-toggle="collapse" href="#socios">
							<i  class="fa fa-users"></i>
							<h4>Sócios
							</h4>
						</div>
					</a>

					<a id="home5" href="#">
					<div class="col-lg-2 col-md-8 i category-item" data-toggle="collapse" href="#saude">
							<i class="fas fa-hospital-user"></i>
							<h4>Saúde</h4>
						</div>
					</a>

					<a id="home7" href="#" >
					<div class="col-lg-2 col-md-8 i category-item" data-toggle="collapse" href="#contribuicao">
							<i class="fa-solid fa-hand-holding-heart"></i>
							<h4>Contribuição</h4>
						</div>
					</a>

					<a class="visivel" href="#">
						<div class="col-lg-2 col-md-8 i category-item" data-toggle="collapse" href="#configuracao">
							<i  class="fa fa-cogs"></i>
							<h4>Configurações</h4>
						</div>
					</a>
				</div>

				<div class="back-container hidden" id="back-container">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="left-arrow-icon">
					<path d="M19 12H5" stroke="#888888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M12 19L5 12L12 5" stroke="#888888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<span class="back">Voltar</span>
				</div>
				
				<hr class="mobile-only">
				


				<!--Parte interna de #pessoas-->
				<div class="row category-row-second">
					<div id="pessoas" class="collapse">
						<a href="#">
							<div class="col-lg-2 col-md-8 i category-item-second" data-toggle="collapse" href="#funcionarios">
								<i class="fa fa-briefcase"></i>
								<h4>Funcionários</h4>
							</div>
						</a>
						<a href="#">
							<div class="col-lg-2 col-md-8 i category-item-second" data-toggle="collapse" href="#atendidos">
								<i class="fa fa-user"></i>
								<h4>Atendidos</h4>
							</div>
						</a>
						<a href="#">
					</div>
				</div>

				<div class="row category-row-third">
					<div  id="funcionarios" class="removeIn collapse">
						<a href="<?= WWW ?>html/funcionario/pre_cadastro_funcionario.php">	
							<div class="col-lg-2 col-md-8 i">
								<i  class="fa fa-address-book"></i>
								<h4>Cadastrar Funcionário</h4>
							</div>
						</a>
						<a href="<?= WWW ?>controle/control.php?metodo=listarTodos&nomeClasse=FuncionarioControle&nextPage=<?= WWW ?>html/funcionario/informacao_funcionario.php">	
							<div class="col-lg-2 col-md-8 i">
								<i  class="far fa-address-card"></i>
								<h4>Informações Funcionários</h4>
							</div>
						</a>
					</div>
				</div>

				<div class="row category-row-third">
					<div  id="atendidos" class="removeIn collapse">
						<a href="<?= WWW ?>html/atendido/pre_cadastro_atendido.php">	
							<div class="col-lg-2 col-md-8 i">
								<i  class="fa fa-address-book"></i>
								<h4>Cadastrar Atendido</h4>
							</div>
						</a>

						<a href="<?= WWW ?>controle/control.php?metodo=listarTodos&nomeClasse=AtendidoControle&nextPage=<?= WWW ?>html/atendido/Informacao_Atendido.php">
							<div class="col-lg-2 col-md-8 i">
								<form id="listarAtendido" method="POST" action="<?= WWW ?>controle/control.php">
									<i  class="far fa-address-card" id="listarAtendido"></i>
									<h4>Informação Atendidos</h4>
								</form>
							</div>
						</a>

						<a href="../controle/control.php?metodo=listarTodos&nomeClasse=AtendidoControle&nextPage=../html/atendido/cadastro_ocorrencia.php">
							<div class="col-lg-2 col-md-8 i">
								<form id="listarAtendido" method="POST" action="../controle/control.php">
									<i  class="fa fa-edit" id="listarAtendido"></i>
									<h4>Cadastrar Ocorrência</h4>
								</form>
							</div>
						</a>
						
					</div>
				</div>

				<div class="row category-row-third">
					<div  id="ocorrencias" class="removeIn collapse">
<<<<<<< HEAD
					<a href="<?= WWW ?>controle/control.php?metodo=listarTodos&nomeClasse=AtendidoControle&nextPage=.<?= WWW ?>html/atendido/cadastro_ocorrencia.php">
							<div class="col-lg-2 col-md-8 i">
								<form id="listarAtendido" method="POST" action="<?= WWW ?>controle/control.php">
									<i  class="fa fa-address-book" id="listarAtendido"></i>
									<h4>Cadastrar Ocorrência</h4>
								</form>
							</div>
						</a>
						<a href="<?= WWW ?>controle/control.php?metodo=listarTodos&nomeClasse=AtendidoControle&nextPage=<?= WWW ?>html/atendido/listar_ocorrencias_ativas.php">
							<div class="col-lg-2 col-md-8 i">
								<form id="listarAtendido" method="POST" action="<?= WWW ?>controle/control.php">
									<i  class="far fa-address-card" id="listarAtendido"></i>
									<h4>Ocorrências Ativas</h4>
								</form>
							</div>
						</a>
=======
						
>>>>>>> origin/pre-master-250514
					</div>
				</div>
				<!--fim da parte interna de #pessoas-->

				<!--parte interna de #pet-->
				<div class="row category-row-second">
					<div id="pet" class="collapse">
						<a href="<?= WWW ?>html/pet/cadastro_pet.php">
							<div class="col-lg-2 col-md-8 i">
								<i  class="fa fa-paw"></i>
								<h4>Cadastrar Pet</h4>
							</div>
						</a>
						<a href="<?= WWW ?>html/pet/informacao_pet.php">
							<div class="col-lg-2 col-md-8 i">
								<i  class="fa fa-clipboard-list"></i>
								<h4>Informações Pets</h4>
							</div>
						</a>
						<a href="#">
							<div class="col-lg-2 col-md-8 i category-item-second" data-toggle="collapse" href="#saudePet">
								<i class="fa fa-ambulance"></i>
								<h4>Saúde Pet</h4>
							</div>
						</a>
						
						<a href="#">
							<div class="col-lg-2 col-md-8 i category-item-second" data-toggle="collapse" href="#adotante">
								<i class="fa fa-user"></i></i><i class="fas fa-paw"></i>
								<h4>Adotantes</h4>
							</div>
						</a>
					</div>
				</div>

				<div class="row category-row-third">
					<div  id="adotante" class="removeIn collapse">
						<a href="<?= WWW ?>html/pet/adotantes/pre_cadastro_adotante.php">	
							<div class="col-lg-2 col-md-8 i">
								<i  class="fa fa-address-book"></i>
								<h4>Cadastrar Adotante</h4>
							</div>
						</a>
						<a href="<?= WWW ?>html/pet/adotantes/informacao_adotantes.php">
							<div class="col-lg-2 col-md-8 i">
								<i  class="far fa-address-card"></i>
								<h4>Informações Adotantes</h4>
							</div>
						</a>
					</div>
				</div>
				<div class="row category-row-third">
					<div  id="saudePet" class="removeIn collapse">
						<a href="<?= WWW ?>html/pet/cadastro_ficha_medica_pet.php">	
							<div class="col-lg-2 col-md-8 i">
								<i class="fa-solid fa-book-medical"></i>
								<h4>Cadastrar Ficha Médica Pet</h4>
							</div>
						</a>
						<a href="<?= WWW ?>html/pet/informacao_saude_pet.php">	
							<div class="col-lg-2 col-md-8 i">
								<i class="fa-solid fa-clipboard-list"></i>
								<h4>Informações Saúde Pet</h4>
							</div>
						</a>
						<a href="#">
							<div class="col-lg-2 col-md-8 i category-item-third" data-toggle="collapse" href="#medicamentos">
								<i class="fa fa-pills"></i>
								<h4>Medicamentos dos Pets</h4>
							</div>
						</a>
					</div>
				</div>

				<div class="row category-row-fourth">
					<div  id="medicamentos" class="removeIn-2 collapse">
						<a href="<?= WWW ?>html/pet/cadastrar_medicamento.php">
							<div class="col-lg-2 col-md-8 i">
								<i  class="far fa-clipboard"></i>
								<h4>Cadastrar Medicamento</h4>
							</div>
						</a>
						<a href="<?= WWW ?>html/pet/informacao_medicamento.php">
							<div class="col-lg-2 col-md-8 i">
								<i  class="fa fa-clipboard-list"></i>
								<h4>Informações Medicamentos</h4>
							</div>
						</a>
					</div>
				</div>
				<!--fim da parte interna de #pet-->

				<!--Parte interna de #material-->
				<div class="row category-row-second">
					<div  id="material" class="collapse">
						<a href="#">
							<div class="col-lg-2 col-md-8 i category-item-second" data-toggle="collapse" href="#entrada">
							<i class="fa-solid fa-circle-arrow-down"></i>
								<h4>Entrada</h4>
							</div>
						</a>
						<a href="#">
							<div class="col-lg-2 col-md-8 i category-item-second" data-toggle="collapse" href="#saida">
							<i class="fa-solid fa-circle-arrow-up"></i>
								<h4>Saída</h4>
							</div>
						</a>
						<a href="#">
							<div class="col-lg-2 col-md-8 i category-item-second" data-toggle="collapse" href="#estoque">
								<i class="fa fa-boxes"></i>
								<h4>Estoque</h4>
							</div>
						</a>
					</div>
				</div>


				<div class="row category-row-third">
					<div  id="entrada" class="removeIn collapse">
						<a href="<?= WWW ?>html/matPat/cadastro_entrada.php">	
							<div class="col-lg-2 col-md-8 i">
								<i  class="far fa-clipboard"></i>
								<h4>Registrar Entrada</h4>
							</div>
						</a>
						<a href="<?= WWW ?>html/matPat/listar_entrada.php">
							<div class="col-lg-2 col-md-8 i">
								<i  class="fa fa-barcode"></i>
								<h4>Informações Entradas</h4>
							</div>
						</a>
					</div>
				</div>
				
				<div class="row category-row-third">
					<div  id="saida" class="removeIn collapse">
						<a href="<?= WWW ?>html/matPat/cadastro_saida.php">	
							<div class="col-lg-2 col-md-8 i">
								<i  class="far fa-clipboard"></i>
								<h4>Registrar Saída</h4>
							</div>
						</a>
						<a href="<?= WWW ?>html/matPat/listar_saida.php">
							<div class="col-lg-2 col-md-8 i">
								<i  class="fa fa-barcode"></i>
								<h4>Informações Saídas</h4>
							</div>
						</a>
					</div>
				</div>
				
				<div class="row category-row-third">
					<div  id="estoque" class="removeIn collapse">
						<a href="<?= WWW ?>html/matPat/relatorio.php">
							<div class="col-lg-2 col-md-8 i">
								<i  class="far fa-clipboard"></i>
								<h4>Gerar Relatório</h4>
							</div>
						</a>
						<a href="#">
							<div class="col-lg-2 col-md-8 i category-item-third" data-toggle="collapse" href="#produtos">
								<i class="fa fa-box"></i>
								<h4>Produtos</h4>
							</div>
						</a>
						<a href="#">
							<div class="col-lg-2 col-md-8 i category-item-third" data-toggle="collapse" href="#almoxarifados">
								<i class="fa fa-warehouse"></i>
								<h4>Almoxarifados</h4>
							</div>
						</a>
					</div>
				</div>

				<div class="row category-row-fourth">
					<div  id="produtos" class="removeIn-2 collapse">
						<a href="<?= WWW ?>html/matPat/cadastro_produto.php">
							<div class="col-lg-2 col-md-8 i">
								<i class="fa fa-barcode"></i>
								<h4>Cadastrar Produto</h4>
							</div>
						</a>
						<a href="<?= WWW ?>html/matPat/listar_produto.php">
							<div class="col-lg-2 col-md-8 i">
							    <i class="fa fa-box"></i>
								<h4>Listar Produtos</h4>
							</div>
						</a>
					</div>
				</div>

				<div class="row category-row-fourth">
					<div  id="almoxarifados" class="removeIn-2 collapse">
						<a href="<?= WWW ?>html/matPat/adicionar_almoxarifado.php">
							<div class="col-lg-2 col-md-8 i">
								<i  class="far fa-clipboard"></i>
								<h4>Adicionar Almoxarifado</h4>
							</div>
						</a>
						<a href="<?= WWW ?>html/matPat/listar_almox.php">
							<div class="col-lg-2 col-md-8 i">
								<i  class="fa fa-clipboard-list"></i>
								<h4>Listar Almoxarifados</h4>
							</div>
						</a>
					</div>
				</div>
				<!--fim da parte interna de #material-->

				<!--parte interna de memorando-->
				<div class="row">
					<div id="memorando" class="collapse">
						<a href="<?= WWW ?>html/memorando/listar_memorandos_ativos.php">	
							<div class="col-lg-2 col-md-8 i">
								<i  class="fa fa-envelope"></i>
								<h4>Caixa de Entrada</h4>


							</div>
						</a>
						<a href="<?= WWW ?>html/memorando/novo_memorandoo.php">	
							<div class="col-lg-2 col-md-8 i">
								<i  class="fa fa-edit"></i>
								<h4>Novo Memorando</h4>
							</div>
						</a>


						<a href="<?= WWW ?>html/memorando/listar_memorandos_antigos.php">	
							<div class="col-lg-2 col-md-8 i">
								<i  class="fa fa-mail-forward"></i>
								<h4>Memorandos despachados</h4>
							</div>
						</a>
					</div>
				</div>
				<!--fim da parte interna de memorando-->

				<!--parte interna de #socios-->
				<div class="row category-row-second">
					<div id="socios" class="collapse">
						<a href="<?= WWW ?>html/socio/sistema/">
							<div class="col-lg-2 col-md-8 i">
								<i  class="fa fa-users"></i>
								<h4>Listar Sócios</h4>
							</div>
						</a>
						<a href="<?= WWW ?>html/socio/sistema/relatorios_socios.php">	
							<div class="col-lg-2 col-md-8 i">
								<i  class="far fa-clipboard"></i>
								<h4>Relatórios</h4>
							</div>
						</a>
						<a href="#">
							<div class="col-lg-2 col-md-8 i category-item-second" data-toggle="collapse" href="#contribuicoes">
								<i class="fa fa-money-bill"></i>
								<h4>Contribuições</h4>
							</div>
						</a>
						<a href="#">
							<div class="col-lg-2 col-md-8 i category-item-second" data-toggle="collapse" href="#extra">
								<i class="far fa-plus-square"></i>
								<h4>Extra</h4>
							</div>
						</a>
					</div>
				</div>
				
				<div class="row category-row-third">
					<div  id="cobrancas" class="removeIn collapse">
						<a href="<?= WWW ?>html/socio/sistema/cobrancas.php">
							<div class="col-lg-2 col-md-8 i">
								<i  class="far fa-chart-bar"></i>
								<h4>Controle Cobranças (Antigo)</h4>
							</div>
						</a>
						<a href="<?= WWW ?>html/socio/sistema/contribuicoes.php">
							<div class="col-lg-2 col-md-8 i">
								<i  class="far fa-chart-bar"></i>
								<h4>Controle de Contribuições</h4>
							</div>
						</a>
						<a href="<?= WWW ?>html/socio/sistema/psocio_geracao.php">	
							<div class="col-lg-2 col-md-8 i">
								<i  class="fas fa-hand-holding-usd"></i>
								<h4>Gerar Boleto</h4>
							</div>
						</a>
					</div>
				</div>
				<div class="row category-row-third">
					<div  id="extra" class="removeIn collapse">
						<a href="<?= WWW ?>html/socio/sistema/tags.php">	
							<div class="col-lg-2 col-md-8 i">
								<i  class="fa fa-tag"></i>
								<h4>Tags (grupos)</h4>
							</div>
						</a>
					</div>
				</div>
				<!--fim da parte interna de #socios-->

				<!--parte interna de #saude-->
				<div class="row category-row-second">
					<div  id="saude" class="collapse" >
						<a href="#">
							<div class="col-lg-2 col-md-8 i category-item-second" data-toggle="collapse" href="#paciente">
								<i class="fa fa-user"></i>
								<h4>Paciente</h4>
							</div>
						</a>
						<a href="#">
							<div class="col-lg-2 col-md-8 i category-item-second" data-toggle="collapse" href="#enfermaria">
								<i class="fa fa-user-md"></i>
								<h4>Registro Diário</h4>
							</div>
						</a>
					</div>
				</div>
				
				<div class="row category-row-third">
					<div  id="paciente" class="removeIn collapse">
						<a href="<?= WWW ?>html/saude/cadastro_ficha_medica.php">	
							<div class="col-lg-2 col-md-8 i">
								<i  class="fa fa-address-book"></i>
								<h4>Cadastrar Ficha Médica</h4>
							</div>
						</a>
						<a href="<?= WWW ?>html/saude/informacao_saude.php">	
							<div class="col-lg-2 col-md-8 i">
								<i  class="far fa-address-card"></i>
								<h4>Informações Pacientes</h4>
							</div>
						</a>
					</div>
				</div>
				
				<div class="row category-row-third">
					<div  id="enfermaria" class="removeIn collapse">
						<a href="<?= WWW ?>html/saude/administrar_medicamento.php">	
							<div class="col-lg-2 col-md-8 i">
								<i  class="fa fa-pills"></i>
								<h4>Medicamentos</h4>
							</div>
						</a>
						<a href="<?= WWW ?>html/saude/listar_sinais_vitais.php">	
							<div class="col-lg-2 col-md-8 i">
								<i  class="fa fa-address-book"></i>
								<h4>Sinais Vitais</h4>
							</div>
						</a>
						<a href="<?= WWW ?>html/saude/listar_cadastro_intercorrencia.php">	
							<div class="col-lg-2 col-md-8 i">
								<i  class="fa fa-edit"></i>
								<h4>Intercorrências</h4>
							</div>
						</a>
					</div>
				</div>		

				<!--fim da parte interna de #saude-->

				<!--parte interna de #contribuicao-->
				<div class="row">
					<div  id="contribuicao" class="collapse">
						<a href="<?= WWW ?>html/contribuicao/view/gateway_pagamento.php">
							<div class="col-lg-2 col-md-8 i" >
								<i class="fa-solid fa-building"></i>
								<h4>Gateway de pagamentos</h4>
							</div>
						</a>
						<a href="<?= WWW ?>html/contribuicao/view/meio_pagamento.php">
							<div class="col-lg-2 col-md-8 i" >
								<i class="fa-regular fa-credit-card"></i>
								<h4>Meio de pagamento</h4>
							</div>
						</a>
						<a href="<?= WWW ?>html/contribuicao/view/regra_pagamento.php">
							<div class="col-lg-2 col-md-8 i" >
								<i class="fa-solid fa-circle-exclamation"></i>
								<h4>Regras de pagamento</h4>
							</div>
						</a>
					</div>
				</div>
				<!--fim da parte interna de #contribuicao-->

				<!--parte interna de #configuracao-->
				<div class="row">
					<div  id="configuracao" class="collapse">
						<a href="<?= WWW ?>html/personalizacao.php">
							<div class="col-lg-2 col-md-8 i" >
								<i  class="fa fa-font"></i>
								<h4>Editar conteúdos</h4>
							</div>
						</a>
						<a href="<?= WWW ?>html/personalizacao_imagem.php">
							<div class="col-lg-2 col-md-8 i" >
								<i  class="fa fa-picture-o"></i>
								<h4>Lista de imagens</h4>
							</div>
						</a>
						<a href="../html/configuracao/configuracao_geral.php">
							<div class="col-lg-2 col-md-8 i" >
								<i  class="fas fa-cog"></i>
								<h4>Configurações Gerais</h4>
							</div>
						</a>
						<a href="<?= WWW ?>html/geral/editar_permissoes.php">
							<div class="col-lg-2 col-md-8 i" >
								<i  class="fa fa-key"></i>
								<h4>Permissões</h4>
							</div>
						</a>	
						<a href="<?= WWW ?>html/geral/modulos_visiveis.php">
							<div class="col-lg-2 col-md-8 i" >
								<i class="fa fa-eye"></i>
								<h4>Módulos Visíveis</h4>
							</div>
						</a> 				
					</div>
				</div>
				<!--fim da parte interna de #configuracao-->
			<!-- end: page -->
			</section>
		</div>
	</section>

	<!-- Vendor -->
	<script src="<?= WWW ?>assets/vendor/jquery/jquery.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="<?= WWW ?>assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="<?= WWW ?>assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="<?= WWW ?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
		
	<!-- Specific Page Vendor -->
	<script src="<?= WWW ?>assets/vendor/jquery-ui/js/jquery-ui-1.10.4.custom.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-ui-touch-punch/jquery.ui.touch-punch.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-appear/jquery.appear.js"></script>
	<script src="<?= WWW ?>assets/vendor/bootstrap-multiselect/bootstrap-multiselect.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-easypiechart/jquery.easypiechart.js"></script>
	<script src="<?= WWW ?>assets/vendor/flot/jquery.flot.js"></script>
	<script src="<?= WWW ?>assets/vendor/flot-tooltip/jquery.flot.tooltip.js"></script>
	<script src="<?= WWW ?>assets/vendor/flot/jquery.flot.pie.js"></script>
	<script src="<?= WWW ?>assets/vendor/flot/jquery.flot.categories.js"></script>
	<script src="<?= WWW ?>assets/vendor/flot/jquery.flot.resize.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-sparkline/jquery.sparkline.js"></script>
	<script src="<?= WWW ?>assets/vendor/raphael/raphael.js"></script>
	<script src="<?= WWW ?>assets/vendor/morris/morris.js"></script>
	<script src="<?= WWW ?>assets/vendor/gauge/gauge.js"></script>
	<script src="<?= WWW ?>assets/vendor/snap-svg/snap.svg.js"></script>
	<script src="<?= WWW ?>assets/vendor/liquid-meter/liquid.meter.js"></script>
	<script src="<?= WWW ?>assets/vendor/jqvmap/jquery.vmap.js"></script>
	<script src="<?= WWW ?>assets/vendor/jqvmap/data/jquery.vmap.sampledata.js"></script>
	<script src="<?= WWW ?>assets/vendor/jqvmap/maps/jquery.vmap.world.js"></script>
	<script src="<?= WWW ?>assets/vendor/jqvmap/maps/continents/jquery.vmap.africa.js"></script>
	<script src="<?= WWW ?>assets/vendor/jqvmap/maps/continents/jquery.vmap.asia.js"></script>
	<script src="<?= WWW ?>assets/vendor/jqvmap/maps/continents/jquery.vmap.australia.js"></script>
	<script src="<?= WWW ?>assets/vendor/jqvmap/maps/continents/jquery.vmap.europe.js"></script>
	<script src="<?= WWW ?>assets/vendor/jqvmap/maps/continents/jquery.vmap.north-america.js"></script>
	<script src="<?= WWW ?>assets/vendor/jqvmap/maps/continents/jquery.vmap.south-america.js"></script>
	<script>
		$(document).ready(function(){
		setTimeout(function(){
			$(".alerta_c").fadeOut();
			window.history.replaceState({}, document.title, window.location.pathname);
		}, 3000);
	});
	</script>

	<!-- Lida com as mensagens -->
	<script src="<?= WWW ?>html/geral/msg.js"></script>
		
	<div align="right">
	<iframe src="https://www.wegia.org/software/footer/home.html" width="200" height="60" style="border:none;"></iframe>
	</div>
</body>
</html>
