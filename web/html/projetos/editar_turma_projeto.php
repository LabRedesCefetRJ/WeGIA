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

$id_turma = filter_input(INPUT_GET, 'id_turma', FILTER_SANITIZE_NUMBER_INT);

if (!$id_turma || $id_turma < 1) {
  header('Location: informacao_projeto.php?msg=ID da turma inválido!');
  exit();
}

require_once ROOT . "/dao/ProjetoDAO.php";
require_once ROOT . "/controle/ProjetoControle.php";

$projetoControle = new ProjetoControle();
$projetoDAO       = new ProjetoDAO();

$turma = $projetoDAO->listarUmaTurma($id_turma);

if (!$turma) {
  header('Location: informacao_projeto.php?msg=Turma não encontrada!');
  exit();
}

$id_projeto = $turma['id_projeto'];
$projeto    = $projetoDAO->listarUm($id_projeto);

if (!$projeto) {
  header('Location: informacao_projeto.php?msg=Projeto não encontrado!');
  exit();
}

require_once ROOT . "/html/personalizacao_display.php";
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
?>
<!DOCTYPE html>
<html class="fixed">

<head>
  <meta charset="UTF-8">
  <title>Editar Turma</title>
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
    .obrig {
      color: rgb(255, 0, 0);
    }

    .pagination-container {
      margin-top: 12px;
      display: flex;
      justify-content: flex-end;
    }
  </style>
</head>

<body>
  <div id="header"></div>
  <div class="inner-wrapper">
    <aside id="sidebar-left" class="sidebar-left menuu"></aside>

    <section role="main" class="content-body">
      <header class="page-header">
        <h2>Editar Turma</h2>
        <div class="right-wrapper pull-right">
          <ol class="breadcrumbs">
            <li><a href="../home.php"><i class="fa fa-home"></i></a></li>
            <li><span>Projetos</span></li>
            <li><a href="editar_projeto.php?id_projeto=<?= $id_projeto ?>">Editar Projeto</a></li>
            <li><span>Editar Turma</span></li>
          </ol>
          <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
        </div>
      </header>

      <div class="row">
        <div class="col-md-12">
          <div class="tabs">
            <ul class="nav nav-tabs tabs-primary">
              <li class="active"><a href="#overview" data-toggle="tab">Dados da Turma</a></li>
              <li><a href="#executantes" data-toggle="tab">Executantes</a></li>
              <li><a href="#atendidos" data-toggle="tab">Atendidos</a></li>
            </ul>
            <div class="tab-content">

              <!-- ABA 1: Dados da Turma -->
              <div id="overview" class="tab-pane active">
                <div class="row">
                  <div class="col-md-8">
                    <form class="form-horizontal" role="form">
                      <h4 class="mb-xlg">Informações da Turma</h4>
                      <h5 class="obrig">Campos Obrigatórios(*)</h5>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="nome_turma">Nome<sup class="obrig">*</sup></label>
                        <div class="col-md-8">
                          <input type="text" class="form-control" id="nome_turma" maxlength="150" value="<?= htmlspecialchars($turma['nome']) ?>" required>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="descricao_turma">Descrição</label>
                        <div class="col-md-8">
                          <textarea class="form-control" id="descricao_turma" maxlength="255" rows="4"><?= htmlspecialchars($turma['descricao'] ?? '') ?></textarea>
                        </div>
                      </div>

                      <div class="form-group">
                        <div class="col-md-8 col-md-offset-3">
                          <input type="hidden" id="csrf_token" value="<?= Csrf::generateToken() ?>">
                          <input type="hidden" id="id_turma" value="<?= $id_turma ?>">
                          <input type="hidden" id="id_projeto" value="<?= $id_projeto ?>">
                          <button type="button" class="btn btn-primary" id="btn-salvar">Salvar Alterações</button>
                          <a href="editar_projeto.php?id_projeto=<?= $id_projeto ?>" class="btn btn-default">Cancelar</a>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

              <!-- ABA 2: Executantes da Turma -->
              <div id="executantes" class="tab-pane">
                <div class="row">
                  <div class="col-md-12">
                    <section class="panel">
                      <header class="panel-heading">
                        <div class="panel-actions">
                          <a href="#" class="fa fa-caret-down"></a>
                        </div>
                        <h2 class="panel-title">Executantes da Turma</h2>
                      </header>
                      <div class="panel-body">

                        <h4>Adicionar Executante à Turma</h4>
                        <form class="form-horizontal" role="form">
                          <div class="form-group">
                            <label class="col-md-2 control-label" for="novo_executante_turma">Executante<sup class="obrig">*</sup></label>
                            <div class="col-md-4">
                              <select id="novo_executante_turma" class="form-control">
                                <option selected disabled>Selecionar Executante</option>
                              </select>
                            </div>
                            <div class="col-md-1">
                              <button type="button" id="btn-adicionar-executante-turma" class="btn btn-primary" onclick="adicionarExecutanteNaTurma()">
                                <i class="fa fa-plus"></i>
                              </button>
                            </div>
                          </div>
                        </form>
                        <hr class="dotted short">
                        <div class="table-responsive">
                          <table class="table table-bordered table-striped mb-none">
                            <thead>
                              <tr>
                                <th>Executante</th>
                                <th>CPF</th>
                                <th class="text-center" width="100">Ação</th>
                              </tr>
                            </thead>
                            <tbody id="executantes-turma-tab">
                              <tr>
                                <td colspan="3" class="text-center">Carregando...</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <div class="pagination-container">
                          <ul class="pagination pagination-sm" id="executantes-turma-paginacao"></ul>
                        </div>
                      </div>
                    </section>
                  </div>
                </div>
              </div>

              <!-- ABA 3: Atendidos da Turma -->
              <div id="atendidos" class="tab-pane">
                <div class="row">
                  <div class="col-md-12">
                    <section class="panel">
                      <header class="panel-heading">
                        <div class="panel-actions">
                          <a href="#" class="fa fa-caret-down"></a>
                        </div>
                        <h2 class="panel-title">Atendidos da Turma</h2>
                      </header>
                      <div class="panel-body">

                        <h4>Adicionar Atendido à Turma</h4>
                        <form class="form-horizontal" role="form">
                          <div class="form-group">
                            <label class="col-md-2 control-label" for="novo_atendido_turma">Atendido<sup class="obrig">*</sup></label>
                            <div class="col-md-4">
                              <select id="novo_atendido_turma" class="form-control">
                                <option selected disabled>Selecionar Atendido</option>
                              </select>
                            </div>
                            <div class="col-md-1">
                              <button type="button" id="btn-adicionar-atendido-turma" class="btn btn-primary" onclick="adicionarAtendidoNaTurma()">
                                <i class="fa fa-plus"></i>
                              </button>
                            </div>
                          </div>
                        </form>
                        <hr class="dotted short">
                        <div class="table-responsive">
                          <table class="table table-bordered table-striped mb-none">
                            <thead>
                              <tr>
                                <th>Atendido</th>
                                <th>CPF</th>
                                <th class="text-center" width="100">Ação</th>
                              </tr>
                            </thead>
                            <tbody id="atendidos-turma-tab">
                              <tr>
                                <td colspan="3" class="text-center">Carregando...</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <div class="pagination-container">
                          <ul class="pagination pagination-sm" id="atendidos-turma-paginacao"></ul>
                        </div>
                      </div>
                    </section>
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
  <script src="../../Functions/projetos_turma_editar.js"></script>

  <script type="text/javascript">
    $(function() {
      $("#header").load("../header.php");
      $(".menuu").load("../menu.php");
    });
  </script>
</body>

</html>