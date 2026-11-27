function configurarAutopreenchimento(opcoes = {}) {
    var prefixo = opcoes.prefixo || '';
    function elemento(nome) {
        return document.getElementById(prefixo + nome);
    }

    var revisoes = {};
    var revisaoEndereco = 0;
    var camposEndereco = ['cep', 'numero', 'complemento', 'bairro', 'cidade', 'uf', 'logradouro'];
    ['nome', 'telefone', 'email'].concat(camposEndereco).forEach(function (campo) {
        revisoes[campo] = 0;
        elemento(campo).addEventListener('input', function () {
            revisoes[campo]++;
            if (campo === 'cep') revisaoEndereco++;
        });
    });

    configurarMascaras();
    elemento('uf').addEventListener('input', function () {
        this.value = this.value.toUpperCase();
    });
    configurarCnpj();

    function configurarMascaras() {
        var formatos = {
            cpf: {limite: 11, mascara: '###.###.###-##'},
            cnpj: {limite: 14, mascara: '##.###.###/####-##'},
            cep: {limite: 8, mascara: '#####-###'},
            telefone: {limite: 11}
        };
        Object.keys(formatos).forEach(function (nome) {
            var input = nome === 'cpf'
                ? document.getElementById(opcoes.campoCpf || prefixo + 'cpf')
                : elemento(nome);
            if (!input) return;
            var formato = formatos[nome];
            input.inputMode = nome === 'telefone' ? 'tel' : 'numeric';
            input.maxLength = nome === 'telefone' ? 15 : formato.mascara.length;
            function formatar() {
                var cursor = input.selectionStart;
                var digitosAntes = input.value.slice(0, cursor).replace(/[^0-9]/g, '').length;
                var numeros = input.value.replace(/[^0-9]/g, '').slice(0, formato.limite);
                var mascara = formato.mascara;
                if (nome === 'telefone') {
                    mascara = numeros.length > 10 ? '(##) #####-####' : '(##) ####-####';
                }
                var valor = '';
                var indice = 0;
                for (var i = 0; i < mascara.length && indice < numeros.length; i++) {
                    valor += mascara[i] === '#' ? numeros[indice++] : mascara[i];
                }
                if (input.value === valor) return;
                input.value = valor;
                if (document.activeElement === input && cursor !== null) {
                    var posicao = 0;
                    var encontrados = 0;
                    while (posicao < valor.length && encontrados < digitosAntes) {
                        if (/[0-9]/.test(valor[posicao])) encontrados++;
                        posicao++;
                    }
                    input.setSelectionRange(posicao, posicao);
                }
            }
            input.addEventListener('input', formatar);
            input.addEventListener('blur', formatar);
            formatar();
        });
    }

    function configurarCnpj() {
        var cnpj = elemento('cnpj');
        var status = document.getElementById(opcoes.statusCnpj || 'cnpj-status');
        var consulta = 0;
        var requisicao;
        var ultimoCnpj = '';
        var cnpjDosDados = '';
        var preenchidosAutomaticamente = {};
        var campos = {
            nome: 'razao_social', telefone: 'ddd_telefone_1', email: 'email',
            cep: 'cep', numero: 'numero', complemento: 'complemento',
            bairro: 'bairro', cidade: 'municipio', uf: 'uf', logradouro: 'logradouro'
        };

        function cancelarConsulta() {
            consulta++;
            if (requisicao) requisicao.abort();
            status.textContent = '';
        }

        function cnpjValido(codigo) {
            if (!/^[0-9]{14}$/.test(codigo) || /^([0-9])\1{13}$/.test(codigo)) return false;
            for (var tamanho = 12; tamanho <= 13; tamanho++) {
                var soma = 0;
                var peso = tamanho - 7;
                for (var i = 0; i < tamanho; i++) {
                    soma += Number(codigo[i]) * peso--;
                    if (peso < 2) peso = 9;
                }
                var resto = soma % 11;
                if (Number(codigo[tamanho]) !== (resto < 2 ? 0 : 11 - resto)) return false;
            }
            return true;
        }

        function limparDadosDoCnpjAnterior() {
            Object.keys(preenchidosAutomaticamente).forEach(function (campo) {
                var input = elemento(campo);
                var preenchimento = preenchidosAutomaticamente[campo];
                if (input.value === preenchimento.valor && revisoes[campo] === preenchimento.revisao) {
                    input.value = '';
                    input.dispatchEvent(new Event('input'));
                }
            });
            preenchidosAutomaticamente = {};
            cnpjDosDados = '';
        }

        cnpj.addEventListener('input', function () {
            var codigo = cnpj.value.replace(/[^0-9]/g, '');
            if (cnpjDosDados && codigo !== cnpjDosDados) limparDadosDoCnpjAnterior();
            ultimoCnpj = '';
            cancelarConsulta();
        });
        cnpj.addEventListener('blur', async function () {
            var codigo = cnpj.value.replace(/[^0-9]/g, '');
            if (!cnpjValido(codigo)) {
                status.textContent = cnpj.value.trim() ? 'Informe um CNPJ válido para consultar.' : '';
                return;
            }
            if (codigo === ultimoCnpj) return;
            cancelarConsulta();
            var atual = consulta;
            var controlador = new AbortController();
            requisicao = controlador;
            var limite = setTimeout(function () { controlador.abort(); }, 8000);
            var anteriores = {};
            var revisoesAnteriores = {};
            Object.keys(campos).forEach(function (campo) {
                anteriores[campo] = elemento(campo).value;
                revisoesAnteriores[campo] = revisoes[campo];
            });
            var enderecoNaConsulta = revisaoEndereco;
            status.textContent = 'Consultando CNPJ…';
            try {
                var resposta = await fetch('https://brasilapi.com.br/api/cnpj/v1/' + codigo, {signal: controlador.signal});
                if (!resposta.ok) {
                    if (atual === consulta) {
                        status.textContent = resposta.status === 404
                            ? 'CNPJ não encontrado. Preencha os dados manualmente.'
                            : 'Não foi possível consultar o CNPJ. Tente novamente ou preencha manualmente.';
                    }
                    return;
                }
                var dados = await resposta.json();
                if (atual !== consulta) return;
                var preenchidos = 0;
                cnpjDosDados = codigo;
                // Um CEP informado manualmente pelo usuário tem prioridade.
                var preencherEndereco = enderecoNaConsulta === revisaoEndereco
                    && (!anteriores.cep.trim()
                        || anteriores.cep.replace(/[^0-9]/g, '') === String(dados.cep || '').replace(/[^0-9]/g, ''));
                Object.keys(campos).forEach(function (campo) {
                    var input = elemento(campo);
                    var valor = dados[campos[campo]];
                    if (camposEndereco.indexOf(campo) !== -1 && !preencherEndereco) return;
                    // Preenche apenas campos vazios que não foram editados durante a consulta.
                    if (anteriores[campo].trim() || input.value.trim()
                        || revisoes[campo] !== revisoesAnteriores[campo]
                        || typeof valor !== 'string' || !valor.trim()) return;
                    if (campo === 'logradouro') {
                        valor = [dados.descricao_tipo_de_logradouro, valor].filter(Boolean).join(' ');
                    }
                    if (campo === 'cep') {
                        valor = valor.replace(/[^0-9]/g, '');
                        if (valor.length !== 8) return;
                        valor = valor.slice(0, 5) + '-' + valor.slice(5);
                    }
                    if (campo === 'telefone') {
                        var telefone = valor.replace(/[^0-9]/g, '');
                        if (telefone.length === 10 || telefone.length === 11) {
                            valor = '(' + telefone.slice(0, 2) + ')' + telefone.slice(2, -4) + '-' + telefone.slice(-4);
                        }
                    }
                    input.value = valor;
                    input.dispatchEvent(new Event('input'));
                    preenchidosAutomaticamente[campo] = {valor: input.value, revisao: revisoes[campo]};
                    preenchidos++;
                });
                ultimoCnpj = codigo;
                status.textContent = preenchidos
                    ? 'Dados preenchidos pelo CNPJ. Confira antes de salvar.'
                    : 'CNPJ consultado. Os dados existentes foram preservados.';
            } catch (erro) {
                if (atual === consulta) {
                    status.textContent = 'Não foi possível consultar o CNPJ. Tente novamente ou preencha manualmente.';
                }
            } finally {
                clearTimeout(limite);
            }
        });
        cnpj.form.addEventListener('reset', function () {
            preenchidosAutomaticamente = {};
            cnpjDosDados = '';
            ultimoCnpj = '';
            cancelarConsulta();
        });
        cnpj.form.addEventListener('submit', cancelarConsulta);

        if (opcoes.modal) {
            $(opcoes.modal).on('hide.bs.modal', function () {
                preenchidosAutomaticamente = {};
                cnpjDosDados = '';
                ultimoCnpj = '';
                cancelarConsulta();
            });
        }
    }
}

