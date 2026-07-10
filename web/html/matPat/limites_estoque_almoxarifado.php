<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

require_once ROOT . '/dao/EstoqueDAO.php';
require_once ROOT . "/html/personalizacao_display.php";

permissao($_SESSION['id_pessoa'], 21, 5);

$idAlmoxarifado = filter_input(INPUT_GET, 'id_almoxarifado', FILTER_VALIDATE_INT);

if (!$idAlmoxarifado || $idAlmoxarifado < 1) {
    header('Location: ' . WWW . 'html/matPat/listar_almox.php');
    exit();
}

$estoqueDAO = new EstoqueDAO();

$produtos = $estoqueDAO->listarProdutosPorAlmoxarifadoComLimite($idAlmoxarifado);

?>

<!doctype html>
<html class="fixed">

<head>
    <meta charset="UTF-8">

    <title>Limites de Estoque</title>

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

    <script>
        $(function() {
            $("#header").load("<?= WWW ?>html/header.php");
            $(".menuu").load("<?= WWW ?>html/menu.php");
        });

        function salvarQuantidadeMinima(idProduto, idAlmoxarifado) {

            const input = $("#qtd_minima_" + idProduto);

            $.ajax({
                url: "<?= WWW ?>controle/control.php",
                type: "POST",
                dataType: "json",

                data: {
                    nomeClasse: "EstoqueControle",
                    metodo: "atualizarQuantidadeMinima",
                    id_produto: idProduto,
                    id_almoxarifado: idAlmoxarifado,
                    qtd_minima: input.val()
                },

                success: function(resposta) {

                    if (resposta.sucesso) {
                        alert("Quantidade mínima atualizada com sucesso.");
                    } else {
                        alert(resposta.mensagem || "Erro ao atualizar.");
                    }
                },

                error: function(xhr) {
                    alert(xhr.responseText);
                }          
            });
        }
    </script>

    <style>
        .input-qtd-minima {
            width: 100px;
        }
    </style>
</head>

<body>

<section class="body">

    <div id="header"></div>

    <div class="inner-wrapper">

        <aside id="sidebar-left" class="sidebar-left menuu"></aside>

        <section role="main" class="content-body">

           <header class="page-header">
                <h2>Limites de Estoque</h2>

                <div class="right-wrapper pull-right">
                    <ol class="breadcrumbs">
                        <li>
                            <a href="<?= WWW ?>html/home.php">
                                <i class="fa fa-home"></i>
                            </a>
                        </li>
                        <li><span>Material e Patrimônio</span></li>
                        <li><span>Limites de Estoque</span></li>
                    </ol>

                    <a class="sidebar-right-toggle">
                        <i class="fa fa-chevron-left"></i>
                    </a>
                </div>
            </header>

            <section class="panel">

                <header class="panel-heading">
                    <h2 class="panel-title">
                        Produtos do Almoxarifado
                    </h2>
                </header>

                <div class="panel-body">

                    <table class="table table-bordered table-striped table-hover">

                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Produto</th>
                                <th>Categoria</th>
                                <th>Quantidade Atual</th>
                                <th>Quantidade Mínima</th>
                                <th>Ação</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (!empty($produtos)): ?>

                            <?php foreach ($produtos as $produto): ?>

                                <tr>

                                    <td>
                                        <?= htmlspecialchars($produto['codigo']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($produto['descricao']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($produto['descricao_categoria']) ?>
                                    </td>

                                    <td>

                                        <?php
                                            $classe = '';

                                            if (
                                                isset($produto['qtd_minima']) &&
                                                $produto['qtd'] <= $produto['qtd_minima']
                                            ) {
                                                $classe = 'text-danger';
                                            }
                                        ?>

                                        <strong class="<?= $classe ?>">
                                            <?= (int)$produto['qtd'] ?>
                                        </strong>

                                    </td>

                                    <td>

                                        <input
                                            type="number"
                                            min="0"
                                            class="form-control input-qtd-minima"
                                            id="qtd_minima_<?= (int)$produto['id_produto'] ?>"
                                            value="<?= (int)$produto['qtd_minima'] ?>"
                                        >

                                    </td>

                                    <td>

                                        <button
                                            type="button"
                                            class="btn btn-primary btn-sm"
                                            onclick="salvarQuantidadeMinima(
                                                <?= (int)$produto['id_produto'] ?>,
                                                <?= (int)$produto['id_almoxarifado'] ?>
                                            )"
                                        >
                                            Salvar
                                        </button>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="6">
                                    Nenhum produto encontrado neste almoxarifado.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>

        </section>

    </div>

</section>
<!-- end: page -->

				<!-- Vendor -->

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