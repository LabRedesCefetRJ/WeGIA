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
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'geral' . DIRECTORY_SEPARATOR . 'msg.php';

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
	$nomeProcessoGet = isset($_GET['nome-processo']) ? trim(filter_input(INPUT_GET, 'nome-processo', FILTER_SANITIZE_SPECIAL_CHARS)) : '';
	$processosAceitacao = $processoDAO->getByStatus($idStatusGet, $nomeProcessoGet);

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

	$error = $_SESSION['mensagem_erro'] ?? '';
	unset($_SESSION['mensagem_erro']);
	$oldInput = getSessionFormData(false);
	$fieldErrors = getSessionFormErrors(false);
	$openModal = getSessionOpenModal(false);

	function formatCpfForDisplay(?string $cpf): string {
		$digits = preg_replace('/\D+/', '', $cpf ?? '');
		if (strlen($digits) !== 11) {
			return htmlspecialchars($cpf ?? '', ENT_QUOTES, 'UTF-8');
		}
		return htmlspecialchars(preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits), ENT_QUOTES, 'UTF-8');
	}
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
				<?php sessionMsg(); ?>

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
					<div class="form-group" style="margin-bottom: 0;">
						<input type="text" id="nome-processo" class="form-control" placeholder="Pesquisar por nome" value="<?= htmlspecialchars($nomeProcessoGet ?? '', ENT_QUOTES, 'UTF-8') ?>" />
					</div>

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
												<td <?php if(!$showCpfColumn) echo 'style="display:none"' ?>><?= isset($processo['cpf']) && !empty($processo['cpf']) ? formatCpfForDisplay($processo['cpf']) : 'Não informado.' ?></td>
												<td style="max-width: 150px;"><?= isset($processo['descricao']) && !empty($processo['descricao']) ? nl2br(htmlspecialchars(html_entity_decode($processo['descricao'], ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8')) : '' ?></td>
												<td>
								<?php if (!empty($processo['etapas_count']) && (int)$processo['etapas_count'] > 0): ?>
									<a href="etapa_processo.php?id=<?= (int)$processo['id'] ?>" class="btn btn-xs btn-primary">
										<i class="fa fa-edit"></i>
									</a>
								<?php else: ?>
									<span class="text-muted" style="font-size: 12px;">Sem etapas</span>
								<?php endif; ?>

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

												<td style="display: flex; flex-wrap: wrap; gap: 10px;">
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
															onclick="return confirm('Tem certeza de que deseja cadastrar um atendido para o processo de <?= htmlspecialchars($processo['nome'] . ' ' . $processo['sobrenome'], ENT_QUOTES) ?>?');">
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
													<button type="button" class="btn btn-xs btn-warning btn-editar-perfil" data-id_processo="<?= htmlspecialchars($processo['id']) ?>" title="Editar Perfil da Pessoa">
														<i class="fa fa-edit"></i> Editar Perfil
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

				<div class="modal fade" id="modalEditarPerfil" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog" role="document">
                        <form id="formEditarPerfil" method="post" action="../../controle/control.php" class="modal-content" onsubmit="return validarFormularioEditarPerfil(event)">
                            <div class="modal-header">
                                <h5 class="modal-title">Editar Perfil</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="nomeClasse" value="ProcessoAceitacaoControle">
                                <input type="hidden" name="metodo" value="editarPerfil">
                                <input type="hidden" name="id_processo" id="edit_id_processo">

                                <div id="alertEditarPerfil" class="alert alert-danger" role="alert" style="display: none;"></div>

                                <div class="form-group">
                                    <label>Nome <span class="text-danger">*</span></label>
                                    <input type="text" name="nome" id="edit_nome" class="form-control" required autocomplete="given-name" />
                                    <?php if (!empty($fieldErrors['nome']) && $openModal === 'modalEditarPerfil'): ?>
                                        <p class="help-block text-danger"><?= htmlspecialchars($fieldErrors['nome'], ENT_QUOTES, 'UTF-8') ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label>Sobrenome <span class="text-danger">*</span></label>
                                    <input type="text" name="sobrenome" id="edit_sobrenome" class="form-control" required autocomplete="family-name" />
                                    <?php if (!empty($fieldErrors['sobrenome']) && $openModal === 'modalEditarPerfil'): ?>
                                        <p class="help-block text-danger"><?= htmlspecialchars($fieldErrors['sobrenome'], ENT_QUOTES, 'UTF-8') ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label>Sexo</label>
                                    <div>
                                        <label style="margin-right: 20px; margin-left: 10px;"><input type="radio" name="sexo" id="edit_sexo_m" value="m"> <i class="fa fa-male"></i> Masculino</label>
                                        <label><input type="radio" name="sexo" id="edit_sexo_f" value="f"> <i class="fa fa-female"></i> Feminino</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Data de Nascimento</label>
                                    <input type="date" name="data_nascimento" id="edit_data_nascimento" class="form-control"
                                        max="<?= date('Y-m-d') ?>"
                                        min="<?= date('Y-m-d', strtotime('-170 years')) ?>"
                                        onchange="validarDataNascimentoEditar()" />
                                    <p id="editDataNascimentoInvalida" style="display: none; color: #b30000; font-size: 12px;"></p>
                                </div>
                                <div class="form-group">
                                    <label>CPF</label>
                                    <input type="text"
                                        name="cpf"
                                        id="edit_cpf"
                                        maxlength="14"
                                        placeholder="000.000.000-00"
                                        onkeypress="return Onlynumbers(event)"
                                        oninput="mascara('###.###.###-##',this,event)"
                                        onkeyup="mascara('###.###.###-##',this,event)"
                                        onblur="formatCpfField(this); validarCPFEditar(this.value)"
                                        class="form-control<?= !empty($fieldErrors['cpf']) && $openModal === 'modalEditarPerfil' ? ' is-invalid' : '' ?>" />
                                    <p id="editCpfInvalido" style="display: <?= !empty($fieldErrors['cpf']) && $openModal === 'modalEditarPerfil' ? 'block' : 'none' ?>; color: #b30000; font-size: 12px;"><?= !empty($fieldErrors['cpf']) && $openModal === 'modalEditarPerfil' ? htmlspecialchars($fieldErrors['cpf'], ENT_QUOTES, 'UTF-8') : 'CPF INVÁLIDO!' ?></p>
                                </div>

                                <div class="form-group">
                                    <label>Telefone</label>
                                    <input type="tel" name="telefone" id="edit_telefone" maxlength="15" placeholder="(22) 99999-9999" onkeypress="return Onlynumbers(event)" onkeyup="mascara('(##) #####-####',this,event)" class="form-control" />
                                </div>
								<hr> <div class="row">
									<div class="form-group col-md-4">
										<label>CEP</label>
										<input type="text" name="cep" id="edit_cep" class="form-control" maxlength="9" onkeypress="return Onlynumbers(event)" onkeyup="mascara('#####-###',this,event)" onblur="pesquisacep_edit(this.value);" onkeydown="return cepEnterEdit(event, this.value);" placeholder="00000-000" />
                                                                                  <p id="editCepInvalido" class="text-danger" style="display: none; font-size: 12px;">Formato de CEP inválido!</p>
									</div>
									<div class="form-group col-md-8">
										<label>Rua</label>
										<input type="text" name="rua" id="edit_rua" class="form-control" />
									</div>
								</div>

								<div class="row">
									<div class="form-group col-md-3">
										<label>Nº</label>
										<input type="text" name="numero_residencia" id="edit_numero_residencia" class="form-control" />
									</div>
									<div class="form-group col-md-5">
										<label>Bairro</label>
										<input type="text" name="bairro" id="edit_bairro" class="form-control" />
									</div>
									<div class="form-group col-md-4">
										<label>Complemento</label>
										<input type="text" name="complemento" id="edit_complemento" class="form-control" />
									</div>
								</div>

								<div class="row">
									<div class="form-group col-md-5">
										<label>Cidade</label>
										<input type="text" name="cidade" id="edit_cidade" class="form-control" readonly />
									</div>
									<div class="form-group col-md-3">
										<label>UF</label>
										<input type="text" name="uf" id="edit_uf" class="form-control" readonly />
									</div>
									<div class="form-group col-md-4">
										<label>IBGE</label>
										<input type="text" name="ibge" id="edit_ibge" class="form-control" readonly />
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
								<button type="submit" class="btn btn-primary" id="btnSalvarEdicao">Salvar Alterações</button>
							</div>
						</form>
					</div>
				</div>

				<div class="modal fade" id="modalNovoProcesso" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog" role="document">
                        <form id="formNovoProcesso" method="post" action="../../controle/control.php" class="modal-content" onsubmit="return validarFormularioProcesso(event)">
                            <div class="modal-header">
                                <h5 class="modal-title">Novo Processo de Aceitação</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="nomeClasse" value="ProcessoAceitacaoControle">
                                <input type="hidden" name="metodo" value="incluir">

                                <div id="alertNovoProcesso" class="alert alert-danger" role="alert" style="display: none;"></div>

                                <div class="form-group">
                                    <label>Nome <span class="text-danger">*</span></label>
                                    <input type="text" id="nome" name="nome" class="form-control<?= !empty($fieldErrors['nome']) && $openModal === 'modalNovoProcesso' ? ' is-invalid' : '' ?>" required autocomplete="given-name" value="<?= htmlspecialchars($oldInput['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
                                    <?php if (!empty($fieldErrors['nome']) && $openModal === 'modalNovoProcesso'): ?>
                                        <p class="help-block text-danger"><?= htmlspecialchars($fieldErrors['nome'], ENT_QUOTES, 'UTF-8') ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label>Sobrenome <span class="text-danger">*</span></label>
                                    <input type="text" id="sobrenome" name="sobrenome" class="form-control<?= !empty($fieldErrors['sobrenome']) && $openModal === 'modalNovoProcesso' ? ' is-invalid' : '' ?>" required autocomplete="family-name" value="<?= htmlspecialchars($oldInput['sobrenome'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
                                    <?php if (!empty($fieldErrors['sobrenome']) && $openModal === 'modalNovoProcesso'): ?>
                                        <p class="help-block text-danger"><?= htmlspecialchars($fieldErrors['sobrenome'], ENT_QUOTES, 'UTF-8') ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label>Sexo</label>
                                    <div>
                                        <label style="margin-right: 20px; margin-left: 10px;"><input type="radio" name="sexo" value="m" <?= ($oldInput['sexo'] ?? '') === 'm' ? 'checked' : '' ?>> <i class="fa fa-male"></i> Masculino</label>
                                        <label><input type="radio" name="sexo" value="f" <?= ($oldInput['sexo'] ?? '') === 'f' ? 'checked' : '' ?>> <i class="fa fa-female"></i> Feminino</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Data de Nascimento</label>
                                    <input type="date" name="data_nascimento" id="data_nascimento_processo" class="form-control"
                                        max="<?= date('Y-m-d') ?>"
                                        min="<?= date('Y-m-d', strtotime('-170 years')) ?>"
                                        value="<?= htmlspecialchars($oldInput['data_nascimento'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        onchange="validarDataNascimentoProcesso()" />
                                    <p id="dataNascimentoInvalida" style="display: <?= !empty($fieldErrors['data_nascimento']) && $openModal === 'modalNovoProcesso' ? 'block' : 'none' ?>; color: #b30000; font-size: 12px;"><?= !empty($fieldErrors['data_nascimento']) && $openModal === 'modalNovoProcesso' ? htmlspecialchars($fieldErrors['data_nascimento'], ENT_QUOTES, 'UTF-8') : '' ?></p>
                                </div>
                                <div class="form-group">
                                    <label>CPF</label>
                                    <input type="text"
                                        name="cpf"
                                        id="cpf"
                                        maxlength="14"
                                        placeholder="000.000.000-00"
                                        onkeypress="return Onlynumbers(event)"
                                        oninput="mascara('###.###.###-##',this,event)"
                                        onkeyup="mascara('###.###.###-##',this,event); if (this.value.replace(/\D/g,'').length === 11) buscarPessoaPorCpf(this.value);"
                                        onblur="formatCpfField(this); validarCPF(this.value); buscarPessoaPorCpf(this.value)"
                                        class="form-control<?= !empty($fieldErrors['cpf']) && $openModal === 'modalNovoProcesso' ? ' is-invalid' : '' ?>"
                                        value="<?= htmlspecialchars(preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $oldInput['cpf'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
                                    <p id="cpfInvalido" style="display: <?= !empty($fieldErrors['cpf']) && $openModal === 'modalNovoProcesso' ? 'block' : 'none' ?>; color: #b30000; font-size: 12px;"><?= !empty($fieldErrors['cpf']) && $openModal === 'modalNovoProcesso' ? htmlspecialchars($fieldErrors['cpf'], ENT_QUOTES, 'UTF-8') : 'CPF INVÁLIDO!' ?></p>
                                </div>

                                <div class="form-group">
                                    <label>Telefone</label>
                                    <input type="tel" name="telefone" id="telefone" maxlength="15" placeholder="(22) 99999-9999" onkeypress="return Onlynumbers(event)" onkeyup="mascara('(##) #####-####',this,event)" class="form-control<?= !empty($fieldErrors['telefone']) && $openModal === 'modalNovoProcesso' ? ' is-invalid' : '' ?>" value="<?= htmlspecialchars($oldInput['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
                                    <?php if (!empty($fieldErrors['telefone']) && $openModal === 'modalNovoProcesso'): ?>
                                        <p class="help-block text-danger"><?= htmlspecialchars($fieldErrors['telefone'], ENT_QUOTES, 'UTF-8') ?></p>
                                    <?php endif; ?>
                                </div>
								<hr> <div class="row">
									<div class="form-group col-md-4">
										<label>CEP</label>
										<input type="text" name="cep" id="cep" class="form-control<?= !empty($fieldErrors['cep']) && $openModal === 'modalNovoProcesso' ? ' is-invalid' : '' ?>" maxlength="9" onkeypress="return Onlynumbers(event)" onkeyup="mascara('#####-###',this,event)" onblur="pesquisacep(this.value);" onkeydown="return cepEnter(event, this.value);" placeholder="00000-000" value="<?= htmlspecialchars($oldInput['cep'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
                                                                                  <p id="cepInvalido" class="text-danger" style="display: <?= !empty($fieldErrors['cep']) && $openModal === 'modalNovoProcesso' ? 'block' : 'none' ?>; font-size: 12px;"><?= !empty($fieldErrors['cep']) && $openModal === 'modalNovoProcesso' ? htmlspecialchars($fieldErrors['cep'], ENT_QUOTES, 'UTF-8') : 'Formato de CEP inválido!' ?></p>
									</div>
									<div class="form-group col-md-8">
										<label>Rua</label>
										<input type="text" name="rua" id="rua" class="form-control" value="<?= htmlspecialchars($oldInput['rua'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
									</div>
								</div>

								<div class="row">
									<div class="form-group col-md-3">
										<label>Nº</label>
										<input type="text" name="numero_residencia" id="numero_residencia" class="form-control" value="<?= htmlspecialchars($oldInput['numero_residencia'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
									</div>
									<div class="form-group col-md-5">
										<label>Bairro</label>
										<input type="text" name="bairro" id="bairro" class="form-control" value="<?= htmlspecialchars($oldInput['bairro'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
									</div>
									<div class="form-group col-md-4">
										<label>Complemento</label>
										<input type="text" name="complemento" id="complemento" class="form-control" value="<?= htmlspecialchars($oldInput['complemento'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
									</div>
								</div>

								<div class="row">
									<div class="form-group col-md-5">
										<label>Cidade</label>
										<input type="text" name="cidade" id="cidade" class="form-control" readonly value="<?= htmlspecialchars($oldInput['cidade'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
									</div>
									<div class="form-group col-md-3">
										<label>UF</label>
										<input type="text" name="uf" id="uf" class="form-control" readonly value="<?= htmlspecialchars($oldInput['uf'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
									</div>
									<div class="form-group col-md-4">
										<label>IBGE</label>
										<input type="text" name="ibge" id="ibge" class="form-control" readonly value="<?= htmlspecialchars($oldInput['ibge'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
									</div>
								</div>

								<div class="form-group">
									<label>Descrição</label>
									<textarea class="form-control" rows="5" name="descricao"><?= htmlspecialchars($oldInput['descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
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
					<div class="modal-dialog modal-lg modal-arquivos-processo" role="document">
						<div class="modal-content">
							<div class="modal-header processo-arquivos-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
									<span aria-hidden="true">&times;</span>
								</button>
								<div class="processo-arquivos-title-wrap">
									<div>
										<h5 class="modal-title">Arquivos do Processo</h5>
										<p id="tituloProcesso" class="processo-arquivos-subtitle"></p>
									</div>
								</div>
							</div>
							<div class="modal-body processo-arquivos-body">
								<section class="processo-arquivos-section">
									<div class="processo-arquivos-section-header">
										<h6>Arquivos anexados</h6>
									</div>
									<div id="lista-arquivos-processo" class="processo-arquivos-lista"></div>
								</section>

								<section class="processo-arquivos-section processo-arquivos-upload">
									<div class="processo-arquivos-section-header">
										<h6>Novo anexo</h6>
									</div>
									<form id="formUploadDocProcesso" method="post" action="../../controle/control.php" enctype="multipart/form-data">
										<input type="hidden" name="nomeClasse" value="PaArquivoControle">
										<input type="hidden" name="metodo" value="upload">
										<input type="hidden" name="id_processo" id="upload_id_processo">

										<div class="processo-arquivos-grid">
											<div class="form-group processo-arquivos-campo">
												<label for="tipoDocumentoProcesso">Tipo de Documento <span class="text-danger">*</span></label>
												<div class="processo-arquivos-select-row">
													<select name="id_tipo_documentacao" class="form-control" id="tipoDocumentoProcesso" required>
														<option selected disabled value="">Selecionar...</option>
														<?php
														foreach ($pdo->query("SELECT * FROM atendido_docs_atendidos ORDER BY descricao ASC")->fetchAll(PDO::FETCH_ASSOC) as $item) {
															echo "<option value='" . $item["idatendido_docs_atendidos"] . "'>" . htmlspecialchars($item["descricao"]) . "</option>";
														}
														?>
													</select>
													<a href="javascript:void(0)" class="btn btn-default processo-arquivos-add-tipo" onclick="adicionarTipoProcesso()" title="Adicionar tipo de documento" aria-label="Adicionar tipo de documento">
														<i class="fas fa-plus" aria-hidden="true"></i>
													</a>
												</div>
											</div>

											<div class="form-group processo-arquivos-campo">
												<label for="arquivoProcesso">Arquivo <span class="text-danger">*</span></label>
												<p class="help-block processo-arquivos-ajuda">Permitido envio de até <?= ini_get('upload_max_filesize') ?> de tamanho por documento.</p>
												<input type="file" name="arquivo" class="form-control processo-arquivos-file" id="arquivoProcesso"
													accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.odp" required>
											</div>
										</div>

										<div class="processo-arquivos-actions">
											<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
											<button type="submit" class="btn btn-primary" onclick="return verificaTipoProcesso(event)">
												<i class="fa fa-upload" aria-hidden="true"></i> Anexar arquivo
											</button>
										</div>
									</form>
								</section>
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
	<?php if (!empty($openModal)): ?>
		<script>
			$(function() {
				const modalId = <?= json_encode($openModal) ?>;
				const oldInput = <?= json_encode($oldInput, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

				if (modalId === 'modalEditarPerfil') {
					$('#edit_id_processo').val(oldInput.id_processo || '');
					$('#edit_nome').val(oldInput.nome || '');
					$('#edit_sobrenome').val(oldInput.sobrenome || '');
					$('#edit_data_nascimento').val(oldInput.data_nascimento || '');
					$('#edit_cpf').val(oldInput.cpf || '');
					$('#edit_telefone').val(oldInput.telefone || '');
					$('#edit_cep').val(oldInput.cep || '');
					$('#edit_rua').val(oldInput.rua || '');
					$('#edit_numero_residencia').val(oldInput.numero_residencia || '');
					$('#edit_bairro').val(oldInput.bairro || '');
					$('#edit_complemento').val(oldInput.complemento || '');
					$('#edit_cidade').val(oldInput.cidade || '');
					$('#edit_uf').val(oldInput.uf || '');
					$('#edit_ibge').val(oldInput.ibge || '');
					if (oldInput.sexo === 'm' || oldInput.sexo === 'f') {
						$('input[name="sexo"][value="' + oldInput.sexo + '"]', '#modalEditarPerfil').prop('checked', true);
					}
				}

				$('#' + modalId).modal('show');
			});
		</script>
	<?php clearSessionFormState(); endif; ?>

	<style type="text/css">
		.obrig {
			color: #ff0000;
		}

		#modalArquivosProcesso .modal-arquivos-processo {
			width: min(920px, calc(100vw - 32px));
			margin: 30px auto;
		}

		#modalArquivosProcesso .modal-content {
			border-radius: 6px;
			overflow: hidden;
		}

		#modalArquivosProcesso .processo-arquivos-header {
			padding: 20px 24px;
		}

		#modalArquivosProcesso .processo-arquivos-header .close {
			margin-top: 4px;
			font-size: 28px;
		}

		#modalArquivosProcesso .processo-arquivos-title-wrap {
			display: flex;
			align-items: center;
			padding-right: 36px;
		}

		#modalArquivosProcesso .modal-title {
			margin: 0;
			font-size: 18px;
			font-weight: 600;
			line-height: 1.3;
		}

		#modalArquivosProcesso .processo-arquivos-subtitle {
			margin: 3px 0 0;
			font-size: 13px;
			line-height: 1.35;
			word-break: break-word;
		}

		#modalArquivosProcesso .processo-arquivos-body {
			padding: 24px;
		}

		#modalArquivosProcesso .processo-arquivos-section + .processo-arquivos-section {
			margin-top: 24px;
			padding-top: 24px;
			border-top: 1px solid #ddd;
		}

		#modalArquivosProcesso .processo-arquivos-section-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 14px;
		}

		#modalArquivosProcesso .processo-arquivos-section-header h6 {
			margin: 0;
			font-size: 14px;
			font-weight: 700;
		}

		#modalArquivosProcesso .processo-arquivos-lista {
			max-height: 300px;
			overflow: auto;
			border: 1px solid #ddd;
			border-radius: 6px;
		}

		#modalArquivosProcesso .processo-arquivos-lista .alert {
			margin: 0;
			border: 0;
			border-radius: 0;
			padding: 16px;
		}

		#modalArquivosProcesso .processo-arquivos-lista table {
			margin-bottom: 0;
			min-width: 640px;
		}

		#modalArquivosProcesso .processo-arquivos-lista th {
			font-weight: 600;
			white-space: nowrap;
		}

		#modalArquivosProcesso .processo-arquivos-lista td {
			vertical-align: middle;
		}

		#modalArquivosProcesso .processo-arquivos-upload {
			margin-left: -24px;
			margin-right: -24px;
			margin-bottom: -24px;
			padding-left: 24px;
			padding-right: 24px;
			padding-bottom: 24px;
		}

		#modalArquivosProcesso .processo-arquivos-grid {
			display: grid;
			grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
			gap: 20px;
			align-items: start;
		}

		#modalArquivosProcesso .processo-arquivos-campo {
			margin-bottom: 0;
		}

		#modalArquivosProcesso .processo-arquivos-campo label {
			display: block;
			margin-bottom: 8px;
			font-weight: 600;
		}

		#modalArquivosProcesso .processo-arquivos-select-row {
			display: flex;
			gap: 10px;
			align-items: stretch;
		}

		#modalArquivosProcesso .processo-arquivos-select-row select {
			min-width: 0;
			flex: 1;
		}

		#modalArquivosProcesso .processo-arquivos-add-tipo {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 42px;
			flex: 0 0 42px;
			padding: 0;
		}

		#modalArquivosProcesso .processo-arquivos-ajuda {
			margin-top: -2px;
			margin-bottom: 8px;
			font-size: 12px;
			line-height: 1.35;
		}

		#modalArquivosProcesso .processo-arquivos-file {
			height: auto;
			padding: 8px 10px;
		}

		#modalArquivosProcesso .processo-arquivos-actions {
			display: flex;
			justify-content: flex-end;
			gap: 10px;
			margin-top: 22px;
		}

		@media (max-width: 767px) {
			#modalArquivosProcesso .modal-arquivos-processo {
				width: calc(100vw - 20px);
				margin: 10px auto;
			}

			#modalArquivosProcesso .processo-arquivos-header,
			#modalArquivosProcesso .processo-arquivos-body {
				padding: 18px;
			}

			#modalArquivosProcesso .processo-arquivos-upload {
				margin-left: -18px;
				margin-right: -18px;
				margin-bottom: -18px;
				padding-left: 18px;
				padding-right: 18px;
				padding-bottom: 18px;
			}

			#modalArquivosProcesso .processo-arquivos-grid {
				grid-template-columns: 1fr;
				gap: 16px;
			}

			#modalArquivosProcesso .processo-arquivos-lista {
				max-height: 260px;
			}

			#modalArquivosProcesso .processo-arquivos-actions {
				flex-direction: column-reverse;
			}

			#modalArquivosProcesso .processo-arquivos-actions .btn {
				width: 100%;
			}
		}
	</style>

	<script>
