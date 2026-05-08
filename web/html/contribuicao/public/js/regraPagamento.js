document.addEventListener('DOMContentLoaded', function () {
    // Seletor para todos os botões de editar
    const editButtons = document.querySelectorAll('button[title="Editar"]');

    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const valor = this.closest('tr').querySelector('td:nth-child(4)').textContent;

            // Preenche o modal com os dados do gateway
            document.getElementById('editId').value = id;
            document.getElementById('editValor').value = valor;

            $('#editModal').modal('show');
        });
    });

    //Checkbox de ativar/desativar uma regra de pagamento
    const toggles = document.querySelectorAll('.toggle-input');

    toggles.forEach(toggle => {
        toggle.addEventListener('change', function (ev) {
            alterarStatus(ev, '../controller/control.php', 'RegraPagamentoController');
        });
    });
});