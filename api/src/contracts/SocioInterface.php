<?php

namespace api\contracts;

/**
 * Interface SocioInterface
 * 
 * Define o contrato para qualquer implementação de Socio na aplicação.
 */
interface SocioInterface
{
    /**
     * Obtém o ID do sócio
     */
    public function getId(): int;

    /**
     * Obtém a Pessoa associada ao sócio
     */
    public function getPessoa(): PessoaInterface;
}
