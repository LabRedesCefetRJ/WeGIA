(function () {
    'use strict';

    document.querySelectorAll('form[data-cotacao-form]').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (form.dataset.enviando === '1') return;

            let aviso = form.querySelector('[data-erro-cotacao]');
            if (!aviso) {
                aviso = document.createElement('div');
                aviso.className = 'alert alert-danger';
                aviso.dataset.erroCotacao = '1';
                aviso.setAttribute('role', 'alert');
                aviso.tabIndex = -1;
                const corpo = form.querySelector('.modal-body') || form;
                corpo.prepend(aviso);
            }
            aviso.hidden = true;
            const dados = new FormData(form);
            const botoes = Array.from(form.querySelectorAll('[type="submit"]'));
            form.dataset.enviando = '1';
            botoes.forEach(function (botao) { botao.disabled = true; });

            try {
                const resposta = await fetch(form.action, {
                    method: 'POST',
                    body: dados,
                    credentials: 'same-origin',
                    headers: { 'X-Cotacao-Form': '1', 'Accept': 'application/json' }
                });
                const tipo = resposta.headers.get('Content-Type') || '';
                if (!tipo.includes('application/json')) {
                    throw new Error('Não foi possível concluir a operação. Verifique sua sessão e suas permissões.');
                }
                const resultado = await resposta.json();
                if (!resposta.ok || resultado.status !== 'sucesso') {
                    throw new Error(resultado.mensagem || 'Não foi possível concluir a operação.');
                }
                window.location.assign(resultado.redirect);
            } catch (erro) {
                aviso.textContent = erro.message || 'Não foi possível enviar o formulário.';
                aviso.hidden = false;
                aviso.focus();
            } finally {
                form.dataset.enviando = '0';
                botoes.forEach(function (botao) { botao.disabled = false; });
            }
        });
    });
}());
