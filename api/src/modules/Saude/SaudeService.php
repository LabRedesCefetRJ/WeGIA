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