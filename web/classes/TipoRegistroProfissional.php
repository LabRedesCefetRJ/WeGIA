<?php
    class TipoRegistroProfissional implements JsonSerializable
    {
        private $id_tipo_registro;
        private $descricao;
        private $status;

        public function __construct($descricao,$id = null,$status = true){
            $this->setDescricao($descricao);
            if($id){
                $this->setId_tipo_registro($id);
            }
            if($status){
                $this->setStatus($staus);
            }
        }

        public function jsonSerialize(): mixed
        {
            return [
                'id_registro_profissional_tipo' => $this->id_tipo_registro,
                'descricao' => $this->descricao
            ];
        }

        public function getID_tipo_registro()
        {
            return $this->id_tipo_registro;
        }
        public function getDescricao()
        {
            return $this->descricao;
        }
        public function getStatus()
        {
            return $this->status;
        }
        public function setId_tipo_registro(int $id)
        {
            if($id < 1){
                throw new InvalidArgumentException('O número de um id não pode ser menor que 1.');
            }
            $this->id_tipo_registro = $id;
        }
        public function setDescricao(String $descricao)
        {
            if(empty($descricao)){
                throw new InvalidArgumentException('A descrição de um registro não pode ser vazia.');
            }
            $this->descricao = $descricao;
        }
        public function setStatus(bool $status)
        {
            $this->status = $status;
        }
    }
?>