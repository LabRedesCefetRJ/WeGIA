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

require_once ROOT . '/controle/VisitanteControle.php';

$pdo = Conexao::connect();

$visitantesSelecionados = (new VisitanteDAO($pdo))->buscarResumoPorIds(VisitanteControle::obterIdsSelecionados());

if (empty($visitantesSelecionados)) {
    header('Location: pre_registro_entrada.php?msg_e=' . urlencode('Adicione ao menos um visitante para registrar a entrada.'));
    exit();
}

$idsVisitantes = array_map(fn($v) => (int) $v['id_visitante'], $visitantesSelecionados);

?>


<!doctype html>
<html class="fixed">

<head>

	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Registro de Entrada</title>

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
		const WWW_BASE = <?= json_encode(WWW) ?>;
		const fotoSemFoto = WWW_BASE + "img/semfoto.png";
		const idsVisitantes = <?= json_encode($idsVisitantes) ?>;

		let visitadosSelecionados = {};

		function fotoVisitadoHtml(item) {
			const src = item.imagem ? ("data:image;base64," + item.imagem) : fotoSemFoto;
			return `<img src="${src}" alt="Foto" class="rounded" style="width:40px;height:40px;object-fit:cover;">`;
		}

		function escaparHtml(texto) {
			return $("<div>").text(texto == null ? "" : texto).html();
		}

		function atualizarSelecionados() {
			const ids = Object.keys(visitadosSelecionados);

			const $lista = $("#listaVisitadosSelecionados");
			$lista.empty();

			if (ids.length === 0) {
				$lista.append('<li class="text-muted" style="list-style:none;">Nenhum visitado selecionado — a visita será registrada para a instituição</li>');
			} else {
				ids.forEach(function (id) {
					$lista.append(
						`<li class="label label-primary" style="list-style:none; display:inline-block; margin:2px 6px 2px 0; padding:6px 10px; font-size:100%;">
							${escaparHtml(visitadosSelecionados[id])}
						</li>`
					);
				});
			}

			$("#contadorVisitadosSelecionados").text(ids.length || "instituição");
			$("#contadorVisitadosResumo").text(ids.length || "1 (instituição)");
			$("#totalVisitasGeradas").text(Math.max(ids.length, 1) * idsVisitantes.length);
		}

		function carregar(tipo) {

			if (!tipo) return;

			$.ajax({
				url: "./listar_pessoas.php",
				method: "GET",
				data: { tipo: tipo },
				dataType: "json",
				success: function (dados) {

					let tabela = $("#datatable-default").DataTable();

					tabela.clear();

					$.each(dados, function (i, item) {
						const id = item.id_visitado;
						const nome = [item.nome, item.sobrenome].filter(Boolean).join(" ");
						const marcado = Object.prototype.hasOwnProperty.call(visitadosSelecionados, id) ? "checked" : "";

						tabela.row.add([
							fotoVisitadoHtml(item),
							escaparHtml(nome),
							escaparHtml(item.identificador || "—"),
							`
							<div class="text-center">
								<input type="checkbox" class="checkbox-visitado" data-id="${id}" data-nome="${escaparHtml(nome)}" ${marcado} style="width:18px;height:18px;">
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

			const tipoSalvo = localStorage.getItem("tipoVisitado");

			if (tipoSalvo && $("#tipo option[value='" + tipoSalvo + "']").length) {
				$("#tipo").val(tipoSalvo);
				carregar(tipoSalvo);
			}

			$("#tipo").on("change", function () {
   				let tipo = $(this).val();
				localStorage.setItem("tipoVisitado", tipo);
    			carregar(tipo);
			});

			$(document).on("change", ".checkbox-visitado", function () {
				const id = $(this).data("id");
				const nome = $(this).data("nome");

				if (this.checked) {
					visitadosSelecionados[id] = nome;
				} else {
					delete visitadosSelecionados[id];
				}

				atualizarSelecionados();
			});

			$("#botaoRegistrarEntradaTopo").on("click", function () {
				const ids = Object.keys(visitadosSelecionados);

				const csrfField = <?= json_encode(Csrf::inputField()) ?>;
				const descricao = $("#descricaoVisita").val() || "";

				let camposIdVisitante = "";
				idsVisitantes.forEach(function (id) {
					camposIdVisitante += `<input type="hidden" name="idVisitante[]" value="${id}">`;
				});

				let camposIdVisitado = "";
				ids.forEach(function (id) {
					camposIdVisitado += `<input type="hidden" name="idVisitado[]" value="${id}">`;
				});

				const formHtml = `
					<form method="POST" action="../../controle/control.php" id="formRegistrarEntradaMultipla" style="display:none;">
						${csrfField}
						<input type="hidden" name="nomeClasse" value="VisitaControle">
						<input type="hidden" name="metodo" value="incluir">
						${camposIdVisitante}
						<input type="hidden" name="descricao" value="${escaparHtml(descricao)}">
						${camposIdVisitado}
					</form>
				`;

				$("#formRegistrarEntradaMultipla").remove();
				$("body").append(formHtml);
				$("#formRegistrarEntradaMultipla").trigger("submit");
			});
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
					<h2>Registro Entrada</h2>

					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li><a href="../index.php"> <i class="fa fa-home"></i>
								</a></li>
							<li><span>Registro Entrada</span></li>
						</ol>

						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->

				</header>

				<!-- start: page -->
				<?php
					if (isset($_GET['msg']) && $_GET['msg'] !== '') {
						echo ('<div class="alert alert-danger" role="alert">' . htmlspecialchars($_GET['msg']) . '</div>');
					}
					if (isset($_SESSION['msg_c']) && !empty($_SESSION['msg_c'])) {
						echo ('<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['msg_c']) . '</div>');
						$_SESSION['msg_c'] = "";
					}
					else if (isset($_SESSION['msg_e']) && !empty($_SESSION['msg_e'])) {
						echo ('<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['msg_e']) . '</div>');
						$_SESSION['msg_e'] = "";
					}
				?>

				<section class="panel">
					<header class="panel-heading">
						<h2 class="panel-title">Visitantes (<?= count($visitantesSelecionados) ?>)</h2>
					</header>
					<div class="panel-body">
						<div style="display:flex; align-items:flex-start; justify-content:space-between; gap:15px; flex-wrap:wrap;">
							<div style="flex:1; min-width:260px;">
								<?php foreach ($visitantesSelecionados as $visitanteSelecionado) : ?>
									<?php $fotoSelecionado = !empty($visitanteSelecionado['imagem']) ? 'data:image;base64,' . $visitanteSelecionado['imagem'] : WWW . 'img/semfoto.png'; ?>
									<div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
										<img src="<?= $fotoSelecionado ?>" alt="Foto do visitante" class="rounded" style="width:44px;height:44px;object-fit:cover;margin-left:0;">
										<span><?= htmlspecialchars($visitanteSelecionado['nome'] . ' ' . $visitanteSelecionado['sobrenome'], ENT_QUOTES, 'UTF-8') ?></span>
										<form method="POST" action="../../controle/control.php" style="display:inline; margin:0;">
											<?= Csrf::inputField() ?>
											<input type="hidden" name="nomeClasse" value="VisitanteControle">
											<input type="hidden" name="metodo" value="removerVisita">
											<input type="hidden" name="retorno" value="registro_entrada">
											<input type="hidden" name="idVisitante" value="<?= (int) $visitanteSelecionado['id_visitante'] ?>">
											<button type="submit" class="btn btn-danger btn-xs" title="Remover visitante"><i class="fa fa-times"></i></button>
										</form>
									</div>
								<?php endforeach; ?>
							</div>
							<div>
								<a href="pre_registro_entrada.php" class="btn btn-default">
									<i class="fa fa-user-plus"></i> Adicionar visitante
								</a>
								<a href="cadastro_visitado.php" class="btn btn-primary">
									<i class="fa fa-user-plus"></i> Cadastrar Visitado
								</a>
							</div>
						</div>
					</div>
				</section>

				<section class="panel">
    				<header class="panel-heading">
        				<h2 class="panel-title">Visitados (<span id="contadorVisitadosSelecionados">0</span>)</h2>
    				</header>

    				<div class="panel-body" style="display:flex; align-items:center; justify-content:space-between; gap:15px; flex-wrap:wrap;">
        				<div style="flex:1; min-width:260px;">
            				<ul id="listaVisitadosSelecionados" style="margin:0; padding:0;">
                				<li class="text-muted" style="list-style:none;">
                    				Nenhum visitado selecionado — a visita será registrada para a instituição
                				</li>
            				</ul>
        				</div>

        				<button type="button" id="botaoRegistrarEntradaTopo" class="btn btn-primary">
            				<i class="fa fa-sign-in"></i>
            				Registrar Entrada (<span id="contadorVisitadosSelecionados">instituição</span>)
        				</button>
    				</div>
				</section>

				<!-- start: page -->
				<section class="panel">
					<header class="panel-heading">
						<div class="panel-actions">
							<a href="#" class="fa fa-caret-down"></a>
						</div>
						<h2 class="panel-title">Selecione o tipo de visitado:</h2><br>
						<form method="GET" action="#" id="select_tipo" name="select_tipo">
							<select name="select_tipo" id="tipo">
								<option selected disabled></option>
								<option value="atendido">Atendido</option>
								<option value="funcionario">Funcionário</option>
								<option value="pet">Pet</option>
								<option value="setor">Setor</option>
								<option value="voluntario">Voluntário</option>
							</select>
							<br>
						</form>
						<div class="form-group" style="margin-top:15px;">
							<label for="descricaoVisita">Descrição da visita (opcional):</label>
							<textarea id="descricaoVisita" name="descricaoVisita" class="form-control" rows="2" maxlength="255" placeholder="Digite uma breve descrição da visita (até 255 caracteres)"></textarea>
						</div>
					</header>
					<div class="panel-body">
						<table class="table table-bordered table-striped mb-none"
							id="datatable-default">
							<thead>
								<tr>
									<th>Foto</th>
									<th>Nome</th>
									<th>Identificador</th>
									<th class="text-center">Selecionar</th>
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
</body>

</html>