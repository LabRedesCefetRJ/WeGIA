function mascaraCPF(input) {
    let value = input.value.replace(/\D/g, '');
    value = value.slice(0, 11);

    if (value.length > 9) {
        value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4');
    } else if (value.length > 6) {
        value = value.replace(/^(\d{3})(\d{3})(\d{0,3})/, '$1.$2.$3');
    } else if (value.length > 3) {
        value = value.replace(/^(\d{3})(\d{0,3})/, '$1.$2');
    }

    input.value = value;
}

function mascaraCNPJ(input) {
    let value = input.value.replace(/\D/g, '');
    value = value.slice(0, 14);

    if (value.length > 12) {
        value = value.replace(
            /^(\d{2})(\d{3})(\d{3})(\d{4})(\d{0,2})/,
            '$1.$2.$3/$4-$5'
        );
    } else if (value.length > 8) {
        value = value.replace(
            /^(\d{2})(\d{3})(\d{3})(\d{0,4})/,
            '$1.$2.$3/$4'
        );
    } else if (value.length > 5) {
        value = value.replace(
            /^(\d{2})(\d{3})(\d{0,3})/,
            '$1.$2.$3'
        );
    } else if (value.length > 2) {
        value = value.replace(
            /^(\d{2})(\d{0,3})/,
            '$1.$2'
        );
    }

    input.value = value;
}
