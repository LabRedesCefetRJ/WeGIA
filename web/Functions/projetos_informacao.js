// Dados dos projetos vindos do PHP
var projetos = window.projetosData || [];

// Função para preencher a tabela
function preencherTabela(listaProjetos) {
  var tbody = $('#tbody-projetos');
  tbody.empty();
  
  if (listaProjetos.length === 0) {
    tbody.append('<tr><td colspan="6" class="text-center">Nenhum projeto encontrado</td></tr>');
    return;
  }
  
  $.each(listaProjetos, function(i, projeto) {
    var descricao = projeto.descricao || 'Sem descrição';
    if (descricao.length > 80) {
      descricao = descricao.substring(0, 80) + '...';
    }

    var btnEditar = $('<button type="button" class="btn btn-primary btn-xs" title="Editar projeto">')
      .append('<i class="fa fa-pencil"></i>')
      .on('click', function() {
        var id = encodeURIComponent(projeto.id_projeto);
        window.location.href = 'editar_projeto.php?id_projeto=' + id;
      });

    var tr = $('<tr>')
      .append($('<td>').text(projeto.nome))
      .append($('<td>').text(projeto.tipo))
      .append($('<td>').text(projeto.local))
      .append($('<td>').text(projeto.status))
      .append($('<td>').text(descricao))
      .append($('<td class="text-center">').append(btnEditar));
    
    tbody.append(tr);
  });
}

// Função para filtrar por status
function filtrarPorStatus() {
  var statusSelecionado = $('#filtro_status').val();
  
  if (!statusSelecionado) {
    preencherTabela(projetos);
  } else {
    var filtrados = projetos.filter(function(projeto) {
      return projeto.id_status == statusSelecionado;
    });
    preencherTabela(filtrados);
  }
}

// Carregar dados ao iniciar
$(document).ready(function() {
  preencherTabela(projetos);
  filtrarPorStatus();
});