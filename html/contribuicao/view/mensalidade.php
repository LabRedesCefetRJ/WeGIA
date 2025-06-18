<?php
//verificar se o meio de pagamento está ativo
require_once '../controller/MeioPagamentoController.php';
$meioPagamentoController = new MeioPagamentoController();
if(!$meioPagamentoController->verificarStatus('Carne', true)){
    header("Location: ./forma_contribuicao.php");
    exit();
}

$title = 'Emitir mensalidade';
require_once './templates/header.php';

$textoTipoContribuicao = 'GERAR MENSALIDADE';
$tipoContribuicao = 'MENSALIDADE';

?>
<div class="container-contact100">
    <div class="wrap-contact100">

        <!--Adiciona a logo e o título ao topo da página-->
        <?php include('./components/contribuicao_brand.php'); ?>

        <p class="text-center">Campos obrigatórios <span class="obrigatorio">*</span></p>

        <form id="formulario" autocomplete="off">

            <input type="hidden" name="forma-contribuicao" id="forma-contribuicao" value="boleto">

            <div id="pag1" class="wrap-input100">
                <!--Adiciona a página de valor de contribuição, quantidade de parcelas e dia de vencimento-->
                <?php include('./components/contribuicao_valor.php'); ?>
                <?php include('./components/contribuicao_parcelas.php'); ?>
                <?php include('./components/contribuicao_dia_vencimento.php'); ?>

                <?php $tipoAvanca = 'valor'; include('./components/btn_avanca.php'); ?>
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
    </div>

    </form>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>

    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <script src="../vendor/select2/select2.min.js"></script>
    <script src="../public/js/mascara.js"></script>
    <script src="../public/js/util.js"></script>
    <script src="../public/js/mensalidade.js"></script>
    <!--Busca cep-->
    <script src="../../../Functions/busca_cep.js"></script>
    <?php
    require_once './templates/footer.php';
    ?>