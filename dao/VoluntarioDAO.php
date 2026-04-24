<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once ROOT . "/dao/Conexao.php";
require_once ROOT . "/classes/Voluntario.php";

class VoluntarioDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        is_null($pdo) ? $this->pdo = Conexao::connect() : $this->pdo = $pdo;
    }

    public function incluir(Voluntario $voluntario, $cpf)
    {
        $this->pdo->beginTransaction();

        try {
            // Verifica se a pessoa já existe
            $buscaPessoa = $this->pdo->prepare("SELECT id_pessoa FROM pessoa WHERE cpf = :cpf");
            $buscaPessoa->bindParam(':cpf', $cpf);
            $buscaPessoa->execute();
            $idPessoa = $buscaPessoa->fetchColumn();

            if (!$idPessoa) {
                $sqlPessoa = "INSERT INTO pessoa (nome, sobrenome, cpf, sexo, telefone, data_nascimento, cep, estado, cidade, bairro, logradouro, numero_endereco, complemento, ibge, registro_geral, orgao_emissor, data_expedicao, nome_pai, nome_mae, tipo_sanguineo) VALUES (:nome, :sobrenome, :cpf, :sexo, :telefone, :data_nascimento, :cep, :estado, :cidade, :bairro, :logradouro, :numero_endereco, :complemento, :ibge, :registro_geral, :orgao_emissor, :data_expedicao, :nome_pai, :nome_mae, :tipo_sanguineo)";

                $stmtPessoa = $this->pdo->prepare($sqlPessoa);

                $nome = $voluntario->getNome();
                $sobrenome = $voluntario->getSobrenome();
                $sexo = $voluntario->getSexo();
                $telefone = $voluntario->getTelefone();
                $nascimento = $voluntario->getDataNascimento();
                $cep = $voluntario->getCep();
                $estado = $voluntario->getEstado();
                $cidade = $voluntario->getCidade();
                $bairro = $voluntario->getBairro();
                $logradouro = $voluntario->getLogradouro();
                $numeroEndereco = $voluntario->getNumeroEndereco();
                $complemento = $voluntario->getComplemento();
                $ibge = $voluntario->getIbge();
                $rg = $voluntario->getRegistroGeral();
                $orgaoEmissor = $voluntario->getOrgaoEmissor();
                $dataExpedicao = $voluntario->getDataExpedicao();
                $nomePai = $voluntario->getNomePai();
                $nomeMae = $voluntario->getNomeMae();
                $sangue = $voluntario->getTipoSanguineo();

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
            }
            else {
                // Atualiza pessoa existente
                $sqlPessoa = "UPDATE pessoa SET nome=:nome, sobrenome=:sobrenome, sexo=:sexo, telefone=:telefone, data_nascimento=:data_nascimento, cep=:cep, estado=:estado, cidade=:cidade, bairro=:bairro, logradouro=:logradouro, numero_endereco=:numero_endereco, complemento=:complemento, ibge=:ibge, registro_geral=:registro_geral, orgao_emissor=:orgao_emissor, data_expedicao=:data_expedicao, nome_pai=:nome_pai, nome_mae=:nome_mae, tipo_sanguineo=:tipo_sanguineo WHERE id_pessoa=:id_pessoa";

                $stmtPessoa = $this->pdo->prepare($sqlPessoa);

                $nome = $voluntario->getNome();
                $sobrenome = $voluntario->getSobrenome();
                $sexo = $voluntario->getSexo();
                $telefone = $voluntario->getTelefone();
                $nascimento = $voluntario->getDataNascimento();
                $cep = $voluntario->getCep();
                $estado = $voluntario->getEstado();
                $cidade = $voluntario->getCidade();
                $bairro = $voluntario->getBairro();
                $logradouro = $voluntario->getLogradouro();
                $numeroEndereco = $voluntario->getNumeroEndereco();
                $complemento = $voluntario->getComplemento();
                $ibge = $voluntario->getIbge();
                $rg = $voluntario->getRegistroGeral();
                $orgaoEmissor = $voluntario->getOrgaoEmissor();
                $dataExpedicao = $voluntario->getDataExpedicao();
                $nomePai = $voluntario->getNomePai();
                $nomeMae = $voluntario->getNomeMae();
                $sangue = $voluntario->getTipoSanguineo();

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
            }

            $sqlVoluntario = "INSERT INTO voluntario (id_pessoa, id_situacao, data_admissao) VALUES (:id_pessoa, :id_situacao, :data_admissao)";
            $stmtVoluntario = $this->pdo->prepare($sqlVoluntario);

            $situacao = $voluntario->getId_situacao();
            $dataAdmissao = $voluntario->getData_admissao();

            $stmtVoluntario->bindParam(':id_pessoa', $idPessoa);
            $stmtVoluntario->bindParam(':id_situacao', $situacao);
            $stmtVoluntario->bindParam(':data_admissao', $dataAdmissao);

            $stmtVoluntario->execute();
            $idVoluntario = $this->pdo->lastInsertId();

            $this->pdo->commit();
            return $idVoluntario;

        }
        catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function incluirExistente($cpf, $situacao, $data_admissao)
    {
        $this->pdo->beginTransaction();

        try {
            $buscaPessoa = $this->pdo->prepare("SELECT id_pessoa FROM pessoa WHERE cpf = :cpf");
            $buscaPessoa->bindParam(':cpf', $cpf);
            $buscaPessoa->execute();
            $idPessoa = $buscaPessoa->fetchColumn();

            if (!$idPessoa) {
                throw new PDOException('Pessoa não encontrada.');
            }

            $sqlVoluntario = "INSERT INTO voluntario (id_pessoa, id_situacao, data_admissao) VALUES (:id_pessoa, :id_situacao, :data_admissao)";
            $stmtVoluntario = $this->pdo->prepare($sqlVoluntario);
            $stmtVoluntario->bindParam(':id_pessoa', $idPessoa);
            $stmtVoluntario->bindParam(':id_situacao', $situacao);
            $stmtVoluntario->bindParam(':data_admissao', $data_admissao);
            $stmtVoluntario->execute();
            $idVoluntario = $this->pdo->lastInsertId();

            $this->pdo->commit();
            return $idVoluntario;

        }
        catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function listarTodos()
    {
        $voluntarios = array();
        try {
            $consulta = $this->pdo->prepare("SELECT v.id_voluntario, p.nome, p.sobrenome, p.cpf, s.situacoes FROM pessoa p JOIN voluntario v ON p.id_pessoa = v.id_pessoa JOIN situacao s ON v.id_situacao=s.id_situacao");
            $consulta->execute();

            while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
                $voluntarios[] = array(
                    'id_voluntario' => htmlspecialchars($linha['id_voluntario']),
                    'cpf' => htmlspecialchars($linha['cpf']),
                    'nome' => htmlspecialchars($linha['nome']),
                    'sobrenome' => htmlspecialchars($linha['sobrenome']),
                    'situacao' => htmlspecialchars($linha['situacoes'])
                );
            }
        }
        catch (PDOException $e) {
            throw $e;
        }
        return $voluntarios;
    }

    public function selecionarCadastro(string $cpf)
    {
        try {
            $cpf = filter_var($cpf, FILTER_SANITIZE_SPECIAL_CHARS);
            $stmt = $this->pdo->prepare("SELECT v.id_voluntario FROM voluntario v JOIN pessoa p on v.id_pessoa=p.id_pessoa WHERE p.cpf = :cpf");
            $stmt->bindValue(':cpf', $cpf, PDO::PARAM_STR);
            $stmt->execute();

            $consultaVoluntario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$consultaVoluntario) {
                // Not a voluntario yet, check if exists as person
                $stmtCheckPessoa = $this->pdo->prepare("SELECT id_pessoa FROM pessoa WHERE cpf = :cpf");
                $stmtCheckPessoa->bindValue(':cpf', $cpf, PDO::PARAM_STR);
                $stmtCheckPessoa->execute();
                $pessoa = $stmtCheckPessoa->fetch(PDO::FETCH_ASSOC);

                if ($pessoa) {
                    return 'PESSOA_EXISTENTE';
                }
                else {
                    return 'NOVO_CADASTRO';
                }
            }
            else {
                throw new Exception("Erro, Voluntário já cadastrado no sistema.");
            }
        }
        catch (PDOException $e) {
            throw $e;
        }
    }

    public function listarCPF()
    {
        $cpfs = array();
        try {
            $consulta = $this->pdo->query("SELECT v.id_voluntario, p.cpf from pessoa p INNER JOIN voluntario v ON p.id_pessoa=v.id_pessoa");
            while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
                $cpfs[] = array('cpf' => $linha['cpf'], 'id' => $linha['id_voluntario']);
            }
        }
        catch (PDOException $e) {
            throw $e;
        }
        return $cpfs;
    }

    public function listarUm($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT p.*, v.* FROM pessoa p JOIN voluntario v ON p.id_pessoa=v.id_pessoa WHERE v.id_voluntario = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            throw $e;
        }
    }

    public function alterarInfPessoal(Voluntario $voluntario)
    {
        $this->pdo->beginTransaction();
        try {
            $sql = "UPDATE voluntario v JOIN pessoa p ON v.id_pessoa = p.id_pessoa 
                    SET p.nome = :nome, p.sobrenome = :sobrenome, p.sexo = :sexo, p.telefone = :telefone, p.data_nascimento = :data_nascimento, p.tipo_sanguineo = :tipo_sanguineo, p.nome_pai = :nome_pai, p.nome_mae = :nome_mae 
                    WHERE v.id_voluntario = :id";
            $stmt = $this->pdo->prepare($sql);

            $id = $voluntario->getId_voluntario();
            $nome = $voluntario->getNome();
            $sobrenome = $voluntario->getSobrenome();
            $sexo = $voluntario->getSexo();
            $telefone = $voluntario->getTelefone();
            $nascimento = $voluntario->getDataNascimento();
            $nomePai = $voluntario->getNomePai();
            $nomeMae = $voluntario->getNomeMae();
            $sangue = $voluntario->getTipoSanguineo();

            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':sobrenome', $sobrenome);
            $stmt->bindParam(':sexo', $sexo);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':data_nascimento', $nascimento);
            $stmt->bindParam(':tipo_sanguineo', $sangue);
            $stmt->bindParam(':nome_pai', $nomePai);
            $stmt->bindParam(':nome_mae', $nomeMae);

            $stmt->execute();
            $this->pdo->commit();
        }
        catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function alterarEndereco(Voluntario $voluntario)
    {
        $this->pdo->beginTransaction();
        try {
            $sql = "UPDATE voluntario v JOIN pessoa p ON v.id_pessoa = p.id_pessoa 
                    SET p.cep = :cep, p.estado = :estado, p.cidade = :cidade, p.bairro = :bairro, p.logradouro = :logradouro, p.numero_endereco = :numero_endereco, p.complemento = :complemento, p.ibge = :ibge 
                    WHERE v.id_voluntario = :id";
            $stmt = $this->pdo->prepare($sql);

            $id = $voluntario->getId_voluntario();
            $cep = $voluntario->getCep();
            $estado = $voluntario->getEstado();
            $cidade = $voluntario->getCidade();
            $bairro = $voluntario->getBairro();
            $logradouro = $voluntario->getLogradouro();
            $numeroEndereco = $voluntario->getNumeroEndereco();
            $complemento = $voluntario->getComplemento();
            $ibge = $voluntario->getIbge();

            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':cep', $cep);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':cidade', $cidade);
            $stmt->bindParam(':bairro', $bairro);
            $stmt->bindParam(':logradouro', $logradouro);
            $stmt->bindParam(':numero_endereco', $numeroEndereco);
            $stmt->bindParam(':complemento', $complemento);
            $stmt->bindParam(':ibge', $ibge);

            $stmt->execute();
            $this->pdo->commit();
        }
        catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function alterarDetalhes(Voluntario $voluntario)
    {
        $this->pdo->beginTransaction();
        try {
            $sql = "UPDATE voluntario 
                    SET data_admissao = :data_admissao, id_situacao = :id_situacao 
                    WHERE id_voluntario = :id";
            $stmt = $this->pdo->prepare($sql);

            $id = $voluntario->getId_voluntario();
            $data = $voluntario->getData_admissao();
            $situacao = $voluntario->getId_situacao();

            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':data_admissao', $data);
            $stmt->bindParam(':id_situacao', $situacao);

            $stmt->execute();
            $this->pdo->commit();
        }
        catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function alterarImagem($id, $img)
    {
        $this->pdo->beginTransaction();
        try {
            $sql = "UPDATE voluntario v JOIN pessoa p ON v.id_pessoa = p.id_pessoa 
                    SET p.imagem = :imagem 
                    WHERE v.id_voluntario = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':imagem', $img, PDO::PARAM_LOB);

            $stmt->execute();
            $this->pdo->commit();
        }
        catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}