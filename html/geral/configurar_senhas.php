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
		header ("Location: ".WWW."index.php");
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
			if($permissao['id_acao'] == 1){
				$msg = "Você não tem as permissões necessárias para essa página.";
				header("Location: ".WWW."/html/home.php?msg_c=$msg");
			}
			$permissao = $permissao['id_acao'];
		}else{
        	$permissao = 1;
			$msg = "Você não tem as permissões necessárias para essa página.";
			header("Location: ".WWW."/html/home.php?msg_c=$msg");
		}	
	}else{
		$permissao = 1;
		$msg = "Você não tem as permissões necessárias para essa página.";
		header("Location: ".WWW."/html/home.php?msg_c=$msg");
	}	
	// Adiciona a Função display_campo($nome_campo, $tipo_campo)
	// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT."/html/personalizacao_display.php";
      $cargo = mysqli_query($conexao, "SELECT * FROM cargo");
      $acao = mysqli_query($conexao, "SELECT * FROM acao");
      $recurso = mysqli_query($conexao, "SELECT * FROM recurso");
?>
<!doctype html>
<html class="fixed">
<head>

	<!-- Basic -->
	<meta charset="UTF-8">
	<title>Alterar senha</title>
	<meta name="keywords" content="HTML5 Admin Template" />
      <meta name="description" content="Porto Admin - Responsive HTML5 Template">
      <meta name="author" content="okler.net">
      <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
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
    <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">

    <!-- Head Libs -->
    <script src="../../assets/vendor/modernizr/modernizr.js"></script>
        
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
    <script src="<?php echo WWW;?>Functions/onlyNumbers.js"></script>
    <script src="<?php echo WWW;?>Functions/onlyChars.js"></script>
    <script src="<?php echo WWW;?>Functions/mascara.js"></script>

	<script type="text/javascript">
		$(function () {
			$("#header").load("<?php echo WWW;?>html/header.php");
            $(".menuu").load("<?php echo WWW;?>html/menu.php");
	    });	
	</script>
		
	<script>


		$(function() {
			var verificacao = '<?php echo $_GET['verificacao'];?>';
		 	console.log(verificacao);

		 	if (verificacao=='1') {
		 		alert("Confirmação de senha não coincide com nova senha");
		 	}else if (verificacao=='2') {
		 		alert("Senha antiga está errada");
		 	}else if (verificacao=='3') {
		 		alert("Senha alterada com sucesso!");
				
		 	}

		} );

    </script>
		
</head>
<body>
	<section class="body">
		<!-- start: header -->
		<header id="header">
		</header>
		<!-- end: header -->

		<div class="inner-wrapper">
			<!-- start: sidebar -->
			<aside id="sidebar-left" class="sidebar-left menuu">
			</aside>
				
			<section role="main" class="content-body">
				<header class="page-header">
					<h2>Configurar senha funcionário</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="<?= WWW ?>html/home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Alterar senha</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->
				<div class="row">
					<div class="col-md-4 col-lg-3">
						<section class="panel"></section>
					</div>
					<div class="col-md-8 col-lg-8">
						<div class="tabs">
							<ul class="nav nav-tabs tabs-primary">
								<li class="active">
									<a href="#overview" data-toggle="tab">Alterar senha</a>
								</li>
							</ul>
							<div class="tab-content">
								<div id="overview" class="tab-pane active">
									<div>
										<h3  id="erro">Selecione um funcionário para modificar a senha</h3>
									</div>
									<form class="form-horizontal" method="post" action="<?php echo(WWW.'controle/control.php'); ?>">
									<fieldset>
										<div class="form-group">
											<label class="col-md-3 control-label" >Funcionário:
											</label>
											<div class="col-md-6">
                                            <select name="id_pessoa" id="id_pessoa" class="form-control mb-md">
                                                    <option selected disabled>Selecionar</option>
                                                       <?php
                                                            $funcionario = mysqli_query($conexao, "SELECT p.nome, p.id_pessoa as id_pessoa, c.cargo as nome_cargo from pessoa p join funcionario f on f.id_pessoa = p.id_pessoa join cargo c on f.id_cargo = c.id_cargo");
                                                            while($row = $funcionario->fetch_array(MYSQLI_NUM))
                                                            {
                                                             echo "<option value=".$row[1].">".$row[0]. " - " . $row[2] ."</option>";
                                                            }     
                                                       ?>
													</select>
											</label>
												
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label" >Nova senha:
											</label>
											<div class="col-md-6">
												<input type="password" name="nova_senha" class="form-control" required><br/>
											</label>
												
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label" >Confirmar senha:
											</label>
											<div class="col-md-6">
												<input type="password" name="confirmar_senha" class="form-control" required><br/>
											</label>
												
											</div>
										</div>
									</fieldset>
									<input type="hidden" name="nomeClasse" value="FuncionarioControle">
                                      <input type="hidden" name="metodo" value="alterarSenha">
                                      <input type="hidden" name="redir" value="geral/configurar_senhas.php">
                                      <a href="editar_permissoes.php" class="btn btn-danger">Voltar</a>
									<input type="submit" name="alterar" value="Alterar"  class="btn btn-primary">
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			<!-- end: page -->
			</section>
		</div>
	</section>

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
			<iframe src="https://www.wegia.org/software/footer/conf.html" width="200" height="60" style="border:none;"></iframe>
		</div>

	</body>
</html>
