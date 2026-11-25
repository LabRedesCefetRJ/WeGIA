<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../erros/login_erro/");
    exit();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($_SESSION['id_pessoa'], 4, 7);

require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'Functions' . DIRECTORY_SEPARATOR . 'authenticatedRequest.php';
?>
<!DOCTYPE html>
<html class="fixed" lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Vídeo de demonstração</title>
    <link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="<?= WWW ?>assets/vendor/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme.css">
    <link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/skins/default.css">
    <link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme-custom.css">
    <script src="<?= WWW ?>assets/vendor/modernizr/modernizr.js"></script>
    <script src="<?= WWW ?>assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= WWW ?>assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="<?= WWW ?>assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="<?= WWW ?>assets/javascripts/theme.js"></script>
    <script src="<?= WWW ?>assets/javascripts/theme.custom.js"></script>
    <script src="<?= WWW ?>assets/javascripts/theme.init.js"></script>
    <style>
        .video-configuration-panel {
            max-width: 900px;
        }

        .video-configuration-help {
            margin-top: 8px;
        }

        .video-configuration-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }
    </style>
    <script>
        $(function() {
            $("#header").load("<?= WWW ?>html/header.php");
            $(".menuu").load("<?= WWW ?>html/menu.php");
        });
    </script>
</head>

<body>
    <section class="body">
        <header id="header"></header>
        <div class="inner-wrapper">
            <aside id="sidebar-left" class="sidebar-left menuu"></aside>
            <section role="main" class="content-body">
                <header class="page-header">
                    <h2>Vídeo de demonstração</h2>
                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li><a href="../../home.php"><i class="fa fa-home"></i></a></li>
                            <li><span>Sócios</span></li>
                            <li><span>Extra</span></li>
                            <li><span>Vídeo de demonstração</span></li>
                        </ol>
                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>

                <div class="row">
                    <div class="col-md-12">
                        <section class="panel video-configuration-panel">
                            <header class="panel-heading">
                                <h2 class="panel-title">Configurar vídeo de captação</h2>
                            </header>
                            <div class="panel-body">
                                <div id="video-configuration-message" class="alert" role="status" aria-live="polite" style="display:none;"></div>
                                <form id="video-configuration-form">
                                    <div class="form-group">
                                        <label for="video-url">URL para incorporação do vídeo</label>
                                        <input id="video-url" class="form-control" type="url" maxlength="512" required>
                                        <p class="help-block video-configuration-help">
                                            Informe uma URL de incorporação, como https://www.youtube.com/embed/ID. O vídeo será iniciado automaticamente sem som.
                                        </p>
                                    </div>
                                    <div class="video-configuration-actions">
                                        <button id="save-video-configuration" class="btn btn-primary" type="submit" disabled>Salvar vídeo</button>
                                        <button id="remove-video-configuration" class="btn btn-danger" type="button" disabled>Remover vídeo</button>
                                    </div>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>
            </section>
        </div>
    </section>

    <script>
        const configurationUrl = `${apiServer}socios/configuration`;
        const configurationType = 'video_demonstration';
        const configurationForm = document.getElementById('video-configuration-form');
        const videoUrlInput = document.getElementById('video-url');
        const saveConfigurationButton = document.getElementById('save-video-configuration');
        const removeConfigurationButton = document.getElementById('remove-video-configuration');
        const messageContainer = document.getElementById('video-configuration-message');
        let configurationExists = false;

        function showConfigurationMessage(message, isError = false) {
            messageContainer.textContent = message;
            messageContainer.className = `alert ${isError ? 'alert-danger' : 'alert-success'}`;
            messageContainer.style.display = 'block';
        }

        async function sendConfigurationRequest(method, body) {
            const response = await authenticatedRequest(() => fetch(configurationUrl, {
                method,
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Client-Type': 'web'
                },
                body: body === undefined ? undefined : JSON.stringify(body)
            }));

            let result;
            try {
                result = await response.json();
            } catch (error) {
                throw new Error('A API retornou uma resposta inválida.');
            }

            if (!response.ok) {
                throw new Error(result.error || result.message || `A API retornou o status ${response.status}.`);
            }

            return result;
        }

        async function loadVideoConfiguration() {
            try {
                const result = await sendConfigurationRequest('GET');
                if (!Array.isArray(result.configurations)) {
                    throw new Error('A API retornou uma lista de configurações inválida.');
                }

                const configuration = result.configurations.find((item) => item.type === configurationType);
                configurationExists = Boolean(configuration);
                videoUrlInput.value = configuration ? configuration.value : '';
                saveConfigurationButton.disabled = false;
                removeConfigurationButton.disabled = !configurationExists;
            } catch (error) {
                showConfigurationMessage(error.message, true);
            }
        }

        configurationForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const value = videoUrlInput.value.trim();
            saveConfigurationButton.disabled = true;

            try {
                await sendConfigurationRequest(configurationExists ? 'PUT' : 'POST', {
                    type: configurationType,
                    value
                });
                configurationExists = true;
                removeConfigurationButton.disabled = false;
                showConfigurationMessage('Vídeo de demonstração salvo com sucesso.');
            } catch (error) {
                showConfigurationMessage(error.message, true);
            } finally {
                saveConfigurationButton.disabled = false;
            }
        });

        removeConfigurationButton.addEventListener('click', async () => {
            if (!window.confirm('Deseja remover o vídeo de demonstração?')) {
                return;
            }

            try {
                await sendConfigurationRequest('DELETE', { type: configurationType });
                configurationExists = false;
                videoUrlInput.value = '';
                removeConfigurationButton.disabled = true;
                showConfigurationMessage('Vídeo de demonstração removido com sucesso.');
            } catch (error) {
                showConfigurationMessage(error.message, true);
            }
        });

        loadVideoConfiguration();
    </script>
</body>

</html>
