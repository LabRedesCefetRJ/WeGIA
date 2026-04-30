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
require_once ROOT . "/dao/Conexao.php";

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

$cpf = filter_input(INPUT_GET, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$cpf || strlen($cpf) < 1) {
    http_response_code(400);
    echo "O CPF informado não é válido.";
    exit();
}

$pdo = Conexao::connect();

$stmt = $pdo->prepare("SELECT * FROM pessoa WHERE cpf = :cpf");
$stmt->bindParam(':cpf', $cpf);
$stmt->execute();
$pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pessoa) {
    http_response_code(404);
    echo "Pessoa não encontrada.";
    exit();
}

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
    
    <script>
        $(function() {
            var pessoa = <?php echo json_encode($pessoa); ?>;
            $("#nome").val(pessoa.nome).prop('readonly', true);
            $("#sobrenome").val(pessoa.sobrenome).prop('readonly', true);
            $("#cpf").val(pessoa.cpf).prop('readonly', true);
            if (pessoa.data_nascimento) {
                var date = pessoa.data_nascimento.split("/");
                if (date.length === 3) {
                    $("#nascimento").val(date[2] + "-" + date[1] + "-" + date[0]).prop('readonly', true);
                } else {
                    $("#nascimento").val(pessoa.data_nascimento).prop('readonly', true);
                }
            }
            if (pessoa.sexo == "m") {
                $("#radioM").prop('checked', true);
                $("input[name=gender]").prop('disabled', true);
                $("#hiddenGender").val('m');
            } else if (pessoa.sexo == "f") {
                $("#radioF").prop('checked', true);
                $("input[name=gender]").prop('disabled', true);
                $("#hiddenGender").val('f');
            }
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
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Nome *</label>
                                    <div class="col-md-6"><input type="text" class="form-control" name="nome" id="nome" required readonly>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Sobrenome *</label>
                                    <div class="col-md-6"><input type="text" class="form-control" name="sobrenome" id="sobrenome"
                                            required readonly></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">CPF *</label>
                                    <div class="col-md-6"><input type="text" class="form-control" name="cpf" id="cpf"
                                            maxlength="14" required readonly></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Sexo *</label>
                                    <div class="col-md-6">
                                        <input type="radio" name="gender" id="radioM" value="m" required disabled> M
                                        <input type="radio" name="gender" id="radioF" value="f" required disabled> F
                                        <input type="hidden" name="gender" id="hiddenGender" value="">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Nascimento *</label>
                                    <div class="col-md-6"><input type="date" class="form-control" name="nascimento" id="nascimento"
                                            min="<?= $dataNascimentoMinima?>" max="<?= $dataNascimentoMaxima?>"
                                            required readonly></div>
                                </div>
                                <hr>
                                <h4 class="mb-xlg">Detalhes do Voluntariado</h4>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Data de Admissão *</label>
                                    <div class="col-md-6"><input type="date" class="form-control" name="data_admissao"
                                            required></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Situação *</label>
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
                                <input type="hidden" name="metodo" value="incluirExistente">
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
