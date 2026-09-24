<?php

require_once ROOT . '/classes/Cotacao.php';
require_once ROOT . '/classes/CotacaoSuporte.php';
require_once ROOT . '/dao/CotacaoDAO.php';

require_once ROOT . '/classes/Orcamento.php';
require_once ROOT . '/classes/OrcamentoDoc.php';
require_once ROOT . '/classes/Arquivo.php';

require_once ROOT . '/dao/Conexao.php';
require_once ROOT . '/dao/OrcamentoDAO.php';
require_once ROOT . '/dao/OrcamentoDocDAO.php';
require_once ROOT . '/dao/OrigemDAO.php';
require_once ROOT . '/html/permissao/permissao.php';

class CotacaoControle
{
    private PDO $pdo;
    private CotacaoDAO $cotacaoDAO;
    private OrcamentoDAO $orcamentoDAO;
    private OrcamentoDocDAO $orcamentoDocDAO;

    public function __construct()
    {
        $this->pdo = Conexao::connect();

        $this->cotacaoDAO = new CotacaoDAO($this->pdo);
        $this->orcamentoDAO = new OrcamentoDAO($this->pdo);
        $this->orcamentoDocDAO = new OrcamentoDocDAO($this->pdo);
    }

    public function incluir(): void
    {
        permissao($_SESSION['id_pessoa'], 26, 3);
        CotacaoSuporte::validarCsrf();

        try {
            $this->pdo->beginTransaction();

            $cotacao = $this->criarCotacaoDaRequisicao();
            $id_cotacao = $this->cotacaoDAO->incluir($cotacao);

            $fornecedores = $_POST['id_fornecedor'] ?? [];
            $valores = $_POST['valor'] ?? [];
            $prazos = $_POST['prazo_entrega'] ?? [];

            if (!is_array($fornecedores) || !is_array($valores) || !is_array($prazos)) {
                throw new InvalidArgumentException(
                    'Os dados dos orçamentos são inválidos.',
                    400
                );
            }

            if (count($fornecedores) < 1) {
                throw new InvalidArgumentException(
                    'A cotação deve possuir pelo menos um orçamento.',
                    400
                );
            }

            if (count($fornecedores) > 3) {
                throw new InvalidArgumentException(
                    'A cotação pode possuir no máximo três orçamentos.',
                    400
                );
            }

            if (!$this->indicesSaoSequenciais($fornecedores)) {
                throw new InvalidArgumentException(
                    'Os dados dos orçamentos são inválidos.',
                    400
                );
            }

            foreach ($fornecedores as $indice => $id_fornecedor) {

                $orcamento = new Orcamento(
                    $id_cotacao,
                    $id_fornecedor,
                    $prazos[$indice] ?? null,
                    $valores[$indice] ?? null
                );

                $id_orcamento = $this->orcamentoDAO->incluir($orcamento);

                if ($id_orcamento < 1) {
                    throw new InvalidArgumentException(
                        'Não foi possível adicionar o orçamento à cotação.',
                        409
                    );
                }

                $erroArquivo = $this->obterErroUpload($indice);

                if ($erroArquivo !== UPLOAD_ERR_NO_FILE) {

                    $upload = $this->obterUpload($indice);

                    $arquivo = CotacaoSuporte::validarArquivo($upload);

                    $documento = new OrcamentoDoc(
                        $id_orcamento,
                        $arquivo
                    );

                    $this->orcamentoDocDAO->incluir($documento);
                }
            }

            $this->pdo->commit();

            $_SESSION['msg'] = 'Cotação cadastrada com sucesso.';
            CotacaoSuporte::redirecionar($id_cotacao);

        } catch (Throwable $e) {
            if($this->pdo->inTransaction()) {
                $this->pdo->rollback();
            }

            throw $e;
        }
    }

    public function excluir(): void
    {
        permissao($_SESSION['id_pessoa'], 26, 3);
        CotacaoSuporte::validarCsrf();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            throw new InvalidArgumentException('A exclusão exige uma requisição POST.', 405);
        }
        $id_cotacao = CotacaoSuporte::obterIdObrigatorio('id_cotacao', 'cotação');
        $alterados = $this->cotacaoDAO->excluir($id_cotacao);

        if ($alterados < 1) {
            throw new InvalidArgumentException(
                'A cotação não existe ou não está mais em análise. O histórico não pode ser excluído.',
                409
            );
        }

