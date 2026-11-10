<?php
$title = 'Escolha sua forma de contribuição';
$useBootstrap5Contribution = true;
$publicsFiles = ['css' => ['contact.css', 'contribution-refresh.css']];
require_once './templates/header.php';

//buscar meios de pagamento
require_once '../controller/MeioPagamentoController.php';

$meioPagamentoController = new MeioPagamentoController();
$meiosDePagamentoArray = $meioPagamentoController->buscaTodos();


?>
<div class="container-contact100 contribution-page">
    <div class="wrap-contact100 contribution-card">

        <!--Adiciona a logo e o título ao topo da página-->
        <?php include('./components/contribuicao_brand.php'); ?>

        <div class="contribution-feedback-region" aria-live="polite" aria-atomic="true"></div>
        <div class="doacao_boleto contribution-methods">
            <h1 class="h2 text-center mb-2">Escolha sua forma de contribuição</h1>
            <p class="text-center text-muted mb-4">Selecione como deseja apoiar nosso trabalho.</p>
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-4" role="group" aria-label="Periodicidade da contribuição">
                <button id="btn-doacao-unica" type="button" class="btn btn-outline-primary btn-lg px-4" aria-pressed="false">
                    <i class="fa fa-heart-o me-2" aria-hidden="true"></i>Doação Única
                </button>
                <button id="btn-doacao-mensal" type="button" class="btn btn-primary btn-lg px-4" aria-pressed="true">
                    <i class="fa fa-calendar me-2" aria-hidden="true"></i>Doação Mensal
                </button>
            </div>
            
            <p class="text-center text-muted mb-4">Selecione sua forma de pagamento.</p>
            <!-- Opções para Doação Única -->
            <div id="opcoes-unica" class="contribution-method-options-single" style="display:none;">
                <?php 
                $algumMeioDePagamentoUnicoAtivo = false;
                foreach ($meiosDePagamentoArray as $meioDePagamento):
                    if ($meioDePagamento['meio'] === 'Boleto' && $meioDePagamento['status'] === 1): ?>
                        <a class="btn btn-outline-secondary btn-lg m-2 contribution-method-link" href="./boleto.php" role="button">
                            <i class="fa fa-barcode" aria-hidden="true"></i> Boleto
                        </a>
                    <?php $algumMeioDePagamentoUnicoAtivo = true;
                        continue;
                    endif; ?>

                    <?php if ($meioDePagamento['meio'] === 'Pix' && $meioDePagamento['status'] === 1): ?>
                        <a class="btn btn-outline-secondary btn-lg m-2 contribution-method-link" href="./pix.php" role="button">
                            <i class="fa fa-qrcode" aria-hidden="true"></i> PIX
                        </a>
                    <?php $algumMeioDePagamentoUnicoAtivo = true;
                        continue;
                    endif; ?>

                    <?php if ($meioDePagamento['meio'] === 'CartaoCredito' && $meioDePagamento['status'] === 1): ?>
                        <a class="btn btn-outline-secondary btn-lg m-2 contribution-method-link" href="./cartao_credito.php" role="button">
                            <i class="fa fa-credit-card" aria-hidden="true"></i> Cartão de crédito
                        </a>
                    <?php $algumMeioDePagamentoUnicoAtivo = true;
                        continue;
                    endif; ?>
                <?php endforeach; ?>

                <?php if ($algumMeioDePagamentoUnicoAtivo === false): ?>
                    <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
                        <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                        Nenhum meio de pagamento disponível para doações únicas.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Opções para Doação Mensal -->
            <div id="opcoes-mensal" style="display:block; text-align:center;">
                <?php 
                $algumMeioDePagamentoMensalAtivo = false;
                foreach ($meiosDePagamentoArray as $meioDePagamento):
                    if ($meioDePagamento['meio'] === 'Carne' && $meioDePagamento['status'] === 1): ?>
                        <a class="btn btn-outline-secondary btn-lg m-2 contribution-method-link" href="./mensalidade.php" role="button">
                            <i class="fa fa-barcode" aria-hidden="true"></i> Boleto mensal
                        </a>
                    <?php $algumMeioDePagamentoMensalAtivo = true;
                        continue;
                    endif; ?>

                    <?php if ($meioDePagamento['meio'] === 'Recorrencia' && $meioDePagamento['status'] === 1): ?>
                        <a class="btn btn-outline-secondary btn-lg m-2 contribution-method-link" href="./recorrencia.php" role="button">
                            <i class="fa fa-refresh" aria-hidden="true"></i> Cartão recorrente
                        </a>
                    <?php $algumMeioDePagamentoMensalAtivo = true;
                        continue;
                    endif; ?>
                <?php endforeach; ?>

                <?php if ($algumMeioDePagamentoMensalAtivo === false): ?>
                    <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
                        <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                        Nenhum meio de pagamento disponível para doações mensais.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <section id="video-demonstration-section" class="contribution-video" aria-label="Vídeo sobre as contribuições" style="display:none;">
            <iframe
                id="video-demonstration-frame"
                title="Vídeo sobre as contribuições"
                allow="autoplay; encrypted-media; picture-in-picture"
                allowfullscreen>
            </iframe>
        </section>
        <div id="contact-container"></div>

        
    </div>
    </div>
</div>

<script src="../public/js/forma_contribuicao.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>

<script src="../vendor/select2/select2.min.js"></script>
<script src="../public/js/mascara.js"></script>
<script>
    const contributionApiBaseUrl = <?= json_encode(rtrim(API_BASE_URL, '/') . '/', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    (async function loadVideoDemonstration() {
        try {
            const response = await fetch(`${contributionApiBaseUrl}socios/video_demonstration`);
            if (!response.ok) {
                throw new Error(`A API retornou o status ${response.status}`);
            }

            const configuration = await response.json();
            if (typeof configuration.value !== 'string' || configuration.value.trim() === '') {
                return;
            }

            const videoUrl = new URL(configuration.value);
            if (!['http:', 'https:'].includes(videoUrl.protocol)) {
                throw new Error('A URL de vídeo configurada não utiliza HTTP ou HTTPS.');
            }

            videoUrl.searchParams.set('autoplay', '1');
            videoUrl.searchParams.set('mute', '1');

            document.getElementById('video-demonstration-frame').src = videoUrl.toString();
            document.getElementById('video-demonstration-section').style.display = 'block';
        } catch (error) {
            console.error('Não foi possível carregar o vídeo de demonstração.', error);
        }
    })();
</script>
<?php
require_once './templates/footer.php';
?>