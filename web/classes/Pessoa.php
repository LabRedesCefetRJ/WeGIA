<?php
abstract class Pessoa
{

    private $idpessoa;

    private $cpf;

    private $senha;

    private $nome;

    private $sobrenome;

    private $sexo;

    private $telefone;

    private $dataNascimento;

    private $imagem;

    private $cep;
    
    private $estado;

    private $cidade;

    private $bairro;

    private $logradouro;

    private $numeroEndereco;

    private $complemento;

    private $ibge;

    private $registroGeral;

    private $orgaoEmissor;

    private $dataExpedicao;

    private $nomeMae;

    private $nomePai;
    
    private $tipoSanguineo;

    public function __construct($cpf,$nome,$sobrenome,$sexo,$dataNascimento,$registroGeral,$orgaoEmissor,$dataExpedicao,$nomeMae,$nomePai,$tipoSanguineo,$senha,$telefone,$imagem,$cep,$estado,$cidade,$bairro,$logradouro,$numeroEndereco,$complemento,$ibge)
    {
        $this->cpf=$cpf;
        $this->nome=$nome;
        $this->sobrenome=$sobrenome;
        $this->sexo=$sexo;
        $this->dataNascimento=$dataNascimento;
        $this->registroGeral=$registroGeral;
        $this->orgaoEmissor=$orgaoEmissor;
        $this->dataExpedicao=$dataExpedicao;
        $this->nomeMae=$nomeMae;
        $this->nomePai=$nomePai;
        $this->tipoSanguineo=$tipoSanguineo;
        $this->senha=$senha;
        $this->telefone=$telefone;
        $this->imagem=$imagem;
        $this->cep=$cep;
        $this->estado=$estado;
        $this->cidade=$cidade;
        $this->bairro=$bairro;
        $this->logradouro=$logradouro;
        $this->numeroEndereco=$numeroEndereco;
        $this->complemento=$complemento;
        $this->ibge=$ibge;
    }
    
    public function getEstado()
    {
        return $this->estado;
    }

    public function setEstado($estado)
    {
        $this->estado=$estado;
    }
    
    public function getIbge()
    {
        return $this->ibge;
    }
    public function setIbge($ibge)
    {
        $this->ibge=$ibge;
    }
    public function getIdpessoa()
    {
        return $this->idpessoa;
    }

    public function getCpf()
    {
        return $this->cpf;
    }

    public function getSenha()
    {
        return $this->senha;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function getSobrenome()
    {
        return $this->sobrenome;
    }

    public function getSexo()
    {
        return $this->sexo;
    }

    public function getTelefone()
    {
        return $this->telefone;
    }

    public function getDataNascimento()
    {
        return $this->dataNascimento;
    }

    public function getImagem()
    {
        return $this->imagem;
    }

    public function getCep()
    {
        return $this->cep;
    }

    public function getCidade()
    {
        return $this->cidade;
    }

    public function getBairro()
    {
        return $this->bairro;
    }

    public function getLogradouro()
    {
        return $this->logradouro;
    }

    public function getNumeroEndereco()
    {
        return $this->numeroEndereco;
    }

    public function getComplemento()
    {
        return $this->complemento;
    }

    public function getRegistroGeral()
    {
        return $this->registroGeral;
    }

    public function getOrgaoEmissor()
    {
        return $this->orgaoEmissor;
    }

    public function getDataExpedicao()
    {
        return $this->dataExpedicao;
    }

    public function getNomeMae()
    {
        return $this->nomeMae;
    }

    public function getNomePai()
    {
        return $this->nomePai;
    }

    public function getTipoSanguineo()
    {
        return $this->tipoSanguineo;
    }

    public function setIdpessoa($idpessoa)
    {
        $this->idpessoa = $idpessoa;
    }

    public function setCpf($cpf)
    {
        $this->cpf = $cpf;
    }

    public function setSenha($senha)
    {
        $this->senha = $senha;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function setSobrenome($sobrenome)
    {
        $this->sobrenome = $sobrenome;
    }

    public function setSexo($sexo)
    {
        $this->sexo = $sexo;
    }

    public function setTelefone($telefone)
    {
        $this->telefone = $telefone;
    }

    public function setDataNascimento($dataNascimento)
    {
        $this->dataNascimento = $dataNascimento;
    }

    public function setImagem($imagem)
    {
        $this->imagem = $imagem;
    }

    public function setCep($cep)
    {
        $this->cep = $cep;
    }

    public function setCidade($cidade)
    {
        $this->cidade = $cidade;
    }

    public function setBairro($bairro)
    {
        $this->bairro = $bairro;
    }

    public function setLogradouro($logradouro)
    {
        $this->logradouro = $logradouro;
    }

    public function setNumeroEndereco($numeroEndereco)
    {
        $this->numeroEndereco = $numeroEndereco;
    }

    public function setComplemento($complemento)
    {
        $this->complemento = $complemento;
    }

    public function setRegistroGeral($registroGeral)
    {
        $this->registroGeral = $registroGeral;
    }

    public function setOrgaoEmissor($orgaoEmissor)
    {
        $this->orgaoEmissor = $orgaoEmissor;
    }

    public function setDataExpedicao($dataExpedicao)
    {
        $this->dataExpedicao = $dataExpedicao;
    }

    public function setNomeMae($nomeMae)
    {
        $this->nomeMae = $nomeMae;
    }

    public function setNomePai($nomePai)
    {
        $this->nomePai = $nomePai;
    }

    public function setTipoSanguineo($tipoSanguineo)
    {
        $this->tipoSanguineo = $tipoSanguineo;
    }
}