<?php

namespace api\modules\Socio;

use api\contracts\entities\PessoaInterface;
use api\contracts\entities\SocioInterface;
use api\contracts\services\SocioServiceInterface;
use DateTime;

class SocioService implements SocioServiceInterface
{
    private SocioRepository $socioRepository;

    public function __construct(SocioRepository $socioRepository)
    {
        $this->socioRepository = $socioRepository;
    }

    public function criarSocio(PessoaInterface $pessoa, DateTime $inicioContribuicao, float $valorMensalidade,int $idSocioStatus = 1, bool $autoStatusContribuicao = true, int $idSocioTipo = 0): SocioInterface
    {
        $socio = new Socio($pessoa, $inicioContribuicao, $valorMensalidade, $idSocioStatus, $autoStatusContribuicao, $idSocioTipo);
        return $this->socioRepository->save($socio);
    }

    public function obterSocioPorId(int $id): ?SocioInterface
    {
        // Lógica para obter um sócio por ID
        // Exemplo: consultar o banco de dados e retornar o objeto Sócio correspondente ou null se não encontrado
        throw new \Exception("Método obterSocioPorId ainda não implementado", 501);
    }

    public function atualizarSocio(int $id, PessoaInterface $pessoa, DateTime $inicioContribuicao, float $valorMensalidade,int $idSocioStatus = 1, bool $autoStatusContribuicao = true, int $idSocioTipo = 0): SocioInterface
    {
        // Lógica para atualizar um sócio existente
        // Exemplo: validar dados, atualizar o objeto Sócio e salvar as alterações no banco de dados
        throw new \Exception("Método atualizarSocio ainda não implementado", 501);
    }

    public function deletarSocio(int $id): bool
    {
        // Lógica para deletar um sócio por ID
        // Exemplo: anonimizar o registro do banco de dados e retornar true se a operação foi bem-sucedida ou false caso contrário
        throw new \Exception("Método deletarSocio ainda não implementado", 501);
    }
}