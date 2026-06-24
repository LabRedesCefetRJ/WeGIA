<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE)
	session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location:  " . WWW . "html/index.php");
	exit();
} else {
	session_regenerate_id();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

permissao($_SESSION['id_pessoa'], 24, 5);
// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";
?>
<!doctype html>
<html class="fixed">

<head>
	<?php
	include_once ROOT . '/dao/Conexao.php';
	include_once ROOT . '/dao/SaidaDAO.php';

	$pdo = Conexao::connect();

	$stmtDestinos = $pdo->query("
    	SELECT id_destino, nome_destino
    	FROM destino
    	ORDER BY nome_destino
	");
	$destinos = $stmtDestinos->fetchAll(PDO::FETCH_ASSOC);

	$stmtTiposSaida = $pdo->query("
    	SELECT id_tipo, descricao
    	FROM tipo_saida
    	ORDER BY descricao
	");
	$tiposSaida = $stmtTiposSaida->fetchAll(PDO::FETCH_ASSOC);

	if (!isset($_SESSION['isaida'])) {
		header('Location: ' . WWW . 'controle/control.php?metodo=listarId&nomeClasse=IsaidaControle&nextPage=' . WWW . 'html/matPat/listar_Isaida.php');
	}
	if (isset($_SESSION['isaida'])) {
    	$dadosIsaida = $_SESSION['isaida'];

    	if (is_string($dadosIsaida)) {
        	$dadosIsaida = json_decode($dadosIsaida, true);

        	if (is_string($dadosIsaida)) {
            	$dadosIsaida = json_decode($dadosIsaida, true);
        	}
    	}

    	if (!is_array($dadosIsaida)) {
        	$dadosIsaida = [];
    	}

    	$isaida = json_encode($dadosIsaida);
	}
	if (!isset($_SESSION['saidaUnica'])) {
		header('Location: ' . WWW . 'controle/control.php?metodo=listarId&nomeClasse=IsaidaControle&nextPage=' . WWW . 'html/matPat/listar_Isaida.php');
	}
	if (isset($_SESSION['saidaUnica'])) {
		$saida = $_SESSION['saidaUnica'];
	}
	?>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Informações Detalhadas De Saída</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />

	<!-- Specific Page Vendor CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/select2/select2.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">


	<!-- Theme CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="<?= WWW ?>assets/vendor/modernizr/modernizr.js"></script>
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">

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
	<script src="<?= WWW ?>Functions/onlyNumbers.js"></script>
	<script src="<?= WWW ?>Functions/onlyChars.js"></script>
	<script src="<?= WWW ?>Functions/enviar_dados.js"></script>
	<script src="<?= WWW ?>Functions/mascara.js"></script>

	<!-- jquery functions -->
	<script>
		async function getSaida() {
			return  await <?php echo json_encode($saida); ?>;
		}


		document.addEventListener("DOMContentLoaded", async () => {
			const container = document.getElementById("containerInformacoesDeSaida");

			let saida = await getSaida();

			const campos = [{
					label: "Almoxarifado",
					valor: saida.descricao_almoxarifado
				},
				{
					label: "Destino",
					valor: saida.nome_destino
				},
				{
					label: "Tipo",
					valor: saida.descricao
				},
				{
					label: "Responsável",
					valor: saida.nome
				},
				{
					label: "Valor Total",
					valor: saida.valor_total
				},
				{
					label: "Data",
					valor: saida.data
				},
				{
					label: "Hora",
					valor: saida.hora
				}
			];

			campos.forEach(campo => {
				const div = document.createElement("div");
				div.className = "linha-informacao";

				const span = document.createElement("span");
				span.className = "campoDeTexto";
				span.textContent = `${campo.label}:`;

				const texto = document.createElement("p");
				texto.textContent = campo.valor;

				div.appendChild(span);
				div.appendChild(texto);
				container.appendChild(div);
			});
		});


		$(function() {
			var isaida = <?php
							echo $isaida;
							?>;

			if (typeof isaida === 'string') {
    			try {
        			isaida = JSON.parse(isaida);
    			} catch (e) {
        			isaida = [];
    			}
			}


			$.each(isaida, function(i, item) {

				$('#tabela')
					.append($('<tr />')
						.append($('<td />')
							.text(item.descricao))
						.append($('<td />')
							.text(item.qtd))
						.append($('<td />')
							.text(item.valor_unitario))
						.append($('<td />')
							.text(item.unidade))
						.append($('<td />')
							.text(item.valor_unitario * item.qtd)))
			});
		});
		$(function() {
			$("#header").load("<?= WWW ?>html/header.php");
			$(".menuu").load("<?= WWW ?>html/menu.php");
		});

		$(document).ready(function() {
			$('#datatable-default').DataTable({
				paging: false,
				searching: false,
				info: false,
				lengthChange: false,
				ordering: false
			});
		});

		function anularSaida() {
    		if (!confirm("Tem certeza que deseja anular esta saída? Os produtos serão devolvidos ao estoque.")) {
        		return;
    		}

    		$.ajax({
        		url: "<?= WWW ?>controle/control.php",
        		method: "POST",
        		dataType: "json",
        		data: {
            		nomeClasse: "SaidaControle",
            		metodo: "anular",
            		id_saida: <?= (int)$saida['id_saida'] ?>
        		},
        		success: function(resposta) {
            		if (resposta.sucesso) {
                		alert(resposta.mensagem);
                		window.location.href = "<?= WWW ?>html/matPat/listar_saida.php";
            		} else {
                		alert(resposta.mensagem || "Erro ao anular saída.");
            		}
        		},
        		error: function(xhr) {
            		let mensagem = "Erro ao anular saída.";

            		if (xhr.responseJSON && xhr.responseJSON.mensagem) {
                		mensagem = xhr.responseJSON.mensagem;
            		}

            		alert(mensagem);
        		}
    		});
		}

		function dataBRParaInput(dataBR) {
    		if (!dataBR) return '';

    		const partes = dataBR.split('/');

    		if (partes.length !== 3) {
        		return dataBR;
    		}

    		return partes[2] + '-' + partes[1] + '-' + partes[0];
		}

		function abrirModalEditarSaida() {
    		let saida = <?php echo json_encode($saida); ?>;
    		let itens = <?php echo $isaida; ?>;

    		if (typeof itens === 'string') {
        		try {
            		itens = JSON.parse(itens);
        		} catch (e) {
            		itens = [];
        		}
    		}

    		$('#editar-data-saida').val(dataBRParaInput(saida.data));
    		$('#editar-hora-saida').val((saida.hora || '').substring(0, 5));

    		$('#editar-destino-saida').val(String(saida.id_destino));
    		$('#editar-tipo-saida').val(String(saida.id_tipo));

    		$('#editar-itens-saida').empty();

    		$.each(itens, function(i, item) {
        		$('#editar-itens-saida').append(`
            		<tr>
                		<td>${item.descricao}</td>
                		<td>
                    		<input 
                        		type="number" 
                        		step="1" 
                        		min="1"
                        		class="form-control qtd-item-saida"
                        		data-id-isaida="${item.id_isaida}"
                        		value="${item.qtd}"
                    		>
                		</td>
                		<td>
                   			<input 
                        		type="number" 
                        		step="0.01" 
                        		min="0"
                        		class="form-control valor-item-saida"
                        		data-id-isaida="${item.id_isaida}"
                        		value="${item.valor_unitario}"
                    		>
                		</td>
                		<td>${item.unidade}</td>
                		<td>R$ ${(Number(item.qtd) * Number(item.valor_unitario)).toFixed(2)}</td>
            		</tr>
        		`);
    		});

    		$('#modalEditarSaida').modal('show');
		}

		function salvarEdicaoSaida() {
    		const itens = [];

    		$('.qtd-item-saida').each(function() {
        		const idIsaida = $(this).data('id-isaida');
        		const qtd = $(this).val();
        		const valorUnitario = $('.valor-item-saida[data-id-isaida="' + idIsaida + '"]').val();

        		itens.push({
            		id_isaida: idIsaida,
            		qtd: qtd,
            		valor_unitario: valorUnitario
        		});
    		});

    		const idDestino = $('#editar-destino-saida').val();
    		const idTipo = $('#editar-tipo-saida').val();

    		if (!idDestino) {
        		alert("Selecione um destino.");
        		return;
   			}

    		if (!idTipo) {
        		alert("Selecione um tipo de saída.");
        		return;
    		}

    		if (itens.length === 0) {
        		alert("Nenhum item informado para edição.");
        		return;
    		}

    		$.ajax({
        		url: "<?= WWW ?>controle/control.php",
        		method: "POST",
        		dataType: "json",
        		data: {
            		nomeClasse: "SaidaControle",
            		metodo: "editar",
            		id_saida: <?= (int)$saida['id_saida'] ?>,
            		data: $('#editar-data-saida').val(),
            		hora: $('#editar-hora-saida').val(),
            		id_destino: idDestino,
            		id_tipo: idTipo,
            		itens: JSON.stringify(itens)
        		},
        		success: function(resposta) {
            		if (resposta.sucesso) {
                		alert(resposta.mensagem);
                		location.reload();
            		} else {
                		alert(resposta.mensagem || "Erro ao editar saída.");
            		}
        		},
        		error: function(xhr) {
            		console.log("ERRO STATUS:", xhr.status);
            		console.log("ERRO RESPOSTA:", xhr.responseText);

            		let mensagem = "Erro ao editar saída.";

            		if (xhr.responseJSON && xhr.responseJSON.mensagem) {
                		mensagem = xhr.responseJSON.mensagem;
            		}

            		alert(mensagem);
        		}
    		});
		}
	</script>
	<style>
		.linha-informacao {
			margin-bottom: 10px;
		}

		.linha-informacao span {
			font-weight: bold;
			font-size: 13px;
			display: inline-block;
			min-width: 140px;
		}

		.linha-informacao p {
			display: inline;
			font-size: 13px;
			margin: 0;
		}

		@media (max-width: 768px) {

			.linha-informacao span,
			.linha-informacao p {
				display: block;
				font-size: 14px;
			}
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
					<h2>Informações Detalhadas De Saída</h2>

					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="<?= WWW ?>html/home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Informações Detalhadas Saída</span></li>
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
						<h2 class="panel-title">Saída Detalhada</h2>
					</header>
					<div class="panel-body">
						<?php if ((int)$saida['ativo'] === 1): ?>
    						<div style="margin-bottom: 15px;">
        						<button type="button" class="btn btn-primary" onclick="abrirModalEditarSaida()">
            						Editar saída
        						</button>

        						<button type="button" class="btn btn-danger" onclick="anularSaida()">
            						Anular saída
        						</button>
    						</div>
						<?php else: ?>
    						<div class="alert alert-warning">
        						Esta saída foi anulada.
    						</div>
						<?php endif; ?>

						<div id="containerInformacoesDeSaida" class="container"></div>
						<table class="table table-bordered table-striped mb-none" id="datatable-default">
							<thead>
								<tr>
									<th>Produto</th>
									<th>Quantidade</th>
									<th>Valor Unitário</th>
									<th>Tipo de Unidade</th>
									<th>Valor Total</th>
								</tr>
							</thead>
							<tbody id="tabela">
							</tbody>
						</table>
					</div><br>
				</section>
			</section>
		</div>
	</section>
	<!-- end: page -->

	<!-- Specific Page Vendor -->
	<script src="<?= WWW ?>assets/vendor/select2/select2.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>

	<!-- Theme Base, Components and Settings -->
	<script src="<?= WWW ?>assets/javascripts/theme.js"></script>

	<!-- Theme Custom -->
	<script src="<?= WWW ?>assets/javascripts/theme.custom.js"></script>

	<!-- Theme Initialization Files -->
	<script src="<?= WWW ?>assets/javascripts/theme.init.js"></script>

	<!-- Examples -->
	<script src="<?= WWW ?>assets/javascripts/tables/examples.datatables.default.js"></script>
	<script src="<?= WWW ?>assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
	<script src="<?= WWW ?>assets/javascripts/tables/examples.datatables.tabletools.js"></script>

	<div class="modal fade" id="modalEditarSaida" tabindex="-1" role="dialog" aria-labelledby="modalEditarSaidaLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalEditarSaidaLabel">Editar saída</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <label>Data</label>
                        <input type="date" id="editar-data-saida" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Hora</label>
                        <input type="time" id="editar-hora-saida" class="form-control">
                    </div>
                </div>

                <br>

                <div class="row">
                    <div class="col-md-6">
                        <label>Destino</label>
                        <select id="editar-destino-saida" class="form-control">
                            <?php foreach ($destinos as $destino): ?>
                                <option value="<?= (int)$destino['id_destino'] ?>">
                                    <?= htmlspecialchars($destino['nome_destino'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Tipo de saída</label>
                        <select id="editar-tipo-saida" class="form-control">
                            <?php foreach ($tiposSaida as $tipoSaida): ?>
                                <option value="<?= (int)$tipoSaida['id_tipo'] ?>">
                                    <?= htmlspecialchars($tipoSaida['descricao'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <hr>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Valor Unitário</th>
                            <th>Unidade</th>
                            <th>Total antigo</th>
                        </tr>
                    </thead>
                    <tbody id="editar-itens-saida"></tbody>
                </table>

                <p class="text-muted">
                    A edição altera apenas esta saída. O cadastro do produto não será alterado.
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    Cancelar
                </button>

                <button type="button" class="btn btn-primary" onclick="salvarEdicaoSaida()">
                    Salvar alterações
                </button>
            </div>
        </div>
    </div>
</div>
</body>

</html>