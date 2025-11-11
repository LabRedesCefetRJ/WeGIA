<?php

    Class SinaisVitais{
        private $id_fichamedica;
        private $id_funcionario;
        private $data;
        private $saturacao;
        private $pressao_arterial;
        private $frequencia_cardiaca;
        private $frequencia_respiratoria;
        private $temperatura;
        private $hgt;
        private $observacao;


        public function getIdFuncionario(){
            return $this->id_funcionario;
        }

        public function getIdFichamedica(){
            return $this->id_fichamedica;
        }

        public function getData(){
            return $this->data;
        }

        public function getSaturacao(){
            return $this->saturacao;
        }

        public function getPressaoArterial(){
            return $this->pressao_arterial;
        }

        public function getFrequenciaCardiaca(){
            return $this->frequencia_cardiaca;
        }
        public function getFrequenciaRespiratoria(){
            return $this->frequencia_respiratoria;
        }
        public function getTemperatura(){
            return $this->temperatura;
        }

        public function getHgt(){
            return $this->hgt;
        }

        public function getObservacao(){
            return $this->observacao;
        }

        public function setIdFuncionario($id_funcionario){
            $this->id_funcionario = $id_funcionario;
        }
        
        public function setIdFichamedica($id_fichamedica){
            $this->id_fichamedica = $id_fichamedica;
        }
        
        public function setData($data){
            $this->data = $data;
        }
        
        public function setSaturacao($saturacao){
            $this->saturacao = $saturacao;
        }
        
        public function setPressaoArterial($pressao_arterial){
            $this->pressao_arterial = $pressao_arterial;
        }
        
        public function setFrequenciaCardiaca($frequencia_cardiaca){
            $this->frequencia_cardiaca = $frequencia_cardiaca;
        }
        
        public function setFrequenciaRespiratoria($frequencia_respiratoria){
            $this->frequencia_respiratoria = $frequencia_respiratoria;
        }
        
        public function setTemperatura($temperatura){
            $this->temperatura = $temperatura;
        }
        
        public function setHgt($hgt){
            $this->hgt = $hgt;
        }

        public function setObservacao($observacao){
            $this->observacao = $observacao;
        }
    }
?>