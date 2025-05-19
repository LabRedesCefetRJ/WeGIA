<?php
	session_start();
	if(!isset($_SESSION['usuario']))
	{
		header ("Location: ../index.php");
	}

	$config_path = "config.php";
	if(file_exists($config_path))
	{
		require_once($config_path);
	}
	else
	{
		while(true)
		{
			$config_path = "../" . $config_path;
			if(file_exists($config_path)) break;
		}
		require_once($config_path);
	}
	
	$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$id_pessoa = $_SESSION['id_pessoa'];
	$resultado = mysqli_query($conexao, "SELECT * FROM funcionario WHERE id_pessoa=$id_pessoa");
	if(!is_null($resultado))
	{
		$id_cargo = mysqli_fetch_array($resultado);
		if(!is_null($id_cargo))
		{
			$id_cargo = $id_cargo['id_cargo'];
		}
        $resultado = mysqli_query($conexao, "SELECT * FROM permissao p JOIN acao a ON(p.id_acao=a.id_acao) JOIN recurso r ON(p.id_recurso=r.id_recurso) WHERE id_cargo=$id_cargo AND a.descricao = 'LER, GRAVAR E EXECUTAR' AND r.descricao='Cadastrar Pet'");
		if(!is_bool($resultado) and mysqli_num_rows($resultado))
		{
			$permissao = mysqli_fetch_array($resultado);
			if($permissao['id_acao'] < 5)
			{
        		$msg = "Você não tem as permissões necessárias para essa página.";
        		header("Location: ../../home.php?msg_c=$msg");
			}
			$permissao = $permissao['id_acao'];
		}
		else
		{
        	$permissao = 1;
          	$msg = "Você não tem as permissões necessárias para essa página.";
          	header("Location: ../../home.php?msg_c=$msg");
		}	
	}
	else
	{
		$permissao = 1;
    	$msg = "Você não tem as permissões necessárias para essa página.";
    	header("Location: ../../home.php?msg_c=$msg");
	}	

	// Lógica para listar os adotantes
	$dsn = 'mysql:host=localhost;dbname=wegia;charset=utf8';  
	$username = 'wegiauser'; 
	$password = 'senha';
	try {
		$conexao = new PDO($dsn, $username, $password);
		$conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
		$sqlListarAdotantes = "SELECT cpf, nome, sobrenome, sexo, telefone, data_nascimento, imagem, cep, 
										estado, cidade, bairro, logradouro, numero_endereco, complemento
								FROM pessoa;";
		
		$stmt = $conexao->prepare($sqlListarAdotantes);
		$stmt->execute();
	
		$resultadosListarAdotantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

	} catch (PDOException $e) {
		echo 'Erro ao conectar ao banco de dados: ' . $e->getMessage();
	}
?>	

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<title>Informações dos Adotantes</title>

	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/select2/select2.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/stylesheets/theme.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>/assets/stylesheets/skins/default.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/stylesheets/theme-custom.css">

    <script src="<?php echo WWW;?>assets/vendor/modernizr/modernizr.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/jquery-autosize/jquery.autosize.js"></script>
    <script src="<?php echo WWW;?>assets/javascripts/theme.js"></script>
    <script src="<?php echo WWW;?>assets/javascripts/theme.custom.js"></script>
    <script src="<?php echo WWW;?>assets/javascripts/theme.init.js"></script>
    <script src="<?php echo WWW;?>Functions/onlyNumbers.js"></script>
    <script src="<?php echo WWW;?>Functions/onlyChars.js"></script>
    <script src="<?php echo WWW;?>Functions/mascara.js"></script>
    <script src="<?php echo WWW;?>Functions/testaCPF.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/jasonday-printThis-f73ca19/printThis.js"></script>
 
    <script>
      $(function(){
          $("#header").load("<?php echo WWW;?>html/header.php");
          $(".menuu").load("<?php echo WWW;?>html/menu.php");
      });
    </script>
	</head>
