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
permissao($_SESSION['id_pessoa'], 12, 5);

$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
require_once ROOT . '/classes/Csrf.php';
require_once ROOT . '/dao/Conexao.php';

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once "../personalizacao_display.php";
?>


<!doctype html>
<html class="fixed">

<head>

	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Registro de Saída</title>

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
		let gruposPorIndice = {};

		function formatarDataHora(data) {
			if (!data) return "Não informado";
			const d = new Date(data.replace(" ", "T"));
			if (isNaN(d.getTime())) return data;
			return d.toLocaleString("pt-BR");
		}

		function nomeCompleto(item) {
			return [item.nome, item.sobrenome].filter(Boolean).join(" ");
		}

		const iconesTipo = {
			pessoa: "fa-user",
			pet: "fa-paw",
			setor: "fa-building-o",
			instituicao: "fa-university"
		};

		function listarNomes(itens) {
			return '<ul class="list-unstyled" style="margin:0;">' +
				itens.map(function (item) {
					return `<li><i class="fa ${iconesTipo[item.tipo] || "fa-user"} text-muted"></i> ${nomeCompleto(item)}</li>`;
				}).join("") +
				"</ul>";
		}

		function itemDetalhe(item) {
			const identificador = item.identificador && item.identificador.trim() !== "" ? item.identificador : "—";
			return `
				<li class="list-group-item">
					<div style="display:flex; align-items:flex-start; gap:10px;">
						<span style="flex:0 0 24px; width:24px; height:24px; display:flex; align-items:center; justify-content:center;">
							<i class="fa ${iconesTipo[item.tipo] || "fa-user"} fa-lg text-muted"></i>
						</span>
						<div>
							<strong>${nomeCompleto(item)}</strong>
							<div class="text-muted" style="font-size:90%;">${identificador}</div>
						</div>
					</div>
				</li>`;
		}

		function mostrarDetalhes(indice) {
			const grupo = gruposPorIndice[indice];
			if (!grupo) return;

			$("#detalhesVisitantesList").html(grupo.visitantes.map(itemDetalhe).join(""));
			$("#detalhesVisitadosList").html(grupo.visitados.map(itemDetalhe).join(""));

			$("#detalhesTotalVisitantes").text(grupo.visitantes.length);
			$("#detalhesTotalVisitados").text(grupo.visitados.length);

			$("#detalhesHorarioEntrada").text(formatarDataHora(grupo.horario_entrada));
			$("#detalhesDescricao").text(grupo.descricao && grupo.descricao.trim() !== "" ? grupo.descricao : "Sem descrição");

			$("#modalDetalhesVisita").modal("show");
		}

		function registrarSaida(indice) {
			const grupo = gruposPorIndice[indice];
			if (!grupo) return;

			const csrfField = <?= json_encode(Csrf::inputField()) ?>;

			let camposIdVisita = "";
			grupo.ids_visita.forEach(function (id) {
				camposIdVisita += `<input type="hidden" name="idVisita[]" value="${id}">`;
			});

			const formHtml = `
				<form method="POST" action="../../controle/control.php" id="formRegistrarSaidaLote" style="display:none;">
					${csrfField}
					<input type="hidden" name="nomeClasse" value="VisitaControle">
					<input type="hidden" name="metodo" value="encerrar">
					${camposIdVisita}
				</form>
			`;

			$("#formRegistrarSaidaLote").remove();
			$("body").append(formHtml);
			$("#formRegistrarSaidaLote").trigger("submit");
		}

		function carregar() {

			$.ajax({
				url: "./listar_visitas.php",
				method: "GET",
				dataType: "json",
				success: function (dados) {

					let tabela = $("#datatable-default").DataTable();

					tabela.clear();
					gruposPorIndice = {};

					$.each(dados, function (indice, grupo) {
						gruposPorIndice[indice] = grupo;

						tabela.row.add([
							listarNomes(grupo.visitantes),
							listarNomes(grupo.visitados),
							formatarDataHora(grupo.horario_entrada),
							`
							<div class="text-center" style="display:flex; align-items:center; justify-content:center; gap:6px;">
								<button type="button" class="btn btn-default btn-sm" onclick="mostrarDetalhes(${indice})">
									<i class="fa fa-info-circle"></i> Detalhes
								</button>
								<button type="button" class="btn btn-primary btn-sm" onclick="registrarSaida(${indice})">
									<i class="fa fa-sign-out"></i> Registrar Saída
								</button>
							</div>
							`
						]);
					});

					tabela.draw();
				},
			});
		}

		$(function () {
			$("#datatable-default").DataTable();
			
            $("#header").load("../header.php");
            $(".menuu").load("../menu.php");

			carregar();
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
					<h2>Registro Saída</h2>

					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li><a href="../index.php"> <i class="fa fa-home"></i>
								</a></li>
							<li><span>Registro Saída</span></li>
						</ol>

						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->
				<?php
					if (isset($_SESSION['msg_c']) && !empty($_SESSION['msg_c'])) {
						echo ('<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['msg_c']) . '</div>');
						$_SESSION['msg_c'] = "";
					}
					else if (isset($_SESSION['msg_e']) && !empty($_SESSION['msg_e'])) {
						echo ('<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['msg_e']) . '</div>');
						$_SESSION['msg_e'] = "";
					}
				?>
				</header>
				<section class="panel">
					<header class="panel-heading">
						<div class="panel-actions">
							<a href="#" class="fa fa-caret-down"></a>
						</div>
						<h2 class="panel-title">Registro de Saída</h2><br>
					</header>
					<div class="panel-body">
						<table class="table table-bordered table-striped mb-none"
							id="datatable-default">
							<thead>
								<tr>
									<th>Visitante(s)</th>
									<th>Visitado(s)</th>
									<th>Horário de Entrada</th>
									<th class="text-center">Ação</th>
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
					<iframe src="https://www.wegia.org/software/footer/pessoa.html" width="200" height="60" style="border:none;"></iframe>
				</div>

				<div class="modal fade" id="modalDetalhesVisita" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog modal-lg" role="document">
						<div class="modal-content">

							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
									<span aria-hidden="true">&times;</span>
								</button>
								<h4 class="modal-title">
									<i class="fa fa-info-circle"></i>
									Detalhes da Visita
								</h4>
							</div>

							<div class="modal-body">

								<!-- Resumo da visita -->
								<div class="panel panel-default">
									<div class="panel-body" style="margin-bottom:0;">
										<div class="row">
											<div class="col-sm-4">
												<small class="text-muted text-uppercase"><i class="fa fa-clock-o"></i> Entrada</small>
												<div><strong id="detalhesHorarioEntrada">Não informado</strong></div>
											</div>
											<div class="col-sm-8">
												<small class="text-muted text-uppercase"><i class="fa fa-align-left"></i> Descrição</small>
												<div id="detalhesDescricao">Sem descrição</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Visitantes e visitados -->
								<div class="row">
									<div class="col-sm-6">
										<div class="panel panel-default">
											<div class="panel-heading">
												<strong><i class="fa fa-users"></i> Visitantes</strong>
												<span class="badge pull-right" id="detalhesTotalVisitantes">0</span>
											</div>
											<ul class="list-group" id="detalhesVisitantesList" style="margin-bottom:0;"></ul>
										</div>
									</div>

									<div class="col-sm-6">
										<div class="panel panel-default">
											<div class="panel-heading">
												<strong><i class="fa fa-sign-in"></i> Visitados</strong>
												<span class="badge pull-right" id="detalhesTotalVisitados">0</span>
											</div>
											<ul class="list-group" id="detalhesVisitadosList" style="margin-bottom:0;"></ul>
										</div>
									</div>
								</div>

							</div>

							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">
									<i class="fa fa-times"></i>
									Fechar
								</button>
							</div>

						</div>
					</div>
				</div>

</body>

</html>