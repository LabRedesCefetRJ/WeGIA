//window.onload = disableAutocomplete;

let acao = 'mensalidade';
let regras;

async function configurarRegrasDePagamento() {
    regras = await buscarRegrasDePagamento('Carne');
    console.log('Conjunto de regras: ' + regras);
}

async function decidirAcao() {
    try {
        switch (acao) {
            case 'mensalidade':
                await gerarMensalidade();
                break;

            case 'cadastrar':
                await cadastrarSocio();
                await gerarMensalidade();
                break;

            case 'atualizar':
                await atualizarSocio();
                await gerarMensalidade();
                break;

            case 'atualizar_parcial':
                await completarCadastroSocio();
                await gerarMensalidade();
                break;

            case 'cadastrar_existente':
                await cadastrarSocioPessoaExistente();
                await gerarMensalidade();
                break;

            default:
                console.log('Ação indefinida');
        }
    } catch (error) {
        console.error(error.message);
        atualizarBotaoEnvioContribuicao('fracasso', 'avanca-terminar', error.message);
    }
}

async function gerarMensalidade() {
    const form = document.getElementById('formulario');
    const formData = new FormData(form);

    const documento = pegarDocumento();

    formData.append('nomeClasse', 'ContribuicaoLogController');
    formData.append('metodo', 'criarCarne');
    formData.append('documento_socio', documento);

    return fetch("../controller/control.php", {
        method: "POST",
        body: formData
    })
        .then(response => {
            return response.json(); // Converte a resposta para JSON
        })
        .then(resposta => {
            if (resposta.link) {
                console.log(resposta.link);
                // Redirecionar o usuário para o link do boleto em uma nova aba
                window.open(resposta.link, '_blank');
                atualizarBotaoEnvioContribuicao('sucesso', 'avanca-terminar', 'Carnê gerado. Se o documento não abrir, verifique se o navegador bloqueou a nova janela.');
            } else if (resposta.mensagem) {
                // Rota pública: o link foi enviado por email, não aparece aqui
                atualizarBotaoEnvioContribuicao('sucesso', 'avanca-terminar', resposta.mensagem);
            } else if (resposta.erro) {
                voltarParaConfirmarCpf();
                atualizarBotaoEnvioContribuicao('fracasso', 'avanca-terminar', 'Erro: ' + resposta.erro);
            } else {
                voltarParaConfirmarCpf();
                atualizarBotaoEnvioContribuicao('fracasso', 'avanca-terminar', "Ops! Ocorreu um problema na geração do carnê. Tente novamente ou contate o suporte.");
            }

        })
        .catch(error => {
            console.error("Erro:", error);
            atualizarBotaoEnvioContribuicao('fracasso', 'avanca-terminar', error);
        });
}

function verificarValorMensalidade(valor, parcelas, diaVencimento) {

    if (!valor || isNaN(valor) || valor <= 0) {
        mostrarFeedbackContribuicao("Por favor, preencha um valor numérico válido.", 'warning');
        return false;
    }

    if (regras && regras.length > 0) {
        console.log('Existem regras cadastradas no sistema');

        for (const regra of regras) {
            if (regra.id_regra == 1 && parseFloat(valor) < regra.valor) {
                mostrarFeedbackContribuicao(`O valor está abaixo do mínimo de R$${regra.valor}`, 'warning');
                return false;
            } else if (regra.id_regra == 2 && parseFloat(valor) > regra.valor) {
                mostrarFeedbackContribuicao(`O valor está acima do máximo de R$${regra.valor}`, 'warning');
                return false;
            }
        }
    }

    if (!parcelas || parcelas < 2 || parcelas > 12) {
        mostrarFeedbackContribuicao('A quantidade de parcelas deve ser um número entre 2 e 12.', 'warning');
        return false;
    }

    const diasValidos = [1, 5, 10, 15, 20, 25];

    if (undefined === diasValidos.find((dia) => { return dia == diaVencimento })) {
        mostrarFeedbackContribuicao('O dia de vencimento escolhido não é válido', 'warning');
        return false;
    }

    return true;
}

function configurarAvancaValorMensalidade(funcao) {
    const btnAvancaValor = document.getElementById('avanca-valor');

    btnAvancaValor.addEventListener('click', (ev) => {
        const valor = document.getElementById('valor').value;
        const parcelas = document.getElementById('parcelas').value;
        const diaVencimentoObject = document.querySelector("input[name='dia']:checked");

        if (!diaVencimentoObject) {
            mostrarFeedbackContribuicao('Selecione um dia de vencimento', 'warning');
            return;
        }

        const diaVencimento = diaVencimentoObject.value;

        ev.preventDefault();
        if (!funcao(valor, parcelas, diaVencimento)) {
            return;
        }

        alternarPaginas('pag2', 'pag1');
    });
}

configurarAvancaValorMensalidade(verificarValorMensalidade);
configurarVoltaValor();
configurarVoltaCpf();
configurarVoltaContato();
configurarVoltaEndereco();
configurarAvancaEndereco(verificarEnderecoDinamico);
configurarAvancaContatoDinamico(verificarContato);
configurarAvancaTerminar(decidirAcao);
configurarMudancaOpcao(alternarPfPj);
configurarConsulta(buscarCadastroSocio);
configurarRegrasDePagamento();