function exibirErroOrigem(formulario, campo, mensagem) {
    formulario.querySelectorAll('[data-erro-origem]').forEach(function (aviso) {
        var id = aviso.id;
        formulario.querySelectorAll('[aria-describedby]').forEach(function (input) {
            var referencias = input.getAttribute('aria-describedby').split(/\s+/).filter(function (referencia) {
                return referencia !== id;
            });
            if (referencias.length) input.setAttribute('aria-describedby', referencias.join(' '));
            else input.removeAttribute('aria-describedby');
        });
        aviso.remove();
    });
    formulario.querySelectorAll('[data-campo-erro-origem]').forEach(function (input) {
        input.removeAttribute('aria-invalid');
        input.removeAttribute('data-campo-erro-origem');
    });
    if (!campo || !mensagem) return;
    var inputs = Array.from(formulario.elements).filter(function (input) {
        return input.name === campo || input.name === campo + '[]';
    });
    if (!inputs.length) return;
    var primeiro = inputs[0];
    var aviso = document.createElement('p');
    aviso.id = (primeiro.id || 'origem-' + campo) + '-erro-servidor';
    aviso.className = 'text-danger';
    aviso.setAttribute('data-erro-origem', '');
    aviso.setAttribute('role', 'alert');
    aviso.textContent = mensagem;
    primeiro.parentNode.appendChild(aviso);
    inputs.forEach(function (input) {
        input.setAttribute('aria-invalid', 'true');
        input.setAttribute('data-campo-erro-origem', '');
        input.setAttribute('aria-describedby', ((input.getAttribute('aria-describedby') || '') + ' ' + aviso.id).trim());
        input.addEventListener('input', function limpar() {
            exibirErroOrigem(formulario, null, null);
        }, {once: true});
    });
    var secao = primeiro.closest('details');
    if (secao) secao.open = true;
    primeiro.focus();
}

