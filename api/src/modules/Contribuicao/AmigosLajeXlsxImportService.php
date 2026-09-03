<?php

namespace api\modules\Contribuicao;

use api\contracts\services\ContribuicaoImportServiceInterface;
use DateTime;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Psr\Http\Message\UploadedFileInterface;
use Throwable;

class AmigosLajeXlsxImportService implements ContribuicaoImportServiceInterface
{
    private const MESES = ['JAN' => 1, 'FEV' => 2, 'MAR' => 3, 'ABR' => 4, 'MAI' => 5, 'JUN' => 6, 'JUL' => 7, 'AGO' => 8, 'SET' => 9, 'OUT' => 10, 'NOV' => 11, 'DEZ' => 12];

    public function __construct(private ContribuicaoRepository $repository)
    {
    }

    public function importar(UploadedFileInterface $arquivo, int $ano, ?int $idMeioPagamento = null): array
    {
        if ($arquivo->getError() !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Não foi possível receber o arquivo enviado.', 400);
        }

        $caminho = $arquivo->getStream()->getMetadata('uri');
        if (!is_string($caminho) || !is_file($caminho)) {
            throw new InvalidArgumentException('Arquivo de importação indisponível.', 400);
        }

        try {
            $planilha = IOFactory::load($caminho);
        } catch (Throwable $e) {
            throw new InvalidArgumentException('Arquivo XLSX inválido ou corrompido.', 400, $e);
        }

        $socios = $this->indexarSocios();
        $resultado = ['importados' => 0, 'duplicados' => 0, 'rejeitados' => []];

        foreach ($planilha->getWorksheetIterator() as $planilhaAba) {
            if (mb_strtolower(trim($planilhaAba->getTitle())) === 'modelo') {
                continue;
            }

            $cabecalho = array_map(
                fn ($valor) => strtoupper(trim((string)$valor)),
                $planilhaAba->rangeToArray('A1:N1', null, true, true, false)[0] ?? []
            );
            if (array_slice($cabecalho, 0, 2) !== ['NOME', 'VALOR']) {
                throw new InvalidArgumentException("Cabeçalho inválido na aba '{$planilhaAba->getTitle()}'.", 400);
            }

            $colunasMes = array_flip(array_slice($cabecalho, 2, 12));
            foreach (range(1, 12) as $mes) {
                if (!isset($colunasMes[array_search($mes, self::MESES, true)])) {
                    throw new InvalidArgumentException("Colunas de meses inválidas na aba '{$planilhaAba->getTitle()}'.", 400);
                }
            }

            $ultimaLinha = $planilhaAba->getHighestDataRow();
            for ($linha = 2; $linha <= $ultimaLinha; $linha++) {
                $nome = trim((string)$planilhaAba->getCell("A{$linha}")->getFormattedValue());
                $valor = $this->lerValor($planilhaAba->getCell("B{$linha}")->getFormattedValue());
                if ($nome === '' && $valor === null) {
                    continue;
                }

                $socioIds = $socios[$this->normalizarTexto($nome)] ?? [];
                if (count($socioIds) !== 1) {
                    $resultado['rejeitados'][] = ['aba' => $planilhaAba->getTitle(), 'linha' => $linha, 'motivo' => count($socioIds) === 0 ? 'Sócio não encontrado.' : 'Nome de sócio ambíguo.'];
                    continue;
                }
                if ($valor === null || $valor <= 0) {
                    $resultado['rejeitados'][] = ['aba' => $planilhaAba->getTitle(), 'linha' => $linha, 'motivo' => 'Valor inválido.'];
                    continue;
                }

                foreach (self::MESES as $nomeMes => $numeroMes) {
                    $coluna = chr(ord('C') + $numeroMes - 1);
                    $dia = trim((string)$planilhaAba->getCell("{$coluna}{$linha}")->getFormattedValue());
                    if ($dia === '' || strtoupper($dia) === 'X' || !preg_match('/^\d{1,2}$/', $dia)) {
                        continue;
                    }

                    try {
                        $data = new DateTime(sprintf('%04d-%02d-%02d', $ano, $numeroMes, (int)$dia));
                        if ($data->format('Y-m-d') !== sprintf('%04d-%02d-%02d', $ano, $numeroMes, (int)$dia)) {
                            throw new InvalidArgumentException('Data de pagamento inválida.');
                        }
                    } catch (Throwable) {
                        $resultado['rejeitados'][] = ['aba' => $planilhaAba->getTitle(), 'linha' => $linha, 'mes' => $nomeMes, 'motivo' => 'Data de pagamento inválida.'];
                        continue;
                    }

                    $dataTexto = $data->format('Y-m-d');
                    if ($this->repository->existeContribuicao($socioIds[0], $valor, $dataTexto)) {
                        $resultado['duplicados']++;
                        continue;
                    }

                    $contribuicao = new Contribuicao(null, null, $idMeioPagamento, $socioIds[0], $valor, clone $data, clone $data, clone $data, 'paid');
                    if ($this->repository->create($contribuicao)) {
                        $resultado['importados']++;
                    }
                }
            }
        }

        return $resultado;
    }

    private function indexarSocios(): array
    {
        $index = [];
        foreach ($this->repository->findSociosComPessoas() as $socio) {
            $nome = $this->normalizarTexto(trim(($socio['nome'] ?? '') . ' ' . ($socio['sobrenome'] ?? '')));
            if ($nome !== '') {
                $index[$nome][] = (int)$socio['id_socio'];
            }
        }
        return $index;
    }

    private function normalizarTexto(string $texto): string
    {
        $texto = trim(preg_replace('/\s+/', ' ', $texto) ?? '');
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;
        return mb_strtolower($texto);
    }

    private function lerValor(string $valor): ?float
    {
        $valor = trim(str_replace(['R$', ' '], '', $valor));
        if ($valor === '') {
            return null;
        }
        if (str_contains($valor, ',') && str_contains($valor, '.')) {
            $valor = str_replace('.', '', $valor);
        }
        $valor = str_replace(',', '.', $valor);
        return is_numeric($valor) ? round((float)$valor, 2) : null;
    }
}
