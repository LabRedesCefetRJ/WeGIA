<?php
class InformacaoAdicional implements JsonSerializable
{
    private int $id;
    private string $descricao;
    private string $dados;

    public function __construct(int $id, string $descricao, string $dados)
    {
        $this->setId($id);
        $this->setDescricao($descricao);
        $this->setDados($dados);
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'descricao' => $this->descricao,
            'dados' => $this->dados
        ];
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
    public function setDescricao(string $descricao)
    {
        if (strlen(trim($descricao)) < 1) {
            throw new InvalidArgumentException('A descrição de uma informação adicional não pode ser vazia.', 400);
        }

        $this->descricao = $descricao;

        return $this;
    }

    /**
     * Get the value of dados
     */
    public function getDados()
    {
        return $this->dados;
    }

    /**
     * Set the value of dados
     *
     * @return  self
     */
    public function setDados(string $dados)
    {
        if (strlen(trim($dados)) < 1) {
            throw new InvalidArgumentException('Os dados de uma informação adicional não podem ser vazios.', 400);
        }

        $this->dados = $dados;

        return $this;
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
            throw new InvalidArgumentException('O id de uma informação adicional deve ser um inteiro positivo igual ou maior que 1', 400);
        }

        $this->id = $id;

        return $this;
    }
}
