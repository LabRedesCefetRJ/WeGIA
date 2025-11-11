<?php
require_once '../classes/Isaida.php';
require_once 'Conexao.php';
require_once '../Functions/funcoes.php';

class IsaidaDAO
{
    // Consultar itens de saída por ID
    public function listarId($id_saida)
    {
        try {
            // Validação simples do parâmetro
            if (!is_numeric($id_saida) || $id_saida <= 0) {
                throw new InvalidArgumentException("ID de saída inválido.");
            }

            $pdo = Conexao::connect();
            $sql = "
                SELECT 
                    i.id_isaida,
                    i.id_saida,
                    p.descricao,
                    i.qtd,
                    i.valor_unitario,
                    u.descricao_unidade
                FROM isaida i 
                RIGHT JOIN produto p ON p.id_produto = i.id_produto 
                INNER JOIN unidade u ON u.id_unidade = p.id_unidade
                WHERE i.id_saida = :id_saida AND i.oculto = false
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_saida', $id_saida, PDO::PARAM_INT);
            $stmt->execute();

            $isaidas = [];
            while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $isaidas[] = [
                    'id_isaida' => $linha['id_isaida'],
                    'id_saida' => $linha['id_saida'],
                    'descricao' => htmlspecialchars($linha['descricao'], ENT_QUOTES, 'UTF-8'),
                    'qtd' => (float)$linha['qtd'],
                    'valor_unitario' => (float)$linha['valor_unitario'],
                    'unidade' => htmlspecialchars($linha['descricao_unidade'], ENT_QUOTES, 'UTF-8')
                ];
            }

            // Evita exposição de dados sensíveis
            return json_encode($isaidas, JSON_UNESCAPED_UNICODE);

        } catch (InvalidArgumentException $e) {
            error_log("Erro de parâmetro em listarId: " . $e->getMessage());
            echo "Parâmetro inválido para consulta.";
        } catch (PDOException $e) {
            error_log("Erro no método listarId (IsaidaDAO): " . $e->getMessage());
            echo "Ocorreu um erro ao consultar os itens de saída.";
        }
    }

    // Inserir um novo registro de item de saída
    public function incluir($isaida)
    {
        try {
            // Verifica se o parâmetro é um objeto Isaida válido
            if (!($isaida instanceof Isaida)) {
                throw new InvalidArgumentException("O objeto informado não é uma instância válida de Isaida.");
            }

            $pdo = Conexao::connect();

            $sql = "
                INSERT INTO isaida (id_saida, id_produto, qtd, valor_unitario) 
                VALUES (:id_saida, :id_produto, :qtd, :valor_unitario)
            ";

            $stmt = $pdo->prepare($sql);

            $id_saida = $isaida->getId_saida()->getId_saida();
            $id_produto = $isaida->getId_produto()->getId_produto();
            $qtd = $isaida->getQtd();
            $valor_unitario = $isaida->getValor_unitario();

            // Validação dos valores
            if (!is_numeric($qtd) || $qtd <= 0) {
                throw new InvalidArgumentException("A quantidade deve ser um número positivo.");
            }
            if (!is_numeric($valor_unitario) || $valor_unitario < 0) {
                throw new InvalidArgumentException("O valor unitário deve ser um número válido.");
            }

            $stmt->bindParam(':id_saida', $id_saida, PDO::PARAM_INT);
            $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
            $stmt->bindParam(':qtd', $qtd);
            $stmt->bindParam(':valor_unitario', $valor_unitario);

            $stmt->execute();

        } catch (InvalidArgumentException $e) {
            error_log("Erro de validação em incluir: " . $e->getMessage());
            echo "Dados inválidos para inserção.";
        } catch (PDOException $e) {
            error_log("Erro no método incluir (IsaidaDAO): " . $e->getMessage());
            echo "Erro ao registrar item de saída.";
        }
    }
}
?>