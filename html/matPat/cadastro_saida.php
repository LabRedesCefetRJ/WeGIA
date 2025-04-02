<?php
session_start();
if (!isset($_SESSION['usuario'])) {
	header("Location: ../index.php");
	exit();
}

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

$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$id_pessoa = $_SESSION['id_pessoa'];

// Proteção contra injeção de SQL utilizando prepared statements
$stmt = $conexao->prepare("SELECT id_cargo FROM funcionario WHERE id_pessoa = ?");
$stmt->bind_param("i", $id_pessoa);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado && $resultado->num_rows > 0) {
	$row = $resultado->fetch_assoc();
	$id_cargo = $row['id_cargo'];

	// Segunda consulta usando prepared statements
	$stmt = $conexao->prepare("SELECT id_acao FROM permissao WHERE id_cargo = ? AND id_recurso = 24");
	$stmt->bind_param("i", $id_cargo);
	$stmt->execute();
	$resultado = $stmt->get_result();

	if ($resultado && $resultado->num_rows > 0) {
		$permissao = $resultado->fetch_assoc();
		if ($permissao['id_acao'] < 3) {
			$msg = urlencode("Você não tem as permissões necessárias para essa página.");
			header("Location: ./home.php?msg_c=$msg");
			exit();
		}
		$permissao = $permissao['id_acao'];
	} else {
		$permissao = 1;
		$msg = urlencode("Você não tem as permissões necessárias para essa página.");
		header("Location: ./home.php?msg_c=$msg");
		exit();
	}
} else {
	$permissao = 1;
	$msg = urlencode("Você não tem as permissões necessárias para essa página.");
	header("Location: ./home.php?msg_c=$msg");
	exit();
}

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once "personalizacao_display.php";
require_once "../Functions/permissao/permissao.php";
?>

<!doctype html>
<html class="fixed">

