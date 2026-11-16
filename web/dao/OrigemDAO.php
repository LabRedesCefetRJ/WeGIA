<?php
require_once ROOT . '/classes/Origem.php';
require_once ROOT . '/classes/OrigemValidacaoException.php';
require_once ROOT . '/dao/Conexao.php';
require_once ROOT . '/Functions/funcoes.php';

class OrigemDAO
{
    public function incluir($origem, array $almoxarifados = [])
    {
        $pdo = Conexao::connect();
        try {
            $pdo->beginTransaction();
            $this->validarAlmoxarifados($pdo, $almoxarifados);
            $this->validarDuplicidade($pdo, $origem);

            $sql = 'INSERT INTO origem(nome_origem,cnpj,cpf,telefone,inscricao_estadual,email,cep,logradouro,numero,complemento,bairro,cidade,uf) VALUES(:nome_origem,:cnpj,:cpf,:telefone,:inscricao_estadual,:email,:cep,:logradouro,:numero,:complemento,:bairro,:cidade,:uf)';
            $sql = str_replace("'", "\'", $sql);

            $stmt = $pdo->prepare($sql);

            $nome = $origem->getNome();
            $cnpj = $origem->getCnpj();
            $cpf = $origem->getCpf();
            $telefone = $origem->getTelefone();

            $stmt->bindParam(':nome_origem', $nome);
            $stmt->bindParam(':cnpj', $cnpj);
            $stmt->bindParam(':cpf', $cpf);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindValue(':inscricao_estadual', $origem->getInscricao_estadual());
            $stmt->bindValue(':email', $origem->getEmail());
            $stmt->bindValue(':cep', $origem->getCep());
            $stmt->bindValue(':logradouro', $origem->getLogradouro());
            $stmt->bindValue(':numero', $origem->getNumero());
            $stmt->bindValue(':complemento', $origem->getComplemento());
            $stmt->bindValue(':bairro', $origem->getBairro());
            $stmt->bindValue(':cidade', $origem->getCidade());
            $stmt->bindValue(':uf', $origem->getUf());


            $stmt->execute();

            $id_origem = (int) $pdo->lastInsertId();
            $this->atualizarAlmoxarifados($pdo, $id_origem, $almoxarifados);
            $this->salvarCondicoesPagamento($pdo, $id_origem, $origem);
            $pdo->commit();

            return $id_origem;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
    public function listarUm($id)
    {
        try {
            $pdo = Conexao::connect();
            $sql = "SELECT o.*, c.condicao_pagamento, (SELECT GROUP_CONCAT(v.dias ORDER BY v.dias SEPARATOR '/') FROM fornecedor_vencimento v WHERE v.id_origem = o.id_origem) AS vencimentos_pagamento FROM origem o LEFT JOIN fornecedor_condicao_pagamento c ON c.id_origem = o.id_origem WHERE o.id_origem = :id_origem";
            $consulta = $pdo->prepare($sql);
            $consulta->execute(array(
                ':id_origem' => $id,
            ));
            while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
                $origem = new Origem($linha['nome_origem'], $linha['cnpj'], $linha['cpf'], $linha['telefone'], $linha);
                $origem->setId_origem($linha['id_origem']);
            }
        } catch (PDOException $e) {
            throw $e;
        }
        return $origem;
    }
    public function excluir($id_origem)
    {
        try {
            $pdo = Conexao::connect();
            $consulta = $pdo->prepare('SELECT 1 FROM orcamento WHERE id_fornecedor = :id_origem LIMIT 1');
            $consulta->bindValue(':id_origem', $id_origem, PDO::PARAM_INT);
            $consulta->execute();

            if ($consulta->fetchColumn() !== false) {
                throw new DomainException('Não é possível excluir este fornecedor, pois existem orçamentos vinculados a ele.');
            }

            $sql = 'DELETE FROM origem WHERE id_origem = :id_origem';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_origem', $id_origem);
            $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }
    }
    public function listarTodos()
    {

        try {
            $origens = array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("
                SELECT
                    o.id_origem,
                    o.nome_origem,
                    o.cnpj,
                    o.cpf,
                    o.telefone,
                    o.inscricao_estadual,
                    o.email,
                    o.cep,
                    o.logradouro,
                    o.numero,
                    o.complemento,
                    o.bairro,
                    o.cidade,
                    o.uf,
                    c.condicao_pagamento,
                    (SELECT GROUP_CONCAT(v.dias ORDER BY v.dias SEPARATOR '/') FROM fornecedor_vencimento v WHERE v.id_origem = o.id_origem) AS vencimentos_pagamento,
                    GROUP_CONCAT(oa.id_almoxarifado) AS almoxarifados
                FROM origem o
                LEFT JOIN fornecedor_condicao_pagamento c ON c.id_origem = o.id_origem
                LEFT JOIN origem_almoxarifado oa
                    ON oa.id_origem = o.id_origem
                GROUP BY
                    o.id_origem,
                    o.nome_origem,
                    o.cnpj,
                    o.cpf,
                    o.telefone,
                    o.inscricao_estadual,
                    o.email,
                    o.cep,
                    o.logradouro,
                    o.numero,
                    o.complemento,
                    o.bairro,
                    o.cidade,
                    o.uf,
                    c.condicao_pagamento
                ORDER BY o.nome_origem
            ");
            $x=0;
            while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){
                $origens[$x] = array(
                    'id_origem' => $linha['id_origem'],
                    'nome_origem' => $linha['nome_origem'],
                    'cnpj' => $linha['cnpj'],
                    'cpf' => $linha['cpf'],
                    'telefone' => $linha['telefone'],
                    'inscricao_estadual' => $linha['inscricao_estadual'],
                    'email' => $linha['email'],
                    'cep' => $linha['cep'],
                    'logradouro' => $linha['logradouro'],
                    'numero' => $linha['numero'],
                    'complemento' => $linha['complemento'],
                    'bairro' => $linha['bairro'],
                    'cidade' => $linha['cidade'],
                    'uf' => $linha['uf'],
                    'condicao_pagamento' => $linha['condicao_pagamento'],
                    'vencimentos_pagamento' => $linha['vencimentos_pagamento'],

                    'almoxarifados' => $linha['almoxarifados']
                        ? explode(',', $linha['almoxarifados'])
                        : array()
                );
                $x++;
            }
        } catch (PDOException $e) {
            throw $e;
        }
        return json_encode($origens);
    }

    public function listarId_Nome()
    {
        try {
            $origens = array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("SELECT id_origem,nome_origem FROM origem ORDER BY nome_origem");
            $x = 0;
            while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
                $origens[$x] = array('id_origem' => $linha['id_origem'], 'nome_origem' => $linha['nome_origem']);
                $x++;
            }
        } catch (PDOException $e) {
            throw $e;
        }
        return json_encode($origens);
    }

