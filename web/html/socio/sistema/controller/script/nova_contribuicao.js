(function () {
    'use strict';

    const form = document.getElementById('formNovaContribuicao');
    if (!form) return;

    const socioInput = document.getElementById('nova-contribuicao-socio');
    const socioIdInput = document.getElementById('nova-contribuicao-id-socio');
    const socioSelected = document.getElementById('nova-contribuicao-socio-selecionado');
    const alertBox = document.getElementById('nova-contribuicao-alert');
    const submitButton = document.getElementById('salvar-nova-contribuicao');
    const apiUrl = typeof apiServer !== 'undefined' ? apiServer : '';

    function formatDateTime(value) {
        return value ? value.replace('T', ' ') + (value.length === 16 ? ':00' : '') : '';
    }

    function setAlert(type, message) {
        alertBox.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible">'
            + '<button type="button" class="close" data-dismiss="alert">&times;</button>'
            + message + '</div>';
    }

    function resetSocio() {
        socioIdInput.value = '';
        socioSelected.textContent = '';
    }

    function setDefaultDates() {
        const now = new Date();
        const due = new Date(now.getTime() + (7 * 24 * 60 * 60 * 1000));
        const localValue = date => new Date(date.getTime() - date.getTimezoneOffset() * 60000)
            .toISOString().slice(0, 16);
        document.getElementById('nova-contribuicao-pagamento').value = localValue(now);
        document.getElementById('nova-contribuicao-geracao').value = localValue(now);
        document.getElementById('nova-contribuicao-vencimento').value = localValue(due);
    }

    async function loadPaymentMethods() {
        const select = document.getElementById('nova-contribuicao-meio');
        try {
            const response = await authenticatedRequest(() => fetch(apiUrl + 'contribuicoes/payment_methods', {
                credentials: 'include',
                headers: { 'X-Client-Type': 'web' }
            }));
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.error || 'Não foi possível carregar os meios de pagamento.');
            select.innerHTML = '<option value="">Selecione</option>';
            result.payment_methods.forEach(function (method) {
                const option = document.createElement('option');
                option.value = method.id;
                option.textContent = method.meio;
                select.appendChild(option);
            });
        } catch (error) {
            select.innerHTML = '<option value="">Indisponível</option>';
            setAlert('danger', error.message);
        }
    }

    const socioContainer = socioInput.parentElement;
    const resultsBox = document.createElement('div');
    let searchTimer;
    let searchSequence = 0;
    socioContainer.style.position = 'relative';
    resultsBox.className = 'nova-contribuicao-resultados';
    resultsBox.style.cssText = 'position:absolute;left:0;right:0;top:100%;z-index:1051;background:#fff;border:1px solid #d2d6de;display:none;max-height:220px;overflow-y:auto;';
    socioContainer.appendChild(resultsBox);

    function clearSearchResults() {
        resultsBox.innerHTML = '';
        resultsBox.style.display = 'none';
    }

    function showSearchMessage(message) {
        resultsBox.textContent = message;
        resultsBox.style.display = 'block';
    }

    function showSocioResults(socios) {
        resultsBox.innerHTML = '';
        if (!socios.length) {
            showSearchMessage('Nenhum sócio encontrado.');
            return;
        }
        socios.forEach(function (socio) {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'btn btn-link btn-block text-left';
            option.style.cssText = 'display:block;text-align:left;padding:8px 12px;border-bottom:1px solid #eee;white-space:normal;';
            option.textContent = socio.label;
            option.addEventListener('click', function () {
                socioInput.value = socio.label;
                socioIdInput.value = socio.id;
                socioSelected.textContent = 'Sócio selecionado: ' + socio.label;
                clearSearchResults();
            });
            resultsBox.appendChild(option);
        });
        resultsBox.style.display = 'block';
    }

    socioInput.addEventListener('input', function () {
        resetSocio();
        clearTimeout(searchTimer);
        const term = socioInput.value.trim();
        if (term.length < 2) {
            clearSearchResults();
            return;
        }

        showSearchMessage('Buscando sócios...');
        const requestSequence = ++searchSequence;
        searchTimer = setTimeout(function () {
            fetch('buscar_socios.php?q=' + encodeURIComponent(term), {
                credentials: 'include',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (response) {
                    if (!response.ok) throw new Error('Não foi possível pesquisar os sócios.');
                    return response.json();
                })
                .then(function (socios) {
                    if (requestSequence === searchSequence) showSocioResults(socios);
                })
                .catch(function (error) {
                    if (requestSequence === searchSequence) showSearchMessage(error.message);
                });
        }, 250);
    });

    socioInput.addEventListener('focus', function () {
        if (socioInput.value.trim().length >= 2 && resultsBox.children.length) {
            resultsBox.style.display = 'block';
        }
    });

    document.addEventListener('click', function (event) {
        if (!socioContainer.contains(event.target)) clearSearchResults();
    });
    $('#modalNovaContribuicao').on('shown.bs.modal', function () {
        setDefaultDates();
        setAlert('info', 'Informe os dados do pagamento.');
        loadPaymentMethods();
        socioInput.focus();
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        alertBox.innerHTML = '';

        const pagamento = document.getElementById('nova-contribuicao-pagamento').value;
        const vencimento = document.getElementById('nova-contribuicao-vencimento').value;
        const geracao = document.getElementById('nova-contribuicao-geracao').value;
        const valor = Number(document.getElementById('nova-contribuicao-valor').value);

        if (!socioIdInput.value || !form.checkValidity() || !Number.isFinite(valor) || valor <= 0) {
            setAlert('warning', 'Preencha todos os campos com valores válidos e selecione um sócio da lista.');
            return;
        }
        if (new Date(pagamento) < new Date(geracao)) {
            setAlert('warning', 'A data de pagamento não pode ser anterior à data de geração.');
            return;
        }

        submitButton.disabled = true;
        submitButton.innerHTML = 'Registrando <i class="fa fa-spinner fa-spin"></i>';
        const payload = {
            id_socio: Number(socioIdInput.value),
            id_meio_pagamento: Number(document.getElementById('nova-contribuicao-meio').value),
            valor: valor,
            data_pagamento: formatDateTime(pagamento),
            data_vencimento: formatDateTime(vencimento),
            data_geracao: formatDateTime(geracao),
            status: document.getElementById('nova-contribuicao-status').value
        };

        try {
            const response = await authenticatedRequest(() => fetch(apiUrl + 'contribuicoes/manual', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Client-Type': 'web'
                },
                body: JSON.stringify(payload)
            }));
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.error || 'Não foi possível registrar a contribuição.');
            $('#modalNovaContribuicao').modal('hide');
            window.location.reload();
        } catch (error) {
            setAlert('danger', error.message);
        } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Registrar contribuição';
        }
    });
}());
