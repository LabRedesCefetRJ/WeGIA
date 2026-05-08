const CONTROL_URL = '../../controle/control.php';

function submeterEdicao() {
  var nome       = $('#nome_projeto').val().trim();
  var tipo       = $('#tipo_projeto').val();
  var local      = $('#local_projeto').val();
  var status     = $('#status_projeto').val();
  var dataInicio = $('#data_inicio').val();
  var dataFim    = $('#data_fim').val();
  var descricao  = $('#descricao_projeto').val().trim();
  var csrf       = $('#csrf_token').val();
  var idProjeto  = $('#id_projeto').val();

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
      metodo:            'alterarProjeto',
      csrf_token:        csrf,
      id_projeto:        idProjeto,
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
      $('#btn-salvar').prop('disabled', false).text('Salvar Alterações');
      return;
    }
    window.location.href = '../projetos/informacao_projeto.php?msg=Projeto alterado com sucesso!';
  })
  .catch(function() {
    alert('Erro ao salvar alterações.');
    $('#btn-salvar').prop('disabled', false).text('Salvar Alterações');
  });
}

$(document).ready(function() {
    $('#btn-salvar').on('click', function() {
        submeterEdicao();
    });
});