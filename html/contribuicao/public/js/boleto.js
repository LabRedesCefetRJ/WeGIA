//window.onload = disableAutocomplete();

let acao = 'boleto';
let regras;

async function configurarRegrasDePagamento() {
    regras = await buscarRegrasDePagamento('Boleto');
    console.log('Conjunto de regras: ' + regras);
}

async function decidirAcao() {
    switch (acao) {
        case 'boleto': gerarBoleto(); break;
        case 'cadastrar': await cadastrarSocio(); gerarBoleto(); break;
        case 'atualizar': await atualizarSocio(); gerarBoleto(); break;
        default: console.log('Ação indefinida');
    }
}

function gerarBoleto() {
    const form = document.getElementById('formulario');
    const formData = new FormData(form);

    const documento = pegarDocumento();

    formData.append('nomeClasse', 'ContribuicaoLogController');
    formData.append('metodo', 'criarBoleto');
    formData.append('documento_socio', documento);

    fetch("../controller/control.php", {
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
            } else if (resposta.erro){
                alert('Erro: '+ resposta.erro);
            }
            else {
                alert("Ops! Ocorreu um problema na geração da sua forma de pagamento, tente novamente, se o erro persistir contate o suporte.");
            }

        })
        .catch(error => {
            alert(error);
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
