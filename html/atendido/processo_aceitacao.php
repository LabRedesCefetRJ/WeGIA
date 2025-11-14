<?php
session_start();
// Verificação de usuário e permissões padrão
// Inclusão de config.php e outros controladores aqui

$processosAtivos = []; // Buscar processos no controlador
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html class="fixed">
<head>
    <meta charset="UTF-8">
    <title>Processo de Aceitação</title>

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />

    <script src="../../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>

    <style>
        /* Painéis com fundo #0088cc e texto branco */
        .panel-primary > .panel-heading {
            background-color: #0088cc !important;
            border-color: #0088cc !important;
            color: white !important;
        }
        /* Opcional: corpo do painel com cor leve */
        .panel-primary > .panel-body {
            background-color: #b3d7f5;
            color: #003366;
        }
    </style>

    <script>
        $(function() {
            $("#header").load("../header.php");
            $(".menuu").load("../menu.php");
        });
    </script>
</head>
<body>
<section class="body">
    <div id="header"></div>
    <div class="inner-wrapper">
        <aside id="sidebar-left" class="sidebar-left menuu"></aside>
        <section role="main" class="content-body">
            <header class="page-header">
                <h2>Processo de Aceitação</h2>
            </header>

            <!-- Painel Cadastro - colapsado por padrão -->
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title text-center">Cadastro de Novo Processo</h3>
                    <div class="panel-actions">
                        <a href="#collapseCadastro" class="fa fa-caret-down" title="Mostrar/ocultar"
                           data-toggle="collapse" aria-expanded="false" aria-controls="collapseCadastro"></a>
                    </div>
                </div>
                <div id="collapseCadastro" class="panel-collapse collapse">
                    <div class="panel-body">
                        <form id="formProcesso" method="POST" action="../controller/control.php" enctype="multipart/form-data">
                            <input type="hidden" name="nomeClasse" value="ProcessoAceitacaoController">
                            <input type="hidden" name="metodo" value="incluir">

                            <div class="form-group">
                                <label>Nome <span class="text-danger">*</span></label>
                                <input type="text" name="nome" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Sobrenome <span class="text-danger">*</span></label>
                                <input type="text" name="sobrenome" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>CPF <span class="text-danger">*</span></label>
                                <input type="text" name="cpf" class="form-control" required maxlength="14" placeholder="000.000.000-00" onkeypress="return onlyNumbers(event);">
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Cadastrar Processo</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal para adicionar etapa (sem alteração) -->
            <div class="modal fade" id="modalEtapa" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <form id="formEtapa" class="modal-content" onsubmit="return false;">
                        <div class="modal-header">
                            <h5 class="modal-title">Adicionar Etapa</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Data de Início <span class="text-danger">*</span></label>
                                <input type="date" name="data_inicio" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Data de Conclusão</label>
                                <input type="date" name="data_conclusao" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Descrição <span class="text-danger">*</span></label>
                                <textarea name="descricao" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Arquivos (opcional)</label>
                                <input type="file" name="arquivos[]" class="form-control" multiple>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="concluida" class="form-check-input" id="concluida">
                                <label class="form-check-label" for="concluida">Etapa Concluída</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="btnAdicionarEtapa">Adicionar Etapa</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Painel Lista Processos Ativos -->
            <div class="panel panel-primary mt-4">
                <div class="panel-heading">
                    <h3 class="panel-title text-center">Processos Ativos</h3>
                    <div class="panel-actions">
                        <a href="#collapseAtivos" class="fa fa-caret-up" title="Mostrar/ocultar"
                            data-toggle="collapse" aria-expanded="true" aria-controls="collapseAtivos"></a>
                    </div>
                </div>
                <div id="collapseAtivos" class="panel-collapse collapse in">
                    <div class="panel-body">
                        <?php if (empty($processosAtivos)): ?>
                            <div class="alert alert-warning text-center">Nenhum processo ativo encontrado.</div>
                        <?php else: ?>
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>CPF</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($processosAtivos as $processo): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($processo['nome'] . ' ' . $processo['sobrenome']) ?></td>
                                            <td><?= htmlspecialchars($processo['cpf']) ?></td>
                                            <td><?= htmlspecialchars($processo['status']) ?></td>
                                            <td>
                                                <a href="editar_processo.php?id=<?= $processo['id'] ?>" class="btn btn-sm btn-primary">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</section>

<script>
    let etapas = [];

    // Adicionar etapa via modal
    $('#btnAdicionarEtapa').on('click', function() {
        const form = document.getElementById('formEtapa');
        const etapa = {
            data_inicio: form.data_inicio.value,
            data_conclusao: form.data_conclusao.value,
            descricao: form.descricao.value,
            concluida: form.concluida.checked
        };
        etapas.push(etapa);
        atualizarListaEtapas();
        $('#modalEtapa').modal('hide');
        form.reset();
    });

    function atualizarListaEtapas() {
        const lista = document.getElementById('listaEtapas');
        lista.innerHTML = '';
        etapas.forEach((etapa, i) => {
            lista.innerHTML += `<div><b>Etapa ${i + 1}</b>: ${etapa.descricao} (Início: ${etapa.data_inicio}) - Completa: ${etapa.concluida ? 'Sim' : 'Não'}</div>`;
        });
    }

    // Serializar etapas ao enviar form
    $('#formProcesso').on('submit', function(e) {
        $('<input>').attr({
            type: 'hidden',
            name: 'etapas_json',
            value: JSON.stringify(etapas)
        }).appendTo(this);
    });

    function onlyNumbers(evt) {
        let charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }
</script>
</body>
</html>
