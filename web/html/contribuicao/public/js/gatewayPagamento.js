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
            document.getElementById('editPrivateToken').value = privateToken;
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