let acao = 'cartao-credito';
let regras;

// Tokenização de cartão (Mercado Pago / Pagar.me) fica em tokenizacao_cartao.js
// — compartilhado com recorrencia.js.

async function configurarRegrasDePagamento() {
    regras = await buscarRegrasDePagamento('CartaoCredito');
    console.log('Conjunto de regras: ' + regras);
}

async function decidirAcao(dadosCartao) {
    try {
        switch (acao) {
            case 'cartao-credito':
                await processarCartaoCredito(dadosCartao);
                break;

            case 'cadastrar':
                await cadastrarSocio();
                await processarCartaoCredito(dadosCartao);
                break;

            case 'atualizar':
                await atualizarSocio();
                await processarCartaoCredito(dadosCartao);
                break;

            case 'cadastrar_existente':
                await cadastrarSocioPessoaExistente();
                await processarCartaoCredito(dadosCartao);
                break;

            default:
                console.log('Ação indefinida');
        }
    } catch (error) {
        console.error(error.message);
        alert(error.message);
    }
}

function obterDadosCartao() {
    return {
        number: document.getElementById('card_number').value.replace(/\D/g, ''),
        holder_name: document.getElementById('card_holder_name').value.trim(),
        exp_month: document.getElementById('card_exp_month').value.trim(),
        exp_year: document.getElementById('card_exp_year').value.trim(),
        cvv: document.getElementById('card_cvv').value.trim(),
        // Exigido pelo Mercado Pago em createCardToken() (identificationNumber).
        documento: pegarDocumento().replace(/\D/g, '')
    };
}

function validarDadosCartao(dadosCartao) {
    if (dadosCartao.number.length < 13 || dadosCartao.number.length > 19) {
        alert('Número de cartão inválido. Deve ter entre 13 e 19 dígitos.');
        return false;
    }

    if (dadosCartao.holder_name.length < 3) {
        alert('Por favor, informe o nome como está no cartão.');
        return false;
    }

    const mesExpiracao = Number(dadosCartao.exp_month);
    if (mesExpiracao < 1 || mesExpiracao > 12) {
        alert('Por favor, informe um mês válido (1-12).');
        return false;
    }

    if (dadosCartao.exp_year.length !== 2 && dadosCartao.exp_year.length !== 4) {
        alert('Por favor, informe um ano válido (2 ou 4 dígitos).');
        return false;
    }

    if (dadosCartao.cvv.length < 3) {
        alert('Por favor, informe o código de segurança do cartão.');
        return false;
    }

    return true;
}

function removerCamposSensiveisCartao(formData) {
    ['card_number', 'card_holder_name', 'card_exp_month', 'card_exp_year', 'card_cvv'].forEach((campo) => {
        formData.delete(campo);
    });
}

async function processarCartaoCredito(dadosCartao) {
    const dados = dadosCartao || obterDadosCartao();
    const cardToken = await tokenizarCartaoPorMetodoPagamento('CartaoCredito', dados);

    const form = document.getElementById("formulario");
    const formData = new FormData(form);
    const documento = pegarDocumento();

    formData.append("nomeClasse", "ContribuicaoLogController");
    formData.append("metodo", "processarCartaoCredito");
    formData.append("documento_socio", documento);
    removerCamposSensiveisCartao(formData);
    formData.set("card_token", cardToken.id);

    // BIN (6 primeiros dígitos): o Mercado Pago exige pra identificar a
    // bandeira do cartão na hora de cobrar. A Pagar.me não usa (fica null).
    if (cardToken.bin) {
        formData.set("card_bin", cardToken.bin);
    }

    // Gerado pelo script de Device Fingerprint do Mercado Pago (security.js).
    // Enviado como header X-meli-session-id ao criar o pagamento, ajuda o
    // antifraude deles a não tratar a cobrança como suspeita por padrão.
    if (typeof MP_DEVICE_SESSION_ID !== "undefined") {
        formData.append("device_id", MP_DEVICE_SESSION_ID);
    }

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
            exibirResultadoPagamento(resposta);
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

            const dadosCartao = obterDadosCartao();

            if (!validarDadosCartao(dadosCartao)) {
                return;
            }

            //Verificação do reCAPTCHA
            const captchaResponse = grecaptcha.getResponse();

            if (!captchaResponse) {
                alert('Por favor, confirme que você não é um robô.');
                return;
            }

            // Se todas as validações passarem, processa o pagamento
            decidirAcao(dadosCartao);
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
