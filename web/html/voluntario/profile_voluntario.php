<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
Util::definirFusoHorario();
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
} else {
    session_regenerate_id();
}

$id_pessoa = filter_var($_SESSION['id_pessoa'], FILTER_SANITIZE_NUMBER_INT);

if (!$id_pessoa || $id_pessoa < 1) {
    http_response_code(400);
    echo json_encode(['erro' => 'O id da pessoa informado não é válido.']);
    exit();
}

// TODO: update permission logic if needed
// require_once "../permissao/permissao.php";
// permissao($_SESSION['id_pessoa'], 11, 7);

extract($_REQUEST);

//Sanitizar entrada
$idVoluntario = filter_input(INPUT_GET, 'id_voluntario', FILTER_SANITIZE_NUMBER_INT);

if (!$idVoluntario || $idVoluntario < 1) {
    echo json_encode(['erro' => 'O id de um voluntário deve ser maior ou igual a 1.']);
    exit(400);
}

try {
    require_once "../../dao/Conexao.php";
    $pdo = Conexao::connect();

    if (!isset($_SESSION['voluntario'])) {
        header('Location: ../../controle/control.php?metodo=listarUm&nomeClasse=VoluntarioControle&id_voluntario=' . urlencode($idVoluntario));
        exit();
    } else {
        $vol = $_SESSION['voluntario'];
        unset($_SESSION['voluntario']);
        $vol = json_decode($vol);
        if ($vol) {
            $vol = $vol[0];
            $vol = json_encode([$vol]);
        }
    }
    require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

    $situacao = $pdo->query("SELECT * FROM situacao")->fetchAll();

    require_once "../personalizacao_display.php";
    require_once ROOT . "/controle/VoluntarioControle.php";
    require_once ROOT . '/classes/Util.php';
    require_once "../../classes/Voluntario.php";
    require_once "../../classes/Csrf.php";
    require_once "../geral/msg.php";

    $dataNascimentoMaxima = Voluntario::getDataNascimentoMaxima();
    $dataNascimentoMinima = Voluntario::getDataNascimentoMinima();

} catch (Exception $e) {
    Util::tratarException($e);
    exit();
}
?>
<!doctype html>
<html class="fixed">

