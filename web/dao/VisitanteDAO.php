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
            $buscaPessoa = $this->pdo->prepare("SELECT id_pessoa FROM pessoa WHERE cpf = :cpf");
            $buscaPessoa->bindParam(':cpf', $cpf);
            $buscaPessoa->execute();
            $idPessoa = $buscaPessoa->fetchColumn();

            if(!$idPessoa) {
                $sqlPessoa = "INSERT INTO pessoa (nome, sobrenome, cpf, sexo, telefone, data_nascimento, cep, estado, cidade, bairro, logradouro, numero_endereco, complemento, ibge, registro_geral, orgao_emissor, data_expedicao, nome_pai, nome_mae, tipo_sanguineo) VALUES (:nome, :sobrenome, :cpf, :sexo, :telefone, :data_nascimento, :cep, :estado, :cidade, :bairro, :logradouro, :numero_endereco, :complemento, :ibge, :registro_geral, :orgao_emissor, :data_expedicao, :nome_pai, :nome_mae, :tipo_sanguineo)";
                $stmtPessoa = $this->pdo->prepare($sqlPessoa);

                $nome = $visitante->getNome();
                $sobrenome = $visitante->getSobrenome();
                $cpf = $visitante->getCpf();
                $sexo = $visitante->getSexo();
                $telefone = $visitante->getTelefone();
                $dataNascimento = $visitante->getDataNascimento();
                $cep = $visitante->getCep();
                $estado = $visitante->getEstado();
                $cidade = $visitante->getCidade();
                $bairro = $visitante->getBairro();
                $logradouro = $visitante->getLogradouro();
                $numeroEndereco = $visitante->getNumeroEndereco();
                $complemento = $visitante->getComplemento();
                $ibge = $visitante->getIbge();
                $registroGeral = $visitante->getRegistroGeral();
                $orgaoEmissor = $visitante->getOrgaoEmissor();
                $dataExpedicao = $visitante->getDataExpedicao();
                $nomePai = $visitante->getNomePai();
                $nomeMae = $visitante->getNomeMae();
                $tipoSanguineo = $visitante->getTipoSanguineo();

                $stmtPessoa->bindParam(':nome', $nome);
                $stmtPessoa->bindParam(':sobrenome', $sobrenome);
                $stmtPessoa->bindParam(':cpf', $cpf);
                $stmtPessoa->bindParam(':sexo', $sexo);
                $stmtPessoa->bindParam(':telefone', $telefone);
                $stmtPessoa->bindParam(':data_nascimento', $dataNascimento);
                $stmtPessoa->bindParam(':cep', $cep);
                $stmtPessoa->bindParam(':estado', $estado);
                $stmtPessoa->bindParam(':cidade', $cidade);
                $stmtPessoa->bindParam(':bairro', $bairro);
                $stmtPessoa->bindParam(':logradouro', $logradouro);
                $stmtPessoa->bindParam(':numero_endereco', $numeroEndereco);
                $stmtPessoa->bindParam(':complemento', $complemento);
                $stmtPessoa->bindParam(':ibge', $ibge);
                $stmtPessoa->bindParam(':registro_geral', $registroGeral);
                $stmtPessoa->bindParam(':orgao_emissor', $orgaoEmissor);
                $stmtPessoa->bindParam(':data_expedicao', $dataExpedicao);
                $stmtPessoa->bindParam(':nome_pai', $nomePai);
                $stmtPessoa->bindParam(':nome_mae', $nomeMae);
                $stmtPessoa->bindParam(':tipo_sanguineo', $tipoSanguineo);

                $stmtPessoa->execute();
                
                $idPessoa = $this->pdo->lastInsertId();
            } else {
                $sqlPessoa = "UPDATE pessoa SET nome=:nome, sobrenome=:sobrenome, sexo=:sexo, telefone=:telefone, data_nascimento=:data_nascimento, cep=:cep, estado=:estado, cidade=:cidade, bairro=:bairro, logradouro=:logradouro, numero_endereco=:numero_endereco, complemento=:complemento, ibge=:ibge, registro_geral=:registro_geral, orgao_emissor=:orgao_emissor, data_expedicao=:data_expedicao, nome_pai=:nome_pai, nome_mae=:nome_mae, tipo_sanguineo=:tipo_sanguineo WHERE id_pessoa=:id_pessoa";
                $stmtPessoa = $this->pdo->prepare($sqlPessoa);

                $nome = $visitante->getNome();
                $sobrenome = $visitante->getSobrenome();
                $sexo = $visitante->getSexo();
                $telefone = $visitante->getTelefone();
                $dataNascimento = $visitante->getDataNascimento();
                $cep = $visitante->getCep();
                $estado = $visitante->getEstado();
                $cidade = $visitante->getCidade();
                $bairro = $visitante->getBairro();
                $logradouro = $visitante->getLogradouro();
                $numeroEndereco = $visitante->getNumeroEndereco();
                $complemento = $visitante->getComplemento();
                $ibge = $visitante->getIbge();
                $registroGeral = $visitante->getRegistroGeral();
                $orgaoEmissor = $visitante->getOrgaoEmissor();
                $dataExpedicao = $visitante->getDataExpedicao();
                $nomePai = $visitante->getNomePai();
                $nomeMae = $visitante->getNomeMae();
                $tipoSanguineo = $visitante->getTipoSanguineo();

                $stmtPessoa->bindParam(':nome', $nome);
                $stmtPessoa->bindParam(':sobrenome', $sobrenome);
                $stmtPessoa->bindParam(':sexo', $sexo);
                $stmtPessoa->bindParam(':telefone', $telefone);
                $stmtPessoa->bindParam(':data_nascimento', $dataNascimento);
                $stmtPessoa->bindParam(':cep', $cep);
                $stmtPessoa->bindParam(':estado', $estado);
                $stmtPessoa->bindParam(':cidade', $cidade);
                $stmtPessoa->bindParam(':bairro', $bairro);
                $stmtPessoa->bindParam(':logradouro', $logradouro);
                $stmtPessoa->bindParam(':numero_endereco', $numeroEndereco);
                $stmtPessoa->bindParam(':complemento', $complemento);
                $stmtPessoa->bindParam(':ibge', $ibge);
                $stmtPessoa->bindParam(':registro_geral', $registroGeral);
                $stmtPessoa->bindParam(':orgao_emissor', $orgaoEmissor);
                $stmtPessoa->bindParam(':data_expedicao', $dataExpedicao);
                $stmtPessoa->bindParam(':nome_pai', $nomePai);
                $stmtPessoa->bindParam(':nome_mae', $nomeMae);
                $stmtPessoa->bindParam(':tipo_sanguineo', $tipoSanguineo);
                $stmtPessoa->bindParam(':id_pessoa', $idPessoa);

                $stmtPessoa->execute();
            }

            $sqlVisitante = "INSERT INTO visitante(id_pessoa, id_situacao) VALUES(:id_pessoa, :id_situacao)";
            $stmtVisitante = $this->pdo->prepare($sqlVisitante);

            // $idVisitanteTipo = $visitante->getIdVisitanteTipo();
            $idSituacao = $visitante->getIdSituacao();

            $stmtVisitante->bindParam(':id_pessoa', $idPessoa);
            // $stmtVisitante->bindParam(':id_visitante_tipo', $idVisitanteTipo);
            $stmtVisitante->bindParam(':id_situacao', $idSituacao);

            $stmtVisitante->execute();
            $idVisitante = $this->pdo->lastInsertId();
            $this->pdo->commit();
            return $idVisitante;
        } catch(PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function incluirExistente($cpf, $idSituacao)
    {
        $this->pdo->beginTransaction();

        try {
            $buscaPessoa = $this->pdo->prepare("SELECT id_pessoa FROM pessoa WHERE cpf = :cpf");
            $buscaPessoa->bindParam(':cpf', $cpf);
            $buscaPessoa->execute();
            $idPessoa = $buscaPessoa->fetchColumn();

            if(!$idPessoa) {
                throw new PDOException('Pessoa não encontrada.');
            }

            $sqlVisitante = "INSERT INTO visitante(id_pessoa, id_situacao) VALUES(:id_pessoa, :id_situacao)";
            $stmtVisitante = $this->pdo->prepare($sqlVisitante);
            $stmtVisitante->bindParam(':id_pessoa', $idPessoa);
            $stmtVisitante->bindParam(':id_situacao', $idSituacao);
            $stmtVisitante->execute();
            $idVisitante = $this->pdo->lastInsertId();

            $this->pdo->commit();
            return $idVisitante;
        } catch(PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function selecionarCadastro(string $cpf)
    {
        try {
            $cpf = filter_var($cpf, FILTER_SANITIZE_SPECIAL_CHARS);
            $stmt = $this->pdo->prepare("SELECT v.id_visitante FROM visitante v JOIN pessoa p ON v.id_pessoa = p.id_pessoa WHERE p.cpf = :cpf");
            $stmt->bindValue(':cpf', $cpf, PDO::PARAM_STR);
            $stmt->execute();

            $consultaVisitante = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!$consultaVisitante) {
                $stmtCheckPessoa = $this->pdo->prepare("SELECT id_pessoa FROM pessoa WHERE cpf = :cpf");
                $stmtCheckPessoa->bindValue(':cpf', $cpf, PDO::PARAM_STR);
                $stmtCheckPessoa->execute();
                $pessoa = $stmtCheckPessoa->fetch(PDO::FETCH_ASSOC);

                if($pessoa) {
                    return 'PESSOA_EXISTENTE';
                } else {
                    return 'NOVO_CADASTRO';
                }
            } else {
                throw new Exception("Erro, Visitante já cadastrado no sistema.");
            }
        } catch(PDOException $e) {
            throw $e;
        }
    }

    public function listarTodos()
    {
        $visitantes = array();
        try {
            $consulta = $this->pdo->prepare("SELECT v.id_visitante, p.nome, p.sobrenome, p.cpf, s.situacoes FROM pessoa p JOIN visitante v ON p.id_pessoa = v.id_pessoa JOIN situacao s ON v.id_situacao = s.id_situacao");
            $consulta->execute();

            while($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
                $visitantes[] = array(
                    'id_visitante' => htmlspecialchars($linha['id_visitante']),
                    'cpf' => htmlspecialchars($linha['cpf']),
                    'nome' => htmlspecialchars($linha['nome']),
                    'sobrenome' => htmlspecialchars($linha['sobrenome']),
                    'situacao' => htmlspecialchars($linha['situacoes'])
                );
            }
        } catch(PDOException $e) {
            throw $e;
        }
        return $visitantes;
    }

    public function listarUm($idVisitante)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT p.*, v.* FROM pessoa p JOIN visitante v ON p.id_pessoa = v.id_pessoa WHERE v.id_visitante = :idVisitante");
            $stmt->bindValue(':idVisitante', $idVisitante, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            throw $e;
        }
    }

    public function listarCPF()
    {
        $cpfs = array();
        try {
            $consulta = $this->pdo->query("SELECT v.id_visitante, p.cpf FROM pessoa p INNER JOIN visitante v ON p.id_pessoa = v.id_pessoa");
            while($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
                $cpfs[] = array('cpf' => $linha['cpf'], 'idVisitante' => $linha['id_visitante']);
            }
        } catch(PDOException $e) {
            throw $e;
        }
        return $cpfs;
    }

    public function getIdPessoaByIdVisitante(int $idVisitante) : int
    {
        $query = 'SELECT id_pessoa FROM visitante WHERE id_visitante = :idVisitante';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':idVisitante', $idVisitante, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC)['id_pessoa'];
    }
}

// Funções de UPDATE a serem criadas posteriormente...