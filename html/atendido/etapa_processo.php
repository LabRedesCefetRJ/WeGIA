<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: processo_aceitacao.php");
    exit();
}

$idProcesso = (int)$_GET['id'];

require_once '../../dao/Conexao.php';
require_once '../../dao/PaEtapaDAO.php';
require_once '../../dao/PaStatusDAO.php';
require_once '../../dao/ProcessoAceitacaoDAO.php';
require_once '../../dao/EtapaArquivoDAO.php';
require_once "../personalizacao_display.php";

$pdo         = Conexao::connect();
$etapaDAO    = new PaEtapaDAO($pdo);
$statusDAO   = new PaStatusDAO($pdo);
$procDAO     = new ProcessoAceitacaoDAO($pdo);
$arqEtapaDAO = new EtapaArquivoDAO($pdo);

$etapas    = $etapaDAO->listarPorProcesso($idProcesso);
$statuses  = $statusDAO->listarTodos();
$processo  = $procDAO->buscarResumoPorId($idProcesso);

$nomeCompleto     = $processo ? ($processo['nome'] . ' ' . $processo['sobrenome']) : ('Processo #' . $idProcesso);
$processoStatusId = isset($processo['id_status']) ? (int)$processo['id_status'] : null;

$msg   = $_SESSION['msg'] ?? '';
$error = $_SESSION['mensagem_erro'] ?? '';
unset($_SESSION['msg'], $_SESSION['mensagem_erro']);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Etapas</title>

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
    <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css" />

    <script src="../../assets/vendor/modernizr/modernizr.js"></script>
      <style>
    .btn-gray-dark {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
        color: #fff !important;
    }
    .btn-gray-dark:hover {
        background-color: #5a6268 !important;
        border-color: #545b62 !important;
    }
    </style>



</head>

