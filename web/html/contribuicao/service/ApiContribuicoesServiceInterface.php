<?php
require_once dirname(__FILE__, 2).DIRECTORY_SEPARATOR.'model'.DIRECTORY_SEPARATOR.'ContribuicaoLogCollection.php';

interface ApiContribuicoesServiceInterface{
    /**
     * Retorna o conjunto de contribuições armazenadas no servidor do gateway de pagamentos
     */
    public function getContribuicoes(?string $status):ContribuicaoLogCollection;
}