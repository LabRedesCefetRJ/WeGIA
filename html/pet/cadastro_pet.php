<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

include_once("conexao.php");

if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$id_pessoa = filter_var($_SESSION['id_pessoa'], FILTER_SANITIZE_NUMBER_INT);

if ($id_pessoa < 1) {
  http_response_code(400);
  echo json_encode(['erro' => 'O id da pessoa informado é inválido']);
  exit();
}

permissao($id_pessoa, 61, 3);

require_once ROOT . "/controle/FuncionarioControle.php";
$listaCPF = new FuncionarioControle;
$listaCPF->listarCpf();

require_once ROOT . "/controle/AtendidoControle.php";
$listaCPF2 = new AtendidoControle;
$listaCPF2->listarCpf();
$cpf = $cpf = isset($_GET['cpf']) ? preg_replace('/\D/', '', $_GET['cpf']) : '';
$funcionario = new FuncionarioDAO;
$informacoesFunc = $funcionario->listarPessoaExistente($cpf);


// Inclui display de Campos
require_once "../personalizacao_display.php";

/** selecionando elementos pet */

$cor = $mysqli->query("select * from pet_cor");
$especie = $mysqli->query("select * from pet_especie");
$raca = $mysqli->query("select * from pet_raca");

/* fim */
//Pedro



