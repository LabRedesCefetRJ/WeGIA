<?php

namespace api\modules\Configuracao;

use api\modules\Auth\UserRepository;
use Psr\Http\Message\UploadedFileInterface;

class DocumentoModeloService
{
    private DocumentoModeloRepository $repository;
    private UserRepository $userRepository;

    public function __construct(DocumentoModeloRepository $repository, UserRepository $userRepository)
    {
        $this->repository = $repository;
        $this->userRepository = $userRepository;
    }

    /**
     * @return array<int, array{id:int, descricao:string, extensao:string|null, created_at:string|null, updated_at:string|null, global:int}>
     */
    public function listarModelos(): array
    {
        return $this->repository->listarModelos();
    }

    /**
     * @return array{conteudo:string, extensao:string, mime_type:string}
     */
    public function obterArquivoModelo(int $id): array
    {
        if ($id < 1) {
            throw new \InvalidArgumentException('ID do modelo de documento inválido.', 400);
        }

        $modelo = $this->repository->buscarArquivoModelo($id);
        if ($modelo === null || $modelo['documento'] === null || $modelo['documento'] === '') {
            throw new \RuntimeException('Arquivo do modelo de documento não encontrado.', 404);
        }

        $extensao = strtolower((string) ($modelo['extensao'] ?? ''));
        $mimeTypes = [
            'odt' => 'application/vnd.oasis.opendocument.text',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        if (!isset($mimeTypes[$extensao])) {
            throw new \RuntimeException('Formato do arquivo do modelo não suportado.', 404);
        }

        return [
            'conteudo' => $modelo['documento'],
            'extensao' => $extensao,
            'mime_type' => $mimeTypes[$extensao],
        ];
    }

    public function salvarModelo(string $descricao, UploadedFileInterface $arquivo, int $idPessoa, bool $global = false): int
    {
        $descricao = trim($descricao);
        if ($descricao === '') {
            throw new \InvalidArgumentException('Descrição do modelo de documento é obrigatória', 400);
        }

        $idFuncionario = $this->userRepository->findFuncionarioIdByPessoaId($idPessoa);
        if ($idFuncionario === null) {
            throw new \RuntimeException('Usuário não possui funcionário associado para registrar o modelo.', 403);
        }

        if ($global && !$this->userRepository->isAdministrador($idPessoa)) {
            throw new \InvalidArgumentException('Apenas administradores podem criar modelos globais.', 403);
        }

        $arquivoNome = $arquivo->getClientFilename() ?? '';
        $extensao = strtolower(pathinfo($arquivoNome, PATHINFO_EXTENSION));

        if (!in_array($extensao, ['odt', 'docx'], true)) {
            throw new \InvalidArgumentException('Formato do arquivo inválido. Envie um arquivo .odt ou .docx.', 400);
        }

        if ($arquivo->getError() !== UPLOAD_ERR_OK) {
            throw new \InvalidArgumentException('Arquivo enviado com erro.', 400);
        }

        $conteudo = $arquivo->getStream()->getContents();
        if ($conteudo === '') {
            throw new \InvalidArgumentException('Arquivo vazio.', 400);
        }

        $id = $this->repository->salvarModelo($descricao, $conteudo, $extensao, $idFuncionario, $global);
        if ($id === false || $id <= 0) {
            throw new \RuntimeException('Não foi possível salvar o modelo de documento.', 500);
        }

        return $id;
    }
}