function formatCpfField(field) {
            if (!field) {
                return;
            }

            const digits = field.value.replace(/\D/g, '');
            if (digits.length === 11) {
                field.value = digits.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            }
        }

        function validarCPF(strCPF) {
			if (strCPF.length != 0 && !testaCPF(strCPF)) {
				$('#cpfInvalido').show();
				$('#enviar').prop('disabled', true);
			} else {
				$('#cpfInvalido').hide();
				$('#enviar').prop('disabled', false);
			}
		}

        function buscarPessoaPorCpf(cpf) {
            const digits = cpf.replace(/\D/g, '');
            if (digits.length !== 11) {
                return;
            }

            $.ajax({
                url: '../../controle/control.php',
                type: 'GET',
                dataType: 'json',
                data: {
                    nomeClasse: 'ProcessoAceitacaoControle',
                    metodo: 'getPessoaPorCpf',
                    cpf: digits
                },
                success: function(response) {
                    if (!response.success || !response.pessoa) {
                        return;
                    }

                    $('#nome').val(response.pessoa.nome || '');
                    $('#sobrenome').val(response.pessoa.sobrenome || '');
                    $('#telefone').val(response.pessoa.telefone || '');
                    $('#cep').val(response.pessoa.cep || '');
                    $('#rua').val(response.pessoa.logradouro || '');
                    $('#numero_residencia').val(response.pessoa.numero_endereco || '');
                    $('#bairro').val(response.pessoa.bairro || '');
                    $('#cidade').val(response.pessoa.cidade || '');
                    $('#uf').val(response.pessoa.estado || '');
                    $('#ibge').val(response.pessoa.ibge || '');
                    $('#data_nascimento_processo').val(response.pessoa.data_nascimento || '');

                    if (response.pessoa.sexo === 'm' || response.pessoa.sexo === 'f') {
                        $('input[name="sexo"][value="' + response.pessoa.sexo + '"]').prop('checked', true);
                    }
                },
                error: function() {
                    // ignore errors silently
                }
            });
        }

        function mostrarErroModal(message) {
            const alertBox = $('#alertNovoProcesso');
            alertBox.text(message).show();
        }

        function limparErroModal() {
            const alertBox = $('#alertNovoProcesso');
            alertBox.hide().text('');
        }

        function validarFormularioProcesso(event) {
            event = event || window.event;
            const form = event.target || event.srcElement;

            limparErroModal();
            limparCepErro();

            const nome = form.nome.value.trim();
            const sobrenome = form.sobrenome.value.trim();
            const telefone = form.telefone.value.trim();
            const cep = form.cep.value.trim();
            const rua = form.rua.value.trim();
            const bairro = form.bairro.value.trim();
            const cidade = form.cidade.value.trim();
            const uf = form.uf.value.trim();

            if (!nome || !sobrenome) {
                mostrarErroModal('Informe nome e sobrenome antes de cadastrar o processo.');
                return false;
            }

            if (telefone) {
                const telefoneNumeros = telefone.replace(/\D/g, '');
                if (!/^\d{10,11}$/.test(telefoneNumeros)) {
                    mostrarErroModal('Telefone inválido. Digite o DDD e número com 10 ou 11 dígitos.');
                    return false;
                }
            }

            if (cep) {
                const cepNumeros = cep.replace(/\D/g, '');
                if (!/^\d{8}$/.test(cepNumeros)) {
                    mostrarErroModal('CEP inválido. Use o formato 00000-000.');
                    return false;
                }
            }

            const sexo = form.querySelector('input[name="sexo"]:checked');
            const dataNascimento = form.data_nascimento.value.trim();
            if (dataNascimento && !validarDataNascimentoProcesso()) {
                return false;
            }

            const enderecoPreenchido = cep || rua || bairro || cidade || uf;
            if (enderecoPreenchido && (!rua || !bairro || !cidade || !uf)) {
                mostrarErroModal('Preencha o endereço completo ou deixe todos os campos de endereço em branco.');
                return false;
            }

            return true;
        }

        function validarDataNascimentoProcesso() {
            const dataNascimentoElm = document.getElementById('data_nascimento_processo');
            const mensagemElm = document.getElementById('dataNascimentoInvalida');
            const dataValue = dataNascimentoElm.value.trim();
            mensagemElm.style.display = 'none';
            mensagemElm.textContent = '';

            if (!dataValue) {
                return true;
            }

            const data = new Date(dataValue);
            if (Number.isNaN(data.getTime())) {
                mensagemElm.textContent = 'Data de nascimento em formato inválido.';
                mensagemElm.style.display = 'block';
                mostrarErroModal('Data de nascimento em formato inválido.');
                return false;
            }

            const hoje = new Date();
            hoje.setHours(0, 0, 0, 0);
            if (data > hoje) {
                mensagemElm.textContent = 'A data de nascimento não pode ser no futuro.';
                mensagemElm.style.display = 'block';
                mostrarErroModal('A data de nascimento não pode ser no futuro.');
                return false;
            }

            const dataMinima = new Date();
            dataMinima.setFullYear(dataMinima.getFullYear() - 170);
            if (data < dataMinima) {
                mensagemElm.textContent = 'A data de nascimento deve estar em um intervalo válido (máximo 170 anos).';
                mensagemElm.style.display = 'block';
                mostrarErroModal('A data de nascimento deve estar em um intervalo válido (máximo 170 anos).');
                return false;
            }

            mensagemElm.style.display = 'none';
            return true;
        }

        function verificaTipoProcesso(ev) {
            const idProcesso = document.getElementById('upload_id_processo');
            const tipo = document.getElementById('tipoDocumentoProcesso');

            if (!idProcesso.value || isNaN(idProcesso.value) || idProcesso.value < 1) {
                alert('Erro: processo não informado para anexar o arquivo.');
                ev.preventDefault();
                return false;
            }

            if (!tipo.value || isNaN(tipo.value) || tipo.value < 1) {
                alert('Erro: selecione um tipo de documento adequado antes de prosseguir.');
                ev.preventDefault();
                return false;
            }

            return true;
        }

        $(document).on('click', '.btn-arquivos-processo', function() {
            const idProcesso = $(this).attr('data-id_processo') || $(this).data('id_processo');
            const nome = $(this).attr('data-nome') || '';

            $('#upload_id_processo').val(idProcesso || '');
            $('#tituloProcesso').text(nome ? '- ' + nome : '');
            $('#tipoDocumentoProcesso').val('');
            $('#arquivoProcesso').val('');

            if (idProcesso) {
                $('#lista-arquivos-processo').load(
                    'lista_arquivos_processo.php?id_processo=' + encodeURIComponent(idProcesso)
                );
            } else {
                $('#lista-arquivos-processo').html('<div class="alert alert-danger">Processo inválido.</div>');
            }
        });

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
			const nomeProcesso = document.getElementById('nome-processo').value.trim();

			let url = './processo_aceitacao.php?status-processo=' + encodeURIComponent(valorStatus);
			if (nomeProcesso !== '') {
				url += '&nome-processo=' + encodeURIComponent(nomeProcesso);
			}

			window.location.href = url;
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

	<script>

		function limpa_formulário_cep() {
			document.getElementById('rua').value=("");
			document.getElementById('bairro').value=("");
			document.getElementById('cidade').value=("");
			document.getElementById('uf').value=("");
			document.getElementById('ibge').value=("");
			limparCepErro();
		}

		function mostrarCepErro(message) {
			const cepError = document.getElementById('cepInvalido');
			cepError.textContent = message;
			cepError.style.display = 'block';
		}

		function limparCepErro() {
			const cepError = document.getElementById('cepInvalido');
			if (cepError) {
				cepError.style.display = 'none';
				cepError.textContent = 'Formato de CEP inválido!';
			}
		}

		function cepEnter(event, value) {
			if (event.key === 'Enter') {
				event.preventDefault();
				pesquisacep(value);
				return false;
			}
			return true;
		}

		function meu_callback(conteudo) {
			if (!("erro" in conteudo)) {
				document.getElementById('rua').value=(conteudo.logradouro);
				document.getElementById('bairro').value=(conteudo.bairro);
				document.getElementById('cidade').value=(conteudo.localidade);
				document.getElementById('uf').value=(conteudo.uf);
				document.getElementById('ibge').value=(conteudo.ibge);
			} else {
				limpa_formulário_cep();
				mostrarCepErro('CEP não encontrado.');
			}
		}
			
		function pesquisacep(valor) {
			var cep = valor.replace(/\D/g, '');
			if (cep != "") {
				var validacep = /^[0-9]{8}$/;
				if(validacep.test(cep)) {
                    limparCepErro();

					var script = document.createElement('script');
					script.src = 'https://viacep.com.br/ws/'+ cep + '/json/?callback=meu_callback';
					document.body.appendChild(script);
				} else {
					limpa_formulário_cep();
					mostrarCepErro('Formato de CEP inválido.');
				}
			} else {
				limpa_formulário_cep();
			}
		};
		
	</script>

	<script src="../../Functions/pa_status.js"></script>

	<script>
		$(document).on('click', '.btn-editar-perfil', function() {
			const idProcesso = $(this).data('id_processo');
			
			$('#formEditarPerfil')[0].reset();
			limparErroModalEdit();
			limparCepErroEdit();
			$('#edit_id_processo').val(idProcesso);
			
			$.ajax({
				url: '../../controle/control.php',
				type: 'GET',
				dataType: 'json',
				data: {
					id_processo: idProcesso,
					nomeClasse: 'ProcessoAceitacaoControle',
					metodo: 'getPessoaDoProcesso'
				},
				success: function(response) {
					if (response.success) {
						const pessoa = response.pessoa;
						
						$('#edit_nome').val(pessoa.nome);
						$('#edit_sobrenome').val(pessoa.sobrenome);
						
						if (pessoa.sexo === 'm') {
							$('#edit_sexo_m').prop('checked', true);
						} else if (pessoa.sexo === 'f') {
							$('#edit_sexo_f').prop('checked', true);
						}
						
						$('#edit_data_nascimento').val(pessoa.data_nascimento);
						$('#edit_cpf').val(pessoa.cpf);
					formatCpfField(document.getElementById('edit_cpf'));
						$('#edit_telefone').val(pessoa.telefone);
						$('#edit_cep').val(pessoa.cep);
						$('#edit_rua').val(pessoa.logradouro);
						$('#edit_numero_residencia').val(pessoa.numero_endereco);
						$('#edit_bairro').val(pessoa.bairro);
						$('#edit_complemento').val(pessoa.complemento);
						$('#edit_cidade').val(pessoa.cidade);
						$('#edit_uf').val(pessoa.estado);
						$('#edit_ibge').val(pessoa.ibge);
						
						$('#modalEditarPerfil').modal('show');
					} else {
						alert('Erro: ' + (response.erro || 'Não foi possível carregar os dados da pessoa.'));
					}
				},
				error: function() {
					alert('Erro ao comunicar com o servidor.');
				}
			});
		});

		function validarCPFEditar(strCPF) {
			if (strCPF.length != 0 && !testaCPF(strCPF)) {
				$('#editCpfInvalido').show();
				$('#btnSalvarEdicao').prop('disabled', true);
			} else {
				$('#editCpfInvalido').hide();
				$('#btnSalvarEdicao').prop('disabled', false);
			}
		}

        function mostrarErroModalEdit(message) {
            const alertBox = $('#alertEditarPerfil');
            alertBox.text(message).show();
        }

        function limparErroModalEdit() {
            const alertBox = $('#alertEditarPerfil');
            alertBox.hide().text('');
        }

        function validarFormularioEditarPerfil(event) {
            event = event || window.event;
            const form = event.target || event.srcElement;

            limparErroModalEdit();
            limparCepErroEdit();

            const nome = form.nome.value.trim();
            const sobrenome = form.sobrenome.value.trim();
            const telefone = form.telefone.value.trim();
            const cep = form.cep.value.trim();
            const rua = form.rua.value.trim();
            const bairro = form.bairro.value.trim();
            const cidade = form.cidade.value.trim();
            const uf = form.uf.value.trim();

            if (!nome || !sobrenome) {
                mostrarErroModalEdit('Informe nome e sobrenome.');
                return false;
            }

            if (telefone) {
                const telefoneNumeros = telefone.replace(/\D/g, '');
                if (!/^\d{10,11}$/.test(telefoneNumeros)) {
                    mostrarErroModalEdit('Telefone inválido. Digite o DDD e número com 10 ou 11 dígitos.');
                    return false;
                }
            }

            if (cep) {
                const cepNumeros = cep.replace(/\D/g, '');
                if (!/^\d{8}$/.test(cepNumeros)) {
                    mostrarErroModalEdit('CEP inválido. Use o formato 00000-000.');
                    return false;
                }
            }

            const dataNascimento = form.data_nascimento.value.trim();
            if (dataNascimento && !validarDataNascimentoEditar()) {
                return false;
            }

            const enderecoPreenchido = cep || rua || bairro || cidade || uf;
            if (enderecoPreenchido && (!rua || !bairro || !cidade || !uf)) {
                mostrarErroModalEdit('Preencha o endereço completo ou deixe todos os campos de endereço em branco.');
                return false;
            }

            return true;
        }

        function validarDataNascimentoEditar() {
            const dataNascimentoElm = document.getElementById('edit_data_nascimento');
            const mensagemElm = document.getElementById('editDataNascimentoInvalida');
            const dataValue = dataNascimentoElm.value.trim();
            mensagemElm.style.display = 'none';
            mensagemElm.textContent = '';

            if (!dataValue) {
                return true;
            }

            const data = new Date(dataValue);
            if (Number.isNaN(data.getTime())) {
                mensagemElm.textContent = 'Data de nascimento em formato inválido.';
                mensagemElm.style.display = 'block';
                mostrarErroModalEdit('Data de nascimento em formato inválido.');
                return false;
            }

            const hoje = new Date();
            hoje.setHours(0, 0, 0, 0);
            if (data > hoje) {
                mensagemElm.textContent = 'A data de nascimento não pode ser no futuro.';
                mensagemElm.style.display = 'block';
                mostrarErroModalEdit('A data de nascimento não pode ser no futuro.');
                return false;
            }

            const dataMinima = new Date();
            dataMinima.setFullYear(dataMinima.getFullYear() - 170);
            if (data < dataMinima) {
                mensagemElm.textContent = 'A data de nascimento deve estar em um intervalo válido.';
                mensagemElm.style.display = 'block';
                mostrarErroModalEdit('A data de nascimento deve estar em um intervalo válido.');
                return false;
            }

            mensagemElm.style.display = 'none';
            return true;
        }

		function limpa_formulário_cep_edit() {
			document.getElementById('edit_rua').value=("");
			document.getElementById('edit_bairro').value=("");
			document.getElementById('edit_cidade').value=("");
			document.getElementById('edit_uf').value=("");
			document.getElementById('edit_ibge').value=("");
			limparCepErroEdit();
		}

		function mostrarCepErroEdit(message) {
			const cepError = document.getElementById('editCepInvalido');
			cepError.textContent = message;
			cepError.style.display = 'block';
		}

		function limparCepErroEdit() {
			const cepError = document.getElementById('editCepInvalido');
			if (cepError) {
				cepError.style.display = 'none';
				cepError.textContent = 'Formato de CEP inválido!';
			}
		}

		function cepEnterEdit(event, value) {
			if (event.key === 'Enter') {
				event.preventDefault();
				pesquisacep_edit(value);
				return false;
			}
			return true;
		}

		function meu_callback_edit(conteudo) {
			if (!("erro" in conteudo)) {
				document.getElementById('edit_rua').value=(conteudo.logradouro);
				document.getElementById('edit_bairro').value=(conteudo.bairro);
				document.getElementById('edit_cidade').value=(conteudo.localidade);
				document.getElementById('edit_uf').value=(conteudo.uf);
				document.getElementById('edit_ibge').value=(conteudo.ibge);
			} else {
				limpa_formulário_cep_edit();
				mostrarCepErroEdit('CEP não encontrado.');
			}
		}
			
		function pesquisacep_edit(valor) {
			var cep = valor.replace(/\D/g, '');
			if (cep != "") {
				var validacep = /^[0-9]{8}$/;
				if(validacep.test(cep)) {
                    limparCepErroEdit();

					var script = document.createElement('script');
					script.src = 'https://viacep.com.br/ws/'+ cep + '/json/?callback=meu_callback_edit';
					document.body.appendChild(script);
				} else {
					limpa_formulário_cep_edit();
					mostrarCepErroEdit('Formato de CEP inválido.');
				}
			} else {
				limpa_formulário_cep_edit();
			}
		};
	</script>
</body>

</html>
