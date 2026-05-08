document.addEventListener('DOMContentLoaded', function () {
    // Seletor para todos os botões de editar
    const editButtons = document.querySelectorAll('button[title="Editar"]');

    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const nome = this.closest('tr').querySelector('td:nth-child(1)').textContent;
            const plataformaId = this.getAttribute('data-plataforma-id');

            // Preenche o modal com os dados do gateway
            document.getElementById('editId').value = id;
            document.getElementById('editNome').value = nome;
            let plataformas = document.getElementById('editPlataforma');
            plataformas.value = plataformaId;
            const options = plataformas.options;

            // Verifica se a opção foi selecionada corretamente
            if (plataformas.value !== plataformaId) {
                console.error('Erro ao selecionar a plataforma com ID:', plataformaId);
            } else {
                console.log('Plataforma selecionada:', plataformas.options[plataformas.selectedIndex].textContent);
            }

            $('#editModal').modal('show');
        });
    });

    //Checkbox de ativar/desativar um meio de pagamento
    const toggles = document.querySelectorAll('.toggle-input');

    toggles.forEach(toggle => {
        toggle.addEventListener('change', function (ev) {
            alterarStatus(ev, '../controller/control.php', 'MeioPagamentoController');
        });
    });
});