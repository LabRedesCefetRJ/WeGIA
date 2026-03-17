<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'SocioTagDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'SocioTagMySql.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';

class SocioTag{
    private ?int $id;
    private string $descricao;
    private SocioTagDAO $dao;

    public function __construct(string $descricao, ?int $id = null, ?SocioTagDAO $dao = null)
    {
        $this->setDescricao($descricao)->setId($id);

        $this->dao = isset($dao) ? $dao : new SocioTagMySql(Conexao::connect());
    }

    public static function delete(int $id){
        $socioTagMysql =  new SocioTagMySql(Conexao::connect());
        return $socioTagMysql->delete($id);
    }

    public function setId(int $id){
        if($id < 1)
            throw new InvalidArgumentException('O id de uma tag não pode ser menor que 1.', 412);

        $this->id = $id;
        return $this;
    }

    public function setDescricao(string $descricao){
        $descricao = trim($descricao);

        if(strlen($descricao) < 1 || strlen($descricao) > 255)
            throw new InvalidArgumentException('A descricao de uma tag não pode ser menor que 1.', 412);

        $this->descricao = $descricao;
        return $this;
    }

    public function getId(){
        return $this->id;
    }

    public function getDescricao(){
        return $this->descricao;
    }
}