<?php
	session_start();
	if(!isset($_SESSION['usuario'])){
		header ("Location: ../index.php");
		exit();
	}

	require_once './permissao/permissao.php';
	permissao($_SESSION['id_pessoa'], 22, 7);

	$config_path = "config.php";
	if(file_exists($config_path)){
		require_once($config_path);
	}else{
		while(true){
			$config_path = "../" . $config_path;
			if(file_exists($config_path)) break;
		}
		require_once($config_path);
	}	
	if (!isset($_GET['id_produto'])){
        header("Location: listar_produto.php");
    }
	// Adiciona a Função display_campo($nome_campo, $tipo_campo)
	require_once "personalizacao_display.php";

	include_once "./geral/msg.php";
?>
<!doctype html>
<html class="fixed">
<head>
<?php 
  include_once '../dao/Conexao.php';
  include_once '../dao/ProdutoDAO.php';

  if (!isset($_GET['id_produto'])){
	  header("Location: ./listar_produto.php");
  }
  if(!isset($_SESSION['produtos'])){
    header('Location: ../controle/control.php?metodo=listarTodos&nomeClasse=ProdutoControle&nextPage=../html/remover_produto.php?id_produto='.$_GET['id_produto']);
  }
  if(!isset($_SESSION['tipo_saida'])){
    header('Location: ../controle/control.php?metodo=listarTodos&nomeClasse=TipoSaidaControle&nextPage=../html/remover_produto.php?id_produto='.$_GET['id_produto']);
  }
  if(!isset($_SESSION['destino'])){
    header('Location: ../controle/control.php?metodo=listarTodos&nomeClasse=DestinoControle&nextPage=../html/remover_produto.php?id_produto='.$_GET['id_produto']);
  }
  if(!isset($_SESSION['almoxarifado'])){
    header('Location: ../controle/control.php?metodo=listarTodos&nomeClasse=AlmoxarifadoControle&nextPage='.WWW.'/html/remover_produto.php?id_produto='.$_GET['id_produto']);
  }
  extract($_SESSION);
