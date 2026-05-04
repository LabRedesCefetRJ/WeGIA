const SaudeValidator = (function () {

    const REGEX_NOME = /^[A-Za-zÀ-ÿ\s\-'.]+$/;


    function validarNome(valor) {
        if (!valor || valor.trim() === '') {
            return { valido: false, mensagem: 'Este campo não pode estar vazio.' };
        }
        if (/\d/.test(valor)) {
            return { valido: false, mensagem: 'Este campo não deve conter números.' };
        }
        if (!REGEX_NOME.test(valor.trim())) {
            return { valido: false, mensagem: 'Este campo contém caracteres inválidos.' };
        }
        return { valido: true };
    }

    function validarValorPositivo(valor, nomeCampo) {
        if (!valor || valor.trim() === '') {
            return { valido: false, mensagem: 'O campo "' + nomeCampo + '" não pode estar vazio.' };
        }
        if (/^-/.test(valor.trim())) {
            return { valido: false, mensagem: 'O campo "' + nomeCampo + '" não pode ser negativo.' };
        }
        return { valido: true };
    }

    return { validarNome, validarValorPositivo };
})();
