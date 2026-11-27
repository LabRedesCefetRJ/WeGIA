// O Mercado Pago não suporta gerar boletos com vencimento futuro em lote
// (limite de 29 dias de validade do bolbradesco), então não pode ser
// selecionado como plataforma do meio de pagamento "Carne".
function filtrarPlataformasIncompativeis(nomeInput, selectPlataforma) {
    const nome = nomeInput.value.trim().toLowerCase();
    const ehCarne = nome === 'carne';

    Array.from(selectPlataforma.options).forEach(function (option) {
        if (option.dataset.plataforma !== 'MercadoPago') {
            return;
        }

        option.hidden = ehCarne;
        option.disabled = ehCarne;

        if (ehCarne && selectPlataforma.value === option.value) {
            selectPlataforma.value = '';
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const nomeCadastro = document.getElementById('meio-pagamento-nome');
    const plataformaCadastro = document.getElementById('meio-pagamento-plataforma');
    if (nomeCadastro && plataformaCadastro) {
        nomeCadastro.addEventListener('input', function () {
            filtrarPlataformasIncompativeis(nomeCadastro, plataformaCadastro);
        });
    }

    const nomeEdicao = document.getElementById('editNome');
    const plataformaEdicao = document.getElementById('editPlataforma');
    if (nomeEdicao && plataformaEdicao) {
        nomeEdicao.addEventListener('input', function () {
            filtrarPlataformasIncompativeis(nomeEdicao, plataformaEdicao);
        });
    }

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
            filtrarPlataformasIncompativeis(document.getElementById('editNome'), plataformas);

            // O filtro acima pode ter limpado a seleção (ex: meio "Carne" ainda
            // vinculado a um gateway MercadoPago, incompatível). Avisa o usuário
            // em vez de deixar o campo em branco silenciosamente.
            if (plataformas.value !== plataformaId) {
                console.error('Erro ao selecionar a plataforma com ID:', plataformaId);
                alert('A plataforma atualmente vinculada a este meio de pagamento não é mais compatível com ele. Selecione uma nova plataforma antes de salvar.');
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