<?php

include_once("conexao.php");
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
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


$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$situacao = $mysqli->query("SELECT * FROM situacao");
$cargo = $mysqli->query("SELECT * FROM cargo");
$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$id_pessoa = $_SESSION['id_pessoa'];
$resultado = mysqli_query($conexao, "SELECT * FROM funcionario WHERE id_pessoa=$id_pessoa");
if (!is_null($resultado)) {
  $id_cargo = mysqli_fetch_array($resultado);
  if (!is_null($id_cargo)) {
    $id_cargo = $id_cargo['id_cargo'];
  }
  $resultado = mysqli_query($conexao, "SELECT * FROM permissao WHERE id_cargo=$id_cargo and id_recurso=11");
  if (!is_bool($resultado) and mysqli_num_rows($resultado)) {
    $permissao = mysqli_fetch_array($resultado);
    if ($permissao['id_acao'] < 3) {
      $msg = "Você não tem as permissões necessárias para essa página.";
      header("Location: ../home.php?msg_c=$msg");
    }
    $permissao = $permissao['id_acao'];
  } else {
    $permissao = 1;
    $msg = "Você não tem as permissões necessárias para essa página.";
    header("Location: ../home.php?msg_c=$msg");
  }
} else {
  $permissao = 1;
  $msg = "Você não tem as permissões necessárias para essa página.";
  header("Location: ../home.php?msg_c=$msg");
}

$idFuncionario = filter_input(INPUT_GET, 'id_funcionario', FILTER_VALIDATE_INT);
if(!$idFuncionario){
  echo json_encode(['erro' => 'O id do funcionário informado não é válido']);
  exit(400);
}

require_once ROOT . "/controle/FuncionarioControle.php";
$listaCPF = new FuncionarioControle;
$listaCPF->listarCpf();

require_once ROOT . "/controle/AtendidoControle.php";
$listaCPF2 = new AtendidoControle;
$listaCPF2->listarCpf();

// Inclui display de Campos
require_once "../personalizacao_display.php";

require_once "../../dao/Conexao.php";
$pdo = Conexao::connect();


$cpfDigitado = $_SESSION['cpf_digitado'];
$parentescoPrevio = $_SESSION['parentesco_previo'];

?>
<!DOCTYPE html>
<html class="fixed">

<head>
  
  <!-- Basic -->
  <meta charset="UTF-8">
  <title>Cadastro de Funcionário</title>
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

  <!-- Web Fonts  -->
  <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

  <!-- Vendor CSS -->
  <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
  <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
  <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
  <link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
  <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon">

  <!-- Theme CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />

  <!-- Skin CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />

  <!-- Theme Custom CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
  <script src="../../assets/vendor/jquery/jquery.min.js"></script>

</head>

