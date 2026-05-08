const CONTROL_URL = '../../controle/control.php';

function recarregarSelects(dados) {
  if (dados.tipos) {
    $('#tipo_projeto').empty().append('<option selected disabled>Selecionar</option>');
    dados.tipos.forEach(function(t) {
      $('#tipo_projeto').append('<option value="' + t.id_tipo + '">' + t.descricao + '</option>');
    });
  }
  if (dados.locais) {
    $('#local_projeto').empty().append('<option selected disabled>Selecionar</option>');
    dados.locais.forEach(function(l) {
      $('#local_projeto').append('<option value="' + l.id_local + '">' + l.nome + '</option>');
    });
  }
  if (dados.status) {
    $('#status_projeto').empty().append('<option selected disabled>Selecionar</option>');
    dados.status.forEach(function(s) {
      $('#status_projeto').append('<option value="' + s.id_status + '">' + s.descricao + '</option>');
    });
  }
}

function adicionar_tipo() {
  var tipo = window.prompt('Cadastre um Novo Tipo de Projeto:');
  if (!tipo || !tipo.trim()) return;

  fetch(CONTROL_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ nomeClasse: 'ProjetoControle', metodo: 'adicionarTipo', tipo: tipo.trim() })
  })
  .then(function(r) { return r.json(); })
  .then(function(dados) {
    if (dados.erro) { alert(dados.erro); return; }
    recarregarSelects(dados);
  })
  .catch(function() { alert('Erro ao adicionar tipo.'); });
}

function adicionar_local() {
  var local = window.prompt('Cadastre um Novo Local:');
  if (!local || !local.trim()) return;

  fetch(CONTROL_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ nomeClasse: 'ProjetoControle', metodo: 'adicionarLocal', local: local.trim() })
  })
  .then(function(r) { return r.json(); })
  .then(function(dados) {
    if (dados.erro) { alert(dados.erro); return; }
    recarregarSelects(dados);
  })
  .catch(function() { alert('Erro ao adicionar local.'); });
}

function adicionar_status() {
  var status = window.prompt('Cadastre um Novo Status:');
  if (!status || !status.trim()) return;

  fetch(CONTROL_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ nomeClasse: 'ProjetoControle', metodo: 'adicionarStatus', status: status.trim() })
  })
  .then(function(r) { return r.json(); })
  .then(function(dados) {
    if (dados.erro) { alert(dados.erro); return; }
    recarregarSelects(dados);
  })
  .catch(function() { alert('Erro ao adicionar status.'); });
}

function limparFormulario() {
  $('#nome_projeto').val('');
  $('#tipo_projeto').val('');
  $('#local_projeto').val('');
  $('#status_projeto').val('');
  $('#data_inicio').val('');
  $('#data_fim').val('');
  $('#descricao_projeto').val('');
}

function submeterFormulario() {
  var nome       = $('#nome_projeto').val().trim();
  var tipo       = $('#tipo_projeto').val();
  var local      = $('#local_projeto').val();
  var status     = $('#status_projeto').val();
  var dataInicio = $('#data_inicio').val();
  var dataFim    = $('#data_fim').val();
  var descricao  = $('#descricao_projeto').val().trim();
  var csrf       = $('#csrf_token').val();

  if (!nome || nome.length < 3) { alert('Nome do projeto inválido.'); return; }
  if (!tipo)       { alert('Selecione um tipo de projeto.'); return; }
  if (!local)      { alert('Selecione um local.'); return; }
  if (!status)     { alert('Selecione um status.'); return; }
  if (!dataInicio) { alert('Informe a data de início.'); return; }
  if (dataFim && dataInicio && new Date(dataFim) < new Date(dataInicio)) {
    alert('Data de término não pode ser anterior à data de início.');
    return;
  }

  $('#btn-salvar').prop('disabled', true).text('Salvando...');

  fetch(CONTROL_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      nomeClasse:        'ProjetoControle',
      metodo:            'incluir',
      csrf_token:        csrf,
      nome_projeto:      nome,
      tipo_projeto:      tipo,
      local_projeto:     local,
      status_projeto:    status,
      data_inicio:       dataInicio,
      data_fim:          dataFim,
      descricao_projeto: descricao
    })
  })
  .then(function(r) { return r.json(); })
  .then(function(dados) {
    if (dados.erro) {
      alert('Erro: ' + dados.erro);
      $('#btn-salvar').prop('disabled', false).text('Salvar');
      return;
    }
    window.location.href = '../projetos/informacao_projeto.php?msg=Projeto cadastrado com sucesso!';
  })
  .catch(function() {
    alert('Erro ao cadastrar projeto.');
    $('#btn-salvar').prop('disabled', false).text('Salvar');
  });
}

$(function() {
  $("#header").load("../header.php");
  $(".menuu").load("../menu.php");
  $('#btn-salvar').on('click', submeterFormulario);
});