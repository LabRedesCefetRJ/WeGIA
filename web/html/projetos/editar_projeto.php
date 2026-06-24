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

$id_projeto = filter_input(INPUT_GET, 'id_projeto', FILTER_SANITIZE_NUMBER_INT);

if (!$id_projeto || $id_projeto < 1) {
  header('Location: informacao_projeto.php?msg=ID do projeto inválido!');
  exit();
}

require_once ROOT . "/dao/ProjetoDAO.php";
require_once ROOT . "/controle/ProjetoControle.php";

$projetoControle = new ProjetoControle();

if (isset($_SESSION['projeto'])) {
  $projeto = $_SESSION['projeto'];
  unset($_SESSION['projeto']);
} else {
  $projetoDAO = new ProjetoDAO();
  $projeto    = $projetoDAO->listarUm($id_projeto);
  if (!$projeto) {
    header('Location: informacao_projeto.php?msg=Projeto não encontrado!');
    exit();
  }
}

$tipos         = $projetoControle->obterTipos();
$locais        = $projetoControle->obterLocais();
$statusProjeto = $projetoControle->obterStatus();

$executanteDAO = new ProjetoDAO();
$executantes   = $executanteDAO->listarFuncionariosAtivos();

$projetoDAO = new ProjetoDAO();
$funcoes    = $projetoDAO->listarFuncoesProjeto();

require_once ROOT . "/html/personalizacao_display.php";
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
?>
<!DOCTYPE html>
<html class="fixed">

