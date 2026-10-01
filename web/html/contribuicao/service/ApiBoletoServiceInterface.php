<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'ContribuicaoLog.php';
interface ApiBoletoServiceInterface{
    public function gerarBoleto(ContribuicaoLog $contribuicaoLog);
    public function guardarSegundaVia(string $link, ContribuicaoLog $contribuicaoLog);//Considerar transformar em utilitário
    /*Adicionar mais métodos posteriormente */
}
