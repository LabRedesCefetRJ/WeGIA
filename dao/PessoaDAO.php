<?php
require_once dirname(__FILE__).'/Conexao.php';
require_once dirname(__FILE__, 2).'/classes/PessoaDTOSocio.php';

class PessoaDAO{
    private PDO $pdo;

    public function __construct(PDO $pdo = null)
    {
        if(is_null($pdo)){
            $this->pdo = Conexao::connect();
        }else{
            $this->pdo = $pdo;
        }
    }

    /**
     * Verifica se existe uma pessoa com o CPF equivalente ao informado cadastrada no sistema
     * @return Pessoa em caso positivo
     * @return null em caso negativo
     */
    public function verificarExistencia(string $cpf):PessoaDTOSocio|null{
        
        $sql = "SELECT * FROM pessoa WHERE cpf=:cpf";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->execute();

        if($stmt->rowCount() < 1){
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


    public function inserirPessoa(string $cpf, string $nome, string $sobrenome): int
{
    $sql = "INSERT INTO pessoa (cpf, nome, sobrenome) VALUES (:cpf, :nome, :sobrenome)";
    $stmt = $this->pdo->prepare($sql);

    $stmt->bindParam(':cpf', $cpf);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':sobrenome', $sobrenome);

    if (!$stmt->execute()) {
        throw new PDOException("Erro ao inserir pessoa no banco.");
    }

    return (int)$this->pdo->lastInsertId();
}


}