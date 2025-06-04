<?php
//verificar se o meio de pagamento está ativo
require_once '../controller/MeioPagamentoController.php';
$meioPagamentoController = new MeioPagamentoController();
if(!$meioPagamentoController->verificarStatus('Recorrencia', true)){
    header("Location: ./forma_contribuicao.php");
    exit();
}

$title = 'Apoio Mensal - Pagamento Recorrente';
require_once './templates/header.php';

$textoTipoContribuicao = 'ASSINATURA MENSAL';
$tipoContribuicao = 'Apoio Mensal';

?>
<div class="container-contact100">
    <div class="wrap-contact100">

        <!--Adiciona a logo e o título ao topo da página-->
        <?php include('./components/contribuicao_brand.php'); ?>

        <p class="text-center">Campos obrigatórios <span class="obrigatorio">*</span></p>
        <p class="text-center text-info">
            <small><i class="fa fa-info-circle"></i> Esta é uma assinatura mensal que será cobrada automaticamente todos os meses</small>
        </p>

        <form id="formulario">
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
                <!-- Informações específicas do cartão de crédito para assinatura -->
                <?php include('./components/contribuicao_cartao.php'); ?>
            </div>

            <div id="pag6" class="wrap-input100 hidden">
                <!--Adiciona a página para agradecimento e confirmação-->
                <div class="text-center">
                    <div id="loading" class="mt-4 mb-4">
                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                            <span class="sr-only">Carregando...</span>
                        </div>
                        <h3 class="mt-3">Criando sua assinatura...</h3>
                        <p class="text-muted">Processando dados da assinatura mensal</p>
                    </div>
                    <div id="payment-result" class="mt-4 hidden">
                        <div id="success-message" class="alert alert-success p-4">
                            <div class="d-flex justify-content-center mb-3">
                                <i class="fa fa-check-circle fa-4x text-success"></i>
                            </div>
                            <h3 class="text-success">Assinatura criada com sucesso!</h3>
                            <p>Agradecemos pelo seu apoio mensal!</p>
                        </div>
                        <div id="error-message" class="alert alert-danger p-4 hidden">
                            <div class="d-flex justify-content-center mb-3">
                                <i class="fa fa-exclamation-triangle fa-4x text-danger"></i>
                            </div>
                            <h4 class="text-danger">Erro na Assinatura</h4>
                            <p class="mt-2" id="error-text"></p>
                        </div>
                    </div>
                    <div class="container-contact100-form-btn">
                        <button class="contact100-form-btn btn-voltar" id="volta-endereco">
                            <i style="margin-right: 15px; " class="fa fa-long-arrow-left m-l-7" aria-hidden="true"></i>
                            VOLTAR
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="../vendor/select2/select2.min.js"></script>
<script src="../public/js/mascara.js"></script>
<script src="../public/js/util.js"></script>
<script src="../public/js/recorrencia.js"></script>
<!--Busca cep-->
<script src="../../../Functions/busca_cep.js"></script>
<?php
require_once './templates/footer.php';
?>