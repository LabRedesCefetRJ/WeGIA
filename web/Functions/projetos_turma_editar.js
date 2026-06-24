// ================== DATATABLES ==================

var tabelaExecutantesTurma;
var tabelaAtendidosTurma;

// ================== DADOS DA TURMA ==================

function submeterEdicaoTurma() {
    const nome      = $('#nome_turma').val().trim();
    const descricao = $('#descricao_turma').val().trim();
    const idTurma   = $('#id_turma').val();
    const idProjeto = $('#id_projeto').val();
    const csrf      = $('#csrf_token').val();

    if (!nome) { alert('Nome da turma inválido.'); return; }

    $('#btn-salvar').prop('disabled', true).text('Salvando...');

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'editarTurma',
            nomeClasse: 'ProjetoControle',
            id_turma: idTurma,
            projeto_id: idProjeto,
            nome: nome,
            descricao: descricao,
            csrf_token: csrf
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                window.location.href = 'editar_projeto.php?id_projeto=' + idProjeto + '&msg=Turma alterada com sucesso!';
            } else {
                alert('Erro: ' + (response.message || 'Tente novamente.'));
                $('#btn-salvar').prop('disabled', false).text('Salvar Alterações');
            }
        },
        error: function(xhr) {
            alert('Erro ao salvar alterações.');
            console.error(xhr.responseText);
            $('#btn-salvar').prop('disabled', false).text('Salvar Alterações');
        }
    });
}

// ================== EXECUTANTES DA TURMA ==================

function carregarExecutantesDisponiveisTurma() {
    const idProjeto = $('#id_projeto').val();
    const idTurma   = $('#id_turma').val();

    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: {
            metodo: 'listarExecutantesForaDaTurmaAjax',
            nomeClasse: 'ProjetoControle',
            projeto_id: idProjeto,
            turma_id: idTurma
        },
        dataType: 'json',
        success: function(response) {
            const $select = $('#novo_executante_turma');
            $select.empty().append('<option selected disabled>Selecionar Executante</option>');
            if (response.success && response.data) {
                $.each(response.data, function(i, m) {
                    const nome = ((m.nome || '').trim() + ' ' + (m.sobrenome || '').trim()).trim();
                    $select.append('<option value="' + m.id_pessoa + '">' + escapeHtml(nome) + '</option>');
                });
            }
        },
        error: function(xhr) { console.error('Erro ao carregar executantes disponíveis:', xhr.responseText); }
    });
}

function adicionarExecutanteNaTurma() {
    const idPessoa = $('#novo_executante_turma').val();
    const idTurma  = $('#id_turma').val();
    const csrf     = $('#csrf_token').val();

    if (!idPessoa) { alert('Selecione um executante.'); return; }

    const $btn = $('#btn-adicionar-executante-turma');
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'adicionarExecutanteTurma',
            nomeClasse: 'ProjetoControle',
            id_turma: idTurma,
            id_pessoa: idPessoa,
            csrf_token: csrf
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                recarregarExecutantesTurma();
                carregarExecutantesDisponiveisTurma();
            } else {
                alert('Erro: ' + (response.message || 'Tente novamente.'));
            }
            $btn.prop('disabled', false).html('<i class="fa fa-plus"></i>');
        },
        error: function(xhr) {
            alert('Erro ao conectar com o servidor.');
            console.error(xhr.responseText);
            $btn.prop('disabled', false).html('<i class="fa fa-plus"></i>');
        }
    });
}

function removerExecutanteDaTurma(idPessoa) {
    if (!confirm('Remover este executante da turma?')) return;

    const idTurma = $('#id_turma').val();
    const csrf    = $('#csrf_token').val();

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'removerExecutanteTurma',
            nomeClasse: 'ProjetoControle',
            id_turma: idTurma,
            id_pessoa: idPessoa,
            csrf_token: csrf
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                recarregarExecutantesTurma();
                carregarExecutantesDisponiveisTurma();
            } else {
                alert('Erro: ' + (response.message || 'Tente novamente.'));
            }
        },
        error: function(xhr) {
            alert('Erro ao conectar com o servidor.');
            console.error(xhr.responseText);
        }
    });
}

function recarregarExecutantesTurma() {
    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: {
            metodo: 'listarExecutantesDaTurmaAjax',
            nomeClasse: 'ProjetoControle',
            turma_id: $('#id_turma').val()
        },
        dataType: 'json',
        success: function(response) {
            tabelaExecutantesTurma.clear();
            if (response.success && response.data) {
                $.each(response.data, function(i, executante) {
                    const nomeCompleto = ((executante.nome || '') + ' ' + (executante.sobrenome || '')).trim();
                    const cpf          = executante.cpf || '--';
                    const acoes        =
                        '<button type="button" onclick="removerExecutanteDaTurma(' + executante.id_pessoa + ')" ' +
                        'class="btn btn-danger btn-xs" title="Remover da turma">' +
                        '<i class="fa fa-trash"></i></button>';

                    tabelaExecutantesTurma.row.add([
                        escapeHtml(nomeCompleto),
                        escapeHtml(cpf),
                        acoes
                    ]).nodes().to$().attr('id', 'executante-turma-' + executante.id_pessoa);
                });
            } else {
                console.error('Erro ao recarregar executantes da turma:', response.message);
            }
            tabelaExecutantesTurma.draw();
        },
        error: function(xhr) { console.error('Erro ao recarregar executantes da turma:', xhr.responseText); }
    });
}

