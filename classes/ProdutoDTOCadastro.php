<?php
class ProdutoDTOCadastro implements JsonSerializable{
    private int $id;
    private string $descricao;
    private int $quantidade;
    private string $codigo;

    public function __construct(int $id, string $descricao, int $quantidade, string $codigo)
    {
        $this->setId($id);
        $this->setDescricao($descricao);
        $this->setQuantidade($quantidade);
        $this->setCodigo($codigo);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_produto' => $this->id,
            'descricao' => $this->descricao,
            'qtd' => $this->quantidade,
            'codigo' => $this->codigo
        ];
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId(int $id)
    {
        if($id < 1){
            throw new InvalidArgumentException('O id de um produto deve ser um intiero positivo maior ou igual a 1.', 400);
        }

        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of descricao
     */ 
    public function getDescricao()
    {
        return $this->descricao;
    }

    /**
     * Set the value of descricao
     *
     * @return  self
     */ 
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;

        return $this;
    }

    /**
     * Get the value of quantidade
     */ 
    public function getQuantidade()
    {
        return $this->quantidade;
    }

    /**
     * Set the value of quantidade
     *
     * @return  self
     */ 
    public function setQuantidade(int $quantidade)
    {
        if($quantidade < 0){
            throw new InvalidArgumentException('A quantidade de um produto não pode ser menor que 0.', 400);
        }

        $this->quantidade = $quantidade;

        return $this;
    }

    /**
     * Get the value of codigo
     */ 
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set the value of codigo
     *
     * @return  self
     */ 
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }
}