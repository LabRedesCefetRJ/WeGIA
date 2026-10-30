<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if(session_status() === PHP_SESSION_NONE)
	session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: ../../index.php");
	exit();
}else{
	session_regenerate_id();
}

// Inclui display de Campos
require_once "../personalizacao_display.php";

// Adiciona o Sistema de Mensagem
require_once "../geral/msg.php";

require_once "../../dao/FuncionarioDAO.php";

$idFuncionario = 0;
$isAdministrador = false;
$erroVerificacaoAdministrador = null;
try {
	$funcionarioDAO = new FuncionarioDAO();
	$idFuncionario = (int) ($funcionarioDAO->getIdFuncionarioComIdPessoa((int) $_SESSION['id_pessoa']) ?? 0);
	$isAdministrador = $funcionarioDAO->verificaAdm((int) $_SESSION['id_pessoa']);
} catch (Throwable $e) {
	error_log('[documento_modelo] Falha ao buscar funcionário/verificar administrador: ' . $e->getMessage());
	$erroVerificacaoAdministrador = 'Não foi possível verificar as permissões do funcionário. Recarregue a página ou contate o suporte.';
}

?>
<!doctype html>
<html class="fixed">

<head>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Modelos de documento</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Web Fonts  -->
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">

	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon">

	<!-- Theme CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />

	<!-- Head Libs -->
	<script src="../../assets/vendor/modernizr/modernizr.js"></script>

	<!-- Vendor -->
	<script src="../../assets/vendor/jquery/jquery.min.js"></script>
	<script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>

</head>

