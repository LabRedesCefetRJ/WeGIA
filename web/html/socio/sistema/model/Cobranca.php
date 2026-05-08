<?php
    class Cobranca{

        private $id;
        private $idSocio;
        private $localRecepcao;
        private $receptor;
        private $valorPagamento;
        private $formaPagamento;
        private $dataPagamento;
        private $codigo;
        private $linkCobranca;
        private $linkBoleto;
        private $linhaDigitavel;

        public function setIdSocio($idSocio){
            $this->idSocio = $idSocio;
        }

        public function getIdSocio(){
            return $this->idSocio;
        }

        public function setLocalRecepcao($localRecepcao){
            $this->localRecepcao = $localRecepcao;
        }

        public function getLocalRecepcao(){
            return $this->localRecepcao;
        }

        public function setReceptor($receptor){
            $this->receptor = $receptor;
        }

        public function getReceptor(){
            return $this->receptor;
        }

        public function setValorPagamento($valorPagamento){
            $this->valorPagamento = $valorPagamento;
        }

        public function getValorPagamento(){
            return $this->valorPagamento;
        }

        public function setFormaPagamento($formaPagamento){
            $this->formaPagamento = $formaPagamento;
        }

        public function getFormaPagamento(){
            return $this->formaPagamento;
        }

        /**
         * Get the value of dataPagamento
         */ 
        public function getDataPagamento()
        {
                return $this->dataPagamento;
        }

        /**
         * Set the value of dataPagamento
         *
         * @return  self
         */ 
        public function setDataPagamento($dataPagamento)
        {
                $this->dataPagamento = $dataPagamento;
        }

        /**
         * Get the value of id
         */ 
        public function getId()
        {
                return $this->id;
        }

        /**
         * Set the value of id
         *
         * @return  self
         */ 
        public function setId($id)
        {
                $this->id = $id;
        }

        public function getDescricao(){
            return [
                    'formaPagamento' => $this->formaPagamento, 
                    'localidade' => $this->localRecepcao, 
                    'receptor' => $this->receptor
                   ];
        }

        /**
         * Get the value of codigo
         */ 
        public function getCodigo()
        {
                return $this->codigo;
        }

        /**
         * Set the value of codigo
         *
         * @return  self
         */ 
        public function setCodigo($codigo)
        {
                $this->codigo = $codigo;

                return $this;
        }

        /**
         * Get the value of linkCobranca
         */ 
        public function getLinkCobranca()
        {
                return $this->linkCobranca;
        }

        /**
         * Set the value of linkCobranca
         *
         * @return  self
         */ 
        public function setLinkCobranca($linkCobranca)
        {
                $this->linkCobranca = $linkCobranca;

                return $this;
        }

        /**
         * Get the value of linkBoleto
         */ 
        public function getLinkBoleto()
        {
                return $this->linkBoleto;
        }

        /**
         * Set the value of linkBoleto
         *
         * @return  self
         */ 
        public function setLinkBoleto($linkBoleto)
        {
                $this->linkBoleto = $linkBoleto;

                return $this;
        }

        /**
         * Get the value of linhaDigitavel
         */ 
        public function getLinhaDigitavel()
        {
                return $this->linhaDigitavel;
        }

        /**
         * Set the value of linhaDigitavel
         *
         * @return  self
         */ 
        public function setLinhaDigitavel($linhaDigitavel)
        {
                $this->linhaDigitavel = $linhaDigitavel;

                return $this;
        }
    }
?>