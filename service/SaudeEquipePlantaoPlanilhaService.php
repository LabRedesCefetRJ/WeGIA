<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'SaudeEquipePlantaoService.php';

class SaudeEquipePlantaoPlanilhaService
{
    private const NS_CALCEXT = 'urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0';
    private const NS_CONFIG = 'urn:oasis:names:tc:opendocument:xmlns:config:1.0';
    private const NS_DRAW = 'urn:oasis:names:tc:opendocument:xmlns:drawing:1.0';
    private const NS_FO = 'urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0';
    private const NS_OFFICE = 'urn:oasis:names:tc:opendocument:xmlns:office:1.0';
    private const NS_STYLE = 'urn:oasis:names:tc:opendocument:xmlns:style:1.0';
    private const NS_TABLE = 'urn:oasis:names:tc:opendocument:xmlns:table:1.0';
    private const NS_TEXT = 'urn:oasis:names:tc:opendocument:xmlns:text:1.0';

    private const MAPA_MESES_TABELA = [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Março',
        4 => 'Abril',
        5 => 'Mai',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro'
    ];

    private const MAPA_MESES_ARQUIVO = [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Marco',
        4 => 'Abril',
        5 => 'Maio',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro'
    ];

    private const NOMES_GLOBAIS_MANTIDOS = [
        'AnoCivil',
        'DiasESemanas',
        'InícioDaSemana',
        'LinhaTítuloRegião1..L3.1'
    ];

    private SaudeEquipePlantaoService $servicePlantao;

    public function __construct(?SaudeEquipePlantaoService $servicePlantao = null)
    {
        $this->servicePlantao = $servicePlantao ?? new SaudeEquipePlantaoService();
    }

    public function gerarPlanilhaMensal(int $ano, int $mes): array
    {
        if (!isset(self::MAPA_MESES_TABELA[$mes])) {
            throw new InvalidArgumentException('Mês inválido para exportação da planilha.', 400);
        }

        $modelo = $this->obterCaminhoModelo();
        if (!is_file($modelo)) {
            throw new RuntimeException('Modelo de planilha não encontrado.');
        }

        $escala = $this->servicePlantao->listarEscalaMensal($ano, $mes);
        $arquivoTemporario = tempnam(sys_get_temp_dir(), 'plantao_');

        if ($arquivoTemporario === false) {
            throw new RuntimeException('Não foi possível preparar o arquivo temporário da planilha.');
        }

        $arquivoSaida = $arquivoTemporario . '.ods';
        @unlink($arquivoSaida);

        if (!copy($modelo, $arquivoSaida)) {
            @unlink($arquivoTemporario);
            throw new RuntimeException('Não foi possível copiar o modelo da planilha.');
        }

        @unlink($arquivoTemporario);

        $zip = new ZipArchive();
        if ($zip->open($arquivoSaida) !== true) {
            @unlink($arquivoSaida);
            throw new RuntimeException('Não foi possível abrir a planilha para edição.');
        }

        $contentXml = $zip->getFromName('content.xml');
        $settingsXml = $zip->getFromName('settings.xml');
        $stylesXml = $zip->getFromName('styles.xml');

        if ($contentXml === false) {
            $zip->close();
            @unlink($arquivoSaida);
            throw new RuntimeException('Arquivo content.xml não encontrado na planilha base.');
        }

        $contentXml = $this->atualizarConteudoPlanilha($contentXml, $ano, $mes, $escala);
        $zip->addFromString('content.xml', $contentXml);

        if ($settingsXml !== false) {
            $settingsXml = $this->atualizarSettingsPlanilha($settingsXml, self::MAPA_MESES_TABELA[$mes]);
            $zip->addFromString('settings.xml', $settingsXml);
        }

        if ($stylesXml !== false) {
            $stylesXml = $this->atualizarStylesPlanilha($stylesXml);
            $zip->addFromString('styles.xml', $stylesXml);
        }

        $zip->close();

        return [
            'caminho' => $arquivoSaida,
            'nome_arquivo' => sprintf('Escala_%04d_%s.ods', $ano, self::MAPA_MESES_ARQUIVO[$mes]),
            'content_type' => 'application/vnd.oasis.opendocument.spreadsheet'
        ];
    }

