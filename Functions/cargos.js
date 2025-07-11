function gerarCargo() {
    url = '../../controle/control.php?nomeClasse=CargoControle&metodo=listarTodos';

    $.ajax({
        type: "GET",
        url: url,
        success: function (response) {
            var cargo = response;
            $('#cargo').empty();
            $('#cargo').append('<option selected disabled>Selecionar</option>');
            $.each(cargo, function (i, item) {
                $('#cargo').append('<option value="' + item.id_cargo + '">' + item.cargo + '</option>');
            });
        },
        dataType: 'json'
    });
}

function adicionar_cargo() {
    url = '../../controle/control.php';
    var cargo = window.prompt("Cadastre um Novo Cargo:");
    if (!cargo) {
        return
    }
    situacao = cargo.trim();
    if (cargo == '') {
        return
    }
    data = {
        nomeClasse: 'CargoControle',
        metodo: 'incluir',
        cargo: cargo
    };

    $.ajax({
        type: "POST",
        url: url,
        data: JSON.stringify(data),
        contentType: "application/json",
        success: function (response) {
            gerarCargo();
        },
        dataType: 'text'
    });
}