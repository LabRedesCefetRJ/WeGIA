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
            $sqlPessoa = "INSERT INTO pessoa (nome, sobrenome, cpf, sexo, telefone, data_nascimento, imagem, cep, estado, cidade, bairro, logradouro, numero_endereco, complemento, ibge, registro_geral, orgao_emissor, data_expedicao, nome_pai, nome_mae, tipo_sanguineo) VALUES (:nome, :sobrenome, :cpf, :sexo, :telefone, :data_nascimento, :imagem, :cep, :estado, :cidade, :bairro, :logradouro, :numero_endereco, :complemento, :ibge, :registro_geral, :orgao_emissor, :data_expedicao, :nome_pai, :nome_mae, :tipo_sanguineo)";

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
            $imagem = $visitante->getImagem();
            $imagem = !empty($imagem) ? base64_encode($imagem) : null;

            $stmtPessoa->bindParam(':nome', $nome);
            $stmtPessoa->bindParam(':sobrenome', $sobrenome);
            $stmtPessoa->bindParam(':cpf', $cpf);
            $stmtPessoa->bindParam(':sexo', $sexo);
            $stmtPessoa->bindParam(':telefone', $telefone);
            $stmtPessoa->bindParam(':data_nascimento', $nascimento);
            $stmtPessoa->bindParam(':imagem', $imagem);
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

    /**
     * Cadastra como visitante uma pessoa que já existe no sistema.
     *
     * A pessoa pode ser atendido, funcionário, sócio etc., então esta tela não altera
     * os dados que já estão cadastrados: só preenche os campos que estão vazios.
     */
    public function incluirExistente(Visitante $visitante, $cpf)
    {
        $this->pdo->beginTransaction();

        try {
            $buscaPessoa = $this->pdo->prepare("SELECT id_pessoa FROM pessoa WHERE cpf = :cpf FOR UPDATE");
            $buscaPessoa->bindParam(":cpf", $cpf);
            $buscaPessoa->execute();
            $idPessoa = $buscaPessoa->fetchColumn();

            if ($idPessoa === false)
                throw new InvalidArgumentException("Pessoa não encontrada.", 404);

            $buscaVisitante = $this->pdo->prepare("SELECT id_visitante FROM visitante WHERE id_pessoa = :id_pessoa");
            $buscaVisitante->bindParam(":id_pessoa", $idPessoa);
            $buscaVisitante->execute();

            if ($buscaVisitante->fetchColumn() !== false)
                throw new InvalidArgumentException("Erro, Visitante já cadastrado no sistema.", 400);

            $campos = [
                "nome" => $visitante->getNome(),
                "sobrenome" => $visitante->getSobrenome(),
                "sexo" => $visitante->getSexo(),
                "data_nascimento" => $visitante->getDataNascimento(),
                "cep" => $visitante->getCep(),
                "estado" => $visitante->getEstado(),
                "cidade" => $visitante->getCidade(),
                "bairro" => $visitante->getBairro(),
                "logradouro" => $visitante->getLogradouro(),
                "numero_endereco" => $visitante->getNumeroEndereco(),
                "complemento" => $visitante->getComplemento(),
                "ibge" => $visitante->getIbge(),
            ];

            $imagem = $visitante->getImagem();
            if (!empty($imagem))
                $campos["imagem"] = base64_encode($imagem);

            // Nomes de coluna vêm da lista fixa acima; os valores vão por parâmetro.
            $sets = [];
            foreach (array_keys($campos) as $coluna)
                $sets[] = "{$coluna} = COALESCE(NULLIF(TRIM({$coluna}), ''), NULLIF(:{$coluna}, ''), {$coluna})";

            $stmtPessoa = $this->pdo->prepare("UPDATE pessoa SET " . implode(", ", $sets) . " WHERE id_pessoa = :id_pessoa");

            foreach ($campos as $coluna => $valor)
                $stmtPessoa->bindValue(":{$coluna}", is_null($valor) ? null : trim((string) $valor));

            $stmtPessoa->bindValue(":id_pessoa", $idPessoa, PDO::PARAM_INT);
            $stmtPessoa->execute();

            $sqlVisitante = "INSERT INTO visitante (id_pessoa) VALUES (:id_pessoa)";
            $stmtVisitante = $this->pdo->prepare($sqlVisitante);
            $stmtVisitante->bindParam(":id_pessoa", $idPessoa);
            $stmtVisitante->execute();
            $idVisitante = $this->pdo->lastInsertId();

            $this->pdo->commit();
            return $idVisitante;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function selecionarCadastro(string $cpf)
    {
        try {
            $cpf = filter_var($cpf, FILTER_SANITIZE_SPECIAL_CHARS);
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

    public function buscarResumoPorIds(array $idsVisitante): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $idsVisitante), fn($id) => $id > 0)));

        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $this->pdo->prepare("SELECT v.id_visitante, p.nome, p.sobrenome, p.cpf, p.imagem FROM visitante v INNER JOIN pessoa p ON p.id_pessoa = v.id_pessoa WHERE v.id_visitante IN ($placeholders)");

        foreach ($ids as $i => $id) {
            $stmt->bindValue($i + 1, $id, PDO::PARAM_INT);
        }

        $stmt->execute();
        $porId = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $porId[(int) $linha['id_visitante']] = $linha;
        }

        $resultado = [];
        foreach ($ids as $id) {
            if (isset($porId[$id])) {
                $resultado[] = $porId[$id];
            }
        }

        return $resultado;
    }
}