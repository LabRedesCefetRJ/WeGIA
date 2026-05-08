<?php 
    class Aviso{

        //Propriedades
        private $idAviso;
        private $idFuncionario;
        private $idPessoaAtendida;
        private $descricao;
        private $data;

        /**
         * Recebe dois parâmetros do tipo inteiro ($idFuncionario, $idPessoaAtendida) e uma String ($descricao) para instanciar um objeto do tipo Aviso.
         */
        public function __construct(int $idFuncionario, int $idPessoaAtendida, string $descricao)
        {
            $this->setIdFuncionario($idFuncionario);
            $this->setIdPessoaAtendida($idPessoaAtendida);
            $this->setDescricao($descricao);
        }

        //Métodos Acessores

        public function getIdAviso(){
            return $this->idAviso;
        }

        public function setIdAviso(int $idAviso){
            if($idAviso < 1){
                throw new InvalidArgumentException('O id de uma intercorrência não pode ser menor que 1.');
            }
            $this->idAviso = $idAviso;
        }

        public function getIdFuncionario(){
            return $this->idFuncionario;
        }

        public function setIdFuncionario(int $idFuncionario){
            if($idFuncionario < 1){
                throw new InvalidArgumentException('O id de um funcionário não pode ser menor que 1.');
            }
            $this->idFuncionario = $idFuncionario;
        }

        public function getIdPessoaAtendida(){
            return $this->idPessoaAtendida;
        }

        public function setIdPessoaAtendida(int $idPessoaAtendia){
            if($idPessoaAtendia < 1){
                throw new InvalidArgumentException('O id de um paciente não pode ser menor que 1.');
            }
            $this->idPessoaAtendida = $idPessoaAtendia;
        }

        public function getDescricao(){
            return $this->descricao;
        }

        public function setDescricao(string $descricao){
            if(empty($descricao)){
                throw new InvalidArgumentException('A descrição de uma intercorrência não pode ser vazia.');
            }
            $this->descricao = $descricao;
        }

        public function getData(){
            return $this->data;
        }

        public function setData($data){
            $this->data = $data;
        }
    }
?>