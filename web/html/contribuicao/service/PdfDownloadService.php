<?php
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PaymentServiceException.php';

class PdfDownloadService
{
    public static function baixarConteudo(string $url, string $contexto = 'PDF'): string
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_HEADER => false,
            CURLOPT_ENCODING => '',
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $erroCurl = curl_error($ch);
            curl_close($ch);

            throw new PaymentServiceException(
                "Não foi possível concluir a geração do {$contexto} no momento.",
                'Erro ao baixar o PDF: ' . $erroCurl,
                502
            );
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        curl_close($ch);

        if ($httpCode !== 200) {
            throw new PaymentServiceException(
                "Não foi possível concluir a geração do {$contexto} no momento.",
                "Erro ao baixar o PDF: HTTP {$httpCode}",
                $httpCode
            );
        }

        $conteudoEhPdf = stripos($contentType, 'application/pdf') !== false;
        $assinaturaPdf = substr(ltrim($response), 0, 5) === '%PDF-';

        if (!$conteudoEhPdf && !$assinaturaPdf) {
            throw new PaymentServiceException(
                "Não foi possível concluir a geração do {$contexto} no momento.",
                'Erro: o conteúdo da URL não é um PDF.',
                400
            );
        }

        return $response;
    }
}
