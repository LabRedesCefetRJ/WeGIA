(() => {
  const TEAM_COLORS = ['#f57c00', '#6a1b9a', '#2e7d32', '#1e3a8a'];
  const TURNOS = ['DIA', 'NOITE'];
  const MONTH_NAMES = ['', 'Janeiro', 'Fevereiro', 'Marco', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

  const state = {
    ano: (window.plantaoConfig && window.plantaoConfig.anoAtual) || new Date().getFullYear(),
    mes: (window.plantaoConfig && window.plantaoConfig.mesAtual) || (new Date().getMonth() + 1),
    equipes: [],
    tecnicos: [],
    dayMap: {},
    selectedDays: new Set(),
    dirty: false,
    locked: false,
    hasSavedScale: false,
    teamColorMap: {},
    calendar: null,
    currentDayModal: null,
    currentTurnoModal: 'DIA',
    loading: false,
    lastLoadedSnapshot: {}
  };

  const els = {};

  function cacheElements() {
    els.msg = document.getElementById('globalMessage');
    els.selMes = document.getElementById('filtroMes');
    els.selAno = document.getElementById('filtroAno');
    els.calendar = document.getElementById('plantaoCalendar');
    els.btnLoad = document.getElementById('btnCarregar');
    els.btnEditScale = document.getElementById('btnEditarEscala');
    els.btnDeleteScale = document.getElementById('btnApagarEscala');
    els.btnSaveScale = document.getElementById('btnSalvarEscala');
    els.btnPreviewPrint = document.getElementById('btnVisualizarImpressao');
    els.btnDirectPrint = document.getElementById('btnImprimirDireto');
    els.btnApplySelected = document.getElementById('btnAplicarEquipeSelecionados');
    els.btnClearSelected = document.getElementById('btnLimparEquipeSelecionados');
    els.btnClearSelection = document.getElementById('btnLimparSelecao');
    els.btnGenerate1236 = document.getElementById('btnGerar12x36');
    els.btnUndoLocal = document.getElementById('btnDesfazerLocal');
    els.btnOpenDay = document.getElementById('btnAbrirDiaSelecionado');

    els.batchTurno = document.getElementById('loteTurno');
    els.batchTeam = document.getElementById('loteEquipe');
    els.scaleTurno = document.getElementById('escala12x36Turno');
    els.scaleStartDay = document.getElementById('escala12x36DiaInicial');
    els.scaleTeamA = document.getElementById('escala12x36EquipeA');
    els.scaleTeamB = document.getElementById('escala12x36EquipeB');

    els.teamTable = document.getElementById('listaEquipesContainer');

    els.modalTeam = $('#modalEquipePlantao');
    els.modalDay = $('#modalDiaPlantao');

    els.teamId = document.getElementById('equipeId');
    els.teamName = document.getElementById('equipeNome');
    els.teamDesc = document.getElementById('equipeDescricao');
    els.teamActive = document.getElementById('equipeAtiva');
    els.teamChecklist = document.getElementById('checkTecnicosEquipe');
    els.teamModalTitle = document.getElementById('tituloModalEquipe');

    els.dayModalTitle = document.getElementById('tituloModalDia');
    els.dayNumber = document.getElementById('modalDiaNumero');
    els.dayDate = document.getElementById('modalDiaData');
    els.dayTurno = document.getElementById('modalDiaTurno');
    els.dayTeam = document.getElementById('modalDiaEquipe');
    els.dayObs = document.getElementById('modalDiaObs');
    els.dayPersistStatus = document.getElementById('statusDiaPersistencia');

    els.listFixos = document.getElementById('listaMembrosFixos');
    els.listPlantao = document.getElementById('listaMembrosPlantao');
    els.listAdd = document.getElementById('listaAdicionadosDia');
    els.listRem = document.getElementById('listaRemovidosDia');
    els.dayLogs = document.getElementById('tabelaLogsDia');

    els.adjustTech = document.getElementById('ajusteTecnico');
    els.adjustType = document.getElementById('ajusteTipo');
    els.adjustObs = document.getElementById('ajusteObservacao');
    els.btnApplyDayLocal = document.getElementById('btnAplicarDiaLocal');
    els.btnClearDayLocal = document.getElementById('btnLimparDiaLocal');
    els.btnSaveDayNow = document.getElementById('btnSalvarDiaAgora');
    els.btnSaveDayAdjustment = document.getElementById('btnSalvarAjusteDia');
    els.btnRemoveDayAdjustment = document.getElementById('btnRemoverAjusteDia');
  }

  function bindEvents() {
    const handlePeriodoChange = () => {
      if (state.dirty) {
        const confirmar = window.confirm('Existem alteracoes locais nao salvas. Deseja descartar e carregar outro periodo?');
        if (!confirmar) {
          els.selMes.value = String(state.mes);
          els.selAno.value = String(state.ano);
          return;
        }
      }

      state.mes = Number(els.selMes.value || state.mes);
      state.ano = Number(els.selAno.value || state.ano);
      loadMonthData();
    };

    els.selMes.addEventListener('change', handlePeriodoChange);
    els.selAno.addEventListener('change', handlePeriodoChange);

    els.btnLoad.addEventListener('click', () => {
      if (state.dirty) {
        const confirmar = window.confirm('Deseja descartar a edicao atual e recarregar a escala salva no banco de dados?');
        if (!confirmar) {
          return;
        }
      }

      loadMonthData();
    });

    els.btnEditScale.addEventListener('click', unlockScaleForEditing);
    els.btnDeleteScale.addEventListener('click', clearScale);
    els.btnSaveScale.addEventListener('click', saveScale);
    els.btnPreviewPrint.addEventListener('click', () => openPrint(false));
    els.btnDirectPrint.addEventListener('click', () => openPrint(true));

    els.btnApplySelected.addEventListener('click', applyTeamToSelectedDays);
    els.btnClearSelected.addEventListener('click', clearTeamFromSelectedDays);
    els.btnClearSelection.addEventListener('click', clearSelection);
    els.btnGenerate1236.addEventListener('click', generate12x36);
    els.btnUndoLocal.addEventListener('click', undoLocalChanges);
    els.btnOpenDay.addEventListener('click', () => {
      if (state.selectedDays.size !== 1) {
        showMessage('Selecione exatamente um dia para abrir a edicao.', 'warning');
        return;
      }

      openDayModal(Array.from(state.selectedDays)[0], selectedTurno());
    });

    document.getElementById('btnNovaEquipe').addEventListener('click', () => openTeamModal());
    document.getElementById('btnSalvarEquipeModal').addEventListener('click', saveTeamModal);

    els.teamTable.addEventListener('click', (event) => {
      const btn = event.target.closest('button[data-action]');
      if (!btn) {
        return;
      }

      const action = btn.getAttribute('data-action');
      const idEquipe = Number(btn.getAttribute('data-id') || 0);
      if (idEquipe <= 0) {
        return;
      }

      if (action === 'edit') {
        openTeamModal(idEquipe);
        return;
      }

      if (action === 'toggle') {
        const ativoAtual = Number(btn.getAttribute('data-ativo') || 0) === 1;
        toggleTeamStatus(idEquipe, ativoAtual);
      }
    });

    els.dayTurno.addEventListener('change', () => {
      if (!state.currentDayModal) {
        return;
      }
      const turno = normalizeTurno(els.dayTurno.value || 'DIA');
      if (isInactiveAssignedShift(state.currentDayModal, turno)) {
        showMessage('Este turno esta bloqueado porque a equipe vinculada esta inativa. Substitua por uma equipe ativa via lote para voltar a usar.', 'warning');
      }
      state.currentTurnoModal = turno;
      loadDayDetails(state.currentDayModal, turno);
    });

    els.btnApplyDayLocal.addEventListener('click', applyDayChangesLocal);
    els.btnClearDayLocal.addEventListener('click', clearDayLocal);
    els.btnSaveDayNow.addEventListener('click', saveDayImmediately);
    els.btnSaveDayAdjustment.addEventListener('click', saveDayAdjustment);
    els.btnRemoveDayAdjustment.addEventListener('click', removeDayAdjustment);
  }

  function showMessage(message, level = 'info') {
    if (!els.msg) {
      return;
    }

    els.msg.className = `alert alert-${level} alert-inline show`;
    els.msg.textContent = message;

    window.clearTimeout(showMessage._timer);
    showMessage._timer = window.setTimeout(() => {
      els.msg.className = 'alert alert-info alert-inline';
      els.msg.textContent = '';
    }, 4500);
  }

  async function api(method, payload = {}) {
    const response = await fetch(window.plantaoConfig.endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        nomeClasse: window.plantaoConfig.nomeClasse,
        metodo: method,
        ...payload
      })
    });

    const json = await response.json().catch(() => ({}));

    if (!response.ok) {
      throw new Error(json.erro || json.mensagem || 'Erro ao processar requisicao.');
    }

    if (json.status && json.status !== 'ok') {
      throw new Error(json.mensagem || 'Erro ao processar requisicao.');
    }

    return json.dados;
  }

  function totalDaysInMonth() {
    return new Date(state.ano, state.mes, 0).getDate();
  }

  function buildDateIso(day) {
    return `${String(state.ano).padStart(4, '0')}-${String(state.mes).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
  }

  function nextDateIso(dateIso) {
    const dt = new Date(`${dateIso}T00:00:00`);
    dt.setDate(dt.getDate() + 1);
    return `${dt.getFullYear()}-${String(dt.getMonth() + 1).padStart(2, '0')}-${String(dt.getDate()).padStart(2, '0')}`;
  }

  function dayFromIso(dateIso) {
    const parts = String(dateIso).split('-');
    return Number(parts[2] || 0);
  }

  function safeText(value) {
    return String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function formatDateLabel(day) {
    return `${String(day).padStart(2, '0')}/${String(state.mes).padStart(2, '0')}/${state.ano}`;
  }

  function formatDateTime(dateValue) {
    if (!dateValue) {
      return '';
    }

    const dt = new Date(String(dateValue).replace(' ', 'T'));
    if (Number.isNaN(dt.getTime())) {
      return dateValue;
    }

    return `${String(dt.getDate()).padStart(2, '0')}/${String(dt.getMonth() + 1).padStart(2, '0')}/${dt.getFullYear()} ${String(dt.getHours()).padStart(2, '0')}:${String(dt.getMinutes()).padStart(2, '0')}:${String(dt.getSeconds()).padStart(2, '0')}`;
  }

  function normalizeTurno(turno) {
    return String(turno || 'DIA').toUpperCase() === 'NOITE' ? 'NOITE' : 'DIA';
  }

  function turnoLabel(turno) {
    return normalizeTurno(turno) === 'DIA' ? 'Dia 07:00-19:00' : 'Noite 19:00-07:00';
  }

  function turnoShortLabel(turno) {
    return normalizeTurno(turno) === 'DIA' ? 'Dia' : 'Noite';
  }

  function emptyShift(turno) {
    return {
      turno,
      turno_label: turnoLabel(turno),
      faixa_horario: normalizeTurno(turno) === 'DIA' ? '07:00 às 19:00' : '19:00 às 07:00',
      id_escala_dia: null,
      id_equipe_plantao: 0,
      equipe_nome: 'Nao definida',
      equipe_ativa: null,
      observacao: '',
      quantidade_membros: 0,
      membros_plantao: []
    };
  }

  function teamById(idEquipe) {
    return state.equipes.find((item) => Number(item.id_equipe_plantao) === Number(idEquipe)) || null;
  }

  function shiftInfo(day, turno) {
    const infoDia = state.dayMap[day] || null;
    if (!infoDia) {
      return emptyShift(normalizeTurno(turno));
    }

    const normalizedTurno = normalizeTurno(turno);
    return infoDia.turnos[normalizedTurno] || emptyShift(normalizedTurno);
  }

  function isInactiveAssignedShift(day, turno) {
    const info = shiftInfo(day, turno);
    return Number(info.id_equipe_plantao || 0) > 0 && Number(info.equipe_ativa || 0) !== 1;
  }

  function hasAnyInactiveShift(day) {
    return TURNOS.some((turno) => isInactiveAssignedShift(day, turno));
  }

  function hasAnyActiveShift(day) {
    return TURNOS.some((turno) => {
      const info = shiftInfo(day, turno);
      return Number(info.id_equipe_plantao || 0) > 0 && Number(info.equipe_ativa || 0) === 1;
    });
  }

  function hasAnyTeam(day) {
    return TURNOS.some((turno) => Number(shiftInfo(day, turno).id_equipe_plantao || 0) > 0);
  }

  function allAssignedShiftsInactive(day) {
    return hasAnyInactiveShift(day) && !hasAnyActiveShift(day);
  }

  function selectedTurno() {
    return normalizeTurno((els.batchTurno && els.batchTurno.value) || 'DIA');
  }

  function seededRandomFactory(seed) {
    let value = seed % 2147483647;
    if (value <= 0) {
      value += 2147483646;
    }

    return () => {
      value = (value * 16807) % 2147483647;
      return (value - 1) / 2147483646;
    };
  }

  function buildShuffledPalette() {
    const palette = TEAM_COLORS.slice();
    const random = seededRandomFactory((state.ano * 100) + state.mes + Math.max(state.equipes.length, 1));

    for (let index = palette.length - 1; index > 0; index -= 1) {
      const swapIndex = Math.floor(random() * (index + 1));
      [palette[index], palette[swapIndex]] = [palette[swapIndex], palette[index]];
    }

    return palette;
  }

  function rebuildTeamColorMap() {
    const map = {};
    const palette = buildShuffledPalette();
    const teamIds = state.equipes
      .map((item) => Number(item.id_equipe_plantao || 0))
      .filter((id) => id > 0)
      .sort((left, right) => left - right);

    teamIds.forEach((idEquipe, index) => {
      map[idEquipe] = palette[index % palette.length];
    });

    state.teamColorMap = map;
  }

  function teamColor(idEquipe) {
    if (!idEquipe || Number(idEquipe) <= 0) {
      return '#d9534f';
    }

    return state.teamColorMap[Number(idEquipe)] || TEAM_COLORS[0];
  }

  function teamTextColor(backgroundColor) {
    const color = String(backgroundColor || '').replace('#', '');
    if (color.length !== 6) {
      return '#ffffff';
    }

    const red = parseInt(color.slice(0, 2), 16);
    const green = parseInt(color.slice(2, 4), 16);
    const blue = parseInt(color.slice(4, 6), 16);
    const luminance = ((red * 299) + (green * 587) + (blue * 114)) / 1000;

    return luminance >= 150 ? '#25384a' : '#ffffff';
  }

  function setupMonthYearSelectors() {
    if (els.selMes.options.length === 0) {
      for (let month = 1; month <= 12; month += 1) {
        const option = document.createElement('option');
        option.value = String(month);
        option.textContent = MONTH_NAMES[month];
        els.selMes.appendChild(option);
      }
    }

    if (els.selAno.options.length === 0) {
      for (let year = state.ano - 2; year <= state.ano + 5; year += 1) {
        const option = document.createElement('option');
        option.value = String(year);
        option.textContent = String(year);
        els.selAno.appendChild(option);
      }
    }

    els.selMes.value = String(state.mes);
    els.selAno.value = String(state.ano);
  }

  function snapshotDayMap() {
    state.lastLoadedSnapshot = JSON.parse(JSON.stringify(state.dayMap));
  }

  function restoreSnapshot() {
    state.dayMap = JSON.parse(JSON.stringify(state.lastLoadedSnapshot || {}));
  }

  function buildDayMap(rawDays) {
    const total = totalDaysInMonth();
    const map = {};

    for (let day = 1; day <= total; day += 1) {
      map[day] = {
        dia: day,
        turnos: {
          DIA: emptyShift('DIA'),
          NOITE: emptyShift('NOITE')
        }
      };
    }

    (Array.isArray(rawDays) ? rawDays : []).forEach((item) => {
      const day = Number(item.dia || 0);
      if (day < 1 || day > total) {
        return;
      }

      const turnos = item.turnos || {};
      TURNOS.forEach((turno) => {
        const current = turnos[turno] || emptyShift(turno);
        map[day].turnos[turno] = {
          turno,
          turno_label: current.turno_label || turnoLabel(turno),
          faixa_horario: current.faixa_horario || emptyShift(turno).faixa_horario,
          id_escala_dia: current.id_escala_dia || null,
          id_equipe_plantao: Number(current.id_equipe_plantao || 0),
          equipe_nome: current.equipe_nome || 'Nao definida',
          equipe_ativa: current.equipe_ativa === null || typeof current.equipe_ativa === 'undefined'
            ? null
            : Number(current.equipe_ativa),
          observacao: String(current.observacao || ''),
          quantidade_membros: Number(current.quantidade_membros || 0),
          membros_plantao: Array.isArray(current.membros_plantao) ? current.membros_plantao : []
        };
      });
    });

    state.dayMap = map;
    state.selectedDays = new Set();
  }

  function markDirty(isDirty) {
    state.dirty = !!isDirty;
  }

  function setDisabled(element, disabled) {
    if (element) {
      element.disabled = !!disabled;
    }
  }

  function currentModalShiftBlocked() {
    if (!state.currentDayModal) {
      return false;
    }
    return isInactiveAssignedShift(state.currentDayModal, state.currentTurnoModal);
  }

  function updateDayModalLockUi() {
    const blocked = currentModalShiftBlocked();
    const locked = !!state.locked || blocked;

    [els.dayTeam, els.dayObs, els.adjustTech, els.adjustType, els.adjustObs].forEach((field) => setDisabled(field, locked));
    [els.btnApplyDayLocal, els.btnClearDayLocal, els.btnSaveDayNow, els.btnSaveDayAdjustment, els.btnRemoveDayAdjustment].forEach((button) => setDisabled(button, locked));
  }

  function updateScaleLockUi() {
    const locked = !!state.locked;
    [els.batchTurno, els.batchTeam, els.scaleTurno, els.scaleStartDay, els.scaleTeamA, els.scaleTeamB].forEach((field) => setDisabled(field, locked));
    [els.btnSaveScale, els.btnApplySelected, els.btnClearSelected, els.btnClearSelection, els.btnGenerate1236, els.btnUndoLocal, els.btnOpenDay].forEach((button) => setDisabled(button, locked));

    setDisabled(els.btnEditScale, !state.hasSavedScale || !locked);
    setDisabled(els.btnDeleteScale, !state.hasSavedScale || locked);
    els.btnLoad.style.display = state.hasSavedScale && !locked ? '' : 'none';

    document.body.classList.toggle('scale-locked', locked);
    els.calendar.classList.toggle('is-readonly', locked);
    updateDayModalLockUi();
  }

  function ensureScaleEditable(message = 'A escala deste mes esta bloqueada. Clique em Editar escala para liberar alteracoes.') {
    if (!state.locked) {
      return true;
    }

    showMessage(message, 'warning');
    return false;
  }

  function renderSummary() {
    return;
  }

  function buildTeamOptionHtml(currentValue = 0, includeZero = true) {
    const options = [];
    if (includeZero) {
      options.push('<option value="0">Sem equipe</option>');
    }

    state.equipes.forEach((team) => {
      const teamId = Number(team.id_equipe_plantao || 0);
      const active = Number(team.ativo || 0) === 1;
      const selected = teamId === Number(currentValue || 0) ? ' selected' : '';
      const disabled = !active ? ' disabled' : '';
      const label = `${safeText(team.nome)}${active ? '' : ' (Inativa)'}`;
      options.push(`<option value="${teamId}"${selected}${disabled}>${label}</option>`);
    });

    return options.join('');
  }

  function renderTeamSelectors() {
    els.batchTeam.innerHTML = buildTeamOptionHtml(0, true);
    els.scaleTeamA.innerHTML = buildTeamOptionHtml(0, true);
    els.scaleTeamB.innerHTML = buildTeamOptionHtml(0, true);
    els.dayTeam.innerHTML = buildTeamOptionHtml(0, true);

    els.scaleStartDay.innerHTML = '';
    for (let day = 1; day <= totalDaysInMonth(); day += 1) {
      const option = document.createElement('option');
      option.value = String(day);
      option.textContent = String(day);
      els.scaleStartDay.appendChild(option);
    }

    els.adjustTech.innerHTML = '<option value="0">Selecione um tecnico</option>';
    state.tecnicos.forEach((tech) => {
      const option = document.createElement('option');
      option.value = String(tech.id_funcionario);
      option.textContent = `${tech.nome_completo} (${tech.cargo})`;
      els.adjustTech.appendChild(option);
    });
  }

  function buildTeamUsageMap() {
    const usage = {};
    Object.keys(state.dayMap).forEach((dayKey) => {
      TURNOS.forEach((turno) => {
        const idEquipe = Number(shiftInfo(Number(dayKey), turno).id_equipe_plantao || 0);
        if (idEquipe > 0) {
          usage[idEquipe] = (usage[idEquipe] || 0) + 1;
        }
      });
    });
    return usage;
  }

  function renderTeamsTable() {
    if (!state.equipes.length) {
      els.teamTable.innerHTML = '<p>Nenhuma equipe cadastrada.</p>';
      return;
    }

    const usage = buildTeamUsageMap();

    const rows = state.equipes.map((team) => {
      const teamId = Number(team.id_equipe_plantao || 0);
      const active = Number(team.ativo || 0) === 1;
      const color = teamColor(teamId);
      const uso = usage[teamId] || 0;

      return `
        <tr>
          <td class="table-team-name">
            <div class="team-name-row">
              <span class="team-color-chip" style="background:${color}"></span>
              <span class="team-name">${safeText(team.nome)}</span>
              <span class="team-days-meta">${uso} plantao(s)</span>
            </div>
          </td>
          <td class="muted-cell">${Number(team.quantidade_membros || 0)} membro(s)</td>
          <td class="team-status-text">${active ? 'Ativo' : 'Inativo'}</td>
          <td class="table-team-actions">
            <div class="team-inline-actions">
              <button type="button" class="btn btn-warning btn-xs" data-action="edit" data-id="${teamId}">Editar</button>
              <button type="button" class="btn btn-primary btn-xs" data-action="toggle" data-id="${teamId}" data-ativo="${active ? 1 : 0}">${active ? 'Inativar' : 'Ativar'}</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');

    els.teamTable.innerHTML = `
      <div class="table-responsive">
        <table class="table table-bordered table-striped mb-none table-teams">
          <thead>
            <tr>
              <th>Equipe</th>
              <th>Membros</th>
              <th>Status</th>
              <th>Acoes</th>
            </tr>
          </thead>
          <tbody>${rows}</tbody>
        </table>
      </div>
    `;
  }

  function renderLegend() {
    return;
  }

  function createCalendarIfNeeded() {
    if (state.calendar) {
      return;
    }

    if (typeof FullCalendar === 'undefined') {
      showMessage('FullCalendar nao foi carregado. Verifique a conexao de rede ou os assets.', 'danger');
      return;
    }

    state.calendar = new FullCalendar.Calendar(els.calendar, {
      initialView: 'dayGridMonth',
      locale: 'pt-br',
      height: 'auto',
      fixedWeekCount: false,
      selectable: true,
      dayMaxEvents: 3,
      headerToolbar: {
        left: '',
        center: 'title',
        right: ''
      },
      select: (info) => {
        if (state.locked) {
          state.calendar.unselect();
          showMessage('A escala esta bloqueada. Clique em Editar escala para liberar alteracoes.', 'warning');
          return;
        }

        addSelectionRange(info.startStr, info.endStr);
        state.calendar.unselect();
      },
      dateClick: (info) => {
        if (info.date.getFullYear() !== state.ano || (info.date.getMonth() + 1) !== state.mes) {
          return;
        }
        const day = dayFromIso(info.dateStr);
        if (state.locked) {
          openDayModal(day, selectedTurno());
          return;
        }
        toggleDaySelection(day);
      },
      eventClick: (info) => {
        const day = Number(info.event.extendedProps.day || 0);
        const turno = normalizeTurno(info.event.extendedProps.turno || 'DIA');

        if (day <= 0) {
          return;
        }

        if (isInactiveAssignedShift(day, turno)) {
          showMessage('Este turno esta bloqueado porque a equipe vinculada esta inativa. Substitua por uma equipe ativa para voltar a usar.', 'warning');
          return;
        }

        openDayModal(day, turno);
      }
    });

    state.calendar.render();
  }

  function buildCalendarEvents() {
    const events = [];
    const total = totalDaysInMonth();

    for (let day = 1; day <= total; day += 1) {
      const start = buildDateIso(day);

      TURNOS.forEach((turno) => {
        const info = shiftInfo(day, turno);
        const idEquipe = Number(info.id_equipe_plantao || 0);
        if (idEquipe <= 0) {
          return;
        }

        const color = teamColor(idEquipe);
        const textColor = teamTextColor(color);
        const inactiveAssignment = isInactiveAssignedShift(day, turno);

        events.push({
          id: `team-${day}-${turno}`,
          start,
          end: nextDateIso(start),
          allDay: true,
          title: `${turnoShortLabel(turno)} · ${info.equipe_nome || 'Equipe'}`,
          classNames: ['event-team', `event-${turno.toLowerCase()}`, inactiveAssignment ? 'event-inactive-assignment' : ''],
          backgroundColor: color,
          borderColor: color,
          textColor,
          extendedProps: {
            kind: 'team',
            day,
            turno
          }
        });
      });

      if (state.selectedDays.has(day)) {
        events.push({
          id: `selection-${day}`,
          start,
          end: nextDateIso(start),
          allDay: true,
          display: 'background',
          classNames: ['event-selected-overlay'],
          extendedProps: {
            kind: 'selection',
            day
          }
        });
      }
    }

    return events;
  }

  function applyDayCellClasses() {
    const cells = els.calendar.querySelectorAll('.fc-daygrid-day[data-date]');

    cells.forEach((cell) => {
      const dateIso = cell.getAttribute('data-date') || '';
      const parts = dateIso.split('-');
      const cellYear = Number(parts[0] || 0);
      const cellMonth = Number(parts[1] || 0);
      const day = Number(parts[2] || 0);

      cell.classList.remove('day-empty', 'day-filled', 'day-selected', 'day-disabled');

      if (cellYear !== state.ano || cellMonth !== state.mes) {
        return;
      }

      if (hasAnyTeam(day)) {
        cell.classList.add('day-filled');
      } else {
        cell.classList.add('day-empty');
      }

      if (allAssignedShiftsInactive(day)) {
        cell.classList.add('day-disabled');
      }

      if (state.selectedDays.has(day)) {
        cell.classList.add('day-selected');
      }
    });
  }

  function renderCalendar() {
    createCalendarIfNeeded();
    if (!state.calendar) {
      return;
    }

    const targetDate = `${state.ano}-${String(state.mes).padStart(2, '0')}-01`;
    state.calendar.gotoDate(targetDate);
    state.calendar.removeAllEvents();
    state.calendar.addEventSource(buildCalendarEvents());

    window.setTimeout(applyDayCellClasses, 0);
  }

  function toggleDaySelection(day) {
    if (!ensureScaleEditable()) {
      return;
    }

    if (day < 1 || day > totalDaysInMonth()) {
      return;
    }

    if (state.selectedDays.has(day)) {
      state.selectedDays.delete(day);
    } else {
      state.selectedDays.add(day);
    }

    renderCalendar();
  }

  function addSelectionRange(startStr, endStrExclusive) {
    if (!ensureScaleEditable()) {
      return;
    }

    const startDate = new Date(`${startStr}T00:00:00`);
    const endDate = new Date(`${endStrExclusive}T00:00:00`);

    for (let dt = new Date(startDate); dt < endDate; dt.setDate(dt.getDate() + 1)) {
      if (dt.getMonth() + 1 !== state.mes || dt.getFullYear() !== state.ano) {
        continue;
      }
      state.selectedDays.add(dt.getDate());
    }

    renderCalendar();
  }

  function clearSelection() {
    if (!ensureScaleEditable()) {
      return;
    }

    state.selectedDays = new Set();
    renderCalendar();
  }

  function applyTeamToSelectedDays() {
    if (!ensureScaleEditable()) {
      return;
    }

    if (!state.selectedDays.size) {
      showMessage('Selecione ao menos um dia no calendario.', 'warning');
      return;
    }

    const turno = selectedTurno();
    const idEquipe = Number(els.batchTeam.value || 0);
    if (idEquipe <= 0) {
      showMessage('Escolha uma equipe para aplicar.', 'warning');
      return;
    }

    const team = teamById(idEquipe);
    state.selectedDays.forEach((day) => {
      state.dayMap[day].turnos[turno] = {
        ...state.dayMap[day].turnos[turno],
        id_equipe_plantao: idEquipe,
        equipe_ativa: 1,
        equipe_nome: team ? team.nome : 'Nao definida',
        quantidade_membros: team ? Number(team.quantidade_membros || 0) : 0
      };
    });

    markDirty(true);
    renderCalendar();
    renderTeamsTable();
  }

  function clearTeamFromSelectedDays() {
    if (!ensureScaleEditable()) {
      return;
    }

    if (!state.selectedDays.size) {
      showMessage('Selecione dias antes de limpar.', 'warning');
      return;
    }

    const turno = selectedTurno();
    state.selectedDays.forEach((day) => {
      state.dayMap[day].turnos[turno] = {
        ...state.dayMap[day].turnos[turno],
        id_equipe_plantao: 0,
        equipe_ativa: null,
        equipe_nome: 'Nao definida',
        observacao: '',
        quantidade_membros: 0,
        membros_plantao: []
      };
    });

    markDirty(true);
    renderCalendar();
    renderTeamsTable();
  }

  function generate12x36() {
    if (!ensureScaleEditable()) {
      return;
    }

    const turno = normalizeTurno(els.scaleTurno.value || 'DIA');
    const idEquipeA = Number(els.scaleTeamA.value || 0);
    const idEquipeB = Number(els.scaleTeamB.value || 0);
    const startDay = Number(els.scaleStartDay.value || 1);

    if (idEquipeA <= 0 || idEquipeB <= 0) {
      showMessage('Selecione as duas equipes para gerar o 12x36.', 'warning');
      return;
    }

    if (idEquipeA === idEquipeB) {
      showMessage('No 12x36 as equipes devem ser diferentes.', 'warning');
      return;
    }

    const teamA = teamById(idEquipeA);
    const teamB = teamById(idEquipeB);

    for (let day = 1; day <= totalDaysInMonth(); day += 1) {
      const useA = Math.abs(day - startDay) % 2 === 0;
      const team = useA ? teamA : teamB;
      const teamId = useA ? idEquipeA : idEquipeB;

      state.dayMap[day].turnos[turno] = {
        ...state.dayMap[day].turnos[turno],
        id_equipe_plantao: teamId,
        equipe_ativa: 1,
        equipe_nome: team ? team.nome : 'Nao definida',
        quantidade_membros: team ? Number(team.quantidade_membros || 0) : 0
      };
    }

    state.selectedDays = new Set();
    markDirty(true);
    renderCalendar();
    renderTeamsTable();
    showMessage(`Escala 12x36 aplicada ao turno ${turnoShortLabel(turno).toLowerCase()}. Clique em Salvar escala para persistir.`, 'success');
  }

  function undoLocalChanges() {
    if (!ensureScaleEditable()) {
      return;
    }

    restoreSnapshot();
    state.selectedDays = new Set();
    markDirty(false);
    renderCalendar();
    renderTeamsTable();
    showMessage('Alteracoes locais desfeitas.', 'info');
  }

  function buildPayloadDays() {
    const payload = {};

    for (let day = 1; day <= totalDaysInMonth(); day += 1) {
      payload[day] = { turnos: {} };
      TURNOS.forEach((turno) => {
        const info = shiftInfo(day, turno);
        payload[day].turnos[turno] = {
          id_equipe_plantao: isInactiveAssignedShift(day, turno) ? 0 : Number(info.id_equipe_plantao || 0),
          observacao: String(info.observacao || '')
        };
      });
    }

    return payload;
  }

  async function saveScale() {
    if (!ensureScaleEditable()) {
      return;
    }

    try {
      await api('salvarEscalaMensal', {
        ano: state.ano,
        mes: state.mes,
        dias: buildPayloadDays()
      });

      showMessage('Escala salva e bloqueada com sucesso.', 'success');
      await loadMonthData();
    } catch (error) {
      showMessage(error.message || 'Erro ao salvar escala.', 'danger');
    }
  }

  async function unlockScaleForEditing() {
    if (!state.hasSavedScale || !state.locked) {
      return;
    }

    const confirmacao = window.confirm('Liberar esta escala para edicao? Apos liberar, novas alteracoes poderao ser feitas ate o proximo salvamento.');
    if (!confirmacao) {
      return;
    }

    try {
      await api('alterarBloqueioEscalaMensal', {
        ano: state.ano,
        mes: state.mes,
        bloqueada: 0
      });

      showMessage('Edicao da escala liberada.', 'success');
      await loadMonthData();
    } catch (error) {
      showMessage(error.message || 'Erro ao liberar edicao da escala.', 'danger');
    }
  }

  async function clearScale() {
    if (!state.hasSavedScale) {
      showMessage('Nao existe escala salva para apagar neste mes.', 'warning');
      return;
    }

    if (!ensureScaleEditable('A escala deste mes esta bloqueada. Clique em Editar escala antes de apagar.')) {
      return;
    }

    const confirmacao = window.confirm('Apagar toda a escala deste mes? Esta acao remove os turnos configurados da escala.');
    if (!confirmacao) {
      return;
    }

    try {
      await api('limparEscalaMensal', {
        ano: state.ano,
        mes: state.mes
      });

      showMessage('Escala apagada com sucesso.', 'success');
      await loadMonthData();
    } catch (error) {
      showMessage(error.message || 'Erro ao apagar escala.', 'danger');
    }
  }

  function openPrint(autoPrint) {
    const params = new URLSearchParams({
      ano: String(state.ano),
      mes: String(state.mes),
      formato: 'calendario'
    });

    if (autoPrint) {
      params.set('auto_print', '1');
    }

    window.open(`./equipe_plantao_impressao.php?${params.toString()}`, '_blank');
  }

  function openTeamModal(idEquipe = null) {
    if (!idEquipe) {
      els.teamModalTitle.textContent = 'Cadastro de equipe';
      els.teamId.value = '';
      els.teamName.value = '';
      els.teamDesc.value = '';
      els.teamActive.checked = true;
      renderTechChecklist([]);
      els.modalTeam.modal('show');
      return;
    }

    api('buscarEquipe', { id_equipe_plantao: Number(idEquipe) })
      .then((team) => {
        els.teamModalTitle.textContent = `Editar equipe: ${team.nome}`;
        els.teamId.value = String(team.id_equipe_plantao || '');
        els.teamName.value = String(team.nome || '');
        els.teamDesc.value = String(team.descricao || '');
        els.teamActive.checked = Number(team.ativo) === 1;
        renderTechChecklist((team.membros || []).map((item) => Number(item.id_funcionario)));
        els.modalTeam.modal('show');
      })
      .catch((error) => {
        showMessage(error.message || 'Erro ao carregar equipe.', 'danger');
      });
  }

  function renderTechChecklist(selectedIds) {
    if (!state.tecnicos.length) {
      els.teamChecklist.innerHTML = '<p>Nenhum tecnico disponivel.</p>';
      return;
    }

    els.teamChecklist.innerHTML = state.tecnicos.map((tech) => {
      const checked = selectedIds.includes(Number(tech.id_funcionario)) ? 'checked' : '';
      return `
        <div class="checkbox" style="margin:4px 0;">
          <label>
            <input type="checkbox" class="check-tecnico-equipe" value="${Number(tech.id_funcionario)}" ${checked}>
            ${safeText(tech.nome_completo)} (${safeText(tech.cargo)})
          </label>
        </div>
      `;
    }).join('');
  }

  async function saveTeamModal() {
    try {
      const idEquipe = Number(els.teamId.value || 0);
      const nome = els.teamName.value.trim();
      if (!nome) {
        showMessage('Informe o nome da equipe.', 'warning');
        return;
      }

      const membros = Array.from(document.querySelectorAll('.check-tecnico-equipe:checked')).map((item) => Number(item.value));

      await api('salvarEquipe', {
        id_equipe_plantao: idEquipe > 0 ? idEquipe : null,
        nome,
        descricao: els.teamDesc.value.trim(),
        ativo: !!els.teamActive.checked,
        membros
      });

      els.modalTeam.modal('hide');
      showMessage('Equipe salva com sucesso.', 'success');
      await loadMonthData();
    } catch (error) {
      showMessage(error.message || 'Erro ao salvar equipe.', 'danger');
    }
  }

  async function toggleTeamStatus(idEquipe, activeNow) {
    try {
      await api('alterarStatusEquipe', {
        id_equipe_plantao: Number(idEquipe),
        ativo: activeNow ? 0 : 1
      });

      showMessage('Status da equipe atualizado.', 'success');
      await loadMonthData();
    } catch (error) {
      showMessage(error.message || 'Erro ao alterar status da equipe.', 'danger');
    }
  }

  function fillList(element, items) {
    if (!items || !items.length) {
      element.innerHTML = '<li>-</li>';
      return;
    }

    element.innerHTML = items.map((item) => `<li>${safeText(item.nome_completo || '')}</li>`).join('');
  }

  function renderLogs(logs) {
    if (!logs || !logs.length) {
      els.dayLogs.innerHTML = '<tr><td colspan="4">Sem logs para este turno.</td></tr>';
      return;
    }

    els.dayLogs.innerHTML = logs.map((log) => `
      <tr>
        <td>${safeText(formatDateTime(log.data_hora || ''))}</td>
        <td>${safeText(log.acao || '')}</td>
        <td>${safeText(log.usuario_nome || '')}</td>
        <td>${safeText(log.descricao || '')}</td>
      </tr>
    `).join('');
  }

  function refreshModalTurnoOptions(day, preferredTurno) {
    const available = TURNOS.filter((turno) => !isInactiveAssignedShift(day, turno));
    let current = normalizeTurno(preferredTurno || 'DIA');

    if (!available.length) {
      current = normalizeTurno(preferredTurno || 'DIA');
    } else if (!available.includes(current)) {
      current = available[0];
    }

    els.dayTurno.innerHTML = TURNOS.map((turno) => {
      const disabled = isInactiveAssignedShift(day, turno) ? ' disabled' : '';
      const selected = turno === current ? ' selected' : '';
      return `<option value="${turno}"${selected}${disabled}>${turnoLabel(turno)}</option>`;
    }).join('');

    state.currentTurnoModal = current;
    return current;
  }

  async function openDayModal(day, turno = 'DIA') {
    if (!state.dayMap[day]) {
      return;
    }

    if (TURNOS.every((item) => isInactiveAssignedShift(day, item))) {
      showMessage('Os dois turnos deste dia estao vinculados a equipes inativas. Substitua via lote para voltar a usar.', 'warning');
      return;
    }

    state.currentDayModal = day;
    els.dayModalTitle.textContent = `Edicao do dia ${formatDateLabel(day)}`;
    els.dayNumber.value = String(day);
    els.dayDate.value = formatDateLabel(day);

    const turnoAtivo = refreshModalTurnoOptions(day, turno);
    fillList(els.listFixos, []);
    fillList(els.listPlantao, []);
    fillList(els.listAdd, []);
    fillList(els.listRem, []);
    renderLogs([]);
    els.dayPersistStatus.textContent = 'Carregando dados operacionais do turno...';
    updateDayModalLockUi();
    els.modalDay.modal('show');

    await loadDayDetails(day, turnoAtivo);
  }

  async function loadDayDetails(day, turno) {
    const normalizedTurno = normalizeTurno(turno);
    const dayInfo = shiftInfo(day, normalizedTurno);
    state.currentTurnoModal = normalizedTurno;

    els.dayTurno.value = normalizedTurno;
    els.dayTeam.innerHTML = buildTeamOptionHtml(dayInfo.id_equipe_plantao || 0, true);
    els.dayTeam.value = String(dayInfo.id_equipe_plantao || 0);
    els.dayObs.value = dayInfo.observacao || '';
    updateDayModalLockUi();

    try {
      const details = await api('detalharDia', {
        ano: state.ano,
        mes: state.mes,
        dia: Number(day),
        turno: normalizedTurno
      });

      els.dayTeam.innerHTML = buildTeamOptionHtml(details.id_equipe_plantao || 0, true);
      els.dayTeam.value = String(details.id_equipe_plantao || 0);
      els.dayObs.value = details.observacao || '';

      if (Number(details.id_equipe_plantao || 0) <= 0) {
        els.dayPersistStatus.textContent = state.locked
          ? `${details.turno_label || turnoLabel(normalizedTurno)} sem equipe persistida. Escala em leitura.`
          : `${details.turno_label || turnoLabel(normalizedTurno)} ainda nao persistido no banco.`;
        fillList(els.listFixos, []);
        fillList(els.listPlantao, []);
        fillList(els.listAdd, []);
        fillList(els.listRem, []);
        renderLogs([]);
        updateDayModalLockUi();
        return;
      }

      els.dayPersistStatus.textContent = `${details.turno_label || turnoLabel(normalizedTurno)} | ${details.equipe_nome || 'Nao definida'} | ${details.faixa_horario || ''}${state.locked ? ' | Escala em leitura' : ''}`;
      fillList(els.listFixos, details.membros_fixos || []);
      fillList(els.listPlantao, details.membros_plantao || []);
      fillList(els.listAdd, details.adicionados || []);
      fillList(els.listRem, details.removidos || []);
      renderLogs(details.logs || []);
      updateDayModalLockUi();
    } catch (error) {
      els.dayPersistStatus.textContent = 'Nao foi possivel carregar os detalhes operacionais deste turno.';
      showMessage(error.message || 'Erro ao detalhar turno.', 'warning');
    }
  }

  function applyDayChangesLocal() {
    if (!ensureScaleEditable()) {
      return;
    }

    const day = Number(els.dayNumber.value || 0);
    const turno = normalizeTurno(els.dayTurno.value || 'DIA');
    if (day <= 0 || !state.dayMap[day]) {
      return;
    }

    const idEquipe = Number(els.dayTeam.value || 0);
    const team = teamById(idEquipe);

    state.dayMap[day].turnos[turno] = {
      ...state.dayMap[day].turnos[turno],
      id_equipe_plantao: idEquipe,
      equipe_ativa: idEquipe > 0 ? 1 : null,
      equipe_nome: team ? team.nome : 'Nao definida',
      observacao: els.dayObs.value.trim(),
      quantidade_membros: team ? Number(team.quantidade_membros || 0) : 0
    };

    markDirty(true);
    renderCalendar();
    renderTeamsTable();
    showMessage('Alteracao local aplicada. Salve a escala para persistir.', 'info');
  }

  function clearDayLocal() {
    if (!ensureScaleEditable()) {
      return;
    }
    els.dayTeam.value = '0';
    els.dayObs.value = '';
    applyDayChangesLocal();
  }

  async function saveDayImmediately() {
    if (!ensureScaleEditable()) {
      return;
    }

    const day = Number(els.dayNumber.value || 0);
    const turno = normalizeTurno(els.dayTurno.value || 'DIA');
    const idEquipe = Number(els.dayTeam.value || 0);
    const observacao = els.dayObs.value.trim();

    if (day <= 0) {
      return;
    }

    if (idEquipe <= 0) {
      showMessage('Para salvar o turno diretamente, selecione uma equipe.', 'warning');
      return;
    }

    try {
      await api('definirEquipeDia', {
        ano: state.ano,
        mes: state.mes,
        dia: day,
        turno,
        id_equipe_plantao: idEquipe,
        observacao
      });

      showMessage('Turno salvo no banco com sucesso.', 'success');
      await loadMonthData();
      openDayModal(day, turno);
    } catch (error) {
      showMessage(error.message || 'Erro ao salvar turno no banco.', 'danger');
    }
  }

  async function saveDayAdjustment() {
    if (!ensureScaleEditable()) {
      return;
    }

    const day = Number(els.dayNumber.value || 0);
    const turno = normalizeTurno(els.dayTurno.value || 'DIA');
    const idFuncionario = Number(els.adjustTech.value || 0);
    const ajuste = String(els.adjustType.value || 'ADICIONAR');
    const observacao = els.adjustObs.value.trim();

    if (day <= 0 || idFuncionario <= 0) {
      showMessage('Selecione o tecnico para ajustar o plantao.', 'warning');
      return;
    }

    try {
      await api('salvarAjusteMembroDia', {
        ano: state.ano,
        mes: state.mes,
        dia: day,
        turno,
        id_funcionario: idFuncionario,
        ajuste,
        observacao
      });

      showMessage('Ajuste do turno salvo.', 'success');
      await loadMonthData();
      openDayModal(day, turno);
    } catch (error) {
      showMessage(error.message || 'Erro ao salvar ajuste.', 'danger');
    }
  }

  async function removeDayAdjustment() {
    if (!ensureScaleEditable()) {
      return;
    }

    const day = Number(els.dayNumber.value || 0);
    const turno = normalizeTurno(els.dayTurno.value || 'DIA');
    const idFuncionario = Number(els.adjustTech.value || 0);

    if (day <= 0 || idFuncionario <= 0) {
      showMessage('Selecione o tecnico para remover o ajuste.', 'warning');
      return;
    }

    try {
      await api('removerAjusteMembroDia', {
        ano: state.ano,
        mes: state.mes,
        dia: day,
        turno,
        id_funcionario: idFuncionario
      });

      showMessage('Ajuste removido.', 'success');
      await loadMonthData();
      openDayModal(day, turno);
    } catch (error) {
      showMessage(error.message || 'Erro ao remover ajuste.', 'danger');
    }
  }

  async function loadMonthData() {
    if (state.loading) {
      return;
    }

    state.loading = true;

    try {
      const data = await api('listarPainel', {
        ano: state.ano,
        mes: state.mes
      });

      state.equipes = data.equipes || [];
      state.tecnicos = data.tecnicos || [];
      rebuildTeamColorMap();
      state.hasSavedScale = Number((data.escala && data.escala.id_escala_mensal) || 0) > 0;
      state.locked = Number((data.escala && data.escala.bloqueada) || 0) === 1;

      buildDayMap((data.escala && data.escala.dias) || []);
      snapshotDayMap();
      markDirty(false);

      setupMonthYearSelectors();
      renderTeamSelectors();
      renderTeamsTable();
      renderLegend();
      renderCalendar();
      updateScaleLockUi();
      renderSummary();
    } catch (error) {
      showMessage(error.message || 'Erro ao carregar painel de plantao.', 'danger');
    } finally {
      state.loading = false;
    }
  }

  function init() {
    cacheElements();
    bindEvents();
    setupMonthYearSelectors();
    createCalendarIfNeeded();
    loadMonthData();
  }

  document.addEventListener('DOMContentLoaded', init);
})();
