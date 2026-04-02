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
            Util::tratarException($e);
            return null;
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
            Util::tratarException($e);
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
                    header('Location: ../html/voluntario/cadastro_voluntario_pessoa_existente.php?cpf=' . htmlspecialchars($cpf));
                    exit;
                }
                else {
                    header('Location: ../html/voluntario/cadastro_voluntario.php?cpf=' . htmlspecialchars($cpf));
                    exit;
                }
            }
            else {
                header("Location: ../html/voluntario/pre_cadastro_voluntario.php?msg_e=Erro, Voluntário já cadastrado no sistema.");
                exit;
            }
        }
        catch (PDOException $e) {
            Util::tratarException($e);
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
            Util::tratarException($e);
        }
        return $cpfs;
    }
}