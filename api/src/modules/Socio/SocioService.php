<?php

namespace api\modules\Socio;

use api\contracts\entities\PessoaInterface;
use api\contracts\entities\SocioInterface;
use api\contracts\services\SocioServiceInterface;
use api\modules\Auth\AuthService;
use api\utils\Util;
use DateTime;
use Ramsey\Uuid\Uuid;

class SocioService implements SocioServiceInterface
{
    private SocioRepository $socioRepository;
    private ?EmailVerificationService $emailVerificationService;
    private ?AuthService $authService;

    public function __construct(SocioRepository $socioRepository, ?EmailVerificationService $emailVerificationService = null, ?AuthService $authService = null)
    {
        $this->socioRepository = $socioRepository;
        $this->emailVerificationService = $emailVerificationService;
        $this->authService = $authService;
    }

    public function criarSocio(PessoaInterface $pessoa, DateTime $inicioContribuicao, float $valorMensalidade, int $idSocioStatus = 1, bool $autoStatusContribuicao = true, int $idSocioTipo = 0): SocioInterface
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

    public function getIdPessoaByIdSocio(int $idSocio): ?int
    {
        return $this->socioRepository->getIdPessoaByIdSocio($idSocio);
    }

    public function obterSocioPorPessoaId(int $idPessoa, PessoaInterface $pessoa): ?SocioInterface
    {
        $resultado = $this->socioRepository->findByPessoaId($idPessoa);

        if (!$resultado) {
            return null;
        }

        return new Socio(
            $pessoa,
            new DateTime($resultado['data_referencia']),
            (float)$resultado['valor_periodo'],
            (int)$resultado['id_sociostatus'],
            (bool)$resultado['auto_status_contribuicoes'],
            (int)$resultado['id_sociotipo'],
            (int)$resultado['id_socio'],
            $resultado['uuid'] ?? null
        );
    }

    public function obterContatoSuporte(): ?array
    {
        $resultado = $this->socioRepository->findContatoInstituicaoById(1);
        if (!$resultado) {
            return null;
        }

        return ['contatct' => $resultado['contato'] ?? null];
    }

    public function obterBeneficiosPorSocio(int $idSocio): int
    {
        //pegar as regras de benefício.
        $benefitRules = $this->socioRepository->getBenefitRules();

        if (empty($benefitRules)) {
            throw new \Exception("Benefit rules not defined", 500);
        }

        //meses
        $tempoAnalise = $benefitRules['analysis_window_months'];
        $maxPontos = $benefitRules['max_points_concurrent'];

        //reais
        $valorPonto = $benefitRules['value_per_point'];

        //pegar as contribuições do sócio nos últimos X meses
        $contribuicoes = $this->socioRepository->findContribuicoesBySocioIdAndDateRange($idSocio, $tempoAnalise);

        if (empty($contribuicoes)) {
            return 0;
        }

        //calcular os pontos
        $pontos = 0;
        $valorTotal = 0;

        foreach ($contribuicoes as $contribuicao) {
            $valorTotal += $contribuicao['valor'];
        }

        $pontos = floor($valorTotal / $valorPonto);

        //limitar os pontos à quantidade máxima
        $pontos = min($pontos, $maxPontos);

        return (int)$pontos;
    }

    public function validarBeneficiosPorUuid(string $uuid): ?array
    {
        try {
            $uuidObj = Uuid::fromString($uuid);
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException('UUID inválido.', 400);
        }

        if ($uuidObj->getVersion() !== 7) {
            throw new \InvalidArgumentException('UUID v7 inválido.', 400);
        }

        $resultado = $this->socioRepository->findByUuidBinary($uuidObj->getBytes());

        if (!$resultado) {
            return null;
        }

        $idSocio = (int)$resultado['id_socio'];
        $pontosBeneficios = $this->obterBeneficiosPorSocio($idSocio);

        return [
            'nome' => $resultado['nome'] ?? '',
            'sobrenome' => $resultado['sobrenome'] ?? '',
            'email' => $resultado['email'] ?? null,
            'telefone' => $resultado['telefone'] ?? null,
            'dataNascimento' => $this->censurarDataNascimento($resultado['data_nascimento'] ?? null),
            'cpf' => $this->censurarCpf($resultado['cpf'] ?? null),
            'dataReferenciaContribuicao' => $this->normalizarData($resultado['data_referencia'] ?? null),
            'dataUltimaContribuicao' => $this->normalizarData($resultado['data_ultima_contribuicao'] ?? null),
            'benefit_points' => $pontosBeneficios
        ];
    }

