<?php
namespace api\modules\Contribuicao;

use api\contracts\entities\ContribuicaoInterface;
use DateTime;

class Contribuicao implements ContribuicaoInterface
{
    private ?int $id = null;
    private ?int $idGateway = null;
    private int $idMeioPagamento;
    private int $idSocio;
    private float $valor;
    private ?DateTime $dataPagamento = null;
    private DateTime $dataVencimento;
    private DateTime $dataGeracao;
    private string $status;
    private string $codigo;

    public function __construct(?int $id, ?int $idGateway, int $idMeioPagamento, int $idSocio, float $valor, ?DateTime $dataPagamento, DateTime $dataVencimento, DateTime $dataGeracao, string $status, ?string $codigo = null)
    {
        if ($id !== null) {
            $this->setId($id);
        }

        if ($idGateway !== null) {
            $this->setIdGateway($idGateway);
        }

        if( $codigo !== null) {
            $this->setCodigo($codigo);
        } else {
            $this->setCodigo('wegia_' . $this->generateCodigo());
        }

        $this->setIdMeioPagamento($idMeioPagamento)
            ->setIdSocio($idSocio)
            ->setValor($valor)
            ->setDataPagamento($dataPagamento)
            ->setDataVencimento($dataVencimento)
            ->setDataGeracao($dataGeracao)
            ->setStatus($status);
    }

    // Behaviors methods

    private function generateCodigo(int $length = 8): string
    {
        return bin2hex(random_bytes($length));
    }

    // Getters and setters for each property can be added here
    public function getId(): int
    {
        return $this->id;
    }

    public function getIdGateway(): ?int
    {
        return $this->idGateway;
    }

    public function getIdMeioPagamento(): int
    {
        return $this->idMeioPagamento;
    }

    public function getIdSocio(): int
    {
        return $this->idSocio;
    }

    public function getValor(): float
    {
        return $this->valor;
    }

    public function getDataPagamento(): ?DateTime
    {
        return $this->dataPagamento;
    }

    public function getDataVencimento(): DateTime
    {
        return $this->dataVencimento;
    }

    public function getDataGeracao(): DateTime
    {
        return $this->dataGeracao;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function setId(int $id): ContribuicaoInterface
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException("ID deve ser um número positivo.", 400);
        }

        $this->id = $id;
        return $this;
    }

    public function setIdGateway(?int $idGateway): ContribuicaoInterface
    {
        if ($idGateway !== null && $idGateway <= 0) {
            throw new \InvalidArgumentException("ID do gateway deve ser um número positivo.", 400);
        }

        $this->idGateway = $idGateway;
        return $this;
    }

    public function setIdMeioPagamento(int $idMeioPagamento): ContribuicaoInterface
    {
        if ($idMeioPagamento <= 0) {
            throw new \InvalidArgumentException("ID do meio de pagamento deve ser um número positivo.", 400);
        }

        $this->idMeioPagamento = $idMeioPagamento;
        return $this;
    }

    public function setIdSocio(int $idSocio): ContribuicaoInterface
    {
        if( $idSocio <= 0) {
            throw new \InvalidArgumentException("ID do sócio deve ser um número positivo.", 400);
        }

        $this->idSocio = $idSocio;
        return $this;
    }

    public function setValor(float $valor): ContribuicaoInterface
    {
        if($valor <= 0) {
            throw new \InvalidArgumentException("O valor da contribuição deve ser um número positivo.", 400);
        }

        //considerar apenas as duas casas decimais do valor
        $valorRound = round($valor, 2);
        $this->valor = $valorRound;
        return $this;
    }

    public function setDataPagamento(?DateTime $dataPagamento): ContribuicaoInterface
    {
        $this->dataPagamento = $dataPagamento;
        return $this;
    }

    public function setDataVencimento(DateTime $dataVencimento): ContribuicaoInterface
    {
        $this->dataVencimento = $dataVencimento;
        return $this;
    }

    public function setDataGeracao(DateTime $dataGeracao): ContribuicaoInterface
    {
        $this->dataGeracao = $dataGeracao;
        return $this;
    }

    public function setStatus(string $status): ContribuicaoInterface
    {
        if (!in_array($status, ['paid', 'pending'])) {
            throw new \InvalidArgumentException("Status inválido. Deve ser 'pago' ou 'pendente'.", 400);
        }
        $this->status = $status;
        return $this;
    }

    private function setCodigo(string $codigo): ContribuicaoInterface
    {
        if (empty($codigo)) {
            throw new \InvalidArgumentException("Código não pode ser vazio.", 400);
        }

        if(strlen($codigo) > 255) {
            throw new \InvalidArgumentException("Código não pode ter mais de 255 caracteres.", 400);
        }

        $this->codigo = $codigo;
        return $this;
    }

}