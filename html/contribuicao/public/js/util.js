/**
 * Pega o campo radio com nome de opcao marcado na página 
 * @returns string
 */
function opcaoSelecionada() {
    const opcao = document.querySelector("input[name='opcao']:checked").value;
    return opcao;
}

/**
 * Configura a função passada como parâmetro como ação padrão para a mudança 
 * de marcação dos inputs radio com nome de opcao na página
 * @param {*} funcao 
 */
function configurarMudancaOpcao(funcao) {
    const opcoes = document.querySelectorAll("input[name='opcao']");
    opcoes.forEach(opcao => {
        opcao.addEventListener("change", function () {
            if (this.checked) {
                funcao();
            }
        });
    });
}

/**
 * Configura a função passada como parâmetro como ação padrão para 
 * o clique do botão com id de consultar-btn
 * @param {*} funcao 
 */
function configurarConsulta(funcao) {
    const btnConsulta = document.getElementById("consultar-btn");
    btnConsulta.addEventListener("click", function (ev) {
        ev.preventDefault();
        funcao();
    })
}

/**
 * Alterna a exibição das divs de Pessoa Física e Pessoa Jurídica
 */
function alternarPfPj() {
    const opcao = opcaoSelecionada();

    const divFisica = document.getElementById('cpf');
    const divJuridica = document.getElementById('cnpj');

    if (opcao == "fisica") {
        divFisica.classList.remove('hidden');
        divJuridica.classList.add('hidden');
    } else if (opcao == "juridica") {
        divJuridica.classList.remove('hidden');
        divFisica.classList.add('hidden');
    }
}

/**
 * Pega o documento informado na página, se a opção fisica estiver marcada o CPF é retornado,
 * se a opção jurídica estiver marcada o CNPJ é retornado.
 * @returns string
 */
function pegarDocumento() {
    const opcao = opcaoSelecionada();
    let documento;

    if (opcao == "fisica") {
        documento = document.getElementById('dcpf').value;
    } else if (opcao == "juridica") {
        documento = document.getElementById('dcnpj').value;
    }

    return documento;
}

/**
 * Valida a string de documento informada
 * @param string documento 
 * @returns boolean
 */
function validarDocumento(documento) {
    const documentoSemEspacos = documento.trim();
    if (!documentoSemEspacos) {
        return false;
    }

    const documentoSomenteNumeros = documentoSemEspacos.replace(/[^0-9]/g, '');
    const opcao = opcaoSelecionada();

    if (opcao == 'fisica') {
        if (documentoSomenteNumeros.length != 11) {
            return false;
        }

        if (!testaCPF(documentoSomenteNumeros)) {
            return false;
        }
    } else if (opcao == 'juridica') {
        if (documentoSomenteNumeros.length != 14) {
            return false;
        }
    }

    return true;
}

/**
 * Recebe como parâmetros a página para qual se deseja ir e a página atual,
 * alterando qual das divs está visível no momento. 
 * @param {*} idProxima 
 * @param {*} idAtual 
 */
function alternarPaginas(idProxima, idAtual) {
    const atual = document.getElementById(idAtual);
    const proxima = document.getElementById(idProxima);

    proxima.classList.remove('hidden');
    atual.classList.add('hidden');
}

/**
 * Configura a função passada como parâmetro como ação padrão para 
 * o clique do botão com id de consultar-btn
 * @param {*} funcao 
 */
function configurarConsulta(funcao) {
    const btnConsulta = document.getElementById("consultar-btn");
    btnConsulta.addEventListener("click", function (ev) {
        ev.preventDefault();
        funcao();
    })
}

/**
 * Verifica se o valor passado como parâmetro está dentro dos limites estipulados para uma doação
 * @param {*} valor 
 * @returns 
 */
