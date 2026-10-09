function changeType(tipo) {
    const produto = tipo === 'produto';
    const grupo = tipo === 'grupo';
    const entrada = tipo === 'entrada';
    const saida = tipo === 'saida';
    const relatorioProduto = produto || grupo;
    const usaPeriodo = entrada || saida || tipo === 'requisicao' || tipo === 'itens_compra';

    const campos = {
        per: usaPeriodo,
        orig: entrada,
        dest: saida,
        resp: entrada || saida,
        'tipo-entrada': entrada,
        'tipo-saida': saida,
        'panel-mostrarZerados': tipo === 'estoque',
        almoxarifado: !relatorioProduto,
        gerar: !relatorioProduto,
        gerar3: false,
        per2: relatorioProduto,
        almoxarifado2: relatorioProduto,
        gerar2: relatorioProduto,
        produto,
        'grupo-produto-relatorio': grupo
    };

    Object.entries(campos).forEach(([id, visivel]) => {
        document.getElementById(id).style.display = visivel ? 'block' : 'none';
    });

    // Apenas o seletor visível deve enviar o parâmetro "tipo".
    document.getElementById('tipoEntradaSelect').name = entrada ? 'tipo' : '';
    document.getElementById('tipoSaidaSelect').name = saida ? 'tipo' : '';

    document.getElementById('produtoSelect').required = produto;
    document.getElementById('grupoSelect').required = grupo;

    const formulario = document.getElementById('form-produto-grupo');
    formulario.action = grupo ? formulario.dataset.actionGrupo : formulario.dataset.actionProduto;
}