<body>
	<section class="body">
		<div id="header"><?php include '../header.php'; ?></div>
		<!-- end: header -->
		<div class="inner-wrapper">
			<!-- start: sidebar -->
			<aside id="sidebar-left" class="sidebar-left menuu"><?php include '../menu.php'; ?></aside>
			<!-- end: sidebar -->
			<section role="main" class="content-body">
				<header class="page-header">
					<h2>Modelos de documento</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="../home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Configurações</span></li>
							<li><span>Modelos de documento</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>
				<!--start: page-->
				<div class="row">
					<div class="col-md-12">
						<div id="feedback-modelo" aria-live="polite"></div>
						<?php if ($erroVerificacaoAdministrador !== null) : ?>
							<div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($erroVerificacaoAdministrador, ENT_QUOTES, 'UTF-8'); ?></div>
						<?php endif; ?>
						<div class="panel panel-default">
							<div class="panel-heading clearfix">
								<h3 class="panel-title pull-left">Modelos pessoais</h3>
								<button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#modalNovoModelo" <?php echo $idFuncionario > 0 ? '' : 'disabled'; ?>>
									<i class="fa fa-plus"></i> Novo modelo
								</button>
							</div>
							<div class="panel-body">
								<p>Modelos criados por você. Modelos globais de sua autoria também aparecem nesta lista.</p>
								<div class="table-responsive">
									<table class="table table-striped table-bordered">
										<thead>
											<tr>
												<th>Descrição</th>
												<th>Formato</th>
												<th>Data de cadastro</th>
												<th>Ações</th>
											</tr>
										</thead>
										<tbody id="modelos-pessoais">
											<tr><td colspan="4" class="text-center">Carregando modelos...</td></tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h3 class="panel-title">Modelos globais</h3>
							</div>
							<div class="panel-body">
								<p>Modelos globais criados por outros funcionários e disponíveis para todos.</p>
								<div class="table-responsive">
									<table class="table table-striped table-bordered">
										<thead>
											<tr>
												<th>Descrição</th>
												<th>Formato</th>
												<th>Data de cadastro</th>
												<th>Ações</th>
											</tr>
										</thead>
										<tbody id="modelos-globais">
											<tr><td colspan="4" class="text-center">Carregando modelos...</td></tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- end: page-->
			</section>
		</div>
	</section>

	<div class="modal fade" id="modalNovoModelo" tabindex="-1" role="dialog" aria-labelledby="modalNovoModeloLabel">
		<div class="modal-dialog" role="document">
			<form id="formNovoModelo" enctype="multipart/form-data">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="modalNovoModeloLabel">Novo modelo de documento</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="descricaoModelo">Descrição</label>
							<input class="form-control" type="text" id="descricaoModelo" name="descricao" maxlength="255" required>
						</div>
						<div class="form-group">
							<label for="arquivoModelo">Arquivo (.odt ou .docx)</label>
							<input class="form-control" type="file" id="arquivoModelo" name="arquivo" accept=".odt,.docx" required>
						</div>
						<?php if ($isAdministrador) : ?>
							<div class="checkbox">
								<label><input type="checkbox" name="global" value="true"> Disponibilizar como modelo global</label>
							</div>
						<?php endif; ?>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
						<button type="submit" class="btn btn-primary" id="salvarModelo">Salvar modelo</button>
					</div>
				</div>
			</form>
		</div>
	</div>

	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/conf.html" width="200" height="60" style="border:none;"></iframe>
	</div>

	<?php require_once "../../Functions/authenticatedRequest.php"; ?>
	<script>
		(function () {
			const configuracaoModelo = <?= json_encode([
				'idFuncionario' => $idFuncionario,
				'administrador' => $isAdministrador,
			], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
			const apiBase = apiServer + 'configuracao/documentos/modelo';
			const feedback = document.getElementById('feedback-modelo');
			const tbodyPessoal = document.getElementById('modelos-pessoais');
			const tbodyGlobal = document.getElementById('modelos-globais');
			const formulario = document.getElementById('formNovoModelo');
			const botaoSalvar = document.getElementById('salvarModelo');

			function exibirFeedback(mensagem, tipo) {
				const alerta = document.createElement('div');
				alerta.className = `alert alert-${tipo} alert-dismissable`;
				alerta.setAttribute('role', 'alert');

				const fechar = document.createElement('button');
				fechar.type = 'button';
				fechar.className = 'close';
				fechar.setAttribute('data-dismiss', 'alert');
				fechar.setAttribute('aria-label', 'Fechar');
				fechar.innerHTML = '<span aria-hidden="true">&times;</span>';
				alerta.appendChild(fechar);
				alerta.appendChild(document.createTextNode(mensagem));
				feedback.replaceChildren(alerta);
			}

			async function mensagemErroResposta(response) {
				try {
					const resultado = await response.json();
					return resultado.error || resultado.message || `Erro na requisição (${response.status}).`;
				} catch (error) {
					return `Erro na requisição (${response.status}).`;
				}
			}

			function adicionarCelula(linha, conteudo) {
				const celula = document.createElement('td');
				celula.textContent = conteudo;
				linha.appendChild(celula);
				return celula;
			}

			function formatarDataBrasileira(data) {
				const correspondencia = /^(\d{4})-(\d{2})-(\d{2})/.exec(data || '');
				if (!correspondencia) {
					return 'Data indisponível';
				}

				const [, ano, mes, dia] = correspondencia;
				const dataValidada = new Date(Date.UTC(Number(ano), Number(mes) - 1, Number(dia)));
				if (
					dataValidada.getUTCFullYear() !== Number(ano) ||
					dataValidada.getUTCMonth() !== Number(mes) - 1 ||
					dataValidada.getUTCDate() !== Number(dia)
				) {
					return 'Data indisponível';
				}

				return `${dia}/${mes}/${ano}`;
			}

			function adicionarBotaoAcoes(container, modelo, podeGerenciar) {
				const botaoBaixar = document.createElement('button');
				botaoBaixar.type = 'button';
				botaoBaixar.className = 'btn btn-primary btn-sm';
				botaoBaixar.title = 'Baixar arquivo';
				botaoBaixar.setAttribute('aria-label', `Baixar ${modelo.descricao}`);
				botaoBaixar.innerHTML = '<i class="fa fa-download" aria-hidden="true"></i>';
				botaoBaixar.addEventListener('click', () => baixarModelo(modelo));
				container.appendChild(botaoBaixar);

				if (configuracaoModelo.administrador && Number(modelo.global) === 1) {
					const indicativoGlobal = document.createElement('span');
					indicativoGlobal.className = 'label label-info';
					indicativoGlobal.style.marginLeft = '5px';
					indicativoGlobal.textContent = 'Global';
					container.appendChild(indicativoGlobal);
				}

				if (!podeGerenciar) {
					return;
				}

				const acoes = [
					{ texto: 'Editar', icone: 'fa-pencil', acao: 'editar' },
					{ texto: 'Excluir', icone: 'fa-trash', acao: 'excluir' }
				];
				if (configuracaoModelo.administrador) {
					acoes.push({
						texto: Number(modelo.global) === 1 ? 'Tornar pessoal' : 'Tornar global',
						icone: 'fa-globe',
						acao: 'globalidade'
					});
				}

				acoes.forEach((item) => {
					const botao = document.createElement('button');
					botao.type = 'button';
					botao.className = item.acao === 'excluir' ? 'btn btn-danger btn-sm' : 'btn btn-default btn-sm';
					botao.style.marginLeft = '5px';
					botao.title = item.texto;
					botao.setAttribute('aria-label', `${item.texto}: ${modelo.descricao}`);
					botao.innerHTML = `<i class="fa ${item.icone}" aria-hidden="true"></i>`;
					botao.addEventListener('click', () => informarAcaoEmDesenvolvimento(item.acao));
					container.appendChild(botao);
				});
			}

			function informarAcaoEmDesenvolvimento(acao) {
				const mensagens = {
					editar: 'A edição de modelos de documento ainda está em desenvolvimento na API.',
					excluir: 'A exclusão de modelos de documento ainda está em desenvolvimento na API.',
					globalidade: 'A alteração da globalidade ainda está em desenvolvimento na API.'
				};
				exibirFeedback(mensagens[acao], 'warning');
			}

			function renderizarModelos(tbody, modelos, podeGerenciar) {
				tbody.replaceChildren();
				if (modelos.length === 0) {
					const linha = document.createElement('tr');
					const celula = adicionarCelula(linha, 'Nenhum modelo encontrado.');
					celula.colSpan = 4;
					celula.className = 'text-center';
					tbody.appendChild(linha);
					return;
				}

				modelos.forEach((modelo) => {
					const linha = document.createElement('tr');
					adicionarCelula(linha, modelo.descricao || '');
					adicionarCelula(linha, (modelo.extensao || '').toUpperCase());
					adicionarCelula(linha, formatarDataBrasileira(modelo.created_at));
					const celulaAcoes = document.createElement('td');
					adicionarBotaoAcoes(celulaAcoes, modelo, podeGerenciar(modelo));
					linha.appendChild(celulaAcoes);
					tbody.appendChild(linha);
				});
			}

			async function carregarModelos() {
				const response = await authenticatedRequest(() => fetch(apiBase, {
					method: 'GET',
					credentials: 'include',
					headers: { 'X-Client-Type': 'web' }
				}));
				if (!response.ok) {
					throw new Error(await mensagemErroResposta(response));
				}

				const modelos = await response.json();
				if (!Array.isArray(modelos)) {
					throw new Error('A API retornou uma lista de modelos inválida.');
				}

				const pessoais = modelos.filter((modelo) =>
					Number(modelo.id_funcionario) === configuracaoModelo.idFuncionario
				);
				const globais = modelos.filter((modelo) =>
					Number(modelo.global) === 1 &&
					Number(modelo.id_funcionario) !== configuracaoModelo.idFuncionario
				);

				renderizarModelos(tbodyPessoal, pessoais, () => true);
				renderizarModelos(tbodyGlobal, globais, () => configuracaoModelo.administrador);
			}

			async function baixarModelo(modelo) {
				try {
					const response = await authenticatedRequest(() => fetch(`${apiBase}/${modelo.id}/file`, {
						method: 'GET',
						credentials: 'include',
						headers: { 'X-Client-Type': 'web' }
					}));
					if (!response.ok) {
						throw new Error(await mensagemErroResposta(response));
					}

					const arquivo = URL.createObjectURL(await response.blob());
					const link = document.createElement('a');
					link.href = arquivo;
					link.download = `modelo-${modelo.id}.${modelo.extensao || 'odt'}`;
					document.body.appendChild(link);
					link.click();
					link.remove();
					setTimeout(() => URL.revokeObjectURL(arquivo), 1000);
				} catch (error) {
					exibirFeedback(error.message || 'Não foi possível baixar o modelo.', 'danger');
				}
			}

			formulario.addEventListener('submit', async (event) => {
				event.preventDefault();
				const arquivo = document.getElementById('arquivoModelo').files[0];
				if (!arquivo || !/\.(odt|docx)$/i.test(arquivo.name)) {
					exibirFeedback('Selecione um arquivo no formato .odt ou .docx.', 'warning');
					return;
				}

				const dados = new FormData(formulario);
				if (configuracaoModelo.administrador) {
					dados.set('global', formulario.elements.global.checked ? 'true' : 'false');
				} else {
					dados.delete('global');
				}

				botaoSalvar.disabled = true;
				try {
					const response = await authenticatedRequest(() => fetch(apiBase, {
						method: 'POST',
						credentials: 'include',
						headers: { 'X-Client-Type': 'web' },
						body: dados
					}));
					if (!response.ok) {
						throw new Error(await mensagemErroResposta(response));
					}

					const resultado = await response.json();
					$('#modalNovoModelo').modal('hide');
					formulario.reset();
					exibirFeedback(resultado.message || 'Modelo de documento salvo com sucesso.', 'success');
					await carregarModelos();
				} catch (error) {
					exibirFeedback(error.message || 'Não foi possível salvar o modelo de documento.', 'danger');
				} finally {
					botaoSalvar.disabled = false;
				}
			});

			if (configuracaoModelo.idFuncionario < 1) {
				exibirFeedback('Não foi possível identificar o vínculo de funcionário para gerenciar modelos pessoais.', 'danger');
			}
			carregarModelos().catch((error) => {
				exibirFeedback(error.message || 'Não foi possível carregar os modelos de documento.', 'danger');
			});
		})();
	</script>

</body>

</html>