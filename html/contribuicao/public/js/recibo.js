document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-recibo');

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const resultado = document.getElementById('resultado');
            const submitBtn = form.querySelector('button[type="submit"]');

            // Desabilitar botão e mostrar loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Gerando &nbsp <i class="fa fa-spinner fa-spin"></i>';
            resultado.innerHTML = '<div class="alert alert-info"><i class="fa fa-clock-o"></i> Processando recibo ...</div>';

            const formData = new FormData(form);

            // Adicionar parâmetros para o control.php
            formData.append('nomeClasse', 'ReciboController');
            formData.append('metodo', 'gerarRecibo');

            fetch('../controller/control.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.sucesso) {
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success';

                        // Título
                        const titulo = document.createElement('h4');
                        titulo.innerHTML = '<i class="fa fa-check-circle"></i> Recibo Gerado com Sucesso!';
                        alertDiv.appendChild(titulo);

                        // Mensagem
                        const message = document.createElement('p');
                        message.textContent = data.mensagem;
                        alertDiv.appendChild(message);

                        // Informações do recibo

                        const info = document.createElement('div');
                        info.className = 'recibo-info mt-3';
                        info.innerHTML = `
                        <p><strong>Email:</strong> ${data.email}</p>
                    `;
                        alertDiv.appendChild(info);

                        resultado.innerHTML = '';
                        resultado.appendChild(alertDiv);

                        // Limpar formulário
                        form.reset();

                    } else if (data.erro) {
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger';

                        let suporte = data.suporte;
                        let textoSuporte = '';

                        // Verifica se suporte existe e é uma string não vazia
                        if (typeof suporte === 'string' && suporte.trim() !== '') {
                            let link = '';

                            // Detecta e-mail
                            if (suporte.includes('@')) {
                                link = `<a href="mailto:${suporte}" target="_blank">${suporte}</a>`;
                            }
                            // Detecta telefone (só números)
                            else {
                                const numeroLimpo = suporte.replace(/\D/g, '');
                                link = `<a href="https://wa.me/${numeroLimpo}" target="_blank">${suporte}</a>`;
                            }

                            textoSuporte = ` Caso o problema persista, contate o nosso suporte: ${link}`;
                        }

                        alertDiv.innerHTML = `
                            <i class="fa fa-exclamation-triangle"></i> 
                            ${data.erro}${textoSuporte}
                        `;

                        resultado.innerHTML = '';
                        resultado.appendChild(alertDiv);
                    }

                })
                .catch(error => {
                    console.error('Erro:', error);
                    resultado.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fa fa-times-circle"></i> 
                        Erro na comunicação com o servidor: Se o problema persistir, contate o suporte!
                    </div>
                `;
                })
                .finally(() => {
                    // Reabilitar botão
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Gerar Recibo';
                });
        });
    }
});