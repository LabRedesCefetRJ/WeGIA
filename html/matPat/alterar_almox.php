<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if(session_status() === PHP_SESSION_NONE)
	session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: " . WWW . "html/index.php");
	exit();
}else{
	session_regenerate_id();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

permissao($_SESSION['id_pessoa'], 22, 3);

require_once ROOT . "/html/personalizacao_display.php";

require_once ROOT . '/classes/Csrf.php';

include_once ROOT . '/dao/Conexao.php';
include_once ROOT . '/dao/AlmoxarifadoDAO.php';

if(!isset($_SESSION['almoxarifado'])) {
    extract($_REQUEST);

    header('Location:' . WWW . 'controle/control.php?metodo=listarUm&nomeClasse=AlmoxarifadoControle&id_almoxarifado=' . $id_almoxarifado . '&nextPage=' . WWW . 'html/matPat/alterar_almox.php?id_almoxarifado=' . $id_almoxarifado);
    exit();
}

if(isset($_SESSION['almoxarifado'])) {
    $almoxarifado = $_SESSION['almoxarifado'];
    unset($_SESSION['almoxarifado']);
}
?>

<!doctype html>
<html class="fixed">

<head>
    <meta charset="UTF-8">

    <title>Alterar Almoxarifado</title>
    <!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Web Fonts  -->
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

	<!-- Theme CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="<?= WWW ?>assets/vendor/modernizr/modernizr.js"></script>

	<script src="<?= WWW ?>assets/vendor/jquery/jquery.min.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="<?= WWW ?>assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="<?= WWW ?>assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="<?= WWW ?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.js"></script>
	<script src="<?= WWW ?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
	<script type="text/javascript">
        function editar() {
            $("#descricao").prop('disabled', false);

            $("#botaoEditar").html('Cancelar');
            $("#botaoSalvar").prop('disabled', false);

            $("#botaoEditar").attr('onclick', "cancelar()");
        }

        function cancelar() {
            $("#descricao").prop('disabled', true);

            $("#botaoEditar").html('Editar');
            $("#botaoSalvar").prop('disabled', true);

            $("#botaoEditar").attr('onclick', "editar()")
        }

        $(function() {
            $("#header").load("<?= WWW ?>html/header.php");
            $(".menuu").load("<?= WWW ?>html/menu.php");

            var almox = JSON.parse('<?php echo json_encode($almoxarifado ?? []); ?>');
            console.log(almox);

            $("#descricao").prop('disabled', true);
            $("#botaoSalvar").prop('disabled', true);

            $("#descricao").val(almox.descricao_almoxarifado);
            $("#id_almoxarifado").val(almox.id_almoxarifado);
        });
    </script>
</head>

<body>
    <div class="inner-wrapper">
        <aside id="sidebar-left" class="sidebar-left menuu"></aside>

        <section role="main" class="content-body">
            <header class="page-header">
                <h2>Alterar Almoxarifado</h2>
            </header>

            <div class="row">
                <div class="col-md-8 col-lg-6">

                    <section class="panel">
                        <header class="panel-heading">
                            <h2 class="panel-title">Editar Dados</h2>
                        </header>

                        <div class="panel-body">
                            <form action="<?= WWW ?>controle/control.php" method="POST">

                                <?= Csrf::inputField() ?>

                                <input type="hidden" name="nomeClasse" value="AlmoxarifadoControle">
                                <input type="hidden" name="metodo" value="alterarAlmoxarifado">
                                <input type="hidden" name="id_almoxarifado" id="id_almoxarifado">

                                <div class="form-group">
                                    <label>Nome do Almoxarifado</label>
                                    <input type="text" class="form-control" name="descricao_almoxarifado" id="descricao">
                                </div>

                                <div class="panel-footer">
                                    <button type="button" class="btn btn-primary" id="botaoEditar" onclick="editar()">Editar</button>
                                    <button type="submit" class="btn btn-primary" id="botaoSalvar">Salvar</button>
                                    <button type="button" class="btn btn-default" onclick="window.location.href='<?= WWW ?>html/matPat/listar_almox.php'">Voltar</button>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        </section>
    </div> 
</body>    
</html>