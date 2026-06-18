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

include_once ROOT . '/dao/Conexao.php';
include_once ROOT . '/dao/ProdutoDAO.php';

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
} else {
    $produtos = $_SESSION['produtos'];
    unset($_SESSION['produtos']);
}
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
					<div style="margin-bottom: 15px;">
    					<a href="listar_produto.php?tipo=ativo"
        					class="btn btn-default <?= $tipo === 'ativo' ? 'active' : '' ?>">
        					Ativos
    					</a>

    					<a href="listar_produto.php?tipo=arquivado"
        					class="btn btn-default <?= $tipo === 'arquivado' ? 'active' : '' ?>">
        					Arquivados
    					</a>
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
								<th width="12%">Código</th>
								<th>Nome</th>
								<th width="18%">Tipo</th>
								<th width="12%">Preço</th>
								<th width="12%">Ação</th>
							</tr>
						</thead>
						<tbody id="tabela">
    						<?php foreach (json_decode($produtos, true) as $item): ?>
        						<tr>
            						<td><?= htmlspecialchars($item['codigo'] ?? '') ?></td>
            						<td><?= htmlspecialchars($item['descricao']) ?></td>
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
			<div align="right">
				<iframe src="https://www.wegia.org/software/footer/matPat.html" width="200" height="60" style="border:none;"></iframe>
			</div>
</body>

</html>