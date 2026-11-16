<?php

namespace api\modules\Configuracao;

use DOMDocument;
use FilesystemIterator;

class DocumentoCorpoHtmlConverter
{
    private const TEMPO_LIMITE_CONVERSAO_SEGUNDOS = 60;

    /** @return array{documento_html:string, style:string} */
    public function converterParaDocumento(string $conteudo, string $extensao): array
    {
        $extensao = strtolower($extensao);
        if (!in_array($extensao, ['docx', 'odt'], true)) {
            throw new \InvalidArgumentException('Formato de documento não suportado para conversão HTML.', 400);
        }

        if (!function_exists('proc_open')) {
            throw new \RuntimeException('A função proc_open do PHP é necessária para executar a conversão com LibreOffice.', 500);
        }

        $diretorioTemporario = $this->criarDiretorioTemporario();

        try {
            $arquivoEntrada = $diretorioTemporario . DIRECTORY_SEPARATOR . 'documento.' . $extensao;
            if (file_put_contents($arquivoEntrada, $conteudo, LOCK_EX) !== strlen($conteudo)) {
                throw new \RuntimeException('Não foi possível gravar o arquivo temporário para conversão HTML.', 500);
            }
            $this->validarEstruturaDocumento($arquivoEntrada, $extensao);

            $perfilLibreOffice = $diretorioTemporario . DIRECTORY_SEPARATOR . 'perfil';
            if (!mkdir($perfilLibreOffice, 0700)) {
                throw new \RuntimeException('Não foi possível preparar o perfil temporário do LibreOffice.', 500);
            }

            $arquivoHtml = $diretorioTemporario . DIRECTORY_SEPARATOR . 'documento.html';
            $this->executarConversao($arquivoEntrada, $diretorioTemporario, $perfilLibreOffice);
            $html = @file_get_contents($arquivoHtml);
            if ($html === false) {
                throw new \InvalidArgumentException(
                    'Não foi possível extrair o conteúdo do documento enviado. Verifique se o arquivo está íntegro.',
                    400
                );
            }

            return $this->extrairDocumentoHtml($html, $diretorioTemporario);
        } finally {
            $this->removerDiretorioTemporario($diretorioTemporario);
        }
    }

    private function criarDiretorioTemporario(): string
    {
        for ($tentativa = 0; $tentativa < 3; $tentativa++) {
            $diretorio = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wegia_documento_' . bin2hex(random_bytes(16));
            if (mkdir($diretorio, 0700)) {
                return $diretorio;
            }

            if (!file_exists($diretorio)) {
                throw new \RuntimeException('Não foi possível preparar os arquivos temporários para conversão HTML.', 500);
            }
        }

        throw new \RuntimeException('Não foi possível reservar um diretório temporário para conversão HTML.', 500);
    }

