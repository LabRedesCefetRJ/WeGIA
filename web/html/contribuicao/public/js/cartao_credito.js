//window.onload = disableAutocomplete;

let acao = 'cartao-credito';
let regras;

async function configurarRegrasDePagamento() {
    regras = await buscarRegrasDePagamento('CartaoCredito');
    console.log('Conjunto de regras: ' + regras);
}

async function decidirAcao() {
    switch (acao) {
        case 'cartao-credito': processarCartaoCredito(); break;
        case 'cadastrar': await cadastrarSocio(); processarCartaoCredito(); break;
        case 'atualizar': await atualizarSocio(); processarCartaoCredito(); break;
        default: console.log('Ação indefinida');
    }
}

function processarCartaoCredito() {
    const form = document.getElementById("formulario");
    const formData = new FormData(form);
    const documento = pegarDocumento();

    formData.append("nomeClasse", "ContribuicaoLogController");
    formData.append("metodo", "processarCartaoCredito");
    formData.append("documento_socio", documento);

    // Mostrar loading
    document.getElementById("pag5").classList.add("hidden");
    document.getElementById("pag6").classList.remove("hidden");
    document.getElementById("loading").classList.remove("hidden");
    document.getElementById("payment-result").classList.add("hidden");

    fetch("../controller/control.php", {
        method: "POST",
        body: formData,
    })
        .then((response) => {
            return response.json();
        })
        .then((resposta) => {
            document.getElementById("loading").classList.add("hidden");
            document.getElementById("payment-result").classList.remove("hidden");

            if (resposta.sucesso) {
                document.getElementById("success-message").classList.remove("hidden");
                document.getElementById("error-message").classList.add("hidden");
            } else {
                document.getElementById("error-message").classList.remove("hidden");
                document.getElementById("success-message").classList.add("hidden");
                document.getElementById("error-text").textContent = resposta.erro || "Erro ao processar pagamento";
            }
        })
        .catch((error) => {
            console.error("Erro:", error);
            document.getElementById("loading").classList.add("hidden");
            document.getElementById("payment-result").classList.remove("hidden");
            document.getElementById("success-message").classList.add("hidden");
            document.getElementById("error-message").classList.remove("hidden");
            document.getElementById("error-text").textContent = error.erro || "Erro no processamento do cartão";
        });
}

// Função para formatar o número do cartão com espaços a cada 4 dígitos
function formatarNumeroCartao(valor) {
    // Remove tudo exceto dígitos
    valor = valor.replace(/\D/g, '');

    // Adiciona espaços a cada 4 dígitos
    return valor.replace(/(\d{4})(?=\d)/g, '$1 ');
}

// Configuração de máscaras específicas para cartão de crédito
document.addEventListener('DOMContentLoaded', function () {
    // Máscara dinâmica para número do cartão
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function () {
            this.value = formatarNumeroCartao(this.value);
        });
    }

    // Mascaras
    const mascaras = {
        card_exp_month: "00",
        card_exp_year: "00",
        card_cvv: "0000",
    };

    Object.entries(mascaras).forEach(([id, formato]) => {
        const elemento = document.getElementById(id);
        if (elemento) {
            $(elemento).mask(formato);
        }
    });

    const btnVoltarEndereco = document.getElementById('btn-voltar-endereco');
    if (btnVoltarEndereco) {
        btnVoltarEndereco.addEventListener('click', function (e) {
            e.preventDefault();
            document.getElementById('pag5').classList.add('hidden');
            document.getElementById('pag4').classList.remove('hidden');
        });
    }

    const btnFinalizar = document.getElementById('btn-finalizar');
    if (btnFinalizar) {
        btnFinalizar.addEventListener('click', function (e) {
            e.preventDefault();

            // Validação dos campos do cartão
            const cardNumber = document.getElementById('card_number').value.replace(/\D/g, '');
            const cardHolderName = document.getElementById('card_holder_name').value.trim();
            const cardExpMonth = document.getElementById('card_exp_month').value.trim();
            const cardExpYear = document.getElementById('card_exp_year').value.trim();
            const cardCvv = document.getElementById('card_cvv').value.trim();

            // Validação de comprimento variável (13-19 dígitos)
            if (cardNumber.length < 13 || cardNumber.length > 19) {
                alert('Número de cartão inválido. Deve ter entre 13 e 19 dígitos.');
                return;
            }

            if (cardHolderName.length < 3) {
                alert('Por favor, informe o nome como está no cartão.');
                return;
            }

            if (cardExpMonth < 1 || cardExpMonth > 12) {
                alert('Por favor, informe um mês válido (1-12).');
                return;
            }

            if (cardExpYear.length !== 2 && cardExpYear.length !== 4) {
                alert('Por favor, informe um ano válido (2 ou 4 dígitos).');
                return;
            }

            if (cardCvv.length < 3) {
                alert('Por favor, informe o código de segurança do cartão.');
                return;
            }

            //Verificação do reCAPTCHA
            const captchaResponse = grecaptcha.getResponse();

            if (!captchaResponse) {
                alert('Por favor, confirme que você não é um robô.');
                return;
            }

            // Se todas as validações passarem, processa o pagamento
            decidirAcao();
        });
    }
});

configurarAvancaValor(verificarValor);
configurarVoltaValor();
configurarVoltaCpf();
configurarVoltaContato();
configurarAvancaEndereco(verificarEndereco);
configurarAvancaContato(verificarContato);
configurarMudancaOpcao(alternarPfPj);
configurarConsulta(buscarSocio);
configurarRegrasDePagamento();