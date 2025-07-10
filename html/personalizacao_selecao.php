<?php
	session_start();
	if(!isset($_SESSION['usuario'])){
		header ("Location: ../index.php");
	}
	require_once "../dao/Conexao.php";
	require_once "../classes/Personalizacao_campo.php";

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
		$resultado = mysqli_query($conexao, "SELECT * FROM permissao WHERE id_cargo=$id_cargo and id_recurso=9");
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

	// Adiciona a Função display_campo($nome_campo, $tipo_campo)
	require_once "personalizacao_display.php";

	$pdo = Conexao::connect();

	$res = $pdo->query("select id_imagem, imagem as arquivo, tipo, nome from imagem;");
	$img_tab = $res->fetchAll(PDO::FETCH_ASSOC);

	

	

	
	function display_img_selection($id,$pdo,$img_tab){
		$tipo_display = $_POST["tipo"] . "-select";
		echo('
		<div class="tab-pane active" id="img-tab" role="tabpanel" aria-labelledby="home-tab">
			<div style="display: flex; justify-content: space-between; flex-direction: column;">
				<button class="btn btn-primary fill-space" onclick="open_tab('."'add_form'".')"><i class="fas fa-plus icon"></i>Adicionar Imagem</button>
				<form action="personalizacao_upload.php" class="container" style="display: none; justify-content: space-between; width: -webkit-fill-available;justify-content: space-between; width: -moz-available;" method="post" id="add_form" enctype="multipart/form-data">
					<input type="file" name="img_file" class="form-control-file" style="padding: 10px;">
					<input type="number" name="id_campo" value="'.$id.'" style="display: none;" readonly>
					<button type="submit" class="btn btn-success"><i class="fas fa-arrow-right"></i></button>
				</form>
			</div>
			<table class="table table-hover">
				<thead>
					<tr>
					<th scope="col" width="30%">Arquivo</th>
					<th scope="col">Visualização</th>
					</tr>
				</thead>
				<tbody>
		');
		foreach($img_tab as $key => $value){
			$img_item = new Campo(
				[0 => $value["id_imagem"], 1 => $id],
				$tipo_display,
				$value["nome"],
				$value["arquivo"]
			);
			$img_item->display();
		}
		echo('
				</tbody>
			</table>
		</div>');
	}

	function display_car_selection($nome_carrossel, $pdo, $img_tab){
		$tipo_display = $_POST["tipo"] . "-select";
		$res = $pdo->query("select ic.id_imagem from campo_imagem c inner join tabela_imagem_campo ic on c.id_campo = ic.id_campo where c.tipo='car';");
		$carrossel = $res->fetchAll(PDO::FETCH_ASSOC);
		echo('
		<div class="selection-field" id="matrix">
		</div>
		<form action="personalizacao_upload.php" method="post" id="car-selection" style="display: none;">
			<input type="text" name="nome_car" value="' . $nome_carrossel . '" readonly>
		</form>
		<div>
			<button class="btn btn-success fill-space" onclick="submitCar()">Enviar</button>
		</div>
		<div class="tab-pane active" id="img-tab" role="tabpanel" aria-labelledby="home-tab">
			<table class="table table-hover">
				<thead>
					<tr>
					<th scope="col" width="8%">Selecionar</th>
					<th scope="col" width="30%">Arquivo</th>
					<th scope="col">Visualização</th>
					</tr>
				</thead>
				<tbody>
		');


		foreach ($img_tab as $key => $value){
			$img_item = new Campo(
				$value["id_imagem"],
				$tipo_display,
				$value["nome"],
				$value["arquivo"]
			);
			$img_item->display();
		}


		echo('
				</tbody>
			</table>
		</div>');

		foreach ($carrossel as $key => $value){
			$id = $value["id_imagem"];
			echo("
			<script>
				addToSelection(document.getElementById('img-$id').parentNode.parentNode)
			</script>
			");
		}
	}

?>
<!doctype html>
<html class="fixed">
<head>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Seleção de Conteúdo</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Web Fonts  -->
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../css/personalizacao-theme.css" />
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

	<!-- Head Libs -->
	<script src="../assets/vendor/modernizr/modernizr.js"></script>

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

	<!-- javascript functions --> <script
	src="../Functions/onlyNumbers.js"></script> <script
	src="../Functions/onlyChars.js"></script> <script
	src="../Functions/mascara.js"></script>

	<!-- jquery functions -->
	<script>
   		document.write('<a href="' + document.referrer + '"></a>');
	</script>
	
	<!-- Javascript: Seleção de imagem -->

	<script>
		function addToSelection(element){
			var matrix = document.getElementById("matrix")
			var fileName = element.children[1].innerText
			var src = element.children[2].firstElementChild.src
			var button = element.firstElementChild.firstElementChild.firstElementChild
			var selected = button.className == 'btn btn-success' ? 1 : 0

			if (selected == 0){

				img = document.createElement("IMG")
				img.src = src
				img.id = fileName
				img.classNmae = 'selected-imgr'
				matrix.appendChild(img)
				element.style.backgroundColor = '#ddffdd'
				button.className = "btn btn-success"
				button.title = "Desselecionar"
				button.firstElementChild.className = "far fa-check-square"
			}else{
				element.style.backgroundColor = ''
				button.className = "btn btn-light"
				button.title = "Selecionar"
				button.firstElementChild.className = "far fa-square"
				var img = document.getElementById(fileName)
				img.parentNode.removeChild(img)
			}
		}
	</script>

	<script type="text/javascript">
		$(function () {
	      $("#header").load("header.php");
	      $(".menuu").load("menu.php");
	    });	
    </script>
    
    <!-- javascript tab management script -->

    

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
					<h2>Seleção de Imagem</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="./home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Seleção de Imagem</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

                <!-- start: page -->

                <div class="row">
					<div class="col-md-4 col-lg-2"></div>
					<div class="col-md-8 col-lg-8">
                        <div class="tab-content" id="myTabContent">

							<?php
							
							$tipo = $_POST["tipo"];
							if ($tipo == "img"){
								$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

								if(!$id || $id < 1){
									http_response_code(400);
									exit('O id informado não é válido.');
								}

								display_img_selection($id,$pdo,$img_tab);
							}elseif ($tipo == "car"){
								$nome_car = $_POST["nome_car"];
								display_car_selection($nome_car,$pdo,$img_tab);
							}
							?>
                        </div>
					</div>
				</div>

                <!-- end: page -->
				<script>

					let btn1_state = false

					function open_tab(id){
						var tag = window.document.getElementById(id)
						var button = tag.parentNode.firstElementChild
						var icon = tag.parentElement.firstElementChild.firstElementChild
						if (!btn1_state){
							button.innerHTML = "<i class='fas fa-times icon'></i>"
							tag.style.display = 'flex'
							button.className = 'btn btn-outline-primary fill-space'
							btn1_state = true
						}else{
							button.innerHTML = "<i class='fas fa-plus icon'></i>Adicionar Imagem"
							tag.style.display = 'none'
							button.className = 'btn btn-primary fill-space'
							btn1_state = false
						}
					}

					// Indica o ID da linha que está sendo editada
					var selected


					// Alterna entre o texto normal e a textarea da linha selecionada
					function tr_select(id){
						selected = id
						var row = window.document.getElementById(id)
						var icon = row.firstElementChild
						var column_2 = row.children[2]
						var column_3 = row.children[3]
						if (column_2.style.display != 'none'){
							column_2.style.display = 'none'
							column_3.style.display = ''
							column_3.firstElementChild.innerText = column_2.innerText
							icon.firstElementChild.firstElementChild.className = "fas fa-chevron-left"
						}else{
							column_2.style.display = ''
							column_3.style.display = 'none'
							icon.firstElementChild.firstElementChild.className = "fas fa-edit"
						}
					}

					function addToSelection(element){
						var matrix = document.getElementById("matrix")
						var fileName = element.children[1].innerText
						var src = element.children[2].firstElementChild.src
						var button = element.firstElementChild.firstElementChild.firstElementChild
						var selected = button.className == 'btn btn-success' ? 1 : 0

						if (selected == 0){

							img = document.createElement("IMG")
							img.src = src
							img.id = fileName
							img.classNmae = 'selected-imgr'
							matrix.appendChild(img)
							element.style.backgroundColor = '#ddffdd'
							button.className = "btn btn-success"
							button.title = "Desselecionar"
							button.firstElementChild.className = "far fa-check-square"
						}else{
							element.style.backgroundColor = ''
							button.className = "btn btn-light"
							button.title = "Selecionar"
							button.firstElementChild.className = "far fa-square"
							var img = document.getElementById(fileName)
							img.parentNode.removeChild(img)
						}
					}

					function submitCar(){
						var matrix = document.getElementById("matrix")
						var form = document.getElementById("car-selection")
						var qtd_img = matrix.childElementCount
						if (qtd_img > 0){
							for (var i = 0; i < qtd_img; i++){
								input = document.createElement("INPUT")
								input.readOnly = true
								input.type = "text"
								input.name = "imagem_" + i
								input.value = matrix.children[i].id
								form.appendChild(input)
							}
							form.submit()
						}
					}

					function post(path, params, method='post') {
						const form = document.createElement('form');
						form.method = method;
						form.action = path;

						for (const key in params) {
							if (params.hasOwnProperty(key)) {
								const hiddenField = document.createElement('input');
								hiddenField.type = 'hidden';
								hiddenField.name = key;
								hiddenField.value = params[key];

								form.appendChild(hiddenField);
							}
						}

						document.body.appendChild(form);
						form.submit();
					}

				</script>
			</section>
		</div>
	</section>

	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/conf.html" width="200" height="60" style="border:none;"></iframe>
	</div>
</body>
</html>