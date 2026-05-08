<?php
require_once 'Pessoa.php';


class Interno extends Pessoa
{

    private $idInterno;

    private $idSituacaoInterno;

    private $nomeContatoUrgente;

    private $telefoneContatoUrgente1;

    private $telefoneContatoUrgente2;

    private $telefoneContatoUrgente3;

    private $certidaoNascimento;

    private $curatela;

    private $inss;

    private $loas;

    private $bpc;

    private $funrural;

    private $saf;

    private $sus;

    private $certidaoCasamento;

    private $ctps;

    private $titulo;

    private $observacao;

    public function getObservacao()
    {
        return $this->observacao;
    }

    public function setObservacao($observacao)
    {
        $this->observacao=$observacao;
    }

    public function getIdInterno()
    {
        return $this->idInterno;
    }

    public function getIdSituacaoInterno()
    {
        return $this->idSituacaoInterno;
    }

    public function getNomeContatoUrgente()
    {
        return $this->nomeContatoUrgente;
    }

    public function getTelefoneContatoUrgente1()
    {
        return $this->telefoneContatoUrgente1;
    }

    public function getTelefoneContatoUrgente2()
    {
        return $this->telefoneContatoUrgente2;
    }

    public function getTelefoneContatoUrgente3()
    {
        return $this->telefoneContatoUrgente3;
    }

    public function getCertidaoNascimento()
    {
        return $this->certidaoNascimento;
    }

    public function getCuratela()
    {
        return $this->curatela;
    }

    public function getInss()
    {
        return $this->inss;
    }

    public function getLoas()
    {
        return $this->loas;
    }

    public function getBpc()
    {
        return $this->bpc;
    }

    public function getFunrural()
    {
        return $this->funrural;
    }

    public function getSaf()
    {
        return $this->saf;
    }

    public function getSus()
    {
        return $this->sus;
    }
    public function getCertidaoCasamento()
    {
        return $this->certidaoCasamento;
    }
    public function getCtps()
    {
        return $this->ctps;
    }
    public function getTitulo()
    {
        return $this->titulo;
    }

    public function setIdInterno($idInterno)
    {
        $this->idInterno = $idInterno;
    }

    public function setIdSituacaoInterno($idSituacaoInterno)
    {
        $this->idSituacaoInterno = $idSituacaoInterno;
    }

    public function setNomeContatoUrgente($nomeContatoUrgente)
    {
        $this->nomeContatoUrgente = $nomeContatoUrgente;
    }

    public function setTelefoneContatoUrgente1($telefoneContatoUrgente1)
    {
        $this->telefoneContatoUrgente1 = $telefoneContatoUrgente1;
    }

    public function setTelefoneContatoUrgente2($telefoneContatoUrgente2)
    {
        $this->telefoneContatoUrgente2 = $telefoneContatoUrgente2;
    }

    public function setTelefoneContatoUrgente3($telefoneContatoUrgente3)
    {
        $this->telefoneContatoUrgente3 = $telefoneContatoUrgente3;
    }

    public function setCertidaoNascimento($certidao_nascimento)
    {
        $this->certidaoNascimento = $certidao_nascimento;
    }

    public function setCuratela($curatela)
    {
        $this->curatela = $curatela;
    }

    public function setInss($inss)
    {
        $this->inss = $inss;
    }

    public function setLoas($loas)
    {
        $this->loas = $loas;
    }

    public function setBpc($bpc)
    {
        $this->bpc = $bpc;
    }

    public function setFunrural($funrural)
    {
        $this->funrural = $funrural;
    }

    public function setSaf($saf)
    {
        $this->saf = $saf;
    }

    public function setSus($sus)
    {
        $this->sus = $sus;
    }
    public function setCertidaoCasamento($certidaoCasamento)
    {
        $this->certidaoCasamento = $certidaoCasamento;
    }
    public function setCtps($ctps)
    {
        $this->ctps = $ctps;
    }
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;
    }
    
}

