<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE)
	session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: ../../index.php");
	exit();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

// Registrar entradas exige ler e gravar no módulo Recepção (as ações também são verificadas no backend).
permissao($_SESSION['id_pessoa'], 20, 7);

require_once ROOT . '/classes/Csrf.php';
require_once ROOT . '/classes/Visitante.php';
require_once ROOT . '/classes/RascunhoEntrada.php';
require_once "../personalizacao_display.php";

/*
 * Registro de entrada em etapas (issue #2157): 1) visitantes, 2) visitados e descrição, 3) confirmação.
 * Tudo acontece nesta página por fetch (js/registro_entrada.js e RegistroVisitaControle). A entrada em andamento fica
 * salva na sessão, então o recepcionista pode pausar, sair da página e retomar de onde parou.
 */
?>
<!doctype html>
<html class="fixed">

<head>
	<meta charset="UTF-8">
	<title>Registro de Entrada</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">
	<link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
	<link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
	<link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
	<link rel="stylesheet" href="../../css/recepcao.css">

	<script src="../../assets/vendor/modernizr/modernizr.js"></script>
	<script src="../../assets/vendor/jquery/jquery.min.js"></script>
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
		<div id="header"></div>

		<div class="inner-wrapper">
			<aside id="sidebar-left" class="sidebar-left menuu"></aside>

			<section role="main" class="content-body">
				<header class="page-header">
					<h2>Registro de entrada</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li><a href="../home.php"><i class="fa fa-home"></i></a></li>
							<li><span>Recepção</span></li>
							<li><span>Registro de entrada</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->
				<div class="registro-entrada" id="registroEntrada">

					<div class="alert alert-info entrada-retomada" id="avisoRetomada" role="status" hidden>
						<i class="fa fa-clock-o" aria-hidden="true"></i>
						<span id="textoRetomada"></span>
						<button type="button" class="btn btn-link btn-xs" data-acao="descartar">Descartar e começar outra</button>
					</div>

					<nav class="etapas-entrada" aria-label="Etapas do registro de entrada">
						<ol>
							<li data-etapa="1">
								<button type="button" class="etapa-botao" data-ir-etapa="1">
									<span class="etapa-numero">1</span>
									<span class="etapa-texto"><strong>Visitantes</strong><small>Quem está chegando</small></span>
								</button>
							</li>
							<li data-etapa="2">
								<button type="button" class="etapa-botao" data-ir-etapa="2">
									<span class="etapa-numero">2</span>
									<span class="etapa-texto"><strong>Visitados</strong><small>Quem vai receber a visita</small></span>
								</button>
							</li>
							<li data-etapa="3">
								<button type="button" class="etapa-botao" data-ir-etapa="3">
									<span class="etapa-numero">3</span>
									<span class="etapa-texto"><strong>Confirmação</strong><small>Revisar e registrar</small></span>
								</button>
							</li>
						</ol>
						<div class="etapas-progresso" aria-hidden="true"><span id="barraProgresso"></span></div>
						<p class="etapas-status">
							<span id="textoEtapa" aria-live="polite">Etapa 1 de 3</span>
							<span class="etapas-salvo" id="statusSalvo"></span>
						</p>
					</nav>

					<div id="mensagemEntrada" aria-live="assertive"></div>

					<!-- Etapa 1: visitantes -->
					<section class="panel etapa-painel" data-painel="1" hidden>
						<header class="panel-heading">
							<h2 class="panel-title">Quem está chegando?</h2>
						</header>
						<div class="panel-body">
							<form id="formBuscaCpf" class="busca-cpf" autocomplete="off" novalidate>
								<label for="cpfBusca">CPF do visitante</label>
								<div class="input-group">
									<input type="text" class="form-control input-lg" id="cpfBusca" inputmode="numeric" maxlength="14" placeholder="000.000.000-00" aria-describedby="ajudaCpf">
									<span class="input-group-btn">
										<button type="submit" class="btn btn-primary btn-lg" id="botaoBuscarCpf"><i class="fa fa-search" aria-hidden="true"></i> Buscar</button>
									</span>
								</div>
								<p class="help-block" id="ajudaCpf">Digite o CPF e tecle Enter. Se a pessoa ainda não for visitante, o cadastro abre aqui mesmo.</p>
							</form>

							<div id="resultadoCpf"></div>

							<form id="formCadastroVisitante" class="cadastro-visitante" enctype="multipart/form-data" novalidate hidden>
								<h4 id="tituloCadastro">Cadastrar visitante</h4>
								<p class="text-muted" id="explicacaoCadastro"></p>

								<div class="row">
									<div class="col-sm-3 text-center" id="blocoFoto">
										<img src="<?= WWW ?>img/semfoto.png" alt="Foto do visitante" class="cadastro-foto" id="previaFoto">
										<label class="btn btn-default btn-sm cadastro-foto-botao">
											<i class="fa fa-camera" aria-hidden="true"></i> Foto (opcional)
											<input type="file" name="imgperfil" id="fotoVisitante" accept="image/*" class="sr-only">
										</label>
									</div>
									<div class="col-sm-9">
										<div class="row">
											<div class="form-group col-sm-6">
												<label for="cadNome">Nome <span class="campo-obrigatorio">*</span></label>
												<input type="text" class="form-control" name="nome" id="cadNome" required>
											</div>
											<div class="form-group col-sm-6">
												<label for="cadSobrenome">Sobrenome <span class="campo-obrigatorio">*</span></label>
												<input type="text" class="form-control" name="sobrenome" id="cadSobrenome" required>
											</div>
											<div class="form-group col-sm-6">
												<span class="control-label-like">Sexo <span class="campo-obrigatorio">*</span></span>
												<div class="cadastro-sexo" role="radiogroup" aria-label="Sexo">
													<label class="radio-inline"><input type="radio" name="gender" value="f"> Feminino</label>
													<label class="radio-inline"><input type="radio" name="gender" value="m"> Masculino</label>
													<label class="radio-inline"><input type="radio" name="gender" value="o"> Outro</label>
												</div>
											</div>
											<div class="form-group col-sm-6">
												<label for="cadNascimento">Data de nascimento <span class="campo-obrigatorio">*</span></label>
												<input type="date" class="form-control" name="nascimento" id="cadNascimento" min="<?= Visitante::getDataNascimentoMinima() ?>" max="<?= Visitante::getDataNascimentoMaxima() ?>" required>
											</div>
										</div>

										<fieldset class="cadastro-endereco">
											<legend>Endereço <small class="text-muted">(opcional)</small></legend>
											<div class="row">
												<div class="form-group col-sm-4">
													<label for="cadCep">CEP</label>
													<input type="text" class="form-control" name="cep" id="cadCep" maxlength="9" inputmode="numeric" placeholder="00000-000">
												</div>
												<div class="form-group col-sm-5">
													<label for="cadCidade">Cidade</label>
													<input type="text" class="form-control" name="cidade" id="cadCidade">
												</div>
												<div class="form-group col-sm-3">
													<label for="cadUf">UF</label>
													<input type="text" class="form-control" name="uf" id="cadUf" maxlength="2">
												</div>
												<div class="form-group col-sm-8">
													<label for="cadRua">Logradouro</label>
													<input type="text" class="form-control" name="rua" id="cadRua">
												</div>
												<div class="form-group col-sm-4">
													<label for="cadNumero">Número</label>
													<input type="text" class="form-control" name="numero_residencia" id="cadNumero" inputmode="numeric" placeholder="Sem número">
												</div>
												<div class="form-group col-sm-6">
													<label for="cadBairro">Bairro</label>
													<input type="text" class="form-control" name="bairro" id="cadBairro">
												</div>
												<div class="form-group col-sm-6">
													<label for="cadComplemento">Complemento</label>
													<input type="text" class="form-control" name="complemento" id="cadComplemento" maxlength="50">
												</div>
											</div>
											<input type="hidden" name="ibge" id="cadIbge">
										</fieldset>
									</div>
								</div>

								<input type="hidden" name="cpf" id="cadCpf">
								<div class="cadastro-acoes">
									<button type="button" class="btn btn-default" data-acao="cancelar-cadastro">Cancelar</button>
									<button type="submit" class="btn btn-primary" id="botaoCadastrar"><i class="fa fa-user-plus" aria-hidden="true"></i> Cadastrar e adicionar</button>
								</div>
							</form>

							<h4 class="lista-titulo">Visitantes desta entrada <span class="badge" id="contadorVisitantes">0</span></h4>
							<ul class="lista-entrada" id="listaVisitantes"></ul>
						</div>
						<footer class="panel-footer etapa-rodape">
							<button type="button" class="btn btn-link texto-perigo" data-acao="descartar"><i class="fa fa-trash-o" aria-hidden="true"></i> Descartar entrada</button>
							<button type="button" class="btn btn-primary" data-ir-etapa="2" id="botaoParaVisitados">Próximo: visitados <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
						</footer>
					</section>

					<!-- Etapa 2: visitados e descrição -->
					<section class="panel etapa-painel" data-painel="2" hidden>
						<header class="panel-heading">
							<h2 class="panel-title">Quem vai receber a visita?</h2>
						</header>
						<div class="panel-body">
							<div class="visitados-escolhidos">
								<span class="text-muted">Selecionados:</span>
								<span id="chipsVisitados"></span>
							</div>

							<div class="row filtros-visitados">
								<div class="form-group col-sm-4">
									<label for="tipoVisitado">Tipo</label>
									<select id="tipoVisitado" class="form-control">
										<option value="">Todos</option>
										<option value="atendido">Atendido</option>
										<option value="funcionario">Funcionário</option>
										<option value="voluntario">Voluntário</option>
										<option value="pet">Pet</option>
										<option value="setor">Setor</option>
									</select>
								</div>
								<div class="form-group col-sm-8">
									<label for="pesquisaVisitado">Pesquisar</label>
									<input type="search" id="pesquisaVisitado" class="form-control" placeholder="Nome ou identificador">
								</div>
							</div>

							<div id="alertaVisitados"></div>
							<ul class="lista-visitados" id="listaVisitados" aria-label="Visitados disponíveis"></ul>
							<p class="text-muted cadastro-visitado-dica">
								Não encontrou? <a href="cadastro_visitado.php?origem=entrada" id="linkCadastrarVisitado">Cadastrar visitado</a>.
								A entrada fica salva e você volta para esta etapa.
							</p>

							<div class="form-group">
								<label for="descricaoVisita">Descrição da visita <small class="text-muted">(opcional)</small></label>
								<textarea id="descricaoVisita" class="form-control" rows="2" maxlength="<?= RascunhoEntrada::TAMANHO_MAXIMO_DESCRICAO ?>" placeholder="Ex.: visita da família, entrega de doações"></textarea>
								<small class="text-muted"><span id="contadorDescricao">0</span>/<?= RascunhoEntrada::TAMANHO_MAXIMO_DESCRICAO ?></small>
							</div>
						</div>
						<footer class="panel-footer etapa-rodape">
							<button type="button" class="btn btn-default" data-ir-etapa="1"><i class="fa fa-arrow-left" aria-hidden="true"></i> Voltar</button>
							<button type="button" class="btn btn-primary" data-ir-etapa="3">Próximo: confirmar <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
						</footer>
					</section>

					<!-- Etapa 3: confirmação -->
					<section class="panel etapa-painel" data-painel="3" hidden>
						<header class="panel-heading">
							<h2 class="panel-title">Confira antes de registrar</h2>
						</header>
						<div class="panel-body">
							<dl class="resumo-entrada">
								<div>
									<dt>Visitantes <button type="button" class="btn btn-link btn-xs" data-ir-etapa="1">alterar</button></dt>
									<dd id="resumoVisitantes"></dd>
								</div>
								<div>
									<dt>Visitados <button type="button" class="btn btn-link btn-xs" data-ir-etapa="2">alterar</button></dt>
									<dd id="resumoVisitados"></dd>
								</div>
								<div>
									<dt>Descrição <button type="button" class="btn btn-link btn-xs" data-ir-etapa="2">alterar</button></dt>
									<dd id="resumoDescricao"></dd>
								</div>
							</dl>
							<p class="text-muted" id="resumoRegistros"></p>
						</div>
						<footer class="panel-footer etapa-rodape">
							<button type="button" class="btn btn-default" data-ir-etapa="2"><i class="fa fa-arrow-left" aria-hidden="true"></i> Voltar</button>
							<button type="button" class="btn btn-success btn-lg" id="botaoRegistrar"><i class="fa fa-sign-in" aria-hidden="true"></i> Registrar entrada</button>
						</footer>
					</section>

					<!-- Concluído -->
					<section class="panel etapa-painel entrada-concluida" data-painel="concluida" hidden>
						<div class="panel-body text-center">
							<i class="fa fa-check-circle entrada-concluida-icone" aria-hidden="true"></i>
							<h3>Entrada registrada</h3>
							<p id="textoConcluida"></p>
							<div class="entrada-concluida-acoes">
								<button type="button" class="btn btn-primary" data-acao="nova"><i class="fa fa-plus" aria-hidden="true"></i> Registrar outra entrada</button>
								<a href="registro_saida.php" class="btn btn-default"><i class="fa fa-sign-out" aria-hidden="true"></i> Ir para o registro de saída</a>
							</div>
						</div>
					</section>
				</div>

				<!-- Confirmação de descarte -->
				<div class="modal fade" id="modalDescartar" tabindex="-1" role="dialog" aria-labelledby="tituloDescartar" aria-hidden="true">
					<div class="modal-dialog modal-sm" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title" id="tituloDescartar">Descartar a entrada?</h4>
							</div>
							<div class="modal-body">
								Os visitantes e visitados escolhidos serão removidos desta entrada. Os cadastros de visitantes feitos nela continuam no sistema.
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Continuar a entrada</button>
								<button type="button" class="btn btn-danger" id="confirmarDescarte">Descartar</button>
							</div>
						</div>
					</div>
				</div>
				<!-- end: page -->
			</section>
		</div>
	</section>

	<script>
		window.REGISTRO_ENTRADA = {
			controle: "../../controle/control.php",
			csrf: <?= json_encode(Csrf::generateToken()) ?>,
			fotoPadrao: <?= json_encode(WWW . 'img/semfoto.png') ?>
		};
	</script>
	<script src="../../assets/javascripts/theme.js"></script>
	<script src="../../assets/javascripts/theme.custom.js"></script>
	<script src="../../assets/javascripts/theme.init.js"></script>
	<script src="js/registro_entrada.js"></script>

	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/pessoa.html" width="200" height="60" style="border:none;"></iframe>
	</div>
</body>

</html>
