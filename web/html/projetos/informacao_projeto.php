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

permissao($id_pessoa, 82, 1);

require_once ROOT . "/dao/ProjetoDAO.php";
require_once ROOT . "/controle/ProjetoControle.php";

$projetoControle = new ProjetoControle();
$statusList      = $projetoControle->obterStatus();
$projetos        = (new ProjetoDAO())->listarTodos();

require_once ROOT . "/html/personalizacao_display.php";

$msg = isset($_GET['msg']) ? htmlspecialchars($_GET['msg'], ENT_QUOTES, 'UTF-8') : '';
?>
<!DOCTYPE html>
<html class="fixed">

<head>
  <meta charset="UTF-8">
  <title>Informações de Projetos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

  <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
  <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
  <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
  <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon">
  <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
  <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
  <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">

  <style>
    .table tbody tr { cursor: pointer; transition: background-color 0.2s; }
    .table tbody tr:hover { background-color: #f5f5f5 !important; }
  </style>
</head>

<body>
  <div id="header"></div>
  <div class="inner-wrapper">
    <aside id="sidebar-left" class="sidebar-left menuu"></aside>

    <section role="main" class="content-body">
      <header class="page-header">
        <h2>Informações de Projetos</h2>
        <div class="right-wrapper pull-right">
          <ol class="breadcrumbs">
            <li><a href="../home.php"><i class="fa fa-home"></i></a></li>
            <li><span>Projetos</span></li>
            <li><span>Informações Projetos</span></li>
          </ol>
          <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
        </div>
      </header>

      <div class="row">
        <div class="col-md-12">
          <section class="panel">
            <header class="panel-heading">
              <h2 class="panel-title">Lista de Projetos</h2>
            </header>
            <div class="panel-body">
              <?php if (!empty($msg)): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                  <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                  <?php echo $msg; ?>
                </div>
              <?php endif; ?>

              <div class="form-inline" style="margin-bottom:20px;">
                <div class="form-group">
                  <label for="filtro_status" style="margin-right:10px;">Status:</label>
                  <select class="form-control input-sm" id="filtro_status" style="width:auto;display:inline-block;">
                    <option value="">Todos</option>
                    <?php foreach ($statusList as $status): ?>
                      <option value="<?= $status['id_status'] ?>"><?= htmlspecialchars($status['descricao']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="filtrarPorStatus()" style="margin-left:10px;">
                  <i class="fa fa-filter"></i> Filtrar
                </button>
              </div>

              <div class="table-responsive">
                <table class="table table-bordered table-striped mb-none">
                  <thead>
                    <tr>
                      <th>Nome</th><th>Tipo</th><th>Local</th><th>Status</th><th>Descrição</th>
                    </tr>
                  </thead>
                  <tbody id="tbody-projetos"></tbody>
                </table>
              </div>
            </div>
          </section>
        </div>
      </div>
    </section>
  </div>

  <script src="../../assets/vendor/jquery/jquery.min.js"></script>
  <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
  <script>window.projetosData = <?= json_encode($projetos) ?>;</script>
  <script src="../../Functions/projetos_informacao.js"></script>
  <script>
    $(function() {
      $("#header").load("../header.php");
      $(".menuu").load("../menu.php");
    });
  </script>
</body>
</html>