        $_SESSION['msg'] = 'Cotação excluída com sucesso.';
        CotacaoSuporte::redirecionar();
    }

    public function visualizar(): void
    {
        permissao($_SESSION['id_pessoa'], 26, 5);

        $id_cotacao = CotacaoSuporte::obterIdObrigatorio('id_cotacao', 'cotação');
        $cotacao = $this->cotacaoDAO->buscarPorId($id_cotacao);

        if ($cotacao === null) {
            throw new InvalidArgumentException(
                'A cotação informada não existe.',
                404
            );
        }

        $orcamentos = $this->orcamentoDAO->listarDetalhesPorCotacao($id_cotacao);

        $fornecedores = $this->listarFornecedores();

        require ROOT . '/html/matPat/visualizar_cotacao.php';
        exit;
    }

    public function listar(): void
    {
        permissao($_SESSION['id_pessoa'], 26, 5);

        $tipo = $_GET['tipo'] ?? 'andamento';

        if (!is_string($tipo) || !in_array($tipo, ['andamento', 'historico'], true)) {
            $tipo = 'andamento';
        }

        $cotacoes = $tipo === 'historico'
            ? $this->cotacaoDAO->listarHistorico()
            : $this->cotacaoDAO->listarEmAndamento();

        require ROOT . '/html/matPat/listar_cotacoes.php';
        exit;
    }

    public function cadastrar(): void
    {
        permissao($_SESSION['id_pessoa'], 26, 3);

        $fornecedores = $this->listarFornecedores();

        require ROOT . '/html/matPat/cadastro_cotacao.php';
        exit;
    }

    private function listarFornecedores(): array
    {
        $origemDAO = new OrigemDAO();
        $fornecedores = json_decode($origemDAO->listarId_Nome(), true);

        if (!is_array($fornecedores)) {
            throw new RuntimeException(
                'Não foi possível carregar os dados da cotação.',
                500
            );
        }

        return array_filter(
            $fornecedores,
            function ($fornecedor) {
                return isset($fornecedor['nome_origem']) &&
                    $fornecedor['nome_origem'] !== 'Doador não identificado';
            }
        );
    }

    private function criarCotacaoDaRequisicao(): Cotacao
    {
        $id_responsavel = $_SESSION['id_pessoa'] ?? null;

        $descricao = $_POST['descricao'] ?? null;

        if (!is_string($descricao) || trim($descricao) === '') {
            throw new InvalidArgumentException(
                'A descrição da cotação é obrigatória.',
                400
            );
        }

        return new Cotacao(
            $id_responsavel,
            'analise',
            $descricao,
            null,
            null
        );
    }

    private function indicesSaoSequenciais(array $valores): bool
    {
        return array_keys($valores) === range(0, count($valores) - 1);
    }

    private function obterUpload($indice): array
    {
        $camposObrigatorios = ['name', 'tmp_name', 'error', 'size'];
        $upload = [];

        foreach ($camposObrigatorios as $campo) {
            if (!isset($_FILES['arquivo'][$campo][$indice])) {
                throw new InvalidArgumentException(
                    'Os dados do arquivo do orçamento são inválidos.',
                    400
                );
            }

            $upload[$campo] = $_FILES['arquivo'][$campo][$indice];
        }

        $upload['type'] = $_FILES['arquivo']['type'][$indice] ?? '';

        return $upload;
    }

    private function obterErroUpload($indice): int
    {
        if (!isset($_FILES['arquivo'])) {
            return UPLOAD_ERR_NO_FILE;
        }

        if (
            !is_array($_FILES['arquivo']) ||
            !isset($_FILES['arquivo']['error']) ||
            !is_array($_FILES['arquivo']['error'])
        ) {
            throw new InvalidArgumentException(
                'Os dados do arquivo do orçamento são inválidos.',
                400
            );
        }

        $erro = $_FILES['arquivo']['error'][$indice] ?? UPLOAD_ERR_NO_FILE;

        if (!is_int($erro)) {
            throw new InvalidArgumentException(
                'Os dados do arquivo do orçamento são inválidos.',
                400
            );
        }

        return $erro;
    }

    public function escolherOrcamento(): void
    {
        permissao($_SESSION['id_pessoa'], 26, 3);
        CotacaoSuporte::validarCsrf();

        $id_cotacao = CotacaoSuporte::obterIdObrigatorio('id_cotacao','cotação');

        $id_orcamento = CotacaoSuporte::obterIdObrigatorio('id_orcamento','orçamento');

        $cotacao = $this->cotacaoDAO->buscarPorId(
            $id_cotacao
        );

        if ($cotacao === null) {
            throw new InvalidArgumentException(
                'A cotação informada não existe.',
                404
            );
        }

        if ($cotacao['status'] !== 'analise') {
            throw new InvalidArgumentException(
                'Somente cotações em análise podem ser concluídas.',
                409
            );
        }

        $justificativa = $_POST['justificativa'] ?? null;

        if ($justificativa !== null && !is_string($justificativa)) {
            throw new InvalidArgumentException(
                'A justificativa deve ser um texto.',
                400
            );
        }

        if ($justificativa !== null) {
            $justificativa = trim($justificativa);

            if ($justificativa === '') {
                $justificativa = null;
            }   
        }

        if (
            $justificativa !== null &&
            mb_strlen($justificativa, 'UTF-8') > 255
        ) {
            throw new InvalidArgumentException(
                'A justificativa deve ter no máximo 255 caracteres.',
                400
            );
        }

        $orcamento = $this->orcamentoDAO->buscarPorId($id_orcamento);

        if ($orcamento === null) {
            throw new InvalidArgumentException(
                'O orçamento informado não existe.',
                404
            );
        }

        if ($orcamento->getId_cotacao() !== $id_cotacao) {
            throw new InvalidArgumentException(
                'O orçamento informado não pertence a esta cotação.',
                400
            );
        }

        $alterados = $this->cotacaoDAO->escolherOrcamento(
            $id_cotacao,
            $id_orcamento,
            $justificativa
        );

        if ($alterados < 1) {
            throw new InvalidArgumentException(
                'A cotação não pode mais ser concluída.',
                409
            );
        }

        $_SESSION['msg'] = 'Orçamento selecionado e cotação concluída com sucesso.';

        CotacaoSuporte::redirecionar($id_cotacao);
    }

}
