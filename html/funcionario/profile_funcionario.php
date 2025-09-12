<?php
ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);
extract($_REQUEST);
session_start();

if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
  exit();
}

//Sanitizar entrada do id_funcionario
$idFuncionario = filter_input(INPUT_GET, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);

//Verificar se é um id válido
if (!$idFuncionario || $idFuncionario < 1) {
  echo json_encode(['erro' => 'O id de um funcionário deve ser maior ou igual a 1.']);
  exit(400);
}

try {
  require_once "../../dao/Conexao.php";
  $pdo = Conexao::connect();

  if (!isset($_SESSION['funcionario'])) {
    header('Location: ../../controle/control.php?metodo=listarUm&nomeClasse=FuncionarioControle&nextPage=../html/funcionario/profile_funcionario.php?id_funcionario=' . $idFuncionario . '&id_funcionario=' . $idFuncionario);
  } else {
    $func = $_SESSION['funcionario'];
    unset($_SESSION['funcionario']);
    // Adiciona Descrição de escala e tipo
    $func = json_decode($func);
    if ($func) {
      $func = $func[0];
      if ($func->tipo) {
        $func->tipo_descricao = $pdo->query("SELECT descricao FROM tipo_quadro_horario WHERE id_tipo=" . $func->tipo)->fetch(PDO::FETCH_ASSOC)['descricao'];
      }
      if ($func->escala) {
        $func->escala_descricao = $pdo->query("SELECT descricao FROM escala_quadro_horario WHERE id_escala=" . $func->escala)->fetch(PDO::FETCH_ASSOC)['descricao'];
      }
      $_SESSION['data_nasc'] = $func->data_nascimento;
      $_SESSION['data_emissao'] = $func->data_expedicao;
      $func = json_encode([$func]);
    }
  }

  $config_path = "config.php";
  if (file_exists($config_path)) {
    require_once($config_path);
  } else {
    while (true) {
      $config_path = "../" . $config_path;
      if (file_exists($config_path)) break;
    }
    require_once($config_path);
  }
  require_once "../permissao/permissao.php";
  permissao($_SESSION['id_pessoa'], 11, 7);

  $situacao = $pdo->query("SELECT * FROM situacao")->fetchAll();
  $cargo = $pdo->query("SELECT * FROM cargo")->fetchAll();
  // Adiciona a Função display_campo($nome_campo, $tipo_campo)
  require_once "../personalizacao_display.php";
  require_once "../../dao/Conexao.php";
  require_once ROOT . "/controle/FuncionarioControle.php";
  require_once ROOT . '/classes/Util.php';
  $cpf = new FuncionarioControle;
  $cpf->listarCPF();
  require_once ROOT . "/controle/AtendidoControle.php";
  $cpf1 = new AtendidoControle;
  $cpf1->listarCPF();
  require_once "../geral/msg.php";
  $docfuncional = $pdo->prepare("SELECT * FROM funcionario_docs f JOIN funcionario_docfuncional docf ON f.id_docfuncional = docf.id_docfuncional WHERE id_funcionario =:idFuncionario");

  $docfuncional->bindValue(':idFuncionario', $idFuncionario, PDO::PARAM_INT);

  if (!$docfuncional->execute()) {
    echo json_encode(['erro' => 'Falha ao executar consulta da documentação do funcionário']);
    exit(500);
  }

  $docfuncional = $docfuncional->fetchAll(PDO::FETCH_ASSOC);
  foreach ($docfuncional as $key => $value) {
    $docfuncional[$key]["arquivo"] = gzuncompress($value["arquivo"]);

    //formatar data
    $data = new DateTime($value['data']);
    $docfuncional[$key]['data'] = $data->format('d/m/Y h:i:s');
  }
  $docfuncional = json_encode($docfuncional);
  //SQL Injection abaixo
  $dependente = $pdo->prepare("SELECT fdep.id_dependente AS id_dependente, p.nome AS nome, p.cpf AS cpf, par.descricao AS parentesco FROM funcionario_dependentes fdep LEFT JOIN funcionario f ON f.id_funcionario = fdep.id_funcionario LEFT JOIN pessoa p ON p.id_pessoa = fdep.id_pessoa LEFT JOIN funcionario_dependente_parentesco par ON par.id_parentesco = fdep.id_parentesco WHERE fdep.id_funcionario =:idFuncionario");

  $dependente->bindValue(':idFuncionario', $idFuncionario, PDO::PARAM_INT);

  if (!$dependente->execute()) {
    echo json_encode(['erro' => 'Falha ao consultar dependentes de um funcionário']);
    exit(500);
  }

  $dependente = $dependente->fetchAll(PDO::FETCH_ASSOC);
  $dependente = json_encode($dependente);
} catch (Exception $e) {
  echo json_encode(['erro' => $e->getMessage()]);
  exit($e->getCode());
}

// Recebendo informação se o usuário tem o campo 'adm_configurado' como true (1) ou false (0)
$stmt = $pdo->prepare('SELECT adm_configurado FROM pessoa WHERE id_pessoa='. $_SESSION['id_pessoa']);
$stmt->execute();
$adm_configurado = $stmt->fetch(PDO::FETCH_ASSOC)['adm_configurado'];
?>
<!doctype html>
<html class="fixed">

