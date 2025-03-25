<?php
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'ApiContribuicoesServiceInterface';

class PagarMeContribuicoesService implements ApiContribuicoesServiceInterface{
    public function getContribuicoes(?string $status): ContribuicaoLogCollection
    {
        $url = '';
        //verificar o parâmetro de status
        if(!is_null($status)){

        }

        //definir o período de tempo de análise

        //realizar requisições

        //retornar contribuições
        $contribuicaoLogCollection = new ContribuicaoLogCollection();
        return $contribuicaoLogCollection;
    }
}