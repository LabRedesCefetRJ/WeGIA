<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
    <form id="formInfoAdicional">
        <div class="form-group">
            <label for="descricao_addInfo" class="col-form-label">Descrição</label>
            <div style="display: block ruby;">
            <select name="id_descricao" id="descricao_addInfo" class="form-control" style="width: 300px;" required>
                <option selected disabled>Selecionar</option>
                <option>Selecionar</option>
                <option>A</option>
            </select>
            <a onclick="adicionar_addInfoDescricao()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw; margin-left: 10px;"></i></a>
            </div>
        </div>
        <div class="form-group">
            <label for="dados_addInfo" class="col-form-label">Dados</label>
            <textarea class="form-control" id="dados_addInfo" style="padding: 6px 12px; height: 120px;" name="dados" maxlength="255" required></textarea>
        </div>
        <!-- -->
        <input type="submit" value="enviar">
    </form>

    <script>
        let form = document.getElementById("formInfoAdicional");
        form.addEventListener('submit', function(event){
            event.preventDefault();
            let descricao = document.getElementById("descricao_addInfo").value;
            let dado = document.getElementById("dados_addInfo").value;
            console.log(descricao);
            console.log(dado);

           
        })
    </script>
</body>
</html>