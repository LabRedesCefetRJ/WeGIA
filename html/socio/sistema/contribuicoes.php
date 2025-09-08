<?php
require("../conexao.php");
// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";
session_start();
if (!isset($_SESSION['usuario'])) header("Location: ../erros/login_erro/");
$id = $_SESSION['usuario'];
$id_pessoa = $_SESSION['id_pessoa'];
$resultado = mysqli_query($conexao, "SELECT `imagem`, `nome` FROM `pessoa` WHERE id_pessoa=$id_pessoa");
$pessoa = mysqli_fetch_array($resultado);
$nome = $pessoa['nome'];

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

if (!isset($_SESSION['usuario'])) {
  header("Location: " . WWW . "index.php");
}
$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$id_pessoa = $_SESSION['id_pessoa'];
$resultado = mysqli_query($conexao, "SELECT * FROM funcionario WHERE id_pessoa=$id_pessoa");
if (!is_null($resultado)) {
  $id_cargo = mysqli_fetch_array($resultado);
  if (!is_null($id_cargo)) {
    $id_cargo = $id_cargo['id_cargo'];
  }
  $resultado = mysqli_query($conexao, "SELECT * FROM permissao WHERE id_cargo=$id_cargo and id_recurso=4");
  if (!is_bool($resultado) and mysqli_num_rows($resultado)) {
    $permissao = mysqli_fetch_array($resultado);
    if ($permissao['id_acao'] < 7) {
      $msg = "Você não tem as permissões necessárias para essa página.";
      header("Location: " . WWW . "/html/home.php?msg_c=$msg");
    }
    $permissao = $permissao['id_acao'];
  } else {
    $permissao = 1;
    $msg = "Você não tem as permissões necessárias para essa página.";
    header("Location: " . WWW . "/html/home.php?msg_c=$msg");
  }
} else {
  $permissao = 1;
  $msg = "Você não tem as permissões necessárias para essa página.";
  header("Location: " . WWW . "/html/home.php?msg_c=$msg");
}
// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";


try {

  //Buscar data e hora da última atualização das contribuições
  require_once '../../../dao/SistemaLogDAO.php';

  $sistemaLogDao = new SistemaLogDAO();
  $sistemaLogContribuicao = $sistemaLogDao->getLogsPorRecurso(71, TRUE);

  //Buscar sócios para os relatórios personalizados
  require_once '../../contribuicao/dao/SocioDAO.php';

  $socioDao = new SocioDAO();
  $socios = $socioDao->getSocios();
} catch (PDOException $e) {
  error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
  http_response_code(500);
  echo json_encode(['erro' => 'Erro ao carregar dados']);
}

?>
<!DOCTYPE html>
<html class="fixed">

