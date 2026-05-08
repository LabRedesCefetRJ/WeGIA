<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if(session_status() === PHP_SESSION_NONE)
	session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: ../../index.php");
	exit();
}else{
	session_regenerate_id();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 9);

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if(!$conexao){
	http_response_code(500);
	exit("Erro ao estabelecer conexão");
}

// Função para obter as branches de um repositório no GitHub usando a API
function getGitHubBranches($owner, $repo)
{
	$url = "https://api.github.com/repos/$owner/$repo/branches";
	$options = [
		'http' => [
			'header' => "User-Agent: My-App\r\n",
		],
	];
	$context = stream_context_create($options);
	$response = file_get_contents($url, false, $context);
	return json_decode($response, true);
}

function getCurrentBranch()
{
	$output = array();
	exec("git -C " . ROOT . " branch | grep '*' | cut -d ' ' -f2", $output);
	return $output[0];
}

// Função para fazer o checkout para uma branch
function gitCheckout($branch)
{
	$output = array();
	exec("git -C " . ROOT . " checkout " . escapeshellarg($branch), $output);
	return $output;
}

// Obtém as branches do repositório
$owner = "nilsonLazarin";
$repo = "WeGIA";
$branches = [];
try {
	$branchesData = getGitHubBranches($owner, $repo);
	foreach ($branchesData as $branch) {
		$branches[] = $branch['name'];
	}
} catch (Exception $e) {
	echo "Erro ao obter as branches do GitHub: " . $e->getMessage();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['branch'])) {
	$branch = filter_input(INPUT_POST, 'branch', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

	if (!preg_match('/^[a-zA-Z0-9._\-\/]+$/', $branch)) {
		http_response_code(400);
		exit("Branch inválida.");
	}

	if ($action == "switch") {
		$output = gitCheckout($branch);
	} elseif ($action == "update") {
		header("Location: atualizacao.php");
	}
}

// Inclui display de Campos
require_once "../personalizacao_display.php";

// Adiciona o Sistema de Mensagem
require_once "../geral/msg.php";

?>
<!doctype html>
<html class="fixed">

<head>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Configurações Gerais</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Web Fonts  -->
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
	<link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon">

	<!-- Theme CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="../../assets/vendor/modernizr/modernizr.js"></script>

	<!-- Configuração CSS -->
	<link rel="stylesheet" href="../../css/configuracao.css" />

	<!-- Vendor -->
	<script src="../../assets/vendor/jquery/jquery.min.js"></script>
	<script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
	<script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

	<!-- Specific Page Vendor -->
	<script src="../../assets/vendor/jquery-autosize/jquery.autosize.js"></script>

	<!-- Theme Base, Components and Settings -->
	<script src="../../assets/javascripts/theme.js"></script>

	<!-- Theme Custom -->
	<script src="../../assets/javascripts/theme.custom.js"></script>

	<!-- Theme Initialization Files -->
	<script src="../../assets/javascripts/theme.init.js"></script>

	<!-- javascript functions -->
	<script
		src="../../Functions/onlyNumbers.js"></script>
	<script
		src="../../Functions/onlyChars.js"></script>
	<script
		src="../../Functions/mascara.js"></script>

	<!-- jquery functions -->
	<script>
		document.write('<a href="' + document.referrer + '"></a>');
	</script>

	<script type="text/javascript">
		$(function() {
			$("#header").load("../header.php");
			$(".menuu").load("../menu.php");
		});
	</script>

	<!-- javascript tab management script -->

	<style>
		.btn {
			width: 10%;
		}

		.config-item {
			margin: 20px 0;
		}

		.config-item form {
			display: flex;
			align-items: center;
		}

		.config-item label,
		.config-item select,
		.config-item button {
			margin: 0 10px;
		}

		.config-item button {
			background-color: #4CAF50;
			/* Verde */
			border: none;
			color: white;
			padding: 15px 32px;
			text-align: center;
			text-decoration: none;
			display: inline-block;
			font-size: 16px;
			transition-duration: 0.4s;
			cursor: pointer;

			border-radius: 12px;

			width: 150px;
		}

		.config-item button:hover {
			background-color: #45a049;

			transform: scale(1.1);
		}
	</style>

</head>

<body>
	<section class="body">
		<div id="header"></div>
		<!-- end: header -->
		<div class="inner-wrapper">
			<!-- start: sidebar -->
			<aside id="sidebar-left" class="sidebar-left menuu"></aside>
			<!-- end: sidebar -->
			<section role="main" class="content-body">
				<header class="page-header">
					<h2>Informações de debug</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="../home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Informações de debug</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>
				<!--start: page-->

				<!-- Caso haja uma mensagem do sistema -->
				<?php displayMsg();
				getMsgSession("mensagem", "tipo"); ?>

				<session class="panel">
					<header class="panel-heading">
						<div class="panel-actions">
							<a href="#" class="fa fa-caret-down"></a>
						</div>
						<h2 class="panel-title">Informações para desenvolvedores</h2>
					</header>
					<div class="panel-body">
						<div class="ls-container">
							<div class="space-between">
								<div>Versão do php:</div>
								<p><?php echo (phpversion()); ?></p>
							</div>
							<div class="space-between">
								<div>Servidor:</div>
								<p><?php echo (php_uname()); ?></p>
							</div>
							<div class="space-between">
								<div>Servidor do banco de dados:</div>
								<p><?php echo (mysqli_get_server_info($conexao)); ?></p>
							</div>
							<div class="space-between">
								<div>Charset padrão banco de dados:</div>
								<p><?php echo (mysqli_get_charset($conexao)->charset . " - colação: " . mysqli_get_charset($conexao)->collation); ?></p>
							</div>
							<div>
								<div>Status banco de dados:</div>
								<p class="float-right" style="float: right">

									<?php if (mysqli_get_connection_stats($conexao)) {
										echo (' <div style="right: 0; margin-right: 0" class="badge badge-success text-wrap">
                                        Ativo
                                    </div>');
									} else {
										echo ('<div class="alert alert-danger" role="alert">
                                        O banco de dados está inativo ou com problemas!
                                        </div>');
									}
									?></p>
							</div>
							<div class="space-between">
								<div>Tamanho banco de dados:</div>
								<p class="float-right" style="float: right">

									<?php echo (mysqli_fetch_assoc(mysqli_query($conexao, "SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size' FROM information_schema.TABLES WHERE table_schema = 'wegia' GROUP BY table_schema"))['size'] . " MB");
									?></p>
							</div>
							<div>
								<div class="config-item">
									<div>Atualizar sistema para versão em desenvolvimento:</div>
									<form action="debug_info.php" method="post">
										<label for="branch">Escolha a branch para atualizar:</label>
										<select name="branch" id="branch">
											<?php foreach ($branches as $branch): ?>
												<option value="<?php echo htmlspecialchars($branch); ?>" <?php if ($branch == getCurrentBranch()) echo 'style="color: #32CD32;"'; ?>><?php echo htmlspecialchars($branch); ?></option>
											<?php endforeach; ?>
										</select>
										<button type="submit" name="action" value="switch">Trocar branch</button>
										<button type="submit" name="action" value="update">Atualizar</button>
									</form>
								</div>
							</div>
						</div>

						<div>
							<div>Parâmetros de configuração php:</div>
							<p><?php
								function phpinfo_array()
								{
									ob_start();
									phpinfo(INFO_CONFIGURATION);
									$info_arr = array();
									$info_lines = explode("\n", strip_tags(ob_get_clean(), "<tr><td><h2>"));
									$cat = "General";
									foreach ($info_lines as $line) {
										// new cat?
										preg_match("~<h2>(.*)</h2>~", $line, $title) ? $cat = $title[1] : null;
										if (preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val)) {
											$info_arr[$cat][$val[1]] = $val[2];
										} elseif (preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val)) {
											$info_arr[$cat][$val[1]] = array("local" => $val[2], "master" => $val[3]);
										}
									}
									return $info_arr;
								}

								// example:
								echo "<pre>" . print_r(phpinfo_array(), 1) . "</pre>";
								?></p>
						</div>
					</div>
		</div>
		</session>
	</section>
	</div>
	</section>

	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/conf.html" width="200" height="60" style="border:none;"></iframe>
	</div>

</body>
<script>
	function setLoader(btn) {
		btn.firstElementChild.style.display = "none";
		if (btn.childElementCount == 1) {
			loader = document.createElement("DIV");
			loader.className = "loader";
			btn.appendChild(loader);
		}
		window.location.href = btn.firstElementChild.href;
	}
</script>

<!-- Adiciona função de fechar mensagem e tirá-la da url -->
<script src="../geral/msg.js"></script>

</html>