?>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Remover Produto</title>
		
	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />

	<!-- Specific Page Vendor CSS -->
	<link rel="stylesheet" href="../assets/vendor/select2/select2.css" />
	<link rel="stylesheet" href="../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
	<link rel="icon" href="<?php display_campo("Logo",'file');?>" type="image/x-icon" id="logo-icon">

	<!-- Theme CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
    <link rel="stylesheet" href="../assets/stylesheets/theme-custom.css">
    
	<!-- Atualizacao CSS -->
	<link rel="stylesheet" href="../css/atualizacao.css" />

	<!-- Head Libs -->
	<script src="../assets/vendor/modernizr/modernizr.js"></script>
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
		
	<!-- Vendor -->
	<script src="../assets/vendor/jquery/jquery.min.js"></script>
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

	<!-- javascript functions -->
	<script src="../Functions/onlyNumbers.js"></script>
	<script src="../Functions/onlyChars.js"></script>
	<script src="../Functions/enviar_dados.js"></script>
	<script src="../Functions/mascara.js"></script>
		
	<!-- jquery functions -->
	<script>
        const produtos = <?= $produtos ?>;
        const destino = <?= $destino ?>;
        const almoxarifado = <?= $almoxarifado ?>;
        const tipo_saida = <?= $tipo_saida ?>;


  		$(function(){
			fillOptions()
		})
        <?php
            $pdo = Conexao::connect();
            $idProduto = $_GET['id_produto'];

			$sql1 = "SELECT p.id_produto, p.preco, p.descricao,p.codigo, p.id_categoria_produto, c.descricao_categoria, p.id_unidade, u.descricao_unidade 
            FROM produto p 
            INNER JOIN categoria_produto c ON p.id_categoria_produto = c.id_categoria_produto 
            INNER JOIN unidade u ON p.id_unidade = u.id_unidade 
            WHERE p.id_produto =:idProduto";

            $stmt1 = $pdo->prepare($sql1);
			$stmt1->bindParam(':idProduto', $idProduto);
			$stmt1->execute();

			$item = $stmt1->fetch(PDO::FETCH_ASSOC);

			$sql2 = "SELECT qtd FROM estoque WHERE id_produto=:idProduto";

			$stmt2 = $pdo->prepare($sql2);
			$stmt2->bindParam(':idProduto', $idProduto);
			$stmt2->execute();

			$item['qtd'] = ($stmt2->fetch(PDO::FETCH_ASSOC))['qtd'];
        ?>
        const itemEstoque = <?= JSON_encode($item) ?>;
        var descOf = {};

        $.each(produtos, function(i, item){
            descOf[item.id_produto] = item.descricao;
        });

        var acao = {
            valor: 'none',
            substituicao(){
				$('#replace').empty()
                $('#replace')
                .append($('<hr />'))
                .append($("<p />")
                    .text("ATENÇÃO: Nesta opção, os itens em estoque serão atribuidos a outro produto.")
                    .addClass("text-danger")
                )
                .append($('<br />'))
                .append($('<h4 />')
                    .text("Produto que receberá os itens:")
                )
                .append($('<input list="list" type="text" id="produto" nome="id_produto_novo" required />')
                    .addClass("form-control ui-autocomplete-input")
                )
                .append($('<datalist />')
                    .attr('id', 'list')
                );

                $.each(produtos, function(i, item){
                    if (item.id_produto != <?= $_GET['id_produto']?>){
                        $('#list').append($('<option />')
                            .val(item.id_produto)
                            .text(item.id_produto+" | "+item.descricao+" | "+item.codigo)
                        )
                    }
                })
            },
            saida(){
                $('#replace').empty();
				$('#replace')
                	.append($('<hr />'))
					.append($('<p/>').text("Há itens relacionados ao produto em estoque, deseja registrar saída deles?"))
					.append('Tipo de saída: ')
                	.append($('<br />'))
					.append($('<select />')
						.attr({name: 'tipo_saida', id: 'saida', required: true})
					)
                	.append($('<br />'))
                	.append($('<br />'))
					.append('Destino: ')
                	.append($('<br />'))
					.append($('<select />')
						.attr({name: 'destino', id: 'destino', required: true})
					)
                	.append($('<br />'))
                	.append($('<br />'))
					.append('Almoxarifado: ')
                	.append($('<br />'))
					.append($('<select />')
						.attr({name: 'almoxarifado', id: 'almox', required: true})
					);
				fillOptions();
            },
			none(){
                $('#replace').empty();
			}
        }

        function selecao(valor){
            acao.valor = valor
            acao[valor]();
        }

        function cancelar(){
            window.location.replace('../html/listar_produto.php');
        }
		
		function submitForm(){
			if (acao.valor == 'saida'){
				if ($('#saida').val() && $('#destino').val() && $('#almox').val()){
					if (window.confirm("Tem certeza que deseja registrar a saída dos itens sem excluir o produto?")){
						$('#form').append($('<input name="id_produto" readonly hidden />').val(itemEstoque.id_produto)).append($('<input name="total_total" readonly hidden />').val(itemEstoque.qtd));
						$('#form').submit();
					}
				}else{
					window.alert("Preencha todos os campos antes de prosseguir.")
				}
			}else if (acao.valor == 'none'){
				if (window.confirm("Tem certeza que deseja registrar a saída dos itens sem excluir o produto?")){
					$('#form').append($('<input name="id_produto" readonly hidden />').val(itemEstoque.id_produto));
					$('#form').submit();
				}
			}
		}

		function fillOptions(){
			fill('#saida', tipo_saida, 'id_tipo');
			fill('#destino', destino, 'id_destino', 'nome_destino');
			fill('#almox', almoxarifado, 'id_almoxarifado', 'descricao_almoxarifado');
		}

		function fill(tagId, itemList, nomeId, nomeDesc = 'descricao'){
			$(tagId).append(
				$('<option />')
					.text('Selecionar')
					.val('nenhum')
					.attr({selected: true, disabled: true})
			)
			$.each(itemList, function (i, item){
				$(tagId).append(
					$('<option />')
						.text(item[nomeDesc])
						.val(item[nomeId])
				)
			})
		}
		$(function(){
			if (itemEstoque.qtd){
				selecao('saida');
			}else{
				selecao('none');
			}
		})
	</script>
	<script>
        $(function () {
            $("#header").load("header.php");
            $(".menuu").load("menu.php");
	    });
	</script>

