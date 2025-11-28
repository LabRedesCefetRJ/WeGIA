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
require_once "../personalizacao_display.php";


$pdo       = Conexao::connect();
$etapaDAO  = new PaEtapaDAO($pdo);
$statusDAO = new PaStatusDAO($pdo);
$procDAO   = new ProcessoAceitacaoDAO($pdo);

$etapas    = $etapaDAO->listarPorProcesso($idProcesso);
$statuses  = $statusDAO->listarTodos();
$processo  = $procDAO->buscarResumoPorId($idProcesso);

$nomeCompleto = $processo ? ($processo['nome'].' '.$processo['sobrenome']) : ('Processo #'.$idProcesso);

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
    <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">

    <script src="../../assets/vendor/modernizr/modernizr.js"></script>
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
                                        <th>Descrição</th>
                                        <th>Arquivos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($etapas as $etapa): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($etapa['data_inicio']) ?></td>
                                            <td><?= htmlspecialchars($etapa['data_fim'] ?? 'Em andamento') ?></td>
                                            <td><?= htmlspecialchars($etapa['descricao']) ?></td>
                                            <td>
                                                <form method="post" action="../../controle/control.php"
                                                      enctype="multipart/form-data" class="form-inline">
                                                    <input type="hidden" name="nomeClasse" value="ArquivoEtapaControle">
                                                    <input type="hidden" name="metodo" value="upload">
                                                    <input type="hidden" name="id_processo" value="<?= (int)$idProcesso ?>">
                                                    <input type="hidden" name="id_etapa" value="<?= (int)$etapa['id'] ?>">
                                                    <input type="file" name="arquivo" class="form-control input-sm" />
                                                    <button type="submit" class="btn btn-sm btn-default">Anexar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="panel panel-primary" style="border-color: #0088cc;">
                <header class="panel-heading">
                    <h2 class="panel-title">Nova Etapa</h2>
                </header>
                <div class="panel-body">
                    <form method="post" action="../../controle/control.php" enctype="multipart/form-data">
                        <input type="hidden" name="nomeClasse" value="EtapaProcessoControle">
                        <input type="hidden" name="metodo" value="salvar">
                        <input type="hidden" name="id_processo" value="<?= (int)$idProcesso ?>">

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Data de Início</label>
                                    <input type="date" name="data_inicio" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Data de Conclusão</label>
                                    <input type="date" name="data_fim" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Descrição da Etapa <span class="obrig">*</span></label>
                            <textarea name="descricao" class="form-control" rows="3" required
                                      placeholder="Ex.: Entrevista inicial com a família, visita técnica, etc."></textarea>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-plus"></i> Adicionar Etapa
                        </button>
                        <a href="processo_aceitacao.php" class="btn btn-default">Voltar</a>
                    </form>
                </div>
            </section>

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
    .obrig { color: #ff0000; }
</style>
<script>
    $(function() {
        $("#header").load("../header.php");
        $(".menuu").load("../menu.php");
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
