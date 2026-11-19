/*
 * Histórico de visitas do módulo Recepção (historico_visitas.php).
 *
 * Os dados chegam sem escape do servidor (VisitaControle::listarHistorico e as fichas de
 * VisitanteControle/VisitadoControle::detalhar); todo texto passa por escaparHtml antes de entrar no DOM.
 */
(function ($) {
	"use strict";

	const CONFIG = window.HISTORICO_VISITAS;

	const ROTULOS_CATEGORIA = {
		atendido: "Atendido",
		funcionario: "Funcionário",
		voluntario: "Voluntário",
		pessoa: "Pessoa",
		pet: "Pet",
		setor: "Setor",
		instituicao: "Instituição"
	};

	// Setor e instituição não têm ficha.
	const CATEGORIAS_COM_FICHA = ["atendido", "funcionario", "voluntario", "pessoa", "pet"];

	let tabela = null;

	function escaparHtml(texto) {
		return $("<div>").text(texto == null ? "" : String(texto)).html();
	}

	function nomeCompleto(item) {
		return [item.nome, item.sobrenome].filter(Boolean).join(" ");
	}

	/* ---------- datas ---------- */

	function paraData(dataHora) {
		// "2026-11-19 13:09:05" -> Date local
		const [data, hora = "00:00:00"] = String(dataHora).split(" ");
		const [ano, mes, dia] = data.split("-").map(Number);
		const [h, m, s] = hora.split(":").map(Number);
		return new Date(ano, mes - 1, dia, h, m, s || 0);
	}

	function doisDigitos(n) {
		return String(n).padStart(2, "0");
	}

	function formatarData(dataHora) {
		const d = paraData(dataHora);
		return `${doisDigitos(d.getDate())}/${doisDigitos(d.getMonth() + 1)}/${d.getFullYear()}`;
	}

	function formatarHora(dataHora) {
		const d = paraData(dataHora);
		return `${doisDigitos(d.getHours())}:${doisDigitos(d.getMinutes())}`;
	}

	function isoDia(data) {
		return `${data.getFullYear()}-${doisDigitos(data.getMonth() + 1)}-${doisDigitos(data.getDate())}`;
	}

	/* ---------- filtros ---------- */

	function lerFiltrosDoFormulario() {
		const filtros = {};
		$("#formFiltros").serializeArray().forEach(function (campo) {
			if (campo.value !== "") {
				filtros[campo.name] = campo.value;
			}
		});
		return filtros;
	}

	function validarFiltros(filtros) {
		if (filtros.data_inicio && filtros.data_fim && filtros.data_inicio > filtros.data_fim) {
			return "A data inicial não pode ser posterior à data final.";
		}
		return null;
	}

	function mostrarAlerta(tipo, mensagem) {
		$("#alertaHistorico").html(
			`<div class="alert alert-${tipo}" role="alert">${escaparHtml(mensagem)}</div>`
		);
	}

	function definirPeriodo(periodo) {
		const hoje = new Date();
		let inicio = "";
		let fim = isoDia(hoje);

		if (periodo === "hoje") {
			inicio = fim;
		} else if (periodo === "7" || periodo === "30") {
			const d = new Date(hoje);
			d.setDate(d.getDate() - (Number(periodo) - 1));
			inicio = isoDia(d);
		} else if (periodo === "mes") {
			inicio = isoDia(new Date(hoje.getFullYear(), hoje.getMonth(), 1));
		} else if (periodo === "todos") {
			fim = "";
		}

		$("#filtroDataInicio").val(inicio);
		$("#filtroDataFim").val(fim);
	}

	/* ---------- tabela ---------- */

	function linkPessoa(item, tipo) {
		const nome = escaparHtml(nomeCompleto(item));

		if (tipo === "visitante") {
			return `<a href="#" class="link-ficha" data-tipo="visitante" data-id="${Number(item.id_visitante)}">${nome}</a>`;
		}

		if (CATEGORIAS_COM_FICHA.indexOf(item.categoria) === -1) {
			return `<span>${nome}</span>`;
		}

		return `<a href="#" class="link-ficha" data-tipo="visitado" data-id="${Number(item.id_visitado)}">${nome}</a>`;
	}

	// Mostra o primeiro nome; havendo mais, reticências e a contagem (a lista completa fica na linha expandida).
	function resumoNomes(itens, tipo) {
		if (!itens.length) {
			return "";
		}

		let html = linkPessoa(itens[0], tipo);

		if (itens.length > 1) {
			const restantes = itens.length - 1;
			html += `<span class="mais-itens" title="Clique na linha para ver todos">… <span class="badge">+${restantes}</span></span>`;
		}

		return html;
	}

	function listaCompleta(itens, tipo) {
		return itens.map(function (item) {
			const categoria = tipo === "visitado" && ROTULOS_CATEGORIA[item.categoria]
				? ` <span class="label label-default">${escaparHtml(ROTULOS_CATEGORIA[item.categoria])}</span>`
				: "";
			const identificador = item.identificador && item.identificador !== "—"
				? `<div class="text-muted small">${escaparHtml(item.identificador)}</div>`
				: "";

			return `<li>${linkPessoa(item, tipo)}${categoria}${identificador}</li>`;
		}).join("");
	}

	// Visita aberta desde um dia anterior: provavelmente a saída não foi registrada.
	function textoEmAndamento(grupo) {
		const dias = Number(grupo.dias_em_andamento) || 0;
		return dias === 0 ? "Em andamento" : `Em andamento há ${dias} ${dias === 1 ? "dia" : "dias"}`;
	}

	function rotuloEmAndamento(grupo) {
		const atrasada = (Number(grupo.dias_em_andamento) || 0) > 0;
		return `<span class="label ${atrasada ? "label-danger" : "label-warning"}"` +
			(atrasada ? ' title="Entrada de um dia anterior sem saída registrada"' : "") +
			`>${textoEmAndamento(grupo)}</span>`;
	}

	function detalhesDaLinha(grupo) {
		const descricao = grupo.descricao && grupo.descricao.trim() !== ""
			? escaparHtml(grupo.descricao)
			: '<span class="text-muted">Sem descrição</span>';

		let saida = `<span class="text-danger"><strong>${textoEmAndamento(grupo)}</strong></span>` +
			((Number(grupo.dias_em_andamento) || 0) > 0 ? ' <span class="text-muted">(a saída pode não ter sido registrada)</span>' : "");
		if (!grupo.em_andamento && grupo.horario_saida) {
			saida = `${formatarData(grupo.horario_saida)} às ${formatarHora(grupo.horario_saida)}`;
		}

		return `
			<div class="detalhes-entrada">
				<div class="row">
					<div class="col-sm-6">
						<h5><i class="fa fa-users" aria-hidden="true"></i> Visitantes (${grupo.visitantes.length})</h5>
						<ul class="lista-detalhes">${listaCompleta(grupo.visitantes, "visitante")}</ul>
					</div>
					<div class="col-sm-6">
						<h5><i class="fa fa-user-check" aria-hidden="true"></i> Visitados (${grupo.visitados.length})</h5>
						<ul class="lista-detalhes">${listaCompleta(grupo.visitados, "visitado")}</ul>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6"><strong>Entrada:</strong> ${formatarData(grupo.horario_entrada)} às ${formatarHora(grupo.horario_entrada)}</div>
					<div class="col-sm-6"><strong>Saída:</strong> ${saida}</div>
				</div>
				<div><strong>Descrição:</strong> ${descricao}</div>
			</div>`;
	}

	function textoBusca(itens) {
		return itens.map(nomeCompleto).join(" ");
	}

	function iniciarTabela() {
		tabela = $("#tabelaHistorico").DataTable({
			order: [[1, "desc"]],
			columns: [
				{
					data: null,
					orderable: false,
					searchable: false,
					className: "coluna-expandir",
					render: function () {
						return '<button type="button" class="btn btn-link btn-xs botao-expandir" aria-expanded="false" title="Ver detalhes da entrada">' +
							'<i class="fa fa-chevron-right" aria-hidden="true"></i><span class="sr-only">Ver detalhes da entrada</span></button>';
					}
				},
				{
					data: "horario_entrada",
					render: function (valor, tipo) {
						return tipo === "display" ? formatarData(valor) : valor;
					}
				},
				{
					data: "horario_entrada",
					render: function (valor, tipo) {
						return tipo === "display" ? formatarHora(valor) : valor;
					}
				},
				{
					data: null,
					render: function (grupo, tipo) {
						if (grupo.em_andamento) {
							return tipo === "display" ? rotuloEmAndamento(grupo) : "9999";
						}
						if (tipo !== "display") {
							return grupo.horario_saida;
						}
						const mesmoDia = formatarData(grupo.horario_saida) === formatarData(grupo.horario_entrada);
						return mesmoDia ? formatarHora(grupo.horario_saida) : `${formatarData(grupo.horario_saida)} ${formatarHora(grupo.horario_saida)}`;
					}
				},
				{
					data: "visitantes",
					render: function (itens, tipo) {
						return tipo === "display" ? resumoNomes(itens, "visitante") : textoBusca(itens);
					}
				},
				{
					data: "visitados",
					render: function (itens, tipo) {
						return tipo === "display" ? resumoNomes(itens, "visitado") : textoBusca(itens);
					}
				}
			]
		});

		$("#tabelaHistorico tbody").on("click", "tr", function (evento) {
			if ($(evento.target).closest(".link-ficha").length || $(this).hasClass("linha-detalhes")) {
				return;
			}

			const $linha = $(this);
			const linha = tabela.row($linha);
			if (!linha.data()) {
				return;
			}

			const $botao = $linha.find(".botao-expandir");

			if (linha.child.isShown()) {
				linha.child.hide();
				$linha.removeClass("linha-aberta");
				$botao.attr("aria-expanded", "false");
			} else {
				linha.child(detalhesDaLinha(linha.data()), "linha-detalhes").show();
				$linha.addClass("linha-aberta");
				$botao.attr("aria-expanded", "true");
			}
		});
	}

	function carregar(filtros) {
		$("#alertaHistorico").empty();
		$("#botaoFiltrar").prop("disabled", true);

		$.ajax({
			url: CONFIG.controle,
			method: "GET",
			dataType: "json",
			data: $.extend({ nomeClasse: "VisitaControle", metodo: "listarHistorico" }, filtros)
		}).done(function (grupos) {
			tabela.clear().rows.add(grupos).draw();
			if (!grupos.length) {
				mostrarAlerta("info", "Nenhuma visita encontrada com os filtros selecionados.");
			}
		}).fail(function (xhr) {
			tabela.clear().draw();
			const erro = xhr.responseJSON && xhr.responseJSON.erro ? xhr.responseJSON.erro : "Não foi possível carregar o histórico de visitas.";
			mostrarAlerta("danger", erro);
		}).always(function () {
			$("#botaoFiltrar").prop("disabled", false);
		});
	}

	function aplicarFiltros() {
		const filtros = lerFiltrosDoFormulario();
		const erro = validarFiltros(filtros);

		if (erro) {
			mostrarAlerta("warning", erro);
			return null;
		}

		carregar(filtros);
		return filtros;
	}

	/* ---------- ficha ---------- */

	function linkSeguro(link) {
		// Os links vêm do servidor (FichaRecepcao); aceita só os esquemas esperados.
		return typeof link === "string" && (link.indexOf("https://wa.me/") === 0 || link.indexOf("mailto:") === 0) ? link : null;
	}

	function renderizarFicha(ficha) {
		const foto = ficha.foto ? "data:image;base64," + ficha.foto : CONFIG.fotoPadrao;

		const secoes = ficha.secoes.map(function (secao) {
			const campos = secao.campos.map(function (campo) {
				let valor = campo.valor == null
					? '<span class="text-muted">Não informado</span>'
					: escaparHtml(campo.valor);

				const link = linkSeguro(campo.link);
				if (campo.valor != null && link) {
					const whatsapp = link.indexOf("https://wa.me/") === 0;
					const icone = whatsapp ? "fa-brands fa-whatsapp" : "fa fa-envelope";
					const rotulo = whatsapp ? "Abrir no WhatsApp" : "Enviar e-mail";
					valor += ` <a href="${escaparHtml(link)}" class="btn btn-default btn-xs" target="_blank" rel="noopener noreferrer">` +
						`<i class="${icone}" aria-hidden="true"></i> ${rotulo}</a>`;
				}

				return `<dt>${escaparHtml(campo.rotulo)}</dt><dd>${valor}</dd>`;
			}).join("");

			return `<h5 class="ficha-secao">${escaparHtml(secao.titulo)}</h5><dl class="dl-horizontal ficha-campos">${campos}</dl>`;
		}).join("");

		return `
			<div class="ficha-cabecalho">
				<img src="${escaparHtml(foto)}" alt="Foto de ${escaparHtml(ficha.nome)}" class="ficha-foto">
				<div>
					<div class="ficha-nome">${escaparHtml(ficha.nome)}</div>
					<span class="label label-primary">${escaparHtml(ficha.titulo)}</span>
				</div>
			</div>
			${secoes}`;
	}

	function abrirFicha(tipo, id) {
		const parametros = tipo === "visitante"
			? { nomeClasse: "VisitanteControle", metodo: "detalhar", id_visitante: id }
			: { nomeClasse: "VisitadoControle", metodo: "detalhar", id_visitado: id };

		$("#modalFichaTitulo").text(tipo === "visitante" ? "Ficha do visitante" : "Ficha do visitado");
		$("#modalFichaCorpo").html('<p class="text-center text-muted"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Carregando...</p>');
		$("#modalFicha").modal("show");

		$.ajax({
			url: CONFIG.controle,
			method: "GET",
			dataType: "json",
			data: parametros
		}).done(function (ficha) {
			$("#modalFichaTitulo").text("Ficha: " + ficha.titulo.toLowerCase());
			$("#modalFichaCorpo").html(renderizarFicha(ficha));
		}).fail(function (xhr) {
			const erro = xhr.responseJSON && xhr.responseJSON.erro ? xhr.responseJSON.erro : "Não foi possível carregar a ficha.";
			$("#modalFichaCorpo").html(`<div class="alert alert-danger" role="alert">${escaparHtml(erro)}</div>`);
		});
	}

	/* ---------- relatório ---------- */

	function baixarRelatorio() {
		const filtros = aplicarFiltros();
		if (!filtros) {
			return;
		}

		const $botao = $("#botaoBaixarRelatorio");
		const textoOriginal = $botao.html();
		$botao.prop("disabled", true).html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i><span>Gerando...</span>');

		fetch(CONFIG.relatorio + "?" + $.param(filtros), { credentials: "same-origin" })
			.then(function (resposta) {
				const tipo = resposta.headers.get("Content-Type") || "";
				if (!resposta.ok || tipo.indexOf("application/pdf") === -1) {
					return resposta.json().catch(function () { return {}; }).then(function (corpo) {
						throw new Error(corpo.erro || "Não foi possível gerar o relatório.");
					});
				}

				const disposicao = resposta.headers.get("Content-Disposition") || "";
				const nome = (disposicao.match(/filename="?([^";]+)"?/) || [])[1] || "relatorio_visitas.pdf";

				return resposta.blob().then(function (arquivo) {
					const url = URL.createObjectURL(arquivo);
					const $link = $("<a>").attr({ href: url, download: nome }).appendTo("body");
					$link[0].click();
					$link.remove();
					setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
				});
			})
			.catch(function (erro) {
				mostrarAlerta("danger", erro.message);
			})
			.finally(function () {
				$botao.prop("disabled", false).html(textoOriginal);
			});
	}

	/* ---------- inicialização ---------- */

	$(function () {
		iniciarTabela();

		if ($.fn.select2) {
			$("#filtroVisitado").select2({ width: "100%" });
		}

		$("#formFiltros").on("submit", function (evento) {
			evento.preventDefault();
			aplicarFiltros();
		});

		$("[data-periodo]").on("click", function () {
			definirPeriodo(String($(this).data("periodo")));
			aplicarFiltros();
		});

		$("#botaoLimparFiltros").on("click", function () {
			definirPeriodo("30");
			$("#filtroVisitado").val("").trigger("change");
			$("#filtroCategoria, #filtroSituacao").val("");
			aplicarFiltros();
		});

		$("#tabelaHistorico").on("click", ".link-ficha", function (evento) {
			evento.preventDefault();
			evento.stopPropagation();
			abrirFicha($(this).data("tipo"), Number($(this).data("id")));
		});

		$("#botaoBaixarRelatorio").on("click", baixarRelatorio);

		$("#corpoHistorico").on("hidden.bs.collapse shown.bs.collapse", function (evento) {
			const aberto = evento.type === "shown";
			$(".box-recepcao-recolher").attr({ "aria-expanded": aberto, title: aberto ? "Recolher" : "Expandir" })
				.find("i").toggleClass("fa-minus", aberto).toggleClass("fa-plus", !aberto);
		});

		aplicarFiltros();
	});
})(jQuery);
