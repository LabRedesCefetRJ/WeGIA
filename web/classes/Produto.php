<?php

class Produto
{
    private $id_produto;
    private $id_categoria_produto;
    private $id_unidade;
    private $id_grupo_produto;
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
    
    public function get_grupo_produto()
    {
        return $this->id_grupo_produto;
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
            throw new InvalidArgumentException('O id de um produto deve ser um inteiro maior ou igual a 1.', 400);
        }
        $this->id_produto = $id_produto;
    }

    public function set_categoria_produto($id_categoria_produto)
    {
        if (!$id_categoria_produto || !is_numeric($id_categoria_produto) || $id_categoria_produto < 1) {
            throw new InvalidArgumentException('O id de uma categoria deve ser um inteiro maior ou igual a 1.', 400);
        }
        $this->id_categoria_produto = $id_categoria_produto;
    }

    public function set_unidade($id_unidade)
    {
        if (!$id_unidade || !is_numeric($id_unidade) || $id_unidade < 1) {
            throw new InvalidArgumentException('O id de uma unidade deve ser um inteiro maior ou igual a 1.', 400);
        }
        $this->id_unidade = $id_unidade;
    }

    public function set_grupo_produto($id_grupo_produto)
    {
        if ($id_grupo_produto === null || $id_grupo_produto === '') {
            $this->id_grupo_produto = null;
            return;
        }

        if (!is_numeric($id_grupo_produto) || $id_grupo_produto < 1) {
            throw new InvalidArgumentException(
                'O id de um grupo de produto deve ser um inteiro maior ou igual a 1.',
                400
            );
        }

        $this->id_grupo_produto = (int) $id_grupo_produto;
    }

    public function setPreco($preco)
    {
        $preco = str_replace(',', '.', $preco);

        if (!$preco || !is_numeric($preco) || (float) $preco < 0) {
            throw new InvalidArgumentException('O preço de um produto deve ser um número positivo.', 400);
        }
        $this->preco = $preco;
    }

    public function setDescricao($descricao)
    {
        if (!$descricao || empty($descricao)) {
            throw new InvalidArgumentException('A descrição de um produto não pode ser vazia.', 400);
        }
        $this->descricao = filter_var($descricao, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    public function setCodigo($codigo)
    {
        if ($codigo === null || $codigo === ''){
            $this->codigo = null;
            return;
        }

        $this->codigo = filter_var($codigo, FILTER_SANITIZE_SPECIAL_CHARS);
    }
}
