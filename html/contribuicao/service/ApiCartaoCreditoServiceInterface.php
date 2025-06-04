<?php
require_once '../model/ContribuicaoLog.php';
interface ApiCartaoCreditoServiceInterface {
    /**
     * Recebe como parâmetro uma ContribuicaoLog e faz uma requisição para a API processar o pagamento
     */
    public function processarCartaoCredito(ContribuicaoLog $contribuicaoLog);
}