function verificarValor(valor) {

    if (!valor || isNaN(valor) || valor <= 0) {
        alert("Por favor, preencha um valor numérico válido.");
        return false;
    }

    if (regras && regras.length > 0) {
        console.log('Existem regras cadastradas no sistema');

        for (const regra of regras) {
            if (regra.id_regra == 1 && parseFloat(valor) < regra.valor) {
                alert(`O valor está abaixo do mínimo de R$${regra.valor}`);
                return false;
            } else if (regra.id_regra == 2 && parseFloat(valor) > regra.valor) {
                alert(`O valor está acima do máximo de R$${regra.valor}`);
                return false;
            }
        }

        return true;
    } else {
        console.log('Não existem regras cadastradas');
        return true;
    }
}

/**
 * Recebe como parâmetro uma função de validação de números e atribuí a sua execução como processo do comportamento do botão avanca-valor
 * @param {*} funcao 
 */
function configurarAvancaValor(funcao) {
    const btnAvancaValor = document.getElementById('avanca-valor');

    btnAvancaValor.addEventListener('click', (ev) => {
        const valor = document.getElementById('valor').value;
        ev.preventDefault();
        if (!funcao(valor)) {
            return;
        }

        alternarPaginas('pag2', 'pag1');
    });
}

/**
 * Configura o comportamento do botão volta-valor
 */
function configurarVoltaValor() {
    const btnVoltaValor = document.getElementById('volta-valor');

    btnVoltaValor.addEventListener('click', (ev) => {
        ev.preventDefault();
        alternarPaginas('pag1', 'pag2');
    });
}

function configurarVoltaCpf() {
    const btnVoltaCpf = document.getElementById('volta-cpf');
    btnVoltaCpf.addEventListener('click', (ev) => {
        ev.preventDefault();
        alternarPaginas('pag2', 'pag3');
    });
}

function configurarVoltaContato() {
    const btnVoltaContato = document.getElementById('volta-contato');
    btnVoltaContato.addEventListener('click', (ev) => {
        ev.preventDefault();
        alternarPaginas('pag3', 'pag4');
    });
}

function configurarAvancaContato(funcao) {
    const btnAvancaContato = document.getElementById('avanca-contato');
    btnAvancaContato.addEventListener('click', (ev) => {
        ev.preventDefault();
        if (!funcao()) {
            return;
        }

        alternarPaginas('pag4', 'pag3');
    })
}

function configurarAvancaEndereco(funcao) {
    const btnAvancaEndereco = document.getElementById('avanca-endereco');
    btnAvancaEndereco.addEventListener('click', (ev) => {
        ev.preventDefault();
        if (!funcao()) {
            return;
        }

        alternarPaginas('pag5', 'pag4');
    });
}

function configurarAvancaTerminar(funcao) {
    const btnAvancaTerminar = document.getElementById('avanca-terminar');
    btnAvancaTerminar.addEventListener('click', (ev) => {
        ev.preventDefault();
        btnAvancaTerminar.disabled = true;
        btnAvancaTerminar.classList.add('disabled');
        setLoader(btnAvancaTerminar);
        funcao();
    });
}

/**
 * Verifica se alguma propriedade de um objeto do tipo Socio está vazia
 */
function verificarSocio({ bairro, cep, cidade, complemento, documento, email, estado, id, logradouro, nome, numeroEndereco, telefone }) {
    //verificar propriedades
    if (!bairro || bairro.length < 1) {
        return false;
    }

    if (!cep || cep.length < 1) {
        return false;
    }

    if (!cidade || cidade.length < 1) {
        return false;
    }

    /*if(!complemento || complemento.length < 1){
        return false;
    }*/

    if (!documento || documento.length < 1) {
        return false;
    }

    if (!email || email.length < 1) {
        return false;
    }

    if (!estado || estado.length < 1) {
        return false;
    }

    if (!id || id.length < 1) {
        return false;
    }

    if (!logradouro || logradouro.length < 1) {
        return false;
    }

    if (!nome || nome.length < 1) {
        return false;
    }

    if (!numeroEndereco || numeroEndereco.length < 1) {
        return false;
    }

    if (!telefone || telefone.length < 1) {
        return false;
    }

    return true;
}

