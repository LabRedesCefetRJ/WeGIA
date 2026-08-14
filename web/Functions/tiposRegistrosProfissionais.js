function carregarTabelaTiposRegistros() {
    var status = $('#select_status').val();
    if (!status) {
        status = 1;
    }

    var url = '../../controle/control.php?nomeClasse=TipoRegistroProfissionalControle&metodo=listarTodos&status=' + status;

    $.ajax({
        type: "GET",
        url: url,
        dataType: 'json',
        success: function (response) {
            var $tbody = $('#tabela-tipos-registros');
            $tbody.empty();

            if (!response || response.length === 0) {
                $tbody.append('<tr><td colspan="4" class="text-center">Nenhum registro encontrado.</td></tr>');
                return;
            }

            $.each(response, function (i, item) {
                var id = item.id || item.id_registro_profissional_tipo;
                var descricao = item.descricao;
                var statusItem = item.status;

                var badgeStatus = (statusItem == 1) 
                    ? '<span class="label label-success">Ativo</span>' 
                    : '<span class="label label-danger">Inativo</span>';

                var acaoStatusBtn = (statusItem == 1)
                    ? '<button class="btn btn-xs btn-warning" onclick="alterarStatus(' + id + ', \'desativar\')"><i class="fa fa-ban"></i> Desativar</button>'
                    : '<button class="btn btn-xs btn-success" onclick="alterarStatus(' + id + ', \'ativar\')"><i class="fa fa-check"></i> Ativar</button>';

                var tr = '<tr>' +
                    '<td>' + id + '</td>' +
                    '<td>' + descricao + '</td>' +
                    '<td>' + badgeStatus + '</td>' +
                    '<td>' + acaoStatusBtn + '</td>' +
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

function adicionar_tipoRegistro() {
    var descricao = window.prompt("Cadastre um Novo Tipo de Registro Profissional (ex: CRM, OAB):");
    
    if (!descricao) {
        return;
    }
    
    descricao = descricao.trim();
    if (descricao === '') {
        alert("A descrição não pode ser vazia.");
        return;
    }

    var url = '../../controle/control.php';
    var data = {
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
            carregarTabelaTiposRegistros();
        },
        error: function (xhr) {
            var mensagem = "Erro ao incluir registro.";
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

    var url = '../../controle/control.php';
    var data = {
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
            var mensagem = "Erro ao alterar status.";
            if (xhr.responseJSON && xhr.responseJSON.mensagem) {
                mensagem = xhr.responseJSON.mensagem;
            }
            alert(mensagem);
        }
    });
}