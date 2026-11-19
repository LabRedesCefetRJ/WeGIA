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

//verifica permissão do usuário
permissao($_SESSION['id_pessoa'], 20, 5);

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once "../personalizacao_display.php";

require_once ROOT . '/dao/VisitaDAO.php';

$rotulosCategoria = [
	'atendido' => 'Atendidos',
	'funcionario' => 'Funcionários',
	'voluntario' => 'Voluntários',
	'pessoa' => 'Outras pessoas',
	'pet' => 'Pets',
	'setor' => 'Setores',
	'instituicao' => 'Instituição',
];

$visitadosPorCategoria = [];
foreach ((new VisitaDAO())->listarVisitadosComVisitas() as $visitado) {
	$visitadosPorCategoria[$visitado['categoria']][] = $visitado;
}

// Padrão: últimos 30 dias
$hoje = new DateTimeImmutable('today');
$dataFimPadrao = $hoje->format('Y-m-d');
$dataInicioPadrao = $hoje->modify('-29 days')->format('Y-m-d');

function escaparHistorico($texto)
{
	return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html class="fixed">

<head>

	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Histórico de Visitas</title>

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
	<link rel="stylesheet" href="../../css/recepcao.css">

	<!-- Head Libs -->
	<script src="../../assets/vendor/modernizr/modernizr.js"></script>
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">

	<!-- Vendor -->
	<script src="../../assets/vendor/jquery/jquery.min.js"></script>
	<script src="js/datatables-pt-br.js"></script>
	<script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
	<script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

	<script>
		$(function () {
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
					<h2>Histórico de Visitas</h2>

					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li><a href="../home.php"> <i class="fa fa-home"></i></a></li>
							<li><span>Recepção</span></li>
							<li><span>Histórico de Visitas</span></li>
						</ol>

						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->
				<div class="box-recepcao">
					<div class="box-recepcao-header">
						<h3 class="box-recepcao-title">Entradas e saídas registradas</h3>
						<button type="button" class="btn btn-link box-recepcao-recolher" data-toggle="collapse" data-target="#corpoHistorico" aria-expanded="true" aria-controls="corpoHistorico" title="Recolher">
							<i class="fa fa-minus" aria-hidden="true"></i>
						</button>
					</div>

					<div id="corpoHistorico" class="collapse in">
						<div class="box-recepcao-body">
							<form id="formFiltros" class="filtros-historico" novalidate>
								<div class="row">
									<div class="form-group col-sm-6 col-md-3">
										<label for="filtroReferencia">Filtrar período por</label>
										<select class="form-control" id="filtroReferencia" name="referencia">
											<option value="presenca">Presença no período</option>
											<option value="entrada">Data de entrada</option>
											<option value="saida">Data de saída</option>
										</select>
									</div>
									<div class="form-group col-sm-6 col-md-3">
										<label for="filtroDataInicio">Data inicial</label>
										<input type="date" class="form-control" id="filtroDataInicio" name="data_inicio" value="<?= escaparHistorico($dataInicioPadrao) ?>" max="9999-12-31">
									</div>
									<div class="form-group col-sm-6 col-md-3">
										<label for="filtroDataFim">Data final</label>
										<input type="date" class="form-control" id="filtroDataFim" name="data_fim" value="<?= escaparHistorico($dataFimPadrao) ?>" max="9999-12-31">
									</div>
									<div class="form-group col-sm-6 col-md-3">
										<label for="filtroSituacao">Situação</label>
										<select class="form-control" id="filtroSituacao" name="situacao">
											<option value="">Todas</option>
											<option value="andamento">Em andamento</option>
											<option value="encerrada">Encerradas</option>
										</select>
									</div>
								</div>
								<div class="row">
									<div class="form-group col-sm-6 col-md-6">
										<label for="filtroVisitado">Visitado</label>
										<select class="form-control" id="filtroVisitado" name="id_visitado">
											<option value="">Todos</option>
											<?php foreach ($rotulosCategoria as $categoria => $rotulo) : ?>
												<?php if (!empty($visitadosPorCategoria[$categoria])) : ?>
													<optgroup label="<?= escaparHistorico($rotulo) ?>">
														<?php foreach ($visitadosPorCategoria[$categoria] as $visitado) : ?>
															<option value="<?= (int) $visitado['id_visitado'] ?>"><?= escaparHistorico($visitado['nome']) ?></option>
														<?php endforeach; ?>
													</optgroup>
												<?php endif; ?>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="form-group col-sm-6 col-md-4">
										<label for="filtroCategoria">Tipo de visitado</label>
										<select class="form-control" id="filtroCategoria" name="categoria">
											<option value="">Todos</option>
											<option value="atendido">Atendido</option>
											<option value="funcionario">Funcionário</option>
											<option value="voluntario">Voluntário</option>
											<option value="pet">Pet</option>
											<option value="setor">Setor</option>
											<option value="instituicao">Instituição</option>
										</select>
									</div>
									<div class="form-group col-sm-12 col-md-2 filtros-acoes">
										<button type="submit" class="btn btn-primary btn-block" id="botaoFiltrar">
											<i class="fa fa-filter" aria-hidden="true"></i> Filtrar
										</button>
									</div>
								</div>
								<div class="filtros-atalhos">
									<span class="text-muted">Período:</span>
									<button type="button" class="btn btn-default btn-xs" data-periodo="hoje">Hoje</button>
									<button type="button" class="btn btn-default btn-xs" data-periodo="7">Últimos 7 dias</button>
									<button type="button" class="btn btn-default btn-xs" data-periodo="30">Últimos 30 dias</button>
									<button type="button" class="btn btn-default btn-xs" data-periodo="mes">Este mês</button>
									<button type="button" class="btn btn-default btn-xs" data-periodo="todos">Todo o histórico</button>
									<button type="button" class="btn btn-link btn-xs" id="botaoLimparFiltros">Limpar filtros</button>
								</div>
							</form>

							<div id="alertaHistorico" aria-live="polite"></div>

							<p class="text-muted dica-historico">
								<i class="fa fa-info-circle" aria-hidden="true"></i>
								Clique em uma linha para ver todos os visitantes e visitados da entrada. Clique em um nome para ver a ficha da pessoa. <span id="dicaPeriodo">O período mostra as visitas que estavam em andamento em algum momento dele, inclusive as que entraram antes ou saíram depois.</span>
							</p>

							<table class="table table-hover" id="tabelaHistorico" style="width:100%" data-plugin-options='{"searchPlaceholder": "Pesquisar"}'>
								<thead>
									<tr>
										<th class="coluna-expandir"><span class="sr-only">Expandir</span></th>
										<th>Entrada</th>
										<th>Saída</th>
										<th>Visitantes</th>
										<th>Visitados</th>
									</tr>
								</thead>
								<tbody></tbody>
							</table>

							<div class="box-recepcao-acoes">
								<button type="button" class="btn btn-app-recepcao" id="botaoBaixarRelatorio">
									<i class="fa fa-file-pdf-o" aria-hidden="true"></i>
									<span>Baixar relatório (PDF)</span>
								</button>
								<a href="pre_registro_entrada.php" class="btn btn-app-recepcao">
									<i class="fa fa-sign-in" aria-hidden="true"></i>
									<span>Registrar entrada</span>
								</a>
								<a href="registro_saida.php" class="btn btn-app-recepcao">
									<i class="fa fa-sign-out" aria-hidden="true"></i>
									<span>Registrar saída</span>
								</a>
							</div>
						</div>
					</div>
				</div>
				<!-- end: page -->
			</section>
		</div>
	</section>

	<?php require __DIR__ . '/modal_ficha.php'; ?>

	<!-- Vendor -->
	<script src="../../assets/vendor/select2/select2.js"></script>
	<script src="../../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
	<script src="../../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>

	<!-- Theme Base, Components and Settings -->
	<script src="../../assets/javascripts/theme.js"></script>
	<script src="../../assets/javascripts/theme.custom.js"></script>
	<script src="../../assets/javascripts/theme.init.js"></script>

	<script>
		window.HISTORICO_VISITAS = {
			controle: "../../controle/control.php",
			relatorio: "../../service/RelatorioVisitasService.php"
		};
	</script>
	<script src="js/ficha_recepcao.js"></script>
	<script src="js/historico_visitas.js"></script>

	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/pessoa.html" width="200" height="60" style="border:none;"></iframe>
	</div>
</body>

</html>
