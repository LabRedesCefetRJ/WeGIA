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

//Mascarar tokens para exibição
foreach ($gateways as $key => $gateway) {
    if ($gateway['token'] != '' && $gateway['token'] != 'coloque o token aqui') {
        $gateways[$key]['token'] = ofuscarToken($gateway['token']);
    }
}

function ofuscarToken(string $token, int $visivelInicio = 3, int $visivelFim = 3, float $percentualMaxVisivel = 0.3): string
{
    $tamanho = strlen($token);

    // Número máximo total de caracteres visíveis com base na porcentagem
    $maxVisiveis = floor($tamanho * $percentualMaxVisivel);

    // Garante que o total de visíveis não ultrapasse o permitido
    $totalVisiveis = $visivelInicio + $visivelFim;
    if ($totalVisiveis > $maxVisiveis) {
        // Divide o número máximo permitido entre início e fim
        $visivelInicio = floor($maxVisiveis / 2);
        $visivelFim = $maxVisiveis - $visivelInicio;
    }

    // Se ainda assim for muito curto, oculta completamente
    if ($visivelInicio + $visivelFim >= $tamanho) {
        return str_repeat('*', $tamanho);
    }

    $inicio = substr($token, 0, $visivelInicio);
    $fim = substr($token, -$visivelFim);
    $meio = str_repeat('*', $tamanho - $visivelInicio - $visivelFim);

    return $inicio . $meio . $fim;
}

?>

<!DOCTYPE html>
<html class="fixed">

