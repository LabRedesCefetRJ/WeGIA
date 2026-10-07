<?php

require_once __DIR__ . '/Arquivo.php';
require_once __DIR__ . '/Csrf.php';

final class CotacaoSuporte
{
    public static function limitesUpload(): array
    {
        return [
            'upload_max_filesize_bytes' => self::converterParaBytes(ini_get('upload_max_filesize')),
            'post_max_size_bytes' => self::converterParaBytes(ini_get('post_max_size')),
        ];
    }

    private static function converterParaBytes(string $valor): int
    {
        $valor = trim($valor);
        $numero = (int) $valor;
        switch (strtolower(substr($valor, -1))) {
            case 'g':
                return $numero * 1024 * 1024 * 1024;
            case 'm':
                return $numero * 1024 * 1024;
            case 'k':
                return $numero * 1024;
            default:
                return $numero;
        }
    }

    public static function tamanhoMaximoArquivo(): int
    {
        return self::converterParaBytes(ini_get('upload_max_filesize'));
    }

    public static function tamanhoMaximoArquivoFormatado(): string
    {
        $valor = ini_get('upload_max_filesize');

        return preg_replace('/([0-9]+)([KMG])/i', '$1 $2B', $valor);
    }

    private const EXTENSOES_PERMITIDAS = [
        'pdf',
        'jpg',
        'jpeg',
        'png'
    ];

    public static function obterIdObrigatorio($campo, $entidade): int
    {
        $id = filter_var($_POST[$campo] ?? $_GET[$campo] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            throw new InvalidArgumentException("O id de {$entidade} informado não é válido.", 400);
        }

        return (int) $id;
    }

    public static function validarCsrf(): void
    {
        if (!Csrf::validateToken($_POST['csrf_token'] ?? null)) {
            throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 403);
        }
    }

    public static function validarArquivo($upload): Arquivo
    {
        if (!is_array($upload) || !isset($upload['error'])) {
            throw new InvalidArgumentException(
                'Nenhum arquivo foi enviado.',
                400
            );
        }

        if (!is_int($upload['error'])) {
            throw new InvalidArgumentException(
                'Os dados do arquivo são inválidos.',
                400
            );
        }

        if (in_array($upload['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            throw new InvalidArgumentException(
                'O arquivo deve possuir no máximo ' . self::tamanhoMaximoArquivoFormatado() . '.',
                413
            );
        }

        if ($upload['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException(
                'Não foi possível receber o arquivo.',
                400
            );
        }

        if (
            !isset($upload['size'], $upload['name'], $upload['tmp_name']) ||
            !is_int($upload['size']) ||
            !is_string($upload['name']) ||
            !is_string($upload['tmp_name'])
        ) {
            throw new InvalidArgumentException(
                'Os dados do arquivo são inválidos.',
                400
            );
        }

        if ($upload['size'] < 1 || $upload['size'] > self::tamanhoMaximoArquivo()) {
            throw new InvalidArgumentException(
                'O arquivo deve possuir no máximo ' . self::tamanhoMaximoArquivoFormatado() . '.',
                413
            );
        }

        $extensao = strtolower(
            pathinfo($upload['name'], PATHINFO_EXTENSION)
        );

        if (!in_array($extensao, self::EXTENSOES_PERMITIDAS, true)) {
            throw new InvalidArgumentException(
                'O arquivo do orçamento deve estar nos formatos PDF, JPG, JPEG ou PNG.',
                415
            );
        }

        try {
            $arquivo = Arquivo::fromUpload($upload);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException(
                'O arquivo enviado não é válido.',
                415,
                $e
            );
        }

        return $arquivo;
    }

    public static function origemLista(): string
    {
        return ($_POST['origem'] ?? $_GET['origem'] ?? '') === 'historico'
            ? 'historico' : 'andamento';
    }

    public static function respostaFormulario(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
            && ($_SERVER['HTTP_X_COTACAO_FORM'] ?? '') === '1';
    }

    public static function redirecionar(?int $id_cotacao = null): void
    {
        $parametros = ['nomeClasse' => 'CotacaoControle', 'metodo' => 'listar', 'tipo' => self::origemLista()];
        if ($id_cotacao !== null) {
            $parametros['metodo'] = 'visualizar';
            $parametros['id_cotacao'] = $id_cotacao;
            $parametros['origem'] = self::origemLista();
            unset($parametros['tipo']);
        }

        $url = WWW . 'controle/control.php?' . http_build_query($parametros);
        if (self::respostaFormulario()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'sucesso', 'redirect' => $url]);
            exit;
        }
        header('Location: ' . $url);
        exit;
    }
}
