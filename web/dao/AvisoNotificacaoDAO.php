<?php

require_once 'Conexao.php';

class AvisoNotificacaoDAO
{

    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::connect();
    }

    /**
     * Recebe como parâmetro um objeto do tipo AvisoNotificacao, realizando os procedimentos necessários para garantir a inserção correta de todas as notificações de um determinado aviso no banco de dados.
     */
    public function cadastrar(AvisoNotificacao $avisoNotificacao)
    {
        $sql = 'INSERT INTO aviso_notificacao (id_aviso, id_funcionario, status) VALUES(:idAviso, :idFuncionario, 1)';

        try {
            $this->pdo->beginTransaction();

            if ($this->loopCadastro($avisoNotificacao, $sql)) {
                $this->pdo->commit();
            } else {
                $this->pdo->rollBack();
            }
        } catch (PDOException $e) {
            echo 'Erro ao cadastrar a notificação de uma intercorrência no banco de dados: ' . $e->getMessage();
        }
    }

    /**
     * Recebe como parâmetros um objeto do tipo AvisoNotificacao e uma string de cadastro SQL, obtém um array de funcionários e cria uma notificação para cada um dos funcionários.
     */
    private function loopCadastro(AvisoNotificacao $avisoNotificacao, $sql)
    {
        $funcionarios = $this->pdo->query('SELECT f.id_funcionario FROM funcionario f JOIN cargo c ON(f.id_cargo=c.id_cargo) JOIN permissao p ON (c.id_cargo=p.id_cargo) WHERE p.id_acao>=5 AND p.id_recurso=5')->fetchAll(PDO::FETCH_ASSOC);

        foreach ($funcionarios as $funcionario) {
            $stmt = $this->pdo->prepare($sql);

            $idAviso = $avisoNotificacao->getIdAviso();
            $idFuncionario = $funcionario['id_funcionario'];
            $stmt->bindParam(':idAviso', $idAviso);
            $stmt->bindParam(':idFuncionario', $idFuncionario);

            $stmt->execute();
        }

        return true;
    }

    /**
     * Recebe como parâmetro o id de uma pessoa e retornar todas as notificações que ainda não foram marcadas como lidas.
     */
    public function buscarRecentes($idPessoa)
    {

        $sql = "SELECT a.descricao, a.data, p.nome as atendido_nome, p.sobrenome as atendido_sobrenome, pe.nome as funcionario_nome, pe.sobrenome as funcionario_sobrenome, an.id_aviso_notificacao
        FROM pessoa as p 
        JOIN aviso as a ON (p.id_pessoa=a.id_pessoa_atendida) 
        JOIN aviso_notificacao as an ON (a.id_aviso=an.id_aviso) 
        JOIN funcionario as f ON (an.id_funcionario=f.id_funcionario) 
        JOIN funcionario as fu ON (a.id_funcionario_aviso=fu.id_funcionario) 
        JOIN pessoa as pe ON(fu.id_pessoa=pe.id_pessoa) 
        WHERE f.id_pessoa=:idPessoa AND an.status=1
        ORDER BY a.data DESC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':idPessoa', $idPessoa);
            $stmt->execute();
            $avisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $avisos;
        } catch (PDOException $e) {
            echo 'Erro ao tentar realizar a busca pelas intercorrências recentes: ' . $e->getMessage();
        }
    }

    /**
     * Recebe como parâmetro o id de uma pessoa e retornar todas as notificações que foram marcadas como lidas.
     */
    public function buscarHistoricos($idPessoa)
    {
        $sql = "SELECT a.descricao, a.data, p.nome as atendido_nome, p.sobrenome as atendido_sobrenome, pe.nome as funcionario_nome, pe.sobrenome as funcionario_sobrenome
        FROM pessoa as p 
        JOIN aviso as a ON (p.id_pessoa=a.id_pessoa_atendida) 
        JOIN aviso_notificacao as an ON (a.id_aviso=an.id_aviso) 
        JOIN funcionario as f ON (an.id_funcionario=f.id_funcionario) 
        JOIN funcionario as fu ON (a.id_funcionario_aviso=fu.id_funcionario) 
        JOIN pessoa as pe ON(fu.id_pessoa=pe.id_pessoa) 
        WHERE f.id_pessoa=:idPessoa AND an.status=0
        ORDER BY a.data DESC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':idPessoa', $idPessoa);
            $stmt->execute();
            $avisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $avisos;
        } catch (PDOException $e) {
            echo 'Erro ao tentar buscar o histórico de intercorrências: ' . $e->getMessage();
        }
    }

    /**
     * Recebe o id de uma pessoa como parâmetro e retorna a quantidade de notificações que essa pessoa ainda não leu.
     */
    public function contarRecentes($idPessoa)
    {
        $sql = "SELECT count(an.id_aviso_notificacao) as qtd_notificacoes FROM aviso_notificacao as an JOIN funcionario as f ON (an.id_funcionario=f.id_funcionario) WHERE f.id_pessoa=:idPessoa AND an.status=1";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':idPessoa', $idPessoa);
            $stmt->execute();
            $quantidade = $stmt->fetch();
            return $quantidade['qtd_notificacoes'];
        } catch (PDOException $e) {
            echo 'Erro ao tentar contar a quantidade de intercorrências não lidas ainda: ' . $e->getMessage();
        }
    }

    /**
     * Recebe como parâmetro o id de uma notificação e altera o status dela de ativa para inativa.
     */
    public function alterarStatus($idNotificacao)
    {
        $sql = "UPDATE aviso_notificacao SET status = 0 WHERE id_aviso_notificacao=:idNotificacao";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':idNotificacao', $idNotificacao);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Erro ao tentar marcar uma intercorrência como lida: ' . $e->getMessage();
        }
    }
}
