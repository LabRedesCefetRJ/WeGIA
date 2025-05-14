// function testaCPF(strCPF) {
//     var strCPF = strCPF.replace(/[^\d]+/g, ''); // Limpa a string do CPF removendo espaços em branco e caracteres especiais.
//     var Soma;
//     var Resto;
//     Soma = 0;
//     if (strCPF == "00000000000") return false;
//     if (strCPF == "11111111111") return false;

//     for (i = 1; i <= 9; i++) Soma = Soma + parseInt(strCPF.substring(i - 1, i)) * (11 - i);
//     Resto = (Soma * 10) % 11;

//     if ((Resto == 10) || (Resto == 11)) Resto = 0;
//     if (Resto != parseInt(strCPF.substring(9, 10))) return false;

//     Soma = 0;
//     for (i = 1; i <= 10; i++) Soma = Soma + parseInt(strCPF.substring(i - 1, i)) * (12 - i);
//     Resto = (Soma * 10) % 11;

//     if ((Resto == 10) || (Resto == 11)) Resto = 0;
//     if (Resto != parseInt(strCPF.substring(10, 11))) return false;
//     return true;
// }

function testaCPF(cpf) {
    // Remove caracteres não numéricos
    cpf = cpf.replace(/[^\d]+/g, '');

    // Verifica se o CPF tem 11 dígitos
    if (cpf.length !== 11) return false;

    // Elimina CPFs inválidos conhecidos (como todos os dígitos iguais)
    if (/^(\d)\1{10}$/.test(cpf)) return false;

    // Valida primeiro dígito verificador
    let soma = 0;
    for (let i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i);
    }
    let resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.charAt(9))) return false;

    // Valida segundo dígito verificador
    soma = 0;
    for (let i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i);
    }
    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.charAt(10))) return false;

    return true;
}