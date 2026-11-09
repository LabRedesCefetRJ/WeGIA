<?php

/** Apresentação comum dos relatórios; o corpo recebe HTML já escapado pela página. */
class RelatorioMatPat
{
    private $resumo;
    private $body;
    private $titulo;
    private $tipo;
    private $logo;
    private $instituicao;
    private $geradoEm;

    public function __construct(array $resumo, $body, $titulo = 'Relatório', $tipo = '')
    {
        $this->resumo = $resumo;
        $this->body = $body;
        $this->titulo = $titulo;
        $this->tipo = $tipo;
        $this->geradoEm = new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE));

        $logo = trim($this->campoPersonalizado('Logo', 'file'));
        $this->logo = '';
        if (preg_match('~^data:image(?:/[a-z0-9.+-]+)?;base64,(.+)$~is', $logo, $partes)) {
            $imagem = base64_decode($partes[1], true);
            $dados = $imagem !== false ? @getimagesizefromstring($imagem) : false;
            if ($dados && strpos($dados['mime'], 'image/') === 0) {
                $this->logo = 'data:' . $dados['mime'] . ';base64,' . base64_encode($imagem);
            }
        }
        $nome = trim(html_entity_decode(strip_tags($this->campoPersonalizado('Titulo', 'str')), ENT_QUOTES, 'UTF-8'));
        $this->instituicao = $nome === NO_DATA ? '' : $nome;
    }

    protected function campoPersonalizado($campo, $tipo)
    {
        require_once ROOT . '/html/personalizacao_display.php';
        ob_start();
        try {
            display_campo($campo, $tipo);
            return ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }

    private static function escapar($valor)
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }

    public static function periodo($inicio, $fim)
    {
        $formatar = static function ($data) { return (new DateTimeImmutable($data))->format('d/m/Y'); };
        if ($inicio && $fim) return $formatar($inicio) . ' a ' . $formatar($fim);
        if ($inicio) return 'A partir de ' . $formatar($inicio);
        if ($fim) return 'Até ' . $formatar($fim);
        return 'Todo o período';
    }

    private function renderizar()
    {
        $html = '<article class="relatorio-documento relatorio-' . self::escapar($this->tipo) . '">';
        $html .= '<header class="relatorio-cabecalho">';
        if ($this->logo !== '') {
            $html .= '<img src="' . self::escapar($this->logo) . '" alt="Logo da instituição" width="76" height="79">';
        }
        $html .= '<div>';
        if ($this->instituicao !== '') $html .= '<strong>' . self::escapar($this->instituicao) . '</strong>';
        $html .= '<h1>' . self::escapar($this->titulo) . '</h1><p>Material e Patrimônio • ' . $this->geradoEm->format('d/m/Y H:i') . '</p></div></header>';
        if ($this->resumo) {
            $html .= '<section class="relatorio-filtros"><h2>Filtros utilizados</h2><dl>';
            foreach ($this->resumo as $nome => $valor) {
                $html .= '<div><dt>' . self::escapar($nome) . '</dt><dd>' . self::escapar($valor ?? 'Não registrado') . '</dd></div>';
            }
            $html .= '</dl></section>';
        }
        return $html . '<div class="relatorio-conteudo">' . $this->body . '</div></article>';
    }

    public function getPrinter()
    {
        return $this->renderizar();
    }

    public function getHtml()
    {
        return '<div class="relatorio-tela"><div class="relatorio-acoes print-hide"><button type="button" class="btn btn-default" onclick="window.print()">Imprimir</button></div>'
            . $this->renderizar() . '</div><template class="relatorio-template">' . $this->getPrinter() . '</template>';
    }
}
