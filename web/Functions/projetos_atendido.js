const ATENDIDOS_POR_PAGINA = 10;
let atendidosDados = [];
let atendidosPaginaAtual = 1;
let atendidosTurmasFiltro = []; // array de IDs — interseção

// ================== FUNÇÕES ==================

function adicionarNovoStatusAtendido() {
    var novoStatus = window.prompt("Cadastre um novo status para atendido no projeto:");
    if (!novoStatus) return;
    novoStatus = novoStatus.trim();
    if (novoStatus === '') { alert("O status não pode estar vazio."); return; }

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'adicionarStatusAtendidoProjeto',
            nomeClasse: 'ProjetoControle',
            descricao: novoStatus,
            csrf_token: $('#csrf_token').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                carregarStatusAtendidos();
                alert('Status cadastrado com sucesso!');
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

function carregarStatusAtendidos() {
    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: { metodo: 'listarStatusAtendidosAjax', nomeClasse: 'ProjetoControle' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                var select = $('#status_atendido');
                select.empty().append('<option selected disabled>Selecionar Status</option>');
                $.each(response.data, function(i, status) {
                    select.append('<option value="' + status.id_status + '">' + escapeHtml(status.descricao) + '</option>');
                });
            }
        },
        error: function(xhr) { console.error('Erro ao carregar status:', xhr.responseText); }
    });
}

