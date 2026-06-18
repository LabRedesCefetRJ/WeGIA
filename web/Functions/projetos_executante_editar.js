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

$(document).ready(function() {
    $('#btn-salvar').on('click', function() {
        submeterEdicaoExecutante();
    });
});