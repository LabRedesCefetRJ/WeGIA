<?php
	require("./conexao.php");
	if(!$conexao) header("Location: ./erros/bd_erro/");
	session_start();
	if(isset($_SESSION['usuario'])){
		header("Location: ./sistema/");exit();
	}

	//Desativação temporária da tela SaGA, considerar a remoção ou a continuidade do desenvolvimento
	header("Location: /WeGIA/index.php");exit();

	// Adiciona a Função display_campo($nome_campo, $tipo_campo)
	require_once ROOT."/html/personalizacao_display.php";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<title>SaGA - <?php display_campo("Titulo","str");?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
<!--===============================================================================================-->	
	<link rel="icon" type="image/png" href="login/images/icons/favicon.ico"/>
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="login/vendor/bootstrap/css/bootstrap.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="login/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="login/fonts/Linearicons-Free-v1.0.0/icon-font.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="login/vendor/animate/animate.css">
<!--===============================================================================================-->	
	<link rel="stylesheet" type="text/css" href="login/vendor/css-hamburgers/hamburgers.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="login/vendor/animsition/css/animsition.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="login/vendor/select2/select2.min.css">
<!--===============================================================================================-->	
	<link rel="stylesheet" type="text/css" href="login/vendor/daterangepicker/daterangepicker.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="login/css/util.css">
	<link rel="stylesheet" type="text/css" href="login/css/main.css">
	<script src="https://kit.fontawesome.com/d7eb01c67c.js" crossorigin="anonymous"></script>
<!--===============================================================================================-->
<script src="../../Functions/onlyChars.js"></script>
</head>
<body style="background-color: #666666;">
	
	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">
				<form class="login100-form validate-form" action="login.php" method="post">
					<span class="login100-form-title p-b-43">
					<?php display_campo("Titulo","str");?> - SaGA
					</span>
					
					
					<div class="wrap-input100 validate-input" data-validate = "Digite um usuário válido!">
						<input class="input100" type="text" name="usuario">
						<span class="focus-input100"></span>
						<span class="label-input100">Usuário</span>
					</div>
					
					
					<div class="wrap-input100 validate-input" data-validate="Digite sua senha!">
						<input class="input100" type="password" name="senha">
						<span class="focus-input100"></span>
						<span class="label-input100">Senha</span>
					</div>
			

					<div class="container-login100-form-btn">
						<button class="login100-form-btn">
							Login
						</button>
					</div>

					<div class="alert alert-dark mt-4" role="alert">
						<?php display_campo("Subtitulo","str");?>
					</div>
				</form>


				<div class="login100-more" style="background-image: url('<?php display_campo("Logo","file");?>');">
				</div>
			</div>
		</div>
	</div>
	
	

	
	
<!--===============================================================================================-->
	<script src="login/vendor/jquery/jquery-3.2.1.min.js"></script>
<!--===============================================================================================-->
	<script src="login/vendor/animsition/js/animsition.min.js"></script>
<!--===============================================================================================-->
	<script src="login/vendor/bootstrap/js/popper.js"></script>
	<script src="login/vendor/bootstrap/js/bootstrap.min.js"></script>
<!--===============================================================================================-->
	<script src="login/vendor/select2/select2.min.js"></script>
<!--===============================================================================================-->
	<script src="login/vendor/daterangepicker/moment.min.js"></script>
	<script src="login/vendor/daterangepicker/daterangepicker.js"></script>
<!--===============================================================================================-->
	<script src="login/vendor/countdowntime/countdowntime.js"></script>
<!--===============================================================================================-->
	<script src="login/js/main.js"></script>

	<div align="right">
	<iframe src="https://www.wegia.org/software/footer/socio.html" width="200" height="60" style="border:none;"></iframe>
	</div>
</body>
</html>