<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Contribuições</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="controller/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="controller/bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="controller/dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="controller/dist/css/skins/_all-skins.min.css">
  <!-- Morris chart -->
  <link rel="stylesheet" href="controller/bower_components/morris.js/morris.css">
  <!-- jvectormap -->
  <link rel="stylesheet" href="controller/bower_components/jvectormap/jquery-jvectormap.css">
  <!-- Date Picker -->
  <link rel="stylesheet" href="controller/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="controller/bower_components/bootstrap-daterangepicker/daterangepicker.css">
  <!-- bootstrap wysihtml5 - text editor -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

  <link rel="stylesheet" href="controller/css/animacoes.css">
  <link rel="stylesheet" href="controller/css/tabelas.css">
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@700&display=swap" rel="stylesheet">
  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
  <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/font-awesome/css/font-awesome.css" />
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
  <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/magnific-popup/magnific-popup.css" />
  <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
  <!--<link rel="icon" href="<?php //display_campo("Logo",'file');
                              ?>" type="image/x-icon">-->

  <!-- Specific Page Vendor CSS -->
  <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/select2/select2.css" />
  <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

  <!-- Theme CSS -->
  <link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/theme.css" />

  <!-- Skin CSS -->
  <link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/skins/default.css" />

  <!-- Theme Custom CSS -->
  <link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/theme-custom.css">

  <!-- Head Libs -->
  <script src="<?php echo WWW; ?>assets/vendor/modernizr/modernizr.js"></script>

  <!-- Vendor -->
  <script src="<?php echo WWW; ?>assets/vendor/jquery/jquery.min.js"></script>
  <script src="<?php echo WWW; ?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
  <script src="<?php echo WWW; ?>assets/vendor/nanoscroller/nanoscroller.js"></script>
  <script src="<?php echo WWW; ?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
  <script src="<?php echo WWW; ?>assets/vendor/magnific-popup/magnific-popup.js"></script>
  <script src="<?php echo WWW; ?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

  <!-- Specific Page Vendor -->
  <script src="<?php echo WWW; ?>assets/vendor/jquery-autosize/jquery.autosize.js"></script>

  <!-- Theme Base, Components and Settings -->
  <script src="<?php echo WWW; ?>assets/javascripts/theme.js"></script>

  <!-- Theme Custom -->
  <script src="<?php echo WWW; ?>assets/javascripts/theme.custom.js"></script>

  <!-- Theme Initialization Files -->
  <script src="<?php echo WWW; ?>assets/javascripts/theme.init.js"></script>

  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <link type="text/css" rel="stylesheet" charset="UTF-8" href="https://translate.googleapis.com/translate_static/css/translateelement.css">

  <!-- javascript functions -->
  <script src="<?php echo WWW; ?>Functions/onlyNumbers.js"></script>
  <script src="<?php echo WWW; ?>Functions/onlyChars.js"></script>
  <script src="<?php echo WWW; ?>Functions/mascara.js"></script>
  <script src="<?php echo WWW; ?>html/contribuicao/js/geraboleto.js"></script>
  <script src="<?php echo WWW; ?>html/socio/sistema/controller/script/relatorios_socios.js"></script>
  <script src="<?php echo WWW; ?>html/socio/sistema/controller/script/sincronizacao_contribuicoes.js" defer></script>

  <script type="text/javascript">
    $(function() {
      $("#header").load("<?php echo WWW; ?>html/header.php");
      $(".menuu").load("<?php echo WWW; ?>html/menu.php");
    });
  </script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.css">

  <style>
    .sync-control {
      display: flex;
      align-items: center;
      gap: 1rem;
      /* Espaço entre o botão e o texto */
    }

    .sync-control p {
      margin: 0;
      /* Remove margem padrão do parágrafo */
    }

    .me-5 {
      margin-right: 5px;
    }

    .mt-10 {
      margin-top: 10px;
    }

    .hidden {
      display: none;
    }

    @media print {
      #header {
        display: none;
      }

      .menuu {
        display: none;
      }

      .panel-heading {
        display: none;
      }

      .content-body {
        padding: 0;
        font-size: smaller;
      }

      #tabela-relatorio-contribuicao th:last-child {
        text-overflow: ellipsis;
      }

      #tabela-relatorio-contribuicao td:last-child {
        white-space: normal;
        word-break: break-word;
        hyphens: auto;
      }

      #mensagem-relatorio {
        margin-left: 5px;
      }

      .print-hide {
        display: none;
      }
    }
  </style>
</head>

