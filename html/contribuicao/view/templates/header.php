<?php
if(session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php' ;
require_once "../../../config.php";
require_once "../../../dao/Conexao.php";
require_once "../../../classes/Personalizacao_display.php";
require_once "../../personalizacao_display.php";
require_once dirname(__FILE__, 5) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';

/**
 * Carrega dinamicamente arquivos CSS e JS de uma página.
 *
 * @param array $publicsFile Exemplo:
 * [
 *     'css' => ['estilo.css', 'tema.css'],
 *     'js'  => ['script.js', 'app.js']
 * ]
 */
function loadPublicFiles(array $publicsFile): void
{
    // Diretório base do projeto (ajuste se necessário)
    $baseDir = realpath(__DIR__ . '/../../public');

    if (!$baseDir) {
        error_log('Diretório público não encontrado.');
        return;
    }

    // === CSS ===
    if (!empty($publicsFile['css']) && is_array($publicsFile['css'])) {
        foreach ($publicsFile['css'] as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);

            // Verifica se é um CSS válido
            if (strtolower($ext) !== 'css') {
                error_log("Arquivo CSS inválido ignorado: $file");
                continue;
            }

            $filePath = $baseDir . '/css/' . $file;

            // Verifica se o arquivo existe
            if (!file_exists($filePath)) {
                error_log("Arquivo CSS não encontrado: $filePath");
                continue;
            }

            echo '<link rel="stylesheet" href="../public/css/' . htmlspecialchars($file) . '">' . PHP_EOL;
        }
    }

    // === JS ===
    if (!empty($publicsFile['js']) && is_array($publicsFile['js'])) {
        foreach ($publicsFile['js'] as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);

            // Verifica se é um JS válido
            if (strtolower($ext) !== 'js') {
                error_log("Arquivo JS inválido ignorado: $file");
                continue;
            }

            $filePath = $baseDir . '/js/' . $file;

            // Verifica se o arquivo existe
            if (!file_exists($filePath)) {
                error_log("Arquivo JS não encontrado: $filePath");
                continue;
            }

            echo '<script src="../public/js/' . htmlspecialchars($file) . '"></script>' . PHP_EOL;
        }
    }
}

//Proteção contra robôs
require_once dirname(__FILE__, 5) . DIRECTORY_SEPARATOR . 'service' . DIRECTORY_SEPARATOR . 'CaptchaGoogleService.php';
$captchaGoogle = new CaptchaGoogleService();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="icon" href="<?php display_campo("Logo", "file"); ?>" type="image/x-icon">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script type="text/javascript" src="../../../Functions/onlyNumbers.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

    <link rel="stylesheet" type="text/css" href="../public/css/index.css">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Bitter&display=swap" rel="stylesheet">
    <!--
=========================================================================================-->

    <link rel="stylesheet" type="text/css" href="../vendor/bootstrap/css/bootstrap.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../vendor/font-awesome-4.7.0/css/font-awesome.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../vendor/iconic/css/material-design-iconic-font.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../vendor/animate/animate.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../vendor/animsition/css/animsition.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../vendor/select2/select2.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../vendor/daterangepicker/daterangepicker.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../vendor/noui/nouislider.min.css">
    <!--===============================================================================================-->

    <link rel="stylesheet" type="text/css" href="../public/css/util.css">
    <link rel="stylesheet" type="text/css" href="../public/css/main.css">
    <link rel="stylesheet" type="text/css" href="../public/css/donation.css">
    <link rel="stylesheet" type="text/css" href="../public/css/segundaVia.css">

    <!-- Carrega dinamicamente arquivos específicos de uma página -->
    <?php
    if (isset($publicsFiles) && is_array($publicsFiles))
        loadPublicFiles($publicsFiles);
    ?>
    <!-- Função para validar CPF -->
    <script src="../../../Functions/testaCPF.js"></script>

    <script src="<?= $captchaGoogle->getApi() ?>" async defer></script>

    <style>
        #logo_img {
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        #avisoPf {
            font-size: 20px;
            color: red;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        #avisoPj {
            font-size: 20px;
            color: red;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .disabled {
            pointer-events: none;
            opacity: 0.5;
        }

        .pultima_div {
            margin-left: auto;
            margin-right: auto;
        }

        .loader {
            border: 1px solid #f3f3f3;
            border-radius: 50%;
            border-top: 1px solid #3498db;
            width: 20px;
            height: 20px;
            -webkit-animation: spin 2s linear infinite;
            animation: spin 2s linear infinite;
            margin: 0 auto !important;
        }

        span.obrigatorio {
            color: red;
        }

        .doacao_boleto {
            justify-content: center;
            border-radius: 20px;
            text-align: center;
        }

        @-webkit-keyframes spin {
            0% {
                -webkit-transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>

</head>

<body>