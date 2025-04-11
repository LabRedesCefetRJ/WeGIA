<?php
$title = 'Escolha sua forma de contribuição';
require_once './templates/header.php';

//buscar meios de pagamento
require_once '../controller/MeioPagamentoController.php';

$meioPagamentoController = new MeioPagamentoController();
$meiosDePagamentoArray = $meioPagamentoController->buscaTodos();

$algumMeioDePagamentoAtivo = false;

?>
<div class="container-contact100">
    <div class="wrap-contact100">

        <!--Adiciona a logo e o título ao topo da página-->
        <?php include('./components/contribuicao_brand.php'); ?>

        <div class="doacao_boleto">
            <h3>Escolha sua forma de contribuição</h3>

            <?php foreach ($meiosDePagamentoArray as $meioDePagamento):
                if ($meioDePagamento['meio'] === 'Boleto' && $meioDePagamento['status'] === 1): ?>
                    <a class="btn btn-secondary m-2" href="./boleto.php" role="button">Boleto Único</a>
                <?php $algumMeioDePagamentoAtivo = true;
                    continue;
                endif; ?>

                <?php if ($meioDePagamento['meio'] === 'Carne' && $meioDePagamento['status'] === 1): ?>
                    <a class="btn btn-secondary m-2" href="./mensalidade.php" role="button">Carnê de Mensalidades</a>
                <?php $algumMeioDePagamentoAtivo = true;
                    continue;
                endif; ?>

                <?php if ($meioDePagamento['meio'] === 'Pix' && $meioDePagamento['status'] === 1): ?>
                    <a class="btn btn-secondary m-2" href="./pix.php" role="button">PIX</a>
                <?php $algumMeioDePagamentoAtivo = true;
                    continue;
                endif; ?>
            <?php endforeach; ?>

            <?php if ($algumMeioDePagamentoAtivo === false): ?>
                <div class="alert alert-warning alert-dismissible" role="alert" style="width: 60%; margin:auto">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    Nenhum meio de pagamento disponível.
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>

<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

<script src="../vendor/select2/select2.min.js"></script>
<script src="../public/js/mascara.js"></script>
<?php
require_once './templates/footer.php';
?>