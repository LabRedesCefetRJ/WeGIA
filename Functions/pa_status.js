function adicionar_status() {
    const descricao = prompt("Informe a descrição do novo status:");
    const nomeClasse = "PaStatusControle";
    const metodo = "incluir";

    if (!descricao || !descricao.trim()) {
        return; // usuário cancelou ou digitou vazio
    }

    fetch('../../controle/control.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            descricao,
            nomeClasse,
            metodo
        })
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao cadastrar status');
            }
            return response.json();
        })
        .then(listaStatus => {
            // seleciona TODOS os selects da página
            const selects = document.querySelectorAll('.select-status-processo');

            selects.forEach(select => {
                // limpa opções
                select.innerHTML = '';

                // recria opções
                listaStatus.forEach(status => {
                    const option = document.createElement('option');
                    option.value = status.id;
                    option.textContent = status.descricao;
                    select.appendChild(option);
                });

                // seleciona automaticamente o último (novo)
                select.value = listaStatus[listaStatus.length - 1].id;
            });
        })
        .catch(error => {
            console.error(error);
            alert('Não foi possível cadastrar o status.');
        });
}