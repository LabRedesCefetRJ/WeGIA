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
    document.getElementById('almoxarifado').style.display = 'block';

    document.getElementById('gerar2').style.display = 'none';
    document.getElementById('per2').style.display = 'none';
    document.getElementById('produto').style.display = 'none';
    document.getElementById('almoxarifado2').style.display = 'none';
    document.getElementById('grupo-produto-relatorio').style.display = 'none';
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
    document.getElementById('grupo-produto-relatorio').style.display = 'none';

    document.querySelector("#tipo-entrada > div > select").name = is_entrada ? 'tipo' : '';
    document.querySelector("#tipo-saida > div > select").name = is_entrada ? '' : 'tipo';
}

function isProdutoOuGrupo(tipo) {
    const isProduto = tipo === 'produto';
    const isGrupo = tipo === 'grupo';
    const ativo = isProduto || isGrupo;

    const displayValue = ativo ? 'block' : 'none';

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
    document.getElementById('gerar2').style.display = displayValue;

    document.getElementById('produto').style.display =
        isProduto ? 'block' : 'none';

    document.getElementById('grupo-produto-relatorio').style.display =
        isGrupo ? 'block' : 'none';

    const produtoSelect = document.getElementById('produtoSelect');
    const grupoSelect = document.getElementById('grupoSelect');

    produtoSelect.required = isProduto;
    grupoSelect.required = isGrupo;

    const form = document.getElementById('form-produto-grupo');

    if (isGrupo) {
        form.action = form.dataset.actionGrupo;
    } else {
        form.action = form.dataset.actionProduto;
    }
}

function changeType(selection) {
    if (selection === 'estoque') {

        isProdutoOuGrupo(null);
        isEstoque(true);

    } else if (selection === 'produto' || selection === 'grupo') {

        isEstoque(false);
        isProdutoOuGrupo(selection);

    } else {

        isProdutoOuGrupo(null);
        isEstoque(false);
        isEntrada(selection === 'entrada');

        document.getElementById('gerar3').style.display = 'none';
    }
}

$(document).ready(function() {
    const tipo_relat = $('#tipo-relat').val();
    changeType(tipo_relat);
});