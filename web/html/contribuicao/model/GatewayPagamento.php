<?php
class GatewayPagamento
{

    //atributos
    private int $id;
    private string $nome;
    private string $endpoint;
    private string $privateToken;
    private string $publicToken;
    private $status;

    public function __construct(string $nome, string $endpoint, string $privateToken, string $publicToken, $status = null)
    {
        $this->setNome($nome)->setEndpoint($endpoint)->setPrivateToken($privateToken)->setPublicToken($publicToken);
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
        $gatewayPagamentoDao->cadastrar($this->nome, $this->endpoint, $this->privateToken, $this->publicToken, $this->status);
    }

    /**
     * Altera os dados do sistema pelos novos fornecidos através dos atributos $nome e $endpoint e $token
     */
    public function editar()
    {
        require_once '../dao/GatewayPagamentoDAO.php';
        $gatewayPagamentoDao = new GatewayPagamentoDAO();
        
        // Verifica se o token informado está ofuscado (possui apenas asteriscos ou é parcialmente ofuscado)
        if (strpos($this->privateToken, '*') !== false) {
            // Não atualiza o token se ele estiver ofuscado
            $gatewayPagamentoDao->editarPorId($this->id, $this->nome, $this->endpoint, null, $this->publicToken);
        } else {
            // Token foi alterado, então atualiza normalmente
            $gatewayPagamentoDao->editarPorId($this->id, $this->nome, $this->endpoint, $this->privateToken, $this->publicToken);
        }
    }

    /**
     * Retorna os dados públicos do gateway de pagamento
     */
    public function getPublicData()
    {
        return [
            'id' => $this->id,
            'description' => $this->nome,
            'endpoint' => $this->endpoint,
            'publicToken' => $this->publicToken,
            'status' => $this->status
        ];
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
    public function getPrivateToken()
    {
        return $this->privateToken;
    }

    /**
     * Set the value of token
     *
     * @return  self
     */
    public function setPrivateToken($token)
    {
        $tokenLimpo = trim($token);

        if (!$tokenLimpo || empty($tokenLimpo)) {
            throw new InvalidArgumentException('O token de um gateway de pagamento não pode ser vazio.');
        }

        $this->privateToken = $token;

        return $this;
    }

    /**
     * Get the value of token
     */
    public function getPublicToken()
    {
        return $this->publicToken;
    }

    /**
     * Set the value of token
     *
     * @return  self
     */
    public function setPublicToken($token)
    {
        $tokenLimpo = trim($token);

        if (!$tokenLimpo || empty($tokenLimpo)) {
            throw new InvalidArgumentException('O token de um gateway de pagamento não pode ser vazio.');
        }

        $this->publicToken = $token;

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
