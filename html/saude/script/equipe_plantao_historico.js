(() => {
  const els = {};
  let historyDataTable = null;

  function cacheElements() {
    els.message = document.getElementById('historicoMessage');
    els.buttonRefresh = document.getElementById('btnAtualizarHistorico');
    els.table = document.getElementById('datatable-historico-plantao');
    els.tableBody = document.getElementById('historicoPlantaoBody');
  }

  function showMessage(message, level = 'info') {
    if (!els.message) {
      return;
    }

    els.message.className = `alert alert-${level} alert-inline show`;
    els.message.textContent = message;

    window.clearTimeout(showMessage._timer);
    showMessage._timer = window.setTimeout(() => {
      els.message.className = 'alert alert-info alert-inline';
      els.message.textContent = '';
    }, 4000);
  }

  async function api(method, payload = {}) {
    const response = await fetch(window.plantaoHistoricoConfig.endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        nomeClasse: window.plantaoHistoricoConfig.nomeClasse,
        metodo: method,
        ...payload
      })
    });

    const json = await response.json().catch(() => ({}));

    if (!response.ok) {
      throw new Error(json.erro || json.mensagem || 'Erro ao processar requisição.');
    }

    if (json.status && json.status !== 'ok') {
      throw new Error(json.mensagem || 'Erro ao processar requisição.');
    }

    return json.dados;
  }

  function safeText(value) {
    return String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function formatDateTime(dateValue) {
    if (!dateValue) {
      return '-';
    }

    const dt = new Date(String(dateValue).replace(' ', 'T'));
    if (Number.isNaN(dt.getTime())) {
      return String(dateValue);
    }

    return `${String(dt.getDate()).padStart(2, '0')}/${String(dt.getMonth() + 1).padStart(2, '0')}/${dt.getFullYear()} ${String(dt.getHours()).padStart(2, '0')}:${String(dt.getMinutes()).padStart(2, '0')}`;
  }

  function buildDownloadUrl(ano, mes) {
    const params = new URLSearchParams({
      ano: String(ano),
      mes: String(mes)
    });

    return `./equipe_plantao_planilha.php?${params.toString()}`;
  }

  function parseHistoryRows(rows) {
    if (!Array.isArray(rows) || !rows.length) {
      return [];
    }

    return rows.map((item) => {
      const ano = Number(item.ano || 0);
      const mes = Number(item.mes || 0);
      const periodo = item.periodo_extenso || item.periodo_label || `${String(mes).padStart(2, '0')}/${ano}`;
      const dias = Number(item.quantidade_dias_com_escala || 0);
      const turnos = Number(item.quantidade_turnos_definidos || 0);
      const status = item.status_label || (Number(item.bloqueada || 0) === 1 ? 'Fechada' : 'Em edição');
      const atualizado = formatDateTime(item.data_atualizacao || item.data_criacao);
      const downloadUrl = buildDownloadUrl(ano, mes);

      return [
        safeText(periodo),
        dias,
        turnos,
        safeText(status),
        safeText(atualizado),
        `<a href="${downloadUrl}" class="btn btn-primary btn-xs historico-action-btn" target="_blank" rel="noopener" title="Baixar planilha" aria-label="Baixar planilha"><i class="fa fa-download"></i></a>`
      ];
    });
  }

  function renderHistoryFallback(rows) {
    if (!Array.isArray(rows) || !rows.length) {
      els.tableBody.innerHTML = '<tr><td colspan="6">Nenhuma escala salva até o momento.</td></tr>';
      return;
    }

    els.tableBody.innerHTML = rows.map((item) => {
      const ano = Number(item.ano || 0);
      const mes = Number(item.mes || 0);
      const periodo = item.periodo_extenso || item.periodo_label || `${String(mes).padStart(2, '0')}/${ano}`;
      const dias = Number(item.quantidade_dias_com_escala || 0);
      const turnos = Number(item.quantidade_turnos_definidos || 0);
      const status = item.status_label || (Number(item.bloqueada || 0) === 1 ? 'Fechada' : 'Em edição');
      const atualizado = formatDateTime(item.data_atualizacao || item.data_criacao);
      const downloadUrl = buildDownloadUrl(ano, mes);

      return `
        <tr>
          <td>${safeText(periodo)}</td>
          <td>${dias}</td>
          <td>${turnos}</td>
          <td>${safeText(status)}</td>
          <td>${safeText(atualizado)}</td>
          <td>
            <a href="${downloadUrl}" class="btn btn-primary btn-xs historico-action-btn" target="_blank" rel="noopener" title="Baixar planilha" aria-label="Baixar planilha">
              <i class="fa fa-download"></i>
            </a>
          </td>
        </tr>
      `;
    }).join('');
  }

  function initDataTable(initialRows = []) {
    if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable || !els.table) {
      return;
    }

    if (els.tableBody) {
      els.tableBody.innerHTML = '';
    }

    historyDataTable = jQuery(els.table).DataTable({
      data: initialRows,
      order: []
    });
  }

  function renderHistory(rows) {
    const parsedRows = parseHistoryRows(rows);

    if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable || !els.table) {
      renderHistoryFallback(rows);
      return;
    }

    if (!historyDataTable) {
      initDataTable(parsedRows);
      return;
    }

    historyDataTable.clear();
    if (parsedRows.length) {
      historyDataTable.rows.add(parsedRows);
    }
    historyDataTable.draw(false);
  }

  async function loadHistory(options = {}) {
    const refreshLabel = els.buttonRefresh ? els.buttonRefresh.innerHTML : '';

    if (els.buttonRefresh) {
      els.buttonRefresh.disabled = true;
      els.buttonRefresh.innerHTML = '<i class="fa fa-refresh fa-spin"></i> Atualizando...';
    }

    try {
      const rows = await api('listarHistoricoEscalasMensais', { limite: 180 });
      renderHistory(rows);

      if (options.notify) {
        showMessage('Histórico atualizado.', 'success');
      }
    } catch (error) {
      renderHistory([]);
      showMessage(error.message || 'Erro ao carregar o histórico de plantões.', 'danger');
    } finally {
      if (els.buttonRefresh) {
        els.buttonRefresh.disabled = false;
        els.buttonRefresh.innerHTML = refreshLabel;
      }
    }
  }

  function bindEvents() {
    if (els.buttonRefresh) {
      els.buttonRefresh.addEventListener('click', () => {
        loadHistory({ notify: true });
      });
    }
  }

  function init() {
    cacheElements();
    bindEvents();
    loadHistory();
  }

  document.addEventListener('DOMContentLoaded', init);
})();
