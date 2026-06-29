document.addEventListener('DOMContentLoaded', function () {
    const btnUnica = document.getElementById('btn-doacao-unica');
    const btnMensal = document.getElementById('btn-doacao-mensal');
    const opcoesUnica = document.getElementById('opcoes-unica');
    const opcoesMensal = document.getElementById('opcoes-mensal');

    //Função para alternar entre doação única e mensal
    function alterarTipoDoacao(unica) {
        if (unica) {
            //Mostrar doação única
            btnUnica.classList.remove('btn-outline-primary');
            btnUnica.classList.add('btn-primary');
            btnMensal.classList.remove('btn-primary');
            btnMensal.classList.add('btn-outline-primary');
            opcoesUnica.style.display = 'block';
            opcoesMensal.style.display = 'none';
        } else {
            //Mostrar doação mensal
            btnMensal.classList.remove('btn-outline-primary');
            btnMensal.classList.add('btn-primary');
            btnUnica.classList.remove('btn-primary');
            btnUnica.classList.add('btn-outline-primary');
            opcoesUnica.style.display = 'none';
            opcoesMensal.style.display = 'block';
        }
    }

    btnUnica.addEventListener('click', function () {
        alterarTipoDoacao(true);
    });

    btnMensal.addEventListener('click', function () {
        alterarTipoDoacao(false);
    });

    const contactContainer = document.getElementById('contact-container');

    const contactUrl = '../controller/control.php?nomeClasse=ContactController&metodo=getSupportContact';

    fetch(contactUrl, {
        method: 'GET'
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na busca do contato de suporte.');
            }

            return response.json();
        })
        .then(data => {
            const supportContact = String(data.contato ?? '').trim();

            if (!supportContact) {
                throw new Error('Nenhum contato de suporte foi retornado.');
            }

            contactContainer.innerHTML = '';

            // Expressões para identificação do tipo de contato
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const phoneRegex = /^\+?[\d\s()-]{8,}$/;

            const link = document.createElement('a');
            link.target = '_blank';
            link.rel = 'noopener noreferrer';

            let channelName;

            if (emailRegex.test(supportContact)) {
                channelName = 'e-mail';
                link.href = `mailto:${supportContact}`;
                link.textContent = supportContact;
            } else if (phoneRegex.test(supportContact)) {
                channelName = 'WhatsApp';

                // Mantém apenas os dígitos para o wa.me
                const phone = supportContact.replace(/\D/g, '');

                const message = encodeURIComponent(
                    'Olá! Preciso de ajuda para realizar uma contribuição financeira através do site institucional.'
                );

                link.href = `https://wa.me/${phone}?text=${message}`;
                link.textContent = supportContact;
            } else {
                throw new Error('Formato de contato inválido.');
            }

            const alert = document.createElement('div');
            alert.className = 'alert alert-info';
            alert.role = 'alert';

            const title = document.createElement('strong');
            title.textContent = 'Precisa de ajuda?';

            alert.appendChild(title);
            alert.appendChild(document.createElement('br'));

            alert.append(
                document.createTextNode(
                    'Em caso de dúvidas ou se precisar de auxílio, nossa equipe está disponível para atendê-lo por '
                )
            );

            const channel = document.createElement('strong');
            channel.textContent = channelName;
            alert.appendChild(channel);

            alert.append(document.createTextNode('. Clique em '));
            alert.appendChild(link);
            alert.append(
                document.createTextNode(
                    ' para iniciar uma conversa com nossa equipe de atendimento.'
                )
            );

            contactContainer.appendChild(alert);
        })
        .catch(error => {
            console.error(error);

            contactContainer.innerHTML = `
            <div class="alert alert-warning" role="alert">
                <strong>Não foi possível carregar os canais de atendimento.</strong><br>
                Tente novamente em alguns instantes. Se o problema persistir, entre em contato com a equipe de suporte.
            </div>
        `;
        });

});