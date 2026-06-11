<?php

namespace api\contracts\services;

use api\contracts\entities\PessoaInterface;
use DateTime;

interface PessoaServiceInterface
{
    public function criarPessoa(string $nome, string $sobrenome, ?DateTime $dataNascimento, ?string $sexo, ?string $telefone, ?string $email, string $cpf): PessoaInterface;
    public function obterPessoaPorId(int $id): ?PessoaInterface;
    public function obterPessoaPorCpf(string $cpf): ?PessoaInterface;
    public function atualizarPessoa(int $id, string $nome, string $sobrenome, ?DateTime $dataNascimento, ?string $sexo, ?string $telefone, ?string $email, string $cpf, ?array $endereco = null): PessoaInterface;
    public function deletarPessoa(int $id): bool;
}
