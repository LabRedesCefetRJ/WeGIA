<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'ContatoInstituicaoMySQL.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'ContatoInstituicao.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class ContatoInstituicaoControle{
    private PDO $pdo;

    public function __construct(?PDO $pdo=null)
    {
        isset($pdo) ? $this->pdo = $pdo : $this->pdo = Conexao::connect();
    }

    /**
     * Extrai os dados de uma requisição POST e armazena a persistência no banco de dados MySQL da aplicação.
     */
    public function incluir(){
        try{
            $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
            $contato = filter_input(INPUT_POST, 'contato', FILTER_SANITIZE_SPECIAL_CHARS);

            $contatoInstituicao = new ContatoInstituicao($descricao, $contato, new ContatoInstituicaoMySQL($this->pdo));
            $resultado = $contatoInstituicao->incluir();

            if($resultado === false)
                throw new Exception('Falha no servidor ao cadastrar um novo contato.', 500);

            echo json_encode(['resultado' => $resultado]);
        }catch(Exception $e){
            Util::tratarException($e);
        }
    }

    /**
     * Imprime um JSON de todos os contatos salvos no banco de dados MySQL da aplicação.
     */
    public function listarTodos(){
        try{
            $contatoInstituicaoMysql = new ContatoInstituicaoMySQL($this->pdo);
            $contatos = ContatoInstituicao::listarTodos($contatoInstituicaoMysql);

            //considerar adicionar armazenamento de log do acesso do usuário posteriormente.

            echo json_encode(['resultado' => $contatos]);
        }catch(Exception $e){
            Util::tratarException($e);
        }
    }

    /**
     * Remove a persistência de id equivalente ao informado do banco de dados MySQL da aplicação.
     */
    public function excluir(){
        try{
            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

            $contatoInstituicaoMysql = new ContatoInstituicaoMySQL($this->pdo);
            $resultado = ContatoInstituicao::excluirPorId($id, $contatoInstituicaoMysql);

            if($resultado === false)
                throw new Exception('Falha no servidor ao excluir um contato.', 500);

            echo json_encode(['resultado' => $resultado]);
        }catch(Exception $e){
            Util::tratarException($e);
        }
    }
}