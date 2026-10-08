// ================== DATATABLES ==================

var tabelaEquipe;
var todosEquipeData = [];

// ================== FUNÇÕES ==================

function carregarFuncoesFiltro() {
    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: { metodo: 'listarFuncoesAjax', nomeClasse: 'ProjetoControle' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                var select = $('#filtro_funcao_equipe');
                select.empty().append('<option value="">Todos os Cargos</option>');
                $.each(response.data, function(i, funcao) {
                    select.append('<option value="' + funcao.id_funcao + '">' + escapeHtml(funcao.descricao) + '</option>');
                });
                // Aplica o filtro inicial (todos)
                aplicarFiltroEquipe();
            } else {
                console.error('Erro ao carregar funções para filtro:', response.message);
            }
        },
        error: function(xhr) { console.error('Erro ao carregar funções:', xhr.responseText); }
    });
}

function carregarFuncoesSelect() {
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
                carregarFuncoesSelect();
                carregarFuncoesFiltro();
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
                $('#novo_funcionario').select2('data', null);
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

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'removerMembroEquipe',
            nomeClasse: 'ProjetoControle',
            id: id,
            projeto_id: $('#id_projeto').val(),
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

    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: params,
        dataType: 'json',
        success: function(response) {
            tabelaEquipe.clear();
            todosEquipeData = [];
            if (response.success && response.data) {
                todosEquipeData = response.data;
                aplicarFiltroEquipe();
            } else {
                tabelaEquipe.draw();
            }
        },
        error: function(xhr) { console.error('Erro ao recarregar lista:', xhr.responseText); }
    });
}

function filtrarEquipePorFuncao() {
    aplicarFiltroEquipe();
}

function aplicarFiltroEquipe() {
    const funcaoFiltro = $('#filtro_funcao_equipe').val();
    tabelaEquipe.clear();
    let dadosFiltrados = todosEquipeData;
    
    if (funcaoFiltro !== '') {
        dadosFiltrados = todosEquipeData.filter(function(membro) {
            return String(membro.id_funcao) === String(funcaoFiltro);
        });
    }

    $.each(dadosFiltrados, function(i, membro) {
        const nomeCompleto = ((membro.nome || '') + ' ' + (membro.sobrenome || '')).trim();
        const cpf          = membro.cpf || '--';
        const funcao       = membro.funcao_descricao || '--';
        const acoes        =
            '<button type="button"' +
            ' onclick="event.stopPropagation(); removerMembroEquipe(' + membro.id + ')"' +
            ' id="btn-remover-' + membro.id + '"' +
            ' class="btn btn-danger btn-xs" title="Remover do projeto">' +
            '<i class="fa fa-trash"></i></button>';

        tabelaEquipe.row.add([
            escapeHtml(nomeCompleto),
            escapeHtml(cpf),
            escapeHtml(funcao),
            acoes
        ]).nodes().to$().attr('id', 'equipe-' + membro.id)
                        .css('cursor', 'pointer')
                        .on('click', (function(id) {
                            return function() {
                                window.location.href = 'editar_executante_projeto.php?id=' + id;
                            };
                        })(membro.id));
    });
    tabelaEquipe.draw();
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
    tabelaEquipe = $('#equipe-table').DataTable({
        language: {
            url: '../../assets/vendor/jquery-datatables/i18n/pt-BR.json'
        },
        columnDefs: [{ orderable: false, targets: -1 }],
        pageLength: 10
    });

    // Select2 v3.4.6 - sintaxe legada (ajax.data/results, não data/processResults)
    $('#novo_funcionario').select2({
        placeholder: 'Digite o nome do executante...',
        minimumInputLength: 2,
        allowClear: true,
        ajax: {
            url: '../../controle/control.php',
            dataType: 'json',
            quietMillis: 300, // debounce nativo do select2 v3
            data: function(term) {
                return {
                    metodo: 'listarFuncionariosAtivosAjax',
                    nomeClasse: 'ProjetoControle',
                    termo: term
                };
            },
            results: function(response) {
                if (!response.success) return { results: [] };
                return {
                    results: $.map(response.data, function(item) {
                        return {
                            id: item.id_pessoa,
                            text: ((item.nome || '') + ' ' + (item.sobrenome || '')).trim()
                        };
                    })
                };
            }
        }
    });

    carregarFuncoesSelect();
    carregarFuncoesFiltro();
    recarregarListaEquipe();
});