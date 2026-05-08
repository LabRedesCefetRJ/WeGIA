
// Complementar ao sistema de mensgem de msg.php
// Adiciona a opção de fechar a mensagem e tirá-la da url
function closeMsg(){
    window.history.replaceState({}, document.title, window.location.pathname);
}