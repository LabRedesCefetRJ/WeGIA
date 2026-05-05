// Dados dos projetos vindos do PHP
var projetos = window.projetosData || [];

// Função para preencher a tabela
function preencherTabela(listaProjetos) {
  var tbody = $('#tbody-projetos');
  tbody.empty();
  
  if (listaProjetos.length === 0) {
    tbody.append('<tr><td colspan="5" class="text-center">Nenhum projeto encontrado</td></tr>');
    return;
  }
  
  $.each(listaProjetos, function(i, projeto) {
    var descricao = projeto.descricao || 'Sem descrição';
    if (descricao.length > 80) {
      descricao = descricao.substring(0, 80) + '...';
    }
    
    var tr = $('<tr>')
      .css('cursor', 'pointer')
      .data('id', projeto.id_projeto)
      .on('click', function () {
        var id = encodeURIComponent($(this).data('id'));
        window.location.href = 'editar_projeto.php?id_projeto=' + id;
      })
      .append($('<td>').text(projeto.nome))
      .append($('<td>').text(projeto.tipo))
      .append($('<td>').text(projeto.local))
      .append($('<td>').text(projeto.status))
      .append($('<td>').text(descricao));
    
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
});