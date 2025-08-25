//Futuramente refatorar para servir tanto para funcionários quanto para atendidos
function gerarParentesco() {
    url = 'dependente_parentesco_listar.php';
    $.ajax({
        data: '',
        type: "POST",
        url: url,
        async: true,
        success: function (response) {
            var parentesco = response;
            $('#parentesco').empty();
            $('#parentesco').append('<option selected disabled>Selecionar...</option>');
            $.each(parentesco, function (i, item) {
                $('#parentesco').append('<option value="' + item.id_parentesco + '">' + item.descricao + '</option>');
            });
        },
        dataType: 'json'
    });
}

function adicionarParentesco() {
    url = 'dependente_parentesco_adicionar.php';
    var descricao = window.prompt("Cadastre um novo tipo de Parentesco:");
    if (!descricao) {
        return
    }
    descricao = descricao.trim();
    if (descricao == '') {
        return
    }
    data = 'descricao=' + descricao;
    $.ajax({
        type: "POST",
        url: url,
        data: data,
        success: function (response) {
            gerarParentesco();
        },
        dataType: 'text'
    })
}