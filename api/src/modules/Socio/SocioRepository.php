<?php
namespace api\modules\Socio;
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
        $query = "INSERT INTO socio (id_pessoa, id_sociostatus, id_sociotipo, valor_periodo, data_referencia, auto_status_contribuicoes) VALUES (:id_pessoa, :id_sociostatus, :id_sociotipo, :valor_periodo, :data_referencia, :auto_status_contribuicoes)";
        $stmt = $this->db->prepare($query);
        $resultado = $stmt->execute([
            ':id_pessoa' => $socio->getPessoa()->getId(),
            ':id_sociostatus' => $socio->getStatus(),
            ':id_sociotipo' => $socio->getIdSocioTipo(),
            ':valor_periodo' => $socio->getValorMensalidade(),
            ':data_referencia' => $socio->getInicioContribuicao()->format('Y-m-d'),
            ':auto_status_contribuicoes' => $socio->getAutoStatusContribuicao() ? 1 : 0
        ]);

        if (!$resultado || !$this->db->lastInsertId()) {
            return false;
        }
        $socioId = (int)$this->db->lastInsertId();
        $socio->setId($socioId);
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

}