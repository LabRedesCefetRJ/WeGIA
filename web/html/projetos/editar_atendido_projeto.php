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
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

$id_pessoa = filter_var($_SESSION['id_pessoa'], FILTER_SANITIZE_NUMBER_INT);

if ($id_pessoa < 1) {
  http_response_code(400);
  echo json_encode(['erro' => 'O id da pessoa informado é inválido']);
  exit();
}

permissao($id_pessoa, 81, 7);

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$id || $id < 1) {
  header('Location: informacao_projeto.php?msg=ID inválido!');
  exit();
}

require_once ROOT . "/dao/ProjetoDAO.php";
require_once ROOT . "/controle/ProjetoControle.php";

$projetoDAO = new ProjetoDAO();

$atendido = $projetoDAO->buscarAtendidoProjeto($id);

if (!$atendido) {
  header('Location: informacao_projeto.php?msg=Atendido não encontrado!');
  exit();
}

$id_projeto = $atendido['id_projeto'];
$projeto    = $projetoDAO->listarUm($id_projeto);

if (!$projeto) {
  header('Location: informacao_projeto.php?msg=Projeto não encontrado!');
  exit();
}

$statusList = $projetoDAO->listarStatusAtendidoProjeto();

require_once ROOT . "/html/personalizacao_display.php";
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
?>
<!DOCTYPE html>
<html class="fixed">

<head>
  <meta charset="UTF-8">
  <title>Editar Atendido</title>
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
    .obrig { color: rgb(255, 0, 0); }
    .campo-readonly { background-color: #f5f5f5; cursor: not-allowed; }
  </style>
</head>

<body>
  <div id="header"></div>
  <div class="inner-wrapper">
    <aside id="sidebar-left" class="sidebar-left menuu"></aside>

    <section role="main" class="content-body">
      <header class="page-header">
        <h2>Editar Atendido</h2>
        <div class="right-wrapper pull-right">
          <ol class="breadcrumbs">
            <li><a href="../home.php"><i class="fa fa-home"></i></a></li>
            <li><span>Projetos</span></li>
            <li><a href="editar_projeto.php?id_projeto=<?= $id_projeto ?>">Editar Projeto</a></li>
            <li><span>Editar Atendido</span></li>
          </ol>
          <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
        </div>
      </header>

      <div class="row">
        <div class="col-md-12">
          <div class="tabs">
            <ul class="nav nav-tabs tabs-primary">
              <li class="active"><a href="#dados" data-toggle="tab">Dados do Atendido</a></li>
            </ul>
            <div class="tab-content">
              <div id="dados" class="tab-pane active">
                <div class="row">
                  <div class="col-md-8">
                    <form class="form-horizontal" role="form">
                      <h4 class="mb-xlg">Dados do Atendido no Projeto</h4>
                      <h5 class="obrig">Campos Obrigatórios(*)</h5>

                      <div class="form-group">
                        <label class="col-md-3 control-label">Nome</label>
                        <div class="col-md-8">
                          <input type="text" class="form-control campo-readonly"
                            value="<?= htmlspecialchars(trim($atendido['nome'] . ' ' . $atendido['sobrenome'])) ?>"
                            readonly>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label">CPF</label>
                        <div class="col-md-8">
                          <input type="text" class="form-control campo-readonly"
                            value="<?= htmlspecialchars($atendido['cpf'] ?? '--') ?>"
                            readonly>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label">Projeto</label>
                        <div class="col-md-8">
                          <input type="text" class="form-control campo-readonly"
                            value="<?= htmlspecialchars($projeto['nome']) ?>"
                            readonly>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="id_status">Status<sup class="obrig">*</sup></label>
                        <div class="col-md-8">
                          <select id="id_status" class="form-control" required>
                            <option value="" disabled>Selecionar Status</option>
                            <?php foreach ($statusList as $status): ?>
                              <option value="<?= $status['id_status'] ?>"
                                <?= ($status['id_status'] == $atendido['id_status']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($status['descricao']) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>

                      <div class="form-group">
                        <div class="col-md-8 col-md-offset-3">
                          <input type="hidden" id="csrf_token" value="<?= Csrf::generateToken() ?>">
                          <input type="hidden" id="id_vinculo" value="<?= $id ?>">
                          <input type="hidden" id="id_projeto" value="<?= $id_projeto ?>">
                          <button type="button" class="btn btn-primary" id="btn-salvar">Salvar Alterações</button>
                          <a href="editar_projeto.php?id_projeto=<?= $id_projeto ?>" class="btn btn-default">Cancelar</a>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <script src="../../assets/vendor/jquery/jquery.min.js"></script>
  <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
  <script src="../../Functions/projetos_atendido_editar.js"></script>

  <script type="text/javascript">
    $(function() {
      $("#header").load("../header.php");
      $(".menuu").load("../menu.php");
    });
  </script>
</body>

</html>