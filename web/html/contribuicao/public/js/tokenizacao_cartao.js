class ServicoTokenizacaoCartaoInterface {
    async tokenizar() {
        throw new Error('O serviço de tokenização deve implementar o método tokenizar().');
    }
}

class PagarMeTokenizacaoCartaoService extends ServicoTokenizacaoCartaoInterface {
    constructor(publicToken) {
        super();
        this.publicToken = publicToken;
    }

    async tokenizar(dadosCartao) {
        const url = `https://api.pagar.me/core/v5/tokens/?appId=${encodeURIComponent(this.publicToken)}`;
        const payload = {
            type: 'card',
            card: {
                number: String(dadosCartao.number || '').replace(/\D/g, ''),
                holder_name: dadosCartao.holder_name,
                exp_month: Number(dadosCartao.exp_month),
                exp_year: Number(dadosCartao.exp_year),
                cvv: String(dadosCartao.cvv || ''),
                label: dadosCartao.label || inferirBandeiraCartao(dadosCartao.number)
            }
        };

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const resposta = await lerRespostaJsonOuTexto(response);

        if (!response.ok) {
            throw new Error(extrairMensagemErroTokenizacao(resposta, `Falha ao tokenizar o cartão na Pagar.me (HTTP ${response.status}).`));
        }

        if (!resposta || !resposta.id) {
            throw new Error('A Pagar.me não retornou o identificador do cartão tokenizado.');
        }

        // bin: null porque o backend do Pagar.me usa o card_token diretamente
        // (não precisa dos 6 primeiros dígitos, ao contrário do Mercado Pago).
        return { id: resposta.id, bin: null };
    }
}

class MercadoPagoTokenizacaoCartaoService extends ServicoTokenizacaoCartaoInterface {
    constructor(publicToken) {
        super();
        this.mpSdk = new MercadoPago(publicToken, { locale: 'pt-BR' });
    }

    async tokenizar(dadosCartao) {
        const anoExpiracao = String(dadosCartao.exp_year).length === 2
            ? '20' + dadosCartao.exp_year
            : String(dadosCartao.exp_year);

        try {
            const cardToken = await this.mpSdk.createCardToken({
                cardNumber: dadosCartao.number,
                cardholderName: dadosCartao.holder_name,
                cardExpirationMonth: dadosCartao.exp_month,
                cardExpirationYear: anoExpiracao,
                securityCode: dadosCartao.cvv,
                identificationType: 'CPF',
                identificationNumber: dadosCartao.documento
            });

            // O Mercado Pago exige o BIN (6 primeiros dígitos) separadamente do
            // token, pra identificar a bandeira do cartão na hora de cobrar.
            return { id: cardToken.id, bin: cardToken.first_six_digits };
        } catch (error) {
            throw new Error(descreverErroMercadoPago(error));
        }
    }
}

/**
 * Extrai uma mensagem legível do erro retornado por mp.createCardToken().
 * O SDK do Mercado Pago rejeita a Promise com um objeto que costuma trazer
 * `cause` (array de {code, description}, um por campo inválido) e/ou
 * `message` — sem isso, o usuário só veria uma mensagem genérica.
 */
function descreverErroMercadoPago(error) {
    const causas = Array.isArray(error?.cause) ? error.cause : (Array.isArray(error) ? error : null);
    if (causas && causas.length > 0) {
        const descricoes = causas
            .map((causa) => (typeof causa === 'string' ? causa : causa?.description || causa?.message))
            .filter(Boolean);
        if (descricoes.length > 0) {
            return descricoes.join(' ');
        }
    }
    if (typeof error?.message === 'string' && error.message) {
        return error.message;
    }
    try {
        const bruto = JSON.stringify(error);
        if (bruto && bruto !== '{}' && bruto !== 'null') {
            return 'Não foi possível validar os dados do cartão: ' + bruto;
        }
    } catch (e) {
        // ignora falha ao serializar (ex: referência circular)
    }
    return 'Não foi possível validar os dados do cartão. Verifique as informações e tente novamente.';
}

function inferirBandeiraCartao(numeroCartao) {
    const numero = String(numeroCartao || '').replace(/\D/g, '');

    if (/^4/.test(numero)) {
        return 'Visa';
    }

    if (/^5[1-5]/.test(numero) || /^2(2[2-9]|[3-6]|7[01]|720)/.test(numero)) {
        return 'Mastercard';
    }

    if (/^3[47]/.test(numero)) {
        return 'American Express';
    }

    if (/^(4011|431274|438935|451416|457393|457631|457632|504175|5067|5090|6277|636297|636368|6504|6505|6509|6516|6550)/.test(numero)) {
        return 'Elo';
    }

    return 'Visa';
}

function criarServicoTokenizacaoCartao(gatewayInfo) {
    const descricao = String(gatewayInfo?.description || '').trim().toLowerCase();

    if (descricao.includes('pagarme')) {
        return new PagarMeTokenizacaoCartaoService(gatewayInfo.publicToken);
    }

    if (descricao.includes('mercadopago')) {
        return new MercadoPagoTokenizacaoCartaoService(gatewayInfo.publicToken);
    }

    throw new Error(`Gateway de tokenização não suportado: ${gatewayInfo?.description || 'indefinido'}.`);
}

async function obterInfoGatewayPagamento(paymentMethod) {
    const response = await fetch(`../controller/control.php?nomeClasse=GatewayPagamentoController&metodo=getGatewayInfoByMethodPayment&payment_method=${encodeURIComponent(paymentMethod)}`, {
        method: 'GET'
    });

    const resposta = await lerRespostaJsonOuTexto(response);

    if (!response.ok) {
        throw new Error(extrairMensagemErroTokenizacao(resposta, 'Não foi possível consultar o gateway de pagamento.'));
    }

    return resposta;
}

async function tokenizarCartaoPorMetodoPagamento(paymentMethod, dadosCartao) {
    const gatewayInfo = await obterInfoGatewayPagamento(paymentMethod);

    if (!gatewayInfo || !gatewayInfo.publicToken) {
        throw new Error('Não foi possível localizar a chave pública do gateway.');
    }

    const servicoTokenizacao = criarServicoTokenizacaoCartao(gatewayInfo);
    return await servicoTokenizacao.tokenizar(dadosCartao);
}

async function lerRespostaJsonOuTexto(response) {
    const contentType = response.headers.get('content-type') || '';
    const texto = await response.text();

    if (!texto) {
        return null;
    }

    if (contentType.includes('application/json')) {
        try {
            return JSON.parse(texto);
        } catch (error) {
            return texto;
        }
    }

    try {
        return JSON.parse(texto);
    } catch (error) {
        return texto;
    }
}

function extrairMensagemErroTokenizacao(resposta, mensagemPadrao) {
    if (!resposta) {
        return mensagemPadrao;
    }

    if (typeof resposta === 'string') {
        return resposta;
    }

    if (Array.isArray(resposta.errors) && resposta.errors.length > 0) {
        const primeiroErro = resposta.errors[0];
        if (typeof primeiroErro === 'string') {
            return primeiroErro;
        }

        if (primeiroErro && typeof primeiroErro === 'object') {
            return primeiroErro.message || primeiroErro.code || mensagemPadrao;
        }
    }

    return resposta.erro || resposta.error || resposta.message || mensagemPadrao;
}