function adicionarAtendidoProjeto() {
    const atendidoId = $('#novo_atendido').val();
    const statusId   = $('#status_atendido').val();
    const projetoId  = $('#id_projeto').val();
    const csrfToken  = $('#csrf_token').val();

    if (!atendidoId) { alert('Selecione um atendido.'); return; }
    if (!statusId)   { alert('Selecione um status.'); return; }

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
            status_id: statusId,
            csrf_token: csrfToken
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#novo_atendido').prop('selectedIndex', 0);
                $('#status_atendido').prop('selectedIndex', 0);
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

function confirmarAdicionarAtendidoTurma() {
    const id_atendido = $('#select-atendido-turma').val();
    if (!id_atendido) { alert('Selecione um atendido.'); return; }
    adicionarAtendidoNaTurma(id_atendido);
}

function adicionarAtendidoNaTurma(id_atendido) {
    const id_turma = atendidosTurmasFiltro[atendidosTurmasFiltro.length - 1];
    if (!id_turma) { alert('Selecione uma turma antes de adicionar.'); return; }

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'adicionarAtendidoTurma',
            nomeClasse: 'ProjetoControle',
            id_turma: id_turma,
            id_atendido: id_atendido,
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

function removerAtendidoDaTurma(id_atendido) {
    if (!confirm('Remover este atendido da turma?')) return;

    const id_turma = atendidosTurmasFiltro[atendidosTurmasFiltro.length - 1];

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'removerAtendidoTurma',
            nomeClasse: 'ProjetoControle',
            id_turma: id_turma,
            id_atendido: id_atendido,
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
    const projetoId = $('#id_projeto').val();
    const params = {
        metodo: 'listarAtendidosProjetoAjax',
        nomeClasse: 'ProjetoControle',
        projeto_id: projetoId
    };

    if (atendidosTurmasFiltro.length > 0) {
        params['turma_ids[]'] = atendidosTurmasFiltro;
    }

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
                carregarSelectAtendidosTurma();
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
}

// ================== PAGINAÇÃO ==================

function renderizarPaginaAtendidos() {
    const inicio       = (atendidosPaginaAtual - 1) * ATENDIDOS_POR_PAGINA;
    const fim          = inicio + ATENDIDOS_POR_PAGINA;
    const totalPaginas = Math.ceil(atendidosDados.length / ATENDIDOS_POR_PAGINA);
    atualizarTabelaAtendidos(atendidosDados.slice(inicio, fim));
    renderizarPaginacaoAtendidos(totalPaginas);
}

function atualizarTabelaAtendidos(dados) {
    const $tbody    = $('#atendidos-tab');
    const filtrando = atendidosTurmasFiltro.length > 0;
    $tbody.empty();

    if (!dados || dados.length === 0) {
        $tbody.html('<tr><td colspan="4" class="text-center">Nenhum atendido encontrado.</td></tr>');
        return;
    }

    dados.forEach(function(atendido) {
        const nomeCompleto = ((atendido.nome || '') + ' ' + (atendido.sobrenome || '')).trim();
        const cpf          = atendido.cpf ? escapeHtml(atendido.cpf) : '--';
        const status       = atendido.status_descricao || '--';

        let acoes = '<button type="button" onclick="removerAtendidoProjeto(' + atendido.id + ')" ' +
                    'class="btn btn-danger btn-xs" title="Remover do projeto">' +
                    '<i class="fa fa-trash"></i></button>';

        if (filtrando) {
            acoes += ' <button type="button" onclick="removerAtendidoDaTurma(' + atendido.id_atendido + ')" ' +
                     'class="btn btn-warning btn-xs" title="Remover da turma">' +
                     '<i class="fa fa-minus"></i></button>';
        }

        $tbody.append(
            '<tr id="atendido-' + atendido.id + '">' +
            '<td>' + escapeHtml(nomeCompleto) + '</td>' +
            '<td>' + cpf + '</td>' +
            '<td>' + escapeHtml(status) + '</td>' +
            '<td class="actions text-center">' + acoes + '</td>' +
            '</tr>'
        );
    });
}

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
}

function mudarPaginaAtendidos(pagina) {
    const totalPaginas = Math.ceil(atendidosDados.length / ATENDIDOS_POR_PAGINA);
    if (pagina < 1 || pagina > totalPaginas) return;
    atendidosPaginaAtual = pagina;
    renderizarPaginaAtendidos();
}

// ================== FILTRO POR TURMAS (SELECTS ENCADEADOS) ==================

function renderizarSelectsFiltroAtendidos() {
    const $container = $('#turmas-atendidos-filtro');
    $container.empty();

    const slots = atendidosTurmasFiltro.length + 1;

    for (var i = 0; i < slots; i++) {
        (function(idx) {
            const valorAtual = atendidosTurmasFiltro[idx] || '';
            const $select = $('<select class="form-control input-sm filtro-turma-select" ' +
                'style="display:inline-block;width:auto;min-width:160px;margin-right:6px;"></select>');

            $select.append('<option value="">— Turma —</option>');
            turmasDados.forEach(function(t) {
                const sel = (String(t.id_turma) === String(valorAtual)) ? ' selected' : '';
                $select.append('<option value="' + t.id_turma + '"' + sel + '>' + escapeHtml(t.nome) + '</option>');
            });

            $select.on('change', function() {
                const val = parseInt($(this).val(), 10);
                atendidosTurmasFiltro = atendidosTurmasFiltro.slice(0, idx);
                if (val) atendidosTurmasFiltro.push(val);
                renderizarSelectsFiltroAtendidos();
                recarregarListaAtendidos();
            });

            $container.append($select);
        })(i);
    }

    $('#btn-limpar-turma-atendidos').toggle(atendidosTurmasFiltro.length > 0);
}

function limparFiltroAtendidos() {
    atendidosTurmasFiltro = [];
    renderizarSelectsFiltroAtendidos();
    recarregarListaAtendidos();
}

function carregarSelectAtendidosTurma() {
    const $painel = $('#painel-adicionar-atendido-turma');

    if (atendidosTurmasFiltro.length === 0) {
        $painel.hide();
        return;
    }

    const id_turma  = atendidosTurmasFiltro[atendidosTurmasFiltro.length - 1];
    const projetoId = $('#id_projeto').val();

    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: {
            metodo: 'listarAtendidosForaDaTurmaAjax',
            nomeClasse: 'ProjetoControle',
            projeto_id: projetoId,
            turma_id: id_turma
        },
        dataType: 'json',
        success: function(response) {
            const $select = $('#select-atendido-turma');
            $select.empty().append('<option selected disabled>Selecionar atendido</option>');

            if (response.success && response.data && response.data.length > 0) {
                $.each(response.data, function(i, a) {
                    const nome = escapeHtml(((a.nome || '') + ' ' + (a.sobrenome || '')).trim());
                    $select.append('<option value="' + a.id_atendido + '">' + nome + '</option>');
                });
                $painel.show();
            } else {
                $painel.hide();
            }
        },
        error: function(xhr) { console.error('Erro ao carregar atendidos para turma:', xhr.responseText); }
    });
}

$(document).ready(function() {
    carregarAtendidosDisponiveis();
    carregarStatusAtendidos();
    recarregarListaAtendidos();
});