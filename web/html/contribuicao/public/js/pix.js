//window.onload = disableAutocomplete;

let acao = 'qrcode';
let regras;

async function configurarRegrasDePagamento() {
    regras = await buscarRegrasDePagamento('Pix');
    console.log('Conjunto de regras: ' + regras);
}

async function decidirAcao() {
    switch (acao) {
        case 'qrcode': gerarQRCode(); break;
        case 'cadastrar': await cadastrarSocio(); gerarQRCode(); break;//colocar chamada para função de cadastrar sócio
        case 'atualizar': await atualizarSocio(); gerarQRCode(); break;//colocar chamada para função de atualizar sócio
        default: console.log('Ação indefinida');
    }
}

function gerarQRCode() {
    const form = document.getElementById('formulario');
    const formData = new FormData(form);

    const documento = pegarDocumento();

    formData.append('nomeClasse', 'ContribuicaoLogController');
    formData.append('metodo', 'criarQRCode');
    formData.append('documento_socio', documento);

    fetch("../controller/control.php", {
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

                // Criar uma div para centralizar o conteúdo
                let qrContainer = document.createElement("div");
                qrContainer.style.textAlign = "center";

                // Adicionar o QR Code como imagem
                let qrcode = document.createElement("img");
                qrcode.src = "data:image/jpeg;base64," + resposta.qrcode;
                qrcode.style.maxWidth = "51%";
                qrContainer.appendChild(qrcode);

                // Adicionar um botão abaixo do QR Code
                let copyButton = document.createElement("button");
                copyButton.textContent = "Copiar QRCode";
                copyButton.style.display = "block";
                copyButton.style.marginTop = "10px";
                copyButton.style.margin = "auto";
                copyButton.classList.add('btn');
                copyButton.classList.add('btn-success');
                qrContainer.appendChild(copyButton);

                qrCodeDiv.appendChild(qrContainer);

                // Ajustar a largura do botão após a imagem carregar
                qrcode.onload = function () {
                    const desktop = window.matchMedia("(min-width: 768px)");

                    if (desktop.matches) {
                        copyButton.style.width = qrcode.width * 0.75 + "px";
                    }
                };

                // Rolar a página para o form3
                window.location.hash = '#qrcode-div';

                // Adicionar o evento de clique no botão para copiar o código
                copyButton.addEventListener('click', function (ev) {
                    ev.preventDefault();
                    // Criar um elemento temporário para copiar o texto
                    let tempInput = document.createElement("input");
                    tempInput.value = resposta.copiaCola;//substituir pelo código da área de transferência
                    document.body.appendChild(tempInput);

                    // Selecionar e copiar o texto
                    tempInput.select();
                    document.execCommand("copy");

                    // Remover o elemento temporário
                    document.body.removeChild(tempInput);

                    alert("Código QR copiado para a área de transferência!");
                });

            } else if (resposta.erro) {
                alert('Erro: ' + resposta.erro);
            } else {
                alert("Ops! Ocorreu um problema na geração da sua forma de pagamento, tente novamente, se o erro persistir contate o suporte.");
            }

        })
        .catch(error => {
            console.error("Erro:", error);
        });
}

configurarAvancaValor(verificarValor);
configurarVoltaValor();
configurarVoltaCpf();
configurarVoltaContato();
configurarAvancaEndereco(verificarEndereco);
configurarAvancaContato(verificarContato);
configurarAvancaTerminar(decidirAcao);
configurarMudancaOpcao(alternarPfPj);
configurarConsulta(buscarSocio);
configurarRegrasDePagamento();