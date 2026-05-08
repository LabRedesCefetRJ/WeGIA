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
    btnConsulta.addEventListener("click", function () {
        funcao();
    })
}

/**
 * Configura a função passada como parâmetro como ação padrão para
 * o clique do botão com id voltar-btn
 * @param {*} funcao 
 */
function configurarVolta(funcao) {
    const btnVoltar = document.getElementById("voltar-btn");
    btnVoltar.addEventListener("click", function () {
        funcao('pag1');

        //Apaga conteúdo independentemente da função de redirecionamento informada
        apagarConteudoTabelaBoletos();
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
 * Realiza os procedimentos necessários para fazer uma consulta de segunda via
 * @returns 
 */
function realizarConsulta() {
    const documento = pegarDocumento();
    const csrf = document.querySelector('input[name="csrf_token"]').value;

    if (!validarDocumento(documento)) {
        alert("O documento deve ser preenchido");//Alterar forma de exibição do alerta posteriormente
        return;
    }

    const url = `../controller/control.php?nomeClasse=SocioController&metodo=exibirBoletosPorCpf&documento=${encodeURIComponent(documento)}&csrf_token=${encodeURIComponent(csrf)}`;

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
            if (data.length < 1) {
                //Exibir mensagem de aviso
                desenharConteudoNaTabela(mensagemBoletosNaoEncontrados());
            } else {
                //Exibir a tabela de boletos
                desenharConteudoNaTabela(montarTabela(prepararDadosParaTabela(data)));
            }

            alternarPaginas('pag2');
        })
        .catch(error => {
            console.error('Erro ao realizar a consulta:', error);
        });

    console.log("Consulta realizada");
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
    } else if (opcao == 'juridica') {
        if (documentoSomenteNumeros.length != 14) {
            return false;
        }
    }

    return true;
}

/**
 * Alterna a exibição entre as divs das páginas
 * @param string pagina 
 */
function alternarPaginas(pagina) {
    const pag1 = document.getElementById('pag1');
    const pag2 = document.getElementById('pag2');

    if (pagina == 'pag1') {
        pag1.classList.remove('hidden');
        pag2.classList.add('hidden');
    } else if (pagina == 'pag2') {
        pag2.classList.remove('hidden');
        pag1.classList.add('hidden');
    }
}

/**
 * Monta a string de uma tabela com boletos
 * @param array dados 
 * @returns 
 */
function montarTabela(dados) {

    let tabela = `<table class="table table-hover">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Data</th>
      <th scope="col">Valor</th>
      <th scope="col">Ação</th>
    </tr>
  </thead>
  <tbody>`;

    dados.forEach((dado, index) => {
        tabela += gerarLinha(dado, index+1);
    });

    tabela += `</tbody>
</table>`;

    return tabela;
}

/**
 * Recebe como parâmetro um array de dados e utiliza as informações para gerar uma string 
 * correspondente a uma linha da tabela.
 * @param array dados
 * @returns string 
 */
function gerarLinha(dados, index) {
    const linha = `<tr>
      <th scope="row">${index}</th>
      <td>${dados[2]}</td>
      <td>${dados[3]}</td>
      <td><a href="${dados[5]}" class="btn btn-primary">Visualizar</a></td>
    </tr>`

    return linha;
}

/**
 * Realiza os procedimentos necessários para transformar os dados do back-end para o
 * formato correto que a tabela espera.
 * @param array dados
 * @returns array 
 */
function prepararDadosParaTabela(dados) {
    //separar os dados
    let dadosSeparados = [];
    dados.forEach(dado => {
        let dadoSeparado = separarDados(dado);
        dadoSeparado.push(`../pdfs/${dado}`);//adiciona link
        dadosSeparados.push(dadoSeparado);
    });
    //ordenar array pela data
    const arrayOrdenado = ordenarPelaData(dadosSeparados);
    //formatar data e valor
    arrayOrdenado.forEach((boleto, index) => {
        arrayOrdenado[index][2] = formatarDataEmBrasileiro(boleto[2]);
        arrayOrdenado[index][3] = formatarValorEmReais(boleto[3]);
    });
    return arrayOrdenado;
}

/**
 * Recebe uma string como parâmetro e separa as partes a partir dos caracteres chaves
 * @param string texto 
 * @returns array
 */
function separarDados(texto) {
    const textoSeparado = texto.split(/[_\.]/);
    return textoSeparado;
}

/**
 * Recebe um array como parâmetro e organiza os elementos em ordem decrescente de acordo com a data
 * @param array array 
 * @returns array
 */
function ordenarPelaData(array) {
    array.sort((a, b) => b[2] - a[2]);
    return array;
}

/**
 * Recebe como parâmetro uma string e retorna ela no formato de data brasileira
 * @param string data 
 * @returns 
 */
function formatarDataEmBrasileiro(data) {
    ano = data.substring(0, 4);
    mes = data.substring(4, 6);
    dia = data.substring(6, 8);

    const dataFormatoBrasileiro = `${dia}/${mes}/${ano}`;
    return dataFormatoBrasileiro;
}

/**
 * Recebe uma string ou um number como parâmetro e retorna ele no formato monetário brasileiro
 * @param {string | number} valor 
 * @returns 
 */
function formatarValorEmReais(valor) {
    return `R$ ${valor},00`;
}

/**
 * Remove todo o conteúdo que estiver dentro da div tabela-boletos
 */
function apagarConteudoTabelaBoletos(){
    const tabelaBoleto = document.getElementById('tabela-boletos');
    tabelaBoleto.innerText = "";
}

/**
 * Recebe como um parâmetro uma string e insere ela dentro da div da tabela de boletos
 * @param string conteudo 
 */
function desenharConteudoNaTabela(conteudo){
    const tabelaBoleto = document.getElementById('tabela-boletos');
    tabelaBoleto.innerHTML = conteudo;
}

/**
 * Recebe como parâmetro uma string e retorna o resultado da junção do texto informado com as tags html
 * para a criação de um alerta personalizado do bootstrap
 * @param string texto 
 * @returns string
 */
function mensagemBoletosNaoEncontrados(texto = "Nenhum boleto associado ao documento informado foi encontrado."){
    const mensagem = `<div class="alert alert-warning text-center" role="alert">`+
                        `${texto}`
                    +`</div>`;
    return mensagem;
}

configurarMudancaOpcao(alternarPfPj);
configurarConsulta(realizarConsulta);
configurarVolta(alternarPaginas);