<?php
class RegraPagamento
{
    private $id;
    private $meioPagamentoId;
    private $regraContribuicaoId;
    private $valor;
    private $status;

    /**
     * Instancia um objeto do tipo RegraPagamentoDAO e chama o seu método de cadastrar passando os 
     * valores de $meioPagamentoId, $regraContribuicaoId e $valor como parâmetros
     */
    public function cadastrar(){
        require_once '../dao/RegraPagamentoDAO.php';
        $gatewayPagamentoDao = new RegraPagamentoDAO();
        $gatewayPagamentoDao->cadastrar($this->meioPagamentoId, $this->regraContribuicaoId, $this->valor, $this->status);
    }

    /**
     * Altera o valor de uma regra de pagamento no sistema
     */
    public function editar(){
        require_once '../dao/RegraPagamentoDAO.php';
        $meioPagamentoDao = new RegraPagamentoDAO();
        $meioPagamentoDao->editarPorId($this->id, $this->valor);
    }


    /**
     * Get the value of valor
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * Set the value of valor
     *
     * @return  self
     */
    public function setValor($valor)
    {
        $valor = floatval($valor);

        if(!$valor || $valor < 0){
            throw new InvalidArgumentException();
        }
        $this->valor = $valor;

        return $this;
    }

    /**
     * Get the value of regraContribuicaoId
     */
    public function getRegraContribuicaoId()
    {
        return $this->regraContribuicaoId;
    }

    /**
     * Set the value of regraContribuicaoId
     *
     * @return  self
     */
    public function setRegraContribuicaoId($regraContribuicaoId)
    {
        $regraContribuicaoIdLimpo = trim($regraContribuicaoId);

        if(!$regraContribuicaoIdLimpo || $regraContribuicaoIdLimpo <1){
            throw new InvalidArgumentException();
        }
        $this->regraContribuicaoId = $regraContribuicaoIdLimpo;

        return $this;
    }

    /**
     * Get the value of meioPagamentoId
     */ 
    public function getMeioPagamentoId()
    {
       return $this->meioPagamentoId;
    }

    /**
     * Set the value of meioPagamentoId
     *
     * @return  self
     */ 
    public function setMeioPagamentoId($meioPagamentoId)
    {
        $meioPagamentoIdLimpo = trim($meioPagamentoId);

        if(!$meioPagamentoIdLimpo || $meioPagamentoIdLimpo <1){
            throw new InvalidArgumentException();
        }
        $this->meioPagamentoId = $meioPagamentoIdLimpo;

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
    public function setId($id)
    {
        if(!$id || $id < 1){
            throw new InvalidArgumentException();
        }

        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of status
     */ 
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */ 
    public function setStatus($status)
    {
        $statusLimpo = trim($status);

        if((!$statusLimpo || empty($statusLimpo)) && $statusLimpo != 0){
            throw new InvalidArgumentException('O status de um meio de pagamento não pode ser vazio.');
        }

        $this->status = $status;

        return $this;
    }
}
