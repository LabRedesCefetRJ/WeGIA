
<section class="body">

		<!-- start: header -->
		<header id="header" class="header">
			
		<!-- end: search & user box -->
		</header>
		<!-- end: header -->
		<div class="inner-wrapper">
			<!-- start: sidebar -->
			<aside id="sidebar-left" class="sidebar-left menuu"></aside>
			<!-- end: sidebar -->

			<section role="main" class="content-body">
				<header class="page-header">
					<h2>Sócios</h2>
					
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="../../home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Sócios</span></li>
						</ol>
					
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->
				<div class="row">
        <div class="box box-warning">
            <div class="box-header with-border">
              <h3 class="box-title">Controle de sócios</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
              <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body" style="">
            <table id="example" class="table table-hover" style="width: 100%">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Nome</th>
                      <th>Email</th>
                      <th>Telefone</th>
                      <th>Endereço</th>
                      <th>CPF/CNPJ</th>
                      <th>Tipo</th>
                      <th>Editar</th>
                      <th>Deletar</th>
                    </tr>
                  </thead>
                  <tbody>
                      <?php
                          $fisica = 0;
                          $juridica = 0;
                          $socios_atrasados = 0;
                          $mensal = 0;
                          $casual = 0;
                          $si_contrib = 0;
                          $query = mysqli_query($conexao, "SELECT *, s.id_socio as socioid FROM socio AS s LEFT JOIN pessoa AS p ON s.id_pessoa = p.id_pessoa LEFT JOIN socio_tipo AS st ON s.id_sociotipo = st.id_sociotipo LEFT JOIN (SELECT id_socio, MAX(data) AS ultima_data_doacao FROM log_contribuicao GROUP BY id_socio) AS lc ON lc.id_socio = s.id_socio");
                          while($resultado = mysqli_fetch_array($query)){
                            switch($resultado['id_sociotipo']){
                              case 0: case 1: 
                                  $casual++;
                                  $contribuinte = "casual";
                                  break;
                              case 2: case 3:
                                  $mensal++;
                                  $contribuinte = "mensal";
                                  break;
                              default:
                                  $si_contrib++;
                                  $contribuinte = "si";
                                  break;
                            }

                            $class = "bg-normal";
                            if($contribuinte == "mensal"){
                              $data_ultima_doacao = date_create($resultado['ultima_data_doacao']);
                              $data_hoje = date_create();
                              $subtracao_datas = date_diff($data_ultima_doacao, $data_hoje);
                              if($subtracao_datas->days > 31){
                                  // Adiciona tag vermelha indicando atraso
                                  $socios_atrasados++;
                                  $class = "bg-danger";
                              }
                            }
                            $id = $resultado['socioid'];
                            $cpf_cnpj = $resultado['cpf'];
                            $nome_s = $resultado['nome'];
                            $email = $resultado['email'];
                            $telefone = $resultado['telefone'];
                            $tipo_socio = $resultado['tipo'];
                            if($resultado['logradouro'] == ""){
                              $endereco = "Endereço não informado/incompleto.";
                            }else{
                              $endereco = $resultado['logradouro']." ".$resultado['numero_endereco'].", ".$resultado['bairro'].", ".$resultado['cidade']." - ".$resultado['estado'];
                            }
                            
                            if(strlen($telefone) == 14){
                              $tel_url = preg_replace("/[^0-9]/", "", $telefone);
                              $telefone = "<a target='_blank' href='http://wa.me/55$tel_url'>$telefone</a>";
                            }
                            if(strlen($cpf_cnpj) == 14){
                              $pessoa = "fisica";
                              $fisica++;
                            }else{
                              $pessoa = "juridica";
                              $juridica++;
                            } 
                            
                            if($email == "null"){
                              $email = '';
                            }
                            if($telefone == "null"){
                              $telefone = '';
                            }
                            $del_json = json_encode(array("id"=>$id,"nome"=>$nome_s,"pessoa"=>$pessoa));
                            echo("<tr><td >$id</td><td onclick='detalhar_socio($id);' style='cursor: pointer' class='$class'>$nome_s</td><td><a href='mailto:$email'>$email</a></td><td>$telefone</td><td>$endereco</td><td>$cpf_cnpj</td><td>$tipo_socio</td><td><a href='editar_socio.php?socio=$id'><button type='button' class='btn btn-default btn-flat'><i class='fa fa-edit'></i></button></a></td><td><button onclick='deletar_socio_modal($del_json)' type='button' class='btn btn-default btn-flat'><i class='fa fa-remove text-red'></i></button></td></tr>");
                          }
                      ?>
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>ID</th>
                      <th>Nome</th>
                      <th>Email</th>
                      <th>Telefone</th>
                      <th>Endereço</th>
                      <th>CPF/CNPJ</th>
                      <th>Tipo</th>
                      <th>Editar</th>
                      <th>Deletar</th>
                    </tr>
                  </tfoot>
                </table>
                <?php $num_socios = mysqli_num_rows(mysqli_query($conexao,"select * from socio")); ?>
                <div class="row">
                <a id="btn_add_socio" class="btn btn-app">
                <span class="badge bg-purple"><span id="qtd_socios"><?php echo($num_socios); ?></span></span>
                <i class="fa fa-user-plus"></i> Adicionar Sócio
              </a>
              <a id="btn_importar_xlsx" class="btn btn-app">
                <i class="fa fa-upload"></i> Importar sócios
              </a>
              <a onclick="location.reload()" id="btn_atualizar" class="btn btn-app">
                <i class="fa fa-refresh"></i> Atualizar
              </a>
              <a id="btn_aniversariantes" class="btn btn-app">
                <i class="fa fa-birthday-cake"></i> Aniversariantes do mês
              </a>
              <a href="graficos.php" id="btn_graficos" class="btn btn-app">
                <i class="fa fa-chart-area"></i> Gráficos
              </a>
              <a id="btn_bd_off" class="btn btn-app" disabled>
                <i class="fa fa-database"></i> Banco de dados
              </a>
                </div>
             
    
            </div>
            <!-- /.box-body -->
          </div>
          <div class="box box-warning">
            <div class="box-header with-border">
              <h3 class="box-title">Últimas doações</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
              <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body" style="">
            <table id="tbDoacoes" class="table table-hover" style="width: 100%">
                  <thead>
                    <tr>
                      <th>Nome</th>
                      <th>Sistema</th>
                      <th>Data geração</th>
                      <th>Valor</th>
                      <th>Data de vencimento</th>
                    </tr>
                  </thead>
                  <tbody>
                      <?php
                          $fisica = 0;
                          $juridica = 0;
                          $socios_atrasados = 0;
                          $mensal = 0;
                          $casual = 0;
                          $si_contrib = 0;
                          $query = mysqli_query($conexao, "SELECT *, sp.nome_sistema as sistema_pagamento, DATE_FORMAT(lc.data, '%d/%m/%Y') as data_geracao, DATE_FORMAT(lc.data_venc_boleto, '%d/%m/%Y') as data_vencimento, s.id_socio as socioid FROM socio AS s LEFT JOIN pessoa AS p ON s.id_pessoa = p.id_pessoa LEFT JOIN socio_tipo AS st ON s.id_sociotipo = st.id_sociotipo LEFT JOIN log_contribuicao AS lc ON lc.id_socio = s.id_socio LEFT JOIN sistema_pagamento as sp ON sp.id = lc.id_sistema WHERE s.id_socio");
                          while($resultado = mysqli_fetch_assoc($query)){
                            $nome = $resultado['nome'];
                            $id_log = $resultado['id_log'];
                            $sistema_pag = $resultado['sistema_pagamento'];
                            if(is_null($id_log)){
                              break;
                            }
                            $data_geracao = $resultado['data_geracao'];
                            $valor = $resultado['valor_boleto'];
                            $data_vencimento =  $resultado['data_vencimento'];
                            echo("<tr><td>$nome</td><td>$sistema_pag</td><td>$data_geracao</td><td>R$ $valor</td><td>$data_vencimento</td></tr>");
                          }
                      ?>
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Nome</th>
                      <th>Sistema</th>
                      <th>Data geração</th>
                      <th>Valor</th>
                      <th>Data de vencimento</th>
                    </tr>
                  </tfoot>
                </table>
             
    
            </div>
            <!-- /.box-body -->
          </div>
				</div>
			<!-- end: page -->
      <div class="row">
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-red">
            <div class="inner">
              <h3><?php echo($socios_atrasados); ?></h3>
              
              <p>Sócio(s) com pagamento atrasado.</p>
            </div>
          </div>
        </div>
        <!-- ./col -->
      </div>
			</section>
		</div>	
		<aside id="sidebar-right" class="sidebar-right">
			<div class="nano">
				<div class="nano-content">
					<a href="#" class="mobile-close visible-xs">
						Collapse <i class="fa fa-chevron-right"></i>
					</a>
				</div>
			</div>
		</aside>
	</section>
