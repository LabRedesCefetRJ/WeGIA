(function () {
    'use strict';

    const modal = $('#modalImportarContribuicoes');
    const form = document.getElementById('formImportarContribuicoes');
    if (!modal.length || !form) return;

    const fileInput = document.getElementById('importar-contribuicoes-arquivo');
    const yearInput = document.getElementById('importar-contribuicoes-ano');
    const modelInput = document.getElementById('importar-contribuicoes-modelo');
    const paymentInput = document.getElementById('importar-contribuicoes-meio');
    const alertBox = document.getElementById('importar-contribuicoes-alert');
    const submitButton = document.getElementById('enviar-importacao-contribuicoes');
    const apiUrl = typeof apiServer !== 'undefined' ? apiServer : '';
    let importFinished = false;

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function setAlert(type, message, details) {
        let html = '<div class="alert alert-' + type + ' alert-dismissible">'
            + '<button type="button" class="close" data-dismiss="alert">&times;</button>'
            + '<div>' + escapeHtml(message) + '</div>';

        if (details) html += details;
        alertBox.innerHTML = html + '</div>';
    }

    function setDefaultYear() {
        yearInput.value = new Date().getFullYear();
    }

    function resetForm() {
        form.reset();
        setDefaultYear();
        paymentInput.innerHTML = '<option value="">Sem meio de pagamento</option>';
        alertBox.innerHTML = '';
        importFinished = false;
    }

    async function loadPaymentMethods() {
        paymentInput.innerHTML = '<option value="">Carregando...</option>';
        try {
            const response = await authenticatedRequest(() => fetch(apiUrl + 'contribuicoes/payment_methods', {
                credentials: 'include',
                headers: { 'X-Client-Type': 'web' }
            }));
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.error || 'Não foi possível carregar os meios de pagamento.');

            paymentInput.innerHTML = '<option value="">Sem meio de pagamento</option>';
            (result.payment_methods || []).forEach(function (method) {
                const option = document.createElement('option');
                option.value = method.id;
                option.textContent = method.meio;
                paymentInput.appendChild(option);
            });
        } catch (error) {
            paymentInput.innerHTML = '<option value="">Sem meio de pagamento</option>';
            setAlert('danger', error.message || 'Não foi possível carregar os meios de pagamento.');
        }
    }

    function formatRejectedItems(items) {
        if (!Array.isArray(items) || !items.length) return '';

        const rows = items.map(function (item) {
            const location = [item.aba, item.linha, item.mes]
                .filter(value => value !== undefined && value !== null && value !== '')
                .join(' / ');
            return '<li>' + escapeHtml(location || 'Registro') + ': '
                + escapeHtml(item.motivo || 'Dados inválidos.') + '</li>';
        }).join('');

        return '<details><summary>Ver linhas rejeitadas (' + items.length + ')</summary><ul>' + rows + '</ul></details>';
    }

    function showImportResult(result) {
        const summary = result.resultado || {};
        const imported = Number(summary.importados || 0);
        const duplicated = Number(summary.duplicados || 0);
        const rejected = Array.isArray(summary.rejeitados) ? summary.rejeitados : [];
        const details = '<p class="mb-none">Importados: <strong>' + imported
            + '</strong> &middot; Duplicados ignorados: <strong>' + duplicated
            + '</strong> &middot; Rejeitados: <strong>' + rejected.length + '</strong></p>'
            + formatRejectedItems(rejected);

        setAlert(rejected.length ? 'warning' : 'success', result.mensagem || 'Importação processada.', details);
        importFinished = true;
    }

    modal.on('shown.bs.modal', function () {
        if (!importFinished) {
            setDefaultYear();
            setAlert('info', 'Selecione o arquivo e os dados da importação.');
            loadPaymentMethods();
        }
    });

    modal.on('hidden.bs.modal', function () {
        if (importFinished) {
            window.location.reload();
            return;
        }
        resetForm();
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        alertBox.innerHTML = '';

        if (!form.checkValidity() || !fileInput.files.length) {
            setAlert('warning', 'Selecione um arquivo e informe um ano válido.');
            return;
        }

        const file = fileInput.files[0];
        const extension = file.name.toLowerCase().split('.').pop();
        if (['xlsx', 'ods', 'csv'].indexOf(extension) === -1) {
            setAlert('warning', 'O arquivo deve possuir extensão XLSX, ODS ou CSV.');
            return;
        }

        const payload = new FormData();
        payload.append('arquivo', file);
        payload.append('ano', yearInput.value);
        payload.append('modelo', modelInput.value);
        if (paymentInput.value) payload.append('id_meio_pagamento', paymentInput.value);

        submitButton.disabled = true;
        submitButton.innerHTML = 'Importando <i class="fa fa-spinner fa-spin"></i>';

        try {
            const response = await authenticatedRequest(() => fetch(apiUrl + 'contribuicoes/import', {
                method: 'POST',
                credentials: 'include',
                headers: { 'X-Client-Type': 'web' },
                body: payload
            }));
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.error || 'Não foi possível importar as contribuições.');
            showImportResult(result);
        } catch (error) {
            setAlert('danger', error.message || 'Não foi possível importar as contribuições.');
        } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Importar contribuições';
        }
    });
}());
