(function (global) {
	'use strict';

	let nextEditorId = 0;

	function criarElemento(nome, classes, texto) {
		const elemento = document.createElement(nome);
		if (classes) {
			elemento.className = classes;
		}
		if (texto !== undefined) {
			elemento.textContent = texto;
		}
		return elemento;
	}

	async function jsonDaResposta(response) {
		let resultado;
		try {
			resultado = await response.json();
		} catch {
			throw new Error(`A API retornou uma resposta inválida (${response.status}).`);
		}

		if (!response.ok) {
			const mensagem = resultado && typeof resultado === 'object'
				? resultado.error || resultado.message
				: null;
			throw new Error(mensagem || `Erro na requisição (${response.status}).`);
		}

		return resultado;
	}

	function substituirConteudo(editor, html) {
		const instancia = editor.editor;
		const comprimento = instancia.getDocument().getLength();
		instancia.setSelectedRange([0, comprimento]);
		instancia.deleteInDirection('forward');
		instancia.setSelectedRange([0, 0]);
		instancia.insertHTML(html);
	}

	/**
	 * Monta um campo Trix que submete o HTML pelo input oculto associado ao formulário pai.
	 * Opções: name, modelsUrl, bodyUrl, request (e, opcionalmente, label e placeholder).
	 */
	async function mount(container, options) {
		if (!(container instanceof HTMLElement)) {
			throw new TypeError('O componente Trix precisa de um elemento HTML para montagem.');
		}
		if (!options || typeof options.name !== 'string' || options.name.trim() === '') {
			throw new TypeError('Informe o atributo name para o valor do campo Trix.');
		}
		if (!options.modelsUrl || !options.bodyUrl || typeof options.request !== 'function') {
			throw new TypeError('Informe modelsUrl, bodyUrl e a função request para o componente Trix.');
		}
		if (!global.Trix) {
			throw new Error('A biblioteca Trix deve ser carregada antes do componente de edição.');
		}
		await global.customElements.whenDefined('trix-editor');

		const componentId = `wegia-trix-${++nextEditorId}`;
		const root = criarElemento('div', 'wegia-trix-editor');
		const controls = criarElemento('div', 'wegia-trix-editor__controls');
		const select = criarElemento('select', 'form-control');
		select.setAttribute('aria-label', 'Modelo de documento para importar');
		const optionInicial = criarElemento('option', '', 'Carregando modelos...');
		optionInicial.value = '';
		optionInicial.selected = true;
		select.appendChild(optionInicial);

		const importButton = criarElemento('button', 'btn btn-default', 'Importar');
		importButton.type = 'button';
		importButton.disabled = true;
		const status = criarElemento('span', 'wegia-trix-editor__status');
		status.setAttribute('role', 'status');
		status.setAttribute('aria-live', 'polite');
		controls.append(select, importButton, status);

		const input = criarElemento('input');
		input.type = 'hidden';
		input.id = `${componentId}-input`;
		input.name = options.name;

		const editor = criarElemento('trix-editor', 'trix-content');
		editor.id = options.id || `${componentId}-editor`;
		editor.setAttribute('input', input.id);
		editor.setAttribute('aria-label', options.label || 'Campo de edição de texto');
		editor.addEventListener('trix-initialize', () => {
			const toolbar = editor.previousElementSibling;
			if (toolbar && toolbar.matches('trix-toolbar')) {
				toolbar.appendChild(controls);
			}
		}, { once: true });
		if (options.placeholder) {
			editor.setAttribute('placeholder', options.placeholder);
		}

		root.append(controls, input, editor);
		container.replaceChildren(root);

		select.addEventListener('change', () => {
			importButton.disabled = select.value === '';
		});

		importButton.addEventListener('click', async () => {
			if (!select.value) {
				return;
			}

			importButton.disabled = true;
			select.disabled = true;
			status.className = 'wegia-trix-editor__status';
			status.textContent = 'Importando modelo...';

			try {
				const response = await options.request(() => fetch(
					`${options.bodyUrl.replace(/\/$/, '')}/${encodeURIComponent(select.value)}/body`,
					{
						method: 'GET',
						credentials: 'include',
						headers: { 'X-Client-Type': 'web' }
					}
				));
				const modelo = await jsonDaResposta(response);
				if (typeof modelo.body_html !== 'string') {
					throw new Error('A API não retornou o conteúdo HTML do modelo.');
				}

				substituirConteudo(editor, modelo.body_html);
				status.textContent = `Modelo "${modelo.descricao}" importado.`;
				root.dispatchEvent(new CustomEvent('wegia-trix-import', {
					bubbles: true,
					detail: { modelo }
				}));
			} catch (error) {
				status.className = 'wegia-trix-editor__status text-danger';
				status.textContent = error.message || 'Não foi possível importar o modelo.';
			} finally {
				select.disabled = false;
				importButton.disabled = select.value === '';
			}
		});

		async function carregarModelos() {
			const valorSelecionado = select.value;
			select.disabled = true;
			importButton.disabled = true;
			status.className = 'wegia-trix-editor__status';
			status.textContent = 'Carregando modelos...';

			try {
				const response = await options.request(() => fetch(options.modelsUrl, {
					method: 'GET',
					credentials: 'include',
					headers: { 'X-Client-Type': 'web' }
				}));
				const modelos = await jsonDaResposta(response);
				if (!Array.isArray(modelos)) {
					throw new Error('A API retornou uma lista de modelos inválida.');
				}

				select.replaceChildren();
				const placeholder = criarElemento('option', '', modelos.length > 0 ? 'Selecione um modelo...' : 'Nenhum modelo disponível');
				placeholder.value = '';
				placeholder.disabled = true;
				select.appendChild(placeholder);

				modelos.forEach((modelo) => {
					const option = criarElemento('option');
					option.value = String(modelo.id);
					option.textContent = `${modelo.descricao || 'Modelo sem descrição'} (${Number(modelo.global) === 1 ? 'Global' : 'Pessoal'})`;
					select.appendChild(option);
				});

				select.value = modelos.some((modelo) => String(modelo.id) === valorSelecionado)
					? valorSelecionado
					: '';
				select.disabled = modelos.length === 0;
				importButton.disabled = select.value === '';
				status.textContent = modelos.length === 0 ? 'Não há modelos com conteúdo disponível para importar.' : '';
			} catch (error) {
				select.replaceChildren();
				const optionErro = criarElemento('option', '', 'Não foi possível carregar os modelos');
				optionErro.value = '';
				optionErro.selected = true;
				select.appendChild(optionErro);
				select.disabled = true;
				status.className = 'wegia-trix-editor__status text-danger';
				status.textContent = error.message || 'Não foi possível carregar os modelos.';
				throw error;
			}
		}

		try {
			await carregarModelos();
		} catch (error) {
			// A mensagem é exibida no componente; mantê-lo montado permite tentar novamente.
		}

		return {
			editor,
			input,
			select,
			importButton,
			refresh: carregarModelos
		};
	}

	global.WegiaTrixEditor = Object.freeze({ mount });
})(window);