</body>
<script>
	function gerarCargo(){
          url = '../../dao/exibir_cargo.php';
          $.ajax({
          data: '',
          type: "POST",
          url: url,
          success: function(response){
            var cargo = response;
            $('#cargo').empty();
            $('#cargo').append('<option selected disabled>Selecionar</option>');
            $.each(cargo,function(i,item){
              $('#cargo').append('<option value="' + item.id_cargo + '">' + item.cargo + '</option>');
            });
          },
          dataType: 'json'
        });
      }

      function adicionar_cargo(){
        url = '../../dao/adicionar_cargo.php';
        var cargo = window.prompt("Cadastre um Novo Cargo:");
        if(!cargo){return}
        situacao = cargo.trim();
        if(cargo == ''){return}              
        
          data = 'cargo=' +cargo; 
          console.log(data);
          $.ajax({
          type: "POST",
          url: url,
          data: data,
          success: function(response){
            gerarCargo();
          },
          dataType: 'text'
        })
      }

	  function verificar_recursos_cargo(cargo_id){
          url = '../../dao/verificar_recursos_cargo.php';              
          data = 'cargo=' +cargo_id; 
          console.log(data);
          $.ajax({
          type: "POST",
          url: url,
          data: data,
          success: function(response){
			var recursos = JSON.parse(response);
            console.log(response);
			$(".recurso").prop("checked",false ).attr("disabled", false);
			for(recurso of recursos){
				$("#recurso_"+recurso).prop("checked",true ).attr("disabled", true);
			}
          },
          dataType: 'text'
        })
      }

	  $(document).ready(function(){
		$("#cargo").change(function(){
			verificar_recursos_cargo($(this).val());
		});
	  });
</script>

           
            <!-- /.box-body -->
 
