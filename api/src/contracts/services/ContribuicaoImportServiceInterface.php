<?php

namespace api\contracts\services;

use Psr\Http\Message\UploadedFileInterface;

interface ContribuicaoImportServiceInterface
{
    public function importar(
        UploadedFileInterface $arquivo,
        int $ano,
        ?int $idMeioPagamento = null
    ): array;
}
