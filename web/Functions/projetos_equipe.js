const EQUIPE_POR_PAGINA = 10;
let equipeDados = [];
let equipePaginaAtual = 1;
let equipeTurmasFiltro = []; // array de IDs — interseção

// ================== FUNÇÕES ==================

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
        data: { metodo: 'listarFuncoesAjax', nomeClasse: 'ProjetoControle' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                var select = $('#nova_funcao');
                select.empty().append('<option selected disabled>Selecionar Função</option>');
                $.each(response.data, function(i, funcao) {
                    select.append('<option value="' + funcao.id_funcao + '">' + escapeHtml(funcao.descricao) + '</option>');
                });
            }
        },
        error: function(xhr) { console.error('Erro ao carregar funções:', xhr.responseText); }
    });
}

function adicionarMembroEquipe() {
    const executanteId = $('#novo_funcionario').val();
    const funcaoId     = $('#nova_funcao').val();
    const projetoId    = $('#id_projeto').val();
    const csrfToken    = $('#csrf_token').val();

    if (!executanteId) { alert('Selecione um executante.'); return; }
    if (!funcaoId)     { alert('Selecione uma função/cargo.'); return; }

    const $btn = $('#btn-adicionar-membro');
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

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
            $btn.prop('disabled', false).html('<i class="fa fa-plus"></i>');
        },
        error: function(xhr) {
            alert('Erro ao conectar com o servidor.');
            console.error(xhr.responseText);
            $btn.prop('disabled', false).html('<i class="fa fa-plus"></i>');
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

function confirmarAdicionarExecutanteTurma() {
    const id_pessoa = $('#select-executante-turma').val();
    if (!id_pessoa) { alert('Selecione um executante.'); return; }
    adicionarExecutanteNaTurma(id_pessoa);
}

function adicionarExecutanteNaTurma(id_pessoa) {
    // Adiciona à última turma do filtro (a mais recente selecionada)
    const id_turma = equipeTurmasFiltro[equipeTurmasFiltro.length - 1];
    if (!id_turma) { alert('Selecione uma turma antes de adicionar.'); return; }

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'adicionarExecutanteTurma',
            nomeClasse: 'ProjetoControle',
            id_turma: id_turma,
            id_pessoa: id_pessoa,
            csrf_token: $('#csrf_token').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                recarregarListaEquipe();
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

function removerExecutanteDaTurma(id_pessoa) {
    if (!confirm('Remover este executante da turma?')) return;

    const id_turma = equipeTurmasFiltro[equipeTurmasFiltro.length - 1];

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'removerExecutanteTurma',
            nomeClasse: 'ProjetoControle',
            id_turma: id_turma,
            id_pessoa: id_pessoa,
            csrf_token: $('#csrf_token').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                recarregarListaEquipe();
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

function recarregarListaEquipe() {
    const projetoId = $('#id_projeto').val();
    const params = {
        metodo: 'listarEquipeAjax',
        nomeClasse: 'ProjetoControle',
        projeto_id: projetoId
    };

    if (equipeTurmasFiltro.length > 0) {
        params['turma_ids[]'] = equipeTurmasFiltro;
    }

    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: params,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                equipeDados = response.data;
                equipePaginaAtual = 1;
                renderizarPaginaEquipe();
                carregarSelectExecutantesTurma();
            } else {
                console.error('Erro ao recarregar lista:', response.message);
            }
        },
        error: function(xhr) { console.error('Erro ao recarregar lista:', xhr.responseText); }
    });
}

// ================== PAGINAÇÃO ==================

function renderizarPaginaEquipe() {
    const inicio       = (equipePaginaAtual - 1) * EQUIPE_POR_PAGINA;
    const fim          = inicio + EQUIPE_POR_PAGINA;
    const totalPaginas = Math.ceil(equipeDados.length / EQUIPE_POR_PAGINA);
    atualizarTabelaEquipe(equipeDados.slice(inicio, fim));
    renderizarPaginacaoEquipe(totalPaginas);
}

function atualizarTabelaEquipe(dados) {
    const $tbody = $('#equipe-tab');
    $tbody.empty();

    if (!dados || dados.length === 0) {
        $tbody.html('<tr><td colspan="4" class="text-center">Nenhum membro encontrado.</td></tr>');
        return;
    }

    dados.forEach(function(membro) {
        const nomeCompleto = ((membro.nome || '') + ' ' + (membro.sobrenome || '')).trim();
        const cpf          = membro.cpf || '--';
        const funcao       = membro.funcao_descricao || '--';

        const acoes = '<button type="button" onclick="event.stopPropagation(); removerMembroEquipe(' + membro.id + ')" ' +
                    'id="btn-remover-' + membro.id + '" class="btn btn-danger btn-xs" title="Remover do projeto">' +
                    '<i class="fa fa-trash"></i></button>';

        $('<tr>')
            .attr('id', 'equipe-' + membro.id)
            .css('cursor', 'pointer')
            .on('click', function() {
                window.location.href = 'editar_executante_projeto.php?id=' + membro.id;
            })
            .append($('<td>').text(nomeCompleto))
            .append($('<td>').text(cpf))
            .append($('<td>').text(funcao))
            .append($('<td class="actions text-center">').html(acoes))
            .appendTo($tbody);
    });
}

