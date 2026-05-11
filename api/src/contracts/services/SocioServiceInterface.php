<?php
namespace api\contracts\services;

use api\contracts\entities\PessoaInterface;
use api\contracts\entities\SocioInterface;
use DateTime;

interface SocioServiceInterface
{
    public function criarSocio(PessoaInterface $pessoa, DateTime $inicioContribuicao, float $valorMensalidade,int $idSocioStatus = 1, bool $autoStatusContribuicao = true, int $idSocioTipo = 0): SocioInterface;
    public function obterSocioPorId(int $id): ?SocioInterface;
    public function atualizarSocio(int $id, PessoaInterface $pessoa, DateTime $inicioContribuicao, float $valorMensalidade,int $idSocioStatus = 1, bool $autoStatusContribuicao = true, int $idSocioTipo = 0): SocioInterface;
    public function deletarSocio(int $id): bool;
} 