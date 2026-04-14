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
						<li><span>Gerar relatório</span></li>
					</ol>

					<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>

			<!-- start: page -->
			<div class="row">
				<div class="box box-info box-solid box-geracaounica">
					<div class="box-header">
						<h3 class="box-title"><i class="far fa-list-alt"></i> Geração de relatório</h3>
					</div>
					<div class="box-body">
						<form class="form-horizontal" id="form_relatorio" method="post">

							<h4 class="mb-xlg">Parâmetros do relatório</h4>

							<div class="form-group" id="orig" style="display: block;">
								<label class="col-md-3 control-label">Sócios</label>
								<div class="col-md-8">
									<select id="tipo_socio" name="tipo_socio">
										<option value="x">Todas as Opções</option>
										<option value="c">Casuais</option>
										<option value="m">Mensais</option>
										<option value="b">Bimestrais</option>
										<option value="t">Trimestrais</option>
										<option value="s">Semestrais</option>
									</select>
								</div>
							</div>

							<div class="form-group" style="display: block;">
								<label class="col-md-3 control-label">Pessoas</label>
								<div class="col-md-8">
									<select id="tipo_pessoa">
										<option value="x">Todas as Opções</option>
										<option value="f">Físicas</option>
										<option value="j">Jurídicas</option>
									</select>
								</div>
							</div>

							<div class="form-group" style="display: block;">
								<label class="col-md-3 control-label">Início de contribuição</label>
								<div class="col-md-8">
									<select id="data-contribuicao" style="display: inline-block; width: auto;">
										<option value="qualquer">Qualquer</option>
										<option value="partir">A partir de</option>
										<option value="ate">Até</option>
										<option value="entre">Entre datas</option>
									</select>
									<input type="date" id="data_inicio" class="form-control" style="display: none; width: auto; margin-left: 10px;">
									<input type="date" id="data_fim" class="form-control" style="display: none; width: auto; margin-left: 10px;">
								</div>
							</div>

							<div class="form-group" style="display: block;">
								<label class="col-md-3 control-label">Status</label>
								<div class="col-md-8">
									<select id="status">
										<option value="x">Todas as Opções</option>
										<option value="0">Ativos</option>
										<option value="1">Inativos</option>
										<option value="2">Inadimplentes</option>
										<option value="3">Inativos temporariamente</option>
										<option value="4">Sem informações de status</option>
									</select>
								</div>
							</div>

							<div class="form-group" style="display: block;">
								<label class="col-md-3 control-label">Valor</label>
								<div class="col-md-2">
									<select id="operador">
										<option value="maior_q">Maior que</option>
										<option value="maior_ia">Maior ou igual a</option>
										<option value="igual_a">Igual a</option>
										<option value="menor_ia">Menor ou igual a</option>
										<option value="menor_q">Menor que</option>
									</select>
									<input type="number" min="0" step="any" style="display: inline-block" class="form-control" id="valor">
								</div>
							</div>

							<div class="form-group" style="display: block;">
								<label class="col-md-3 control-label">Tag (grupo)</label>
								<div class="col-md-2">
									<select id="tag">
										<option value="x">Todas as Opções</option>
										<?php
										//$socio_tag = "socio_tag";
										$stmt = $conexao->prepare("SELECT * FROM socio_tag");
										//$stmt->bind_param("s", $socio_tag);
										$stmt->execute();
										$tags = $stmt->get_result();

										while ($row = $tags->fetch_array(MYSQLI_NUM)) {
											echo ("<option value=" . htmlspecialchars($row[0]) . ">" . htmlspecialchars($row[1]) . "</option>");
										}
										?>
									</select>
								</div>
							</div>



							<!--<div class="form-group" style="display: block;">
								<label class="col-md-3 control-label">Supor periodicidade, (in)atividade e valor de contribuição dos sócios através das cobranças</label>
								<div class="col-md-8">
								<select id="sup">
									  <option value="n">Não</option>
                                      <option value="s">Sim</option>
								</select>
								</div>
							</div>-->

							<div style="margin-top: 2em" class="pull-right mt-2">
								<button type="submit" id="btn_geracao_relatorio" class="btn btn-primary">Gerar relatório</button>
							</div>

						</form>

					</div>
				</div>

				<div class="resultado">

				</div>
				<!-- /.box -->
			</div>

	</div>
	<!-- end: page -->
</section>
</div>
</section>
</body>