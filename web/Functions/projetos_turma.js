// ================== DATATABLES ==================

var tabelaTurmas;

// ================== FUNÇÕES ==================

function adicionarTurma() {
    const nome      = $('#nova_turma_nome').val().trim();
    const descricao = $('#nova_turma_descricao').val().trim();
    const projetoId = $('#id_projeto').val();
    const csrfToken = $('#csrf_token').val();

    if (!nome) { alert('Informe o nome da turma.'); return; }

    const $btn = $('#btn-adicionar-turma');
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'adicionarTurma',
            nomeClasse: 'ProjetoControle',
            projeto_id: projetoId,
            nome: nome,
            descricao: descricao,
            csrf_token: csrfToken
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#nova_turma_nome').val('');
                $('#nova_turma_descricao').val('');
                recarregarListaTurmas();
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

function removerTurma(id_turma) {
    if (!confirm('Tem certeza que deseja remover esta turma?')) return;

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'removerTurma',
            nomeClasse: 'ProjetoControle',
            id_turma: id_turma,
            projeto_id: $('#id_projeto').val(),
            csrf_token: $('#csrf_token').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                recarregarListaTurmas();
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

function recarregarListaTurmas() {
    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: {
            metodo: 'listarTurmasAjax',
            nomeClasse: 'ProjetoControle',
            projeto_id: $('#id_projeto').val()
        },
        dataType: 'json',
        success: function(response) {
            tabelaTurmas.clear();
            if (response.success && response.data) {
                $.each(response.data, function(i, turma) {
                    const descricao = turma.descricao || '--';
                    const acoes     =
                        '<button type="button"' +
                        ' onclick="event.stopPropagation(); removerTurma(' + turma.id_turma + ')"' +
                        ' id="btn-remover-turma-' + turma.id_turma + '"' +
                        ' class="btn btn-danger btn-xs" title="Remover turma">' +
                        '<i class="fa fa-trash"></i></button>';

                    tabelaTurmas.row.add([
                        escapeHtml(turma.nome),
                        escapeHtml(descricao),
                        acoes
                    ]).nodes().to$().attr('id', 'turma-' + turma.id_turma)
                                    .css('cursor', 'pointer')
                                    .on('click', (function(id) {
                                        return function() {
                                            window.location.href = 'editar_turma_projeto.php?id_turma=' + id;
                                        };
                                    })(turma.id_turma));
                });
            }
            tabelaTurmas.draw();
        },
        error: function(xhr) { console.error('Erro ao recarregar turmas:', xhr.responseText); }
    });
}

$(document).ready(function() {
    tabelaTurmas = $('#turmas-table').DataTable({
        language: {
            url: '../../assets/vendor/jquery-datatables/i18n/pt-BR.json'
        },
        columnDefs: [{ orderable: false, targets: -1 }],
        pageLength: 10
    });

    recarregarListaTurmas();
});