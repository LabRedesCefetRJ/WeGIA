<?php
require_once dirname(__DIR__) . '/classes/InformacaoAdicional.php';

class InformacaoAdicionalDAO{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function montarInformacaoAdicional(array $array){
        return new InformacaoAdicional($array['id'], $array['descricao'], $array['dado']);
    }

    public function getTodasInformacoesAdicionaisPorIdFuncionario(int $idFuncionario){
        $informacoesAdicionais = [];

        $sql = "SELECT fo.idfunncionario_outrasinfo as id, fo.dado, fl.descricao  
            FROM funcionario_outrasinfo fo 
            JOIN funcionario_listainfo fl ON (fo.funcionario_listainfo_idfuncionario_listainfo=fl.idfuncionario_listainfo)
            WHERE fo.funcionario_id_funcionario=:idFuncionario";
    
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':idFuncionario', $idFuncionario);
        $stmt->execute();

        if($stmt->rowCount() > 0){
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach($resultado as $informacaoAdicionalArray){
                $informacaoAdicional = $this->montarInformacaoAdicional($informacaoAdicionalArray);
                $informacoesAdicionais[] = $informacaoAdicional;
            }
        }

        return $informacoesAdicionais;
    }

}