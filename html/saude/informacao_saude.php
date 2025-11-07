<?php
/*
    ini_set('display_errors', 1);
    ini_set('display_startup_erros', 1);
    error_reporting(E_ALL);
*/

session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: ../index.php");
	exit();
}

if (!isset($_SESSION['saude'])) {
	header('Location: ../../controle/control.php?metodo=listarTodos&nomeClasse=SaudeControle&nextPage=../html/saude/informacao_saude.php');
	exit();
}

$config_path = __DIR__ . "/../../config.php";

if (file_exists($config_path)) {
	require_once($config_path);
} else {
	die("Erro crítico: Arquivo de configuração não encontrado.");
}

$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if (mysqli_connect_errno()) {
	die("Falha na conexão com o banco de dados: " . mysqli_connect_error());
}

if (!isset($_SESSION['id_pessoa']) || !is_numeric($_SESSION['id_pessoa'])) {
	$msg = "Sessão inválida ou ID de pessoa não encontrado.";
	header("Location: ../home.php?msg_c=$msg");
	exit();
}
$id_pessoa = (int) $_SESSION['id_pessoa'];

$id_cargo = null;
$permissao = 1;

$sql1 = "SELECT id_cargo FROM funcionario WHERE id_pessoa = ?";
$stmt1 = mysqli_prepare($conexao, $sql1);

if ($stmt1) {
	mysqli_stmt_bind_param($stmt1, "i", $id_pessoa);
	mysqli_stmt_execute($stmt1);
	$resultado1 = mysqli_stmt_get_result($stmt1);

	if ($resultado1 && mysqli_num_rows($resultado1) > 0) {
		$row = mysqli_fetch_assoc($resultado1);
		$id_cargo = (int) $row['id_cargo'];
	}
	mysqli_stmt_close($stmt1);
}

if (!is_null($id_cargo)) {

	$sql2 = "SELECT p.id_acao 
                 FROM permissao p 
                 JOIN acao a ON (p.id_acao = a.id_acao) 
                 JOIN recurso r ON (p.id_recurso = r.id_recurso) 
                 WHERE p.id_cargo = ? 
                   AND a.descricao = 'LER, GRAVAR E EXECUTAR' 
                   AND r.descricao = 'Módulo Saúde'";

	$stmt2 = mysqli_prepare($conexao, $sql2);

	if ($stmt2) {
		mysqli_stmt_bind_param($stmt2, "i", $id_cargo);
		mysqli_stmt_execute($stmt2);
		$resultado2 = mysqli_stmt_get_result($stmt2);

		if ($resultado2 && mysqli_num_rows($resultado2) > 0) {
			$permissao_row = mysqli_fetch_assoc($resultado2);

			if ($permissao_row['id_acao'] < 5) {
				$msg = "Você não tem as permissões necessárias para essa página.";
				header("Location: ../home.php?msg_c=$msg");
				exit();
			} else {
				$permissao = (int) $permissao_row['id_acao'];
			}
		} else {
			$msg = "Você não tem as permissões necessárias para essa página.";
			header("Location: ../home.php?msg_c=$msg");
			exit();
		}
		mysqli_stmt_close($stmt2);
	} else {
		$msg = "Erro ao verificar permissões.";
		header("Location: ../home.php?msg_c=$msg");
		exit();
	}
} else {
	$msg = "Funcionário não encontrado ou sem cargo associado.";
	header("Location: ../../home.php?msg_c=$msg");
	exit();
}

mysqli_close($conexao);

require_once __DIR__ . "/../personalizacao_display.php";

?>

<!doctype html>
<html class="fixed">

<head>

	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Informações</title>

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
		function irPg(id_fichamedica) {
			localStorage.setItem('id_fichamedica', id_fichamedica);
			window.location.href = "./profile_paciente.php?id_fichamedica=" + id_fichamedica;
		}
		$(function() {
			$(function() {
				$('.tabble-row').on("click", function(evt) {
					let teste = $(this).attr('id')
					//window.open("profile_paciente.php?id_fichamedica="+teste,"_blank");
					localStorage.setItem('id_ficha_medica', teste)
					console.log("id aqui", teste);
					window.location.href = "profile_paciente.php?id_fichamedica=" + teste;

				});
			});

			if (localStorage.getItem("id_ficha_medica") && localStorage.getItem("id_ficha_medica") !== 'null') {

				window.location.href = "profile_paciente.php?id_fichamedica=" + localStorage.getItem("id_ficha_medica");
			}
			var pacientes = <?php echo $_SESSION['saude']; ?>;
			<?php unset($_SESSION['saude']); ?>;
			$.each(pacientes, function(i, item) {
				$("#tabela")
					.append($("<tr <tr onclick=irPg('" + item.id_fichamedica + "') id='" + item.id_fichamedica + "' class='tabble-row'>")
						.append($("<td>")
							.text(item.nome + ' ' + item.sobrenome))
						.append($("<td />")
							.html('<i class="glyphicon glyphicon-pencil"></i>')));
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
				<header class="page-header">
					<h2>Informações</h2>

					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li><a href="../index.php"> <i class="fa fa-home"></i>
								</a></li>
							<li><span>Informações Paciente</span></li>
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

						<h2 class="panel-title">Pacientes</h2>
					</header>
					<div class="panel-body">
						<table class="table table-bordered table-striped mb-none"
							id="datatable-default">
							<thead>
								<tr>
									<th>Nome</th>
									<th>Ação</th>
								</tr>
							</thead>
							<tbody id="tabela">

							</tbody>
						</table>
					</div>
					<br>
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

				<div align="right">
					<iframe src="https://www.wegia.org/software/footer/saude.html" width="200" height="60" style="border:none;"></iframe>
				</div>
</body>

</html>