// ================== ATENDIDOS DA TURMA ==================

function carregarAtendidosDisponiveisTurma() {
    const idProjeto = $('#id_projeto').val();
    const idTurma   = $('#id_turma').val();

    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: {
            metodo: 'listarAtendidosForaDaTurmaAjax',
            nomeClasse: 'ProjetoControle',
            projeto_id: idProjeto,
            turma_id: idTurma
        },
        dataType: 'json',
        success: function(response) {
            const $select = $('#novo_atendido_turma');
            $select.empty().append('<option selected disabled>Selecionar Atendido</option>');
            if (response.success && response.data) {
                $.each(response.data, function(i, a) {
                    const nome = ((a.nome || '').trim() + ' ' + (a.sobrenome || '').trim()).trim();
                    $select.append('<option value="' + a.id_atendido + '">' + escapeHtml(nome) + '</option>');
                });
            }
        },
        error: function(xhr) { console.error('Erro ao carregar atendidos disponíveis:', xhr.responseText); }
    });
}

function adicionarAtendidoNaTurma() {
    const idAtendido = $('#novo_atendido_turma').val();
    const idTurma    = $('#id_turma').val();
    const csrf       = $('#csrf_token').val();

    if (!idAtendido) { alert('Selecione um atendido.'); return; }

    const $btn = $('#btn-adicionar-atendido-turma');
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'adicionarAtendidoTurma',
            nomeClasse: 'ProjetoControle',
            id_turma: idTurma,
            id_atendido: idAtendido,
            csrf_token: csrf
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                recarregarAtendidosTurma();
                carregarAtendidosDisponiveisTurma();
            } else {
                alert('Erro: ' + (response.message || 'Tente novamente.'));
            }
            $btn.prop('disabled', false).html('<i class="fa fa-plus"></i>');
        },
        error: function(xhr) {
            alert('Erro ao conectar com o servidor.');
            console.error(xhr.responseText);
            $btn.prop('disabled', false).html('<i class="fa fa-plus"></i>');
        }
    });
}

function removerAtendidoDaTurma(idAtendido) {
    if (!confirm('Remover este atendido da turma?')) return;

    const idTurma = $('#id_turma').val();
    const csrf    = $('#csrf_token').val();

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'removerAtendidoTurma',
            nomeClasse: 'ProjetoControle',
            id_turma: idTurma,
            id_atendido: idAtendido,
            csrf_token: csrf
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                recarregarAtendidosTurma();
                carregarAtendidosDisponiveisTurma();
            } else {
                alert('Erro: ' + (response.message || 'Tente novamente.'));
            }
        },
        error: function(xhr) {
            alert('Erro ao conectar com o servidor.');
            console.error(xhr.responseText);
        }
    });
}

function recarregarAtendidosTurma() {
    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: {
            metodo: 'listarAtendidosDaTurmaAjax',
            nomeClasse: 'ProjetoControle',
            turma_id: $('#id_turma').val()
        },
        dataType: 'json',
        success: function(response) {
            tabelaAtendidosTurma.clear();
            if (response.success && response.data) {
                $.each(response.data, function(i, atendido) {
                    const nomeCompleto = ((atendido.nome || '') + ' ' + (atendido.sobrenome || '')).trim();
                    const cpf          = atendido.cpf || '--';
                    const acoes        =
                        '<button type="button" onclick="removerAtendidoDaTurma(' + atendido.id_atendido + ')" ' +
                        'class="btn btn-danger btn-xs" title="Remover da turma">' +
                        '<i class="fa fa-trash"></i></button>';

                    tabelaAtendidosTurma.row.add([
                        escapeHtml(nomeCompleto),
                        escapeHtml(cpf),
                        acoes
                    ]).nodes().to$().attr('id', 'atendido-turma-' + atendido.id_atendido);
                });
            } else {
                console.error('Erro ao recarregar atendidos da turma:', response.message);
            }
            tabelaAtendidosTurma.draw();
        },
        error: function(xhr) { console.error('Erro ao recarregar atendidos da turma:', xhr.responseText); }
    });
}

// ================== UTILS ==================

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

$(document).ready(function() {
    const dtConfig = {
        language: { url: '../../assets/vendor/jquery-datatables/i18n/pt-BR.json' },
        columnDefs: [{ orderable: false, targets: -1 }],
        pageLength: 10
    };

    tabelaExecutantesTurma = $('#executantes-turma-table').DataTable(dtConfig);
    tabelaAtendidosTurma   = $('#atendidos-turma-table').DataTable(dtConfig);

    $('#btn-salvar').on('click', function() {
        submeterEdicaoTurma();
    });

    carregarExecutantesDisponiveisTurma();
    recarregarExecutantesTurma();

    carregarAtendidosDisponiveisTurma();
    recarregarAtendidosTurma();
});