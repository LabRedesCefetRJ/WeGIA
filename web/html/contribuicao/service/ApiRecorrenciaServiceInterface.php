<?php
require_once '../model/Recorrencia.php';
interface ApiRecorrenciaServiceInterface {
    /**
     * Recebe como parâmetro uma Recorrencia e faz uma requisição para a API criar a assinatura
     */
    public function criarAssinatura(Recorrencia $recorrencia);
}