<head>
  <!-- Basic -->
  <meta charset="UTF-8">
  <title>Perfil funcionário</title>
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
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
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
  <script src="../../assets/vendor/modernizr/modernizr.js"></script>
  <script src="../../Functions/onlyNumbers.js"></script>
  <script src="../../Functions/onlyChars.js"></script>
  <script src="../../Functions/mascara.js"></script>
  <script src="../../Functions/lista.js"></script>
  <script src="../../Functions/funcionario_parentesco.js"></script>
  <script src="<?php echo WWW; ?>Functions/testaCPF.js"></script>
  <script src="<?php echo WWW; ?>Functions/cargos.js"></script>

  <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
  <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
  <link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
  <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">
  <!-- Specific Page Vendor CSS -->
  <link rel="stylesheet" href="../../assets/vendor/select2/select2.css" />
  <link rel="stylesheet" href="../../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
  <!-- Theme CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
  <!-- Skin CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
  <!-- Theme Custom CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
  <!-- Head Libs -->
  <script src="../../assets/vendor/modernizr/modernizr.js"></script>
  <!-- Vendor -->
  <script src="../../assets/vendor/jquery/jquery.min.js"></script>
  <script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
  <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
  <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
  <script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
  <script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
  <script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
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
  <!-- jquery functions -->
  <script>
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

    function editar_documentacao() {
      $("#rg").prop('disabled', false);
      $("#orgao_emissor").prop('disabled', false);
      $("#data_expedicao").prop('disabled', false);
      $("#cpf").prop('disabled', true);
      alert("O cpf não pode ser editado!");
      $("#data_admissao").prop('disabled', false);
      $("#botaoEditarDocumentacao").html('Cancelar');
      $("#botaoSalvarDocumentacao").prop('disabled', false);
      $("#botaoEditarDocumentacao").removeAttr('onclick');
      $("#botaoEditarDocumentacao").attr('onclick', "return cancelar_documentacao()");
    }

    function cancelar_documentacao() {
      $("#rg").prop('disabled', true);
      $("#orgao_emissor").prop('disabled', true);
      $("#data_expedicao").prop('disabled', true);
      $("#cpf").prop('disabled', true);
      $("#data_admissao").prop('disabled', true);
      $("#botaoEditarDocumentacao").html('Editar');
      $("#botaoSalvarDocumentacao").prop('disabled', true);
      $("#botaoEditarDocumentacao").removeAttr('onclick');
      $("#botaoEditarDocumentacao").attr('onclick', "return editar_documentacao()");
    }

    function editar_outros() {
      let adm_configurado = <?php echo $adm_configurado; ?>;
      
      $("#pis").prop('disabled', false);
      $("#ctps").prop('disabled', false);
      $("#uf_ctps").prop('disabled', false);
      $("#zona_eleitoral").prop('disabled', false);
      $("#titulo_eleitor").prop('disabled', false);
      $("#secao_titulo_eleitor").prop('disabled', false);
      $("#certificado_reservista_numero").prop('disabled', false);
      $("#certificado_reservista_serie").prop('disabled', false);
      $("#situacao").prop('disabled', false);
      if(adm_configurado){
        $("#cargo").prop('disabled', false);
      }
      $("#botaoEditarOutros").html('Cancelar');
      $("#botaoSalvarOutros").prop('disabled', false);
      $("#botaoEditarOutros").removeAttr('onclick');
      $("#botaoEditarOutros").attr('onclick', "return cancelar_outros()");
    }

    function cancelar_outros() {
      $("#pis").prop('disabled', true);
      $("#ctps").prop('disabled', true);
      $("#uf_ctps").prop('disabled', true);
      $("#zona_eleitoral").prop('disabled', true);
      $("#titulo_eleitor").prop('disabled', true);
      $("#secao_titulo_eleitor").prop('disabled', true);
      $("#certificado_reservista_numero").prop('disabled', true);
      $("#certificado_reservista_serie").prop('disabled', true);
      $("#situacao").prop('disabled', true);
      $("#cargo").prop('disabled', true);
      $("#botaoEditarOutros").html('Editar');
      $("#botaoSalvarOutros").prop('disabled', true);
      $("#botaoEditarOutros").removeAttr('onclick');
      $("#botaoEditarOutros").attr('onclick', "return editar_outros()");
    }

    function alterardate(data) {
      var date = data.split("/")
      return date[2] + "-" + date[1] + "-" + date[0];
    }
    $(function() {
      var funcionario = <?= $func ?>;
      $.each(funcionario, function(i, item) {
        //Informações pessoais
        $("#nomeForm").val(item.nome).prop('disabled', true);
        $("#sobrenomeForm").val(item.sobrenome).prop('disabled', true);
        if (item.sexo == "m") {
          $("#radioM").prop('checked', true).prop('disabled', true);
          $("#radioF").prop('checked', false).prop('disabled', true);
          $("#reservista1").show();
          $("#reservista2").show();
        } else if (item.sexo == "f") {
          $("#radioM").prop('checked', false).prop('disabled', true)
          $("#radioF").prop('checked', true).prop('disabled', true);
        }
        $("#telefone").val(item.telefone).prop('disabled', true);
        $("#nascimento").val(alterardate(item.data_nascimento)).prop('disabled', true);
        $("#pai").val(item.nome_pai).prop('disabled', true);
        $("#mae").val(item.nome_mae).prop('disabled', true);
        $("#sangue").val(item.tipo_sanguineo).prop('disabled', true);
        //Endereço
        $("#cep").val(item.cep).prop('disabled', true);
        $("#uf").val(item.estado).prop('disabled', true);
        $("#cidade").val(item.cidade).prop('disabled', true);
        $("#bairro").val(item.bairro).prop('disabled', true);
        $("#rua").val(item.logradouro).prop('disabled', true);
        $("#complemento").val(item.complemento).prop('disabled', true);
        $("#ibge").val(item.ibge).prop('disabled', true);
        if (item.numero_endereco == 'N?o possui' || item.numero_endereco == null) {
          $("#numResidencial").prop('checked', true).prop('disabled', true);
          $("#numero_residencia").prop('disabled', true);
        } else {
          $("#numero_residencia").val(item.numero_endereco).prop('disabled', true);
          $("#numResidencial").prop('disabled', true);
        }
        //Documentação
        var cpf = item.cpf;
        $("#rg").val(item.registro_geral).prop('disabled', true);
        $("#orgao_emissor").val(item.orgao_emissor).prop('disabled', true);
        $("#data_expedicao").val(alterardate(item.data_expedicao)).prop('disabled', true);
        $("#cpf").val(cpf).prop('disabled', true);
        $("#data_admissao").val(alterardate(item.data_admissao)).prop('disabled', true);
        //Outros
        $("#pis").val(item.pis).prop('disabled', true);
        $("#ctps").val(item.ctps).prop('disabled', true);
        $("#uf_ctps").val(item.uf_ctps).prop('disabled', true);
        $("#zona_eleitoral").val(item.zona).prop('disabled', true);
        $("#titulo_eleitor").val(item.numero_titulo).prop('disabled', true);
        $("#secao_titulo_eleitor").val(item.secao).prop('disabled', true);
        $("#certificado_reservista_numero").val(item.certificado_reservista_numero).prop('disabled', true);
        $("#certificado_reservista_serie").val(item.certificado_reservista_serie).prop('disabled', true);
        $("#situacao").val(item.id_situacao).prop('disabled', true);
        $("#cargo").val(item.id_cargo).prop('disabled', true);
        //CARGA HORÁRIA
        $("#dias_trabalhados").text("Dias trabalhados: " + (item.dias_trabalhados || "Sem informação"));
        if (item.dias_trabalhados == "Plantão") {
          $("#dias_trabalhados").text("Dias trabalhados: " + (item.dias_trabalhados || "Sem informação") + " 12/36");
        }
        $("#dias_folga").text("Dias de folga: " + (item.folga || "Sem informação"));
        $("#total").text("Carga horária diária: " + (item.total || "Sem informação"));
        $("#carga_horaria_mensal").text("Carga horária mensal: " + (item.carga_horaria || "Sem informação"));
        if (item.escala) {
          $("#escala_input").val(item.escala);
        }
        if (item.tipo) {
          $("#tipoCargaHoraria_input").val(item.tipo);
        }
        if (item.entrada1) {
          $("#entrada1_input").val(item.entrada1);
        }
        if (item.saida1) {
          $("#saida1_input").val(item.saida1);
        }
        if (item.entrada2) {
          $("#entrada2_input").val(item.entrada2);
        }
        if (item.saida2) {
          $("#saida2_input").val(item.saida2);
        }
        var dia_trabalhado = (item.dias_trabalhados ? item.dias_trabalhados.split(",") : []);
        var dia_folga = (item.folga ? item.folga.split(",") : []);
        for (var i = 0; i < dia_trabalhado.length; i++) {
          $("#diaTrabalhado_" + dia_trabalhado[i]).prop("checked", true);
        }
        for (var j = 0; j < dia_folga.length; j++) {
          $("#diaFolga_" + dia_folga[j]).prop("checked", true);
        }
      })
    });
    //ARQUIVOS
    $(function() {
      var docfuncional = <?= $docfuncional ?>;
      $.each(docfuncional, function(i, item) {
        $("#doc-tab")
          .append($("<tr>")
            .append($("<td>").text(item.nome_docfuncional))
            .append($("<td>").text(item.data))
            .append($("<td style='display: flex; justify-content: space-evenly;'>")
              .append($("<a href='documento_download.php?id_doc=" + item.id_fundocs + "' title='Visualizar ou Baixar'><button class='btn btn-primary'><i class='fas fa-download'></i></button></a>"))
              .append($("<a onclick='removerFuncionarioDocs(" + item.id_fundocs + ")' href='#' title='Excluir'><button class='btn btn-danger'><i class='fas fa-trash-alt'></i></button></a>"))
            )
          )
      });
    });

    function listarFunDocs(docfuncional) {
      $("#doc-tab").empty();
      $.each(docfuncional, function(i, item) {
        $("#doc-tab")
          .append($("<tr>")
            .append($("<td>").text(item.nome_docfuncional))
            .append($("<td>").text(item.data))
            .append($("<td style='display: flex; justify-content: space-evenly;'>")
              .append($("<a href='documento_download.php?id_doc=" + item.id_fundocs + "' title='Visualizar ou Baixar'><button class='btn btn-primary'><i class='fas fa-download'></i></button></a>"))
              .append($("<a onclick='removerFuncionarioDocs(" + item.id_fundocs + ")' href='#' title='Excluir'><button class='btn btn-danger'><i class='fas fa-trash-alt'></i></button></a>"))
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

    function listarDependentes(dependente) {
      $("#dep-tab").empty();
      $.each(dependente, function(i, dependente) {
        $("#dep-tab")
          .append($("<tr>")
            .append($("<td>").text(dependente.nome))
            .append($("<td>").text(dependente.cpf))
            .append($("<td>").text(dependente.parentesco))
            .append($("<td style='display: flex; justify-content: space-evenly;'>")
              .append($("<a href='profile_dependente.php?id_dependente=" + dependente.id_dependente + "' title='Editar'><button class='btn btn-primary'><i class='fas fa-user-edit'></i></button></a>"))
              .append($("<button class='btn btn-danger' onclick='removerDependente(" + dependente.id_dependente + ")'><i class='fas fa-trash-alt'></i></button>"))
            )
          )
      });
    }
    $(function() {
      listarDependentes(<?= $dependente ?>);
    });
    $(function() {
      $('#datatable-dependente').DataTable({
        "order": [
          [0, "asc"]
        ]
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

    function exibir_reservista() {
      $("#reservista1").show();
      $("#reservista2").show();
    }

    function esconder_reservista() {
      $("#reservista1").hide();
      $("#reservista2").hide();
    }

    function limpa_formulário_cep() {
      //Limpa valores do formulário de cep.
      document.getElementById('rua').value = ("");
      document.getElementById('bairro').value = ("");
      document.getElementById('cidade').value = ("");
      document.getElementById('uf').value = ("");
      document.getElementById('ibge').value = ("");
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

    function validarCPF(strCPF, botão) {
      if (!testaCPF(strCPF)) {
        $('.cpfInvalido').show();
        document.getElementById(botão).disabled = true;
      } else {
        $('.cpfInvalido').hide();
        document.getElementById(botão).disabled = false;
      }
    }
  </script>
  <script language="JavaScript">
    var numValidos = "0123456789-()";
    var num1invalido = "78";
    var i;

    function validarTelefone() {
      //Verificando quantos dígitos existem no campo, para controlarmos o looping;
      digitos = document.form1.telefone.value.length;
      for (i = 0; i < digitos; i++) {
        if (numValidos.indexOf(document.form1.telefone.value.charAt(i), 0) == -1) {
          alert("Apenas números são permitidos no campo Telefone!");
          document.form1.telefone.select();
          return false;
        }
        if (i == 0) {
          if (num1invalido.indexOf(document.form1.telefone.value.charAt(i), 0) != -1) {
            alert("Número de telefone inválido!");
            document.form1.telefone.select();
            return false;
          }
        }
      }
    }
    $(function() {
      $("#header").load("../header.php");
      $(".menuu").load("../menu.php");
    });

    function gerarSituacao() {
      url = '../../dao/exibir_situacao.php';
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
      url = '../../dao/adicionar_situacao.php';
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
                <a href="../home.php">
                  <i class="fa fa-home"></i>
                </a>
              </li>
              <li><span>Páginas</span></li>
              <li><span>Perfil</span></li>
            </ol>
            <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
          </div>
        </header>
        <!-- start: page -->
        <!-- Mensagem -->
        <?php getMsgSession("msg", "tipo"); ?>
        <div class="row">
          <div class="col-md-4 col-lg-3">
            <section class="panel">
              <div class="panel-body">
                <div class="thumb-info mb-md">
                  <?php
                  $id_pessoa = $_SESSION['id_pessoa'];

                  //SQL Injection abaixo
                  $stmtImagem = $pdo->prepare("SELECT pessoa.imagem, pessoa.nome FROM pessoa, funcionario  WHERE pessoa.id_pessoa=funcionario.id_pessoa and funcionario.id_funcionario=:idFuncionario");

                  $stmtImagem->bindValue(':idFuncionario', $idFuncionario, PDO::PARAM_INT);

                  if (!$stmtImagem->execute()) {
                    $foto = WWW . "img/semfoto.png";
                  } else {
                    $pessoa = $stmtImagem->fetch(PDO::FETCH_ASSOC);
                    if (isset($_SESSION['id_pessoa']) and !empty($_SESSION['id_pessoa'])) {
                      $foto = $pessoa['imagem'];
                      if ($foto != null and $foto != "") {
                        $foto = 'data:image;base64,' . $foto;
                      } else {
                        $foto = WWW . "img/semfoto.png";
                      }
                    }
                  }
                  echo "<img src='$foto' style='margin-bottom: 15px;' id='imagem' class='rounded img-responsive' alt='John Doe'>";
                  ?>
                  <button class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal"><i class="fa fa-camera-retro"></i></button>

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
                              <input type="hidden" name="nomeClasse" value="FuncionarioControle">
                              <input type="hidden" name="metodo" value="alterarImagem">
                              <div class="form-group">
                                <label class="col-md-4 control-label" for="imgperfil">Carregue nova imagem de perfil:</label>
                                <div class="col-md-8">
                                  <input type="file" name="imgperfil" size="60" id="imgform" class="form-control">
                                </div>
                              </div>
                          </div>
                          <div class="modal-footer">
                            <!-- Pegar id funcionário de variável sanitizada -->
                            <input type="hidden" name="id_funcionario" value=<?= $idFuncionario ?>>
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
                      <ul class="simple-todo-list"></ul>
                    </div>
                  </div>
                </div>
              </div>
            </section>
          </div>
          <div class="col-md-8 col-lg-8">
            <div class="tabs">
              <ul class="nav nav-tabs tabs-primary">
                <li class="active">
                  <a href="#overview" data-toggle="tab">Informações Pessoais</a>
                </li>
                <li>
                  <a href="#endereco" data-toggle="tab">Endereço</a>
                </li>
                <li>
                  <a href="#documentos" data-toggle="tab">Documentação</a>
                </li>
                <li>
                  <a href="#arquivo" data-toggle="tab">Arquivos</a>
                </li>
                <li>
                  <a href="#outros" data-toggle="tab">Outros</a>
                </li>
                <li>
                  <a href="#beneficio" data-toggle="tab">Remuneração</a>
                </li>
                <li>
                  <a href="#editar_cargaHoraria" data-toggle="tab">Carga Horária</a>
                </li>
                <li>
                  <a href="#dependentes" data-toggle="tab">Dependentes</a>
                </li>
              </ul>
              <div class="tab-content">
                <!--Aba de Informações Pessoais-->
                <div id="overview" class="tab-pane active">
                  <form class="form-horizontal" method="post" action="../../controle/control.php">
                    <input type="hidden" name="nomeClasse" value="FuncionarioControle">
                    <input type="hidden" name="metodo" value="alterarInfPessoal">
                    <h4 class="mb-xlg">Informações Pessoais</h4>
                    <fieldset>
                      <div class="form-group">
                        <label class="col-md-3 control-label" for="profileFirstName">Nome</label>
                        <div class="col-md-8">
                          <input type="text" class="form-control" name="nome" id="nomeForm" onkeypress="return Onlychars(event)">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-md-3 control-label" for="profileFirstName">Sobrenome</label>
                        <div class="col-md-8">
                          <input type="text" class="form-control" name="sobrenome" id="sobrenomeForm" onkeypress="return Onlychars(event)">
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
                        <label class="col-md-3 control-label" for="profileCompany">Telefone</label>
                        <div class="col-md-8">
                          <input type="text" class="form-control" maxlength="14" minlength="14" name="telefone" id="telefone" placeholder="Ex: (22)99999-9999" onkeypress="return Onlynumbers(event)" onkeyup="mascara('(##)#####-####',this,event)">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-md-3 control-label" for="profileCompany">Nascimento</label>
                        <div class="col-md-8">
                          <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="nascimento" id="nascimento" max=<?php echo date('Y-m-d'); ?>>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-md-3 control-label" for="profileFirstName">Nome do pai</label>
                        <div class="col-md-8">
                          <input type="text" class="form-control" name="nome_pai" id="pai" onkeypress="return Onlychars(event)">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-md-3 control-label" for="profileFirstName">Nome da mãe</label>
                        <div class="col-md-8">
                          <input type="text" class="form-control" name="nome_mae" id="mae" onkeypress="return Onlychars(event)">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-md-3 control-label" for="inputSuccess">Tipo sanguíneo</label>
                        <div class="col-md-6">
                          <select class="form-control input-lg mb-md" name="sangue" id="sangue">
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
                      <!-- Pegar id funcionário de variável sanitizada -->
                      <input type="hidden" name="id_funcionario" value=<?= $idFuncionario ?>>
                      <button type="button" class="btn btn-primary" id="botaoEditarIP" onclick="return editar_informacoes_pessoais()">Editar</button>
                      <input type="submit" class="btn btn-primary" disabled="true" value="Salvar" id="botaoSalvarIP">
                    </fieldset>
                  </form><br>
                  <div class="panel-footer">
                    <div class="row">
                      <div class="col-md-9 col-md-offset-3">
                        <!-- <button id="excluir" type="button" class="btn btn-danger" data-toggle="modal" data-target="#exclusao">Demitir</button> -->
                      </div>
                    </div>
                  </div>
                  <div class="modal fade" id="exclusao" role="dialog">
                    <div class="modal-dialog">
                      <!-- Modal content-->
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" aba-dismiss="modal">×</button>
                          <h3>Demitir um Funcionário</h3>
                        </div>
                        <div class="modal-body">
                          <p> Tem certeza que deseja demitir esse funcionário? Essa ação não poderá ser desfeita e todas as informações referentes a esse funcionário serão perdidas!</p>
                          <!-- Pegar id funcionário de variável sanitizada -->
                          <a href="../../controle/control.php?metodo=excluir&nomeClasse=FuncionarioControle&id_funcionario=<?= $idFuncionario ?>"><button button type="button" class="btn btn-success">Confirmar</button></a>
                          <button button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Aba de remuneração do funcionário -->
                <div id="beneficio" class="tab-pane">
                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Remuneração</h2>
                    </header>
                    <div class="panel-body">
                      <h5 class="mb-xlg">Remuneração: R$ <b class="total"></b></h5>
                      <table class="table table-bordered table-striped mb-none" id="datatable-default">
                        <thead>
                          <tr>
                            <th>Remuneração</th>
                            <th>Data Início</th>
                            <th>Data Fim</th>
                            <th>Valor</th>
                            <th>Ação</th>
                          </tr>
                        </thead>
                        <tbody id="tabela_remuneracao"></tbody>
                      </table>
                      <button id="excluir" type="button" class="btn btn-success" data-toggle="modal" data-target="#adicionar">Adicionar</button>
                    </div><br>
                    <div class="modal fade" id="adicionar" role="dialog">
                      <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" id="closeRemuneracaoModal">×</button>
                            <h3>Adicionar Remuneração</h3>
                          </div>
                          <div class="modal-body">
                            <fieldset id="formRemuneracao">
                              <div class="form-group">
                                <label class="col-md-3 control-label" for="tipo_remuneracao">Tipo</label>
                                <div class="col-md-6" style="display: flex;">
                                  <select class="form-control input-lg mb-md" name="id_tipo" id="tipo_remuneracao" required>
                                    <option selected disabled>Selecionar</option>
                                    <?php
                                    $tipos = ($pdo->query("SELECT idfuncionario_remuneracao_tipo as id, descricao FROM funcionario_remuneracao_tipo;"))->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($tipos as $key => $tipo) {
                                      echo "<option value='" . $tipo["id"] . "'>" . $tipo["descricao"] . "</option>";
                                    }
                                    ?>
                                  </select>
                                  <a onclick="adicionarTipoRemuneracao()" style="margin: 0 20px;" id="btn_adicionar_tipo_remuneracao"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="col-md-3 control-label" for="valor_remuneracao">Valor</label>
                                <div class="col-md-8">
                                  <input type="number" class="form-control" name="valor" id="valor_remuneracao" onkeypress="return Onlynumbers(event)" required>
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="col-md-3 control-label" for="inicio_remuneracao">Data Inicio</label>
                                <div class="col-md-8">
                                  <input type="date" name="inicio" id="inicio_remuneracao" class="form-control">
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="col-md-3 control-label" for="fim_remuneracao">Data Fim</label>
                                <div class="col-md-8">
                                  <input type="date" name="fim" id="fim_remuneracao" class="form-control">
                                </div>
                              </div>
                              <!-- Pegar id funcionário de variável sanitizada -->
                              <input type="hidden" name="id_funcionario" value=<?= $idFuncionario ?>>
                              <input type="hidden" name="action" value="remuneracao_adicionar">
                              <button class="btn btn-primary" id="botaoSalvarRemuneracao" onclick="adicionarRemuneracao('formRemuneracao', console.log)">Salvar</button>
                            </fieldset>
                          </div>
                        </div>
                      </div>
                  </section>
                </div>
                <!--Outros-->
                <script>
                  function formatPIS(input) {
                    let value = input.value.replace(/\D/g, '');
                    if (value.length > 11) value = value.substring(0, 11);
                    input.value = value.replace(/(\d{3})(\d{5})(\d{2})(\d{1})/, '$1.$2.$3-$4');
                  }

                  function formatCTPS(input) {
                    let value = input.value.replace(/\D/g, '');
                    if (value.length > 11) value = value.substring(0, 11);
                    input.value = value.replace(/(\d{7})(\d{4})/, '$1/$2');
                  }
                </script>
                <div id="outros" class="tab-pane">
                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Outros</h2>
                    </header>
                    <div class="panel-body">
                      <form class="form-horizontal" method="POST" action="../../controle/control.php">
                        <input type="hidden" name="nomeClasse" value="FuncionarioControle">
                        <input type="hidden" name="metodo" value="alterarOutros">
                        <div class="form-group">
                          <label for="pis" class="col-md-3 control-label">PIS (Número de Identificação do Trabalhador)</label>
                          <div class="col-md-6">
                            <input type="text" id="pis" name="pis" class="form-control" maxlength="14"
                              placeholder="123.45678.91-0"
                              oninput="formatPIS(this)" required>
                            <small>Formato: 123.45678.91-0</small>
                          </div>
                        </div>

                        <div class="form-group">
                          <label for="ctps" class="col-md-3 control-label">CTPS (Carteira de Trabalho e Previdência Social)</label>
                          <div class="col-md-6">
                            <input type="text" id="ctps" name="ctps" class="form-control" maxlength="12"
                              placeholder="1234567/8910"
                              oninput="formatCTPS(this)" required>
                            <small>Formato: 1234567/8910</small>
                          </div>
                        </div>

                        <!-- Campo Estado CTPS -->
                        <div class="form-group">
                          <label class="col-md-3 control-label" for="uf">Estado CTPS</label>
                          <div class="col-md-6">
                            <input type="text" name="uf_ctps" size="60" class="form-control" id="uf_ctps">
                          </div>
                        </div>

                        <!-- Campo Título de Eleitor -->
                        <div class="form-group">
                          <label for="titulo_eleitor" class="col-md-3 control-label">Título de Eleitor</label>
                          <div class="col-md-6">
                            <input type="text" name="titulo_eleitor" id="titulo_eleitor" class="form-control"
                              pattern="\d{12}" maxlength="12" placeholder="123456789012"
                              oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                              required>
                            <small>Formato: 123456789012</small>
                          </div>
                        </div>

                        <!-- Campo Zona Eleitoral -->
                        <div class="form-group">
                          <label for="zona_eleitoral" class="col-md-3 control-label">Zona Eleitoral</label>
                          <div class="col-md-6">
                            <input type="text" name="zona_eleitoral" id="zona_eleitoral" class="form-control"
                              pattern="\d{3}" maxlength="3" placeholder="123"
                              oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                              required>
                            <small>Formato: 123</small>
                          </div>
                        </div>

                        <!-- Campo Seção Eleitoral -->
                        <div class="form-group">
                          <label for="secao_titulo_eleitor" class="col-md-3 control-label">Seção do Título de Eleitor</label>
                          <div class="col-md-6">
                            <input type="text" name="secao_titulo_eleitor" id="secao_titulo_eleitor" class="form-control"
                              pattern="\d{4}" maxlength="4" placeholder="1234"
                              oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                              required>
                            <small>Formato: 1234</small>
                          </div>
                        </div>

                        <div class="form-group" id="reservista1" style="display: none">
                          <label class="col-md-3 control-label">Número do certificado reservista</label>
                          <div class="col-md-6">
                            <input type="text" id="certificado_reservista_numero" name="certificado_reservista_numero"
                              class="form-control num_reservista" maxlength="9"
                              pattern="\d*" inputmode="numeric" placeholder="123456789"
                              oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                            <small>Formato: 123456789</small>
                          </div>
                        </div>

                        <div class="form-group" id="reservista2" style="display: none">
                          <label class="col-md-3 control-label">Série do certificado reservista</label>
                          <div class="col-md-6">
                            <input type="text" id="certificado_reservista_serie" name="certificado_reservista_serie"
                              class="form-control serie_reservista" maxlength="3"
                              pattern="\d*" inputmode="numeric" placeholder="001"
                              oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                            <small>Formato: 001</small>
                          </div>
                        </div>

                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileCompany">Data de Admissão</label>
                          <div class="col-md-6">
                            <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="data_admissao" id="data_admissao" max=<?php echo date('Y-m-d'); ?>>
                          </div>

                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label" style='text-align: right; margin-top: 10px;' for="situacao">Situação</label>
                          <div class="col-md-6">
                            <select class="form-control input-lg mb-md" name="situacao" id="situacao">
                              <option selected disabled>Selecionar</option>
                              <?php
                              foreach ($situacao as $row) {
                                echo "<option value=" . $row[0] . ">" . htmlspecialchars($row[1]) . "</option>";
                              }
                              ?>
                            </select>
                          </div>
                          <a onclick="adicionar_situacao()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label" style='text-align: right;  margin-top: 10px;' for="inputSuccess">Cargo</label>
                          <div class="col-md-6">
                            <select class="form-control input-lg mb-md" name="cargo" id="cargo">
                              <option selected disabled>Selecionar</option>
                              <?php
                              foreach ($cargo as $row) {
                                echo "<option value=" . $row[0] . ">" . htmlspecialchars($row[1]) . "</option>";
                              }
                              ?>
                            </select>
                          </div>
                          <a onclick="adicionar_cargo()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                        </div>
                        <!-- Pegar id funcionário de variável sanitizada -->
                        <input type="hidden" name="id_funcionario" value=<?= $idFuncionario ?>>
                        <button type="button" class="btn btn-primary" id="botaoEditarOutros" onclick="return editar_outros()">Editar</button>
                        <input type="submit" class="btn btn-primary" value="Salvar" id="botaoSalvarOutros" disabled="true">
                      </form>
                      <h4>Informações Adicionais</h4>
                      <table class="table table-bordered table-striped mb-none" id="datatable-addInfo">
                        <thead>
                          <tr>
                            <th>Descrição</th>
                            <th>Dados</th>
                            <th>Ação</th>
                          </tr>
                        </thead>
                        <tbody id="addInfo-tab">
                          <?php
                          try {
                            $stmtInfoAdd = $pdo->prepare("SELECT * FROM funcionario_outrasinfo WHERE funcionario_id_funcionario =:idFuncionario");
                            $stmtInfoAdd->bindValue(':idFuncionario', $idFuncionario, PDO::PARAM_INT);
                            $stmtInfoAdd->execute();
                            $infoAdd = $stmtInfoAdd->fetchAll(PDO::FETCH_ASSOC);

                            $tam = count($infoAdd);
                            for ($i = 0; $i < $tam; $i++) {
                              $dado = htmlspecialchars($infoAdd[$i]['dado']);
                              $descricaoId = htmlspecialchars($infoAdd[$i]['funcionario_listainfo_idfuncionario_listainfo']);
                              $idInfoAdicional = $infoAdd[$i]['idfunncionario_outrasinfo'];

                              $stmtDescricao = $pdo->prepare("SELECT descricao FROM funcionario_listainfo WHERE idfuncionario_listainfo =:descricaoId");

                              $stmtDescricao->bindValue(':descricaoId', $descricaoId, PDO::PARAM_INT);
                              $stmtDescricao->execute();
                              $descricao = $stmtDescricao->fetchAll(PDO::FETCH_ASSOC);

                              $nome_desc = htmlspecialchars($descricao[0]['descricao']);
                              echo
                              "
                                  <tr id='informacao$idInfoAdicional'>
                                    <td>$nome_desc</td>
                                    <td>$dado</td>
                                    <td style='display: flex; justify-content: space-evenly;'>
                                      <button onclick='removerDescricao($idInfoAdicional)' title='Excluir' class='btn btn-danger'><i class='fas fa-trash-alt'></i></button>
                                    </td>
                                  </tr>
                                ";
                            }
                          } catch (PDOException $e) {
                            echo json_encode(['erro' => $e->getMessage()]);
                            exit($e->getCode());
                          }
                          ?>
                        </tbody>
                      </table><br>
                      <!-- Button trigger modal -->
                      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addInfoModal">
                        Adicionar Informação
                      </button>
                      <div class="modal fade" id="addInfoModal" tabindex="-1" role="dialog" aria-labelledby="addInfoModal" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="modal-header" style="display: block ruby;">
                              <h5 class="modal-title" id="addInfoModalLabel">Adicionar informação adicional</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="close_addInfoModal">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <form id="formInfoAdicional">
                                <div class="form-group">
                                  <label for="descricao_addInfo" class="col-form-label">Descrição</label>
                                  <div style="display: block ruby;">
                                    <select name="id_descricao" id="descricao_addInfo" class="form-control" style="width: 300px;" required>
                                      <option selected disabled>Selecionar</option>
                                      <?php
                                      $descricao = $pdo->query("SELECT * FROM funcionario_listainfo;")->fetchAll(PDO::FETCH_ASSOC);
                                      foreach ($descricao as $key => $value) {
                                        echo ("<option id='desc' value=" . $value["idfuncionario_listainfo"] . ">" . htmlspecialchars($value["descricao"]) . "</option>");
                                      }
                                      ?>
                                    </select>
                                    <a onclick="adicionar_addInfoDescricao()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw; margin-left: 10px;"></i></a>
                                  </div>
                                </div>
                                <div class="form-group">
                                  <label for="dados_addInfo" class="col-form-label">Dados</label>
                                  <textarea class="form-control" id="dados_addInfo" style="padding: 6px 12px; height: 120px;" name="dados" maxlength="255" required></textarea>
                                </div>
                                <input type="text" name="action" value="adicionar" hidden>
                                <!-- Pegar id funcionário de variável sanitizada -->
                                <input type="text" name="id_funcionario" value="<?= $idFuncionario ?>" hidden>
                              </form>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                              <button type="button" class="btn btn-primary" onclick="adicionarAddInfo()">Enviar</button>
                              <script>
                                $(function() {
                                  $('#datatable-addInfo').DataTable({
                                    "order": [
                                      [0, "asc"]
                                    ]
                                  });
                                  //Pegar id funcionário de variável sanitizada
                                  post("informacao_adicional.php", "action='listar'&id_funcionario=<?= $idFuncionario ?>")
                                });

                                function adicionar_addInfoDescricao() {
                                  url = 'informacao_adicional.php';
                                  var situacao = window.prompt("Cadastrar nova descrição:");
                                  if (!situacao) {
                                    return
                                  }
                                  situacao = situacao.trim();
                                  if (situacao == '')

                                  {
                                    return
                                  }
                                  post(url, "action=adicionar_descricao&descricao=" + situacao, listarInfoDescricao);
                                }

                                function listarInfoDescricao(lista) {
                                  if (lista["aviso"] || lista["errorInfo"]) {
                                    return false;
                                  }
                                  $('#descricao_addInfo').empty();
                                  $('#descricao_addInfo').append('<option selected disabled>Selecionar</option>');
                                  $.each(lista, function(i, item) {
                                    $('#descricao_addInfo').append('<option value="' + item.idfuncionario_listainfo + '">' + item.descricao + '</option>');
                                  });
                                }

                                function adicionarAddInfo() {
                                  if (submitForm("formInfoAdicional", listarInfoAdicional)) {
                                    $("#close_addInfoModal").click();
                                  }
                                }

                                function removerDescricao(id_descricao) {
                                  let url = "informacao_adicional.php";
                                  let data = "action=remover&id_descricao=" + id_descricao;
                                  post(url, data, listarInfoAdicional);
                                  $("#" + 'informacao'+id_descricao + "").remove();
                                }

                                //Refazer lógica abaixo
                                function listarInfoAdicional(lista) {
                                  //Pegar id funcionário de variável sanitizada
                                  console.log('Linha 1279');
                                  $.ajax({
                                    type: "GET",
                                    url: `../../controle/control.php?nomeClasse=${encodeURIComponent('InformacaoAdicionalControle')}&metodo=${encodeURIComponent('getTodasInformacoesAdicionaisPorIdFuncionario')}&id_funcionario=<?= $idFuncionario ?>`,
                                    dataType: 'json',
                                    success: function(resp) {
                                      console.log('Informações adicionais', resp);

                                      let tabela = $("#addInfo-tab");

                                      // Limpa a tabela antes de adicionar os novos dados (se necessário)
                                      tabela.empty();

                                      // Verifica se há dados antes de tentar adicioná-los
                                      if (resp.length > 0) {
                                        resp.forEach(info => {
                                          let idInfoAdicional = info.id;
                                          let nome_desc = info.descricao;
                                          let dado = info.dados;

                                          let linha = `
                                              <tr id="informacao${idInfoAdicional}">
                                                <td>${nome_desc}</td>
                                                <td>${dado}</td>
                                                <td style='display: flex; justify-content: space-evenly;'>
                                                  <button onclick='removerDescricao(${idInfoAdicional})' title='Excluir' class='btn btn-danger'>
                                                    <i class='fas fa-trash-alt'></i>
                                                  </button>
                                                </td>
                                              </tr>
                                            `;

                                          tabela.append(linha);
                                        });
                                      } else {
                                        tabela.append("<tr><td colspan='3' class='text-center'>Nenhuma informação adicional encontrada.</td></tr>");
                                      }
                                    },
                                    error: function(xhr, status, error) {
                                      console.error("Erro ao buscar informações adicionais:", error);
                                    }
                                  });
                                }
                              </script>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </section>
                </div>
                <!-- Aba de carga horária do funcionário -->
                <div id="editar_cargaHoraria" class="tab-pane">
                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Carga Horária</h2>
                    </header>
                    <div class="panel-body">
                      <form class="form-horizontal" method="post" action="../../controle/control.php">
                        <div class="form-group">
                          <label class="col-md-3 control-label">Escala</label>
                          <div class="col-md-6">
                            <select class="form-control input-lg mb-md" name="escala" id="escala_input">
                              <option id="escala_default" selected disabled value="">Selecionar</option>
                              <?php
                              $pdo = Conexao::connect();
                              $escala = $pdo->query("SELECT * FROM escala_quadro_horario;")->fetchAll(PDO::FETCH_ASSOC);
                              foreach ($escala as $key => $value) {
                                echo ("<option id='escala_" . $value["id_escala"] . "' value=" . $value["id_escala"] . ">" . htmlspecialchars($value["descricao"]) . "</option>");
                              }
                              ?>
                            </select>
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label">Tipo</label>
                          <div class="col-md-6">
                            <select class="form-control input-lg mb-md" name="tipoCargaHoraria" id="tipoCargaHoraria_input">
                              <option selected disabled value="">Selecionar</option>
                              <?php
                                $pdo = Conexao::connect();
                                $tipoCarga = $pdo->query("SELECT * FROM tipo_quadro_horario;")->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($tipoCarga as $key => $value) {
                                  echo ("<option id='tipo_" . $value["id_tipo"] . "' value=" . $value["id_tipo"] . ">" . htmlspecialchars($value["descricao"]) . "</option>");
                                };
                              ?>
                            </select>
                            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
                            <script>
                              $(document).ready(function() {
                                $("#tipoCargaHoraria_input").on('change', function() {
                                  var selectValor = $(this).val();
                                  if (selectValor == 1) {
                                    $("#diaTrabalhado").hide();
                                  } else if (selectValor == 2) {
                                    $("#diaTrabalhado").show();
                                  }
                                });
                              });
                            </script>
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label">Primeira entrada</label>
                          <div class="col-md-3">
                            <input type="time" placeholder="07:25" class="form-control" name="entrada1" id="entrada1_input">
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label">Primeira saída</label>
                          <div class="col-md-3">
                            <input type="time" placeholder="07:25" class="form-control" name="saida1" id="saida1_input">
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label">Segunda entrada</label>
                          <div class="col-md-3">
                            <input type="time" placeholder="07:25" class="form-control" name="entrada2" id="entrada2_input">
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label">Segunda saída</label>
                          <div class="col-md-3">
                            <input type="time" placeholder="07:25" class="form-control" name="saida2" id="saida2_input">
                          </div>
                        </div>
                        <div id="diaTrabalhado">
                          <div class="text-center">
                            <h3 class="col-md-12">Dias Trabalhados</h3>
                            <div class="btn-group">
                              <label class="btn btn-primary ">
                                <input type="checkbox" id="diaTrabalhado_Seg" name="trabSeg" value="Seg">Seg
                                <span class="fa fa-check"></span>
                              </label>
                              <label class="btn btn-primary">
                                <input type="checkbox" id="diaTrabalhado_Ter" name="trabTer" value="Ter"> Ter
                                <span class="fa fa-check"></span>
                              </label>
                              <label class="btn btn-primary">
                                <input type="checkbox" id="diaTrabalhado_Qua" name="trabQua" value="Qua"> Qua
                                <span class="fa fa-check"></span>
                              </label>
                              <label class="btn btn-primary">
                                <input type="checkbox" id="diaTrabalhado_Qui" name="trabQui" value="Qui"> Qui
                                <span class="fa fa-check"></span>
                              </label>
                              <label class="btn btn-primary">
                                <input type="checkbox" id="diaTrabalhado_Sex" name="trabSex" value="Sex"> Sex
                                <span class="fa fa-check"></span>
                              </label>
                              <label class="btn btn-primary">
                                <input type="checkbox" id="diaTrabalhado_Sab" name="trabSab" value="Sab"> Sab
                                <span class="fa fa-check"></span>
                              </label>
                              <label class="btn btn-primary">
                                <input type="checkbox" id="diaTrabalhado_Dom" name="trabDom" value="Dom"> Dom
                                <span class="fa fa-check"></span>
                              </label>
                              <label class="btn btn-primary">
                                <input type="checkbox" id="diaTrabalhado_Plantão" name="plantao" value="Plantão"> Plantão 12/36
                                <span class="fa fa-check"></span>
                              </label>
                            </div>
                          </div>
                        </div>
                        <div class="text-center">
                          <h3 class="col-md-12">Dias de Folga</h3>
                          <div class="btn-group ">
                            <label class="btn btn-primary ">
                              <input type="checkbox" id="diaFolga_Seg" name="folgaSeg" value="Seg">Seg
                              <span class="fa fa-check"></span>
                            </label>
                            <label class="btn btn-primary">
                              <input type="checkbox" id="diaFolga_Ter" name="folgaTer" value="Ter"> Ter
                              <span class="fa fa-check"></span>
                            </label>
                            <label class="btn btn-primary">
                              <input type="checkbox" id="diaFolga_Qua" name="folgaQua" value="Qua"> Qua
                              <span class="fa fa-check"></span>
                            </label>
                            <label class="btn btn-primary">
                              <input type="checkbox" id="diaFolga_Qui" name="folgaQui" value="Qui"> Qui
                              <span class="fa fa-check"></span>
                            </label>
                            <label class="btn btn-primary">
                              <input type="checkbox" id="diaFolga_Sex" name="folgaSex" value="Sex"> Sex
                              <span class="fa fa-check"></span>
                            </label>
                            <label class="btn btn-primary">
                              <input type="checkbox" id="diaFolga_Sab" name="folgaSab" value="Sab"> Sab
                              <span class="fa fa-check"></span>
                            </label>
                            <label class="btn btn-primary">
                              <input type="checkbox" id="diaFolga_Dom" name="folgaDom" value="Dom"> Dom
                              <span class="fa fa-check"></span>
                            </label>
                            <label class="btn btn-primary">
                              <input type="checkbox" id="diaTrabalhado" name="folgaAlternado" value="Alternado"> Alternado
                              <span class="fa fa-check"></span>
                            </label>
                          </div>
                        </div>
                        <div class="">
                          <h3 class="text-center col-md-12">Carga Horária</h3>
                          <ul class="nav nav-children" id="info">
                            <li id="total"> Carga horária diária:</br></li>
                            <li id="carga_horaria_mensal">Carga horária mensal:</li>
                          </ul>
                        </div>
                        <hr class="dotted short">
                        <input type="hidden" name="nomeClasse" value="FuncionarioControle">
                        <input type="hidden" name="metodo" value="alterarCargaHoraria">
                        <input type="hidden" name="id_funcionario" value=<?= $idFuncionario ?>>
                        <div class="form-group center">
                          <button type="button" class="btn btn-primary" id="botaoEditar_editar_cargaHoraria" onclick="switchForm('editar_cargaHoraria')">Editar</button>
                          <input id="enviarCarga" type="submit" class="btn btn-primary" value="Alterar carga">
                          <input type="reset" class="btn btn-default">
                        </div>
                      </form>
                    </div>
                  </section>
                </div>
                <!-- Aba de documentos do funcionário -->
                <div id="documentos" class="tab-pane">
                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Documentos</h2>
                    </header>
                    <div class="panel-body">
                      <!--Documentação-->
                      <hr class="dotted short">
                      <form class="form-horizontal" method="post" action="../../controle/control.php" id="formAlterarDocumentacao">
                        <input type="hidden" name="nomeClasse" value="FuncionarioControle">
                        <input type="hidden" name="metodo" value="alterarDocumentacao">
                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileCompany">Número do RG</label>
                          <div class="col-md-6">
                            <input type="text" class="form-control" name="rg" id="rg" onkeypress="return Onlynumbers(event)" placeholder="Ex: 22.222.222-2" onkeyup="mascara('##.###.###-#',this,event)">
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileCompany">Órgão Emissor</label>
                          <div class="col-md-6">
                            <input type="text" class="form-control" name="orgao_emissor" id="orgao_emissor" onkeypress="return Onlychars(event)">
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileCompany">Data de expedição</label>
                          <div class="col-md-6">
                            <input type="date" class="form-control" maxlength="10" placeholder="dd/mm/aaaa" name="data_expedicao" id="data_expedicao" max=<?php echo date('Y-m-d'); ?>>
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileCompany">Número do CPF</label>
                          <div class="col-md-6">
                            <input type="text" class="form-control" id="cpf" name="cpf" placeholder="Ex: 222.222.222-22" maxlength="14" onblur="validarCPF(this.value, 'enviarEditar')" onkeypress="return Onlynumbers(event)" onkeyup="mascara('###.###.###-##',this,event)">
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileCompany"></label>
                          <div class="col-md-6">
                            <p class="cpfInvalido" style="display: none; color: #b30000">CPF INVÁLIDO!</p>
                          </div>
                        </div>
                        <input type="hidden" id="id_funcionario" name="id_funcionario" value=<?= $idFuncionario ?>>
                        <button type="button" class="btn btn-primary" id="botaoEditarDocumentacao" onclick="return editar_documentacao()">Editar</button>
                        <input id="botaoSalvarDocumentacao" type="submit" class="btn btn-primary" disabled="true" value="Salvar" >
                      </form>
                    </div>
                  </section>
                </div>
                <!-- Aba de arquivos -->
                <div id="arquivo" class="tab-pane">
                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Arquivos</h2>
                    </header>
                    <div class="panel-body">
                      <table class="table table-bordered table-striped mb-none" id="datatable-docfuncional">
                        <thead>
                          <tr>
                            <th>Arquivo</th>
                            <th>Data</th>
                            <th>Ação</th>
                          </tr>
                        </thead>
                        <tbody id="doc-tab"></tbody>
                      </table><br>
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
                                    <select name="id_docfuncional" id="id_docfuncional" class="custom-select my-1 mr-sm-2" id="tipoDocumento" required>
                                      <option value="" selected disabled>Selecionar...</option>
                                      <?php
                                      foreach ($pdo->query("SELECT * FROM funcionario_docfuncional ORDER BY nome_docfuncional ASC;")->fetchAll(PDO::FETCH_ASSOC) as $item) {
                                        echo ("<option value='" . htmlspecialchars($item["id_docfuncional"]) . "' >" . htmlspecialchars($item["nome_docfuncional"]) . "</option>");
                                      }
                                      ?>
                                    </select>
                                    <a onclick="adicionarDocFuncional()" style="margin: 0 20px;"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                                  </div>
                                </div>
                                <div class="form-group">
                                  <label for="arquivoDocumento">Arquivo</label>
                                  <input name="arquivo" type="file" class="form-control-file" id="arquivoDocumento" accept="png;jpeg;jpg;pdf;docx;doc;odp" required>
                                </div>
                                <input type="number" name="id_funcionario" value="<?= $idFuncionario ?>" style='display: none;'>
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
                  </section>
                </div>
                <!-- Aba dependentes -->
                <div id="dependentes" class="tab-pane">
                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Dependentes</h2>
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
                        <tbody id="dep-tab"></tbody>
                      </table><br>
                      <!-- Button trigger modal -->
                      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#depFormModal">
                        Adicionar Dependente
                      </button>
                    </div>

                    <!-- Modal Form Dependentes to Cpf -->
                    <div class="modal fade" id="depFormModal" tabindex="-1" role="dialog" aria-labelledby="depFormModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header" style="display: flex;justify-content: space-between;">
                            <h5 class="modal-title" id="exampleModalLabel">Adicionar Dependente</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <!-- Ask for CPF -->
                          <form action='dependente_cadastrar.php' method='post' id='funcionarioDepForm'>
                            <div class="modal-body" style="padding: 15px 40px">
                              <div class="form-group" style="display: grid;">
                                <div class="form-group">
                                  <label class="col-md-3 control-label" for="cpf">CPF<sup class="obrig">*</sup></label>
                                  <div class="col-md-6">
                                    <input type="text" class="form-control" id="cpf" id="cpf" name="cpf" placeholder="Ex: 222.222.222-22" maxlength="14" onblur="validarCPF(this.value, 'enviarDependente')" onkeypress="return Onlynumbers(event)" onkeyup="mascara('###.###.###-##',this,event)" required>
                                  </div>
                                </div>
                                <div class="form-group">
                                  <label class="col-md-3 control-label" for="profileCompany"></label>
                                  <div class="col-md-6">
                                    <p class="cpfInvalido" style="display: none; color: #b30000">CPF INVÁLIDO!</p>
                                  </div>
                                </div>
                                <div class="form-group">
                                  <label class="col-md-3 control-label" for="parentesco">Parentesco<sup class="obrig">*</sup></label>
                                  <div class="col-md-6" style="display: flex;">
                                    <select name="id_parentesco" id="parentesco">
                                      <option selected disabled>Selecionar...</option>
                                      <?php
                                      foreach ($pdo->query("SELECT * FROM funcionario_dependente_parentesco ORDER BY descricao ASC;")->fetchAll(PDO::FETCH_ASSOC) as $item) {
                                        echo ("<option value='" . $item["id_parentesco"] . "' >" . htmlspecialchars($item["descricao"]) . "</option>");
                                      }
                                      ?>
                                    </select>
                                    <a onclick="adicionarParentesco()" style="margin: 0 20px;"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                                  </div>
                                </div>
                                <input type="hidden" name="id_funcionario" value=<?= $idFuncionario ?> readonly>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                  <input id="enviarDependente" type="submit" value="Enviar" class="btn btn-primary">
                                </div>
                              </div>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>

                    <!-- <div class="modal fade" id="newDepFormModal" tabindex="-1" role="dialog" aria-labelledby="depFormModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header" style="display: flex;justify-content: space-between;">
                            <h5 class="modal-title" id="exampleModalLabel">Adicionar Dependente</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <form action='dependente_cadastrar.php' method='post' id='funcionarioDepForm'>
                            <div class="modal-body" style="padding: 15px 40px">
                              <div class="form-group" style="display: grid;">
                                <h4 class="mb-xlg">Informações Pessoais</h4>
                                <h5 class="obrig">Campos Obrigatórios(*)</h5>
                                <div class="form-group">
                                  <label class="col-md-3 control-label" for="profileFirstName">Nome<sup class="obrig">*</sup></label>
                                  <div class="col-md-8">
                                    <input type="text" class="form-control" name="nome" id="profileFirstName" id="nome" onkeypress="return Onlychars(event)" required>
                                  </div>
                                </div>
                                <div class="form-group">
                                  <label class="col-md-3 control-label">Sobrenome<sup class="obrig">*</sup></label>
                                  <div class="col-md-8">
                                    <input type="text" class="form-control" name="sobrenome" id="sobrenome" onkeypress="return Onlychars(event)" required>
                                  </div>
                                </div>
                                <div class="form-group">
                                  <label class="col-md-3 control-label" for="profileLastName">Sexo<sup class="obrig">*</sup></label>
                                  <div class="col-md-8">
                                    <label><input type="radio" name="sexo" id="radio" id="M" value="m" style="margin-top: 10px; margin-left: 15px;" onclick="return exibir_reservista()" required><i class="fa fa-male" style="font-size: 20px;"></i></label>
                                    <label><input type="radio" name="sexo" id="radio" id="F" value="f" style="margin-top: 10px; margin-left: 15px;" onclick="return esconder_reservista()"><i class="fa fa-female" style="font-size: 20px;"></i> </label>
                                  </div>
                                </div>
                                <div class="form-group">
                                  <label class="col-md-3 control-label" for="telefone">Telefone</label>
                                  <div class="col-md-8">
                                    <input type="text" class="form-control" maxlength="14" minlength="14" name="telefone" id="telefone" id="telefone" placeholder="Ex: (22)99999-9999" onkeypress="return Onlynumbers(event)" onkeyup="mascara('(##)#####-####',this,event)">
                                  </div>
                                </div>
                                <div class="form-group">
                                  <label class="col-md-3 control-label" for="profileCompany">Nascimento<sup class="obrig">*</sup></label>
                                  <div class="col-md-8">
                                    <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="nascimento" id="nascimento" max=<?php echo date('Y-m-d'); ?> required>
                                  </div>
                                </div>
                                <hr class="dotted short">
                                <h4 class="mb-xlg doch4">Documentação</h4>
                                <div class="form-group">
                                  <label class="col-md-3 control-label" for="cpf">Número do CPF<sup class="obrig">*</sup></label>
                                  <div class="col-md-6">
                                    <input type="text" class="form-control" id="cpf" id="cpf" name="cpf" placeholder="Ex: 222.222.222-22" maxlength="14" onblur="validarCPF(this.value)" onkeypress="return Onlynumbers(event)" onkeyup="mascara('###.###.###-##',this,event)" required>
                                  </div>
                                </div>
                                <div class="form-group">
                                  <label class="col-md-3 control-label" for="profileCompany"></label>
                                  <div class="col-md-6">
                                    <p id="cpfInvalido" style="display: none; color: #b30000">CPF INVÁLIDO!</p>
                                  </div>
                                </div>
                                <div class="form-group">
                                  <label class="col-md-3 control-label" for="parentesco">Parentesco<sup class="obrig">*</sup></label>
                                  <div class="col-md-6" style="display: flex;">
                                    <select name="id_parentesco" id="parentesco">
                                      <option selected disabled>Selecionar...</option>
                                      <?php
                                      foreach ($pdo->query("SELECT * FROM funcionario_dependente_parentesco ORDER BY descricao ASC;")->fetchAll(PDO::FETCH_ASSOC) as $item) {
                                        echo ("<option value='" . $item["id_parentesco"] . "' >" . htmlspecialchars($item["descricao"]) . "</option>");
                                      }
                                      ?>
                                    </select>
                                    <a onclick="adicionarParentesco()" style="margin: 0 20px;"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                                  </div>
                                </div>
                                <div class="form-group">
                                  <label class="col-md-3 control-label" for="profileCompany">Número do RG</label>
                                  <div class="col-md-6">
                                    <input type="text" class="form-control" name="rg" id="rg" onkeypress="return Onlynumbers(event)" placeholder="Ex: 22.222.222-2" onkeyup="mascara('##.###.###-#',this,event)">
                                  </div>
                                </div>
                                <div class="form-group">
                                  <label class="col-md-3 control-label" for="profileCompany">Órgão Emissor</label>
                                  <div class="col-md-6">
                                    <input type="text" class="form-control" name="orgao_emissor" id="profileCompany" id="orgao_emissor" onkeypress="return Onlychars(event)">
                                  </div>
                                </div>
                                <div class="form-group">
                                  <label class="col-md-3 control-label" for="profileCompany">Data de expedição</label>
                                  <div class="col-md-6">
                                    <input type="date" class="form-control" maxlength="10" placeholder="dd/mm/aaaa" id="profileCompany" name="data_expedicao" id="data_expedicao" max=<?php //echo date('Y-m-d'); 
                                                                                                                                                                                      ?>>
                                  </div>
                                </div>
                                <input type="hidden" name="id_funcionario" value=<?php //echo $idFuncionario
                                                                                  ?> readonly>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                  <input type="submit" value="Enviar" class="btn btn-primary">
                                </div>
                              </div>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div> -->
                  </section>
                </div>
                <!-- Aba endereço -->
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
                      <form class="form-horizontal" method="post" action="../../controle/control.php" id="formAlterarEndereco">
                        <input type="hidden" name="nomeClasse" value="FuncionarioControle">
                        <input type="hidden" name="metodo" value="alterarEndereco">
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
                          <input type="hidden" name="id_funcionario" value=<?= $idFuncionario ?>>
                          <button type="button" class="btn btn-primary" id="botaoEditarEndereco" onclick="return editar_endereco()">Editar</button>
                          <input id="botaoSalvarEndereco" type="submit" class="btn btn-primary" disabled="true" value="Salvar">
                        </div>
                    </div>
                    </form>
                </div>
      </section>
    </div>
    <!-- end: page -->
    </div>
    </div>
    </div>
  </section>
  </div>
  </section>
  <!-- Vendor -->
  <script src="../../assets/vendor/select2/select2.js"></script>
  <script src="../../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
  <script src="../../assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
  <script src="../../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
  <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
  <!-- Theme Base, Components and Settings -->
  <script src="../../assets/javascripts/theme.js"></script>
  <!-- Theme Custom -->
  <script src="../../assets/javascripts/theme.custom.js"></script>
  <!-- Metodo Post -->
  <script src="../geral/post.js"></script>
  <!-- Theme Initialization Files -->
  <script src="../../assets/javascripts/theme.init.js"></script>
  <!-- Examples -->
  <script src="../../assets/javascripts/tables/examples.datatables.default.js"></script>
  <script src="../../assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
  <script src="../../assets/javascripts/tables/examples.datatables.tabletools.js"></script>
  <script>
    function submitForm(idForm, callback = function() {
      return true;
    }) {
      var data = getFormPostParams(idForm);
      console.log(data);
      var url;
      var data_nova;
      switch (idForm) {
        case "formRemuneracao":
          url = "remuneracao.php";
          data_nova = "id_tipo=" + data[0] + "&valor=" + data[1] + "&inicio=" + data[2] + "&fim=" + data[3] + "&action=remuneracao_adicionar&id_funcionario=" + data[4];
          break;
        case "formInfoAdicional":
          url = "informacao_adicional.php";
          data_nova = "id_descricao=" + data[0] + "&dados=" + data[1] + "&action=adicionar&id_funcionario=" + data[3];
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
      post(url, data_nova, callback);
      console.log(idForm + " => " + data + " | ", callback);
      return true;
    }

    function listar_remuneracao(lista) {
      $("#tabela_remuneracao").empty();
      $.each(lista, function(i, item) {
        $("#tabela_remuneracao")
          .append($("<tr>")
            .append($("<td>").text(item.descricao))
            .append($("<td>").text(item.inicio))
            .append($("<td>").text(item.fim))
            .append($("<td class='tabela'>").text(item.valor))
            .append($("<td style='display: flex; justify-content: space-evenly;'>")
              .append($("<button onclick='removerRemuneracao(" + item.id_remuneracao + ")' title='Excluir' class='btn btn-danger'><i class='fas fa-trash-alt'></i></button>"))
            )
          )
        $(function() {
          var total = 0;
          $('.tabela').each(function() {
            total += parseInt(jQuery(this).text());
          });
          $('.total').html(total);
        });
      });
    }

    function adicionarRemuneracao() {
      if (submitForm('formRemuneracao', listar_remuneracao)) {
        document.getElementById("closeRemuneracaoModal").click();
      }
    }

    function adicionarTipoRemuneracao() {
      url = 'remuneracao.php';
      var descricao = window.prompt("Cadastre um novo tipo de Remuneração:");
      if (!descricao) {
        return
      }
      descricao = descricao.trim();
      if (descricao == '') {
        return
      }
      data = "action=tipo_adicionar&descricao=" + descricao;
      console.log(url + "?" + data);
      post(url, data, gerarTipoRemuneracao);
    }

    function removerRemuneracao(id) {
      var url = "remuneracao.php";
      var data = "action=remover&id_remuneracao=" + id + "&id_funcionario=<?= $idFuncionario ?>";
      post(url, data, listar_remuneracao);
    }

    function gerarTipoRemuneracao(response) {
      var documento = response;
      if (response["aviso"] || response["errorInfo"]) {
        return false;
      }
      $('#tipo_remuneracao').empty();
      $('#tipo_remuneracao').append('<option selected disabled>Selecionar</option>');
      $.each(documento, function(i, item) {
        $('#tipo_remuneracao').append('<option value="' + item.id + '">' + item.descricao + '</option>');
      });
    }
    $(function() {
      post("remuneracao.php", "action=listar&id_funcionario=<?= $idFuncionario ?>", listar_remuneracao);
    })

    function funcao3(){
      var idfunc = <?= $idFuncionario ?>;
      var cpfs = <?php echo $_SESSION['cpf_funcionario']; ?>;
      var cpf_funcionario = $("#cpf").val();
      var cpf_funcionario_correto = cpf_funcionario.replace(".", "");
      var cpf_funcionario_correto1 = cpf_funcionario_correto.replace(".", "");
      var cpf_funcionario_correto2 = cpf_funcionario_correto1.replace(".", "");
      var cpf_funcionario_correto3 = cpf_funcionario_correto2.replace("-", "");
      var apoio = 0;
      var cpfs1 = <?php echo $_SESSION['cpf_atendido']; ?>;
      $.each(cpfs, function(i, item) {
        if (item.cpf == cpf_funcionario_correto3 && item.id != idfunc) {
          alert("Alteração não realizada! O CPF informado já está cadastrado no sistema");
          apoio = 1;
        }
      });
      $.each(cpfs1, function(i, item) {
        if (item.cpf == cpf_funcionario_correto3) {
          alert("Cadastro não realizado! O CPF informado já está cadastrado no sistema");
          apoio = 1;
        }
      });

      const data_nasc = new Date($('#nascimento').val());
      const data_exp = new Date($('#data_expedicao').val());
      if(data_exp < data_nasc){
        alert("Edição não efetuada. A data de expedição não pode ser anterior à de nascimento");
        apoio = 1;
      }
      
      if (apoio == 0) {
        alert("Editado com sucesso!");
        return true;
      }
      return false;
    }

    $('#formAlterarDocumentacao').on('submit', function(e){
      if(!funcao3()){
        e.preventDefault();
      }
    });
    $('#formAlterarEndereco').on('submit', function(e){
      if(!funcao3()){
        e.preventDefault();
      }
    });

    function gerarDocFuncional() {
      url = 'documento_listar.php';
      $.ajax({
        data: '',
        type: "POST",
        url: url,
        async: true,
        success: function(response) {
          var documento = response;
          $('#id_docfuncional').empty();
          $('#id_docfuncional').append('<option selected disabled>Selecionar...</option>');
          $.each(documento, function(i, item) {
            $('#id_docfuncional').append('<option value="' + item.id_docfuncional + '">' + item.nome_docfuncional + '</option>');
          });
        },
        dataType: 'json'
      });
    }

    function adicionarDocFuncional() {
      url = 'documento_adicionar.php';
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

    function removerDependente(id_dep) {
      let url = "dependente_remover.php";
      let data = "id_funcionario=<?= $idFuncionario ?>&id_dependente=" + id_dep;
      post(url, data, verificaSucesso);
    }

    function removerFuncionarioDocs(id_doc) {
      if (!window.confirm("Tem certeza que deseja remover esse documento?")) {
        return false;
      }
      let url = "documento_excluir.php?id_doc=" + id_doc + "&id_funcionario=<?= $idFuncionario ?>";
      let data = "";
      post(url, data, listarFunDocs);
    }
  </script>
  <!-- JavaScript Custom -->
  <script src="../geral/post.js"></script>
  <script src="../geral/formulario.js"></script>
  <script>
    var formState = [];

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
      } else {
        formState[idForm] = true;
        enableForm(idForm);
      }
      switchButton(idForm);
    }
    switchForm("editar_cargaHoraria", false)
  </script>
  <div align="right">
    <iframe src="https://www.wegia.org/software/footer/pessoa.html" width="200" height="60" style="border:none;"></iframe>
  </div>
</body>

</html>