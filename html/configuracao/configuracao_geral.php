<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if(session_status() === PHP_SESSION_NONE)
	session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: ../../index.php");
	exit();
}else{
	session_regenerate_id();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 9);

// Inclui display de Campos
require_once "../personalizacao_display.php";

// Adiciona o Sistema de Mensagem
require_once "../geral/msg.php";

// Ultima release disponível
$last_release = intval(file_get_contents("https://www.wegia.org/software/release"));
// Release instalada
$local_release = intval(file_get_contents("../../.release"));

define("SYSTEM_UP_TO_DATE", $local_release >= $last_release);

?>
<!doctype html>
<html class="fixed">

<head>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Configurações Gerais</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Web Fonts  -->
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
	<link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon">

	<!-- Theme CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="../../assets/vendor/modernizr/modernizr.js"></script>

	<!-- Configuração CSS -->
	<link rel="stylesheet" href="../../css/configuracao.css" />

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
	<script
		src="../../Functions/onlyNumbers.js"></script>
	<script
		src="../../Functions/onlyChars.js"></script>
	<script
		src="../../Functions/mascara.js"></script>

	<!-- jquery functions -->
	<script>
		document.write('<a href="' + document.referrer + '"></a>');
	</script>

	<script type="text/javascript">
		$(function() {
			$("#header").load("../header.php");
			$(".menuu").load("../menu.php");
		});
	</script>

	<!-- javascript tab management script -->

	<style>
		.btn {
			width: 10%;
		}
	</style>

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
					<h2>Configurações Gerais do Sistema</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="../home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Configurações Gerais</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>
				<!--start: page-->

				<!-- Caso haja uma mensagem do sistema -->
				<?php displayMsg();
				getMsgSession("mensagem", "tipo"); ?>

				<session class="panel">
					<header class="panel-heading">
						<div class="panel-actions">
							<a href="#" class="fa fa-caret-up"></a>
						</div>
						<h2 class="panel-title">Ações Globais</h2>
					</header>
					<div class="panel-body" style="display: none;">
						<div class="ls-container">
							<!-- Conteúdo -->
							<div class="config-item" <?= SYSTEM_UP_TO_DATE ? "style='display: none;'" : ""; ?>>
								<div>Atualizar sistema:</div>
								<button id="btn2" class="btn btn-primary" onClick="setLoader(this)"><a href="./atualizacao.php"><i class="fas fa-download" aria-hidden="true"></i></a></button>
							</div>
							<div class="config-item" <?= SYSTEM_UP_TO_DATE ? "" : "style='display: none;'"; ?>>
								<div>Atualizar sistema:</div>
								<div>
									<button disabled="disabled" class="btn btn-info"><i class="fas fa-download" aria-hidden="true"></i></button>
									<i class="fas fa-check-circle" style="color: green; padding: 0 5px;"></i>
									Sistema atualizado
								</div>
							</div>
							<div class="config-item">
								<div>Recalcular Estoque:</div>
								<button id="btn2" class="btn btn-primary" onClick="setLoader(this)"><a href="./correcao_estoque.php"><i class="fas fa-wrench"></i></a></button>
							</div>
						</div>
					</div>
				</session>

				<session class="panel">
					<header class="panel-heading">
						<div class="panel-actions">
							<a href="#" class="fa fa-caret-up"></a>
						</div>
						<h2 class="panel-title">Banco de Dados</h2>
					</header>
					<div class="panel-body" style="display: none;">
						<div class="ls-container">
							<!-- Conteúdo -->
							<div class="config-item">
								<div>Fazer backup:</div>
								<button id="btn3" class="btn btn-primary" onClick="setLoader(this)"><a href="./backup.php"><i class="fa fa-floppy-o" aria-hidden="true"></i></a></button>
							</div>
							<div class="config-item">
								<div>Gerenciar backup:</div>
								<button id="btn4" class="btn btn-primary" onClick="setLoader(this)"><a href="./listar_backup.php"><i class="fa fa-list" aria-hidden="true"></i></a></button>
							</div>
							<div class="config-item">
								<div>Importar backup:</div>
								<div>
									<button id="btn5" class="btn btn-primary" onclick="openItem('btn5', 'section1')">
										<a href="#">
											<i class="fa fa-upload" aria-hidden="true"></i>
										</a>
									</button>
									<div id="section1" style="display: none;">
										<button id="btn51" class="btn btn-success" onclick="submitForm('form5')">
											<i class="fas fa-arrow-right"></i>
										</button>
										<form method="post" action="./importar_dump.php" id="form5" enctype="multipart/form-data">
											<input type="file" name="import" id="impFile" accept=".dump.tar.gz" required>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</session>


			</section>
		</div>
	</section>

	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/conf.html" width="200" height="60" style="border:none;"></iframe>
	</div>

</body>
<script>
	function setLoader(btn) {
		btn.firstElementChild.style.display = "none";
		if (btn.childElementCount == 1) {
			loader = document.createElement("DIV");
			loader.className = "loader";
			btn.appendChild(loader);
		}
		window.location.href = btn.firstElementChild.href;
	}

	var selectionState = {};

	function openItem(id, sectionId) {
		let btn = document.getElementById(id);
		let section = document.getElementById(sectionId);
		let open = selectionState[id] ? selectionState[id] : false;
		let icon = document.querySelector("#" + id + " a i");

		if (open) {
			$("#" + sectionId).hide();
			selectionState[id] = false;
			icon.className = "fa fa-download";
			icon.style = "color: white;"
			btn.className = "btn btn-primary";
		} else {
			$("#" + sectionId).show();
			selectionState[id] = true;
			icon.className = "fa fa-times";
			icon.style = "color: black;"
			btn.className = "btn btn-light";
		}
	}

	function submitForm(id) {
		if (!!$("#" + id + " input").val()) {
			if (window.confirm("ATENÇÃO! Backups podem alterar o seu Banco de Dados quando restaurados. Ao importar esse arquivo, você aceita que ele estará disponível na lista de Backups e poderá alterar por completo o Banco de Dados. Tem certeza que deseja importar esse arquivo?"))
				$("#" + id).submit();
		} else {
			window.alert("Preencha todos os campos antes de enviar o formulário!")
		}
	}
</script>

<!-- Adiciona função de fechar mensagem e tirá-la da url -->
<script src="../geral/msg.js"></script>

</html>