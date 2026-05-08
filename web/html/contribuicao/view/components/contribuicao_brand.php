<?php
require_once '../controller/BrandController.php';

$brandController = new BrandController();
$brand = $brandController->getBrand();
?>

<span id="logo_img">
    <?php
    if (!is_null($brand)) {
        echo $brand->getImagem()->getHtml();
    }
    ?>
</span>
<span class="contact100-form-title" id="titulo_pag">
    <?php
    if (!is_null($brand)) {
        echo $brand->getMensagem();
    }
    ?>
</span>