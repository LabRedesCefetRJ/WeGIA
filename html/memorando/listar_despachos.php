<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);*/

$config_path = "config.php";
if (file_exists($config_path)) {
	require_once($config_path);
} else {
	while (true) {
		$config_path = "../" . $config_path;
		if (file_exists($config_path)) break;
	}
	require_once($config_path);
}
session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: " . WWW . "html/index.php");
}

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";
require_once ROOT . "/dao/Conexao.php";
require_once ROOT . "/html/memorando/gerar_tabela_impressao.php";

$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$id_pessoa = $_SESSION['id_pessoa'];
$resultado = mysqli_query($conexao, "SELECT * FROM funcionario WHERE id_pessoa=$id_pessoa");
if (!is_null($resultado)) {
	$id_cargo = mysqli_fetch_array($resultado);
	if (!is_null($id_cargo)) {
		$id_cargo = $id_cargo['id_cargo'];
	}
	$resultado = mysqli_query($conexao, "SELECT * FROM permissao WHERE id_cargo=$id_cargo and id_recurso=3");
	if (!is_bool($resultado) and mysqli_num_rows($resultado)) {
		$permissao = mysqli_fetch_array($resultado);
		if ($permissao['id_acao'] == 1) {
			$msg = "Você não tem as permissões necessárias para essa página.";
			header("Location: " . WWW . "/html/home.php?msg_c=$msg");
		}
		$permissao = $permissao['id_acao'];
	} else {
		$permissao = 1;
		$msg = "Você não tem as permissões necessárias para essa página.";
		header("Location: " . WWW . "/html/home.php?msg_c=$msg");
	}
} else {
	$permissao = 1;
	$msg = "Você não tem as permissões necessárias para essa página.";
	header("Location: " . WWW . "/html/home.php?msg_c=$msg");
}

require_once ROOT . "/controle/memorando/DespachoControle.php";
require_once ROOT . "/controle/FuncionarioControle.php";
require_once ROOT . "/controle/memorando/MemorandoControle.php";
require_once ROOT . "/controle/memorando/AnexoControle.php";
require_once ROOT . '/controle/memorando/StatusMemorandoControle.php';


$id_memorando = filter_input(INPUT_GET, 'id_memorando', FILTER_VALIDATE_INT);

if(!$id_memorando || $id_memorando < 1){
	http_response_code(400);
	echo json_encode(['erro' => 'O id do memorando informado não está dentro dos limites permitidos.']);
	exit();
}

//Cria novos objetos (Despachos)
$despachos = new DespachoControle;
$despachos->listarTodos();

$despachos2 = new DespachoControle;
$despachos2->listarTodosComAnexo();

//Cria novo objeto (FuncionarioControle)
$funcionarios = new FuncionarioControle;
$funcionarios->listarTodos2();

//Cria novo objeto (MemorandoControle)
$ultimoDespacho =  new MemorandoControle;
$ultimoDespacho->buscarUltimoDespacho($id_memorando);

//Cria novo objeto (AnexoControle)
$Anexos = new AnexoControle;
$Anexos->listarTodos($id_memorando);

//Cria novos objetos (MemorandoControle)
$id_status = new MemorandoControle;
$id_status->buscarIdStatusMemorando($id_memorando);

$memorandosDespachados = new MemorandoControle;
$memorandosDespachados->listarIdTodosInativos();

// var_dump($memorando);

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";
?>

<!DOCTYPE html>

<html class="fixed">

