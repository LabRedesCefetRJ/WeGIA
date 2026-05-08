/**
 * Máscara visual para Cartão SUS
 * Remove caracteres não numéricos e limita a 15 dígitos
 * Validação completa é feita no servidor (Util.php)
 */

function mascaraCNS(valor) {
    // Remove caracteres não numéricos
    valor = valor.replace(/[^0-9]/g, '');
    // Limita a 15 caracteres (sem espaços)
    valor = valor.substring(0, 15);
    return valor;
}

document.addEventListener('DOMContentLoaded', function() {
    var campoCNS = document.getElementById('cns');
    if (campoCNS) {
        campoCNS.addEventListener('input', function() {
            this.value = mascaraCNS(this.value);
        });
    }
});
