<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION['usuario'])) {
	header("Location: ../index.php");
	exit(401);
} else {
	session_regenerate_id();
}

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

permissao($_SESSION['id_pessoa'], 9, 7);

require_once "../dao/Conexao.php";
require_once "../classes/Personalizacao_campo.php";

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once "personalizacao_display.php";

$pdo = Conexao::connect();

$res = $pdo->query("select id_imagem, imagem as arquivo, tipo, nome from imagem;");
$img_tab = $res->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html class="fixed">

<head>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Lista de Imagens</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />

	<!-- Web Fonts  -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../css/personalizacao-theme.css" />
	<link rel="stylesheet" href="../assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
	<link rel="stylesheet" href="../assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href='<?php display_campo("Logo", 'file'); ?>' type="image/x-icon">

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

	<!-- javascript functions -->
	<script
		src="../Functions/onlyNumbers.js"></script>
	<script
		src="../Functions/onlyChars.js"></script>
	<script
		src="../Functions/mascara.js"></script>

	<!-- Javascript: Seleção de imagem -->

	<script>
		function addToSelection(element) {
			var matrix = document.getElementById("matrix")
			var fileName = element.children[1].innerText
			var src = element.children[2].firstElementChild.src
			var button = element.firstElementChild.firstElementChild.firstElementChild
			var selected = button.className == 'btn btn-success' ? 1 : 0

			if (selected == 0) {

				img = document.createElement("IMG")
				img.src = src
				img.id = fileName
				img.classNmae = 'selected-imgr'
				matrix.appendChild(img)
				element.style.backgroundColor = '#ddffdd'
				button.className = "btn btn-success"
				button.title = "Desselecionar"
				button.firstElementChild.className = "far fa-check-square"
			} else {
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
		$(function() {
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
					<h2>Lista de Imagens</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="./home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Lista de Imagens</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->

				<div class="row">
					<div class="col-md-4 col-lg-2"></div>
					<div class="col-md-8 col-lg-8">
						<!-- Caso as alterações feitas sejam feitas com sucesso -->
						<?php if (isset($_GET['msg'])) {
							if ($_GET['msg'] == 'success') {
								echo ('<div class="alert alert-success"><i class="fas fa-check mr-md"></i><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Edição feita com sucesso!</div>');
							}
						} ?>

						<!-- Caso haja um erro fatal na alteração dos dados -->
						<?php if (isset($_GET['msg'])) {
							if ($_GET['msg'] == 'error') {
								echo ('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-md"></i><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>' . htmlspecialchars($_GET["err"]) . '</div>');
							}
						} ?>

						<!-- Caso haja um erro na alteração dos dados que não seja fatal -->
						<?php if (isset($_GET['msg'])) {
							if ($_GET['msg'] == 'warn') {
								echo ('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-md"></i><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>' . htmlspecialchars($_GET["err"]) . '</div>');
							}
						} ?>

						<div class="tab-content" id="myTabContent">
							<div class="tab-pane active" id="img-tab" role="tabpanel" aria-labelledby="home-tab">
								<div style="display: flex; flex-direction: column;">
									<button class="btn btn-primary fill-space" onclick="open_tab('add_form',this)" id="add"><i class="fas fa-plus icon"></i>Adicionar Imagem</button>
									<form action="personalizacao_upload.php" class="container" style="display: none;width: -webkit-fill-available;width: -moz-available;justify-content: space-between;" method="post" id="add_form" enctype="multipart/form-data">
										<input type="file" name="img_file" class="form-control-file" style="padding: 10px;">
										<input type="text" name="source" class="none" value="personalizacao_imagem.php" readonly>
										<button type="submit" class="btn btn-success"><i class="fas fa-arrow-right"></i></button>
									</form>
									<hr>
									<button class="btn btn-danger fill-space" onclick="open_tab('del_form',this)" id="del"><i class="fas fa-trash-alt icon"></i>Excluir Imagem</button>
									<form action="personalizacao_remover.php" class="container" style="display: none;width: -webkit-fill-available;justify-content: space-between;" method="post" id="del_form" enctype="multipart/form-data">
										<button type="button" class="btn btn-danger fill-space" onclick="submitDel()"><i class="fas fa-trash-alt icon"></i></button>
									</form>
									<hr>
								</div>
								<table class="table table-hover">
									<thead>
										<tr id="cols">
											<th scope="col" width="8%">Nome</th>
											<th scope="col" width="30%">Imagem</th>
										</tr>
									</thead>
									<tbody id="tbody">
										<?php
										foreach ($img_tab as $key => $value) {
											$img_item = new Campo(
												$value["id_imagem"],
												'img-simple',
												$value["nome"],
												$value["arquivo"]
											);
											$img_item->display();
										}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>

				<!-- end: page -->
				<script>
					var tbody = document.getElementById("tbody")
					var qtd_child = tbody.childElementCount

					// Array que indica se o item está selecionado
					var item_state = []
					for (var c = 0; c < qtd_child; c++) {
						item_state[tbody.children[c].id] = false
						//window.alert(item_state[tbody.children[c].id])
					}

					// Array que indica o estado dos botões de adicionar/excluir imagens
					var btn1_state = false
					var btn2_state = false

					function open_tab(id_tag, button) {
						var tag = window.document.getElementById(id_tag)
						var icon = tag.parentElement.firstElementChild.firstElementChild
						switch (button.id) {
							case "add":
								if (!btn1_state) {
									button.innerHTML = "<i class='fas fa-times icon'></i>"
									tag.style.display = 'flex'
									button.className = 'btn btn-outline-primary fill-space'
									btn1_state = true
								} else {
									button.innerHTML = "<i class='fas fa-plus icon'></i>Adicionar Imagem"
									tag.style.display = 'none'
									button.className = 'btn btn-primary fill-space'
									btn1_state = false
								}
								break;
							case "del":
								if (!btn2_state) {
									button.innerHTML = "<i class='fas fa-times icon'></i>"
									tag.style.display = 'flex'
									button.className = 'btn btn-outline-danger fill-space'

									var th = createTag("TH")
									th.id = 'temp-th'
									th.scope = 'col'
									th.innerText = "Selecionar"
									th.style.width = '1%'
									document.getElementById("cols").insertBefore(th, document.getElementById("cols").firstElementChild)

									for (var i = 0; i < qtd_child; i++) {
										var e = tbody.children[i]
										addSelector(e, i)
									}
									btn2_state = true


								} else {
									button.innerHTML = "<i class='fas fa-trash-alt icon'></i>Excluir Imagem"
									tag.style.display = 'none'
									button.className = 'btn btn-danger fill-space'

									item_state.forEach(function(selected, key) {
										if (selected) {
											addToSelection(document.getElementById(key))
										}
									})

									for (var i = 0; i < qtd_child; i++) {
										selector = tbody.children[i].firstElementChild
										if (selector)
											removeTag(i + "-selector")
									}
									removeTag("temp-th")
									btn2_state = false
								}
								break;
						}

					}

					function addSelector(parent, n) {

						var icon = document.createElement("I")
						icon.className = "far fa-square"

						var button = document.createElement("BUTTON")
						button.className = "btn btn-light"
						button.type = "button"

						var div = document.createElement("DIV")

						var td = document.createElement("TD")
						td.id = n + '-selector'
						td.className = 'v-center'

						parent.insertBefore(td, parent.firstElementChild)
						td.appendChild(div)
						div.appendChild(button)
						button.appendChild(icon)
					}

					function createTag(nome) {
						return document.createElement(nome)
					}


					function removeTag(id) {
						var tag = document.getElementById(id)
						tag.parentNode.removeChild(tag)
					}


					// Alterna entre o texto normal e a textarea da linha selecionada
					function tr_select(id) {
						selected = id
						var row = window.document.getElementById(id)
						var icon = row.firstElementChild
						var column_2 = row.children[2]
						var column_3 = row.children[3]
						if (column_2.style.display != 'none') {
							column_2.style.display = 'none'
							column_3.style.display = ''
							column_3.firstElementChild.innerText = column_2.innerText
							icon.firstElementChild.firstElementChild.className = "fas fa-chevron-left"
						} else {
							column_2.style.display = ''
							column_3.style.display = 'none'
							icon.firstElementChild.firstElementChild.className = "fas fa-edit"
						}
					}

					function addToSelection(element) {
						var button = element.firstElementChild.firstElementChild.firstElementChild
						var selected = item_state[element.id] ? true : false
						var form = document.getElementById("del_form")

						if (!selected && btn2_state) {
							element.style.backgroundColor = '#ffdddd'
							button.className = "btn btn-danger"
							button.title = "Desselecionar"
							button.firstElementChild.className = "far fa-check-square"
							item_state[element.id] = true
						} else {
							element.style.backgroundColor = ''
							button.className = "btn btn-light"
							button.title = "Selecionar"
							button.firstElementChild.className = "far fa-square"
							item_state[element.id] = false
						}
					}

					function submitDel() {
						const form = document.getElementById("del_form");
						const selecionados = item_state
							.map((sel, idx) => sel ? idx : null)
							.filter(val => val !== null);

						if (selecionados.length === 0) {
							alert("Nenhuma imagem foi selecionada.");
							return;
						}

						if (!confirm("ATENÇÃO! Tem certeza que deseja excluir os itens selecionados do Banco de Dados?")) {
							return;
						}

						selecionados.forEach(id => {
							const input = document.createElement("input");
							input.type = "hidden";
							input.name = "imagem[]";
							input.value = id;
							form.appendChild(input);
						});

						form.submit();
					}

					function post(path, params, method = 'post') {
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