    private function obterCaminhoModelo(): string
    {
        return dirname(__FILE__, 2)
            . DIRECTORY_SEPARATOR . 'html'
            . DIRECTORY_SEPARATOR . 'saude'
            . DIRECTORY_SEPARATOR . 'template'
            . DIRECTORY_SEPARATOR . 'escala_plantao_base.ods';
    }

    private function atualizarConteudoPlanilha(string $xml, int $ano, int $mes, array $escala): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($xml);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('office', self::NS_OFFICE);
        $xpath->registerNamespace('table', self::NS_TABLE);
        $xpath->registerNamespace('text', self::NS_TEXT);
        $xpath->registerNamespace('draw', self::NS_DRAW);
        $xpath->registerNamespace('style', self::NS_STYLE);

        $this->atualizarAnoCivil($dom, $xpath, $ano);
        $this->limparConteudosDosMeses($dom, $xpath);

        $sheetName = self::MAPA_MESES_TABELA[$mes];
        $tabelaMes = $this->buscarTabelaPorNome($xpath, $sheetName);

        if (!$tabelaMes) {
            throw new RuntimeException(sprintf('A aba "%s" não foi encontrada no modelo da planilha.', $sheetName));
        }

        $this->normalizarEstilosTextoPadrao($xpath);
        $this->normalizarEstilosCondicionais($xpath);
        $this->normalizarEstilosDaGradeMensal($xpath, $tabelaMes);
        $this->atualizarCabecalhoSemana($dom, $tabelaMes);

        $conteudosPorDia = $this->montarConteudosDoMes($escala);
        foreach ($conteudosPorDia as $dia => $linhas) {
            [$linha, $coluna] = $this->resolverPosicaoDia($ano, $mes, (int) $dia);
            $celula = $this->obterCelula($tabelaMes, $linha, $coluna);

            if ($celula instanceof DOMElement && $celula->localName === 'table-cell') {
                $this->preencherCelulaPlantao($dom, $celula, $linhas);
            }
        }

        $this->manterSomenteTabelaDoMes($xpath, $sheetName);
        $this->ajustarReferenciasGlobais($xpath, $sheetName);

