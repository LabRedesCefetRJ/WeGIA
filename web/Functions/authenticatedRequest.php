<script>
    const apiServer = "<?=API_BASE_URL?>";

    /**
     * Executa uma requisição autenticada.
     *
     * Se a API retornar:
     * 401 { error: "Token expirado" }
     *
     * realiza o refresh do Access Token e tenta novamente uma única vez.
     *
     * @param {Function} callback Função assíncrona que retorna um objeto Response.
     * @returns {Promise<Response>}
     */
    async function authenticatedRequest(callback) {
        let response = await callback();

        if (response.status !== 401) {
            return response;
        }

        let body;

        try {
            body = await response.clone().json();
        } catch {
            return response;
        }

        if (body.error !== 'Token expirado' && body.error !== 'Token inválido') {
            return response;
        }

        try {

            const refreshResponse = await fetch(`${apiServer}refresh`, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'X-Client-Type': 'web'
                }
            });

            if (!refreshResponse.ok) {
                return response;
            }

            // Access Token renovado.
            // Executa novamente a requisição original.
            return await callback();

        } catch {
            // Falha silenciosa.
            return response;
        }
    }
</script>