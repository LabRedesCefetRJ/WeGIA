<?php
class GatewayPagamento
{

    //atributos
    private $id;
    private $nome;
    private $endpoint;
    private $token;
    private $status;

    public function __construct($nome, $endpoint, $token, $status = null)
    {
        $this->setNome($nome)->setEndpoint($endpoint)->setToken($token);
        if (!$status) {
            $this->setStatus(0);
        } else {
            $this->setStatus($status);
        }
    }

    /**
     * Pega os atributos nome, endpoint, token e status e realiza os procedimentos necessários
     * para inserir um Gateway de pagamento no sistema
     */
    public function cadastrar()
    {
        require_once '../dao/GatewayPagamentoDAO.php';
        $gatewayPagamentoDao = new GatewayPagamentoDAO();
        $gatewayPagamentoDao->cadastrar($this->nome, $this->endpoint, $this->token, $this->status);
    }

    /**
     * Altera os dados do sistema pelos novos fornecidos através dos atributos $nome e $endpoint e $token
     */
    public function editar()
    {
        require_once '../dao/GatewayPagamentoDAO.php';
        $gatewayPagamentoDao = new GatewayPagamentoDAO();
        
        // Verifica se o token informado está ofuscado (possui apenas asteriscos ou é parcialmente ofuscado)
        if (strpos($this->token, '*') !== false) {
            // Não atualiza o token se ele estiver ofuscado
            $gatewayPagamentoDao->editarPorId($this->id, $this->nome, $this->endpoint, null);
        } else {
            // Token foi alterado, então atualiza normalmente
            $gatewayPagamentoDao->editarPorId($this->id, $this->nome, $this->endpoint, $this->token);
        }
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

        if ((!$statusLimpo || empty($statusLimpo)) && $statusLimpo != 0) {
            throw new InvalidArgumentException('O status de um gateway de pagamento não pode ser vazio.');
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set the value of token
     *
     * @return  self
     */
    public function setToken($token)
    {
        $tokenLimpo = trim($token);

        if (!$tokenLimpo || empty($tokenLimpo)) {
            throw new InvalidArgumentException('O token de um gateway de pagamento não pode ser vazio.');
        }

        $this->token = $token;

        return $this;
    }

    /**
     * Get the value of endpoint
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Set the value of endpoint
     *
     * @return  self
     */
    public function setEndpoint($endpoint)
    {
        $endpointLimpo = trim($endpoint);

        if (!$endpointLimpo || empty($endpointLimpo)) {
            throw new InvalidArgumentException('O endpoint de um gateway de pagamento não pode ser vazio.');
        }

        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * Get the value of nome
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * Set the value of nome
     *
     * @return  self
     */
    public function setNome($nome)
    {
        $nomeLimpo = trim($nome);

        if (!$nomeLimpo || empty($nomeLimpo)) {
            throw new InvalidArgumentException('O nome de um gateway de pagamento não pode ser vazio.');
        }
        $this->nome = $nome;

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

        if (!$idLimpo || $idLimpo < 1) {
            throw new InvalidArgumentException();
        }
        $this->id = $id;

        return $this;
    }
}
