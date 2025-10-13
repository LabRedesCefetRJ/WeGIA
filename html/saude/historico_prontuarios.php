<?php

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

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

require_once '../../controle/SaudeControle.php';

$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$id_pessoa = $_SESSION['id_pessoa'];
$resultado = mysqli_query($conexao, "SELECT * FROM funcionario WHERE id_pessoa=$id_pessoa");
if (!is_null($resultado)) {
  $id_cargo = mysqli_fetch_array($resultado);
  if (!is_null($id_cargo)) {
    $id_cargo = $id_cargo['id_cargo'];
  }
  //Alterar essa busca pelo resultado
  $resultado = mysqli_query($conexao, "SELECT * FROM permissao p JOIN acao a ON(p.id_acao=a.id_acao) JOIN recurso r ON(p.id_recurso=r.id_recurso) WHERE id_cargo=$id_cargo AND p.id_acao >=5  AND p.id_recurso=5");
  if (!is_bool($resultado) and mysqli_num_rows($resultado)) {
    $permissao = mysqli_fetch_array($resultado);
    $permissao = $permissao['id_acao'];
  } else {
    $permissao = 1;
    $msg = "Você não tem as permissões necessárias para essa página.";
    header("Location: ../home.php?msg_c=$msg");
  }
} else {
  $permissao = 1;
  $msg = "Você não tem as permissões necessárias para essa página.";
  header("Location: ../../home.php?msg_c=$msg");
}

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once "../personalizacao_display.php";

extract($_REQUEST);

$saudeControle = new SaudeControle();
$prontuariosDoHistorico = $saudeControle->listarProntuariosDoHistorico($id_paciente);

?>

<!doctype html>
<html class="fixed">

<head>

  <style>

    #historicoOpcao {
      width: 60%;
    }

    .btn#visualizar {
      margin-top: 10px;
      margin-bottom: 10px;
    }

    .hidden {
      display: none;
    }

    #conteudo-pagina{
      margin-left: 10%;
    }

    /*@media(max-width:1000px){
      #conteudo-pagina{
        margin-left: 0;
      }

      #historicoOpcao {
      width: 45%;
      }
      
      #prontuario_publico{
        max-width: 80%;
      }
    }*/

  </style>

  <!-- Basic -->
  <meta charset="UTF-8">

  <title>Histórico dos prontuários</title>

  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

  <!-- Vendor CSS -->
  <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
  <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
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
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">

  <!-- Vendor -->
  <script src="../../assets/vendor/jquery/jquery.min.js"></script>
  <script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
  <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
  <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
  <script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
  <script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
  <script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

  <!-- Specific Page Vendor -->
  <script src="../../assets/vendor/jquery-autosize/jquery.autosize.js"></script>

  <!-- Theme Base, Components and Settings -->
  <script src="../../assets/javascripts/theme.js"></script>

  <!-- Theme Custom -->
  <script src="../../assets/javascripts/theme.custom.js"></script>

  <!-- Theme Initialization Files -->
  <script src="../../assets/javascripts/theme.init.js"></script>

  <!-- javascript functions -->
  <script src="../../Functions/onlyNumbers.js"></script>
  <script src="../../Functions/onlyChars.js"></script>
  <script src="../../Functions/enviar_dados.js"></script>
  <script src="../../Functions/mascara.js"></script>

  <!-- Ckeditor -->
  <script src="<?php echo WWW; ?>assets/vendor/ckeditor/ckeditor.js"></script>
  <!-- jquery functions -->
  <script>
    $(function() {
      $("#header").load("../header.php");
      $(".menuu").load("../menu.php");
    });
  </script>
</head>

