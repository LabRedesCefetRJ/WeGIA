// ================== DATATABLES ==================

var tabelaAtendidos;
var todosAtendidosData = [];

// ================== FUNÇÕES ==================

function carregarStatusAtendidos() {
    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: { metodo: 'listarStatusAtendidosAjax', nomeClasse: 'ProjetoControle' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                var select = $('#filtro_status_atendido');
                select.empty();
                $.each(response.data, function(i, status) {
                    select.append('<option value="' + status.id_status + '">' + escapeHtml(status.descricao) + '</option>');
                });
                select.append('<option value="">Todos</option>');
                select.val('1');
                aplicarFiltroAtendidos();
            } else {
                console.error('Erro ao carregar status:', response.message);
            }
        },
        error: function(xhr) { console.error('Erro ao carregar status:', xhr.responseText); }
    });
}

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

function recarregarListaAtendidos() {
    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: {
            metodo: 'listarAtendidosProjetoAjax',
            nomeClasse: 'ProjetoControle',
            projeto_id: $('#id_projeto').val()
        },
        dataType: 'json',
        success: function(response) {
            tabelaAtendidos.clear();
            todosAtendidosData = [];
            if (response.success && response.data) {
                todosAtendidosData = response.data;
                aplicarFiltroAtendidos();
            } else {
                tabelaAtendidos.draw();
            }
        },
        error: function(xhr) { console.error('Erro ao recarregar atendidos:', xhr.responseText); }
    });
}

function filtrarAtendidosPorStatus() {
    aplicarFiltroAtendidos();
}

function aplicarFiltroAtendidos() {
    const statusFiltro = $('#filtro_status_atendido').val();
    tabelaAtendidos.clear();
    let dadosFiltrados = todosAtendidosData;
    if (statusFiltro !== '') {
        dadosFiltrados = todosAtendidosData.filter(function(atendido) {
            return String(atendido.id_status) === String(statusFiltro);
        });
    }
    $.each(dadosFiltrados, function(i, atendido) {
        const nomeCompleto = ((atendido.nome || '') + ' ' + (atendido.sobrenome || '')).trim();
        const cpf          = atendido.cpf || '--';
        const status       = atendido.status_descricao || '--';
        const acoes        =
            '<button type="button"' +
            ' onclick="event.stopPropagation(); removerAtendidoProjeto(' + atendido.id + ')"' +
            ' class="btn btn-danger btn-xs" title="Remover do projeto">' +
            '<i class="fa fa-trash"></i></button>';

        tabelaAtendidos.row.add([
            escapeHtml(nomeCompleto),
            escapeHtml(cpf),
            escapeHtml(status),
            acoes
        ]).nodes().to$().attr('id', 'atendido-' + atendido.id)
                        .css('cursor', 'pointer')
                        .on('click', (function(id) {
                            return function() {
                                window.location.href = 'editar_atendido_projeto.php?id=' + id;
                            };
                        })(atendido.id));
    });
    tabelaAtendidos.draw();
}

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
    tabelaAtendidos = $('#atendidos-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/2.0.2/i18n/pt-BR.json'
        },
        columnDefs: [{ orderable: false, targets: -1 }],
        pageLength: 10
    });

    carregarStatusAtendidos();
    carregarAtendidosDisponiveis();
    recarregarListaAtendidos();
});