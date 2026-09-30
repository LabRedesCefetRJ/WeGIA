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

		.relatorio-card {
			border: 1px solid #e3e7ec;
			border-radius: 12px;
			box-shadow: 0 8px 20px rgba(17, 24, 39, 0.06);
			overflow: hidden;
			background: #fff;
		}

		.relatorio-card .panel-heading {
			background: linear-gradient(135deg, #f8fafc 0%, #eef4ff 100%);
			border-bottom: 1px solid #e6edf6;
			padding: 18px 20px;
		}

		.relatorio-card .panel-title {
			font-size: 2rem;
			font-weight: 600;
			margin: 0;
			color: #1f2937;
		}

		.relatorio-card .panel-body {
			padding: 24px 22px 18px;
		}

		.relatorio-form {
			margin: 0;
		}

		.relatorio-form .form-group {
			margin-bottom: 20px;
		}

		.relatorio-form .control-label {
			font-weight: 600;
			color: #374151;
			padding-top: 9px;
		}

		.relatorio-form select,
		.relatorio-form input {
			border: 1px solid #d7def0;
			border-radius: 8px;
			min-height: 38px;
			box-shadow: none;
			padding: 8px 12px;
			background: #fff;
		}

		.relatorio-form select:focus,
		.relatorio-form input:focus {
			border-color: #5c7cfa;
			box-shadow: 0 0 0 2px rgba(92, 124, 250, 0.12);
		}

		.relatorio-form .date-group {
			display: flex;
			align-items: center;
			gap: 10px;
			flex-wrap: wrap;
		}

		.relatorio-form .date-label {
			font-size: 12px;
			color: #475569;
			margin: 0 0 0 6px;
			font-weight: 600;
		}

		.relatorio-actions {
			display: flex;
			justify-content: center;
			margin-top: 14px;
		}

		.relatorio-actions .btn-primary {
			border-radius: 8px;
			padding: 10px 16px;
			font-weight: 600;
			background-color: #0d6efd;
			border-color: #0d6efd;
		}

		.relatorio-result {
			margin-top: 20px;
		}

		.relatorio-panel {
			border: 1px solid #e3e7ec;
			border-radius: 12px;
			box-shadow: 0 8px 20px rgba(17, 24, 39, 0.04);
			overflow: hidden;
			background: #fff;
		}

		.relatorio-panel .panel-heading {
			background: linear-gradient(135deg, #f8fafc 0%, #eef4ff 100%);
			padding: 16px 20px;
			border-bottom: 1px solid #e6edf6;
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 12px;
		}

		.relatorio-panel .panel-title {
			margin: 0;
			font-size: 2rem;
			font-weight: 600;
			color: #1f2937;
		}

		.relatorio-panel .panel-body {
			padding: 20px;
		}

		.resumo-relatorio {
			display: flex;
			flex-wrap: wrap;
			gap: 8px 10px;
			margin-bottom: 16px;
		}

		.badge-resumo {
			display: inline-flex;
			align-items: center;
			padding: 7px 12px;
			border-radius: 999px;
			background: #eef2ff;
			color: #374151;
			font-size: 12px;
			font-weight: 600;
			border: 1px solid #dfe7ff;
		}

		.relatorio-table {
			margin-bottom: 0;
			border-radius: 10px;
			overflow: hidden;
		}

		.relatorio-table thead th {
			background: #f3f6fb;
			color: #334155;
			font-size: 12px;
			text-transform: uppercase;
			letter-spacing: 0.03em;
			vertical-align: middle;
		}

		.relatorio-table tbody td {
			vertical-align: middle;
			color: #1f2937;
		}

		.relatorio-table tbody tr:hover {
			background: #f8fbff;
		}

		.print-button {
			border-radius: 8px;
			font-weight: 600;
			padding: 8px 12px;
		}

		.empty-state,
		.error-state {
			margin-top: 20px;
			padding: 18px 20px;
			border-radius: 10px;
			border: 1px solid transparent;
		}

		.empty-state {
			background: #edf7ff;
			border-color: #cfe9ff;
			color: #0f172a;
		}

		.error-state {
			background: #fff2f2;
			border-color: #ffc7c7;
			color: #7f1d1d;
		}

		@media print {
			.menuu,
			.page-header,
			.box-geracaounica,
			.header,
			.print-button,
			#sidebar-left {
				display: none !important;
			}

			body {
				background: #fff !important;
			}

			.inner-wrapper,
			.content-body,
			.row,
			.relatorio-result,
			.panel {
				display: block !important;
				width: 100% !important;
				max-width: 100% !important;
				margin: 0 !important;
				padding: 0 !important;
			}

			.resultado {
				display: block !important;
				margin-top: 0;
				width: 100%;
			}

			.relatorio-table {
				font-size: 10px;
				width: 100%;
			}
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
				<div class="col-md-12">
					<div class="panel panel-default relatorio-card">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="far fa-list-alt"></i> Parâmetros de pesquisa</h3>
						</div>
						<div class="panel-body">
							<form class="form-horizontal relatorio-form" id="form_relatorio" method="post">

								<div class="form-group" id="orig" style="display: block;">
									<label class="col-md-3 control-label">Sócios</label>
									<div class="col-md-8">
										<select id="tipo_socio" name="tipo_socio" class="form-control">
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
										<select id="tipo_pessoa" class="form-control">
											<option value="x">Todas as Opções</option>
											<option value="f">Físicas</option>
											<option value="j">Jurídicas</option>
										</select>
									</div>
								</div>

								<div class="form-group" style="display: block;">
									<label class="col-md-3 control-label">Início de contribuição</label>
									<div class="col-md-8 date-group">
										<select id="data-contribuicao" class="form-control" style="display: inline-block; width: auto; min-width: 160px;">
											<option value="qualquer">Qualquer</option>
											<option value="partir">A partir de</option>
											<option value="ate">Até</option>
											<option value="entre">Entre datas</option>
										</select>
										<input type="date" id="data_inicio" class="form-control" style="display: none; width: auto; min-width: 150px; margin-left: 10px;">
										<input type="date" id="data_fim" class="form-control" style="display: none; width: auto; min-width: 150px; margin-left: 10px;">
									</div>
								</div>

								<div class="form-group" style="display: block;">
									<label class="col-md-3 control-label">Status</label>
									<div class="col-md-8">
										<select id="status" class="form-control">
											<option value="x">Todas as Opções</option>
											<?php
											$stmt_status = $conexao->prepare("SELECT id_sociostatus, status FROM socio_status ORDER BY id_sociostatus");
											$stmt_status->execute();
											$statuses = $stmt_status->get_result();
											while ($row_status = $statuses->fetch_assoc()) {
												$selected = ($row_status['id_sociostatus'] == $status) ? 'selected' : '';
												echo "<option value=" . htmlspecialchars($row_status['id_sociostatus']) . " $selected>" . htmlspecialchars($row_status['status']) . "</option>";
											}
											?>
										</select>
									</div>
								</div>

								<div class="form-group" style="display: block;">
									<label class="col-md-3 control-label">Valor</label>
									<div class="col-md-4">
										<select id="operador" class="form-control" style="display: inline-block; width: auto; min-width: 170px;">
											<option value="maior_q">Maior que</option>
											<option value="maior_ia">Maior ou igual a</option>
											<option value="igual_a">Igual a</option>
											<option value="menor_ia">Menor ou igual a</option>
											<option value="menor_q">Menor que</option>
										</select>
										<input type="number" min="0" step="any" class="form-control" id="valor" style="display: inline-block; width: auto; min-width: 150px; margin-top: 10px;">
									</div>
								</div>

								<div class="form-group" style="display: block;">
									<label class="col-md-3 control-label">Tag (grupo)</label>
									<div class="col-md-4">
										<select id="tag" class="form-control">
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

								<div class="relatorio-actions">
									<button type="submit" id="btn_geracao_relatorio" class="btn btn-primary"><i class="fa fa-file-text-o"></i> Gerar relatório</button>
								</div>
							</form>
						</div>
					</div>
				</div>

				<div class="col-md-12 relatorio-result">
					<div class="resultado" aria-live="polite"></div>
				</div>
			</div>
		</section>
	</div>
</section>
</body>