</head>
<body>
	<section class="body">
		<div id="header"></div>
        <div class="inner-wrapper">
          <aside id="sidebar-left" class="sidebar-left menuu"></aside>
			<section role="main" class="content-body">
				<header class="page-header">
					<h2>Informações</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li><a href="../../index.php"> <i class="fa fa-home"></i>
							</a></li>
							<li><span>Informações dos Adotantes</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>
				<section class="panel">
					<header class="panel-heading">
						<div class="panel-actions">
							<a href="#" class="fa fa-caret-down"></a>
						</div>
						<h2 class="panel-title">Adotantes</h2>
					</header>
					<div class="panel-body">
						<table class="table table-bordered table-striped mb-none"
							id="datatable-default">
							<thead>
								<tr>
									<th>Nome</th>
									<th>Sobrenome</th>
									<th>Telefone</th>
									<th>Nascimento</th>
									<th>Cpf</th>
									<th>Cep</th>
									<th>UF</th>
									<th>Cidade</th>
									<th>Bairro</th>
									<th>Logradouro</th>									
									<th>Número Residencial</th>
									<th>Complemento</th>
								</tr>
							</thead>
							<tbody id="tabela">
	  						<?php
								// Listagem de todos os adotantes
								foreach ($resultadosListarAdotantes as $linha) {

									if (!empty($linha['cpf']) && !empty($linha['nome']) && !empty($linha['sobrenome']) && 
										!empty($linha['telefone']) && !empty($linha['data_nascimento']) && !empty($linha['cep']) && 
										!empty($linha['estado']) && !empty($linha['cidade']) && !empty($linha['bairro']) && 
										!empty($linha['logradouro']) && !empty($linha['numero_endereco']) && !empty($linha['complemento'])) {
					
										echo '<tr>';
										echo '<td>' . $linha['nome'] . '</td>';
										echo '<td>' . $linha['sobrenome'] . '</td>';
										echo '<td>' . $linha['telefone'] . '</td>';
										echo '<td>' . date('d/m/Y', strtotime($linha['data_nascimento'])) . '</td>'; // Formatar a data
										echo '<td>' . $linha['cpf'] . '</td>';
										echo '<td>' . $linha['cep'] . '</td>';
										echo '<td>' . $linha['estado'] . '</td>';
										echo '<td>' . $linha['cidade'] . '</td>';
										echo '<td>' . $linha['bairro'] . '</td>';
										echo '<td>' . $linha['logradouro'] . '</td>';
										echo '<td>' . $linha['numero_endereco'] . '</td>';
										echo '<td>' . $linha['complemento'] . '</td>';
										echo '</tr>';
									}
								}
								?>
							</tbody>
						</table>
					</div>
					<br>
				</section>
		
		<div align="right">
	  		<iframe src="https://www.wegia.org/software/footer/pet.html" width="200" height="60" style="border:none;"></iframe>
  		</div>
	</body>

    <!-- SCRIPTS IMPORTANTES -->
    <script src="../../../assets/vendor/modernizr/modernizr.js"></script>
	<script src="../../../assets/vendor/jquery/jquery.min.js"></script>
	<script src="../../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="../../../assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="../../../assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="../../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="../../../assets/vendor/magnific-popup/magnific-popup.js"></script>
	<script src="../../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
	<script src="../../../assets/vendor/jquery-autosize/jquery.autosize.js"></script>	
	<script src="../../../assets/javascripts/theme.js"></script>	
	<script src="../../../assets/javascripts/theme.custom.js"></script>
	<script src="../../../assets/javascripts/theme.init.js"></script>
	<script src="../../../Functions/onlyNumbers.js"></script>
	<script src="../../../Functions/onlyChars.js"></script>
	<script src="../../../Functions/enviar_dados.js"></script>
	<script src="../../../Functions/mascara.js"></script>
	<script src="../../../assets/vendor/select2/select2.js"></script>
	<script src="../../../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
	<script src="../../../assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
	<script src="../../../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
	<script src="../../../assets/javascripts/theme.js"></script>
	<script src="../../../assets/javascripts/theme.custom.js"></script>
	<script src="../../../assets/javascripts/theme.init.js"></script>
	<script src="../../../assets/javascripts/tables/examples.datatables.default.js"></script>
	<script src="../../../assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
	<script src="../../../assets/javascripts/tables/examples.datatables.tabletools.js"></script>
</body>
</html>