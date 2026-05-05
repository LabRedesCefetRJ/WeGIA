<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
Util::definirFusoHorario();
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
  exit();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once ROOT . "/html/permissao/permissao.php";

$id_pessoa = filter_var($_SESSION['id_pessoa'], FILTER_SANITIZE_NUMBER_INT);

if ($id_pessoa < 1) {
  http_response_code(400);
  echo json_encode(['erro' => 'O id da pessoa informado é inválido']);
  exit();
}

permissao($id_pessoa, 81, 3);

require_once ROOT . "/dao/projetos/ProjetoDAO.php";
$projetoDAO = new ProjetoDAO();

$tipos = $projetoDAO->listarTiposProjeto();
$locais = $projetoDAO->listarLocaisProjeto();
$statusProjeto = $projetoDAO->listarStatusProjeto();

require_once ROOT . "/html/personalizacao_display.php";

if (isset($_GET['msg'])) {
  $mensagem = htmlspecialchars($_GET['msg'], ENT_QUOTES, 'UTF-8');
  $mensagem = trim(strip_tags($mensagem));

  if (!preg_match('/^[\p{L}\p{N} ]+$/u', $mensagem)) {
    exit();
  }

  $mensagem = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8');
  $mensagem = json_encode($mensagem, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

  echo <<<HTML
    <script>
        alert($mensagem);
        window.location.href = "../../html/projetos/cadastrar_projeto.php";
    </script>
    HTML;
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
?>
<!DOCTYPE html>
<html class="fixed">

<head>
  <meta charset="UTF-8">
  <title>Cadastro de Projeto</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

  <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

  <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
  <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
  <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
  <link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
  <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon">

  <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
  <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
  <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">

  <style type="text/css">
    .obrig {
      color: rgb(255, 0, 0);
    }
  </style>
</head>

<body>
  <div id="header"></div>
  <div class="inner-wrapper">
    <aside id="sidebar-left" class="sidebar-left menuu"></aside>

    <section role="main" class="content-body">
      <header class="page-header">
        <h2>Cadastro de Projeto</h2>
        <div class="right-wrapper pull-right">
          <ol class="breadcrumbs">
            <li>
              <a href="../home.php">
                <i class="fa fa-home"></i>
              </a>
            </li>
            <li><span>Projetos</span></li>
            <li><span>Cadastrar Projeto</span></li>
          </ol>
          <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
        </div>
      </header>

      <div class="row" id="formulario">
        <form class="form-horizontal" method="POST" action="../../controle/control.php">
          <div class="row">
            <div class="col-md-8 col-lg-8">
              <div class="tabs">
                <ul class="nav nav-tabs tabs-primary">
                  <li class="active">
                    <a href="#overview" data-toggle="tab">Cadastro de Projetos</a>
                  </li>
                </ul>
                <div class="tab-content">
                  <div id="overview" class="tab-pane active">
                    <h4 class="mb-xlg">Informações do Projeto</h4>
                    <h5 class="obrig">Campos Obrigatórios(*)</h5>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="nome_projeto">Nome do Projeto<sup class="obrig">*</sup></label>
                      <div class="col-md-8">
                        <input type="text" class="form-control" name="nome_projeto" id="nome_projeto" onkeypress="return Onlychars(event)" required>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="tipo_projeto">Tipo de Projeto<sup class="obrig">*</sup></label>
                      <a onclick="adicionar_tipo()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                      <div class="col-md-8">
                        <select class="form-control input-lg mb-md" name="tipo_projeto" id="tipo_projeto" required>
                          <option selected disabled>Selecionar</option>
                          <?php
                          foreach ($tipos as $tipo) {
                            echo "<option value='{$tipo['id_tipo']}'>" . htmlspecialchars($tipo['descricao']) . "</option>";
                          }
                          ?>
                        </select>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="local_projeto">Local<sup class="obrig">*</sup></label>
                      <a onclick="adicionar_local()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                      <div class="col-md-8">
                        <select class="form-control input-lg mb-md" name="local_projeto" id="local_projeto" required>
                          <option selected disabled>Selecionar</option>
                          <?php
                          foreach ($locais as $local) {
                            echo "<option value='{$local['id_local']}'>" . htmlspecialchars($local['nome']) . "</option>";
                          }
                          ?>
                        </select>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="status_projeto">Status<sup class="obrig">*</sup></label>
                      <a onclick="adicionar_status()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                      <div class="col-md-8">
                        <select class="form-control input-lg mb-md" name="status_projeto" id="status_projeto" required>
                          <option selected disabled>Selecionar</option>
                          <?php
                          foreach ($statusProjeto as $status) {
                            echo "<option value='{$status['id_status']}'>" . htmlspecialchars($status['descricao']) . "</option>";
                          }
                          ?>
                        </select>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="data_inicio">Data de Início<sup class="obrig">*</sup></label>
                      <div class="col-md-8">
                        <input type="date" class="form-control" name="data_inicio" id="data_inicio" required>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="data_fim">Data de Término</label>
                      <div class="col-md-8">
                        <input type="date" class="form-control" name="data_fim" id="data_fim">
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="descricao_projeto">Descrição</label>
                      <div class="col-md-8">
                        <textarea class="form-control" name="descricao_projeto" id="descricao_projeto" rows="4"></textarea>
                      </div>
                    </div>

                    <hr class="dotted short">

                    <div class="panel-footer">
                      <div class="row">
                        <div class="col-md-9 col-md-offset-3">
                          <input type="hidden" name="nomeClasse" value="ProjetoControle">
                          <?= Csrf::inputField() ?>
                          <input type="hidden" name="metodo" value="incluir">
                          <input id="enviar" type="submit" class="btn btn-primary" value="Salvar">
                          <input type="reset" class="btn btn-default" value="Limpar">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </section>
  </div>
  <script src="../../assets/vendor/jquery/jquery.min.js"></script>
  <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
  <script src="../../Functions/onlyChars.js"></script>
  <script src="../../Functions/onlyNumbers.js"></script>
  <script src="../../Functions/lista.js"></script>
  <script src="../../Functions/projetos_cadastrar.js"></script>

  <script type="text/javascript">
    $(function() {
      $("#header").load("../header.php");
      $(".menuu").load("../menu.php");
    });
  </script>
</body>

</html>