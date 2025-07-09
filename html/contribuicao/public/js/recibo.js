$(document).ready(function() {
    // Máscara para CPF
    $('#cpf').on('input', function() {
        let v = $(this).val().replace(/\D/g, '');
        if (v.length > 11) v = v.substr(0, 11);
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        $(this).val(v);
    });

    // Obter token CSRF
    $.get('../controller/ReciboController.php?csrf=1', function(data) {
        $('#csrf_token').val(data.token);
    }, 'json');

    // Submissão do formulário
    $('#formulario-recibo').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '../controller/ReciboController.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                $('#alert-box').removeClass('alert-danger alert-success');
                if (response.success) {
                    $('#alert-box').addClass('alert-success');
                } else {
                    $('#alert-box').addClass('alert-danger');
                }
                $('#mensagem-texto').text(response.message);
                $('#mensagem-resultado').show();
            },
            error: function() {
                $('#alert-box').removeClass('alert-success').addClass('alert-danger');
                $('#mensagem-texto').text('Erro na comunicação com o servidor');
                $('#mensagem-resultado').show();
            }
        });
    });
});