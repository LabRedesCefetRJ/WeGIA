<?php
    class AgendaAlocacao{
        private $id;
        private $id_agenda;
        private $id_equipe;
        private $inicio;
        private $fim;
        private $lembrete;
        private $lembrete_enviado;

        public function getId()
        {
            return $this->id;
        }

        public function getId_agenda()
        {
            return $this->id_agenda;
        }

        public function getId_equipe()
        {        
            return $this->id_equipe;
        }

        public function getInicio()
        {
            return $this->inicio;
        }

        public function getFim()
        {
            return $this->fim;
        }

        public function getLembrete()
        {
            return $this->lembrete;
        }

        public function getLembrete_enviado()
        {
            return $this->lembrete_enviado;
        }

        public function setId(int $id)
        {
            if(!$id || $id < 1)
                throw new InvalidArgumentException('O id da alocação de agenda fornecido não é válido.', 412);

            $this->id = $id;
        }
        public function  setId_agenda($id_agenda)
        {
            $this->id_agenda = $id_agenda;
        }   
        public function setId_equipe($id_equipe)
        {
            $this->id_equipe = $id_equipe;
        }
        public function setInicio($inicio)
        {
            $this->inicio = $inicio;
        }
        public function setFim($fim)
        {
            $this->fim = $fim;
        }
        public function setLembrete($lembrete)
        {               
            $this->lembrete = $lembrete;
        }
        public function setLembrete_enviado($lembrete_enviado)
        {
            $this->lembrete_enviado = $lembrete_enviado;
        }
    }
?>