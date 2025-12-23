<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'EnfermidadeDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';

class EnfermidadeSaude
{
    private $idCid;
    private EnfermidadeDAO $enfermidadeDao;

    function __construct(int $id,  ?EnfermidadeDAO $enfermidadeDao = null)
    {
        $this->setIdCid($id);
        isset($enfermidadeDao) ? $this->enfermidadeDao = $enfermidadeDao : $this->enfermidadeDao = new EnfermidadeDAO(Conexao::connect());
    }

    public function getidCid()
    {
        return $this->idCid;
    }

    public function setIdCid(int $idCid)
    {
        if($idCid < 1)
            throw new InvalidArgumentException('O id fornecido é menor que 1.', 412);

        $this->idCid = $idCid;
        return $this;
    }

    /**
     * Aplica soft delete na enfermidade de id equivalente no banco de dados da aplicação.
     */
    function delete(int $idFichaMedica): bool
    {
        return $this->enfermidadeDao->inativarByIdCid($this->idCid, $idFichaMedica);
    }
}
