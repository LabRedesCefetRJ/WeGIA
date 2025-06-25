<?php

require_once 'Conexao.php';
require_once '../classes/Aviso.php';

class AvisoDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::connect();
    }

    /**
     * Recebe um objeto do tipo Aviso como parâmetro, extrai suas propriedades e realiza os procedimentos necessários para realizar o cadastro dele no banco de dados da aplicação
     */
    public function cadastrar(Aviso $aviso)
    {
        $sql = 'INSERT INTO aviso(id_funcionario_aviso, id_pessoa_atendida, descricao, data) VALUES (:idFuncionario, :idPessoaAtendida, :descricao, :data);';

        $idFuncionario = $aviso->getIdFuncionario();
        $idPessoaAtendida = $aviso->getIdPessoaAtendida();
        $descricao = $aviso->getDescricao();

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':idFuncionario', $idFuncionario);
            $stmt->bindParam(':idPessoaAtendida', $idPessoaAtendida);
            $stmt->bindParam(':descricao', $descricao);

            date_default_timezone_set('America/Sao_Paulo');
            $data = date('Y-m-d H:i:s');
            $stmt->bindParam(':data', $data);

            $stmt->execute();

            $ultimoID = $this->pdo->lastInsertId();
            $informacoesUltimoId = $this->procuraPorID($ultimoID);

            if ($informacoesUltimoId['id_funcionario_aviso'] == $idFuncionario && $informacoesUltimoId['id_pessoa_atendida'] == $idPessoaAtendida && $informacoesUltimoId['descricao'] == $descricao) {

                $this->pdo->commit();
                return $ultimoID;
            } else {
                $this->pdo->rollBack();
            }
        } catch (PDOException $e) {
            echo 'Erro ao cadastrar uma intercorrência no banco de dados: '.$e->getMessage();
            throw $e;
        }
    }

    /**
     * Recebe como parâmetro um número inteiro, retorna as informações de um aviso no banco de dados que possua o mesmo id que o parâmetro recebido;
     */
    public function procuraPorID($id)
    {
        $sql = 'SELECT id_funcionario_aviso, id_pessoa_atendida, descricao FROM aviso WHERE id_aviso=:id';

        try {

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado;
        } catch (PDOException $e) {
            echo 'Erro ao procurar uma intercorrência com o id fornecido: '.$e->getMessage();
        }
    }

     public function listarIntercorrenciaPorIdDaFichaMedica($id){
        try{
            $stmt = $this->pdo->prepare("
                SELECT descricao, data
                FROM aviso
                JOIN saude_fichamedica sf on ( sf.id_fichamedica = :id_fichamedica)
                WHERE id_pessoa_atendida = sf.id_pessoa
                ORDER BY data DESC
            ");
            $stmt->bindValue(':id_fichamedica', $id, PDO::PARAM_INT);
            $stmt->execute();
            $intercorrencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $intercorrencias;
        } catch (PDOException $e){
            echo 'Erro ao procurar uma intercorrência com o id da ficha médica fornecida: '.$e->getMessage();
        }
        
    }
}
