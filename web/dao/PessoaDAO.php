<?php

use function PHPSTORM_META\type;

require_once dirname(__FILE__) . '/Conexao.php';
require_once dirname(__FILE__, 2) . '/classes/PessoaDTOSocio.php';
require_once dirname(__FILE__, 2) . '/classes/Util.php';

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
        $cpfDigits = preg_replace('/\D+/', '', $cpf);

        $sql = "SELECT * FROM pessoa WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = :cpf";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':cpf', $cpfDigits);
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
            $pessoaArray['email'],
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


    public function inserirPessoa($cpf, $nome, $sobrenome, $email = null, $telefone = null, $cep = null, $rua = null, $bairro = null, $cidade = null, $uf = null, $numero = null, $complemento = null, $ibge = null, $sexo = null, $dataNascimento = null)
    {
        Util::validarNomePessoaOuLancar($nome, 'nome', 400);
        Util::validarNomePessoaOuLancar($sobrenome, 'sobrenome', 400);

        $sql = "INSERT INTO pessoa (cpf, nome, sobrenome, email, telefone, cep, logradouro, bairro, cidade, estado, numero_endereco, complemento, ibge, sexo, data_nascimento) 
            VALUES (:cpf, :nome, :sobrenome, :email, :telefone, :cep, :rua, :bairro, :cidade, :uf, :numero, :complemento, :ibge, :sexo, :dataNascimento)";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':cpf', $cpf);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':sobrenome', $sobrenome);
        $stmt->bindValue(':email', $email);
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

    public function buscarPessoaPorId(int $id_pessoa): ?array
    {
        $sql = "SELECT * FROM pessoa WHERE id_pessoa = :id_pessoa";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_pessoa', $id_pessoa, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : null;
    }

    public function atualizarPessoa(int $id_pessoa, array $dados): bool
    {
        if (isset($dados['nome'])) {
            Util::validarNomePessoaOuLancar($dados['nome'], 'nome', 400);
        }

        if (isset($dados['sobrenome'])) {
            Util::validarNomePessoaOuLancar($dados['sobrenome'], 'sobrenome', 400);
        }

        $setParts = [];
        $params = [];

        foreach ($dados as $key => $value) {
            $setParts[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        if (empty($setParts)) {
            return false;
        }

        $params[':id_pessoa'] = $id_pessoa;
        $sql = "UPDATE pessoa SET " . implode(', ', $setParts) . " WHERE id_pessoa = :id_pessoa";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function buscarPessoasComCargo(): array
    {
        $sql = '
            SELECT p.nome, p.id_pessoa as id_pessoa, c.cargo as nome_cargo 
            FROM pessoa p 
            JOIN funcionario f ON f.id_pessoa = p.id_pessoa 
            JOIN cargo c ON f.id_cargo = c.id_cargo
            UNION
            SELECT p.nome, p.id_pessoa as id_pessoa, c.cargo as nome_cargo 
            FROM pessoa p 
            JOIN voluntario v ON v.id_pessoa = p.id_pessoa 
            JOIN cargo c ON v.id_cargo = c.id_cargo
        ';

        $query = $this->pdo->query($sql);
        $pessoas = $query->fetchAll(PDO::FETCH_ASSOC);
        return $pessoas;
    }

    public function verificaAdmConfigurado(int $id_pessoa): int
    {
        $stmt = $this->pdo->prepare('SELECT adm_configurado FROM pessoa WHERE id_pessoa=:idPessoa');
        $stmt->bindValue(':idPessoa', $id_pessoa, PDO::PARAM_INT);
        $stmt->execute();
        $adm_configurado = $stmt->fetch(PDO::FETCH_ASSOC)['adm_configurado'];
        return $adm_configurado;
    }

    public function getAlvoVoluntario(int $idVoluntario): array
    {
        $stmtAlvo = $this->pdo->prepare('SELECT p.id_pessoa, p.adm_configurado FROM pessoa p JOIN voluntario v ON p.id_pessoa = v.id_pessoa WHERE v.id_voluntario = :idVoluntario');
        $stmtAlvo->bindValue(':idVoluntario', $idVoluntario, PDO::PARAM_INT);
        $stmtAlvo->execute();
        $alvo = $stmtAlvo->fetch(PDO::FETCH_ASSOC);
        return $alvo;
    }
}
