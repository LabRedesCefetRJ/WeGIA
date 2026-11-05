<?php

namespace api\modules\Configuracao;

use DOMDocument;
use PhpOffice\PhpWord\IOFactory;
use Throwable;

class DocumentoCorpoHtmlConverter
{
    public function converterParaHtml(string $conteudo, string $extensao): string
    {
        $leitores = [
            'docx' => 'Word2007',
            'odt' => 'ODText',
        ];
        $extensao = strtolower($extensao);

        if (!isset($leitores[$extensao])) {
            throw new \InvalidArgumentException('Formato de documento não suportado para conversão HTML.', 400);
        }

        $arquivoTemporario = tempnam(sys_get_temp_dir(), 'wegia_documento_');
        if ($arquivoTemporario === false) {
            throw new \RuntimeException('Não foi possível preparar o arquivo para conversão HTML.', 500);
        }

        try {
            if (file_put_contents($arquivoTemporario, $conteudo, LOCK_EX) !== strlen($conteudo)) {
                throw new \RuntimeException('Não foi possível gravar o arquivo temporário para conversão HTML.', 500);
            }

            try {
                $documento = IOFactory::createReader($leitores[$extensao])->load($arquivoTemporario);
                $html = IOFactory::createWriter($documento, 'HTML')->getContent();

                return $this->extrairCorpoHtml($html);
            } catch (Throwable $e) {
                throw new \InvalidArgumentException(
                    'Não foi possível extrair o conteúdo do documento enviado. Verifique se o arquivo está íntegro.',
                    400,
                    $e
                );
            }
        } finally {
            if (is_file($arquivoTemporario) && !unlink($arquivoTemporario)) {
                error_log('[DocumentoCorpoHtmlConverter] Não foi possível remover o arquivo temporário da conversão.');
            }
        }
    }

    private function extrairCorpoHtml(string $html): string
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
            throw new \RuntimeException('Não foi possível interpretar o HTML gerado pelo documento.');
        }

        $corpos = $documentoHtml->getElementsByTagName('body');
        $corpo = $corpos->item(0);
        if ($corpo === null) {
            throw new \RuntimeException('A conversão do documento não gerou um corpo HTML.');
        }

        $conteudo = '';
        foreach ($corpo->childNodes as $no) {
            $htmlNo = $documentoHtml->saveHTML($no);
            if ($htmlNo === false) {
                throw new \RuntimeException('Não foi possível serializar o corpo HTML do documento.');
            }
            $conteudo .= $htmlNo;
        }

        return $conteudo;
    }
}
