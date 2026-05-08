function isEstoque(is_estoque) {
    const display = is_estoque ? 'none' : 'block';
    const hide = is_estoque ? 'block' : 'none';

    document.getElementById('dest').style.display = display;
    document.getElementById('orig').style.display = display;
    document.getElementById('resp').style.display = display;
    document.getElementById('per').style.display = display;
    document.getElementById('tipo-entrada').style.display = display;
    document.getElementById('tipo-saida').style.display = display;
    document.getElementById('panel-mostrarZerados').style.display = hide;
    document.getElementById('gerar').style.display = display;
    document.getElementById('gerar3').style.display = is_estoque ? 'block' : 'none'; 

    document.getElementById('gerar2').style.display = 'none';
    document.getElementById('per2').style.display = 'none';
    document.getElementById('produto').style.display = 'none';
    document.getElementById('almoxarifado2').style.display = 'none';
}

function isEntrada(is_entrada) {
    const display = is_entrada ? 'block' : 'none';
    const hide = is_entrada ? 'none' : 'block';

    document.getElementById('tipo-entrada').style.display = display;
    document.getElementById('tipo-saida').style.display = hide;
    document.getElementById('orig').style.display = display;
    document.getElementById('dest').style.display = hide;
    document.getElementById('produto').style.display = 'none';
    document.getElementById('almoxarifado2').style.display = 'none';
    document.getElementById('gerar3').style.display = hide;

    document.querySelector("#tipo-entrada > div > select").name = is_entrada ? 'tipo' : '';
    document.querySelector("#tipo-saida > div > select").name = is_entrada ? '' : 'tipo';
}

function isProduto(is_produto) {
    const displayValue = is_produto ? 'block' : 'none';

    document.getElementById('tipo-entrada').style.display = 'none';
    document.getElementById('tipo-saida').style.display = 'none';
    document.getElementById('orig').style.display = 'none';
    document.getElementById('dest').style.display = 'none';
    document.getElementById('resp').style.display = 'none';
    document.getElementById('per').style.display = 'none';
    document.getElementById('gerar').style.display = 'none';
    document.getElementById('almoxarifado').style.display = 'none';
    document.getElementById('gerar3').style.display = 'none';

    document.getElementById('almoxarifado2').style.display = displayValue;
    document.getElementById('per2').style.display = displayValue;
    document.getElementById('produto').style.display = displayValue;
    document.getElementById('gerar2').style.display = displayValue;
}

function changeType(selection) {
    if (selection === 'estoque') {
        isEstoque(true);
    } else if (selection === 'produto') {
        isEstoque(false);
        isProduto(true);
    } else {
        isEstoque(false);
        isEntrada(selection === 'entrada');
        document.getElementById('gerar3').style.display = 'none';
    }
}

$(document).ready(function() {
    const tipo_relat = $('#tipo-relat').val();
    changeType(tipo_relat);
});