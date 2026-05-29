const EQUIPE_POR_PAGINA = 10;
let equipeDados = [];
let equipePaginaAtual = 1;

function adicionarNovaFuncao() {
    var novaFuncao = window.prompt("Cadastre uma nova função/cargo para o projeto:");
    if (!novaFuncao) return;
    novaFuncao = novaFuncao.trim();
    if (novaFuncao === '') {
        alert("O nome da função não pode estar vazio.");
        return;
    }

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'adicionarFuncaoProjeto',
            nomeClasse: 'ProjetoControle',
            descricao: novaFuncao,
            csrf_token: $('#csrf_token').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                carregarFuncoes();
                alert('Função cadastrada com sucesso!');
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

function carregarFuncoes() {
    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: {
            metodo: 'listarFuncoesAjax',
            nomeClasse: 'ProjetoControle'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                var select = $('#nova_funcao');
                select.empty();
                select.append('<option selected disabled>Selecionar Função</option>');
                $.each(response.data, function(index, funcao) {
                    select.append('<option value="' + funcao.id_funcao + '">' + escapeHtml(funcao.descricao) + '</option>');
                });
            }
        },
        error: function(xhr) {
            console.error('Erro ao carregar funções:', xhr.responseText);
        }
    });
}

function adicionarMembroEquipe() {
    const executanteId = $('#novo_funcionario').val();
    const funcaoId     = $('#nova_funcao').val();
    const projetoId    = $('#id_projeto').val();
    const csrfToken    = $('#csrf_token').val();

    if (!executanteId) {
        alert('Selecione um executante.');
        return;
    }

    if (!funcaoId) {
        alert('Selecione uma função/cargo.');
        return;
    }

    const $btn = $('#btn-adicionar-membro');
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Adicionando...');

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'adicionarMembroEquipe',
            nomeClasse: 'ProjetoControle',
            projeto_id: projetoId,
            funcionario_id: executanteId,
            funcao_id: funcaoId,
            csrf_token: csrfToken
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#novo_funcionario').prop('selectedIndex', 0);
                $('#nova_funcao').prop('selectedIndex', 0);
                recarregarListaEquipe();
            } else {
                alert('Erro: ' + (response.message || 'Tente novamente.'));
            }
            $btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Adicionar');
        },
        error: function(xhr) {
            alert('Erro ao conectar com o servidor.');
            console.error(xhr.responseText);
            $btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Adicionar');
        }
    });
}

function removerMembroEquipe(id) {
    if (!confirm('Tem certeza que deseja remover este membro da equipe?')) return;

    const projetoId    = $('#id_projeto').val();
    const csrfToken    = $('#csrf_token').val();
    const $btn         = $('#btn-remover-' + id);
    const originalHtml = $btn.html();
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'removerMembroEquipe',
            nomeClasse: 'ProjetoControle',
            id: id,
            projeto_id: projetoId,
            csrf_token: csrfToken
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                recarregarListaEquipe();
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

function recarregarListaEquipe() {
    const projetoId = $('#id_projeto').val();

    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: {
            metodo: 'listarEquipeAjax',
            nomeClasse: 'ProjetoControle',
            projeto_id: projetoId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                equipeDados = response.data;
                equipePaginaAtual = 1;
                renderizarPaginaEquipe();
            } else {
                console.error('Erro ao recarregar lista:', response.message);
            }
        },
        error: function(xhr) {
            console.error('Erro ao recarregar lista:', xhr.responseText);
        }
    });
}

function renderizarPaginaEquipe() {
    const inicio      = (equipePaginaAtual - 1) * EQUIPE_POR_PAGINA;
    const fim         = inicio + EQUIPE_POR_PAGINA;
    const paginaAtual = equipeDados.slice(inicio, fim);
    const totalPaginas = Math.ceil(equipeDados.length / EQUIPE_POR_PAGINA);

    atualizarTabelaEquipe(paginaAtual);
    renderizarPaginacaoEquipe(totalPaginas);
}

function atualizarTabelaEquipe(dados) {
    const $tbody = $('#equipe-tab');
    $tbody.empty();

    if (!dados || dados.length === 0) {
        $tbody.html('<tr><td colspan="4" class="text-center">Nenhum membro cadastrado nesta equipe.</td></tr>');
        return;
    }

    dados.forEach(function(membro) {
        const nomeCompleto = ((membro.nome || '') + ' ' + (membro.sobrenome || '')).trim();
        const cpf          = membro.cpf || '--';
        const funcao       = membro.funcao_descricao || '--';

        $tbody.append(
            '<tr id="equipe-' + membro.id + '">' +
            '<td>' + escapeHtml(nomeCompleto) + '</td>' +
            '<td>' + escapeHtml(cpf) + '</td>' +
            '<td>' + escapeHtml(funcao) + '</td>' +
            '<td class="actions text-center">' +
              '<button type="button" onclick="removerMembroEquipe(' + membro.id + ')" id="btn-remover-' + membro.id + '" class="btn btn-danger btn-xs" title="Remover">' +
                '<i class="fa fa-trash"></i>' +
              '</button>' +
            '</td>' +
            '</tr>'
        );
    });
}

function renderizarPaginacaoEquipe(totalPaginas) {
    const $paginacao = $('#equipe-paginacao');
    $paginacao.empty();

    if (totalPaginas <= 1) return;

    $paginacao.append(
        '<li class="' + (equipePaginaAtual === 1 ? 'disabled' : '') + '">' +
        '<a href="#" onclick="mudarPaginaEquipe(' + (equipePaginaAtual - 1) + '); return false;">&laquo;</a></li>'
    );

    for (var i = 1; i <= totalPaginas; i++) {
        $paginacao.append(
            '<li class="' + (i === equipePaginaAtual ? 'active' : '') + '">' +
            '<a href="#" onclick="mudarPaginaEquipe(' + i + '); return false;">' + i + '</a></li>'
        );
    }

    $paginacao.append(
        '<li class="' + (equipePaginaAtual === totalPaginas ? 'disabled' : '') + '">' +
        '<a href="#" onclick="mudarPaginaEquipe(' + (equipePaginaAtual + 1) + '); return false;">&raquo;</a></li>'
    );
}

function mudarPaginaEquipe(pagina) {
    const totalPaginas = Math.ceil(equipeDados.length / EQUIPE_POR_PAGINA);
    if (pagina < 1 || pagina > totalPaginas) return;
    equipePaginaAtual = pagina;
    renderizarPaginaEquipe();
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
    carregarFuncoes();
    recarregarListaEquipe();
});