<head>
	<?php
	include_once '../dao/Conexao.php';
	include_once '../dao/AlmoxarifadoDAO.php';
	include_once '../dao/TipoEntradaDAO.php';
	include_once '../dao/ProdutoDAO.php';

	if (!isset($_SESSION['almoxarifado'])) {
		header('Location: ../controle/control.php?metodo=listarTodos&nomeClasse=AlmoxarifadoControle&nextPage=' . WWW . '/html/cadastro_saida.php');
	}
	if (!isset($_SESSION['tipo_saida'])) {
		header('Location: ../controle/control.php?metodo=listarTodos&nomeClasse=TipoSaidaControle&nextPage=../html/cadastro_saida.php');
	}
	if (!isset($_SESSION['autocomplete'])) {
		header('Location: ../controle/control.php?metodo=listarDescricao&nomeClasse=ProdutoControle&nextPage=../html/cadastro_saida.php');
	}
	if (!isset($_SESSION['destino'])) {
		header('Location: ../controle/control.php?metodo=listarTodos&nomeClasse=DestinoControle&nextPage=../html/cadastro_saida.php');
	}
	if (isset($_SESSION['almoxarifado']) && isset($_SESSION['tipo_saida']) &&  isset($_SESSION['autocomplete']) && isset($_SESSION['destino'])) {

		$almoxarifado = $_SESSION['almoxarifado'];
		$tipo_saida = $_SESSION['tipo_saida'];
		$autocomplete = $_SESSION['autocomplete'];
		$destino = $_SESSION['destino'];

		unset($_SESSION['almoxarifado']);
		unset($_SESSION['tipo_saida']);
		unset($_SESSION['autocomplete']);
		unset($_SESSION['destino']);
	}
	?>

	<!-- Basic -->
	<meta charset="UTF-8">
	<title>Cadastro saída</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="../assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">

	<!-- Theme CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="../assets/vendor/modernizr/modernizr.js"></script>

	<!-- Javascript functions -->
	<script src="../assets/vendor/jquery/jquery.min.js"></script>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<link type="text/css" rel="stylesheet" charset="UTF-8" href="https://translate.googleapis.com/translate_static/css/translateelement.css">

	<script type="text/javascript">
		$(function() {
			var prods = [];
			var almoxarifado = <?= filtrarAlmoxarifado($_SESSION['id_pessoa'], $almoxarifado) ?>;

			var tipo_saida = <?php
								echo $tipo_saida;
								?>;

			var produtos_autocomplete = <?php
										echo $autocomplete;
										?>;

			var destino = <?php
							echo $destino;
							?>;

			$.each(almoxarifado, function(i, item) {
				$('#almoxarifado').append('<option value="' + item.id_almoxarifado + '">' + item.descricao_almoxarifado + '</option>');
			})

			$.each(tipo_saida, function(i, item) {
				$('#tipo_entrada').append('<option value="' + item.id_tipo + '">' + item.descricao + '</option>');
			})

			$.each(produtos_autocomplete, function(i, item) {
				//$('#produtos_autocomplete').append('<option value="' + item.id_produto + '|' + item.descricao + '">');
				prods[i] = item.id_produto + '|' + item.descricao + '|' + item.codigo; //alterar aqui
			})

			$("#input_produtos").autocomplete({
				source: prods,
				response: function(event, ui) {
					if (ui.content.length == 1) {
						ui.item = ui.content[0];
						$(this).val(ui.item.value)
						$(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
					}
				}
			});

			$.each(destino, function(i, item) {
				$('#origens').append('<option value="' + item.id_destino + '-' + item.nome_destino + '">');
			})

			$('#input_produtos').on('change', function() {
				var teste = this.value.split('|');
				$.each(produtos_autocomplete, function(i, item) {
					if (teste[0] == item.id_produto && teste[1] == item.descricao) {
						$("#valor_unitario").val(item.preco);
						$("#quantidade").focus();
					}
				})

			});

			//adicionar tabela
			var conta = 0;
			var verificar = 0;
			$(".add-row").click(function() {
				var produto = $("#input_produtos").val();
				var val = $("#input_produtos").val();

				//As próximas 3 linhas de código são responsáveis por deixar a formatação compatível para a verificação na linha de código seguinte, uma vez que os dados vem de tabelas diferentes e a tabela de produtos não possuí o campo quantidade
				let parts = val.split('|');
				parts.splice(2, 1);
				val = parts.join('|')

				var obj = prods.find;
				(prod => prod === val);

				produto = produto.split("|");
				if (obj != null && obj.length > 0) {
					if (Number(produto[2]) >= Number($("#quantidade").val())) {
						$.each(produtos_autocomplete, function(i, item) {
							if (produto[0] == item.id_produto && produto[1] == item.descricao) {
								var quantidade = $("#quantidade").val();
								var preco = parseFloat($("#valor_unitario").val());

								conta = conta + 1;

								$("#conta").val(conta);

								var markup = "<tr class='produtoRow'><td class='prod' style='width: 160px;'><input type='text' value='" + val + "' name='id" + conta + "' readonly='readonly'></td><td class='quant'><input type='text' class='number'  id='qtd' maxlength='2' size='2' class='form-control' min='1' value='" + quantidade + "' name='qtd" + conta + "' readonly='readonly'></td><td><input type='text' class='preco' value='" + preco + "' name='valor_unitario" + conta + "'  size='2' readonly='readonly'></td><th><input type='text' size='3' id='total' class='total' value='" + quantidade * preco + "' readonly='readonly'></th><td><button type='button' class='delete-row'>remover</button></td></tr>";
								$("table tbody ").append(markup);
								$("#valor_unitario").empty();
								$("#input_produtos").val("");
								var x = $("#total_total").val();
								x = Number(x);
								x += (quantidade * preco);

								$("#total_total").val(x);
								verificar++;
								$("#verifica").val(verificar);
							}
						})
					} else {
						alert("Não há estoque suficiente de " + produto[1] + " para saída. Tente uma quantidade menor." + produto[2] + " < " + $("#quantidade").val());
					}
				} else {
					alert("Produto inválido!");
					$("#input_produtos").val("");
					$("#input_produtos").focus();
					$("#valor_unitario").empty();
					verificar--;
					$("#verifica").val(verificar);
				}
			});

			//remover tabela
			$("table tbody").on('click', '.delete-row', function() {
				var valor_menos = $(this).closest('tr').find('th').find('input').val();
				var xx = $("#total_total").val();
				xx = xx - valor_menos;
				$("#total_total").val(xx);
				$(this).closest('tr').remove();
				verificar = verificar - 1;
			});

			// validar origem
			$("#origem").blur(function() {
				var val = $("#origem").val();
				var obj = $("#origens").find("option[value='" + val + "']");
				if (obj != null && obj.length > 0) {
					return true;
				} else {
					alert("Destino inválido, por favor insira um destino válido");
					$("#origem").val("");
				}
			});
		});
	</script>

	<!-- Script para validar formulário -->
	<script>
		function validar() {
			var almox = document.getElementById("almoxarifado");
			var tipo = document.getElementById("tipo_entrada");
			var verificar = document.getElementById("verifica");
			if (almox.value == "blank") {
				alert("Selecione um almoxarifado");
				almox.focus();
				return false;
			} else if (tipo.value == "blank") {
				alert("Selecione o tipo da saida")
				tipo.focus();
				return false;
			} else if (verificar.value == 0) {
				alert("Nenhum produto inserido");
				document.getElementById("input_produtos").focus();
				return false;
			}
		}

		$(function() {
			$("#header").load("header.php");
			$(".menuu").load("menu.php");
		});
	</script>



	<!--CSS-->
	<style type="text/css">
		.body {
			position: relative;
		}

		.box {
			padding-right: 34px;
			border-right-width: 23px;
			right: 50px;
			width: 796px;
		}
	</style>
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
					<h2>Cadastro</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Registro</span></li>
							<li><span>Saída</span></li>
						</ol>
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->
				<div class="row">
					<div class="col-md-8 col-lg-8">
						<div class="tabs">
							<ul class="nav nav-tabs tabs-primary">
								<li class="active">
									<a href="#overview" data-toggle="tab">Registrar Saída</a>
								</li>
							</ul>
							<div class="tab-content">
								<div id="overview" class="tab-pane active">
									<form class="form-horizontal" method="post" id="formulario" onsubmit="return validar()" action="../controle/control.php" autocomplete="off">
										<fieldset>
											<div class="info-entrada">
												<p>Atenção: Almoxarifados só serão exibidos como opção caso o usuário esteja cadastrado como almoxarife.</p>
												<div class="form-group">
													<label class="col-md-3 control-label" for="origem">Destino</label>
													<a href="cadastro_destino.php"><i class="fas fa-plus w3-xlarge"></i></a>
													<div class="col-md-8">
														<input type="search" list="origens" id="origem" name="destino" class="form-control" autocomplete="off" required>
														<datalist id="origens">
														</datalist>
													</div>
												</div>

												<div class="form-group">
													<label class="col-md-3 control-label" for="almoxarifado">Almoxarifado</label>
													<a href="adicionar_almoxarifado.php"><i class="fas fa-plus w3-xlarge"></i></a>
													<div class="col-md-6">
														<select class="form-control " name="almoxarifado" id="almoxarifado">
															<option selected disabled value="blank">Selecionar</option>
														</select>
													</div>
												</div>

												<div class="form-group">
													<label class="col-md-3 control-label" for="tipo_entrada">Tipo</label>
													<a href="adicionar_tipoSaida.php"><i class="fas fa-plus w3-xlarge"></i></a>
													<div class="col-md-6">
														<select class="form-control " name="tipo_saida" id="tipo_entrada">
															<option selected disabled value="blank">Selecionar</option>
														</select>
													</div>
												</div>
											</div>

											<div class="panel-body">
												<div class="table-responsive">
													<table class="table table-bordered mb-none">
														<thead>
															<tr style="width: 768px;">
																<th>Produto
																	<a href="cadastro_produto.php" class="fas fa-plus w3-xlarge" style="float:right;" id="produto" class="produto">
																	</a>
																</th>
																<th>Quantidade</th>
																<th>Valor unitário</th>
																<th>Incluir</th>
															</tr>
															<tr>
																<td>
																	<input type="text" id="input_produtos" name="produtos_autocomplete" autocomplete="on" size="20" class="form-control">
																	<!-- <datalist id="produtos_autocomplete">
															</datalist> -->
																</td>
																<td><input type="number" name="quantidade" style="width: 74px;" value="1" min="1" id="quantidade"></td>
																<td><input id="valor_unitario" type="number" name="quantidade" style="width: 74px;" step="any" value="0" min="0"></td>
																<td>
																	<button id="incluir" type="button" class="add-row">Adicionar produtos</button>
																</td>
															</tr>
														</thead>
													</table><br>
												</div>

												<div class="table-responsive">
													<table class="table table-bordered mb-none table">
														<thead>
															<tr>

																<th style="width: 160px;">Produto
																<th style="width: 85px;">Quantidade</th>
																<th>Preço</th>
																<th>Total</th>
																<th>Ação</th>
															</tr>
														</thead>
														<tbody>
														</tbody>
														<tfoot>
															<tr>
																<td>Valor total:</td>
																<td id="valor-total">
																	<input type="number" id="total_total" name="total_total" readonly="readonly">
																	<input type="hidden" id="conta" name="conta" readonly="readonly">
																	<input type="hidden" id="verifica" disabled>
																</td>

															</tr>
														</tfoot>
													</table>
												</div>
											</div>
											<!--<button id="array">Pegar valores da tabela</button>
										<div id="resultado"></div>-->

										</fieldset><br>
										<div class="row">
											<div class="col-md-9 col-md-offset-3">
												<input type="hidden" name="nomeClasse" value="SaidaControle">
												<input type="hidden" name="metodo" value="incluir">
												<input type="submit" class="btn btn-primary">
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
		<!-- end: page -->
	</section>


	<!-- Vendor -->
	<script src="../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="../assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="../assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="../assets/vendor/magnific-popup/magnific-popup.js"></script>
	<script src="../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

	<!-- Specific Page Vendor -->
	<script src="../assets/vendor/jquery-autosize/jquery.autosize.js"></script>

	<!-- Theme Base, Components and Settings -->
	<script src="../assets/javascripts/theme.js"></script>

	<!-- Theme Custom -->
	<script src="../assets/javascripts/theme.custom.js"></script>

	<!-- Theme Initialization Files -->
	<script src="../assets/javascripts/theme.init.js"></script>

	<script>
		$(function() {
			$('form').submit(function(event) {
				return checkFocus(event);
			});
		});

		function checkFocus(event) {
			if ($('#input_produtos').is(':focus')) {
				event.preventDefault();
				return false;
			}
			return true;
		}
	</script>

	<script type="text/javascript">
		$(document).ready(function() {
			$("#almoxarifado").change(function() {
				var almox = $(this).val();
				$.ajax({
					async: false,
					url: "../controle/getProdutosPorAlmox.php",
					data: {
						"almox": almox
					},
					type: "GET",
					success: function(respostaProds) {
						var produtos = JSON.parse(respostaProds);
						prods = [];
						// console.log(produtos);
						$("#produtos_autocomplete").children().remove();
						for (let [i, produto] of produtos.entries()) {
							// $("#produtos_autocomplete").append(
							// 	$("<option/>").val(produto.id_produto + '-' + produto.descricao+ '-' + produto.qtd+ '-' + produto.codigo).attr("qtd", produto.qtd)
							// );
							console.log(i, produto);
							prods[i] = produto.id_produto + '|' + produto.descricao + '|' + produto.qtd + '|' + produto.codigo;
						}
						$("#input_produtos").autocomplete({
							source: prods,
							response: function(event, ui) {
								if (ui.content.length == 1) {
									ui.item = ui.content[0];
									console.log(ui.item);
									$(this).val(ui.item.value)
									$(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
								}
							}
						});
					},
					error: function(e) {
						alert(e);
					}
				});
			});
		});
	</script>
	<script src="../assets/script/logistica.js"></script>
	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/pet.html" width="200" height="60" style="border:none;"></iframe>
	</div>
</body>

</html>