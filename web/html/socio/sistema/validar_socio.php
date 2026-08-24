<?php
//Favicon dependecies

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . "config.php";
require_once ROOT . "/html/personalizacao_display.php";
?>
<!DOCTYPE html>
<!-- Página pública para consulta e validação de sócios, informações parcialmente censuradas -->
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validar Amigo Doador</title>

    <!-- JQuery 3.6.0 -->
    <script src="../../../assets/vendor/jquery/jquery.min.js" defer></script>

    <!--Bootstrap 3.4.1-->
    <link rel="stylesheet" href="../../../assets/vendor/bootstrap/css/bootstrap.min.css">
    <script src="../../../assets/vendor/bootstrap/js/bootstrap.min.js" defer></script>

    <!-- Font Awesome 4.1.0-->
    <link rel="stylesheet" href="../../../assets/vendor/font-awesome/css/font-awesome.css">

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

    <!-- Page CSS -->
    <link rel="stylesheet" href="../css/validar_socio.css">

    <!-- Page script -->
    <script src="controller/script/validar_socio.js" defer></script>

    <!-- Favicon -->
    <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon">
    <script>
        window.WEGIA_VALIDAR_SOCIO_CONFIG = <?php echo json_encode([
                                                'apiBaseUrl' => defined('API_BASE_URL') ? API_BASE_URL : '',
                                            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    </script>
</head>

<body>
    <header>
        <nav class="navbar navbar-default">
            <div class="container-fluid">

                <div class="navbar-header">

                    <!-- Logo -->
                    <a class="navbar-brand" href="#">
                        <img src="<?php display_campo("Logo", 'file'); ?>" alt="Logo">
                    </a>

                    <!-- Botão do menu mobile -->
                    <button type="button"
                        class="navbar-toggle collapsed"
                        data-toggle="collapse"
                        data-target="#menu-principal"
                        aria-expanded="false">
                        <span class="sr-only">Alternar navegação</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                </div>

                <div class="collapse navbar-collapse" id="menu-principal">
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="#" id="link_contato_suporte" rel="noopener noreferrer"><i class="fa fa-phone"></i> Contato</a></li>
                        <li><a href="../../contribuicao/view/forma_contribuicao.php"><i class="fa fa-usd"></i> Doe já</a></li>
                    </ul>
                </div>

            </div>
        </nav>
    </header>
    <main>
        <div class="container validar-socio-page">
            <section class="validar-hero text-center">
                <div class="validar-hero__icon" aria-hidden="true">
                    <i class="fa fa-shield"></i>
                </div>
                <h1>Validação de Sócio</h1>
                <p>Consulte a situação de um sócio por QR Code ou UUID de identificação.</p>
            </section>

            <div id="mensagens_usuario" class="mensagens-usuario" aria-live="polite" aria-atomic="true"></div>

            <section class="buscar-socio-card">
                <div class="buscar-socio-card__header">
                    <div>
                        <h2>Buscar sócio</h2>
                    </div>
                </div>

                <form class="buscar-socio-form" action="#" method="post" autocomplete="off">
                    <div class="buscar-socio-form__scan">
                        <div class="scan-icone" aria-hidden="true">
                            <i class="fa fa-qrcode"></i>
                        </div>
                        <div>
                            <strong>Escanear QR Code</strong>
                            <span>Use a câmera ou leitor para preencher o código automaticamente.</span>
                        </div>
                    </div>

                    <div class="buscar-socio-form__field">
                        <label for="codigo_socio" class="sr-only">Código do sócio</label>
                        <input
                            type="text"
                            id="codigo_socio"
                            name="codigo_socio"
                            class="form-control"
                            placeholder="Digite o UUID do sócio"
                            aria-label="Digite o UUID do sócio">
                    </div>

                    <button type="submit" class="btn btn-consultar">
                        <i class="fa fa-search" aria-hidden="true"></i>
                        Consultar
                    </button>
                </form>
            </section>

            <section class="socio-resumo-card" id="resumo_socio" hidden aria-hidden="true">
                <div class="socio-resumo-card__topo">
                    <div class="socio-resumo-card__status">
                        <!-- Resultado deve ser dinâmico -->
                        <div class="socio-resumo-card__status-icone" id="socio-status-icone" aria-hidden="true">
                            <i class="fa fa-question-circle"></i>
                        </div>
                        <div id="socio-status">

                        </div>
                    </div>

                    <div class="socio-resumo-card__codigo">
                        <span>Código de validação</span>
                        <div class="codigo-validacao-wrap">
                            <strong id="codigo_validacao">--</strong>
                            <button
                                type="button"
                                class="btn btn-default btn-copy-codigo"
                                id="btn_copy_codigo"
                                aria-label="Copiar código de validação"
                                title="Copiar código de validação">
                                <i class="fa fa-files-o" aria-hidden="true"></i>
                                <span class="btn-copy-codigo__text">Copiar</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="socio-resumo-card__dados">
                    <div class="socio-avatar" aria-hidden="true">
                        <i class="fa fa-user"></i>
                    </div>

                    <div class="socio-dados-principais">
                        <h3 id="socio_nome">Nome do Contribuidor</h3>
                        <div class="socio-dados-grid">
                            <p><span>CPF:</span> <strong id="socio_cpf">***.***.***-xx</strong></p>
                            <p><span>Data de nascimento:</span> <strong id="socio_data_nascimento">dd/**/**yy</strong></p>
                            <p><span>Telefone:</span> <strong id="socio_telefone">(XX) XXXX-XXXX</strong></p>
                            <p><span>E-mail:</span> <strong id="socio_email">email_do_contribuidor@exemplo.com</strong></p>
                        </div>
                    </div>
                </div>

                <div class="socio-resumo-card__metas">
                    <div class="meta-box">
                        <div class="meta-box__icone" aria-hidden="true">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <div>
                            <span>Início da contribuição</span>
                            <strong id="contribuicao_inicio">dd/mm/yyyy</strong>
                        </div>
                    </div>

                    <div class="meta-box">
                        <div class="meta-box__icone" aria-hidden="true">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <div>
                            <span>Última contribuição</span>
                            <strong id="contribuicao_ultima">dd/mm/yyyy</strong>
                        </div>
                    </div>
                </div>

                <div class="socio-resumo-card__beneficios">
                    <div class="beneficio-icone" aria-hidden="true">
                        <i class="fa fa-gift"></i>
                    </div>
                    <div class="beneficio-conteudo">
                        <span>Pontos de benefícios disponíveis</span>
                        <strong id="pontos_beneficios">[PONTOS]</strong>
                    </div>

                    <p class="beneficio-descricao">
                        Use seus pontos nos estabelecimentos parceiros.
                    </p>

                    <button type="button" class="btn btn-link btn-saiba-mais">Saiba mais</button>
                </div>
            </section>
        </div>
    </main>
    <footer>

        <div class="text-center">
            <p>&copy; 2026 Nome da instituição</p>
        </div>

    </footer>
</body>

</html>
