<?php
require_once '../model/ContribuicaoLog.php';
interface ApiPixServiceInterface{
    /**
     * Recebe como parâmetro uma ContribuicaoLog e faz uma requisição para a API gerar o QrCode
     */
    public function gerarQrCode(ContribuicaoLog $contribuicaoLog);
}