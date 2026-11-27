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

    // Apenas os seletores do relatório ativo devem enviar seus tipos.
    document.getElementById('tipoEntradaSelect').disabled = !entrada;
    document.getElementById('tiposEntradaValores').disabled = !entrada;
    document.getElementById('tipoSaidaSelect').disabled = !saida;
    document.getElementById('tiposSaidaValores').disabled = !saida;

    document.getElementById('produtoSelect').required = produto;
    document.getElementById('grupoSelect').required = grupo;

    const formulario = document.getElementById('form-produto-grupo');
    formulario.action = grupo ? formulario.dataset.actionGrupo : formulario.dataset.actionProduto;
}
