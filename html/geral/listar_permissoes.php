<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE)
	session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: " . WWW . "index.php");
	exit();
} else {
	session_regenerate_id();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';

$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$acoesResult = mysqli_query($conexao, "SELECT id_acao, descricao FROM acao ORDER BY descricao");
$acoes = [];
while ($acao = $acoesResult->fetch_array(MYSQLI_ASSOC)) {
	$acoes[] = $acao;
}

$cargosResult = mysqli_query($conexao, "SELECT DISTINCT c.id_cargo, c.cargo FROM cargo c JOIN permissao p ON p.id_cargo = c.id_cargo WHERE c.id_cargo <> 2 ORDER BY c.cargo");
$cargos = [];
while ($cargo = $cargosResult->fetch_array(MYSQLI_ASSOC)) {
	$cargos[] = $cargo;
}

$permissoesPorCargo = [];
$permissoesResult = mysqli_query($conexao, "SELECT p.id_cargo, p.id_recurso, p.id_acao, r.descricao AS recurso FROM permissao p JOIN recurso r ON p.id_recurso = r.id_recurso ORDER BY p.id_cargo, r.descricao");
while ($permissaoCargo = $permissoesResult->fetch_array(MYSQLI_ASSOC)) {
	$idCargo = (int)$permissaoCargo['id_cargo'];
	if (!isset($permissoesPorCargo[$idCargo])) {
		$permissoesPorCargo[$idCargo] = [];
	}
	$permissoesPorCargo[$idCargo][] = $permissaoCargo;
}

permissao($_SESSION['id_pessoa'], 91, 1);

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";
?>
<!doctype html>
<html class="fixed" lang="pt-br">

<head>
	<!-- Basic -->
	<meta charset="UTF-8">
	<title>Listar permissões</title>
	<meta name="keywords" content="HTML5 Admin Template" />
	<meta name="description" content="Porto Admin - Responsive HTML5 Template">
	<meta name="author" content="okler.net">
	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
	<!-- Vendor CSS -->
	<link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
	<link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

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
	<script src="<?php echo WWW; ?>Functions/onlyNumbers.js"></script>
	<script src="<?php echo WWW; ?>Functions/onlyChars.js"></script>
	<script src="<?php echo WWW; ?>Functions/mascara.js"></script>

	<script type="text/javascript">
		$(function() {
			$("#header").load("<?php echo WWW; ?>html/header.php");
			$(".menuu").load("<?php echo WWW; ?>html/menu.php");
		});
	</script>

</head>

<body>
	<section class="body">
		<!-- start: header -->
		<header id="header">
		</header>
		<!-- end: header -->

		<div class="inner-wrapper">
			<!-- start: sidebar -->
			<aside id="sidebar-left" class="sidebar-left menuu">
			</aside>

			<section role="main" class="content-body">
				<header class="page-header">
					<h2>Listar permissões de cargos </h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="../home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Listar permissões</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->
				<div class="row">
					<section class="panel">
						<header class="panel-heading">
							<div class="panel-actions">
								<a href="#" class="fa fa-caret-down"></a>
							</div>
							<h2 class="panel-title">Permissões</h2>
						</header>
						<div class="panel-body">
							<?php
							if (isset($_GET['msg_c'])) {
								$msg = trim(filter_input(INPUT_GET, 'msg_c', FILTER_SANITIZE_SPECIAL_CHARS));
								echo ('<div class="alert alert-success" role="alert">
										' . htmlspecialchars($msg) . '
									  </div>');
							} else if (isset($_GET['msg_e'])) {
								$msg = trim(filter_input(INPUT_GET, 'msg_e', FILTER_SANITIZE_SPECIAL_CHARS));
								echo ('<div class="alert alert-danger" role="alert">
										' . htmlspecialchars($msg) . '
									  </div>');
							}
							?>
							<table class="table table-bordered table-striped mb-none" id="datatable-default">
								<thead>
									<tr>
										<th>Cargo</th>
										<th>Recurso disponível</th>
										<th>Tipo permissão</th>
										<th>Ações</th>
									</tr>
								</thead>
								<tbody id="tabela">
									<?php
									foreach ($cargos as $cargo) {
										$cargoId = (int)$cargo['id_cargo'];
										$rowId = 'cargo_' . $cargoId;
										$permissoesCargo = $permissoesPorCargo[$cargoId] ?? [];
										$temPermissao = !empty($permissoesCargo);

										echo "<tr>";
										echo "<td>" . htmlspecialchars($cargo['cargo']) . "</td>";

										echo "<td>";
										echo "<select id='recurso_" . $rowId . "' class='form-control recurso-select' style='min-width:220px;' " . ($temPermissao ? '' : 'disabled') . ">";

										if (!$temPermissao) {
											echo "<option value=''>Sem recurso para este cargo</option>";
										} else {
											foreach ($permissoesCargo as $index => $permissaoCargo) {
												$recursoId = (int)$permissaoCargo['id_recurso'];
												$acaoId = (int)$permissaoCargo['id_acao'];
												$selected = $index === 0 ? ' selected' : '';
												echo "<option value='" . $recursoId . "' data-acao='" . $acaoId . "'" . $selected . ">" . htmlspecialchars($permissaoCargo['recurso']) . "</option>";
											}
										}

										echo "</select>";
										echo "</td>";

										echo "<td>";
										echo "<select id='acao_" . $rowId . "' class='form-control acao-select' style='min-width:180px;' " . ($temPermissao ? '' : 'disabled') . ">";
										foreach ($acoes as $acao) {
											$acaoId = (int)$acao['id_acao'];
											$selectedAcao = ($temPermissao && isset($permissoesCargo[0]) && (int)$permissoesCargo[0]['id_acao'] === $acaoId) ? ' selected' : '';
											echo "<option value='" . $acaoId . "'" . $selectedAcao . ">" . htmlspecialchars($acao['descricao']) . "</option>";
										}
										echo "</select>";
										echo "<div id='atual_" . $rowId . "' style='color:#d2322d; font-size:11px; margin-top:4px;'></div>";
										echo "</td>";

										echo "<td>";
										echo "<div style='display:inline-flex; align-items:center; gap:6px; white-space:nowrap;'>";
										echo "<form method='post' action='" . WWW . "controle/control.php' class='form-acao' style='display:inline-block; margin:0;'>";
										echo Csrf::inputField();
										echo "<input type='hidden' name='nomeClasse' value='FuncionarioControle'>";
										echo "<input type='hidden' name='metodo' value='alterarPermissao'>";
										echo "<input type='hidden' name='cargo' value='" . $cargoId . "'>";
										echo "<input type='hidden' name='recurso' class='recurso-hidden'>";
										echo "<input type='hidden' name='acao' class='acao-hidden'>";
										echo "<button type='submit' class='btn btn-primary btn-alterar' data-row='" . $rowId . "' " . ($temPermissao ? '' : 'disabled') . ">Alterar</button>";
										echo "</form> ";

										echo "<form method='post' action='" . WWW . "controle/control.php' class='form-acao' style='display:inline-block; margin:0;'>";
										echo Csrf::inputField();
										echo "<input type='hidden' name='nomeClasse' value='FuncionarioControle'>";
										echo "<input type='hidden' name='metodo' value='excluirPermissao'>";
										echo "<input type='hidden' name='cargo' value='" . $cargoId . "'>";
										echo "<input type='hidden' name='recurso' class='recurso-hidden'>";
										echo "<input type='hidden' name='acao' class='acao-hidden'>";
										echo "<button type='submit' class='btn btn-danger btn-excluir' data-row='" . $rowId . "' " . ($temPermissao ? '' : 'disabled') . ">Excluir</button>";
										echo "</form>";
										echo "</div>";
										echo "</td>";
										echo "</tr>";
									}
									?>
								</tbody>
							</table>
						</div><br>
						<a href="cadastrar_permissoes.php" class="btn btn-danger">Voltar</a>
					</section>
			</section>
		</div>
	</section>
	</div>
	<!-- end: page -->
	</section>
	</div>

	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/conf.html" width="200" height="60" style="border:none;"></iframe>
	</div>

	</section>

	<!-- Vendor -->
	<script>
		$(document).ready(function() {
			function atualizarIndicadorAtual(rowId) {
				const recursoSelect = $('#recurso_' + rowId);
				const acaoSelect = $('#acao_' + rowId);
				const acaoAtualId = String(recursoSelect.find('option:selected').data('acao') || '');
				const acaoSelecionada = String(acaoSelect.val() || '');

				if (acaoAtualId && acaoSelecionada === acaoAtualId) {
					$('#atual_' + rowId).text('*Atual');
				} else {
					$('#atual_' + rowId).text('');
				}
			}

			$('.recurso-select').each(function() {
				const recursoSelect = $(this);
				const rowId = recursoSelect.attr('id').replace('recurso_', '');
				const acaoAtualId = recursoSelect.find('option:selected').data('acao');
				if (acaoAtualId) {
					$('#acao_' + rowId).val(String(acaoAtualId));
				}
				atualizarIndicadorAtual(rowId);
			});

			$('.recurso-select').on('change', function() {
				const recursoSelect = $(this);
				const rowId = recursoSelect.attr('id').replace('recurso_', '');
				const acaoAtualId = recursoSelect.find('option:selected').data('acao');
				if (acaoAtualId) {
					$('#acao_' + rowId).val(String(acaoAtualId));
				}
				atualizarIndicadorAtual(rowId);
			});

			$('.acao-select').on('change', function() {
				const acaoSelect = $(this);
				const rowId = acaoSelect.attr('id').replace('acao_', '');
				atualizarIndicadorAtual(rowId);
			});

			$('.btn-alterar, .btn-excluir').on('click', function() {
				const button = $(this);
				const rowId = button.data('row');
				const recurso = $('#recurso_' + rowId).val();
				const acao = $('#acao_' + rowId).val();
				const form = button.closest('form');

				form.find('.recurso-hidden').val(recurso);
				form.find('.acao-hidden').val(acao);
			});

			setTimeout(function() {
				$(".alert").fadeOut();
				window.history.replaceState({}, document.title, window.location.pathname);
			}, 3000);
		});
	</script>
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

</body>

</html>