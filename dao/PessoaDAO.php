<?php

use function PHPSTORM_META\type;

require_once dirname(__FILE__) . '/Conexao.php';
require_once dirname(__FILE__, 2) . '/classes/PessoaDTOSocio.php';

class PessoaDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo = null)
    {
        if (is_null($pdo)) {
            $this->pdo = Conexao::connect();
        } else {
            $this->pdo = $pdo;
        }
    }

    /**
     * Verifica se existe uma pessoa com o CPF equivalente ao informado cadastrada no sistema
     * @return Pessoa em caso positivo
     * @return null em caso negativo
     */
    public function verificarExistencia(string $cpf): PessoaDTOSocio|null
    {

        $sql = "SELECT * FROM pessoa WHERE cpf=:cpf";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->execute();

        if ($stmt->rowCount() < 1) {
            return null;
        }

        $pessoaArray = $stmt->fetch(PDO::FETCH_ASSOC);
        $pessoa = new PessoaDTOSocio(
            $pessoaArray['cpf'],
            $pessoaArray['nome'],
            $pessoaArray['sobrenome'],
            $pessoaArray['sexo'],
            $pessoaArray['data_nascimento'],
            $pessoaArray['registro_geral'],
            $pessoaArray['orgao_emissor'],
            $pessoaArray['data_expedicao'],
            $pessoaArray['nome_mae'],
            $pessoaArray['nome_pai'],
            $pessoaArray['tipo_sanguineo'],
            null,
            $pessoaArray['telefone'],
            null,
            $pessoaArray['cep'],
            $pessoaArray['estado'],
            $pessoaArray['cidade'],
            $pessoaArray['bairro'],
            $pessoaArray['logradouro'],
            $pessoaArray['numero_endereco'],
            $pessoaArray['complemento'],
            $pessoaArray['ibge']
        );

        $pessoa->setIdpessoa($pessoaArray['id_pessoa']);

        return $pessoa;
    }


public function inserirPessoa($cpf, $nome, $sobrenome, $telefone = null, $cep = null, $rua = null, $bairro = null, $cidade = null, $uf = null, $numero = null, $complemento = null, $ibge = null, $sexo = null, $dataNascimento = null) 
{
    $sql = "INSERT INTO pessoa (cpf, nome, sobrenome, telefone, cep, logradouro, bairro, cidade, estado, numero_endereco, complemento, ibge, sexo, data_nascimento) 
            VALUES (:cpf, :nome, :sobrenome, :telefone, :cep, :rua, :bairro, :cidade, :uf, :numero, :complemento, :ibge, :sexo, :dataNascimento)";
    
    $stmt = $this->pdo->prepare($sql);
    
    $stmt->bindValue(':cpf', $cpf);
    $stmt->bindValue(':nome', $nome);
    $stmt->bindValue(':sobrenome', $sobrenome);
    $stmt->bindValue(':telefone', $telefone);
    $stmt->bindValue(':cep', $cep);
    $stmt->bindValue(':rua', $rua);
    $stmt->bindValue(':bairro', $bairro); 
    $stmt->bindValue(':cidade', $cidade);
    $stmt->bindValue(':uf', $uf);
    $stmt->bindValue(':numero', $numero);
    $stmt->bindValue(':complemento', $complemento);
    $stmt->bindValue(':ibge', $ibge);
    $stmt->bindValue(':sexo', $sexo);
    $stmt->bindValue(':dataNascimento', $dataNascimento);

    if ($stmt->execute()) {
        return $this->pdo->lastInsertId();
    }
    
    throw new Exception("Erro ao inserir pessoa.");
}
}
