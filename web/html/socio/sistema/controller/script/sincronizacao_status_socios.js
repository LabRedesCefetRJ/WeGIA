document.addEventListener('DOMContentLoaded', function () {
  const syncStatusBtn = document.getElementById('btn_sync_status_socios');

  if (!syncStatusBtn) {
    return;
  }

  function setButtonLoading(isLoading) {
    syncStatusBtn.disabled = isLoading;
    syncStatusBtn.innerHTML = isLoading
      ? 'Sincronizando <i class="fa fa-spinner fa-spin"></i>'
      : '<i class="fa fa-refresh"></i> Sincronizar status';
  }

  async function sincronizarStatusSocios() {
    setButtonLoading(true);

    try {
      const response = await fetch('./controller/sincronizar_status_socios.php', {
        method: 'POST',
        headers: {
          Accept: 'application/json',
        },
      });

      if (!response.ok) {
        const text = await response.text();
        throw new Error(`Erro na sincronização: ${response.status} ${response.statusText} ${text}`);
      }

      const result = await response.json();

      if (result.erro) {
        throw new Error(result.erro);
      }

      //alert(result.mensagem || 'Status dos sócios sincronizados com sucesso.');
      location.reload();
    } catch (error) {
      alert(error.message);
      console.error(error);
    } finally {
      setButtonLoading(false);
    }
  }

  syncStatusBtn.addEventListener('click', sincronizarStatusSocios);
});