<body>

  <section class="body">

    <!-- start: header -->
    <header id="header" class="header print-hide">

      <!-- end: search & user box -->
    </header>

    <!-- end: header -->
    <div class="inner-wrapper">
      <!-- start: sidebar -->
      <aside id="sidebar-left" class="sidebar-left menuu"></aside>
      <!-- end: sidebar -->

      <section role="main" class="content-body">
        <header class="page-header print-hide">
          <h2>Contribuições</h2>

          <div class="right-wrapper pull-right">
            <ol class="breadcrumbs">
              <li>
                <a href="../../home.php">
                  <i class="fa fa-home"></i>
                </a>
              </li>
              <li><span>Páginas</span></li>
              <li><span>Contribuições</span></li>
            </ol>

            <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
          </div>
        </header>

        <!-- start: page -->
        <?php
        // Exibir mensagens de sucesso ou erro
        if (isset($_GET['msg_s'])) {
            echo '<div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-check"></i> Sucesso!</h4>
                    ' . htmlspecialchars($_GET['msg_s']) . '
                  </div>';
        }
        if (isset($_GET['msg_c'])) {
            echo '<div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-ban"></i> Erro!</h4>
                    ' . htmlspecialchars($_GET['msg_c']) . '
                  </div>';
        }
        ?>
        <div class="row">

          <div class="box box-warning collapsed-box">
            <div class="box-header with- print-hide">
              <h3 class="box-title">Relatórios personalizados</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                </button>
              </div>

            </div>

            <div class="box-body">
              <p class="print-hide">Filtros de pesquisa</p>

              <form id="form-relatorio-contribuicao" action="" class="form-inline print-hide">
                <div class="form-group me-5">
                  <label for="periodo" class="control-label">Período:&nbsp;</label>
                  <select class="form-control" name="periodo" id="periodo" style="width: 200px;">
                    <option value="1">Todos</option>
                    <option value="2">Mês atual</option>
                    <option value="3">Mês passado</option>
                    <option value="4">Bimestre</option>
                    <option value="5">Trimestre</option>
                    <option value="6">Semestre</option>
                    <option value="7">Ano atual</option>
                    <option value="8">Ano passado</option>
                    <!--<option value="9">Específico</option> Adicionar futuramente-->
                  </select>
                </div>

                <div class="form-group me-5">
                  <label for="socio" class="control-label">Sócio:&nbsp;</label>
                  <select class="form-control" name="socio" id="socio" style="width: 200px;">
                    <option value="0">Todos</option>
                    <?php if (!is_null($socios)): ?>
                      <?php foreach ($socios as $socio): ?>
                        <option value="<?= $socio->getId() ?>"><?= $socio->getNome() ?></option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>

                <div class="form-group me-5">
                  <label for="status" class="control-label">Status:&nbsp;</label>
                  <select class="form-control" name="status" id="status" style="width: 200px;">
                    <option value="1">Todos</option>
                    <option value="2">Emitida</option>
                    <option value="3">Vencida</option>
                    <option value="4">Paga</option>
                  </select>
                </div>

                <button id="relatorio-btn" type="submit" class="btn btn-primary">Gerar relatório</button>
              </form>

              <button id="relatorio-imprimir-btn" class="btn btn-primary mt-10 hidden print-hide" onclick="window.print()">Imprimir</button>

              <div id="relatorio-gerado">

                <div id="mensagem-relatorio">

                </div>

                <table id="tabela-relatorio-contribuicao" class="table table-hover hidden" style="width: 100%">
                  <thead>
                    <tr>
                      <th>Código</th>
                      <th>N. Sócio</th>
                      <th>Plataforma</th>
                      <th>M. pagamento</th>
                      <th>D. emissão</th>
                      <th>D. vencimento</th>
                      <th>D. pagamento</th>
                      <th>Valor</th>
                      <th>Status</th>
                      <!--Ativar novamente quando as opções forem implementadas <th>Opções</th>-->
                    </tr>
                  </thead>
                  <tbody>

                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="box box-warning print-hide">
            <div class="box-header with-border">
              <h3 class="box-title">Visão Geral e Controle</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
              <!-- /.box-tools -->

              <div class="sync-control">
                <button class="btn btn-primary" id="sync-btn" title="Sincroniza contribuições de acordo com os múltiplos gateways de pagamentos cadastrados">Sincronizar pagamentos</button>
                <button class="btn btn-primary" id="fatura-btn" title="Busca as novas faturas de acordo com os múltiplos gateways de pagamentos cadastrados">Carregar faturas de recorrências</button>
                <!--Informações de data e hora da última sincronização -->
                <?php if (is_null($sistemaLogContribuicao)): ?>
                  <p>Sem registros da última atualização realizada.</p>
                <?php elseif (is_array($sistemaLogContribuicao) && $sistemaLogContribuicao[0] instanceof SistemaLog): ?>
                  <p>Última atualização realizada em: <?= $sistemaLogContribuicao[0]->getData()->format('d/m/Y à\s H:i:s') ?></p>
                <?php else: ?>
                  <p>Erro ao tentar buscar data da última atualização.</p>
                <?php endif; ?>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body box_tabela_cobranca">

              <table id="tabela-contribuicoes" class="table table-hover" style="width: 100%">
                <thead>
                  <tr>
                    <th>Código</th>
                    <th>N. Sócio</th>
                    <th>Plataforma</th>
                    <th>M. pagamento</th>
                    <th>D. emissão</th>
                    <th>D. vencimento</th>
                    <th>D. pagamento</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <!--Ativar novamente quando as opções forem implementadas <th>Opções</th>-->
                  </tr>
                </thead>
                <tbody>

                </tbody>
              </table>

            </div>
            <!-- /.box-body -->
          </div>
        </div>
        <!-- end: page -->
      </section>



      <?php require_once("./controller/import_scripts.php"); ?>

      <div align="right">
        <iframe src="https://www.wegia.org/software/footer/socio.html" width="200" height="60" style="border:none;"></iframe>
      </div>

</body>
<script src="./controller/script/relatorios_contribuicao.js"></script>

<script>
  /*function printBy(selector) {
    var $print = $(selector)
      .clone()
      .addClass('printable')
      .prependTo('body');

    // Stop JS execution
    window.print();

    // Remove div once printed
    $print.remove();
  }*/
</script>

</html>