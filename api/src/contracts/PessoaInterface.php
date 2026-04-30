<?php

namespace api\contracts;

use DateTime;

/**
 * Interface PessoaInterface
 * 
 * Define o contrato para qualquer implementação de Pessoa na aplicação.
 * Permite que diferentes módulos trabalhem com Pessoa sem acoplamento direto.
 */
interface PessoaInterface
{
    /**
     * Obtém o ID da pessoa
     */
    public function getId(): ?int;

    /**
     * Obtém o nome da pessoa
     */
    public function getNome(): string;

    /**
     * Obtém o sobrenome da pessoa
     */
    public function getSobrenome(): string;

    /**
     * Obtém a data de nascimento da pessoa
     */
    public function getDataNascimento(): ?DateTime;

    /**
     * Obtém o sexo da pessoa
     */
    public function getSexo(): ?string;

    /**
     * Obtém o telefone da pessoa
     */
    public function getTelefone(): ?string;

    /**
     * Obtém o CPF da pessoa
     */
    public function getCpf(): string;
}
