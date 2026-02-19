async function getReleaseInstall() {
    const response = await fetch("../controle/control.php?nomeClasse=ReleaseControle&metodo=getReleaseInstall");
    const text = await response.text();
    return parseInt(text, 10);
}

async function getReleaseAvaible() {
    const response = await fetch("../controle/control.php?nomeClasse=ReleaseControle&metodo=getReleaseAvaible"); //fazer um fetch para o proxy do backend
    const text = await response.text();
    return parseInt(text, 10);
}

function newReleaseMessage() {
    var container = document.getElementById("message-container");
    if (!container) return;

    // Evita duplicar o alerta
    if (document.getElementById("new-release-alert")) return;

    var alert = document.createElement("div");
    alert.id = "new-release-alert";
    alert.className = "alert alert-warning alert-dismissible";
    alert.setAttribute("role", "alert");

    alert.innerHTML =
        '<button type="button" class="close" data-dismiss="alert" aria-label="Fechar">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' +
        '<strong>⚠️ O sistema possui atualizações disponíveis!</strong> ';

    container.appendChild(alert);
}

function formatReleaseDate(timestamp) {
    const date = new Date(timestamp * 1000);

    return new Intl.DateTimeFormat('pt-BR', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
}


async function main() {
    const STORAGE_KEY = "release_check_timestamp";
    const ONE_HOUR = 60 * 60 * 1000;
    const now = Date.now();

    // 1. Verificar se já checou na última hora
    const lastCheck = sessionStorage.getItem(STORAGE_KEY);

    if (lastCheck && (now - parseInt(lastCheck, 10)) < ONE_HOUR) {
        console.log("Verificação de release já feita na última hora.");
        return;
    }

    try {
        // 2. Buscar releases
        const [installed, available] = await Promise.all([
            getReleaseInstall(),
            getReleaseAvaible()
        ]);

        let message = `Release instalada: \n${formatReleaseDate(installed)}`;

        // 3. Comparar
        if (installed < available) {
            message += ' (Desatualizado)';
            newReleaseMessage();
        }

        // 5. Marcar verificação em sessão
        sessionStorage.setItem(STORAGE_KEY, now.toString());
        localStorage.setItem('RELEASE_MESSAGE', message);

    } catch (error) {
        console.error("Erro ao verificar release:", error);
    }
}

main();