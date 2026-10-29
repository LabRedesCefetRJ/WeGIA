<?php

require_once dirname(__FILE__, 2)
    . DIRECTORY_SEPARATOR . 'seguranca'
    . DIRECTORY_SEPARATOR . 'security_headers.php';
require_once dirname(__FILE__, 3)
    . DIRECTORY_SEPARATOR . 'config.php';

require_once ROOT . "/html/personalizacao_display.php";
require_once ROOT . "/html/geral/msg.php";
require_once ROOT . '/classes/Csrf.php';
require_once ROOT . '/classes/CotacaoSuporte.php';
require_once ROOT . '/classes/Orcamento.php';

if (!isset($fornecedores) || !is_array($fornecedores)) {
    http_response_code(500);
    exit('Não foi possível carregar o cadastro da cotação.');
}
if (!isset($_SESSION['rascunho_cadastro_cotacao_token'])) {
    $_SESSION['rascunho_cadastro_cotacao_token'] = bin2hex(random_bytes(16));
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

                    <h2>Nova Cotação</h2>

                    <div class="right-wrapper pull-right">

                        <ol class="breadcrumbs">

                            <li>
                                <a href="<?= WWW ?>html/home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>

                            <li>
                                <span>Processo de Compra</span>
                            </li>

                            <li>
                                <span>Nova Cotação</span>
                            </li>

                        </ol>

                        <a class="sidebar-right-toggle">
                            <i class="fa fa-chevron-left"></i>
                        </a>

                    </div>

                </header>

                <?php getMsg(); ?>

                <section class="panel panel-primary">

                    <header class="panel-heading">
                        <h2 class="panel-title">
                            Cadastro de Cotação
                        </h2>
                    </header>

                    <div class="panel-body">

                        <form data-cotacao-form
                            id="formCotacao"
                            method="post"
                            action="<?= WWW ?>controle/control.php"
                            enctype="multipart/form-data"
                        >

                            <input
                                type="hidden"
                                name="nomeClasse"
                                value="CotacaoControle"
                            >

                            <input
                                type="hidden"
                                name="metodo"
                                value="incluir"
                            >

                            <?= Csrf::inputField() ?>

                            <div class="form-group">

                                <label for="descricao">
                                    Descrição da cotação
                                    <span class="text-danger">*</span>
                                </label>

                                <textarea
                                    name="descricao"
                                    id="descricao"
                                    class="form-control"
                                    maxlength="255"
                                    rows="3"
                                    required
                                    placeholder="Ex.: Compra de materiais de escritório"
                                ></textarea>

                            </div>

                            <hr>

                            <h4>Orçamentos</h4>

                            <div id="listaOrcamentos">

                                <div class="panel panel-default orcamento">

                                    <header class="panel-heading">
                                        <button
                                            type="button"
                                            class="btn btn-xs btn-danger pull-right btn-remover-orcamento"
                                            style="display: none;"
                                        >
                                            <i class="fa fa-trash"></i>
                                            Remover
                                        </button>

                                        <h3 class="panel-title">
                                            Orçamento <span class="numero-orcamento">1</span>
                                        </h3>
                                    </header>

                                    <div class="panel-body">

                                        <div class="row">

                                            <div class="form-group col-md-3">
                                                <label for="fornecedor-0">
                                                    Fornecedor
                                                    <span class="text-danger">*</span>
                                                    <a href="<?= WWW ?>html/matPat/cadastro_doador.php?origem=cotacao" data-cadastrar-fornecedor title="Cadastrar fornecedor" aria-label="Cadastrar fornecedor"><i class="fas fa-plus w3-xlarge" aria-hidden="true"></i></a>
                                                </label>

                                                <select
                                                    name="id_fornecedor[]"
                                                    id="fornecedor-0"
                                                    class="form-control fornecedor"
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

                                            <div class="form-group col-md-3">

                                                <label for="valor-0">
                                                    Valor <span class="text-danger">*</span>
                                                </label>

                                                <input
                                                    type="number"
                                                    name="valor[]"
                                                    id="valor-0"
                                                    class="form-control"
                                                    min="0"
                                                    step="0.01"
                                                    max="99999999.99"
                                                    required
                                                >

                                            </div>

                                            <div class="form-group col-md-3">
                                                <label for="condicao-pagamento-0">Condição de pagamento <span class="text-danger">*</span></label>
                                                <select name="condicao_pagamento[]" id="condicao-pagamento-0" class="form-control" required>
                                                    <option value="" selected disabled>Selecionar</option>
                                                    <?php foreach (Orcamento::CONDICOES_PAGAMENTO as $valorCondicao => $rotuloCondicao): ?>
                                                        <option value="<?= htmlspecialchars($valorCondicao, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($rotuloCondicao, ENT_QUOTES, 'UTF-8') ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-md-3">

                                                <label for="prazo-0">Prazo de entrega</label>

                                                <input
                                                    type="date"
                                                    name="prazo_entrega[]"
                                                    id="prazo-0"
                                                    class="form-control"
                                                    min="1000-01-01"
                                                    max="9999-12-31"
                                                >

                                            </div>

                                        </div>

                                        <div class="form-group">

                                            <label for="arquivo-0" >Arquivo do orçamento</label>
                                            <small class="text-muted">
                                                Formatos permitidos: PDF, JPG, JPEG ou PNG. Máximo de <?= CotacaoSuporte::tamanhoMaximoArquivoFormatado() ?>.
                                            </small>

                                            <input
                                                type="file"
                                                name="arquivo[]"
                                                id="arquivo-0"
                                                class="form-control"
                                                accept=".pdf,.jpg,.jpeg,.png"
                                            >

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <button
                                type="button"
                                id="adicionarOrcamento"
                                class="btn btn-default"
                            >
                                <i class="fa fa-plus"></i>
                                Adicionar orçamento
                            </button>

                            <hr>

                            <div style="text-align: right;">

                                <a
                                    href="<?= WWW ?>controle/control.php?nomeClasse=CotacaoControle&amp;metodo=listar"
                                    class="btn btn-default"
                                    id="cancelarCotacao"
                                >
                                    Cancelar
                                </a>

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Cadastrar Cotação
                                </button>

                            </div>

                        </form>

                    </div>

                </section>
            </section>
        </div>
    </section>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const TAMANHO_MAXIMO_ARQUIVO = <?= CotacaoSuporte::tamanhoMaximoArquivo() ?>;

            const listaOrcamentos = document.getElementById('listaOrcamentos');
            const btnAdicionar = document.getElementById('adicionarOrcamento');

            const LIMITE_ORCAMENTOS = 3;
            const opcoesFornecedores = Array.from(
                listaOrcamentos.querySelector('select[name="id_fornecedor[]"]').options
            ).map(function (opcao) { return opcao.cloneNode(true); });

            function atualizarFornecedores() {
                const selects = Array.from(listaOrcamentos.querySelectorAll('select[name="id_fornecedor[]"]'));
                const escolhidos = selects.map(function (select) { return select.value; });
                selects.forEach(function (select, indice) {
                    const atual = escolhidos[indice];
                    select.replaceChildren();
                    opcoesFornecedores.forEach(function (opcao) {
                        if (!opcao.value || opcao.value === atual || !escolhidos.includes(opcao.value)) {
                            select.appendChild(opcao.cloneNode(true));
                        }
                    });
                    select.value = atual;
                });
            }

            listaOrcamentos.addEventListener('change', function (event) {
                if (event.target.matches('select[name="id_fornecedor[]"]')) atualizarFornecedores();
            });

            listaOrcamentos.addEventListener('change', function (event) {
                if (!event.target.matches('input[type="file"][name="arquivo[]"]')) {
                    return;
                }

                const arquivo = event.target.files[0];

                if (!arquivo) {
                    return;
                }

                if (arquivo.size > TAMANHO_MAXIMO_ARQUIVO) {
                    alert(<?= json_encode('O arquivo do orçamento deve possuir no máximo ' . CotacaoSuporte::tamanhoMaximoArquivoFormatado() . '.') ?>);
                    event.target.value = '';
                }
            });

            btnAdicionar.addEventListener('click', function () {

                const orcamentos = listaOrcamentos.querySelectorAll('.orcamento');

                if (orcamentos.length >= LIMITE_ORCAMENTOS) {
                    return;
                }

                const primeiroOrcamento = orcamentos[0];

                const novoOrcamento = primeiroOrcamento.cloneNode(true);

                limparOrcamento(novoOrcamento);

                novoOrcamento
                    .querySelector('.btn-remover-orcamento')
                    .style.display = '';

                listaOrcamentos.appendChild(novoOrcamento);

                atualizarOrcamentos();
            });

            listaOrcamentos.addEventListener('click', function (event) {

                const botaoRemover = event.target.closest(
                    '.btn-remover-orcamento'
                );

                if (!botaoRemover) {
                    return;
                }

                const orcamento = botaoRemover.closest('.orcamento');

                orcamento.remove();

                atualizarOrcamentos();
            });

            function limparOrcamento(orcamento) {
                delete orcamento.dataset.arquivoPendente;
                orcamento.querySelectorAll('.aviso-arquivo-rascunho').forEach(function (aviso) { aviso.remove(); });

                const selectFornecedor = orcamento.querySelector(
                    'select[name="id_fornecedor[]"]'
                );

                selectFornecedor.selectedIndex = 0;
                orcamento.querySelector('[name="condicao_pagamento[]"]').selectedIndex = 0;

                const valor = orcamento.querySelector(
                    'input[name="valor[]"]'
                );

                valor.value = '';

                const prazo = orcamento.querySelector(
                    'input[name="prazo_entrega[]"]'
                );

                prazo.value = '';

                const arquivo = orcamento.querySelector(
                    'input[name="arquivo[]"]'
                );

                arquivo.value = '';
            }

            function atualizarOrcamentos() {
                atualizarFornecedores();

                const orcamentos = listaOrcamentos.querySelectorAll(
                    '.orcamento'
                );

                orcamentos.forEach(function (orcamento, indice) {

                    const numero = orcamento.querySelector(
                        '.numero-orcamento'
                    );

                    numero.textContent = indice + 1;

                    orcamento.querySelectorAll('input, select').forEach(function (campo) {
                        const label = orcamento.querySelector('label[for="' + campo.id + '"]');
                        const prefixo = campo.id.replace(/-\d+$/, '');
                        campo.id = prefixo + '-' + indice;
                        if (label) {
                            label.htmlFor = campo.id;
                        }
                    });

                    const botaoRemover = orcamento.querySelector(
                        '.btn-remover-orcamento'
                    );

                    if (indice === 0) {
                        botaoRemover.style.display = 'none';
                    } else {
                        botaoRemover.style.display = '';
                    }
                });

                if (orcamentos.length >= LIMITE_ORCAMENTOS) {
                    btnAdicionar.style.display = 'none';
                } else {
                    btnAdicionar.style.display = '';
                }
            }


            const form = document.getElementById('formCotacao');
            const CHAVE = 'rascunho_cadastro_cotacao';
            const TOKEN_SESSAO = <?= json_encode($_SESSION['rascunho_cadastro_cotacao_token']) ?>;
            let rascunhoConcluido = false;
            let restaurando = false;

            function salvarRascunho() {
                if (rascunhoConcluido || restaurando) return;
                const dados = {
                    tokenSessao: TOKEN_SESSAO,
                    descricao: form.elements.descricao.value,
                    orcamentos: Array.from(listaOrcamentos.querySelectorAll('.orcamento')).map(function (orcamento) {
                        return {
                            fornecedor: orcamento.querySelector('select[name="id_fornecedor[]"]').value,
                            valor: orcamento.querySelector('[name="valor[]"]').value,
                            condicaoPagamento: orcamento.querySelector('[name="condicao_pagamento[]"]').value,
                            prazo: orcamento.querySelector('[name="prazo_entrega[]"]').value,
                            tinhaArquivo: orcamento.querySelector('[type="file"]').files.length > 0 ||
                                orcamento.dataset.arquivoPendente === '1'
                        };
                    })
                };
                try {
                    sessionStorage.setItem(CHAVE, JSON.stringify(dados));
                } catch (erro) {
                    console.error('Não foi possível salvar o rascunho da cotação:', erro);
                }
            }

            function limparRascunho() {
                rascunhoConcluido = true;
                try { sessionStorage.removeItem(CHAVE); } catch (erro) {
                    console.error('Não foi possível limpar o rascunho da cotação:', erro);
                }
            }

            function restaurarRascunho() {
                restaurando = true;
                try {
                    const bruto = sessionStorage.getItem(CHAVE);
                    if (!bruto) return;
                    const dados = JSON.parse(bruto);
                    if (dados.tokenSessao !== TOKEN_SESSAO || !Array.isArray(dados.orcamentos)) {
                        sessionStorage.removeItem(CHAVE);
                        return;
                    }
                    form.elements.descricao.value = dados.descricao || '';
                    const orcamentos = dados.orcamentos.slice(0, LIMITE_ORCAMENTOS);
                    while (listaOrcamentos.querySelectorAll('.orcamento').length < orcamentos.length) {
                        btnAdicionar.click();
                    }
                    listaOrcamentos.querySelectorAll('.orcamento').forEach(function (orcamento, indice) {
                        const salvo = orcamentos[indice];
                        if (!salvo) return;
                        const select = orcamento.querySelector('select[name="id_fornecedor[]"]');
                        select.replaceChildren(...opcoesFornecedores.map(function (opcao) { return opcao.cloneNode(true); }));
                        select.value = salvo.fornecedor || '';
                        orcamento.querySelector('[name="condicao_pagamento[]"]').value = salvo.condicaoPagamento || '';
                        orcamento.querySelector('[name="valor[]"]').value = salvo.valor || '';
                        orcamento.querySelector('[name="prazo_entrega[]"]').value = salvo.prazo || '';
                        if (salvo.tinhaArquivo) {
                            orcamento.dataset.arquivoPendente = '1';
                            const aviso = document.createElement('p');
                            aviso.className = 'text-warning aviso-arquivo-rascunho';
                            aviso.textContent = 'Selecione novamente o arquivo deste orçamento. Os demais dados foram restaurados.';
                            orcamento.querySelector('[type="file"]').after(aviso);
                        }
                    });
                    atualizarOrcamentos();
                } catch (erro) {
                    console.error('Não foi possível restaurar o rascunho da cotação:', erro);
                } finally {
                    restaurando = false;
                }
            }

            form.addEventListener('input', salvarRascunho);
            form.addEventListener('change', function (event) {
                if (event.target.type === 'file' && event.target.files.length) {
                    const orcamento = event.target.closest('.orcamento');
                    delete orcamento.dataset.arquivoPendente;
                    const aviso = orcamento.querySelector('.aviso-arquivo-rascunho');
                    if (aviso) aviso.remove();
                }
                salvarRascunho();
            });
            form.addEventListener('click', function () { salvarRascunho(); });
            window.addEventListener('pagehide', salvarRascunho);
            form.addEventListener('cotacao:sucesso', limparRascunho);
            document.getElementById('cancelarCotacao').addEventListener('click', limparRascunho);
            restaurarRascunho();

        });
    </script>
    <script>
        window.limitesUploadCotacao = <?= json_encode(CotacaoSuporte::limitesUpload()) ?>;
    </script>
    <script src="../../assets/javascripts/forms/cotacao.js"></script>
</body>
</html>
