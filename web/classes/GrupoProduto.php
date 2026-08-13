<?php

class GrupoProduto {
    private $id_grupo_produto;
    private $descricao_grupo;

    public function __construct(string $descricao_grupo, int $id_grupo_produto = null) {
        $this->setDescricaoGrupo($descricao_grupo);
        if($id_grupo_produto) {
            $this->setIdGrupoProduto($id_grupo_produto);
        }
    }

    public function getIdGrupoProduto() {
        return $this->id_grupo_produto;
    }

    public function getDescricaoGrupo() {
        return $this->descricao_grupo;
    }

    public function setDescricaoGrupo(string $descricao_grupo) {
        if(empty($descricao_grupo)) {
            throw new InvalidArgumentException('A descrição de um grupo de produto não pode ser vazia.');
        }
        $this->descricao_grupo = $descricao_grupo;
    }

    public function setIdGrupoProduto(int $id_grupo_produto) {
        if($id_grupo_produto < 1) {
            throw new InvalidArgumentException('O id de um grupo de produto não pode ser menor que 1.');
        }
        $this->id_grupo_produto = $id_grupo_produto;
    }
}