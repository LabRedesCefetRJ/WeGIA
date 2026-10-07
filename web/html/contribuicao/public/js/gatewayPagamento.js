document.addEventListener('DOMContentLoaded', function () {
    // Seletor para todos os botões de editar
    const editButtons = document.querySelectorAll('button[title="Editar"]');

    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const nome = this.closest('tr').querySelector('td:nth-child(1)').textContent;
            const endpoint = this.closest('tr').querySelector('td:nth-child(2)').textContent;
            const privateToken = this.closest('tr').querySelector('td:nth-child(3)').textContent;
            const publicToken = this.closest('tr').querySelector('td:nth-child(4)').textContent;

            // Preenche o modal com os dados do gateway
            document.getElementById('editId').value = id;
            document.getElementById('editNome').value = nome;
            document.getElementById('editEndpoint').value = endpoint;

            // O token PRIVADO nunca vai como valor pré-preenchido (evita
            // reenviar o valor mascarado sem querer e sobrescrever o token
            // real por engano) — só como placeholder, pra indicar que já
            // existe um token salvo. Campo vazio no envio = "manter o token
            // atual". O token público não é secreto, então pode ir
            // preenchido normalmente.
            const editPrivateTokenField = document.getElementById('editPrivateToken');
            editPrivateTokenField.value = '';
            editPrivateTokenField.placeholder = (privateToken && privateToken !== 'coloque o token aqui')
                ? `Token atual: ${privateToken} — deixe em branco para manter`
                : 'Insira o token privado da API';

            document.getElementById('editPublicToken').value = publicToken;

            // Exibe o modal
            $('#editModal').modal('show');
        });
    });

    //Checkbox de ativar/desativar um gateway de pagamento
    const toggles = document.querySelectorAll('.toggle-input');

    toggles.forEach(toggle => {
        toggle.addEventListener('change', function (ev) {
            alterarStatus(ev, '../controller/control.php', 'GatewayPagamentoController');
        });
    });

});