<?php
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

	session_start();
?>
<style>
	ul.nav-main > li:not(.visivel){ /* Faz com que apenas os selecionados por verificar_modulos se tornem visíveis*/
	display: none !important;
	}
</style>
<script type="text/javascript">
	$(document).ready(function(){
	verificar_modulos();
	})
	function verificar_modulos(){
		let tentativas = 0;
		let url = "<?php echo WWW;?>dao/verificar_modulos_visiveis.php";
		$.ajax({
			type: "POST",
			url: url,
			success: function(response){
			var visiveis = JSON.parse(response);
			for(visivel of visiveis){
			$("#"+visivel).addClass("visivel");
			}
		},
	dataType: 'text'
	});
	}
</script>
	<div class="sidebar-header">
		<div class="sidebar-title">
			Menu
		</div>
		<div class="sidebar-toggle hidden-xs" data-toggle-class="sidebar-left-collapsed" data-target="html" data-fire-event="sidebar-left-toggle">
			<i class="fa fa-bars" aria-label="Toggle sidebar"></i>
		</div>
	</div>
	
	<div class="nano">
		<div class="nano-content" tabindex="0" style="right: -17px;display: flex;flex-direction: column;justify-content: space-between; padding-bottom: 0;">
			<nav id="menu" class="nav-main" role="navigation">
				<ul class="nav nav-main">
					<li class="visivel">
						<a href="<?= WWW ?>html/home.php">
							<i class="fa-solid fa-home" aria-hidden="true"></i>
							<span>Início</span>
						</a>
					</li>

					<li class="nav-parent nav-active" id="1">
						<a>
							<i class="far fa-address-book"></i>
							<span>Pessoas</span>
						</a>
						<ul class="nav nav-children">
							<li class="nav-parent nav-active">
								<a>
									<i class="fa fa-briefcase" aria-hidden="true"></i>
									<span>Funcionários</span>
								</a>
								<ul class="nav nav-children">
									<li>
										<a href="<?= WWW ?>html/funcionario/pre_cadastro_funcionario.php">
											<span>Cadastrar Funcionário</span>
										</a>
									</li>
									<li>
										<a href="<?= WWW ?>controle/control.php?metodo=listarTodos&nomeClasse=FuncionarioControle&nextPage=<?= WWW ?>html/funcionario/informacao_funcionario.php">
											<span>Informações Funcionários</span>
										</a>
									</li>
								</ul>
							</li>
							<li class="nav-parent nav-active">
								<a>
									<i class="fa fa-user" aria-hidden="true"></i>
									<span>Atendidos</span>
								</a>
								<ul class="nav nav-children">
									<li>
										<a href="<?= WWW ?>html/atendido/processo_aceitacao.php">
											<span>Processo de Aceitação</span>
										</a>
									</li>	
									<li>
										<a href="<?= WWW ?>html/atendido/pre_cadastro_atendido.php">
											<span>Cadastrar Atendido</span>
										</a>
									</li>
									<li>
										<a href="<?= WWW ?>controle/control.php?metodo=listarTodos&nomeClasse=AtendidoControle&nextPage=<?= WWW ?>html/atendido/Informacao_Atendido.php">
											<span>Informações Atendidos</span>
										</a>
									</li>
									<li>
										<a href="<?= WWW ?>controle/control.php?metodo=listarTodos&nomeClasse=AtendidoControle&nextPage=<?= WWW ?>html/atendido/cadastro_ocorrencia.php">
											<span>Cadastrar Ocorrências</span>
										</a>
									</li>
								</ul>
							</li>
						</ul>
					</li>

					<li class="nav-parent nav-active" id="6">
						<a>
							<i class="fa fa-paw" aria-hidden="true"></i>
							<span>Pet</span>
						</a>
						<ul class="nav nav-children">
							<li>
								<a href="<?= WWW ?>html/pet/cadastro_pet.php">
									<i class="fa fa-paw"></i>
									<span>Cadastrar Pet</span>
								</a>
							</li>
							<li>
								<a href="<?= WWW ?>html/pet/informacao_pet.php">
									<i class="fa fa-clipboard-list"></i>
									<span>Informações Pets</span>
								</a>
							</li>
							

							

							<li class="nav-parent nav-active" >
								<a>
									<i class="fa fa-ambulance" aria-hidden="true"></i>
									<span>Saúde Pet</span>
								</a>

								<ul class="nav nav-children">
									
									
									<li>
										<a href="<?= WWW ?>html/pet/cadastro_ficha_medica_pet.php">
											<span>Cadastrar Ficha Médica Pet</span>
										</a>
									</li>
									
									<li>
												<a href="<?= WWW ?>html/pet/vacinacao_vermifugacao.php">
													<span>Vacinação e Vermifugação</span>
												</a>
											</li>

									<li class="nav-parent nav-active">
										<a>
											<i class="fa fa-pills" aria-hidden="true"></i>
											<span>Medicamentos</span>
										</a>

										<ul class="nav nav-children">
											<li>
												<a href="<?= WWW ?>html/pet/cadastrar_medicamento.php">
													<span>Cadastrar Medicamentos</span>
												</a>
											</li>
											<li>
												<a href="<?= WWW ?>html/pet/informacao_medicamento.php">
													<span>Informacões Medicamentos</span>
												</a>
											</li>
											
										</ul>

									</li>
								</ul>
							</li>


							<li class="nav-parent nav-active">
								<a>
									<i class="fa fa-user"></i></i><i class="fas fa-paw"></i>
									<span>Adotantes</span>
								</a>
								<ul class="nav nav-children">
									<li>
										<a href="<?= WWW ?>/html/pet/adotantes/pre_cadastro_adotante.php">
											<span>Cadastrar Adotante</span>
										</a>
									</li>
									<li>
										<a href="<?= WWW ?>/html/pet/adotantes/informacao_adotantes.php">
											<span>Informações Adotantes</span>
										</a>
									</li>
								</ul>					
							</li>
							
						</ul>
					</li>


						
					<li class="nav-parent nav-active" id="2">
						<a>
							<i class="fa fa-copy" aria-hidden="true"></i>
							<span>Material e Patrimônio</span>
						</a>

						<ul class="nav nav-children">
							<li class="nav-parent nav-active">
								<a>
									<i class="fas fa-circle-arrow-down" aria-hidden="true"></i>
									<span>Entrada</span>
								</a>
							
								<ul class="nav nav-children">
									<li>
										<a href="<?= WWW ?>html/matPat/cadastro_entrada.php">
											<span>Registrar Entrada</span>
										</a>
									</li>
									<li>
										<a href="<?= WWW ?>html/matPat/listar_entrada.php">
											<span>Informações de Entradas</span>
										</a>
									</li>
								</ul>
							</li>

							<li class="nav-parent nav-active" >
								<a>
									<i class="fas fa-circle-arrow-up" aria-hidden="true"></i>
									<span>Saída</span>
								</a>
							
								<ul class="nav nav-children">
									<li>
										<a href="<?= WWW ?>html/matPat/cadastro_saida.php">
											<span>Registrar Saída</span>
										</a>
									</li>
									<li>
										<a href="<?= WWW ?>html/matPat/listar_saida.php">
											<span>Informações de Saídas</span>
										</a>
									</li>
								</ul>
							</li>

							<li class="nav-parent nav-active" >
								<a>
									<i class="fa fa-boxes" aria-hidden="true"></i>
									<span>Estoque</span>
								</a>
							
								<ul class="nav nav-children">
									<li>
										<a href="<?= WWW ?>html/matPat/relatorio.php">
											<i class="fa fa-clipboard"></i>
											<span>Gerar Relatório</span>
										</a>
									</li>
									<li class="nav-parent nav-active" >
										<a>
											<i class="fa fa-box" aria-hidden="true"></i>
											<span>Produtos</span>
										</a>
										<ul class="nav nav-children">
											<li>
												<a href="<?= WWW ?>html/matPat/cadastro_produto.php">
													<span>Cadastrar Produto</span>
												</a>
											</li>
											<li>
												<a href="<?= WWW ?>html/matPat/listar_produto.php">
													<span>Listar Produtos</span>
												</a>
											</li>
										</ul>
									</li>
									<li class="nav-parent nav-active" >
										<a>
											<i class="fa fa-warehouse" aria-hidden="true"></i>
											<span>Almoxarifados</span>
										</a>
										<ul class="nav nav-children">
											<li>
												<a href="<?= WWW ?>html/matPat/adicionar_almoxarifado.php">
													<span>Adicionar Almoxarifado</span>
												</a>
											</li>
											<li>
												<a href="<?= WWW ?>html/matPat/listar_almox.php">
													<span>Listar Almoxarifados</span>
												</a>
											</li>
										</ul>
									</li>
								</ul>
							</li>
						</li>
					</ul>

					</li>
					<li class="nav-parent nav-active" id="3">
						<a>
							<i class="fa fa-book" aria-hidden="true"></i>
							<span>Memorando</span>
						</a>
						<ul class="nav nav-children">
							<li id="31">
								<a href="<?= WWW ?>html/memorando/listar_memorandos_ativos.php">
									 Caixa de Entrada
									 
								</a>
							</li>

							<li id="31">
								<a href="<?= WWW ?>html/memorando/novo_memorandoo.php">
									 Criar Memorando
								</a>
							</li>

							<li id="32">
								<a href="<?= WWW ?>html/memorando/listar_memorandos_antigos.php">
									 Memorandos despachados
								</a>
							</li>
						</ul>
					</li>
					
					<li class="nav-parent nav-active" id="4">
						<a>
							<i class="fa fa-users" aria-hidden="true"></i>
							<span>Sócios</span>
						</a>
						<ul class="nav nav-children">
							<li id="31">
								<a href="<?= WWW ?>html/socio/">
									<i class="fa fa-users"></i>
									<span>Lista de Sócios</span>
								</a>
								<a href="<?= WWW ?>html/socio/sistema/relatorios_socios.php">
									<i class="fa fa-clipboard"></i>
									<span>Relatórios Sócios</span>
								</a>
							</li>
							<li class="nav-parent nav-active" id="32">
								<a>
									<i class="fa fa-money-bill" aria-hidden="true"></i>
									<span>Contribuições</span>
								</a>
							
								<ul class="nav nav-children">
									<li>
										<a href="<?= WWW ?>html/socio/sistema/psocio_geracao.php">
											<span>Gerar carnê/boleto para sócio</span>
										</a>
									</li>
									<li>
										<a href="<?= WWW ?>html/socio/sistema/contribuicoes.php">
											<span>Controle de Contribuições</span>
										</a>
									</li>
								</ul>
							</li>
							<li class="nav-parent nav-active" id="33">
								<a>
									<i class="far fa-plus-square" aria-hidden="true"></i>
									<span>Extra</span>
								</a>
								<ul class="nav nav-children">
									<li>
										<a href="<?= WWW ?>html/socio/sistema/tags.php">
											<span>Tags</span>
										</a>
									</li>
								</ul>
							</li>
						</ul>
					</li>

					<li class="nav-parent nav-active" id="5">
						<a>
							<i class="fa fa-hospital-user" aria-hidden="true"></i>
							<span>Saúde</span>
						</a>

						<ul class="nav nav-children">
							<li class="nav-parent nav-active">
								<a>
									<i class="fa fa-user" aria-hidden="true"></i>
									<span>Paciente</span>
								</a>
								<ul class="nav nav-children">
									<li>
										<a href="<?= WWW ?>html/saude/cadastro_ficha_medica.php">
											<span>Cadastrar Ficha Médica</span>
										</a>
									</li>
									<li>
										<a href="<?= WWW ?>html/saude/informacao_saude.php">
											<span>Informações Pacientes</span>
										</a>
									</li>
								</ul>
							</li>

							<li class="nav-parent nav-active" >
								<a>
									<i class="fa fa-user-md" aria-hidden="true"></i>
									<span>Registro Diário</span>
								</a>
								<ul class="nav nav-children">
									<li>
										<a href="<?= WWW ?>html/saude/administrar_medicamento.php">
											<span>Medicamentos</span>
										</a>
									</li>
									<li>
										<a href="<?= WWW ?>html/saude/listar_sinais_vitais.php">
											<span>Sinais Vitais</span>
										</a>
									</li>
									<li>
										<a href="<?= WWW ?>html/saude/listar_cadastro_intercorrencia.php">
											<span>Intercorrências</span>
										</a>
									</li>
								</ul>
							</li>
						</ul>
					</li>

					<!--contribuiçao-->
					<li class="nav-parent nav-active" id="7">
						<a>
							<i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i>
							<span>Contribuição</span>
						</a>
						<ul class="nav nav-children">
							<li >
								<a href="<?= WWW ?>html/contribuicao/view/gateway_pagamento.php">
									<i class="fa-solid fa-building"></i>
									Gateway de pagamento
								</a>
							</li>
							<li >
								<a href="<?= WWW ?>html/contribuicao/view/meio_pagamento.php">
									<i class="fa-regular fa-credit-card"></i>
									Meio de pagamento
								</a>
							</li>
							<li >
								<a href="<?= WWW ?>html/contribuicao/view/regra_pagamento.php">
									<i class="fa-solid fa-circle-exclamation"></i>
									Regras de pagamento
								</a>
							</li>
							<li >
								<a href="<?= WWW ?>html/contribuicao/view/captcha.php">
									<i class="fa-solid fa-user-check"></i>
									Captcha
								</a>
							</li>
						</ul>
					</li>
					<!--fim contribuiçao-->

					<li class="nav-parent nav-active visivel">
						<a>
							<i class="fa fa-cog" aria-hidden="true"></i>
							<span>Configurações</span>
						</a>
						<ul class="nav nav-children">
						<li >
								<a href="<?= WWW ?>html/personalizacao.php">
									Editar Conteúdo
								</a>
							</li>
							<li >
								<a href="<?= WWW ?>html/personalizacao_imagem.php">
									Lista de Imagens
								</a>
							</li>
							<li >
								<a href="<?= WWW ?>html/configuracao/configuracao_geral.php">
									Configurações Gerais
								</a>
							</li>
							<li >
								<a href="<?= WWW ?>html/configuracao/configuracao_email.php">
									Configurações de Email
								</a>
							</li>
							<li >
								<a href="<?= WWW ?>html/geral/editar_permissoes.php">
									Permissões
								</a>
							</li>
							<li >
								<a href="<?= WWW ?>html/geral/modulos_visiveis.php">
									Módulos visíveis
								</a>
							</li>
							<li >
								<a href="<?= WWW ?>html/geral/cargos.php">
									Cargos
								</a>
							</li>
							<li >
								<a href="<?= WWW ?>html/geral/documentos_funcionario.php">
									Documentos Funcionário
								</a>
							</li>
							<li >
								<a href="<?= WWW ?>html/configuracao/debug_info.php">
									Informações de debug
								</a>
							</li>
						</ul>
					</li>
				</ul>
			</nav>
			<p style="text-align: center;"><?= "Release instalada:<br> ".$_SESSION['local_release']?></p>
		</div>
	</div>
	<form id="listarFuncionario" method="POST" action="<?= WWW ?>controle/control.php">
		<input type="hidden" name="nomeClasse" value="FuncionarioControle">
		<input type="hidden" name="metodo" value="listartodos">
		<input type="hidden" name="nextPage" value="<?= WWW ?>html/informacao_funcionario.php">
	</form>
	
	<form id="listarAtendido" method="POST" action="<?= WWW ?>controle/control.php">
		<input type="hidden" name="nomeClasse" value="AtendidoControle">
		<input type="hidden" name="metodo" value="listartodos">
		<input type="hidden" name="nextPage" value="<?= WWW ?>html/Informacao_Atendido.php">
	</form>
		
	<!-- Theme Base, Components and Settings -->
	<script src="<?= WWW ?>assets/javascripts/theme.js"></script>
		
	<!-- Theme Custom -->
	<script src="<?= WWW ?>assets/javascripts/theme.custom.js"></script>
	
	<!-- Theme Initialization Files -->
	<script src="<?= WWW ?>assets/javascripts/theme.init.js"></script>
