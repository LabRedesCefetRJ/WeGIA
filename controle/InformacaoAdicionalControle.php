<?php
require_once dirname(__DIR__) . '/dao/Conexao.php';
require_once dirname(__DIR__) . '/dao/InformacaoAdicionalDAO.php';

class InformacaoAdicionalControle {

    private PDO $pdo;

    public function __construct(PDO $pdo = null)
    {
        if(!is_null($pdo)){
            $this->pdo = $pdo;
        }else{
            $this->pdo = Conexao::connect();
        }
    }

    public function getTodasInformacoesAdicionaisPorIdFuncionario(){
        try{
            $idFuncionario = intval(filter_input(INPUT_GET, 'id_funcionario', FILTER_VALIDATE_INT));

            if(!$idFuncionario || $idFuncionario < 1){
                throw new InvalidArgumentException('O id de um funcionário deve ser um inteiro positivo maior ou igual a 1', 400);
            }

            $informacaoAdicionalDao = new InformacaoAdicionalDAO($this->pdo);
            $informacoesAdicionais = $informacaoAdicionalDao->getTodasInformacoesAdicionaisPorIdFuncionario($idFuncionario);

            echo json_encode($informacoesAdicionais);
        }catch(Exception $e){
            http_response_code(intval($e->getCode()));
            echo json_encode(['erro' => $e->getMessage()]);
        }
    }

}
