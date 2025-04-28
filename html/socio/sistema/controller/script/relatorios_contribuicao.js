const formRelatorio = document.getElementById('form-relatorio-contribuicao');

formRelatorio.addEventListener('submit', async function (ev) {
    ev.preventDefault();

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
    } catch (error) {
        alert(`${error.message}`);
        console.error(error.message);
    } finally {
        relatorioBtn.innerHTML = 'Gerar relatório';
        relatorioBtn.disabled = false;
    }
});