<?php
require_once ROOT . "/dao/Conexao.php";

class ProjetoDAO
{
    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = Conexao::connect();
        } catch (Exception $e) {
            throw new Exception("Erro ao conectar ao banco de dados: " . $e->getMessage());
        }
    }

    public function adicionarProjeto($nome, $descricao, $id_tipo, $id_local, $id_status, $data_inicio, $data_fim)
    {
        try {
            $sql = "INSERT INTO projeto (nome, descricao, id_tipo, id_local, id_status, data_inicio, data_fim) 
                    VALUES (:nome, :descricao, :id_tipo, :id_local, :id_status, :data_inicio, :data_fim)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':id_tipo', $id_tipo, PDO::PARAM_INT);
            $stmt->bindValue(':id_local', $id_local, PDO::PARAM_INT);
            $stmt->bindValue(':id_status', $id_status, PDO::PARAM_INT);
            $stmt->bindValue(':data_inicio', $data_inicio);

            if (empty($data_fim) || $data_fim === '' || $data_fim === null) {
                $stmt->bindValue(':data_fim', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':data_fim', $data_fim);
            }

            $resultado = $stmt->execute();

            if (!$resultado) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Erro SQL: " . $errorInfo[2]);
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Erro ao inserir projeto: " . $e->getMessage());
        }
    }

    public function listarTodos()
    {
        try {
            $sql = "SELECT p.id_projeto, p.nome, p.descricao, 
                       p.id_status,
                       pt.descricao as tipo, 
                       pl.nome as local, 
                       ps.descricao as status, 
                       p.data_inicio, p.data_fim
                FROM projeto p
                INNER JOIN projeto_tipo pt ON p.id_tipo = pt.id_tipo
                INNER JOIN projeto_local pl ON p.id_local = pl.id_local
                INNER JOIN projeto_status ps ON p.id_status = ps.id_status
                ORDER BY p.data_inicio DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao listar projetos: " . $e->getMessage());
            return [];
        }
    }

    public function listarUm($id_projeto)
    {
        try {
            $pd = $this->pdo->prepare("SELECT * FROM projeto WHERE id_projeto = :id");
            $pd->bindValue(":id", $id_projeto, PDO::PARAM_INT);
            $pd->execute();
            $projeto = $pd->fetch(PDO::FETCH_ASSOC);

            if ($projeto) {
                return array(
                    'nome'       => $projeto['nome'],
                    'descricao'  => $projeto['descricao'],
                    'id_tipo'    => $projeto['id_tipo'],
                    'id_local'   => $projeto['id_local'],
                    'id_status'  => $projeto['id_status'],
                    'data_inicio' => $projeto['data_inicio'],
                    'data_fim'   => $projeto['data_fim']
                );
            }

            return null;
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar projeto: " . $e->getMessage());
        }
    }

    public function alterarProjeto($id_projeto, $nome, $descricao, $id_tipo, $id_local, $id_status, $data_inicio, $data_fim)
    {
        try {
            $projetoExistente = $this->listarUm($id_projeto);
            if (!$projetoExistente) {
                throw new Exception("Nenhum projeto encontrado com o ID informado.");
            }

            $pd = $this->pdo->prepare("UPDATE projeto SET 
            nome = :nome, 
            descricao = :descricao, 
            id_tipo = :id_tipo, 
            id_local = :id_local, 
            id_status = :id_status, 
            data_inicio = :data_inicio, 
            data_fim = :data_fim 
            WHERE id_projeto = :id_projeto");

            $pd->bindValue(':nome', $nome);
            $pd->bindValue(':descricao', $descricao);
            $pd->bindValue(':id_tipo', $id_tipo, PDO::PARAM_INT);
            $pd->bindValue(':id_local', $id_local, PDO::PARAM_INT);
            $pd->bindValue(':id_status', $id_status, PDO::PARAM_INT);
            $pd->bindValue(':data_inicio', $data_inicio);

            if (empty($data_fim) || $data_fim === '' || $data_fim === null) {
                $pd->bindValue(':data_fim', null, PDO::PARAM_NULL);
            } else {
                $pd->bindValue(':data_fim', $data_fim);
            }

            $pd->bindValue(':id_projeto', $id_projeto, PDO::PARAM_INT);
            $pd->execute();

            return true;
        } catch (Exception $e) {
            throw new Exception("Erro ao alterar projeto: " . $e->getMessage());
        }
    }

    public function listarTiposProjeto()
    {
        try {
            $sql = "SELECT * FROM projeto_tipo ORDER BY descricao";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function listarLocaisProjeto()
    {
        try {
            $sql = "SELECT * FROM projeto_local ORDER BY nome";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function listarStatusProjeto()
    {
        try {
            $sql = "SELECT * FROM projeto_status ORDER BY descricao";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function adicionarTipoProjeto($descricao)
    {
        try {
            if (empty($descricao)) {
                throw new Exception("Tipo não informado.");
            }
            $stmt = $this->pdo->prepare("INSERT INTO projeto_tipo (descricao) VALUES (:descricao)");
            $stmt->bindValue(':descricao', $descricao);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            throw new Exception("Erro ao adicionar tipo: " . $e->getMessage());
        }
    }

    public function adicionarLocalProjeto($nome)
    {
        try {
            if (empty($nome)) {
                throw new Exception("Local não informado.");
            }
            $stmt = $this->pdo->prepare("INSERT INTO projeto_local (nome) VALUES (:nome)");
            $stmt->bindValue(':nome', $nome);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            throw new Exception("Erro ao adicionar local: " . $e->getMessage());
        }
    }

    public function adicionarStatusProjeto($descricao)
    {
        try {
            if (empty($descricao)) {
                throw new Exception("Status não informado.");
            }
            $stmt = $this->pdo->prepare("INSERT INTO projeto_status (descricao) VALUES (:descricao)");
            $stmt->bindValue(':descricao', $descricao);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            throw new Exception("Erro ao adicionar status: " . $e->getMessage());
        }
    }

    public function listarFuncoesProjeto()
    {
        try {
            $sql = "SELECT id_funcao, descricao FROM projeto_funcao ORDER BY descricao";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao listar funções: " . $e->getMessage());
            return [];
        }
    }

    public function listarEquipeProjeto($projeto_id)
    {
        try {
            $sql = "SELECT pe.id, pe.id_projeto, pe.id_pessoa, pe.id_funcao,
                       p.nome, p.sobrenome, p.cpf,
                       pf.descricao as funcao_descricao
                FROM projeto_executante pe
                JOIN pessoa p ON pe.id_pessoa = p.id_pessoa
                LEFT JOIN projeto_funcao pf ON pe.id_funcao = pf.id_funcao
                WHERE pe.id_projeto = :projeto_id
                ORDER BY pe.id DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':projeto_id', $projeto_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao listar equipe do projeto: " . $e->getMessage());
            return [];
        }
    }

    public function adicionarMembroEquipe($projeto_id, $id_pessoa, $id_funcao)
    {
        try {
            $check = $this->pdo->prepare("SELECT id FROM projeto_executante WHERE id_projeto = :projeto_id AND id_pessoa = :id_pessoa");
            $check->bindValue(':projeto_id', $projeto_id, PDO::PARAM_INT);
            $check->bindValue(':id_pessoa', $id_pessoa, PDO::PARAM_INT);
            $check->execute();

            if ($check->fetch()) {
                throw new Exception('Pessoa já está na equipe deste projeto.');
            }

            $sql = "INSERT INTO projeto_executante (id_projeto, id_pessoa, id_funcao) 
                VALUES (:projeto_id, :id_pessoa, :id_funcao)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':projeto_id', $projeto_id, PDO::PARAM_INT);
            $stmt->bindValue(':id_pessoa', $id_pessoa, PDO::PARAM_INT);
            $stmt->bindValue(':id_funcao', $id_funcao, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (Exception $e) {
            throw new Exception("Erro ao adicionar membro: " . $e->getMessage());
        }
    }

    public function removerMembroEquipe($id, $projeto_id)
    {
        try {
            $sql = "DELETE FROM projeto_executante WHERE id = :id AND id_projeto = :projeto_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':projeto_id', $projeto_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Erro ao remover membro: " . $e->getMessage());
        }
    }

    public function adicionarFuncaoProjeto($descricao)
    {
        try {
            if (empty($descricao)) {
                throw new Exception("Descrição não informada.");
            }

            $sql = "INSERT INTO projeto_funcao (descricao) VALUES (:descricao)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->execute();

            return true;
        } catch (Exception $e) {
            throw new Exception("Erro ao adicionar função: " . $e->getMessage());
        }
    }

    public function listarTodosAtendidos()
    {
        try {
            // CPF excluído intencionalmente: atendidos podem ser cadastrados sem CPF
            $sql = "SELECT a.idatendido, a.pessoa_id_pessoa,
                           p.nome, p.sobrenome,
                           s.situacoes as status_descricao,
                           at.descricao as tipo_descricao
                    FROM atendido a
                    INNER JOIN pessoa p ON a.pessoa_id_pessoa = p.id_pessoa
                    LEFT JOIN situacao s ON a.atendido_status_idatendido_status = s.id_situacao
                    LEFT JOIN atendido_tipo at ON a.atendido_tipo_idatendido_tipo = at.idatendido_tipo
                    ORDER BY p.nome ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao listar atendidos: " . $e->getMessage());
            return [];
        }
    }

    public function listarAtendidosProjeto($projeto_id)
    {
        try {
            $sql = "SELECT pa.id, pa.id_projeto, pa.id_atendido, pa.id_status,
                   p.nome, p.sobrenome, p.cpf, p.id_pessoa,
                   pas.descricao as status_descricao
            FROM projeto_atendido pa
            JOIN atendido a ON pa.id_atendido = a.idatendido
            JOIN pessoa p ON a.pessoa_id_pessoa = p.id_pessoa
            LEFT JOIN projeto_atendido_status pas ON pa.id_status = pas.id_status
            WHERE pa.id_projeto = :projeto_id
            ORDER BY pa.id DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':projeto_id', $projeto_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao listar atendidos do projeto: " . $e->getMessage());
            return [];
        }
    }

    public function listarStatusAtendidoProjeto()
    {
        try {
            $sql = "SELECT id_status, descricao FROM projeto_atendido_status ORDER BY id_status";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao listar status: " . $e->getMessage());
            return [];
        }
    }

    public function adicionarAtendidoProjeto($projeto_id, $id_atendido, $id_status)
    {
        try {
            $check = $this->pdo->prepare("SELECT id FROM projeto_atendido WHERE id_projeto = :projeto_id AND id_atendido = :id_atendido");
            $check->bindValue(':projeto_id', $projeto_id, PDO::PARAM_INT);
            $check->bindValue(':id_atendido', $id_atendido, PDO::PARAM_INT);
            $check->execute();

            if ($check->fetch()) {
                throw new Exception('Atendido já está vinculado a este projeto.');
            }

            $sql = "INSERT INTO projeto_atendido (id_projeto, id_atendido, id_status) 
                VALUES (:projeto_id, :id_atendido, :id_status)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':projeto_id', $projeto_id, PDO::PARAM_INT);
            $stmt->bindValue(':id_atendido', $id_atendido, PDO::PARAM_INT);
            $stmt->bindValue(':id_status', $id_status, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (Exception $e) {
            throw new Exception("Erro ao adicionar atendido: " . $e->getMessage());
        }
    }

    public function removerAtendidoProjeto($id, $projeto_id)
    {
        try {
            $sql = "DELETE FROM projeto_atendido WHERE id = :id AND id_projeto = :projeto_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':projeto_id', $projeto_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Erro ao remover atendido: " . $e->getMessage());
        }
    }

    public function atualizarStatusAtendidoProjeto($id, $id_status)
    {
        try {
            $sql = "UPDATE projeto_atendido SET id_status = :id_status WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_status', $id_status, PDO::PARAM_INT);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Erro ao atualizar status: " . $e->getMessage());
        }
    }

    public function adicionarStatusAtendidoProjeto($descricao)
    {
        try {
            if (empty($descricao)) {
                throw new Exception("Descrição do status não informada.");
            }

            $sql = "INSERT INTO projeto_atendido_status (descricao) VALUES (:descricao)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->execute();

            return true;
        } catch (Exception $e) {
            throw new Exception("Erro ao adicionar status: " . $e->getMessage());
        }
    }
}