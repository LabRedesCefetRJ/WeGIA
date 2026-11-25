<?php
require_once ROOT . '/classes/Util.php';
require_once __DIR__ . '/OrigemValidacaoException.php';

class Origem
{
    private $id_origem;
    private $nome;
    private $cnpj;
    private $cpf;
    private $telefone;
    private $inscricao_estadual;
    private $email;
    private $cep;
    private $logradouro;
    private $numero;
    private $complemento;
    private $bairro;
    private $cidade;
    private $uf;
    private $condicao_pagamento;
    private $vencimentos_pagamento;


    public function __construct($nome, $cnpj, $cpf = null, $telefone = null, array $dados = [])
    {
        $this->nome = $nome;
        $this->cnpj = $cnpj;

        if (isset($cpf))
            $this->cpf = $cpf;

        $this->telefone = $telefone;
        $this->inscricao_estadual = $dados['inscricao_estadual'] ?? null;
        $this->email = $dados['email'] ?? null;
        $this->cep = $dados['cep'] ?? null;
        $this->logradouro = $dados['logradouro'] ?? null;
        $this->numero = $dados['numero'] ?? null;
        $this->complemento = $dados['complemento'] ?? null;
        $this->bairro = $dados['bairro'] ?? null;
        $this->cidade = $dados['cidade'] ?? null;
        $this->uf = $dados['uf'] ?? null;
        $this->condicao_pagamento = $dados['condicao_pagamento'] ?? null;
        $this->vencimentos_pagamento = $dados['vencimentos_pagamento'] ?? '';

    }

    /** Normaliza as condições habituais de pagamento da origem. */
    public static function validarCondicoesPagamento(array $entrada): array
    {
        $dados = [];
        $campo = 'condicao_pagamento';
        $condicao = $entrada['condicao_pagamento'] ?? '';
        if (!is_string($condicao) || !in_array($condicao, ['', 'a_vista', 'a_prazo', 'parcelado'], true)) {
            throw new OrigemValidacaoException($campo, 'Selecione uma condição de pagamento válida.');
        }
        $dados['condicao_pagamento'] = $condicao === '' ? null : $condicao;
        $mapa = ['' => 'avista', 'a_vista' => 'avista', 'a_prazo' => 'aprazo', 'parcelado' => 'parcelado'];
        try {
            $dados['vencimentos_pagamento'] = Util::validarVencimentosPagamento(
                $mapa[$condicao], $entrada['vencimentos_pagamento'] ?? ''
            );
        } catch (InvalidArgumentException $e) {
            throw new OrigemValidacaoException('vencimentos_pagamento', $e->getMessage());
        }
        return $dados;
    }

    public function getId_origem()
    {
        return $this->id_origem;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function getCnpj()
    {
        return $this->cnpj;
    }

    public function getCpf()
    {
        return $this->cpf;
    }

    public function getTelefone()
    {
        return $this->telefone;
    }

    public function setId_origem($id_origem)
    {
        $this->id_origem = $id_origem;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function setCnpj($cnpj)
    {
        // Validar se o CNPJ possui um formato válido - Xablau
        if (Util::validaCnpj($cnpj)) {
            $this->cnpj = $cnpj;
        } else {
            throw new Exception('CNPJ inválido');
        }
    }

    public function setCpf($cpf)
    {
        if (Util::validarCPF($cpf)) {
            $this->cpf = $cpf;
        } else {
            throw new Exception('CPF inválido');
        }
    }

    public function setTelefone($telefone)
    {
        $this->telefone = $telefone;
    }
    public function getInscricao_estadual()
    {
        return $this->inscricao_estadual;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getCep()
    {
        return $this->cep;
    }

    public function getLogradouro()
    {
        return $this->logradouro;
    }

    public function getNumero()
    {
        return $this->numero;
    }

    public function getComplemento()
    {
        return $this->complemento;
    }

    public function getBairro()
    {
        return $this->bairro;
    }

    public function getCidade()
    {
        return $this->cidade;
    }

    public function getCondicaoPagamento() { return $this->condicao_pagamento; }

    public function getVencimentosPagamento() { return $this->vencimentos_pagamento; }

    public function getUf()
    {
        return $this->uf;
    }

}
