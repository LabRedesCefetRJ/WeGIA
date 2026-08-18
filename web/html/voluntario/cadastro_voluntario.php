<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit();
}
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

require_once ROOT . "/controle/VoluntarioControle.php";
require_once ROOT . "/classes/Voluntario.php";
require_once ROOT . "/html/personalizacao_display.php";
$dataNascimentoMaxima = Voluntario::getDataNascimentoMaxima();
$dataNascimentoMinima = Voluntario::getDataNascimentoMinima();

$erro = null;
if (isset($_SESSION['erro'])) {
    $erro = $_SESSION['erro'];
    unset($_SESSION['erro']);
}
if (isset($_GET['msg'])) {
    $erro = $_GET['msg'];
}

$cpfPrefilled = '';
if (isset($_GET['cpf'])) {
    $cpfPrefilled = htmlspecialchars($_GET['cpf'], ENT_QUOTES, 'UTF-8');
}

// Teste da Issue #1587

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$situacao = $mysqli->query("SELECT * FROM situacao");
require_once ROOT . '/classes/Csrf.php';
?>
<!DOCTYPE html>
<html class="fixed">

<head>
    <meta charset="UTF-8">
    <title>Cadastro de Voluntário</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
    <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
    <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
    <script src="../../assets/vendor/modernizr/modernizr.js"></script>
    <script src="../../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../../assets/javascripts/theme.js"></script>
    <script src="../../assets/javascripts/theme.custom.js"></script>
    <script src="../../assets/javascripts/theme.init.js"></script>
    <script src="../../Functions/onlyNumbers.js"></script>
    <script src="../../Functions/onlyChars.js"></script>
    <script src="../../Functions/mascara.js"></script>
    <script src="<?php echo WWW; ?>Functions/testaCPF.js"></script>

    <style type="text/css">
    .obrig {
        color: rgb(255, 0, 0);
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
                    <h2>Cadastro Voluntário</h2>
                </header>
                <div class="row" id="formulario">
                    <?php if ($erro): ?>
                    <div style="color: red; font-weight: bold; text-align:center">
                        <?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <?php
endif; ?>
                    <div class="col-md-12 col-lg-12">
                        <form class="form-horizontal" method="POST" action="../../controle/control.php">
                            <div class="panel-body">
                                <h4 class="mb-xlg">Informações Pessoais</h4>
                                <h5 class="obrig">Campos Obrigatórios(*)</h5>
                                <div class="form-group">
                                    <label class="col-md-3 control-label" for="profileFirstName">Nome<sup class="obrig">*</sup></label>
                                    <div class="col-md-6">
                                    <input type="text" class="form-control<?= isset($fieldErrors['nome']) ? ' is-invalid' : '' ?>" name="nome" id="nome" onkeypress="return Onlychars(event)" required value="<?= htmlspecialchars($oldInput['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <p id="error_nome" class="help-block text-danger" style="display: <?= isset($fieldErrors['nome']) ? 'block' : 'none' ?>;">
                                        <?= isset($fieldErrors['nome']) ? htmlspecialchars($fieldErrors['nome'], ENT_QUOTES, 'UTF-8') : '' ?>
                                    </p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Sobrenome<sup class="obrig">*</sup></label>
                                    <div class="col-md-6">
                                    <input type="text" class="form-control<?= isset($fieldErrors['sobrenome']) ? ' is-invalid' : '' ?>" name="sobrenome" id="sobrenome" onkeypress="return Onlychars(event)" required value="<?= htmlspecialchars($oldInput['sobrenome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <p id="error_sobrenome" class="help-block text-danger" style="display: <?= isset($fieldErrors['sobrenome']) ? 'block' : 'none' ?>;">
                                        <?= isset($fieldErrors['sobrenome']) ? htmlspecialchars($fieldErrors['sobrenome'], ENT_QUOTES, 'UTF-8') : '' ?>
                                    </p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label" for="cpf">Número do CPF<sup class="obrig">*</sup></label>
                                    <div class="col-md-6">
                                    <input type="text" class="form-control<?= isset($fieldErrors['cpf']) ? ' is-invalid' : '' ?>" id="cpf" name="cpf" placeholder="Ex: 222.222.222-22" maxlength="14" onblur="validarCPF(this.value)" onkeypress="return Onlynumbers(event)" onkeyup="mascara('###.###.###-##', this, event)" value="<?= htmlspecialchars($oldInput['cpf'] ?? ($cpf ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                                    <p id="error_cpf" class="help-block text-danger" style="display: <?= isset($fieldErrors['cpf']) ? 'block' : 'none' ?>;">
                                        <?= isset($fieldErrors['cpf']) ? htmlspecialchars($fieldErrors['cpf'], ENT_QUOTES, 'UTF-8') : '' ?>
                                    </p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label" for="genero">Gênero<sup class="obrig">*</sup></label>
                                    <div class="col-md-6">
                                    <select class="form-control" name="gender" id="genero" required onchange="return this.value === 'm' ? exibir_reservista() : esconder_reservista()">
                                      <option value="" selected disabled>Selecionar</option>
                                      <option value="m" <?= isset($oldInput['gender']) && $oldInput['gender'] === 'm' ? 'selected' : '' ?>>Masculino</option>
                                      <option value="f" <?= isset($oldInput['gender']) && $oldInput['gender'] === 'f' ? 'selected' : '' ?>>Feminino</option>
                                      <option value="o" <?= isset($oldInput['gender']) && $oldInput['gender'] === 'o' ? 'selected' : '' ?>>Outro</option>
                                      <option value="n" <?= isset($oldInput['gender']) && $oldInput['gender'] === 'n' ? 'selected' : '' ?>>Prefiro não informar</option>
                                    </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label" for="profileCompany">Nascimento<sup class="obrig">*</sup></label>
                                    <div class="col-md-6">
                                    <input type="date" name="nascimento" id="nascimento" class="form-control<?= isset($fieldErrors['nascimento']) ? ' is-invalid' : '' ?>" min="<?= $dataNascimentoMinima ?>" max="<?= $dataNascimentoMaxima ?>" required value="<?= htmlspecialchars($oldInput['nascimento'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <p id="error_nascimento" class="help-block text-danger" style="display: <?= isset($fieldErrors['nascimento']) ? 'block' : 'none' ?>;">
                                        <?= isset($fieldErrors['nascimento']) ? htmlspecialchars($fieldErrors['nascimento'], ENT_QUOTES, 'UTF-8') : '' ?>
                                    </p>
                                    </div>
                                </div>
                                <hr>
                                <h4 class="mb-xlg">Detalhes do Voluntariado</h4>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Data de Admissão<sup class="obrig">*</sup></label>
                                    <div class="col-md-6"><input type="date" class="form-control" name="data_admissao"
                                            required></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Situação<sup class="obrig">*</sup></label>
                                    <div class="col-md-6">
                                        <select class="form-control" name="situacao" required>
                                            <option selected disabled>Selecionar</option>
                                            <?php while ($row = $situacao->fetch_array(MYSQLI_NUM)) {
    echo "<option value=" . $row[0] . ">" . htmlspecialchars($row[1]) . "</option>";
}?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-footer">
                                <?= Csrf::inputField()?>
                                <input type="hidden" name="nomeClasse" value="VoluntarioControle">
                                <input type="hidden" name="metodo" value="incluir">
                                <button type="submit" class="btn btn-primary">Salvar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </section>
    <script>
        $(function () {
            $("#header").load("../header.php");
            $(".menuu").load("../menu.php");
        });
    </script>
</body>

</html>
