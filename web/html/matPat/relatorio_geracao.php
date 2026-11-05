<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE)
	session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: ../index.php");
	exit();
} else {
	session_regenerate_id();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

permissao($_SESSION['id_pessoa'], 25, 5);

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";

require_once ROOT . "/dao/Conexao.php";

require_once ROOT . "/html/relatorios/Relatorio_item.php";

$tipoMedia = $_POST['tipo_media'] ?? 'dia';
$modoRequisicao = $_POST['modo_requisicao'] ?? 'movimentados';

$mostrarZerados = isset($_POST['mostrarZerados']) && $_POST['mostrarZerados'] === 'on';

try {
    $tiposEntrada = $_POST['tipo_relatorio'] === 'entrada'
        ? Item::normalizarTipos($_POST['tiposEntrada'] ?? []) : [];
    $tiposSaida = $_POST['tipo_relatorio'] === 'saida'
        ? Item::normalizarTipos($_POST['tiposSaida'] ?? []) : [];
} catch (InvalidArgumentException $erro) {
    http_response_code(400);
    exit('Seleção de tipos inválida. Volte ao formulário e selecione os tipos novamente.');
}

// Nas movimentações, a lista de tipos é o único filtro de tipo.
$tipoFiltro = in_array($_POST['tipo_relatorio'], ['requisicao', 'estoque', 'itens_compra'], true)
    ? ($_POST['categoria_produto'] ?? null) : null;

$o_d = null;
if ($_POST['tipo_relatorio'] == 'entrada') {
	$o_d = $_POST['origem'];
} else if ($_POST['tipo_relatorio'] == 'saida') {
	$o_d = $_POST['destino'];
}
$post = [
	$_POST['tipo_relatorio'] != '' ? $_POST['tipo_relatorio'] : null,
	$o_d != '' ? $o_d : null,
	$tipoFiltro ?: null,
	$_POST['responsavel'] != '' ? $_POST['responsavel'] : null,
	[
		'inicio' => $_POST['data_inicio'] != '' ? $_POST['data_inicio'] : null,
		'fim' => $_POST['data_fim'] != '' ? $_POST['data_fim'] : null
	],
	$_POST['almoxarifado'] != '' ? $_POST['almoxarifado'] : null,
	$mostrarZerados
];

if(($_POST['tipo_relatorio'] ?? null) === 'itens_compra' && empty($_POST['almoxarifado'])) {
	echo "<script>alert('Por favor, selecione um almoxarifado para gerar o relatório de itens de compra.'); window.history.back();</script>";
	exit();
}

$item = new Item(
	$_POST['tipo_relatorio'],
	$o_d,
	$tipoFiltro,
	$tiposEntrada,
    $tiposSaida,
	$_POST['responsavel'],
	[
		'inicio' => $_POST['data_inicio'],
		'fim' => $_POST['data_fim']
	],
	$_POST['almoxarifado'],
	$mostrarZerados,
	$tipoMedia,
	$modoRequisicao
);


// Reutiliza a logo da instalação, sem depender de uma imagem fixa.
ob_start();
display_campo('Logo', 'file');
$logoCadastrada = trim(ob_get_clean());
$logoInstituicao = '';
if (preg_match('~^data:image(?:/[a-z0-9.+-]+)?;base64,(.+)$~is', $logoCadastrada, $partesLogo)) {
    $imagemLogo = base64_decode($partesLogo[1], true);
    $dadosLogo = $imagemLogo !== false ? @getimagesizefromstring($imagemLogo) : false;
    if ($dadosLogo && strpos($dadosLogo['mime'], 'image/') === 0) {
        $logoInstituicao = 'data:' . $dadosLogo['mime'] . ';base64,' . base64_encode($imagemLogo);
    }
}

function quickQuery($query, $parametro, $column)
{
	$pdo = Conexao::connect();
	$stmt = $pdo->prepare($query);
	$chave = array_key_first($parametro);
	$valor = $parametro[$chave];
	$stmt->bindValue($chave, $valor);
	$stmt->execute();
	$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $res[0][$column] ?? null;
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


	<!-- javascript tab management script -->

	<style>
		.folha-requisicao {
    		width: 100%;
    		max-width: 28.2cm;
    		margin: 0 auto 20px auto;
    		page-break-after: always;
    		font-family: Arial, sans-serif;
		}

		.cabecalho-requisicao {
    		text-align: center;
    		font-size: 20px;
    		font-weight: bold;
    		margin-bottom: 2px;
		}

		.instrucao-requisicao {
    		text-align: center;
    		font-size: 9px;
    		margin-bottom: 8px;
		}

		.tabela-requisicao {
    		width: 100%;
    		border-collapse: collapse;
    		table-layout: fixed;
		}

		.tabela-requisicao td {
    		border: 1px solid #999;
    		padding: 2px;
    		vertical-align: top;
		}

		.produto-requisicao {
    		width: 9%;
    		font-size: 10px;
    		font-weight: bold;
    		vertical-align: middle !important;
    		overflow-wrap: break-word;
    		word-break: normal;
		}

		.produto-requisicao-manual {
    		padding: 4px !important;
    		vertical-align: middle !important;
		}

		.texto-produto-manual {
    		font-size: 9px;
    		font-weight: bold;
    		line-height: 1.1;
    		margin-bottom: 8px;
		}

		.linha-produto-manual {
    		width: 100%;
    		height: 10px;
    		border-bottom: 1px solid #555;
		}

		.dia-requisicao {
    		width: 5.6875%;
    		height: 0.78cm;
    		font-size: 8px;
    		font-weight: bold;
    		text-align: left;
    		vertical-align: top;
		}

		.dia-azul {
    		background-color: #eaf5ff;
		}

		.total-requisicao {
    		background-color: #f2f2f2;
		}

		.rodape-requisicao {
    		text-align: right;
    		font-size: 8px;
    		color: #999;
    		margin-top: 4px;
		}

		@media print {
   			@page {
        		size: A4 landscape;
        		margin: 0.35cm;
    		}

    		.print-hide,
    		.descricao,
    		h4,
    		.print-button {
        		display: none !important;
    		}

    		body {
        		margin: 0;
        		padding: 0;
    		}

    		.content-body,
    		.inner-wrapper,
    		.tab-content {
        		margin: 0 !important;
        		padding: 0 !important;
        		width: 100% !important;
    		}

    		.folha-requisicao {
        		width: 28.2cm;
        		max-width: 28.2cm;
        		margin: 0 auto;
        		page-break-after: always;
    		}

    		.tabela-requisicao {
        		width: 28.2cm;
    		}

    		.produto-requisicao {
        		width: 2.43cm;
    		}

    		.dia-requisicao {
        		width: 1.61cm;
    		}
		}

        .logo-relatorio { display: none; }
        @media print {
            html, body, body > .body, body > .body > .inner-wrapper,
            body > .body > .inner-wrapper > .content-body {
                display: block !important;
                position: static !important;
                width: auto !important;
                height: auto !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }
            .tab-content > .descricao { display: block !important; padding: 0 0 10px; }
            .logo-relatorio { display: block; margin-bottom: 8px; }
            .logo-relatorio img { object-fit: contain; }
            .descricao { break-inside: avoid; }
            .descricao h3 { margin: 0 0 8px; }
            .print-hide, .print-button { display: none !important; }
            .table { width: 100%; font-size: 9pt; }
            .table thead { display: table-header-group; }
            .table tr { break-inside: avoid; }
            .badge { background: transparent !important; color: #000 !important; border: 0; padding: 0; white-space: normal; }
            .table-info { font-weight: bold; }
            .folha-requisicao:last-child { page-break-after: auto; break-after: auto; }
        }
</style>

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
                    <?php if ($logoInstituicao !== ''): ?>
                        <div class="logo-relatorio">
                            <img src="<?= htmlspecialchars($logoInstituicao, ENT_QUOTES, 'UTF-8') ?>" alt="Logo da instituição" width="76" height="79">
                        </div>
                    <?php endif; ?>
					<div class="descricao">
						<p>
							<li>
								<?php
								if (isset($post[0])) {

									if (DEBUG) {
										echo '<pre class="print-hide">';
										var_dump("GET", $_GET);
										var_dump("POST", $_POST, $post);
										var_dump("INSTANCIAS", $item, $item->hasValue());
										echo "</pre>";
									}

									$tipoRelatorio = htmlspecialchars($post[0]) == 'itens_compra' ? 'itens de compra' : htmlspecialchars($post[0]);
									echo ("<h3>Relatório de " . $tipoRelatorio . "</h3>");

									if ($post[0] == 'entrada') {
										if (isset($post[1])) {
											$origem = quickQuery("select nome_origem from origem where id_origem =  :id_origem;", [':id_origem' => $post[1]], "nome_origem");
											echo ("<ul>Origem: " . htmlspecialchars($origem) . "</ul>");
										} else {
											echo ("<ul>Origem: Todas</ul>");
										}
										if ($tiposEntrada) {
                                        $nomesTipos = [];
                                        foreach ($tiposEntrada as $idTipo) {
                                            $nomesTipos[] = quickQuery("SELECT descricao FROM tipo_entrada WHERE id_tipo = :id", [':id' => $idTipo], 'descricao') ?? ('Tipo #' . $idTipo);
                                        }
                                        echo '<ul>Tipos: ' . htmlspecialchars(implode(', ', $nomesTipos), ENT_QUOTES, 'UTF-8') . '</ul>';
                                    } else {
                                        echo '<ul>Tipos: Todos</ul>';
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
											$destino = quickQuery("select nome_destino from destino where id_destino =  :id_destino;", [':id_destino' => $post[1]], "nome_destino");
											echo ("<ul>Destino: " . htmlspecialchars($destino) . "</ul>");
										} else {
											echo ("<ul>Destino: Todos</ul>");
										}
										if ($tiposSaida) {
                                        $nomesTipos = [];
                                        foreach ($tiposSaida as $idTipo) {
                                            $nomesTipos[] = quickQuery("SELECT descricao FROM tipo_saida WHERE id_tipo = :id", [':id' => $idTipo], 'descricao') ?? ('Tipo #' . $idTipo);
                                        }
                                        echo '<ul>Tipos: ' . htmlspecialchars(implode(', ', $nomesTipos), ENT_QUOTES, 'UTF-8') . '</ul>';
                                    } else {
                                        echo '<ul>Tipos: Todos</ul>';
                                    }
                                    if (isset($post[3])) {
											$responsavel = quickQuery("select nome from pessoa where id_pessoa = :id_pessoa;", [':id_pessoa' => $post[3]], "nome");
											echo ("<ul>Responsável: " . htmlspecialchars($responsavel) . "</ul>");
										} else {
											echo ("<ul>Responsável: Todos</ul>");
										}
									}

									if ($post[0] !== 'estoque' && empty($post[4]['inicio']) && empty($post[4]['fim'])) {
                                        echo '<ul>Período: Todo o período</ul>';
                                    }
                                    if (in_array($post[0], ['saida', 'itens_compra'], true)) {
                                        $medias = ['dia' => 'Por dia', 'mes' => 'Por mês', 'ano' => 'Por ano'];
                                        echo '<ul>Média de saída: ' . htmlspecialchars($medias[$tipoMedia] ?? 'Por dia', ENT_QUOTES, 'UTF-8') . '</ul>';
                                    }
                                    $modeloBrasileiro = 'd/m/Y';

									if (isset($post[4]['inicio'])) {
										$dataInicio = $post[4]['inicio'];
										$dataInicioFormatada = date_format(date_create($dataInicio), $modeloBrasileiro);
										echo ("<ul>A partir de: " . htmlspecialchars($dataInicioFormatada) . "</ul>");
									}

									if (isset($post[4]['fim'])) {
										$dataFim = $post[4]['fim'];
										$dataFimFormatada = date_format(date_create($dataFim), $modeloBrasileiro);
										echo ("<ul>Até: " . htmlspecialchars($dataFimFormatada) . "</ul>");
									}

									if (!empty($_POST['data_inicio']) && !empty($_POST['data_fim']) && $_POST['data_inicio'] > $_POST['data_fim']) {
										echo ("<ul style='color: red;'>Aviso: A data de início é maior que a data de fim.</ul>");
										exit();
									}

									if (isset($post[5])) {
										$almoxarifado = quickQuery("select descricao_almoxarifado from almoxarifado where id_almoxarifado = :id_almoxarifado;", [':id_almoxarifado' => $post[5]], "descricao_almoxarifado");
										echo ("<ul>Almoxarifado: " . htmlspecialchars($almoxarifado) . "</ul>"); 
									} else {
										echo ("<ul>Almoxarifado: Todos</ul>");
									}

									if ($post[0] === 'requisicao') {
										$modelosRequisicao = [
											'movimentados' => 'Produtos mais movimentados',
											'completo' => 'Todos os produtos em estoque'
										];

										echo (
											"<ul>Modelo da requisição: " .
											htmlspecialchars($modelosRequisicao[$modoRequisicao] ?? 'Produtos mais movimentados') .
											"</ul>"
										);

										if ($modoRequisicao === 'movimentados') {
											echo ("<ul>Critério: produtos com saída total igual ou superior a 10 no período selecionado.</ul>");
										}
									} else {
										if ($post[0] === 'estoque') {
											if ($post[6]) {
												echo ("<ul>Mostrando produtos fora de estoque</ul>");
											} else {
												echo ("<ul>Ocultando produtos fora de estoque</ul>");
											}
										}
									}

									if($post[0] == 'estoque' || $post[0] == 'requisicao' || $post[0] == 'itens_compra') {
										if (isset($post[2])) {
											$categoriaProduto = quickQuery("select descricao_categoria from categoria_produto where id_categoria_produto = :id_categoria_produto;", [':id_categoria_produto' => $post[2]], "descricao_categoria");
											echo ("<ul>Categoria de Produto: " . htmlspecialchars($categoriaProduto) . "</ul>");
										} else {
											echo ("<ul>Categoria de Produto: Todos</ul>");
										}
									}
								}
								?>
							</li>
						</p>
						<button style="float: right;" class="mb-xs mt-xs mr-xs btn btn-default print-button" onclick="window.print();">Imprimir</button>
					</div>
					<?php if ($_POST['tipo_relatorio'] == 'requisicao') { ?>

    					<?php $item->displayRequisicao(); ?>

					<?php } else { ?>

					<h4>Resultado</h4>

					<table class="table table-striped">
						<thead class="thead-dark">
							<tr>
								<th scope="col" width="11%">Quantidade</th>
								<?php 
								if($_POST['tipo_relatorio'] == 'itens_compra'){
									echo ('<th scope="col" width="14%">Quantidade mínima</th>');
								}
								if($_POST['tipo_relatorio'] == 'saida' || $_POST['tipo_relatorio'] == 'itens_compra') {
									$labelMedia = [
										'dia' => 'Média por dia',
										'mes' => 'Média por mês',
										'ano' => 'Média por ano'
									];

									echo ('<th scope="col" width="14%">' . htmlspecialchars($labelMedia[$tipoMedia] ?? 'Média por dia') . '</th>');
								}?>
								<th scope="col">Descrição</th>
								<?php 
								if ($_POST['tipo_relatorio'] != 'estoque' && $_POST['tipo_relatorio'] != 'requisicao' && $_POST['tipo_relatorio'] != 'itens_compra') {
									echo ('<th scope="col" width="12%">Tipo</th>');
									echo ('<th scope="col" width="12%">Data de Registro</th>');
									echo ('<th scope="col" width="12%">Valor Unitário</th>');
								} else {
									echo ('<th scope="col" width="14%">Preço Médio</th>');
								} 	
								?>
								<th scope="col" width="14%" class="tot">Tipo de Unidade</th>
								<th scope="col" width="14%" class="tot">Total</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$item->display();
							?>
						</tbody>
					</table>
					<?php } ?>
				</div>
				<!--end: page-->
			</section>
		</div>
	</section>
	<div align="right" class="print-hide">
		<iframe src="https://www.wegia.org/software/footer/matPat.html" width="200" height="60" style="border:none;"></iframe>
	</div>
</body>
<script>
</script>

</html>
