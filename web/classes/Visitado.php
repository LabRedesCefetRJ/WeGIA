<?php

class Visitado
{
    private $id_visitado;
    private $id_visitado_tipo;
    private $id_atendido;
    private $id_funcionario;
    private $id_voluntario;
    private $id_pet;
    private $id_outro;

    public function getId_Visitado()
    {
        return $this->id_visitado;
    }
    public function getId_Visitado_Tipo()
    {
        return $this->id_visitado_tipo;
    }
    public function getId_Atendido()
    {
        return $this->id_atendido;
    }
    public function getId_Funcionario()
    {
        return $this->id_funcionario;
    }
    public function getId_Voluntario()
    {
        return $this->id_voluntario;
    }
    public function getId_Pet()
    {
        return $this->id_pet;
    }
    public function getId_Outro()
    {
        return $this->id_outro;
    }

    public function setId_Visitado($id_visitado)
    {
        $this->id_visitado = $id_visitado;
    }
    public function setId_Visitado_Tipo($id_visitado_tipo)
    {
        $this->id_visitado_tipo = $id_visitado_tipo;
    }
    public function setId_Atendido($id_atendido)
    {
        $this->id_atendido = $id_atendido;
    }
    public function setId_Funcionario($id_funcionario)
    {
        $this->id_funcionario = $id_funcionario;
    }
    public function setId_Voluntario($id_voluntario)
    {
        $this->id_voluntario = $id_voluntario;
    }
    public function setId_Pet($id_pet)
    {
        $this->id_pet = $id_pet;
    }
    public function setId_Outro($id_outro)
    {
        $this->id_outro = $id_outro;
    }
}