// Comportamento da seção de pagamento compartilhada pelos formulários de origem.
(function () {
    'use strict';
    function configurar(secao) {
        var condicao = secao.querySelector('[name="condicao_pagamento"]');
        var vencimentos = secao.querySelector('[name="vencimentos_pagamento"]');
        function atualizar() {
            var parcelado = condicao.value === 'parcelado';
            var temPrazo = parcelado || condicao.value === 'a_prazo';
            secao.querySelector('[data-campo-vencimentos]').hidden = !temPrazo;
            vencimentos.disabled = !temPrazo;
            vencimentos.required = temPrazo;
            vencimentos.pattern = parcelado ? '[0-9]{1,4}([ ]*/[ ]*[0-9]{1,4})+' : '[0-9]{1,4}';
            vencimentos.placeholder = parcelado ? 'Ex.: 30/60/90' : 'Ex.: 30';
            secao.querySelector('[data-rotulo-vencimentos]').textContent = parcelado ? 'Vencimentos em dias' : 'Prazo em dias';
            secao.querySelector('[data-ajuda-vencimentos]').textContent = parcelado
                ? 'Separe os vencimentos por /. Ex.: 0/30/60 para entrada e duas parcelas.'
                : 'Dias após a data de referência combinada.';
            var resumo = condicao.value ? condicao.options[condicao.selectedIndex].text : '';
            if (temPrazo && vencimentos.value.trim()) resumo += ' · ' + vencimentos.value.trim() + ' dias';
            secao.querySelector('[data-resumo-pagamento]').textContent = resumo;
        }
        condicao.addEventListener('change', function () {
            vencimentos.value = '';
            atualizar();
        });
        vencimentos.addEventListener('input', atualizar);
        secao.addEventListener('invalid', function () { secao.open = true; }, true);
        secao.closest('form').addEventListener('reset', function () {
            setTimeout(function () { secao.open = false; atualizar(); }, 0);
        });
        secao.atualizarPagamento = atualizar;
        atualizar();
    }
    document.querySelectorAll('.condicoes-pagamento').forEach(configurar);
    window.preencherCondicoesPagamento = function (formulario, dados) {
        var secao = formulario.querySelector('.condicoes-pagamento');
        secao.querySelector('[name="condicao_pagamento"]').value = dados.condicao_pagamento || '';
        secao.querySelector('[name="vencimentos_pagamento"]').value = dados.vencimentos_pagamento || '';
        secao.open = false;
        secao.atualizarPagamento();
    };
}());
