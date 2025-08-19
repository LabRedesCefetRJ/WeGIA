<?php
class Recorrencia{
    //atributos privados
    private int $id;
    private Socio $socio;
    private GatewayPagamento $gatewayPagamento;
    private string $codigo;
    private float $valor;
    private DateTime $inicio;
    private DateTime $fim;
    private bool $status;

    private RecorrenciaDAO $dao;

    public function __construct(RecorrenciaDAO $dao)
    {
       $this->dao = $dao;
    }

    /**Cria uma recorrência no banco de dados da aplicação*/
    public function create(){
        $this->dao->create($this);
    }

    //Métodos de acesso

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
     * Get the value of socio
     */ 
    public function getSocio()
    {
        return $this->socio;
    }

    /**
     * Set the value of socio
     *
     * @return  self
     */ 
    public function setSocio($socio)
    {
        $this->socio = $socio;

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
        $this->valor = $valor;

        return $this;
    }

    /**
     * Get the value of inicio
     */ 
    public function getInicio()
    {
        return $this->inicio;
    }

    /**
     * Set the value of inicio
     *
     * @return  self
     */ 
    public function setInicio($inicio)
    {
        $this->inicio = $inicio;

        return $this;
    }

    /**
     * Get the value of fim
     */ 
    public function getFim()
    {
        return $this->fim;
    }

    /**
     * Set the value of fim
     *
     * @return  self
     */ 
    public function setFim($fim)
    {
        $this->fim = $fim;

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
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of gatewayPagamento
     */ 
    public function getGatewayPagamento()
    {
        return $this->gatewayPagamento;
    }

    /**
     * Set the value of gatewayPagamento
     *
     * @return  self
     */ 
    public function setGatewayPagamento($gatewayPagamento)
    {
        $this->gatewayPagamento = $gatewayPagamento;

        return $this;
    }
}