<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_SESSION['usuario'])) {
  header("Location: " . "../../index.php");
  exit(401);
} else {
  session_regenerate_id();
}

//verificação da permissão do usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 12, 7);

include_once '../../classes/Cache.php';

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once "../personalizacao_display.php";

$id = filter_input(INPUT_GET, 'idatendido', FILTER_SANITIZE_NUMBER_INT);

if (!$id || $id < 0) {
  http_response_code(400);
  exit('O id do paciente informado não é válido');
}

$cache = new Cache();
$teste = $cache->read($id);

require_once "../../dao/Conexao.php";
$pdo = Conexao::connect();

$stmtDocFuncional = $pdo->prepare("SELECT * FROM atendido_documentacao a JOIN atendido_docs_atendidos doca ON a.atendido_docs_atendidos_idatendido_docs_atendidos  = doca.idatendido_docs_atendidos WHERE atendido_idatendido =:idAtendido");

$stmtDocFuncional->bindParam(':idAtendido', $id);
$stmtDocFuncional->execute();

$docfuncional = $stmtDocFuncional->fetchAll(PDO::FETCH_ASSOC);
foreach ($docfuncional as $key => $value) {
  $docfuncional[$key]["arquivo"] = gzuncompress($value["arquivo"]);

  //formatar data
  $data = new DateTime($value['data']);
  $docfuncional[$key]['data'] = $data->format('d/m/Y');
}
$docfuncional = json_encode($docfuncional);

$atend = $_SESSION['atendido'];