<head>
  <meta charset="UTF-8">
  <title>Editar Projeto</title>
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
  <link rel="stylesheet" href="../../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

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
        <h2>Editar Projeto</h2>
        <div class="right-wrapper pull-right">
          <ol class="breadcrumbs">
            <li><a href="../home.php"><i class="fa fa-home"></i></a></li>
            <li><span>Projetos</span></li>
            <li><span>Editar Projeto</span></li>
          </ol>
          <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
        </div>
      </header>

      <div class="row">
        <div class="col-md-12">
          <div class="tabs">
            <ul class="nav nav-tabs tabs-primary">
              <li class="active"><a href="#overview" data-toggle="tab">Dados do Projeto</a></li>
              <li><a href="#equipe" data-toggle="tab">Equipe do Projeto</a></li>
              <li><a href="#atendidos" data-toggle="tab">Atendidos</a></li>
              <li><a href="#turmas" data-toggle="tab">Turmas</a></li>
            </ul>
            <div class="tab-content">

              <!-- ABA 1: Dados do Projeto -->
              <div id="overview" class="tab-pane active">
                <div class="row">
                  <div class="col-md-8">
                    <form class="form-horizontal" role="form">
                      <h4 class="mb-xlg">Informações do Projeto</h4>
                      <h5 class="obrig">Campos Obrigatórios(*)</h5>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="nome_projeto">Nome do Projeto<sup class="obrig">*</sup></label>
                        <div class="col-md-8">
                          <input type="text" class="form-control" id="nome_projeto" value="<?= htmlspecialchars($projeto['nome']) ?>" required>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="tipo_projeto">Tipo de Projeto<sup class="obrig">*</sup></label>
                        <div class="col-md-8">
                          <select class="form-control" id="tipo_projeto" required>
                            <option selected disabled>Selecionar</option>
                            <?php foreach ($tipos as $tipo): ?>
                              <option value="<?= $tipo['id_tipo'] ?>" <?= ($tipo['id_tipo'] == $projeto['id_tipo']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tipo['descricao']) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="local_projeto">Local<sup class="obrig">*</sup></label>
                        <div class="col-md-8">
                          <select class="form-control" id="local_projeto" required>
                            <option selected disabled>Selecionar</option>
                            <?php foreach ($locais as $local): ?>
                              <option value="<?= $local['id_local'] ?>" <?= ($local['id_local'] == $projeto['id_local']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($local['nome']) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="status_projeto">Status<sup class="obrig">*</sup></label>
                        <div class="col-md-8">
                          <select class="form-control" id="status_projeto" required>
                            <option selected disabled>Selecionar</option>
                            <?php foreach ($statusProjeto as $status): ?>
                              <option value="<?= $status['id_status'] ?>" <?= ($status['id_status'] == $projeto['id_status']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($status['descricao']) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="data_inicio">Data de Início<sup class="obrig">*</sup></label>
                        <div class="col-md-8">
                          <input type="date" class="form-control" id="data_inicio" value="<?= htmlspecialchars($projeto['data_inicio']) ?>" required>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="data_fim">Data de Término</label>
                        <div class="col-md-8">
                          <input type="date" class="form-control" id="data_fim" value="<?= htmlspecialchars($projeto['data_fim'] ?? '') ?>">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="descricao_projeto">Descrição</label>
                        <div class="col-md-8">
                          <textarea class="form-control" id="descricao_projeto" rows="4"><?= htmlspecialchars($projeto['descricao'] ?? '') ?></textarea>
                        </div>
                      </div>

                      <div class="form-group">
                        <div class="col-md-8 col-md-offset-3">
                          <input type="hidden" id="csrf_token" value="<?= Csrf::generateToken() ?>">
                          <input type="hidden" id="id_projeto" value="<?= $id_projeto ?>">
                          <button type="button" class="btn btn-primary" id="btn-salvar">Salvar Alterações</button>
                          <a href="informacao_projeto.php" class="btn btn-default">Cancelar</a>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

              <!-- ABA 2: Equipe do Projeto -->
              <div id="equipe" class="tab-pane">
                <div class="row">
                  <div class="col-md-12">
                    <section class="panel">
                      <header class="panel-heading">
                        <div class="panel-actions">
                          <a href="#" class="fa fa-caret-down"></a>
                        </div>
                        <h2 class="panel-title">Membros da Equipe</h2>
                      </header>
                      <div class="panel-body">

                        <h4>Adicionar Novo Membro</h4>
                        <form class="form-horizontal" role="form">
                          <div class="form-group">
                            <label class="col-md-2 control-label" for="novo_funcionario">Executante<sup class="obrig">*</sup></label>
                            <div class="col-md-4">
                              <select id="novo_funcionario" class="form-control">
                                <option selected disabled>Selecionar Executante</option>
                                <?php foreach ($executantes as $executante): ?>
                                  <option value="<?= $executante['id_pessoa'] ?>">
                                    <?= htmlspecialchars($executante['nome'] . ' ' . ($executante['sobrenome'] ?? '')) ?>
                                  </option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                            <label class="col-md-2 control-label" for="nova_funcao">Cargo/Função<sup class="obrig">*</sup></label>
                            <div class="col-md-3">
                              <div style="display: flex; align-items: center;">
                                <select id="nova_funcao" class="form-control" style="flex: 1;">
                                  <option selected disabled>Selecionar Função</option>
                                  <?php foreach ($funcoes as $funcao): ?>
                                    <option value="<?= $funcao['id_funcao'] ?>">
                                      <?= htmlspecialchars($funcao['descricao']) ?>
                                    </option>
                                  <?php endforeach; ?>
                                </select>
                                <a onclick="adicionarNovaFuncao()" style="cursor:pointer; margin-left: 8px; display: flex; align-items: center;">
                                  <i class="fas fa-plus" style="font-size: 20px; color: #007bff;"></i>
                                </a>
                              </div>
                            </div>
                            <div class="col-md-1">
                              <button type="button" id="btn-adicionar-membro" class="btn btn-primary" onclick="adicionarMembroEquipe()">
                                <i class="fa fa-plus"></i>
                              </button>
                            </div>
                          </div>
                        </form>

                        <hr class="dotted short">
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label for="filtro_funcao_equipe" class="control-label" style="font-weight: normal;">Filtrar por Cargo/Função:</label>
                              <select id="filtro_funcao_equipe" class="form-control input-sm" style="width: 250px; display: inline-block;" onchange="filtrarEquipePorFuncao()">
                                <!-- Será preenchido dinamicamente -->
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="table-responsive">
                          <table class="table table-bordered table-striped mb-none" id="equipe-table">
                            <thead>
                              <tr>
                                <th>Executante</th>
                                <th>CPF</th>
                                <th>Cargo/Função</th>
                                <th class="text-center" width="100">Ação</th>
                              </tr>
                            </thead>
                            <tbody id="equipe-tab">
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </section>
                  </div>
                </div>
              </div>

              <!-- ABA 3: Atendidos do Projeto -->
              <div id="atendidos" class="tab-pane">
                <div class="row">
                  <div class="col-md-12">
                    <section class="panel">
                      <header class="panel-heading">
                        <div class="panel-actions">
                          <a href="#" class="fa fa-caret-down"></a>
                        </div>
                        <h2 class="panel-title">Atendidos Vinculados</h2>
                      </header>
                      <div class="panel-body">

                        <h4>Vincular Novo Atendido</h4>
                        <form class="form-horizontal" role="form">
                          <div class="form-group">
                            <label class="col-md-2 control-label" for="novo_atendido">Atendido<sup class="obrig">*</sup></label>
                            <div class="col-md-4">
                              <select id="novo_atendido" class="form-control">
                                <option selected disabled>Selecionar Atendido</option>
                              </select>
                            </div>
                            <div class="col-md-1">
                              <button type="button" id="btn-adicionar-atendido" class="btn btn-primary" onclick="adicionarAtendidoProjeto()">
                                <i class="fa fa-plus"></i>
                              </button>
                            </div>
                          </div>
                        </form>

                        <hr class="dotted short">

                        <!-- FILTRO POR STATUS -->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label for="filtro_status_atendido" class="control-label" style="font-weight: normal;">Filtrar por Status:</label>
                              <select id="filtro_status_atendido" class="form-control input-sm" style="width: 250px; display: inline-block;" onchange="filtrarAtendidosPorStatus()">
                                <!-- Será preenchido dinamicamente -->
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="table-responsive">
                          <table class="table table-bordered table-striped mb-none" id="atendidos-table">
                            <thead>
                              <tr>
                                <th>Atendido</th>
                                <th>CPF</th>
                                <th>Status</th>
                                <th class="text-center" width="100">Ação</th>
                              </tr>
                            </thead>
                            <tbody id="atendidos-tab">
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </section>
                  </div>
                </div>
              </div>

              <!-- ABA 4: Turmas -->
              <div id="turmas" class="tab-pane">
                <div class="row">
                  <div class="col-md-12">
                    <section class="panel">
                      <header class="panel-heading">
                        <div class="panel-actions">
                          <a href="#" class="fa fa-caret-down"></a>
                        </div>
                        <h2 class="panel-title">Turmas do Projeto</h2>
                      </header>
                      <div class="panel-body">

                        <h4>Nova Turma</h4>
                        <form class="form-horizontal" role="form">
                          <div class="form-group">
                            <label class="col-md-2 control-label" for="nova_turma_nome">Nome<sup class="obrig">*</sup></label>
                            <div class="col-md-4">
                              <input type="text" id="nova_turma_nome" class="form-control" maxlength="150" placeholder="Nome da turma">
                            </div>
                            <label class="col-md-1 control-label" for="nova_turma_descricao">Descrição</label>
                            <div class="col-md-4">
                              <input type="text" id="nova_turma_descricao" class="form-control" maxlength="255" placeholder="Descrição (opcional)">
                            </div>
                            <div class="col-md-1">
                              <button type="button" id="btn-adicionar-turma" class="btn btn-primary" onclick="adicionarTurma()">
                                <i class="fa fa-plus"></i>
                              </button>
                            </div>
                          </div>
                        </form>
                        <hr class="dotted short">
                        <div class="table-responsive">
                          <table class="table table-bordered table-striped mb-none" id="turmas-table">
                            <thead>
                              <tr>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th class="text-center" width="100">Ação</th>
                              </tr>
                            </thead>
                            <tbody id="turmas-tab">
                            </tbody>
                          </table>
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
  <script src="../../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
  <script src="../../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
  <script src="../../Functions/projetos_editar.js"></script>
  <script src="../../Functions/projetos_equipe.js"></script>
  <script src="../../Functions/projetos_atendido.js"></script>
  <script src="../../Functions/projetos_turma.js"></script>
</body>

</html>