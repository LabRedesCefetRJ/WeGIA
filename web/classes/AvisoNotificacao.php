<?php 

    require_once 'Aviso.php';

    class AvisoNotificacao{
        //Propriedades
        private $idAvisoNotificacao;
        private $idAviso;
        private $idFuncionario;
        private $status;

        /**
         * Recebe um parâmetros do tipo Aviso e instancia um objeto do tipo AvisoNotificacao
         */
        public function __construct(Aviso $aviso)
        {
            $this->setIdAviso($aviso->getIdAviso());
            //$this->setIdFuncionario($aviso->getIdFuncionario());
            $this->setStatus(true);
        }

        //Métodos Acessores

        public function getIdAvisoNotificacao(){
            return $this->idAvisoNotificacao;
        }

        public function setIdAvisoNotificacao($idAvisoNotificacao){
            $this->idAvisoNotificacao = $idAvisoNotificacao;
        }

        public function getIdAviso(){
            return $this->idAviso;
        }

        public function setIdAviso($idAviso){
            $this->idAviso = $idAviso;
        }

        public function getIdFuncionario(){
            return $this->idFuncionario;
        }

        public function setIdFuncionario($idFuncionario){
            $this->idFuncionario = $idFuncionario;
        }

        public function getStatus(){
            return $this->status;
        }

        public function setStatus($status){
            $this->status = $status;
        }
    }
?>