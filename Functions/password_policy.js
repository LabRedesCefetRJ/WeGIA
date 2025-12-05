/**
 * Verifica se a senha informada possui as exigências mínimas para ser considera segura contra ataques de força bruta:
 * Caracteres maiúsculos, minúsculos, números e caracteres especiais.
 * Tamanho mínimo de caracteres.
 * @param {string} password 
 * @returns Boolean
 */
function validatePassword(password, minLength = 8) {
    const regex = new RegExp(
        `^(?=.*[A-Z])(?=.*[a-z])(?=.*\\d)(?=.*[^A-Za-z0-9]).{${minLength},}$`
    );

    return regex.test(password);
}

const passwordInput = document.getElementById('nova_senha');
const passwordDiv = document.getElementById('password-div');
const passwordForm = document.getElementById('password-form');
const weakMessage = '<span class="error-msg">A senha informada é considerada fraca: Adicione 8 ou mais caracteres, letras maiúsculas e minúsculas e algum caractere especial.</span>';

passwordInput.addEventListener('blur', () => {
    const password = passwordInput.value;

    if (!validatePassword(password)) {
        passwordDiv.innerHTML = weakMessage;
    } else {
        passwordDiv.innerHTML = ''; // limpa a mensagem se estiver ok
    }
});

passwordForm.addEventListener('submit', (ev) => {
    const password = passwordInput.value;
    if (!validatePassword(password)) {
        ev.preventDefault();
        passwordDiv.innerHTML = weakMessage;
    }
})
