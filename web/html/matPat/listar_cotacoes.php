<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2)
    . DIRECTORY_SEPARATOR . 'geral'
    . DIRECTORY_SEPARATOR . 'msg.php';

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";
require_once ROOT . "/classes/Csrf.php";

if (
    !isset($tipo, $cotacoes) ||
    !is_string($tipo) ||
    !is_array($cotacoes)
) {
    http_response_code(500);
    exit('Não foi possível carregar as cotações.');
}
?>
<!doctype html>
<html class="fixed">

<head>

    <!-- Basic -->
    <meta charset="UTF-8">
    <base href="<?= htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8') ?>html/matPat/">

    <title>Cotações</title>

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

    <!-- Specific Page Vendor CSS -->
    <link rel="stylesheet" href="../../assets/vendor/select2/select2.css" />
    <link rel="stylesheet" href="../../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />

    <!-- Skin CSS -->
    <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />

    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">

    <!-- Head Libs -->
    <script src="../../assets/vendor/modernizr/modernizr.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">

    <!-- Vendor -->
    <script src="../../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
    <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

    <!-- Specific Page Vendor -->
    <script src="../../assets/vendor/jquery-autosize/jquery.autosize.js"></script>

    <!-- javascript functions -->
    <!-- jquery functions -->
    <script>
        $(function() {
            $("#header").load("../header.php");
            $(".menuu").load("../menu.php");
        });
    </script>
    <link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/processo-compra.css">
</head>

