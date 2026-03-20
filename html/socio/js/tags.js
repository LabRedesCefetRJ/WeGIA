document.addEventListener('click', function (e) {
    if (e.target.classList.contains('delete-tag')) {
        const button = e.target;
        const id_tag = button.dataset.id;

        // confirmação
        if (!confirm('Tem certeza que deseja excluir esta tag?')) {
            return;
        }

        deleteTag(id_tag, button);
    }
});

function deleteTag(id_tag, button) {
    fetch('../../../controle/control.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id_tag: id_tag, nomeClasse: 'SocioTagController', metodo: 'delete' })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Sucesso:', data);

        // remove a linha da tabela
        const row = button.closest('tr');
        if (row) {
            row.remove();
        }
    })
    .catch(error => {
        console.error('Erro:', error);
    });
}