<body>
  <!-- start: header -->
  <div id="header"></div>
  <!-- end: header -->
  <div class="inner-wrapper">
    <!-- start: sidebar -->
    <aside id="sidebar-left" class="sidebar-left menuu"></aside>

    <section role="main" class="content-body">
      <header class="page-header">
        <h2>Cadastro</h2>
        <div class="right-wrapper pull-right">
          <ol class="breadcrumbs">
            <li>
              <a href="../home.php">
                <i class="fa fa-home"></i>
              </a>
            </li>
            <li><span>Cadastros</span></li>
            <li><span>Dependentes</span></li>
          </ol>
          <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
        </div>
      </header>

      <div class="col-md-8 col-lg-12">
        <div class="tabs">
          <ul class="nav nav-tabs tabs-primary">
            <li class="active">
              <a href="#overview" data-toggle="tab">Cadastro de Dependente</a>
            </li>
          </ul>
          <div class="tab-content">
            <div id="overview" class="tab-pane active">
              <form class="form-horizontal" method="POST" action="./dependente_cadastrar_pessoa_nova.php">
                <h4 class="mb-xlg">Informações Pessoais</h4>
                <h5 class="obrig">Campos Obrigatórios(*)</h5>
                <div class="form-group">
                  <label class="col-md-3 control-label" for="profileFirstName">Nome<sup class="obrig">*</sup></label>
                  <div class="col-md-6">
                    <input type="text" class="form-control" name="nome" id="nome" onkeypress="return Onlychars(event)">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-md-3 control-label">Sobrenome<sup class="obrig">*</sup></label>
                  <div class="col-md-6">
                    <input type="text" class="form-control" name="sobrenome" id="sobrenome" onkeypress="return Onlychars(event)">
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
                  <label class="col-md-3 control-label" for="telefone">Telefone<sup class="obrig">*</sup></label>
                  <div class="col-md-6">
                    <input type="text" class="form-control" maxlength="14" minlength="14" name="telefone" id="telefone" placeholder="Ex: (22)99999-9999" onkeypress="return Onlynumbers(event)" onkeyup="mascara('(##)#####-####',this,event)">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-md-3 control-label" for="profileCompany">Nascimento<sup class="obrig">*</sup></label>
                  <div class="col-md-6">
                    <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="nascimento" id="nascimento" max=<?php echo date('Y-m-d'); ?>>
                  </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label" for="parentesco">Parentesco<sup class="obrig">*</sup></label>
                    <div class="col-md-6">
                        <select name="id_parentesco" id="parentesco">
                            <?php
                                foreach ($pdo->query("SELECT * FROM funcionario_dependente_parentesco ORDER BY descricao ASC;")->fetchAll(PDO::FETCH_ASSOC) as $item) {
                                    if($item["id_parentesco"] == $parentescoPrevio) {
                                        echo("<option value='" . $item["id_parentesco"] . "' selected>" . htmlspecialchars($item["descricao"]) . "</option>");
                                    }
                                    else {
                                        echo("<option value='" . $item["id_parentesco"] . "' >" . htmlspecialchars($item["descricao"]) . "</option>");
                                    }
                                }
                            ?>
                        </select>
                        <a onclick="adicionarParentesco()" style="margin: 0 20px;"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                    </div>
                </div>
                <hr class="dotted short">
                <h4 class="mb-xlg doch4">Documentação</h4>
                <div class="form-group">
                  <label class="col-md-3 control-label" for="cpf">Número do CPF<sup class="obrig">*</sup></label>
                  <div class="col-md-6">
                    <input type="text" class="form-control" id="cpf" name="cpf" placeholder="Ex: 222.222.222-22" maxlength="14" onblur="validarCPF(this.value)" onkeypress="return Onlynumbers(event)" onkeyup="mascara('###.###.###-##',this,event)" value="<?php echo $cpfDigitado; ?>" readonly>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-md-3 control-label" for="profileCompany">Número do RG<sup class="obrig">*</sup></label>
                  <div class="col-md-6">
                    <input type="text" class="form-control" name="rg" id="rg" onkeypress="return Onlynumbers(event)" placeholder="Ex: 22.222.222-2" onkeyup="mascara('##.###.###-#',this,event)" required>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-md-3 control-label" for="profileCompany">Órgão Emissor<sup class="obrig">*</sup></label>
                  <div class="col-md-6">
                    <input type="text" class="form-control" name="orgao_emissor" id="orgao_emissor" onkeypress="return Onlychars(event)" required>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-md-3 control-label" for="profileCompany">Data de expedição<sup class="obrig">*</sup></label>
                  <div class="col-md-6">
                    <input type="date" class="form-control" maxlength="10" placeholder="dd/mm/aaaa" name="data_expedicao" id="data_expedicao" max=<?php echo date('Y-m-d'); ?> required disabled>
                    <p id="dataNascInvalida" style="display: block; color: #b30000">Selecione a data de nascimento primeiro!</p>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-md-3 control-label" for="profileCompany"></label>
                  <div class="col-md-6">
                    <p id="cpfInvalido" style="display: none; color: #b30000">CPF INVÁLIDO!</p>
                  </div>
                </div>
                <input type="hidden" name="id_funcionario" value=<?= $idFuncionario?> readonly>
                
                <div class="modal-footer">
                    <input type="reset" class="btn btn-default">
                    <input type="submit" value="Enviar" class="btn btn-primary">
                </div>

              </form>
            </div>
          </div>
              <!-- end: page -->
    </section>
  </div>
  </section>
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

  <!-- JQuery Online -->
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

  <!-- JQuery Local -->
  <script src="../../assets/vendor/jquery/jquery.min.js"></script>
  <script src="https://requirejs.org/docs/release/2.3.6/r.js"></script>
  <style type="text/css">
    .btn span.fa-check {
      opacity: 0;
    }

    .btn.active span.fa-check {
      opacity: 1;
    }

    .obrig {
      color: rgb(255, 0, 0);
    }
  </style>
  <script type="text/javascript">
    function funcao1() {
      var send = $("#enviar");
      var cpfs = <?php echo $_SESSION['cpf_funcionario']; ?>;
      var cpf_funcionario = $("#cpf").val();
      var cpf_funcionario_correto = cpf_funcionario.replace(".", "");
      var cpf_funcionario_correto1 = cpf_funcionario_correto.replace(".", "");
      var cpf_funcionario_correto2 = cpf_funcionario_correto1.replace(".", "");
      var cpf_funcionario_correto3 = cpf_funcionario_correto2.replace("-", "");
      var apoio = 0;
      //var cpfs1 = <?php echo $_SESSION['cpf_interno']; ?>;
      $.each(cpfs, function(i, item) {
        if (item.cpf == cpf_funcionario_correto3) {
          alert("Cadastro não realizado! O CPF informado já está cadastrado no sistema");
          apoio = 1;
          send.attr('disabled', 'disabled');
        }
      });

      if (apoio == 0) {
        alert("Cadastrado com sucesso!");
      }
    }

    function validarFuncionario() {
      var btn = $("#enviar");
      var cpf_cadastrado = (<?php echo $_SESSION['cpf_funcionario']; ?>).concat(<?php echo $_SESSION['cpf_interno']; ?>);
      var cpf_cadastrado = (<?php echo $_SESSION['cpf_funcionario']; ?>);
      var cpf = (($("#cpf").val()).replaceAll(".", "")).replaceAll("-", "");
      console.log(this);
      $.each(cpf_cadastrado, function(i, item) {
        if (item.cpf == cpf) {
          alert("Cadastro não realizado! O CPF informado já está cadastrado no sistema");
          btn.attr('disabled', 'disabled');
          return false;
        }
      });

      var sexo = document.querySelector('input[name="gender"]:checked').value;

      var rg = document.getElementById('rg').value;

      var orgao_emissor = document.getElementById('orgao_emissor').value;

      var dt_expedicao = document.getElementById('data_expedicao').value;

      var dt_admissao = document.getElementById('data_admissao').value;

      var a = document.getElementById('situacao');
      var situacao = a.options[a.selectedIndex].text;

      var b = document.getElementById('cargo');
      var cargo = b.options[b.selectedIndex].text;

      var c = document.getElementById('escala_input');
      var escala = c.options[c.selectedIndex].text;

      var d = document.getElementById('tipoCargaHoraria_input');
      var tipo = d.options[d.selectedIndex].text;

      const data_nascimento = document.querySelector("#nascimento").value;
      if(dt_expedicao < data_nascimento){
        return 0;
      }

      if (sexo && rg && orgao_emissor && dt_expedicao && dt_admissao && situacao && cargo && escala && tipo) {
        alert("Cadastrado com sucesso!");
      }
    }

    function numero_residencial() {

      if ($("#numResidencial").prop('checked')) {

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

      $('.num_reservista').val("");
      $('.serie_reservista').val("");

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

    function validarCPF(strCPF) {

      if (!testaCPF(strCPF)) {
        $('#cpfInvalido').show();
        document.getElementById("enviar").disabled = true;

      } else {
        $('#cpfInvalido').hide();

        document.getElementById("enviar").disabled = false;
      }
    }

    function adicionarParentesco() {
      url = 'dependente_parentesco_adicionar.php';
      var descricao = window.prompt("Cadastre um novo tipo de Parentesco:");
      if (!descricao) {
        return
      }
      descricao = descricao.trim();
      if (descricao == '') {
        return
      }
      data = 'descricao=' + descricao;
      $.ajax({
        type: "POST",
        url: url,
        data: data,
        success: function(response) {
          gerarParentesco();
        },
        dataType: 'text'
      })
    }

    // Delimitando a data mínima para a data de expedição da identidade por meio da data de nascimento
    $("#nascimento").on('change', function () {
      if($(this).val()){
        $('#data_expedicao').prop('disabled', false);
        $('#dataNascInvalida').hide();
        const nascimento = new  Date( $(this).val() );
        const dataMinimaExpedicao = new Date(nascimento);
        dataMinimaExpedicao.setDate(dataMinimaExpedicao.getDate() + 1);

        $('#data_expedicao').attr('min', dataMinimaExpedicao.toISOString().split('T')[0]);
      }
      else{
        $('#data_expedicao').prop('disabled', true).val('');
        $('#dataNascInvalida').show();
      }
    });
    // Desabilitando o input Data de Expedição quando o formulário é resetado
    $('input[type="reset"]').on('click', function() {
      $('#data_expedicao').val('').prop('disabled', true);
      $('#data_expedicao').removeAttr('min');
    });

    $(function() {

      $("#header").load("../header.php");
      $(".menuu").load("../menu.php");
    });
  </script>
  <!-- Head Libs -->
  <script src="../../assets/vendor/modernizr/modernizr.js"></script>

  <!-- javascript functions -->
  <script src="../../Functions/onlyNumbers.js"></script>
  <script src="../../Functions/onlyChars.js"></script>
  <script src="../../Functions/mascara.js"></script>
  <script src="../../Functions/lista.js"></script>
  <script src="../../Functions/funcionario_parentesco.js"></script>
  <script src="<?php echo WWW; ?>Functions/testaCPF.js"></script>
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
  </script>
  <!-- Vendor -->
  <script src="../../assets/vendor/jquery/jquery.js"></script>
  <script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
  <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
  <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
  <script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
  <script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
  <script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

  <div align="right">
    <iframe src="https://www.wegia.org/software/footer/pessoa.html" width="200" height="60" style="border:none;"></iframe>
  </div>
</body>

</html>