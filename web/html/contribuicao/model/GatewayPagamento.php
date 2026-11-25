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

    public function __construct(string $nome, string $endpoint, string $privateToken, string $publicToken, $status = null, ?int $id = null)
    {
        $this->setNome($nome)->setEndpoint($endpoint)->setPrivateToken($privateToken)->setPublicToken($publicToken);
        if (!$status) {
            $this->setStatus(0);
        } else {
            $this->setStatus($status);
        }

        if ($id) {
            $this->setId($id);
        }
    }

    /**
     * Pega os atributos nome, endpoint, token e status e realiza os procedimentos necessários
     * para inserir um Gateway de pagamento no sistema
     */
    public function cadastrar()
    {
        // Ao contrário de editar(), aqui o token vazio nunca é válido —
        // não existe "manter o atual" pra um gateway que ainda não existe
        if (trim((string) $this->privateToken) === '') {
            throw new InvalidArgumentException('O token de um gateway de pagamento não pode ser vazio.');
        }

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

        // Verifica se o token não foi reinformado: veio vazio (campo agora só
        // mostra o valor mascarado como placeholder, não como texto — ver
        // gatewayPagamento.js) ou é exatamente a versão mascarada do token
        // já salvo (defesa contra reenvio do valor mostrado na tabela, ex:
        // uma chamada direta à API em vez de pelo formulário). Comparar
        // contra a máscara real do token atual — em vez de só checar se tem
        // "*" — evita falso positivo num token de verdade que contenha "*".
        $tokenAtual = $gatewayPagamentoDao->buscarTokenPorId($this->id);
        $tokenAtualMascarado = $tokenAtual !== null && $tokenAtual !== '' ? self::ofuscarToken($tokenAtual) : null;

        if (trim((string) $this->privateToken) === '' || ($tokenAtualMascarado !== null && $this->privateToken === $tokenAtualMascarado)) {
            // Token não foi reinformado. Se o endpoint
            // estiver mudando mesmo assim, recusa: sem essa checagem, dava pra
            // redirecionar as cobranças pra um servidor de terceiros mantendo
            // a credencial real intacta no banco — o token real seguiria
            // sendo enviado, agora pro host do atacante.
            $endpointAtual = $gatewayPagamentoDao->buscarEndpointPorId($this->id);

            if ($endpointAtual !== null && $endpointAtual !== $this->endpoint) {
                throw new InvalidArgumentException(
                    'Para alterar o endpoint do gateway é necessário reinformar o Token API — ele não pode continuar ofuscado.'
                );
            }

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
        // Vazio é um valor válido aqui: editar() usa isso pra decidir "não
        // reinformou o token, manter o atual". Quem precisa exigir um token
        // privado não vazio (cadastrar()) valida isso na hora certa.
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

        // Exige URL válida em HTTPS: sem isso, um endpoint como
        // "http://attacker.tld/collect" seria aceito, e a credencial do
        // gateway (token) trafegaria em texto claro pro atacante.
        if (!filter_var($endpointLimpo, FILTER_VALIDATE_URL) || stripos($endpointLimpo, 'https://') !== 0) {
            throw new InvalidArgumentException('O endpoint do gateway deve ser uma URL HTTPS válida.');
        }

        $this->endpoint = $endpointLimpo;

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

    /**
     * Oculta o meio do token, deixando só as pontas visíveis — usado pra
     * exibição (view/gateway_pagamento.php) e por editar() pra comparar
     * contra o token atual e detectar se ele foi de fato reinformado.
     */
    public static function ofuscarToken(string $token, int $visivelInicio = 3, int $visivelFim = 3, float $percentualMaxVisivel = 0.3): string
    {
        $tamanho = strlen($token);

        // Número máximo total de caracteres visíveis com base na porcentagem
        $maxVisiveis = floor($tamanho * $percentualMaxVisivel);

        // Garante que o total de visíveis não ultrapasse o permitido
        $totalVisiveis = $visivelInicio + $visivelFim;
        if ($totalVisiveis > $maxVisiveis) {
            // Divide o número máximo permitido entre início e fim
            $visivelInicio = floor($maxVisiveis / 2);
            $visivelFim = $maxVisiveis - $visivelInicio;
        }

        // Se ainda assim for muito curto, oculta completamente
        if ($visivelInicio + $visivelFim >= $tamanho) {
            return str_repeat('*', $tamanho);
        }

        $inicio = substr($token, 0, $visivelInicio);
        $fim = substr($token, -$visivelFim);
        $meio = str_repeat('*', $tamanho - $visivelInicio - $visivelFim);

        return $inicio . $meio . $fim;
    }
}
