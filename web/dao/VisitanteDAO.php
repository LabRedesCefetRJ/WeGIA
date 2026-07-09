<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once ROOT . "/dao/Conexao.php";
require_once ROOT . "/classes/Visitante.php";

class VisitanteDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        is_null($pdo) ? $this->pdo = Conexao::connect() : $this->pdo = $pdo;
    }

    public function incluir(Visitante $visitante, $cpf)
    {
        $this->pdo->beginTransaction();

        try {
            $sqlPessoa = "INSERT INTO pessoa (nome, sobrenome, cpf, sexo, telefone, data_nascimento, cep, estado, cidade, bairro, logradouro, numero_endereco, complemento, ibge, registro_geral, orgao_emissor, data_expedicao, nome_pai, nome_mae, tipo_sanguineo) VALUES (:nome, :sobrenome, :cpf, :sexo, :telefone, :data_nascimento, :cep, :estado, :cidade, :bairro, :logradouro, :numero_endereco, :complemento, :ibge, :registro_geral, :orgao_emissor, :data_expedicao, :nome_pai, :nome_mae, :tipo_sanguineo)";

            $stmtPessoa = $this->pdo->prepare($sqlPessoa);

            $nome = $visitante->getNome();
            $sobrenome = $visitante->getSobrenome();
            $sexo = $visitante->getSexo();
            $telefone = $visitante->getTelefone();
            $nascimento = $visitante->getDataNascimento();
            $cep = $visitante->getCep();
            $estado = $visitante->getEstado();
            $cidade = $visitante->getCidade();
            $bairro = $visitante->getBairro();
            $logradouro = $visitante->getLogradouro();
            $numeroEndereco = $visitante->getNumeroEndereco();
            $complemento = $visitante->getComplemento();
            $ibge = $visitante->getIbge();
            $rg = $visitante->getRegistroGeral();
            $orgaoEmissor = $visitante->getOrgaoEmissor();
            $dataExpedicao = $visitante->getDataExpedicao();
            $nomePai = $visitante->getNomePai();
            $nomeMae = $visitante->getNomeMae();
            $sangue = $visitante->getTipoSanguineo();

            $stmtPessoa->bindParam(':nome', $nome);
            $stmtPessoa->bindParam(':sobrenome', $sobrenome);
            $stmtPessoa->bindParam(':cpf', $cpf);
            $stmtPessoa->bindParam(':sexo', $sexo);
            $stmtPessoa->bindParam(':telefone', $telefone);
            $stmtPessoa->bindParam(':data_nascimento', $nascimento);
            $stmtPessoa->bindParam(':cep', $cep);
            $stmtPessoa->bindParam(':estado', $estado);
            $stmtPessoa->bindParam(':cidade', $cidade);
            $stmtPessoa->bindParam(':bairro', $bairro);
            $stmtPessoa->bindParam(':logradouro', $logradouro);
            $stmtPessoa->bindParam(':numero_endereco', $numeroEndereco);
            $stmtPessoa->bindParam(':complemento', $complemento);
            $stmtPessoa->bindParam(':ibge', $ibge);
            $stmtPessoa->bindParam(':registro_geral', $rg);
            $stmtPessoa->bindParam(':orgao_emissor', $orgaoEmissor);
            $stmtPessoa->bindParam(':data_expedicao', $dataExpedicao);
            $stmtPessoa->bindParam(':nome_pai', $nomePai);
            $stmtPessoa->bindParam(':nome_mae', $nomeMae);
            $stmtPessoa->bindParam(':tipo_sanguineo', $sangue);

            $stmtPessoa->execute();

            $idPessoa = $this->pdo->lastInsertId();

            $sqlVisitante = "INSERT INTO visitante (id_pessoa) VALUES (:id_pessoa)";
            $stmtVisitante = $this->pdo->prepare($sqlVisitante);
            $stmtVisitante->bindParam(':id_pessoa', $idPessoa);
            $stmtVisitante->execute();
            $idVisitante = $this->pdo->lastInsertId();

            $this->pdo->commit();
            return $idVisitante;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function incluir_existente(Visitante $visitante, $cpf)
    {
        $this->pdo->beginTransaction();

        try {
            $buscaPessoa = $this->pdo->prepare("SELECT id_pessoa FROM pessoa WHERE cpf = :cpf");
            $buscaPessoa->bindParam(':cpf', $cpf);
            $buscaPessoa->execute();
            $idPessoa = $buscaPessoa->fetchColumn();

            $sqlPessoa = "UPDATE pessoa SET nome=:nome, sobrenome=:sobrenome, sexo=:sexo, telefone=:telefone, data_nascimento=:data_nascimento, cep=:cep, estado=:estado, cidade=:cidade, bairro=:bairro, logradouro=:logradouro, numero_endereco=:numero_endereco, complemento=:complemento, ibge=:ibge, registro_geral=:registro_geral, orgao_emissor=:orgao_emissor, data_expedicao=:data_expedicao, nome_pai=:nome_pai, nome_mae=:nome_mae, tipo_sanguineo=:tipo_sanguineo WHERE id_pessoa=:id_pessoa";

            $stmtPessoa = $this->pdo->prepare($sqlPessoa);

            $nome = $visitante->getNome();
            $sobrenome = $visitante->getSobrenome();
            $sexo = $visitante->getSexo();
            $telefone = $visitante->getTelefone();
            $nascimento = $visitante->getDataNascimento();
            $cep = $visitante->getCep();
            $estado = $visitante->getEstado();
            $cidade = $visitante->getCidade();
            $bairro = $visitante->getBairro();
            $logradouro = $visitante->getLogradouro();
            $numeroEndereco = $visitante->getNumeroEndereco();
            $complemento = $visitante->getComplemento();
            $ibge = $visitante->getIbge();
            $rg = $visitante->getRegistroGeral();
            $orgaoEmissor = $visitante->getOrgaoEmissor();
            $dataExpedicao = $visitante->getDataExpedicao();
            $nomePai = $visitante->getNomePai();
            $nomeMae = $visitante->getNomeMae();
            $sangue = $visitante->getTipoSanguineo();

            $stmtPessoa->bindParam(':nome', $nome);
            $stmtPessoa->bindParam(':sobrenome', $sobrenome);
            $stmtPessoa->bindParam(':sexo', $sexo);
            $stmtPessoa->bindParam(':telefone', $telefone);
            $stmtPessoa->bindParam(':data_nascimento', $nascimento);
            $stmtPessoa->bindParam(':cep', $cep);
            $stmtPessoa->bindParam(':estado', $estado);
            $stmtPessoa->bindParam(':cidade', $cidade);
            $stmtPessoa->bindParam(':bairro', $bairro);
            $stmtPessoa->bindParam(':logradouro', $logradouro);
            $stmtPessoa->bindParam(':numero_endereco', $numeroEndereco);
            $stmtPessoa->bindParam(':complemento', $complemento);
            $stmtPessoa->bindParam(':ibge', $ibge);
            $stmtPessoa->bindParam(':registro_geral', $rg);
            $stmtPessoa->bindParam(':orgao_emissor', $orgaoEmissor);
            $stmtPessoa->bindParam(':data_expedicao', $dataExpedicao);
            $stmtPessoa->bindParam(':nome_pai', $nomePai);
            $stmtPessoa->bindParam(':nome_mae', $nomeMae);
            $stmtPessoa->bindParam(':tipo_sanguineo', $sangue);
            $stmtPessoa->bindParam(':id_pessoa', $idPessoa);

            $stmtPessoa->execute();

            $sqlVisitante = "INSERT INTO visitante (id_pessoa) VALUES (:id_pessoa)";
            $stmtVisitante = $this->pdo->prepare($sqlVisitante);
            $stmtVisitante->bindParam(':id_pessoa', $idPessoa);
            $stmtVisitante->execute();
            $idVisitante = $this->pdo->lastInsertId();

            $this->pdo->commit();
            return $idVisitante;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function selecionarCadastro(string $cpf)
    {
        try {
            $cpf = filter_var($cpf, FILTER_SANITIZE_SPECIAL_CHARS);
            var_dump($cpf);
            $stmt = $this->pdo->prepare("SELECT v.id_visitante FROM visitante v JOIN pessoa p on v.id_pessoa=p.id_pessoa WHERE p.cpf = :cpf");
            $stmt->bindValue(':cpf', $cpf, PDO::PARAM_STR);
            $stmt->execute();

            $consultaVisitante = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$consultaVisitante) {
                // Not a visitante yet, check if exists as person
                $stmtCheckPessoa = $this->pdo->prepare("SELECT id_pessoa FROM pessoa WHERE cpf = :cpf");
                $stmtCheckPessoa->bindValue(':cpf', $cpf, PDO::PARAM_STR);
                $stmtCheckPessoa->execute();
                $pessoa = $stmtCheckPessoa->fetch(PDO::FETCH_ASSOC);

                if ($pessoa) {
                    return 'PESSOA_EXISTENTE';
                } else {
                    return 'NOVO_CADASTRO';
                }
            } else {
                return "VISITANTE_EXISTENTE";
            }
        } catch (PDOException $e) {
            throw $e;
        }
    }
}