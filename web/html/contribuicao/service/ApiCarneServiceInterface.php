<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'ContribuicaoLogCollection.php';
interface ApiCarneServiceInterface{
    public function gerarCarne(ContribuicaoLogCollection $contribuicaoLog);
    public function guardarSegundaVia($arquivos, $cpfSemMascara, $ultimaParcela);//Considerar transformar em utilitário
}
