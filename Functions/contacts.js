//carregar lista de contatos e montar tabela
const contactsTable = document.getElementById('contacts-table');
const apiEndpoint = '../controle/control.php?nomeClasse=ContatoInstituicaoControle';

async function getContacts(url) {
    try {
        const response = await fetch(url + '&metodo=listarTodos', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error('Erro ao consultar contatos: ' + response.status + '|' + data.erro);
        }

        const data = await response.json();

        // garante que retorna exatamente um array de objetos Contato
        return data.resultado ?? [];
    } catch (error) {
        console.error(error);
        return [];
    }
}

function renderContactsTable(contatos) {
    const table = document.getElementById('contacts-table');

    // Limpa qualquer conteúdo anterior
    table.innerHTML = '';

    // Monta o cabeçalho da tabela
    table.innerHTML = `
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Descrição</th>
                    <th scope="col">Contato</th>
                    <th scope="col" class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody id="contacts-body"></tbody>
        </table>
    `;

    const tbody = document.getElementById('contacts-body');

    // Se não houver contatos
    if (!contatos || contatos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted py-3">
                    Nenhum contato encontrado.
                </td>
            </tr>
        `;
        return;
    }

    // Monta cada linha da tabela
    contatos.forEach(c => {
        const tr = document.createElement('tr');

        tr.innerHTML = `
            <td>${c.id}</td>
            <td>${c.descricao}</td>
            <td>${c.contato}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-primary me-1 btn-editar" data-id="${c.id}">
                    <i class="fa fa-edit"></i> Editar
                </button>

                <button class="btn btn-sm btn-danger btn-excluir" data-id="${c.id}">
                    <i class="fa fa-trash"></i> Excluir
                </button>
            </td>
        `;

        tbody.appendChild(tr);
    });
}

// Eventos para abrir o modal de editar
document.addEventListener('click', (e) => {
    if (e.target.closest('.btn-editar')) {

        const id = e.target.closest('.btn-editar').dataset.id;
        const descricao = e.target.closest('tr').children[1].textContent;
        const contato = e.target.closest('tr').children[2].textContent;

        // Preenche o modal
        document.getElementById('editar-id').value = id;
        document.getElementById('editar-descricao').value = descricao;
        document.getElementById('editar-contato').value = contato;

        // Abre o modal
        $('#modalEditarContato').modal('show');
    }
});


// Evento para abrir modal de exclusão
document.addEventListener('click', (e) => {
    if (e.target.closest('.btn-excluir')) {

        const id = e.target.closest('.btn-excluir').dataset.id;
        const descricao = e.target.closest('tr').children[1].textContent;

        // Guarda ID para exclusão
        document.getElementById('btn-confirmar-excluir').dataset.id = id;
        document.getElementById('excluir-descricao').textContent = descricao;

        // Abre o modal
        $('#modalExcluirContato').modal('show');
    }
});

getContacts(apiEndpoint).then(contatos => {
    renderContactsTable(contatos);
});
