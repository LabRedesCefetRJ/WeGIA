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

require_once ROOT . '/html/relatorios/RelatorioMatPat.php';

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

	<link rel="stylesheet" href="<?= WWW ?>css/relatorio-matpat.css">
<script src="<?= WWW ?>assets/javascripts/relatorio-matpat.js" defer></script>
</head>

<body class="relatorio-matpat relatorio-pagina-paisagem">
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

					<?php
                    $filtros = [];
                    if (in_array($post[0], ['entrada', 'saida'], true)) {
                        $entrada = $post[0] === 'entrada';
                        $filtros[$entrada ? 'Origem' : 'Destino'] = $post[1]
                            ? ($entrada
                                ? quickQuery('SELECT nome_origem FROM origem WHERE id_origem = :id', [':id' => $post[1]], 'nome_origem')
                                : quickQuery('SELECT nome_destino FROM destino WHERE id_destino = :id', [':id' => $post[1]], 'nome_destino'))
                            : ($entrada ? 'Todas' : 'Todos');
                        $nomesTipos = [];
                        foreach ($entrada ? $tiposEntrada : $tiposSaida as $idTipo) {
                            $nomesTipos[] = quickQuery('SELECT descricao FROM ' . ($entrada ? 'tipo_entrada' : 'tipo_saida') . ' WHERE id_tipo = :id', [':id' => $idTipo], 'descricao') ?? ('Tipo #' . $idTipo);
                        }
                        $filtros[$entrada ? 'Tipos de entrada' : 'Tipos de saída'] = $nomesTipos ? implode(', ', $nomesTipos) : 'Todos';
                        $filtros['Responsável'] = $post[3]
                            ? quickQuery('SELECT nome FROM pessoa WHERE id_pessoa = :id', [':id' => $post[3]], 'nome') : 'Todos';
                    }
                    if ($post[0] !== 'estoque') {
                        if (!empty($post[4]['inicio']) && !empty($post[4]['fim']) && $post[4]['inicio'] > $post[4]['fim']) {
                            exit('Aviso: A data de início é maior que a data de fim.');
                        }
                        $filtros['Período'] = RelatorioMatPat::periodo($post[4]['inicio'], $post[4]['fim']);
                    }
                    $filtros['Almoxarifado'] = $post[5]
                        ? quickQuery('SELECT descricao_almoxarifado FROM almoxarifado WHERE id_almoxarifado = :id', [':id' => $post[5]], 'descricao_almoxarifado') : 'Todos';
                    if (in_array($post[0], ['estoque', 'requisicao', 'itens_compra'], true)) {
                        $filtros['Categoria de produto'] = $post[2]
                            ? quickQuery('SELECT descricao_categoria FROM categoria_produto WHERE id_categoria_produto = :id', [':id' => $post[2]], 'descricao_categoria') : 'Todas';
                    }
                    if ($post[0] === 'estoque') {
                        $filtros['Produtos fora de estoque'] = $post[6] ? 'Mostrar' : 'Ocultar';
                    }
                    if (in_array($post[0], ['saida', 'itens_compra'], true)) {
                        $filtros['Média de saída'] = ['dia' => 'Por dia', 'mes' => 'Por mês', 'ano' => 'Por ano'][$tipoMedia] ?? 'Por dia';
                    }
                    if ($post[0] === 'requisicao') {
                        $filtros['Modelo da requisição'] = $modoRequisicao === 'completo' ? 'Todos os produtos em estoque' : 'Produtos mais movimentados';
                        if ($modoRequisicao !== 'completo') {
                            $filtros['Critério'] = 'Saída total igual ou superior a 10 no período selecionado';
                        }
                    }
                    ob_start();
                    ?>
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
                    <?php
                    $corpoRelatorio = ob_get_clean();
                    $titulos = [
                        'entrada' => 'Relatório de Entradas', 'saida' => 'Relatório de Saídas',
                        'estoque' => 'Relatório de Estoque', 'itens_compra' => 'Relatório de Itens de Compra',
                        'requisicao' => 'Ficha de Requisição',
                    ];
                    $relatorio = new RelatorioMatPat($filtros, $corpoRelatorio, $titulos[$post[0]] ?? 'Relatório', $post[0]);
                    echo $relatorio->getHtml();
                    ?>
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
