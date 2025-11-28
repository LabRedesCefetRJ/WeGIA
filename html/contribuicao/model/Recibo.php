<?php
class Recibo
{
    private $id;
    private $idSocio;
    private $codigo;
    private $valorTotal;
    private $dataInicio;
    private $dataFim;
    private $arquivo;
    private $dataGeracao;
    private $expirado;
    private $email;
    private $totalContribuicoes;

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
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the value of idSocio
     */
    public function getIdSocio()
    {
        return $this->idSocio;
    }

    /**
     * Set the value of idSocio
     *
     * @return  self
     */
    public function setIdSocio($idSocio)
    {
        $this->idSocio = $idSocio;
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

    /**
     * Get the value of valorTotal
     */
    public function getValorTotal()
    {
        return $this->valorTotal;
    }

    /**
     * Set the value of valorTotal
     *
     * @return  self
     */
    public function setValorTotal($valorTotal)
    {
        $this->valorTotal = $valorTotal;
        return $this;
    }

    /**
     * Get the value of DataInicio
     */
    public function getDataInicio()
    {
        return $this->dataInicio;
    }

    /**
     * Set the value of DataInicio
     *
     * @return  self
     */
    public function setDataInicio($dataInicio)
    {
        $this->dataInicio = $dataInicio;
        return $this;
    }

    /**
     * Get the value of DataFim
     */
    public function getDataFim()
    {
        return $this->dataFim;
    }

    /**
     * Set the value of DataFim
     *
     * @return  self
     */
    public function setDataFim($dataFim)
    {
        $this->dataFim = $dataFim;
        return $this;
    }

    /**
     * Get the value of arquivo
     */
    public function getArquivo()
    {
        return $this->arquivo;
    }

    /**
     * Set the value of arquivo
     *
     * @return  self
     */
    public function setArquivo($arquivo)
    {
        $this->arquivo = $arquivo;
        return $this;
    }

    /**
     * Get the value of dataGeracao
     */
    public function getDataGeracao()
    {
        return $this->dataGeracao;
    }

    /**
     * Set the value of dataGeracao
     *
     * @return  self
     */
    public function setDataGeracao($dataGeracao)
    {
        $this->dataGeracao = $dataGeracao;
        return $this;
    }

    /**
     * Get the value of expirado
     */
    public function getExpirado()
    {
        return $this->expirado;
    }

    /**
     * Set the value of expirado
     *
     * @return  self
     */
    public function setExpirado($expirado)
    {
        $this->expirado = $expirado;
        return $this;
    }

    /**
     * Get the value of email
     * @param bool $protected define se o resultado retornado estará censurado ou não para a exibição no frontend da aplicação
     * @param int $visibleChars define a quantidade de caracteres que será visível no começo e no final do e-mail, domínios não são ocultados por padrão.
     * @return string devolve o e-mail de um recibo de acordo com as especificações passadas. 
     */
    public function getEmail(?bool $protected = false, int $visibleChars = 1): string
    {
        if ($protected === true) {
            $email = $this->email;
            [$nome, $dominio] = explode('@', $email);

            // Calcula o tamanho da parte visível no início e no fim
            $inicio = substr($nome, 0, $visibleChars);
            $fim = substr($nome, -$visibleChars);
            $oculto = str_repeat('*', max(strlen($nome) - 2 * $visibleChars, 0));

            return "{$inicio}{$oculto}{$fim}@{$dominio}";
        }

        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get the value of totalContribuicoes
     */
    public function getTotalContribuicoes()
    {
        return $this->totalContribuicoes;
    }

    /**
     * Set the value of totalContribuicoes
     *
     * @return  self
     */
    public function setTotalContribuicoes($totalContribuicoes)
    {
        $this->totalContribuicoes = $totalContribuicoes;
        return $this;
    }
}