    public function atualizarSocio(int $id, PessoaInterface $pessoa, DateTime $inicioContribuicao, float $valorMensalidade, int $idSocioStatus = 1, bool $autoStatusContribuicao = true, int $idSocioTipo = 0): SocioInterface
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

    /**
     * Alter password of a socio using a verification code
     * 
     * @param int $idSocio The ID of the socio
     * @param int $idPessoa The ID of the pessoa (user) associated with the socio
     * @param string $senha The new password
     * @param string $confirmacaoSenha The password confirmation
     * @param string $code The verification code
     * @return array Result array with success status and message
     */
    public function alterPassword(int $idSocio, string $senha, string $confirmacaoSenha, string $code): array
    {
        try {
            // Validate that both email verification and auth services are available
            if ($this->emailVerificationService === null || $this->authService === null) {
                return [
                    'success' => false,
                    'message' => 'Required services not available'
                ];
            }

            // Verify password and confirmation are equals
            if ($senha !== $confirmacaoSenha) {
                return [
                    'success' => false,
                    'message' => 'Passwords do not match'
                ];
            }

            // Verify the code
            $verifyResult = $this->emailVerificationService->verifyCode($idSocio, $code);
            if (!$verifyResult['success']) {
                return $verifyResult;
            }

            //get id_pessoa associated with id_socio
            $idPessoa = $this->socioRepository->getIdPessoaByIdSocio($idSocio);
            if (!$idPessoa) {
                return [
                    'success' => false,
                    'message' => 'Pessoa not found for the given socio ID'
                ];
            }

            // Update the password
            try {
                $this->authService->assignPasswordToPerson($idPessoa, $senha);
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Error updating password: ' . $e->getMessage()
                ];
            }

            // Mark the code as used
            $markResult = $this->emailVerificationService->markCodeAsUsed($idSocio, $code);
            if (!$markResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Password updated but error marking code as used: ' . $markResult['message']
                ];
            }

