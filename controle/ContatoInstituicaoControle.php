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
}