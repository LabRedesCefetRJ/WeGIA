<?php
session_start();

define("DEBUG", false);

$config_path = "config.php";
if (file_exists($config_path)) {
	require_once($config_path);
} else {
	while (true) {
		$config_path = "../" . $config_path;
		if (file_exists($config_path)) break;
	}
	require_once($config_path);
}

if(!isset($_SESSION['usuario'])){
	header ("Location:  ". WWW ."html/index.php");
	exit;
}

$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$id_pessoa = $_SESSION['id_pessoa'];
$stmt = mysqli_prepare($conexao, "SELECT id_cargo FROM funcionario WHERE id_pessoa = ?");
mysqli_stmt_bind_param($stmt, 'i', $id_pessoa);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
if (!is_null($resultado)) {
	$id_cargo = mysqli_fetch_array($resultado);
	if (!is_null($id_cargo)) {
		$id_cargo = $id_cargo['id_cargo'];
	}
	$resultado = mysqli_query($conexao, "SELECT * FROM permissao WHERE id_cargo=$id_cargo and id_recurso=25");
	if (!is_bool($resultado) and mysqli_num_rows($resultado)) {
		$permissao = mysqli_fetch_array($resultado);
		if ($permissao['id_acao'] < 5) {
			$msg = "Você não tem as permissões necessárias para essa página.";
			header("Location: " . WWW ."html/home.php?msg_c=$msg");
		}
		$permissao = $permissao['id_acao'];
	} else {
		$permissao = 1;
		$msg = "Você não tem as permissões necessárias para essa página.";
		header("Location: " . WWW ."html/home.php?msg_c=$msg");
	}
} else {
	$permissao = 1;
	$msg = "Você não tem as permissões necessárias para essa página.";
	header("Location: " . WWW ."html/home.php?msg_c=$msg");
}
// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";

require_once ROOT . "/dao/Conexao.php";

require_once ROOT . "/html/relatorios/Relatorio_item.php";

$o_d = null;
if ($_POST['tipo_relatorio'] == 'entrada') {
	$o_d = $_POST['origem'];
} else if ($_POST['tipo_relatorio'] == 'saida') {
	$o_d = $_POST['destino'];
}
$post = [
	$_POST['tipo_relatorio'] != '' ? $_POST['tipo_relatorio'] : null,
	$o_d != '' ? $o_d : null,
	$_POST['tipo'] != '' ? $_POST['tipo'] : null,
	$_POST['responsavel'] != '' ? $_POST['responsavel'] : null,
	[
		'inicio' => $_POST['data_inicio'] != '' ? $_POST['data_inicio'] : null,
		'fim' => $_POST['data_fim'] != '' ? $_POST['data_fim'] : null
	],
	$_POST['almoxarifado'] != '' ? $_POST['almoxarifado'] : null,
	$_POST['mostrarZerados'] == "on" ?? false
];

$item = new Item(
	$_POST['tipo_relatorio'],
	$o_d,
	$_POST['tipo'],
	$_POST['responsavel'],
	[
		'inicio' => $_POST['data_inicio'],
		'fim' => $_POST['data_fim']
	],
	$_POST['almoxarifado'],
	$_POST['mostrarZerados'] == "on" ?? false
);

function quickQuery($query, $parametro, $column)
{
	$pdo = Conexao::connect();
	$stmt = $pdo->prepare($query);
	$chave = array_key_first($parametro);
	$valor = $parametro[$chave];
	$stmt->bindValue($chave, $parametro);
    $stmt->execute();
	$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $res[0][$column];
}

?>
<!doctype html>
<html class="fixed">

<head>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Geração de Relatório</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Web Fonts  -->
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon">

	<!-- Theme CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="<?= WWW ?>assets/vendor/modernizr/modernizr.js"></script>

	<!-- Atualizacao CSS -->
	<link rel="stylesheet" href="<?= WWW ?>css/atualizacao.css" />

	<!-- Vendor -->
	<script src="<?= WWW ?>assets/vendor/jquery/jquery.min.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="<?= WWW ?>assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="<?= WWW ?>assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="<?= WWW ?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

	<!-- Specific Page Vendor -->
	<script src="<?= WWW ?>assets/vendor/jquery-autosize/jquery.autosize.js"></script>

	<!-- Theme Base, Components and Settings -->
	<script src="<?= WWW ?>assets/javascripts/theme.js"></script>

	<!-- Theme Custom -->
	<script src="<?= WWW ?>assets/javascripts/theme.custom.js"></script>

	<!-- Theme Initialization Files -->
	<script src="<?= WWW ?>assets/javascripts/theme.init.js"></script>

	<!-- javascript functions -->
	<script
		src="<?= WWW ?>Functions/onlyNumbers.js"></script>
	<script
		src="<?= WWW ?>Functions/onlyChars.js"></script>
	<script
		src="<?= WWW ?>Functions/mascara.js"></script>

	<!-- jquery functions -->
	<script>
		document.write('<a href="' + document.referrer + '"></a>');
	</script>

	<script type="text/javascript">
		$(function() {
			$("#header").load("<?= WWW ?>html/header.php");
			$(".menuu").load("<?= WWW ?>html/menu.php");
		});
	</script>

	<script>
		var homeIcon;
		// Antes do navegador imprimir a página
		window.onbeforeprint = function(event) {
			homeIcon = $('#home-icon').children();
			$('#home-icon').empty();
			$('#home-icon').append($('<span />').text("<?php display_campo("Titulo", "str"); ?>"));
		}

		// Depois do navegador imprimir ou cancelar a impressão da página
		window.onafterprint = function(event) {
			$('#home-icon').empty();
			$('#home-icon').append(homeIcon);
		};
	</script>

	<!-- javascript tab management script -->



