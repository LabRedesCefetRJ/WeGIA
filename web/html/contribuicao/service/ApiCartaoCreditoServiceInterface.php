<?php
require_once '../model/ContribuicaoLog.php';
interface ApiCartaoCreditoServiceInterface {
    /**
     * Recebe como parâmetro uma ContribuicaoLog e faz uma requisição para a API
     * processar o pagamento. Lança PaymentServiceException se o pagamento for
     * recusado. Se não lançar, retorna um array:
     *   ['transacao_id' => string, 'status' => 'aprovado'|'em_analise']
     * 'em_analise' significa que a cobrança foi ACEITA PARA PROCESSAMENTO mas
     * ainda não está confirmada (o gateway pode aprovar ou recusar depois de
     * forma assíncrona) — o chamador não deve tratar isso como "aprovado".
     */
    public function processarCartaoCredito(ContribuicaoLog $contribuicaoLog);
}