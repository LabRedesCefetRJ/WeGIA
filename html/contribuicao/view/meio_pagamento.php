<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
//verificação de autenticação
ini_set('display_errors', 0);
ini_set('display_startup_erros', 0);

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../../index.php");
}

$config_path = "config.php";
if (file_exists($config_path)) {
    require_once($config_path);
} else {
    while (true) {
        $config_path = "../../../" . $config_path;
        if (file_exists($config_path)) break;
    }
    require_once($config_path);
}

require_once '../../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 9, 3);

//Captura mensagem passada na URL como parâmetro
if (isset($_GET['msg'])) {
    $msg = trim($_GET['msg']);
}

//carrega gateways salvos no banco de dados da aplicação
require_once('../controller/GatewayPagamentoController.php');

$gatewayPagamentoController = new GatewayPagamentoController();
$gateways = $gatewayPagamentoController->buscaTodos();

//carrega meios de pagamentos salvos no banco de dados da aplicação
require_once('../controller/MeioPagamentoController.php');

$meioPagamentoController = new MeioPagamentoController();
$meiosPagamento = $meioPagamentoController->buscaTodos();

require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
?>

<!DOCTYPE html>
<html class="fixed">

<head>
    <meta charset="UTF-8">
    <title>Meio de Pagamento</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="../../../assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../../../assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="../../../assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="../../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="stylesheet" href="../../../assets/stylesheets/theme.css" />
    <link rel="stylesheet" href="../../../assets/stylesheets/skins/default.css" />
    <link rel="stylesheet" href="../../../assets/stylesheets/theme-custom.css">
    <link rel="stylesheet" href="../../../css/personalizacao-theme.css" />
    <script src="../../../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
    <script src="../../../assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="../../../assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="../../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="../../../assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="../../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
    <script src="../../../assets/vendor/jquery-autosize/jquery.autosize.js"></script>
    <script src="../../../assets/vendor/modernizr/modernizr.js"></script>
    <script src="../../../assets/javascripts/theme.js"></script>
    <script src="../../../assets/javascripts/theme.custom.js"></script>
    <script src="../../../assets/javascripts/theme.init.js"></script>

    <link rel="stylesheet" href="../public/css/contribuicao-configuracao.css">

</head>

