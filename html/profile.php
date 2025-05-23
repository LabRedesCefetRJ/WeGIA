<?php

	session_start();
	if(!isset($_SESSION['usuario'])){
		header ("Location: ../index.php");
	}
	if(!isset($_SESSION['perfil'])){
		$cpf=$_GET['cpf'];
		header('Location: ../controle/control.php?metodo=listarUm&nomeClasse=FuncionarioControle&nextPage=../html/profile_funcionario.php?cpf='.$cpf.'&cpf='.$cpf);
	}

	
	// Adiciona a Função display_campo($nome_campo, $tipo_campo)
	require_once "personalizacao_display.php";

?>

<!doctype html>
<html class="fixed">
	<head>

		<!-- Basic -->
		<meta charset="UTF-8">

		<title>Perfil</title>

		<!-- Mobile Metas -->
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<!-- Web Fonts  -->
		<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

		<!-- Vendor CSS -->
		<link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="../assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
		<link rel="stylesheet" href="../assets/vendor/magnific-popup/magnific-popup.css" />
		<link rel="stylesheet" href="../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
		<link rel="icon" href="<?php display_campo("Logo",'file');?>" type="image/x-icon" id="logo-icon">

		<!-- Theme CSS -->
		<link rel="stylesheet" href="../assets/stylesheets/theme.css" />

		<!-- Skin CSS -->
		<link rel="stylesheet" href="../assets/stylesheets/skins/default.css" />

		<!-- Theme Custom CSS -->
		<link rel="stylesheet" href="../assets/stylesheets/theme-custom.css">
		<script src="../Functions/lista.js"></script>
		<!-- Head Libs -->
		<script src="../assets/vendor/modernizr/modernizr.js"></script>
	</head>
	<body>
		<section class="body">

			<!-- start: header -->
			<header class="header">
				<div class="logo-container">
					<a href="home.php" class="logo">
						<img src="../img/logofinal.png" height="35" alt="Porto Admin" />
					</a>
					<div class="visible-xs toggle-sidebar-left" data-toggle-class="sidebar-left-opened" data-target="html" data-fire-event="sidebar-left-opened">
						<i class="fa fa-bars" aria-label="Toggle sidebar"></i>
					</div>
				</div>
			
				<!-- start: search & user box -->
				<div class="header-right">
				<span class="separator"></span>
				<div id="userbox" class="userbox">
					<a href="#" data-toggle="dropdown">
						<figure class="profile-picture">
							<img src="../img/semfoto.jpg" alt="Joseph Doe" class="img-circle" data-lock-picture="../assets/images/!logged-user.jpg" />
						</figure>
						<div class="profile-info" data-lock-name="John Doe" data-lock-email="johndoe@okler.com">
							<span class="name">Usuário</span>
							<span class="role">Funcionário</span>
						</div>
						<i class="fa custom-caret"></i>
					</a>
			
					<div class="dropdown-menu">
						<ul class="list-unstyled">
							<li class="divider"></li>
							<li>
								<a role="menuitem" tabindex="-1" href="../html/alterar_senha.php"><i class="glyphicon glyphicon-lock"></i> Alterar senha</a>
							</li>
							<li>
								<a role="menuitem" tabindex="-1" href="./logout.php"><i class="fa fa-power-off"></i> Sair da sessão</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
				<!-- end: search & user box -->
			</header>
			<!-- end: header -->

			<div class="inner-wrapper">
			<!-- start: sidebar -->
			<aside id="sidebar-left" class="sidebar-left">
				<div class="sidebar-header">
					<div class="sidebar-title">
						Menu
					</div>
					<div class="sidebar-toggle hidden-xs" data-toggle-class="sidebar-left-collapsed" data-target="html" data-fire-event="sidebar-left-toggle">
						<i class="fa fa-bars" aria-label="Toggle sidebar"></i>
					</div>
				</div>
				
				<div class="nano">
					<div class="nano-content">
						<nav id="menu" class="nav-main" role="navigation">
							<ul class="nav nav-main">
								<li>
									<a href="home.php">
										<i class="fa fa-home" aria-hidden="true"></i>
										<span>Início</span>
									</a>
								</li>
								<li class="nav-parent nav-active">
									<a>
										<i class="fa fa-copy"></i>
										<span>Pessoas</span>
									</a>
									<ul class="nav nav-children">
										<li>
											<a href="cadastro_funcionario.php">
												 Cadastrar funcionário
											</a>
										</li>
										<li>
											<a href="cadastro_interno.php">
												 Cadastrar interno
											</a>
										</li>
										<!--<li>
											<a href="cadastro_voluntario.php">
												 Cadastrar voluntário
											</a>
										</li>
										<li>
											<a href="cadastro_voluntario_judicial.php">
												 Cadastrar voluntário judicial
											</a>
										</li>-->
										<li>
											<a href="../controle/control.php?metodo=listarTodos&nomeClasse=FuncionarioControle&nextPage=../html/informacao_funcionario.php">
												 Informações funcionarios
											</a>
										</li>
										<li>
											<a href="../controle/control.php?metodo=listarTodos&nomeClasse=InternoControle&nextPage=../html/informacao_interno.php">
												 Informações interno
											</a>
										</li>
									</ul>
								</li>

								<li class="nav-parent nav-active">
									<a>
										<i class="fa fa-copy" aria-hidden="true"></i>
										<span>Material e Patrimônio</span>
									</a>
									<ul class="nav nav-children">
										<li>
											<a href="../html/matPat/cadastro_entrada.php">
												 Cadastrar Produtos
											</a>
										</li>
										<li>
											<a href="../html/cadastro_saida.php">
												 Saida de Produtos
											</a>
										</li>
										<li>
											<a href="../html/estoque.php">
												 Estoque
											</a>
										</li>
										<li>
											<a href="../html/listar_almox.php">
												 Almoxarifados
											</a>
										</li>
									</ul>
								</li>
							</ul>
						</nav>
					</div>
				</div>
				<form id="listarFuncionario" method="POST" action="../controle/control.php">
					<input type="hidden" name="nomeClasse" value="FuncionarioControle">
					<input type="hidden" name="metodo" value="listartodos">
					<input type="hidden" name="nextPage" value="../html/informacao_funcionario.php">
				</form>
				<form id="listarInterno" method="POST" action="../controle/control.php">
					<input type="hidden" name="nomeClasse" value="InternoControle">
					<input type="hidden" name="metodo" value="listartodos">
					<input type="hidden" name="nextPage" value="../html/informacao_interno.php">
				</form>
				</aside>
				<!-- end: sidebar -->

				<section role="main" class="content-body">
					<header class="page-header">
						<h2>Perfil</h2>
					
						<div class="right-wrapper pull-right">
							<ol class="breadcrumbs">
								<li>
									<a href="home.php">
										<i class="fa fa-home"></i>
									</a>
								</li>
								<li><span>Páginas</span></li>
								<li><span>Perfil</span></li>
							</ol>
					
							<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
						</div>
					</header>

					<!-- start: page -->

					<div class="row">
						<div class="col-md-4 col-lg-3">
							<section class="panel">
								<div class="panel-body">
									<div class="thumb-info mb-md">
										<img src="../img/semfoto.jpg" class="rounded img-responsive" alt="John Doe">
										<i class="fas fa-camera-retro btn btn-info btn-lg" data-toggle="modal" data-target="#myModal"></i>
										<div class="container">

											  <div class="modal fade" id="myModal" role="dialog">
											    <div class="modal-dialog">
											    
											      <!-- Modal content-->
											      <div class="modal-content">
											        <div class="modal-header">
											          <button type="button" class="close" data-dismiss="modal">&times;</button>
											          <h4 class="modal-title">Adicionar uma Foto</h4>
											        </div>
											        <div class="modal-body">
											        	<form action="/action_page.php">
											        	<div class="form-group">
															<label class="col-md-4 control-label" for="imgperfil">Carregue uma imagem de perfil:</label>
															<div class="col-md-8">
																<input type="file" name="imgperfil" size="60" id="imgform" class="form-control">
															</div>
														</div>
											        </div>
											        <div class="modal-footer">
											          <input type="submit" id="formsubmit">
											        </div>
											      </div>
											      
											    </div>
											  </div>
											  
											</div>
									</div>
							</section>
						</div>
						<div class="col-md-8 col-lg-6">

							<div class="tabs">
								<ul class="nav nav-tabs tabs-primary">
									<li class="active">
										<a href="#overview" data-toggle="tab">Visaõ Geral</a>
									</li>
									<li>
										<a href="#edit" data-toggle="tab">Editar Dados</a>
									</li>
								</ul>
								<div class="tab-content">
									<div id="overview" class="tab-pane active">
																		<div>
											<section class="panel">
												<header class="panel-heading">
													<div class="panel-actions">
														<a href="#" class="fa fa-caret-down"></a>
													</div>

													<h2 class="panel-title">Informações do Funcionário</h2>
												</header>
												 
												<div class="panel-body" style="display: block;">
													<ul class="nav nav-children" id="info">

														<li id="cap">Dados Pessoais:</li>
														<li>Nome:</li>
														<li>Sexo: <i class="fa fa-male"></i>   <i class="fa fa-female"></i></li>
														<li>Telefone:</li>
														<li>Data de Nascimento:</li>
														<li>CEP:</li>
														<li>Cidade:</li>
														<li>Bairro:</li>
														<li>Logradouro:</li>
														<li>Número:</li>
														<li>Complemento:</li>
														<br/>

														<li id="cap">RG</li>
														<li>Número:</li>
														<li>Data de Expedição do RG:</li>
														<br/>

														<li id="cap">CTPS</li>
														<li>Número:</li> 
														<li>PIS:</li>
														<li>UF:</li>
														<br/>

														<li>Zona Eleitoral: </li>
														<li>Título de Eleitor:</li>
														<br/>
														
														<li id="cap">Certificado de Reservista</li>
														<li>Número:</li>
														<li>Série:</li>
														<br/>

														<li id="cap">Empresa</li>
														<li>Vale Transporte: <i class="fa fa-check"></i>   <i class="fa fa-times"></i> </li>
														<li>Data de Admissão: 00/00/0000</li>
														<br/>
														<li id="cap">Vestuário</li>
														<li>Calçado: </li>
														<li>Calça: </li>
														<li>Jaleco: </li>
														<li>Camisa: </li>

												</div>
											</section>
											<section class="panel">
												<header class="panel-heading">
													<div class="panel-actions">
														<a href="#" class="fa fa-caret-down"></a>
													</div>

													<h2 class="panel-title">Observações Gerais:</h2>
												</header>
											</section>
										</div>
				
									</div>
									<div id="edit" class="tab-pane">

										<form class="form-horizontal" method="get">
											<h4 class="mb-xlg">Informações Pessoais</h4>
											<fieldset>
												<form method="get" action=".">
												<div class="form-group">
													<label class="col-md-3 control-label" for="profileFirstName">Primeiro Nome</label>
													<div class="col-md-8">
														<input type="text" class="form-control" id="profileFirstName">
													</div>
												</div>
												</div>
												<div class="form-group">
													<label class="col-md-3 control-label" for="profileCompany">Telefone</label>
													<div class="col-md-8">
														<input type="text" class="form-control" id="profileCompany">
													</div>
												</div>
												<div class="form-group">
													<label class="col-md-3 control-label" for="profileCompany">Nascimento</label>
													<div class="col-md-8">
														<input type="date" class="form-control" id="profileCompany">
													</div>
												</div>
												<div class="form-group">
													<label class="col-md-3 control-label" for="cep">CEP</label>
													<div class="col-md-8">
														<input type="text" name="cep"  value="" size="10" onblur="pesquisacep(this.value);" class="form-control" id="cep">
													</div>
												</div>
												<div class="form-group">
													<label class="col-md-3 control-label" for="uf">Estado</label>
													<div class="col-md-8">
														<input type="text" name="uf" size="60" class="form-control" id="uf">
													</div>
												</div>
												<div class="form-group">
													<label class="col-md-3 control-label" for="cidade">Cidade</label>
													<div class="col-md-8">
														<input type="text" size="40" class="form-control" id="cidade">
													</div>
												</div>
												<div class="form-group">
													<label class="col-md-3 control-label" for="bairro">Bairro</label>
													<div class="col-md-8">
														<input type="text" name="bairro" size="40" class="form-control" id="bairro">
													</div>
												</div>
												<div class="form-group">
													<label class="col-md-3 control-label" for="rua">Logradouro</label>
													<div class="col-md-8">
														<input type="text" name="rua" size="2" class="form-control" id="rua">
													</div>
												</div>
												<div class="form-group">
													<label class="col-md-3 control-label" for="profileCompany">Número</label>
													<div class="col-md-8">
														<input type="number" class="form-control" id="profileCompany">
													</div>
												</div>
												<div class="form-group">
													<label class="col-md-3 control-label" for="profileCompany">Complemento</label>
													<div class="col-md-8">
														<input type="text" class="form-control" id="profileCompany">
													</div>
												</div>
												<div class="form-group">
													<label class="col-md-3 control-label" for="ibge">IBGE</label>
													<div class="col-md-8">
														<input type="text" size="8" name="ibge" class="form-control" id="ibge">
													</div>
												</div>
												<br/>
											</form>

											</fieldset>
											<hr class="dotted tall">
											<h4 class="mb-xlg">Alterar Senha de Acesso</h4>
											<fieldset class="mb-xl">
												<div class="form-group">
													<label class="col-md-3 control-label" for="profileNewPassword">Nova Senha</label>
													<div class="col-md-8">
														<input type="text" class="form-control" id="profileNewPassword">
													</div>
												</div>
												<div class="form-group">
													<label class="col-md-3 control-label" for="profileNewPasswordRepeat">Confirmação</label>
													<div class="col-md-8">
														<input type="text" class="form-control" id="profileNewPasswordRepeat">
													</div>
												</div>
											</fieldset>
											<div class="panel-footer">
												<div class="row">
													<div class="col-md-9 col-md-offset-3">
														<button type="submit" class="btn btn-primary">Submit</button>
														<button type="reset" class="btn btn-default">Reset</button>
													</div>
												</div>
											</div>

										</form>

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
						<a href="#" class="mobile-close visible-xs">
							Collapse <i class="fa fa-chevron-right"></i>
						</a>
			
						<div class="sidebar-right-wrapper">
			
							<div class="sidebar-widget widget-calendar">
								<h6>Upcoming Tasks</h6>
								<div data-plugin-datepicker data-plugin-skin="dark" ></div>
			
								<ul>
									<li>
										<time datetime="2014-04-19T00:00+00:00">04/19/2014</time>
										<span>Company Meeting</span>
									</li>
								</ul>
							</div>
			
							<div class="sidebar-widget widget-friends">
								<h6>Friends</h6>
								<ul>
									<li class="status-online">
										<figure class="profile-picture">
											<img src="../img/semfoto.jpg" alt="Joseph Doe" class="img-circle">
										</figure>
										<div class="profile-info">
											<span class="name">Joseph Doe Junior</span>
											<span class="title">Hey, how are you?</span>
										</div>
									</li>
									<li class="status-online">
										<figure class="profile-picture">
											<img src="../img/semfoto.jpg" alt="Joseph Doe" class="img-circle">
										</figure>
										<div class="profile-info">
											<span class="name">Joseph Doe Junior</span>
											<span class="title">Hey, how are you?</span>
										</div>
									</li>
									<li class="status-offline">
										<figure class="profile-picture">
											<img src="../img/semfoto.jpg" alt="Joseph Doe" class="img-circle">
										</figure>
										<div class="profile-info">
											<span class="name">Joseph Doe Junior</span>
											<span class="title">Hey, how are you?</span>
										</div>
									</li>
									<li class="status-offline">
										<figure class="profile-picture">
											<img src="../img/semfoto.jpg" alt="Joseph Doe" class="img-circle">
										</figure>
										<div class="profile-info">
											<span class="name">Joseph Doe Junior</span>
											<span class="title">Hey, how are you?</span>
										</div>
									</li>
								</ul>
							</div>
			
						</div>
					</div>
				</div>
			</aside>
		</section>

		<!-- Vendor -->
		<script src="../assets/vendor/jquery/jquery.js"></script>
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

	</body>
</html>