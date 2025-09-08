<?php
require_once '../model/Socio.php';
require_once '../model/GatewayPagamento.php';
require_once '../model/MeioPagamento.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'RecorrenciaDTO.php';

class ContribuicaoLog
{
    private $id;
    private $valor;
    private $codigo;
    private $dataGeracao;
    private $dataVencimento;
    private $dataPagamento;
    private Socio $socio;
    private GatewayPagamento $gatewayPagamento;
    private MeioPagamento $meioPagamento;
    private ?RecorrenciaDTO $recorrenciaDTO = null;
    private $statusPagamento = 0;
    private $agradecimento;

    /**
     * Recebe como parâmetro um inteiro e retorna um código de caracteres aleatórios do tamanho informado
     */
    public function gerarCodigo(int $tamanho = 16)
    {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $caracteresTamanho = strlen($caracteres);
        $codigoString = '';
        for ($i = 0; $i < $tamanho; $i++) {
            $codigoString .= $caracteres[rand(0, $caracteresTamanho - 1)];
        }
        return $codigoString;
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
        $this->id = $id;

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
     * Get the value of dataVencimento
     */
    public function getDataVencimento()
    {
        return $this->dataVencimento;
    }

    /**
     * Set the value of dataVencimento
     *
     * @return  self
     */
    public function setDataVencimento($dataVencimento)
    {
        $this->dataVencimento = $dataVencimento;

        return $this;
    }

    /**
     * Get the value of idSocio
     */
    public function getSocio()
    {
        return $this->socio;
    }

    /**
     * Set the value of idSocio
     *
     * @return  self
     */
    public function setSocio(Socio $socio)
    {
        $this->socio = $socio;

        return $this;
    }

    /**
     * Get the value of statusPagamento
     */
    public function getStatusPagamento()
    {
        return $this->statusPagamento;
    }

    /**
     * Set the value of statusPagamento
     *
     * @return  self
     */
    public function setStatusPagamento($statusPagamento)
    {
        $this->statusPagamento = $statusPagamento;

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
    public function setGatewayPagamento(GatewayPagamento $gatewayPagamento)
    {
        $this->gatewayPagamento = $gatewayPagamento;

        return $this;
    }

    /**
     * Get the value of meioPagamento
     */ 
    public function getMeioPagamento()
    {
        return $this->meioPagamento;
    }

    /**
     * Set the value of meioPagamento
     *
     * @return  self
     */ 
    public function setMeioPagamento(MeioPagamento $meioPagamento)
    {
        $this->meioPagamento = $meioPagamento;

        return $this;
    }

    /**
     * Get the value of agradecimento
     */ 
    public function getAgradecimento()
    {
        return $this->agradecimento;
    }

    /**
     * Set the value of agradecimento
     *
     * @return  self
     */ 
    public function setAgradecimento(string $agradecimento)
    {
        $this->agradecimento = $agradecimento;

        return $this;
    }

    /**
     * Get the value of dataPagamento
     */ 
    public function getDataPagamento()
    {
        return $this->dataPagamento;
    }

    /**
     * Set the value of dataPagamento
     *
     * @return  self
     */ 
    public function setDataPagamento($dataPagamento)
    {
        $this->dataPagamento = $dataPagamento;

        return $this;
    }

    /**
     * Get the value of recorrenciaDTO
     */ 
    public function getRecorrenciaDTO()
    {
        return $this->recorrenciaDTO;
    }

    /**
     * Set the value of recorrenciaDTO
     *
     * @return  self
     */ 
    public function setRecorrenciaDTO(RecorrenciaDTO $recorrenciaDTO)
    {
        $this->recorrenciaDTO = $recorrenciaDTO;

        return $this;
    }
}
