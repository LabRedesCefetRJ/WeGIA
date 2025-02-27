<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$hasConfig = file_exists("../config.php");
	if ($hasConfig){
		header("Location: ../html/home.php");
		exit();
	}
?>

<html>
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="css/theme.css">
	<?php function exibirVoltar(){echo '<a href="JavaScript: window.history.back();">Voltar</a><br><a href="../">Inicio</a>';} ?>
</head>
<body>
	<?php

		function getExtensao($filename){
			$file = explode(".", $filename);
			if (sizeof($file) < 2){
				return false;
			}
			return (end($file) != '' ? end($file) : false);
		}

		function validSqlFiles($fileList){
			$files = $fileList;
			foreach ($files as $key => $file){
				$extensao = getExtensao($file);
				if (!$extensao){
					unset($files[$key]);
					continue;
				}
				if ($extensao != "sql"){
					unset($files[$key]);
					continue;
				}
				if (stristr($file, "test")){
					unset($files[$key]);
					continue;
				}
			}
			return array_values($files);
		}

		function createInportFile($file, $new_file, $replace){
			$file = realpath($file);
			$new_file_content = fopen($new_file, "w");
			fwrite($new_file_content, str_replace("use wegia;", "use `$replace`;", str_replace("DROP DATABASE IF EXISTS wegia;", "DROP DATABASE IF EXISTS `$replace`;", str_replace("`wegia`", "`$replace`", file_get_contents($file)))));
		}


		$nomeDB = str_replace(' ', '_', $_POST["nomebd"]); // nome da base de dados
		$dbDir = scandir("../BD/");
		//$localsql = '../BD/WeGIA-criacao.sql';  //local arquivo .sql
		$local = $_POST["local"];//local servidor mysql
		$user = $_POST["usuario"];
		$senha = $_POST["senha"];
		$backup = $_POST["backup"];
		$www = $_POST["www"];
		$reinstalar = isset($_POST["reinstalar"]);
		$inport_dir = "$backup/";

		//Cria um diretório de Backup caso não haja um
		if (!is_dir($backup)) {
			if (!mkdir($backup, 0777, TRUE)){
				$backup = "";
			}
		}

		//Cria um config.php ou sobrescreve o anterior
		$file_name = realpath('../').'/config.php';
		$file = fopen($file_name, "w");


		fwrite($file, "<?php
/**
 *Configuração do WEGIA
*/
define( 'DB_NAME', '$nomeDB' );
define( 'DB_USER', '$user' );
define( 'DB_PASSWORD', '$senha' );
define( 'DB_HOST', '$local');
define( 'DB_CHARSET', 'utf8');
define( 'ROOT',dirname(__FILE__));
define( 'BKP_DIR', '$backup');
define( 'WWW', '$www');");


		echo('<p style="color:green;">config.php criado!</p>');
		if (!$backup){
			echo('<p style="color:orange;">Falha ao criar pasta de backup!</p>');
		}
		
		$sqlFiles = validSqlFiles($dbDir);

		foreach ($sqlFiles as $key => $file){
			createInportFile("../BD/$file", "$backup/$file", "$nomeDB");
		}

		/*conexao*/
		$conn = new mysqli($local, $user, $senha);
		verificarConexao($conn->connect_errno);//verificar se conexao foi estabelecida
		
		/*verificar reinstalar*/	
		if(mysqli_select_db ($conn, $nomeDB))//verificar se db já existe
		{
			if(!$reinstalar)//verificar se opçao de reinstalar foi marcada
			{
				$conn->close();
				echo '<p style="color:orange;">Base de dados já existe!</p>';
				exibirVoltar();
			}
		}else{
			$conn->query("CREATE DATABASE ".$nomeDB.";");//criar db
		}


		/*importar base de dados*/
		if ($reinstalar){
			if (PHP_OS != "Linux"){
				// Caso o Sistema não seja Linux
				echo("<p style='color:orange;'>ATENÇÃO: O Sistema é mais instável se executado em um sistema operacional diferente do Linux. O seu sistema atual é: ".PHP_OS."<p>");
				foreach ($sqlFiles as $key => $file){
					$sql = file_get_contents($inport_dir . $file);
					
					$mysqli = new mysqli("$local", "$user", "$senha", "$nomeDB");
					
					/* execute multi query */
					if ($mysqli->multi_query($sql) === true){
						echo("<p style='color:green;'>Arquivo $file importado para a Base de Dados</p>");
					} else {
						echo '<p style="color:red;">Falha ao inserir os dados iniciais no banco de dados: </p><br><pre>' . $mysqli->error . '</pre></br>';
					}
				}
			}else{
				foreach ($sqlFiles as $key => $file){
					$log = shell_exec("mysql --default-character-set=utf8 -u $user -p$senha $nomeDB < ".realpath($inport_dir . $file)."");
					if (!$log){
						echo("<p style='color:green;'>$file importado com sucesso<p>");
					}else{
						echo("<p style='color:red;'>Log da importação do arquivo $file<pre>$log</pre></p>");
					}
				}
			}
		}

		/*
		$lines = file($localsql);
		$op_data = '';
		foreach ($lines as $line)
		{
		    if (substr($line, 0, 2) == '--' || $line == '')//ignora linhas comentadas ou vazias
		    	continue;

		    if(strtoupper(ltrim(substr($line, 0, 15))) == 'CREATE DATABASE') //ignora create database
		    	continue;

		    if(strtoupper(ltrim(substr($line, 0, 3))) == 'USE')//ignora use database
		    	continue;

		    $op_data .= $line; //buffer
		    if (substr(trim($line), -1, 1) == ';')//Breack Line Upto ';' NEW QUERY
		    {
		        $conn->query($op_data);
		        $op_data = '';
		    }
		}
		*/
		
		/*verificar se base de dados foi criada*/
		if ($reinstalar){
			if(mysqli_select_db ($conn, $nomeDB)) 
				echo ( $reinstalar ? '<p style="color:green;">Base de dados ' .$nomeDB .' importada!</p>' : '');
			else
				die('<p style="color:red;">Falha na criaçao do banco de dados! Verifique se o nome do banco de dados foi inserido corretamente e se o usuario "' .$user .'" possui permissão para criar banco de dados.</p>');
		}

		/*configurar arquivo conexao dao*/
		//configurarConexaoDao($local,$nomeDB, $user, $senha);


		$conn->close();
		exibirVoltar();


		/*funcoes*/
		//Verificar e tratar erros de conexao 
		function verificarConexao($erro)
		{
			if($erro == 0) return;
			$msg = 'Erro '.$erro .': ';

			switch ($erro) {
				case 1045:
					$msg .='Nome de usuario e/ou senha incorreto(s).';
					break;
				case 2002:
					$msg .= 'Servidor MYSQL não encontrado.';
					break;
			}
			echo '<p style="color:red;">' .$msg .'</p>';
			exibirVoltar();
			die();
		}

		//Configurar arquivo 'Conexao' da pasta Dao
		/*
		function configurarConexaoDao($host, $dbname, $user, $senha){
			$localDao = '../dao/conexao.php';
			$lines = file($localDao);
			$saida = '';
			foreach ($lines as $line) {
				if(strpos($line, 'new PDO') != false)//achar linha a ser alterada
				{
					$comando = explode("=", $line);//separar nome da variavel original e instanciação
					$saida .= $comando[0] ."=";//guardar nome original da variavel
					$saida .= " new PDO('mysql:host=" .$host ."; dbname=" .$dbname ."','" .$user ."','" .$senha ."');\n"; //nova instanciação
					continue;
				}

				$saida .= $line;
			}

			file_put_contents($localDao, $saida);
			echo '<p style="color:green;">Arquivo conexao.php alterado com sucesso!</p>';
		}*/
		
	?>
	
</body>
</html>