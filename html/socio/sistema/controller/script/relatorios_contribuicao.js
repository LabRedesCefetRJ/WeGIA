const formRelatorio = document.getElementById('form-relatorio-contribuicao');

function gerarTabela(data){
    //Limpar tabela
    const tBody = document.querySelector('#tabela-relatorio-contribuicao tbody');
    tBody.innerHTML = '';

    let valor = 0;

    data.forEach(element => {
        if(parseInt(element.status) === 1){
            valor += parseFloat(element.valor);
        }

        const tr = document.createElement('tr');

        const tdCodigo = document.createElement('td');
        tdCodigo.innerText = element.codigo;

        const tdNome = document.createElement('td'); 
        tdNome.innerText = element.nomeSocio;

        const tdDataGeracao = document.createElement('td'); 
        tdDataGeracao.innerText = element.dataGeracao;
        
        const tdDataVencimento = document.createElement('td'); 
        tdDataVencimento.innerText = element.dataVencimento;
        
        const tdDataPagamento = document.createElement('td'); 
        tdDataPagamento.innerText = element.dataPagamento;
        
        const tdValor = document.createElement('td');
        tdValor.innerText = element.valor;
        
        const tdStatus = document.createElement('td');
        tdStatus.innerText = element.status;
        
        const tdPlataforma = document.createElement('td');
        tdPlataforma.innerText = element.plataforma;
        
        const tdMeioPagamento = document.createElement('td');
        tdMeioPagamento.innerText = element.meio;

        tr.append(tdCodigo, tdNome, tdPlataforma, tdMeioPagamento, tdDataGeracao, tdDataVencimento, tdDataPagamento, tdValor, tdStatus);
        tBody.appendChild(tr);
    });

    return valor;
}

formRelatorio.addEventListener('submit', async function (ev) {
    ev.preventDefault();

    const mensagemRelatorio = document.getElementById('mensagem-relatorio');
    mensagemRelatorio.innerHTML = '';

    const relatorioBtn = document.getElementById('relatorio-btn');
    relatorioBtn.disabled = true;
    relatorioBtn.innerHTML = 'Gerando relatório <i class="fa fa-spinner fa-spin"></i>'

    // Pega os campos do formulário
    const formData = new FormData(formRelatorio);
    
    // Converte o FormData em URLSearchParams
    const params = new URLSearchParams(formData);
    params.append('metodo', 'getRelatorio');
    params.append('nomeClasse', 'ContribuicaoLogController');

    try {
        const response = await fetch(`../../contribuicao/controller/control.php?${params.toString()}`, {
            method: 'GET'
        });

        // Se a resposta não for OK, força um erro para cair no catch
        if (!response.ok) {
            throw new Error(`Erro na sincronização: ${response.status} ${response.statusText}`);
        }

        const result = await response.json();
        console.log('Resposta do servidor:', result);
        //location.reload();

        if(result.length < 1){
            mensagemRelatorio.innerHTML = "Nenhuma contribuição foi encontrada com os filtros selecionados"
        }else{
            const valorPago = gerarTabela(result);
            mensagemRelatorio.innerHTML = `O total adquirido ao longo do período foi R$ ${valorPago},00`;
        }
    } catch (error) {
        alert(`${error.message}`);
        console.error(error.message);
    } finally {
        relatorioBtn.innerHTML = 'Gerar relatório';
        relatorioBtn.disabled = false;
    }
});