// Função para adicionar novo status (chamada pelo ícone +)
function adicionarNovoStatusAtendido() {
    var novoStatus = window.prompt("Cadastre um novo status para atendidos:");
    if (!novoStatus) return;
    novoStatus = novoStatus.trim();
    if (novoStatus === '') {
        alert("O nome do status não pode estar vazio.");
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
                carregarStatusAtendido();
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

// Função para recarregar o select de status
function carregarStatusAtendido() {
    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: { metodo: 'listarStatusAtendidosAjax', nomeClasse: 'ProjetoControle' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                var select = $('#id_status');
                var valorAtual = select.val();
                select.empty().append('<option value="" disabled>Selecionar Status</option>');
                $.each(response.data, function(i, status) {
                    var selected = (status.id_status == valorAtual) ? ' selected' : '';
                    select.append('<option value="' + status.id_status + '"' + selected + '>' + escapeHtml(status.descricao) + '</option>');
                });
            }
        },
        error: function(xhr) { console.error('Erro ao carregar status:', xhr.responseText); }
    });
}

// Função para submeter a edição do atendido
function submeterEdicaoAtendido() {
    const idStatus  = $('#id_status').val();
    const idVinculo = $('#id_vinculo').val();
    const idProjeto = $('#id_projeto').val();
    const csrf      = $('#csrf_token').val();

    if (!idStatus) { alert('Selecione um status.'); return; }

    $('#btn-salvar').prop('disabled', true).text('Salvando...');

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'atualizarStatusAtendidoProjeto',
            nomeClasse: 'ProjetoControle',
            id: idVinculo,
            projeto_id: idProjeto,
            id_status: idStatus,
            csrf_token: csrf
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                window.location.href = 'editar_projeto.php?id_projeto=' + idProjeto + '&msg=Atendido atualizado com sucesso!';
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
        submeterEdicaoAtendido();
    });
});