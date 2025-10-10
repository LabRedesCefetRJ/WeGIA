<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../dao/Conexao.php";
$pdo = Conexao::connect();

function urlGetParams()
{
    $params = "?";
    $first = true;
    foreach ($_GET as $key => $value) {
        $params .= ($first ? "" : "&") . $key . "=" . $value;
        $first = false;
    }
    return $params;
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

require_once "../permissao/permissao.php";
permissao($_SESSION['id_pessoa'], 12, 7);

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once "../personalizacao_display.php";
require_once "../../dao/Conexao.php";
require_once ROOT . "/controle/FuncionarioControle.php";
$cpf = new FuncionarioControle;
$cpf->listarCPF();

require_once ROOT . "/controle/AtendidoControle.php";
$cpf1 = new AtendidoControle;
$cpf1->listarCPF();

require_once "../geral/msg.php";

$id_dependente = filter_input(INPUT_GET, 'id_dependente', FILTER_SANITIZE_NUMBER_INT);

if ($id_dependente) {
    $stmt = $pdo->prepare("
        SELECT *, ap.parentesco AS parentesco
        FROM atendido_familiares af
        LEFT JOIN pessoa p ON p.id_pessoa = af.pessoa_id_pessoa
        LEFT JOIN atendido_parentesco ap ON ap.idatendido_parentesco = af.atendido_parentesco_idatendido_parentesco
        WHERE af.idatendido_familiares = :id_dependente
    ");

    $stmt->bindParam(':id_dependente', $id_dependente, PDO::PARAM_INT);
    $stmt->execute();

    $dependente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dependente) {
        $stmtNome = $pdo->prepare("
            SELECT p.nome FROM atendido a 
            LEFT JOIN pessoa p ON a.pessoa_id_pessoa = p.id_pessoa 
            WHERE a.idatendido = :idatendido
        ");
        $stmtNome->bindParam(':idatendido', $dependente["atendido_idatendido"], PDO::PARAM_INT);
        $stmtNome->execute();
        $nomeAtendido = $stmtNome->fetch(PDO::FETCH_ASSOC);
        $dependente["nome_atendido"] = $nomeAtendido ? $nomeAtendido["nome"] : null;

        $stmtSobrenome = $pdo->prepare("
            SELECT p.sobrenome FROM atendido a 
            LEFT JOIN pessoa p ON a.pessoa_id_pessoa = p.id_pessoa 
            WHERE a.idatendido = :idatendido
        ");
        $stmtSobrenome->bindParam(':idatendido', $dependente["atendido_idatendido"], PDO::PARAM_INT);
        $stmtSobrenome->execute();
        $sobrenomeAtendido = $stmtSobrenome->fetch(PDO::FETCH_ASSOC);
        $dependente["sobrenome_atendido"] = $sobrenomeAtendido ? $sobrenomeAtendido["sobrenome"] : null;

        $id_pessoa = $dependente["id_pessoa"];
        $idatendido_familiares = $dependente["idatendido_familiares"];
        $JSON_dependente = json_encode($dependente);
    } else {
        echo "Dependente não encontrado.";
        exit(400);
    }
} else {
    echo "ID do dependente inválido ou não fornecido.";
    exit(400);
}


?>
<!doctype html>
<html class="fixed">

<head>
    <!-- Basic -->
    <meta charset="UTF-8">
    <title>Perfil de Familiar</title>
    <meta name="keywords" content="HTML5 Admin Template" />
    <meta name="description" content="Porto Admin - Responsive HTML5 Template">
    <meta name="author" content="okler.net">
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <!-- Web Fonts  -->
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">
    <!-- Theme CSS -->
    <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
    <!-- Skin CSS -->
    <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
    <link rel="stylesheet" href="../../css/profile-theme.css" />
    <!-- Head Libs -->
    <script src="../../Functions/onlyNumbers.js"></script>
    <script src="../../Functions/onlyChars.js"></script>
    <!--script src="../Functions/enviar_dados.js"></script-->
    <script src="../../Functions/mascara.js"></script>
    <script src="../../Functions/lista.js"></script>
    <script src="<?php echo WWW; ?>Functions/testaCPF.js"></script>


    <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

    <!-- Specific Page Vendor CSS -->
    <link rel="stylesheet" href="../../assets/vendor/select2/select2.css" />
    <link rel="stylesheet" href="../../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
    <link rel="stylesheet" type="text/css" href="../../css/profile-theme.css">
    <script src="../../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js">
    </script>
    <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>

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
    <script src="../assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

    <!-- JavaScript Custom -->
    <script src="../geral/post.js"></script>
    <script src="../geral/formulario.js"></script>

    <script>
        var dependente = <?= $JSON_dependente; ?>;
        console.log(dependente);
        var url = "familiar_listar_um.php",
            data = "id_dependente=<?= $id_dependente ?>";
        var formState = [],
            form = {
                set: function(id, dep) {
                    if (id) {
                        if (this[id]) {
                            this[id](dep);
                        } else {
                            console.warn("Id de formulário inválido: " + id);
                        }
                    } else {
                        this.formInfoPessoal(dep);
                        this.formEndereco(dep);
                        this.formDocumentacao(dep);
                    }
                },
                formInfoPessoal: function(dep) {
                    $("#nomeForm").val(dep.nome);
                    $("#sobrenomeForm").val(dep.sobrenome);
                    $("#telefoneForm").val(dep.telefone);
                    $("#nascimentoForm").val(dep.data_nascimento);
                    $("#pai").val(dep.nome_pai);
                    $("#mae").val(dep.nome_mae);
                    if (dep.sexo) {
                        let radio = $("input:radio[name=gender]");
                        radio.filter('[value=' + dep.sexo + ']').prop('checked', true);
                    }
                },
                formEndereco: function(dep) {
                    $("#cep").val(dep.cep);
                    $("#uf").val(dep.estado);
                    $("#cidade").val(dep.cidade);
                    $("#bairro").val(dep.bairro);
                    $("#rua").val(dep.logradouro);
                    $("#complemento").val(dep.complemento);
                    $("#ibge").val(dep.ibge);
                    $("#numero_residencia").val(dep.numero_endereco);
                    if (dep.numero_endereco == 'Não possui' || dep.numero_endereco == null) {
                        $("#numResidencial").prop('checked', true).prop('disabled', true);
                        $("#numero_residencia").prop('disabled', true);
                    } else {
                        $("#numero_residencia").val(dep.numero_endereco).prop('disabled', true);
                        $("#numResidencial").prop('disabled', true);
                    }
                },
                formDocumentacao: function(dep) {
                    $("#rg").val(dep.registro_geral).prop('disabled', true);
                    $("#orgao_emissor").val(dep.orgao_emissor).prop('disabled', true);
                    $("#data_expedicao").val(dep.data_expedicao).prop('disabled', true);
                    // $("#cpf").val(dep.cpf.substr(0, 3) + "." + dep.cpf.substr(3, 3) + "." + dep.cpf.substr(6, 3) + "-" + dep.cpf.substr(9, 2)).prop('disabled', true);
                    $("#cpf").val(dep.cpf);
                },

            };

        function switchButton(idForm) {
            if (!formState[idForm]) {
                $("#botaoEditar_" + idForm).text("Editar").prop("class", "btn btn-primary");
            } else {
                $("#botaoEditar_" + idForm).text("Cancelar").prop("class", "btn btn-danger");
            }
        }

        function switchForm(idForm, setState = null) {
            if (setState !== null) {
                formState[idForm] = !setState;
            }
            if (formState[idForm]) {
                formState[idForm] = false;
                disableForm(idForm);
                form.set(idForm, dependente);
            } else {
                formState[idForm] = true;
                enableForm(idForm);
            }
            switchButton(idForm);
        }

        function getInfoDependente(id = null) {
            if (!id) {
                post(url, data, function(response) {
                    form.set(id, response);
                    disableForm("formInfoPessoal");
                    disableForm("formEndereco");
                    disableForm("formDocumentacao");

                    $.each(formState, function(i, item) {
                        formState[i] = false;
                        switchButton(i);
                    })
                })
            } else {
                post(url, data, function(response) {
                    form.set(id, response);
                    disableForm(id);
                    formState[id] = false;
                    switchButton(id);
                })
            }
        }

        // Funções de validação de datas
        function validarDataExpedicao() {
            const dataNascimento = document.getElementById('nascimentoForm').value;
            const dataExpedicao = document.getElementById('data_expedicao').value;
            
            // Só valida se AMBOS os campos estão preenchidos
            if (dataExpedicao && dataNascimento) {
                const nascimento = new Date(dataNascimento);
                const expedicao = new Date(dataExpedicao);
                
                if (expedicao <= nascimento) {
                    alert('A data de expedição do documento não pode ser anterior ou igual à data de nascimento!');
                    document.getElementById('data_expedicao').value = '';
                    document.getElementById('botaoSalvar_formDocumentacao').disabled = true;
                    return false;
                }
            }
            
            // Se um dos campos estiver vazio, permite a edição
            document.getElementById('botaoSalvar_formDocumentacao').disabled = false;
            return true;
        }

        function validarDataNascimento() {
            const dataNascimento = document.getElementById('nascimentoForm').value;
            const dataExpedicao = document.getElementById('data_expedicao').value;
            
            // Só valida se AMBOS os campos estão preenchidos
            if (dataNascimento && dataExpedicao) {
                const nascimento = new Date(dataNascimento);
                const expedicao = new Date(dataExpedicao);
                
                if (nascimento >= expedicao) {
                    alert('A data de nascimento não pode ser posterior ou igual à data de expedição do documento!');
                    document.getElementById('nascimentoForm').value = '';
                    document.getElementById('botaoSalvar_formInfoPessoal').disabled = true;
                    return false;
                }
            }
            
            // Se um dos campos estiver vazio, permite a edição
            document.getElementById('botaoSalvar_formInfoPessoal').disabled = false;
            return true;
        }

        function submitForm(idForm) {
            var data = getFormPostParams(idForm);
            var url;
            switch (idForm) {
                case "formInfoPessoal":
                    url = "./pessoa/editar_info_pessoal.php";
                    break;
                case "formEndereco":
                    url = "./pessoa/editar_endereco.php";
                    break;
                case "formDocumentacao":
                    url = "./pessoa/editar_documentacao.php";
                    break;
                default:
                    console.warn("Não existe nenhuma URL registrada para o formulário com o seguinte id: " + idForm);
                    return false;
                    break;
            }
            if (!data) {
                window.alert("Preencha todos os campos obrigatórios antes de prosseguir!");
                return false;
            }
            post(url, "id_pessoa=" + dependente.id_pessoa + "&" + data);
            getInfoDependente(idForm);
        }

        var id_dependente = <?= $id_dependente ?? null; ?>;

        $(function() {
            $("#header").load("../header.php");
            $(".menuu").load("../menu.php");
            if (id_dependente) {
                getInfoDependente();
            }

            post("./funcionario/dependente_documento.php", "id_dependente=" + dependente.id_dependente, function(response) {
                listarDocDependente(response)

                $('#datatable-documentos').DataTable({
                    "order": [
                        [0, "asc"]
                    ]
                });
            });

        });
    </script>

    <script type="text/javascript">
        function numero_residencial() {
            if ($("#numResidencial").prop('checked')) {
                $("#numero_residencia").val('');
                document.getElementById("numero_residencia").disabled = true;

            } else {
                document.getElementById("numero_residencia").disabled = false;
            }
        }

        function meu_callback(conteudo) {
            if (!("erro" in conteudo)) {
                //Atualiza os campos com os valores.
                document.getElementById('rua').value = (conteudo.logradouro);
                document.getElementById('bairro').value = (conteudo.bairro);
                document.getElementById('cidade').value = (conteudo.localidade);
                document.getElementById('uf').value = (conteudo.uf);
                document.getElementById('ibge').value = (conteudo.ibge);
            } //end if.
            else {
                //CEP não Encontrado.
                limpa_formulário_cep();
                alert("CEP não encontrado.");
            }
        }

        function pesquisacep(valor) {
            //Nova variável "cep" somente com dígitos.
            var cep = valor.replace(/\D/g, '');

            //Verifica se campo cep possui valor informado.
            if (cep != "") {

                //Expressão regular para validar o CEP.
                var validacep = /^[0-9]{8}$/;

                //Valida o formato do CEP.
                if (validacep.test(cep)) {

                    //Preenche os campos com "..." enquanto consulta webservice.
                    document.getElementById('rua').value = "...";
                    document.getElementById('bairro').value = "...";
                    document.getElementById('cidade').value = "...";
                    document.getElementById('uf').value = "...";
                    document.getElementById('ibge').value = "...";

                    //Cria um elemento javascript.
                    var script = document.createElement('script');

                    //Sincroniza com o callback.
                    script.src = 'https://viacep.com.br/ws/' + cep + '/json/?callback=meu_callback';

                    //Insere script no documento e carrega o conteúdo.
                    document.body.appendChild(script);

                } //end if.
                else {
                    //cep é inválido.
                    limpa_formulário_cep();
                    alert("Formato de CEP inválido.");
                }
            } //end if.
            else {
                //cep sem valor, limpa formulário.
                limpa_formulário_cep();
            }

        };

        function validarCPF(strCPF) {

            if (!testaCPF(strCPF)) {
                $('#cpfInvalido').show();
                document.getElementById("botaoSalvar_formDocumentacao").disabled = true;

            } else {
                $('#cpfInvalido').hide();

                document.getElementById("botaoSalvar_formDocumentacao").disabled = false;
            }

        }

        function gerarSituacao() {
            url = '../dao/exibir_situacao.php';
            $.ajax({
                data: '',
                type: "POST",
                url: url,
                async: true,
                success: function(response) {
                    var situacoes = response;
                    $('#situacao').empty();
                    $('#situacao').append('<option selected disabled>Selecionar</option>');
                    $.each(situacoes, function(i, item) {
                        $('#situacao').append('<option value="' + item.id_situacao + '">' + item.situacoes + '</option>');
                    });
                },
                dataType: 'json'
            });
        }

        function adicionar_situacao() {
            url = '../dao/adicionar_situacao.php';
            var situacao = window.prompt("Cadastre uma Nova Situação:");
            if (!situacao) {
                return
            }
            situacao = situacao.trim();
            if (situacao == '') {
                return
            }

            data = 'situacao=' + situacao;

            $.ajax({
                type: "POST",
                url: url,
                data: data,
                success: function(response) {
                    gerarSituacao();
                },
                dataType: 'text'
            })
        }

        function listarDocDependente(doc) {
            $("#doc-tab").empty();
            $.each(doc, function(i, item) {
                $("#doc-tab")
                    .append($("<tr>")
                        .append($("<td>").text(item.descricao))
                        .append($("<td>").text(item.data))
                        .append($("<td style='display: flex; justify-content: space-evenly;'>")
                            .append($("<a href='./funcionario/dependente_docdependente.php?action=download&id_doc=" + item.id_doc + "' title='Visualizar ou Baixar'><button class='btn btn-primary'><i class='fas fa-download'></i></button></a>"))
                            .append($("<a href='#' onclick='excluir_docdependente(" + item.id_doc + ")' title='Excluir'><button class='btn btn-danger'><i class='fas fa-trash-alt'></i></button></a>"))
                        )
                    )
            });
        }

        function excluir_docdependente(id_doc) {
            post('./funcionario/dependente_docdependente.php', "action=excluir&id_doc=" + id_doc + "&id_dependente=" + dependente.id_dependente, listarDocDependente);
        }

        function gerarDocFuncional(response) {
            var documento = response;
            $('#tipoDocumento').empty();
            $('#tipoDocumento').append('<option selected disabled>Selecionar...</option>');
            $.each(documento, function(i, item) {
                $('#tipoDocumento').append('<option value="' + item.id_docdependente + '">' + item.nome_docdependente + '</option>');
            });
        }

        function adicionarDocDependente() {
            url = './funcionario/dependente_docdependente.php';
            var nome_docdependente = window.prompt("Cadastre um novo tipo de Documento:");
            if (!nome_docdependente) {
                return
            }
            nome_docdependente = nome_docdependente.trim();
            if (nome_docdependente == '') {
                return
            }
            data = "action=adicionar&nome=" + nome_docdependente;
            post(url, data, gerarDocFuncional);
        }
    </script>
    <script>
        function goBack() {
            window.history.back()
        }
    </script>


    <style type="text/css">
        .btn span.fa-check {
            opacity: 0;
        }

        .btn.active span.fa-check {
            opacity: 1;
        }

        #frame {
            width: 100%;
        }

        .obrig {
            color: rgb(255, 0, 0);
        }

        .form-control {
            padding: 0 12px;
        }

        .btn i {
            color: white;
        }
    </style>
</head>

<body>
    <section class="body">
        <div id="header"></div>
        <!-- end: header -->
        <div class="inner-wrapper">
            <!-- start: sidebar -->
            <aside id="sidebar-left" class="sidebar-left menuu"></aside>
            <!-- end: sidebar -->
            <section role="main" class="content-body">
                <header class="page-header">
                    <h2>Familiar</h2>
                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li>
                                <a href="../home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li><span>Páginas</span></li>
                            <li><span>Familiar</span></li>
                        </ol>
                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>
                <!-- start: page -->

                <!-- Mensagem -->
                <?php getMsgSession("msg", "tipo"); ?>


                <div class="panel">
                    <div class="panel-body">
                        <h3>Familiar de: <?= $dependente["nome_atendido"] . " " . $dependente["sobrenome_atendido"]; ?></h3>
                        <div class="tabs">

                            <ul class="nav nav-tabs tabs-primary">
                                <li class="active">
                                    <a href="#overview" data-toggle="tab">Visão Geral</a>
                                </li>
                                <li>
                                    <a href="#documentacao" data-toggle="tab">Documentação</a>
                                </li>
                                <!-- <li>
                                    <a href="#arquivo" data-toggle="tab">Arquivo</a>
                                </li> -->
                                <li>
                                    <a href="#endereco" data-toggle="tab">Endereço</a>
                                </li>
                            </ul>

                            <div class="tab-content">

                                <!-- 
                                    Aba de visão geral

                                -->
                                <div id="overview" class="tab-pane active" role="tabpanel">
                                    <h4>Informações Pessoais</h4><br>
                                    <form action="familiar_editarInfoPessoal.php?id_pessoa=<?php echo $id_pessoa ?>&idatendido_familiares=<?php echo $idatendido_familiares ?>" method='POST'>
                                        <fieldset id="formInfoPessoal">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="nome">Nome</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="nome" id="nomeForm" onkeypress="return Onlychars(event)" required>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="sobrenome">Sobrenome</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="sobrenomeForm" id="sobrenomeForm" onkeypress="return Onlychars(event)">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="profileLastName">Sexo</label>
                                                <div class="col-md-8">
                                                    <label><input type="radio" name="gender" id="radioM" id="M" value="m" style="margin-top: 10px; margin-left: 15px;" onclick="return exibir_reservista()"> <i class="fa fa-male" style="font-size: 20px;"> Masculino</i></label>
                                                    <label><input type="radio" name="gender" id="radioF" id="F" value="f" style="margin-top: 10px; margin-left: 15px;" onclick="return esconder_reservista()"> <i class="fa fa-female" style="font-size: 20px;"> Feminino</i> </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="telefone">Telefone</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" maxlength="14" minlength="14" name="telefone" id="telefoneForm" placeholder="Ex: (22)99999-9999" onkeypress="return Onlynumbers(event)" onkeydown="mascara('(##)#####-####',this,event)">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="nascimento">Nascimento</label>
                                                <div class="col-md-8">
                                                    <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="nascimento" id="nascimentoForm" max="<?php echo date('Y-m-d'); ?>" onchange="validarDataNascimento()">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="pai">Nome do pai</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="nome_pai" id="pai" onkeypress="return Onlychars(event)">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="mae">Nome da mãe</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="nome_mae" id="mae" onkeypress="return Onlychars(event)">
                                                </div>
                                            </div>
                                            <div class="form-group center">
                                                <button type="button" class="btn btn-primary" id="botaoEditar_formInfoPessoal" onclick="switchForm('formInfoPessoal')">Editar</button>
                                                <input type="submit" class="btn btn-primary" disabled="true" value="Salvar" id="botaoSalvar_formInfoPessoal" onclick="submitForm('formInfoPessoal')">
                                            </div>
                                        </fieldset>
                                    </form>
                                </div>


                                <!-- Aba de arquivos do dependente -->

                                <!-- <div id="arquivo" class="tab-pane" role="tabpanel">
                                    <h4>Arquivo</h4>
                                    <fieldset>
                                        <div class="panel-body">
                                            <table class="table table-bordered table-striped mb-none" id="datatable-documentos">
                                                <thead>
                                                    <tr>
                                                        <th>Documento</th>
                                                        <th>Data</th>
                                                        <th>Ação</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="doc-tab">

                                                </tbody>
                                            </table>
                                            <br>
                                            Button trigger modal 
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#docFormModal">
                                                Adicionar arquivo
                                            </button>

                                            <div class="modal fade" id="docFormModal" tabindex="-1" role="dialog" aria-labelledby="docFormModalLabel" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header" style="display: flex;justify-content: space-between;">
                                                            <h5 class="modal-title" id="exampleModalLabel">Adicionar Documento</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <form action='./funcionario/docdependente_upload.php' method='post' enctype='multipart/form-data' id='funcionarioDocForm'>
                                                            <div class="modal-body" style="padding: 15px 40px">
                                                                <div class="form-group" style="display: grid;">
                                                                    <label class="my-1 mr-2" for="id_docdependente">Tipo de Documento</label><br>
                                                                    <div style="display: flex;">
                                                                        <select name="id_docdependente" class="custom-select my-1 mr-sm-2" id="tipoDocumento" required>
                                                                            <option selected disabled>Selecionar...</option>
                                                                            <?php
                                                                            foreach ($pdo->query("SELECT * FROM funcionario_docdependentes ORDER BY nome_docdependente ASC;")->fetchAll(PDO::FETCH_ASSOC) as $item) {
                                                                                echo ("
                                                                                <option value='" . $item["id_docdependentes"] . "' >" . $item["nome_docdependente"] . "</option>
                                                                                ");
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                        <a onclick="adicionarDocDependente()" style="margin: 0 20px;"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="arquivoDocumento">Arquivo do Documento</label>
                                                                    <input name="arquivo" type="file" class="form-control-file" id="arquivoDocumento" accept="png;jpeg;jpg;pdf;docx;doc;odp" required>
                                                                </div>

                                                                <input type="number" name="id_dependente" value="<?php //$_GET['id_dependente']; 
                                                                                                                    ?>" style='display: none;'>

                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                                <input type="submit" value="Enviar" class="btn btn-primary">
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div> -->

                                <!-- Aba de documentação do dependente -->

                                <div id="documentacao" class="tab-pane" role="tabpanel">
                                    <h4>Documentação</h4>
                                    <form action="familiar_editarDoc.php?id_pessoa=<?php echo $id_pessoa ?>&idatendido_familiares=<?php echo $idatendido_familiares ?>" method='POST'>
                                        <fieldset id="formDocumentacao">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="profileCompany">Número do RG</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="rg" id="rg" onkeypress="return Onlynumbers(event)" placeholder="Ex: 22.222.222-2" onkeydown="mascara('##.###.###-#',this,event)">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="profileCompany">Órgão Emissor</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="orgao_emissor" id="orgao_emissor" onkeypress="return Onlychars(event)">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="profileCompany">Data de expedição</label>
                                                <div class="col-md-8">
                                                    <input type="date" class="form-control" maxlength="10" placeholder="dd/mm/aaaa" name="data_expedicao" id="data_expedicao" max="<?php echo date('Y-m-d'); ?>" onchange="validarDataExpedicao()">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="profileCompany">Número do CPF</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" id="cpf" name="cpf" placeholder="Ex: 222.222.222-22" maxlength="14" onblur="validarCPF(this.value)" onkeypress="return Onlynumbers(event)" onkeydown="mascara('###.###.###-##',this,event)" required>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="profileCompany"></label>
                                                <div class="col-md-6">
                                                    <p id="cpfInvalido" style="display: none; color: #b30000">CPF INVÁLIDO!</p>
                                                </div>
                                            </div>
                                            <br />
                                            <div class="form-group center">
                                                <button type="button" class="btn btn-primary" id="botaoEditar_formDocumentacao" onclick="switchForm('formDocumentacao')">Editar</button>
                                                <input type="submit" class="btn btn-primary" disabled="true" value="Salvar" id="botaoSalvar_formDocumentacao" onclick="submitForm('formDocumentacao')">
                                            </div>
                                        </fieldset>
                                    </form>
                                </div>

                                <!-- Aba de endereço do dependente -->

                                <div id="endereco" class="tab-pane" role="tabpanel">
                                    <h4>Endereço</h4>
                                    <form action="familiar_editarEndereco.php?id_pessoa=<?php echo $id_pessoa ?>&idatendido_familiares=<?php echo $idatendido_familiares ?>" method='POST'>
                                        <fieldset id="formEndereco">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="cep">CEP</label>
                                                <div class="col-md-8">
                                                    <input type="text" name="cep" value="" size="10" onblur="pesquisacep(this.value);" class="form-control" id="cep" maxlength="9" placeholder="Ex: 22222-222" onkeydown="return Onlynumbers(event)" onkeyup="mascara('#####-###',this,event)">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="uf">Estado</label>
                                                <div class="col-md-8">
                                                    <input type="text" name="uf" size="60" class="form-control" id="uf">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="cidade">Cidade</label>
                                                <div class="col-md-8">
                                                    <input type="text" size="40" class="form-control" name="cidade" id="cidade">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="bairro">Bairro</label>
                                                <div class="col-md-8">
                                                    <input type="text" name="bairro" size="40" class="form-control" id="bairro">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="rua">Logradouro</label>
                                                <div class="col-md-8">
                                                    <input type="text" name="rua" size="2" class="form-control" id="rua">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="profileCompany">Número residencial</label>
                                                <div class="col-md-4">
                                                    <input type="number" min="0" oninput="this.value = Math.abs(this.value)" class="form-control" name="numero_residencia" id="numero_residencia">
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Não possuo número
                                                        <input type="checkbox" id="numResidencial" name="naoPossuiNumeroResidencial" style="margin-left: 4px" onclick="return numero_residencial()">
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="profileCompany">Complemento</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="complemento" id="complemento" id="profileCompany">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="ibge">IBGE</label>
                                                <div class="col-md-8">
                                                    <input type="text" size="8" name="ibge" class="form-control" id="ibge">
                                                </div>
                                            </div>
                                            <div class="form-group center">
                                                <button type="button" class="btn btn-primary" id="botaoEditar_formEndereco" onclick="switchForm('formEndereco')">Editar</button>
                                                <input type="submit" class="btn btn-primary" disabled="true" value="Salvar" id="botaoSalvar_formEndereco" onclick="submitForm('formEndereco')">
                                            </div>
                                        </fieldset>
                                    </form>
                                </div>

                                <div class="justify-content-between" style="height: 30px;">

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
        </div>

        <!-- end: page -->
    </section>
    </div>
    </section>

    <script>
        function pesquisacep(valor) {
            //Nova variável "cep" somente com dígitos.
            var cep = valor.replace(/\D/g, '');

            //Verifica se campo cep possui valor informado.
            if (cep != "") {

                //Expressão regular para validar o CEP.
                var validacep = /^[0-9]{8}$/;

                //Valida o formato do CEP.
                if (validacep.test(cep)) {

                    //Preenche os campos com "..." enquanto consulta webservice.
                    document.getElementById('rua').value = "...";
                    document.getElementById('bairro').value = "...";
                    document.getElementById('cidade').value = "...";
                    document.getElementById('uf').value = "...";
                    document.getElementById('ibge').value = "...";

                    //Cria um elemento javascript.
                    var script = document.createElement('script');

                    //Sincroniza com o callback.
                    script.src = 'https://viacep.com.br/ws/' + cep + '/json/?callback=meu_callback';

                    //Insere script no documento e carrega o conteúdo.
                    document.body.appendChild(script);

                } //end if.
                else {
                    //cep é inválido.
                    limpa_formulário_cep();
                    alert("Formato de CEP inválido.");
                }
            } //end if.
            else {
                //cep sem valor, limpa formulário.
                limpa_formulário_cep();
            }

        };
    </script>

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
    <script src="../assets/javascripts/tables/examples.datatables.default.js"></script>
    <script src="../assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
    <script src="../assets/javascripts/tables/examples.datatables.tabletools.js"></script>

    <div align="right">
        <iframe src="https://www.wegia.org/software/footer/pessoa.html" width="200" height="60" style="border:none;"></iframe>
    </div>
</body>

</html>