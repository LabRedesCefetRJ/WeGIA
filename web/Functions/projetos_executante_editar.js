// Função para adicionar nova função (chamada pelo ícone +)
function adicionarNovaFuncaoExecutante() {
    var novaFuncao = window.prompt("Cadastre uma nova função/cargo:");
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
                carregarFuncoesExecutante();
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

// Função para recarregar o select de funções
function carregarFuncoesExecutante() {
    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: { metodo: 'listarFuncoesAjax', nomeClasse: 'ProjetoControle' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                var select = $('#id_funcao');
                var valorAtual = select.val();
                select.empty().append('<option value="" disabled>Selecionar Função</option>');
                $.each(response.data, function(i, funcao) {
                    var selected = (funcao.id_funcao == valorAtual) ? ' selected' : '';
                    select.append('<option value="' + funcao.id_funcao + '"' + selected + '>' + escapeHtml(funcao.descricao) + '</option>');
                });
            }
        },
        error: function(xhr) { console.error('Erro ao carregar funções:', xhr.responseText); }
    });
}

// Função para submeter a edição do executante
function submeterEdicaoExecutante() {
    const idFuncao  = $('#id_funcao').val();
    const idVinculo = $('#id_vinculo').val();
    const idProjeto = $('#id_projeto').val();
    const csrf      = $('#csrf_token').val();

    if (!idFuncao) { alert('Selecione uma função.'); return; }

    $('#btn-salvar').prop('disabled', true).text('Salvando...');

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'alterarFuncaoMembroEquipe',
            nomeClasse: 'ProjetoControle',
            id: idVinculo,
            projeto_id: idProjeto,
            id_funcao: idFuncao,
            csrf_token: csrf
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                window.location.href = 'editar_projeto.php?id_projeto=' + idProjeto + '&msg=Executante atualizado com sucesso!';
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

// Função para escape de HTML
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
        submeterEdicaoExecutante();
    });
});