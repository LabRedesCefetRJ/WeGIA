<?php

namespace api\modules\Pessoa;

use api\contracts\entities\PessoaInterface;
use api\contracts\services\PessoaServiceInterface;
use api\utils\Util;
use DateTime;
use Psr\Http\Message\UploadedFileInterface;

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
        if (!Util::validateCpf($cpf)) {
            throw new \Exception("CPF inválido", 400);
        }

        // Normaliza o CPF antes de criar
        $cpf = Util::normalizeCpf($cpf);

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
        if (!Util::validateCpf($cpf)) {
            return null;
        }

        // Normaliza o CPF (remove máscara e formata)
        $cpf = Util::normalizeCpf($cpf);

        $resultado = $this->pessoaRepository->findByCpf($cpf);

        if (!$resultado) {
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
            $this->criarEnderecoDoResultado($resultado),
            (int)$resultado['id_pessoa']
        );
    }

    public function atualizarPessoa(int $id, string $nome, string $sobrenome, ?DateTime $dataNascimento, ?string $sexo, ?string $telefone, ?string $email, string $cpf, ?array $endereco = null): PessoaInterface
    {
        // Validar CPF
        if (!Util::validateCpf($cpf)) {
            throw new \Exception("CPF inválido", 400);
        }

        // Normaliza o CPF antes de atualizar
        $cpf = Util::normalizeCpf($cpf);

        // Verificar se a pessoa existe
        $pessoaExistente = $this->pessoaRepository->findById((string)$id);
        if (!$pessoaExistente) {
            throw new \Exception("Pessoa não encontrada", 404);
        }

        // Preparar dados para atualização
        $dados = [
            'nome' => $nome,
            'sobrenome' => $sobrenome,
            'cpf' => $cpf,
            'sexo' => $sexo,
            'telefone' => $telefone,
            'email' => $email,
        ];

        // Adicionar data_nascimento no formato correto se fornecida
        if ($dataNascimento !== null) {
            $dados['data_nascimento'] = $dataNascimento->format('Y-m-d');
        }

        if ($endereco !== null) {
            if (array_key_exists('cep', $endereco)) {
                $dados['cep'] = $endereco['cep'];
            }

            if (array_key_exists('estado', $endereco)) {
                $dados['estado'] = $endereco['estado'];
            }

            if (array_key_exists('cidade', $endereco)) {
                $dados['cidade'] = $endereco['cidade'];
            }

            if (array_key_exists('bairro', $endereco)) {
                $dados['bairro'] = $endereco['bairro'];
            }

            if (array_key_exists('logradouro', $endereco)) {
                $dados['logradouro'] = $endereco['logradouro'];
            }

            if (array_key_exists('numero', $endereco)) {
                $dados['numero_endereco'] = $endereco['numero'];
            }

            if (array_key_exists('numero_endereco', $endereco)) {
                $dados['numero_endereco'] = $endereco['numero_endereco'];
            }

            if (array_key_exists('complemento', $endereco)) {
                $dados['complemento'] = $endereco['complemento'];
            }

            if (array_key_exists('ibge', $endereco)) {
                $dados['ibge'] = $endereco['ibge'];
            }
        }

        // Realizar atualização
        $resultado = $this->pessoaRepository->update($id, $dados);

        if (!$resultado) {
            throw new \Exception("Erro ao atualizar pessoa", 500);
        }

        $pessoaAtualizada = $this->pessoaRepository->findById((string)$id);
        if (!$pessoaAtualizada) {
            throw new \Exception("Pessoa não encontrada", 404);
        }

        return $this->criarPessoaDoResultado($pessoaAtualizada);
    }

    public function deletarPessoa(int $id): bool
    {
        // Implementação para deletar uma pessoa por ID
        throw new \Exception("Método deletarPessoa ainda não implementado", 501);
    }

    public function criarPessoaJuridica(string $cnpj, string $razaoSocial, ?string $telefone, ?string $email, ?array $endereco = null): PessoaInterface
    {
        // Validar CNPJ
        if (!Util::validateCnpj($cnpj)) {
            throw new \Exception("CNPJ inválido", 400);
        }

        // Normaliza o CNPJ antes de criar
        $cnpj = Util::normalizeCnpj($cnpj);

        $pessoa = new Pessoa($razaoSocial, '', $cnpj, null, null, $telefone, $email, Endereco::buildFromArray($endereco)); //reorganizar ordem dos parâmetros conforme necessário
        $idPessoa = $this->pessoaRepository->createJuridica($pessoa);

        if (!$idPessoa) {
            throw new \Exception("Erro ao criar pessoa jurídica");
        }

        $pessoa->setId($idPessoa);
        return $pessoa;
    }

    public function atualizarFotoPerfil(int $idPessoa, UploadedFileInterface $foto): bool
    {
        // Verificar se a pessoa existe
        $pessoaExistente = $this->pessoaRepository->findById((string)$idPessoa);
        if (!$pessoaExistente) {
            throw new \Exception("Pessoa não encontrada", 404);
        }

        // Atualizar foto no banco de dados
        return $this->pessoaRepository->updateProfilePhoto($idPessoa, $foto);
    }

    public function getProfilePhoto(int $idPessoa): ?array
    {
        // Verificar se a pessoa existe
        $pessoaExistente = $this->pessoaRepository->findById((string)$idPessoa);
        if (!$pessoaExistente) {
            throw new \Exception("Pessoa não encontrada", 404);
        }

        // Obter a foto do perfil da pessoa
        $fotoBase64 = $this->pessoaRepository->getProfilePhoto($idPessoa);

        if(!$fotoBase64) {
            return null;
        }

        return $this->decodificarFotoPerfil($fotoBase64);
    }

    private function decodificarFotoPerfil(string $fotoBase64): array
    {
        $mime = 'image/png';
        $conteudoBase64 = $fotoBase64;

        if (str_starts_with($fotoBase64, 'data:')) {
            $partes = explode(',', $fotoBase64, 2);
            if (count($partes) !== 2) {
                throw new \RuntimeException('Formato inválido da foto de perfil', 500);
            }

            [$cabecalho, $conteudoBase64] = $partes;
            $cabecalho = substr($cabecalho, 5);

            $dadosCabecalho = explode(';', $cabecalho);
            if (!empty($dadosCabecalho[0])) {
                $mime = $dadosCabecalho[0];
            }
        }

        $conteudo = base64_decode($conteudoBase64, true);
        if ($conteudo === false) {
            throw new \RuntimeException('Não foi possível decodificar a foto de perfil', 500);
        }

        return [
            'mime' => $mime,
            'conteudo' => $conteudo,
        ];
    }

    private function criarPessoaDoResultado(array $resultado): Pessoa
    {
        return new Pessoa(
            $resultado['nome'] ?? '',
            $resultado['sobrenome'] ?? '',
            $resultado['cpf'] ?? '',
            isset($resultado['data_nascimento']) ? new DateTime($resultado['data_nascimento']) : null,
            $resultado['sexo'] ?? null,
            $resultado['telefone'] ?? null,
            $resultado['email'] ?? null,
            $this->criarEnderecoDoResultado($resultado),
            isset($resultado['id_pessoa']) ? (int)$resultado['id_pessoa'] : null
        );
    }

    private function criarEnderecoDoResultado(array $resultado): ?Endereco
    {
        $camposEndereco = ['logradouro', 'numero_endereco', 'bairro', 'cidade', 'estado', 'cep', 'complemento'];
        $temEndereco = false;

        foreach ($camposEndereco as $campo) {
            if (array_key_exists($campo, $resultado) && $resultado[$campo] !== null && $resultado[$campo] !== '') {
                $temEndereco = true;
                break;
            }
        }

        if (!$temEndereco) {
            return null;
        }

        return new Endereco(
            $resultado['logradouro'] ?? null,
            $resultado['numero_endereco'] ?? null,
            $resultado['bairro'] ?? null,
            $resultado['cidade'] ?? null,
            $resultado['estado'] ?? null,
            $resultado['cep'] ?? null,
            $resultado['complemento'] ?? null,
        );
    }
}