<body>
  <section class="body">
    <!-- start: header -->
    <div id="header"></div>
    <!-- end: header -->
    <div class="inner-wrapper">
      <!-- start: sidebar -->
      <aside id="sidebar-left" class="sidebar-left menuu"></aside>

      <!-- end: sidebar -->
      <section role="main" class="content-body">
        <header class="page-header">
          <h2>Históricos dos prontuários</h2>

          <div class="right-wrapper pull-right">
            <ol class="breadcrumbs">
              <li><a href="../index.php"> <i class="fa fa-home"></i>
                </a></li>
              <li><span>Visualizar Históricos dos prontuários públicos</span></li>
            </ol>

            <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
          </div>
        </header>

        <!-- start: page -->
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-12 col-md-6" id="conteudo-pagina">
              <form action="">
                <div class="form-group">
                  <label for="historicoOpcao" class="font-weight-bold">Selecione a data do histórico que você deseja visualizar</label>
                  <select name="historicoOpcao" id="historicoOpcao" class="form-control">
                    <option value="" selected disabled>Selecionar ...</option>
                  </select>
                </div>
                <button class="btn btn-primary" id="visualizar" onclick="event.preventDefault(); visualizarProntuario();">Visualizar</button>
              </form>

              <table class="table table-bordered table-striped mb-none hidden" id="table-prontuario">
                <thead>
                  <tr style="font-size:15px;">
                    <th>Prontuário público</th>
                  </tr>
                </thead>
                <tbody id="prontuario_publico" style="font-size:15px;">
                  <td id="descricao_historico"></td>
                </tbody>
              </table>


            </div>
          </div>
        </div>



        <!-- start: page -->

        <!-- end: page -->

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
        <script src="../../assets/javascripts/tables/examples.datatables.default.js"></script>
        <script src="../../assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
        <script src="../../assets/javascripts/tables/examples.datatables.tabletools.js"></script>

        <div align="right">
          <iframe src="https://www.wegia.org/software/footer/saude.html" width="200" height="60" style="border:none; margin-top:150px;"></iframe>
        </div>
      </section>
  </section>

  <script>

    function formatarDataBr(data) {
      let hour = null;

      // Verifica se existe parte de hora
      if (data.split(" ")[1] !== undefined && data.split(" ")[1] !== null) {
        const partes = data.split(" ");
        hour = partes[1].split(":");
        data = partes[0];
      }

      const parts = data.split('-'); // Supondo que a data esteja no formato 'YYYY-MM-DD'

      let dataFinal = "";
      let dataObj;

      if (hour !== null) {
        dataObj = new Date(parts[0], parts[1] - 1, parts[2], hour[0], hour[1], hour[2]);
        const horaFormatada = dataObj.toLocaleTimeString('pt-BR', {
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit'
        });
        dataFinal += " " + horaFormatada;
      } else {
        dataObj = new Date(parts[0], parts[1] - 1, parts[2]);
      }

      const options = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
      };

      const dataFormatada = dataObj.toLocaleDateString('pt-BR', options);
      dataFinal = dataFormatada + dataFinal;

      return dataFinal;
    }

    function gerarOptions(dados, idSelect){
      const select = document.getElementById(idSelect);
      dados.forEach((obj) => {
        const option = document.createElement("option");
        option.value = obj.idHistorico;
        option.textContent = formatarDataBr(obj.data);
        select.appendChild(option);
      })
    }
    
    const prontuarios = <?=json_encode($prontuariosDoHistorico);?>;

    document.addEventListener("DOMContentLoaded", gerarOptions(prontuarios, "historicoOpcao"))

    async function visualizarProntuario() {

      const opcao = document.getElementById('historicoOpcao').value;

      if(!opcao || opcao.trim() === ""){
        alert("Escolha uma opção de data válida antes de clicar em visualizar.");
        return;
      }

      const URL = `../../controle/control.php?metodo=listarProntuarioHistoricoPorId&nomeClasse=SaudeControle&idHistorico=${opcao}`;

      let resposta = await fetch(URL, {
        headers: {
          'Accept': 'application/json'
        }
      });

      if (!resposta.ok) {
        alert('Ops!, ocorreu algum erro ao tentar puxar as informações do histórico');
        return;
      }

      let prontuario = await resposta.json();

      let descricaoCompleta = "";

      prontuario.forEach(element => {
        descricaoCompleta += element.descricao;
      });

      const tdDescricao = document.getElementById('descricao_historico');
      tdDescricao.innerHTML = descricaoCompleta;

      const tableProntuario = document.getElementById('table-prontuario');

      if (tableProntuario.classList.contains("hidden")) {
        tableProntuario.classList.remove("hidden");
      }
    }
  </script>
</body>

</html>