const ATENDIDOS_POR_PAGINA = 10;
let atendidosDados = [];
let atendidosPaginaAtual = 1;

function adicionarNovoStatusAtendido() {
    var novoStatus = window.prompt("Cadastre um novo status para atendido no projeto:");
    if (!novoStatus) return;
    novoStatus = novoStatus.trim();
    if (novoStatus === '') {
        alert("O status não pode estar vazio.");
        return;
    }

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
        data: {
            metodo: 'listarAtendidosAjax',
            nomeClasse: 'ProjetoControle'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                var select = $('#novo_atendido');
                select.empty();
                select.append('<option selected disabled>Selecionar Atendido</option>');

                $.each(response.data, function(index, atendido) {
                    var nome         = (atendido.nome || '').trim();
                    var sobrenome    = (atendido.sobrenome || '').trim();
                    var nomeCompleto = sobrenome ? nome + ' ' + sobrenome : nome;

                    select.append(
                        '<option value="' + atendido.idatendido + '">' +
                        escapeHtml(nomeCompleto) +
                        '</option>'
                    );
                });
            } else {
                console.error('Erro ao carregar atendidos:', response.message);
            }
        },
        error: function(xhr) {
            console.error('Erro ao carregar atendidos disponíveis:', xhr.responseText);
        }
    });
}

function carregarStatusAtendidos() {
    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: {
            metodo: 'listarStatusAtendidosAjax',
            nomeClasse: 'ProjetoControle'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                var select = $('#status_atendido');
                select.empty();
                select.append('<option selected disabled>Selecionar Status</option>');
                $.each(response.data, function(index, status) {
                    select.append('<option value="' + status.id_status + '">' + escapeHtml(status.descricao) + '</option>');
                });
            }
        },
        error: function(xhr) {
            console.error('Erro ao carregar status:', xhr.responseText);
        }
    });
}

function adicionarAtendidoProjeto() {
    const atendidoId = $('#novo_atendido').val();
    const statusId   = $('#status_atendido').val();
    const projetoId  = $('#id_projeto').val();
    const csrfToken  = $('#csrf_token').val();

    if (!atendidoId) {
        alert('Selecione um atendido.');
        return;
    }

    if (!statusId) {
        alert('Selecione um status.');
        return;
    }

    const $btn = $('#btn-adicionar-atendido');
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Vinculando...');

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
            $btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Vincular');
        },
        error: function(xhr) {
            alert('Erro ao conectar com o servidor.');
            console.error(xhr.responseText);
            $btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Vincular');
        }
    });
}

function removerAtendidoProjeto(id) {
    if (!confirm('Tem certeza que deseja remover este atendido do projeto?')) return;

    const projetoId = $('#id_projeto').val();
    const csrfToken = $('#csrf_token').val();

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'removerAtendidoProjeto',
            nomeClasse: 'ProjetoControle',
            id: id,
            projeto_id: projetoId,
            csrf_token: csrfToken
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

    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: {
            metodo: 'listarAtendidosProjetoAjax',
            nomeClasse: 'ProjetoControle',
            projeto_id: projetoId
        },
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
}

function renderizarPaginaAtendidos() {
    const inicio       = (atendidosPaginaAtual - 1) * ATENDIDOS_POR_PAGINA;
    const fim          = inicio + ATENDIDOS_POR_PAGINA;
    const paginaAtual  = atendidosDados.slice(inicio, fim);
    const totalPaginas = Math.ceil(atendidosDados.length / ATENDIDOS_POR_PAGINA);

    atualizarTabelaAtendidos(paginaAtual);
    renderizarPaginacaoAtendidos(totalPaginas);
}

function atualizarTabelaAtendidos(dados) {
    const $tbody = $('#atendidos-tab');
    $tbody.empty();

    if (!dados || dados.length === 0) {
        $tbody.html('<tr><td colspan="4" class="text-center">Nenhum atendido vinculado a este projeto.</td></tr>');
        return;
    }

    dados.forEach(function(atendido) {
        const nomeCompleto = ((atendido.nome || '') + ' ' + (atendido.sobrenome || '')).trim();
        const cpf          = atendido.cpf ? escapeHtml(atendido.cpf) : '--';
        const status       = atendido.status_descricao || '--';

        $tbody.append(
            '<tr id="atendido-' + atendido.id + '">' +
            '<td>' + escapeHtml(nomeCompleto) + '</td>' +
            '<td>' + cpf + '</td>' +
            '<td>' + escapeHtml(status) + '</td>' +
            '<td class="actions text-center">' +
              '<button type="button" onclick="removerAtendidoProjeto(' + atendido.id + ')" class="btn btn-danger btn-xs" title="Remover">' +
                '<i class="fa fa-trash"></i>' +
              '</button>' +
            '</td>' +
            '</tr>'
        );
    });
}

function renderizarPaginacaoAtendidos(totalPaginas) {
    const $paginacao = $('#atendidos-paginacao');
    $paginacao.empty();

    if (totalPaginas <= 1) return;

    $paginacao.append(
        '<li class="' + (atendidosPaginaAtual === 1 ? 'disabled' : '') + '">' +
        '<a href="#" onclick="mudarPaginaAtendidos(' + (atendidosPaginaAtual - 1) + '); return false;">&laquo;</a></li>'
    );

    for (var i = 1; i <= totalPaginas; i++) {
        $paginacao.append(
            '<li class="' + (i === atendidosPaginaAtual ? 'active' : '') + '">' +
            '<a href="#" onclick="mudarPaginaAtendidos(' + i + '); return false;">' + i + '</a></li>'
        );
    }

    $paginacao.append(
        '<li class="' + (atendidosPaginaAtual === totalPaginas ? 'disabled' : '') + '">' +
        '<a href="#" onclick="mudarPaginaAtendidos(' + (atendidosPaginaAtual + 1) + '); return false;">&raquo;</a></li>'
    );
}

function mudarPaginaAtendidos(pagina) {
    const totalPaginas = Math.ceil(atendidosDados.length / ATENDIDOS_POR_PAGINA);
    if (pagina < 1 || pagina > totalPaginas) return;
    atendidosPaginaAtual = pagina;
    renderizarPaginaAtendidos();
}

$(document).ready(function() {
    carregarAtendidosDisponiveis();
    carregarStatusAtendidos();
    recarregarListaAtendidos();
});