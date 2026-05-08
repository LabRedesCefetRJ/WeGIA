//Verificação de existência de um sócio no formulário de cadastro
document.getElementById('cpf_cnpj').addEventListener('blur', async function () {
    const documento = this.value.trim();

    if (documento === "") return; // evita requisição vazia

    try {
        const url = "../../contribuicao/controller/control.php" +
            "?nomeClasse=SocioController&metodo=buscarPorDocumento" +
            "&documento=" + encodeURIComponent(documento);

        const response = await fetch(url, { method: "GET" });

        if (!response.ok) {
            console.error("Erro ao consultar sócio:", response.status);
            return;
        }

        const json = await response.json();

        if (json.resultado !== "Sócio não encontrado") {
            alert("⚠️ Este CPF/CNPJ já pertence a um sócio cadastrado!");
        }

    } catch (e) {
        console.error("Erro na requisição:", e);
    }
});