</head>

<body>
	<section class="body">
		<div id="header" class="print-hide"></div>
		<!-- end: header -->
		<div class="inner-wrapper">
			<!-- start: sidebar -->
			<aside id="sidebar-left" class="sidebar-left menuu  print-hide"></aside>
			<!-- end: sidebar -->
			<section role="main" class="content-body">
				<header class="page-header print-hide">
					<h2>Geração de Relatório</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li id="home-icon">
								<a href="<?= WWW ?>html/home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Geração de Relatório</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>
				<!--start: page-->
				<div class="tab-content">
					<div class="descricao">
						<p>
							<li>
								<?php
								if (isset($post[0])) {

									if (DEBUG) {
										echo "<pre>";
										var_dump("GET", $_GET);
										var_dump("POST", $_POST, $post);
										var_dump("INSTANCIAS", $item, $item->hasValue());
										echo "</pre>";
									}

									echo ("<h3>Relatório de " . htmlspecialchars($post[0]) . "</h3>");

									if ($post[0] == 'entrada') {
										if (isset($post[1])) {
											$origem = quickQuery("select nome_origem from origem where id_origem =  :id_origem;", [':id_origem' => $post[1]] , "nome_origem");
											echo ("<ul>Origem: " . htmlspecialchars($origem) . "</ul>");
										} else {
											echo ("<ul>Origem: Todas</ul>");
										}
										if (isset($post[2])) {
											$tipo = quickQuery("select descricao from tipo_entrada where id_tipo = :id_tipo;", [':id_tipo' => $post[2]] , "descricao", );
											echo ("<ul>Tipo: " . htmlspecialchars($tipo) . "</ul>");
										} else {
											echo ("<ul>Tipo: Todos</ul>");
										}
										if (isset($post[3])) {
											$responsavel = quickQuery("select nome from pessoa where id_pessoa = :id_pessoa;",  [':id_pessoa' => $post[3]], "nome");
											echo ("<ul>Responsável: " . htmlspecialchars($responsavel) . "</ul>");
										} else {
											echo ("<ul>Responsável: Todos</ul>");
										}
									}

									if ($post[0] == 'saida') {
										if (isset($post[1])) {
											$destino = quickQuery("select nome_destino from destino where id_destino =  :id_destino;", [':id_destino' => $post[1]] , "nome_destino");
											echo ("<ul>Destino: " . htmlspecialchars($destino) . "</ul>");
										} else {
											echo ("<ul>Destino: Todos</ul>");
										}
										if (isset($post[2])) {
											$tipo = quickQuery("select descricao from tipo_saida where id_tipo = :id_tipo;", [':id_tipo' => $post[2]] , "descricao");
											echo ("<ul>Tipo: " . htmlspecialchars($tipo) . "</ul>");
										} else {
											echo ("<ul>Tipo: Todos</ul>");
										}
										if (isset($post[3])) {
											$responsavel = quickQuery("select nome from pessoa where id_pessoa = :id_tipo;", [':id_pessoa' => $post[3]] , "nome");
											echo ("<ul>Responsável: " . htmlspecialchars($responsavel) . "</ul>");
										} else {
											echo ("<ul>Responsável: Todos</ul>");
										}
									}

									if (isset($post[4]['inicio'])) {
										$dataInicio = $post[4]['inicio'];
										$modeloBrasileiro = 'd/m/Y';
										$dataInicioFormatada = date_format(date_create($dataInicio), $modeloBrasileiro);
										echo ("<ul>A partir de: " . htmlspecialchars($dataInicioFormatada) . "</ul>");
									}

									if (isset($post[4]['fim'])) {
										$dataFim = $post[4]['fim'];
										$dataFimFormatada = date_format(date_create($dataFim), $modeloBrasileiro);
										echo ("<ul>Até: " . htmlspecialchars($dataFimFormatada) . "</ul>");
									}

									if (isset($post[5])) {
										$almoxarifado = quickQuery("select descricao_almoxarifado from almoxarifado where id_almoxarifado = :id_almoxarifado;", [':id_almoxarifado' => $post[5]], "descricao_almoxarifado");
										echo ("<ul>Almoxarifado: " . htmlspecialchars($almoxarifado) . "</ul>");
									} else {
										echo ("<ul>Almoxarifado: Todos</ul>");
									}

									if ($post[6]) {
										echo ("<ul>Mostrando produtos fora de estoque</ul>");
									} else {
										echo ("<ul>Ocultando produtos fora de estoque</ul>");
									}
								}
								?>
							</li>
						</p>
						<button style="float: right;" class="mb-xs mt-xs mr-xs btn btn-default print-button" onclick="window.print();">Imprimir</button>
					</div>
					<h4>Resultado</h4>

					<table class="table table-striped">
						<thead class="thead-dark">
							<tr>
								<th scope="col" width="11%">Quantidade</th>
								<th scope="col">Descrição</th>
								<?php if ($_POST['tipo_relatorio'] != 'estoque') {
									echo ('<th scope="col" width="14%">Data de Registro</th>');
									echo ('<th scope="col" width="14%">Valor Unitário</th>');
								} else {
									echo ('<th scope="col" width="14%">Preço Médio</th>');
								} ?>
								<th scope="col" width="14%" class="tot">Total</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$item->display();
							?>
						</tbody>
					</table>
				</div>
				<!--end: page-->
			</section>
		</div>
	</section>
	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/matPat.html" width="200" height="60" style="border:none;"></iframe>
	</div>
</body>
<script>
</script>

</html>