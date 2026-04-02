<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario'])) { header("Location: ../../index.php"); exit(); }
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
?>
<!DOCTYPE html>
<html class="fixed">
<head>
  <meta charset="UTF-8">
  <title>Perfil do Voluntário</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
  <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
  <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
  <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
  <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
  <script src="../../assets/vendor/jquery/jquery.min.js"></script>
</head>
<body>
  <div id="header"></div>
  <div class="inner-wrapper">
    <aside id="sidebar-left" class="sidebar-left menuu"></aside>
    <section role="main" class="content-body">
      <header class="page-header">
        <h2>Perfil do Voluntário</h2>
      </header>
      <div class="row">
        <div class="col-md-12">
          <section class="panel">
            <header class="panel-heading">
              <h2 class="panel-title">Detalhes</h2>
            </header>
            <div class="panel-body">
              <p>Funcionalidade de visualização de detalhes e edição em desenvolvimento.</p>
              <a href="informacao_voluntario.php" class="btn btn-default">Voltar</a>
            </div>
          </section>
        </div>
      </div>
    </section>
  </div>
  <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
  <script>
    $(function() {
      $("#header").load("../header.php");
      $(".menuu").load("../menu.php");
    });
  </script>
</body>
</html>
