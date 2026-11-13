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

	/**
	 * Monta um campo TinyMCE que submete o HTML pelo input oculto associado ao formulário pai.
	 * Opções: name, modelsUrl, bodyUrl, request (e, opcionalmente, label e placeholder).
	 */
	async function mount(container, options) {
		if (!(container instanceof HTMLElement)) {
			throw new TypeError('O componente TinyMCE precisa de um elemento HTML para montagem.');
		}
		if (!options || typeof options.name !== 'string' || options.name.trim() === '') {
			throw new TypeError('Informe o atributo name para o valor do campo TinyMCE.');
		}
		if (!options.modelsUrl || !options.bodyUrl || typeof options.request !== 'function') {
			throw new TypeError('Informe modelsUrl, bodyUrl e a função request para o componente TinyMCE.');
		}
		if (!global.tinymce || typeof global.tinymce.init !== 'function') {
			throw new Error('A biblioteca TinyMCE deve ser carregada antes do componente de edição.');
		}

		const componentId = `wegia-tinymce-${++nextEditorId}`;
		const root = criarElemento('div', 'wegia-tinymce-editor');
		const controls = criarElemento('div', 'wegia-tinymce-editor__controls');
		const select = criarElemento('select', 'form-control');
		select.setAttribute('aria-label', 'Modelo de documento para importar');
		const optionInicial = criarElemento('option', '', 'Carregando modelos...');
		optionInicial.value = '';
		optionInicial.selected = true;
		select.appendChild(optionInicial);

		const importButton = criarElemento('button', 'btn btn-default', 'Importar');
		importButton.type = 'button';
		importButton.disabled = true;
		const status = criarElemento('span', 'wegia-tinymce-editor__status');
		status.setAttribute('role', 'status');
		status.setAttribute('aria-live', 'polite');
		controls.append(select, importButton, status);

		const input = criarElemento('input');
		input.type = 'hidden';
		input.id = `${componentId}-input`;
		input.name = options.name;

		const editorElement = criarElemento('textarea');
		editorElement.id = options.id || `${componentId}-editor`;
		editorElement.setAttribute('aria-label', options.label || 'Campo de edição de texto');
		if (options.placeholder) {
			editorElement.setAttribute('placeholder', options.placeholder);
		}

		root.append(controls, input, editorElement);
		container.replaceChildren(root);

		let editorInstance = null;
		function sincronizarValor() {
			if (editorInstance) {
				input.value = editorInstance.getContent({ format: 'html' });
			}
		}

		const formulario = container.closest('form');
		if (formulario) {
			formulario.addEventListener('submit', sincronizarValor, true);
		}

		const editores = await global.tinymce.init({
			target: editorElement,
			license_key: 'gpl',
			menubar: false,
			statusbar: false,
			min_height: 220,
			plugins: 'image link lists table',
			toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist | outdent indent | link image table',
			extended_valid_elements: 'style[type|media]',
			valid_children: '+body[style]',
			paste_data_images: true,
			convert_urls: false,
			placeholder: options.placeholder || '',
			iframe_attrs: { title: options.label || 'Campo de edição de texto' },
			setup(editor) {
				editor.on('input change Undo Redo SetContent', sincronizarValor);
			}
		});
		editorInstance = editores[0] || null;
		if (!editorInstance) {
			throw new Error('TinyMCE não conseguiu inicializar o campo de edição.');
		}
		sincronizarValor();

		select.addEventListener('change', () => {
			importButton.disabled = select.value === '';
		});

		importButton.addEventListener('click', async () => {
			if (!select.value) {
				return;
			}

			importButton.disabled = true;
			select.disabled = true;
			status.className = 'wegia-tinymce-editor__status';
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

				editorInstance.setContent(modelo.body_html);
				sincronizarValor();
				status.textContent = `Modelo "${modelo.descricao}" importado.`;
				root.dispatchEvent(new CustomEvent('wegia-tinymce-import', {
					bubbles: true,
					detail: { modelo }
				}));
			} catch (error) {
				status.className = 'wegia-tinymce-editor__status text-danger';
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
			status.className = 'wegia-tinymce-editor__status';
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
				status.className = 'wegia-tinymce-editor__status text-danger';
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
			editor: editorInstance,
			input,
			select,
			importButton,
			refresh: carregarModelos,
			getContent: () => editorInstance.getContent({ format: 'html' })
		};
	}

	global.WegiaTinyMceEditor = Object.freeze({ mount });
})(window);
