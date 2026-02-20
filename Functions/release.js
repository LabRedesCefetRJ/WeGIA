async function fetchRelease(url) {
    const response = await fetch(url);

    if (!response.ok) {
        let message = "Erro desconhecido no servidor";

        try {
            const errorBody = await response.json();
            message = errorBody.erro ?? errorBody.message ?? message;
        } catch {
            // backend não retornou JSON
        }

        throw new Error(
            JSON.stringify({
                status: response.status,
                message
            })
        );
    }

    const text = await response.text();
    const value = parseInt(text, 10);

    if (isNaN(value)) {
        throw new Error(
            JSON.stringify({
                status: 500,
                message: `Resposta inválida da API: ${text}`
            })
        );
    }

    return value;
}

function showAlertMessage({
    message,
    type = "warning",      // warning | danger | success | info
    icon = "⚠️",
    id = "generic-alert"
}) {
    const container = document.getElementById("message-container");
    if (!container) return;

    // Evita duplicar o alerta
    if (document.getElementById(id)) return;

    const alert = document.createElement("div");
    alert.id = id;
    alert.className = `alert alert-${type} alert-dismissible`;
    alert.setAttribute("role", "alert");

    alert.innerHTML = `
        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
            <span aria-hidden="true">&times;</span>
        </button>
        <strong>${icon} ${message}</strong>
    `;

    container.appendChild(alert);
}

function getUserFriendlyError(error) {
    if (!(error instanceof Error)) {
        return "Erro inesperado ao comunicar com o servidor.";
    }

    try {
        const { status, message } = JSON.parse(error.message);

        if (status && message) {
            return `Erro ${status}: ${message}`;
        }
    } catch (e) {
        // se não for JSON, cai no fallback
    }

    return "Erro ao processar a requisição.";
}

async function getReleaseInstall() {
    return fetchRelease(
        "../controle/control.php?nomeClasse=ReleaseControle&metodo=getReleaseInstall"
    );
}

async function getReleaseAvaible() {
    return fetchRelease(
        "../controle/control.php?nomeClasse=ReleaseControle&metodo=getReleaseAvaible"
    );
}

function newReleaseMessage() {
    showAlertMessage({
        id: "new-release-alert",
        type: "warning",
        icon: "⚠️",
        message: "O sistema possui atualizações disponíveis!"
    });
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

        //antes de continuar com a lógica precisa verificar se a resposta da promise foi ok

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
        console.error("Erro ao verificar release:", error.message);

        showAlertMessage({
            id: "release-error-alert",
            type: "danger",
            icon: "❌",
            message: getUserFriendlyError(error)
        });
    }
}

main();