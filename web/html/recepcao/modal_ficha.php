<?php
// Modal de ficha usado pelo histórico de visitas e pelo perfil do atendido (preenchido por js/ficha_recepcao.js).
?>
<!-- Ficha do visitante / visitado -->
<div class="modal fade" id="modalFicha" tabindex="-1" role="dialog" aria-labelledby="modalFichaTitulo" aria-hidden="true">
	<div class="modal-dialog modal-ficha" role="document">
		<div class="modal-content">
			<button type="button" class="close ficha-fechar" data-dismiss="modal" aria-label="Fechar">
				<span aria-hidden="true">&times;</span>
			</button>
			<h4 class="sr-only" id="modalFichaTitulo">Ficha</h4>
			<div id="modalFichaCorpo"></div>
			<div class="modal-footer ficha-rodape">
				<span class="ficha-aviso">
					<i class="fa fa-lock" aria-hidden="true"></i> Documentos exibidos parcialmente para proteger os dados pessoais.
				</span>
				<button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
			</div>
		</div>
	</div>
</div>
