//Essa função serve para adicionar um novo tipo de comorbidade
async function adicionar_enfermidade() {
    const url = `../../controle/control.php`;

    let nome_enfermidade = window.prompt("Insira o nome da enfermidade:");
    let cid_enfermidade = window.prompt("Insira o CID da enfermidade:");

    if (!nome_enfermidade || !cid_enfermidade) {
        return;
    }

   const dados = {
    cid: cid_enfermidade,
    nome: nome_enfermidade,
    nomeClasse: encodeURIComponent("EnfermidadeControle"),
    metodo: encodeURIComponent("adicionarEnfermidade")
   }

   try{
        const resposta = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(dados)
        })
        if(resposta.ok){
            await gerarEnfermidade();
            return;
        }else{
            const data = await resposta.json();
            const mensagemErro = Object.prototype.hasOwnProperty.call(data, "erro") ? data.erro : `Algum erro ocorreu ao tentar adicionar uma nova enfermidade`;
            throw new Error(mensagemErro);
        }
   }catch(e){
    console.error('Erro ao buscar enfermidades:', e.message);
    window.alert(e.message);
    return;
   }

   
}

//Essa função serve para retornar a lista de tipos de enfermidade
async function listarTodasAsEnfermidades() {
    const nomeClasse = 'EnfermidadeControle';
    const metodo = 'listarTodasAsEnfermidades';

    const url = `../../controle/control.php?nomeClasse=${encodeURIComponent(nomeClasse)}&metodo=${encodeURIComponent(metodo)}`;

    try {
        const response = await fetch(url);

        if (!response.ok) {
        throw new Error(`Erro na requisição: ${response.status} - ${response.statusText}`);
        }

        const data = await response.json();

        return data ?? []; //Retorna um array vazio se `null`
    } catch (error) {
        console.error('Erro ao buscar enfermidades:', error);
        return [];
    }
}

//Essa função serve para gerar as opçoes de enfermidades no select de tipos de enfermidade
async function gerarEnfermidade() {
    const situacoes = await listarTodasAsEnfermidades();
    const enfermidadesDoPaciente = await buscarEnfermidadesPorIDFichaMedica();
    const listaSituacoes = Array.isArray(situacoes) ? situacoes : [];
    const listaEnfermidadesPaciente = Array.isArray(enfermidadesDoPaciente) ? enfermidadesDoPaciente : [];
    const idsEnfermidadesDoPaciente = new Set(
        listaEnfermidadesPaciente.map((item) => String(item.id_CID))
    );

    let select = document.getElementById("id_CID");
    while (select.firstChild) {
        select.removeChild(select.firstChild)
    }

    let selecionar = document.createElement("option");
    selecionar.textContent = "Selecionar";
    selecionar.value = "";
    selecionar.selected = true;
    selecionar.disabled = true;
    select.appendChild(selecionar);

    for (const item of listaSituacoes) {
        if (idsEnfermidadesDoPaciente.has(String(item.id_CID))) {
            continue;
        }

        let option = document.createElement("option");
        option.value = item.id_CID;
        option.textContent = item.descricao;
        select.appendChild(option);
    }
}

//Essa função serve para buscar e retornar todas as enfermidades ativas ligadas a uma ficha médica determinada pelo id_fichamedica
async function buscarEnfermidadesPorIDFichaMedica() {
    const params = new URLSearchParams(window.location.search);
    const id_fichamedica = params.get("id_fichamedica")
    const nomeClasse = 'EnfermidadeControle';
    const metodo = 'getEnfermidadesAtivasPorFichaMedica';

    const url = `../../controle/control.php?nomeClasse=${encodeURIComponent(nomeClasse)}&metodo=${encodeURIComponent(metodo)}&id_fichamedica=${encodeURIComponent(id_fichamedica)}`;

    try {
        const response = await fetch(url);

        if (!response.ok) {
        throw new Error(`Erro na requisição: ${response.status} - ${response.statusText}`);
        }

        const data = await response.json();

        return data ?? []; //Retorna um array vazio se `null`
    } catch (error) {
        console.error('Erro ao buscar enfermidades:', error);
        return [];
    }
}

let timeoutMensagemCadastroEnfermidade = null;
let timeoutFecharAnimacaoEnfermidade = null;
let scrollInicialCadastroComorbidade = window.pageYOffset || document.documentElement.scrollTop || 0;

window.addEventListener("load", () => {
    scrollInicialCadastroComorbidade = window.pageYOffset || document.documentElement.scrollTop || 0;
});

function voltarParaScrollInicialComorbidade() {
    window.scrollTo({
        top: Math.max(scrollInicialCadastroComorbidade, 0),
        behavior: "smooth"
    });
}

//Essa função serve para o cadastro de uma nova enfermidade na ficha do paciente
function mostrarMensagemCadastroEnfermidade(mensagem, tipo = "success") {
    const alerta = document.getElementById("mensagem-cadastro-enfermidade");
    const texto = document.getElementById("mensagem-cadastro-enfermidade-texto");

    if (!alerta || !texto) {
        return;
    }

    alerta.classList.remove("alert-success", "alert-danger", "alert-warning");
    if (tipo === "danger") {
        alerta.classList.add("alert-danger");
    } else if (tipo === "warning") {
        alerta.classList.add("alert-warning");
    } else {
        alerta.classList.add("alert-success");
    }
    texto.textContent = mensagem;
    alerta.style.display = "block";
    alerta.classList.remove("is-visible");
    void alerta.offsetWidth;
    alerta.classList.add("is-visible");
    requestAnimationFrame(() => {
        voltarParaScrollInicialComorbidade();
    });

    if (timeoutMensagemCadastroEnfermidade) {
        clearTimeout(timeoutMensagemCadastroEnfermidade);
    }

    if (timeoutFecharAnimacaoEnfermidade) {
        clearTimeout(timeoutFecharAnimacaoEnfermidade);
        timeoutFecharAnimacaoEnfermidade = null;
    }

    timeoutMensagemCadastroEnfermidade = setTimeout(() => {
        ocultarMensagemCadastroEnfermidade();
    }, 10000);
}

