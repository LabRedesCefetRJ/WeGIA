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
    const funcionarioId = $('#novo_funcionario').val();
    const funcaoId      = $('#nova_funcao').val();
    const projetoId     = $('#id_projeto').val();
    const csrfToken     = $('#csrf_token').val();

    if (!funcionarioId || funcionarioId === 'Selecionar Funcionário') {
        alert('Selecione um funcionário.');
        return;
    }

    if (!funcaoId || funcaoId === 'Selecionar Função') {
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
            funcionario_id: funcionarioId,
            funcao_id: funcaoId,
            csrf_token: csrfToken
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#novo_funcionario').val($('#novo_funcionario option:first').val());
                $('#nova_funcao').val($('#nova_funcao option:first').val());
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

    const projetoId = $('#id_projeto').val();
    const csrfToken = $('#csrf_token').val();
    const $btn      = $(`#btn-remover-${id}`);
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
                atualizarTabelaEquipe(response.data);
            } else {
                console.error('Erro ao recarregar lista:', response.message);
            }
        },
        error: function(xhr) {
            console.error('Erro ao recarregar lista:', xhr.responseText);
        }
    });
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

        const linha = `
            <tr id="equipe-${membro.id}">
                <td>${escapeHtml(nomeCompleto)}</td>
                <td>${escapeHtml(cpf)}</td>
                <td>${escapeHtml(funcao)}</td>
                <td class="actions text-center">
                    <button type="button" onclick="removerMembroEquipe(${membro.id})" id="btn-remover-${membro.id}" class="btn btn-danger btn-xs" title="Remover">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        $tbody.append(linha);
    });
}

function carregarAtendidosProjeto() {
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
                atualizarTabelaAtendidos(response.data);
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
                    // Identificação apenas por nome — sem dependência de CPF
                    var nome        = (atendido.nome || '').trim();
                    var sobrenome   = (atendido.sobrenome || '').trim();
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

    if (!atendidoId || atendidoId === 'Selecionar Atendido') {
        alert('Selecione um atendido.');
        return;
    }

    if (!statusId || statusId === 'Selecionar Status') {
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
                $('#novo_atendido').val($('#novo_atendido option:first').val());
                $('#status_atendido').val($('#status_atendido option:first').val());
                carregarAtendidosProjeto();
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
                carregarAtendidosProjeto();
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

        const linha = `
            <tr id="atendido-${atendido.id}">
                <td>${escapeHtml(nomeCompleto)}</td>
                <td>${cpf}</td>
                <td>${escapeHtml(status)}</td>
                <td class="actions text-center">
                    <button type="button" onclick="removerAtendidoProjeto(${atendido.id})" class="btn btn-danger btn-xs" title="Remover">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        $tbody.append(linha);
    });
}

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

function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

$(document).ready(function() {
    carregarFuncoes();
    carregarAtendidosDisponiveis();
    carregarStatusAtendidos();
    carregarAtendidosProjeto();
});