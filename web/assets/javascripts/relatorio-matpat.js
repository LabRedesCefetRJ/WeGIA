(function () {
    function limparImpressao() {
        document.querySelectorAll('.relatorio-impressao').forEach(function (elemento) {
            elemento.remove();
        });
        document.body.classList.remove('relatorio-imprimindo');
    }

    window.addEventListener('beforeprint', function () {
        limparImpressao();
        document.querySelectorAll('template.relatorio-template').forEach(function (template) {
            const impressao = document.createElement('div');
            impressao.className = 'relatorio-impressao';
            impressao.appendChild(template.content.cloneNode(true));
            impressao.querySelectorAll('[id]').forEach(function (elemento) {
                elemento.removeAttribute('id');
            });
            template.after(impressao);
        });
        if (document.querySelector('.relatorio-impressao')) {
            document.body.classList.add('relatorio-imprimindo');
        }
    });
    window.addEventListener('afterprint', limparImpressao);
}());
