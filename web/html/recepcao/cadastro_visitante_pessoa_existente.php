<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit();
}
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

require_once ROOT . "/controle/VisitanteControle.php";
require_once ROOT . "/classes/Visitante.php";
require_once ROOT . "/html/personalizacao_display.php";
require_once ROOT . "/dao/Conexao.php";

$dataNascimentoMaxima = Visitante::getDataNascimentoMaxima();
$dataNascimentoMinima = Visitante::getDataNascimentoMinima();

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
require_once ROOT . '/classes/Csrf.php';
?>
<!DOCTYPE html>
<html class="fixed">

<head>
    <meta charset="UTF-8">
    <title>Cadastro de Visitante</title>
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
                $("input[name=gender]").prop('readonly', true);
                $("#hiddenGender").val('m');
            } else if (pessoa.sexo == "f") {
                $("#radioF").prop('checked', true);
                $("input[name=gender]").prop('readonly', true);
                $("#hiddenGender").val('f');
            }
            $("#cep").val(pessoa.cep).prop('readonly', true);
            $("#uf").val(pessoa.estado).prop('readonly', true);
            $("#cidade").val(pessoa.cidade).prop('readonly', true);
            $("#bairro").val(pessoa.bairro).prop('readonly', true);
            $("#rua").val(pessoa.logradouro).prop('readonly', true);
            $("#numero_residencia").val(pessoa.numero_endereco).prop('readonly', true);
            $("#numero_residencia").prop('readonly', true);
            $("#complemento").val(pessoa.complemento).prop('readonly', true);
            $("#ibge").val(pessoa.ibge).prop('readonly', true);
            if (pessoa.numero_endereco == 'N?o possui' || pessoa.numero_endereco == null || pessoa.numero_endereco == '')
                $("#numResidencial").prop('checked', true);
        });

        function editar_informacoes() {
            $("#nome").prop('readonly', false);
            $("#sobrenome").prop('readonly', false);
            $("#radioM").prop('disabled', false);
            $("#radioF").prop('disabled', false);
            $("#nascimento").prop('readonly', false);
            $("#cep").prop('readonly', false);
            $("#uf").prop('readonly', false);
            $("#cidade").prop('readonly', false);
            $("#bairro").prop('readonly', false);
            $("#rua").prop('readonly', false);
            $("#numero_residencia").prop('readonly', false);
            $("#numResidencial").prop('disabled', false);
            $("#complemento").prop('readonly', false);
            $("#ibge").prop('readonly', false);
            $("#botaoEditarIP").html('Cancelar');
            $("#botaoCadastrarIP").prop('disabled', true);
            $("#botaoEditarIP").removeAttr('onclick');
            $("#botaoEditarIP").attr('onclick', "return cancelar_informacoes()");
        }

        function cancelar_informacoes() {
            $("#nome").prop('readonly', true);
            $("#sobrenome").prop('readonly', true);
            $("#radioM").prop('disabled', true);
            $("#radioF").prop('disabled', true);
            $("#nascimento").prop('readonly', true);
            $("#cep").prop('readonly', true);
            $("#uf").prop('readonly', true);
            $("#cidade").prop('readonly', true);
            $("#bairro").prop('readonly', true);
            $("#rua").prop('readonly', true);
            $("#numero_residencia").prop('readonly', true);
            $("#numResidencial").prop('disabled', true);
            $("#complemento").prop('readonly', true);
            $("#ibge").prop('readonly', true);
            $("#botaoEditarIP").html('Editar');
            $("#botaoCadastrarIP").prop('disabled', false);
            $("#botaoEditarIP").removeAttr('onclick');
            $("#botaoEditarIP").attr('onclick', "return editar_informacoes()");
        }

        function pesquisacep(valor) {
            var cep = valor.replace(/\D/g, '');
            if (cep != "") {
                var validacep = /^[0-9]{8}$/;
                if (validacep.test(cep)) {
                    document.getElementById('rua').value = "...";
                    document.getElementById('bairro').value = "...";
                    document.getElementById('cidade').value = "...";
                    document.getElementById('uf').value = "...";
                    var script = document.createElement('script');
                    script.src = 'https://viacep.com.br/ws/' + cep + '/json/?callback=meu_callback';
                    document.body.appendChild(script);
                } else {
                    alert("Formato de CEP inválido.");
                }
            }
        }

        function meu_callback(conteudo) {
            if (!("erro" in conteudo)) {
                document.getElementById('rua').value = (conteudo.logradouro);
                document.getElementById('bairro').value = (conteudo.bairro);
                document.getElementById('cidade').value = (conteudo.localidade);
                document.getElementById('uf').value = (conteudo.uf);
                document.getElementById('ibge').value = (conteudo.ibge);
            } else {
                alert("CEP não encontrado.");
            }
        }

        function numero_residencial() {
            if ($("#numResidencial").prop('checked')) {
                $("#numero_residencia").val('');
                document.getElementById("numero_residencia").disabled = true;
            } else {
                document.getElementById("numero_residencia").disabled = false;
            }
        }
    </script>
</head>

<body>
    <section class="body">
        <div id="header"></div>
        <div class="inner-wrapper">
            <aside id="sidebar-left" class="sidebar-left menuu"></aside>
            <section role="main" class="content-body">
                <header class="page-header">
                    <h2>Cadastro Visitante</h2>
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
                                <h4 class="mb-xlg">Endereço</h4>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">CEP</label>
                                        <div class="col-md-8">
                                            <input type="text" name="cep" id="cep" class="form-control" value="" size="10" maxlength="9" onblur="pesquisacep(this.value);" required>
                                        </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Estado</label>
                                        <div class="col-md-8">
                                            <input type="text" name="uf" id="uf" class="form-control" required readonly>
                                        </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Cidade</label>
                                        <div class="col-md-8">
                                            <input type="text" name="cidade" id="cidade" class="form-control" required readonly>
                                        </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Bairro</label>
                                        <div class="col-md-8">
                                            <input type="text" name="bairro" id="bairro" class="form-control" required readonly>
                                        </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Logradouro</label>
                                        <div class="col-md-8">
                                            <input type="text" name="rua" id="rua" class="form-control" required readonly>
                                        </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Número</label>
                                        <div class="col-md-8">
                                            <input type="number" class="form-control" name="numero_residencia" id="numero_residencia" min="0" oninput="this.value = Math.abs(this.value)">
                                            <div class="checkbox">
                                                <label><input type="checkbox" id="numResidencial" onclick="return numero_residencial()" disabled> Sem número</label>
                                            </div>
                                        </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Complemento</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="complemento" id="complemento" maxlength="50">
                                        </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">IBGE</label>
                                        <div class="col-md-8">
                                            <input type="text" name="ibge" id="ibge" class="form-control" required readonly>
                                        </div>
                                </div>
                            </div>
                            <div class="panel-footer">
                                <?= Csrf::inputField()?>
                                <input type="hidden" name="nomeClasse" value="VisitanteControle">
                                <input type="hidden" name="metodo" value="incluir_existente">
                                <button type="button" class="btn btn-primary" id="botaoEditarIP" onclick="return editar_informacoes()">Editar</button>
                                <button type="submit" class="btn btn-primary" id="botaoCadastrarIP">Cadastrar</button>
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
