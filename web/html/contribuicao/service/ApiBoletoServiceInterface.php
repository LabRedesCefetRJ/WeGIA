<?php
require_once '../model/ContribuicaoLog.php';
interface ApiBoletoServiceInterface{
    public function gerarBoleto(ContribuicaoLog $contribuicaoLog);
    public function guardarSegundaVia(string $link, ContribuicaoLog $contribuicaoLog);//Considerar transformar em utilitário
    /*Adicionar mais métodos posteriormente */
}