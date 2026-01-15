<?php
//verificar se o meio de pagamento está ativo
require_once '../controller/MeioPagamentoController.php';
$meioPagamentoController = new MeioPagamentoController();
if (!$meioPagamentoController->verificarStatus('Pix', true)) {
    header("Location: ./forma_contribuicao.php");
    exit();
}

$title = 'Emitir QRCode';
require_once './templates/header.php';

$textoTipoContribuicao = 'GERAR QRCode';
$tipoContribuicao = 'QRCode';

?>

<div class="container-contact100" id="pix-page">
    <div class="wrap-contact100">

        <!--Adiciona a logo e o título ao topo da página-->
        <?php include('./components/contribuicao_brand.php'); ?>

        <p id="instrucao" class="text-center">Campos obrigatórios <span class="obrigatorio">*</span></p>

        <form id="formulario" autocomplete="off">
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
                <!--Adiciona a página para agradecimento e confirmação da geração do boleto-->
                <?php include('./components/contribuicao_confirmacao.php'); ?>
            </div>

        </form>

        <div class="wrap-input100 mt-5 hidden text-center" id="qrcode-div">
            <h4>Escaneie seu QRCode, <br> ou então clique no nosso copia e cola!</h4>
        </div>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>

<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

<script src="../vendor/select2/select2.min.js"></script>
<script src="../public/js/mascara.js"></script>
<script src="../public/js/util.js"></script>
<script src="../public/js/pix.js"></script>
<!--Busca cep-->
<script src="../../../Functions/busca_cep.js"></script>
<?php
require_once './templates/footer.php';
?>