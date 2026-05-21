<?php

namespace api\modules\Pessoa;

use api\contracts\entities\PessoaInterface;
use api\contracts\services\PessoaServiceInterface;
use api\utils\Cpf;
use DateTime;

class PessoaService implements PessoaServiceInterface
{
    private PessoaRepository $pessoaRepository;

    public function __construct(PessoaRepository $pessoaRepository)
    {
        $this->pessoaRepository = $pessoaRepository;
    }

    public function criarPessoa(string $nome, string $sobrenome, ?DateTime $dataNascimento, ?string $sexo, ?string $telefone, ?string $email, string $cpf): PessoaInterface
    {
        // Validar CPF
        if (!Cpf::validate($cpf)) {
            throw new \Exception("CPF inválido", 400);
        }

        // Normaliza o CPF antes de criar
        $cpf = Cpf::normalize($cpf);
        
        $pessoa = new Pessoa($nome, $sobrenome, $cpf, $dataNascimento, $sexo, $telefone, $email);
        $idPessoa = $this->pessoaRepository->create($pessoa);

        if (!$idPessoa) {
            throw new \Exception("Erro ao criar pessoa");
        }

        $pessoa->setId($idPessoa);
        return $pessoa;
    }

    public function obterPessoaPorId(int $id): ?PessoaInterface
    {
        // Implementação para obter uma pessoa por ID
        throw new \Exception("Método obterPessoaPorId ainda não implementado", 501);
    }

    public function obterPessoaPorCpf(string $cpf): ?PessoaInterface
    {
        // Validar CPF
        if (!Cpf::validate($cpf)) {
            return null;
        }

        // Normaliza o CPF (remove máscara e formata)
        $cpf = Cpf::normalize($cpf);
        
        $resultado = $this->pessoaRepository->findByCpf($cpf);

        if(!$resultado) {
            return null;
        }

        return new Pessoa(
            $resultado['nome'],
            $resultado['sobrenome'],
            $resultado['cpf'],
            isset($resultado['data_nascimento']) ? new DateTime($resultado['data_nascimento']) : null,
            $resultado['sexo'] ?? null,
            $resultado['telefone'] ?? null,
            $resultado['email'] ?? null,
            null,
            (int)$resultado['id_pessoa']
        );
    }

    public function atualizarPessoa(int $id, string $nome, string $sobrenome, ?DateTime $dataNascimento, ?string $sexo, ?string $telefone, ?string $email, string $cpf): PessoaInterface
    {
        // Implementação para atualizar os dados de uma pessoa existente
        throw new \Exception("Método atualizarPessoa ainda não implementado", 501);
    }

    public function deletarPessoa(int $id): bool
    {
        // Implementação para deletar uma pessoa por ID
        throw new \Exception("Método deletarPessoa ainda não implementado", 501);
    }
}