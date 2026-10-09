<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 12, 7);

require_once ROOT . '/classes/Csrf.php';
require_once ROOT . '/classes/Setor.php';

require_once ROOT . "/html/personalizacao_display.php";

?>


<!DOCTYPE html>

<html class="fixed">

<head>
    <!-- Basic -->
    <meta charset="UTF-8">

    <title>Cadastro de Setor</title>

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

    <!-- Specific Page Vendor CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/select2/select2.css" />
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/theme.css" />

    <!-- Skin CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>/assets/stylesheets/skins/default.css" />

    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/theme-custom.css">

    <!-- Head Libs -->
    <script src="<?php echo WWW; ?>assets/vendor/modernizr/modernizr.js"></script>

    <!-- Vendor -->
    <script src="<?php echo WWW; ?>assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

    <!-- Specific Page Vendor -->
    <script src="<?php echo WWW; ?>assets/vendor/jquery-autosize/jquery.autosize.js"></script>

    <!-- Theme Base, Components and Settings -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.js"></script>

    <!-- Theme Custom -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.custom.js"></script>

    <!-- Theme Initialization Files -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.init.js"></script>


    <!-- javascript functions -->
    <script src="<?php echo WWW; ?>Functions/onlyNumbers.js"></script>
    <script src="<?php echo WWW; ?>Functions/onlyChars.js"></script>
    <script src="<?php echo WWW; ?>Functions/mascara.js"></script>
    <script src="<?php echo WWW; ?>Functions/testaCPF.js"></script>


    <style type="text/css">
        .panel-body {
            margin-bottom: 15px;
        }

        img {
            margin-left: 10px;
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
                    <h2>Cadastro de Setor</h2>
                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li>
                                <a href="<?php echo WWW; ?>html/home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li><span>Cadastro de Setor</span></li>
                        </ol>
                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>

                <!-- start: page -->
                <?php
                $setorCriado = false;
                if (isset($_SESSION['msg_c']) && !empty($_SESSION['msg_c'])) {
                    $setorCriado = true;
                    echo ('<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['msg_c']) . '</div>');
                    $_SESSION['msg_c'] = "";
                } else if (isset($_SESSION['msg_e']) && !empty($_SESSION['msg_e'])) {
                    echo ('<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['msg_e']) . '</div>');
                    $_SESSION['msg_e'] = "";
                }
                ?>

                <p>
                    <a href="cadastro_visitado.php" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Voltar ao cadastro de visitado
                    </a>
                    <?php if ($setorCriado) : ?>
                        <a href="cadastro_visitado.php?tipo=setor" class="btn btn-success">
                            <i class="fa fa-check"></i> Adicionar setor como visitado
                        </a>
                    <?php endif; ?>
                </p>

                <section class="panel">
                    <header class="panel-heading">
                        <h2 class="panel-title">Novo setor</h2>
                    </header>
                    <div class="panel-body">
                        <form method="POST" action="../../controle/control.php" class="form-horizontal">
                            <?= Csrf::inputField() ?>
                            <input type="hidden" name="nomeClasse" value="SetorControle">
                            <input type="hidden" name="metodo" value="incluir">
                            <div class="form-group">
                                <label for="descricaoSetor" class="col-md-2 control-label">Descrição <span class="required">*</span></label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="descricaoSetor" name="descricao" maxlength="<?= Setor::TAMANHO_MAXIMO_DESCRICAO ?>" placeholder="Ex: Secretaria" required autofocus>
                                    <p class="help-block">Até <?= Setor::TAMANHO_MAXIMO_DESCRICAO ?> caracteres. Depois de criado, o setor poderá ser adicionado como visitado.</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-2">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Criar setor</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </section>
            </section>
        </div>
    </section>

    <!-- end: page -->
    <!-- Vendor -->
    <script src="<?php echo WWW; ?>assets/vendor/select2/select2.js"></script>

    <!-- Theme Base, Components and Settings -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.js"></script>

    <!-- Theme Custom -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.custom.js"></script>

    <!-- Theme Initialization Files -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.init.js"></script>

    <script>
        $(function() {
            $("#header").load("<?php echo WWW; ?>html/header.php");
            $(".menuu").load("<?php echo WWW; ?>html/menu.php");
        });
    </script>

    <div align="right">
        <iframe src="https://www.wegia.org/software/footer/pessoa.html" width="200" height="60" style="border:none;"></iframe>
    </div>
</body>

</html>