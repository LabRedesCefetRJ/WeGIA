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
    icon = null,           // nome do ícone Font Awesome (ex: "triangle-exclamation")
    id = "generic-alert"
}) {
    const container = document.getElementById("message-container");
    if (!container) return;

    // Evita duplicar o alerta
    if (document.getElementById(id)) return;

    // Mapa de ícones padrão por tipo de alerta
    const defaultIcons = {
        warning: "fa-triangle-exclamation",
        danger: "fa-circle-exclamation",
        success: "fa-circle-check",
        info: "fa-circle-info"
    };

    // Usa o ícone fornecido ou usa o padrão para o tipo
    const iconName = icon || defaultIcons[type] || defaultIcons.warning;

    const alert = document.createElement("div");
    alert.id = id;
    alert.className = `alert alert-${type} alert-dismissible`;
    alert.setAttribute("role", "alert");
    alert.style.display = "flex";
    alert.style.alignItems = "center";
    alert.style.justifyContent = "space-between";
    alert.style.flexWrap = "nowrap";
    alert.style.padding = "0.55rem 1rem";
    alert.style.lineHeight = "1.1";

    // Cria o elemento ícone com Font Awesome
    const iconElement = document.createElement("i");
    iconElement.className = `fa-solid ${iconName}`;
    if (iconName.includes("spinner")) {
        iconElement.classList.add("fa-spin");
    }
    iconElement.style.setProperty("font-size", "1.25rem", "important");
    iconElement.style.setProperty("margin-top", "0", "important");
    iconElement.style.setProperty("margin-right", "0.55rem", "important");
    iconElement.style.lineHeight = "1";

    // Monta o conteúdo do alerta
    const contentWrapper = document.createElement("div");
    contentWrapper.style.display = "flex";
    contentWrapper.style.alignItems = "center";
    contentWrapper.style.justifyContent = "center";
    contentWrapper.style.flex = "1";
    contentWrapper.style.minWidth = "0";

    const contentSpan = document.createElement("strong");
    contentSpan.style.fontSize = "1.25rem";
    contentSpan.style.fontWeight = "600";
    contentSpan.style.display = "inline-flex";
    contentSpan.style.alignItems = "center";
    contentSpan.style.justifyContent = "center";
    contentSpan.style.wordBreak = "break-word";
    contentSpan.appendChild(iconElement);
    contentSpan.appendChild(document.createTextNode(message));

    contentWrapper.appendChild(contentSpan);

    const closeButton = document.createElement("button");
    closeButton.type = "button";
    closeButton.className = "close";
    closeButton.setAttribute("data-dismiss", "alert");
    closeButton.setAttribute("aria-label", "Fechar");
    closeButton.style.position = "relative";
    closeButton.style.right = "0";
    closeButton.style.padding = "0.3rem 0.5rem";
    closeButton.style.marginLeft = "0.75rem";
    closeButton.style.alignSelf = "center";
    closeButton.style.flexShrink = "0";
    closeButton.innerHTML = '<span aria-hidden="true">&times;</span>';

    alert.appendChild(contentWrapper);
    alert.appendChild(closeButton);

    container.appendChild(alert);
}

function removeAlert(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
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
        icon: "fa-triangle-exclamation",
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
    const FOUR_HOURS = 4 * 60 * 60 * 1000;
    const now = Date.now();

    // 1. Verificar se já checou na última hora
    const lastCheck = sessionStorage.getItem(STORAGE_KEY);

    if (lastCheck && (now - parseInt(lastCheck, 10)) < FOUR_HOURS) {
        console.log("Verificação de release já realizada.");
        return;
    }

    //Mensagem de loading
    showAlertMessage({
        id: "release-loading-alert",
        type: "info",
        icon: "fa-spinner",
        message: "Buscando por novas atualizações..."
    });

    try {
        // 2. Buscar releases
        const [installed, available] = await Promise.all([
            getReleaseInstall(),
            getReleaseAvaible()
        ]);

        // Remove o loading
        removeAlert("release-loading-alert");

        let message = `Release instalada: \n${formatReleaseDate(installed)}`;

        // 3. Comparar
        if (installed < available) {
            message += ' (Desatualizado)';
            newReleaseMessage();
        }else {
            //Sistema atualizado
            showAlertMessage({
                id: "release-success-alert",
                type: "success",
                icon: "fa-circle-check",
                message: "Sistema atualizado"
            });
        }

        // 5. Marcar verificação em sessão
        sessionStorage.setItem(STORAGE_KEY, now.toString());
        localStorage.setItem('RELEASE_MESSAGE', message);

    } catch (error) {
        console.error("Erro ao verificar release:", error.message);

        // Remove o loading
        removeAlert("release-loading-alert");

        showAlertMessage({
            id: "release-error-alert",
            type: "danger",
            icon: "fa-circle-exclamation",
            message: getUserFriendlyError(error)
        });
    }
}

main();