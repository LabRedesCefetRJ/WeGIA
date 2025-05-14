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
require_once ROOT."/html/personalizacao_display.php";
      $cargo = mysqli_query($conexao, "SELECT * FROM cargo");
      $acao = mysqli_query($conexao, "SELECT * FROM acao");
      $recurso = mysqli_query($conexao, "SELECT * FROM recurso");
?>
<!doctype html>
<html class="fixed" lang="pt-br">
<head>

	<!-- Basic -->
	<meta charset="UTF-8">
	<title>Listar permissões</title>
	<meta name="keywords" content="HTML5 Admin Template" />
      <meta name="description" content="Porto Admin - Responsive HTML5 Template">
      <meta name="author" content="okler.net">
      <!-- Mobile Metas -->
       <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="../../../assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../../../assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="../../../assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="../../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="icon" href="<?php display_campo("Logo",'file');?>" type="image/x-icon" id="logo-icon">

    <!-- Specific Page Vendor CSS -->
    <link rel="stylesheet" href="../../../assets/vendor/select2/select2.css" />
    <link rel="stylesheet" href="../../../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="../../../assets/stylesheets/theme.css" />

    <!-- Skin CSS -->
    <link rel="stylesheet" href="../../../assets/stylesheets/skins/default.css" />

    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="../../../assets/stylesheets/theme-custom.css">

    <!-- Head Libs -->
    <script src="../../../assets/vendor/modernizr/modernizr.js"></script>
        
    <!-- Vendor -->
    <script src="../../../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
    <script src="../../../assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="../../../assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="../../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="../../../assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="../../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
        
    <!-- Specific Page Vendor -->
    <script src="../../../assets/vendor/jquery-autosize/jquery.autosize.js"></script>
        
    <!-- Theme Base, Components and Settings -->
    <script src="../../../assets/javascripts/theme.js"></script>
        
    <!-- Theme Custom -->
    <script src="../../../assets/javascripts/theme.custom.js"></script>
        
    <!-- Theme Initialization Files -->
    <script src="../../../assets/javascripts/theme.init.js"></script>


    <!-- javascript functions -->
    <script src="<?php echo WWW;?>Functions/onlyNumbers.js"></script>
    <script src="<?php echo WWW;?>Functions/onlyChars.js"></script>
    <script src="<?php echo WWW;?>Functions/mascara.js"></script>

	<script type="text/javascript">
		$(function () {
			$("#header").load("<?php echo WWW;?>html/header.php");
            $(".menuu").load("<?php echo WWW;?>html/menu.php");
	    });	

        function gerarCargo(){
          url = '../../dao/exibir_cargo.php';
          $.ajax({
          data: '',
          type: "POST",
          url: url,
          success: function(response){
            var cargo = response;
            $('#tabela').empty();
            $.each(cargo,function(i,item){
			  if(item.id_cargo != 1 && item.id_cargo != 2)
              $('#tabela').append(`<tr><td>${item.id_cargo}</td><td><input id='${item.id_cargo}' type='text' value='${item.cargo}'></td><td><a id='a_${item.id_cargo}' class='btn btn-primary' href='salvar_cargo.php?id_cargo=${item.id_cargo}&value='>Salvar</a><td><a class='btn btn-danger' href='deletar_cargo.php?id_cargo=${item.id_cargo}'>Deletar</a></td></tr>`);
            });
          },
          dataType: 'json'
        });
      }

      function adicionar_cargo(){
        url = '../../dao/adicionar_cargo.php';
        var cargo = window.prompt("Cadastre um Novo Cargo:");
        if(!cargo){return}
        situacao = cargo.trim();
        if(cargo == ''){return}              
        
          data = 'cargo=' +cargo; 
          console.log(data);
          $.ajax({
          type: "POST",
          url: url,
          data: data,
          success: function(response){
            gerarCargo();
          },
          dataType: 'text'
        })
      }
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
					<h2>Cargos </h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="../home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Cargos</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->
				<div class="row">
				<section class="panel">
						<header class="panel-heading">
							<div class="panel-actions">
								<a href="#" class="fa fa-caret-down"></a>
							</div>
							<h2 class="panel-title">Cargos</h2>
						</header>
						<div class="panel-body">
						<?php
									if(isset($_GET['msg_c'])){
										$msg = $_GET['msg_c'];
										echo('<div class="alert alert-success" role="alert">
										'. $msg .'
									  </div>');
									}else if(isset($_GET['msg_e'])){
										$msg = $_GET['msg_e'];
										echo('<div class="alert alert-danger" role="alert">
										'. $msg .'
									  </div>');
									}
							?>
							<table class="table text-center table-bordered table-striped mb-none" id="datatable-default">
								<thead>
									<tr>
										<th>Id</th>
										<th>Cargo</th>
                                        <th>Salvar</th>
                                        <th>Deletar</th>
									</tr>
								</thead>
								<tbody id="tabela">
									<?php
										$cargos= mysqli_query($conexao, "SELECT * FROM `cargo`");
										while($row = $cargos->fetch_array(MYSQLI_ASSOC))
                                        {
											$id_cargo = $row['id_cargo'];
											$cargo = $row['cargo'];
											if($id_cargo != 1 && $id_cargo != 2)
                                            echo "<tr><td>$id_cargo</td><td><input id='$id_cargo' type='text' value='$cargo'></td><td><a id='a_$id_cargo' class='btn btn-primary' href='salvar_cargo.php?id_cargo=$id_cargo&value='>Salvar</a><td><a class='btn btn-danger' href='deletar_cargo.php?id_cargo=$id_cargo'>Deletar</a></td></tr>";
                                        }         
									?>
								</tbody>
							</table>
						</div><br>
                        <button onclick="adicionar_cargo()" class="btn btn-primary pull-right">Adicionar cargo</button>
					</section>
				</section>
			</div>
		</section>
				</div>
			<!-- end: page -->
			</section>
		</div>

		<div align="right">
			<iframe src="https://www.wegia.org/software/footer/conf.html" width="200" height="60" style="border:none;"></iframe>
		</div>
	</section>

      <!-- Vendor -->
      <script>
        	$(document).ready(function(){

                function atualiza(){
                    console.log($(this).val());
                    var id = this.id;
                    $("#a_"+id).attr("href", `salvar_cargo.php?id_cargo=${id}&value=${$(this).val()}`)
                }

                $("input").change(function(){
                    console.log($(this).val());
                    var id = this.id;
                    $("#a_"+id).attr("href", `salvar_cargo.php?id_cargo=${id}&value=${$(this).val()}`)
				})
				
				setInterval(function(){
					$("input").change(function(){
						console.log($(this).val());
						var id = this.id;
						$("#a_"+id).attr("href", `salvar_cargo.php?id_cargo=${id}&value=${$(this).val()}`)
					})
				}, 300)
            })
      </script>
	  <script>
	  	$(document).ready(function(){
		setTimeout(function(){
			$(".alert").fadeOut();
			window.history.replaceState({}, document.title, window.location.pathname);
		}, 3000);
	});
	  </script>
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
	</body>
</html>
