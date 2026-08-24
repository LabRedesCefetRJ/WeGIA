document.addEventListener('DOMContentLoaded', function () {
    const config = window.WEGIA_VALIDAR_SOCIO_CONFIG || {};
    const apiBaseUrl = String(config.apiBaseUrl || '').replace(/\/$/, '');

    const form = document.querySelector('.buscar-socio-form');
    const codigoInput = document.getElementById('codigo_socio');
    const resumoSocio = document.getElementById('resumo_socio');
    const alertContainer = document.getElementById('mensagens_usuario');
    const contatoSuporteLink = document.getElementById('link_contato_suporte');
    const btnCopiarCodigo = document.getElementById('btn_copy_codigo');

    if (!form || !codigoInput || !resumoSocio || !alertContainer) {
        return;
    }

    const resumoFields = {
        nome: document.getElementById('socio_nome'),
        cpf: document.getElementById('socio_cpf'),
        dataNascimento: document.getElementById('socio_data_nascimento'),
        telefone: document.getElementById('socio_telefone'),
        email: document.getElementById('socio_email'),
        inicioContribuicao: document.getElementById('contribuicao_inicio'),
        ultimaContribuicao: document.getElementById('contribuicao_ultima'),
        pontosBeneficios: document.getElementById('pontos_beneficios'),
        codigoValidacao: document.getElementById('codigo_validacao'),
        socioStatus: document.getElementById('socio-status')
    };

    let supportContact = '';

    function buildApiUrl(path) {
        return `${apiBaseUrl}${path.startsWith('/') ? '' : '/'}${path}`;
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function clearAlerts() {
        alertContainer.innerHTML = '';
    }

    function showAlert(type, message, options = {}) {
        const dismissible = options.dismissible !== false;
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}${dismissible ? ' alert-dismissible' : ''}`;
        alert.setAttribute('role', 'alert');

        if (dismissible) {
            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'close';
            closeButton.setAttribute('data-dismiss', 'alert');
            closeButton.setAttribute('aria-label', 'Fechar');
            closeButton.innerHTML = '<span aria-hidden="true">&times;</span>';
            alert.appendChild(closeButton);
        }

        alert.insertAdjacentHTML('beforeend', message);
        alertContainer.appendChild(alert);
    }

    function showLoadingAlert() {
        clearAlerts();
        showAlert('info', '<i class="fa fa-spinner fa-spin"></i> Consultando dados do sócio...', {
            dismissible: false
        });
    }

    function hideResumo() {
        resumoSocio.hidden = true;
        resumoSocio.setAttribute('aria-hidden', 'true');
        toggleCopyButtonState(false, 'Copiar');
    }

    function showResumo() {
        resumoSocio.hidden = false;
        resumoSocio.setAttribute('aria-hidden', 'false');
    }

    function toggleCopyButtonState(enabled, text = 'Copiar') {
        if (!btnCopiarCodigo) {
            return;
        }

        btnCopiarCodigo.disabled = !enabled;
        btnCopiarCodigo.classList.toggle('is-copied', text === 'Copiado');

        const label = btnCopiarCodigo.querySelector('.btn-copy-codigo__text');
        if (label) {
            label.textContent = text;
        }
    }

    async function copyToClipboard(text) {
        const value = String(text || '').trim();

        if (!value) {
            throw new Error('Código de validação indisponível.');
        }

        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            await navigator.clipboard.writeText(value);
            return;
        }

        const fallbackInput = document.createElement('textarea');
        fallbackInput.value = value;
        fallbackInput.setAttribute('readonly', 'true');
        fallbackInput.style.position = 'fixed';
        fallbackInput.style.left = '-9999px';
        fallbackInput.style.top = '0';
        document.body.appendChild(fallbackInput);
        fallbackInput.select();
        fallbackInput.setSelectionRange(0, fallbackInput.value.length);

        const copied = document.execCommand('copy');
        document.body.removeChild(fallbackInput);

        if (!copied) {
            throw new Error('Falha ao copiar o código.');
        }
    }

    function setTextContent(element, value) {
        if (element) {
            element.textContent = value;
        }
    }

    function updateStatusState(state) {
        const socioStatus = resumoFields.socioStatus;
        const statusIcon = document.querySelector('.socio-resumo-card__status-icone');
        const statusIconElement = statusIcon ? statusIcon.querySelector('i') : null;

        if (!socioStatus || !statusIcon) {
            return;
        }

        socioStatus.classList.remove('status--ativo', 'status--inativo', 'status--desconhecido');
        statusIcon.classList.remove('status--ativo', 'status--inativo', 'status--desconhecido');

        let titulo = 'Status desconhecido';
        let descricao = 'Não foi possível determinar o status do sócio.';
        let iconClass = 'fa fa-question-circle';
        let statusClass = 'status--desconhecido';

        if (state === 'ativo') {
            titulo = 'Sócio ativo';
            descricao = 'Cadastro válido e com pontos de benefícios disponíveis.';
            iconClass = 'fa fa-check-circle';
            statusClass = 'status--ativo';
        } else if (state === 'inativo') {
            titulo = 'Sócio inativo';
            descricao = 'Cadastro válido, mas sem pontos de benefícios.';
            iconClass = 'fa fa-times-circle';
            statusClass = 'status--inativo';
        }

        socioStatus.innerHTML = `<h2>${titulo}</h2><p>${descricao}</p>`;
        socioStatus.classList.add(statusClass);
        statusIcon.classList.add(statusClass);

        if (statusIconElement) {
            statusIconElement.className = iconClass;
        }
    }

    function formatDate(value) {
        if (!value) {
            return 'Não informado';
        }

        const raw = String(value).trim();

        if (!raw) {
            return 'Não informado';
        }

        if (/^\d{2}\/\*\*\/\*\*\d{2}$/.test(raw)) {
            return raw;
        }

        if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
            const [year, month, day] = raw.split('-');
            return `${day}/${month}/${year}`;
        }

        const parsed = new Date(raw);
        if (!Number.isNaN(parsed.getTime())) {
            const day = String(parsed.getDate()).padStart(2, '0');
            const month = String(parsed.getMonth() + 1).padStart(2, '0');
            const year = String(parsed.getFullYear());
            return `${day}/${month}/${year}`;
        }

        return raw;
    }

    function normalizeContactValue(value) {
        return String(value || '').trim();
    }

    function applySupportContact(contactValue) {
        if (!contatoSuporteLink) {
            return;
        }

        const contact = normalizeContactValue(contactValue);

        if (!contact) {
            contatoSuporteLink.setAttribute('href', '#');
            contatoSuporteLink.removeAttribute('target');
            contatoSuporteLink.removeAttribute('rel');
            return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const phoneRegex = /^\+?[\d\s().-]{8,}$/;

        if (emailRegex.test(contact)) {
            contatoSuporteLink.setAttribute('href', `mailto:${contact}`);
            contatoSuporteLink.removeAttribute('target');
            contatoSuporteLink.setAttribute('rel', 'noopener noreferrer');
            contatoSuporteLink.innerHTML = `<i class="fa fa-envelope"></i> ${escapeHtml(contact)}`;
            return;
        }

        if (phoneRegex.test(contact)) {
            const phone = contact.replace(/\D/g, '');
            const message = encodeURIComponent('Olá! Preciso de ajuda com a validação de sócio.');
            contatoSuporteLink.setAttribute('href', `https://wa.me/${phone}?text=${message}`);
            contatoSuporteLink.setAttribute('target', '_blank');
            contatoSuporteLink.setAttribute('rel', 'noopener noreferrer');
            contatoSuporteLink.innerHTML = `<i class="fa fa-phone"></i> ${escapeHtml(contact)}`;
            return;
        }

        contatoSuporteLink.setAttribute('href', '#');
        contatoSuporteLink.removeAttribute('target');
        contatoSuporteLink.setAttribute('rel', 'noopener noreferrer');
        contatoSuporteLink.innerHTML = '<i class="fa fa-phone"></i> Contato';
    }

    async function carregarContatoSuporte() {
        if (!apiBaseUrl) {
            return;
        }

        try {
            const response = await fetch(buildApiUrl('/socios/support-contact'), {
                method: 'GET',
                headers: {
                    Accept: 'application/json'
                }
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(payload.message || payload.error || 'Não foi possível carregar o contato de suporte.');
            }

            const contactValue = payload.contatct ?? payload.contact ?? '';
            supportContact = normalizeContactValue(contactValue);
            applySupportContact(supportContact);
        } catch (error) {
            applySupportContact('');
            console.error(error);
        }
    }

    function preencherResumo(data, uuid) {
        const nomeCompleto = [data.nome, data.sobrenome].filter(Boolean).join(' ').trim();
        const codigoFormatado = String(uuid || '').trim();

        setTextContent(resumoFields.nome, nomeCompleto || 'Sócio localizado');
        setTextContent(resumoFields.cpf, data.cpf || 'Não informado');
        setTextContent(resumoFields.dataNascimento, formatDate(data.dataNascimento));
        setTextContent(resumoFields.telefone, data.telefone || 'Não informado');
        setTextContent(resumoFields.email, data.email || 'Não informado');
        setTextContent(resumoFields.inicioContribuicao, formatDate(data.dataReferenciaContribuicao));
        setTextContent(resumoFields.ultimaContribuicao, formatDate(data.dataUltimaContribuicao));
        setTextContent(resumoFields.pontosBeneficios, String(data.benefit_points ?? 0));
        setTextContent(resumoFields.codigoValidacao, codigoFormatado || '--');

        const benefitPoints = Number(data.benefit_points);

        if (Number.isFinite(benefitPoints) && benefitPoints > 0) {
            updateStatusState('ativo');
        } else if (Number.isFinite(benefitPoints) && benefitPoints === 0) {
            updateStatusState('inativo');
        } else {
            updateStatusState('desconhecido');
        }

        toggleCopyButtonState(Boolean(codigoFormatado));

        showResumo();
        clearAlerts();
        resumoSocio.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    async function consultarSocio(uuid) {
        const codigo = String(uuid || '').trim();

        if (!codigo) {
            hideResumo();
            clearAlerts();
            showAlert('warning', 'Informe um UUID válido para consultar o sócio.');
            codigoInput.focus();
            return;
        }

        if (!apiBaseUrl) {
            hideResumo();
            clearAlerts();
            showAlert('danger', 'A configuração da API não está disponível para esta página.');
            return;
        }

        showLoadingAlert();

        try {
            const response = await fetch(buildApiUrl(`/socios/${encodeURIComponent(codigo)}/validar_beneficios`), {
                method: 'GET',
                headers: {
                    Accept: 'application/json'
                }
            });

            const payload = await response.json().catch(() => ({}));

            if (response.ok) {
                preencherResumo(payload, codigo);
                return;
            }

            hideResumo();

            if (response.status === 404) {
                clearAlerts();
                const contactText = supportContact ? ` Entre em contato com o suporte em ${escapeHtml(supportContact)}.` : '';
                showAlert('warning', `Nenhum sócio foi encontrado com o código informado.${contactText}`);
                return;
            }

            if (response.status === 400) {
                clearAlerts();
                showAlert('warning', escapeHtml(payload.message || 'UUID inválido.'));
                return;
            }

            clearAlerts();
            showAlert('danger', escapeHtml(payload.error || payload.message || 'Ocorreu um erro ao consultar o sócio.'));
        } catch (error) {
            hideResumo();
            console.error(error);
            clearAlerts();
            showAlert('danger', 'Não foi possível consultar o sócio no momento. Tente novamente em instantes.');
        }
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        consultarSocio(codigoInput.value);
    });

    if (btnCopiarCodigo) {
        btnCopiarCodigo.addEventListener('click', async function () {
            const codigo = resumoFields.codigoValidacao ? resumoFields.codigoValidacao.textContent : '';

            try {
                await copyToClipboard(codigo);
                const originalLabel = 'Copiar';
                toggleCopyButtonState(true, 'Copiado');
                window.setTimeout(() => toggleCopyButtonState(true, originalLabel), 1600);
                clearAlerts();
                showAlert('success', 'Código de validação copiado para a área de transferência.', {
                    dismissible: true
                });
            } catch (error) {
                console.error(error);
                clearAlerts();
                showAlert('warning', 'Não foi possível copiar automaticamente. Selecione e copie o código manualmente.');
            }
        });
    }

    const params = new URLSearchParams(window.location.search);
    const codigoQuery = params.get('codigo');

    if (codigoQuery) {
        codigoInput.value = codigoQuery;
        carregarContatoSuporte()
            .catch(() => undefined)
            .finally(() => consultarSocio(codigoQuery));
    } else {
        carregarContatoSuporte().catch(() => undefined);
        hideResumo();
    }

    toggleCopyButtonState(false, 'Copiar');
});
