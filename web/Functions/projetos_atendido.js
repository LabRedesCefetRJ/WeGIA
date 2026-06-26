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
                $('#novo_atendido').select2('data', null);
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
            url: '../../assets/vendor/jquery-datatables/i18n/pt-BR.json'
        },
        columnDefs: [{ orderable: false, targets: -1 }],
        pageLength: 10
    });

    // Select2 v3.4.6 - sintaxe legada (ajax.data/results, não data/processResults)
    $('#novo_atendido').select2({
        placeholder: 'Digite o nome do atendido...',
        minimumInputLength: 2,
        allowClear: true,
        ajax: {
            url: '../../controle/control.php',
            dataType: 'json',
            quietMillis: 300, // debounce nativo do select2 v3
            data: function(term) {
                return {
                    metodo: 'listarAtendidosAjax',
                    nomeClasse: 'ProjetoControle',
                    termo: term
                };
            },
            results: function(response) {
                if (!response.success) return { results: [] };
                return {
                    results: $.map(response.data, function(item) {
                        return {
                            id: item.idatendido,
                            text: ((item.nome || '') + ' ' + (item.sobrenome || '')).trim()
                        };
                    })
                };
            }
        }
    });

    carregarStatusAtendidos();
    recarregarListaAtendidos();
});