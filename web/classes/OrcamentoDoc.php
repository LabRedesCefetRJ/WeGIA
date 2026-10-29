<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Arquivo.php';

class OrcamentoDoc
{
    private ?int $id_orcamento_arquivo = null;
    private int $id_orcamento;
    private Arquivo $arquivo;

    public function __construct($id_orcamento, Arquivo $arquivo)
    {
        $this->setId_orcamento($id_orcamento);
        $this->setArquivo($arquivo);
    }

    public function getId_orcamento_arquivo(): ?int
    {
        return $this->id_orcamento_arquivo;
    }

    public function getId_orcamento(): int
    {
        return $this->id_orcamento;
    }

    public function getArquivo(): Arquivo
    {
        return $this->arquivo;
    }

    public function setId_orcamento_arquivo($id_orcamento_arquivo): void
    {
        $this->id_orcamento_arquivo = $this->validarId(
            $id_orcamento_arquivo,
            'arquivo do orçamento'
        );
    }

    public function setId_orcamento($id_orcamento): void
    {
        $this->id_orcamento = $this->validarId($id_orcamento, 'orçamento');
    }

    public function setArquivo(Arquivo $arquivo): void
    {
        $this->arquivo = $arquivo;
    }

    private function validarId($id, string $campo): int
    {
        if (filter_var($id, FILTER_VALIDATE_INT) === false || (int) $id < 1) {
            throw new InvalidArgumentException(
                "O id de {$campo} deve ser um inteiro maior que zero.",
                400
            );
        }

        return (int) $id;
    }
}
