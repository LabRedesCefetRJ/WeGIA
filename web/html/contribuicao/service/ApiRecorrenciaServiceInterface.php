<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'Recorrencia.php';
interface ApiRecorrenciaServiceInterface {
    /**
     * Recebe como parâmetro uma Recorrencia e faz uma requisição para a API criar
     * a assinatura. Lança PaymentServiceException se for recusada. Se não
     * lançar, retorna um array:
     *   ['transacao_id' => string, 'status' => 'aprovado'|'em_analise']
     * Ver ApiCartaoCreditoServiceInterface — mesmo racional para 'em_analise'.
     */
    public function criarAssinatura(Recorrencia $recorrencia, ?array $dadosCartao = null);
}
