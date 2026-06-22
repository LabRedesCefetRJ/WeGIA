<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'ContribuicaoLog.php';
interface ApiCartaoCreditoServiceInterface {
    /**
     * Recebe como parâmetro uma ContribuicaoLog e faz uma requisição para a API processar o pagamento
     */
    public function processarCartaoCredito(ContribuicaoLog $contribuicaoLog, ?array $dadosCartao = null);
}
