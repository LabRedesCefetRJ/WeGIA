// Funções para Tipo de Projeto
function gerarTipo() {
  url = '../../dao/projetos/exibir_tipo_projeto.php';
  $.ajax({
    data: '',
    type: "POST",
    url: url,
    success: function(response) {
      var tipos = response;
      $('#tipo_projeto').empty();
      $('#tipo_projeto').append('<option selected disabled>Selecionar</option>');
      $.each(tipos, function(i, item) {
        $('#tipo_projeto').append('<option value="' + item.id_tipo + '">' + item.descricao + '</option>');
      });
    },
    dataType: 'json'
  });
}

function adicionar_tipo() {
  url = '../../dao/projetos/adicionar_tipo_projeto.php';
  var tipo = window.prompt("Cadastre um Novo Tipo de Projeto:");
  if (!tipo) return;
  tipo = tipo.trim();
  if (tipo == '') return;

  data = 'tipo=' + tipo;
  $.ajax({
    type: "POST",
    url: url,
    data: data,
    success: function(response) {
      gerarTipo();
    },
    dataType: 'text'
  });
}

// Funções para Local
function gerarLocal() {
  url = '../../dao/projetos/exibir_local_projeto.php';
  $.ajax({
    data: '',
    type: "POST",
    url: url,
    success: function(response) {
      var locais = response;
      $('#local_projeto').empty();
      $('#local_projeto').append('<option selected disabled>Selecionar</option>');
      $.each(locais, function(i, item) {
        $('#local_projeto').append('<option value="' + item.id_local + '">' + item.nome + '</option>');
      });
    },
    dataType: 'json'
  });
}

function adicionar_local() {
  url = '../../dao/projetos/adicionar_local_projeto.php';
  var local = window.prompt("Cadastre um Novo Local:");
  if (!local) return;
  local = local.trim();
  if (local == '') return;

  data = 'local=' + local;
  $.ajax({
    type: "POST",
    url: url,
    data: data,
    success: function(response) {
      gerarLocal();
    },
    dataType: 'text'
  });
}

// Funções para Status
function gerarStatus() {
  url = '../../dao/projetos/exibir_status_projeto.php';
  $.ajax({
    data: '',
    type: "POST",
    url: url,
    success: function(response) {
      var status = response;
      $('#status_projeto').empty();
      $('#status_projeto').append('<option selected disabled>Selecionar</option>');
      $.each(status, function(i, item) {
        $('#status_projeto').append('<option value="' + item.id_status + '">' + item.descricao + '</option>');
      });
    },
    dataType: 'json'
  });
}

function adicionar_status() {
  url = '../../dao/projetos/adicionar_status_projeto.php';
  var status = window.prompt("Cadastre um Novo Status:");
  if (!status) return;
  status = status.trim();
  if (status == '') return;

  data = 'status=' + status;
  $.ajax({
    type: "POST",
    url: url,
    data: data,
    success: function(response) {
      gerarStatus();
    },
    dataType: 'text'
  });
}

// Validação do formulário antes de enviar
function validarFormularioProjeto(event) {
  var nome = $('#nome_projeto').val().trim();
  var tipo = $('#tipo_projeto').val();
  var local = $('#local_projeto').val();
  var status = $('#status_projeto').val();
  var dataInicio = $('#data_inicio').val();
  
  var erros = [];
  
  if (!nome || nome.length < 3) {
    erros.push('Nome do projeto não informado ou inválido!');
  }
  
  if (!tipo || tipo === null) {
    erros.push('Tipo de projeto não informado!');
  }
  
  if (!local || local === null) {
    erros.push('Local não informado!');
  }
  
  if (!status || status === null) {
    erros.push('Status não informado!');
  }
  
  if (!dataInicio) {
    erros.push('Data de início não informada!');
  }
  
  // Validar data fim se preenchida
  var dataFim = $('#data_fim').val();
  if (dataFim && dataInicio) {
    if (new Date(dataFim) < new Date(dataInicio)) {
      erros.push('Data de término não pode ser anterior à data de início!');
    }
  }
  
  if (erros.length > 0) {
    event.preventDefault(); // Impede o envio do formulário
    
    // Exibe os erros
    var mensagem = 'Por favor, corrija os seguintes erros:\n\n';
    erros.forEach(function(erro, index) {
      mensagem += (index + 1) + '. ' + erro + '\n';
    });
    
    alert(mensagem);
    return false;
  }
  
  return true;
}

// Carregar header e menu
$(function() {
  $("#header").load("../header.php");
  $(".menuu").load("../menu.php");
  
  // Adicionar validação ao formulário
  $('form').on('submit', function(event) {
    return validarFormularioProjeto(event);
  });
});