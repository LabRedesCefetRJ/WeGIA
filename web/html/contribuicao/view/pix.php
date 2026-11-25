<?php
//verificar se o meio de pagamento está ativo
require_once '../controller/MeioPagamentoController.php';
$meioPagamentoController = new MeioPagamentoController();
if (!$meioPagamentoController->verificarStatus('Pix', true)) {
    header("Location: ./forma_contribuicao.php");
    exit();
}

$title = 'Emitir QRCode';
$useBootstrap5Contribution = true;
$publicsFiles = ['css' => ['contribution-refresh.css']];
require_once './templates/header.php';

$textoTipoContribuicao = 'GERAR QRCode';
$tipoContribuicao = 'QRCode';
$iconeTipoContribuicao = 'fa-qrcode';

?>

<div class="container-contact100 contribution-page" id="pix-page">
    <div class="wrap-contact100 contribution-card">

        <!--Adiciona a logo e o título ao topo da página-->
        <?php include('./components/contribuicao_brand.php'); ?>

        <div class="contribution-feedback-region" aria-live="polite" aria-atomic="true"></div>
        <header class="contribution-page-heading">
            <h1 class="h2"><i class="fa fa-qrcode" aria-hidden="true"></i> Contribua com PIX</h1>
            <p>Preencha seus dados em etapas e gere o QR Code ou código copia e cola.</p>
        </header>
        <p id="instrucao" class="contribution-required-note"><i class="fa fa-info-circle" aria-hidden="true"></i> Campos obrigatórios marcados com <span class="obrigatorio">*</span>.</p>

        <form id="formulario" class="contribution-form" autocomplete="off">
            <?= Csrf::inputField() ?>

            <input type="hidden" name="forma-contribuicao" id="forma-contribuicao" value="boleto">

            <div id="pag1" class="wrap-input100">
                <!--Adiciona a página de valor de contribuição-->
                <?php include('./components/contribuicao_valor.php'); ?>
                <?php $tipoAvanca = 'valor';
                include('./components/btn_avanca.php'); ?>
            </div>

            <div id="pag2" class="wrap-input100 hidden">
                <!--Adiciona a página para identificação de Sócios PJ e PF-->
                <?php include('./components/contribuicao_documento.php'); ?>
            </div>

            <div id="pag3" class="wrap-input100 hidden">
                <!--Adiciona a página para coleta do nome, data de nascimento, telefone e e-mail-->
                <?php include('./components/contribuicao_contato.php'); ?>
            </div>

            <div id="pag4" class="wrap-input100 hidden">
                <!--Adiciona a página para coleta do CEP, rua, número, bairro, estado, cidade e complemento-->
                <?php include('./components/contribuicao_endereco.php'); ?>
            </div>

            <div id="pag5" class="wrap-input100 hidden">
                <!--Adiciona a página para agradecimento e confirmação da geração do pix-->
                <?php include('./components/contribuicao_confirmacao.php'); ?>
            </div>

        </form>

        <div class="wrap-input100 contribution-result mt-5 hidden text-center" id="qrcode-div" aria-live="polite">
            <h2 class="h4"><i class="fa fa-mobile" aria-hidden="true"></i> Escaneie o QR Code ou copie o código PIX</h2>
        </div>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>

<script src="../vendor/select2/select2.min.js"></script>
<script src="../public/js/util.js"></script>
<script src="../public/js/pix.js"></script>
<!--Busca cep-->
<script src="../../../Functions/busca_cep.js"></script>
<?php
require_once './templates/footer.php';
?>