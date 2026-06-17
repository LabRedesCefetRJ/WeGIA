const EXECUTANTES_TURMA_POR_PAGINA = 10;
const ATENDIDOS_TURMA_POR_PAGINA   = 10;
let executantesTurmaDados = [];
let executantesTurmaPaginaAtual = 1;
let atendidosTurmaDados = [];
let atendidosTurmaPaginaAtual = 1;

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
    const idTurma = $('#id_turma').val();

    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: {
            metodo: 'listarExecutantesDaTurmaAjax',
            nomeClasse: 'ProjetoControle',
            turma_id: idTurma
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                executantesTurmaDados = response.data;
                executantesTurmaPaginaAtual = 1;
                renderizarPaginaExecutantesTurma();
            } else {
                console.error('Erro ao recarregar executantes da turma:', response.message);
            }
        },
        error: function(xhr) { console.error('Erro ao recarregar executantes da turma:', xhr.responseText); }
    });
}

function renderizarPaginaExecutantesTurma() {
    const inicio       = (executantesTurmaPaginaAtual - 1) * EXECUTANTES_TURMA_POR_PAGINA;
    const fim          = inicio + EXECUTANTES_TURMA_POR_PAGINA;
    const totalPaginas = Math.ceil(executantesTurmaDados.length / EXECUTANTES_TURMA_POR_PAGINA);
    atualizarTabelaExecutantesTurma(executantesTurmaDados.slice(inicio, fim));
    renderizarPaginacaoExecutantesTurma(totalPaginas);
}

function atualizarTabelaExecutantesTurma(dados) {
    const $tbody = $('#executantes-turma-tab');
    $tbody.empty();

    if (!dados || dados.length === 0) {
        $tbody.html('<tr><td colspan="3" class="text-center">Nenhum executante nesta turma.</td></tr>');
        return;
    }

    dados.forEach(function(executante) {
        const nomeCompleto = ((executante.nome || '') + ' ' + (executante.sobrenome || '')).trim();
        const cpf          = executante.cpf || '--';

        const acoes = '<button type="button" onclick="removerExecutanteDaTurma(' + executante.id_pessoa + ')" ' +
                    'class="btn btn-danger btn-xs" title="Remover da turma">' +
                    '<i class="fa fa-trash"></i></button>';

        $tbody.append(
            '<tr id="executante-turma-' + executante.id_pessoa + '">' +
            '<td>' + escapeHtml(nomeCompleto) + '</td>' +
            '<td>' + escapeHtml(cpf) + '</td>' +
            '<td class="actions text-center">' + acoes + '</td>' +
            '</tr>'
        );
    });
}

function renderizarPaginacaoExecutantesTurma(totalPaginas) {
    const $p = $('#executantes-turma-paginacao');
    $p.empty();
    if (totalPaginas <= 1) return;

    $p.append('<li class="' + (executantesTurmaPaginaAtual === 1 ? 'disabled' : '') + '">' +
        '<a href="#" onclick="mudarPaginaExecutantesTurma(' + (executantesTurmaPaginaAtual - 1) + '); return false;">&laquo;</a></li>');

    for (var i = 1; i <= totalPaginas; i++) {
        $p.append('<li class="' + (i === executantesTurmaPaginaAtual ? 'active' : '') + '">' +
            '<a href="#" onclick="mudarPaginaExecutantesTurma(' + i + '); return false;">' + i + '</a></li>');
    }

    $p.append('<li class="' + (executantesTurmaPaginaAtual === totalPaginas ? 'disabled' : '') + '">' +
        '<a href="#" onclick="mudarPaginaExecutantesTurma(' + (executantesTurmaPaginaAtual + 1) + '); return false;">&raquo;</a></li>');
}