<head>
    <meta charset="UTF-8">
    <title>Gateway de Pagamento</title>
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
    <link rel="stylesheet" href="../public/css/contribuicao-configuracao.css">
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

    <style>
        .table {
            table-layout: fixed;
            width: 100%;
        }

        .table th,
        .table td {
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            vertical-align: middle;
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
                    <h2>Gateway de Pagamento</h2>
                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li>
                                <a href="../../home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li><span>Gateway de Pagamento</span></li>
                        </ol>
                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>
                <div class="row">
                    <div class="col-md-10 col-md-offset-1">
                        <h2><a href="forma_contribuicao.php">Clique aqui para acessar a página de Pagamento</a></h2>
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <h3 class="panel-title text-center">Cadastro de um novo Gateway</h3>
                                <div class="panel-actions">
                                    <a href="#" class="fa fa-caret-up" title="Mostrar/ocultar"></a>
                                </div>
                            </div>
                            <div class="panel-body" style="display: none;">
                                <div id="mensagem-cadastro">
                                    <?php if (isset($msg) && $msg == 'cadastrar-sucesso'): ?>
                                        <div class="alert alert-success text-center alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            Gateway cadastrado com sucesso!
                                        </div>
                                    <?php elseif (isset($msg) && $msg == 'cadastrar-falha'): ?>
                                        <div class="alert alert-danger text-center alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            Falha no cadastro do gateway.
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <form method="POST" action="../controller/control.php">

                                    <div class="form-group">
                                        <div class="col-md-10 col-md-offset-1">
                                            Os campos com <span class="text-danger">*</span> devem ser preenchidos antes de prosseguir.
                                        </div>
                                    </div>
                                    <input type="hidden" name="nomeClasse" value="GatewayPagamentoController">
                                    <input type="hidden" name="metodo" value="cadastrar">
                                    <div class="form-group">
                                        <div class="col-md-10 col-md-offset-1">
                                            <label for="plataforma-nome">Nome da Plataforma <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="plataforma-nome" name="nome" placeholder="Insira aqui o nome da plataforma de gateway ..." required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-10 col-md-offset-1">
                                            <label for="plataforma-endpoint">Endpoint <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="plataforma-endpoint" name="endpoint" placeholder="Insira aqui o endpoint da plataforma de gateway ..." required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-10 col-md-offset-1">
                                            <label for="plataforma-chave">Token API <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="plataforma-chave" name="token" placeholder="Insira aqui o token da API da plataforma de gateway ..." required>
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
                                <h3 class="panel-title text-center">Gateways do Sistema</h3>
                                <div class="panel-actions">
                                    <a href="#" class="fa fa-caret-down" title="Mostrar/ocultar"></a>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div id="mensagem-tabela">
                                    <?php if (isset($msg) && $msg == 'excluir-sucesso'): ?>
                                        <div class="alert alert-success text-center alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            Gateway excluído com sucesso!
                                        </div>
                                    <?php elseif (isset($msg) && $msg == 'excluir-falha'): ?>
                                        <div class="alert alert-danger text-center alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            Falha na exclusão do gateway.
                                        </div>
                                    <?php elseif (isset($msg) && $msg == 'editar-sucesso'): ?>
                                        <div class="alert alert-success text-center alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            Gateway editado com sucesso!
                                        </div>
                                    <?php elseif (isset($msg) && $msg == 'editar-falha'): ?>
                                        <div class="alert alert-danger text-center alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            Falha na edição do gateway.
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (!isset($gateways) || empty($gateways)): ?>
                                    <div class="alert alert-warning text-center alert-dismissible" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        Não foi possível encontrar nenhum gateway cadastrado no sistema.
                                    </div>
                                <?php else: ?>
                                    <table class="table table-hover text-center">
                                        <thead>
                                            <th class="text-center">Plataforma</th>
                                            <th class="text-center">Endpoint</th>
                                            <th class="text-center col-token">Token API</th>
                                            <th class="text-center">Ativo</th>
                                            <th class="text-center">Ação</th>
                                        </thead>
                                        <tbody>
                                            <!--Carrega tabela dinamicamente-->
                                            <?php foreach ($gateways as $gateway): ?>
                                                <tr>
                                                    <td class="vertical-center"><?= htmlspecialchars($gateway['plataforma']) ?></td>
                                                    <td class="vertical-center"><?= htmlspecialchars($gateway['endPoint']) ?></td>
                                                    <td class="vertical-center"><?= htmlspecialchars($gateway['token']) ?></td>
                                                    <td class="vertical-center">
                                                        <div class="toggle-switch">
                                                            <?php if (isset($gateway['status']) && $gateway['status'] === 1): ?>
                                                                <input type="checkbox" id="toggle<?= $gateway['id'] ?>" class="toggle-input" checked>
                                                            <?php else: ?>
                                                                <input type="checkbox" id="toggle<?= $gateway['id'] ?>" class="toggle-input">
                                                            <?php endif; ?>
                                                            <label for="toggle<?= $gateway['id'] ?>" class="toggle-label" title="Alterar estado"></label>
                                                        </div>
                                                    </td>
                                                    <td class="vertical-center">
                                                        <button type="button" class="btn btn-default" title="Editar" data-id="<?= $gateway['id'] ?>"><i class="fa fa-edit"></i></button>
                                                        <form action="../controller/control.php" method="post" style="display: inline-block; margin: 0;" onsubmit="return confirmarExclusao();">
                                                            <input type="hidden" name="nomeClasse" value="GatewayPagamentoController">
                                                            <input type="hidden" name="metodo" value="excluirPorId">
                                                            <input type="hidden" name="gateway-id" value="<?= $gateway['id'] ?>">
                                                            <button type="submit" class="btn btn-default" title="Excluir" data-id="<?= $gateway['id'] ?>"><i class="fa fa-remove text-danger"></i></button>
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
                                                    <h4 class="modal-title">Editar Gateway</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <form id="editForm" method="POST" action="../controller/control.php">
                                                        <div class="form-group">
                                                            <label for="editNome">Nome da plataforma:</label>
                                                            <input type="text" class="form-control" id="editNome" name="nome" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="editEndpoint">Endpoint:</label>
                                                            <input type="text" class="form-control" id="editEndpoint" name="endpoint" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="editToken">Token API:</label>
                                                            <input type="text" class="form-control" id="editToken" name="token" required>
                                                        </div>
                                                        <input type="hidden" name="nomeClasse" value="GatewayPagamentoController">
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
    <script src="../public/js/gatewayPagamento.js"></script>
    <div align="right">
        <iframe src="https://www.wegia.org/software/footer/contribuicao.html" width="200" height="60" style="border:none;"></iframe>
    </div>
</body>

</html>