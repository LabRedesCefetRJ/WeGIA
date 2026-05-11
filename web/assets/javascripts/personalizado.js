$(document).ready(function(){
    $('#form-cadastro').on("submit", function(event){
        event.preventDefault();
       
        var dados = $("#form-cadastro").serialize();
        alert(dados);
    }) 
});