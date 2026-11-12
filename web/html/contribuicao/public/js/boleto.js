//window.onload = disableAutocomplete();

let acao = 'boleto';
let regras;

async function configurarRegrasDePagamento() {
    regras = await buscarRegrasDePagamento('Boleto');
    console.log('Conjunto de regras: ' + regras);
}

async function decidirAcao() {
    try {
        switch (acao) {
            case 'boleto':
                await gerarBoleto();
                break;

            case 'cadastrar':
                await cadastrarSocio();
                await gerarBoleto();
                break;

            case 'atualizar':
                await atualizarSocio();
                await gerarBoleto();
                break;

            case 'atualizar_parcial':
                await completarCadastroSocio();
                await gerarBoleto();
                break;

            case 'cadastrar_existente':
                await cadastrarSocioPessoaExistente();
                await gerarBoleto();
                break;

            default:
                console.log('Ação indefinida');
        }
    } catch (error) {
        console.error(error.message);
        atualizarBotaoEnvioContribuicao('fracasso', 'avanca-terminar', error.message);
    }
}

async function gerarBoleto() {
    const form = document.getElementById('formulario');
    const formData = new FormData(form);

    const documento = pegarDocumento();

    formData.append('nomeClasse', 'ContribuicaoLogController');
    formData.append('metodo', 'criarBoleto');
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
                atualizarBotaoEnvioContribuicao('sucesso', 'avanca-terminar', 'Boleto gerado. Se o boleto não abrir, verifique se o navegador bloqueou a nova janela.');
            } else if (resposta.mensagem) {
                // Rota pública: o link foi enviado por email, não aparece aqui
                atualizarBotaoEnvioContribuicao('sucesso', 'avanca-terminar', resposta.mensagem);
            } else if (resposta.erro){
                voltarParaConfirmarCpf();
                atualizarBotaoEnvioContribuicao('fracasso', 'avanca-terminar', 'Erro: ' + resposta.erro);
            }
            else {
                voltarParaConfirmarCpf();
                atualizarBotaoEnvioContribuicao('fracasso', 'avanca-terminar', "Ops! Ocorreu um problema na geração do boleto. Tente novamente ou contate o suporte.");
            }

        })
        .catch(error => {
            console.error("Erro:", error);
            atualizarBotaoEnvioContribuicao('fracasso', 'avanca-terminar', error);
        });
}

configurarAvancaValor(verificarValor);
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
