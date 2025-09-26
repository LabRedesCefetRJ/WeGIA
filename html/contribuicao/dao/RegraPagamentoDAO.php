<?php

//requisitar arquivo de conexão
require_once '../dao/ConexaoDAO.php';

class RegraPagamentoDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo=null)
    {
        if (is_null($pdo)) {
            $this->pdo = ConexaoDAO::conectar();
        } else {
            $this->pdo = $pdo;
        }
    }

    /**
     * Retorna todas as regras de contribuição presentes no banco de dados da aplicação
     */
    public function buscaRegrasContribuicao()
    {
        //definir consulta sql
        $sqlBuscaTodos = "SELECT * FROM contribuicao_regras";
        //executar
        $resultado = $this->pdo->query($sqlBuscaTodos)->fetchAll(PDO::FETCH_ASSOC);
        //retornar resultado
        return $resultado;
    }

    public function buscaConjuntoRegrasPagamento()
    {
        //definir consulta sql
        $sqlBuscaTodos = "SELECT ccr.id, ccr.id_meioPagamento, ccr.id_regra, ccr.valor, ccr.status, cmp.meio, cr.regra, cgp.plataforma, cgp.endpoint   
         FROM contribuicao_conjuntoRegras ccr 
         JOIN contribuicao_meioPagamento cmp ON(cmp.id=ccr.id_meioPagamento) 
         JOIN contribuicao_gatewayPagamento cgp ON(cgp.id = cmp.id_plataforma) 
         JOIN contribuicao_regras cr ON(cr.id=ccr.id_regra)";
        //executar
        $resultado = $this->pdo->query($sqlBuscaTodos)->fetchAll(PDO::FETCH_ASSOC);
        //retornar resultado
        return $resultado;
    }

    public function buscaConjuntoRegrasPagamentoPorIdMeioPagamento(int $idMeioPagamento)//Considerar renomear o método para algo mais simples
    {
        //definir consulta sql
        $sql = "SELECT ccr.id, ccr.id_meioPagamento, ccr.id_regra, ccr.valor, ccr.status, cmp.meio, cr.regra, cgp.plataforma, cgp.endpoint   
        FROM contribuicao_conjuntoRegras ccr 
        JOIN contribuicao_meioPagamento cmp ON(cmp.id=ccr.id_meioPagamento) 
        JOIN contribuicao_gatewayPagamento cgp ON(cgp.id = cmp.id_plataforma) 
        JOIN contribuicao_regras cr ON(cr.id=ccr.id_regra) 
        WHERE ccr.id_meioPagamento =:idMeioPagamento AND ccr.status=1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':idMeioPagamento', $idMeioPagamento);

        $stmt->execute();

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //retornar resultado
        return $resultado;
    }

    public function buscaConjuntoRegrasPagamentoPorNomeMeioPagamento(string $nomeMeioPagamento){
        //definir consulta sql
        $sql = "SELECT ccr.id, ccr.id_meioPagamento, ccr.id_regra, ccr.valor, ccr.status, cmp.meio, cr.regra, cgp.plataforma, cgp.endpoint   
        FROM contribuicao_conjuntoRegras ccr 
        JOIN contribuicao_meioPagamento cmp ON(cmp.id=ccr.id_meioPagamento) 
        JOIN contribuicao_gatewayPagamento cgp ON(cgp.id = cmp.id_plataforma) 
        JOIN contribuicao_regras cr ON(cr.id=ccr.id_regra) 
        WHERE cmp.meio =:nomeMeioPagamento AND ccr.status=1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':nomeMeioPagamento', $nomeMeioPagamento);

        $stmt->execute();

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //retornar resultado
        return $resultado;
    }

    /**
     * Inseri um novo conjunto de regras no banco de dados da aplicação
     */
    public function cadastrar($meioPagamentoId, $regraContribuicaoId, $valor, $status)
    {
        /*Lógica da aplicação */
        //definir consulta SQL
        $sqlCadastrar = "INSERT INTO contribuicao_conjuntoRegras (id_meioPagamento, id_regra, valor, status) 
        VALUES (:meioPagamentoId, :regraContribuicaoId, :valor, :status)";
        //utilizar prepared statements
        $stmt = $this->pdo->prepare($sqlCadastrar);
        $stmt->bindParam(':meioPagamentoId', $meioPagamentoId);
        $stmt->bindParam(':regraContribuicaoId', $regraContribuicaoId);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':status', $status);
        //executar
        $stmt->execute();
    }


    public function excluirPorId($id)
    {
        //definir consulta sql
        $sqlExcluirPorId = "DELETE FROM contribuicao_conjuntoRegras WHERE id=:id";
        //utilizar prepared statements
        $stmt = $this->pdo->prepare($sqlExcluirPorId);
        $stmt->bindParam(':id', $id);
        //executar
        $stmt->execute();

        //verificar se algum elemento foi de fato excluído
        $conjuntoRegraPagamentoExcluido = $stmt->rowCount();

        if ($conjuntoRegraPagamentoExcluido < 1) {
            throw new Exception();
        }
    }

    /**
     * Edita o meio de pagamento que possuí id equivalente no 
     */
    public function editarPorId($id, $valor)
    {
        //definir consulta sql
        $sqlEditarPorId = "UPDATE contribuicao_conjuntoRegras SET valor =:valor WHERE id=:id";
        //utilizar prepared statements
        $stmt = $this->pdo->prepare($sqlEditarPorId);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':id', $id);
        //executar
        $stmt->execute();

        //verificar se algum elemento foi de fato alterado
        $meioPagamentoExcluido = $stmt->rowCount();

        if ($meioPagamentoExcluido < 1) {
            throw new Exception();
        }
    }

    /**
     * Modifica o campo status da tabela contribuicao_conjuntoRegras de acordo com o id fornecido
     */
    public function alterarStatusPorId($status, $regraPagamentoId)
    {
        //definir consulta sql
        $sqlAlterarStatusPorId = "UPDATE contribuicao_conjuntoRegras SET status =:status WHERE id=:regraPagamentoId";
        //utilizar prepared statements
        $stmt = $this->pdo->prepare($sqlAlterarStatusPorId);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':regraPagamentoId', $regraPagamentoId);
        //executar
        $stmt->execute();

        //verificar se algum elemento foi de fato alterado
        $regraAlterada = $stmt->rowCount();

        if ($regraAlterada < 1) {
            throw new Exception();
        }
    }
}