<body>
    <section class="body">
        <div id="header"></div>
        <div class="inner-wrapper">
            <aside id="sidebar-left" class="sidebar-left menuu"></aside>
            <section role="main" class="content-body">
                <header class="page-header">
                    <h2>Meio de Pagamento</h2>
                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li>
                                <a href="../../home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li><span>Meio de Pagamento</span></li>
                        </ol>
                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>

                <div class="row">
                    <div class="col-md-10 col-md-offset-1">
                    <h2><a href="forma_contribuicao.php">Clique aqui para acessar a página de Pagamento</a></h2>
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <h3 class="panel-title text-center">Cadastro de um novo Meio de Pagamento</h3>
                                <div class="panel-actions">
                                    <a href="#" class="fa fa-caret-up" title="Mostrar/ocultar"></a>
                                </div>
                            </div>
                            <div class="panel-body" style="display: none;">
                                <div id="mensagem-cadastro">
                                    <?php if (isset($msg) && $msg == 'cadastrar-sucesso'): ?>
                                        <div class="alert alert-success text-center alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            Meio de pagamento cadastrado com sucesso!
                                        </div>
                                    <?php elseif (isset($msg) && $msg == 'cadastrar-falha'): ?>
                                        <div class="alert alert-danger text-center alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            Falha no cadastro do meio de pagamento.
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <form method="POST" action="../controller/control.php">
                                    <?= Csrf::inputField() ?>
                                    <div class="form-group">
                                        <div class="col-md-10 col-md-offset-1">
                                            Os campos com <span class="text-danger">*</span> devem ser preenchidos antes de prosseguir.
                                        </div>
                                    </div>
                                    <input type="hidden" name="nomeClasse" value="MeioPagamentoController">
                                    <input type="hidden" name="metodo" value="cadastrar">
                                    <div class="form-group">
                                        <div class="col-md-10 col-md-offset-1">
                                            <label for="meio-pagamento-nome">Descrição <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="meio-pagamento-nome" name="nome" placeholder="Insira aqui a descrição do meio de pagamento ..." required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-10 col-md-offset-1">
                                            <label for="meio-pagamento-plataforma">Plataforma <span class="text-danger">*</span></label>
                                            <select class="form-control" id="meio-pagamento-plataforma" name="meio-pagamento-plataforma">
                                                <option selected disabled>Selecione a plataforma desejada ...</option>
                                                <?php foreach ($gateways as $gateway): ?>
                                                    <option value="<?= $gateway['id'] ?>"><?= $gateway['plataforma'].' | '.$gateway['endPoint'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">Cadastrar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-10 col-md-offset-1">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <h3 class="panel-title text-center">Meios de pagamento do sistema</h3>
                                <div class="panel-actions">
                                    <a href="#" class="fa fa-caret-down" title="Mostrar/ocultar"></a>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div id="mensagem-tabela">
                                    <?php if (isset($msg) && $msg == 'excluir-sucesso'): ?>
                                        <div class="alert alert-success text-center alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            Meio de pagamento excluído com sucesso!
                                        </div>
                                    <?php elseif (isset($msg) && $msg == 'excluir-falha'): ?>
                                        <div class="alert alert-danger text-center alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            Falha na exclusão do meio de pagamento.
                                        </div>
                                    <?php elseif (isset($msg) && $msg == 'editar-sucesso'): ?>
                                        <div class="alert alert-success text-center alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            Meio de pagamento editado com sucesso!
                                        </div>
                                    <?php elseif (isset($msg) && $msg == 'editar-falha'): ?>
                                        <div class="alert alert-danger text-center alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            Falha na edição do meio de pagamento.
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (!isset($meiosPagamento) || empty($meiosPagamento)): ?>
                                    <div class="alert alert-warning text-center alert-dismissible" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        Não foi possível encontrar nenhum meio de pagamento cadastrado no sistema.
                                    </div>
                                <?php else: ?>
                                    <table class="table table-hover text-center">
                                        <thead>
                                            <th class="text-center">Descrição</th>
                                            <th class="text-center">Plataforma | Endpoint</th>
                                            <th class="text-center">Ativo</th>
                                            <th class="text-center">Ação</th>
                                        </thead>
                                        <tbody>
                                            <!--Carrega tabela dinamicamente-->
                                            <?php foreach ($meiosPagamento as $meioPagamento): ?>
                                                <tr>
                                                    <td class="vertical-center"><?= htmlspecialchars($meioPagamento['meio']) ?></td>
                                                    <td class="vertical-center"><?=htmlspecialchars($meioPagamento['plataforma']).' | '.htmlspecialchars($meioPagamento['endpoint']) ?></td><!--Substituir impressão do id pelo nome da plataforma | endpoint-->
                                                    <td class="vertical-center">
                                                        <div class="toggle-switch">
                                                            <?php if (isset($meioPagamento['status']) && $meioPagamento['status'] === 1): ?>
                                                                <input type="checkbox" id="toggle<?= $meioPagamento['id'] ?>" class="toggle-input" checked>
                                                            <?php else: ?>
                                                                <input type="checkbox" id="toggle<?= $meioPagamento['id'] ?>" class="toggle-input">
                                                            <?php endif; ?>
                                                            <label for="toggle<?= $meioPagamento['id'] ?>" class="toggle-label" title="Alterar estado"></label>
                                                        </div>
                                                    </td>
                                                    <td class="vertical-center">
                                                        <button type="button" class="btn btn-default" title="Editar" data-id="<?= $meioPagamento['id'] ?>" data-plataforma-id="<?= $meioPagamento['id_plataforma'] ?>"><i class="fa fa-edit"></i></button>
                                                        <form action="../controller/control.php" method="post" style="display: inline-block; margin: 0;" onsubmit="return confirmarExclusao();">
                                                            <?= Csrf::inputField() ?>
                                                            <input type="hidden" name="nomeClasse" value="MeioPagamentoController">
                                                            <input type="hidden" name="metodo" value="excluirPorId">
                                                            <input type="hidden" name="meio-pagamento-id" value="<?= $meioPagamento['id'] ?>">
                                                            <button type="submit" class="btn btn-default" title="Excluir" data-id="<?= $meioPagamento['id'] ?>"><i class="fa fa-remove text-danger"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>

                                    <!-- Modal de Edição -->
                                    <div id="editModal" class="modal fade" role="dialog">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header modal-header-primary">
                                                    <button type="button" class="close" data-dismiss="modal" title="Fechar">&times;</button>
                                                    <h4 class="modal-title">Editar Meio de pagamento</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <form id="editForm" method="POST" action="../controller/control.php">
                                                        <?= Csrf::inputField() ?>
                                                        <div class="form-group">
                                                            <label for="editNome">Descrição:</label>
                                                            <input type="text" class="form-control" id="editNome" name="nome" required>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="meio-pagamento-plataforma">Plataforma</label>
                                                            <select class="form-control" id="editPlataforma" name="plataforma">
                                                                <option selected disabled>Selecione a plataforma desejada ...</option>
                                                                <?php foreach ($gateways as $gateway): ?>
                                                                    <option value="<?= $gateway['id'] ?>"><?= $gateway['plataforma'].' | '.$gateway['endPoint'] ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>

                                                        <input type="hidden" name="nomeClasse" value="MeioPagamentoController">
                                                        <input type="hidden" name="metodo" value="editarPorId">
                                                        <input type="hidden" id="editId" name="id">
                                                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </section>
    <script src="../public/js/configuracoesGerais.js"></script>
    <script src="../public/js/meioPagamento.js"></script>

    <div align="right">
        <iframe src="https://www.wegia.org/software/footer/contribuicao.html" width="200" height="60" style="border:none;"></iframe>
    </div>
</body>

</html>