<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario'])) { header("Location: ../../index.php"); exit(); }
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once ROOT . "/html/personalizacao_display.php";
?>
<!DOCTYPE html>
<html class="fixed">
<head>
  <meta charset="UTF-8">
  <title>Informações Voluntários</title>
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
        <h2>Informações Voluntários</h2>
      </header>
      <div class="row">
        <div class="col-md-12">
          <section class="panel">
            <header class="panel-heading">
              <h2 class="panel-title">Lista de Voluntários</h2>
            </header>
            <div class="panel-body">
              <?php if (isset($_SESSION['msg'])): ?>
                <div class="alert alert-<?= $_SESSION['tipo'] ?> alert-dismissible" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <?= $_SESSION['msg'] ?>
                </div>
                <?php unset($_SESSION['msg'], $_SESSION['tipo']); ?>
              <?php endif; ?>
              <table class="table table-bordered table-striped mb-none" id="datatable-default">
                <thead>
                  <tr>
                    <th>Nome</th>
                    <th>Sobrenome</th>
                    <th>CPF</th>
                    <th>Situação</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  if (isset($_SESSION['voluntarios'])) {
                      $voluntarios = json_decode($_SESSION['voluntarios'], true);
                      if (is_array($voluntarios)) {
                          foreach($voluntarios as $vol) {
                              echo "<tr>";
                              echo "<td>" . htmlspecialchars($vol['nome']) . "</td>";
                              echo "<td>" . htmlspecialchars($vol['sobrenome']) . "</td>";
                              echo "<td>" . htmlspecialchars($vol['cpf']) . "</td>";
                              echo "<td>" . htmlspecialchars($vol['situacao']) . "</td>";
                              echo "</tr>";
                          }
                      }
                  }
                  ?>
                </tbody>
              </table>
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
