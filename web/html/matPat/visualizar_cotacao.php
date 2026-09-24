<?php

require_once dirname(__FILE__, 2)
    . DIRECTORY_SEPARATOR . 'seguranca'
    . DIRECTORY_SEPARATOR . 'security_headers.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once ROOT . '/html/personalizacao_display.php';
require_once ROOT . '/html/geral/msg.php';
require_once ROOT . '/classes/Csrf.php';
require_once ROOT . '/classes/CotacaoSuporte.php';

if (
    !isset($cotacao, $orcamentos, $fornecedores) ||
    !is_array($cotacao) ||
    !is_array($orcamentos) ||
    !is_array($fornecedores)
) {
    http_response_code(500);
    exit('Não foi possível carregar a cotação.');
}
$origemLista = CotacaoSuporte::origemLista();
?>
<!doctype html>
<html class="fixed">

<head>

    <!-- Basic -->
    <meta charset="UTF-8">
    <base href="<?= htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8') ?>html/matPat/">

    <title>Visualizar Cotação</title>

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

    <!-- Theme Base, Components and Settings -->
    <script src="../../assets/javascripts/theme.js"></script>

    <!-- Theme Custom -->
    <script src="../../assets/javascripts/theme.custom.js"></script>

    <!-- Theme Initialization Files -->
    <script src="../../assets/javascripts/theme.init.js"></script>

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
                    <h2>Visualizar Cotação</h2>

                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li>
                                <a href="<?= WWW ?>html/home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li><span>Processo de Compra</span></li>
                            <li><span>Cotações</span></li>
                            <li><span>Visualizar Cotação</span></li>
                        </ol>

                        <a class="sidebar-right-toggle">
                            <i class="fa fa-chevron-left"></i>
                        </a>
                    </div>
                </header>

                <?php sessionMsg(); ?>

                <section class="panel panel-primary">
                    <header class="panel-heading">
                        <h2 class="panel-title">
                            Cotação #<?= (int) $cotacao['id_cotacao'] ?>
                        </h2>
                    </header>

                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Descrição</strong>
                                <p>
                                    <?= htmlspecialchars(
                                        $cotacao['descricao'] ?? 'Sem descrição',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </p>
                            </div>

                            <div class="col-md-3">
                                <strong>Responsável</strong>
                                <p>
                                    <?= htmlspecialchars(
                                        trim(
                                            ($cotacao['nome'] ?? '') .
                                            ' ' .
                                            ($cotacao['sobrenome'] ?? '')
                                        ),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </p>
                            </div>

                            <div class="col-md-3">
                                <strong>Status</strong>
                                <p>
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
                                </p>
                            </div>
                        </div>

                        <hr>
                        <div class="acoes-orcamentos">
                            <h4 style="margin: 0;">Orçamentos</h4>
                            <?php if (
                                $cotacao['status'] === 'analise' &&
                                count($orcamentos) < 3
                            ): ?>
                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    data-toggle="modal"
                                    data-target="#modalAdicionarOrcamento"
                                >
                                    <i class="fa fa-plus"></i>
                                    Adicionar orçamento
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="row orcamentos-compras">
                            <?php foreach ($orcamentos as $indice => $orcamento): ?>
                                <?php
                                $idOrcamento = (int) $orcamento['id_orcamento'];

                                $foiEscolhido =
                                    !empty($cotacao['id_orcamento_escolhido']) &&
                                    (int) $cotacao['id_orcamento_escolhido'] === $idOrcamento;
                                ?>

                                <div class="col-sm-6 col-md-4">
                                    <section class="panel panel-default <?= $foiEscolhido ? 'orcamento-escolhido' : '' ?>">
                                        <header class="panel-heading">
                                            <h3 class="panel-title">Orçamento <?= $indice + 1 ?></h3>

                                            <?php if ($foiEscolhido): ?>
                                                <span class="selo-orcamento-escolhido">
                                                    <i class="fa fa-check-circle" aria-hidden="true"></i>
                                                    Orçamento escolhido
                                                </span>
                                            <?php endif; ?>
                                        </header>

                                        <div class="panel-body">
                                            <?php if ($cotacao['status'] === 'analise'): ?>
                                                <div class="radio">
                                                    <label for="selecionarOrcamento<?= $idOrcamento ?>">
                                                        <input
                                                            type="radio"
                                                            name="id_orcamento"
                                                            id="selecionarOrcamento<?= $idOrcamento ?>"
                                                            value="<?= $idOrcamento ?>"
                                                            form="formEscolherOrcamento"
                                                            required
                                                        >
                                                        Selecionar este orçamento
                                                    </label>
                                                </div>

                                                <hr>
                                            <?php endif; ?>

                                            <p>
                                                <strong>Fornecedor:</strong><br>
                                                <?= htmlspecialchars(
                                                    $orcamento['fornecedor'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </p>

                                            <p>
                                                <strong>Valor:</strong><br>
                                                <?= $orcamento['valor'] !== null
                                                    ? 'R$ ' . number_format(
                                                        (float) $orcamento['valor'],
                                                        2,
                                                        ',',
                                                        '.'
                                                    )
                                                    : 'Não informado'
                                                ?>
                                            </p>

                                            <p>
                                                <strong>Prazo de entrega:</strong><br>
                                                <?= !empty($orcamento['prazo_entrega'])
                                                    ? htmlspecialchars(
                                                        $orcamento['prazo_entrega'],
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    )
                                                    : 'Não informado'
                                                ?>
                                            </p>

                                            <p>
                                                <strong>Documento:</strong><br>
                                                <?php if (!empty($orcamento['id_orcamento_arquivo'])): ?>
                                                    <a
                                                        href="<?= WWW ?>controle/control.php?nomeClasse=OrcamentoControle&amp;metodo=visualizarArquivo&amp;id_orcamento=<?= $idOrcamento ?>"
                                                        class="btn btn-sm btn-primary"
                                                        target="_blank"
                                                        rel="noopener"
                                                        title="Visualizar arquivo em uma nova aba"
                                                    >
                                                        <i class="fa fa-file-pdf" aria-hidden="true"></i>
                                                        Visualizar arquivo
                                                    </a>

                                                    <a
                                                        href="<?= WWW ?>controle/control.php?nomeClasse=OrcamentoControle&metodo=baixarArquivo&id_orcamento=<?= $idOrcamento ?>"
                                                        class="btn btn-sm btn-default"
                                                    >
                                                        <i class="fa fa-download"></i>
                                                        <?= htmlspecialchars(
                                                            $orcamento['arquivo_nome'],
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Nenhum arquivo enviado</span>
                                                <?php endif; ?>
                                            </p>
                                            <?php if ($cotacao['status'] === 'analise'): ?>
                                                <hr>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-primary"
                                                    data-toggle="modal"
                                                    data-target="#modalEditarOrcamento"
                                                    data-id="<?= $idOrcamento ?>"
                                                    data-fornecedor="<?= (int) $orcamento['id_fornecedor'] ?>"
                                                    data-valor="<?= htmlspecialchars($orcamento['valor'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                    data-prazo="<?= htmlspecialchars($orcamento['prazo_entrega'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                    data-arquivo="<?= htmlspecialchars($orcamento['arquivo_nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                >
                                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                                    Editar
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </section>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($cotacao['status'] === 'analise'): ?>
                            <hr>

                            <form data-cotacao-form
                                id="formEscolherOrcamento"
                                method="post"
                                action="<?= WWW ?>controle/control.php"
                            >
                                <input type="hidden" name="nomeClasse" value="CotacaoControle">
                                <input type="hidden" name="metodo" value="escolherOrcamento">
                                <input
                                    type="hidden"
                                    name="id_cotacao"
                                    value="<?= (int) $cotacao['id_cotacao'] ?>"
                                >

                                <?= Csrf::inputField() ?>
                                <input type="hidden" name="origem" value="<?= $origemLista ?>">

                                <div class="form-group">
                                    <label for="justificativa">
                                        Justificativa da escolha
                                    </label>

                                    <textarea
                                        name="justificativa"
                                        id="justificativa"
                                        class="form-control"
                                        rows="4"
                                        maxlength="255"
                                        placeholder="Informe o motivo da escolha deste orçamento"
                                    ></textarea>
                                </div>

                                <div class="text-right">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fa fa-check"></i>
                                        Concluir Cotação
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>

                        <div
                            class="modal fade"
                            id="modalAdicionarOrcamento"
                            tabindex="-1"
                            role="dialog"
                        >
                            <div class="modal-dialog">
                                <div class="modal-content">

                                    <form data-cotacao-form
                                        method="post"
                                        action="<?= WWW ?>controle/control.php"
                                        enctype="multipart/form-data"
                                    >
                                        <div class="modal-header">
                                            <button
                                                type="button"
                                                class="close"
                                                data-dismiss="modal"
                                            >
                                                &times;
                                            </button>

                                            <h4 class="modal-title">
                                                Adicionar orçamento
                                            </h4>
                                        </div>

                                        <div class="modal-body">

                                            <input
                                                type="hidden"
                                                name="nomeClasse"
                                                value="OrcamentoControle"
                                            >

                                            <input
                                                type="hidden"
                                                name="metodo"
                                                value="incluir"
                                            >

                                            <input
                                                type="hidden"
                                                name="id_cotacao"
                                                value="<?= (int) $cotacao['id_cotacao'] ?>"
                                            >

                                            <?= Csrf::inputField() ?>
                                            <input type="hidden" name="origem" value="<?= $origemLista ?>">

                                            <div class="form-group">
                                                <label for="novoFornecedor">
                                                    Fornecedor
                                                    <span class="text-danger">*</span>
                                                </label>

                                                <select
                                                    name="id_fornecedor"
                                                    id="novoFornecedor"
                                                    class="form-control"
                                                    required
                                                >
                                                    <option value="" selected disabled>
                                                        Selecionar fornecedor
                                                    </option>

                                                    <?php foreach ($fornecedores as $fornecedor): ?>
                                                        <option value="<?= (int) $fornecedor['id_origem'] ?>">
                                                            <?= htmlspecialchars(
                                                                $fornecedor['nome_origem'],
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="novoValor">Valor <span class="text-danger">*</span></label>

                                                <input
                                                    type="number"
                                                    name="valor"
                                                    id="novoValor"
                                                    class="form-control"
                                                    min="0"
                                                    step="0.01"
                                                    max="99999999.99"
                                                    required
                                                >
                                            </div>

                                            <div class="form-group">
                                                <label for="novoPrazo">Prazo de entrega</label>

                                                <input
                                                    type="text"
                                                    name="prazo_entrega"
                                                    id="novoPrazo"
                                                    class="form-control"
                                                    maxlength="50"
                                                    placeholder="Ex.: 10 dias úteis"
                                                >
                                            </div>

                                            <div class="form-group">
                                                <label for="arquivoNovoOrcamento">Arquivo do orçamento</label>
                                                <small class="text-muted">
                                                    Formatos permitidos: PDF, JPG, JPEG e PNG. Máximo de 2 MB.
                                                </small>

                                                <input
                                                    type="file"
                                                    name="arquivo"
                                                    id="arquivoNovoOrcamento"
                                                    class="form-control"
                                                    accept=".pdf,.jpg,.jpeg,.png"
                                                >

                                                <small class="text-muted">
                                                    Máximo de 2 MB.
                                                </small>
                                            </div>

                                        </div>

                                        <div class="modal-footer">
                                            <button
                                                type="button"
                                                class="btn btn-default"
                                                data-dismiss="modal"
                                            >
                                                Cancelar
                                            </button>

                                            <button
                                                type="submit"
                                                class="btn btn-primary"
                                            >
                                                <i class="fa fa-plus"></i>
                                                Adicionar orçamento
                                            </button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                        <div
                            class="modal fade"
                            id="modalEditarOrcamento"
                            tabindex="-1"
                            role="dialog"
                        >
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form data-cotacao-form method="post" action="<?= WWW ?>controle/control.php" enctype="multipart/form-data">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">
                                                &times;
                                            </button>

                                            <h4 class="modal-title">Editar orçamento</h4>
                                        </div>

                                        <div class="modal-body">
                                            <input type="hidden" name="nomeClasse" value="OrcamentoControle">
                                            <input type="hidden" name="metodo" value="editar">
                                            <input type="hidden" name="id_orcamento" id="editarIdOrcamento">
                                            <input
                                                type="hidden"
                                                name="id_cotacao"
                                                value="<?= (int) $cotacao['id_cotacao'] ?>"
                                            >

                                            <?= Csrf::inputField() ?>
                                            <input type="hidden" name="origem" value="<?= $origemLista ?>">

                                            <div class="form-group">
                                                <label for="editarFornecedor">Fornecedor</label>
                                                <select
                                                    name="id_fornecedor"
                                                    id="editarFornecedor"
                                                    class="form-control"
                                                    required
                                                >
                                                    <?php foreach ($fornecedores as $fornecedor): ?>
                                                        <option value="<?= (int) $fornecedor['id_origem'] ?>">
                                                            <?= htmlspecialchars(
                                                                $fornecedor['nome_origem'],
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="editarValor">Valor <span class="text-danger">*</span></label>
                                                <input
                                                    type="number"
                                                    name="valor"
                                                    id="editarValor"
                                                    class="form-control"
                                                    min="0"
                                                    step="0.01"
                                                    max="99999999.99"
                                                    required
                                                >
                                            </div>

                                            <div class="form-group">
                                                <label for="editarPrazo">Prazo de entrega</label>
                                                <input
                                                    type="text"
                                                    name="prazo_entrega"
                                                    id="editarPrazo"
                                                    class="form-control"
                                                    maxlength="50"
                                                >
                                            </div>

                                            <div class="form-group">
                                                <label for="editarArquivo">
                                                    Arquivo do orçamento
                                                </label>

                                                <input
                                                    type="file"
                                                    name="arquivo"
                                                    id="editarArquivo"
                                                    class="form-control"
                                                    accept=".pdf,.jpg,.jpeg,.png"
                                                >

                                                <p
                                                    id="editarArquivoAtual"
                                                    class="help-block"
                                                ></p>

                                                <small class="text-muted">
                                                    Escolha um novo arquivo apenas se quiser substituir
                                                    o arquivo atual. Máximo de 2 MB.
                                                </small>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                                Cancelar
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                Salvar alterações
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <?php if (
                            $cotacao['status'] === 'concluido' &&
                            !empty($cotacao['justificativa'])
                        ): ?>
                            <hr>

                            <div class="form-group">
                                <strong>Justificativa da escolha</strong>
                                <p>
                                    <?= nl2br(
                                        htmlspecialchars(
                                            $cotacao['justificativa'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        )
                                    ) ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <hr>

                        <a href="<?= WWW ?>controle/control.php?nomeClasse=CotacaoControle&amp;metodo=listar&amp;tipo=<?= $origemLista ?>" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i>
                            Voltar
                        </a>
                    </div>
                </section>
            </section>
        </div>
    </section>
    <script>
        const fornecedoresUsados = <?= json_encode(array_map(static function ($item) {
            return (string) $item['id_fornecedor'];
        }, $orcamentos), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const opcoesFornecedores = Array.from(document.getElementById('novoFornecedor').options)
            .map(function (opcao) { return opcao.cloneNode(true); });
        function filtrarFornecedores(select, atual) {
            select.replaceChildren();
            opcoesFornecedores.forEach(function (opcao) {
                if (!opcao.value || opcao.value === atual || !fornecedoresUsados.includes(opcao.value)) {
                    select.appendChild(opcao.cloneNode(true));
                }
            });
            select.value = atual;
        }
        filtrarFornecedores(document.getElementById('novoFornecedor'), '');

        $('#modalEditarOrcamento').on('show.bs.modal', function(event) {
            const botao = $(event.relatedTarget);

            const id = botao.data('id');
            const fornecedor = botao.data('fornecedor');
            const valor = botao.data('valor');
            const prazo = botao.data('prazo');
            const arquivo = botao.data('arquivo');

            $('#editarIdOrcamento').val(id);
            filtrarFornecedores(document.getElementById('editarFornecedor'), String(fornecedor));
            $('#editarValor').val(valor);
            $('#editarPrazo').val(prazo);

            $('#editarArquivo').val('');

            if (arquivo) {
                $('#editarArquivoAtual').text(
                    'Arquivo atual: ' + arquivo
                );
            } else {
                $('#editarArquivoAtual').text(
                    'Este orçamento ainda não possui arquivo.'
                );
            }
        });

        const camposArquivo = document.querySelectorAll('#editarArquivo, #arquivoNovoOrcamento');
        const TAMANHO_MAXIMO_ARQUIVO = <?= CotacaoSuporte::TAMANHO_MAXIMO_ARQUIVO ?>;

        camposArquivo.forEach(function (campo) {
            campo.addEventListener('change', function() {
                const arquivo = this.files[0];

                if (!arquivo) {
                    return;
                }

                if (arquivo.size > TAMANHO_MAXIMO_ARQUIVO) {
                    alert(
                        'O arquivo do orçamento deve possuir no máximo 2 MB.'
                    );

                    this.value = '';
                }
            });
        });
    </script>
    <script src="../../assets/javascripts/forms/cotacao.js"></script>
</body>

</html>