            return [
                'success' => true,
                'message' => 'Password altered successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error altering password: ' . $e->getMessage()
            ];
        }
    }

    public function insertSocioParceiro(ParceiroInstitucional $parceiro): array|false
    {
        $result = $this->socioRepository->insertSocioParceiro($parceiro);

        if ($result === false || $result < 1) {
            return false;
        }

        return ['id' => $result];
    }

    public function deleteSocioParceiro(int $id): array
    {
        try {
            $result = $this->socioRepository->deleteSocioParceiro($id);
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Socio parceiro deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete socio parceiro'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error deleting socio parceiro: ' . $e->getMessage()
            ];
        }
    }

    public function getSocioParceiros(): array
    {
        try {
            $result = $this->socioRepository->getSociosParceiros();
            return [
                'success' => true,
                'data' => $result
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching socio parceiros: ' . $e->getMessage()
            ];
        }
    }

    public function alterStatusSocioParceiro(int $id, int $novoStatus): array
    {
        try {
            $result = $this->socioRepository->alterStatusSocioParceiro($id, $novoStatus);
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Status of socio parceiro updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update status of socio parceiro'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating status of socio parceiro: ' . $e->getMessage()
            ];
        }
    }

    public function atualizarSocioParceiro(int $id, array $dados): array
    {
        try {
            $atual = $this->socioRepository->findSocioParceiroById($id);
            if (!$atual) {
                return [
                    'success' => false,
                    'message' => 'Socio parceiro não localizado',
                    'code' => 404
                ];
            }

            $camposAtualizaveis = ['razao_social', 'cnpj', 'telefone', 'email', 'localizacao', 'divulgacao', 'descricao', 'endereco'];
            $temAlteracao = false;
            foreach ($camposAtualizaveis as $campo) {
                if (array_key_exists($campo, $dados)) {
                    $temAlteracao = true;
                    break;
                }
            }

            if (!$temAlteracao) {
                return [
                    'success' => false,
                    'message' => 'Nenhum dado foi informado para atualização',
                    'code' => 400
                ];
            }

            $cnpj = array_key_exists('cnpj', $dados) ? trim((string)$dados['cnpj']) : (string)$atual['cnpj'];
            if ($cnpj !== '' && !Util::validateCnpj($cnpj)) {
                return [
                    'success' => false,
                    'message' => 'CNPJ inválido',
                    'code' => 400
                ];
            }

            if ($cnpj !== '') {
                $cnpj = Util::normalizeCnpj($cnpj);
            }

            $enderecoAtual = [
                'cep' => $atual['cep'] ?? null,
                'estado' => $atual['estado'] ?? null,
                'cidade' => $atual['cidade'] ?? null,
                'bairro' => $atual['bairro'] ?? null,
                'logradouro' => $atual['logradouro'] ?? null,
                'numero_endereco' => $atual['numero_endereco'] ?? null,
                'complemento' => $atual['complemento'] ?? null
            ];

            $enderecoEntrada = [];
            if (isset($dados['endereco']) && is_array($dados['endereco'])) {
                $enderecoEntrada = $dados['endereco'];
            }

            if (array_key_exists('numero', $enderecoEntrada) && !array_key_exists('numero_endereco', $enderecoEntrada)) {
                $enderecoEntrada['numero_endereco'] = $enderecoEntrada['numero'];
            }

            $dadosAtualizados = [
                'razao_social' => array_key_exists('razao_social', $dados) ? trim((string)$dados['razao_social']) : $atual['razao_social'],
                'cnpj' => $cnpj,
                'telefone' => array_key_exists('telefone', $dados) ? $dados['telefone'] : $atual['telefone'],
                'email' => array_key_exists('email', $dados) ? $dados['email'] : $atual['email'],
                'localizacao' => array_key_exists('localizacao', $dados) ? trim((string)$dados['localizacao']) : $atual['localizacao'],
                'divulgacao' => array_key_exists('divulgacao', $dados) ? trim((string)$dados['divulgacao']) : $atual['divulgacao'],
                'descricao' => array_key_exists('descricao', $dados) ? trim((string)$dados['descricao']) : $atual['descricao'],
                'endereco' => array_merge($enderecoAtual, $enderecoEntrada)
            ];

            $resultado = $this->socioRepository->updateSocioParceiro($id, $dadosAtualizados);
            if (!$resultado) {
                return [
                    'success' => false,
                    'message' => 'Failed to update socio parceiro',
                    'code' => 500
                ];
            }

            return [
                'success' => true,
                'message' => 'Socio parceiro updated successfully',
                'data' => $resultado
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error updating socio parceiro: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    public function uploadLogoSocioParceiro(int $id, \Psr\Http\Message\UploadedFileInterface $uploadedFile): bool
    {
        return $this->socioRepository->uploadLogoSocioParceiro($id, $uploadedFile);
    }

    public function getLogoSocioParceiro(int $id): ?array
    {
        $result = $this->socioRepository->getLogoSocioParceiro($id);

        if (!$result || empty($result['imagem'])) {
            return null;
        }

        return $this->decodificarImagemBase64($result['imagem']);
    }

    private function decodificarImagemBase64(string $imagemBase64): array
    {
        $mime = 'image/png';
        $conteudoBase64 = $imagemBase64;

        if (str_starts_with($imagemBase64, 'data:')) {
            $partes = explode(',', $imagemBase64, 2);
            if (count($partes) !== 2) {
                throw new \RuntimeException('Formato inválido da imagem', 500);
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
            throw new \RuntimeException('Não foi possível decodificar a imagem', 500);
        }

        return [
            'mime' => $mime,
            'conteudo' => $conteudo,
        ];
    }

    private function censurarCpf(?string $cpf): ?string
    {
        if (empty($cpf)) {
            return null;
        }

        $cpfNumerico = preg_replace('/\D/', '', $cpf);

        if (strlen($cpfNumerico) !== 11) {
            return null;
        }

        return '***.***.***-' . substr($cpfNumerico, -2);
    }

    private function censurarDataNascimento(?string $dataNascimento): ?string
    {
        $data = $this->normalizarData($dataNascimento);
        if ($data === null) {
            return null;
        }

        $dataTime = DateTime::createFromFormat('Y-m-d', $data);
        if ($dataTime === false) {
            return null;
        }

        return $dataTime->format('d') . '/**/**' . $dataTime->format('y');
    }

    private function normalizarData(?string $data): ?string
    {
        if (empty($data)) {
            return null;
        }

        $baseData = explode(' ', trim($data))[0] ?? '';
        $dataTime = DateTime::createFromFormat('Y-m-d', $baseData);

        if ($dataTime === false) {
            return null;
        }

        return $dataTime->format('Y-m-d');
    }
}
