function configurarOlhoSenha(botaoId, campoId) {
    $(botaoId).on("click", function() {
        var campo = $(campoId);
        var isHidden = campo.attr("type") === "password";

        campo.attr("type", isHidden ? "text" : "password");
        $(this).attr("aria-pressed", isHidden ? "true" : "false");
        $(this).find("i").toggleClass("fa-eye fa-eye-slash");
    });
}