<?php
    class IdentificadorRegistroProfissional{
        private $idIdentificadorRegistroProfissional;
        private $idTipoRegistroProfissional;
        private $idFuncionario;
        private $numeroRegistro;
        private $uf;

        public function __construct($idIdentificador, $idTipoRegistro, $idFuncionario, $numeroRegistro, $estado = null){
            $this->setIdIdentificador($idIdentificador);
            $this->setIdTipoRegistro($idTipoRegistro);
            $this->setIdFuncionario($idFuncionario);
            $this->setNumeroRegistro($numeroRegistro);
            $this->setUf($estado);
        }

        public function setIdIdentificador($idIdentificador){
            if($idIdentificador < 1){
                throw new InvalidArgumentException('O número de um id não pode ser menor que 1.');
            }
            $this->idIdentificadorRegistroProfissional = $idIdentificador;
        }

        public function setIdTipoRegistro($idTipoRegistro){
            $this->idTipoRegistroProfissional = $idTipoRegistro;
        }

        public function setIdFuncionario($idFuncionario){
            $this->idFuncionario = $idFuncionario;
        }

        public function setNumeroRegistro($numeroRegistro){
            if($numeroRegistro !== null && $numeroRegistro !== ''){
                $this->numeroRegistro = $numeroRegistro;
            }
        }

        public function setUf($uf){
            if($uf !== null && $uf !== ''){
                $this->uf = $uf;
            }
        }

        public function getIdIdentificador(){
            return $this->idIdentificadorRegistroProfissional;
        }

        public function getIdTipoRegistro(){
            return $this->idTipoRegistroProfissional;
        }

        public function getIdFuncionario(){
            return $this->idFuncionario;
        }

        public function getNumeroRegistro(){
            return $this->numeroRegistro;
        }

        public function getUf(){
            return $this->uf;
        }
    }
?>