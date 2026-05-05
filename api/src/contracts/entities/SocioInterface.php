<?php

namespace api\contracts\entities;

use DateTime;

/**
 * Interface SocioInterface
 * 
 * Define o contrato para qualquer implementação de Socio na aplicação.
 */
interface SocioInterface
{
    public function __construct(PessoaInterface $pessoa, string $email, DateTime $inicioContribuicao, float $valorMensalidade, int $idSocioStatus = 1, bool $autoStatusContribuicao = true, int $idSocioTipo = 0, ?int $id = null);

    /**
     * Obtém o ID do sócio
     */
    public function getId(): int;

    /**
     * Obtém a Pessoa associada ao sócio
     */
    public function getPessoa(): PessoaInterface;

    /**
     * Obtém o email do sócio
     */
    public function getEmail(): string;

    /**
     * Obtém o status do sócio
     */
    public function getStatus(): int;

    /**
     * Obtém o status de atualização automática com base nas contribuições do sócio
     */
    public function getAutoStatusContribuicao(): bool;

    /**
     * Obtém o valor da mensalidade do sócio
     */
    public function getValorMensalidade(): float;

    /**
     * Obtém a data de início da contribuição do sócio
     */
    public function getInicioContribuicao(): DateTime;

    /**
     * Obtém o ID do tipo de sócio
     */
    public function getIdSocioTipo(): int;

    /**
     * Define o ID do tipo de sócio
     */
    public function setIdSocioTipo(int $idSocioTipo): void;

    /**
     * Define o ID do sócio
     */
    public function setId(int $id): void;

    /**
     * Define o email do sócio
     */
    public function setEmail(string $email): void;

    /**
     * Define o status do sócio
     */
    public function setStatus(int $status): void;

    /**
     * Define o status automaticamente com base nas contribuições do sócio
     */
    public function setAutoStatusContribuicao(bool $autoStatusContribuicao): void;

    /**
     * Define o valor da mensalidade do sócio
     */
    public function setValorMensalidade(float $valorMensalidade): void;

    /**
     * Define a data de início da contribuição do sócio
     */
    public function setInicioContribuicao(DateTime $inicioContribuicao): void;

}
