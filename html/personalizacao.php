
<?php
	session_start();
	if(!isset($_SESSION['usuario'])){
		header ("Location: ../index.php");
	}
	require_once "../dao/Conexao.php";
	$pdo = Conexao::connect();
	require_once "../classes/Personalizacao_campo.php";

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
	$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$id_pessoa = $_SESSION['id_pessoa'];
	$resultado = mysqli_query($conexao, "SELECT * FROM funcionario WHERE id_pessoa=$id_pessoa");
	if(!is_null($resultado)){
		$id_cargo = mysqli_fetch_array($resultado);
		if(!is_null($id_cargo)){
			$id_cargo = $id_cargo['id_cargo'];
		}
		$resultado = mysqli_query($conexao, "SELECT * FROM permissao WHERE id_cargo=$id_cargo and id_recurso=9");
		if(!is_bool($resultado) and mysqli_num_rows($resultado)){
			$permissao = mysqli_fetch_array($resultado);
			if($permissao['id_acao'] < 7){
        $msg = "Você não tem as permissões necessárias para essa página.";
        header("Location: ./home.php?msg_c=$msg");
			}
			$permissao = $permissao['id_acao'];
		}else{
        	$permissao = 1;
          $msg = "Você não tem as permissões necessárias para essa página.";
          header("Location: ./home.php?msg_c=$msg");
		}	
	}else{
		$permissao = 1;
    $msg = "Você não tem as permissões necessárias para essa página.";
    header("Location: ./home.php?msg_c=$msg");
	}	
	
	// Adiciona a Função display_campo($nome_campo, $tipo_campo)
	require_once "personalizacao_display.php";

	require_once ROOT."/controle/EnderecoControle.php";

	// Query das tabelas

	$res = $pdo->query("select * from selecao_paragrafo;");
	$txt_tab = $res->fetchAll(PDO::FETCH_ASSOC);

	$res = $pdo->query("
	select c.id_campo as id_selecao, c.nome_campo, i.imagem as arquivo
	from campo_imagem c
	left join tabela_imagem_campo ic on c.id_campo = ic.id_campo
	left join imagem i on ic.id_imagem = i.id_imagem
	where c.tipo = 'img';");
	$img_tab = $res->fetchAll(PDO::FETCH_ASSOC);


	$res = $pdo->query("select nome_campo from campo_imagem where tipo='car';");
	$carrossels = $res->fetchAll(PDO::FETCH_ASSOC);

	function getCarrossel($carrossels, $key, $pdo){
		$nome_campo = $carrossels[$key]["nome_campo"];
		$res = $pdo->query("
		select c.id_campo as id_selecao, c.nome_campo, i.imagem as arquivo
		from campo_imagem c
		inner join tabela_imagem_campo ic on c.id_campo = ic.id_campo
		inner join imagem i on ic.id_imagem = i.id_imagem
		where c.nome_campo='$nome_campo';");
		return $res->fetchAll(PDO::FETCH_ASSOC);
	}

	$endereco = new EnderecoControle;
	$endereco->listarInstituicao();

?>
<!doctype html>
<html class="fixed">
<head>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Personalização</title>

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Web Fonts  -->
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
	<link rel="stylesheet" href="../assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo",'file');?>" type="image/x-icon">
	
	<!-- Theme CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/theme.css" />
	
	<!-- Skin CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/skins/default.css" />
	
	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/theme-custom.css">
	
	<!-- Head Libs -->
	<script src="../assets/vendor/modernizr/modernizr.js"></script>

	<!-- Personalizacao CSS -->
	<link rel="stylesheet" href="../css/personalizacao-theme.css" />
	
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

	<!-- javascript functions --> <script
	src="../Functions/onlyNumbers.js"></script> <script
	src="../Functions/onlyChars.js"></script> <script
	src="../Functions/mascara.js"></script>

	<!-- jquery functions -->
	<script>
   		document.write('<a href="' + document.referrer + '"></a>');
	</script>

	<script type="text/javascript">
		$(function () {
	      $("#header").load("header.php");
	      $(".menuu").load("menu.php");

	      $("#nome").prop('disabled', true);
          $("#cep").prop('disabled', true);
          $("#uf").prop('disabled', true);
          $("#cidade").prop('disabled', true);
          $("#bairro").prop('disabled', true);
          $("#rua").prop('disabled', true);
          $("#numero_residencia").prop('disabled', true);
          $("#complemento").prop('disabled', true);
          $("#ibge").prop('disabled', true);
	      var endereco = <?php echo $_SESSION['endereco'];?> ;
	      if(endereco=="")
	      {
	      	$("#metodo").val("incluirEndereco");
	      }
	      else
	      {
	      	$("#metodo").val("alterarEndereco");
	      }
	      $.each(endereco,function(i,item){	
	      	console.log(endereco);
              $("#nome").val(item.nome).prop('disabled', true);
              $("#cep").val(item.cep).prop('disabled', true);
              $("#uf").val(item.estado).prop('disabled', true);
              $("#cidade").val(item.cidade).prop('disabled', true);
              $("#bairro").val(item.bairro).prop('disabled', true);
              $("#rua").val(item.logradouro).prop('disabled', true);
              $("#numero_residencia").val(item.numero_endereco).prop('disabled', true);
              $("#complemento").val(item.complemento).prop('disabled', true);
              $("#ibge").val(item.ibge).prop('disabled', true);
              if (item.numero_endereco=='Sem número' || item.numero_endereco==null ) {
                $("#numResidencial").prop('checked',true);
              }
              });
	    });	
	    function editar_endereco(){
         
         	$("#nome").prop('disabled', false);
            $("#cep").prop('disabled', false);
            $("#uf").prop('disabled', false);
            $("#cidade").prop('disabled', false);
            $("#bairro").prop('disabled', false);
            $("#rua").prop('disabled', false);
            $("#complemento").prop('disabled', false);
            $("#ibge").prop('disabled', false);         
            $("#numResidencial").prop('disabled', false);
            $("#numero_residencia").prop('disabled', false)
            $("#botaoEditarEndereco").html('Cancelar');
            $("#botaoSalvarEndereco").prop('disabled', false);
            $("#botaoEditarEndereco").removeAttr('onclick');
            $("#botaoEditarEndereco").attr('onclick', "return cancelar_endereco()");
        }
        function numero_residencial()
        {
        	if($("#numResidencial").prop('checked'))
        	{
        		document.getElementById("numero_residencia").readOnly=true;
        	}
        	else
        	{
        		document.getElementById("numero_residencia").readOnly=false;
        	}
        }
        function cancelar_endereco(){
            $("#cep").prop('disabled', true);
            $("#uf").prop('disabled', true);
            $("#cidade").prop('disabled', true);
            $("#bairro").prop('disabled', true);
            $("#rua").prop('disabled', true);
            $("#complemento").prop('disabled', true);
            $("#ibge").prop('disabled', true);
            $("#numResidencial").prop('disabled', true);
            $("#numero_residencia").prop('disabled', true);
         
            $("#botaoEditarEndereco").html('Editar');
            $("#botaoSalvarEndereco").prop('disabled', true);
            $("#botaoEditarEndereco").removeAttr('onclick');
            $("#botaoEditarEndereco").attr('onclick', "return editar_endereco()");
         
          }
        function limpa_formulário_cep() {
            //Limpa valores do formulário de cep.
            document.getElementById('rua').value=("");
            document.getElementById('bairro').value=("");
            document.getElementById('cidade').value=("");
            document.getElementById('uf').value=("");
            document.getElementById('ibge').value=("");
          }

        function meu_callback(conteudo) {
            if (!("erro" in conteudo)) {
                //Atualiza os campos com os valores.
                document.getElementById('rua').value=(conteudo.logradouro);
                document.getElementById('bairro').value=(conteudo.bairro);
                document.getElementById('cidade').value=(conteudo.localidade);
                document.getElementById('uf').value=(conteudo.uf);
                document.getElementById('ibge').value=(conteudo.ibge);
            }
            else {
                //CEP não Encontrado.
                limpa_formulário_cep();
                alert("CEP não encontrado.");
            }
          }

        function pesquisacep(valor) {
            //Nova variável "cep" somente com dígitos.
            var cep = valor.replace(/\D/g, '');
       
            //Verifica se campo cep possui valor informado.
            if (cep != "") {
       
              //Expressão regular para validar o CEP.
              var validacep = /^[0-9]{8}$/;
     
              //Valida o formato do CEP.
              if(validacep.test(cep)) {
     
                //Preenche os campos com "..." enquanto consulta webservice.
                document.getElementById('rua').value="...";
                document.getElementById('bairro').value="...";
                document.getElementById('cidade').value="...";
                document.getElementById('uf').value="...";
                document.getElementById('ibge').value="...";
   
                //Cria um elemento javascript.
                var script = document.createElement('script');
   
                //Sincroniza com o callback.
                script.src = 'https://viacep.com.br/ws/'+ cep + '/json/?callback=meu_callback';
   
                //Insere script no documento e carrega o conteúdo.
                document.body.appendChild(script);
     
              } //end if.
              else {
                  //cep é inválido.
                  limpa_formulário_cep();
                  alert("Formato de CEP inválido.");
              }
            } //end if.
            else {
                //cep sem valor, limpa formulário.
                limpa_formulário_cep();
            }
         
          };
         
    </script>
    
    <!-- javascript tab management script -->

    

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
					<h2>Edição de Conteúdo</h2>
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="./home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Edição de Conteúdo</span></li>
						</ol>
						<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!--start: page-->

				<div class="row">
					<div class="col-md-4 col-lg-2"></div>
					<div class="col-md-8 col-lg-8">

						<!-- Caso as alterações feitas sejam feitas com sucesso -->
						<?php if (isset($_GET['msg'])){ if ($_GET['msg'] == 'success'){ echo('<div class="alert alert-success"><i class="fas fa-check mr-md"></i><a href="#" class="close" onclick="closeMsg()" data-dismiss="alert" aria-label="close">&times;</a>Edição feita com sucesso!</div>');}}?>

						<!-- Caso haja um erro fatal na alteração dos dados -->
						<?php if (isset($_GET['msg'])){ if ($_GET['msg'] == 'error'){ echo('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-md"></i><a href="#" class="close" onclick="closeMsg()" data-dismiss="alert" aria-label="close">&times;</a>'. htmlspecialchars($_GET["err"]) .'</div>');}}?>
						
						<!-- Caso haja um erro na alteração dos dados que não seja fatal -->
						<?php if (isset($_GET['msg'])){ if ($_GET['msg'] == 'warn'){ echo('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-md"></i><a href="#" class="close" onclick="closeMsg()" data-dismiss="alert" aria-label="close">&times;</a>'. htmlspecialchars($_GET["err"]) .'</div>');}}?>
						
						<ul class="nav nav-tabs" id="myTab" role="tablist">
							<li class="nav-item active">
								<a class="nav-link active" id="img-tab-selector" data-toggle="tab" href="#img-tab" role="tab" aria-controls="img" aria-selected="true">Imagens</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="txt-tab-selector" data-toggle="tab" href="#txt-tab" role="tab" aria-controls="txt" aria-selected="false">Textos</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="txt-tab-selector" data-toggle="tab" href="#address-tab" role="tab" aria-controls="txt" aria-selected="false">Endereço da instituição</a>
							</li>
						</ul>
						<div class="tab-content" id="myTabContent">

							<!-- start: image tab pane-->

							<div class="tab-pane active" id="img-tab" role="tabpanel" aria-labelledby="img-tab">
								<table class="table table-hover">
									<thead>
										<tr>
										<th scope="col" width="40%">Campo</th>
										<th scope="col">Visualização</th>
										</tr>
									</thead>
									<tbody>

										<?php
											foreach ($img_tab as $key => $valor){
												$img_item = new Campo(
													$valor["id_selecao"],
													"img",
													($valor["nome_campo"] ? $valor["nome_campo"] : "Logo"),
													($valor["arquivo"] ? $valor["arquivo"] : "")
												);
												$img_item->display();
											}
											if (sizeof($carrossels) != 0){
												foreach ($carrossels as $key => $valor){
													$car_tab = getCarrossel($carrossels, $key, $pdo);
													$car_name = ( !!$car_tab ? $car_tab[0]["nome_campo"] : "Carrossel");
													echo('<tr onclick="post(' . "'personalizacao_selecao.php', {tipo: 'car', nome_car: '$car_name'})" . '"' . ">
													<td class='v-center'><div>$car_name</div></td>
													<td>");
													foreach ($car_tab as $key => $valor){
														$car_file = gzuncompress($valor["arquivo"] ? $valor["arquivo"] : "");
														echo("<img src='data:image;base64,$car_file' class='my-sm' width='100%'>");
													}
													echo("</td>
													</tr>");
												}
											}
										?>
										</tr>
									</tbody>
								</table>
							</div>

							<!-- end: image tab pane-->

							<!-- start: text tab pane-->
							
							<div class="tab-pane fade" id="txt-tab" role="tabpanel" aria-labelledby="txt-tab">
								<table class="table table">
									<thead>
										<tr>
										<th scope="col" width="6%">Editar</th>
										<th scope="col" width="20%">Campo</th>
										<th scope="col">Visualização</th>
										</tr>
									</thead>
									<tbody>
										<?php
											foreach ($txt_tab as $key => $value){
												$txt_item = new Campo(
													$txt_tab[$key]["id_selecao"],
													"txt",
													$txt_tab[$key]["nome_campo"],
													$txt_tab[$key]["paragrafo"]
												);
												$txt_item->display();
											}
										?>
									</tbody>
								</table>
							</div>

							<div class="tab-pane fade" id="address-tab" role="tabpanel" aria-labelledby="txt-tab">
								<form class="form-horizontal" method="post" action="../controle/control.php">
                            		<input type="hidden" name="nomeClasse" value="EnderecoControle">
                            		<input type="hidden" name="metodo" id="metodo">
                            		<div class="form-group">
                              			<label class="col-md-3 control-label" for="nome">Nome da instituição</label>
                              			<div class="col-md-8">
                                			<input type="text" name="nome" size="10" class="form-control" id="nome">
                              			</div>
                            		</div>
									<div class="form-group">
                              			<label class="col-md-3 control-label" for="cep">CEP</label>
                              			<div class="col-md-8">
                                			<input type="text" name="cep"  value="" size="10" onblur="pesquisacep(this.value);" class="form-control" id="cep" maxlength="9" placeholder="Ex: 22222-222" onkeypress="return Onlynumbers(event)" onkeyup="mascara('#####-###',this,event)" >
                              			</div>
                            		</div>
                            		<div class="form-group">
                              			<label class="col-md-3 control-label" for="uf">Estado</label>
                              			<div class="col-md-8">
                                		<input type="text" name="uf" size="60" class="form-control" id="uf" >
                              			</div>
                              		</div>
                              		<div class="form-group">
                              			<label class="col-md-3 control-label" for="cidade">Cidade</label>
                              			<div class="col-md-8">
                                			<input type="text" size="40" class="form-control" name="cidade" id="cidade" >
                              			</div>
                            		</div>
                            		<div class="form-group">
                              			<label class="col-md-3 control-label" for="bairro">Bairro</label>
                              			<div class="col-md-8">
                                			<input type="text" name="bairro" size="40" class="form-control" id="bairro" >
                              			</div>
                            		</div>
                            		<div class="form-group">
                              			<label class="col-md-3 control-label" for="rua">Logradouro</label>
                              			<div class="col-md-8">
                                			<input type="text" name="rua" size="2" class="form-control" id="rua" >
                              			</div>
                            		</div>
                            		<div class="form-group">
                              			<label class="col-md-3 control-label" for="profileCompany">Número residencial</label>
                              			<div class="col-md-4">
                                			<input type="number" min="0" oninput="this.value = Math.abs(this.value)" class="form-control" name="numero_residencia"  id="numero_residencia">
                              			</div>
                              			<div class="col-md-3"> 
                                			<label>Não possuo número
                                  				<input type="checkbox" id="numResidencial" name="naoPossuiNumeroResidencial"  style="margin-left: 4px" onclick="return numero_residencial()">
                                			</label>
                              			</div>
                            		</div>
                            		<div class="form-group">
                              			<label class="col-md-3 control-label" for="complemento">Complemento</label>
                              			<div class="col-md-8">
                                			<input type="text" class="form-control" name="complemento" id="complemento">
                              			</div>
                            		</div>
                            		<div class="form-group">
                              			<label class="col-md-3 control-label" for="ibge">IBGE</label>
                              			<div class="col-md-8">
                                			<input type="text" size="8" name="ibge" class="form-control"  id="ibge">
                              			</div>
                            		</div>
                            		<br/>
                            		<button type="button" class="btn btn-primary" id="botaoEditarEndereco" onclick="return editar_endereco()">Editar</button>
                            		<input type="submit" class="btn btn-primary" disabled="true"  value="Salvar" id="botaoSalvarEndereco" disabled="true">
								</form>
							</div>

							<!-- end: text tab pane-->

						</div>
					</div>
				</div>
				<!-- end: page -->
				
				<script>

					function post(path, params, method='post') {
						const form = document.createElement('form');
						form.method = method;
						form.action = path;

						for (const key in params) {
							if (params.hasOwnProperty(key)) {
								const hiddenField = document.createElement('input');
								hiddenField.type = 'hidden';
								hiddenField.name = key;
								hiddenField.value = params[key];

								form.appendChild(hiddenField);
							}
						}

						document.body.appendChild(form);
						form.submit();
					}

					// Alterna entre o texto normal e a textarea da linha selecionada
					function tr_select(id){
						selected = id
						var row = window.document.getElementById(id)


						var btn_field = row.firstElementChild

						var btn_togle = btn_field.firstElementChild.children[0]
						var btn_submit = btn_field.firstElementChild.children[1]


						var column_2 = row.children[2]
						var column_3 = row.children[3]

						if (column_2.style.display != 'none'){
							column_2.style.display = 'none'
							column_3.style.display = ''
							column_3.firstElementChild.innerHTML = column_2.innerText
							btn_togle.firstElementChild.className = "fas fa-chevron-left"
							btn_submit.style.display = ''
						}else{
							column_2.style.display = ''
							column_3.style.display = 'none'
							btn_togle.firstElementChild.className = "fas fa-edit"
							btn_submit.style.display = 'none'
						}
					}

					function closeMsg(){
						window.history.replaceState({}, document.title, window.location.pathname);
					}
					
				</script>

			</section>
		</div>
	</section>

	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/conf.html" width="200" height="60" style="border:none;"></iframe>
	</div> 
</body>
</html>
