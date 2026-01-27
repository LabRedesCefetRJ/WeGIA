<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'AtendidoDocumentacaoDTO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'AtendidoDocumentacaoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'AtendidoDocumentacaoMySql.php';

class AtendidoDocumentacao
{
    private int $id;
    private int $idAtendido;
    private int $idTipoDocumentacao;
    private int $idPessoaArquivo;
    private AtendidoDocumentacaoDAO $dao;

    public function __construct(AtendidoDocumentacaoDTO $dto, ?AtendidoDocumentacaoDAO $dao = null)
    {
        if (isset($dto->id))
            $this->setId($dto->id);

        $this->setIdAtendido($dto->idAtendido)->setIdTipoDocumentacao($dto->idTipoDocumentacao)->setIdPessoaArquivo($dto->idPessoaArquivo);

        isset($dao) ? $this->dao = $dao : $this->dao = new AtendidoDocumentacaoMySql();
    }

    /**
     * Cria a persistência do objeto no banco de dados do sistema.
     */
    public function create():int|false{
        return $this->dao->create($this);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        if ($id < 1)
            throw new InvalidArgumentException('O id da documentação de um atendido deve ser um inteiro maior ou igual a 1.', 412);

        if (strlen($id) > 11)
            throw new InvalidArgumentException('O tamanho do id excede o limite permitido.', 412);

        $this->id = $id;
        return $this;
    }

    public function getIdAtendido()
    {
        return $this->idAtendido;
    }

    public function setIdAtendido(int $idAtendido)
    {
        if ($idAtendido < 1)
            throw new InvalidArgumentException('O id de um atendido deve ser um inteiro maior ou igual a 1.', 412);

        if (strlen($idAtendido) > 11)
            throw new InvalidArgumentException('O tamanho do id do atendido excede o limite permitido.', 412);

        $this->idAtendido = $idAtendido;
        return $this;
    }

    public function getIdTipoDocumentacao()
    {
        return $this->idTipoDocumentacao;
    }

    public function setIdTipoDocumentacao(int $idTipoDocumentacao)
    {
        if ($idTipoDocumentacao < 1)
            throw new InvalidArgumentException('O id do tipo de uma documentação deve ser um inteiro maior ou igual a 1.', 412);

        if (strlen($idTipoDocumentacao) > 11)
            throw new InvalidArgumentException('O tamanho do id do tipo da documentação excede o limite permitido.', 412);

        $this->idTipoDocumentacao = $idTipoDocumentacao;
        return $this;
    }

    public function getIdPessoaArquivo()
    {
        return $this->idPessoaArquivo;
    }

    public function setIdPessoaArquivo(int $idPessoaArquivo)
    {
        if ($idPessoaArquivo < 1)
            throw new InvalidArgumentException('O id do arquivo de uma pessoa deve ser um inteiro maior ou igual a 1.', 412);

        if (strlen($idPessoaArquivo) > 11)
            throw new InvalidArgumentException('O tamanho do id do arquivo de uma pessoa excede o limite permitido.', 412);

        $this->idPessoaArquivo = $idPessoaArquivo;
        return $this;
    }
}
