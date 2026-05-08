document.addEventListener('DOMContentLoaded', function() {
    const btnUnica = document.getElementById('btn-doacao-unica');
    const btnMensal = document.getElementById('btn-doacao-mensal');
    const opcoesUnica = document.getElementById('opcoes-unica');
    const opcoesMensal = document.getElementById('opcoes-mensal');
    
    //Função para alternar entre doação única e mensal
    function alterarTipoDoacao(unica) {
        if (unica) {
            //Mostrar doação única
            btnUnica.classList.remove('btn-outline-primary');
            btnUnica.classList.add('btn-primary');
            btnMensal.classList.remove('btn-primary');
            btnMensal.classList.add('btn-outline-primary');
            opcoesUnica.style.display = 'block';
            opcoesMensal.style.display = 'none';
        } else {
            //Mostrar doação mensal
            btnMensal.classList.remove('btn-outline-primary');
            btnMensal.classList.add('btn-primary');
            btnUnica.classList.remove('btn-primary');
            btnUnica.classList.add('btn-outline-primary');
            opcoesUnica.style.display = 'none';
            opcoesMensal.style.display = 'block';
        }
    }

    btnUnica.addEventListener('click', function() {
        alterarTipoDoacao(true);
    });
    
    btnMensal.addEventListener('click', function() {
        alterarTipoDoacao(false);
    });
});