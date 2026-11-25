//window.onload = disableAutocomplete;

let acao = 'qrcode';
let regras;

async function configurarRegrasDePagamento() {
    regras = await buscarRegrasDePagamento('Pix');
    console.log('Conjunto de regras: ' + regras);
}

async function decidirAcao() {
    try {
        switch (acao) {
            case 'qrcode':
                await gerarQRCode();
                break;

            case 'cadastrar':
                await cadastrarSocio();
                await gerarQRCode();
                break;

            case 'atualizar':
                await atualizarSocio();
                await gerarQRCode();
                break;

            case 'atualizar_parcial':
                await completarCadastroSocio();
                await gerarQRCode();
                break;

            case 'cadastrar_existente':
                await cadastrarSocioPessoaExistente();
                await gerarQRCode();
                break;

            default:
                console.log('Ação indefinida');
        }
    } catch (error) {
        console.error(error.message);
        atualizarBotaoEnvioContribuicao('fracasso', 'avanca-terminar', error.message);
    }
}

async function gerarQRCode() {
    const form = document.getElementById('formulario');
    const formData = new FormData(form);

    const documento = pegarDocumento();

    formData.append('nomeClasse', 'ContribuicaoLogController');
    formData.append('metodo', 'criarQRCode');
    formData.append('documento_socio', documento);

    return fetch("../controller/control.php", {
        method: "POST",
        body: formData
    })
        .then(response => {
            return response.json(); // Converte a resposta para JSON
        })
        .then(resposta => {
            if (resposta.qrcode) {
                const qrCodeDiv = document.getElementById('qrcode-div');

                //Remove texto de campos obrigatórios na página final
                const instrucao = document.getElementById('instrucao');
                instrucao.classList.add('hidden');

                // Mostrar div do qrcode gerado
                alternarPaginas('qrcode-div', 'pag5');
                atualizarBotaoEnvioContribuicao('sucesso', 'avanca-terminar', 'QR Code PIX gerado. Escaneie a imagem ou copie o código para concluir a contribuição.');

                let qrContainer = document.createElement("div");
                qrContainer.className = "text-center";

                // Adicionar o QR Code como imagem
                let qrcode = document.createElement("img");
                qrcode.src = "data:image/jpeg;base64," + resposta.qrcode;
                qrcode.alt = "QR Code para realizar a contribuição via PIX";
                qrContainer.appendChild(qrcode);

                // Adicionar um botão abaixo do QR Code
                let copyButton = document.createElement("button");
                copyButton.className = "btn btn-primary btn-lg";
                const copyIcon = document.createElement("i");
                copyIcon.className = "fa fa-copy me-2";
                copyIcon.setAttribute("aria-hidden", "true");
                copyButton.append(copyIcon, document.createTextNode("Copiar código PIX"));
                qrContainer.appendChild(copyButton);

                qrCodeDiv.appendChild(qrContainer);

                // Rolar a página para o form3
                window.location.hash = '#qrcode-div';

                // Adicionar o evento de clique no botão para copiar o código
                copyButton.addEventListener('click', async function (ev) {
                    ev.preventDefault();
                    let copiado = false;

                    if (navigator.clipboard?.writeText) {
                        try {
                            await navigator.clipboard.writeText(resposta.copiaCola);
                            copiado = true;
                        } catch (error) {
                            console.error("Não foi possível copiar usando a área de transferência do navegador.", error);
                        }
                    }

                    if (!copiado) {
                        const tempInput = document.createElement("textarea");
                        tempInput.value = resposta.copiaCola;
                        tempInput.setAttribute("readonly", "");
                        tempInput.style.position = "fixed";
                        tempInput.style.opacity = "0";
                        document.body.appendChild(tempInput);
                        tempInput.select();
                        try {
                            copiado = document.execCommand("copy");
                        } catch (error) {
                            console.error("Não foi possível copiar usando o método alternativo.", error);
                        }
                        document.body.removeChild(tempInput);
                    }

                    if (copiado) {
                        mostrarFeedbackContribuicao("Código PIX copiado para a área de transferência.", 'success');
                    } else {
                        const codigoManual = document.createElement("textarea");
                        codigoManual.className = "form-control mt-3";
                        codigoManual.readOnly = true;
                        codigoManual.value = resposta.copiaCola;
                        codigoManual.setAttribute("aria-label", "Código PIX para copiar manualmente");
                        qrContainer.appendChild(codigoManual);
                        mostrarFeedbackContribuicao("Não foi possível copiar automaticamente. O código PIX está selecionado para cópia manual.", 'warning');
                        codigoManual.focus();
                        codigoManual.select();
                    }
                });

            } else if (resposta.erro) {
                voltarParaConfirmarCpf();
                atualizarBotaoEnvioContribuicao('fracasso', 'avanca-terminar', 'Erro: ' + resposta.erro);
            } else {
                voltarParaConfirmarCpf();
                atualizarBotaoEnvioContribuicao('fracasso', 'avanca-terminar', "Ops! Ocorreu um problema na geração do PIX. Tente novamente ou contate o suporte.");
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