    public function listarPorAlmoxarifado($id_almoxarifado)
    {
        try {
            $origens = array();
            $pdo = Conexao::connect();

            $sql = "
                SELECT o.id_origem, o.nome_origem
                FROM origem o
                INNER JOIN origem_almoxarifado oa
                    ON oa.id_origem = o.id_origem
                WHERE oa.id_almoxarifado = :id_almoxarifado
                ORDER BY o.nome_origem
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_almoxarifado', $id_almoxarifado, PDO::PARAM_INT);
            $stmt->execute();

            while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $origens[] = array(
                    'id_origem' => $linha['id_origem'],
                    'nome_origem' => $linha['nome_origem']
                );
            }

            return json_encode($origens);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function alterar($origem, array $almoxarifados = [])
    {
        $pdo = Conexao::connect();
        try {
            $pdo->beginTransaction();
            $this->validarAlmoxarifados($pdo, $almoxarifados);
            $this->validarDuplicidade($pdo, $origem, (int) $origem->getId_origem());

            $sql = "UPDATE origem
                    SET nome_origem = :nome_origem,
                        cnpj = :cnpj,
                        cpf = :cpf,
                        telefone = :telefone,
                        inscricao_estadual = :inscricao_estadual,
                        email = :email,
                        cep = :cep,
                        logradouro = :logradouro,
                        numero = :numero,
                        complemento = :complemento,
                        bairro = :bairro,
                        cidade = :cidade,
                        uf = :uf
                    WHERE id_origem = :id_origem";

            $stmt = $pdo->prepare($sql);

            $nome = $origem->getNome();
            $cnpj = $origem->getCnpj();
            $cpf = $origem->getCpf();
            $telefone = $origem->getTelefone();
            $id_origem = $origem->getId_origem();

            $stmt->bindParam(':nome_origem', $nome);
            $stmt->bindParam(':cnpj', $cnpj);
            $stmt->bindParam(':cpf', $cpf);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindValue(':inscricao_estadual', $origem->getInscricao_estadual());
            $stmt->bindValue(':email', $origem->getEmail());
            $stmt->bindValue(':cep', $origem->getCep());
            $stmt->bindValue(':logradouro', $origem->getLogradouro());
            $stmt->bindValue(':numero', $origem->getNumero());
            $stmt->bindValue(':complemento', $origem->getComplemento());
            $stmt->bindValue(':bairro', $origem->getBairro());
            $stmt->bindValue(':cidade', $origem->getCidade());
            $stmt->bindValue(':uf', $origem->getUf());

            $stmt->bindParam(':id_origem', $id_origem, PDO::PARAM_INT);

            $stmt->execute();
            $this->atualizarAlmoxarifados($pdo, $id_origem, $almoxarifados);
            $this->salvarCondicoesPagamento($pdo, $id_origem, $origem);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    private function salvarCondicoesPagamento(PDO $pdo, int $id, Origem $origem): void
    {
        $stmt = $pdo->prepare('DELETE FROM fornecedor_condicao_pagamento WHERE id_origem = ?');
        $stmt->execute([$id]);
        if ($origem->getCondicaoPagamento() === null) return;
        $stmt = $pdo->prepare('INSERT INTO fornecedor_condicao_pagamento (id_origem, condicao_pagamento) VALUES (?, ?)');
        $stmt->execute([$id, $origem->getCondicaoPagamento()]);
        if ($origem->getVencimentosPagamento() !== '') {
            $stmt = $pdo->prepare('INSERT INTO fornecedor_vencimento (id_origem, dias) VALUES (?, ?)');
            foreach (explode('/', $origem->getVencimentosPagamento()) as $dia) {
                $stmt->execute([$id, (int) $dia]);
            }
        }
    }

    private function validarDuplicidade(PDO $pdo, Origem $origem, int $ignorarId = 0): void
    {
        $normalizarNome = static function ($nome): string {
            return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $nome)), 'UTF-8');
        };
        $nome = $normalizarNome($origem->getNome());
        $cpf = preg_replace('/[^0-9]/', '', $origem->getCpf() ?? '');
        $cnpj = preg_replace('/[^0-9]/', '', $origem->getCnpj() ?? '');
        $consulta = $pdo->query('SELECT id_origem, nome_origem, cpf, cnpj FROM origem ORDER BY id_origem FOR UPDATE');
        $duplicado = null;
        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            if ((int) $linha['id_origem'] === $ignorarId) continue;
            if ($cpf !== '' && $cpf === preg_replace('/[^0-9]/', '', $linha['cpf'] ?? '')) {
                $duplicado = ['cpf', 'Já existe um fornecedor cadastrado com este CPF.'];
                break;
            }
            if ($cnpj !== '' && $cnpj === preg_replace('/[^0-9]/', '', $linha['cnpj'] ?? '')) {
                $duplicado = ['cnpj', 'Já existe um fornecedor cadastrado com este CNPJ.'];
                break;
            }
            if ($nome === $normalizarNome($linha['nome_origem'])) {
                $duplicado = ['nome', 'Já existe um fornecedor cadastrado com este nome. Confira o cadastro existente.'];
                break;
            }
        }
        $consulta->closeCursor();
        if ($duplicado !== null) {
            throw new OrigemValidacaoException($duplicado[0], $duplicado[1]);
        }
    }

    private function validarAlmoxarifados(PDO $pdo, array $almoxarifados): void
    {
        if (!$almoxarifados) {
            return;
        }
        $ids = array_values(array_unique($almoxarifados));
        sort($ids, SORT_NUMERIC);
        $consulta = $pdo->prepare(
            'SELECT id_almoxarifado FROM almoxarifado '
            . 'WHERE id_almoxarifado = :id AND ativo = 1 FOR UPDATE'
        );
        foreach ($ids as $id) {
            if (!is_int($id) || $id < 1 || $id > 2147483647) {
                throw new InvalidArgumentException('Seleção de almoxarifados inválida.');
            }
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $encontrado = $consulta->fetchColumn();
            $consulta->closeCursor();
            if ($encontrado === false) {
                throw new InvalidArgumentException(
                    'Um dos almoxarifados selecionados não existe ou está arquivado. Atualize a página e selecione novamente.'
                );
            }
        }
    }

    private function atualizarAlmoxarifados(PDO $pdo, $id_origem, array $almoxarifados)
    {
        $stmt = $pdo->prepare("DELETE FROM origem_almoxarifado WHERE id_origem = :id_origem");
        $stmt->bindValue(':id_origem', $id_origem, PDO::PARAM_INT);
        $stmt->execute();

        if (!empty($almoxarifados)) {
            $stmt = $pdo->prepare("
                INSERT INTO origem_almoxarifado (id_origem, id_almoxarifado)
                VALUES (:id_origem, :id_almoxarifado)
            ");

            foreach (array_unique($almoxarifados) as $id_almoxarifado) {
                $stmt->bindValue(':id_origem', $id_origem, PDO::PARAM_INT);
                $stmt->bindValue(':id_almoxarifado', (int) $id_almoxarifado, PDO::PARAM_INT);
                $stmt->execute();
            }
        }
    }
}
