<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DependenteDTO.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Util.php';

class Dependente
{
    private int $id;
    private string $nome;
    private string $sobrenome;
    private string $sexo;
    private DateTime $nascimento;
    private ?string $telefone = null;
    private ?string $nomePai = null;
    private ?string $nomeMae = null;

    public function __construct(DependenteDTO $dto)
    {
        //dados obrigatórios
        $this->setNome($dto->nome)->setSobrenome($dto->sobrenome)->setSexo($dto->sexo)->setDataNascimento($dto->nascimento);

        //dados opcionais
        if(isset($dto->id))
            $this->setId($dto->id);

        if(isset($dto->telefone))
            $this->setTelefone($dto->telefone);

        if(isset($dto->nomePai))
            $this->setNomePai($dto->nomePai);

        if(isset($dto->nomeMae))
            $this->setNomeMae($dto->nomeMae);
    }

    public function setId(int $id)
    {
        if ($id < 1)
            throw new InvalidArgumentException('O id de um dependente deve ser um inteiro maior ou igual a 1.', 412);

        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setNome(string $nome)
    {
        if (strlen($nome) < 2)
            throw new InvalidArgumentException('Nome deve ter pelo menos 2 caracteres.', 412);

        $this->nome = $nome;
        return $this;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setSobrenome(string $sobrenome)
    {
        if (strlen($sobrenome) < 2)
            throw new InvalidArgumentException('Sobrenome deve ter pelo menos 2 caracteres.', 412);

        $this->sobrenome = $sobrenome;
        return $this;
    }

    public function getSobrenome()
    {
        return $this->sobrenome;
    }

    public function setSexo(string $sexo)
    {
        $genders = ['m', 'f'];

        if (!in_array($sexo, $genders))
            throw new InvalidArgumentException('O sexo informado não é válido.', 412);

        $this->sexo = $sexo;
        return $this;
    }

    public function getSexo()
    {
        return $this->sexo;
    }

    public function setDataNascimento(DateTime $data)
    {
        $hoje = new DateTime('today');
        $minima = new DateTime('1900-01-01');

        if ($data > $hoje)
            throw new InvalidArgumentException('A data de nascimento não pode ser no futuro.', 412);

        //verificar se não é menor que 1900-01-01
        if ($data < $minima)
            throw new InvalidArgumentException('A data de nascimento não pode ser anterior ao ano de 1900.', 412);

        $this->nascimento = $data;
        return $this;
    }

    public function getDataNascimento()
    {
        return $this->nascimento;
    }

    public function setTelefone(string $telefone)
    {
        $telefoneValidado = Util::validarTelefone($telefone);

        if ($telefoneValidado === false)
            throw new InvalidArgumentException('O telefone informado não está em um formato válido.', 412);

        $this->telefone = $telefoneValidado;
        return $this;
    }

    public function getTelefone(){
        return $this->telefone;
    }

    public function setNomePai(string $nome)
    {
        if (strlen($nome) < 2)
            throw new InvalidArgumentException('Nome do pai deve ter pelo menos 2 caracteres.', 412);

        $this->nomePai = $nome;
        return $this;
    }

    public function getNomePai()
    {
        return $this->nomePai;
    }

    public function setNomeMae(string $nome)
    {
        if (strlen($nome) < 2)
            throw new InvalidArgumentException('Nome da mãe deve ter pelo menos 2 caracteres.', 412);

        $this->nomeMae = $nome;
        return $this;
    }

    public function getNomeMae()
    {
        return $this->nomeMae;
    }
}
