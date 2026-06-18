const ATENDIDOS_POR_PAGINA = 10;
let atendidosDados = [];
let atendidosPaginaAtual = 1;
// ================== FUNÇÕES ==================

function carregarAtendidosDisponiveis() {
    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: { metodo: 'listarAtendidosAjax', nomeClasse: 'ProjetoControle' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                var select = $('#novo_atendido');
                select.empty().append('<option selected disabled>Selecionar Atendido</option>');
                $.each(response.data, function(i, atendido) {
                    var nome = ((atendido.nome || '').trim() + ' ' + (atendido.sobrenome || '').trim()).trim();
                    select.append('<option value="' + atendido.idatendido + '">' + escapeHtml(nome) + '</option>');
                });
            } else {
                console.error('Erro ao carregar atendidos:', response.message);
            }
        },
        error: function(xhr) { console.error('Erro ao carregar atendidos:', xhr.responseText); }
    });
}

function adicionarAtendidoProjeto() {
    const atendidoId = $('#novo_atendido').val();
    const projetoId  = $('#id_projeto').val();
    const csrfToken  = $('#csrf_token').val();

    if (!atendidoId) { alert('Selecione um atendido.'); return; }

    const $btn = $('#btn-adicionar-atendido');
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'adicionarAtendidoProjeto',
            nomeClasse: 'ProjetoControle',
            projeto_id: projetoId,
            atendido_id: atendidoId,
            csrf_token: csrfToken
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#novo_atendido').prop('selectedIndex', 0);
                recarregarListaAtendidos();
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

function removerAtendidoProjeto(id) {
    if (!confirm('Tem certeza que deseja remover este atendido do projeto?')) return;

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'removerAtendidoProjeto',
            nomeClasse: 'ProjetoControle',
            id: id,
            projeto_id: $('#id_projeto').val(),
            csrf_token: $('#csrf_token').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                recarregarListaAtendidos();
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

$(document).ready(
function() {
    const projetoId = $('#id_projeto').val();
    const params = {
        metodo: 'listarAtendidosProjetoAjax',
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
                atendidosDados = response.data;
                atendidosPaginaAtual = 1;
                renderizarPaginaAtendidos();
            } else {
                console.error('Erro ao carregar atendidos:', response.message);
                $('#atendidos-tab').html('<tr><td colspan="4" class="text-center">Erro ao carregar atendidos.</td></tr>');
            }
        },
        error: function(xhr) {
            console.error('Erro ao carregar atendidos:', xhr.responseText);
            $('#atendidos-tab').html('<tr><td colspan="4" class="text-center">Erro ao carregar atendidos.</td></tr>');
        }
    });
},

// ================== PAGINAÇÃO ==================

function renderizarPaginaAtendidos() {
    const inicio       = (atendidosPaginaAtual - 1) * ATENDIDOS_POR_PAGINA;
    const fim          = inicio + ATENDIDOS_POR_PAGINA;
    const totalPaginas = Math.ceil(atendidosDados.length / ATENDIDOS_POR_PAGINA);
    atualizarTabelaAtendidos(atendidosDados.slice(inicio, fim));
    renderizarPaginacaoAtendidos(totalPaginas);
},

function atualizarTabelaAtendidos(dados) {
    const $tbody = $('#atendidos-tab');
    $tbody.empty();

    if (!dados || dados.length === 0) {
        $tbody.html('<tr><td colspan="4" class="text-center">Nenhum atendido encontrado.</td></tr>');
        return;
    }

    dados.forEach(function(atendido) {
        const nomeCompleto = ((atendido.nome || '') + ' ' + (atendido.sobrenome || '')).trim();
        const cpf          = atendido.cpf || '--';
        const status       = atendido.status_descricao || '--';

        const acoes = '<button type="button" onclick="event.stopPropagation(); removerAtendidoProjeto(' + atendido.id + ')" ' +
                    'class="btn btn-danger btn-xs" title="Remover do projeto">' +
                    '<i class="fa fa-trash"></i></button>';

        $('<tr>')
            .attr('id', 'atendido-' + atendido.id)
            .css('cursor', 'pointer')
            .on('click', function() {
                window.location.href = 'editar_atendido_projeto.php?id=' + atendido.id;
            })
            .append($('<td>').text(nomeCompleto))
            .append($('<td>').text(cpf))
            .append($('<td>').text(status))
            .append($('<td class="actions text-center">').html(acoes))
            .appendTo($tbody);
    });
},

function renderizarPaginacaoAtendidos(totalPaginas) {
    const $p = $('#atendidos-paginacao');
    $p.empty();
    if (totalPaginas <= 1) return;

    $p.append('<li class="' + (atendidosPaginaAtual === 1 ? 'disabled' : '') + '">' +
        '<a href="#" onclick="mudarPaginaAtendidos(' + (atendidosPaginaAtual - 1) + '); return false;">&laquo;</a></li>');

    for (var i = 1; i <= totalPaginas; i++) {
        $p.append('<li class="' + (i === atendidosPaginaAtual ? 'active' : '') + '">' +
            '<a href="#" onclick="mudarPaginaAtendidos(' + i + '); return false;">' + i + '</a></li>');
    }

    $p.append('<li class="' + (atendidosPaginaAtual === totalPaginas ? 'disabled' : '') + '">' +
        '<a href="#" onclick="mudarPaginaAtendidos(' + (atendidosPaginaAtual + 1) + '); return false;">&raquo;</a></li>');
},

function mudarPaginaAtendidos(pagina) {
    const totalPaginas = Math.ceil(atendidosDados.length / ATENDIDOS_POR_PAGINA);
    if (pagina < 1 || pagina > totalPaginas) return;
    atendidosPaginaAtual = pagina;
    renderizarPaginaAtendidos();
},


$(document).ready(function() {
    carregarAtendidosDisponiveis();
    recarregarListaAtendidos();
}))