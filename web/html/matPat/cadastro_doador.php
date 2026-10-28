<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ". WWW ."html/index.php");
    exit;
}else{
    session_regenerate_id();
}

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
require_once ROOT . '/service/PermissaoFornecedorService.php';
$permissaoFornecedor = new PermissaoFornecedorService();
if (!$permissaoFornecedor->permite((int) $_SESSION['id_pessoa'], 3)) {
    header('Location: ' . WWW . 'html/home.php?msg_c=' . urlencode(
        'Você não tem permissão para realizar esta ação em origens.'
    ));
    exit;
}

require_once ROOT . '/classes/OrigemNavegacao.php';
require_once ROOT . '/classes/Csrf.php';
$origemPagina = OrigemNavegacao::normalizar($_GET['origem'] ?? null);
$paginaVoltar = OrigemNavegacao::destino($origemPagina);

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";

require_once ROOT . "/html/geral/msg.php";

if (!isset($_SESSION['almoxarifado'])) {
	header('Location: ' . WWW . 'controle/control.php?' . http_build_query([
        'metodo' => 'listarTodos',
        'nomeClasse' => 'AlmoxarifadoControle',
        'nextPage' => OrigemNavegacao::cadastro($origemPagina)
    ]));
	exit;
}

if (isset($_SESSION['almoxarifado'])) {
	$almoxarifados = json_decode($_SESSION['almoxarifado'], true);

	unset($_SESSION['almoxarifado']);
}
$campoErro = $_SESSION['origem_campo_erro'] ?? null;
$mensagemCampoErro = $_SESSION['msg'] ?? '';
unset($_SESSION['origem_campo_erro']);
$dadosFormulario = $_SESSION['origem_formulario'] ?? [];
unset($_SESSION['origem_formulario']);
$valorFormulario = static function ($campo) use ($dadosFormulario) {
    $valor = $dadosFormulario[$campo] ?? '';
    return htmlspecialchars(is_string($valor) ? $valor : '', ENT_QUOTES, 'UTF-8');
};
$almoxarifadosSelecionados = array_map('intval', (array) ($dadosFormulario['almoxarifados'] ?? []));
?>

<!doctype html>
<html class="fixed">

<head>
    <!-- Basic -->
    <meta charset="UTF-8">

    <title>Cadastro de Origem</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="<?= WWW ?>assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">

    <!-- Theme CSS -->
    <link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme.css" />

    <!-- Skin CSS -->
    <link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/skins/default.css" />

    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme-custom.css">

    <!-- Head Libs -->
    <script src="<?= WWW ?>assets/vendor/modernizr/modernizr.js"></script>

    <!-- Javascript functions -->

    <script src="<?= WWW ?>assets/vendor/jquery/jquery.min.js"></script>

    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <!-- Functions -->
    <script src="<?= WWW ?>Functions/mascara.js"></script>
    <script src="<?= WWW ?>Functions/onlyNumbers.js"></script>
    <script src="<?= WWW ?>Functions/onlyChars.js"></script>
    <script src="<?= WWW ?>Functions/testaCPF.js"></script>
    <script>
        function atualizarEstadoEnvio() {
            var cpf = document.getElementById("NCPF").value.trim();
            var cnpj = document.getElementById("cnpj").value.trim();
            var cpfValido = cpf.length === 0 || testaCPF(cpf);
            var cnpjValido = cnpj.length === 0 || validarCNPJ(cnpj);

            var documentosCompativeis = cpf.length === 0 || cnpj.length === 0;
            document.getElementById("enviar").disabled = !cpfValido || !cnpjValido || !documentosCompativeis;
            return cpfValido && cnpjValido && documentosCompativeis;
        }

        function validarCPF(strCPF) {
            strCPF = strCPF.trim();
            var cpfValido = strCPF.length === 0 || testaCPF(strCPF);

            $('#cpfInvalido').toggle(!cpfValido);
            atualizarEstadoEnvio();
            return cpfValido;
        }

        function validarCNPJ(cnpj) {

            cnpj = cnpj.replace(/[^\d]+/g, '');

            if (cnpj == '') return false;
            if (cnpj.length != 14)
                return false;
            // Elimina CNPJs invalidos conhecidos
            if (cnpj == "00000000000000" ||
                cnpj == "11111111111111" ||
                cnpj == "22222222222222" ||
                cnpj == "33333333333333" ||
                cnpj == "44444444444444" ||
                cnpj == "55555555555555" ||
                cnpj == "66666666666666" ||
                cnpj == "77777777777777" ||
                cnpj == "88888888888888" ||
                cnpj == "99999999999999")
                return false;
            // Valida DVs
            tamanho = cnpj.length - 2
            numeros = cnpj.substring(0, tamanho);
            digitos = cnpj.substring(tamanho);
            soma = 0;
            pos = tamanho - 7;
            for (i = tamanho; i >= 1; i--) {
                soma += numeros.charAt(tamanho - i) * pos--;
                if (pos < 2)
                    pos = 9;
            }
            resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
            if (resultado != digitos.charAt(0))
                return false;
            tamanho = tamanho + 1;
            numeros = cnpj.substring(0, tamanho);
            soma = 0;
            pos = tamanho - 7;
            for (i = tamanho; i >= 1; i--) {
                soma += numeros.charAt(tamanho - i) * pos--;
                if (pos < 2)
                    pos = 9;
            }
            resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
            if (resultado != digitos.charAt(1))
                return false;
            return true;
        }

        function exibirCNPJ(cnpj) {
            cnpj = cnpj.trim();
            var cnpjValido = cnpj.length === 0 || validarCNPJ(cnpj);

            $('#cnpjInvalido').toggle(!cnpjValido);
            atualizarEstadoEnvio();
            return cnpjValido;
        }
    </script>
    <script type="text/javascript">
        function validar() {
            var cpfValido = validarCPF(document.getElementById("NCPF").value);
            var cnpjValido = exibirCNPJ(document.getElementById("cnpj").value);
            return cpfValido && cnpjValido && atualizarEstadoEnvio();
        }
        $(function() {
            $("#header").load("../header.php");
            $(".menuu").load("../menu.php");
        });
    </script>
    <link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/processo-compra.css">
