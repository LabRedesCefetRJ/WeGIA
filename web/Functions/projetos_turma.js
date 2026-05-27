// Gerencia o painel de turmas e propaga o filtro para equipe e atendidos

let turmasDados = [];

function carregarTurmas() {
    const projetoId = $('#id_projeto').val();

    $.ajax({
        url: '../../controle/control.php',
        type: 'GET',
        data: {
            metodo: 'listarTurmasAjax',
            nomeClasse: 'ProjetoControle',
            projeto_id: projetoId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                turmasDados = response.data || [];
                renderizarTurmas();
            } else {
                console.error('Erro ao carregar turmas:', response.message);
            }
        },
        error: function(xhr) {
            console.error('Erro ao carregar turmas:', xhr.responseText);
        }
    });
}

function renderizarTurmas() {
    const $lista = $('#turmas-lista');
    $lista.empty();

    if (!turmasDados || turmasDados.length === 0) {
        $lista.html('<p class="text-muted">Nenhuma turma cadastrada.</p>');
    } else {
        turmasDados.forEach(function(turma) {
            $lista.append(
                '<div class="label label-default" style="display:inline-flex;align-items:center;margin:3px;padding:6px 10px;font-size:13px;">' +
                escapeHtml(turma.nome) +
                ' <a onclick="removerTurma(' + turma.id_turma + ')" style="cursor:pointer;margin-left:8px;color:#fff;" title="Remover turma">' +
                '<i class="fa fa-times"></i></a>' +
                '</div>'
            );
        });
    }

    // Delega a renderização dos selects de filtro aos JS de equipe e atendidos
    if (typeof renderizarSelectsFiltroEquipe === 'function')   renderizarSelectsFiltroEquipe();
    if (typeof renderizarSelectsFiltroAtendidos === 'function') renderizarSelectsFiltroAtendidos();
}

function adicionarTurma() {
    var nome = window.prompt("Nome da nova turma:");
    if (!nome) return;
    nome = nome.trim();
    if (nome === '') {
        alert("O nome da turma não pode estar vazio.");
        return;
    }

    const projetoId = $('#id_projeto').val();
    const csrfToken = $('#csrf_token').val();

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'adicionarTurma',
            nomeClasse: 'ProjetoControle',
            projeto_id: projetoId,
            nome: nome,
            csrf_token: csrfToken
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                carregarTurmas();
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

function removerTurma(id_turma) {
    if (!confirm('Remover esta turma? Os vínculos de membros também serão removidos.')) return;

    const projetoId = $('#id_projeto').val();
    const csrfToken = $('#csrf_token').val();

    // Se a turma removida estava ativa como filtro, limpar
    if (typeof equipeTurmaAtiva !== 'undefined' && equipeTurmaAtiva == id_turma) {
        equipeTurmaAtiva = null;
        recarregarListaEquipe();
    }
    if (typeof atendidosTurmaAtiva !== 'undefined' && atendidosTurmaAtiva == id_turma) {
        atendidosTurmaAtiva = null;
        recarregarListaAtendidos();
    }

    $.ajax({
        url: '../../controle/control.php',
        type: 'POST',
        data: {
            metodo: 'removerTurma',
            nomeClasse: 'ProjetoControle',
            id_turma: id_turma,
            projeto_id: projetoId,
            csrf_token: csrfToken
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                carregarTurmas();
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

$(document).ready(function() {
    carregarTurmas();
});