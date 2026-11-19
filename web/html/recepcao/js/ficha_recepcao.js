/*
 * Modal de ficha (visitante ou visitado) compartilhado pelo módulo Recepção e pelo perfil do atendido.
 *
 * Espera na página o modal de html/recepcao/modal_ficha.php e os estilos .ficha-* de css/recepcao.css.
 * As fichas chegam sem escape do servidor (FichaRecepcao); todo texto passa por escaparHtml antes de entrar no DOM.
 *
 * Uso: FichaRecepcao.abrir(urlDoControle, { nomeClasse, metodo, ... }, "Ficha do visitante");
 */
(function ($) {
	"use strict";

	function escaparHtml(texto) {
		return $("<div>").text(texto == null ? "" : String(texto)).html();
	}

	function linkSeguro(link) {
		// Os links vêm do servidor (FichaRecepcao); aceita só os esquemas esperados.
		return typeof link === "string" && (link.indexOf("https://wa.me/") === 0 || link.indexOf("mailto:") === 0) ? link : null;
	}

	/* Ícone de cada seção da ficha, pelo começo do título. */
	const ICONES_SECAO = [
		["Documentos", "fa-id-card"],
		["Contato", "fa-phone"],
		["Endereço", "fa-location-dot"],
		["Dados do pet", "fa-paw"],
		["Dados", "fa-user"]
	];

	/* Campos que ocupam a linha inteira da grade (valores longos ou com botões). */
	const CAMPOS_LARGOS = ["Nome completo", "Telefone", "E-mail", "Logradouro"];

	function iconeSecao(titulo) {
		const par = ICONES_SECAO.find(function (item) { return titulo.indexOf(item[0]) === 0; });
		return par ? par[1] : "fa-circle-info";
	}

	function iniciais(nome) {
		const partes = String(nome || "").trim().split(/\s+/).filter(Boolean);
		if (!partes.length) {
			return "?";
		}
		const primeira = partes[0].charAt(0);
		const ultima = partes.length > 1 ? partes[partes.length - 1].charAt(0) : "";
		return (primeira + ultima).toUpperCase();
	}

	function avatarFicha(ficha) {
		if (ficha.foto) {
			return `<img src="${escaparHtml("data:image;base64," + ficha.foto)}" alt="Foto de ${escaparHtml(ficha.nome)}" class="ficha-avatar">`;
		}
		if (ficha.titulo === "Pet") {
			return '<div class="ficha-avatar ficha-avatar-vazio" aria-hidden="true"><i class="fa fa-paw"></i></div>';
		}
		return `<div class="ficha-avatar ficha-avatar-vazio" aria-hidden="true">${escaparHtml(iniciais(ficha.nome))}</div>`;
	}

	function botaoContato(link) {
		const whatsapp = link.indexOf("https://wa.me/") === 0;
		return `<a href="${escaparHtml(link)}" class="ficha-acao ${whatsapp ? "ficha-acao-whatsapp" : "ficha-acao-email"}" target="_blank" rel="noopener noreferrer">` +
			`<i class="${whatsapp ? "fa-brands fa-whatsapp" : "fa fa-envelope"}" aria-hidden="true"></i> ${whatsapp ? "WhatsApp" : "Enviar e-mail"}</a>`;
	}

	function renderizarFicha(ficha) {
		const secoes = ficha.secoes.map(function (secao) {
			const campos = secao.campos.map(function (campo) {
				const link = linkSeguro(campo.link);
				const valor = campo.valor == null
					? '<span class="ficha-vazio">Não informado</span>'
					: escaparHtml(campo.valor) + (link ? botaoContato(link) : "");
				const largo = CAMPOS_LARGOS.indexOf(campo.rotulo) !== -1 ? " ficha-campo-largo" : "";

				return `<div class="ficha-campo${largo}"><dt>${escaparHtml(campo.rotulo)}</dt><dd>${valor}</dd></div>`;
			}).join("");

			return `
				<section class="ficha-secao">
					<h5><i class="fa ${iconeSecao(secao.titulo)}" aria-hidden="true"></i> ${escaparHtml(secao.titulo)}</h5>
					<dl class="ficha-grade">${campos}</dl>
				</section>`;
		}).join("");

		return `
			<div class="ficha-cabecalho">
				${avatarFicha(ficha)}
				<div class="ficha-identificacao">
					<div class="ficha-nome">${escaparHtml(ficha.nome)}</div>
					<span class="ficha-tipo ficha-tipo-${escaparHtml(ficha.titulo.toLowerCase().normalize("NFD").replace(/[̀-ͯ]/g, ""))}">${escaparHtml(ficha.titulo)}</span>
				</div>
			</div>
			<div class="ficha-corpo">${secoes}</div>`;
	}

	function estadoFicha(icone, mensagem, classe) {
		return `<div class="ficha-estado ${classe || ""}"><i class="fa ${icone}" aria-hidden="true"></i><span>${mensagem}</span></div>`;
	}

	function abrir(url, parametros, tituloCarregando) {
		// O Bootstrap só exibe o modal como filho direto do body; numa página com marcação aninhada (como o
		// perfil do atendido) ele ficaria preso dentro de outro elemento oculto.
		const $modal = $("#modalFicha");
		if (!$modal.parent().is("body")) {
			$modal.appendTo("body");
		}

		$("#modalFichaTitulo").text(tituloCarregando || "Ficha");
		$("#modalFichaCorpo").html(estadoFicha("fa-spinner fa-spin", "Carregando ficha..."));
		$("#modalFicha .ficha-aviso").hide();
		$("#modalFicha").modal("show");

		$.ajax({
			url: url,
			method: "GET",
			dataType: "json",
			data: parametros
		}).done(function (ficha) {
			$("#modalFichaTitulo").text(`Ficha de ${ficha.nome} (${ficha.titulo.toLowerCase()})`);
			$("#modalFichaCorpo").html(renderizarFicha(ficha));
			// O aviso de mascaramento só faz sentido na ficha de pessoas, que tem documentos.
			$("#modalFicha .ficha-aviso").toggle(ficha.titulo !== "Pet");
		}).fail(function (xhr) {
			const erro = xhr.responseJSON && xhr.responseJSON.erro ? xhr.responseJSON.erro : "Não foi possível carregar a ficha.";
			$("#modalFichaCorpo").html(estadoFicha("fa-triangle-exclamation", escaparHtml(erro), "ficha-estado-erro"));
		});
	}

	window.FichaRecepcao = { abrir: abrir };
})(jQuery);