<head>
    <meta charset="UTF-8">
    <title>Perfil Voluntário</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">
    <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
    <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
    <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
    <script src="../../assets/vendor/modernizr/modernizr.js"></script>
    <script src="../../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="../../Functions/onlyNumbers.js"></script>
    <script src="../../Functions/onlyChars.js"></script>
    <script src="../../Functions/mascara.js"></script>

    <script>
        function alterardate(data) {
            if (!data) return "";
            var date = data.split("/");
            if(date.length !== 3) return data.split(" ")[0]; // handles yyyy-mm-dd
            return date[2] + "-" + date[1] + "-" + date[0];
        }

        $(function() {
            $("#header").load("../header.php");
            $(".menuu").load("../menu.php");

            var voluntario = <?= $vol ?>;
            $.each(voluntario, function(i, item) {
                // Info Pessoal
                $("#nomeForm").val(item.nome).prop('disabled', true);
                $("#sobrenomeForm").val(item.sobrenome).prop('disabled', true);
                if (item.sexo == "m") {
                    $("#radioM").prop('checked', true).prop('disabled', true);
                    $("#radioF").prop('checked', false).prop('disabled', true);
                } else if (item.sexo == "f") {
                    $("#radioM").prop('checked', false).prop('disabled', true);
                    $("#radioF").prop('checked', true).prop('disabled', true);
                }
                $("#telefone").val(item.telefone).prop('disabled', true);
                $("#nascimento").val(item.data_nascimento).prop('disabled', true);
                $("#cpf").val(item.cpf).prop('disabled', true);
                $("#pai").val(item.nome_pai).prop('disabled', true);
                $("#mae").val(item.nome_mae).prop('disabled', true);
                $("#sangue").val(item.tipo_sanguineo).prop('disabled', true);

                // Endereco
                $("#cep").val(item.cep).prop('disabled', true);
                $("#uf").val(item.estado).prop('disabled', true);
                $("#cidade").val(item.cidade).prop('disabled', true);
                $("#bairro").val(item.bairro).prop('disabled', true);
                $("#rua").val(item.logradouro).prop('disabled', true);
                $("#complemento").val(item.complemento).prop('disabled', true);
                $("#ibge").val(item.ibge).prop('disabled', true);
                if (item.numero_endereco == 'N?o possui' || item.numero_endereco == null || item.numero_endereco == '') {
                    $("#numResidencial").prop('checked', true).prop('disabled', true);
                    $("#numero_residencia").prop('disabled', true);
                } else {
                    $("#numero_residencia").val(item.numero_endereco).prop('disabled', true);
                    $("#numResidencial").prop('disabled', true);
                }

                // Voluntariado
                $("#data_admissao").val(item.data_admissao).prop('disabled', true);
                $("#situacao").val(item.id_situacao).prop('disabled', true);
            });
        });

        function editar_informacoes_pessoais() {
            $("#nomeForm").prop('disabled', false);
            $("#sobrenomeForm").prop('disabled', false);
            $("#radioM").prop('disabled', false);
            $("#radioF").prop('disabled', false);
            $("#telefone").prop('disabled', false);
            $("#nascimento").prop('disabled', false);
            $("#pai").prop('disabled', false);
            $("#mae").prop('disabled', false);
            $("#sangue").prop('disabled', false);
            $("#botaoEditarIP").html('Cancelar');
            $("#botaoSalvarIP").prop('disabled', false);
            $("#botaoEditarIP").removeAttr('onclick');
            $("#botaoEditarIP").attr('onclick', "return cancelar_informacoes_pessoais()");
        }

        function cancelar_informacoes_pessoais() {
            $("#nomeForm").prop('disabled', true);
            $("#sobrenomeForm").prop('disabled', true);
            $("#radioM").prop('disabled', true);
            $("#radioF").prop('disabled', true);
            $("#telefone").prop('disabled', true);
            $("#nascimento").prop('disabled', true);
            $("#pai").prop('disabled', true);
            $("#mae").prop('disabled', true);
            $("#sangue").prop('disabled', true);
            $("#botaoEditarIP").html('Editar');
            $("#botaoSalvarIP").prop('disabled', true);
            $("#botaoEditarIP").removeAttr('onclick');
            $("#botaoEditarIP").attr('onclick', "return editar_informacoes_pessoais()");
        }

        function editar_endereco() {
            $("#cep").prop('disabled', false);
            $("#uf").prop('disabled', false);
            $("#cidade").prop('disabled', false);
            $("#bairro").prop('disabled', false);
            $("#rua").prop('disabled', false);
            $("#complemento").prop('disabled', false);
            $("#ibge").prop('disabled', false);
            $("#numResidencial").prop('disabled', false);
            if ($('#numResidencial').is(':checked')) {
                $("#numero_residencia").prop('disabled', true);
            } else {
                $("#numero_residencia").prop('disabled', false);
            }
            $("#botaoEditarEndereco").html('Cancelar');
            $("#botaoSalvarEndereco").prop('disabled', false);
            $("#botaoEditarEndereco").removeAttr('onclick');
            $("#botaoEditarEndereco").attr('onclick', "return cancelar_endereco()");
        }

        function cancelar_endereco() {
            $("#cep").prop('disabled', true);
            $("#uf").prop('disabled', true);
            $("#cidade").prop('disabled', true);
            $("#bairro").prop('disabled', true);
            $("#rua").prop('disabled', true);
            $("#complemento").prop('disabled', true);
            $("#ibge").prop('disabled', true);
            $("#numResidencial").prop('disabled', true);
            $("#numero_residencia").prop('disabled', true);
            $("#botaoEditarEndereco").html('Editar');
            $("#botaoSalvarEndereco").prop('disabled', true);
            $("#botaoEditarEndereco").removeAttr('onclick');
            $("#botaoEditarEndereco").attr('onclick', "return editar_endereco()");
        }

        function editar_outros() {
            $("#situacao").prop('disabled', false);
            $("#data_admissao").prop('disabled', false);
            $("#botaoEditarOutros").html('Cancelar');
            $("#botaoSalvarOutros").prop('disabled', false);
            $("#botaoEditarOutros").removeAttr('onclick');
            $("#botaoEditarOutros").attr('onclick', "return cancelar_outros()");
        }

        function cancelar_outros() {
            $("#situacao").prop('disabled', true);
            $("#data_admissao").prop('disabled', true);
            $("#botaoEditarOutros").html('Editar');
            $("#botaoSalvarOutros").prop('disabled', true);
            $("#botaoEditarOutros").removeAttr('onclick');
            $("#botaoEditarOutros").attr('onclick', "return editar_outros()");
        }

        function numero_residencial() {
            if ($("#numResidencial").prop('checked')) {
                $("#numero_residencia").val('');
                document.getElementById("numero_residencia").disabled = true;
            } else {
                document.getElementById("numero_residencia").disabled = false;
            }
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
    </script>
</head>

<body>
    <section class="body">
        <div id="header"></div>
        <div class="inner-wrapper">
            <aside id="sidebar-left" class="sidebar-left menuu"></aside>
            <section role="main" class="content-body">
                <header class="page-header">
                    <h2>Perfil Voluntário</h2>
                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li><a href="../home.php"><i class="fa fa-home"></i></a></li>
                            <li><span>Páginas</span></li>
                            <li><span>Perfil</span></li>
                        </ol>
                        <a class="sidebar-right-toggle" style="cursor: default;"></a>
                    </div>
                </header>

                <?php sessionMsg(); ?>
                <div class="row">
                    <div class="col-md-4 col-lg-3">
                        <section class="panel">
                            <div class="panel-body">
                                <div class="thumb-info mb-md">
                                    <?php
                                    $stmtImagem = $pdo->prepare("SELECT pessoa.imagem, pessoa.nome FROM pessoa, voluntario WHERE pessoa.id_pessoa=voluntario.id_pessoa and voluntario.id_voluntario=:idVoluntario");
                                    $stmtImagem->bindValue(':idVoluntario', $idVoluntario, PDO::PARAM_INT);

                                    if (!$stmtImagem->execute()) {
                                        $foto = WWW . "img/semfoto.png";
                                    } else {
                                        $pessoa = $stmtImagem->fetch(PDO::FETCH_ASSOC);
                                        if ($pessoa && $pessoa['imagem'] != null && $pessoa['imagem'] != "") {
                                            $foto = 'data:image;base64,' . $pessoa['imagem'];
                                        } else {
                                            $foto = WWW . "img/semfoto.png";
                                        }
                                    }
                                    echo "<img src='$foto' style='margin-bottom: 15px;' id='imagem' class='rounded img-responsive' alt='Perfil'>";
                                    ?>
                                    <button class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal"><i class="fa fa-camera-retro"></i></button>

                                    <div class="container">
                                        <div class="modal fade" id="myModal" role="dialog">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        <h4 class="modal-title">Adicionar uma Foto</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form class="form-horizontal" method="POST" action="../../controle/control.php" enctype="multipart/form-data">
                                                            <input type="hidden" name="nomeClasse" value="VoluntarioControle">
                                                            <input type="hidden" name="metodo" value="alterarImagem">
                                                            <?= Csrf::inputField() ?>
                                                            <div class="form-group">
                                                                <label class="col-md-4 control-label" for="imgperfil">Carregue nova imagem de perfil:</label>
                                                                <div class="col-md-8">
                                                                    <input type="file" name="imgperfil" size="60" id="imgform" class="form-control">
                                                                </div>
                                                            </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <input type="hidden" name="id_voluntario" value="<?= htmlspecialchars($idVoluntario) ?>">
                                                        <input type="submit" id="formsubmit" value="Alterar imagem" class="btn btn-primary">
                                                    </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="col-md-8 col-lg-8">
                        <div class="tabs">
                            <ul class="nav nav-tabs tabs-primary">
                                <li class="active"><a href="#overview" data-toggle="tab">Informações Pessoais</a></li>
                                <li><a href="#endereco" data-toggle="tab">Endereço</a></li>
                                <li><a href="#outros" data-toggle="tab">Voluntariado</a></li>
                            </ul>
                            <div class="tab-content">

                                <!-- Aba Pessoal -->
                                <div id="overview" class="tab-pane active">
                                    <form class="form-horizontal" method="post" action="../../controle/control.php">
                                        <?= Csrf::inputField() ?>
                                        <input type="hidden" name="nomeClasse" value="VoluntarioControle">
                                        <input type="hidden" name="metodo" value="alterarInfPessoal">
                                        <fieldset>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Nome</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="nome" id="nomeForm" onkeypress="return Onlychars(event)">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Sobrenome</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="sobrenome" id="sobrenomeForm" onkeypress="return Onlychars(event)">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">CPF</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="cpf" id="cpf" disabled>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Sexo</label>
                                                <div class="col-md-8">
                                                    <label><input type="radio" name="gender" id="radioM" value="m" style="margin-top: 10px; margin-left: 15px;"> <i class="fa fa-male" style="font-size: 20px;"></i></label>
                                                    <label><input type="radio" name="gender" id="radioF" value="f" style="margin-top: 10px; margin-left: 15px;"> <i class="fa fa-female" style="font-size: 20px;"></i></label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Telefone</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" maxlength="14" name="telefone" id="telefone" onkeypress="return Onlynumbers(event)" onkeyup="mascara('(##)#####-####',this,event)" required>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Nascimento</label>
                                                <div class="col-md-8">
                                                    <input type="date" class="form-control" name="nascimento" id="nascimento" min="<?= $dataNascimentoMinima ?>" max="<?= $dataNascimentoMaxima ?>" required>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Nome do pai</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="nome_pai" id="pai" onkeypress="return Onlychars(event)">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Nome da mãe</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="nome_mae" id="mae" onkeypress="return Onlychars(event)">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Tipo sanguíneo</label>
                                                <div class="col-md-6">
                                                    <select class="form-control mb-md" name="sangue" id="sangue">
                                                        <option selected disabled>Selecionar</option>
                                                        <option value="A+">A+</option>
                                                        <option value="A-">A-</option>
                                                        <option value="B+">B+</option>
                                                        <option value="B-">B-</option>
                                                        <option value="O+">O+</option>
                                                        <option value="O-">O-</option>
                                                        <option value="AB+">AB+</option>
                                                        <option value="AB-">AB-</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <input type="hidden" name="id_voluntario" value="<?= htmlspecialchars($idVoluntario) ?>">
                                            <button type="button" class="btn btn-primary" id="botaoEditarIP" onclick="return editar_informacoes_pessoais()">Editar</button>
                                            <input type="submit" class="btn btn-primary" disabled="true" value="Salvar" id="botaoSalvarIP">
                                        </fieldset>
                                    </form>
                                </div>

                                <!-- Aba Endereco -->
                                <div id="endereco" class="tab-pane">
                                    <form class="form-horizontal" method="post" action="../../controle/control.php">
                                        <?= Csrf::inputField() ?>
                                        <input type="hidden" name="nomeClasse" value="VoluntarioControle">
                                        <input type="hidden" name="metodo" value="alterarEndereco">
                                        <fieldset>
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
                                                        <label><input type="checkbox" id="numResidencial" onclick="return numero_residencial()"> Sem número</label>
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
                                            <input type="hidden" name="id_voluntario" value="<?= htmlspecialchars($idVoluntario) ?>">
                                            <button type="button" class="btn btn-primary" id="botaoEditarEndereco" onclick="return editar_endereco()">Editar</button>
                                            <input type="submit" class="btn btn-primary" disabled="true" value="Salvar" id="botaoSalvarEndereco">
                                        </fieldset>
                                    </form>
                                </div>

                                <!-- Aba Voluntariado -->
                                <div id="outros" class="tab-pane">
                                    <form class="form-horizontal" method="post" action="../../controle/control.php">
                                        <?= Csrf::inputField() ?>
                                        <input type="hidden" name="nomeClasse" value="VoluntarioControle">
                                        <input type="hidden" name="metodo" value="alterarDetalhes">
                                        <fieldset>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Data Admissão</label>
                                                <div class="col-md-8">
                                                    <input type="date" class="form-control" name="data_admissao" id="data_admissao" required>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Situação</label>
                                                <div class="col-md-6">
                                                    <select class="form-control mb-md" name="situacao" id="situacao" required>
                                                        <?php foreach ($situacao as $row) {
                                                            echo "<option value=" . htmlspecialchars($row['id_situacao']) . ">" . htmlspecialchars($row['situacoes']) . "</option>";
                                                        } ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <input type="hidden" name="id_voluntario" value="<?= htmlspecialchars($idVoluntario) ?>">
                                            <button type="button" class="btn btn-primary" id="botaoEditarOutros" onclick="return editar_outros()">Editar</button>
                                            <input type="submit" class="btn btn-primary" disabled="true" value="Salvar" id="botaoSalvarOutros">
                                        </fieldset>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </section>
</body>
</html>
