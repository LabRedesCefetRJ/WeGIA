<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE)
	session_start();

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

if (!isset($_SESSION['usuario'], $_SESSION['id_pessoa'])) {
	header("Location: " . WWW . "html/index.php");
	exit();
} else {
	session_regenerate_id();
}

$id_pessoa = filter_var($_SESSION['id_pessoa'], FILTER_VALIDATE_INT);

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

permissao($id_pessoa, 22, 5);

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';

$tipo = $_GET['tipo'] ?? 'ativo';
$tipo = in_array($tipo, ['ativo', 'arquivado'], true) ? $tipo : 'ativo';

if (!isset($_SESSION['produtos'])) {
    if ($tipo === 'arquivado') {
        header('Location: ' . WWW . 'controle/control.php?metodo=listarArquivados&nomeClasse=ProdutoControle');
    } else {
        header('Location: ' . WWW . 'controle/control.php?metodo=listarTodos&nomeClasse=ProdutoControle&nextPage=' . WWW . 'html/matPat/listar_produto.php?tipo=ativo');
    }

    exit();
}

$nextPage = '../html/matPat/listar_produto.php?tipo=' . $tipo;

if (!isset($_SESSION['grupo_produto'])) {
    header('Location: ' . WWW . 'controle/control.php?metodo=listarTodos' . '&nomeClasse=GrupoProdutoControle' . '&nextPage=' . urlencode($nextPage));
    exit;
}

if (!isset($_SESSION['categoria'])) {
    header('Location: ' . WWW . 'controle/control.php?metodo=listarTodos' . '&nomeClasse=CategoriaControle' . '&nextPage=' . urlencode($nextPage));
    exit;
}

$produtos = json_decode($_SESSION['produtos'], true);
$grupoProdutos = json_decode($_SESSION['grupo_produto'], true);
$categorias = json_decode($_SESSION['categoria'], true);

unset(
    $_SESSION['produtos'],
    $_SESSION['grupo_produto'],
    $_SESSION['categoria']
);
?>
<!doctype html>
<html class="fixed">

<head>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Informações</title>

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
		function clicar(id) {
    		window.location.replace('<?= WWW ?>html/matPat/alterar_produto.php?id_produto=' + id);
		}
	</script>
	<script>
		$(function() {
			$("#header").load("<?= WWW ?>html/header.php");
			$(".menuu").load("<?= WWW ?>html/menu.php");
		});
	</script>

	<style>
		.dataTables_length,
		.dataTables_filter,
		.dataTables_info {
			display: none !important;
		}

		.dataTables_paginate {
			margin-top: 15px;
		}

		.barra-produtos {
			display: flex;
			justify-content: space-between;
			align-items: flex-end;
			gap: 15px;
			flex-wrap: wrap;
			margin-bottom: 18px;
			padding: 12px 14px;
			background: #f7f7f7;
			border: 1px solid #e5e5e5;
			border-radius: 6px;
		}

		.abas-produtos {
			display: flex;
			gap: 6px;
		}

		.filtros-produtos {
			display: flex;
			gap: 12px;
			align-items: flex-end;
			flex-wrap: wrap;
		}

		.campo-filtro label {
			display: block;
			font-size: 12px;
			color: #666;
			margin-bottom: 4px;
			font-weight: 600;
		}

		.campo-filtro select,
		.campo-filtro input {
			width: 220px;
		}

		#datatable-default thead th {
			background: #fafafa;
			font-size: 12px;
			color: #666;
		}

		#datatable-default td {
			vertical-align: middle;
		}

		.acoes-produto {
			display: flex;
			gap: 12px;
			align-items: center;
		}

		.acoes-produto form {
			margin: 0;
			display: inline-block;
		}

		.acoes-produto button {
			border: none;
			background: none;
			cursor: pointer;
			padding: 0;
			color: #666;
		}

		.acoes-produto button:hover {
			color: #000;
		}
	</style>

</head>

