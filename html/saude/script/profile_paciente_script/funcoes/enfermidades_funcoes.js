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
    situacoes = await listarTodasAsEnfermidades()
    let length = situacoes.length - 1;
    let select = document.getElementById("id_CID");
    while (select.firstChild) {
        select.removeChild(select.firstChild)
    }
    for (let i = 0; i <= length; i = i + 1) {
        if (i == 0) {
        let selecionar = document.createElement("option");
        selecionar.textContent = "Selecionar"
        selecionar.value = "";
        selecionar.selected = true;
        selecionar.disabled = true;
        select.appendChild(selecionar)
        }
        let option = document.createElement("option");
        option.value = situacoes[i].id_CID;
        option.textContent = situacoes[i].descricao;
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

//Essa função serve para o cadastro de uma nova enfermidade na ficha do paciente
async function cadastrarEnfermidade(ev) { // Torna a função assíncrona
    ev.preventDefault();
    const selectStatus = document.getElementById("intStatus")
    const selectEnfermidades = document.getElementById("id_CID");
    const inputData = document.getElementById("data_diagnostico");
    const inputIdFichaMedica = document.getElementById("id_fichamedica_enfermidade");
    const formEnfermidade = document.getElementById('form-enfermidade');
    
    if (!inputData.value || !selectEnfermidades.value || !selectStatus.value) {
        return;
    }

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
            window.alert(mensagemErro);
            return;
        }

        formEnfermidade.reset();
        selectEnfermidades.selectedIndex = 0;
        await gerarEnfermidadesDoPaciente();

    } catch (error) {
        console.error('Erro:', error);
        window.alert("Aconteceu algum problema ao cadastrar a comorbidade.");
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
}

//Essa função serve para remover uma enfermidade da ficha de um paciente
async function removerEnfermidade(id_enfermidade) {
    if (!window.confirm("Tem certeza que deseja inativar essa enfermidade?")) {
        return false;
    }
    const url = `../../controle/control.php?nomeClasse=${encodeURIComponent("EnfermidadeControle")}&metodo=${encodeURIComponent("tornarEnfermidadeInativa")}&id_enfermidade=${encodeURIComponent(id_enfermidade)}`;
    try{
        const resposta = await fetch(url);

        if(resposta){
            await gerarEnfermidadesDoPaciente();
        }else{
            window.alert("Aconteceu algum problema ao remover uma enfermidade");
        }
    }catch(e){
        window.alert("Aconteceu algum problema ao remover uma enfermidade");
    }
}
