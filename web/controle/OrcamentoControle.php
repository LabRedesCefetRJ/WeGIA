<?php
require_once ROOT . '/dao/Conexao.php';

require_once ROOT . '/classes/Orcamento.php';
require_once ROOT . '/classes/OrcamentoDoc.php';
require_once ROOT . '/classes/CotacaoSuporte.php';
require_once ROOT . '/html/permissao/permissao.php';

require_once ROOT . '/dao/OrcamentoDAO.php';
require_once ROOT . '/dao/OrcamentoDocDAO.php';
require_once ROOT . '/dao/CotacaoDAO.php';

class OrcamentoControle
{

    private OrcamentoDAO $orcamentoDAO;
    private OrcamentoDocDAO $orcamentoDocDAO;
    private CotacaoDAO $cotacaoDAO;
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::connect();

        $this->orcamentoDAO = new OrcamentoDAO($this->pdo);
        $this->orcamentoDocDAO = new OrcamentoDocDAO($this->pdo);
        $this->cotacaoDAO = new CotacaoDAO($this->pdo);
    }

    public function incluir(): void
    {
        permissao($_SESSION['id_pessoa'], 26, 3);
        CotacaoSuporte::validarCsrf();

        $orcamento = $this->criarOrcamentoDaRequisicao();
        $id_cotacao = $orcamento->getId_cotacao();

        $this->validarCotacaoEmAnalise($id_cotacao);

        if ($this->orcamentoDAO->contarPorCotacao($id_cotacao) >= 3) {
            throw new InvalidArgumentException(
                'A cotação pode possuir no máximo três orçamentos.',
                409
            );
        }

        try {
            $this->pdo->beginTransaction();

            $id_orcamento = $this->orcamentoDAO->incluir($orcamento);

            if ($id_orcamento < 1) {
                throw new InvalidArgumentException(
                    'Não foi possível adicionar o orçamento à cotação.',
                    409
                );
            }

            if ($this->possuiNovoArquivo()) {
                $documento = $this->criarDocumentoDaRequisicao($id_orcamento);
                $id_documento = $this->orcamentoDocDAO->incluir($documento);

                if ($id_documento < 1) {
                    throw new RuntimeException(
                        'Não foi possível salvar o arquivo do orçamento.',
                        500
                    );
                }
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }

        $_SESSION['msg'] = 'Orçamento adicionado com sucesso.';
        CotacaoSuporte::redirecionar($id_cotacao);
    }

    public function editar(): void
    {
        permissao($_SESSION['id_pessoa'], 26, 3);
        CotacaoSuporte::validarCsrf();

        $id_orcamento = CotacaoSuporte::obterIdObrigatorio(
            'id_orcamento',
            'orçamento'
        );

        $orcamentoAtual = $this->obterOrcamento($id_orcamento);
        $id_cotacao = $orcamentoAtual->getId_cotacao();

        $this->validarCotacaoEmAnalise($id_cotacao);

        $id_cotacao_informado = CotacaoSuporte::obterIdObrigatorio(
            'id_cotacao',
            'cotação'
        );

        if ($id_cotacao_informado !== $id_cotacao) {
            throw new InvalidArgumentException(
                'O orçamento informado não pertence a esta cotação.',
                400
            );
        }

        $orcamento = $this->criarOrcamentoDaRequisicao($id_cotacao);
        $orcamento->setId_orcamento($id_orcamento);

        $novoDocumento = null;

        if ($this->possuiNovoArquivo()) {
            $novoDocumento = $this->criarDocumentoDaRequisicao(
                $id_orcamento
            );
        }

        try {
            $this->pdo->beginTransaction();

            $this->validarCotacaoEmAnalise($id_cotacao, true);
            $atual = $this->obterOrcamento($id_orcamento, true);
            if ($atual->getId_cotacao() !== $id_cotacao) {
                throw new InvalidArgumentException('O orçamento não pertence a esta cotação.', 409);
            }

            // Com a cotação e o orçamento bloqueados, zero indica dados sem alteração.
            $alterados = $this->orcamentoDAO->editar($orcamento);

            if ($novoDocumento !== null) {
                $this->orcamentoDocDAO->excluir($id_orcamento);

                $id_documento = $this->orcamentoDocDAO->incluir(
                    $novoDocumento
                );

                if ($id_documento < 1) {
                    throw new RuntimeException(
                        'Não foi possível atualizar o arquivo do orçamento.',
                        500
                    );
                }
            }

            $this->pdo->commit();

            $_SESSION['msg'] = $alterados === 0 && $novoDocumento === null
                ? 'Nenhuma alteração nos dados do orçamento.'
                : 'Orçamento editado com sucesso.';

            CotacaoSuporte::redirecionar($id_cotacao);

        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function excluir(): void
    {
        permissao($_SESSION['id_pessoa'], 26, 3);
        CotacaoSuporte::validarCsrf();

        $id_orcamento = CotacaoSuporte::obterIdObrigatorio(
            'id_orcamento',
            'orçamento'
        );

        $orcamento = $this->obterOrcamento($id_orcamento);

        $id_cotacao = $orcamento->getId_cotacao();

        $this->validarCotacaoEmAnalise($id_cotacao);

        $quantidade = $this->orcamentoDAO
            ->contarPorCotacao($id_cotacao);

        if ($quantidade <= 1) {
            throw new InvalidArgumentException(
                'A cotação deve possuir pelo menos um orçamento.',
                409
            );
        }

        $alterados = $this->orcamentoDAO->excluir(
            $id_orcamento
        );

        if ($alterados < 1) {
            throw new InvalidArgumentException(
                'O orçamento informado não existe.',
                404
            );
        }

        $_SESSION['msg'] = 'Orçamento excluído com sucesso.';

        CotacaoSuporte::redirecionar(
            $id_cotacao
        );
    }

    public function adicionarArquivo(): void
    {
        permissao($_SESSION['id_pessoa'], 26, 3);
        CotacaoSuporte::validarCsrf();

        $id_orcamento = CotacaoSuporte::obterIdObrigatorio(
            'id_orcamento',
            'orçamento'
        );

        $orcamento = $this->obterOrcamento($id_orcamento);
        $id_cotacao = $orcamento->getId_cotacao();

        $documento = $this->criarDocumentoDaRequisicao($id_orcamento);

        try {
            $this->pdo->beginTransaction();
            $this->validarCotacaoEmAnalise($id_cotacao, true);
            $atual = $this->obterOrcamento($id_orcamento, true);
            if ($atual->getId_cotacao() !== $id_cotacao) {
                throw new InvalidArgumentException('O orçamento não pertence a esta cotação.', 409);
            }

            if ($this->orcamentoDocDAO->buscarPorOrcamento($id_orcamento) !== null) {
                throw new InvalidArgumentException('Este orçamento já possui um arquivo.', 409);
            }

            $id_documento = $this->orcamentoDocDAO->incluir($documento);
            if ($id_documento < 1) {
                throw new InvalidArgumentException('Não foi possível adicionar o arquivo ao orçamento.', 409);
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        $_SESSION['msg'] = 'Arquivo do orçamento adicionado com sucesso.';

        CotacaoSuporte::redirecionar($id_cotacao);
    }

    public function excluirArquivo(): void
    {
        permissao($_SESSION['id_pessoa'], 26, 3);
        CotacaoSuporte::validarCsrf();

        $id_orcamento = CotacaoSuporte::obterIdObrigatorio(
            'id_orcamento',
            'orçamento'
        );

        $orcamento = $this->obterOrcamento($id_orcamento);
        $id_cotacao = $orcamento->getId_cotacao();

        $this->validarCotacaoEmAnalise($id_cotacao);

        $alterados = $this->orcamentoDocDAO->excluir($id_orcamento);

        if ($alterados < 1) {
            throw new InvalidArgumentException(
                'Arquivo do orçamento não encontrado.',
                404
            );
        }

        $_SESSION['msg'] = 'Arquivo do orçamento excluído com sucesso.';

        CotacaoSuporte::redirecionar($id_cotacao);
    }

    public function visualizarArquivo(): void
    {
        $this->enviarArquivo(true);
    }

    public function baixarArquivo(): void
    {
        $this->enviarArquivo(false);
    }

    private function enviarArquivo(bool $visualizar): void
    {
        permissao($_SESSION['id_pessoa'], 26, 5);
        $id_orcamento = CotacaoSuporte::obterIdObrigatorio(
            'id_orcamento',
            'orçamento'
        );

        $documento = $this->orcamentoDocDAO
            ->buscarPorOrcamento($id_orcamento);

        if ($documento === null) {
            throw new RuntimeException(
                'Arquivo do orçamento não encontrado.',
                404
            );
        }

        $arquivo = $documento->getArquivo();

        $conteudo = base64_decode(
            $arquivo->getConteudo(),
            true
        );

        if ($conteudo === false) {
            throw new RuntimeException(
                'O conteúdo do arquivo do orçamento é inválido.',
                500
            );
        }

        $nome = str_replace(
            ["\r", "\n", '"'],
            '',
            basename($arquivo->getNome())
        );

        header(
            'Content-Type: ' .
            $this->obterMimeType($arquivo->getExtensao())
        );

        header(
            'Content-Length: ' .
            strlen($conteudo)
        );

        header(
            'Content-Disposition: ' . ($visualizar ? 'inline' : 'attachment') . '; filename="' .
            $nome .
            '"'
        );

        header('X-Content-Type-Options: nosniff');

        echo $conteudo;
        exit;
    }

    private function criarOrcamentoDaRequisicao($id_cotacao = null): Orcamento
    {
        $prazo_entrega = $_POST['prazo_entrega'] ?? null;

        if ($prazo_entrega !== null && !is_string($prazo_entrega)) {
            throw new InvalidArgumentException(
                'O prazo de entrega deve ser um texto.',
                400
            );
        }

        return new Orcamento(
            $id_cotacao ?? ($_POST['id_cotacao'] ?? null),
            $_POST['id_fornecedor'] ?? null,
            $prazo_entrega,
            $_POST['valor'] ?? null
        );
    }

    private function criarDocumentoDaRequisicao($id_orcamento): OrcamentoDoc
    {
        $arquivo = CotacaoSuporte::validarArquivo($_FILES['arquivo'] ?? null);

        return new OrcamentoDoc(
            $id_orcamento,
            $arquivo
        );
    }

    private function obterMimeType($extensao): string
    {
        $mimes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'odt' => 'application/vnd.oasis.opendocument.text',
        ];

        return $mimes[strtolower($extensao)]
            ?? 'application/octet-stream';
    }

    private function obterOrcamento($id_orcamento, $bloquear = false): Orcamento
    {
        $orcamento = $this->orcamentoDAO->buscarPorId($id_orcamento, $bloquear);

        if ($orcamento === null) {
            throw new InvalidArgumentException(
                'O orçamento informado não existe.',
                404
            );
        }

        return $orcamento;
    }

    private function validarCotacaoEmAnalise($id_cotacao, $bloquear = false): void
    {
        $cotacao = $bloquear
            ? $this->cotacaoDAO->buscarPorIdParaAtualizacao($id_cotacao)
            : $this->cotacaoDAO->buscarPorId($id_cotacao);

        if ($cotacao === null) {
            throw new InvalidArgumentException(
                'A cotação informada não existe.',
                404
            );
        }

        if ($cotacao['status'] !== 'analise') {
            throw new InvalidArgumentException(
                'A cotação não está em análise.',
                409
            );
        }
    }

    private function possuiNovoArquivo(): bool
    {
        if (!isset($_FILES['arquivo'])) {
            return false;
        }

        if (
            !is_array($_FILES['arquivo']) ||
            !array_key_exists('error', $_FILES['arquivo'])
        ) {
            throw new InvalidArgumentException(
                'Os dados do arquivo são inválidos.',
                400
            );
        }

        if (!is_int($_FILES['arquivo']['error'])) {
            throw new InvalidArgumentException(
                'Os dados do arquivo são inválidos.',
                400
            );
        }

        return $_FILES['arquivo']['error'] !== UPLOAD_ERR_NO_FILE;
    }
}