async function cadastrarSocio() {
    const form = document.getElementById('formulario');
    const formData = new FormData(form);

    const documento = formatarCPF(pegarDocumento());
    const telefone = formatarTelefone(formData.get('telefone'))

    formData.append('nomeClasse', 'SocioController');
    formData.append('metodo', 'criarSocio');
    formData.append('documento_socio', documento);
    formData.append('telefone', telefone);

    try {
        const response = await fetch("../controller/control.php", {
            method: "POST",
            body: formData
        });

        if (!response.ok) {
            throw new Error("Erro na requisição: " + response.status);
        }

        const resposta = await response.json(); // Converte a resposta para JSON

        if (resposta.mensagem) {
            console.log(resposta.mensagem);
        } else {
            alert("Ops! Ocorreu um problema durante o seu cadastro, se o erro persistir contate o suporte.");
        }
    } catch (error) {
        console.error("Erro:", error);
    }
}

async function atualizarSocio() {
    const form = document.getElementById('formulario');
    const formData = new FormData(form);

    const documento = formatarCPF(pegarDocumento());

    formData.append('nomeClasse', 'SocioController');
    formData.append('metodo', 'atualizarSocio');
    formData.append('documento_socio', documento);

    try {
        const response = await fetch("../controller/control.php", {
            method: "POST",
            body: formData
        });

        if (!response.ok) {
            throw new Error("Erro na requisição: " + response.status);
        }

        const resposta = await response.json(); // Converte a resposta para JSON

        if (resposta.mensagem) {
            console.log(resposta.mensagem);
        } else {
            alert("Ops! Ocorreu um problema durante o seu cadastro, se o erro persistir contate o suporte.");
        }
    } catch (error) {
        console.error("Erro:", error);
    }
}

function verificarEndereco() {
    const cep = document.getElementById('cep').value;
    const rua = document.getElementById('rua').value;
    const numeroEndereco = document.getElementById('numero').value;
    const bairro = document.getElementById('bairro').value;
    const uf = document.getElementById('uf').value;
    const cidade = document.getElementById('cidade').value;

    if (!cep || cep.length != 9) {
        alert('O CEP informado não está no formato válido');
        return false;
    }

    if (!rua || rua.length < 1) {
        alert('A rua não pode estar vazia.');
        return false;
    }

    if (!numeroEndereco || numeroEndereco.length < 1) {
        alert('O número de endereço não pode estar vazio.');
        return false;
    }

    if (!bairro || bairro.length < 1) {
        alert('O bairro não pode estar vazio.');
        return false;
    }

    if (!uf || uf.length < 1) {
        alert('O estado não pode estar vazio.');
        return false;
    }

    if (!cidade || cidade.length < 1) {
        alert('A cidade não pode estar vazia.');
        return false;
    }

    return true;
}

function verificarContato() {
    const nome = document.getElementById('nome').value;
    const dataNascimento = document.getElementById('data_nascimento').value;
    const email = document.getElementById('email').value;
    const telefone = document.getElementById('telefone').value;

    if (!nome || nome.length < 3) {
        alert('O nome não pode estar vazio.');
        return false;
    }

    if (!dataNascimento) {
        alert('A data de nascimento não pode estar vazia');
        return false;
    }

    if (!email) {
        alert('O e-mail não pode estar vazio.');
        return false;
    }

    if (!telefone) {
        alert('O telefone não pode estar vazio.');
        return false;
    }

    return true;
}

/**
 * Recebe como parâmetro um objeto do tipo Socio e preenche os campos do formulário automaticamente
 * @param {*} param0 
 */
function formAutocomplete({ bairro, cep, cidade, complemento, dataNascimento, documento, email, estado, id, logradouro, nome, numeroEndereco, telefone }) {

    //Definir elementos do HTML
    const nomeObject = document.getElementById('nome');
    const dataNascimentoObject = document.getElementById('data_nascimento');
    const emailObject = document.getElementById('email');
    const telefoneObject = document.getElementById('telefone');
    const cepObject = document.getElementById('cep');
    const ruaObject = document.getElementById('rua');
    const numeroEnderecoObject = document.getElementById('numero');
    const bairroObject = document.getElementById('bairro');
    const ufObject = document.getElementById('uf');
    const cidadeObject = document.getElementById('cidade');
    const complementoObject = document.getElementById('complemento');

    //Atribuir valor aos campos
    nomeObject.value = nome;
    dataNascimentoObject.value = dataNascimento;
    emailObject.value = email;
    telefoneObject.value = telefone;
    cepObject.value = cep;
    ruaObject.value = logradouro;
    numeroEnderecoObject.value = numeroEndereco;
    bairroObject.value = bairro;
    ufObject.value = estado;
    cidadeObject.value = cidade;
    complementoObject.value = complemento;
}

