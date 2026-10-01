<?php

namespace api\modules\Saude;

use api\contracts\services\SaudeServiceInterface;
use Psr\Http\Message\UploadedFileInterface;

class SaudeService implements SaudeServiceInterface
{
    private SaudeRepository $saudeRepository;

    public function __construct(SaudeRepository $saudeRepository)
    {
        $this->saudeRepository = $saudeRepository;
    }

    /**
     * @return SaudeEspecialidade[]
     */
    public function listarEspecialidades(): array
    {
        $linhas = $this->saudeRepository->listarEspecialidades();

        return array_map(
            fn (array $linha) => new SaudeEspecialidade(
                (int) ($linha['id'] ?? 0),
                (string) ($linha['descricao'] ?? '')
            ),
            $linhas
        );
    }

    /**
     * @return array<int, array{id:int, descricao:string, extensao:string|null, created_at:string|null, updated_at:string|null}>
     */
    public function listarModelosParecer(): array
    {
        return $this->saudeRepository->listarModelosParecer();
    }

    /**
     * @return array{conteudo:string, extensao:string, mime_type:string}
     */
    public function obterArquivoModeloParecer(int $id): array
    {
        if ($id < 1) {
            throw new \InvalidArgumentException('ID do modelo de parecer inválido.', 400);
        }

        $modelo = $this->saudeRepository->buscarArquivoModeloParecer($id);
        if ($modelo === null || $modelo['arquivo'] === null || $modelo['arquivo'] === '') {
            throw new \RuntimeException('Arquivo do modelo de parecer não encontrado.', 404);
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
            'conteudo' => $modelo['arquivo'],
            'extensao' => $extensao,
            'mime_type' => $mimeTypes[$extensao],
        ];
    }

    public function salvarModeloParecer(string $descricao, UploadedFileInterface $arquivo): int
    {
        $descricao = trim($descricao);
        if ($descricao === '') {
            throw new \InvalidArgumentException('Descrição do modelo de parecer é obrigatória', 400);
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

        $id = $this->saudeRepository->salvarModeloParecer($descricao, $conteudo, $extensao);

        if ($id === false || $id <= 0) {
            throw new \RuntimeException('Não foi possível salvar o modelo de parecer.', 500);
        }

        return $id;
    }
}