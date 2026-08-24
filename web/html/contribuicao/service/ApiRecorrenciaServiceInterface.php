<?php
require_once '../model/Recorrencia.php';
interface ApiRecorrenciaServiceInterface {
    /**
     * Recebe como parâmetro uma Recorrencia e faz uma requisição para a API criar
     * a assinatura. Lança PaymentServiceException se for recusada. Se não
     * lançar, retorna um array:
     *   ['transacao_id' => string, 'status' => 'aprovado'|'em_analise']
     * Ver ApiCartaoCreditoServiceInterface — mesmo racional para 'em_analise'.
     */
    public function criarAssinatura(Recorrencia $recorrencia);
}