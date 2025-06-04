<?php
require_once '../model/ContribuicaoLog.php';
interface ApiRecorrenciaServiceInterface {
    /**
     * Recebe como parâmetro uma ContribuicaoLog e faz uma requisição para a API criar a assinatura
     */
    public function criarAssinatura(ContribuicaoLog $contribuicaoLog);
}