function mudarPaginaExecutantesTurma(pagina) {
    const totalPaginas = Math.ceil(executantesTurmaDados.length / EXECUTANTES_TURMA_POR_PAGINA);
    if (pagina < 1 || pagina > totalPaginas) return;
    executantesTurmaPaginaAtual = pagina;
    renderizarPaginaExecutantesTurma();
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
    const idTurma = $('#id_turma').val();

    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: {
            metodo: 'listarAtendidosDaTurmaAjax',
            nomeClasse: 'ProjetoControle',
            turma_id: idTurma
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                atendidosTurmaDados = response.data;
                atendidosTurmaPaginaAtual = 1;
                renderizarPaginaAtendidosTurma();
            } else {
                console.error('Erro ao recarregar atendidos da turma:', response.message);
            }
        },
        error: function(xhr) { console.error('Erro ao recarregar atendidos da turma:', xhr.responseText); }
    });
}

function renderizarPaginaAtendidosTurma() {
    const inicio       = (atendidosTurmaPaginaAtual - 1) * ATENDIDOS_TURMA_POR_PAGINA;
    const fim          = inicio + ATENDIDOS_TURMA_POR_PAGINA;
    const totalPaginas = Math.ceil(atendidosTurmaDados.length / ATENDIDOS_TURMA_POR_PAGINA);
    atualizarTabelaAtendidosTurma(atendidosTurmaDados.slice(inicio, fim));
    renderizarPaginacaoAtendidosTurma(totalPaginas);
}

function atualizarTabelaAtendidosTurma(dados) {
    const $tbody = $('#atendidos-turma-tab');
    $tbody.empty();

    if (!dados || dados.length === 0) {
        $tbody.html('<tr><td colspan="3" class="text-center">Nenhum atendido nesta turma.</td></tr>');
        return;
    }

    dados.forEach(function(atendido) {
        const nomeCompleto = ((atendido.nome || '') + ' ' + (atendido.sobrenome || '')).trim();
        const cpf          = atendido.cpf || '--';

        const acoes = '<button type="button" onclick="removerAtendidoDaTurma(' + atendido.id_atendido + ')" ' +
                    'class="btn btn-danger btn-xs" title="Remover da turma">' +
                    '<i class="fa fa-trash"></i></button>';

        $tbody.append(
            '<tr id="atendido-turma-' + atendido.id_atendido + '">' +
            '<td>' + escapeHtml(nomeCompleto) + '</td>' +
            '<td>' + escapeHtml(cpf) + '</td>' +
            '<td class="actions text-center">' + acoes + '</td>' +
            '</tr>'
        );
    });
}

function renderizarPaginacaoAtendidosTurma(totalPaginas) {
    const $p = $('#atendidos-turma-paginacao');
    $p.empty();
    if (totalPaginas <= 1) return;

    $p.append('<li class="' + (atendidosTurmaPaginaAtual === 1 ? 'disabled' : '') + '">' +
        '<a href="#" onclick="mudarPaginaAtendidosTurma(' + (atendidosTurmaPaginaAtual - 1) + '); return false;">&laquo;</a></li>');

    for (var i = 1; i <= totalPaginas; i++) {
        $p.append('<li class="' + (i === atendidosTurmaPaginaAtual ? 'active' : '') + '">' +
            '<a href="#" onclick="mudarPaginaAtendidosTurma(' + i + '); return false;">' + i + '</a></li>');
    }

    $p.append('<li class="' + (atendidosTurmaPaginaAtual === totalPaginas ? 'disabled' : '') + '">' +
        '<a href="#" onclick="mudarPaginaAtendidosTurma(' + (atendidosTurmaPaginaAtual + 1) + '); return false;">&raquo;</a></li>');
}

function mudarPaginaAtendidosTurma(pagina) {
    const totalPaginas = Math.ceil(atendidosTurmaDados.length / ATENDIDOS_TURMA_POR_PAGINA);
    if (pagina < 1 || pagina > totalPaginas) return;
    atendidosTurmaPaginaAtual = pagina;
    renderizarPaginaAtendidosTurma();
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
    $('#btn-salvar').on('click', function() {
        submeterEdicaoTurma();
    });

    carregarExecutantesDisponiveisTurma();
    recarregarExecutantesTurma();

    carregarAtendidosDisponiveisTurma();
    recarregarAtendidosTurma();
});