<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE)
  session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../../index.php");
  exit();
}

if (!isset($_SESSION['visitantes'])) {
  header('Location: ../../controle/control.php?metodo=listarTodos&nomeClasse=VisitanteControle&nextPage=../html/visitante/informacao_visitante.php');
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once ROOT . "/html/personalizacao_display.php";
?>
<!DOCTYPE html>
<html class="fixed">

<head>
  <meta charset="UTF-8">
  <title>Informações Visitantes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
  <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
  <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
  <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
  <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
  <script src="../../assets/vendor/modernizr/modernizr.js"></script>
  <script src="../../assets/vendor/jquery/jquery.min.js"></script>
  <script src="../../assets/javascripts/theme.js"></script>
  <script src="../../assets/javascripts/theme.custom.js"></script>
  <script src="../../assets/javascripts/theme.init.js"></script>

</head>

<body>
  <section class="body">
    <div id="header"></div>
    <div class="inner-wrapper">
      <aside id="sidebar-left" class="sidebar-left menuu"></aside>
      <section role="main" class="content-body">
        <header class="page-header">
          <h2>Informações Visitantes</h2>
        </header>
        <div class="row">
          <div class="col-md-12">
            <section class="panel">
              <header class="panel-heading">
                <h2 class="panel-title">Lista de Visitantes</h2>
              </header>
              <div class="panel-body">
                <?php if (isset($_SESSION['msg'])): ?>
                <div class="alert alert-<?= $_SESSION['tipo']?> alert-dismissible" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                      aria-hidden="true">&times;</span></button>
                  <?= $_SESSION['msg']?>
                </div>
                <?php unset($_SESSION['msg'], $_SESSION['tipo']); ?>
                <?php
endif; ?>
                <table class="table table-bordered table-striped mb-none" id="datatable-default">
                  <thead>
                    <tr>
                      <th>Nome</th>
                      <th>Sobrenome</th>
                      <th>CPF</th>
                      <th>Situação</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
if (isset($_SESSION['visitantes'])) {
  $visitantes = json_decode($_SESSION['visitantes'], true);
  if (is_array($visitantes)) {
    foreach ($visitantes as $visitante) {
      if (strtolower($visitante['situacao']) !== 'ativo') {
        echo "<tr style='background-color: #ededed; color: #777;'>";
      } else {
        echo "<tr>";
      }
      echo "<td>" . htmlspecialchars($visitante['nome']) . "</td>";
      echo "<td>" . htmlspecialchars($visitante['sobrenome']) . "</td>";
      echo "<td>" . htmlspecialchars($visitante['cpf']) . "</td>";
      echo "<td>" . htmlspecialchars($visitante['situacao']) . "</td>";
      echo "<td><a href='profile_visitante.php?id_visitante=" . urlencode($visitante['id_visitante']) . "'><i class='fas fa-user-edit'></i></a></td>";
      echo "</tr>";
    }
  }
  unset($_SESSION['visitantes']);
}
?>
                  </tbody>
                </table>
              </div>
            </section>
          </div>
        </div>
    </div>
  </section>
  </div>
  </section>
  <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
  <script>
    $(function () {
      $("#header").load("../header.php");
      $(".menuu").load("../menu.php");
    });
  </script>
</body>

</html>