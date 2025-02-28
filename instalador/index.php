
<?php
	$hasConfig = file_exists("../config.php");
	if ($hasConfig){
		header("Location: ../html/home.php");
		exit();
	}
?>
<html>
 <head>
 	<meta charset="UTF-8">
  	<title>Instalador</title>
  	<link rel="stylesheet" type="text/css" href="css/theme.css">
  	<script type="text/javascript">
  		function validarForm(){
  			var nomebd = document.getElementsByName("nomebd")[0].value.trim();
  			var local = document.getElementsByName("local")[0].value.trim();
  			var usuario = document.getElementsByName("usuario")[0].value.trim();

  			if(nomebd == ''){alert("campo 'banco de dados' não preenchido");return false};
  			if(local== ''){alert("campo 'Local' não preenchido");return false};
  			if(usuario == ''){alert("campo 'Usuario' não preenchido");return false};
  	
  			nomebd = nomebd.replace(/ /g, '_');
  			document.getElementsByName("nomebd")[0].value = nomebd;
  			document.getElementsByName("local")[0].value = local;
  			//document.getElementsByName("usuario")[0].value = usuario;
  			return true;
  		}

  	</script>
 </head>
 <body>
	 <form action="instalador.php" onsubmit="return validarForm()" method="post">
		 <div>
			 <p>Preencha o formulário com as informações necessárias para instalar o software, ou em caso de dúvidas, acesse o <a href="../manual/instalacao.php">Manual de Instalação do Wegia</a>.</p>
		 </div>
		<h5 class="required">Campos Obrigatórios: (*)</h5>

		<p>
			Banco de Dados (BD)<hr>
		</p>
		<label for="nomebd"><span class="required">*</span>Nome do BD: </label>
		<input type="text" name="nomebd" value="wegia" required> 

		<label for="local"><span class="required">*</span>Host do BD: </label>
		<input type="text" name="local" value="localhost" required> 
		
		<label for="usuario"><span class="required">*</span>Usuario do BD: </label>
		<input type="text" name="usuario" value="root" required>

		<label for="senha"><span class="required"></span>Senha do BD: </label>
		<input type="password" name="senha">
		
		<p><input type="checkbox" name="reinstalar" value="">Reinstalar base de dados</p>

		<p>
			Arquivos e Domínio<hr>
		</p>
		
		<label for="backup"><span class="required">*</span>Caminho para a pasta de Backups: </label>
		<input type="text" name="backup" placeholder="/pasta/de/backup/" required> 
		
		<label for="local"><span class="required">*</span>Domínio do site (url): </label>
		<input type="text" name="www" placeholder="https://url.do.site/" required> 

		<input type="submit" value="Instalar">
	</form>
 </body>
</html>