<body>
    <section class="body">
        <div id="header"></div>

        <div class="inner-wrapper">
            <aside id="sidebar-left" class="sidebar-left menuu"></aside>

            <section role="main" class="content-body">
                <header class="page-header">
                    <h2>Etapas do Processo</h2>
                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li>
                                <a href="../home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li><a href="processo_aceitacao.php">Processo de Aceitação</a></li>
                            <li><span>Etapas do Processo</span></li>
                        </ol>
                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>

                <?php if ($msg): ?>
                    <div class="alert alert-success alert-block">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <p><?= htmlspecialchars($msg) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-block">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <p><?= htmlspecialchars($error) ?></p>
                    </div>
                <?php endif; ?>


                <div class="d-flex align-items-center" style="margin-bottom: 15px;">
                    <button type="button" class="btn btn-secondary btn-gray-dark" data-toggle="modal" data-target="#modalStatusProcesso">
                        Alterar Status do Processo
                    </button>

                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalNovaEtapa">
                        Cadastrar Etapas
                    </button>
                </div>

                <section class="panel panel-primary">
                    <header class="panel-heading">
                        <h2 class="panel-title">Etapas do Processo de <?= htmlspecialchars($nomeCompleto) ?></h2>
                    </header>
                    <div class="panel-body">
                        <?php if (empty($etapas)): ?>
                            <div class="alert alert-warning">
                                Nenhuma etapa cadastrada para este processo.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Data de Início</th>
                                            <th>Data de Conclusão</th>
                                            <th>Status</th>
                                            <th>Descrição</th>
                                            <th>Arquivos</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($etapas as $etapa): ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($etapa['data_inicio'])) ?></td>
                                                <td>
                                                    <?php
                                                    if (!empty($etapa['data_fim'])) {
                                                        echo date('d/m/Y', strtotime($etapa['data_fim']));
                                                    } else {
                                                        echo 'Em andamento';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?= htmlspecialchars($etapa['status_nome']) ?></td>
                                                <td><?= htmlspecialchars($etapa['descricao']) ?></td>
                                                <td>
                                                    <button type="button"
                                                        class="btn btn-xs btn-info btn-arquivos-etapa"
                                                        data-toggle="modal"
                                                        data-target="#modalArquivosEtapa"
                                                        data-id_etapa="<?= (int)$etapa['id'] ?>">
                                                        <i class="fa fa-paperclip"></i> Arquivos
                                                    </button>
                                                </td>
                                                <td>
                                                    <button
                                                        type="button"
                                                        class="btn btn-xs btn-primary btn-editar-etapa"
                                                        data-toggle="modal"
                                                        data-target="#modalEditarEtapa"
                                                        data-id="<?= (int)$etapa['id'] ?>"
                                                        data-descricao="<?= htmlspecialchars($etapa['descricao'], ENT_QUOTES) ?>"
                                                        data-datafim="<?= htmlspecialchars($etapa['data_fim'] ?? '', ENT_QUOTES) ?>"
                                                        data-status="<?= (int)$etapa['id_status'] ?>">
                                                        <i class="fa fa-edit"></i> Editar
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <div class="modal fade" id="modalStatusProcesso" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form method="post" action="../../controle/control.php" class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Alterar Status do Processo</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="nomeClasse" value="ProcessoAceitacaoControle">
                                <input type="hidden" name="metodo" value="atualizarStatus">
                                <input type="hidden" name="id_processo" value="<?= (int)$idProcesso ?>">

                                <div class="form-group">
                                    <label>Status do Processo:</label>
                                    <select name="id_status" class="form-control" style="min-width: 200px;">
                                        <?php foreach ($statuses as $st): ?>
                                            <option value="<?= (int)$st['id'] ?>"
                                                <?= ($processoStatusId !== null && $processoStatusId === (int)$st['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($st['descricao']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Salvar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="modal fade" id="modalNovaEtapa" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form method="post" action="../../controle/control.php" enctype="multipart/form-data" class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Nova Etapa</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="nomeClasse" value="EtapaProcessoControle">
                                <input type="hidden" name="metodo" value="salvar">
                                <input type="hidden" name="id_processo" value="<?= (int)$idProcesso ?>">

                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="id_status" class="form-control">
                                        <?php foreach ($statuses as $st): ?>
                                            <option value="<?= (int)$st['id'] ?>"><?= htmlspecialchars($st['descricao']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Data de Início</label>
                                    <input type="date" name="data_inicio" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Data de Conclusão</label>
                                    <input type="date" name="data_fim" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Descrição da Etapa <span class="obrig">*</span></label>
                                    <textarea name="descricao" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">Salvar Etapa</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="modal fade" id="modalEditarEtapa" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form method="post" action="../../controle/control.php" class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Editar Etapa</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="nomeClasse" value="EtapaProcessoControle">

                                <input type="hidden" name="id_processo" id="edit_id_processo" value="<?= (int)$idProcesso ?>">
                                <input type="hidden" name="id_etapa" id="edit_id_etapa">

                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="id_status" id="edit_id_status" class="form-control">
                                        <?php foreach ($statuses as $st): ?>
                                            <option value="<?= (int)$st['id'] ?>"><?= htmlspecialchars($st['descricao']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Data de Conclusão</label>
                                    <input type="date" name="data_fim" id="edit_data_fim" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Descrição</label>
                                    <textarea name="descricao" id="edit_descricao" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">

                                <button type="submit"
                                    name="metodo"
                                    value="excluir"
                                    class="btn btn-danger"
                                    onclick="return confirm('Tem certeza que deseja excluir esta etapa?');">
                                    Excluir
                                </button>

                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>


                                <button type="submit"
                                    name="metodo"
                                    value="atualizar"
                                    class="btn btn-success">
                                    Salvar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>


                <div class="modal fade" id="modalArquivosEtapa" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Arquivos da Etapa</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div id="lista-arquivos-etapa"></div>

                                <hr>
                                <form method="post" action="../../controle/control.php"
                                    enctype="multipart/form-data" class="form-inline">
                                    <input type="hidden" name="nomeClasse" value="ArquivoEtapaControle">
                                    <input type="hidden" name="metodo" value="upload">
                                    <input type="hidden" name="alvo" value="etapa">
                                    <input type="hidden" name="id_processo" value="<?= (int)$idProcesso ?>">
                                    <input type="hidden" id="upload_id_etapa" name="id_etapa" value="">
                                    <input type="file" name="arquivo" class="form-control input-sm" />
                                    <button type="submit" class="btn btn-sm btn-default mt-1">Anexar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </section>
        </div>
    </section>

    <script src="../../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
    <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
    <script src="../../assets/vendor/jquery-autosize/jquery.autosize.js"></script>
    <script src="../../assets/javascripts/theme.js"></script>
    <script src="../../assets/javascripts/theme.custom.js"></script>
    <script src="../../assets/javascripts/theme.init.js"></script>

    <style type="text/css">
        .obrig {
            color: #ff0000;
        }
    </style>

    <script>
        $(function() {
            $("#header").load("../header.php");
            $(".menuu").load("../menu.php");

            $('.btn-editar-etapa').on('click', function() {
                var btn = $(this);
                $('#edit_id_etapa').val(btn.data('id'));
                $('#edit_descricao').val(btn.data('descricao'));
                $('#edit_data_fim').val(btn.data('datafim'));
                $('#edit_id_status').val(btn.data('status'));
            });

            $('.btn-arquivos-etapa').on('click', function() {
                var idEtapa = $(this).data('id_etapa');
                $('#upload_id_etapa').val(idEtapa);
                $('#lista-arquivos-etapa').load('lista_arquivos_etapa.php?id_etapa=' + idEtapa);
            });
        });

        function onlyNumbers(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }
            return true;
        }
    </script>
</body>

</html>