<?php

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);
extract($_REQUEST);
session_start();


if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
}

if (!isset($_SESSION['id_fichamedica'])) {
  header('Location: ../../controle/control.php?metodo=listarUm&nomeClasse=SaudeControle&nextPage=../html/saude/sinais_vitais.php');
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

require_once "./verifica_permissao_saude.php";

include_once '../../classes/Cache.php';
require_once "../personalizacao_display.php";

require_once ROOT . "/controle/SaudeControle.php";

if(!is_numeric($_GET['id_fichamedica']) || $_GET['id_fichamedica'] < 1){
  header("Location: ../home.php?msg_c=O parâmetro informado é incorreto, informe um inteiro positivo maior ou igual a 1.");
  exit();
}

$id = $_GET['id_fichamedica'];
$cache = new Cache();
$teste = $cache->read($id);
$_SESSION['id_upload_med'] = $id;
require_once "../../dao/Conexao.php";
$pdo = Conexao::connect();

if (!isset($teste)) {
  header('Location: ../../controle/control.php?metodo=listarUm&nomeClasse=SaudeControle&nextPage=../html/saude/sinais_vitais.php?id_fichamedica=' . $id . '&id=' . $id);
}

$teste = $pdo->query("SELECT nome, f.id_funcionario FROM pessoa p JOIN funcionario f ON(p.id_pessoa = f.id_pessoa) WHERE f.id_pessoa = " . $_SESSION['id_pessoa'])->fetchAll(PDO::FETCH_ASSOC);
$id_funcionario = $teste[0]['nome'];
$funcionario_id = $teste[0]['id_funcionario'];

$stmtSinaisVitais = $pdo->prepare("SELECT id_sinais_vitais, data, saturacao, pressao_arterial, frequencia_cardiaca, frequencia_respiratoria, temperatura, hgt, observacao, p.nome, p.sobrenome FROM saude_sinais_vitais sv JOIN funcionario f ON(sv.id_funcionario = f.id_funcionario) JOIN pessoa p ON (f.id_pessoa = p.id_pessoa) WHERE sv.id_fichamedica =:idFichaMedica");

$stmtSinaisVitais->bindParam(':idFichaMedica', $id);
$stmtSinaisVitais->execute();

$sinaisvitais = $stmtSinaisVitais->fetchAll(PDO::FETCH_ASSOC);

//formatar data
foreach ($sinaisvitais as $key => $value) {
  $data = new DateTime($value['data']);
  $sinaisvitais[$key]['data'] = $data->format('d/m/Y H:i');
  $sinaisvitais[$key]['observacao'] = htmlspecialchars($value['observacao']);
}

$sinaisvitais = json_encode($sinaisvitais);

$sqlPaciente = "SELECT id_pessoa FROM saude_fichamedica WHERE id_fichamedica =:idFichaMedica";

$stmtPaciente = $pdo->prepare($sqlPaciente);

$stmtPaciente->bindValue(':idFichaMedica', $_GET['id_fichamedica']);

if (!$stmtPaciente->execute()) {
  http_response_code(500);
  echo json_encode(['erro' => 'Erro ao buscar paciente']);
  exit();
}

$idPaciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);

?>
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
<!-- <script src="<?php echo WWW; ?>assets/javascripts/theme.js"></script> -->

<!-- Theme Custom -->
<script src="<?php echo WWW; ?>assets/javascripts/theme.custom.js"></script>

<!-- Theme Initialization Files -->
<script src="<?php echo WWW; ?>assets/javascripts/theme.init.js"></script>

<!-- javascript functions -->
<script src="<?php echo WWW; ?>Functions/onlyNumbers.js"></script>
<script src="<?php echo WWW; ?>Functions/onlyChars.js"></script>
<script src="<?php echo WWW; ?>Functions/mascara.js"></script>

<!-- jkeditor -->
<script src="<?php echo WWW; ?>assets/vendor/ckeditor/ckeditor.js"></script>

<!-- Specific Page Vendor CSS -->
<link rel="stylesheet" href="../../assets/vendor/select2/select2.css" />
<link rel="stylesheet" href="../../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />


<script>
  $(function() {

    $("#header").load("../header.php");
    $(".menuu").load("../menu.php");
  });
</script>

