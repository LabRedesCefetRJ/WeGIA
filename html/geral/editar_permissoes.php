<?php
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
	
	session_start();
	if(!isset($_SESSION['usuario'])){
		header ("Location: ".WWW."html/index.php");
	}
	$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$id_pessoa = $_SESSION['id_pessoa'];
	$resultado = mysqli_query($conexao, "SELECT * FROM funcionario WHERE id_pessoa=$id_pessoa");
	if(!is_null($resultado)){
		$id_cargo = mysqli_fetch_array($resultado);
		if(!is_null($id_cargo)){
			$id_cargo = $id_cargo['id_cargo'];
		}
		$resultado = mysqli_query($conexao, "SELECT * FROM permissao WHERE id_cargo=$id_cargo and id_recurso=91");
		if(!is_bool($resultado) and mysqli_num_rows($resultado)){
			$permissao = mysqli_fetch_array($resultado);
			if($permissao['id_acao'] < 7){
				$msg = "Você não tem as permissões necessárias para essa página.";
				header("Location: ".WWW."html/home.php?msg_c=$msg");
			}
			$permissao = $permissao['id_acao'];
		}else{
        	$permissao = 1;
			$msg = "Você não tem as permissões necessárias para essa página.";
			header("Location: ".WWW."html/home.php?msg_c=$msg");
		}	
	}else{
		$permissao = 1;
		$msg = "Você não tem as permissões necessárias para essa página.";
		header("Location: ".WWW."html/home.php?msg_c=$msg");
	}	
	// Adiciona a Função display_campo($nome_campo, $tipo_campo)
	// Adiciona a Função display_campo($nome_campo, $tipo_campo)
	require_once ROOT."/html/personalizacao_display.php";
      $cargo = mysqli_query($conexao, "SELECT * FROM cargo");
      $acao = mysqli_query($conexao, "SELECT * FROM acao");
      $recurso = mysqli_query($conexao, "SELECT * FROM recurso");
	  
	require_once '../geral/msg.php';
	  
  if(!isset($_SESSION['almoxarifado'])){
    header('Location: ../../controle/control.php?metodo=listarTodos&nomeClasse=AlmoxarifadoControle&nextPage='.WWW.'/html/geral/editar_permissoes.php');
  }
  if(!isset($_SESSION['funcionarios'])){
    header('Location: ../../controle/control.php?metodo=listarTodos&nomeClasse=FuncionarioControle&nextPage=../html/geral/editar_permissoes.php');
  }
  extract($_SESSION);

?>
<!doctype html>
<html class="fixed" lang="pt-br">
<head>
	<!-- Basic -->
	<meta charset="utf-8">

	<title>Editar permissões</title>

	<!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="icon" href="<?php display_campo("Logo",'file');?>" type="image/x-icon" id="logo-icon">

    <!-- Specific Page Vendor CSS -->
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/select2/select2.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="<?php echo WWW;?>assets/stylesheets/theme.css" />

    <!-- Skin CSS -->
    <link rel="stylesheet" href="<?php echo WWW;?>assets/stylesheets/skins/default.css" />

    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="<?php echo WWW;?>assets/stylesheets/theme-custom.css">

    <!-- Head Libs -->
    <script src="<?php echo WWW;?>assets/vendor/modernizr/modernizr.js"></script>
        
    <!-- Vendor -->
    <script src="<?php echo WWW;?>assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
        
    <!-- Specific Page Vendor -->
    <script src="<?php echo WWW;?>assets/vendor/jquery-autosize/jquery.autosize.js"></script>
        
    <!-- Theme Base, Components and Settings -->
    <script src="<?php echo WWW;?>assets/javascripts/theme.js"></script>
        
    <!-- Theme Custom -->
    <script src="<?php echo WWW;?>assets/javascripts/theme.custom.js"></script>
        
    <!-- Theme Initialization Files -->
    <script src="<?php echo WWW;?>assets/javascripts/theme.init.js"></script>


    <!-- javascript functions -->
    <script src="<?php echo WWW;?>Functions/onlyNumbers.js"></script>
    <script src="<?php echo WWW;?>Functions/onlyChars.js"></script>
	<script src="<?php echo WWW;?>Functions/mascara.js"></script>
	<script src="<?php echo WWW; ?>Functions/cargos.js"></script>
	
	<!-- Estoque CSS -->
	<link rel="stylesheet" href="../../css/estoque.css">

	<script type="text/javascript">
		$(function () {
			$("#header").load("<?php echo WWW;?>html/header.php");
            $(".menuu").load("<?php echo WWW;?>html/menu.php");
	    });	
	</script>
		
	<!-- Almoxarife -->
	<script>
		$(function(){
			let Almoxarifado = <?= $almoxarifado?>;
			let Funcionarios = <?= $funcionarios?>;

			$.each(Almoxarifado,function(i,item){
				$("#id_almoxarifado")
					.append($('<option value="'+item.id_almoxarifado+'"/>')
						.text(`${item.id_almoxarifado} | ${item.descricao_almoxarifado}`)
					)
			});

			$.each(Funcionarios,function(i,item){
				$("#id_funcionario")
					.append($('<option value="'+item.id_funcionario+'"/>')
						.text(`${item.nome || "Sem Nome"} | ${item.cpf}`)
					)
			});
		});
	</script>
