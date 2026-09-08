function carregarTabelaTiposRegistros() {
    const status = $('#select_status').val();
    if (!status) {
        status = 1;
    }

    const url = '../../controle/control.php?nomeClasse=TipoRegistroProfissionalControle&metodo=listarTodos&status=' + status;

    $.ajax({
        type: "GET",
        url: url,
        dataType: 'json',
        success: function (response) {
            let $tbody = $('#tabela-tipos-registros');
            $tbody.empty();

            if (!response || response.length === 0) {
                $tbody.append('<tr><td colspan="4" class="text-center">Nenhum registro encontrado.</td></tr>');
                return;
            }

            $.each(response, function (i, item) {
                let id = item.id || item.id_registro_profissional_tipo;
                let descricao = item.descricao;
                let statusItem = item.status;

                let badgeStatus = (statusItem == 1) 
                    ? '<span class="label label-success">Ativo</span>' 
                    : '<span class="label label-danger">Inativo</span>';

                let acaoStatusBtn = (statusItem == 1)
                    ? '<button class="btn btn-xs btn-warning" onclick="alterarStatus(' + id + ', \'desativar\')"> Desativar</button>'
                    : '<button class="btn btn-xs btn-success" onclick="alterarStatus(' + id + ', \'ativar\')"> Ativar</button>';

                let acaoExcluirBtn = '<button class="btn btn-xs btn-danger" onclick=excluirTipoRegistro('+ id +')> Excluir </button>';

                const tr = '<tr>' +
                    '<td>' + id + '</td>' +
                    '<td>' + descricao + '</td>' +
                    '<td>' + badgeStatus + '</td>' +
                    '<td>' + acaoStatusBtn + '&nbsp;' + acaoExcluirBtn + '</td>' +
                    '</tr>';

                $tbody.append(tr);
            });
        },
        error: function (xhr, status, error) {
            console.error("Erro ao carregar tipos de registros:", error);
            $('#tabela-tipos-registros').html('<tr><td colspan="4" class="text-center text-danger">Erro ao carregar dados.</td></tr>');
        }
    });
}
function excluirTipoRegistro(id){
    if(!confirm("Tem certeza deseja excluir permanentemente esse tipo de registro?")){
        return;
    }
    const url = '../../controle/control.php';
    const data = {
        nomeClasse: 'TipoRegistroProfissionalControle',
        metodo: 'excluir',
        id_tipo_registro_profissional: id
    };
    $.ajax({
        type: "POST",
        url: url,
        data: $.param(data),
        contentType: "application/x-www-form-urlencoded; charset=utf-8",
        dataType: 'json',
        success: function (response) {
            alert(response.mensagem || "Registro excluído com sucesso!");
            carregarTabelaTiposRegistros();
        },
        error: function (xhr) {
            let mensagem = "Não foi possível excluir o registro.";
            if (xhr.responseJSON && xhr.responseJSON.mensagem) {
                mensagem = xhr.responseJSON.mensagem;
            }
            alert(mensagem);
        }
    });
}

function adicionarTipoRegistro() {
    let descricao = window.prompt("Cadastre um Novo Tipo de Registro Profissional (ex: CRM, OAB):");
    
    descricao=descricao.trim();
    if (!descricao) {
        alert("A descrição não pode ser vazia");
        return;
    }
    
    descricao = descricao.trim();
    if (descricao === '') {
        alert("A descrição não pode ser vazia.");
        return;
    }

    const url = '../../controle/control.php';
    const data = {
        nomeClasse: 'TipoRegistroProfissionalControle',
        metodo: 'incluir',
        descricao: descricao
    };

    $.ajax({
        type: "POST",
        url: url,
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: 'json',
        success: function (response) {
            alert(response.mensagem || "Registro cadastrado com sucesso!");
            carregarTabelaTiposRegistros(); 
        },
        error: function (xhr) {
            let mensagem = "Erro ao incluir registro.";
            if (xhr.responseJSON && xhr.responseJSON.mensagem) {
                mensagem = xhr.responseJSON.mensagem;
            }
            alert(mensagem);
        }
    });
}

function alterarStatus(id, operacao) {
    if (!confirm("Deseja realmente " + operacao + " este registro?")) {
        return;
    }

    const url = '../../controle/control.php';
    const data = {
        nomeClasse: 'TipoRegistroProfissionalControle',
        metodo: 'alterarStatus',
        id_tipo_registro_profissional: id,
        operacao: operacao
    };

    $.ajax({
        type: "POST",
        url: url,
        data: $.param(data),
        contentType: "application/x-www-form-urlencoded; charset=utf-8",
        dataType: 'json',
        success: function (response) {
            carregarTabelaTiposRegistros();
        },
        error: function (xhr) {
            let mensagem = "Erro ao alterar status.";
            if (xhr.responseJSON && xhr.responseJSON.mensagem) {
                mensagem = xhr.responseJSON.mensagem;
            }
            alert(mensagem);
        }
    });
}