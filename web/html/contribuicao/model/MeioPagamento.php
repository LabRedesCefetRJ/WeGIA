<?php
class MeioPagamento{
    private $id;
    private $descricao;
    private $gatewayId;
    private $status;

    public function __construct($descricao, $gatewayId, $status=null)
    {
        $this->setDescricao($descricao)->setGatewayId($gatewayId);

        if(!$status){
            $this->setStatus(0);
        }else{
            $this->setStatus($status);
        }
    }

    /**
     * Pega os atributos $descricao e $gatewayId e realiza os procedimentos necessários para
     * inserir um novo meio de pagamento no sistema da aplicação.
     */
    public function cadastrar(){
        require_once '../dao/MeioPagamentoDAO.php';
        $meioPagamentoDao = new MeioPagamentoDAO();
        $meioPagamentoDao->cadastrar($this->descricao, $this->gatewayId, $this->status);
    }

    /**
     * Altera os dados do sistema pelos novos fornecidos através dos atributos $descricao e $gatewayId
     */
    public function editar(){
        require_once '../dao/MeioPagamentoDAO.php';
        $meioPagamentoDao = new MeioPagamentoDAO();
        $meioPagamentoDao->editarPorId($this->id, $this->descricao, $this->gatewayId);
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
        $descricaoLimpa = trim($descricao);
        if(!$descricaoLimpa || empty($descricaoLimpa)){
            throw new InvalidArgumentException();
        }

        $this->descricao = $descricao;

        return $this;
    }

    /**
     * Get the value of gatewayId
     */ 
    public function getGatewayId()
    {
        return $this->gatewayId;
    }

    /**
     * Set the value of gatewayId
     *
     * @return  self
     */ 
    public function setGatewayId($gatewayId)
    {
        if(!$gatewayId || $gatewayId < 1){
            throw new InvalidArgumentException();
        }

        $this->gatewayId = $gatewayId;

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
        $idLimpo = trim($id);

        if(!$idLimpo || $idLimpo <1){
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
        //echo $statusLimpo;

        if((!$statusLimpo || empty($statusLimpo)) && $statusLimpo != 0){
            throw new InvalidArgumentException('O status de um meio de pagamento não pode ser vazio.');
        }

        $this->status = $status;

        return $this;
    }
}