if (!isset($teste)) {

  header('Location: ../../controle/control.php?metodo=listarUm&nomeClasse=AtendidoControle&nextPage=../html/atendido/Profile_Atendido.php?idatendido=' . $id . '&id=' . $id);
}
$stmtDependente = $pdo->prepare("SELECT
      af.idatendido_familiares AS id_dependente, p.nome AS nome, p.cpf AS cpf, par.parentesco AS parentesco
      FROM atendido_familiares af
      LEFT JOIN atendido a ON a.idatendido = af.atendido_idatendido
      LEFT JOIN pessoa p ON p.id_pessoa = af.pessoa_id_pessoa
      LEFT JOIN atendido_parentesco par ON par.idatendido_parentesco = af.atendido_parentesco_idatendido_parentesco
      WHERE af.atendido_idatendido =:idAtendido");

$stmtDependente->bindParam(':idAtendido', $id);
$stmtDependente->execute();

$dependente = $stmtDependente->fetchAll(PDO::FETCH_ASSOC);
$dependente = json_encode($dependente);
?>

<!doctype html>
<html class="fixed">

<head>
  <!-- Basic -->
  <meta charset="UTF-8">
  <title>Perfil Atendido</title>
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <!-- Web Fonts  -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
  <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">
  <!-- Vendor CSS -->
  <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
  <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
  <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
  <link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
  <link rel="stylesheet" type="text/css" href="../../css/profile-theme.css">
  <script src="../../assets/vendor/jquery/jquery.min.js"></script>
  <script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
  <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
  <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
  <script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
  <script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
  <script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

  <!-- JavaScript Functions -->
  <script src="<?php echo WWW; ?>Functions/testaCPF.js"></script>

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
  <!-- Theme CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
  <!-- Skin CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
  <!-- Theme Custom CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
  <!-- Head Libs -->
  <script src="../../assets/vendor/modernizr/modernizr.js"></script>
  <script src="../../Functions/lista.js"></script>
  <!-- JavaScript Functions -->
  <script src="../../Functions/enviar_dados.js"></script>
  <script src="../../Functions/mascara.js"></script>
  <script src="../../Functions/onlyNumbers.js"></script>
  <script src="../../Functions/onlyChars.js"></script>
  <script>
    function exibir_reservista() {

      $("#reservista1").show();
      $("#reservista2").show();
    }

    function listarDependentes(dependente) {
      $("#dep-tab").empty();
      $.each(dependente, function(i, dependente) {
        $("#dep-tab")
          .append($("<tr>")
            .append($("<td>").text(dependente.nome))
            .append($("<td>").text(dependente.cpf))
            .append($("<td>").text(dependente.parentesco))
            .append($("<td style='display: flex; justify-content: space-evenly;'>")
              .append($("<a href='profile_familiar.php?id_dependente=" + dependente.id_dependente + "' title='Editar'><button class='btn btn-primary'><i class='fas fa-user-edit'></i></button></a>"))
              .append($("<button class='btn btn-danger' onclick='removerDependente(" + dependente.id_dependente + ")'><i class='fas fa-trash-alt'></i></button>"))
            )
          )
      });
    }

    $(function() {
      listarDependentes(<?= $dependente ?>);
    });

    function esconder_reservista() {

      $("#reservista1").hide();
      $("#reservista2").hide();
    }

    function alterardate(data) {
      var date = data.split("-");
      return date[2] + "/" + date[1] + "/" + date[0];
    }

    function excluirimg(id) {
      $("#excluirimg").modal('show');
      $('input[name="id_documento"]').val(id);
    }

    function editimg(id, descricao) {
      $('#teste').val(descricao).prop('selected', true);
      $('input[name="id_documento"]').val(id);
      $("#editimg").modal('show');
    }
    $(function() {
      var interno = <?php echo $_SESSION['atendido'] ?>;
      console.log(interno);
      $.each(interno, function(i, item) {
        if (i = 1) {
          $("#formulario").append($("<input type='hidden' name='idatendido' value='" + item.id + "'>"));
          var cpf = item.cpf;
          $("#nome").text("Nome: " + item.nome + ' ' + item.sobrenome);
          $("#nome").val(item.nome);
          $("#sobrenome").val(item.sobrenome);
          if (item.imagem) {
            $("#imagem").attr("src", "data:image/gif;base64," + item.imagem);
          } else {
            $("#imagem").attr("src", "../../img/semfoto.png");
          }
          if (item.sexo == "m") {
            $("#sexo").html("Sexo: <i class='fa fa-male'></i>  Masculino");
            $("#radioM").prop('checked', true);
          } else if (item.sexo == "f") {
            $("#sexo").html("Sexo: <i class='fa fa-female'>  Feminino");
            $("#radioF").prop('checked', true);
          }

          $("#telefone").text("Telefone:" + item.telefone);
          $("#telefone").val(item.telefone);


          $("#tipoSanguineoSelecionado").text(item.tipo_sanguineo);
          $("#tipoSanguineoSelecionado").val(item.tipo_sanguineo);

          $("#nascimento").text("Data de nascimento: " + alterardate(item.data_nascimento));
          $("#nascimento").val(item.data_nascimento);

          $("#registroGeral").text("Registro geral: " + item.registro_geral);
          $("#registroGeral").val(item.registro_geral);

          if (item.data_expedicao == "0000-00-00") {
            $("#dataExpedicao").text("Data de expedição: Não informado");
          } else {
            $("#dataExpedicao").text("Data de expedição: " + item.data_expedicao);
          }
          $("#dataExpedicao").val(item.data_expedicao);

          $('#orgaoEmissor').text("Orgão emissor: " + item.orgao_emissor);
          $("#orgaoEmissor").val(item.orgao_emissor);
          if (item.cpf.indexOf("ni") != -1) {
            $("#cpf").text("Não informado");
            $("#cpf").val("Não informado");
          } else {
            $("#cpf").text(item.cpf);
            $("#cpf").val(item.cpf);
          }

          $("#inss").text("INSS: " + item.inss);

          $("#loas").text("LOAS: " + item.loas);

          $("#funrural").text("FUNRURAL: " + item.funrural);

          $("#certidao").text("Certidão de nascimento: " + item.certidao);

          $("#casamento").text("Certidão de Casamento: " + item.casamento);

          $("#curatela").text("Curatela: " + item.curatela);

          $("#saf").text("SAF: " + item.saf);

          $("#sus").text("SUS: " + item.sus);

          $("#bpc").text("BPC: " + item.bpc);

          $("#ctps").text("CTPS: " + item.ctps);

          $("#titulo").text("Titulo de eleitor: " + item.titulo);

          $("#observacao").text("Observações: " + item.observacao);
          $("#observacaoform").val(item.observacao);
        }
      })
    });
    $(function() {
      $("#header").load("../header.php");
      $(".menuu").load("../menu.php");
    });
  </script>

  <script type="text/javascript">
    function editar_informacoes_pessoais() {
      $("#nome").prop('disabled', false);
      $("#sobrenome").prop('disabled', false);
      $("#radioM").prop('disabled', false);
      $("#radioF").prop('disabled', false);
      $("#telefone").prop('disabled', false);
      $("#nascimento").prop('disabled', false);
      $("#pai").prop('disabled', false);
      $("#mae").prop('disabled', false);
      $("#tipoSanguineo").prop('disabled', false);
      $("#botaoEditarIP").html('Cancelar');
      $("#botaoSalvarIP").prop('disabled', false);
      $("#botaoEditarIP").removeAttr('onclick');
      $("#botaoEditarIP").attr('onclick', "return cancelar_informacoes_pessoais()");
    }

    function cancelar_informacoes_pessoais() {

      $("#nome").prop('disabled', true);
      $("#sobrenome").prop('disabled', true);
      $("#radioM").prop('disabled', true);
      $("#radioF").prop('disabled', true);
      $("#telefone").prop('disabled', true);
      $("#nascimento").prop('disabled', true);
      $("#pai").prop('disabled', true);
      $("#mae").prop('disabled', true);
      $("#tipoSanguineo").prop('disabled', true);

      $("#botaoEditarIP").html('Editar');
      $("#botaoSalvarIP").prop('disabled', true);
      $("#botaoEditarIP").removeAttr('onclick');
      $("#botaoEditarIP").attr('onclick', "return editar_informacoes_pessoais()");

    }

    function editar_documentacao() {

      $("#registroGeral").prop('disabled', false);
      $("#orgaoEmissor").prop('disabled', false);
      $("#dataExpedicao").prop('disabled', false);
      $("#cpf").prop('disabled', false);
      $("#data_admissao").prop('disabled', false);

      $("#botaoEditarDocumentacao").html('Cancelar');
      $("#botaoSalvarDocumentacao").prop('disabled', false);
      $("#botaoEditarDocumentacao").removeAttr('onclick');
      $("#botaoEditarDocumentacao").attr('onclick', "return cancelar_documentacao()");

    }

    function cancelar_documentacao() {

      $("#registroGeral").prop('disabled', true);
      $("#orgaoEmissor").prop('disabled', true);
      $("#dataExpedicao").prop('disabled', true);
      $("#cpf").prop('disabled', true);
      $("#data_admissao").prop('disabled', true);

      $("#botaoEditarDocumentacao").html('Editar');
      $("#botaoSalvarDocumentacao").prop('disabled', true);
      $("#botaoEditarDocumentacao").removeAttr('onclick');
      $("#botaoEditarDocumentacao").attr('onclick', "return editar_documentacao()");

    }
    $(function() {
      $("#header").load("../header.php");
      $(".menuu").load("../menu.php");
      $("#cep").prop('disabled', true);
      $("#estado").prop('disabled', true);
      $("#cidade").prop('disabled', true);
      $("#bairro").prop('disabled', true);
      $("#rua").prop('disabled', true);
      $("#numero_residencia").prop('disabled', true);
      $("#complemento").prop('disabled', true);
      $("#ibge").prop('disabled', true);
      var endereco = <?= $atend ?>;
      if (endereco == "") {
        $("#metodo").val("incluirEndereco");
      } else {
        $("#metodo").val("alterarEndereco");
      }
      $.each(endereco, function(i, item) {
        //console.log(endereco);
        console.log("estado=" + item.estado);
        $("#nome").val(item.nome).prop('disabled', true);
        $("#cep").val(item.cep).prop('disabled', true);
        $("#estado").val(item.estado).prop('disabled', true);
        $("#cidade").val(item.cidade).prop('disabled', true);
        $("#bairro").val(item.bairro).prop('disabled', true);
        $("#rua").val(item.logradouro).prop('disabled', true);
        $("#numero_residencia").val(item.numero_endereco).prop('disabled', true);
        $("#complemento").val(item.complemento).prop('disabled', true);
        $("#ibge").val(item.ibge).prop('disabled', true);
        if (item.numero_endereco == 'Sem número' || item.numero_endereco == null) {
          $("#numResidencial").prop('checked', true);
        }
      });
    });

    function editar_endereco() {

      $("#nome").prop('disabled', false);
      $("#cep").prop('disabled', false);
      $("#estado").prop('disabled', false);
      $("#cidade").prop('disabled', false);
      $("#bairro").prop('disabled', false);
      $("#rua").prop('disabled', false);
      $("#complemento").prop('disabled', false);
      $("#ibge").prop('disabled', false);
      $("#numResidencial").prop('disabled', false);
      $("#numero_residencia").prop('disabled', false)
      $("#botaoEditarEndereco").html('Cancelar');
      $("#botaoSalvarEndereco").prop('disabled', false);
      $("#botaoEditarEndereco").removeAttr('onclick');
      $("#botaoEditarEndereco").attr('onclick', "return cancelar_endereco()");
    }

    function numero_residencial() {
      if ($("#numResidencial").prop('checked')) {
        document.getElementById("numero_residencia").readOnly = true;
      } else {
        document.getElementById("numero_residencia").readOnly = false;
      }
    }

    function cancelar_endereco() {
      $("#cep").prop('disabled', true);
      $("#estado").prop('disabled', true);
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

    function limpa_formulário_cep() {
      //Limpa valores do formulário de cep.
      document.getElementById('rua').value = ("");
      document.getElementById('bairro').value = ("");
      document.getElementById('cidade').value = ("");
      document.getElementById('estado').value = ("");
      document.getElementById('ibge').value = ("");
    }

    function meu_callback(conteudo) {
      if (!("erro" in conteudo)) {
        //Atualiza os campos com os valores.
        document.getElementById('rua').value = (conteudo.logradouro);
        document.getElementById('bairro').value = (conteudo.bairro);
        document.getElementById('cidade').value = (conteudo.localidade);
        document.getElementById('estado').value = (conteudo.uf);
        document.getElementById('ibge').value = (conteudo.ibge);
      } else {
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
          document.getElementById('estado').value = "...";
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

    function gerarDocFuncional() {
      url = '../../funcionario/documento_listar.php';
      $.ajax({
        data: '',
        type: "POST",
        url: url,
        async: true,
        success: function(response) {
          var documento = response;
          $('#tipoDocumento').empty();
          $('#tipoDocumento').append('<option selected disabled>Selecionar...</option>');
          $.each(documento, function(i, item) {
            $('#tipoDocumento').append('<option value="' + item.id_docfuncional + '">' + item.nome_docfuncional + '</option>');
          });
        },
        dataType: 'json'
      });
    }

    function adicionarDocFuncional() {
      url = '././funcionario/documento_adicionar.php';
      var nome_docfuncional = window.prompt("Cadastre um novo tipo de Documento:");
      if (!nome_docfuncional) {
        return
      }
      nome_docfuncional = nome_docfuncional.trim();
      if (nome_docfuncional == '') {
        return
      }
      data = 'nome_docfuncional=' + nome_docfuncional;
      $.ajax({
        type: "POST",
        url: url,
        data: data,
        success: function(response) {
          gerarDocFuncional();
        },
        dataType: 'text'
      })
    }

    $(function() {
      var docfuncional = <?= $docfuncional ?>;
      //console.log(docfuncional);
      $.each(docfuncional, function(i, item) {
        $("#doc-tab")
          .append($("<tr>")
            .append($("<td>").text(item.descricao))
            .append($("<td>").text(item.data))
            .append($("<td style='display: flex; justify-content: space-evenly;'>")
              .append($("<a href='documento_download.php?id_doc=" + item.idatendido_documentacao + "' target='_tab' title='Visualizar ou Baixar'><button class='btn btn-primary'><i class='fas fa-download'></i></button></a>"))
              .append($("<a onclick='removerFuncionarioDocs(" + item.idatendido_documentacao + ")' href='#' title='Excluir'><button class='btn btn-danger'><i class='fas fa-trash-alt'></i></button></a>"))
            )
          )
      });
    });

    function listarFunDocs(docfuncional) {
      $("#doc-tab").empty();
      $.each(docfuncional, function(i, item) {
        $("#doc-tab")
          .append($("<tr>")
            .append($("<td>").text(item.descricao))
            .append($("<td>").text(item.data))
            .append($("<td style='display: flex; justify-content: space-evenly;'>")
              .append($("<a href='documento_download.php?id_doc=" + item.idatendido_documentacao + " '  target='_' title='Visualizar ou Baixar' ><button class='btn btn-primary'><i class='fas fa-download'></i></button></a>"))
              .append($("<a onclick='removerFuncionarioDocs(" + item.idatendido_documentacao + ")' href='#' title='Excluir'><button class='btn btn-danger'><i class='fas fa-trash-alt'></i></button></a>"))
            )
          )
      });
    }

    $(function() {
      $('#datatable-docfuncional').DataTable({
        "order": [
          [0, "asc"]
        ]
      });
    });

    function validarDataExpedicao() {
      const dataNascimento = document.getElementById('nascimento').value;
      const dataExpedicao = document.getElementById('dataExpedicao').value;

      if (dataExpedicao && dataNascimento) {
        const nascimento = new Date(dataNascimento);
        const expedicao = new Date(dataExpedicao);

        if (expedicao < nascimento) {
          alert('Erro: A data de expedição do documento não pode ser anterior à data de nascimento!');
          document.getElementById('dataExpedicao').value = '';
          document.getElementById('botaoSalvarDocumentacao').disabled = true;
          return false;
        }
      }

      document.getElementById('botaoSalvarDocumentacao').disabled = false;
      return true;
    }

    function validarDataNascimento() {
      const dataNascimento = document.getElementById('nascimento').value;
      const dataExpedicao = document.getElementById('dataExpedicao').value;

      if (dataNascimento && dataExpedicao) {
        const nascimento = new Date(dataNascimento);
        const expedicao = new Date(dataExpedicao);

        if (nascimento > expedicao) {
          alert('Erro: A data de nascimento não pode ser posterior à data de expedição do documento!');
          document.getElementById('nascimento').value = '';
          document.getElementById('botaoSalvarIP').disabled = true;
          return false;
        }
      }

      document.getElementById('botaoSalvarIP').disabled = false;
      return true;
    }
  </script>

  <script>
    function clicar(id) {
      window.location.href = "listar_ocorrencias.php?id=" + id;
    }
  </script>

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
          <h2>Perfil</h2>
          <div class="right-wrapper pull-right">
            <ol class="breadcrumbs">
              <li>
                <a href="../index.php">
                  <i class="fa fa-home"></i>
                </a>
              </li>
              <li><span>Páginas</span></li>
              <li><span>Perfil</span></li>
            </ol>
            <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
          </div>
        </header>
        <!-- start: page -->
        <div class="row">
          <div class="col-md-4 col-lg-3">
            <section class="panel">
              <div class="panel-body">
                <?php
                $pdo = Conexao::connect();
                $resultado = $pdo->query("SELECT COUNT(*) FROM endereco_instituicao;")->fetchColumn();
                if (!$resultado) {
                  echo "<div class='alert alert-warning' id='cadastro_instituicao' style='font-size: 15px;'><i class='fas fa-check mr-md'></i>O endereço da instituição não está cadastrado no sistema<br><a href='../personalizacao.php'>Cadastrar endereço da instituição</a></div>";
                }
                ?>

                <div class="thumb-info mb-md">
                  <img id="imagem" style="margin-bottom: 15px;" alt="">
                  <i class="fas fa-camera-retro btn btn-info btn-lg" data-toggle="modal" data-target="#myModal"></i>
                  <div class="container">
                    <div class="modal fade" id="myModal" role="dialog">
                      <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Adicionar uma Foto</h4>
                          </div>
                          <div class="modal-body">
                            <form class="form-horizontal" method="POST" action="../../controle/control.php" enctype="multipart/form-data">
                              <input type="hidden" name="nomeClasse" value="AtendidoControle">
                              <input type="hidden" name="metodo" value="alterarImagem">
                              <div class="form-group">
                                <label class="col-md-4 control-label" for="imgperfil">Carregue nova imagem de perfil:</label>
                                <div class="col-md-8">
                                  <input type="file" name="imgperfil" size="60" id="imgform" class="form-control">
                                </div>
                              </div>
                          </div>
                          <div class="modal-footer">
                            <input type="hidden" name="idatendido" value="<?= $id ?>">
                            <input type="submit" id="formsubmit" value="Alterar imagem">
                          </div>
                        </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="widget-toggle-expand mb-md">
                  <div class="widget-header">
                    <div class="widget-content-expanded">
                      <ul class="simple-todo-list">
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </section>
          </div>
          <div class="col-md-8 col-lg-6">
            <div class="tabs">
              <ul class="nav nav-tabs tabs-primary">
                <li class="active">
                  <a href="#overview" data-toggle="tab">Informações Pessoais</a>
                </li>
                <li>
                  <a href="#endereco" data-toggle="tab">Endereço</a>
                </li>
                <li>
                  <a href="#arquivo" data-toggle="tab">Arquivos</a>
                </li>
                <li>
                  <a href="#familiares" data-toggle="tab">Composição Familiar</a>
                </li>
                <li>
                  <a href="#ocorrencias" data-toggle="tab">Ocorrências</a>
                </li>
              </ul>
              <div class="tab-content">
                <div class="tab-content">
                  <div id="overview" class="tab-pane active">
                    <form class="form-horizontal" method="post" action="../../controle/control.php">
                      <input type="hidden" name="nomeClasse" value="AtendidoControle">
                      <input type="hidden" name="metodo" value="alterarInfPessoal">
                      <h4 class="mb-xlg">Informações Pessoais</h4>
                      <fieldset>
                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileFirstName">Nome</label>
                          <div class="col-md-8">
                            <input type="text" class="form-control" disabled name="nome" id="nome" onkeypress="return Onlychars(event)">
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileFirstName">Sobrenome</label>
                          <div class="col-md-8">
                            <input type="text" class="form-control" disabled name="sobrenome" id="sobrenome" onkeypress="return Onlychars(event)">
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileLastName">Sexo</label>
                          <div class="col-md-8">
                            <label><input type="radio" name="sexo" id="radioM" id="M" disabled value="m" style="margin-top: 10px; margin-left: 15px;" onclick="return exibir_reservista()"> <i class="fa fa-male" style="font-size: 20px;"> </i></label>
                            <label><input type="radio" name="sexo" id="radioF" disabled id="F" value="f" style="margin-top: 10px; margin-left: 15px;" onclick="return esconder_reservista()"> <i class="fa fa-female" style="font-size: 20px;"> </i> </label>
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileCompany">Telefone</label>
                          <div class="col-md-8">
                            <input type="text" class="form-control" maxlength="14" minlength="14" name="telefone" id="telefone" disabled placeholder="Ex: (22)99999-9999" onkeypress="return Onlynumbers(event)" onkeyup="mascara('(##)#####-####',this,event)">
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileCompany">Nascimento</label>
                          <div class="col-md-8">
                            <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="nascimento" disabled id="nascimento" max="<?php echo date('Y-m-d'); ?>" onchange="validarDataNascimento()">
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label" for="inputSuccess">Tipo sanguíneo</label>
                          <div class="col-md-6">
                            <select class="form-control input-lg mb-md" name="tipoSanguineo" id="tipoSanguineo" disabled>
                              <option selected id="tipoSanguineoSelecionado">Selecionar</option>
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
                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileCompany">Número do CPF</label>
                          <div class="col-md-6">
                            <input type="text" class="form-control" id="cpf" name="cpf" disabled placeholder="Ex: 222.222.222-22" maxlength="14" onblur="validarCPF(this.value)" onkeypress="return Onlynumbers(event)" onkeyup="mascara('###.###.###-##',this,event)" readonly>
                          </div>
                        </div>
                        <input type="hidden" name="idatendido" value=<?= $id ?>>
                        <button type="button" class="btn btn-primary" id="botaoEditarIP" onclick="return editar_informacoes_pessoais()">Editar</button>
                        <input type="submit" class="btn btn-primary" disabled="true" value="Salvar" id="botaoSalvarIP">
                    </form>

                    <br />
                    <!--Exclusao -->

                    <div class="panel-footer">
                      <div class="row">
                        <div class="col-md-9 col-md-offset-3">
                          <?php
                          $atend = json_decode($atend)[0];
                          if ($atend->status == 1):
                          ?>
                            <button id="excluir" type="button" class="btn btn-danger" data-toggle="modal" data-target="#exclusao">Desativar</button>
                          <?php
                          elseif ($atend->status == 2):
                          ?>
                            <form action="../../controle/control.php?metodo=alterarStatus&nomeClasse=AtendidoControle" method="post">
                              <input type="hidden" name="idatendido" value=<?= $id ?>>
                              <input type="hidden" name="operacao" value='ativar'>
                              <button class="btn btn-primary" type="submit">Ativar</button>
                            </form>
                          <?php
                          endif;
                          ?>
                        </div>
                      </div>
                    </div>
                    <div class="modal fade" id="exclusao" role="dialog">
                      <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" aba-dismiss="modal">×</button>
                            <h3>Desativar um atendido</h3>
                          </div>
                          <div class="modal-body">
                            <p>Tem certeza que deseja desativar esse atendido?</p>
                            <form action="../../controle/control.php?metodo=alterarStatus&nomeClasse=AtendidoControle" method="post" class="d-flex">
                              <input type="hidden" name="idatendido" value=<?= $id ?>>
                              <input type="hidden" name="operacao" value='desativar'>
                              <button class="btn btn-primary me-2" type="submit">Desativar</button>
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            </form>
                          </div>

                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Aba  de  Endereço -->

                  <div id="endereco" class="tab-pane">
                    <section class="panel">
                      <header class="panel-heading">
                        <div class="panel-actions">
                          <a href="#" class="fa fa-caret-down"></a>
                        </div>

                        <h2 class="panel-title">Endereço</h2>
                      </header>
                      <div class="panel-body">
                        <!--Endereço-->
                        <hr class="dotted short">
                        <form id="endereco" class="form-horizontal" method="post" action="../../controle/control.php">
                          <input type="hidden" name="nomeClasse" value="AtendidoControle">
                          <input type="hidden" name="metodo" value="alterarEndereco">
                          <div class="form-group">
                            <label class="col-md-3 control-label" for="cep">CEP</label>
                            <div class="col-md-8">
                              <input type="text" name="cep" value="" size="10" onblur="pesquisacep(this.value);" class="form-control" id="cep" maxlength="9" placeholder="Ex: 22222-222" onkeydown="return Onlynumbers(event)" onkeyup="mascara('#####-###',this,event)">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-md-3 control-label" for="estado">Estado</label>
                            <div class="col-md-8">
                              <input type="text" name="estado" size="60" class="form-control" id="estado">
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
                            <input type="hidden" name="idatendido" value=<?= $id ?>>
                            <button type="button" class="btn btn-primary" id="botaoEditarEndereco" onclick="return editar_endereco()">Editar</button>
                            <input id="botaoSalvarEndereco" type="submit" class="btn btn-primary" disabled="true" value="Salvar">
                        </form>
                      </div>
                    </section>
                  </div>
                  <!-- Composição Familiar -->
                  <div id="familiares" class="tab-pane">
                    <section class="panel">
                      <header class="panel-heading">
                        <div class="panel-actions">
                          <a href="#" class="fa fa-caret-down"></a>
                        </div>
                        <h2 class="panel-title">Composição Familiar</h2>
                      </header>
                      <div class="panel-body">
                        <table class="table table-bordered table-striped mb-none" id="datatable-dependente">
                          <thead>
                            <tr>
                              <th>Nome</th>
                              <th>CPF</th>
                              <th>Parentesco</th>
                              <th>Ação</th>
                            </tr>
                          </thead>
                          <tbody id="dep-tab">

                          </tbody>
                        </table>
                        <br>
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#depFormModal">
                          Adicionar Membro
                        </button>
                      </div>

                      <!-- Modal Form Composição Familiar -->
                      <div class="modal fade" id="depFormModal" tabindex="-1" role="dialog" aria-labelledby="depFormModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="modal-header" style="display: flex;justify-content: space-between;">
                              <h5 class="modal-title" id="exampleModalLabel">Adicionar Membro à Composição Familiar</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <form action='familiar_cadastrar.php' method='post' id='funcionarioDepForm'>
                              <div class="modal-body" style="padding: 15px 40px">
                                <div class="form-group" style="display: grid;">
                                  <div class="form-group">
                                    <label class="col-md-3 control-label" for="cpf">CPF<sup class="obrig">*</sup></label>
                                    <div class="col-md-6">
                                      <input type="text" class="form-control" id="cpf" name="cpf" placeholder="Ex: 222.222.222-22" maxlength="14" onblur="validarCPF(this.value)" onkeypress="return Onlynumbers(event)" onkeyup="mascara('###.###.###-##',this,event)" required>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="col-md-3 control-label" for="profileCompany"></label>
                                    <div class="col-md-6">
                                      <p id="cpfFamiliarInvalido" style="display: none; color: #b30000">CPF INVÁLIDO!</p>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="col-md-3 control-label" for="parentesco">Parentesco<sup class="obrig">*</sup></label>
                                    <div class="col-md-6" style="display: flex;">
                                      <select name="id_parentesco" id="parentesco">
                                        <option selected disabled>Selecionar...</option>
                                        <?php
                                        foreach ($pdo->query("SELECT * FROM atendido_parentesco ORDER BY parentesco ASC;")->fetchAll(PDO::FETCH_ASSOC) as $item) {
                                          echo ("
                                            <option value='" . $item["idatendido_parentesco"] . "' >" . htmlspecialchars($item["parentesco"]) . "</option>
                                            ");
                                        }
                                        ?>
                                      </select>
                                      <a onclick="adicionarParentesco()" style="margin: 0 20px;"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                                    </div>
                                  </div>
                                  <input type="hidden" name="idatendido" value="<?= $id ?>" readonly>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <input type="submit" id="cadastrarFamiliar" value="Enviar" class="btn btn-primary">
                                  </div>
                                </div>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </section>
                  </div>

                  <!-- Aba de arquivo -->
                  <div id="arquivo" class="tab-pane">
                    <section class="panel">
                      <header class="panel-heading">
                        <div class="panel-actions">
                          <a href="#" class="fa fa-caret-down"></a>
                        </div>
                        <h2 class="panel-title">Arquivos</h2>
                      </header>
                      <div class="panel-body">
                        <table class="table table-bordered table-striped mb-none">
                          <thead>
                            <tr>
                              <th>Arquivo</th>
                              <th>Data</th>
                              <th>Ação</th>
                            </tr>
                          </thead>
                          <tbody id="doc-tab">
                          </tbody>
                        </table>
                        <br>
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#docFormModal">
                          Adicionar
                        </button>
                        <!-- Modal Form Documentos -->
                        <div class="modal fade" id="docFormModal" tabindex="-1" role="dialog" aria-labelledby="docFormModalLabel" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header" style="display: flex;justify-content: space-between;">
                                <h5 class="modal-title" id="exampleModalLabel">Adicionar Arquivo</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <form action='documento_upload.php' method='post' enctype='multipart/form-data' id='funcionarioDocForm'>
                                <div class="modal-body" style="padding: 15px 40px">
                                  <div class="form-group" style="display: grid;">
                                    <label class="my-1 mr-2" for="tipoDocumento">Tipo de Arquivo</label><br>
                                    <div style="display: flex;">
                                      <select name="id_docfuncional" class="custom-select my-1 mr-sm-2" id="tipoDocumento" required>
                                        <option selected disabled>Selecionar...</option>
                                        <?php
                                        foreach ($pdo->query("SELECT * FROM atendido_docs_atendidos ORDER BY descricao ASC")->fetchAll(PDO::FETCH_ASSOC) as $item) {
                                          echo ("
                                          <option value='" . $item["idatendido_docs_atendidos"] . "' >" . htmlspecialchars($item["descricao"]) . "</option>");
                                        }
                                        ?>

                                      </select>

                                      <a onclick="adicionar_tipo()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label for="arquivoDocumento">Arquivo</label>
                                    <input name="arquivo" type="file" class="form-control-file" id="id_documento" accept="png;jpeg;jpg;pdf;docx;doc;odp" required>
                                  </div>
                                  <input type="number" name="idatendido" value="<?= $id ?>" style='display: none;'>
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                  <input type="submit" value="Enviar" class="btn btn-primary" id="btn-atendido" onclick="verificaTipo(event)">
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                    </section>
                  </div>

                  <div id="ocorrencias" class="tab-pane">
                    <section class="panel">
                      <header class="panel-heading">
                        <div class="panel-actions">
                          <a href="#" class="fa fa-caret-down"></a>
                        </div>
                        <h2 class="panel-title">Ocorrências</h2>
                      </header>
                      <div class="panel-body">
                        <table class="table table-bordered table-striped mb-none">
                          <thead>
                            <tr>
                              <th>Data</th>
                              <th>Informações</th>
                            </tr>
                          </thead>
                          <tbody id="doc-tabl">
                            <?php
                            $stmt = $pdo->prepare("SELECT data, descricao, idatendido_ocorrencias FROM atendido_ocorrencia WHERE atendido_idatendido = :id ORDER BY data DESC");
                            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                            $stmt->execute();

                            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($resultados as $item) {
                              echo "<tr style='cursor: pointer;' onclick='clicar($item[idatendido_ocorrencias])'>";
                              $data = explode('-', $item["data"]);
                              echo "<td>" . $data[2] . "/" . $data[1] . "/" . $data[0] . "</td>";
                              echo "<td>" . $item["descricao"] . "</td>";
                              echo "<tr>";
                            }

                            ?>
                          </tbody>
                        </table>
                        <br>
                        <div>
                          <a href="cadastro_ocorrencia.php?atendido_id=<?= $id ?>" class="btn btn-primary">Cadastrar Ocorrência</a>
                        </div>

                    </section>
                  </div>

                  <!-- end: page -->
      </section>
      <div align="right">
        <iframe src="https://www.wegia.org/software/footer/pessoa.html" width="200" height="60" style="border:none;"></iframe>
      </div>
  </section>

  <!-- Vendor -->
  <script src="../../assets/vendor/select2/select2.js"></script>
  <script src="../geral/post.js"></script>
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
  <script src="../../assets/javascripts/tables/examples.datatables.default.js"></script>
  <script src="../../assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
  <script src="../../assets/javascripts/tables/examples.datatables.tabletools.js"></script>
  <div class="modal fade" id="excluirimg" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
          <h3>Excluir um Documento</h3>
        </div>
        <div class="modal-body">
          <p> Tem certeza que deseja excluir a imagem desse documento? Essa ação não poderá ser desfeita! </p>
          <form action="../../controle/control.php" method="GET">
            <input type="hidden" name="id_documento" id="excluirdoc">
            <input type="hidden" name="nomeClasse" value="DocumentoControle">
            <input type="hidden" name="metodo" value="excluir">
            <input type="hidden" name="id" value="">
            <input type="submit" value="Confirmar" class="btn btn-success">
            <button button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <iv class="modal fade" id="editimg" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">X</button>
          <h3>Alterar um Documento</h3>
        </div>
        <div class="modal-body">
          <p> Selecione o benefício referente a nova imagem</p>
          <form action="../../controle/control.php" method="POST" enctype="multipart/form-data">
            <select name="descricao" id="teste">
              <option value="Certidão de Nascimento">Certidão de Nascimento</option>
              <option value="Certidão de Casamento">Certidão de Casamento</option>
              <option value="Curatela">Curatela</option>
              <option value="INSS">INSS</option>
              <option value="LOAS">LOAS</option>
              <option value="FUNRURAL">FUNRURAL</option>
              <option value="Título de Eleitor">Título de Eleitor</option>
              <option value="CTPS">CTPS</option>
              <option value="SAF">SAF</option>
              <option value="SUS">SUS</option>
              <option value="BPC">BPC</option>
              <option value="CPF">CPF</option>
              <option value="Registro Geral">RG</option>
            </select><br />

            <p> Selecione a nova imagem</p>
            <div class="col-md-12">
              <input type="file" name="doc" size="60" class="form-control">
            </div><br />
            <input type="hidden" name="id_documento" id="id_documento">
            <input type="hidden" name="id" value="">
            <input type="hidden" name="nomeClasse" value="DocumentoControle">
            <input type="hidden" name="metodo" value="alterar">
            <input type="submit" value="Confirmar" class="btn btn-success">
            <button button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          </form>
        </div>
      </div>
    </div>
    </div>
    <script>
      function funcao1() {
        var cpfs = [{
          "cpf": "admin",
          "id": "1"
        }];
        var cpf_atendido = $("#cpf").val();
        var cpf_atendido_correto = cpf_atendido.replace(".", "").replace(".", "").replace(".", "").replace("-", "");
        var apoio = 0;
        var cpfs1 = [];
        $.each(cpfs, function(i, item) {
          if (item.cpf == cpf_atendido_correto) {
            alert("Alteração não realizada! O CPF informado já está cadastrado no sistema");
            apoio = 1;
          }
        });
        $.each(cpfs1, function(i, item) {
          if (item.cpf == cpf_atendido_correto) {
            alert("Cadastro não realizado! O CPF informado já está cadastrado no sistema");
            apoio = 1;
          }
        });
        if (apoio == 0) {
          alert("Cadastrado com sucesso!");
        }
      }

      function funcao3() {
        var idatend = <?= json_encode($id) ?>;
        var cpfs = <?= json_encode(isset($_SESSION['cpf_atendido']) ? $_SESSION['cpf_atendido'] : []) ?>;
        var cpf_atendido = $("#cpf").val();
        var cpf_atendido_correto = cpf_atendido.replace(".", "").replace(".", "").replace(".", "").replace("-", "");
        var apoio = 0;
        var cpfs1 = <?= json_encode(isset($_SESSION['cpf_atendido']) ? $_SESSION['cpf_atendido'] : []) ?>;
        $.each(cpfs, function(i, item) {
          if (item.cpf == cpf_atendido_correto && item.id != idatend) {
            alert("Alteração não realizada! O CPF informado já está cadastrado no sistema");
            apoio = 1;
          }
        });
        $.each(cpfs1, function(i, item) {
          if (item.cpf == cpf_atendido_correto) {
            alert("Cadastro não realizado! O CPF informado já está cadastrado no sistema");
            apoio = 1;
          }
        });
        if (apoio == 0) {
          alert("Editado com sucesso!");
        }
      }
    </script>

    <script>
      function removerFuncionarioDocs(id_doc) {
        if (!window.confirm("Tem certeza que deseja remover esse documento?")) {
          return false;
        }
        let url = "documento_excluir.php?id_doc=" + id_doc + "&idatendido=<?= $id ?>";
        let data = "";
        post(url, data, listarFunDocs);
      }
    </script>
    <script>
      function removerDependente(id_dep) {
        let url = "familiar_remover.php";
        let data = "idatendido=<?= $id ?>&id_dependente=" + id_dep;
        post(url, data, verificaSucesso);
      }
    </script>
    <script>
      function verificaSucesso(response) {
        console.log(response);
        if (response.errorInfo) {
          if (response.errorInfo[1] == 1451) {
            window.alert("O dependente possui documentos cadastrados em seu nome. Retire-os do bando de dados antes de remover o dependente.");
          } else {
            window.alert("Houve um erro ao retirar o dependente. Verifique se todos os documentos referentes a ele foram removidos antes de prosseguir.");
          }
          return false;
        }
        listarDependentes(response);
      }

      function gerarTipo() {
        url = '../../dao/exibir_tipo_docs_atendido.php';
        $.ajax({
          data: '',
          type: "POST",
          url: url,
          async: true,
          success: function(response) {
            var descricao = response;
            $('#tipoDocumento').empty();
            $('#tipoDocumento').append('<option selected disabled>Selecionar</option>');
            $.each(descricao, function(i, item) {
              $('#tipoDocumento').append('<option value="' + item.idatendido_docs_atendidos + '">' + item.descricao + '</option>');
            });
          },
          dataType: 'json'
        });
      }

      function adicionar_tipo() {
        url = '../../dao/adicionar_tipo_docs_atendido.php';
        var tipo = window.prompt("Cadastre um Novo Tipo:");
        if (!tipo) {
          return
        }
        tipo = tipo.trim();
        if (tipo == '') {
          return
        }

        data = 'tipo=' + tipo;

        console.log(data);
        $.ajax({
          type: "POST",
          url: url,
          data: data,
          success: function(response) {
            gerarTipo();
          },
          dataType: 'text'
        })
      }

      function validarCPF(strCPF) {

        if (!testaCPF(strCPF)) {
          $('#cpfFamiliarInvalido').show();
          document.getElementById("cadastrarFamiliar").disabled = true;

        } else {
          $('#cpfFamiliarInvalido').hide();

          document.getElementById("cadastrarFamiliar").disabled = false;
        }
      }

      /**verifica se um tipo de documento foi selecionado antes de submeter o formulário ao backend */
      function verificaTipo(ev) {
        const tipo = document.getElementById('tipoDocumento');

        if (isNaN(tipo.value) || tipo.value < 1) {
          alert('Erro: selecione um tipo de documento adequado antes de prosseguir.');
          ev.preventDefault(); // impede o envio
          return false; // impede o fluxo normal do onclick
        }

        // retorno true permite o envio normal do formulário
        return true;
      }
    </script>

    <script src="../geral/post.js"></script>
    <script src="../geral/formulario.js"></script>
    <script src="../../Functions/atendido_parentesco.js"></script>

</body>

</html>