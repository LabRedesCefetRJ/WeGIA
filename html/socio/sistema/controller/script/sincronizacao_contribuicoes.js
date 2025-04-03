const syncBtn = document.getElementById('sync-btn');

async function sincronizar() {
    syncBtn.disabled = true;
    syncBtn.innerHTML = 'Sincronizando <i class="fa fa-spinner fa-spin"></i>';

    const formData = new FormData();
    formData.append('metodo', 'sincronizarStatus');
    formData.append('nomeClasse', 'ContribuicaoLogController');

    try {
        const response = await fetch('../../contribuicao/controller/control.php', {
            method: 'POST',
            body: formData
        });

        // Se a resposta não for OK, força um erro para cair no catch
        if (!response.ok) {
            throw new Error(`Erro na sincronização: ${response.status} ${response.statusText}`);
        }

        const result = await response.text();
        console.log('Resposta do servidor:', result);
        location.reload();
    } catch (error) {
        alert(`${error.message}`);
        console.error(error.message);
    } finally {
        syncBtn.innerHTML = 'Sincronizar pagamentos';
        syncBtn.disabled = false;
    }
}

syncBtn.addEventListener('click', sincronizar);
