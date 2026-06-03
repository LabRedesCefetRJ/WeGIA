<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'VisitanteDocumentacaoDTO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'VisitanteDocumentacaoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'VisitanteDocumentacaoMySql.php';

class VisitanteDocumentacao
{
    private int $idDocumentacaoVisitante;
    private int $idVisitante;
    private int $idTipoDocumentacao;
    private int $idPessoaArquivo; 
    private VisitanteDocumentacaoDAO $dao;

    public function __construct(VisitanteDocumentacaoDTO $dto, ?VisitanteDocumentacaoDAO $dao = null)
    {
        if(isset($dto->idDocumentacaoVisitante))
            $this->setIdDocumentacaoVisitante($dto->idDocumentacaoVisitante);

        $this->setIdVisitante($dto->idVisitante);
        $this->setIdTipoDocumentacao($dto->idTipoDocumentacao);
        $this->setIdPessoaArquivo($dto->idPessoaArquivo);

        isset($dao) ? $this->dao = $dao : $this->dao = new VisitanteDocumentacaoMySql();
    }

    /**
     * Cria a persistência do objeto no banco de dados do sistema.
     */
    public function create():int|false{
        return $this->dao->create($this);
    }

    public function getIdDocumentacaoVisitante() : int
    {
        return $this->idDocumentacaoVisitante;
    }
    public function getIdVisitante() : int
    {
        return $this->idVisitante;
    }
    public function getIdTipoDocumentacao() : int
    {
        return $this->idTipoDocumentacao;
    }
    public function getIdPessoaArquivo() : int
    {
        return $this->idPessoaArquivo;
    }

    public function setIdDocumentacaoVisitante(int $idDocumentacaoVisitante) : self
    {
        if ($idDocumentacaoVisitante < 1)
            throw new InvalidArgumentException('O ID da documentação de um visitante deve ser um inteiro maior ou igual a 1.', 412);

        if (strlen($idDocumentacaoVisitante) > 11)
            throw new InvalidArgumentException('O tamanho do ID excede o limite permitido.', 412);

        $this->idDocumentacaoVisitante = $idDocumentacaoVisitante;
        return $this;
    }
    public function setIdVisitante(int $idVisitante) : self
    {
        if ($idVisitante < 1)
            throw new InvalidArgumentException('O ID de um visitante deve ser um inteiro maior ou igual a 1.', 412);

        if (strlen($idVisitante) > 11)
            throw new InvalidArgumentException('O tamanho do ID do visitante excede o limite permitido.', 412);

        $this->idVisitante = $idVisitante;
        return $this;
    }
    public function setIdTipoDocumentacao(int $idTipoDocumentacao) : self
    {
        if ($idTipoDocumentacao < 1)
            throw new InvalidArgumentException('O ID do tipo de uma documentação deve ser um inteiro maior ou igual a 1.', 412);

        if (strlen($idTipoDocumentacao) > 11)
            throw new InvalidArgumentException('O tamanho do ID do tipo da documentação excede o limite permitido.', 412);

        $this->idTipoDocumentacao = $idTipoDocumentacao;
        return $this;
    }
    public function setIdPessoaArquivo(int $idPessoaArquivo) : self
    {
        if ($idPessoaArquivo < 1)
            throw new InvalidArgumentException('O ID do arquivo de uma pessoa deve ser um inteiro maior ou igual a 1.', 412);

        if (strlen($idPessoaArquivo) > 11)
            throw new InvalidArgumentException('O tamanho do ID do arquivo de uma pessoa excede o limite permitido.', 412);

        $this->idPessoaArquivo = $idPessoaArquivo;
        return $this;
    }
}