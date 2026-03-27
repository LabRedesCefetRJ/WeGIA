<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION['usuario'])) {
	header("Location: ../index.php");
	exit(401);
} else {
	session_regenerate_id();
}

require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 14);

require_once '../../dao/Conexao.php';
require_once '../../dao/ProcessoAceitacaoDAO.php';
require_once '../../dao/PaStatusDAO.php';
require_once "../personalizacao_display.php";
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

try {
	$pdo             = Conexao::connect();

	//buscar status do processo
	$paStatusDao = new PaStatusDAO($pdo);
	$statusProcesso =  $paStatusDao->listarTodos();

	//pegar status da requisição
	$idStatusGet = isset($_GET['status-processo']) ? filter_input(INPUT_GET, 'status-processo', FILTER_SANITIZE_NUMBER_INT) : 1;

	if ($idStatusGet === false)
		$idStatusGet = 1;

	$processoDAO     = new ProcessoAceitacaoDAO($pdo);
	$processosAceitacao = $processoDAO->getByStatus($idStatusGet);

	define('ID_STATUS_CONCLUIDO', 2);

	$processosConcluidos = [];
	foreach ($processosAceitacao as $processo) {
		if (isset($processo['id_status']) && (int)$processo['id_status'] === ID_STATUS_CONCLUIDO) {
			$processosConcluidos[] = (int)$processo['id'];
		}
	}

	$showCpfColumn = false;

	foreach($processosAceitacao as $processo){
		if(!empty($processo['cpf'])){
			$showCpfColumn = true;
			break;
		}
	}

	$msg   = $_SESSION['msg'] ?? '';
	$error = $_SESSION['mensagem_erro'] ?? '';
	unset($_SESSION['msg'], $_SESSION['mensagem_erro']);
} catch (Exception $e) {
	Util::tratarException($e);
	header("Location: ../home.php");
	exit();
}

?>


<!doctype html>
<html class="fixed">

