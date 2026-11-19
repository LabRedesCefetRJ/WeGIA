/*
 * Registro de entrada em etapas (registro_entrada.php, issue #2157).
 *
 * Toda ação vai ao RegistroVisitaControle por fetch e recebe de volta o estado da entrada em andamento, que fica na
 * sessão do servidor. Assim a página nunca recarrega e o recepcionista pode pausar e retomar de onde parou.
 */
(function ($) {
	"use strict";

	const CONFIG = window.REGISTRO_ENTRADA;
	const TOTAL_ETAPAS = 3;
	const NOMES_ETAPA = { 1: "Visitantes", 2: "Visitados", 3: "Confirmação" };

	let estado = null;
	let visitadosDisponiveis = [];
	let selecionados = {};          // id_visitado -> {nome, identificador}
	let salvandoDescricao = null;
	let descricaoPendente = false;

	/* ---------- utilidades ---------- */

	function escaparHtml(texto) {
		return $("<div>").text(texto == null ? "" : String(texto)).html();
	}

	function horaAgora() {
		return new Date().toLocaleTimeString("pt-BR", { hour: "2-digit", minute: "2-digit" });
	}

	function formatarDataHora(valor) {
		if (!valor) {
			return "";
		}
		const [data, hora] = valor.split(" ");
		const [ano, mes, dia] = data.split("-");
		const hoje = new Date();
		const mesmoDia = Number(ano) === hoje.getFullYear() && Number(mes) === hoje.getMonth() + 1 && Number(dia) === hoje.getDate();
		return (mesmoDia ? "hoje" : `${dia}/${mes}`) + " às " + hora.slice(0, 5);
	}

	function formatarCpf(valor) {
		const d = valor.replace(/\D/g, "").slice(0, 11);
		return d.replace(/^(\d{3})(\d)/, "$1.$2").replace(/^(\d{3})\.(\d{3})(\d)/, "$1.$2.$3").replace(/\.(\d{3})(\d)/, ".$1-$2");
	}

	function cpfValido(valor) {
		const d = valor.replace(/\D/g, "");
		if (d.length !== 11 || /^(\d)\1{10}$/.test(d)) {
			return false;
		}
		for (let t = 9; t < 11; t++) {
			let soma = 0;
			for (let i = 0; i < t; i++) {
				soma += Number(d[i]) * (t + 1 - i);
			}
			if (((10 * soma) % 11) % 10 !== Number(d[t])) {
				return false;
			}
		}
		return true;
	}

	function foto(base64) {
		return base64 ? "data:image;base64," + base64 : CONFIG.fotoPadrao;
	}

	function mensagem(tipo, texto) {
		const icone = tipo === "danger" ? "fa-triangle-exclamation" : (tipo === "success" ? "fa-check" : "fa-info-circle");
		$("#mensagemEntrada").html(
			`<div class="alert alert-${tipo} alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
				<i class="fa ${icone}" aria-hidden="true"></i> ${escaparHtml(texto)}
			</div>`
		);
	}

	function limparMensagem() {
		$("#mensagemEntrada").empty();
	}

	function marcarSalvo() {
		$("#statusSalvo").html(`<i class="fa fa-cloud" aria-hidden="true"></i> Salvo às ${horaAgora()}`);
	}

	/* ---------- comunicação com o servidor ---------- */

	/**
	 * Chama um método do RegistroVisitaControle. POST leva o token CSRF. Uma resposta que não é JSON significa que a
	 * sessão expirou ou que o usuário perdeu a permissão (o backend redireciona para outra página nesses casos).
	 */
	async function api(metodo, dados, opcoes) {
		opcoes = opcoes || {};
		const classe = opcoes.classe || "RegistroVisitaControle";
		let resposta;

		if (opcoes.get) {
			const parametros = new URLSearchParams(Object.assign({ nomeClasse: classe, metodo: metodo }, dados || {}));
			resposta = await fetch(`${CONFIG.controle}?${parametros}`, { credentials: "same-origin", headers: { Accept: "application/json" } });
		} else {
			const corpo = dados instanceof FormData ? dados : new FormData();
			if (!(dados instanceof FormData)) {
				Object.entries(dados || {}).forEach(([chave, valor]) => corpo.append(chave, valor));
			}
			corpo.set("nomeClasse", classe);
			corpo.set("metodo", metodo);
			corpo.set("csrf_token", CONFIG.csrf);
			resposta = await fetch(CONFIG.controle, { method: "POST", body: corpo, credentials: "same-origin", headers: { Accept: "application/json" } });
		}

		const tipo = resposta.headers.get("Content-Type") || "";
		if (!tipo.includes("application/json")) {
			throw new Error("Sua sessão expirou ou você não tem permissão para esta ação. Recarregue a página.");
		}

		const json = await resposta.json();
		if (!resposta.ok) {
			throw new Error(json.erro || json.mensagem || "Não foi possível concluir a operação.");
		}
		return json;
	}

	/** Executa uma ação, travando o botão enquanto ela roda e mostrando o erro, se houver. */
	async function executar($botao, acao) {
		const textoOriginal = $botao && $botao.length ? $botao.html() : null;
		if ($botao && $botao.length) {
			$botao.prop("disabled", true).html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Aguarde...');
		}
		try {
			return await acao();
		} catch (erro) {
			mensagem("danger", erro.message);
			return null;
		} finally {
			if ($botao && $botao.length) {
				$botao.prop("disabled", false).html(textoOriginal);
			}
		}
	}

	function aplicarEstado(novo) {
		estado = novo;
		selecionados = {};
		novo.visitados.forEach(function (v) {
			selecionados[v.id_visitado] = { nome: v.nome, identificador: v.identificador };
		});
		renderizar();
	}

	/* ---------- renderização ---------- */

	function renderizar() {
		const etapa = estado.concluida ? "concluida" : estado.etapa;
		const temVisitantes = estado.visitantes.length > 0;

		$(".etapa-painel").each(function () {
			this.hidden = String($(this).data("painel")) !== String(etapa);
		});

		$(".etapas-entrada li").each(function () {
			const n = Number($(this).data("etapa"));
			const atual = !estado.concluida && n === estado.etapa;
			const concluida = estado.concluida || n < estado.etapa;
			$(this).toggleClass("atual", atual).toggleClass("concluida", concluida);
			$(this).find(".etapa-botao")
				.prop("disabled", estado.concluida || (n > 1 && !temVisitantes))
				.attr("aria-current", atual ? "step" : null);
			$(this).find(".etapa-numero").html(concluida && !atual ? '<i class="fa fa-check" aria-hidden="true"></i>' : n);
		});

		const progresso = estado.concluida ? 100 : ((estado.etapa - 1) / (TOTAL_ETAPAS - 1)) * 100;
		$("#barraProgresso").css("width", progresso + "%");
		const faltam = TOTAL_ETAPAS - estado.etapa;
		$("#textoEtapa").text(estado.concluida
			? "Entrada registrada"
			: `Etapa ${estado.etapa} de ${TOTAL_ETAPAS}: ${NOMES_ETAPA[estado.etapa]}` + (faltam > 0 ? ` · ${faltam === 1 ? "falta 1 etapa" : "faltam " + faltam + " etapas"}` : " · última etapa"));

		$("#avisoRetomada").prop("hidden", true);

		renderizarVisitantes();
		renderizarChips();
		renderizarResumo();
		if (estado.etapa === 2 && !estado.concluida) {
			renderizarListaVisitados();
		}
	}

	function renderizarVisitantes() {
		const $lista = $("#listaVisitantes").empty();
		$("#contadorVisitantes").text(estado.visitantes.length);
		$("#botaoParaVisitados").prop("disabled", estado.visitantes.length === 0);

		if (!estado.visitantes.length) {
			$lista.append('<li class="lista-vazia">Nenhum visitante ainda. Busque pelo CPF acima.</li>');
			return;
		}

		estado.visitantes.forEach(function (v) {
			$lista.append(
				`<li>
					<img src="${escaparHtml(foto(v.foto))}" alt="" class="lista-foto">
					<span class="lista-nome">${escaparHtml(v.nome)}<small>${escaparHtml(v.cpf || "CPF não informado")}</small></span>
					<button type="button" class="btn btn-default btn-sm" data-remover-visitante="${Number(v.id_visitante)}" aria-label="Remover ${escaparHtml(v.nome)}">
						<i class="fa fa-times" aria-hidden="true"></i> Remover
					</button>
				</li>`
			);
		});
	}

	function renderizarChips() {
		const ids = Object.keys(selecionados);
		const $chips = $("#chipsVisitados").empty();
		if (!ids.length) {
			$chips.append('<span class="chip-vazio">nenhum — a visita será registrada para a instituição</span>');
			return;
		}
		ids.forEach(function (id) {
			$chips.append(
				`<span class="chip-visitado">${escaparHtml(selecionados[id].nome)}
					<button type="button" data-desmarcar="${Number(id)}" aria-label="Remover ${escaparHtml(selecionados[id].nome)}">&times;</button>
				</span>`
			);
		});
	}

	function renderizarListaVisitados() {
		const termo = $("#pesquisaVisitado").val().trim().toLowerCase();
		const $lista = $("#listaVisitados").empty();
		const filtrados = visitadosDisponiveis.filter(function (v) {
			return !termo || (v.nome + " " + v.identificador).toLowerCase().includes(termo);
		});

		if (!filtrados.length) {
			$lista.append(`<li class="lista-vazia">${visitadosDisponiveis.length ? "Nenhum visitado encontrado para a pesquisa." : "Nenhum visitado disponível deste tipo."}</li>`);
			return;
		}

		filtrados.forEach(function (v) {
			const marcado = Object.prototype.hasOwnProperty.call(selecionados, v.id_visitado);
			$lista.append(
				`<li class="${marcado ? "marcado" : ""}">
					<label>
						<input type="checkbox" data-visitado="${Number(v.id_visitado)}" ${marcado ? "checked" : ""}>
						<img src="${escaparHtml(foto(v.imagem))}" alt="" class="lista-foto">
						<span class="lista-nome">${escaparHtml(v.nome)}<small>${escaparHtml(v.identificador)}</small></span>
					</label>
				</li>`
			);
		});
	}

	function renderizarResumo() {
		$("#resumoVisitantes").html(estado.visitantes.map((v) => escaparHtml(v.nome)).join("<br>") || "—");
		const ids = Object.keys(selecionados);
		$("#resumoVisitados").html(ids.length
			? ids.map((id) => `${escaparHtml(selecionados[id].nome)} <small class="text-muted">${escaparHtml(selecionados[id].identificador)}</small>`).join("<br>")
			: '<span class="text-muted">Instituição (nenhum visitado escolhido)</span>');
		$("#resumoDescricao").html(estado.descricao ? escaparHtml(estado.descricao) : '<span class="text-muted">Sem descrição</span>');

		const registros = estado.visitantes.length * Math.max(ids.length, 1);
		$("#resumoRegistros").text(`Serão registradas ${registros} ${registros === 1 ? "visita" : "visitas"} (uma para cada visitante e visitado), com entrada às ${horaAgora()}.`);

		if (document.activeElement !== $("#descricaoVisita")[0]) {
			$("#descricaoVisita").val(estado.descricao);
			$("#contadorDescricao").text(estado.descricao.length);
		}
	}

	/* ---------- navegação ---------- */

	async function irPara(etapa) {
		limparMensagem();
		await salvarDescricaoAgora();
		const novo = await executar(null, () => api("salvar", { etapa: etapa }));
		if (novo) {
			aplicarEstado(novo);
			marcarSalvo();
			if (etapa === 2) {
				carregarVisitados();
			}
			window.scrollTo({ top: $(".etapas-entrada").offset().top - 80, behavior: "smooth" });
		}
	}

	/* ---------- etapa 1: visitantes ---------- */

	function limparBusca() {
		$("#resultadoCpf").empty();
		$("#formCadastroVisitante").prop("hidden", true)[0].reset();
		$("#previaFoto").attr("src", CONFIG.fotoPadrao);
		$("#cpfBusca").val("").trigger("focus");
	}

	async function buscarCpf() {
		limparMensagem();
		// Uma nova busca sempre fecha o resultado e o cadastro da busca anterior.
		$("#resultadoCpf").empty();
		$("#formCadastroVisitante").prop("hidden", true);
		const cpf = $("#cpfBusca").val();
		if (!cpfValido(cpf)) {
			mensagem("danger", "O CPF informado não é válido. Confira os números.");
			$("#cpfBusca").trigger("focus");
			return;
		}

		$("#formCadastroVisitante").prop("hidden", true);
		const resultado = await executar($("#botaoBuscarCpf"), () => api("buscarCpf", { cpf: cpf }, { get: true }));
		if (!resultado) {
			return;
		}

		if (resultado.situacao === "visitante") {
			const v = resultado.visitante;
			$("#resultadoCpf").html(
				`<div class="cartao-visitante">
					<img src="${escaparHtml(foto(v.foto))}" alt="" class="lista-foto">
					<span class="lista-nome">${escaparHtml(v.nome)}<small>Visitante cadastrado · ${escaparHtml(v.cpf || "")}</small></span>
					${resultado.na_entrada
						? '<span class="label label-default">Já está nesta entrada</span>'
						: `<button type="button" class="btn btn-primary" data-adicionar-visitante="${Number(v.id_visitante)}"><i class="fa fa-plus" aria-hidden="true"></i> Adicionar</button>`}
				</div>`
			);
			$("#resultadoCpf [data-adicionar-visitante]").trigger("focus");
			return;
		}

		$("#resultadoCpf").empty();
		abrirCadastro(resultado);
	}

	/**
	 * Abre o cadastro. Para uma pessoa que já existe no sistema, os dados pessoais vêm preenchidos e travados, e os
	 * campos de endereço já cadastrados ficam bloqueados (o servidor não informa os valores, só que já existem).
	 */
	function abrirCadastro(resultado) {
		const $form = $("#formCadastroVisitante");
		$form[0].reset();
		$form.find("input, select").prop("readonly", false).prop("disabled", false).removeAttr("placeholder");
		$("#cadNumero").attr("placeholder", "Sem número");
		$("#cadCep").attr("placeholder", "00000-000");
		$("#previaFoto").attr("src", CONFIG.fotoPadrao);
		$("#blocoFoto").prop("hidden", false);
		$("#cadCpf").val(resultado.cpf);

		if (resultado.situacao === "pessoa") {
			const p = resultado.pessoa;
			$("#tituloCadastro").text("Completar cadastro de visitante");
			$("#explicacaoCadastro").text(`O CPF ${resultado.cpf} já pertence a uma pessoa cadastrada no sistema. Confira o nome e complete o que faltar; o que já está cadastrado não será alterado.`);
			$("#cadNome").val(p.nome || "").prop("readonly", !!p.nome);
			$("#cadSobrenome").val(p.sobrenome || "").prop("readonly", !!p.sobrenome);
			if (p.sexo) {
				$form.find(`input[name=gender][value="${p.sexo}"]`).prop("checked", true);
				$form.find("input[name=gender]").not(":checked").prop("disabled", true);
			}
			$("#cadNascimento").val(p.nascimento || "").prop("readonly", !!p.nascimento);
			const campos = { cep: "#cadCep", uf: "#cadUf", cidade: "#cadCidade", bairro: "#cadBairro", rua: "#cadRua", numero_residencia: "#cadNumero", complemento: "#cadComplemento", ibge: "#cadIbge" };
			Object.entries(campos).forEach(function ([campo, seletor]) {
				if (p.endereco_preenchido && p.endereco_preenchido[campo]) {
					$(seletor).prop("disabled", true).val("").attr("placeholder", "Já cadastrado");
				}
			});
			$("#blocoFoto").prop("hidden", !!p.tem_foto);
		} else {
			$("#tituloCadastro").text("Cadastrar novo visitante");
			$("#explicacaoCadastro").text(`O CPF ${resultado.cpf} ainda não está cadastrado. Preencha os dados para cadastrar e adicionar à entrada.`);
		}

		$form.prop("hidden", false);
		const $primeiro = $form.find("input:not([readonly]):not([disabled]):not([type=hidden]):not([type=file])").first();
		$primeiro.trigger("focus");
	}

	async function cadastrarVisitante(evento) {
		evento.preventDefault();
		limparMensagem();
		const form = $("#formCadastroVisitante")[0];

		const faltando = [];
		if (!$("#cadNome").val().trim()) faltando.push("nome");
		if (!$("#cadSobrenome").val().trim()) faltando.push("sobrenome");
		if (!$(form).find("input[name=gender]:checked").length) faltando.push("sexo");
		if (!$("#cadNascimento").val()) faltando.push("data de nascimento");
		if (faltando.length) {
			mensagem("danger", "Preencha: " + faltando.join(", ") + ".");
			return;
		}

		// Campos desabilitados não entram no FormData; o sexo travado precisa ir mesmo assim.
		const dados = new FormData(form);
		const sexo = $(form).find("input[name=gender]:checked").val();
		dados.set("gender", sexo);

		const novo = await executar($("#botaoCadastrar"), () => api("cadastrarVisitante", dados));
		if (novo) {
			aplicarEstado(novo);
			marcarSalvo();
			const nome = novo.visitantes[novo.visitantes.length - 1].nome;
			limparBusca();
			mensagem("success", `${nome} foi cadastrado(a) e adicionado(a) à entrada.`);
		}
	}

	async function pesquisarCep() {
		const cep = $("#cadCep").val().replace(/\D/g, "");
		if (cep.length !== 8 || $("#cadCep").prop("disabled")) {
			return;
		}
		try {
			const resposta = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
			const dados = await resposta.json();
			if (dados.erro) {
				mensagem("warning", "CEP não encontrado. Preencha o endereço manualmente.");
				return;
			}
			const preencher = { "#cadRua": dados.logradouro, "#cadBairro": dados.bairro, "#cadCidade": dados.localidade, "#cadUf": dados.uf, "#cadIbge": dados.ibge };
			Object.entries(preencher).forEach(function ([seletor, valor]) {
				if (!$(seletor).prop("disabled") && !$(seletor).val()) {
					$(seletor).val(valor || "");
				}
			});
			$("#cadNumero").trigger("focus");
		} catch (e) {
			mensagem("warning", "Não foi possível consultar o CEP agora. Preencha o endereço manualmente.");
		}
	}

	/* ---------- etapa 2: visitados ---------- */

	async function carregarVisitados() {
		const tipo = $("#tipoVisitado").val();
		$("#linkCadastrarVisitado").attr("href", "cadastro_visitado.php?origem=entrada" + (tipo ? "&tipo=" + encodeURIComponent(tipo) : ""));
		$("#listaVisitados").html('<li class="lista-vazia"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Carregando...</li>');
		$("#alertaVisitados").empty();

		try {
			const dados = await api("listarTodos", { tipo: tipo }, { get: true, classe: "VisitadoControle" });
			visitadosDisponiveis = dados.map(function (v) {
				return {
					id_visitado: Number(v.id_visitado),
					nome: [v.nome, v.sobrenome].filter(Boolean).join(" "),
					identificador: v.identificador || "",
					imagem: v.imagem || null
				};
			});
		} catch (erro) {
			visitadosDisponiveis = [];
			$("#alertaVisitados").html(`<div class="alert alert-danger">${escaparHtml(erro.message)}</div>`);
		}
		renderizarListaVisitados();
	}

	async function salvarVisitados() {
		const ids = Object.keys(selecionados).join(",");
		const novo = await executar(null, () => api("salvar", { visitados: ids }));
		if (novo) {
			aplicarEstado(novo);
			marcarSalvo();
		} else {
			// Volta ao que o servidor tem salvo.
			aplicarEstado(estado);
		}
	}

	function alternarVisitado(id, marcar) {
		const v = visitadosDisponiveis.find((item) => item.id_visitado === id);
		if (marcar && v) {
			selecionados[id] = { nome: v.nome, identificador: v.identificador };
		} else {
			delete selecionados[id];
		}
		renderizarChips();
		renderizarListaVisitados();
		salvarVisitados();
	}

	/* A descrição é salva sozinha um pouco depois que o recepcionista para de digitar. */
	function agendarDescricao() {
		descricaoPendente = true;
		$("#contadorDescricao").text($("#descricaoVisita").val().length);
		$("#statusSalvo").html('<i class="fa fa-pencil" aria-hidden="true"></i> Editando...');
		clearTimeout(salvandoDescricao);
		salvandoDescricao = setTimeout(salvarDescricaoAgora, 700);
	}

	async function salvarDescricaoAgora() {
		clearTimeout(salvandoDescricao);
		if (!descricaoPendente) {
			return;
		}
		descricaoPendente = false;
		try {
			const novo = await api("salvar", { descricao: $("#descricaoVisita").val() });
			estado.descricao = novo.descricao;
			renderizarResumo();
			marcarSalvo();
		} catch (erro) {
			mensagem("danger", erro.message);
		}
	}

	/* ---------- etapa 3 e descarte ---------- */

	async function registrar() {
		limparMensagem();
		await salvarDescricaoAgora();
		const visitantes = estado.visitantes.map((v) => v.nome);
		const visitados = Object.keys(selecionados).map((id) => selecionados[id].nome);

		const resultado = await executar($("#botaoRegistrar"), () => api("registrar"));
		if (!resultado) {
			return;
		}

		$("#textoConcluida").text(
			`${visitantes.join(", ")} → ${visitados.length ? visitados.join(", ") : "instituição"}, às ${horaAgora()}.`
		);
		visitadosDisponiveis = [];
		aplicarEstado(Object.assign(resultado.estado, { concluida: true }));
		$("#statusSalvo").empty();
	}

	async function descartar() {
		const novo = await executar($("#confirmarDescarte"), () => api("descartar"));
		$("#modalDescartar").modal("hide");
		if (novo) {
			limparBusca();
			aplicarEstado(novo);
			$("#statusSalvo").empty();
			mensagem("info", "A entrada foi descartada. Você pode começar outra.");
		}
	}

	/* ---------- início ---------- */

	$(async function () {
		const $doc = $(document);

		$doc.on("click", "[data-ir-etapa]", function () {
			irPara(Number($(this).data("ir-etapa")));
		});
		$doc.on("click", "[data-acao=descartar]", function () {
			$("#modalDescartar").modal("show");
		});
		$("#confirmarDescarte").on("click", descartar);
		$doc.on("click", "[data-acao=nova]", function () {
			aplicarEstado(Object.assign(estado, { concluida: false }));
			limparMensagem();
			limparBusca();
		});

		$("#cpfBusca").on("input", function () {
			this.value = formatarCpf(this.value);
		});
		$("#formBuscaCpf").on("submit", function (e) {
			e.preventDefault();
			buscarCpf();
		});
		$doc.on("click", "[data-adicionar-visitante]", async function () {
			const $botao = $(this);
			const novo = await executar($botao, () => api("adicionarVisitante", { id_visitante: $botao.data("adicionar-visitante") }));
			if (novo) {
				aplicarEstado(novo);
				marcarSalvo();
				const nome = novo.visitantes[novo.visitantes.length - 1].nome;
				limparBusca();
				mensagem("success", `${nome} foi adicionado(a) à entrada. Busque outro CPF ou siga para os visitados.`);
			}
		});
		$doc.on("click", "[data-remover-visitante]", async function () {
			const $botao = $(this);
			const novo = await executar($botao, () => api("removerVisitante", { id_visitante: $botao.data("remover-visitante") }));
			if (novo) {
				aplicarEstado(novo);
				marcarSalvo();
			}
		});
		$doc.on("click", "[data-acao=cancelar-cadastro]", limparBusca);
		$("#formCadastroVisitante").on("submit", cadastrarVisitante);
		$("#cadCep").on("input", function () {
			const d = this.value.replace(/\D/g, "").slice(0, 8);
			this.value = d.length > 5 ? d.slice(0, 5) + "-" + d.slice(5) : d;
		}).on("blur", pesquisarCep);
		$("#cadNumero").on("input", function () {
			this.value = this.value.replace(/\D/g, "");
		});
		$("#fotoVisitante").on("change", function () {
			const arquivo = this.files && this.files[0];
			if (!arquivo) {
				return;
			}
			const leitor = new FileReader();
			leitor.onload = (e) => $("#previaFoto").attr("src", e.target.result);
			leitor.readAsDataURL(arquivo);
		});

		$("#tipoVisitado").on("change", carregarVisitados);
		$("#pesquisaVisitado").on("input", renderizarListaVisitados);
		$doc.on("change", "#listaVisitados input[type=checkbox]", function () {
			alternarVisitado(Number($(this).data("visitado")), this.checked);
		});
		$doc.on("click", "[data-desmarcar]", function () {
			alternarVisitado(Number($(this).data("desmarcar")), false);
		});
		$("#descricaoVisita").on("input", agendarDescricao).on("blur", salvarDescricaoAgora);
		$("#botaoRegistrar").on("click", registrar);

		// Se a página for fechada com a descrição ainda não salva, envia mesmo assim.
		window.addEventListener("pagehide", function () {
			if (!descricaoPendente) {
				return;
			}
			const dados = new FormData();
			dados.set("nomeClasse", "RegistroVisitaControle");
			dados.set("metodo", "salvar");
			dados.set("csrf_token", CONFIG.csrf);
			dados.set("descricao", $("#descricaoVisita").val());
			navigator.sendBeacon(CONFIG.controle, dados);
		});

		try {
			const inicial = await api("estado", null, { get: true });
			aplicarEstado(inicial);
			if (inicial.em_andamento) {
				$("#textoRetomada").text(`Entrada em andamento desde ${formatarDataHora(inicial.iniciado_em)}. Você está de volta à etapa em que parou.`);
				$("#avisoRetomada").prop("hidden", false);
			}
			if (inicial.etapa === 2) {
				carregarVisitados();
			}
		} catch (erro) {
			estado = { etapa: 1, visitantes: [], visitados: [], descricao: "" };
			renderizar();
			mensagem("danger", erro.message);
		}

		// Links antigos (pré-registro, cadastro de visitante) chegam com ?cpf= e já fazem a busca.
		const cpfInicial = new URLSearchParams(location.search).get("cpf");
		if (cpfInicial && estado.etapa === 1) {
			$("#cpfBusca").val(formatarCpf(cpfInicial));
			buscarCpf();
		} else if (estado.etapa === 1) {
			$("#cpfBusca").trigger("focus");
		}
	});
})(jQuery);