</head>
<body>
	<div id="header"></div>
    <!-- end: header -->
    <div class="inner-wrapper">
      	<!-- start: sidebar -->
      	<aside id="sidebar-left" class="sidebar-left menuu"></aside>
		
		<!-- end: sidebar -->
		<section role="main" class="content-body">
			<header class="page-header">
				<h2>Remover Produto</h2>
			
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li>
							<a href="home.php">
								<i class="fa fa-home"></i>
							</a>
						</li>
						<li><span>Remover Produto</span></li>
					</ol>
			
					<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>

			<!-- start: page -->
		
			<section class="panel">
				<?php getMsg();?>
				<header class="panel-heading">
					<div class="panel-actions">
						<a href="#" class="fa fa-caret-down"></a>
					</div>
			
					<h2 class="panel-title">Remover Produto</h2>
				</header>
				<div class="panel-body">
					<div>
                        <p class="text-justify">Este produto possui dependências no banco de dados e não pode ser excluído completamente, pretende ocultar o produto e seus registros?</p>
                        <div class="panel-body" style="display: flex;">
                                <ul class="nav nav-children" id="info" style="padding-right: 20px;">
                                    <li>Nome: </li>
                                    <li>Categoria: </li>
                                    <li>Unidade: </li>
                                    <li>Codigo: </li>
                                    <li>Valor: </li>
                                    <li>Quantidade: </li>
                                </ul>
                                <ul class="nav nav-children" id="info">
                                    <?php
                                        echo("<li id='nome'>".$item['descricao']."</li>
                                        <li id='Categoria'>".$item['descricao_categoria']."</li>
                                        <li id='Unidade'>".$item['descricao_unidade']."</li>
                                        <li id='Codigo'>".$item['codigo']."</li>
                                        <li id='Valor'>R$ ".$item['preco']."</li>
                                        <li id='Quantidade'>".($item['qtd'] ? $item['qtd'] : 0 )."</li>");
                                    ?>
                                    
                                </ul>
                            </div>
                        <br>
						<form action="./remover_produto_ocultar.php" method="post" id="form">
							<!-- <h4>Escolha a forma de lidar com a remoção:</h4>
							<select name="action" id="action" oninput="selecao(this.value)">
								<option value="none"Selecionar>Selecionar</option>
								<option value="saida" <?= ($item['qtd'] ? 'selected' : 'hidden' ) ?>>Registrar saída dos itens</option>
								<option value="substituicao" >Substituir por um produto existente</option>
								<option value="exclusao" >Excluir todos os registros relacionados a este produto</option>
							</select> -->
							<div id="replace">
							</div>
							<br>
						</form>
						<div class="center-content">
							<button class="btn btn-primary sm-rm" onclick="submitForm()">Ocultar</button><button class="btn btn-danger" onclick="cancelar()">Cancelar</button>
						</div>
                    </div>
				</div>
                <br>
			</section>
            
			<!-- end: page -->
	
		<!-- Specific Page Vendor -->
		<script src="../assets/vendor/select2/select2.js"></script>
		<script src="../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="../assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
		
		<!-- Theme Base, Components and Settings -->
		<script src="../assets/javascripts/theme.js"></script>
		
		<!-- Theme Custom -->
		<script src="../assets/javascripts/theme.custom.js"></script>
		
		<!-- Theme Initialization Files -->
		<script src="../assets/javascripts/theme.init.js"></script>

		<!-- Examples -->
		<script src="../assets/javascripts/tables/examples.datatables.default.js"></script>
		<script src="../assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
		<script src="../assets/javascripts/tables/examples.datatables.tabletools.js"></script>
	</body>
</html>
<!--<a href="#" title="" class="btn btn-primary">
	<span class="icon"><i class="fas fa-trash-alt"></i></span>  remover
</a>-->