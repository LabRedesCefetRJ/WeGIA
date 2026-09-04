<?php
    class TipoRegistroProfissional implements JsonSerializable
    {
        private $idTipoRegistro;
        private $descricao;
        private $status;

        public function __construct($descricao,$id = null,$status = true){
            $this->setDescricao($descricao);
            if($id){
                $this->setIdTipoRegistro($id);
            }
            if($status){
                $this->setStatus($status);
            }
        }

        public function jsonSerialize(): array
    {
        return [
            'id_registro_profissional_tipo' => $this->idTipoRegistro,
            'descricao' => $this->descricao,
            'status' => $this->status
        ];
    }

        public function getIdTipoRegistro()
        {
            return $this->idTipoRegistro;
        }
        public function getDescricao()
        {
            return $this->descricao;
        }
        public function getStatus()
        {
            return $this->status;
        }
        public function setIdTipoRegistro(int $id)
        {
            if($id < 1){
                throw new InvalidArgumentException('O número de um id não pode ser menor que 1.');
            }
            $this->idTipoRegistro = $id;
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