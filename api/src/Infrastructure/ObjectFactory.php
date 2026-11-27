<?php

namespace api\Infrastructure;

use api\modules\Pessoa\Pessoa;
use api\modules\Socio\Socio;
use api\contracts\PessoaInterface;
use api\contracts\SocioInterface;
use DateTime;

/**
 * Factory para criar instâncias de objetos com injeção de dependências
 * 
 * Este padrão Factory encapsula a complexidade de criar objetos
 * e garante que as dependências corretas sejam injetadas.
 */
class ObjectFactory
{
    /**
     * Cria uma instância de Socio
     * 
     * @param int $socioId
     * @param string $nome
     * @param string $sobrenome
     * @param string $cpf
     * @param DateTime|null $dataNascimento
     * @param string|null $sexo
     * @param string|null $telefone
     * @return SocioInterface
     */
    public static function criarSocio(
        int $socioId,
        string $nome,
        string $sobrenome,
        string $cpf,
        ?DateTime $dataNascimento = null,
        ?string $sexo = null,
        ?string $telefone = null
    ): SocioInterface {
        // Criar a Pessoa (dependência)
        $pessoa = self::criarPessoa(
            $nome,
            $sobrenome,
            $cpf,
            $dataNascimento,
            $sexo,
            $telefone
        );

        // Injetar a Pessoa no Socio
        return new Socio($socioId, $pessoa);
    }

    /**
     * Cria uma instância de Pessoa
     * 
     * @param string $nome
     * @param string $sobrenome
     * @param string $cpf
     * @param DateTime|null $dataNascimento
     * @param string|null $sexo
     * @param string|null $telefone
     * @return PessoaInterface
     */
    public static function criarPessoa(
        string $nome,
        string $sobrenome,
        string $cpf,
        ?DateTime $dataNascimento = null,
        ?string $sexo = null,
        ?string $telefone = null
    ): PessoaInterface {
        return new Pessoa(
            $nome,
            $sobrenome,
            $cpf,
            $dataNascimento,
            $sexo,
            $telefone
        );
    }

    /**
     * Cria um Socio a partir de uma Pessoa já existente
     * Útil quando você já tem a instância de Pessoa
     * 
     * @param int $socioId
     * @param PessoaInterface $pessoa
     * @return SocioInterface
     */
    public static function criarSocioComPessoa(
        int $socioId,
        PessoaInterface $pessoa
    ): SocioInterface {
        return new Socio($socioId, $pessoa);
    }
}
