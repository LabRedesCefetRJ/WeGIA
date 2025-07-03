<section class="body">
	<style>
		.detalhes::-webkit-scrollbar {
			width: 5px;
		}

		/* Track */
		.detalhes::-webkit-scrollbar-track {
			box-shadow: inset 0 0 5px grey;
			border-radius: 10px;
		}

		/* Handle */
		.detalhes::-webkit-scrollbar-thumb {
			background: orange;
			border-radius: 10px;
		}

		/* Handle on hover */
		.detalhes::-webkit-scrollbar-thumb:hover {
			background: #e0af26;
		}
	</style>
	<!-- start: header -->
	<header id="header" class="header">
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
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
						<li><span>Gerar boleto/carnê</span></li>
					</ol>

					<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>

			<!-- start: page -->
			<div class="row">
				<div class="box box-info box-solid box-geracaounica">
					<div class="box-header">
						<h3 class="box-title"><i class="far fa-list-alt"></i> Gerar boleto/carnê personalizado</h3>
					</div>
					<div class="box-body">
						<form action="gerar_contribuicao.php" method="POST">
							<h1>Pesquise o sócio</h1>
							<input type="text" id="id_pesquisa" name="id_pesquisa" placeholder="Digite o nome do sócio ou o cpf/cnpj" autocomplete="off" size="20" class="form-control">
							<div style="display: none" class="row mt-2 configs_unico">
								<div class="form-group mb-2 col-xs-6 mt-2">
									<label for="pessoa">O que você deseja gerar?</label>
									<select class="form-control" name="tipo_geracao" id="tipo_geracao">
										<option selected value="7">Selecionar</option>
										<option value="0">Boleto único</option>
										<option value="1">Carnê mensal</option>
										<option value="2">Carnê bimestral</option>
										<option value="3">Carnê trimestral</option>
										<option value="6">Carnê semestral</option>
									</select>
								</div>
								<div class="form-group mb-2 col-xs-6">

									<div class="valor">
										<label for="pessoa">Valor</label>
										<input type="number" class="form-control" id="valor_u" name="valor" placeholder="Valor único ou por período" required>
									</div>


								</div>
								<div class="form-group mb-2 col-xs-6">

									<div class="data">
										<?php
										$dataVencimento = new DateTime();
										$dataVencimento = $dataVencimento->modify('+7 days')->format('Y-m-d');
										?>
										<label for="data_vencimento">Data de vencimento (se não for boleto único será a data de vencimento da primeira parcela)</label>
										<input type="date" class="form-control" min=<?php echo date('Y-m-d'); ?> id="data_vencimento" name="data_vencimento" placeholder="Data vencimento" value="<?= $dataVencimento ?>" required>
									</div>

								</div>
								<div class="form-group mb-2 col-xs-6">

									<div class="data">
										<label for="data_vencimento">Parcelas</label>
										<input type="number" class="form-control" id="num_parcelas" name="num_parcelas" placeholder="num. parcelas" required>
									</div>



								</div>

								<div style="display: none" class="col-xs-12 div_btn_gerar">
									<button type="button" id="btn_confirma" class="btn btn-primary">Gerar Boleto</button>
									<button type="button" id="btn_geracao_unica" class="btn btn-success" style="display: none;">Confirmar geração</button>
									<button style="display: none" type="button" id="btn_wpp" class="btn btn-success">
										<i style="padding: 0; font-size: 20px" class="fab fa-whatsapp"></i></button>
								</div>
								<div class="col-xs-12 detalhes_unico">


								</div>

							</div>
							<?php
							$socios = array();
							$resultado = mysqli_query($conexao, "SELECT *, s.id_socio as socioid FROM socio AS s LEFT JOIN pessoa AS p ON s.id_pessoa = p.id_pessoa LEFT JOIN socio_tipo AS st ON s.id_sociotipo = st.id_sociotipo");
							while ($registro = mysqli_fetch_assoc($resultado)) {
								$socios[] = $registro['nome'] . '|' . $registro['cpf'] . '|' . $registro['socioid'];
							}
							?>
							<script>
								var socios = <?php
												echo (json_encode($socios));
												?>;
								if ($("#id_pesquisa").length) {
									$("#id_pesquisa").autocomplete({
										source: socios,
										response: function(event, ui) {
											if (ui.content.length == 1) {
												ui.item = ui.content[0];
												$(this).val(ui.item.value)
												$(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
												$("#id_pesquisa").blur();
											}
										}
									});
								}
							</script>
							<div style="margin-top: 2em" class="pull-right mt-2">
								<button type="button" id="btn_gerar_unico" class="btn btn-primary">Próximo</button>
								<button type="button" id="btn_voltar" class="btn btn-primary" style="display: none;">Nova Geração</button>
							</div>
						</form>

					</div>
				</div>

				<!-- /.box -->
				<!-- </div> 
		<div class="row">
        <div class="box box-warning box-solid socioModal box-geracao">
            <div class="box-header">
              <h3 class="box-title"><i class="far fa-list-alt"></i> Geração em massa automática</h3>
            </div>
            <div class="box-body">
				<div class="alert alert-dark text-center" role="alert">
  					Geração em massa de carnês para sócios mensais, bimestrais, trimestrais e semestrais que não sejam inativos e/ou inadimplentes.<br>
					Para ser possível a geração, os sócio devem ter o cpf, data de referência e valor por período cadastrados.
					<br>A geração pode demorar alguns minutos dependendo da quantidade de sócios disponíveis para geração.
					<br><span style="font-size: 20px; color: orange" id="estimacao_tempo"></span>
				</div>
            <form action="geracao_auto.php" method="POST">
                <div class="row">
							<div class="form-group col-xs-12">
								<label for="pessoa">Gerar para</label>
								<select class="form-control" name="geracao" id="geracao">
											<option selected disabled>Selecionar</option>
											<option value="0">Todos</option>
											<option value="4">Mensais</option>
											<option value="1">Bimestrais</option>
											<option value="2">Trimestrais</option>
											<option value="3">Semestrais</option>
								</select>
							</div>
				</div>

				<div style="margin-top: 2em" class="row">
					  <div style="overflow: auto; max-height: 800px;" class="detalhes col-xs-12">
					 
					  </div>
				</div>
				
             <div style="margin-top: 2em" class="pull-right mt-2">
							<button type="button" id="btn_geracao_auto" class="btn btn-primary">Próximo</button>
						</div>
					  </form>

            </div>
            </div>
				-->
				<!-- /.box -->
			</div>
	</div>
	<!-- end: page -->
</section>
</div>
</section>
</body>