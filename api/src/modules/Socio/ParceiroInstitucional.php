<?php
namespace api\modules\Socio;

use api\contracts\entities\PessoaInterface;

class ParceiroInstitucional
{
    private int $id;
    private PessoaInterface $pessoa;
    private string $localizacao;
    private string $divulgacao;
    private ?string $descricao;
    private int $idSetor;

    public function __construct(PessoaInterface $pessoa, string $localizacao, string $divulgacao, int $idSetor, ?string $descricao = null, ?int $id = null)
    {
        if ($id !== null)
            $this->id = $id;
        
        $this->pessoa = $pessoa;
        $this->localizacao = $localizacao;
        $this->divulgacao = $divulgacao;
        $this->idSetor = $idSetor;
        $this->descricao = $descricao;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPessoa(): PessoaInterface
    {
        return $this->pessoa;
    }

    public function getLocalizacao(): string
    {
        return $this->localizacao;
    }

    public function getDivulgacao(): string
    {
        return $this->divulgacao;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function getIdSetor(): int
    {
        return $this->idSetor;
    }
}
