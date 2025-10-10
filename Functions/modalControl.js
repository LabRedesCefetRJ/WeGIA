// Elementos do modal customizado
const customModal = document.getElementById('customModal');
const customCloseBtn = document.querySelector('.custom-modal-close');
const customDataInput = document.getElementById('customDataInput');
const customErrorData = document.getElementById('customErrorData');

// Funções para mostrar e esconder o modal
function showCustomModal() {
    customModal.style.display = 'block';
    document.body.classList.add('custom-modal-open');
    customDataInput.value = '';
    customErrorData.style.display = 'none';
    customDataInput.focus();
}

function hideCustomModal() {
    customModal.style.display = 'none';
    document.body.classList.remove('custom-modal-open');
}

// Event Listeners
customCloseBtn.addEventListener('click', hideCustomModal);

// Fechar modal ao clicar fora dele
customModal.addEventListener('click', function(event) {
    if (event.target === customModal) {
        hideCustomModal();
    }
});

// Fechar modal com tecla ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && customModal.style.display === 'block') {
        hideCustomModal();
    }
});