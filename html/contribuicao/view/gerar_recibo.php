<?php
$title = 'Gerar Recibo de Doação';
require_once './templates/header.php';
require_once '../controller/BrandController.php';

$brandController = new BrandController();
$brand = $brandController->getBrand();


?>

<div class="container-contact100">
    <div class="wrap-contact100">
        <span id="logo_img">
            <?php
            if (!is_null($brand)) {
                echo $brand->getImagem()->getHtml();
            }
            ?>
        </span>
        <h2 class="text-center">Gerar Recibo de Doação</h2>
        <form id="form-recibo" autocomplete="off">
            
            <div class="wrap-input100">
                <span class="label-input100">CPF do Sócio</span>
                <input class="input100" type="text" name="cpf" id="cpf" 
                    placeholder="000.000.000-00" onkeyup="return Onlynumbers(event)" onkeypress="mascara('###.###.###-##',this,event)" maxlength="14" required>
            </div>
            
            <div class="wrap-input100">
                <span class="label-input100">Data Inicial</span>
                <input class="input100" type="date" name="data_inicio" required>
            </div>
            
            <div class="wrap-input100">
                <span class="label-input100">Data Final</span>
                <input class="input100" type="date" name="data_fim" required>
            </div>
            
            <div class="container-contact100-form-btn">
                <button type="submit" class="contact100-form-btn">
                    Gerar Recibo
                </button>
            </div>
        </form>
        
        <div id="resultado"></div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>

<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

<script src="../vendor/select2/select2.min.js"></script>
<script src="../public/js/mascara.js"></script>
<script src="../public/js/util.js"></script>
<script src="../public/js/recibo.js"></script>
<!--Busca cep-->
<script src="../../../Functions/busca_cep.js"></script>
<?php
require_once './templates/footer.php';
?>