function buscarSocio() {
    let documento = formatarCPF(pegarDocumento());

    if (!validarDocumento(documento)) {
        alert("O documento informado não é válido");
        return;
    }

    console.log("Buscando sócio ...");

    const url = `../controller/control.php?nomeClasse=SocioController&metodo=buscarPorDocumento&documento=${encodeURIComponent(documento)}`;

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na consulta: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            // Manipula os dados recebidos do back-end
            //verificar se existem elementos no data
            if (data.resultado && typeof data.resultado === 'object') {

                //Autocompletar campos do formulário
                if (!verificarSocio(data.resultado)) {
                    //Exibir o sócio
                    console.log(data);
                    formAutocomplete(data.resultado);
                    acao = 'atualizar';
                    alternarPaginas('pag3', 'pag2');
                } else {//Enviar para a página de confirmação de geração de boletos
                    alternarPaginas('pag5', 'pag2');
                }

                //Pegar o nome do sócio e o exibir na página de confirmação de geração do boleto
                let nomeSocio = data.resultado.nome;

                const divAgradecimento = document.getElementById('div-agradecimento');
                divAgradecimento.innerHTML = `<h3>Obrigado por contribuir mais uma vez, ${nomeSocio}!<h3>`;
            } else {
                console.log(data.resultado);
                acao = 'cadastrar';
                alternarPaginas('pag3', 'pag2');

                const divAgradecimento = document.getElementById('div-agradecimento');
                divAgradecimento.innerHTML = `<h3>Obrigado pela sua contribuição!<h3>`;
            }

            //alternarPaginas('pag2');
        })
        .catch(error => {
            console.error('Erro ao realizar a consulta:', error);
        });

    console.log("Consulta realizada");
}

async function buscarRegrasDePagamento(meioPagamento) {
    console.log("Buscando regras de pagamento ...");

    const url = `../controller/control.php?nomeClasse=RegraPagamentoController&metodo=buscaConjuntoRegrasPagamentoPorNomeMeioPagamento&meio-pagamento=${encodeURIComponent(meioPagamento)}`;

    try {
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error('Erro na consulta: ' + response.statusText);
        }

        const data = await response.json();

        if (data.regras) {
            return data.regras; // Retorna o conjunto de regras
        } else if (data.erro) {
            alert(data.erro);
            return false; // Retorna null em caso de erro específico
        }
    } catch (error) {
        console.error('Erro ao realizar a consulta:', error);
        return false; // Retorna null em caso de erro genérico
    }
}

function setLoader(btn) {
    // Esconde o primeiro elemento filho (ícone)
    btn.firstElementChild.style.display = "none";

    // Remove o texto do botão sem remover os elementos filhos
    btn.childNodes.forEach(node => {
        if (node.nodeType === Node.TEXT_NODE) {
            node.textContent = '';
        }
    });

    // Adiciona o loader se não houver outros elementos filhos além do ícone
    if (btn.childElementCount == 1) {
        var loader = document.createElement("DIV");
        loader.className = "loader";
        btn.appendChild(loader);
    }
}

function formatarCPF(cpf) {
    // Remove tudo que não for número
    cpf = cpf.replace(/\D/g, '');
  
    // Aplica a formatação: xxx.xxx.xxx-xx
    return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
  }

  function formatarTelefone(telefone){
    // Remove tudo que não for número
    telefone = telefone.replace(/\D/g, '');
  
    // Aplica a formatação: (xx)xxxxx-xxxx
    return telefone.replace(/(\d{2})(\d{5})(\d{4})/, "($1)$2-$3");
  }