<head>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Despachos</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
	<!-- Vendor CSS -->
	<link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
	<link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

	<!-- Specific Page Vendor CSS -->
	<link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/select2/select2.css" />
	<link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

	<!-- Theme CSS -->
	<link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/theme-custom.css">

	<!-- Impressão CSS -->
	<link rel="stylesheet" href="<?php echo WWW; ?>css/impressao.css">

	<!-- Head Libs -->
	<script src="<?php echo WWW; ?>assets/vendor/modernizr/modernizr.js"></script>

	<!-- Vendor -->
	<script src="<?php echo WWW; ?>assets/vendor/jquery/jquery.min.js"></script>
	<script src="<?php echo WWW; ?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="<?php echo WWW; ?>assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="<?php echo WWW; ?>assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="<?php echo WWW; ?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="<?php echo WWW; ?>assets/vendor/magnific-popup/magnific-popup.js"></script>
	<script src="<?php echo WWW; ?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

	<!-- Specific Page Vendor -->
	<script src="<?php echo WWW; ?>assets/vendor/jquery-autosize/jquery.autosize.js"></script>

	<!-- Theme Base, Components and Settings -->
	<script src="<?php echo WWW; ?>assets/javascripts/theme.js"></script>

	<!-- Theme Custom -->
	<script src="<?php echo WWW; ?>assets/javascripts/theme.custom.js"></script>

	<!-- Theme Initialization Files -->
	<script src="<?php echo WWW; ?>assets/javascripts/theme.init.js"></script>


	<!-- javascript functions -->
	<script src="<?php echo WWW; ?>Functions/onlyNumbers.js"></script>
	<script src="<?php echo WWW; ?>Functions/onlyChars.js"></script>
	<script src="<?php echo WWW; ?>Functions/mascara.js"></script>
	<script src="<?php echo WWW; ?>Functions/memorando/mostra_arquivo.js"></script>

	<!-- jkeditor -->
	<script src="<?php echo WWW; ?>assets/vendor/ckeditor/ckeditor.js"></script>

	<!-- printThis -->
	<script src="<?php echo WWW; ?>assets/vendor/jasonday-printThis-f73ca19/printThis.js"></script>

	<script src="<?php echo WWW; ?>Functions/memorando/verifica.js"></script>

	<!-- jquery functions -->

	<script>
		$(function() {
			var impressao = 0;
			var despacho = <?php echo $_SESSION['despacho'] ?>;

			var despachoAnexo = <?php echo $_SESSION['despachoComAnexo'] ?>;
			var arquivo = <?php echo $_SESSION['arquivos'] ?>;
			<?php
			if (!empty($_SESSION['ultimo_despacho'])) {
				if ($_SESSION['id_status_memorando'][0] != 6 && $_SESSION['ultimo_despacho'][0]['id_destinatarioo'] != $_SESSION['id_pessoa']) {
			?>var arquivar = 1;
			<?php
				} else {
			?>var arquivar = 0;
		<?php
				}
			}
		?>
		$.each(despacho, function(i, item) {
			$("#listaDeDespachos")
				.append($("<table class='table table-bordered table-striped mb-none' id='" + item.id + "'>")
					.append($("<tr>")
						.append($("<th>")
							.text("Remetente"))
						.append($("<td>")
							.text(item.remetente))
						.append($("<th>")
							.text("Destinatario"))
						.append($("<td>")
							.text(item.destinatario)))
					.append($("<tr>")
						.append($("<th colspan=2>")
							.text("Despacho"))
						.append($("<th>")
							.text("Data"))
						.append($("<td>")
							.text(item.data.substr(8, 2) + "/" + item.data.substr(5, 2) + "/" + item.data.substr(0, 4) + " " + item.data.substr(10))))
					.append($("<tr>")
						.append($("<td colspan=4 id=texto" + item.id + ">")
							.html(item.texto))));
		});
		$.each(despachoAnexo, function(i, item) {
			$("#" + item.id_despacho)
				.append($("<tr>")
					.append($("<th colspan=4>")
						.text("Anexos")));
		});
		$.each(arquivo, function(i, item) {
			$("#" + item.id_despacho)
				.append($("<tr id=link>")
					.append($("<td colspan=4>")
						.html("<a href='<?php echo WWW; ?>html/memorando/exibe_anexo.php?id_anexo=" + item.id_anexo + "&extensao=" + item.extensao + "&nome=" + item.nome + "'>" + item.nome + "." + item.extensao + "</a>")));
		});

		$("#header").load("<?php echo WWW; ?>html/header.php");
		$(".menuu").load("<?php echo WWW; ?>html/menu.php");

		var id_memorando = <?php echo $_GET['id_memorando'] ?>;
		$("#id_memorando").val(id_memorando);

		<?php if (!empty($_SESSION['ultimo_despacho'])) { ?>
			if (arquivar == 0) {
				CKEDITOR.replace('despacho');
			}
		<?php } ?>

		});
	</script>
	<script>
		$(function() {
			var funcionario = <?php echo $_SESSION['funcionarios2'] ?>;
			$.each(funcionario, function(i, item) {
				$("#destinatario")
				// .append($("<option id="+item.id_pessoa+" value="+item.id_pessoa+" name="+item.id_pessoa+">"+item.nome+" "+item.sobrenome+"</option>"));
			});
			$("#header").load("<?php echo WWW; ?>html/header.php");
			$(".menuu").load("<?php echo WWW; ?>html/menu.php");

			var id_memorando = <?php echo $_GET['id_memorando'] ?>;
			$("#id_memorando").val(id_memorando);

			CKEDITOR.replace('despacho');
		});
	</script>


	<style type="text/css">
		ul {
			list-style: none;
		}

		.select {
			position: absolute;
			width: 235px;
		}

		.select-table-filter {
			width: 141px;
			float: left;
		}

		#link {
			border-radius: 0px;
			border: none;
			color: #000000 !important;
		}

		#link:hover {
			background-color: #e6e5e5;
		}

		.panel-body {
			margin-bottom: 15px;
		}

		input[type="file"] {
			margin-bottom: 10px;
			margin-top: 15px;
		}

		.col-md-3 {
			width: 10%;
		}

		#despacho {
			height: 500px;
		}

		#div_texto {
			width: 100%;
		}

		#cke_despacho {
			height: 500px;
		}

		.cke_contents {
			height: 500px;
		}

		#cke_1_contents {
			height: 450px !important;
		}

		.table.mb-none {
			margin-bottom: 25px !important;
		}

		.printable {
			display: none;
		}

		/* print styles*/
		@media print {
			.printable {
				display: block;
			}

			.screen {
				display: none;
			}
		}
	</style>

	<script>
		(function($) {
			$.fn.uploader = function(options) {
				var settings = $.extend({
						// MessageAreaText: "No files selected.",
						// MessageAreaTextWithFiles: "File List:",
						// DefaultErrorMessage: "Unable to open this file.",
						// BadTypeErrorMessage: "We cannot accept this file type at this time.",
						acceptedFileTypes: [
							"pdf",
							"php",
							"odt",
							"jpg",
							"gif",
							"jpeg",
							"bmp",
							"tif",
							"tiff",
							"png",
							"xps",
							"doc",
							"docx",
							"fax",
							"wmp",
							"ico",
							"txt",
							"cs",
							"rtf",
							"xls",
							"xlsx"
						]
					},
					options
				);

				var uploadId = 1;
				//update the messaging
				//atualiza a mensagem
				$(".file-uploader__message-area p").text(
					options.MessageAreaText || settings.MessageAreaText
				);

				//create and add the file list and the hidden input list
				// cria e adiciona a lista de arquivos e a lista de entrada oculta
				var fileList = $('<ul class="file-list"></ul>');
				var hiddenInputs = $('<div class="hidden-inputs hidden"></div>');
				$(".file-uploader__message-area").after(fileList);
				$(".file-list").after(hiddenInputs);

				//when choosing a file, add the name to the list and copy the file input into the hidden inputs
				//ao escolher um arquivo, adicione o nome à lista e copie a entrada do arquivo para as entradas ocultas
				$(".file-chooser__input").on("change", function() {
					var files = document.querySelector(".file-chooser__input").files;

					for (var i = 0; i < files.length; i++) {
						console.log(files[i]);

						var file = files[i];
						// console.log(file);
						var fileName = file.name.match(/([^\\\/]+)$/)[0];

						//clear any error condition
						//limpe qualquer condição de erro
						$(".file-chooser").removeClass("error");
						$(".error-message").remove();

						//validate the file
						//valide o arquivo

						var check = checkFile(fileName);
						if (check === "valid") {
							// move the 'real' one to hidden list
							//mova o 'real' para a lista oculta


							$(".hidden-inputs").append($(".file-chooser__input"));

							//importante


							//insert a clone after the hiddens (copy the event handlers too)
							//insira um clone após os hiddens (copie os manipuladores de eventos também)

							$(".file-chooser").append(
								$(".file-chooser__input").clone({
									withDataAndEvents: true
								})
							);

							//add the name and a remove button to the file-list
							//adicione o nome e um botão de remoção à lista de arquivos
							$(".file-list").append(
								'<li style="list-style-type: none;"><span class="file-list__name">' +
								fileName +
								'</span></li>'
							);
							$(".file-list").find("li:last").show(800);

							//removal button handler
							//manipulador de botão de remoção
							// $(".removal-button").on("click", function (e) {
							//     e.preventDefault();

							//     //remove the corresponding hidden input
							//     //remove a entrada oculta correspondente
							//     $(
							//     '.hidden-inputs input[data-uploadid="' +
							//         $(this).data("uploadid") +
							//         '"]'
							//     ).remove();

							//     //remove the name from file-list that corresponds to the button clicked
							//     //remova o nome da lista de arquivos que corresponde ao botão clicado
							//     $(this)
							//     .parent()
							//     .hide("puff")
							//     .delay(10)
							//     .queue(function () {
							//         $(this).remove();
							//     });

							//     //if the list is now empty, change the text back
							//     //se a lista estiver vazia, mude o texto de volta
							//     if ($(".file-list li").length === 0) {
							//     $(".file-uploader__message-area").text(
							//         options.MessageAreaText || settings.MessageAreaText
							//     );
							//     }


							// });

							//so the event handler works on the new "real" one
							//então o manipulador de eventos funciona no novo "real"
							$(".hidden-inputs .file-chooser__input")
								.removeClass("file-chooser__input")
								.attr("data-uploadId", uploadId);


							//update the message area
							//atualize a área de mensagem
							$(".file-uploader__message-area").text(
								options.MessageAreaTextWithFiles ||
								settings.MessageAreaTextWithFiles
							);
							uploadId++;

						} else {
							//indicate that the file is not ok
							//indica que o arquivo não está ok
							$(".file-chooser").addClass("error");
							var errorText =
								options.DefaultErrorMessage || settings.DefaultErrorMessage;

							if (check === "badFileName") {
								errorText =
									options.BadTypeErrorMessage || settings.BadTypeErrorMessage;
							}

							$(".file-chooser__input").after(
								'<p class="error-message">' + errorText + "</p>"
							);
						}
					}

					// $(".file-chooser__input").val("");

				});


				var checkFile = function(fileName) {
					var accepted = "invalid",
						acceptedFileTypes = this.acceptedFileTypes || settings.acceptedFileTypes,
						regex;

					for (var i = 0; i < acceptedFileTypes.length; i++) {
						regex = new RegExp("\\." + acceptedFileTypes[i] + "$", "i");

						if (regex.test(fileName)) {
							accepted = "valid";
							break;
						}
					}

					return accepted;
				};


			};

		})($);

		//init
		$(document).ready(function() {
			console.log("hi");
			$(".fileUploader").uploader({
				MessageAreaText: "No files selected. Please select a file."
			});
		});
	</script>