<body class="processo-compra">
    <section class="body">
        <!-- start: header -->
        <div id="header"></div>
        <!-- end: header -->
        <div class="inner-wrapper">
            <!-- start: sidebar -->
            <aside id="sidebar-left" class="sidebar-left menuu"></aside>

            <!-- end: sidebar -->
            <section role="main" class="content-body">
                <header class="page-header">
                    <h2>Cotações</h2>

                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li><a href="../index.php"> <i class="fa fa-home"></i>
                                </a></li>
                            <li><span>Cotações</span></li>
                        </ol>

                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>

                </header>

                <!-- start: page -->
                <?php sessionMsg(); ?>

                <div class="mb-4">
                    <a href="<?= WWW ?>controle/control.php?nomeClasse=CotacaoControle&amp;metodo=cadastrar" class="btn btn-primary" style="margin-bottom: 15px;"><i class="fa fa-plus"></i> Cadastrar Nova Cotação</a>
                </div>

                <section class="panel panel-primary">
                    <header class="panel-heading">
                        <h2 class="panel-title">Lista de Cotações</h2>
                        <div class="barra-cotacoes">

                            <div class="abas-cotacoes">
                                <a href="<?= WWW ?>controle/control.php?nomeClasse=CotacaoControle&amp;metodo=listar&amp;tipo=andamento"
                                    class="btn btn-default <?= $tipo === 'andamento' ? 'active' : '' ?>">
                                    Em andamento
                                </a>

                                <a href="<?= WWW ?>controle/control.php?nomeClasse=CotacaoControle&amp;metodo=listar&amp;tipo=historico"
                                    class="btn btn-default <?= $tipo === 'historico' ? 'active' : '' ?>">
                                    Histórico
                                </a>
                            </div>

                            <div class="filtros-cotacoes">

                                <div class="campo-filtro">
                                    <label for="buscaCotacao">Buscar</label>

                                    <input
                                        type="text"
                                        id="buscaCotacao"
                                         class="form-control"
                                        placeholder="Descrição ou responsável"
                                    >
                                </div>

                            </div>

                        </div>
                    </header>
                    <div class="panel-body">
                        <?php if (empty($cotacoes)): ?>
                            <div class="alert alert-warning">
                                Nenhuma cotação encontrada.
                            </div>
                        <?php else: ?>
                            <div class="tabela-compras">
                                <table id="tabelaCotacoes" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Descrição</th>
                                        <th>Responsável</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($cotacoes as $cotacao): ?>
                                        <tr data-status="<?= htmlspecialchars(
                                            $cotacao['status'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>">
                                            <td>
                                                <?= !empty($cotacao['descricao'])
                                                    ? htmlspecialchars($cotacao['descricao'], ENT_QUOTES, 'UTF-8')
                                                    : 'Sem descrição'
                                                ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(
                                                    trim(($cotacao['nome'] ?? '') . ' ' . ($cotacao['sobrenome'] ?? '')),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </td>

                                            <td>
                                                <?php
                                                switch ($cotacao['status']) {
                                                    case 'analise':
                                                        echo 'Em análise';
                                                        break;

                                                    case 'concluido':
                                                        echo 'Concluída';
                                                        break;

                                                    default:
                                                        echo 'Não informado';
                                                }
                                                ?>
                                            </td>

                                            <td>
                                                <a
                                                    href="<?= WWW ?>controle/control.php?nomeClasse=CotacaoControle&amp;metodo=visualizar&amp;origem=<?= $tipo ?>&amp;id_cotacao=<?= (int) $cotacao['id_cotacao'] ?>"
                                                     class="btn btn-xs btn-primary"
                                                    title="Ver cotação"
                                                >
                                                    <i class="fa fa-eye"></i>
                                                    Ver
                                                </a>
                                                <?php if ($cotacao['status'] === 'analise'): ?>
                                                    <form
                                                        method="post"
                                                        action="<?= WWW ?>controle/control.php"
                                                        style="display: inline-block; margin: 0 0 0 5px;"
                                                        onsubmit="return confirm('Excluir definitivamente esta cotação e todos os seus orçamentos e documentos? Esta ação não pode ser desfeita.');"
                                                    >
                                                        <input type="hidden" name="nomeClasse" value="CotacaoControle">
                                                        <input type="hidden" name="metodo" value="excluir">
                                                        <input type="hidden" name="id_cotacao" value="<?= (int) $cotacao['id_cotacao'] ?>">
                                                        <?= Csrf::inputField() ?>
                                                        <button type="submit" class="btn btn-xs btn-danger" title="Excluir cotação">
                                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                                            Excluir
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

            </section>
        </div>
    </section>

    <!-- end: page -->

    <!-- Vendor -->
    <script src="../../assets/vendor/select2/select2.js"></script>
    <script src="../../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
    <script src="../../assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
    <script src="../../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>

    <!-- Theme Base, Components and Settings -->
    <script src="../../assets/javascripts/theme.js"></script>

    <!-- Theme Custom -->
    <script src="../../assets/javascripts/theme.custom.js"></script>

    <!-- Theme Initialization Files -->
    <script src="../../assets/javascripts/theme.init.js"></script>

    <!-- Examples -->
    <script src="../../assets/javascripts/tables/examples.datatables.default.js"></script>
    <script src="../../assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
    <script src="../../assets/javascripts/tables/examples.datatables.tabletools.js"></script>

    <div align="right">
        <iframe src="https://www.wegia.org/software/footer/pessoa.html" width="200" height="60" style="border:none;"></iframe>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabelaElemento = document.getElementById('tabelaCotacoes');

            if (!tabelaElemento) {
                return;
            }

            const campoBusca = document.getElementById('buscaCotacao');

            const tabela = $('#tabelaCotacoes').DataTable({
                pageLength: 10,
                lengthChange: false,
                searching: true,
                ordering: true,
                order: [],
                info: true,

                columnDefs: [
                    {
                        targets: 3,
                        orderable: false,
                        searchable: false
                    }
                ],

                dom: 'r<"tabela-scroll"t><"row"<"col-sm-6"i><"col-sm-6"p>>',

                language: {
                    emptyTable: 'Nenhuma cotação encontrada.',
                    zeroRecords: 'Nenhuma cotação encontrada.',
                    info: 'Exibindo _START_ a _END_ de _TOTAL_ cotações',
                    infoEmpty: 'Nenhuma cotação encontrada.',
                    paginate: {
                        previous: 'Anterior',
                        next: 'Próxima'
                    }
                }
            });

            const areaRolavel = tabelaElemento
                .closest('.dataTables_wrapper')
                .querySelector('.tabela-scroll');

            if (areaRolavel) {
                areaRolavel.setAttribute('role', 'region');
                areaRolavel.setAttribute('aria-label', 'Lista de cotações');
                areaRolavel.setAttribute('tabindex', '0');
            }

            $.fn.dataTable.ext.search.push(function (settings, data) {
                if (settings.nTable.id !== 'tabelaCotacoes') {
                    return true;
                }

                const busca = campoBusca.value
                    .trim()
                    .toLocaleLowerCase('pt-BR');

                const descricao = (data[0] || '')
                    .toLocaleLowerCase('pt-BR');

                const responsavel = (data[1] || '')
                    .toLocaleLowerCase('pt-BR');

                const correspondeBusca =
                    busca === '' ||
                    descricao.includes(busca) ||
                    responsavel.includes(busca);

                return correspondeBusca;
            });

            campoBusca.addEventListener('input', function () {
                tabela.draw();
            });

        });
    </script>
</body>
</html>
