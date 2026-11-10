document.addEventListener('DOMContentLoaded', function () {
    const btnUnica = document.getElementById('btn-doacao-unica');
    const btnMensal = document.getElementById('btn-doacao-mensal');
    const opcoesUnica = document.getElementById('opcoes-unica');
    const opcoesMensal = document.getElementById('opcoes-mensal');
    let doacaoUnicaSelecionada = false;

    [opcoesUnica, opcoesMensal].forEach((opcoes) => {
        opcoes.addEventListener('animationend', (event) => {
            if (event.animationName === 'contribution-options-reveal') {
                opcoes.classList.remove('contribution-options-enter');
            }
        });
    });

    function exibirOpcoesComAnimacao(opcoes, display = 'block') {
        opcoes.style.display = display;
        opcoes.classList.remove('contribution-options-enter');
        void opcoes.offsetWidth;
        opcoes.classList.add('contribution-options-enter');
    }

    //Função para alternar entre doação única e mensal
    function alterarTipoDoacao(unica) {
        if (unica === doacaoUnicaSelecionada) {
            return;
        }

        doacaoUnicaSelecionada = unica;
        if (unica) {
            //Mostrar doação única
            btnUnica.classList.remove('btn-outline-primary');
            btnUnica.classList.add('btn-primary');
            btnUnica.setAttribute('aria-pressed', 'true');
            btnMensal.classList.remove('btn-primary');
            btnMensal.classList.add('btn-outline-primary');
            btnMensal.setAttribute('aria-pressed', 'false');
            exibirOpcoesComAnimacao(opcoesUnica, 'flex');
            opcoesMensal.style.display = 'none';
        } else {
            //Mostrar doação mensal
            btnMensal.classList.remove('btn-outline-primary');
            btnMensal.classList.add('btn-primary');
            btnMensal.setAttribute('aria-pressed', 'true');
            btnUnica.classList.remove('btn-primary');
            btnUnica.classList.add('btn-outline-primary');
            btnUnica.setAttribute('aria-pressed', 'false');
            opcoesUnica.style.display = 'none';
            exibirOpcoesComAnimacao(opcoesMensal);
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
            const titleIcon = document.createElement('i');
            titleIcon.className = 'fa fa-life-ring me-2';
            titleIcon.setAttribute('aria-hidden', 'true');
            title.append(titleIcon, document.createTextNode('Precisa de ajuda?'));

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
        });

});