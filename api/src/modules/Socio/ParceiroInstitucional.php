<?php
namespace api\modules\Socio;

use api\contracts\entities\PessoaInterface;

class ParceiroInstitucional
{
    private int $id;
    private PessoaInterface $pessoa;
    private string $localizacao;
    private string $divulgacao;

    public function __construct(PessoaInterface $pessoa, string $localizacao, string $divulgacao, ?int $id = null)
    {
        if ($id !== null)
            $this->id = $id;
        
        $this->pessoa = $pessoa;
        $this->localizacao = $localizacao;
        $this->divulgacao = $divulgacao;
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
}