function ocultarMensagemCadastroEnfermidade() {
    const alerta = document.getElementById("mensagem-cadastro-enfermidade");

    if (!alerta) {
        return;
    }

    alerta.classList.remove("is-visible");

    if (timeoutMensagemCadastroEnfermidade) {
        clearTimeout(timeoutMensagemCadastroEnfermidade);
        timeoutMensagemCadastroEnfermidade = null;
    }

    if (timeoutFecharAnimacaoEnfermidade) {
        clearTimeout(timeoutFecharAnimacaoEnfermidade);
    }

    timeoutFecharAnimacaoEnfermidade = setTimeout(() => {
        alerta.style.display = "none";
        timeoutFecharAnimacaoEnfermidade = null;
    }, 350);
}

async function cadastrarEnfermidade(ev) { // Torna a função assíncrona
    ev.preventDefault();
    const selectStatus = document.getElementById("intStatus")
    const selectEnfermidades = document.getElementById("id_CID");
    const inputData = document.getElementById("data_diagnostico");
    const inputIdFichaMedica = document.getElementById("id_fichamedica_enfermidade");
    const formEnfermidade = document.getElementById('form-enfermidade');
    
    if (!inputData.value || !selectEnfermidades.value || !selectStatus.value) {
        voltarParaScrollInicialComorbidade();
        return;
    }

    ocultarMensagemCadastroEnfermidade();

    const dados = {
        intStatus: selectStatus.value,
        data_diagnostico: inputData.value,
        id_CID: selectEnfermidades.value,
        id_fichamedica: inputIdFichaMedica.value,
        nomeClasse: encodeURIComponent("EnfermidadeControle"),
        metodo: encodeURIComponent("cadastrarEnfermidadeNaFichaMedica")
    }

    try {
        const response = await fetch('./../../controle/control.php', {
        method: 'POST',
        body: JSON.stringify(dados), 
        headers: {
            'Content-Type': 'application/json'
        }
        });

        const data = await response.json();
        
        if (!response.ok) {
            const mensagemErro = Object.prototype.hasOwnProperty.call(data, "erro") ? data.erro : "Nao foi possivel cadastrar a comorbidade.";
            mostrarMensagemCadastroEnfermidade(mensagemErro, "danger");
            return;
        }

        formEnfermidade.reset();
        selectEnfermidades.selectedIndex = 0;
        await gerarEnfermidadesDoPaciente();
        mostrarMensagemCadastroEnfermidade("Comorbidade cadastrada com sucesso!");

    } catch (error) {
        console.error('Erro:', error);
        mostrarMensagemCadastroEnfermidade("Aconteceu algum problema ao cadastrar a comorbidade.", "danger");
    }
}

//Essa função serve para gerar a tabela de enfermidades do paciente
async function gerarEnfermidadesDoPaciente() {
    const tabela = document.getElementById("doc-tab");

    // Limpa a tabela antes de adicionar novas linhas
    while (tabela.firstChild) {
        tabela.removeChild(tabela.firstChild);
    }

    const enfermidades = await buscarEnfermidadesPorIDFichaMedica();

    for (const item of enfermidades) {
        if (!item.descricao || !item.data_diagnostico) {
            console.warn("Dados inválidos:", item);
            continue;
        }

        const tr = document.createElement("tr");

        // Descrição
        const tdDescricao = document.createElement("td");
        tdDescricao.textContent = item.descricao;
        tr.appendChild(tdDescricao);

        // Data
        const tdData = document.createElement("td");
        tdData.textContent = formatarDataBr(item.data_diagnostico);
        tr.appendChild(tdData);

        // Ações
        const tdAcoes = document.createElement("td");
        tdAcoes.style.verticalAlign = "middle";
        tdAcoes.style.textAlign = "center";

        const linkRemover = document.createElement("a");
        linkRemover.href = "#";
        linkRemover.title = "Inativar";
        linkRemover.addEventListener("click", (e) => {
            e.preventDefault();
            removerEnfermidade(item.id_enfermidade);
        });

        const botao = document.createElement("button");
        botao.className = "btn btn-dark";

        const icone = document.createElement("i");
        icone.className = "glyphicon glyphicon-remove";

        botao.appendChild(icone);
        linkRemover.appendChild(botao);
        tdAcoes.appendChild(linkRemover);
        tr.appendChild(tdAcoes);

        tabela.appendChild(tr);
    }

    await gerarEnfermidade();
}

//Essa função serve para remover uma enfermidade da ficha de um paciente
async function removerEnfermidade(id_enfermidade) {
    if (!window.confirm("Tem certeza que deseja inativar essa enfermidade?")) {
        return false;
    }
    const url = `../../controle/control.php?nomeClasse=${encodeURIComponent("EnfermidadeControle")}&metodo=${encodeURIComponent("tornarEnfermidadeInativa")}&id_enfermidade=${encodeURIComponent(id_enfermidade)}`;
    try{
        const resposta = await fetch(url);

        if(resposta.ok){
            await gerarEnfermidadesDoPaciente();
        }else{
            window.alert("Aconteceu algum problema ao remover uma enfermidade");
        }
    }catch(e){
        window.alert("Aconteceu algum problema ao remover uma enfermidade");
    }
}
