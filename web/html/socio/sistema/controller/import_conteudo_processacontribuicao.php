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

          $conexao = Conexao::connect();

          $nome_sistema = "BOLETOFACIL";
          $stmt = $conexao->prepare("SELECT * FROM doacao_boleto_info AS bi JOIN sistema_pagamento AS sp ON (bi.id_sistema = sp.id) JOIN doacao_boleto_regras AS br ON (br.id = bi.id_regras)  WHERE nome_sistema = ?");
          $stmt->execute([$nome_sistema]);
          $dados_sistema = $stmt->fetchAll(PDO::FETCH_ASSOC);

          if ($dados_sistema) {
            foreach ($dados_sistema as $row) {
                $token_api = $row['token_api'];
            }
        
            if (isset($_REQUEST['dados_contrib'])) {
                $dados_contrib = json_decode($_REQUEST['dados_contrib'], true);
            }
        }       
        ?>
        <div class="box box-info box-solid socioModal">
            <div class="box-header">
              <h3 class="box-title"><i class="far fa-list-alt"></i> Gerar boleto/carnê</h3>
            </div>
            <div class="box-body">
            <?php
              switch($tipo_geracao){
                case 1: echo("<h1>Boleto</h1>"); break;
                case 2: echo("<h1>Carnê</h1>"); break;
              }
            ?>
            <form id="frm_editar_socio" action="processa_contribuicao.php" method="POST">
            <input type="hidden" id="id_socio" name="id_socio" value="<?php echo(filter_input(INPUT_GET, 'socio', FILTER_SANITIZE_STRING)); ?>">
        
        <div class="box box-info resultado">
            
            <table class="table table-hover">
            <thead>
              <tr>
                <th scope="col">Vencimento</th>
                <th scope="col">Link</th>
                <th scope="col">Código boleto</th>
              </tr>
            </thead>
            <tbody id="parcelas_tb">
              
            </tbody>
          </table>
            


            <div class="pull-right">
            <a href="./" id="btn_reset" type="reset" class="btn btn-danger">Cancelar</a>
        <button type="submit" class="btn btn-primary btn_salvar_socio">Gerar</button>
            </div>
            </div>
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
<script>
    $(document).ready(function(){

      if(<?php echo($tipo_geracao); ?> == 1){
        var nome = <?php echo("'$nome'"); ?>.replace(/[^a-zA-Zs]/g, "");
        nome = nome.replace(" ",",");
        referencia = nome + Math.round(Math.random()*100000000);

        var api = ""+<?php echo("'$api'"); ?>;

        $.get(api+`token=<?php echo("$token_api"); ?>&description=<?php echo($agradecimento); ?>&amount=<?php echo($valor); ?>&dueDate=<?php echo($dataV); ?>&maxOverdueDays=<?php echo($max_dias_venc); ?>&installments=1&payerName=<?php echo($nome); ?>&payerCpfCnpj=<?php echo($cpf); ?>&payerEmail=<?php echo($email); ?>&payerPhone=<?php echo($telefone); ?>&billingAddressStreet=<?php echo($logradouro); ?>&billingAddressNumber=<?php echo($numero_endereco); ?>&billingAddressComplement=<?php echo($complemento); ?>&billingAddressNeighborhood=<?php echo($bairro); ?>&billingAddressCity=<?php echo($cidade); ?>&billingAddressState=<?php echo($estado); ?>&billingAddressPostcode=<?php echo($cep); ?>&fine=<?php echo($multa); ?>&interest<?php echo($juros); ?>&paymentTypes=BOLETO&notifyPayer=TRUE&reference=${referencia}`).done(function(dados){
                      // cad_log(socioTipo, reference);
                      console.log(dados);
                      for(var charge of dados.data.charges)
                      {
                          $("#parcelas_tb").append(`<tr><td>${charge.dueDate}</td><td><a target='_blank' href="${charge.installmentLink}">Link de pagamento</a></td><td>${charge.payNumber}</td></tr>`)
      
                      }
          })
      }else if(<?php echo($tipo_geracao); ?> == 2){
        var nome = <?php echo("'$nome'"); ?>.replace(/[^a-zA-Zs]/g, "");
        nome = nome.replace(" ",",");
        referencia = nome + Math.round(Math.random()*100000000);

        var api = ""+<?php echo("'$api'"); ?>;

        $.get(api+`token=<?php echo("$token_api"); ?>&description=<?php echo($agradecimento); ?>&amount=<?php echo($valor); ?>&dueDate=<?php echo($dataV); ?>&maxOverdueDays=<?php echo($max_dias_venc); ?>&installments=12&payerName=<?php echo($nome); ?>&payerCpfCnpj=<?php echo($cpf); ?>&payerEmail=<?php echo($email); ?>&payerPhone=<?php echo($telefone); ?>&billingAddressStreet=<?php echo($logradouro); ?>&billingAddressNumber=<?php echo($numero_endereco); ?>&billingAddressComplement=<?php echo($complemento); ?>&billingAddressNeighborhood=<?php echo($bairro); ?>&billingAddressCity=<?php echo($cidade); ?>&billingAddressState=<?php echo($estado); ?>&billingAddressPostcode=<?php echo($cep); ?>&fine=<?php echo($multa); ?>&interest<?php echo($juros); ?>&paymentTypes=BOLETO&notifyPayer=TRUE&reference=${referencia}`).done(function(dados){
                      // cad_log(socioTipo, reference);
                      console.log(dados);
                      for(var charge of dados.data.charges)
                      {
                          $("#parcelas_tb").append(`<tr><td>${charge.dueDate}</td><td><a target='_blank' href="${charge.installmentLink}">Link de pagamento</a></td><td>${charge.payNumber}</td></tr>`)
      
                      }
                      $("#parcelas_tb").append(`<tr><td colspan='2'>Link do carnê inteiro: </td><td><a target='_blank' href='${charge.link}'>${charge.link}</a></td></tr>`)
          })
      }else{
        var nome = <?php echo("'$nome'"); ?>.replace(/[^a-zA-Zs]/g, "");
        nome = nome.replace(" ",",");
        referencia = nome + Math.round(Math.random()*100000000);

        var api = ""+<?php echo("'$api'"); ?>;

        $.get(api+`token=<?php echo("$token_api"); ?>&description=<?php echo($agradecimento); ?>&amount=<?php echo($valor); ?>&dueDate=<?php echo($dataV); ?>&maxOverdueDays=<?php echo($max_dias_venc); ?>&installments=<?php echo($parcelas); ?>&payerName=<?php echo($nome); ?>&payerCpfCnpj=<?php echo($cpf); ?>&payerEmail=<?php echo($email); ?>&payerPhone=<?php echo($telefone); ?>&billingAddressStreet=<?php echo($logradouro); ?>&billingAddressNumber=<?php echo($numero_endereco); ?>&billingAddressComplement=<?php echo($complemento); ?>&billingAddressNeighborhood=<?php echo($bairro); ?>&billingAddressCity=<?php echo($cidade); ?>&billingAddressState=<?php echo($estado); ?>&billingAddressPostcode=<?php echo($cep); ?>&fine=<?php echo($multa); ?>&interest<?php echo($juros); ?>&paymentTypes=BOLETO&notifyPayer=TRUE&reference=${referencia}`).done(function(dados){
                      // cad_log(socioTipo, reference);
                      console.log(dados);
                      for(var charge of dados.data.charges)
                      {
                          $("#parcelas_tb").append(`<tr><td>${charge.dueDate}</td><td><a target='_blank' href="${charge.installmentLink}">Link de pagamento</a></td><td>${charge.payNumber}</td></tr>`)
      
                      }
                      $("#parcelas_tb").append(`<tr><td colspan='2'>Link do carnê inteiro: </td><td><a target='_blank' href='${charge.link}'>${charge.link}</a></td></tr>`)
          })
      }

    });
</script>
