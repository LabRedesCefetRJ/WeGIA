<?php
require_once 'Atendido.php';
// require_once 'Pessoa.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

class Saude extends Atendido
{
    private $texto;
    private $enfermidade;
    private $data_diagnostico;
    
    public function getTexto()
    {
        return $this->texto;
    }
    public function getEnfermidade()
    {
        return $this->enfermidade;
    }
    public function getData_diagnostico()
    {
        return $this->data_diagnostico;
    }
    public function setTexto($texto)
    {
        $this->texto = $texto;
    }
    public function setEnfermidade($enfermidade)
    {
        $this->enfermidade = $enfermidade;
    }
    public function setData_diagnostico($data_diagnostico)
    {
        $this->data_diagnostico = $data_diagnostico;
    }

}

?>