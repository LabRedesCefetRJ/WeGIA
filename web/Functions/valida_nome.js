function validarNome(nome) {
    if (!nome) return false;

    nome = nome.trim();

    var contemLetra = /[A-Za-zÀ-ÿ]/.test(nome);
    var formatoValido = /^[A-Za-zÀ-ÿ\u0300-\u036f]+(?:[ .'\-][A-Za-zÀ-ÿ\u0300-\u036f]+)*$/.test(nome);

    return contemLetra && formatoValido;
}

function sanitizarNome(nome) {
    return nome.replace(/[^A-Za-zÀ-ÿ\u0300-\u036f .'\-]/g, '');
}

function mensagemNomeInvalido(campo) {
    var nomeCampo = campo && campo.dataset && campo.dataset.nomeCampo ? campo.dataset.nomeCampo : 'nome';
    return 'O campo ' + nomeCampo + ' deve conter letras.';
}

function obterGrupoCampo(campo) {
    var elemento = campo.parentElement;

    while (elemento && elemento !== document.body) {
        if (elemento.classList && elemento.classList.contains('form-group')) {
            return elemento;
        }
        elemento = elemento.parentElement;
    }

    return null;
}

function obterElementoErro(campo) {
    var idErro = campo.dataset.nomeErroId;
    var erro = idErro ? document.getElementById(idErro) : null;

    if (erro) {
        return erro;
    }

    erro = document.createElement('small');
    erro.className = 'help-block text-danger js-validacao-nome-erro';
    erro.style.display = 'none';
    erro.style.fontSize = '12px';
    erro.style.marginTop = '5px';

    idErro = (campo.id || campo.name || 'nome') + '_erro_validacao_nome_' + Math.random().toString(36).slice(2, 8);
    erro.id = idErro;
    campo.dataset.nomeErroId = idErro;
    campo.setAttribute('aria-describedby', idErro);

    campo.insertAdjacentElement('afterend', erro);

    return erro;
}

function exibirErroNome(campo) {
    var erro = obterElementoErro(campo);
    var grupo = obterGrupoCampo(campo);

    erro.textContent = mensagemNomeInvalido(campo);
    erro.style.display = 'block';

    if (grupo) {
        grupo.classList.add('has-error');
    }
}

function limparErroNome(campo) {
    var idErro = campo.dataset.nomeErroId;
    var erro = idErro ? document.getElementById(idErro) : null;
    var grupo = obterGrupoCampo(campo);

    if (erro) {
        erro.textContent = '';
        erro.style.display = 'none';
    }

    if (grupo) {
        grupo.classList.remove('has-error');
    }
}

function aplicarValidacaoNome() {
    var seletores = [
        'input[name="nome"]',
        'input[id="nomeForm"]',
        'input[name="sobrenome"]',
        'input[id="sobrenomeForm"]',
        'input[name="sobrenomeForm"]',
        'input[name="nome_pai"]',
        'input[id="pai"]',
        'input[name="nome_mae"]',
        'input[id="mae"]',
        'input[name="nomePai"]',
        'input[name="nomeMae"]'
    ];

    var seletor = seletores.join(',');
    var campos = document.querySelectorAll(seletor);

    campos.forEach(function(campo) {
        if (campo.dataset.validacaoNomeAplicada === 'true') {
            return;
        }

        campo.dataset.validacaoNomeAplicada = 'true';
        campo.dataset.validacaoNomeCompondo = 'false';

        if (!campo.dataset.nomeCampo) {
            campo.dataset.nomeCampo = campo.name === 'sobrenome' || campo.name === 'sobrenomeForm'
                ? 'sobrenome'
                : campo.name === 'nome_pai' || campo.name === 'nomePai'
                    ? 'nome do pai'
                    : campo.name === 'nome_mae' || campo.name === 'nomeMae'
                        ? 'nome da mãe'
                        : 'nome';
        }

        campo.addEventListener('compositionstart', function() {
            campo.dataset.validacaoNomeCompondo = 'true';
        });

        campo.addEventListener('compositionend', function() {
            campo.dataset.validacaoNomeCompondo = 'false';
            var valorSanitizado = sanitizarNome(campo.value);
            if (campo.value !== valorSanitizado) {
                campo.value = valorSanitizado;
            }

            if (!campo.value.trim() || validarNome(campo.value)) {
                limparErroNome(campo);
            }
        });

        campo.addEventListener('input', function() {
            if (campo.dataset.validacaoNomeCompondo === 'true') {
                return;
            }

            var valorSanitizado = sanitizarNome(campo.value);
            if (campo.value !== valorSanitizado) {
                campo.value = valorSanitizado;
            }

            if (!campo.value.trim() || validarNome(campo.value)) {
                limparErroNome(campo);
            }
        });

        campo.addEventListener('blur', function() {
            if (campo.value.trim() && !validarNome(campo.value)) {
                exibirErroNome(campo);
            } else {
                limparErroNome(campo);
            }
        });
    });

    document.querySelectorAll('form').forEach(function(form) {
        if (form.dataset.validacaoNomeSubmitAplicada === 'true') {
            return;
        }

        form.dataset.validacaoNomeSubmitAplicada = 'true';
        form.addEventListener('submit', function(event) {
            var camposDoForm = form.querySelectorAll(seletor);

            for (var i = 0; i < camposDoForm.length; i++) {
                var campo = camposDoForm[i];
                var valor = campo.value.trim();

                if (campo.disabled || campo.type === 'hidden' || !valor) {
                    limparErroNome(campo);
                    continue;
                }

                if (!validarNome(valor)) {
                    exibirErroNome(campo);
                    campo.focus();
                    event.preventDefault();
                    event.stopPropagation();
                    return false;
                }

                limparErroNome(campo);
            }
        }, true);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', aplicarValidacaoNome);
} else {
    aplicarValidacaoNome();
}