        return $dom->saveXML();
    }

    private function atualizarSettingsPlanilha(string $xml, string $abaAtiva): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($xml);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('config', self::NS_CONFIG);

        $itemAbaAtiva = $xpath->query('//config:config-item[@config:name="ActiveTable"]')->item(0);
        if ($itemAbaAtiva) {
            $itemAbaAtiva->nodeValue = $abaAtiva;
        }

        foreach ($xpath->query('//config:config-item-map-named[@config:name="Tables"]/config:config-item-map-entry') as $itemTabela) {
            if (!$itemTabela instanceof DOMElement) {
                continue;
            }

            if ($itemTabela->getAttributeNS(self::NS_CONFIG, 'name') !== $abaAtiva) {
                $itemTabela->parentNode?->removeChild($itemTabela);
            }
        }

        foreach ($xpath->query('//config:config-item-map-named[@config:name="ScriptConfiguration"]/config:config-item-map-entry') as $itemScript) {
            if (!$itemScript instanceof DOMElement) {
                continue;
            }

            if ($itemScript->getAttributeNS(self::NS_CONFIG, 'name') !== $abaAtiva) {
                $itemScript->parentNode?->removeChild($itemScript);
            }
        }

        foreach ($xpath->query('//config:config-item[@config:name="HasSheetTabs"]') as $itemSheetTabs) {
            $itemSheetTabs->nodeValue = 'false';
        }

        return $dom->saveXML();
    }

    private function atualizarStylesPlanilha(string $xml): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($xml);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('style', self::NS_STYLE);

        foreach ($xpath->query('//style:style[starts-with(@style:name,"ConditionalStyle")]') as $styleNode) {
            if (!$styleNode instanceof DOMElement) {
                continue;
            }

            $tableCellProps = $xpath->query('./style:table-cell-properties', $styleNode)->item(0);
            if (!$tableCellProps instanceof DOMElement) {
                $tableCellProps = $dom->createElementNS(self::NS_STYLE, 'style:table-cell-properties');
                $styleNode->appendChild($tableCellProps);
            }

            $tableCellProps->setAttributeNS(self::NS_STYLE, 'style:text-align-source', 'fix');
            $tableCellProps->setAttributeNS(self::NS_FO, 'fo:wrap-option', 'wrap');
            $tableCellProps->setAttributeNS(self::NS_STYLE, 'style:shrink-to-fit', 'false');
            $tableCellProps->setAttributeNS(self::NS_STYLE, 'style:vertical-align', 'middle');

            $paragraphProps = $xpath->query('./style:paragraph-properties', $styleNode)->item(0);
            if (!$paragraphProps instanceof DOMElement) {
                $paragraphProps = $dom->createElementNS(self::NS_STYLE, 'style:paragraph-properties');
                $styleNode->appendChild($paragraphProps);
            }

            $paragraphProps->setAttributeNS(self::NS_FO, 'fo:text-align', 'start');
            $paragraphProps->setAttributeNS(self::NS_FO, 'fo:margin-left', '0.323cm');
            $paragraphProps->setAttributeNS(self::NS_STYLE, 'style:writing-mode', 'page');

            $textProps = $xpath->query('./style:text-properties', $styleNode)->item(0);
            if (!$textProps instanceof DOMElement) {
                $textProps = $dom->createElementNS(self::NS_STYLE, 'style:text-properties');
                $styleNode->appendChild($textProps);
            }

            $textProps->setAttributeNS(self::NS_STYLE, 'style:font-name', 'Arial');
            $textProps->setAttributeNS(self::NS_STYLE, 'style:font-name-asian', 'Arial');
            $textProps->setAttributeNS(self::NS_STYLE, 'style:font-name-complex', 'Arial');

            $textProps->setAttributeNS(self::NS_FO, 'fo:font-size', '8pt');
            $textProps->setAttributeNS(self::NS_FO, 'fo:font-weight', 'normal');
            $textProps->setAttributeNS(self::NS_FO, 'fo:font-style', 'normal');
            $textProps->setAttributeNS(self::NS_STYLE, 'style:font-size-asian', '8pt');
            $textProps->setAttributeNS(self::NS_STYLE, 'style:font-size-complex', '8pt');
            $textProps->setAttributeNS(self::NS_STYLE, 'style:font-weight-asian', 'normal');
            $textProps->setAttributeNS(self::NS_STYLE, 'style:font-weight-complex', 'normal');
            $textProps->setAttributeNS(self::NS_STYLE, 'style:font-style-asian', 'normal');
            $textProps->setAttributeNS(self::NS_STYLE, 'style:font-style-complex', 'normal');
        }

        return $dom->saveXML();
    }

    private function atualizarAnoCivil(DOMDocument $dom, DOMXPath $xpath, int $ano): void
    {
        foreach (self::MAPA_MESES_TABELA as $nomeMes) {
            $tabelaMes = $this->buscarTabelaPorNome($xpath, $nomeMes);
            if (!$tabelaMes) {
                continue;
            }

            $celulaAnoCivil = $this->obterCelula($tabelaMes, 2, 12);
            if ($celulaAnoCivil instanceof DOMElement) {
                $this->definirValorNumerico($dom, $celulaAnoCivil, $ano);
            }

            $celulaAnoVisivel = $this->obterCelula($tabelaMes, 1, 2);
            if (!$celulaAnoVisivel instanceof DOMElement) {
                continue;
            }

            $celulaAnoVisivel->setAttributeNS(self::NS_OFFICE, 'office:value-type', 'float');
            $celulaAnoVisivel->setAttributeNS(self::NS_OFFICE, 'office:value', (string) $ano);
            $celulaAnoVisivel->setAttributeNS(self::NS_CALCEXT, 'calcext:value-type', 'float');

            $spansAno = $xpath->query('.//draw:custom-shape//text:span', $celulaAnoVisivel);
            if ($spansAno->length > 0) {
                $spansAno->item(0)->nodeValue = (string) $ano;

                for ($indice = 1; $indice < $spansAno->length; $indice++) {
                    $spansAno->item($indice)->nodeValue = '';
                }
            }
        }
    }

    private function limparConteudosDosMeses(DOMDocument $dom, DOMXPath $xpath): void
    {
        $linhasConteudo = [4, 6, 8, 10, 12, 14];

        foreach (self::MAPA_MESES_TABELA as $nomeMes) {
            $tabelaMes = $this->buscarTabelaPorNome($xpath, $nomeMes);
            if (!$tabelaMes) {
                continue;
            }

            foreach ($linhasConteudo as $linha) {
                for ($coluna = 3; $coluna <= 9; $coluna++) {
                    $celula = $this->obterCelula($tabelaMes, $linha, $coluna);
                    if (!$celula instanceof DOMElement || $celula->localName !== 'table-cell') {
                        continue;
                    }

                    $this->limparConteudoCelula($dom, $celula);
                }
            }
        }
    }

    private function montarConteudosDoMes(array $escala): array
    {
        $conteudos = [];

        foreach ($escala['dias'] ?? [] as $dadosDia) {
            $dia = (int) ($dadosDia['dia'] ?? 0);
            if ($dia <= 0) {
                continue;
            }

            $linhas = [];
            foreach (['DIA', 'NOITE'] as $turno) {
                $dadosTurno = $dadosDia['turnos'][$turno] ?? [];
                $idEquipe = (int) ($dadosTurno['id_equipe_plantao'] ?? 0);
                $equipeAtiva = isset($dadosTurno['equipe_ativa']) ? (int) $dadosTurno['equipe_ativa'] : 1;

                if ($idEquipe <= 0 || $equipeAtiva !== 1) {
                    continue;
                }

                $membros = $this->formatarListaMembros($dadosTurno['membros_plantao'] ?? []);
                if ($membros === '') {
                    $nomeEquipe = trim((string) ($dadosTurno['equipe_nome'] ?? ''));
                    if ($nomeEquipe === '') {
                        continue;
                    }

                    $membros = $nomeEquipe;
                }

                $linhas[] = [
                    'rotulo' => $turno === 'DIA' ? 'DIA' : 'NOITE',
                    'texto' => $membros
                ];
            }

            if (!empty($linhas)) {
                $conteudos[$dia] = $linhas;
            }
        }

        return $conteudos;
    }

    private function formatarListaMembros(array $membros): string
    {
        $nomes = [];

        foreach ($membros as $membro) {
            $nomeCompleto = trim((string) ($membro['nome_completo'] ?? ''));
            if ($nomeCompleto === '') {
                $nomeCompleto = trim(
                    sprintf(
                        '%s %s',
                        (string) ($membro['nome'] ?? ''),
                        (string) ($membro['sobrenome'] ?? '')
                    )
                );
            }

            $nomeCurto = $this->formatarNomeCurto($nomeCompleto);
            if ($nomeCurto !== '') {
                $nomes[] = $nomeCurto;
            }
        }

        $nomes = array_values(array_unique($nomes));
        return implode(' / ', $nomes);
    }

    private function formatarNomeCurto(string $nomeCompleto): string
    {
        $nomeCompleto = trim(preg_replace('/\s+/', ' ', $nomeCompleto));
        if ($nomeCompleto === '') {
            return '';
        }

        $partes = preg_split('/\s+/', $nomeCompleto) ?: [];
        $primeiroNome = trim((string) ($partes[0] ?? ''));

        return $primeiroNome === '' ? '' : $this->upper($primeiroNome);
    }

    private function resolverPosicaoDia(int $ano, int $mes, int $dia): array
    {
        $weekday = (int) date('w', strtotime(sprintf('%04d-%02d-%02d', $ano, $mes, $dia)));
        $primeiroWeekday = (int) date('w', strtotime(sprintf('%04d-%02d-01', $ano, $mes)));
        $deslocamento = $primeiroWeekday + ($dia - 1);
        $semana = intdiv($deslocamento, 7);

        $linhaConteudo = 4 + ($semana * 2);
        $coluna = 3 + $weekday;

        return [$linhaConteudo, $coluna];
    }

    private function preencherCelulaPlantao(DOMDocument $dom, DOMElement $celula, array $linhas): void
    {
        $this->limparConteudoCelula($dom, $celula);

        if (empty($linhas)) {
            return;
        }

        $celula->setAttributeNS(self::NS_OFFICE, 'office:value-type', 'string');
        $celula->setAttributeNS(self::NS_CALCEXT, 'calcext:value-type', 'string');

        $styleLabelDia = 'T3';
        $styleTexto = 'T4';

        $paragraph = $dom->createElementNS(self::NS_TEXT, 'text:p');
        $textos = [];

        foreach ($linhas as $indice => $linha) {
            $rotulo = trim((string) ($linha['rotulo'] ?? ''));
            $texto = trim((string) ($linha['texto'] ?? ''));
            $textoCompleto = trim($rotulo . ': ' . $texto);

            if ($textoCompleto === '') {
                continue;
            }

            if ($indice > 0) {
                $paragraph->appendChild($dom->createTextNode(' '));
            }

            $styleLabel = $styleLabelDia;

            $spanRotulo = $dom->createElementNS(self::NS_TEXT, 'text:span');
            $spanRotulo->setAttributeNS(self::NS_TEXT, 'text:style-name', $styleLabel);
            $spanRotulo->appendChild($dom->createTextNode($rotulo));
            $paragraph->appendChild($spanRotulo);

            $spanTexto = $dom->createElementNS(self::NS_TEXT, 'text:span');
            $spanTexto->setAttributeNS(self::NS_TEXT, 'text:style-name', $styleTexto);
            $spanTexto->appendChild($dom->createTextNode(': ' . $texto));
            $paragraph->appendChild($spanTexto);

            $textos[] = $textoCompleto;
        }

        if (!empty($textos)) {
            $celula->appendChild($paragraph);
            $celula->setAttributeNS(self::NS_OFFICE, 'office:string-value', implode(' | ', $textos));
        }
    }

    private function atualizarCabecalhoSemana(DOMDocument $dom, DOMElement $tabelaMes): void
    {
        $cabecalho = [
            3 => 'DOM',
            4 => 'SEG',
            5 => 'TER',
            6 => 'QUA',
            7 => 'QUI',
            8 => 'SEX',
            9 => 'SÁB'
        ];

        foreach ($cabecalho as $coluna => $textoDia) {
            $celula = $this->obterCelula($tabelaMes, 2, $coluna);
            if (!$celula instanceof DOMElement || $celula->localName !== 'table-cell') {
                continue;
            }

            $this->removerAtributosValor($celula);
            $this->removerFilhos($celula);
            $celula->removeAttributeNS(self::NS_TABLE, 'formula');
            $celula->removeAttributeNS(self::NS_TABLE, 'content-validation-name');
            $celula->removeAttribute('table:formula');
            $celula->removeAttribute('formula');
            $celula->removeAttribute('table:content-validation-name');
            $celula->removeAttribute('content-validation-name');

            $celula->setAttributeNS(self::NS_OFFICE, 'office:value-type', 'string');
            $celula->setAttributeNS(self::NS_OFFICE, 'office:string-value', $textoDia);
            $celula->setAttributeNS(self::NS_CALCEXT, 'calcext:value-type', 'string');

            $paragraph = $dom->createElementNS(self::NS_TEXT, 'text:p');
            $paragraph->appendChild($dom->createTextNode($textoDia));
            $celula->appendChild($paragraph);
        }
    }

    private function definirValorNumerico(DOMDocument $dom, DOMElement $celula, int $valor): void
    {
        $this->removerAtributosValor($celula);
        $this->removerFilhos($celula);

        $celula->setAttributeNS(self::NS_OFFICE, 'office:value-type', 'float');
        $celula->setAttributeNS(self::NS_OFFICE, 'office:value', (string) $valor);
        $celula->setAttributeNS(self::NS_CALCEXT, 'calcext:value-type', 'float');

        $paragraph = $dom->createElementNS(self::NS_TEXT, 'text:p');
        $paragraph->appendChild($dom->createTextNode((string) $valor));
        $celula->appendChild($paragraph);
    }

    private function limparConteudoCelula(DOMDocument $dom, DOMElement $celula): void
    {
        $this->removerAtributosValor($celula);
        $this->removerFilhos($celula);
    }

    private function removerAtributosValor(DOMElement $celula): void
    {
        $celula->removeAttributeNS(self::NS_OFFICE, 'value-type');
        $celula->removeAttributeNS(self::NS_OFFICE, 'value');
        $celula->removeAttributeNS(self::NS_OFFICE, 'date-value');
        $celula->removeAttributeNS(self::NS_OFFICE, 'string-value');
        $celula->removeAttributeNS(self::NS_CALCEXT, 'value-type');
    }

    private function removerFilhos(DOMElement $elemento): void
    {
        while ($elemento->firstChild) {
            $elemento->removeChild($elemento->firstChild);
        }
    }

    private function buscarTabelaPorNome(DOMXPath $xpath, string $nomeTabela): ?DOMElement
    {
        $query = sprintf('//table:table[@table:name="%s"]', $nomeTabela);
        $resultado = $xpath->query($query);

        if (!$resultado || $resultado->length === 0) {
            return null;
        }

        $item = $resultado->item(0);
        return $item instanceof DOMElement ? $item : null;
    }

    private function obterCelula(DOMElement $tabela, int $linhaAlvo, int $colunaAlvo): ?DOMElement
    {
        $linhas = [];
        foreach ($tabela->childNodes as $filho) {
            if (!$filho instanceof DOMElement) {
                continue;
            }

            if ($filho->namespaceURI === self::NS_TABLE && $filho->localName === 'table-row') {
                $linhas[] = $filho;
            }
        }

        if (!isset($linhas[$linhaAlvo - 1])) {
            return null;
        }

        return $this->obterCelulaDaLinha($linhas[$linhaAlvo - 1], $colunaAlvo);
    }

    private function obterCelulaDaLinha(DOMElement $linha, int $colunaAlvo): ?DOMElement
    {
        $colunaAtual = 1;

        foreach ($linha->childNodes as $filho) {
            if (!$filho instanceof DOMElement) {
                continue;
            }

            if ($filho->namespaceURI !== self::NS_TABLE) {
                continue;
            }

            if (!in_array($filho->localName, ['table-cell', 'covered-table-cell'], true)) {
                continue;
            }

            $repeticoes = (int) $filho->getAttributeNS(self::NS_TABLE, 'number-columns-repeated');
            if ($repeticoes < 1) {
                $repeticoes = 1;
            }

            if ($colunaAlvo >= $colunaAtual && $colunaAlvo < ($colunaAtual + $repeticoes)) {
                return $filho;
            }

            $colunaAtual += $repeticoes;
        }

        return null;
    }

    private function manterSomenteTabelaDoMes(DOMXPath $xpath, string $nomeTabelaMantida): void
    {
        foreach ($xpath->query('//table:table') as $tabela) {
            if (!$tabela instanceof DOMElement) {
                continue;
            }

            $nomeTabela = $tabela->getAttributeNS(self::NS_TABLE, 'name');
            if (!$this->ehTabelaDeMes($nomeTabela)) {
                continue;
            }

            if ($nomeTabela !== $nomeTabelaMantida) {
                $tabela->parentNode?->removeChild($tabela);
            }
        }
    }

    private function ajustarReferenciasGlobais(DOMXPath $xpath, string $nomeTabelaMantida): void
    {
        foreach ($xpath->query('//style:map[@style:base-cell-address]') as $mapaEstilo) {
            if (!$mapaEstilo instanceof DOMElement) {
                continue;
            }

            $valorAtual = $mapaEstilo->getAttributeNS(self::NS_STYLE, 'base-cell-address');
            $mapaEstilo->setAttributeNS(
                self::NS_STYLE,
                'style:base-cell-address',
                $this->substituirAbaNaReferencia($valorAtual, $nomeTabelaMantida)
            );
        }

        foreach ($xpath->query('/office:document-content/office:body/office:spreadsheet/table:named-expressions/table:*') as $expressao) {
            if (!$expressao instanceof DOMElement) {
                continue;
            }

            $nome = $expressao->getAttributeNS(self::NS_TABLE, 'name');
            if (!in_array($nome, self::NOMES_GLOBAIS_MANTIDOS, true)) {
                $expressao->parentNode?->removeChild($expressao);
                continue;
            }

            if ($expressao->hasAttributeNS(self::NS_TABLE, 'base-cell-address')) {
                $expressao->setAttributeNS(
                    self::NS_TABLE,
                    'table:base-cell-address',
                    $this->substituirAbaNaReferencia($expressao->getAttributeNS(self::NS_TABLE, 'base-cell-address'), $nomeTabelaMantida)
                );
            }

            if ($expressao->hasAttributeNS(self::NS_TABLE, 'cell-range-address')) {
                $novaReferencia = match ($nome) {
                    'AnoCivil' => sprintf('$%s.$L$2', $nomeTabelaMantida),
                    'InícioDaSemana' => sprintf('$%s.$L$3', $nomeTabelaMantida),
                    'LinhaTítuloRegião1..L3.1' => sprintf('$%s.$K$2', $nomeTabelaMantida),
                    default => $this->substituirAbaNaReferencia($expressao->getAttributeNS(self::NS_TABLE, 'cell-range-address'), $nomeTabelaMantida)
                };

                $expressao->setAttributeNS(self::NS_TABLE, 'table:cell-range-address', $novaReferencia);
            }
        }
    }

    private function substituirAbaNaReferencia(string $referencia, string $nomeTabela): string
    {
        return (string) preg_replace_callback(
            '/^(\$?)[^.]+\./u',
            static function (array $matches) use ($nomeTabela): string {
                return ($matches[1] ?? '') . $nomeTabela . '.';
            },
            $referencia,
            1
        );
    }

    private function ehTabelaDeMes(string $nomeTabela): bool
    {
        return in_array($nomeTabela, array_values(self::MAPA_MESES_TABELA), true);
    }

    private function normalizarEstilosTextoPadrao(DOMXPath $xpath): void
    {
        $this->normalizarEstiloTextual($xpath, 'T3', '8pt', 'bold');
        $this->normalizarEstiloTextual($xpath, 'T4', '8pt', 'normal');
        $this->normalizarEstiloTextual($xpath, 'T5', '8pt', 'bold');
        $this->normalizarEstiloTextual($xpath, 'T6', '8pt', 'bold');
        $this->normalizarEstiloTextual($xpath, 'T7', '8pt', 'normal');
        $this->normalizarEstiloTextual($xpath, 'T8', '8pt', 'bold');
    }

    private function normalizarEstilosDaGradeMensal(DOMXPath $xpath, DOMElement $tabelaMes): void
    {
        $estilos = [];

        for ($semana = 0; $semana < 6; $semana++) {
            $linhaNumero = 3 + ($semana * 2);
            $linhaConteudo = $linhaNumero + 1;

            for ($coluna = 3; $coluna <= 9; $coluna++) {
                foreach ([$linhaNumero, $linhaConteudo] as $linha) {
                    $celula = $this->obterCelula($tabelaMes, $linha, $coluna);
                    if (!$celula instanceof DOMElement || $celula->localName !== 'table-cell') {
                        continue;
                    }

                    $nomeEstilo = $celula->getAttributeNS(self::NS_TABLE, 'style-name');
                    if ($nomeEstilo !== '') {
                        $estilos[$nomeEstilo] = true;
                    }
                }
            }
        }

        foreach (array_keys($estilos) as $nomeEstilo) {
            $this->normalizarEstiloTextual($xpath, $nomeEstilo, '8pt', 'normal');
        }
    }

    private function normalizarEstilosCondicionais(DOMXPath $xpath): void
    {
        foreach ($xpath->query('//style:style[starts-with(@style:name,"ConditionalStyle")]') as $styleNode) {
            if (!$styleNode instanceof DOMElement) {
                continue;
            }

            $nomeEstilo = $styleNode->getAttributeNS(self::NS_STYLE, 'name');
            if ($nomeEstilo === '') {
                continue;
            }

            $this->normalizarEstiloTextual($xpath, $nomeEstilo, '8pt', 'normal');
        }
    }

    private function normalizarEstiloTextual(DOMXPath $xpath, string $nomeEstilo, string $fontSize, string $fontWeight): void
    {
        $styleNode = $xpath->query(sprintf('//style:style[@style:name="%s"]', $nomeEstilo))->item(0);
        if (!$styleNode instanceof DOMElement) {
            return;
        }

        $textProps = $xpath->query('./style:text-properties', $styleNode)->item(0);
        if (!$textProps instanceof DOMElement) {
            $textProps = $styleNode->ownerDocument->createElementNS(self::NS_STYLE, 'style:text-properties');
            $styleNode->appendChild($textProps);
        }

        $textProps->setAttributeNS(self::NS_STYLE, 'style:font-name', 'Arial');
        $textProps->setAttributeNS(self::NS_STYLE, 'style:font-name-asian', 'Arial');
        $textProps->setAttributeNS(self::NS_STYLE, 'style:font-name-complex', 'Arial');

        $textProps->setAttributeNS(self::NS_FO, 'fo:font-size', $fontSize);
        $textProps->setAttributeNS(self::NS_FO, 'fo:font-weight', $fontWeight);
        $textProps->setAttributeNS(self::NS_FO, 'fo:font-style', 'normal');
        $textProps->setAttributeNS(self::NS_STYLE, 'style:font-size-asian', $fontSize);
        $textProps->setAttributeNS(self::NS_STYLE, 'style:font-size-complex', $fontSize);
        $textProps->setAttributeNS(self::NS_STYLE, 'style:font-weight-asian', $fontWeight);
        $textProps->setAttributeNS(self::NS_STYLE, 'style:font-weight-complex', $fontWeight);
        $textProps->setAttributeNS(self::NS_STYLE, 'style:font-style-asian', 'normal');
        $textProps->setAttributeNS(self::NS_STYLE, 'style:font-style-complex', 'normal');
    }

    private function upper(string $texto): string
    {
        if (function_exists('mb_strtoupper')) {
            return mb_strtoupper($texto, 'UTF-8');
        }

        return strtoupper($texto);
    }
}
