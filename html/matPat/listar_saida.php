<?php
session_start();
if (!isset($_SESSION['usuario'])) {
	header("Location: ../index.php");
	exit();
}

// Carregando configuração com segurança
$config_path = "config.php";
while (!file_exists($config_path)) {
	$config_path = "../" . $config_path;
}
require_once($config_path);

// Estabelecendo conexão segura
$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (!$conexao) {
	die("Erro ao conectar ao banco de dados: " . mysqli_connect_error());
}

// Prevenindo injeção de SQL utilizando prepared statements
$id_pessoa = $_SESSION['id_pessoa'];
$stmt = $conexao->prepare("SELECT * FROM funcionario WHERE id_pessoa = ?");
$stmt->bind_param("i", $id_pessoa);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado && $resultado->num_rows > 0) {
	$id_cargo = $resultado->fetch_assoc()['id_cargo'];

	// Prevenindo injeção de SQL em permissões
	$stmt_permissao = $conexao->prepare("SELECT * FROM permissao WHERE id_cargo = ? AND id_recurso = 24");
	$stmt_permissao->bind_param("i", $id_cargo);
	$stmt_permissao->execute();
	$resultado_permissao = $stmt_permissao->get_result();

	if ($resultado_permissao && $resultado_permissao->num_rows > 0) {
		$permissao = $resultado_permissao->fetch_assoc();

		// Verificando permissões
		if ($permissao['id_acao'] < 5) {
			// Prevenindo XSS com htmlentities
			$msg = htmlentities("Você não tem as permissões necessárias para essa página.");
			header("Location: ./home.php?msg_c=" . urlencode($msg));
			exit();
		}
		$permissao = $permissao['id_acao'];
	} else {
		// Caso não tenha permissão
		$msg = htmlentities("Você não tem as permissões necessárias para essa página.");
		header("Location: ./home.php?msg_c=" . urlencode($msg));
		exit();
	}
} else {
	// Caso o funcionário não seja encontrado
	$msg = htmlentities("Você não tem as permissões necessárias para essa página.");
	header("Location: ./home.php?msg_c=" . urlencode($msg));
	exit();
}

// Incluindo arquivo de personalização de display
require_once "personalizacao_display.php";
?>
<!doctype html>
<html class="fixed">

<head>
	<?php
	include_once '../dao/Conexao.php';
	include_once '../dao/SaidaDAO.php';

	// Validando sessão de saída
	if (!isset($_SESSION['saida'])) {
		header('Location: ../controle/control.php?metodo=listarTodos&nomeClasse=SaidaControle&nextPage=../html/listar_saida.php');
		exit();
	}
	if (isset($_SESSION['saida'])) {
		$saida = $_SESSION['saida'];
		unset($_SESSION['saida']);
	}
	?>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Informações</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="../assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />

	<!-- Specific Page Vendor CSS -->
	<link rel="stylesheet" href="../assets/vendor/select2/select2.css" />
	<link rel="stylesheet" href="../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

	<!-- Theme CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="../assets/vendor/modernizr/modernizr.js"></script>
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">

	<!-- Vendor -->
	<script src="../assets/vendor/jquery/jquery.min.js"></script>
	<script src="../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="../assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="../assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="../assets/vendor/magnific-popup/magnific-popup.js"></script>
	<script src="../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

	<!-- javascript functions -->
	<script src="../Functions/onlyNumbers.js"></script>
	<script src="../Functions/onlyChars.js"></script>
	<script src="../Functions/enviar_dados.js"></script>
	<script src="../Functions/mascara.js"></script>

	<!-- jquery functions -->
	<script>
		function listarId(id) {
			window.location.replace('../controle/control.php?metodo=listarId&nomeClasse=IsaidaControle&nextPage=../html/listar_Isaida.php&id_saida=' + id);
		}
	</script>
	<script>
		$(function() {
			var saida = <?php
						echo $saida;
						?>;

			$.each(saida, function(i, item) {

				$('#tabela')
					.append($('<tr />')
						.attr('onclick', 'listarId("' + item.id_saida + '")')
						.append($('<td />')
							.text(item.nome_destino))
						.append($('<td />')
							.text(item.descricao_almoxarifado))
						.append($('<td />')
							.text(item.descricao))
						.append($('<td />')
							.text(item.nome))
						.append($('<td />')
							.text(item.valor_total))
						.append($('<td />')
							.text(item.data))
						.append($('<td />')
							.text(item.hora)
						)
					)
			})
		});
		$(function() {
			$("#header").load("header.php");
			$(".menuu").load("menu.php");
		});
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
					<h2>Informações</h2>

					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Informações Saida</span></li>
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
						<h2 class="panel-title">Saida</h2>
					</header>
					<!-- start: page -->
					
						<div class="panel-body">
							<table class="table table-bordered table-striped mb-none" id="datatable-default">
								<thead>
									<tr>
										<th>Destino</th>
										<th>Almoxarifado</th>
										<th>Tipo</th>
										<th>Responsável</th>
										<th>Valor Total</th>
										<th>Data</th>
										<th>Hora</th>
									</tr>
								</thead>
								<tbody id="tabela">	
								</tbody>
							</table>
						</div><br>
				
<!-- verificar -->
				</section>
<!-- verificar -->
			</section>
		</div>
	</section>
	<!-- end: page -->

	<!-- Specific Page Vendor -->
	<script src="../assets/vendor/select2/select2.js"></script>
	<script src="../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
	<script src="../assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
	<script src="../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>

	<!-- Theme Base, Components and Settings -->
	<script src="../assets/javascripts/theme.js"></script>

	<!-- Theme Custom -->
	<script src="../assets/javascripts/theme.custom.js"></script>

	<!-- Theme Initialization Files -->
	<script src="../assets/javascripts/theme.init.js"></script>

	<!-- Examples -->
	<script src="../assets/javascripts/tables/examples.datatables.default.js"></script>
	<script src="../assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
	<script src="../assets/javascripts/tables/examples.datatables.tabletools.js"></script>
	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/pet.html" width="200" height="60" style="border:none;"></iframe>
	</div>
</body>

</html>