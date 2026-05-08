<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Arquivo.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PessoaArquivoDTO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'PessoaArquivoMySQL.php';

class PessoaArquivo
{
    //properities
    private int $id;
    private int $idPessoa;
    private Arquivo $arquivo;
    private PessoaArquivoDAO $dao;

    //behavior
    public function __construct(PessoaArquivoDTO $dto, ?PessoaArquivoDAO $dao = null)
    {
        $this->setIdPessoa($dto->idPessoa)->setArquivo($dto->arquivo);

        if (isset($dto->id))
            $this->setId($dto->id);

        isset($dao) ? $this->dao = $dao : $this->dao = new PessoaArquivoMySQL(Conexao::connect());
    }

    /**
     * Cria a persistência do objeto no banco de dados do sistema.
     */
    public function create(): int|false
    {
        return $this->dao->create($this);
    }

    /**
     * Procura a persistência no banco de dados do sistema através do seu id.
     * Retorna um objeto do tipo PessoaArquivoDTO em caso de sucesso na busca
     */
    public static function getById(int $id, ?PessoaArquivoDAO $dao = null): PessoaArquivoDTO|null
    {
        if (!isset($dao))
            $dao = new PessoaArquivoMySQL(Conexao::connect());

        return $dao->getById($id);
    }

    /**
     * Apaga a persistência do banco de dados do sistema que possui id equivalente ao informado.
     */
    public static function deleteById(int $id, ?PessoaArquivoDAO $dao = null)
    {
        if (!isset($dao))
            $dao = new PessoaArquivoMySQL(Conexao::connect());

        return $dao->delete($id);
    }

    //access
    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        if ($id < 1)
            throw new InvalidArgumentException('O id não pode ser menor que 1.', 412);

        if (strlen($id) > 11)
            throw new InvalidArgumentException('O tamanho do id excede o limite máximo permitido.', 412);

        $this->id = $id;
        return $this;
    }

    public function setIdPessoa(int $idPessoa)
    {
        if ($idPessoa < 1)
            throw new InvalidArgumentException('O id da pessoa não pode ser menor que 1.', 412);

        if (strlen($idPessoa) > 11)
            throw new InvalidArgumentException('O tamanho do id da pessoa excede o limite máximo permitido.', 412);

        $this->idPessoa = $idPessoa;
        return $this;
    }

    public function getIdPessoa()
    {
        return $this->idPessoa;
    }

    public function setArquivo(Arquivo $arquivo)
    {
        $this->arquivo = $arquivo;
        return $this;
    }

    public function getArquivo()
    {
        return $this->arquivo;
    }
}
