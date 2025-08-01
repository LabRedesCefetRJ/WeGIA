<?php
class Recibo {
    private $id;
    private $idSocio;
    private $codigo;
    private $valorTotal;
    private $dataInicio;
    private $dataFim;
    private $caminhoPdf;
    private $dataGeracao;
    private $expirado;
    private $email;
    private $totalContribuicoes;

    /**
     * Get the value of id
     */
    public function getId() { 
        return $this->id; 
    }
    
    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id) { 
        $this->id = $id; 
        return $this; 
    }
    
    /**
     * Get the value of idSocio
     */
    public function getIdSocio() { 
        return $this->idSocio; 
    }
    
    /**
     * Set the value of idSocio
     *
     * @return  self
     */
    public function setIdSocio($idSocio) { 
        $this->idSocio = $idSocio; 
        return $this; 
    }
    
    /**
     * Get the value of codigo
     */
    public function getCodigo() { 
        return $this->codigo; 
    }

    /**
     * Set the value of codigo
     *
     * @return  self
     */
    public function setCodigo($codigo) { 
        $this->codigo = $codigo; 
        return $this; 
    }
    
    /**
     * Get the value of valorTotal
     */
    public function getValorTotal() { 
        return $this->valorTotal; 
    }

    /**
     * Set the value of valorTotal
     *
     * @return  self
     */
    public function setValorTotal($valorTotal) { 
        $this->valorTotal = $valorTotal; 
        return $this; 
    }
    
    /**
     * Get the value of DataInicio
     */
    public function getDataInicio() { 
        return $this->dataInicio; 
    }

    /**
     * Set the value of DataInicio
     *
     * @return  self
     */
    public function setDataInicio($dataInicio) { 
        $this->dataInicio = $dataInicio; 
        return $this; 
    }
    
    /**
     * Get the value of DataFim
     */
    public function getDataFim() { 
        return $this->dataFim; 
    }

    /**
     * Set the value of DataFim
     *
     * @return  self
     */
    public function setDataFim($dataFim) { 
        $this->dataFim = $dataFim; 
        return $this; 
    }
    
    /**
     * Get the value of caminhoPdf
     */
    public function getCaminhoPdf() { 
        return $this->caminhoPdf; 
    }

    /**
     * Set the value of caminhoPdf
     *
     * @return  self
     */
    public function setCaminhoPdf($caminhoPdf) { 
        $this->caminhoPdf = $caminhoPdf; 
        return $this; 
    }
    
    /**
     * Get the value of dataGeracao
     */
    public function getDataGeracao() { 
        return $this->dataGeracao; 
    }

    /**
     * Set the value of dataGeracao
     *
     * @return  self
     */
    public function setDataGeracao($dataGeracao) { 
        $this->dataGeracao = $dataGeracao; 
        return $this; 
    }
    
    /**
     * Get the value of expirado
     */
    public function getExpirado() { 
        return $this->expirado; 
    }

    /**
     * Set the value of expirado
     *
     * @return  self
     */
    public function setExpirado($expirado) { 
        $this->expirado = $expirado; 
        return $this; 
    }
    
    /**
     * Get the value of email
     */
    public function getEmail() { 
        return $this->email; 
    }

    /**
     * Set the value of email
     *
     * @return  self
     */
    public function setEmail($email) { 
        $this->email = $email; 
        return $this; 
    }
    
    /**
     * Get the value of totalContribuicoes
     */
    public function getTotalContribuicoes() { 
        return $this->totalContribuicoes; 
    }

    /**
     * Set the value of totalContribuicoes
     *
     * @return  self
     */
    public function setTotalContribuicoes($totalContribuicoes) { 
        $this->totalContribuicoes = $totalContribuicoes; 
        return $this; 
    }
}