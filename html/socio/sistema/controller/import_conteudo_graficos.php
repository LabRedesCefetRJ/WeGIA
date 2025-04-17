
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
					<h2>Gráficos</h2>
					
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Sócios</span></li>
              <li><span>Gráficos</span></li>
						</ol>
					
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->
				<div class="row col-md-6 col-lg-6">
        <div class="box box-warning">
            <div class="box-header with-border">
              <h3 class="box-title">Tipologia dos sócios</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
              <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body">
            <table id="example4" class="table table-hover" style="width: 100%; display: none">
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
                      try {
                          $fisica = 0;
                          $juridica = 0;
                          $socios_atrasados = 0;
                          $mensal = 0;
                          $casual = 0;
                          $si_contrib = 0;

                          $possible_paths = [
                            dirname(__FILE__) . '/../../../../dao/Conexao.php',
                            dirname(__FILE__) . '/../../../dao/Conexao.php',
                            dirname(__FILE__) . '/../../dao/Conexao.php',
                            dirname(__FILE__) . '/../dao/Conexao.php'
                        ];
                        
                        foreach ($possible_paths as $path) {
                            if (file_exists($path)) {
                                require_once realpath($path);
                                break;
                            }
                        }
                        
                        if (!class_exists('Conexao')) {
                            die('Erro: O arquivo conexao.php não foi encontrado em nenhum dos caminhos especificados.');
                        }
                        
                        $pdo = Conexao::connect();

                        $stmt = $pdo->query("
                          SELECT *, s.id_socio as socioid 
                          FROM socio AS s 
                          LEFT JOIN pessoa AS p ON s.id_pessoa = p.id_pessoa 
                          LEFT JOIN socio_tipo AS st ON s.id_sociotipo = st.id_sociotipo 
                        ");
                          while($resultado = $stmt->fetch(PDO::FETCH_ASSOC)){
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
                            $id = htmlspecialchars($resultado['socioid']);
                            $cpf_cnpj = htmlspecialchars($resultado['cpf']);
                            $nome_s = htmlspecialchars($resultado['nome']);
                            $email = htmlspecialchars($resultado['email']);
                            $telefone = htmlspecialchars($resultado['telefone']);
                            $tipo_socio = htmlspecialchars($resultado['tipo']);
                            $endereco = htmlspecialchars($resultado['logradouro']." ".$resultado['numero_endereco'].", ".$resultado['bairro'].", ".$resultado['cidade']." - ".$resultado['estado']);
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
                              
                            $del_json = json_encode(array("id"=>$id,"nome"=>$nome_s,"pessoa"=>$pessoa));
                            echo("<tr><td >$id</td><td onclick='detalhar_socio($id);' style='cursor: pointer' class='$class'>$nome_s</td><td><a href='mailto:$email'>$email</a></td><td>$telefone</td><td>$endereco</td><td>$cpf_cnpj</td><td>$tipo_socio</td><td><a href='editar_socio.php?socio=$id'><button type='button' class='btn btn-default btn-flat'><i class='fa fa-edit'></i></button></a></td><td><button onclick='deletar_socio_modal($del_json)' type='button' class='btn btn-default btn-flat'><i class='fa fa-remove text-red'></i></button></td></tr>");
                          }
                        } catch(Exception $e) {
                          echo "Erro: " . $e->getMessage();
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
                <div class="row">
      <canvas id="grafico1" class="col-xs-6" height="200"></canvas><br>
      </div>
    
            </div>
            <!-- /.box-body -->
          </div>
				</div>

        
     


			<!-- end: page -->
      <div class="row">
        <!-- ./col -->
        <a href="./" class="btn btn-app">
                <i class="fa fa-long-arrow-alt-left"></i> Voltar
              </a>
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
            <!-- /.box-body -->
 
