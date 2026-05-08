<?php

class Categoria
{
    //Atributos
    private $id_categoria_produto;
    private $descricao_categoria;
    
    public function __construct(string $descricao_categoria, int $id_categoria_produto = null){
        $this->setDescricao_categoria($descricao_categoria);
        if($id_categoria_produto){
            $this->setId_categoria_produto($id_categoria_produto);
        }
    }

    /**
     * Retorna o id de uma categoria de produto
     */
    public function getId_categoria_produto()
    {
        return $this->id_categoria_produto;
    }

    /**
     * Retorna a descrição de uma categoria de produto
     */
    public function getDescricaoCategoria()
    {
        return $this->descricao_categoria;
    }

    /**
     * Define o id de uma categoria de produto
     */
    public function setId_categoria_produto(int $id_categoria_produto)
    {
        if($id_categoria_produto < 1){
            throw new InvalidArgumentException('O id de uma categoria de produto não pode ser menor que 1.');
        }
        $this->id_categoria_produto = $id_categoria_produto;
    }

    /**
     * Define a descrição de uma categoria
     */
    public function setDescricao_categoria(string $descricao_categoria)
    {
        if(empty($descricao_categoria)){
            throw new InvalidArgumentException('A descrição de uma categoria não pode ser vazia.');
        }
        $this->descricao_categoria = $descricao_categoria;
    }

}
