<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
Util::definirFusoHorario();
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE)
	session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: ../../index.php");
	exit();
} else {
	session_regenerate_id();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 9);

// Inclui display de Campos
require_once "../personalizacao_display.php";

// Adiciona o Sistema de Mensagem
require_once "../geral/msg.php";

require_once "../../config.php";

function loadDatabaseBackups(string $backupDir): array
{
	$pattern = rtrim($backupDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.dump.tar.gz';
	$backups = [];

	foreach (glob($pattern) ?: [] as $filePath) {
		if (!preg_match('/^\d{14}\.dump\.tar\.gz$/', basename($filePath))) {
			continue;
		}

		$fileSize = filesize($filePath);
		$fileModifiedAt = filemtime($filePath);
		if ($fileSize === false || $fileModifiedAt === false) {
			continue;
		}

		$size = (float) $fileSize;
		$unit = 'B';

		if ($size >= 1024 * 1024 * 1024) {
			$size /= 1024 * 1024 * 1024;
			$unit = 'GB';
		} elseif ($size >= 1024 * 1024) {
			$size /= 1024 * 1024;
			$unit = 'MB';
		} elseif ($size >= 1024) {
			$size /= 1024;
			$unit = 'KB';
		}

		$backupDateTime = (new DateTimeImmutable())->setTimestamp($fileModifiedAt);

		$backups[] = [
			'nome' => basename($filePath),
			'tamanho' => number_format($size, $unit === 'B' ? 0 : 1, '.', '') . ' ' . $unit,
			'data' => $backupDateTime->format('d/m/Y'),
			'hora' => $backupDateTime->format('H:i:s'),
		];
	}

	usort($backups, static function (array $firstBackup, array $secondBackup): int {
		return strcmp($secondBackup['nome'], $firstBackup['nome']);
	});

	return $backups;
}

$bkpFiles = loadDatabaseBackups(BKP_DIR);

?>
<!doctype html>
<html class="fixed">

<head>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Gerenciar Backups</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Web Fonts  -->
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
	<link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker.css" />
	<link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon">

	<!-- Specific Page Vendor CSS -->
	<link rel="stylesheet" href="../../assets/vendor/select2/select2.css" />
	<link rel="stylesheet" href="../../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

	<!-- Theme CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="../../assets/vendor/modernizr/modernizr.js"></script>

	<!-- Atualizacao CSS -->
	<link rel="stylesheet" href="../../css/atualizacao.css" />

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
	<script src="../../Functions/onlyNumbers.js"></script>
	<script src="../../Functions/onlyChars.js"></script>
	<script src="../../Functions/enviar_dados.js"></script>
	<script src="../../Functions/mascara.js"></script>


	<!-- CSS Estoque -->
	<link rel="stylesheet" href="../estoque/estoque.css">

	<!-- jquery functions -->
	<script>
		document.write('<a href="' + document.referrer + '"></a>');
	</script>

	<script type="text/javascript">
		$(function() {
			$("#header").load("../header.php");
			$(".menuu").load("../menu.php");
		});
		$(function() {
			let estoque = <?= JSON_encode($bkpFiles) ?>;

			$.each(estoque, function(i, item) {
				$("#tabela").append(
					$("<tr>").addClass("item").attr("data-file", item.nome)
					.append($("<td>").addClass("txt-center").text(item.nome))
					.append($("<td>").addClass("txt-center").text(item.tamanho))
					.append($("<td>").addClass("txt-center")
						.text((!isNaN(Number(item.dia)) && !isNaN(Number(item.mes)) && !isNaN(Number(item.ano))) ?
							item.dia + "/" + item.mes + "/" + item.ano :
							"Indefinido"))
					.append($("<td>").addClass("txt-center")
						.text((!isNaN(Number(item.hora)) && !isNaN(Number(item.min)) && !isNaN(Number(item.seg))) ?
							item.hora + ":" + item.min + (item.seg ? ":" + item.seg : "") :
							"N/A"))
					.append($("<td>").addClass("txt-center")
						.append($("<div>").addClass("btn-container")

							.append(
								$("<a>", {
									href: "#"
								}).on("click", function(e) {
									e.preventDefault();
									confirmRestore(item.nome);
								}).append(
									$("<button>").addClass("btn btn-primary")
									.html('<i class="fa fa-refresh"></i>')
								)
							)

							.append(
								$("<a>", {
									href: "#"
								}).on("click", function(e) {
									e.preventDefault();
									confirmDelete(item.nome);
								}).append(
									$("<button>").addClass("btn btn-danger")
									.html('<i class="fa fa-trash-o"></i>')
								)
							)

							.append(
								$("<a>", {
									href: "#"
								}).on("click", function(e) {
									e.preventDefault();
									confirmDownload(item.nome);
								}).append(
									$("<button>").addClass("btn btn-success")
									.html('<i class="fa fa-download"></i>')
								)
							)

						)
					)
				);
			});
		});

		$(function() {
			$('#datatable-default').DataTable({
				"order": [
					[0, "desc"]
				]
			});
		});
	</script>

	<!-- javascript tab management script -->

	<style>
		.txt-center {
			text-align: center;
		}

		.space-between {
			display: flex;
			justify-content: space-between;
		}

		.padding-down {
			padding-bottom: 20px;
		}

		.flex {
			display: flex;
		}

		.btn-container {
			display: flex;
			justify-content: space-evenly;
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
					<h2>Gerenciar Backups</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="../home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Gerenciar Backups</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>
				<!--start: page -->

				<!-- Caso haja uma mensagem do sistema -->
				<?php displayMsg();
				getMsgSession("mensagem", "tipo"); ?>
				<section class="panel">
					<header class="panel-heading">
						<h2 class="panel-title">Backups do Banco de Dados</h2>
					</header>
					<div class="panel-body">
						<div class="space-between padding-down">
							<a href="./configuracao_geral.php" class="btn btn-outline-primary btn-sm"><i class="fa fa-chevron-left" aria-hidden="true"></i> Configurações Gerais</a>
							<div class="flex">
								<a href="./backup.php?action=bd" class="btn btn-primary btn-sm">Gerar Backup <i class="fa fa-floppy-o" aria-hidden="true"></i></a>
							</div>
						</div>
						<table class="table table-bordered table-striped mb-none" id="datatable-default">
							<thead>
								<tr>
									<th class='txt-center' width='30%'>Arquivo</th>
									<th class='txt-center' width='15%'>Tamanho</th>
									<th class='txt-center' width='15%'>Data</th>
									<th class='txt-center' width='15%'>Hora</th>
									<th class='txt-center'>Restaurar | Deletar | Exportar</th>
								</tr>
							</thead>
							<tbody id="tabela">
								<?php foreach ($bkpFiles as $backup) :
									$fileName = htmlspecialchars($backup['nome'], ENT_QUOTES, 'UTF-8');
									$fileNameJson = htmlspecialchars(json_encode($backup['nome'], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
								?>
									<tr class="item">
										<td class="txt-center"><?= $fileName ?></td>
										<td class="txt-center"><?= htmlspecialchars($backup['tamanho'], ENT_QUOTES, 'UTF-8') ?></td>
										<td class="txt-center"><?= htmlspecialchars($backup['data'], ENT_QUOTES, 'UTF-8') ?></td>
										<td class="txt-center"><?= htmlspecialchars($backup['hora'], ENT_QUOTES, 'UTF-8') ?></td>
										<td class="txt-center">
											<div class="btn-container">
												<button type="button" class="btn btn-primary" onclick="confirmRestore(<?= $fileNameJson ?>)">
													<i class="fa fa-refresh" aria-hidden="true"></i>
												</button>
												<button type="button" class="btn btn-danger" onclick="confirmDelete(<?= $fileNameJson ?>)">
													<i class="fa fa-trash-o" aria-hidden="true" style="font-family: FontAwesome;"></i>
												</button>
												<button type="button" class="btn btn-success" onclick="confirmDownload(<?= $fileNameJson ?>)">
													<i class="fa fa-download" aria-hidden="true" style="font-family: FontAwesome;"></i>
												</button>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</section>

				<!-- end: page -->
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

	function confirmDelete(file) {
		if (window.confirm("ATENÇÃO! Você tem certeza que deseja deletar esse arquivo de backup do sistema?")) {
			$('.panel form').remove();

			let form = $("<form>", {
				method: "post",
				action: "./gerenciar_backup.php"
			});

			form.append($("<input>", {
				type: "hidden",
				name: "file",
				value: file
			}));

			form.append($("<input>", {
				type: "hidden",
				name: "action",
				value: "remove"
			}));

			$('.panel').append(form);
			form.submit();
		}
	}

	function confirmRestore(file) {
		if (window.confirm("ATENÇÃO! Você tem certeza que deseja sobrescrever a Base de Dados atual pela selecionada?")) {
			$('.panel form').remove();

			let form = $("<form>", {
				method: "post",
				action: "./gerenciar_backup.php"
			});

			form.append($("<input>", {
				type: "hidden",
				name: "file",
				value: file
			}));

			form.append($("<input>", {
				type: "hidden",
				name: "action",
				value: "restore"
			}));

			$('.panel').append(form);
			form.submit();
		}
	}

	function confirmDownload(file) {
		$('.panel form').remove();

		let form = $("<form>", {
			method: "post",
			action: "./exportar_dump.php"
		});

		form.append($("<input>", {
			type: "hidden",
			name: "file",
			value: file
		}));

		$('.panel').append(form);
		form.submit();
	}
</script>

<!-- Vendor -->
<script src="../../assets/vendor/select2/select2.js"></script>
<script src="../../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
<script src="../../assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
<script src="../../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>

<!-- Theme Base, Components and Settings -->
<script src="../../assets/javascripts/theme.js"></script>

<!-- Theme Custom -->
<script src="../../assets/javascripts/theme.custom.js"></script>

<!-- Theme Initialization Files -->
<script src="../../assets/javascripts/theme.init.js"></script>
<!-- Examples -->
<script src="../../assets/javascripts/tables/examples.datatables.default.js"></script>
<script src="../../assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
<script src="../../assets/javascripts/tables/examples.datatables.tabletools.js"></script>

<!-- Adiciona função de fechar mensagem e tirá-la da url -->
<script src="../geral/msg.js"></script>

</html>