</head>
<body>
	<section class="body">

		<!-- start: header -->
		<header id="header" class="header">
			
		<!-- end: search & user box -->
		</header>
		<!-- end: header -->
		<div class="inner-wrapper">
			<!-- start: sidebar -->
			<aside id="sidebar-left" class="sidebar-left menuu"></aside>
			<!-- end: sidebar -->

			<section role="main" class="content-body">
				<header class="page-header">
					<h2>Permissões</h2>
					
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="<?= WWW ?>html/home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Editar permissões</span></li>
						</ol>
					
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->

				
				<div class="row">
					<div class="col-md-4 col-lg-12" style="visibility: hidden;"></div>
					<div class="col-md-8 col-lg-8" >
						<!-- Caso haja uma mensagem do sistema -->
						<?php getMsg();?>
						<div class="tabs">
							<ul class="nav nav-tabs tabs-primary">
								<li class="active">
									<a href="#overview" data-toggle="tab">Editar permissões
									</a>
								</li>
								<li class="nav-item">
									<a href="#almoxarife" data-toggle="tab">Adicionar Almoxarife</a>
								</li>
							</ul>
							<div class="tab-content">
								<div id="overview" class="tab-pane active">
									<fieldset>
										<form method="post" id="formulario" action="<?php echo(WWW.'controle/control.php'); ?>">
										<?php
											if(isset($_GET['msg_c'])){
												$msg = $_GET['msg_c'];
												echo('<div class="alert alert-success" role="alert">
												'. htmlspecialchars($msg) .'
											  </div>');
											}
											if($permissao == 1){
												echo($msg." - ".$permissao);
											}else{
										?>
											<div class="form-group">
												<label class="col-md-3 control-label" for="inputSuccess">Cargo</label>
												<a onclick="adicionar_cargo()">
													<i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i>
												</a>
												<div class="col-md-6">
													<select name="cargo" id="cargo" class="form-control input-lg mb-md">
                                                    <option selected disabled>Selecionar</option>
                                                        <?php
                                                            while($row = $cargo->fetch_array(MYSQLI_NUM))
                                                            {
															 if($row[0] != 2)
                                                             echo "<option value=".$row[0].">". htmlspecialchars($row[1])."</option>";
                                                            }             
                                                        ?>
													</select>
												</div>	
                                            </div>
											<div class="form-group">
												<label class="col-md-3 control-label" for="inputSuccess">Recurso</label>
												<div class="col-md-6">
                                                    <?php
                                                              while($row = $recurso->fetch_array(MYSQLI_NUM))
															  {
															   echo "<div class='checkbox'> <label><input id='recurso_". $row[0] ."' class='recurso' name='recurso[]' type='checkbox' value=". $row[0] .">". $row[1] ."</label> </div>";
															  }           
                                                        ?>
												</div>	
											</div>
                                            <div class="form-group">
												<label class="col-md-3 control-label" for="inputSuccess">Permissões</label>
												
												<div class="col-md-6">
													<select name="acao" id="id_acao" class="form-control input-lg mb-md">
                                                    <option selected disabled>Selecionar</option>
                                                    <?php
                                                            while($row = $acao->fetch_array(MYSQLI_NUM))
                                                            {
                                                             echo "<option value=".$row[0].">".$row[1]."</option>";
                                                            }             
                                                        ?>
													</select>
												</div>	
                                            </div>
											<input type="hidden" name="nomeClasse" value="FuncionarioControle">
											<input type="hidden" name="metodo" value="adicionarPermissao">
											<input type="hidden" name="nextPage" value="../html/geral/editar_permissoes.php">
											<div class="row">
												<div class="col-md-9 col-md-offset-3">
													<button id="enviar" class="btn btn-primary" type="submit">Enviar</button>
												</div>
											</div>
											<?php
												}
											?>
										</form>
									</fieldset>	
								</div>

								<!-- Almoxarife -->

								<div id="almoxarife" class="tab-pane" role="tabpanel">
									<fieldset>
										<form action="../matPat/adicionar_almoxarife.php" method="post">
											<div class="form-group">
												<label class="col-md-3 control-label" for="inputSuccess">Funcionário</label>
												<a href="../funcionario/cadastro_funcionario.php">
												<i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i>
											</a>
											<div class="col-md-6">
													<select name="id_funcionario" id="id_funcionario" class="form-control input-lg mb-md">
														<option selected disabled value="blank">Selecionar</option>
													</select>
												</div>	
											</div>
											
											<div class="form-group">
												<label class="col-md-3 control-label" for="inputSuccess">Almoxarifado</label>
												<a href="../matPat/adicionar_almoxarifado.php">
													<i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i>
												</a>
												<div class="col-md-6">
													<select name="id_almoxarifado" id="id_almoxarifado" class="form-control input-lg mb-md">
														<option selected disabled value="blank">Selecionar</option>
													</select>
												</div>
											</div>
											
											<div class="center-content">
												<input type="submit" value="Enviar" class="btn btn-primary" style="margin-right: 20px;">
												<a class="btn btn-success" href="<?= WWW ?>html/matPat/listar_almoxarife.php">Listar Almoxarifes</a>
											</div>
											
										</form>
									</fieldset>
								</div>
							</div>
						</div>
					</div>
					<div class="card col-md-4">
						<div class="card-body">
							<h5 class="card-title">Permissões para novo funcionário</h5>
							<p class="card-text">Crie uma senha para um novo funcionário entrar no sistema.</p>
							<a href="configurar_senhas.php" class="btn btn-primary">Configurar senhas</a><br><br>
							<p class="card-text">Liste as permissões configuradas para cada funcionário no sistema.</p>
							<a href="listar_permissoes.php" style="color: white; text-decoration:none;">
								<button class="btn btn-success" type="button">Listar permissões</button></a>
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
				</div>
			</div>
		</aside>
	</section>

	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/conf.html" width="200" height="60" style="border:none;"></iframe>
	</div>
</body>
<script>
	$(document).ready(function(){
		setTimeout(function(){
			$(".alert").fadeOut();
		}, 3000);
	});
	
	  function verificar_recursos_cargo(cargo_id){
          url = `../../controle/control.php?nomeClasse=CargoControle&metodo=listarRecursos&cargo=${encodeURIComponent(cargo_id)}`;              
          $.ajax({
          type: "GET",
          url: url, 
          success: function(response){
			var recursos = JSON.parse(response);
            console.log(response);
			$(".recurso").prop("checked",false ).attr("disabled", false);
			for(recurso of recursos){
				$("#recurso_"+recurso).prop("checked",true );
			}
          },
          dataType: 'text'
        })
      }

	  $(document).ready(function(){
		$("#cargo").change(function(){
			verificar_recursos_cargo($(this).val());
		});
	  });
</script>
<script src="../geral/msg.js"></script>
</html>
