<?php
require_once dirname(__DIR__) . '/dao/Conexao.php';
require_once dirname(__DIR__) . '/dao/IntercorrenciaRascunhoDAO.php';
require_once dirname(__DIR__) . '/classes/Util.php';

class IntercorrenciaRascunhoControle
{
    private PDO $pdo;

    public function __construct(PDO $pdo = null)
    {
        if (!is_null($pdo)) {
            $this->pdo = $pdo;
        } else {
            $this->pdo = Conexao::connect();
        }
    }

    private function obterIdFuncionarioSessao(): int
    {
        if (!isset($_SESSION['id_pessoa'])) {
            throw new LogicException('Operação negada: Cliente não autorizado', 401);
        }

        $stmt = $this->pdo->prepare("SELECT id_funcionario FROM funcionario WHERE id_pessoa = :id_pessoa");
        $stmt->bindValue(':id_pessoa', $_SESSION['id_pessoa'], PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$resultado || !isset($resultado['id_funcionario'])) {
            throw new RuntimeException('Funcionário não encontrado.', 404);
        }

        return (int)$resultado['id_funcionario'];
    }

    public function obterRascunho()
    {
        header('Content-Type: application/json');

        try {
            $idFichaMedica = filter_input(INPUT_GET, 'id_fichamedica', FILTER_SANITIZE_NUMBER_INT);

            if (!$idFichaMedica || $idFichaMedica < 1) {
                throw new InvalidArgumentException('Id da ficha médica inválido.', 400);
            }

            $idFuncionario = $this->obterIdFuncionarioSessao();

            $dao = new IntercorrenciaRascunhoDAO($this->pdo);
            $rascunho = $dao->obter((int)$idFichaMedica, $idFuncionario);

            echo json_encode($rascunho ?: ['descricao' => '']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function salvarRascunho()
    {
        header('Content-Type: application/json');

        try {
            $dados = json_decode(file_get_contents('php://input'), true);
            $idFichaMedica = isset($dados['id_fichamedica']) ? (int)$dados['id_fichamedica'] : 0;
            $descricao = isset($dados['descricao']) ? trim($dados['descricao']) : '';

            if (!$idFichaMedica || $idFichaMedica < 1) {
                throw new InvalidArgumentException('Id da ficha médica inválido.', 400);
            }

            $idFuncionario = $this->obterIdFuncionarioSessao();
            $dao = new IntercorrenciaRascunhoDAO($this->pdo);

            if ($descricao === '') {
                $dao->limpar($idFichaMedica, $idFuncionario);
                echo json_encode(['status' => 'ok']);
                return;
            }

            $dao->salvar($idFichaMedica, $idFuncionario, $descricao);
            echo json_encode(['status' => 'ok']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function limparRascunho()
    {
        header('Content-Type: application/json');

        try {
            $dados = json_decode(file_get_contents('php://input'), true);
            $idFichaMedica = isset($dados['id_fichamedica']) ? (int)$dados['id_fichamedica'] : 0;

            if (!$idFichaMedica || $idFichaMedica < 1) {
                throw new InvalidArgumentException('Id da ficha médica inválido.', 400);
            }

            $idFuncionario = $this->obterIdFuncionarioSessao();
            $dao = new IntercorrenciaRascunhoDAO($this->pdo);
            $dao->limpar($idFichaMedica, $idFuncionario);

            echo json_encode(['status' => 'ok']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
