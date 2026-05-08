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
								<a href="home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Gerar boleto/carnê</span></li>
						</ol>
					
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->
				<div class="row">
        <div class="box box-info box-solid socioModal">
            <div class="box-header">
              <h3 class="box-title"><i class="far fa-list-alt"></i> Gerar boleto/carnê</h3>
            </div>
            <div class="box-body">
            <form action="processa_contribuicao.php" method="POST">
            <?php if(isset($_REQUEST['id_pesquisa'])){
              $id_socio = $_REQUEST['id_pesquisa'];
              $id_socio = explode("|",$id_socio)[2];
            }else{
              $id_socio = $_GET['socio'];
            } 
            ?>
            <input type="hidden" id="id_socio" name="id_socio" value="<?php echo($id_socio); ?>">
            <?php
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

      $id_socio = filter_var($id_socio, FILTER_SANITIZE_NUMBER_INT);
      
      $stmt = $pdo->prepare("
          SELECT *, s.id_socio as socioid
          FROM socio AS s
          LEFT JOIN pessoa AS p ON s.id_pessoa = p.id_pessoa
          LEFT JOIN socio_tipo AS st ON s.id_sociotipo = st.id_sociotipo
          LEFT JOIN (
              SELECT id_socio, MAX(data) AS ultima_data_doacao
              FROM log_contribuicao
              GROUP BY id_socio
          ) AS lc ON lc.id_socio = s.id_socio
          WHERE s.id_socio = :id_socio
      ");
      $stmt->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
      $stmt->execute();
      
      $registro = $stmt->fetch(PDO::FETCH_ASSOC);
      
      if ($registro !== false) {
        $nome_socio = $registro['nome'];
        $email = $registro['email'];
        $telefone = $registro['telefone'];
        $status = $registro['id_sociostatus'];
        $data_nasc = $registro['data_nascimento'];
        $cpf_cnpj = $registro['cpf'];
        $logradouro = $registro['logradouro'];
        $numero = $registro['numero_endereco'];
        $tipo_socio = $registro['tipo'];
        $complemento = $registro['complemento'];
        $cep = $registro['cep'];
        $socio_tipo = $registro['id_sociotipo'];
        $bairro = $registro['bairro'];
        $cidade = $registro['cidade'];
        $estado = $registro['estado'];
    } else {
        echo "Nenhum registro encontrado para o id_socio especificado.";
    }

        $dados_contrib = json_encode($registro);

        echo("<input type='hidden' name='dados_contrib' value='$dados_contrib'>");
        echo("<input type='hidden' name='socio' value='$id_socio'>");
    ?>
        
        <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Opções de geração -  Sócio: <?php echo($nome_socio." ($tipo_socio)"); ?></h3>
            </div>
            <div class="box-body">
            <div class="row">
            <div class="form-group mb-2 col-xs-6">
            <label for="pessoa">O que você deseja gerar?</label>
                <select class="form-control" name="tipo_geracao" id="tipo_geracao">
                    <option value="1" selected>Boleto único</option>
                    <option value="2">Carnê mensal do próximo ano</option>
                    <option value="3">Carnê mensal meses restantes ano corrente + próximo ano</option>
                    <option value="4">Carnê bimestral meses restantes ano corrente + próximo ano</option>
                </select>
            </div>
            <div class="form-group mb-2 col-xs-6">
           
            <div class="valor">
            <label for="pessoa">Valor</label>
            <input type="number" class="form-control" id="valor_u" name="valor" placeholder="Valor único" required>
            </div>


            </div>
            <div class="form-group mb-2 col-xs-6 mb-3">
           

            <div class="dta">
            
            </div>

            <input type="hidden" name="dataV" id="dataV">
            <input type="hidden" name="parcelas" id="parcelas">

           
            
            

            </div>
            </div>

            <div style="margin-top: 2em" class="box box-info mt-3">
            <div class="box-header with-border">
              <h3 class="box-title">Datas</h3>
            </div>
            <div class="box-body">
            <!-- <div class="row">
                teste
            </div> -->
            <div class="datas"></div>

            <div class="pull-right">
            <a href="./" id="btn_reset" type="reset" class="btn btn-danger">Cancelar</a>
        <button type="submit" class="btn btn-primary btn_salvar_socio">Gerar</button>
            </div>
            </div>
            </form>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>  
      </div>
			<!-- end: page -->
			</section>
		</div>	
	</section>
</body>