<style type="text/css">
  .select {
    position: absolute;
    width: 235px;
  }

  .select-table-filter {
    width: 140px;
    float: left;
  }

  .panel-body {
    margin-bottom: 15px;
  }

  #div_texto {
    width: 100%;
  }

  #cke_despacho {
    height: 500px;
  }

  .cke_inner {
    height: 500px;
  }

  #cke_1_contents {
    height: 455px !important;
  }

  .col-md-3 {
    width: 10%;
  }

  #sin-vit-tab tr > td:last-child {
    display: flex;
    padding: 0;
    justify-content: center;
    align-items: center;
    height: 100%;
  }

  #sin-vit-tab tr {
    height: 100%;
  }

</style>


<!doctype html>
<html class="fixed">

<head>
  <!-- Basic -->
  <meta charset="UTF-8">
  <title>Histórico do paciente</title>
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

  <link rel="stylesheet" type="text/css" href="../../css/profile-theme.css">
  </script>
  <script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
  <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
  <script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
  <script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
  <script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
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
  <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">
  <script>
    $(function() {
      var interno = <?php echo $_SESSION['id_fichamedica']; ?>;
      $.each(interno, function(i, item) {
        if (i = 1) {
          if (item.imagem != "" && item.imagem != null) {
            $("#imagem").attr("src", "data:image/gif;base64," + item.imagem);
          } else {
            $("#imagem").attr("src", "../../img/semfoto.png");
          }
        }
      });
    });


    $(function() {
      var sinaisvitais = <?= $sinaisvitais ?>;
      $("#sin-vit-tab").empty();
      $.each(sinaisvitais, function(i, item) {
        $("#sin-vit-tab")
          .append($("<tr id=l_" + i + ">")
            .append($("<td>").text(item.data))
            .append($("<td>").text(item.nome + " " + (item.sobrenome !== null ? item.sobrenome : "")))
            .append($("<td>").text(item.saturacao))
            .append($("<td>").text(item.pressao_arterial))
            .append($("<td>").text(item.frequencia_cardiaca))
            .append($("<td>").text(item.frequencia_respiratoria))
            .append($("<td>").text(item.temperatura))
            .append($("<td>").text(item.hgt))
            .append($("<td>").text(item.observacao))
            .append($("<td style=''>")
              .append($("<a onclick='removerSinVit(" + item.id_sinais_vitais + "," + i + ")' href='#' title='Excluir'><button class='btn btn-danger'><i class='fas fa-trash-alt'></i></button></a>"))
            )
          )
      });
    });

    function removerSinVit(sinal, linha) {
      if (!window.confirm("Deseja excluir esse registro da tabela?")) return false;
      $.ajax({
        url: "sinais_vitais_excluir.php",
        type: "POST",
        data: {
          "id_sinais_vitais": sinal
        },
        success: function(msg) {
          let texto = `#l_${linha}`;
          $(texto).empty();
          console.log(msg)
        },
        error: function(err) {
          console.log(err)
        },
      })
    }


    $(document).ready(function() {
      $('#tabmed').DataTable({
        "order": [
          [0, "desc"]
        ]
      });
    });

    function validarPressao(campo) {
      var value = document.getElementById(campo).value;

      if (value < 0) {
        document.getElementById(campo).value = 0;
      }
      if (value > 300) {
        document.getElementById(campo).value = 300;
      }
      if (value.length > 3) {
        document.getElementById(campo).value = value.slice(0, 3);
      }

      var sistolica = document.getElementById('sistolica').value;
      var diastolica = document.getElementById('diastolica').value;

      if (sistolica && diastolica) {
        document.getElementById('pressao').value = sistolica + '/' + diastolica;
      } else {
        document.getElementById('pressao').value = '';
      }
    }

    function definirDataHoraAtualSeVazio(campo) {
      if (!campo.value) {
        const agora = new Date();
        const adicionarZero = numero => numero.toString().padStart(2, '0');

        const ano = agora.getFullYear();
        const mes = adicionarZero(agora.getMonth() + 1); // Janeiro = 0
        const dia = adicionarZero(agora.getDate());
        const horas = adicionarZero(agora.getHours());
        const minutos = adicionarZero(agora.getMinutes());

        campo.value = `${ano}-${mes}-${dia}T${horas}:${minutos}`;
      }
    }

    function limitarValorPositivoComTamanho(campo) {
      if (campo.value.length > campo.maxLength) {
        campo.value = campo.value.slice(0, campo.maxLength);
      }

      if (campo.value < 0) {
        campo.value = campo.value * -1;
      }
    }
    function limitarTemperatura(campo){
      let numeroDividido = campo.value.toString().split('.');
      let numeroInteiro = numeroDividido[0];
      let numeroDecimal = numeroDividido[1] || '';
      if(parseInt(numeroInteiro) > 45){
        campo.value = 45;
      }else if( numeroDecimal && numeroDecimal.length > 1){
        campo.value = numeroInteiro + '.' + numeroDecimal[0];
      }
    }

    function validarObservacao(campo){
      campo.value = campo.value.replace(/<|>/g, '');

      const maxLength = campo.maxLength;
      let currentLength = campo.value.length;

      if (currentLength > maxLength) {
        campo.value = campo.value.slice(0, maxLength);
        currentLength = maxLength;
      }

      const contadorElemento = document.getElementById('contador-caracteres');
      if (contadorElemento) {
        contadorElemento.textContent = currentLength;
      }
    }

  </script>
  <style type="text/css">
    .obrig {
      color: rgb(255, 0, 0);
    }

    #btn-cadastrar-emergencia {
      margin-top: 10px;
    }

    .custom-input {
      color: #555555;
      background-color: #fff;
      background-image: none;
      border: 1px solid #ccc;
      border-radius: 4px;
      -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
      box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
      -webkit-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
      -o-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
      -webkit-transition: border-color ease-in-out .15s, -webkit-box-shadow ease-in-out .15s;
      transition: border-color ease-in-out .15s, -webkit-box-shadow ease-in-out .15s;
      transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
      transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s, -webkit-box-shadow ease-in-out .15s;
  }

  .custom-input:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
  }

  .contador-container{
    display: flex;
    flex-direction: row;
    justify-content: flex-end;
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
          <h2>Histórico do paciente</h2>
          <div class="right-wrapper pull-right">
            <ol class="breadcrumbs">
              <li>
                <a href="../index.php">
                  <i class="fa fa-home"></i>
                </a>
              </li>
              <li><span>Histórico do paciente</span></li>
            </ol>
            <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
          </div>
        </header>
        <!-- start: page -->
        <div class="row">
          <div class="col-md-4 col-lg-3">
            <section class="panel">
              <div class="panel-body">

                <div class="thumb-info mb-md">
                  <img id="imagem" alt="John Doe">
                </div>
              </div>
            </section>
          </div>
          <div class="col-md-9 col-lg-9">
            <div class="tabs">
              <ul class="nav nav-tabs tabs-primary">
                <li id="tab1" class="active">
                  <a href="#atendimento_enfermeiro" data-toggle="tab">Sinais vitais</a>
                </li>
              </ul>

              <div class="tab-content">
                <!-- aba de atendimento enfermeiro -->
                <div id="atendimento_enfermeiro" class="tab-pane active">
                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Sinais vitais</h2>
                    </header>

                    <form action="../../controle/control.php" method="post" enctype='multipart/form-data'>
                      <div class="form-group">
                        <div class="col-md-6">
                          <h5 class="obrig">Campos Obrigatórios(*)</h5>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-md-3 control-label" for="inputSuccess">Enfermeiro:</label>
                        <div class="col-md-8">
                          <input class="form-control" style="width:230px;" name="enfermeiro" id="enfermeiro" value="<?php echo $id_funcionario; ?>" disabled="true">
                        </div>
                      </div>


                      <div class="form-group">
                        <label class="col-md-3 control-label" for="profileCompany">Data da aferição<sup class="obrig">*</sup></label>
                        <div class="col-md-6">
                        <input type="datetime-local" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="data_afericao" id="data_afericao" max=<?php echo date('Y-m-d\TH:i'); ?> 
                        onfocus="definirDataHoraAtualSeVazio(this)" required>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="profileCompany">Saturação (em %):</label>
                        <div class="col-md-6">
                          <input type="number" class="form-control" name="saturacao" id="saturacao" min="0" max="99" maxlength="2" oninput="limitarValorPositivoComTamanho(this)" onkeypress="return Onlynumbers(event)">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="profileCompany">Pressão arterial:</label>
                        <div class="col-md-6">
                          <input type="number" id="sistolica" maxlength="3" class="custom-input" style="width:60px;" oninput="validarPressao('sistolica');" onkeypress="return Onlynumbers(event)">
                          <span>/</span>
                          <input type="number" id="diastolica" maxlength="3" class="custom-input" style="width:60px;" oninput="validarPressao('diastolica');" onkeypress="return Onlynumbers(event);">
                          <input type="hidden" id="pressao" name="pres_art">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="profileCompany">Frequência cardíaca (em bpm):</label>
                        <div class="col-md-6">
                          <input type="number" maxlength="3" class="form-control" name="freq_card" id="freq_card" oninput="limitarValorPositivoComTamanho(this)" onkeypress="return Onlynumbers(event)">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="profileCompany">Frequência respiratória (em rpm):</label>
                        <div class="col-md-6">
                          <input type="number" maxlength="2" class="form-control" name="freq_resp" id="freq_resp" oninput="limitarValorPositivoComTamanho(this)" onkeypress="return Onlynumbers(event)">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="profileCompany">Temperatura (em °C):</label>
                        <div class="col-md-6">
                          <input class="form-control" type="number" step="0.1" min="30" max="45" name="temperatura" inputmode="decimal" oninput="limitarTemperatura(this)">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="profileCompany">HGT (mg/dL):</label>
                        <div class="col-md-6">
                          <input type="number" maxlength="3" class="form-control" name="hgt" id="hgt" oninput="limitarValorPositivoComTamanho(this)" onkeypress="return Onlynumbers(event)">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="profileCompany">Observações:</label>
                        <div class="col-md-6">
                          <textarea name="observacao" id="observacao" maxlength="255" class="form-control" rows="5" oninput="validarObservacao(this)" onkeypress="return " placeholder="Descreva suas observações..."></textarea>
                          <div class="form-group contador-container">
                            <span class="row-md-1"><span id="contador-caracteres">0</span> / 255</span>
                          </div>
                        </div>
                      </div>

                      <input type="hidden" name="id_funcionario" id="id_funcionario" value="<?php echo $funcionario_id; ?>">

                      <input type="hidden" name="id_fichamedica" id="id_fichamedica" value="<?php echo $_SESSION['id_upload_med']; ?>">

                      <input type="hidden" name="nomeClasse" value="SinaisVitaisControle">

                      <input type="hidden" name="metodo" value="incluir">

                      <div class="form-group">
                        <input type="submit" value="Enviar" class="btn btn-primary">
                      </div>
                    </form>

                    <div class="panel-body">
                      <hr class="dotted short">

                      <table class="table table-bordered table-striped mb-none datatable-docfuncional" id="tabmed">
                        <thead>
                          <tr style="font-size:15px;">
                            <th>Data</th>
                            <th>Aferidor</th>
                            <th>Saturação</th>
                            <th>Pressão arterial</th>
                            <th>Frequência cardíaca</th>
                            <th>Frequência repiratória</th>
                            <th>Temperatura</th>
                            <th>HGT</th>
                            <th>Observação</th>
                            <th>Excluir</th>
                          </tr>
                        </thead>
                        <tbody id="sin-vit-tab" style="font-size:15px">

                        </tbody>
                      </table>

                  </section>
                </div>
      </section>
      <!-- Vendor -->
      <script src="../../assets/vendor/select2/select2.js"></script>
      <script src="../../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
      <script src="../../assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
      <script src="../../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
      <!-- Theme Custom -->
      <script src="../../assets/javascripts/theme.custom.js"></script>
      <!-- Theme Initialization Files -->
      <!-- Examples -->
      <script src="../../assets/javascripts/tables/examples.datatables.default.js"></script>
      <script src="../../assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
      <script src="../../assets/javascripts/tables/examples.datatables.tabletools.js"></script>
    </div>
    <!-- Vendor -->
    <script src="<?php echo WWW; ?>assets/vendor/select2/select2.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>

    <!-- Theme Custom -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.custom.js"></script>

    <script src="<?php echo WWW; ?>assets/javascripts/theme.js"></script>

    <!-- Theme Initialization Files -->
    <!-- Examples -->
    <script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.default.js"></script>
    <script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
    <script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.tabletools.js"></script>

    <!-- importante para a aba de exames -->
    <script src="../geral/post.js"></script>
    <script src="../geral/formulario.js"></script>

    <div align="right">
      <iframe src="https://www.wegia.org/software/footer/saude.html" width="200" height="60" style="border:none;"></iframe>
    </div>
  </section>
</body>

</html>