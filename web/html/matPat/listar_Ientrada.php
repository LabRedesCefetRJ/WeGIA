<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if(session_status() === PHP_SESSION_NONE)
	session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location:  " . WWW . "html/index.php");
	exit();
}else{
	session_regenerate_id();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

permissao($_SESSION['id_pessoa'], 23, 5);
// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";
?>

<!doctype html>
<html class="fixed">

<head>
	<?php
	include_once ROOT . '/dao/Conexao.php';
	include_once ROOT . '/dao/IentradaDAO.php';

	$pdo = Conexao::connect();

	$stmtOrigens = $pdo->query("
    	SELECT id_origem, nome_origem
    	FROM origem
    	ORDER BY nome_origem
	");
	$origens = $stmtOrigens->fetchAll(PDO::FETCH_ASSOC);

	$stmtTiposEntrada = $pdo->query("
    	SELECT id_tipo, descricao
    	FROM tipo_entrada
    	ORDER BY descricao
	");
	$tiposEntrada = $stmtTiposEntrada->fetchAll(PDO::FETCH_ASSOC);


	if (!isset($_SESSION['ientrada'])) {
		header('Location: ' . WWW . 'controle/control.php?metodo=listarId&nomeClasse=IentradaControle&nextPage=' . WWW . 'html/matPat/listar_Ientrada.php');
	}
	if (isset($_SESSION['ientrada'])) {
    	$dadosIentrada = $_SESSION['ientrada'];

    	if (is_string($dadosIentrada)) {
        	$dadosIentrada = json_decode($dadosIentrada, true);

        	if (is_string($dadosIentrada)) {
            	$dadosIentrada = json_decode($dadosIentrada, true);
        	}
    	}

    	if (!is_array($dadosIentrada)) {
        	$dadosIentrada = [];
    	}

    	$ientrada = json_encode($dadosIentrada);
	}
	if (!isset($_SESSION['entradaUnica'])) {
		header('Location: ' . WWW . 'controle/control.php?metodo=listarId&nomeClasse=IentradaControle&nextPage=' . WWW . 'html/matPat/listar_Ientrada.php');
	}
	if (isset($_SESSION['entradaUnica'])) {
		$entrada = $_SESSION['entradaUnica'];
	}
	?>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Informações Detalhadas de Entrada</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

	<!-- Specific Page Vendor CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/select2/select2.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

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
		async function getEntrada() {
			return await <?php echo json_encode($entrada); ?>;
		}


		document.addEventListener("DOMContentLoaded", async () => {
			const container = document.getElementById("containerInformacoesDeEntrada");

			let entrada = await getEntrada();

			entrada = entrada[0];

			const campos = [{
					label: "Almoxarifado",
					valor: entrada.descricao_almoxarifado
				},
				{
					label: "Origem",
					valor: entrada.nome_origem
				},
				{
					label: "Tipo",
					valor: entrada.descricao
				},
				{
					label: "Responsável",
					valor: entrada.nome
				},
				{
					label: "Valor Total",
					valor: entrada.valor_total
				},
				{
					label: "Data",
					valor: entrada.data
				},
				{
					label: "Hora",
					valor: entrada.hora
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
			var ientrada = <?php
							echo $ientrada;
							?>;

			if (typeof ientrada === 'string') {
    			try {
        			ientrada = JSON.parse(ientrada);
    			} catch (e) {
        			ientrada = [];
    			}
			}

			$.each(ientrada, function(i, item) {

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

		function anularEntrada() {
    		if (!confirm("Tem certeza que deseja anular esta entrada? O estoque será ajustado.")) {
        		return;
    		}

    		$.ajax({
        		url: "<?= WWW ?>controle/control.php",
        		method: "POST",
        		dataType: "json",
        		data: {
            		nomeClasse: "EntradaControle",
            		metodo: "anular",
            		id_entrada: <?= (int)$entrada[0]['id_entrada'] ?>
        		},
        		success: function(resposta) {
            		if (resposta.sucesso) {
                		alert(resposta.mensagem);
                		window.location.href = "<?= WWW ?>html/matPat/listar_entrada.php";
            		} else {
                		alert(resposta.mensagem || "Erro ao anular entrada.");
            		}
        		},
        		error: function(xhr) {
            		let mensagem = "Erro ao anular entrada.";

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

		function abrirModalEditarEntrada() {
    		let entrada = <?php echo json_encode($entrada); ?>;
    		let itens = <?php echo $ientrada; ?>;

			if (typeof itens === 'string') {
    			try {
        			itens = JSON.parse(itens);
    			} catch (e) {
        			itens = [];
    			}
			}

    		entrada = entrada[0];

    		$('#editar-data-entrada').val(dataBRParaInput(entrada.data));
    		$('#editar-hora-entrada').val((entrada.hora || '').substring(0, 5));
    		$('#editar-origem-entrada').val(entrada.id_origem);
    		$('#editar-tipo-entrada').val(entrada.id_tipo);

    		$('#editar-itens-entrada').empty();

    		$.each(itens, function(i, item) {
    			$('#editar-itens-entrada').append(`
        			<tr>
            			<td>${item.descricao}</td>
            			<td>
                			<input 
                    			type="number" 
                    			step="1" 
                    			min="1"
                    			class="form-control qtd-item-entrada"
                    			data-id-ientrada="${item.id_ientrada}"
                    			value="${item.qtd}"
                			>
            			</td>
            			<td>
                			<input 
                    			type="number" 
                    			step="0.01" 
                    			min="0"
                    			class="form-control valor-item-entrada"
                    			data-id-ientrada="${item.id_ientrada}"
                    			value="${item.valor_unitario}"
                			>
            			</td>
            			<td>${item.unidade}</td>
            			<td>R$ ${(Number(item.qtd) * Number(item.valor_unitario)).toFixed(2)}</td>
        			</tr>
    			`);
			});

    		$('#modalEditarEntrada').modal('show');
		}

		function salvarEdicaoEntrada() {
    		const itens = [];

    		$('.qtd-item-entrada').each(function() {
        		const idIentrada = $(this).data('id-ientrada');
        		const qtd = $(this).val();
        		const valorUnitario = $('.valor-item-entrada[data-id-ientrada="' + idIentrada + '"]').val();

        		itens.push({
            		id_ientrada: idIentrada,
            		qtd: qtd,
            		valor_unitario: valorUnitario
        		});
    		});

			console.log("ITENS ENVIADOS:", itens);
			console.log("DATA:", $('#editar-data-entrada').val());
			console.log("HORA:", $('#editar-hora-entrada').val());
			console.log("ORIGEM:", $('#editar-origem-entrada').val());
			console.log("TIPO:", $('#editar-tipo-entrada').val());

    		$.ajax({
        		url: "<?= WWW ?>controle/control.php",
        		method: "POST",
        		dataType: "json",
        		data: {
            		nomeClasse: "EntradaControle",
            		metodo: "editar",
            		id_entrada: <?= (int)$entrada[0]['id_entrada'] ?>,
            		data: $('#editar-data-entrada').val(),
            		hora: $('#editar-hora-entrada').val(),
            		id_origem: $('#editar-origem-entrada').val(),
            		id_tipo: $('#editar-tipo-entrada').val(),
            		itens: JSON.stringify(itens)
        		},
        		success: function(resposta) {
            		if (resposta.sucesso) {
    					alert(resposta.mensagem);
    					location.reload();
					} else {
                		alert(resposta.mensagem || "Erro ao editar entrada.");
            		}
        		},
        		error: function(xhr) {
            		let mensagem = "Erro ao editar entrada.";

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
		<!-- start: header -->
		<div id="header"></div>
		<!-- end: header -->
		<div class="inner-wrapper">
			<!-- start: sidebar -->
			<aside id="sidebar-left" class="sidebar-left menuu"></aside>

			<!-- end: sidebar -->
			<section role="main" class="content-body">
				<header class="page-header">
					<h2>Informações Detalhadas de Entrada</h2>

					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="<?= WWW ?>html/home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Informações Detalhadas Entrada</span></li>
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
						<h2 class="panel-title">Entrada Detalhada</h2>
					</header>
					<div class="panel-body">
						<?php if ((int)$entrada[0]['ativo'] === 1): ?>
    						<div style="margin-bottom: 15px;">
        						<button type="button" class="btn btn-primary" onclick="abrirModalEditarEntrada()">
            						Editar entrada
        						</button>

        						<button type="button" class="btn btn-danger" onclick="anularEntrada()">
            						Anular entrada
       							 </button>
    						</div>
						<?php else: ?>
    						<div class="alert alert-warning">
        						Esta entrada foi anulada.
   							</div>
						<?php endif; ?>

						<div id="containerInformacoesDeEntrada" class="container"></div>

						<table class="table table-bordered table-striped mb-none" id="datatable-default">
							<thead>
								<tr>
									<th>Produto</th>
									<th>Quantidade</th>
									<th>Valor Unitario</th>
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

	<div class="modal fade" id="modalEditarEntrada" tabindex="-1" role="dialog" aria-labelledby="modalEditarEntradaLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalEditarEntradaLabel">Editar entrada</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <label>Data</label>
                        <input type="date" id="editar-data-entrada" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Hora</label>
                        <input type="time" id="editar-hora-entrada" class="form-control">
                    </div>
                </div>

                <br>

                <div class="row">
                    <div class="col-md-6">
                        <label>Origem</label>
                        <select id="editar-origem-entrada" class="form-control">
                            <?php foreach ($origens as $origem): ?>
                                <option value="<?= (int)$origem['id_origem'] ?>">
                                    <?= htmlspecialchars($origem['nome_origem'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Tipo de entrada</label>
                        <select id="editar-tipo-entrada" class="form-control">
                            <?php foreach ($tiposEntrada as $tipoEntrada): ?>
                                <option value="<?= (int)$tipoEntrada['id_tipo'] ?>">
                                    <?= htmlspecialchars($tipoEntrada['descricao'], ENT_QUOTES, 'UTF-8') ?>
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
                    <tbody id="editar-itens-entrada"></tbody>
                </table>

                <p class="text-muted">
                    A edição altera apenas esta entrada. O cadastro do produto e o estoque não serão alterados.
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    Cancelar
                </button>

                <button type="button" class="btn btn-primary" onclick="salvarEdicaoEntrada()">
                    Salvar alterações
                </button>
            </div>
        </div>
    </div>
</div>
</body>

</html>