    private function validarEstruturaDocumento(string $arquivo, string $extensao): void
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('A extensão ZIP do PHP é necessária para validar documentos ODT/DOCX.', 500);
        }

        $zip = new \ZipArchive();
        if ($zip->open($arquivo) !== true) {
            throw new \InvalidArgumentException(
                'Não foi possível extrair o conteúdo do documento enviado. Verifique se o arquivo está íntegro.',
                400
            );
        }

        try {
            $arquivosObrigatorios = $extensao === 'docx'
                ? ['[Content_Types].xml', '_rels/.rels', 'word/document.xml']
                : ['mimetype', 'content.xml'];
            foreach ($arquivosObrigatorios as $arquivoObrigatorio) {
                if ($zip->locateName($arquivoObrigatorio) === false) {
                    throw new \InvalidArgumentException(
                        'Não foi possível extrair o conteúdo do documento enviado. Verifique se o arquivo está íntegro.',
                        400
                    );
                }
            }

            if (
                $extensao === 'odt'
                && $zip->getFromName('mimetype') !== 'application/vnd.oasis.opendocument.text'
            ) {
                throw new \InvalidArgumentException(
                    'Não foi possível extrair o conteúdo do documento enviado. Verifique se o arquivo está íntegro.',
                    400
                );
            }
        } finally {
            $zip->close();
        }
    }

    private function executarConversao(string $arquivoEntrada, string $diretorioTemporario, string $perfilLibreOffice): void
    {
        $arquivoSaida = $diretorioTemporario . DIRECTORY_SEPARATOR . 'libreoffice.stdout';
        $arquivoErro = $diretorioTemporario . DIRECTORY_SEPARATOR . 'libreoffice.stderr';
        $perfilUri = 'file://' . str_replace('%2F', '/', rawurlencode($perfilLibreOffice));
        $comando = [
            'libreoffice',
            '--headless',
            '-env:UserInstallation=' . $perfilUri,
            '--convert-to',
            'html:XHTML Writer File:UTF8',
            '--outdir',
            $diretorioTemporario,
            $arquivoEntrada,
        ];

        $processo = @proc_open(
            $comando,
            [
                0 => ['file', '/dev/null', 'r'],
                1 => ['file', $arquivoSaida, 'w'],
                2 => ['file', $arquivoErro, 'w'],
            ],
            $pipes,
            $diretorioTemporario,
            null,
            ['bypass_shell' => true]
        );

        if (!is_resource($processo)) {
            throw new \RuntimeException('LibreOffice não está instalado ou não está acessível no PATH do processo PHP.', 500);
        }

        $inicio = microtime(true);
        $codigoSaida = null;
        $tempoEsgotado = false;

        do {
            $estado = proc_get_status($processo);
            if (!$estado['running']) {
                $codigoSaida = $estado['exitcode'];
                break;
            }

            if (microtime(true) - $inicio >= self::TEMPO_LIMITE_CONVERSAO_SEGUNDOS) {
                $tempoEsgotado = true;
                proc_terminate($processo, 9);
                break;
            }

            usleep(100000);
        } while (true);

        $codigoFechamento = proc_close($processo);
        if ($tempoEsgotado) {
            throw new \RuntimeException('A conversão do documento excedeu o tempo limite do LibreOffice.', 504);
        }

        if ($codigoSaida === null || ($codigoSaida !== 0 && $codigoFechamento !== 0)) {
            $detalheErro = $this->lerSaidaErro($arquivoErro);
            if ($detalheErro !== '') {
                error_log('[DocumentoCorpoHtmlConverter] Falha na conversão pelo LibreOffice: ' . $detalheErro);
            }

            throw new \InvalidArgumentException(
                'Não foi possível extrair o conteúdo do documento enviado. Verifique se o arquivo está íntegro.',
                400
            );
        }
    }

    private function lerSaidaErro(string $arquivoErro): string
    {
        $arquivo = @fopen($arquivoErro, 'rb');
        if ($arquivo === false) {
            return '';
        }

        try {
            return trim((string) fread($arquivo, 4096));
        } finally {
            fclose($arquivo);
        }
    }

    /** @return array{documento_html:string, style:string} */
    private function extrairDocumentoHtml(string $html, string $diretorioTemporario): array
    {
        $documentoHtml = new DOMDocument('1.0', 'UTF-8');
        $errosLibxml = libxml_use_internal_errors(true);

        try {
            $carregado = $documentoHtml->loadHTML(
                '<?xml encoding="UTF-8">' . $html,
                LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($errosLibxml);
        }

        if (!$carregado) {
            throw new \RuntimeException('Não foi possível interpretar o HTML gerado pelo LibreOffice.', 500);
        }

        $this->incorporarImagens($documentoHtml, $diretorioTemporario);

        $corpos = $documentoHtml->getElementsByTagName('body');
        $corpo = $corpos->item(0);
        if ($corpo === null) {
            throw new \RuntimeException('A conversão do documento não gerou um corpo HTML.', 500);
        }

        $raizHtml = $documentoHtml->documentElement;
        if ($raizHtml === null || strtolower($raizHtml->tagName) !== 'html') {
            throw new \RuntimeException('A conversão do documento não gerou um documento HTML completo.', 500);
        }

        $style = $this->extrairEstiloCabecalho($documentoHtml);
        $htmlCompleto = '<!DOCTYPE html>';
        if ($documentoHtml->doctype !== null) {
            $doctype = $documentoHtml->saveHTML($documentoHtml->doctype);
            if ($doctype === false) {
                throw new \RuntimeException('Não foi possível serializar o tipo do documento HTML.', 500);
            }
            if (trim($doctype) !== '') {
                $htmlCompleto = $doctype;
            }
        }

        $htmlRaiz = $documentoHtml->saveHTML($raizHtml);
        if ($htmlRaiz === false) {
            throw new \RuntimeException('Não foi possível serializar o documento HTML completo.', 500);
        }

        return [
            'documento_html' => $htmlCompleto . $htmlRaiz,
            'style' => $style,
        ];
    }

    private function extrairEstiloCabecalho(DOMDocument $documentoHtml): string
    {
        $cabecalho = $documentoHtml->getElementsByTagName('head')->item(0);
        if ($cabecalho === null) {
            return '';
        }

        $regras = [];
        foreach ($cabecalho->getElementsByTagName('style') as $estilo) {
            $css = trim($estilo->textContent);
            if ($css !== '') {
                $regras[] = $css;
            }
        }

        return implode("\n", $regras);
    }

    private function incorporarImagens(DOMDocument $documentoHtml, string $diretorioTemporario): void
    {
        $diretorioReal = realpath($diretorioTemporario);
        if ($diretorioReal === false) {
            throw new \RuntimeException('O diretório temporário da conversão não está disponível.', 500);
        }

        foreach ($documentoHtml->getElementsByTagName('img') as $imagem) {
            $origem = $imagem->getAttribute('src');
            if ($origem === '' || str_starts_with(strtolower($origem), 'data:')) {
                continue;
            }

            $partesUrl = parse_url($origem);
            if ($partesUrl === false) {
                continue;
            }

            $esquema = strtolower($partesUrl['scheme'] ?? '');
            if ($esquema !== '' && $esquema !== 'file') {
                continue;
            }

            $caminho = rawurldecode($partesUrl['path'] ?? '');
            if ($caminho === '' || str_contains($caminho, "\0") || str_contains($caminho, '\\')) {
                continue;
            }

            $arquivoImagem = str_starts_with($caminho, '/')
                ? $caminho
                : $diretorioReal . DIRECTORY_SEPARATOR . $caminho;
            $arquivoReal = realpath($arquivoImagem);
            if (
                $arquivoReal === false
                || !str_starts_with($arquivoReal, $diretorioReal . DIRECTORY_SEPARATOR)
                || !is_file($arquivoReal)
            ) {
                throw new \InvalidArgumentException(
                    'Não foi possível incorporar uma imagem do documento convertido.',
                    400
                );
            }

            $tipoMime = (new \finfo(FILEINFO_MIME_TYPE))->file($arquivoReal);
            $tiposImagemPermitidos = ['image/png', 'image/jpeg', 'image/gif', 'image/bmp', 'image/webp', 'image/tiff'];
            if (!is_string($tipoMime) || !in_array($tipoMime, $tiposImagemPermitidos, true)) {
                throw new \InvalidArgumentException('O documento contém uma imagem em formato não suportado.', 400);
            }

            $conteudoImagem = file_get_contents($arquivoReal);
            if ($conteudoImagem === false) {
                throw new \RuntimeException('Não foi possível ler uma imagem gerada pelo LibreOffice.', 500);
            }

            $imagem->setAttribute('src', 'data:' . $tipoMime . ';base64,' . base64_encode($conteudoImagem));
        }
    }

    private function removerDiretorioTemporario(string $diretorio): void
    {
        if (!is_dir($diretorio)) {
            return;
        }

        foreach (new FilesystemIterator($diretorio, FilesystemIterator::SKIP_DOTS) as $arquivo) {
            $caminho = $arquivo->getPathname();
            if ($arquivo->isDir() && !$arquivo->isLink()) {
                $this->removerDiretorioTemporario($caminho);
            } elseif (!unlink($caminho)) {
                error_log('[DocumentoCorpoHtmlConverter] Não foi possível remover um arquivo temporário da conversão.');
            }
        }

        if (!rmdir($diretorio)) {
            error_log('[DocumentoCorpoHtmlConverter] Não foi possível remover o diretório temporário da conversão.');
        }
    }
}