if (isset($_GET['msg'])) {
  $mensagem = htmlspecialchars($_GET['msg'], ENT_QUOTES, 'UTF-8');

  // Remove espaços extras e tags HTML
  $mensagem = trim(strip_tags($mensagem));

  // Verifica se a mensagem é válida (somente letras, números e espaços permitidos)
  if (!preg_match('/^[\p{L}\p{N} ]+$/u', $mensagem)) {
    exit(); // Sai do script se a mensagem tiver caracteres suspeitos
  }

  // Escapa caracteres especiais para evitar XSS
  $mensagem = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8');

  // Transforma a mensagem em um formato seguro para JavaScript
  $mensagem = json_encode($mensagem, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

  echo <<<HTML
    <script>
        alert($mensagem);
        window.location.href = "../../html/pet/cadastro_pet.php";
    </script>
    HTML;

  require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
}




//============================
?>
<!DOCTYPE html>
<html class="fixed">

<head>
  <!-- Basic -->
  <meta charset="UTF-8">
  <title>Cadastro do Pet</title>
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
        <h2>Cadastro Pet</h2>
        <div class="right-wrapper pull-right">
          <ol class="breadcrumbs">
            <li>
              <a href="../home.php">
                <i class="fa fa-home"></i>
              </a>
            </li>
            <li><span>Pet</span></li>
            <span>Cadastro Pet</span>
          </ol>
          <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
        </div>
      </header>
      <!-- start: page -->
      <div class="row" id="formulario">
        <form class="form-horizontal" method="POST" action="../../controle/control.php" enctype="multipart/form-data" onsubmit="verificarDataAcolhimento()">
          <div class="row">
            <div class="col-md-4 col-lg-3">
              <section class="panel">
                <div class="panel-body">
                  <div class="thumb-info mb-md">
                    <!-- Pré-visualização da imagem -->
                    <input type="file" class="image_input form-control" name="imgperfil" id="imgform" accept="image/*">
                    <img id="previewImagemPet" src="#" alt="Prévia da imagem" class="rounded img-responsive" style="display:none; max-height: 200px; margin-bottom: 10px;">

                    <!-- Input para imagem -->

                  </div>
                </div>
              </section>
            </div>


            <div class="col-md-8 col-lg-8">
              <div class="tabs">
                <ul class="nav nav-tabs tabs-primary">
                  <li class="active">
                    <a href="#overview" data-toggle="tab">Cadastro dos Pets</a>
                  </li>
                </ul>
                <div class="tab-content">
                  <div id="overview" class="tab-pane active">
                    <h4 class="mb-xlg">Informações do Pet</h4>
                    <h5 class="obrig">Campos Obrigatórios(*)</h5>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="nome">Nome<sup class="obrig">*</sup></label>
                      <div class="col-md-8">
                        <input type="text" class="form-control" name="nome" id="nome" onkeypress="return Onlychars(event)" required>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="cor">Cor<sup class="obrig">*</sup></label>
                      <a onclick="adicionar_cor()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                      <div class="col-md-8">
                        <select class="form-control input-lg mb-md" name="cor" id="cor" required>
                          <option selected disabled>Selecionar</option>
                          <?php
                          while ($row = $cor->fetch_array(MYSQLI_NUM)) {
                            echo "<option value='{$row[0]}'>" . htmlspecialchars($row[1]) . "</option>";
                          }
                          ?>
                        </select>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="especie">Espécie<sup class="obrig">*</sup></label>
                      <a onclick="adicionar_especie()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                      <div class="col-md-8">
                        <select class="form-control input-lg mb-md" name="especie" id="especie" required>
                          <option selected disabled>Selecionar</option>
                          <?php
                          while ($row = $especie->fetch_array(MYSQLI_NUM)) {
                            echo "<option value='{$row[0]}'>" . htmlspecialchars($row[1]) . "</option>";
                          }
                          ?>
                        </select>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="raca">Raça<sup class="obrig">*</sup></label>
                      <a onclick="adicionar_raca()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                      <div class="col-md-8">
                        <select class="form-control input-lg mb-md" name="raca" id="raca" required>
                          <option selected disabled>Selecionar</option>
                          <?php
                          while ($row = $raca->fetch_array(MYSQLI_NUM)) {
                            echo "<option value='{$row[0]}'>" . htmlspecialchars($row[1]) . "</option>";
                          }
                          ?>
                        </select>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="gender">Sexo<sup class="obrig">*</sup></label>
                      <div class="col-md-8">
                        <label><input type="radio" name="gender" value="m" style="margin: 10px 5px 0 15px;" required><i class="fa fa-mars" style="font-size: 20px;"></i></label>
                        <label><input type="radio" name="gender" value="f" style="margin: 10px 5px 0 15px;"><i class="fa fa-venus" style="font-size: 20px;"></i></label>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="nascimento">Data de Nascimento Aproximada<sup class="obrig">*</sup></label>
                      <div class="col-md-8">
                        <input type="date" class="form-control" name="nascimento" id="nascimento" max="<?php echo date('Y-m-d'); ?>" required>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="acolhimento">Data de Acolhimento<sup class="obrig">*</sup></label>
                      <div class="col-md-8">
                        <input type="date" class="form-control" name="acolhimento" id="acolhimento" max="<?php echo date('Y-m-d'); ?>" required>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="caracEsp">Características Específicas</label>
                      <div class="col-md-8">
                        <input type="text" class="form-control" name="caracEsp" id="caracEsp">
                      </div>
                    </div>

                    <hr class="dotted short">

                    <div class="panel-footer">
                      <div class="row">
                        <div class="col-md-9 col-md-offset-3">

                          <input type="hidden" name="nomeClasse" value="PetControle">
                          <?= Csrf::inputField() ?>
                          <input type="hidden" name="modulo" value="pet">
                          <input type="hidden" name="metodo" value="incluir">
                          <input id="enviar" type="submit" class="btn btn-primary" value="Salvar" onclick="validarFuncionario()">
                          <input type="reset" class="btn btn-default">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>

        <!--<iframe name="frame"></iframe>!-->
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

    #display_image {

      min-height: 250px;
      margin: 0 auto;
      border: 1px solid black;
      background-position: center;
      background-size: cover;
      background-image: url("../../img/semfoto.png")
    }


    #display_image:after {

      content: "";
      display: block;
      padding-bottom: 100%;
    }
  </style>
  <script type="text/javascript">
    //Exibir imagem

    document.getElementById('imgform').addEventListener('change', function(event) {
      const input = event.target;
      const preview = document.getElementById('previewImagemPet');

      if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
          preview.src = e.target.result;
          preview.style.display = 'block';
        }

        reader.readAsDataURL(input.files[0]);
      } else {
        preview.src = "#";
        preview.style.display = "none";
      }
    });


    //Pedro
    /** Aqui começa a implementação das funções relacionada a "PET" */
    // funções relacionadas a datas
    function verificarDataAcolhimento() {
      let nasc = document.querySelector("#nascimento").value;
      let acol = document.querySelector("#acolhimento").value;

      nasc = nasc.split('-').join('');
      acol = acol.split('-').join('');

      let anoLimite = 1990;

      if (parseInt(nasc.substring(0, 4)) < anoLimite) {
        alert("Ano de nascimento não pode ser anterior ao ano limite de " + anoLimite + "!");
        event.preventDefault();
        return;
      }

      if (acol < nasc) {
        alert("Data de acolhimento não pode ser anterior a data de nascimento!");
        event.preventDefault();
      }
    }

    /*
    function noType(){
      event.preventDefault();
    }
    */
    /** Função adicionar_raca */
    function gerarRaca() {
      url = '../../dao/pet/exibir_raca.php';
      $.ajax({
        data: '',
        type: "POST",
        url: url,
        success: function(response) {
          var raca = response;
          $('#raca').empty();
          $('#raca').append('<option selected disabled>Selecionar</option>');
          $.each(raca, function(i, item) {
            $('#raca').append('<option value="' + item.id_raca + '">' + item.raca + '</option>');
          });
        },
        dataType: 'json'
      });

    }

    function adicionar_raca() {
      url = '../../dao/pet/adicionar_raca.php';
      var raca = window.prompt("Cadastre uma Nova Raça:");
      if (!raca) {
        return
      }
      situacao = raca.trim();
      if (raca == '') {
        return
      }
      //=============================
      let r = raca.replace(/[A-ZÁÉÍÓÚáéíóúâêîôûàèìòùÃÕãõÇç ]/gi, '');
      r = r.replaceAll('-', '');

      if (r.length != 0) {
        alert("Caracteres inválidos encontrados. Tente novamente.");
        adicionar_raca();
        return;
      }
      //=============================
      data = 'raca=' + raca;
    
      $.ajax({
        type: "POST",
        url: url,
        data: data,
        success: function(response) {
          gerarRaca();
        },
        dataType: 'text'
      })
    }

    /** Função adicionar_especie */

    function gerarEspecie() {
      url = '../../dao/pet/exibir_especie.php';
      $.ajax({
        data: '',
        type: "POST",
        url: url,
        success: function(response) {
          var especie = response;
          $('#especie').empty();
          $('#especie').append('<option selected disabled>Selecionar</option>');
          $.each(especie, function(i, item) {
            $('#especie').append('<option value="' + item.id_especie + '">' + item.especie + '</option>');
          });
        },
        dataType: 'json'
      });

    }

    function adicionar_especie() {
      url = '../../dao/pet/adicionar_especie.php';
      var especie = window.prompt("Cadastre uma Nova Espécie:");
      if (!especie) {
        return
      }
      situacao = especie.trim();
      if (especie == '') {
        return
      }
      //===========================
      let e = especie.replace(/[A-ZÁÉÍÓÚáéíóúâêîôûàèìòùÃÕãõÇç ]/gi, '');
      e = e.replaceAll('-', '');

      if (e.length != 0) {
        alert("Caracteres inválidos encontrados. Tente novamente.");
        adicionar_especie();
        return;
      }
      //============================

      data = 'especie=' + especie;
      
      $.ajax({
        type: "POST",
        url: url,
        data: data,
        success: function(response) {
          gerarEspecie();
        },
        dataType: 'text'
      })
    }

    /** Função adicionar_cor */

    function gerarCor() {
      url = '../../dao/pet/exibir_cor.php';
      $.ajax({
        data: '',
        type: "POST",
        url: url,
        success: function(response) {
          var cor = response;
          $('#cor').empty();
          $('#cor').append('<option selected disabled>Selecionar</option>');
          $.each(cor, function(i, item) {
            $('#cor').append('<option value="' + item.id_cor + '">' + item.cor + '</option>');
          });
        },
        dataType: 'json'
      });

    }

    function adicionar_cor() {
      url = '../../dao/pet/adicionar_cor.php';
      var cor = window.prompt("Cadastre uma Nova Cor:");
      if (!cor) {
        return
      }
      situacao = cor.trim();
      if (cor == '') {
        return
      }

      //====================================
      let c = cor.replace(/[A-ZÁÉÍÓÚáéíóúâêîôûàèìòùÃÕãõÇç ]/gi, '');
      c = c.replaceAll('-', '');

      if (c.length != 0) {
        alert("Caracteres inválidos encontrados. Tente novamente.");
        adicionar_cor();
        return;
      }
      //====================================

      data = 'cor=' + cor;
      
      $.ajax({
        type: "POST",
        url: url,
        data: data,
        success: function(response) {
          gerarCor();
        },
        dataType: 'text'
      })
    }


    /** Aqui termina a implementação das funções relacionada a "PET" */


    var clickcont = 0;
    $("#botima").toggle();
    $("#imgform").click(function(e) {
      if (clickcont == 0) {
        $("#botima").toggle();
      }
      clickcont = clickcont + 1;
    });

    function okDisplay() {
      document.getElementById("okButton").style.backgroundColor = "#0275d8"; //azul
      document.getElementById("okText").textContent = "Confirme o arquivo selecionado";
      $("#nome").prop('disabled', true);
      $("#cor").prop('disabled', true);
      $("#especie").prop('disabled', true);
      $("#raca").prop('disabled', true);
      $("#radioM").prop('disabled', true);
      $("#radioF").prop('disabled', true);
      $("#nascimento").prop('disabled', true);
      $("#caracEsp").prop('disabled', true);
      $("#vacinacao").prop('disabled', true);
      $("#necEsp").prop('disabled', true);
    }

    function submitButtonStyle(event, _this) {
      // Impede o envio do formulário e o redirecionamento
      event.preventDefault();

      // Mudança de estilo no botão
      _this.style.backgroundColor = "#5cb85c"; // verde
      document.getElementById("okText").textContent = "Arquivo confirmado";

      // Habilitar campos
      $("#nome").prop('disabled', false);
      $("#cor").prop('disabled', false);
      $("#especie").prop('disabled', false);
      $("#raca").prop('disabled', false);
      $("#radioM").prop('disabled', false);
      $("#radioF").prop('disabled', false);
      $("#nascimento").prop('disabled', false);
      $("#caracEsp").prop('disabled', false);
      $("#vacinacao").prop('disabled', false);
      $("#necEsp").prop('disabled', false);
    }


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

      $.each(cpf_cadastrado, function(i, item) {
        if (item.cpf == cpf) {
          alert("Cadastro não realizado! O CPF informado já está cadastrado no sistema");
          btn.attr('disabled', 'disabled');
          return false;
        }
      });

      var nome = document.getElementById('profileFirstName').value;

      var sobrenome = document.getElementById('sobrenome').value;

      var sexo = document.querySelector('input[name="gender"]:checked').value;

      var telefone = document.getElementById('telefone').value;

      var dt_nasc = document.getElementById('nascimento').value;

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

      if (nome && sobrenome && sexo && telefone && dt_nasc && rg && orgao_emissor && dt_expedicao && dt_admissao && situacao && cargo && escala && tipo) {
        alert("Cadastrado com sucesso!");
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
  <script language="JavaScript">
  </script>
  <!-- Vendor -->
  <script src="../../assets/vendor/jquery/jquery.js"></script>
  <script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
  <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
  <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
  <script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
  <script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
  <script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

  <!-- img form -->
  <script>/*
    const image_input = document.querySelector(".image_input");
    var uploaded_image;

    image_input.addEventListener('change', function() {
      const reader = new FileReader();
      reader.addEventListener('load', () => {
        uploaded_image = reader.result;
        document.querySelector("#display_image").style.backgroundImage = `url(${uploaded_image})`;
      });
      reader.readAsDataURL(this.files[0]);
    });*/
  </script>

  <div align="right">
    <iframe src="https://www.wegia.org/software/footer/pet.html" width="200" height="60" style="border:none;"></iframe>
  </div>
</body>

</html>