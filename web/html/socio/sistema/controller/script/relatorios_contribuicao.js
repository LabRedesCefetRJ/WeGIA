const formRelatorio = document.getElementById('form-relatorio-contribuicao');

function formatarData(data) {
    if (!data) return ''; // Trata datas nulas ou vazias
    const dataObj = new Date(data);
    if (isNaN(dataObj)) return data; // Caso a data não seja válida, retorna como está
    return dataObj.toLocaleDateString('pt-BR');
};

function gerarTabela(data) {
    //Limpar tabela
    const tBody = document.querySelector('#tabela-relatorio-contribuicao tbody');
    tBody.innerHTML = '';

    let valor = 0;

    data.forEach(element => {
        if (parseInt(element.status) === 1) {
            valor += parseFloat(element.valor);
        }

        const tr = document.createElement('tr');

        const tdCodigo = document.createElement('td');
        tdCodigo.innerText = element.codigo;

        const tdNome = document.createElement('td');
        tdNome.innerText = element.nomeSocio;

        const tdDataGeracao = document.createElement('td');
        tdDataGeracao.innerText = formatarData(element.dataGeracao);

        const tdDataVencimento = document.createElement('td');
        tdDataVencimento.innerText = formatarData(element.dataVencimento);

        const tdDataPagamento = document.createElement('td');
        tdDataPagamento.innerText = formatarData(element.dataPagamento);

        const tdValor = document.createElement('td');
        tdValor.innerText = parseFloat(element.valor).toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });

        const tdStatus = document.createElement('td');
        tdStatus.innerText = element.status == 1 ? 'Pago' : 'Pendente';

        const tdPlataforma = document.createElement('td');
        tdPlataforma.innerText = element.plataforma;

        const tdMeioPagamento = document.createElement('td');
        tdMeioPagamento.innerText = element.meio == 'Carne' ? 'Carnê' : element.meio;

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

        const tabela = document.getElementById('tabela-relatorio-contribuicao');
        const botaoImprimir = document.getElementById('relatorio-imprimir-btn');

        if (result.length < 1) {
            mensagemRelatorio.innerHTML = "Nenhuma contribuição foi encontrada com os filtros selecionados";
            document.querySelector('#tabela-relatorio-contribuicao tbody').innerHTML = '';

            // Oculta a tabela e o botão
            tabela.classList.add('hidden');
            botaoImprimir.classList.add('hidden');
        } else {
            //Informações para o resumo do relatório
            const valorPago = gerarTabela(result).toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
            const periodoSelecionado = document.querySelector('#periodo option:checked').textContent;
            const socioSelecionado = document.querySelector('#socio option:checked').textContent;
            const statusSelecionado = document.querySelector('#status option:checked').textContent;

            const agora = new Date();
            const dataFormatada = agora.toLocaleDateString('pt-BR');
            const horaFormatada = agora.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });

            const dataHoraFormatada = `${dataFormatada} às ${horaFormatada}`;

            mensagemRelatorio.innerHTML = `
            <h3>Relatório gerado</h3>
            <p><strong>Data e hora</strong>: ${dataHoraFormatada}</p>
            <p><strong>Período selecionado</strong>: ${periodoSelecionado}</p>
            <p><strong>Sócio selecionado</strong>: ${socioSelecionado}</p>
            <p><strong>Status de contribuição selecionado</strong>: ${statusSelecionado}</p>
            <p><strong>Valor total adquirido</strong>: ${valorPago}</p>
            `;

            // Exibe a tabela e o botão
            tabela.classList.remove('hidden');
            botaoImprimir.classList.remove('hidden');
        }
    } catch (error) {
        alert(`${error.message}`);
        console.error(error.message);
    } finally {
        relatorioBtn.innerHTML = 'Gerar relatório';
        relatorioBtn.disabled = false;
    }
});