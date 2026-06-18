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

$(document).ready(function() {
    $('#btn-salvar').on('click', function() {
        submeterEdicaoAtendido();
    });
});