<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

if (session_status() === PHP_SESSION_NONE)
	session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: ../index.php");
	exit();
} else {
	session_regenerate_id();
}

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

permissao($_SESSION['id_pessoa'], 25, 5);

// Incluindo arquivo de personalização de display
require_once ROOT . "/html/personalizacao_display.php";

if (!isset($_SESSION['dados_filtros_relatorio'])) {
	header('Location: ' . WWW . 'controle/control.php?metodo=carregarDadosRelatorio&nomeClasse=EstoqueControle');
	exit;
}

$dadosFiltros = $_SESSION['dados_filtros_relatorio'];
unset($_SESSION['dados_filtros_relatorio']);
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
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/select2/select2.css" />
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
	<link rel="stylesheet" href="../css/atualizacao.css" />

	<!-- Vendor -->
	<script src="<?= WWW ?>assets/vendor/jquery/jquery.min.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="<?= WWW ?>assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="<?= WWW ?>assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="<?= WWW ?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

	<script src="<?= WWW ?>assets/vendor/select2/select2.js"></script>

	<!-- Specific Page Vendor -->
	<script src="<?= WWW ?>assets/vendor/jquery-autosize/jquery.autosize.js"></script>

	<!-- Theme Base, Components and Settings -->
	<script src="<?= WWW ?>assets/javascripts/theme.js"></script>

	<!-- Theme Custom -->
	<script src="<?= WWW ?>assets/javascripts/theme.custom.js"></script>

	<!-- Theme Initialization Files -->
	<script src="<?= WWW ?>assets/javascripts/theme.init.js"></script>

	<!-- javascript functions -->
	<script src="<?= WWW ?>Functions/onlyNumbers.js"></script>
	<script src="<?= WWW ?>Functions/onlyChars.js"></script>
	<script src="<?= WWW ?>Functions/mascara.js"></script>

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
		#s2id_produtoSelect .select2-choice {
    		height: 46px !important;
    		line-height: 20px !important;

    		background: #e9e9ed !important;
    		border: 0 !important;
    		border-radius: 4px !important;
    		box-shadow: none !important;

    		color: #777 !important;
		}

		#s2id_produtoSelect .select2-chosen {
    		padding-top: 13px !important;
    		padding-bottom: 13px !important;
		}

		#s2id_produtoSelect .select2-arrow {
    		background: #e9e9ed !important;
    		border: 0 !important;
		}

		#s2id_produtoSelect.select2-container-active .select2-choice {
    		box-shadow: none !important;
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
					<h2>Geração de Relatório</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
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
					<div id="overview" class="tab-pane active">
						<form id="form-relatorio" class="form-horizontal" method="post" action="relatorio_geracao.php">
							<h4 class="mb-xlg">Tipo de Relatório</h4>
							<h5 class="obrig">Campos Obrigatórios(*)</h5>

							<div class="form-group">
								<label class="col-md-3 control-label" for="type">Tipo de Relatório <span class="obrig">*</span></label>
								<div class="col-md-8">
									<select name="tipo_relatorio" onchange="atualizarTipoRelatorio()" id="tipo-relat" required>
										<option value="entrada">Relatório de Entrada</option>
										<option value="estoque">Relatório de Estoque</option>
										<option value="saida">Relatório de Saída</option>
										<option value="produto">Relatório de Produtos</option>
										<option value="requisicao">Relatório de Requisição</option>
										<option value="itens_compra">Relatório de Itens de Compra</option>
										<option value="grupo">Relatório de Grupos</option>
									</select>
								</div>
							</div>

							<h4 class="mb-xlg" id="param-relat">Parâmetros do relatório</h4>

							<div class="form-group" id="per" style="text-align: center;">
								<button type="button" id="btn-7dias" class="btn btn-primary" style="width: fit-content;" onclick="definirPeriodo(this, '7dias')">Últimos 7 dias</button>
								<button type="button" id="btn-30dias" class="btn btn-primary" style="width: fit-content;" onclick="definirPeriodo(this, '30dias')">Últimos 30 dias</button>
								<button type="button" id="btn-3meses" class="btn btn-primary" style="width: fit-content;" onclick="definirPeriodo(this, '3meses')">Últimos 3 meses</button>
								<button type="button" id="btn-180dias" class="btn btn-primary" style="width: fit-content;" onclick="definirPeriodo(this, '180dias')">Últimos 180 dias</button>
								<button type="button" id="btn-365dias" class="btn btn-primary" style="width: fit-content;" onclick="definirPeriodo(this, '365dias')">Últimos 365 dias</button>
								<br><br>
								<button type="button" id="btn-semana" class="btn btn-primary" style="width: fit-content;" onclick="definirPeriodo(this, 'semana')">Essa semana</button>
								<button type="button" id="btn-mes" class="btn btn-primary" style="width: fit-content;" onclick="definirPeriodo(this, 'mes')">Esse mês</button>
								<button type="button" id="btn-ano" class="btn btn-primary" style="width: fit-content;" onclick="definirPeriodo(this, 'ano')">Esse ano</button>
								<br><br>
								<label class="col-md-3 control-label" >Período</label>
								<div class="col-md-8">
									<input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="data_inicio" max="9999-12-31">
									<br>
									<input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="data_fim" max="9999-12-31">
								</div>
							</div>

							<div class="form-group" id="media-saida">
								<label class="col-md-3 control-label"> Média de saída</label>
								<div class="col-md-8">
									<select name="tipo_media">
										<option value="dia">Por dia</option>
										<option value="mes">Por mês</option>
										<option value="ano">Por ano</option>
									</select>
								</div>
							</div>

							<div class="form-group" id='orig'>
								<label class="col-md-3 control-label">Origem</label>
								<div class="col-md-8">
									<select name="origem">
										<option value="">Todas as Opções</option>
										<?php
										foreach ($dadosFiltros['origens'] as $value) {
											echo ('
												<option class="option-origem" value="' . $value['id_origem'] . '">' . $value['nome_origem'] . '</option>
												');
										}
										?>
									</select>
								</div>
							</div>

							<div class="form-group" id='dest' style="display: none;">
								<label class="col-md-3 control-label">Destino</label>
								<div class="col-md-8">
									<select name="destino">
										<option value="">Todas as Opções</option>
										<?php
										foreach ($dadosFiltros['destinos'] as $value) {
											echo ('
												<option class="option-destino" value="' . $value['id_destino'] . '">' . $value['nome_destino'] . '</option>
												');
										}
										?>
									</select>
								</div>
							</div>

							<div class="form-group" id='tipo-entrada'>
								<label class="col-md-3 control-label">Tipo de Entrada</label>
								<div class="col-md-8">
									<select name="tipo" id="tipoEntradaSelect" onchange="controlarTiposContabilizar()">
										<option value="">Todas as Opções</option>
										<?php
										foreach ($dadosFiltros['tipos_entrada'] as $value) {
											echo ('
												<option value="' . $value['id_tipo'] . '">' . $value['descricao'] . '</option>
												');
										}
										?>
									</select>
								</div>
							</div>

							<div class="form-group" id="tiposEntrada-contabilizar">
								<label class="col-md-3 control-label">Contabilizar no total</label>
								<div class="col-md-6">
									<small class="help-block">Selecione os tipos que devem compor o valor total do relatório.</small>
									<?php foreach ($dadosFiltros['tipos_entrada'] as $value): ?>
										<div class="checkbox">
											<label>
												<input type="checkbox" name="tiposEntrada[]" value="<?= (int) $value['id_tipo'] ?>"<?= $value['descricao'] === 'Doação' ? '' : ' checked' ?>>
												<?= htmlspecialchars($value['descricao'], ENT_QUOTES, 'UTF-8') ?>
											</label>
										</div>
									<?php endforeach; ?>
								</div>
							</div>

							<div class="form-group" id='tipo-saida' style="display: none;">
								<label class="col-md-3 control-label">Tipo de Saida</label>
								<div class="col-md-8">
									<select name="tipo" id="tipoSaidaSelect" onchange="controlarTiposContabilizar()">
										<option value="">Todas as Opções</option>
										<?php
										foreach ($dadosFiltros['tipos_saida'] as $value) {
											echo ('
												<option value="' . $value['id_tipo'] . '">' . $value['descricao'] . '</option>
												');
										}
										?>
									</select>
								</div>
							</div>

							<div class="form-group" id="tiposSaida-contabilizar">
								<label class="col-md-3 control-label">Contabilizar no total</label>
								<div class="col-md-6">
									<small class="help-block">Selecione os tipos que devem compor o valor total do relatório.</small>
									<?php foreach ($dadosFiltros['tipos_saida'] as $value): ?>
										<div class="checkbox">
											<label>
												<input type="checkbox" name="tiposSaida[]" value="<?= (int) $value['id_tipo'] ?>" checked>
												<?= htmlspecialchars($value['descricao'], ENT_QUOTES, 'UTF-8') ?>
											</label>
										</div>
									<?php endforeach; ?>
								</div>
							</div>

							<div class="form-group" id='resp'>
								<label class="col-md-3 control-label">Responsável</label>
								<div class="col-md-8">
									<select name="responsavel">
										<option value="">Todas as Opções</option>
										<?php
										foreach ($dadosFiltros['responsaveis'] as $value) {
											echo ('
												<option value="' . $value['id_pessoa'] . '">' . $value['nome'] . ' ' . $value['sobrenome'] . '</option>
												');
										}
										?>
									</select>
								</div>
							</div>

							<div class="form-group" id="categoria-relat" style="display: none;">
								<label class="col-md-3 control-label">Categoria</label>
								<div class="col-md-8">
									<select name="categoria_produto" id="categoriaProduto">
										<option value="">Todas as Categorias</option>
										<?php
										foreach ($dadosFiltros['categorias'] as $categoria) {
											echo '<option value="' . $categoria['id_categoria_produto'] . '">' . htmlspecialchars($categoria['descricao_categoria']) . '</option>';
										}
										?>
									</select>
								</div>
							</div>

							<div class="form-group" id="modo-requisicao" style="display: none;">
								<label class="col-md-3 control-label">Modelo da Requisição</label>
								<div class="col-md-8">
									<select name="modo_requisicao">
										<option value="movimentados" selected>Produtos mais movimentados</option>
										<option value="completo">Todos os produtos</option>
									</select>
									<br>
									<small>
										O modelo "Produtos mais movimentados" lista os produtos com maior saída no período selecionado.
										Produtos menos frequentes podem ser anotados nas linhas em branco.
									</small>
								</div>
							</div>

							<div class="form-group" id="almoxarifado">
								<label class="col-md-3 control-label">Almoxarifado</label>
								<div class="col-md-8">
									<select name="almoxarifado" id="almoxarifado1">
										<option value="">Todas as Opções</option>
										<?php
										foreach ($dadosFiltros['almoxarifados'] as $value) {
											echo '<option value="' . $value['id_almoxarifado'] . '">' . htmlspecialchars($value['descricao_almoxarifado']) . '</option>';
										}
										?>
									</select>
								</div>
							</div>

							<div class="form-group" id="panel-mostrarZerados">
								<label for="mostrarZerados" class="col-md-3 control-label">Mostrar produtos sem movimentação</label>
								<div class="col-md-8">
									<input id="mostrarZerados" type="checkbox" name="mostrarZerados" style="margin: 10px 0;">
								</div>
							</div>

							<div class="form-group">
								<div class="center-content">
									<input type="submit" value="Gerar" id="gerar" class="btn btn-primary" style="width: fit-content;">
								</div>
							</div>

							<div class="form-group">
								<div class="center-content">
									<input type="submit" value="Gerar" id="gerar3" class="btn btn-primary" style="width: fit-content;">
								</div>
							</div>

						</form>

						<!-- Formulário de produtos -->
						<form id="form-produto-grupo" class="form-horizontal" method="post" action="<?= WWW ?>html/matPat/relatorio_geracao_produto.php" data-action-produto="<?= WWW ?>html/matPat/relatorio_geracao_produto.php" data-action-grupo="<?= WWW ?>controle/control.php?nomeClasse=RelatorioGrupoControle&metodo=gerar">

							<div class="form-group" id='per2' style="text-align: center;">
								<button type="button" id="btn-7dias2" class="btn btn-primary" style="width: fit-content;" onclick="definirPeriodo(this, '7dias')">Últimos 7 dias</button>
								<button type="button" id="btn-30dias2" class="btn btn-primary" style="width: fit-content;" onclick="definirPeriodo(this, '30dias')">Últimos 30 dias</button>
								<button type="button" id="btn-3meses2" class="btn btn-primary" style="width: fit-content;" onclick="definirPeriodo(this, '3meses')">Últimos 3 meses</button>
								<button type="button" id="btn-180dias2" class="btn btn-primary" style="width: fit-content;" onclick="definirPeriodo(this, '180dias')">Últimos 180 dias</button>
								<button type="button" id="btn-365dias2" class="btn btn-primary" style="width: fit-content;" onclick="definirPeriodo(this, '365dias')">Últimos 365 dias</button>
								<br><br>
								<button type="button" id="btn-semana2" class="btn btn-primary" style="width: fit-content;" onclick="definirPeriodo(this, 'semana')">Essa semana</button>
								<button type="button" id="btn-mes2" class="btn btn-primary" style="width: fit-content;" onclick="definirPeriodo(this, 'mes')">Esse mês</button>
								<button type="button" id="btn-ano2" class="btn btn-primary" style="width: fit-content;" onclick="definirPeriodo(this, 'ano')">Esse ano</button>
								<br><br>
								<label class="col-md-3 control-label" >Período</label>
								<div class="col-md-8">
									<input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="data_inicio" max="9999-12-31">
									<br>
									<input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="data_fim" max="9999-12-31">
								</div>
							</div>

							<div class="form-group" id="almoxarifado2">
								<label class="col-md-3 control-label">Almoxarifado</label>
								<div class="col-md-8">
									<select name="almoxarifado" id="almoxarifadoSelect" required>
										<option value="">Selecionar almoxarifado</option>
										<?php
										foreach ($dadosFiltros['almoxarifados'] as $value) {
											echo '<option value="' . $value['id_almoxarifado'] . '">' . htmlspecialchars($value['descricao_almoxarifado']) . '</option>';
										}
										?>
									</select>
								</div>
							</div>

							<div class="form-group" id="produto">
								<label class="col-md-3 control-label">Produtos</label>
								<div class="col-md-8">
									<select name="produto" id="produtoSelect" data-plugin-selectTwo
										data-plugin-options='{ "width": "190px" }' required>
										<option value="">Selecione um Produto</option>
									</select>
								</div>
							</div>

							<div class="form-group" id="grupo-produto-relatorio" style="display: none;">
								<label class="col-md-3 control-label">Grupo</label>
								<div class="col-md-8">
									<select name="grupo" id="grupoSelect">
										<option value="">Selecione um Grupo</option>
										<?php foreach ($dadosFiltros['grupos'] as $grupo): ?>
											<option value="<?= (int) $grupo['id_grupo_produto'] ?>">
												<?= htmlspecialchars($grupo['descricao_grupo'], ENT_QUOTES, 'UTF-8') ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>

							<div class="form-group">
								<div class="center-content">
									<input type="submit" value="Gerar" id="gerar2" class="btn btn-primary" style="width: fit-content;">
								</div>
							</div>

						</form>

					</div>
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
    function formatarData(data) {
        const ano = data.getFullYear();
        const mes = String(data.getMonth() + 1).padStart(2, '0');
        const dia = String(data.getDate()).padStart(2, '0');
        return `${ano}-${mes}-${dia}`;
    }

    function definirPeriodo(botao, periodo) {
        const formulario = botao.closest('form');
        const inicio = new Date();
        const fim = new Date();

        switch (periodo) {
            case '7dias':
            case '30dias':
            case '180dias':
            case '365dias':
                const dias = parseInt(periodo, 10);
    			inicio.setDate(
        			inicio.getDate() - (dias - 1)
    			);

                break;
            case '3meses': {
                const dia = inicio.getDate();
                inicio.setDate(1);
                inicio.setMonth(inicio.getMonth() - 3);
                const ultimoDia = new Date(inicio.getFullYear(), inicio.getMonth() + 1, 0).getDate();
                inicio.setDate(Math.min(dia, ultimoDia));
                break;
            }
            case 'semana': {
                const diaSemana = inicio.getDay();
                inicio.setDate(inicio.getDate() + (diaSemana === 0 ? -6 : 1 - diaSemana));
                fim.setTime(inicio.getTime());
                fim.setDate(fim.getDate() + 6);
                break;
            }
            case 'mes':
                inicio.setDate(1);
                fim.setMonth(fim.getMonth() + 1, 0);
                break;
            case 'ano':
                inicio.setMonth(0, 1);
                fim.setMonth(11, 31);
                break;
        }

        formulario.elements['data_inicio'].value = formatarData(inicio);
        formulario.elements['data_fim'].value = formatarData(fim);
        salvarFiltrosRelatorio();
    }

    function controlarTiposContabilizar() {
        const tipo = document.getElementById('tipo-relat').value;
        document.getElementById('tiposEntrada-contabilizar').style.display =
            tipo === 'entrada' && !document.getElementById('tipoEntradaSelect').value ? 'block' : 'none';
        document.getElementById('tiposSaida-contabilizar').style.display =
            tipo === 'saida' && !document.getElementById('tipoSaidaSelect').value ? 'block' : 'none';
    }

    function controlarCampoMediaSaida() {
        const tipo = document.getElementById('tipo-relat').value;
        const almoxarifado = document.getElementById('almoxarifado1');
        const obrigatorio = tipo === 'itens_compra';

        almoxarifado.required = obrigatorio;
        almoxarifado.options[0].text = obrigatorio ? 'Selecionar almoxarifado' : 'Todas as Opções';
        document.getElementById('media-saida').style.display =
            tipo === 'saida' || tipo === 'itens_compra' ? 'block' : 'none';
        document.getElementById('categoria-relat').style.display =
            ['estoque', 'requisicao', 'itens_compra'].includes(tipo) ? 'block' : 'none';
        document.getElementById('modo-requisicao').style.display =
            tipo === 'requisicao' ? 'block' : 'none';
        controlarTiposContabilizar();
    }

    function atualizarTipoRelatorio() {
        const tipo = document.getElementById('tipo-relat').value;
        changeType(tipo);
        controlarCampoMediaSaida();
    }

    const formularios = {
        principal: document.getElementById('form-relatorio'),
        produtoGrupo: document.getElementById('form-produto-grupo')
    };
    const chaveFiltros = 'filtrosRelatorio:<?= (int) $_SESSION['id_pessoa'] ?>';

    function chaveCampo(campo) {
        if (campo.id === 'tipoEntradaSelect' || campo.id === 'tipoSaidaSelect') {
            return campo.id;
        }
        return campo.name;
    }

    function valoresFormulario(formulario) {
        const valores = {};
        formulario.querySelectorAll('input, select, textarea').forEach(campo => {
            if (['button', 'submit', 'reset'].includes(campo.type)) return;
            const chave = chaveCampo(campo);
            if (!chave) return;

            if (campo.type === 'checkbox') {
                if (campo.name.endsWith('[]')) {
                    if (!Array.isArray(valores[chave])) valores[chave] = [];
                    if (campo.checked) valores[chave].push(campo.value);
                } else {
                    valores[chave] = campo.checked;
                }
            } else {
                valores[chave] = campo.value;
            }
        });
        return valores;
    }

    function salvarFiltrosRelatorio() {
        try {
            localStorage.setItem(chaveFiltros, JSON.stringify({
                principal: valoresFormulario(formularios.principal),
                produtoGrupo: valoresFormulario(formularios.produtoGrupo)
            }));
        } catch (erro) {
            // A geração do relatório continua disponível sem armazenamento local.
        }
    }

    function lerFiltrosRelatorio() {
    	try {
        	return JSON.parse(
            	localStorage.getItem(chaveFiltros)
        	) || {};
    	} catch (erro) {
        	return {};
    	}
	}

    function restaurarFormulario(formulario, valores, ignorarProduto = false) {
        formulario.querySelectorAll('input, select, textarea').forEach(campo => {
            const chave = chaveCampo(campo);
            if (!chave || !Object.prototype.hasOwnProperty.call(valores, chave)) return;
            if (ignorarProduto && chave === 'produto') return;
            const valor = valores[chave];

            if (campo.type === 'checkbox') {
                campo.checked = campo.name.endsWith('[]')
                    ? Array.isArray(valor) && valor.includes(campo.value)
                    : (Array.isArray(valor) ? valor.includes(campo.value) : valor === true || valor === 'on');
            } else if (valor !== null && valor !== undefined) {
                if (campo.tagName !== 'SELECT' || Array.from(campo.options).some(opcao => opcao.value === valor)) {
                    campo.value = valor;
                }
            }
        });
    }

    let produtoPendente = null;
    let requisicaoProdutos = 0;
    $('#almoxarifadoSelect').on('change', function() {
        const idAlmoxarifado = this.value;
        const produtoARestaurar = produtoPendente;
        produtoPendente = null;
        const requisicaoAtual = ++requisicaoProdutos;
        const selectProduto = document.getElementById('produtoSelect');
        selectProduto.innerHTML = '<option value="">Selecione um Produto</option>';

        if (!idAlmoxarifado) {
            $('#produtoSelect').trigger('change');
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('GET', '<?= WWW ?>controle/control.php?nomeClasse=ProdutoControle&metodo=listarDisponiveisRelatorioPorAlmoxarifado&id_almoxarifado=' + encodeURIComponent(idAlmoxarifado), true);
        xhr.onload = function() {
            if (requisicaoAtual !== requisicaoProdutos) return;
            if (xhr.status !== 200) {
                console.error('Erro ao carregar produtos:', xhr.status);
                return;
            }

            try {
                const grupos = new Map();
                JSON.parse(xhr.responseText).forEach(produto => {
                    const grupo = produto.descricao_grupo || 'Sem grupo';
                    if (!grupos.has(grupo)) grupos.set(grupo, []);
                    grupos.get(grupo).push(produto);
                });

                grupos.forEach((produtos, nomeGrupo) => {
                    const optgroup = document.createElement('optgroup');
                    optgroup.label = nomeGrupo;
                    produtos.forEach(produto => {
                        const option = document.createElement('option');
                        option.value = produto.id_produto;
                        option.textContent = produto.descricao;
                        optgroup.appendChild(option);
                    });
                    selectProduto.appendChild(optgroup);
                });

                if (produtoARestaurar && Array.from(selectProduto.options).some(opcao => opcao.value === produtoARestaurar)) {
                    selectProduto.value = produtoARestaurar;
                }
                $('#produtoSelect').trigger('change');
            } catch (erro) {
                console.error('Resposta inválida ao carregar produtos:', erro);
            }
        };
        xhr.send();
    });

    document.addEventListener('DOMContentLoaded', function() {
        const filtros = lerFiltrosRelatorio();
        const principal = filtros.principal || {};
        const produtoGrupo = filtros.produtoGrupo || {};
        const seletorTipo = document.getElementById('tipo-relat');

        if (principal.tipo_relatorio && Array.from(seletorTipo.options).some(opcao => opcao.value === principal.tipo_relatorio)) {
            seletorTipo.value = principal.tipo_relatorio;
        }
        atualizarTipoRelatorio();
        restaurarFormulario(formularios.principal, principal);
        restaurarFormulario(formularios.produtoGrupo, produtoGrupo, true);
        controlarTiposContabilizar();

        if (produtoGrupo.almoxarifado) {
            produtoPendente = produtoGrupo.produto || null;
            document.getElementById('almoxarifadoSelect').dispatchEvent(new Event('change'));
        }

        Object.values(formularios).forEach(formulario => {
    		formulario.addEventListener(
        		'change',
        		salvarFiltrosRelatorio
    		);

    		formulario.addEventListener(
        		'submit',
        		salvarFiltrosRelatorio
    		);
		});
    });
</script>
<script src="<?= WWW ?>html/relatorios/relatorio.js" defer></script>

</html>
