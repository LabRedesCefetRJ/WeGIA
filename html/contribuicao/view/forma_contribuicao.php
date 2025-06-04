<?php
$title = 'Escolha sua forma de contribuição';
require_once './templates/header.php';

//buscar meios de pagamento
require_once '../controller/MeioPagamentoController.php';

$meioPagamentoController = new MeioPagamentoController();
$meiosDePagamentoArray = $meioPagamentoController->buscaTodos();


?>
<div class="container-contact100">
    <div class="wrap-contact100">

        <!--Adiciona a logo e o título ao topo da página-->
        <?php include('./components/contribuicao_brand.php'); ?>

        <div class="doacao_boleto">
            <h3 class="text-center mb-4">Escolha sua forma de contribuição</h3>
            <div class="mb-3">
                <button id="btn-doacao-unica" type="button" class="btn btn-primary btn-lg px-2 m-2">Doação Única</button>
                <button id="btn-doacao-mensal" type="button" class="btn btn-outline-primary btn-lg px-2 m-2">Doação Mensal</button>
            </div>
            
            <!-- Opções para Doação Única -->
            <div id="opcoes-unica" style="display:block; text-align:center;">
                <?php 
                $algumMeioDePagamentoUnicoAtivo = false;
                foreach ($meiosDePagamentoArray as $meioDePagamento):
                    if ($meioDePagamento['meio'] === 'Boleto' && $meioDePagamento['status'] === 1): ?>
                        <a class="btn btn-secondary m-2" href="./boleto.php" role="button">Boleto</a>
                    <?php $algumMeioDePagamentoUnicoAtivo = true;
                        continue;
                    endif; ?>

                    <?php if ($meioDePagamento['meio'] === 'Pix' && $meioDePagamento['status'] === 1): ?>
                        <a class="btn btn-secondary m-2" href="./pix.php" role="button">PIX</a>
                    <?php $algumMeioDePagamentoUnicoAtivo = true;
                        continue;
                    endif; ?>

                    <?php if ($meioDePagamento['meio'] === 'CartaoCredito' && $meioDePagamento['status'] === 1): ?>
                        <a class="btn btn-secondary m-2" href="./cartao_credito.php" role="button">Cartão de Crédito</a>
                    <?php $algumMeioDePagamentoUnicoAtivo = true;
                        continue;
                    endif; ?>
                <?php endforeach; ?>

                <?php if ($algumMeioDePagamentoUnicoAtivo === false): ?>
                    <div class="alert alert-warning alert-dismissible" role="alert" style="width: 60%; margin:auto">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        Nenhum meio de pagamento disponível para doações únicas.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Opções para Doação Mensal -->
            <div id="opcoes-mensal" style="display:none; text-align:center;">
                <?php 
                $algumMeioDePagamentoMensalAtivo = false;
                foreach ($meiosDePagamentoArray as $meioDePagamento):
                    if ($meioDePagamento['meio'] === 'Carne' && $meioDePagamento['status'] === 1): ?>
                        <a class="btn btn-secondary m-2" href="./mensalidade.php" role="button">Boleto</a>
                    <?php $algumMeioDePagamentoMensalAtivo = true;
                        continue;
                    endif; ?>

                    <?php if ($meioDePagamento['meio'] === 'Recorrencia' && $meioDePagamento['status'] === 1): ?>
                        <a class="btn btn-secondary m-2" href="./recorrencia.php" role="button">Cartão de Crédito</a>
                    <?php $algumMeioDePagamentoMensalAtivo = true;
                        continue;
                    endif; ?>
                <?php endforeach; ?>

                <?php if ($algumMeioDePagamentoMensalAtivo === false): ?>
                    <div class="alert alert-warning alert-dismissible" role="alert" style="width: 60%; margin:auto">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        Nenhum meio de pagamento disponível para doações mensais.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="../public/js/forma_contribuicao.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>

<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

<script src="../vendor/select2/select2.min.js"></script>
<script src="../public/js/mascara.js"></script>
<?php
require_once './templates/footer.php';
?>