function renderizarPaginacaoEquipe(totalPaginas) {
    const $p = $('#equipe-paginacao');
    $p.empty();
    if (totalPaginas <= 1) return;

    $p.append('<li class="' + (equipePaginaAtual === 1 ? 'disabled' : '') + '">' +
        '<a href="#" onclick="mudarPaginaEquipe(' + (equipePaginaAtual - 1) + '); return false;">&laquo;</a></li>');

    for (var i = 1; i <= totalPaginas; i++) {
        $p.append('<li class="' + (i === equipePaginaAtual ? 'active' : '') + '">' +
            '<a href="#" onclick="mudarPaginaEquipe(' + i + '); return false;">' + i + '</a></li>');
    }

    $p.append('<li class="' + (equipePaginaAtual === totalPaginas ? 'disabled' : '') + '">' +
        '<a href="#" onclick="mudarPaginaEquipe(' + (equipePaginaAtual + 1) + '); return false;">&raquo;</a></li>');
}

function mudarPaginaEquipe(pagina) {
    const totalPaginas = Math.ceil(equipeDados.length / EQUIPE_POR_PAGINA);
    if (pagina < 1 || pagina > totalPaginas) return;
    equipePaginaAtual = pagina;
    renderizarPaginaEquipe();
}

// ================== FILTRO POR TURMAS (SELECTS ENCADEADOS) ==================

function renderizarSelectsFiltroEquipe() {
    const $container = $('#turmas-equipe-filtro');
    $container.empty();

    // Renderiza um select para cada posição (filtros já escolhidos + 1 novo vazio)
    const slots = equipeTurmasFiltro.length + 1;

    for (var i = 0; i < slots; i++) {
        (function(idx) {
            const valorAtual = equipeTurmasFiltro[idx] || '';
            const $select = $('<select class="form-control input-sm filtro-turma-select" ' +
                'style="display:inline-block;width:auto;min-width:160px;margin-right:6px;"></select>');

            $select.append('<option value="">— Turma —</option>');
            turmasDados.forEach(function(t) {
                const sel = (String(t.id_turma) === String(valorAtual)) ? ' selected' : '';
                $select.append('<option value="' + t.id_turma + '"' + sel + '>' + escapeHtml(t.nome) + '</option>');
            });

            $select.on('change', function() {
                const val = parseInt($(this).val(), 10);
                // Trunca o array até a posição atual e seta o novo valor
                equipeTurmasFiltro = equipeTurmasFiltro.slice(0, idx);
                if (val) equipeTurmasFiltro.push(val);
                renderizarSelectsFiltroEquipe();
                recarregarListaEquipe();
            });

            $container.append($select);
        })(i);
    }

    // Botão limpar — só aparece se houver algum filtro ativo
    const $limpar = $('#btn-limpar-turma-equipe');
    $limpar.toggle(equipeTurmasFiltro.length > 0);
}

function limparFiltroEquipe() {
    equipeTurmasFiltro = [];
    renderizarSelectsFiltroEquipe();
    recarregarListaEquipe();
}

function carregarSelectExecutantesTurma() {
    const $painel = $('#painel-adicionar-equipe-turma');

    if (equipeTurmasFiltro.length === 0) {
        $painel.hide();
        return;
    }

    // "fora da turma" usa a última turma do filtro como referência
    const id_turma  = equipeTurmasFiltro[equipeTurmasFiltro.length - 1];
    const projetoId = $('#id_projeto').val();

    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: {
            metodo: 'listarExecutantesForaDaTurmaAjax',
            nomeClasse: 'ProjetoControle',
            projeto_id: projetoId,
            turma_id: id_turma
        },
        dataType: 'json',
        success: function(response) {
            const $select = $('#select-executante-turma');
            $select.empty().append('<option selected disabled>Selecionar executante</option>');

            if (response.success && response.data && response.data.length > 0) {
                $.each(response.data, function(i, m) {
                    const nome = escapeHtml(((m.nome || '') + ' ' + (m.sobrenome || '')).trim());
                    $select.append('<option value="' + m.id_pessoa + '">' + nome + '</option>');
                });
                $painel.show();
            } else {
                $painel.hide();
            }
        },
        error: function(xhr) { console.error('Erro ao carregar executantes para turma:', xhr.responseText); }
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
    carregarFuncoes();
    recarregarListaEquipe();
});