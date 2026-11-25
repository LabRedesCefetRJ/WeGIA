<?php
require_once '../controller/BrandController.php';

$brandController = new BrandController();
$brand = $brandController->getBrand();
?>

<div class="contribution-brand">
    <?php
    if (!is_null($brand)) {
        echo $brand->getImagem()->getHtml();
    }
    ?>
    <div class="contact100-form-title contribution-brand-title" id="titulo_pag">
    <?php
    if (!is_null($brand)) {
        echo $brand->getMensagem();
    }
    ?>
    </div>
</div>