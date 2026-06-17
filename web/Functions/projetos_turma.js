const TURMAS_POR_PAGINA = 10;
let turmasDados = [];
let turmasPaginaAtual = 1;
// ================== FUNÇÕES ==================

function adicionarTurma() {
    const nome      = $('#nova_turma_nome').val().trim();
    const descricao = $('#nova_turma_descricao').val().trim();
    const projetoId = $('#id_projeto').val();
    const csrfToken = $('#csrf_token').val();

    if (!nome) { alert('Informe o nome da turma.'); return; }

    const $btn = $('#btn-adicionar-turma');
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'adicionarTurma',
            nomeClasse: 'ProjetoControle',
            projeto_id: projetoId,
            nome: nome,
            descricao: descricao,
            csrf_token: csrfToken
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#nova_turma_nome').val('');
                $('#nova_turma_descricao').val('');
                recarregarListaTurmas();
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

function removerTurma(id_turma) {
    if (!confirm('Tem certeza que deseja remover esta turma?')) return;

    const projetoId    = $('#id_projeto').val();
    const csrfToken    = $('#csrf_token').val();
    const $btn         = $('#btn-remover-turma-' + id_turma);
    const originalHtml = $btn.html();
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'removerTurma',
            nomeClasse: 'ProjetoControle',
            id_turma: id_turma,
            projeto_id: projetoId,
            csrf_token: csrfToken
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                recarregarListaTurmas();
            } else {
                alert('Erro: ' + (response.message || 'Tente novamente.'));
                $btn.prop('disabled', false).html(originalHtml);
            }
        },
        error: function(xhr) {
            alert('Erro ao conectar com o servidor.');
            console.error(xhr.responseText);
            $btn.prop('disabled', false).html(originalHtml);
        }
    });
}

function recarregarListaTurmas() {
    const projetoId = $('#id_projeto').val();
    const params = {
        metodo: 'listarTurmasAjax',
        nomeClasse: 'ProjetoControle',
        projeto_id: projetoId
    };

    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: params,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                turmasDados = response.data;
                turmasPaginaAtual = 1;
                renderizarPaginaTurmas();
            } else {
                console.error('Erro ao recarregar lista:', response.message);
            }
        },
        error: function(xhr) { console.error('Erro ao recarregar lista:', xhr.responseText); }
    });
}

// ================== PAGINAÇÃO ==================

function renderizarPaginaTurmas() {
    const inicio       = (turmasPaginaAtual - 1) * TURMAS_POR_PAGINA;
    const fim          = inicio + TURMAS_POR_PAGINA;
    const totalPaginas = Math.ceil(turmasDados.length / TURMAS_POR_PAGINA);
    atualizarTabelaTurmas(turmasDados.slice(inicio, fim));
    renderizarPaginacaoTurmas(totalPaginas);
}

function atualizarTabelaTurmas(dados) {
    const $tbody = $('#turmas-tab');
    $tbody.empty();

    if (!dados || dados.length === 0) {
        $tbody.html('<tr><td colspan="3" class="text-center">Nenhuma turma encontrada.</td></tr>');
        return;
    }

    dados.forEach(function(turma) {
        const descricao = turma.descricao || '--';

        const acoes = '<button type="button" onclick="event.stopPropagation(); removerTurma(' + turma.id_turma + ')" ' +
                    'id="btn-remover-turma-' + turma.id_turma + '" class="btn btn-danger btn-xs" title="Remover turma">' +
                    '<i class="fa fa-trash"></i></button>';

        $('<tr>')
            .attr('id', 'turma-' + turma.id_turma)
            .css('cursor', 'pointer')
            .on('click', function() {
                window.location.href = 'editar_turma_projeto.php?id_turma=' + turma.id_turma;
            })
            .append($('<td>').text(turma.nome))
            .append($('<td>').text(descricao))
            .append($('<td class="actions text-center">').html(acoes))
            .appendTo($tbody);
    });
}

function renderizarPaginacaoTurmas(totalPaginas) {
    const $p = $('#turmas-paginacao');
    $p.empty();
    if (totalPaginas <= 1) return;

    $p.append('<li class="' + (turmasPaginaAtual === 1 ? 'disabled' : '') + '">' +
        '<a href="#" onclick="mudarPaginaTurmas(' + (turmasPaginaAtual - 1) + '); return false;">&laquo;</a></li>');

    for (var i = 1; i <= totalPaginas; i++) {
        $p.append('<li class="' + (i === turmasPaginaAtual ? 'active' : '') + '">' +
            '<a href="#" onclick="mudarPaginaTurmas(' + i + '); return false;">' + i + '</a></li>');
    }

    $p.append('<li class="' + (turmasPaginaAtual === totalPaginas ? 'disabled' : '') + '">' +
        '<a href="#" onclick="mudarPaginaTurmas(' + (turmasPaginaAtual + 1) + '); return false;">&raquo;</a></li>');
}

function mudarPaginaTurmas(pagina) {
    const totalPaginas = Math.ceil(turmasDados.length / TURMAS_POR_PAGINA);
    if (pagina < 1 || pagina > totalPaginas) return;
    turmasPaginaAtual = pagina;
    renderizarPaginaTurmas();
}

$(document).ready(function() {
    recarregarListaTurmas();
});