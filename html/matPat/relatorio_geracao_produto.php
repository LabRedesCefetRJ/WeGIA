<?php
session_start();
ini_set('display_errors', false);
error_reporting(E_ALL);

if (!isset($_SESSION['usuario'])) {
	header("Location: ../index.php");
}

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
			header("Location: ./home.php?msg_c=$msg");
		}
		$permissao = $permissao['id_acao'];
	} else {
		$permissao = 1;
		$msg = "Você não tem as permissões necessárias para essa página.";
		header("Location: ./home.php?msg_c=$msg");
	}
} else {
	$permissao = 1;
	$msg = "Você não tem as permissões necessárias para essa página.";
	header("Location: ./home.php?msg_c=$msg");
}
// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once "personalizacao_display.php";

require_once "../dao/Conexao.php";

function quickQuery($query, $column)
{
	$pdo = Conexao::connect();
	$res = $pdo->query($query);
	$res = $res->fetchAll(PDO::FETCH_ASSOC);
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
	<link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
	<link rel="stylesheet" href="../assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon">

	<!-- Theme CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="../assets/vendor/modernizr/modernizr.js"></script>

	<!-- Atualizacao CSS -->
	<link rel="stylesheet" href="../css/atualizacao.css" />

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

	<!-- jquery functions -->
	<script>
		document.write('<a href="' + document.referrer + '"></a>');
	</script>

	<script type="text/javascript">
		$(function() {
			$("#header").load("header.php");
			$(".menuu").load("menu.php");
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
								<a href="./home.php">
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
                                <h2>Geração de Produto</h2>		
							</li>
						</p>
						<button style="float: right;" class="mb-xs mt-xs mr-xs btn btn-default print-button" onclick="window.print();">Imprimir</button>
					</div>

					<?php
					$pdo = Conexao::connect();

					if($_SERVER["REQUEST_METHOD"] == "POST"){
						$idAlmoxarifado = isset($_POST['almoxarifado']) ? $_POST['almoxarifado'] : null;
						$idProduto = isset($_POST['produto']) ? $_POST['produto'] : null;
						$dataInicio = isset($_POST['data_inicio']) ? $_POST['data_inicio'] : null;
						$dataFim = isset($_POST['data_fim']) ? $_POST['data_fim'] : null;

						$modeloBrasileiro = 'd/m/Y';
						if(!empty($dataInicio))
							$dataInicioFormatada = date_format(date_create($dataInicio), $modeloBrasileiro);
						else
							$dataInicioFormatada = null;

						if(!empty($dataFim))
							$dataFimFormatada = date_format(date_create($dataFim), $modeloBrasileiro);
						else
							$dataFimFormatada = null;

						$query = "
							SELECT e.data
							FROM entrada e
							JOIN ientrada ie ON e.id_entrada = ie.id_entrada
							JOIN produto p ON ie.id_produto = p.id_produto
							WHERE p.id_produto = :id_produto
							AND e.id_almoxarifado = :id_almoxarifado
						";
						
						if ($dataInicio && $dataFim) {
							$query .= " AND e.data BETWEEN :data_inicio AND :data_fim";
						} elseif ($dataInicio) {
							$query .= " AND e.data >= :data_inicio";
						} elseif ($dataFim) {
							$query .= " AND e.data <= :data_fim";
						}
						
						$stmtDatas = $pdo->prepare($query);
						
						$stmtDatas->bindParam(':id_almoxarifado', $idAlmoxarifado, PDO::PARAM_INT);
						$stmtDatas->bindParam(':id_produto', $idProduto, PDO::PARAM_INT);
						
						if ($dataInicio) {
							$stmtDatas->bindParam(':data_inicio', $dataInicio, PDO::PARAM_STR);
						}
						if ($dataFim) {
							$stmtDatas->bindParam(':data_fim', $dataFim, PDO::PARAM_STR);
						}
						
						$stmtDatas->execute();						
						$resultadoDatas = $stmtDatas->fetchAll(PDO::FETCH_ASSOC);

						$datasArray = [];
						foreach ($resultadoDatas as $linha) {
							$datasArray[] = $linha['data']; 
						}

						if ($idProduto && $idAlmoxarifado) {
							$stmt = $pdo->prepare("
								SELECT 
									ientrada.id_entrada,
									entrada.data AS data_entrada,
									entrada.hora AS hora_entrada,
									almoxarifado.descricao_almoxarifado,
									produto.descricao,
									categoria_produto.descricao_categoria,
									ientrada.qtd AS quantidade_entrada,
									isaida.id_saida,
									saida.data AS data_saida,
									saida.hora AS hora_saida,
									isaida.qtd AS quantidade_saida,
									tipo_entrada.descricao AS descricao_tipo_entrada,
									tipo_saida.descricao AS descricao_tipo_saida,
									estoque.qtd AS estoque_atual,  -- Estoque atual da tabela estoque
									entrada.id_entrada AS id_entrada,  -- Adicionando id_entrada
									saida.id_saida AS id_saida  -- Adicionando id_saida
								FROM 
									ientrada
								JOIN 
									entrada ON ientrada.id_entrada = entrada.id_entrada
								JOIN 
									almoxarifado ON entrada.id_almoxarifado = almoxarifado.id_almoxarifado
								JOIN 
									produto ON ientrada.id_produto = produto.id_produto
								LEFT JOIN 
									categoria_produto ON produto.id_categoria_produto = categoria_produto.id_categoria_produto
								LEFT JOIN 
									isaida ON isaida.id_produto = ientrada.id_produto
								LEFT JOIN 
									saida ON isaida.id_saida = saida.id_saida
								LEFT JOIN 
									tipo_entrada ON entrada.id_tipo = tipo_entrada.id_tipo
								LEFT JOIN 
									tipo_saida ON saida.id_tipo = tipo_saida.id_tipo
								LEFT JOIN
									estoque ON estoque.id_produto = produto.id_produto 
									AND estoque.id_almoxarifado = almoxarifado.id_almoxarifado
								WHERE 
									almoxarifado.id_almoxarifado = :idAlmoxarifado
									AND produto.id_produto = :idProduto
						");
							$stmt->bindParam(':idProduto', $idProduto, PDO::PARAM_INT);
							$stmt->bindParam(':idAlmoxarifado', $idAlmoxarifado, PDO::PARAM_INT);
	
							$stmt->execute();
	
							$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
						}
					}
					?>

					<?php
						try {
							if ($idProduto && $idAlmoxarifado) {
								$query = "
									SELECT 
										entrada.data AS data_entrada,
										entrada.hora AS hora_entrada,
										almoxarifado.descricao_almoxarifado,
										produto.descricao,
										categoria_produto.descricao_categoria,
										ientrada.qtd AS quantidade_entrada,
										tipo_entrada.descricao AS descricao_tipo_entrada,
										estoque.qtd AS estoque_atual
									FROM 
										ientrada
									JOIN 
										entrada ON ientrada.id_entrada = entrada.id_entrada
									JOIN 
										almoxarifado ON entrada.id_almoxarifado = almoxarifado.id_almoxarifado
									JOIN 
										produto ON ientrada.id_produto = produto.id_produto
									LEFT JOIN 
										categoria_produto ON produto.id_categoria_produto = categoria_produto.id_categoria_produto
									LEFT JOIN 
										tipo_entrada ON entrada.id_tipo = tipo_entrada.id_tipo
									LEFT JOIN
										estoque ON estoque.id_produto = produto.id_produto 
										AND estoque.id_almoxarifado = almoxarifado.id_almoxarifado
									WHERE 
										almoxarifado.id_almoxarifado = :idAlmoxarifado
										AND produto.id_produto = :idProduto";
						
								if (!empty($datasArray)) {
									$placeholders = implode(', ', array_map(function ($index) {
										return ":data_entrada_$index";
									}, array_keys($datasArray)));
									$query .= " AND entrada.data IN ($placeholders)";
								}
						
								$stmtEntradas = $pdo->prepare($query);
								$stmtEntradas->bindParam(':idProduto', $idProduto, PDO::PARAM_INT);
								$stmtEntradas->bindParam(':idAlmoxarifado', $idAlmoxarifado, PDO::PARAM_INT);
						
								if (!empty($datasArray)) {
									foreach ($datasArray as $index => $data) {
										$stmtEntradas->bindValue(":data_entrada_$index", $data, PDO::PARAM_STR);
									}
								}
						
								$stmtEntradas->execute();
						
								if ($stmtEntradas->rowCount() > 0) {
									$entradas = $stmtEntradas->fetchAll(PDO::FETCH_ASSOC);
								} else {
									$entradas = [];
								}
							}
						} catch (PDOException $e) {
							echo "Não registrado" . $e->getMessage();
						}
						
						
						try {
							$query = "
								SELECT 
									saida.data AS data_saida,
									saida.hora AS hora_saida,
									almoxarifado.descricao_almoxarifado,
									produto.descricao,
									categoria_produto.descricao_categoria,
									isaida.qtd AS quantidade_saida,
									tipo_saida.descricao AS descricao_tipo_saida
								FROM 
									isaida
								JOIN 
									saida ON isaida.id_saida = saida.id_saida
								JOIN 
									almoxarifado ON saida.id_almoxarifado = almoxarifado.id_almoxarifado
								JOIN 
									produto ON isaida.id_produto = produto.id_produto
								LEFT JOIN 
									categoria_produto ON produto.id_categoria_produto = categoria_produto.id_categoria_produto
								LEFT JOIN 
									tipo_saida ON saida.id_tipo = tipo_saida.id_tipo
								WHERE 
									almoxarifado.id_almoxarifado = :idAlmoxarifado
									AND produto.id_produto = :idProduto";
						
							if (!empty($datasArray)) {
								$placeholders = implode(', ', array_map(function ($index) {
									return ":data_saida_$index";
								}, array_keys($datasArray)));
								$query .= " AND saida.data IN ($placeholders)";
							}
						
							$stmtSaidas = $pdo->prepare($query);
							$stmtSaidas->bindParam(':idProduto', $idProduto, PDO::PARAM_INT);
							$stmtSaidas->bindParam(':idAlmoxarifado', $idAlmoxarifado, PDO::PARAM_INT);
						
							if (!empty($datasArray)) {
								foreach ($datasArray as $index => $data) {
									$stmtSaidas->bindValue(":data_saida_$index", $data, PDO::PARAM_STR);
								}
							}
						
							$stmtSaidas->execute();
						
							if ($stmtSaidas->rowCount() > 0) {
								$saidas = $stmtSaidas->fetchAll(PDO::FETCH_ASSOC);
							} else {
								$saidas = [];
							}
						} catch (PDOException $e) {
							echo "Não registrado" . $e->getMessage();
						}
						
					?>

					<table class="table table-striped">
						<thead class="thead-dark">
							<tr>
								<th scope="col" colspan="4" style="font-size: large;">INFORMAÇÕES DO PRODUTO</th>        
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>PRODUTO: <?php echo !empty($entradas[0]['descricao']) ? htmlspecialchars($entradas[0]['descricao']) : 'Não registrado'; ?></td>        
							</tr>
							<tr>
								<td>ALMOXARIFADO: <?php echo !empty($entradas[0]['descricao_almoxarifado']) ? htmlspecialchars($entradas[0]['descricao_almoxarifado']) : 'Não registrado'; ?></td>
							</tr>
							<tr>
								<td>CATEGORIA: <?php echo !empty($entradas[0]['descricao_categoria']) ? htmlspecialchars($entradas[0]['descricao_categoria']) : 'Não registrado'; ?></td>
							</tr>    
							<tr>
								<td>ESTOQUE ATUAL: <?php echo !empty($entradas[0]['estoque_atual']) ? htmlspecialchars($entradas[0]['estoque_atual']) : 'Não registrado'; ?></td>
							</tr>    
							<tr>
								<td>PERÍODO DO RELATÓRIO:
									<?php
									if (empty($dataInicio) && empty($dataFim)) {
										echo "TODAS AS DATAS";
									} elseif (!empty($dataInicio) && !empty($dataFim)) {
										echo "<br> A partir do dia: " . $dataInicioFormatada . "<br>" . "Até: " . $dataFimFormatada;
									} elseif (!empty($dataInicio)) {
										echo "A partir do dia: " . $dataInicioFormatada;
									} elseif (!empty($dataFim)) {
										echo "Até: " . $dataFimFormatada;
									} else {
										echo 'Não registrado';
									}
									?>
								</td>
							</tr>
						</tbody>
					</table>

					<table class="table table-striped">
						<thead class="thead-dark">
							<tr>
								<th scope="col" colspan="4" style="font-size: large;">ENTRADAS</th>
							</tr>
							<tr>
								<th scope="col" style="font-weight: 600;">DATA</th>
								<th scope="col" style="font-weight: 600;">TIPO</th>
								<th scope="col" style="font-weight: 600;">QTD</th>
							</tr>
						</thead>
						<tbody>
							<?php 
							foreach ($entradas as $entrada) {
								$dataEntrada = !empty($entrada['data_entrada']) && !empty($entrada['hora_entrada']) ? date('d/m/Y H:i', strtotime($entrada['data_entrada'] . ' ' . $entrada['hora_entrada'])) : "Não registrado.";
							?>
								<tr>
									<td><?php echo $dataEntrada; ?></td>
									<td><?php echo !empty($entrada['descricao_tipo_entrada']) ? htmlspecialchars(trim($entrada['descricao_tipo_entrada'])) : "Não registrado."; ?></td>
									<td><?php echo !empty($entrada['quantidade_entrada']) ? htmlspecialchars(trim($entrada['quantidade_entrada'])) : "Não registrado."; ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>

					<table class="table table-striped">
						<thead class="thead-dark">
							<tr>
								<th scope="col" colspan="4" style="font-size: large;">SAÍDAS</th>
							</tr>
							<tr>
								<th scope="col" style="font-weight: 600;">DATA</th>
								<th scope="col" style="font-weight: 600;">TIPO</th>
								<th scope="col" style="font-weight: 600;">QTD</th>
							</tr>
						</thead>
						<tbody>
							<?php 
								foreach ($saidas as $saida) {
									$dataSaida = (!empty($saida['data_saida']) && !empty($saida['hora_saida'])) ? date('d/m/Y H:i', strtotime($saida['data_saida'] . ' ' . $saida['hora_saida'])) : "Não registrado.";
									$descricaoTipoSaida = !empty($saida['descricao_tipo_saida']) ? htmlspecialchars(trim($saida['descricao_tipo_saida'])) : "Não registrado.";
									$quantidadeSaida = !empty($saida['quantidade_saida']) ? htmlspecialchars(trim($saida['quantidade_saida'])) : "Não registrado.";
								?>
									<tr>
										<td><?php echo $dataSaida; ?></td>
										<td><?php echo $descricaoTipoSaida; ?></td>
										<td><?php echo $quantidadeSaida; ?></td>
									</tr>
								<?php } ?>
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