</head>

<body class="processo-compra">
    <section class="body">
        <!-- start: header -->
        <div id="header"></div>
        <!-- end: header -->

        <div class="inner-wrapper">
            <!-- start: sidebar -->
            <aside id="sidebar-left" class="sidebar-left menuu"></aside>

            <section role="main" class="content-body">
                <header class="page-header">
                    <h2>Cadastro</h2>

                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li>
                                <a href="<?= WWW ?>html/home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li><span>Cadastro</span></li>
                            <li><span>Origem</span></li>
                        </ol>

                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>

                <!-- start: page -->
                <div class="row">
                    <div class="col-md-4 col-lg-2" style=" visibility: hidden;"></div>
                    <div class="col-md-8 col-lg-8">
                        <div class="tabs">
                            <ul class="nav nav-tabs tabs-primary">
                                <li class="active">
                                    <a href="#overview" data-toggle="tab">Cadastro de Origem</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div id="overview" class="tab-pane active">
                                    <?php sessionMsg(); ?>
                                    <form class="doador" method="post" action="<?= WWW ?>controle/control.php" onsubmit="return validar()" autocomplete="off">
                                        <input type="hidden" name="origem_pagina" value="<?= htmlspecialchars($origemPagina, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="nomeClasse" value="OrigemControle">
                                        <input type="hidden" name="metodo" value="incluir">
                                        <?= Csrf::inputField() ?>
                                        <fieldset>
                                            <h4 class="mb-xlg">Origem</h4>
                                            <div class="row">
                                                <div class="col-md-9">
                                                    <div class="row">
                                                        <div class="col-sm-4">
                                                            <div class="form-group" style="margin-left: 0; margin-right: 0;">
                                                                <label for="cnpj">CNPJ</label>
                                                                <input type="text" name="cnpj" id="cnpj" aria-describedby="cnpj-status" onblur="exibirCNPJ(this.value)" maxlength="18" class="form-control input-md" ng-model="cadastro.cnpj" placeholder="Ex: 77.777.777/7777-77" value="<?= $valorFormulario('cnpj') ?>">
                                                                <small id="cnpj-status" role="status" aria-live="polite"></small>
                                                                <p id="cnpjInvalido" style="display: none; color: #b30000">CNPJ INVÁLIDO!</p>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <div class="form-group" style="margin-left: 0; margin-right: 0;">
                                                                <label for="NCPF">CPF</label>
                                                                <input type="text" class="form-control" id="NCPF" name="cpf" placeholder="Ex: 222.222.222-22" maxlength="14" onblur="validarCPF(this.value)" value="<?= $valorFormulario('cpf') ?>">
                                                                <p id="cpfInvalido" style="display: none; color: #b30000">CPF INVÁLIDO!</p>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <div class="form-group" style="margin-left: 0; margin-right: 0;">
                                                                <label for="telefone">Telefone</label>
                                                                <input type="text" class="form-control" minlength="12" name="telefone" id="telefone" placeholder="Ex: (22)99999-9999" value="<?= $valorFormulario('telefone') ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <div class="form-group" style="margin-left: 0; margin-right: 0;">
                                                                <label for="nome">Nome / Razão social</label>
                                                                <input type="text" class="form-control" name="nome" id="nome" maxlength="100" required value="<?= $valorFormulario('nome') ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-9">
                                                    <div class="row">
                                                        <div class="col-sm-4">
                                                            <div class="form-group" style="margin-left: 0; margin-right: 0;">
                                                                <label for="inscricao_estadual">Inscrição estadual</label>
                                                                <input type="text" class="form-control" name="inscricao_estadual" id="inscricao_estadual" maxlength="30" value="<?= $valorFormulario('inscricao_estadual') ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-8">
                                                            <div class="form-group" style="margin-left: 0; margin-right: 0;">
                                                                <label for="email">E-mail</label>
                                                                <input type="email" class="form-control" name="email" id="email" maxlength="254" value="<?= $valorFormulario('email') ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-3">
                                                            <div class="form-group" style="margin-left: 0; margin-right: 0;">
                                                                <label for="cep">CEP</label>
                                                                <input type="text" class="form-control" name="cep" id="cep" maxlength="9" placeholder="00000-000" pattern="[0-9]{5}-?[0-9]{3}" inputmode="numeric" value="<?= $valorFormulario('cep') ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="form-group" style="margin-left: 0; margin-right: 0;">
                                                                <label for="logradouro">Logradouro</label>
                                                                <input type="text" class="form-control" name="logradouro" id="logradouro" maxlength="255" value="<?= $valorFormulario('logradouro') ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <div class="form-group" style="margin-left: 0; margin-right: 0;">
                                                                <label for="numero">Número</label>
                                                                <input type="text" class="form-control" name="numero" id="numero" maxlength="20" value="<?= $valorFormulario('numero') ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <div class="form-group" style="margin-left: 0; margin-right: 0;">
                                                                <label for="complemento">Complemento</label>
                                                                <input type="text" class="form-control" name="complemento" id="complemento" maxlength="100" value="<?= $valorFormulario('complemento') ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="form-group" style="margin-left: 0; margin-right: 0;">
                                                                <label for="bairro">Bairro</label>
                                                                <input type="text" class="form-control" name="bairro" id="bairro" maxlength="100" value="<?= $valorFormulario('bairro') ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-9">
                                                            <div class="form-group" style="margin-left: 0; margin-right: 0;">
                                                                <label for="cidade">Cidade</label>
                                                                <input type="text" class="form-control" name="cidade" id="cidade" maxlength="100" value="<?= $valorFormulario('cidade') ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <div class="form-group" style="margin-left: 0; margin-right: 0;">
                                                                <label for="uf">UF</label>
                                                                <input type="text" class="form-control" name="uf" id="uf" maxlength="2" pattern="AC|AL|AP|AM|BA|CE|DF|ES|GO|MA|MT|MS|MG|PA|PB|PR|PE|PI|RJ|RN|RS|RO|RR|SC|SP|SE|TO" title="Informe uma UF válida, por exemplo: SP, RJ ou MG." value="<?= $valorFormulario('uf') ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                            $prefixoPagamento = '';
                                            $dadosPagamento = $dadosFormulario;
                                            require __DIR__ . '/condicoes_pagamento.php';
                                            ?>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Almoxarifado(s)</label>
                                                <div class="col-md-6">
                                                    <?php foreach ($almoxarifados as $almoxarifado): ?>
                                                        <div class="checkbox">
                                                            <label>
                                                                <input
                                                                    type="checkbox"
                                                                    name="almoxarifados[]"
                                                                    value="<?= (int) $almoxarifado['id_almoxarifado'] ?>"
                                                                    <?= in_array((int) $almoxarifado['id_almoxarifado'], $almoxarifadosSelecionados, true) ? 'checked' : '' ?>
                                                                >
                                                                <?= htmlspecialchars($almoxarifado['descricao_almoxarifado'], ENT_QUOTES, 'UTF-8') ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-9 col-md-offset-3">
                                                    <button id="enviar" class="btn btn-primary" type="submit">Enviar</button>
                                                    <input type="reset" class="btn btn-default">
                                                    <a href="<?= htmlspecialchars($paginaVoltar, ENT_QUOTES, 'UTF-8')?>" color: white; text-decoration: none;>
                                                        <button type="button" class="btn btn-info">Voltar</button>
                                                    </a>
                                                    <a href="listar_origem.php" style="color: white; text-decoration:none;"><button class="btn btn-success" type="button">Listar origens</button></a>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end: page -->
            </section>
        </div>
    </section>

    <!-- Vendor -->

    <script src="<?= WWW ?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
    <script src="<?= WWW ?>assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="<?= WWW ?>assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="<?= WWW ?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="<?= WWW ?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

    <script src="<?= WWW ?>Functions/origem_autopreenchimento.js"></script>
    <script>
        configurarAutopreenchimento({ prefixo: '', campoCpf: 'NCPF' });
        exibirErroOrigem(
            document.getElementById('nome').form,
            <?= json_encode($campoErro, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            <?= json_encode($mensagemCampoErro, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
        );
        document.getElementById('NCPF').addEventListener('input', atualizarEstadoEnvio);
        document.getElementById('cnpj').addEventListener('input', atualizarEstadoEnvio);
    </script>
    <div align="right">
        <iframe src="https://www.wegia.org/software/footer/matPat.html" width="200" height="60" style="border:none;"></iframe>
    </div>

</body>

</html>
