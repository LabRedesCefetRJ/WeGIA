<?php

class Produto
{
    private $id_produto;
    private $id_categoria_produto;
    private $id_unidade;
    private $descricao;
    private $preco;
    private $codigo;

    public function __construct($descricao, $codigo, $preco)
    {
        $this->setDescricao($descricao);
        $this->setCodigo($codigo);
        $this->setPreco($preco);
    }

    public function getId_produto()
    {
        return $this->id_produto;
    }

    public function get_categoria_produto()
    {
        return $this->id_categoria_produto;
    }

    public function get_unidade()
    {
        return $this->id_unidade;
    }

    public function getPreco()
    {
        return $this->preco;
    }

    public function getDescricao()
    {
        return $this->descricao;
    }

    public function getCodigo()
    {
        return $this->codigo;
    }

    public function setId_produto($id_produto)
    {
        if (!$id_produto || !is_numeric($id_produto) || $id_produto < 1) {
            throw new InvalidArgumentException('O id de um produto deve ser um inteiro maior ou igual a 1.');
        }
        $this->id_produto = $id_produto;
    }

    public function set_categoria_produto($id_categoria_produto)
    {
        if (!$id_categoria_produto || !is_numeric($id_categoria_produto) || $id_categoria_produto < 1) {
            throw new InvalidArgumentException('O id de uma categoria deve ser um inteiro maior ou igual a 1.');
        }
        $this->id_categoria_produto = $id_categoria_produto;
    }

    public function set_unidade($id_unidade)
    {
        if (!$id_unidade || !is_numeric($id_unidade) || $id_unidade < 1) {
            throw new InvalidArgumentException('O id de uma unidade deve ser um inteiro maior ou igual a 1.');
        }
        $this->id_unidade = $id_unidade;
    }

    public function setPreco($preco)
    {
        if (!$preco || !is_numeric($preco) || $preco < 0) {
            throw new InvalidArgumentException('O preço de um produto deve ser um número positivo.');
        }
        $this->preco = $preco;
    }

    public function setDescricao($descricao)
    {
        if (!$descricao || empty($descricao)) {
            throw new InvalidArgumentException('A descrição de um produto não pode ser vazia.');
        }
        $this->descricao = filter_var($descricao, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    public function setCodigo($codigo)
    {
        if (!$codigo || empty($codigo)) {
            throw new InvalidArgumentException('O código de um produto não pode ser vazio.');
        }
        $this->codigo = filter_var($codigo, FILTER_SANITIZE_SPECIAL_CHARS);
    }
}
