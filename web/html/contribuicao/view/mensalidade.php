<?php
//verificar se o meio de pagamento está ativo
require_once '../controller/MeioPagamentoController.php';
$meioPagamentoController = new MeioPagamentoController();
if (!$meioPagamentoController->verificarStatus('Carne', true)) {
    header("Location: ./forma_contribuicao.php");
    exit();
}

$title = 'Emitir mensalidade';
$useBootstrap5Contribution = true;
$publicsFiles = ['css' => ['contribution-refresh.css']];
require_once './templates/header.php';

$textoTipoContribuicao = 'GERAR MENSALIDADE';
$tipoContribuicao = 'MENSALIDADE';
$iconeTipoContribuicao = 'fa-calendar-check-o';

?>
<div class="container-contact100 contribution-page">
    <div class="wrap-contact100 contribution-card">

        <!--Adiciona a logo e o título ao topo da página-->
        <?php include('./components/contribuicao_brand.php'); ?>

        <div class="contribution-feedback-region" aria-live="polite" aria-atomic="true"></div>
        <header class="contribution-page-heading">
            <h1 class="h2"><i class="fa fa-calendar-check-o" aria-hidden="true"></i> Contribua mensalmente por boleto</h1>
            <p>Escolha o valor, o número de parcelas e a data de vencimento.</p>
        </header>
        <p class="contribution-required-note"><i class="fa fa-info-circle" aria-hidden="true"></i> Campos obrigatórios marcados com <span class="obrigatorio">*</span>.</p>

        <form id="formulario" class="contribution-form" autocomplete="off">
            <?= Csrf::inputField() ?>

            <input type="hidden" name="forma-contribuicao" id="forma-contribuicao" value="boleto">

            <div id="pag1" class="wrap-input100">
                <!--Adiciona a página de valor de contribuição, quantidade de parcelas e dia de vencimento-->
                <?php include('./components/contribuicao_valor.php'); ?>
                <?php include('./components/contribuicao_parcelas.php'); ?>
                <?php include('./components/contribuicao_dia_vencimento.php'); ?>

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
                <!--Adiciona a página para agradecimento e confirmação da geração do boleto-->
                <?php include('./components/contribuicao_confirmacao.php'); ?>
            </div>
        </form>
    </div>
</div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>

    <script src="../vendor/select2/select2.min.js"></script>
    <script src="../public/js/util.js"></script>
    <script src="../public/js/mensalidade.js"></script>
    <!--Busca cep-->
    <script src="../../../Functions/busca_cep.js"></script>
    <?php
    require_once './templates/footer.php';
    ?>