<head>

	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Processo de Aceitação</title>

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
		$(function() {
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
					<h2>Processo de Aceitação</h2>

					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li><a href="../index.php"> <i class="fa fa-home"></i>
								</a></li>
							<li><span>Processo de Aceitação</span></li>
						</ol>

						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>

				</header>

				<!-- start: page -->
				<?php if ($msg): ?>
					<div class="alert alert-success alert-block">
						<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
						<p><?= htmlspecialchars($msg) ?></p>
					</div>
				<?php endif; ?>

				<?php if ($error): ?>
					<div class="alert alert-danger alert-block">
						<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
						<p><?= htmlspecialchars($error) ?></p>
					</div>
				<?php endif; ?>

				<div class="mb-4">
					<button type="button" class="btn btn-primary" style="margin-bottom: 15px;" data-toggle="modal" data-target="#modalNovoProcesso">
						<i class="fa fa-plus"></i> Cadastrar Novo Processo
					</button>
				</div>

				<section class="panel panel-primary">
					<header class="panel-heading">
						<h2 class="panel-title">Lista de Processos</h2>
						<div class="form-inline" style="margin-top: 10px;">
							<label for="status-processo">Status: </label>
							<select class="form-control" name="status-processo" id="status-processo">
								<?php foreach ($statusProcesso as $status): ?>
									<option value="<?= $status['id'] ?>"> <?= htmlspecialchars($status['descricao']) ?></option>
								<?php endforeach; ?>
							</select>

							<button type="button" class="btn btn-default" id="listar-processo">
								Listar
							</button>
						</div>
					</header>
					<div class="panel-body">
						<?php if (empty($processosAceitacao)): ?>
							<div class="alert alert-warning">
								Nenhum processo encontrado.
							</div>
						<?php else: ?>
							<div class="table-responsive">
								<table class="table table-striped table-bordered table-hover">
									<thead>
										<tr>
											<th>Nome</th>
											<th <?php if(!$showCpfColumn) echo 'style="display:none"' ?>>CPF</th> <!-- display:none caso todos os cpfs sejam nulos -->
											<th>Descrição</th>
											<th>Etapas</th>
											<th>Arquivos</th>
											<th>Ações</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($processosAceitacao as $processo): ?>
											<tr>
												<td onclick="window.location.href = './etapa_processo.php?id=<?= (int)$processo['id'] ?>'"><a href="etapa_processo.php?id=<?= (int)$processo['id'] ?>" style="color: inherit"><?= htmlspecialchars($processo['nome'] . ' ' . $processo['sobrenome']) ?></a></td>
												<td <?php if(!$showCpfColumn) echo 'style="display:none"' ?>><?= isset($processo['cpf']) && !empty($processo['cpf']) ? htmlspecialchars($processo['cpf']) : 'Não informado.' ?></td>
												<td style="max-width: 150px;"><?= isset($processo['descricao']) && !empty($processo['descricao']) ? nl2br(html_entity_decode($processo['descricao'], ENT_QUOTES, 'UTF-8')) : '' ?></td>
												<td>
													<a href="etapa_processo.php?id=<?= (int)$processo['id'] ?>" class="btn btn-xs btn-primary">
														<i class="fa fa-edit"></i>
													</a>
												</td>

												<td>
													<button type="button"
														class="btn btn-xs btn-info btn-arquivos-processo"
														data-toggle="modal"
														data-target="#modalArquivosProcesso"
														data-id_processo="<?= (int)$processo['id'] ?>"
														data-nome="<?= htmlspecialchars($processo['nome'] . ' ' . $processo['sobrenome'], ENT_QUOTES) ?>">
														<i class="fa fa-paperclip"></i>
													</button>
												</td>

												<td style="max-width:150px; white-space: normal;">
													<?php
													$atendidoId = $processoDAO->getIdAtendido($processo['id']);

													if ($atendidoId != false && $atendidoId >= 1):
													?>
														<a href="Profile_Atendido.php?idatendido=<?= htmlspecialchars($atendidoId) ?>"
															class="btn btn-xs btn-success">
															<i class="fa-solid fa-eye"></i> Ver Perfil
														</a>

													<?php elseif (in_array((int)$processo['id'], $processosConcluidos)): ?>
														<a href="../../controle/control.php?nomeClasse=ProcessoAceitacaoControle&metodo=criarAtendidoProcesso&id_processo=<?= (int)$processo['id'] ?>"
															class="btn btn-xs btn-success"
															onclick="return confirm('Confirmar cadastro de atendido para este processo?');">
															<i class="fa fa-user-plus"></i> Cadastrar Atendido
														</a>
													<?php else: ?>
														<button type="button"
															class="btn btn-xs btn-success"
															disabled
															title="O processo precisa ser concluído antes de criar o atendido"
															style="cursor: not-allowed;">
															<i class="fa fa-user-plus"></i> Cadastrar Atendido
														</button>
													<?php endif; ?>

													<button type="button" class="btn btn-xs btn-primary btn-alter-status" data-toggle="modal" data-id_processo="<?= htmlspecialchars($processo['id']) ?> " data-descricao="<?= isset($processo['descricao']) && !empty($processo['descricao']) ? htmlspecialchars($processo['descricao']) : '' ?>" data-target="#modalStatusProcesso">
														Alterar Processo
													</button>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						<?php endif; ?>
					</div>
				</section>

				<div class="modal fade" id="modalStatusProcesso" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog" role="document">
						<form method="post" action="../../controle/control.php" class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">Alterar Processo</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<input type="hidden" name="nomeClasse" value="ProcessoAceitacaoControle">
								<input type="hidden" name="metodo" value="atualizarStatus">
								<input type="hidden" name="id_processo" id="modal-id-processo">

								<div class="form-group">
									<label>Status do Processo:</label>
									<button type="button" onclick="adicionar_status()" class="btn btn-link p-0">
										<i class="fa fa-plus"></i>
									</button>

									<select name="id_status" id="selectStatusProcesso" class="form-control select-status-processo" style="min-width: 200px;">
										<?php foreach ($statusProcesso as $status): ?>
											<option value="<?= $status['id'] ?>"> <?= htmlspecialchars($status['descricao']) ?></option>
										<?php endforeach; ?>
									</select>
								</div>

								<div class="form-group">
									<label>Descrição</label>
									<textarea class="form-control" rows="5" name="descricao" id="edit_descricao"></textarea>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
								<button type="submit" class="btn btn-primary">Salvar</button>
							</div>
						</form>
					</div>
				</div>

				<div class="modal fade" id="modalNovoProcesso" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog" role="document">
						<form method="post" action="../../controle/control.php" class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">Novo Processo de Aceitação</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<input type="hidden" name="nomeClasse" value="ProcessoAceitacaoControle">
								<input type="hidden" name="metodo" value="incluir">

								<div class="form-group">
									<label>Nome <span class="text-danger">*</span></label>
									<input type="text" name="nome" class="form-control" required />
								</div>
								<div class="form-group">
									<label>Sobrenome <span class="text-danger">*</span></label>
									<input type="text" name="sobrenome" class="form-control" required />
								</div>
								<div class="form-group">
									<label>CPF</label>
									<input type="text"
										name="cpf"
										id="cpf"
										maxlength="14"
										placeholder="000.000.000-00"
										onkeypress="return Onlynumbers(event)"
										onkeyup="mascara('###.###.###-##',this,event)"
										onblur="validarCPF(this.value)"
										class="form-control" />
									<p id="cpfInvalido" style="display: none; color: #b30000; font-size: 12px;">CPF INVÁLIDO!</p>
								</div>

								<div class="form-group">
									<label>Descrição</label>
									<textarea class="form-control" rows="5" name="descricao"></textarea>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
								<button type="submit" class="btn btn-success" id="enviar">Cadastrar Processo</button>
							</div>
						</form>
					</div>
				</div>

				<div class="modal fade" id="modalArquivosProcesso" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog modal-lg" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">
									Arquivos do Processo <span id="tituloProcesso"></span>
								</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<div id="lista-arquivos-processo"></div>

								<hr>
								<form id="formUploadDocProcesso" method="post" action="../../controle/control.php" enctype="multipart/form-data">
									<input type="hidden" name="nomeClasse" value="PaArquivoControle">
									<input type="hidden" name="metodo" value="upload">
									<input type="hidden" name="id_processo" id="upload_id_processo">

									<div class="form-group">
										<label class="my-1 mr-2" for="tipoDocumentoProcesso">Tipo de Documento <span class="text-danger">*</span></label>
										<div style="display: flex; align-items: center; gap: 10px;">
											<select name="id_tipo_documentacao" class="form-control" id="tipoDocumentoProcesso" required style="flex: 1;">
												<option selected disabled value="">Selecionar...</option>
												<?php
												foreach ($pdo->query("SELECT * FROM atendido_docs_atendidos ORDER BY descricao ASC")->fetchAll(PDO::FETCH_ASSOC) as $item) {
													echo "<option value='" . $item["idatendido_docs_atendidos"] . "'>" . htmlspecialchars($item["descricao"]) . "</option>";
												}
												?>
											</select>
											<a href="javascript:void(0)" onclick="adicionarTipoProcesso()">
												<i class="fas fa-plus" style="font-size: 20px;"></i>
											</a>
										</div>
									</div>

									<div class="form-group">
										<label for="arquivoProcesso">Arquivo<span class="text-danger">*</span></label>
										<p>Permitido envio de até <?= ini_get('upload_max_filesize') ?> de tamanho por documento.</p>
										<input type="file" name="arquivo" class="form-control-file" id="arquivoProcesso"
											accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.odp" required>
									</div>

									<button type="submit" class="btn btn-primary" onclick="return verificaTipoProcesso(event)" style="margin-top: 10px;">
										<i class="fa fa-upload"></i> Anexar arquivo
									</button>
								</form>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
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

	<script src="<?php echo WWW; ?>Functions/onlyNumbers.js"></script>
	<script src="<?php echo WWW; ?>Functions/onlyChars.js"></script>
	<script src="<?php echo WWW; ?>Functions/mascara.js"></script>
	<script src="<?php echo WWW; ?>Functions/testaCPF.js"></script>

	<style type="text/css">
		.obrig {
			color: #ff0000;
		}
	</style>

	<script>
		$(function() {
			$('.btn-arquivos-processo').on('click', function() {
				var idProcesso = $(this).data('id_processo');
				var nomeProc = $(this).data('nome');

				$('#upload_id_processo').val(idProcesso);
				$('#tituloProcesso').text(' - ' + nomeProc);

				$('#lista-arquivos-processo').load('lista_arquivos_processo.php?id_processo=' + idProcesso);
			});
		});

		function validarCPF(strCPF) {
			if (strCPF.length != 0 && !testaCPF(strCPF)) {
				$('#cpfInvalido').show();
				$('#enviar').prop('disabled', true);
			} else {
				$('#cpfInvalido').hide();
				$('#enviar').prop('disabled', false);
			}
		}

		function Onlynumbers(evt) {
			var charCode = (evt.which) ? evt.which : evt.keyCode;
			if (charCode > 31 && (charCode < 48 || charCode > 57)) {
				return false;
			}
			return true;
		}


		function verificaTipoProcesso(ev) {
			const tipo = document.getElementById('tipoDocumentoProcesso');

			if (!tipo.value || isNaN(tipo.value) || tipo.value < 1) {
				alert('Erro: selecione um tipo de documento adequado antes de prosseguir.');
				ev.preventDefault();
				return false;
			}

			return true;
		}

		function adicionarTipoProcesso() {
			var tipo = window.prompt("Cadastre um Novo Tipo de Documento:");

			if (!tipo) {
				return;
			}

			tipo = tipo.trim();

			if (tipo === '') {
				return;
			}

			$.ajax({
				type: "POST",
				url: '../../dao/adicionar_tipo_docs_atendido.php',
				data: 'tipo=' + tipo,
				success: function(response) {
					gerarTipoProcesso();
				},
				dataType: 'text'
			});
		}

		function gerarTipoProcesso() {
			$.ajax({
				type: "POST",
				url: '../../dao/exibir_tipo_docs_atendido.php',
				data: '',
				success: function(response) {
					$('#tipoDocumentoProcesso').empty();
					$('#tipoDocumentoProcesso').append('<option selected disabled value="">Selecionar...</option>');

					$.each(response, function(i, item) {
						$('#tipoDocumentoProcesso').append(
							'<option value="' + item.idatendido_docs_atendidos + '">' +
							item.descricao +
							'</option>'
						);
					});
				},
				dataType: 'json'
			});
		}
	</script>

	<script>
		// Seleciona o status adequado
		const selectElement = document.getElementById('status-processo');
		selectElement.value = '<?= $idStatusGet ?>';

		const btnListar = document.getElementById('listar-processo');

		btnListar.addEventListener('click', function() {
			const valorStatus = selectElement.value;

			window.location.href =
				'./processo_aceitacao.php?status-processo=' + encodeURIComponent(valorStatus);
		});
	</script>

	<script>
		function decodeHtml(html) {
			return $('<textarea/>').html(html).text();
		}

		$(document).on('click', '.btn-alter-status', function() {

			const idProcesso = $(this).data('id_processo');
			const btn = $(this);

			// Preenche o hidden do modal
			$('#modal-id-processo').val(idProcesso);

			$('#edit_descricao').val(decodeHtml(btn.data('descricao')));

			// Limpa seleção anterior (opcional)
			$('#modalStatusProcesso select[name="id_status"]').val('');

			// Chamada à API
			$.ajax({
				url: '../../controle/control.php',
				type: 'GET',
				dataType: 'json',
				data: {
					id_processo: idProcesso,
					nomeClasse: 'ProcessoAceitacaoControle',
					metodo: 'getStatusDoProcesso'
				},
				success: function(response) {

					if (response.success) {
						const idStatus = response.id_status;

						// Seleciona o option correspondente
						$('#modalStatusProcesso select[name="id_status"]').val(idStatus);
					} else if (response.erro) {
						alert('Não foi possível obter o status do processo: ', erro);
					} else {
						alert('Não foi possível obter o status do processo.');
					}
				},
				error: function() {
					alert('Erro ao consultar o servidor.');
				}
			});
		});
	</script>

	<script src="../../Functions/pa_status.js"></script>
</body>

</html>