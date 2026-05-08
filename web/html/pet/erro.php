<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<script>
        // Função para sanitizar a entrada
        function sanitizarEntrada(entrada) {
            // Remove caracteres que não são alfanuméricos
            return entrada.replace(/[^a-zA-Z0-9]/g, '');
        }

        // Função para validar se id_pet é um número válido
        function eIdValido(id) {
            // Verifica se id é um número e maior que zero
            return !isNaN(id) && parseInt(id, 10) > 0;
        }

        // Obtém a URL atual
        let urlAtual = window.location.href;

        // Extrai a string de consulta da URL
        let queryString = urlAtual.split('?')[1];
        let parametros = new URLSearchParams(queryString);

        // Recupera id_pet da string de consulta
        let idPet = parametros.get('id_pet');

        if (idPet) {
            // Sanitiza o id_pet
            let idPetSanitizado = sanitizarEntrada(idPet);

            // Valida o id_pet sanitizado
            if (eIdValido(idPetSanitizado)) {
                // Redireciona para a nova página com id_pet sanitizado
                window.location.href = `./profile_pet.php?id_pet=${idPetSanitizado}`;
            } else {
                // Exibe um alerta se id_pet for inválido
                alert("ID do pet inválido. Verifique os dados e tente novamente.");
            }
        } else {
            // Exibe um alerta se id_pet estiver ausente
            alert("ID do pet não fornecido. Verifique os dados e tente novamente.");
        }
    </script>
</body>
</html>