</head>

<body>
	<section class="body">
		<!-- start: header -->
		<div id="header"></div>
		<!-- end: header -->
		<div class="inner-wrapper">
			<!-- start: sidebar -->
			<aside id="sidebar-left" class="sidebar-left menuu"></aside>
			<!-- end: sidebar -->
			<section role="main" class="content-body">
				<header class="page-header">
					<h2>Despacho</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="<?php echo WWW; ?>html/home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Despacho</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>
				<section class="panel">
					<!-- start: page -->
					<?php
					if (!in_array($id_memorando, $_SESSION['memorandoIdInativo'])) {
					?>
						<script>
							$(".panel").html("<p>Desculpe, você não tem acesso à essa página</p>");
						</script>
					<?php
					} else {
					?>
						<div id="myModal">
							<header>
								<h2 class="panel-title">
									<center>

										<p> <img src="<?php display_campo("Logo", "file"); ?>" height="40" class="print-logo" style="margin-right: 701px;"></p>
										WeGIA
										<p> Web Gerenciador Institucional</p>
									</center>

								</h2>
							</header>


							<div class="panel-body" id="">
								<button style="margin-bottom: 0px !important;" class="not-printable mb-xs mt-xs mr-xs btn btn-default" id="btnPrint">Imprimir</button>

								<br>


								<div class="just-printable">

									<?php

									$pdo = Conexao::connect();
									$memorandosDespachados->listarTodosId($id_memorando);
									$memorando = $_SESSION['memorandoId'][0];
									extract($memorando);
									$statusMemorandoControle = new StatusMemorandoControle();
									$statusMemorando = $statusMemorandoControle->getPorId(intval($id_status_memorando));
									if ($statusMemorando) {
										$status = $statusMemorando->getStatus();
									}

									$despachoControle = new DespachoControle();
									$despacho = $despachoControle->getPorId(intval($id_memorando));
									if ($despacho) {
										$despachoNome = $despacho['texto'];
									} else {
										$despachoNome = 'Nenhum despacho';
									}

									$enderecoInstituicao = $pdo->query("SELECT nome, bairro, estado, cidade FROM endereco_instituicao")->fetch(PDO::FETCH_ASSOC);

									if ($enderecoInstituicao) {
										$endereco = $enderecoInstituicao['nome'];
										$bairro = $enderecoInstituicao['bairro'];
										$estado = $enderecoInstituicao['estado'];
										$cidade = $enderecoInstituicao['cidade'];
									} else {
										$endereco = '';
										$bairro = '';
										$estado = '';
										$cidade = '';
									}

									$pessoa1 = $pdo->query("SELECT id_destinatario FROM despacho WHERE id_remetente=$id_pessoa;")->fetch(PDO::FETCH_ASSOC)["id_destinatario"];


									$pessoa_destino = $pdo->query("SELECT nome, sobrenome FROM pessoa WHERE id_pessoa=$pessoa1;")->fetch(PDO::FETCH_ASSOC);

									$pessoa_destino = $pessoa_destino["nome"] . ($pessoa_destino["sobrenome"] ? (" " . $pessoa_destino["sobrenome"]) : "");

									$pessoa_memorando = $pdo->query("SELECT nome, sobrenome FROM pessoa WHERE id_pessoa=$id_pessoa;")->fetch(PDO::FETCH_ASSOC);

									$pessoa_memorando = $pessoa_memorando["nome"] . ($pessoa_memorando["sobrenome"] ? (" " . $pessoa_memorando["sobrenome"]) : "");

									$stmtArquivo = $pdo->prepare("SELECT nome, extensao FROM anexo WHERE id_despacho=:idMemorando");
									$stmtArquivo->bindValue(':idMemorando', $id_memorando, PDO::PARAM_INT);
									$stmtArquivo->execute();

									$strArquivo = $stmtArquivo->fetchAll(PDO::FETCH_ASSOC);

									$stmtAnexo = $pdo->prepare("SELECT anexo FROM anexo WHERE id_despacho=:idMemorando");
									$stmtAnexo->bindValue(':idMemorando', $id_memorando, PDO::PARAM_INT);
									$stmtAnexo->execute();

									$anexo = $stmtAnexo->fetchAll(PDO::FETCH_ASSOC);

									$stmtDataExpedicao = $pdo->prepare("SELECT `data` FROM memorando WHERE id_memorando=:idMemorando");
									$stmtDataExpedicao->bindValue(':idMemorando', $id_memorando, PDO::PARAM_INT);
									$stmtDataExpedicao->execute();

									$data_expedicao = $stmtDataExpedicao->fetch(PDO::FETCH_ASSOC)["data"];

									echo ("

											<p>MEMORANDO NR: $id_memorando</p>
											<p>Assunto: $titulo</p>
								");
									?>
									<div class="panel-heading"> </div>


									<p align="right">

										<?php

										echo (" $cidade - $estado,  $data_expedicao ");

										?>

									</p>
									<?php
									$despachosArr = json_decode($_SESSION['despacho']);
									$anexos = json_decode($_SESSION['arquivos']);
									$tabela = gerarTabelaMemorando($despachosArr, $anexos);

									echo ($tabela);
									?>
								</div>

							</div>


						</div>
						<header class="panel-heading">
							<h2 class="panel-title">Conteúdo do despacho:</h2>
						</header>
						<div class="panel-body" id="listaDeDespachos"></div>

						<?php
						$id ?>

						<header class="panel-heading">
							<h3 class="panel-title">Encaminhar despacho</h3>
						</header>
						<div class="panel-body">

							<?php
							echo "<form action='" . WWW . "controle/control.php' method='post' enctype='multipart/form-data'>";
							?>
							<div class="form-group">
								<label for=destinatario id=etiqueta_destinatario class='col-md-3 control-label'>Destino </label>
								<div class='col-md-6'>
									<select name="destinatario" id="destinatario" required class='form-control mb-md'></select>
								</div>
							</div>
							<div class="form-group">
								<label for=arquivo id=etiqueta_arquivo class='col-md-3 control-label'>Arquivo </label>
								<div class="file-chooser">
									<input type="file" multiple name='anexo[]' class="file-chooser__input" id='teste'>
								</div><br>
								<div class="file-uploader__message-area">
									<!-- <p>Select a file to upload</p> -->
								</div>
							</div>
							<div class="form-group">
								<label for=texto id=etiqueta_despacho class='col-md-3 control-label'>Despacho </label>
								<div class='col-md-6' id='div_texto' style="height: 501px;">
									<textarea cols='30' rows='5' id='despacho' name='texto' required class='form-control'></textarea>
								</div>
							</div>
							<div class='row'>
								<div class='col-md-9 col-md-offset-8'>
									<input type='hidden' value='DespachoControle' name='nomeClasse' class='mb-xs mt-xs mr-xs btn btn-default'>
								</div>
								<div class='col-md-9 col-md-offset-8'>
									<input type='hidden' value='incluir' name='metodo' class='mb-xs mt-xs mr-xs btn btn-default'>
								</div>
								<div class='col-md-9 col-md-offset-8'>
									<input type='hidden' name='id_memorando' id='id_memorando' class='mb-xs mt-xs mr-xs btn btn-default'>
								</div>
								<div class='col-md-9 col-md-offset-8'>
									<input type='hidden' name='modulo' value="memorando" class='mb-xs mt-xs mr-xs btn btn-default'>
								</div>
								<div class='col-md-9 col-md-offset-8'>
									<input type='submit' value='Enviar' name='enviar' id='enviar' class='mb-xs mt-xs mr-xs btn btn-primary'>
								</div>
							</div>
							</form>
						</div>



						<div class="printable"></div>
						<?php
						if ($_SESSION['id_status_memorando'][0] != 6) {
							if ($_SESSION['ultimo_despacho'][0]['id_destinatarioo'] == $_SESSION['id_pessoa']) {
						?>

		</div>
<?php
							}
						}
?>
</div>
</div>
</div>
<?php }; ?>
	</section>
	</section>
	</div>
	</section>




	<!-- end: page -->
	<!-- Vendor -->
	<script src="<?php echo WWW; ?>assets/vendor/select2/select2.js"></script>
	<script src="<?php echo WWW; ?>assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
	<script src="<?php echo WWW; ?>assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
	<script src="<?php echo WWW; ?>assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>

	<!-- Theme Base, Components and Settings -->
	<script src="<?php echo WWW; ?>assets/javascripts/theme.js"></script>

	<!-- Theme Custom -->
	<script src="<?php echo WWW; ?>assets/javascripts/theme.custom.js"></script>

	<!-- Theme Initialization Files -->
	<script src="<?php echo WWW; ?>assets/javascripts/theme.init.js"></script>
	<!-- Examples -->
	<script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.default.js"></script>
	<script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
	<script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.tabletools.js"></script>
	<?php
	if (isset($_SESSION['arquivos'])) {
		$Anexo = $_SESSION["arquivos"];
	}
	unset($_SESSION["arquivos"]);
	?>
	<script>
		$(function() {
			var funcionario = <?php echo $_SESSION['funcionarios2'] ?>;
			$.each(funcionario, function(i, item) {
				$("#destinatario")
					.append($("<option id=" + item.id_pessoa + " value=" + item.id_pessoa + " name=" + item.id_pessoa + ">" + item.nome + " " + item.sobrenome + "</option>"));
			});
			$("#btnPrint").click(function() {
				$("#myModal a").removeAttr("href");
				//get the modal box content and load it into the printable div
				if ((typeof(impressao) == "undefined") || impressao != 1) {
					$(".printable").html($("#myModal").html());
				}
				$(".printable").printThis();
				var impressao = 1;
				/*if($(".printable").text()==$("#myModal").text())
				{
					window.location.reload();
				}*/
			});
		});
	</script>
	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/memorando.html" width="200" height="60" style="border:none;"></iframe>
	</div>
</body>

</html>