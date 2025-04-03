const syncBtn = document.getElementById('sync-btn');

async function sincronizar() {
    syncBtn.disabled = true;
    syncBtn.innerHTML = 'Sincronizando <i class="fa fa-spinner fa-spin"></i>';

    const data = {
        metodo: 'sincronizarStatus',
        nomeClasse: 'ContribuicaoLogController'
    };

    try {
        const response = await fetch('../../contribuicao/controller/control.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) throw new Error('Erro ao sincronizar');

        const result = await response.text();
        console.log('Resposta do servidor:', result);
    } catch (error) {
        console.error(error.message);
        alert(error.message);
    } finally {
        syncBtn.textContent = 'Sincronizar pagamentos';
        syncBtn.disabled = false;
        location.reload();
    }
}

syncBtn.addEventListener('click', sincronizar);
