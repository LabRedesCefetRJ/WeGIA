<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE)
	session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: ../../index.php");
	exit();
} else {
	session_regenerate_id();
}

if (!isset($_SESSION['escala_quadro_horario'])) {
	header('Location: ../../controle/control.php?metodo=listarEscala&nomeClasse=QuadroHorarioControle&nextPage=../html/quadro_horario/' . basename(__FILE__));
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

permissao($_SESSION['id_pessoa'], 11, 5);

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once "../personalizacao_display.php";

// Funções de mensagem
require_once "../geral/msg.php";

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
?>
<!doctype html>
<html class="fixed">

<head>

	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Listar Escalas</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

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
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">

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
	<script src="../../Functions/onlyNumbers.js"></script>
	<script src="../../Functions/onlyChars.js"></script>
	<script src="../../Functions/enviar_dados.js"></script>
	<script src="../../Functions/mascara.js"></script>
	<!-- jquery functions -->
	<script>
		function removerEscala(id) {
			fetch("../../controle/control.php", {
					method: "POST",
					headers: {
						"Content-Type": "application/x-www-form-urlencoded"
					},
					body: new URLSearchParams({
						metodo: "removerEscala",
						nomeClasse: "QuadroHorarioControle",
						csrf_token: <?= json_encode(htmlspecialchars(Csrf::generateToken(), ENT_QUOTES, 'UTF-8')) ?>,
						id: id
					})
				})
				.then(res => res.text())
				.then(resposta => {
					window.location.href = "../quadro_horario/listar_escala.php";
				})
				.catch(err => console.error(err));
		}

		$(function() {
			var tipo_quadro_horario = <?= $_SESSION['escala_quadro_horario']; ?>;
			<?php unset($_SESSION['escala_quadro_horario']); ?>

			console.log(tipo_quadro_horario);
			$.each(tipo_quadro_horario, function(i, item) {
				$("#tabela")
					.append($("<tr>")
						.attr("onclick", "removerEscala('" + item.id_escala + "')")
						.attr("class", "teste")
						.append($("<td>")
							.text(item.descricao))
						.append($('<td />')
							.attr('onclick', 'removerEscala("' + item.id_escala + '")')
							.html('<i class="fas fa-trash-alt" title="Excluir"></i>')));
			});
		});
		$(function() {
			$("#header").load("../header.php");
			$(".menuu").load("../menu.php");
		});
	</script>
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
				<?php sessionMsg(); ?>
				<header class="page-header">
					<h2>Listar Escalas</h2>

					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Listar Escalas</span></li>
						</ol>

						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->

				</header>

				<!-- start: page -->
				<section class="panel">
					<header class="panel-heading">
						<div class="panel-actions">
							<a href="#" class="fa fa-caret-down"></a>
						</div>

						<h2 class="panel-title">Escalas</h2>
					</header>
					<div class="panel-body">
						<table class="table table-bordered table-striped mb-none" id="datatable-default">
							<thead>
								<tr>
									<th width="85%">Escala</th>
									<th>Ação</th>
								</tr>
							</thead>
							<tbody id="tabela">

							</tbody>
						</table>
					</div><br>
				</section>
				<!-- end: page -->

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

				<!-- Complemento opcional às funções de mensagem -->
				<script src="../geral/msg.js"></script>
</body>

</html>