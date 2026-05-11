<?php 

// require "../../dao/Conexao.php";

class DocumentoFuncionario {
    private $id_fundocs;
    private $documento;
    private $extensao;
    private $nome;
    private $exception = false;

    // Constructor

    function __construct($id)
    {
        $id = (int) $id;
        $this->setid_fundocs($id);
        try {
            $pdo = Conexao::connect();
            $query = $pdo->query("SELECT extensao_arquivo, nome_arquivo, arquivo FROM funcionario_docs WHERE id_fundocs = $id ;");
            $query = $query->fetch(PDO::FETCH_ASSOC);
            $this->setDocumento(base64_decode(gzuncompress($query["arquivo"])));
            $this->setExtensao($query["extensao_arquivo"]);
            $this->setNome($query["nome_arquivo"]);
        } catch (PDOException $e) {
            $this->setException("Houve um erro ao consultar o documento no banco de dados: $e");
        }
    }


    // Getters & Setters
    
    public function getid_fundocs()
    {
        return $this->id_fundocs;
    }

     
    public function setid_fundocs($id_fundocs)
    {
        $this->id_fundocs = $id_fundocs;

        return $this;
    }

    public function getDocumento()
    {
        return $this->documento;
    }

    public function setDocumento($documento)
    {
        $this->documento = $documento;

        return $this;
    }

    public function getException()
    {
        return $this->exception;
    }

    public function setException($exception)
    {
        $this->exception = $exception;

        return $this;
    }

    public function getExtensao()
    {
        return $this->extensao;
    }

    public function setExtensao($extensao)
    {
        $this->extensao = $extensao;

        return $this;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;

        return $this;
    }

    // Metodos

    function delete(){
        try {
            $pdo = Conexao::connect();
            $query = $pdo->query("DELETE FROM funcionario_docs WHERE id_fundocs = ".$this->getid_fundocs()." ;");
            $query = $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->setException("Houve um erro ao remover o documento do banco de dados: $e");
        }
    }

}
