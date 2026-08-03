<?php

namespace api\modules\Socio;

use api\utils\UuidGenerator;
use Ramsey\Uuid\Uuid;
use PDO;

class SocioRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function save(Socio $socio): Socio|false
    {
        $uuidBinary = UuidGenerator::generateBinary();
        $query = "INSERT INTO socio (id_pessoa, id_sociostatus, id_sociotipo, valor_periodo, data_referencia, auto_status_contribuicoes, uuid) VALUES (:id_pessoa, :id_sociostatus, :id_sociotipo, :valor_periodo, :data_referencia, :auto_status_contribuicoes, :uuid)";
        $stmt = $this->db->prepare($query);
        $resultado = $stmt->execute([
            ':id_pessoa' => $socio->getPessoa()->getId(),
            ':id_sociostatus' => $socio->getStatus(),
            ':id_sociotipo' => $socio->getIdSocioTipo(),
            ':valor_periodo' => $socio->getValorMensalidade(),
            ':data_referencia' => $socio->getInicioContribuicao()->format('Y-m-d'),
            ':auto_status_contribuicoes' => $socio->getAutoStatusContribuicao() ? 1 : 0,
            ':uuid' => $uuidBinary
        ]);

        if (!$resultado || !$this->db->lastInsertId()) {
            return false;
        }
        $socioId = (int)$this->db->lastInsertId();
        $socio->setId($socioId);
        $socio->setUuid(Uuid::fromBytes($uuidBinary)->toString());
        return $socio;
    }

    public function getIdPessoaByIdSocio(int $idSocio): ?int
    {
        $query = "SELECT id_pessoa FROM socio WHERE id_socio = :id_socio";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id_socio' => $idSocio]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['id_pessoa'] : null;
    }

    public function findByPessoaId(int $idPessoa): ?array
    {
        $query = "SELECT 
                    id_socio,
                    id_sociostatus,
                    id_sociotipo,
                    valor_periodo,
                    data_referencia,
                    auto_status_contribuicoes,
                    uuid
                  FROM socio
                  WHERE id_pessoa = :id_pessoa
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id_pessoa' => $idPessoa]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            return null;
        }

        $result['uuid'] = isset($result['uuid']) && $result['uuid'] !== null
            ? Uuid::fromBytes($result['uuid'])->toString()
            : null;

        return $result;
    }

    public function findByUuidBinary(string $uuidBinary): ?array
    {
        $query = "SELECT
                    s.id_socio,
                    s.id_pessoa,
                    s.data_referencia,
                    s.uuid,
                    p.nome,
                    p.sobrenome,
                    p.cpf,
                    p.data_nascimento,
                    p.telefone,
                    p.email,
                    (
                        SELECT MAX(cl.data_pagamento)
                        FROM contribuicao_log cl
                        WHERE cl.id_socio = s.id_socio
                          AND cl.status_pagamento = 1
                    ) AS data_ultima_contribuicao
                  FROM socio s
                  LEFT JOIN pessoa p ON p.id_pessoa = s.id_pessoa
                  WHERE s.uuid = :uuid
                  LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':uuid', $uuidBinary, PDO::PARAM_LOB);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            return null;
        }

        $result['uuid'] = isset($result['uuid']) && $result['uuid'] !== null
            ? Uuid::fromBytes($result['uuid'])->toString()
            : null;

        return $result;
    }

    public function findContatoInstituicaoById(int $id): ?array
    {
        $query = "SELECT id, descricao, contato
                  FROM contato_instituicao
                  WHERE id = :id
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    public function getBenefitRules(): array
    {
        $query = "SELECT 
                    analysis_window_months, 
                    max_points_concurrent, 
                    value_per_point 
                  FROM socio_benefit_rule
                  WHERE active = 1
                  LIMIT 1";
        $stmt = $this->db->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result === false ? [] : $result;
    }

    public function findContribuicoesBySocioIdAndDateRange(int $idSocio, int $months): array
    {
        $query = "SELECT valor, data_pagamento FROM contribuicao_log WHERE id_socio = :id_socio AND status_pagamento=1 AND data_pagamento >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':id_socio' => $idSocio,
            ':months' => $months
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertSocioParceiro(ParceiroInstitucional $parceiro): int|false
    {
        //adaptar query

        $query = "INSERT INTO socio_parceiro_institucional (id_socio_benefit_rule, id_pessoa, divulgacao, localizacao, created_at, updated_at) VALUES (:idSocioBenefitRule, :idPessoa, :divulgacao, :localizacao, :created_at, :updated_at)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':idSocioBenefitRule' => 1, //temporariamente fixo
            ':idPessoa' => $parceiro->getPessoa()->getId(),
            ':divulgacao' => $parceiro->getDivulgacao(),
            ':localizacao' => $parceiro->getLocalizacao(),
            ':created_at' => date('Y-m-d H:i:s'),
            ':updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->db->lastInsertId() ? (int)$this->db->lastInsertId() : false;
    }

    public function getSociosParceiros(): array
    {
        $query = "SELECT 
                    spi.id,
                    spi.id_pessoa,
                    spi.ativo,
                    spi.divulgacao,
                    spi.localizacao,
                    p.nome as razao_social,
                    p.cpf as cnpj,
                    p.telefone,
                    p.email,
                    p.cep,
                    p.estado,
                    p.cidade,
                    p.bairro,
                    p.logradouro,
                    p.numero_endereco,
                    p.complemento
                  FROM socio_parceiro_institucional spi
                  JOIN pessoa p ON spi.id_pessoa = p.id_pessoa";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findSocioParceiroById(int $id): ?array
    {
        $query = "SELECT 
                    spi.id,
                    spi.id_pessoa,
                    spi.id_socio_benefit_rule,
                    spi.ativo,
                    spi.divulgacao,
                    spi.localizacao,
                    p.nome as razao_social,
                    p.cpf as cnpj,
                    p.telefone,
                    p.email,
                    p.cep,
                    p.estado,
                    p.cidade,
                    p.bairro,
                    p.logradouro,
                    p.numero_endereco,
                    p.complemento
                  FROM socio_parceiro_institucional spi
                  JOIN pessoa p ON spi.id_pessoa = p.id_pessoa
                  WHERE spi.id = :id
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    public function updateSocioParceiro(int $id, array $dados): ?array
    {
        $atual = $this->findSocioParceiroById($id);
        if (!$atual) {
            return null;
        }

        $this->db->beginTransaction();

        try {
            $queryPessoa = "UPDATE pessoa SET 
                                nome = :nome,
                                cpf = :cpf,
                                telefone = :telefone,
                                email = :email,
                                cep = :cep,
                                estado = :estado,
                                cidade = :cidade,
                                bairro = :bairro,
                                logradouro = :logradouro,
                                numero_endereco = :numero_endereco,
                                complemento = :complemento
                            WHERE id_pessoa = :id_pessoa";
            $stmtPessoa = $this->db->prepare($queryPessoa);
            $stmtPessoa->execute([
                ':nome' => $dados['razao_social'] ?? $atual['razao_social'],
                ':cpf' => $dados['cnpj'] ?? $atual['cnpj'],
                ':telefone' => $dados['telefone'] ?? $atual['telefone'],
                ':email' => $dados['email'] ?? $atual['email'],
                ':cep' => $dados['endereco']['cep'] ?? $atual['cep'],
                ':estado' => $dados['endereco']['estado'] ?? $atual['estado'],
                ':cidade' => $dados['endereco']['cidade'] ?? $atual['cidade'],
                ':bairro' => $dados['endereco']['bairro'] ?? $atual['bairro'],
                ':logradouro' => $dados['endereco']['logradouro'] ?? $atual['logradouro'],
                ':numero_endereco' => $dados['endereco']['numero_endereco'] ?? $atual['numero_endereco'],
                ':complemento' => $dados['endereco']['complemento'] ?? $atual['complemento'],
                ':id_pessoa' => $atual['id_pessoa']
            ]);

            $querySocioParceiro = "UPDATE socio_parceiro_institucional SET 
                                        localizacao = :localizacao,
                                        divulgacao = :divulgacao
                                    WHERE id = :id";
            $stmtSocioParceiro = $this->db->prepare($querySocioParceiro);
            $stmtSocioParceiro->execute([
                ':localizacao' => $dados['localizacao'] ?? $atual['localizacao'],
                ':divulgacao' => $dados['divulgacao'] ?? $atual['divulgacao'],
                ':id' => $id
            ]);

            $this->db->commit();

            return $this->findSocioParceiroById($id);
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function alterStatusSocioParceiro(int $id, int $status): bool
    {
        $query = "UPDATE socio_parceiro_institucional SET ativo = :status WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id
        ]);
    }

    public function uploadLogoSocioParceiro(int $id, \Psr\Http\Message\UploadedFileInterface $uploadedFile): bool
    {
        $query = "
            UPDATE pessoa
            SET imagem = :imagem
            WHERE id_pessoa = (
                SELECT id_pessoa
                FROM socio_parceiro_institucional
                WHERE id = :id
            )
        ";

        $stmt = $this->db->prepare($query);

        $conteudo = $uploadedFile->getStream()->getContents();
        $mime = $uploadedFile->getClientMediaType();

        //modelo padrão de armazenamento de imagens no banco de dados do projeto
        $imagem = sprintf(
            'data:%s;base64,%s',
            $mime,
            base64_encode($conteudo)
        );

        $stmt->bindValue(':imagem', $imagem, \PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getLogoSocioParceiro(int $id): ?array
    {
        $query = "
            SELECT p.imagem
            FROM pessoa p
            JOIN socio_parceiro_institucional spi ON spi.id_pessoa = p.id_pessoa
            WHERE spi.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ?? null;
    }

    public function deleteSocioParceiro(int $id): bool
    {
        $query = "DELETE FROM socio_parceiro_institucional WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
