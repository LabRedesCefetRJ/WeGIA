<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'ContribuicaoLog.php';
interface ApiPixServiceInterface{
    /**
     * Recebe como parâmetro uma ContribuicaoLog e faz uma requisição para a API gerar o QrCode
     */
    public function gerarQrCode(ContribuicaoLog $contribuicaoLog);
}
