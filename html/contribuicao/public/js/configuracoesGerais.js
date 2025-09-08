$(function () {//Carrega cabeçalho e menu lateral
    $("#header").load("../../header.php");
    $(".menuu").load("../../menu.php");
});

/**
 * Trava de segurança para evitar exclusão de itens indesejados
 * @returns 
 */
function confirmarExclusao() {
    return confirm("Tem certeza que deseja excluir este item?");
}

/**Extraí os dados necessários da view e envia um fetch de POST para a URL informada */
function alterarStatus(ev, URL, controller) {
    const toggleId = ev.target.id; // ID do toggle clicado
    const isChecked = ev.target.checked; // Verifica se está marcado ou não

    // Usando expressão regular para extrair o número
    const idNumber = toggleId.match(/\d+/)[0]; // Extrai o número após 'toggle'
    // Montar os dados para enviar no POST

    data = new URLSearchParams();
    data.append('id', idNumber);
    data.append('status', isChecked);
    data.append('nomeClasse', controller);
    data.append('metodo', 'alterarStatus');

    // Enviar dados via fetch (POST)
    fetch(URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: data.toString() // Converte o objeto em uma string URL-encoded
    })
        .then(response => {
            if (response.ok) {
                return response.json(); // Se necessário, processa a resposta
            } else {
                return response.json().then(errData => {
                    // Lança o erro com a mensagem extraída do backend
                    throw new Error(errData.erro || 'Erro desconhecido no servidor');
                });
            }
        })
        .then(result => {
            console.log('Resultado:', result); // Processa a resposta do servidor, se houver
        })
        .catch(error => {
            alert(error);
        });
}