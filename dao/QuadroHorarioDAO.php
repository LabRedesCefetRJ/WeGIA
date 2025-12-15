<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Conexao.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Documento.php';

class QuadroHorarioDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        isset($pdo) ? $this->pdo = $pdo : $this->pdo = Conexao::connect();
    }

    public function incluir($quadro_horario)
    {
        $sql = 'call cadhorariofunc(:escala, :tipo, :carga_horaria, :entrada1, :saida1,:entrada2,:saida2, :total, :dias_trabalhados, :folga)';
        $sql = str_replace("'", "\'", $sql);
        $stmt = $this->pdo->prepare($sql);

        $escala = $quadro_horario->getEscala();
        $tipo = $quadro_horario->getTipo();
        $carga_horaria = $quadro_horario->getCarga_horaria();
        $entrada1 = $quadro_horario->getEntrada1();
        $saida1 = $quadro_horario->getSaida1();
        $entrada2 = $quadro_horario->getEntrada2();
        $saida2 = $quadro_horario->getSaida2();
        $total = $quadro_horario->getTotal();
        $dias_trabalhados = $quadro_horario->getDias_trabalhados();
        $folga = $quadro_horario->getFolga();

        $stmt->bindParam(':escala', $escala);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':carga_horaria', $carga_horaria);
        $stmt->bindParam(':entrada1', $entrada1);
        $stmt->bindParam(':saida1', $saida1);
        $stmt->bindParam(':entrada2', $entrada2);
        $stmt->bindParam(':saida2', $saida2);
        $stmt->bindParam(':total', $total);
        $stmt->bindParam(':dias_trabalhados', $dias_trabalhados);
        $stmt->bindParam(':folga', $folga);

        $stmt->execute();
    }

    public function alterar($quadro_horario, $id_funcionario)
    {
        try {
            $pdo = Conexao::connect();
            $quadro = $pdo->query("SELECT id_quadro_horario FROM quadro_horario_funcionario WHERE id_funcionario=$id_funcionario;")->fetch(PDO::FETCH_ASSOC);
            if ($quadro) {
                $sql = 'UPDATE quadro_horario_funcionario SET escala=:escala, tipo=:tipo, carga_horaria=:carga_horaria, entrada1=:entrada1, saida1=:saida1,entrada2=:entrada2,saida2=:saida2, total=:total, dias_trabalhados=:dias_trabalhados, folga=:folga WHERE id_funcionario=:id_funcionario';
                $sql = str_replace("'", "\'", $sql);
                $stmt = $pdo->prepare($sql);

                $escala = $quadro_horario->getEscala();
                $tipo = $quadro_horario->getTipo();
                $carga_horaria = $quadro_horario->getCarga_horaria();
                $entrada1 = $quadro_horario->getEntrada1();
                $saida1 = $quadro_horario->getSaida1();
                $entrada2 = $quadro_horario->getEntrada2();
                $saida2 = $quadro_horario->getSaida2();
                $total = $quadro_horario->getTotal();
                $dias_trabalhados = $quadro_horario->getDias_trabalhados();
                $folga = $quadro_horario->getFolga();

                $stmt->bindParam(':id_funcionario', $id_funcionario);
                $stmt->bindParam(':escala', $escala);
                $stmt->bindParam(':tipo', $tipo);
                $stmt->bindParam(':carga_horaria', $carga_horaria);
                $stmt->bindParam(':entrada1', $entrada1);
                $stmt->bindParam(':saida1', $saida1);
                $stmt->bindParam(':entrada2', $entrada2);
                $stmt->bindParam(':saida2', $saida2);
                $stmt->bindParam(':total', $total);
                $stmt->bindParam(':dias_trabalhados', $dias_trabalhados);
                $stmt->bindParam(':folga', $folga);

                $stmt->execute();
            } else {
                $this->incluir($quadro_horario, $id_funcionario);
            }
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela quadro horario = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    public function adicionarTipo($descricao): bool
    {
        $ins = $this->pdo->prepare("INSERT IGNORE INTO tipo_quadro_horario (descricao) VALUES (:descricao)");
        $ins->bindParam(':descricao', $descricao, PDO::PARAM_STR);
        $ins->execute();

        if ($ins->rowCount() < 1) {
            $_SESSION['flag'] = 'warn';
            return false;
        }

        return true;
    }

    public function adicionarEscala($desc): bool
    {
        $ins = $this->pdo->prepare("INSERT IGNORE INTO escala_quadro_horario (descricao) VALUES (:descricao)");
        $ins->bindParam(':descricao', $desc, PDO::PARAM_STR);
        $ins->execute();

        if ($ins->rowCount() < 1)
            return false;

        return true;
    }

    public function alterarTipo($id, $desc)
    {
        $pdo = Conexao::connect();
        try {
            $desc = str_replace("'", "\'", $desc);
            $ins = $pdo->prepare("UPDATE tipo_quadro_horario SET descricao=:d WHERE id=:id;");
            $ins->bindParam(':d', $desc, PDO::PARAM_STR);
            $ins->bindParam(':id', $id, PDO::PARAM_INT);
            $ins->execute();
        } catch (PDOException $e) {
            echo "Erro ao alterar o tipo de id $id para '$desc': " . $e->getMessage();
        }
    }

    public function alterarEscala($id, $desc)
    {
        $pdo = Conexao::connect();
        try {
            $desc = str_replace("'", "\'", $desc);
            $ins = $pdo->prepare("UPDATE escala_quadro_horario SET descricao=:d WHERE id=:id;");
            $ins->bindParam(':d', $desc, PDO::PARAM_STR);
            $ins->bindParam(':id', $id, PDO::PARAM_INT);
            $ins->execute();
        } catch (PDOException $e) {
            echo "Erro ao alterar o tipo de id $id para '$desc': " . $e->getMessage();
        }
    }

    public function removerTipo($id)
    {
        $ins = $this->pdo->prepare("DELETE IGNORE FROM tipo_quadro_horario WHERE id_tipo=:id;");
        $ins->bindParam(':id', $id, PDO::PARAM_INT);
        $ins->execute();

        if ($ins->rowCount() < 1) {
            $_SESSION['flag'] = 'warn';
            return false;
        }

        return true;
    }

    public function removerEscala($id)
    {
        $ins = $this->pdo->prepare("DELETE IGNORE FROM escala_quadro_horario WHERE id_escala=:id;");
        $ins->bindParam(':id', $id, PDO::PARAM_INT);
        $ins->execute();

        if ($ins->rowCount() < 1) {
            $_SESSION['flag'] = "warn";
            return false;
        }

        return true;
    }

    public function listarTipos()
    {
        $pdo = Conexao::connect();
        try {
            $tipo = $pdo->query("SELECT * FROM tipo_quadro_horario;")->fetchAll(PDO::FETCH_ASSOC);
            session_start();
            $_SESSION['tipo_quadro_horario'] = json_encode($tipo);
        } catch (PDOException $e) {
            echo "Erro ao listar tipos: " . $e->getMessage();
        }
    }

    public function listarEscalas()
    {
        $pdo = Conexao::connect();
        try {
            $tipo = $pdo->query("SELECT * FROM escala_quadro_horario;")->fetchAll(PDO::FETCH_ASSOC);
            session_start();
            $_SESSION['escala_quadro_horario'] = json_encode($tipo);
        } catch (PDOException $e) {
            echo "Erro ao listar escalas: " . $e->getMessage();
        }
    }
}
