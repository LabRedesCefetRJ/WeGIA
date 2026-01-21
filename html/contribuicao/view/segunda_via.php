<?php
$title = 'Emita sua segunda via';

require_once './templates/header.php';
require_once '../controller/BrandController.php';

$brandController = new BrandController();
$brand = $brandController->getBrand();
?>
<div class="container-contact100">
    <div class="wrap-contact100">

        <span id="logo_img"><?php if (!is_null($brand)) {
                                echo $brand->getImagem()->getHtml();
                            } ?></span>
        <span class="contact100-form-title" id="titulo_pag"><?php if (!is_null($brand)) {
                                                                echo $brand->getMensagem();
                                                            } ?></span>

        <input type="hidden" name="forma-contribuicao" id="forma-contribuicao" value="boleto">

        <div id="pag1" class="wrap-input100">

            <?= Csrf::inputField() ?>
            <div class="centralizar">
                <input class="radio" type="radio" id="op_cpf" value="fisica" name="opcao" checked><label class="label" for="op_cpf">PESSOA FÍSICA</label>
                <input class="radio" type="radio" id="op_cnpj" value="juridica" name="opcao"><label class="label" for="op_cnpj">PESSOA JURÍDICA</label><br><br>
            </div>

            <div id="cpf" class="wrap-input100 validate-input bg1" data-validate="Digite um documento válido!">
                <span class="label-input100">Digite um documento CPF*</span>
                <input class="input100" type="text" name="dcpf" id="dcpf" class="text required" placeholder="Ex: 222.222.222-22" oninput="mascaraCPF(this)" inputmode="numeric" maxlength="14" required><span id="avisa_cpf"></span>
            </div>

            <div id="cnpj" class="wrap-input100 validate-input bg1 hidden" data-validate="Digite um documento válido!">
                <span class="label-input100"> Digite um documento CNPJ *</span>
                <input class="input100" type="text" name="dcpf" id="dcnpj" maxlength="18" class="form-control input-md" ng-m placeholder="Ex: 22.222.222/2222-22" onkeypress="mascara('##.###.###/####-##',this,event)"><span id="avisa_cnpj"></span>
            </div>
            <div class="container-contact100-form-btn">
                <button class="contact100-form-btn" id="consultar-btn">
                    AVANÇAR
                    <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div id="pag2" class="wrap-input100 hidden">
            <div id="tabela-boletos"></div>
            <div class="container-contact100-form-btn">
                <button class="contact100-form-btn" id="voltar-btn">
                    <i style="margin-right: 15px; " class="fa fa-long-arrow-left m-l-7" aria-hidden="true"></i> VOLTAR
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>

    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <script src="../vendor/select2/select2.min.js"></script>
    <script src="../public/js/mascaraCPF.js"></script>
    <script src="../public/js/mascara.js"></script>
    <script src="../public/js/segundaVia.js"></script>
    <?php
    require_once './templates/footer.php';
    ?>