<body>
	<div id="header"></div>
	<!-- end: header -->
	<div class="inner-wrapper">
		<!-- start: sidebar -->
		<aside id="sidebar-left" class="sidebar-left menuu"></aside>

		<!-- end: sidebar -->
		<section role="main" class="content-body">
			<header class="page-header">
				<h2>Informações</h2>

				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li>
							<a href="<?= WWW ?>html/home.php">
								<i class="fa fa-home"></i>
							</a>
						</li>
						<li><span>Informações Produto</span></li>
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

					<h2 class="panel-title">Produto</h2>
				</header>
				<div class="panel-body">
					<div class="barra-produtos">

						<div id="acoesSelecao" style="display: none; margin-bottom: 15px;">
    						<button
        						type="button"
        						id="btnSelecionarTodos"
        						class="btn btn-default"
    						>
        						<i class="fa fa-check-double"></i>
        						Selecionar todos os resultados
    						</button>

							<button
    							type="button"
    							id="btnDesmarcarTodos"
    							class="btn btn-default"
							>
    							<i class="fa fa-square-o"></i>
    							Desmarcar resultados
							</button>

    						<span
        						id="quantidadeSelecionados"
        						style="margin-left: 10px; font-weight: 600;"
    						>
        						0 produtos selecionados
    						</span>

							<form
    							id="formAtribuirGrupo"
    							method="POST"
    							action="<?= WWW ?>controle/control.php"
    							style="display: inline-flex; align-items: center; gap: 8px;"
							>

								<input
    								type="hidden"
    								name="produtos_json"
    								id="produtosJson"
								>
								<input
    								type="hidden"
    								name="nomeClasse"
    								value="ProdutoControle"
								>
								<input
    								type="hidden"
    								name="metodo"
    								value="atribuirGrupoEmMassa"
								>
								<?= Csrf::inputField() ?>

    							<label
        							for="grupoSelecionado"
        							style="margin: 0;"
    							>
        							Atribuir ao grupo:
    							</label>

    							<select
        							id="grupoSelecionado"
									name="id_grupo_produto"
        							class="form-control"
        							style="width: 200px;"
    							>
        							<option value="">
            							Selecionar grupo
        							</option>

									<?php foreach ($grupoProdutos as $grupo): ?>
            							<option
                							value="<?= (int)$grupo['id_grupo_produto'] ?>"
            							>
                							<?= htmlspecialchars($grupo['descricao_grupo']) ?>
            							</option>
        							<?php endforeach; ?>
									
    							</select>

    							<button
        							type="submit"
        							id="btnAplicarGrupo"
        							class="btn btn-primary"
        							disabled
    							>
        							Aplicar grupo
    							</button>
							</form>
						</div>

						<div class="abas-produtos">
							<a href="listar_produto.php?tipo=ativo"
								class="btn btn-default <?= $tipo === 'ativo' ? 'active' : '' ?>">
								Ativos
							</a>

							<a href="listar_produto.php?tipo=arquivado"
								class="btn btn-default <?= $tipo === 'arquivado' ? 'active' : '' ?>">
								Arquivados
							</a>
						</div>

						<?php if ($tipo === 'ativo'): ?>
							<button
								type="button"
								id="btnModoSelecao"
								class="btn btn-default"
							>
								<i class="fa fa-check-square-o"></i> 
								Selecionar produtos
							</button>
						<?php endif; ?>

						<div class="filtros-produtos">
							<div class="campo-filtro">
								<label for="filtroCategoria">Categoria</label>
								<select id="filtroCategoria" class="form-control">
									<option value="">Todas</option>
									<?php
									foreach ($categorias as $categoria) {
										echo '<option value="' . htmlspecialchars($categoria['descricao_categoria'], ENT_QUOTES, 'UTF-8') . '">'
											. htmlspecialchars($categoria['descricao_categoria'], ENT_QUOTES, 'UTF-8') .
										'</option>';
									}
									?>
								</select>
							</div>

							<div class="campo-filtro">
								<label for="filtroGrupo">Grupo</label>
								<select id="filtroGrupo" class="form-control">
									<option value="">Todos</option>
									<option value="Sem grupo">Sem grupo</option>
									<?php
									foreach ($grupoProdutos as $grupo) {
										echo '<option value="' . htmlspecialchars($grupo['descricao_grupo'], ENT_QUOTES, 'UTF-8') . '">'
											. htmlspecialchars($grupo['descricao_grupo'], ENT_QUOTES, 'UTF-8') .
										'</option>';
									}
									?>
								</select>
							</div>

							<div class="campo-filtro">
								<label for="buscaProduto">Buscar</label>
								<input type="text" id="buscaProduto" class="form-control" placeholder="Nome, código ou categoria">
							</div>
						</div>
					</div>

					<?php if (isset($_SESSION['erro'])): ?>
    					<div style="
        					background:#fff3cd;
        					border:1px solid #ffeeba;
        					padding:15px;
        					margin-bottom:15px;
        					border-radius:5px;">
        					<strong>Atenção:</strong><br>
        					<?= $_SESSION['erro'] ?><br><br>

        					<form method="POST" action="<?= WWW ?>controle/control.php" style="display:inline;" onsubmit="return confirm('Atenção: ao arquivar este produto, ele será removido de entradas, saídas e relatórios. Movimentações que possuírem apenas este produto serão arquivadas automaticamente. Deseja continuar?');">
            					<input type="hidden" name="metodo" value="arquivar">
            					<input type="hidden" name="nomeClasse" value="ProdutoControle">
            					<input type="hidden" name="id_produto" value="<?= (int)$_SESSION['id_arquivar'] ?>">
            					<?= Csrf::inputField() ?>

            					<button style="background:#007bff;color:white;border:none;padding:5px 10px;">
                					Arquivar
            					</button>
        					</form>

        					<form method="GET" action="listar_produto.php" style="display:inline;">
            					<button style="background:#6c757d;color:white;border:none;padding:5px 10px;">
                					Cancelar
            					</button>
        					</form>
    					</div>
					<?php
					unset($_SESSION['erro']);
					unset($_SESSION['id_arquivar']);
					endif;
					?>

					<?php if (isset($_SESSION['msg'])): ?>
    					<div class="alert alert-success">
        					<?= $_SESSION['msg'] ?>
    					</div>
					<?php
					unset($_SESSION['msg']);
					endif;
					?>
					<table class="table table-bordered table-striped mb-none" id="datatable-default">
						<thead>
							<tr>
								<th></th>
								<th width="12%">Código</th>
								<th>Nome</th>
								<th>Grupo</th>
								<th width="18%">Tipo</th>
								<th width="12%">Preço</th>
								<th width="12%">Ação</th>
							</tr>
						</thead>
						<tbody id="tabela">
						<?php foreach ($produtos as $item): ?>
        						<tr>
									<td>
										<?php if ($tipo === 'ativo'): ?>
											<input 
												type="checkbox"
												class="produto-checkbox"	
												value="<?= (int)$item['id_produto'] ?>"
											>
										<?php endif; ?>
									</td>
            						<td><?= htmlspecialchars($item['codigo'] ?? '') ?></td>
            						<td><?= htmlspecialchars($item['descricao']) ?></td>
									<td><?= htmlspecialchars($item['descricao_grupo'] ?? 'Sem grupo') ?></td>
            						<td><?= htmlspecialchars($item['descricao_categoria']) ?></td>
            						<td><?= htmlspecialchars($item['preco']) ?></td>
            						<td>
                						<?php if ($tipo === 'ativo'): ?>
                    						<form method="POST" action="<?= WWW ?>controle/control.php" style="display:inline;" onsubmit="return confirm('Deseja excluir este produto?');">
                        						<input type="hidden" name="metodo" value="excluir">
                        						<input type="hidden" name="nomeClasse" value="ProdutoControle">
                        						<input type="hidden" name="id_produto" value="<?= (int)$item['id_produto'] ?>">
                        						<?= Csrf::inputField() ?>

                        						<button type="submit" style="border:none;background:none;cursor:pointer;" title="Excluir">
                            						<i class="fas fa-trash-alt"></i>
                        						</button>
                    						</form>

                    						<form method="POST" action="<?= WWW ?>controle/control.php" style="display:inline;" onsubmit="return confirm('Atenção: ao arquivar este produto, ele será removido de entradas, saídas e relatórios. Movimentações que possuírem apenas este produto serão arquivadas automaticamente. Deseja continuar?');">
                        						<input type="hidden" name="metodo" value="arquivar">
                        						<input type="hidden" name="nomeClasse" value="ProdutoControle">
                        						<input type="hidden" name="id_produto" value="<?= (int)$item['id_produto'] ?>">
                        						<?= Csrf::inputField() ?>

                        						<button type="submit" style="border:none;background:none;cursor:pointer;" title="Arquivar">
                            						<i class="fa-solid fa-folder"></i>
                        						</button>
                    						</form>

                    						<button
                        						type="button"
                        						onclick="clicar(<?= (int)$item['id_produto'] ?>)"
                        						style="border:none;background:none;cursor:pointer;"
                        						title="Editar"
                    						>
                        						<i class="fas fa-pencil-alt"></i>
                    						</button>
                						<?php else: ?>
                    						<form method="POST" action="<?= WWW ?>controle/control.php" style="display:inline;" onsubmit="return confirm('Deseja restaurar este produto? As entradas e saídas ocultadas por este arquivamento serão restauradas.');">
                        						<input type="hidden" name="metodo" value="desarquivar">
                        						<input type="hidden" name="nomeClasse" value="ProdutoControle">
                        						<input type="hidden" name="id_produto" value="<?= (int)$item['id_produto'] ?>">
                        						<?= Csrf::inputField() ?>

                        						<button title="Restaurar" style="border:none;background:none;cursor:pointer;">
                            						<i class="fa-solid fa-folder-open"></i>
                        						</button>
                    						</form>
                						<?php endif; ?>
            						</td>
        						</tr>
    						<?php endforeach; ?>
						</tbody>
					</table>

				</div><br>
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
			<script>
				$(document).ready(function () {
					const tabelaProdutos = $('#datatable-default').dataTable().api();

					let modoSelecao = false;

					const produtosSelecionados = new Set();

					function atualizarBotaoAplicar() {
    					const temProdutos = produtosSelecionados.size > 0;
    					const temGrupo = $('#grupoSelecionado').val() !== '';

    					$('#btnAplicarGrupo').prop(
        					'disabled',
        					!(temProdutos && temGrupo)
    					);
					}

					function atualizarQuantidadeSelecionados() {
    					const quantidade = produtosSelecionados.size;

    					$('#quantidadeSelecionados').text(
        					quantidade +
        					(quantidade === 1
            					? ' produto selecionado'
            					: ' produtos selecionados')
    					);

						atualizarBotaoAplicar();
					}

					function sincronizarCheckboxes() {
    					const configuracao = $('#datatable-default')
        					.dataTable()
        					.fnSettings();

    					configuracao.aoData.forEach(function (dado) {
        					const checkbox = $(dado.nTr)
            					.find('.produto-checkbox');

        					if (!checkbox.length) {
            					return;
        					}

        					checkbox.prop(
            					'checked',
            					produtosSelecionados.has(
                					Number(checkbox.val())
            					)
        					);
    					});
					}

					$('#datatable-default').on('draw.dt', function () {
    					sincronizarCheckboxes();
					});

					tabelaProdutos.column(0).visible(false);

					$('#datatable-default').on('change', '.produto-checkbox', function () {
    					const idProduto = Number(this.value);

    					if (this.checked) {
        					produtosSelecionados.add(idProduto);
    					} else {
        					produtosSelecionados.delete(idProduto);
    					}

    					atualizarQuantidadeSelecionados();
					});

					$('#btnModoSelecao').on('click', function () {
    					modoSelecao = !modoSelecao;

    					tabelaProdutos
        					.column(0)
        					.visible(modoSelecao);

    					if (modoSelecao) {
							$('#acoesSelecao').show();
							sincronizarCheckboxes();
        					$(this).html(
            					'<i class="fa fa-times"></i> Cancelar seleção'
        					);
    					} else {
							$('#acoesSelecao').hide();
							produtosSelecionados.clear();

							$('#grupoSelecionado').val('');

							sincronizarCheckboxes();
							atualizarQuantidadeSelecionados();

        					$(this).html(
            					'<i class="fa fa-check-square"></i> Selecionar produtos'
        					);
    					}
					});

					function alterarSelecaoResultados(selecionar) {
    					const configuracao = $('#datatable-default')
        					.dataTable()
        					.fnSettings();

    					configuracao.aiDisplay.forEach(function (indice) {
        					const checkbox = $(configuracao.aoData[indice].nTr)
            					.find('.produto-checkbox');

        					if (!checkbox.length) {
            					return;
        					}

        					const idProduto = Number(checkbox.val());

        					if (selecionar) {
            					produtosSelecionados.add(idProduto);
        					} else {
            					produtosSelecionados.delete(idProduto);
        					}
    					});

    					atualizarQuantidadeSelecionados();
    					sincronizarCheckboxes();
					}

					$('#btnSelecionarTodos').on('click', function () {
    					alterarSelecaoResultados(true);
					});

					$('#btnDesmarcarTodos').on('click', function () {
    					alterarSelecaoResultados(false);
					});

					$('#formAtribuirGrupo').on('submit', function (event) {
    					event.preventDefault();

    					const quantidade = produtosSelecionados.size;
    					const grupo = $('#grupoSelecionado option:selected');

    					if (!quantidade || !grupo.val()) {
        					return;
    					}

    					$('#produtosJson').val(
        					JSON.stringify([...produtosSelecionados])
    					);

    					const mensagem =
        					`Aplicar o grupo "${grupo.text().trim()}" a ` +
        					`${quantidade} produto${quantidade === 1 ? '' : 's'}?`;

    					if (confirm(mensagem)) {
        					this.submit();
    					}
					});	

					$('#grupoSelecionado').on('change', function () {
    					atualizarBotaoAplicar();
					});

					$('#filtroCategoria').on('change', function () {
						tabelaProdutos
							.column(4)
							.search(this.value)
							.draw();
					});

					$('#filtroGrupo').on('change', function () {
						tabelaProdutos
							.column(3)
							.search(this.value)
							.draw();
					});

					$('#buscaProduto').on('keyup change', function () {
						tabelaProdutos
							.search(this.value)
							.draw();
					});
				});
			</script>
			<div align="right">
				<iframe src="https://www.wegia.org/software/footer/matPat.html" width="200" height="60" style="border:none;"></iframe>
			</div>
		</section>
	</div>
</body>

</html>
