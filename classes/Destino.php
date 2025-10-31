<?php
require_once ROOT . '/html/contribuicao/helper/Util.php';

class Destino
{
   private $id_destino;
   private $nome;
   private $cnpj;
   private $cpf;
   private $telefone;
   
    public function __construct($nome,$cnpj,$cpf,$telefone)
    {

        $this->nome=$nome;
        $this->cnpj=$cnpj;
        $this->cpf=$cpf;
        $this->telefone=$telefone;

    }

    public function getId_destino()
    {
        return $this->id_destino;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function getCnpj()
    {
        return $this->cnpj;
    }

    public function getCpf()
    {
        return $this->cpf;
    }

    public function getTelefone()
    {
        return $this->telefone;
    }

    public function setId_destino($id_destino)
    {
        $this->id_destino = $id_destino;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function setCnpj($cnpj) {
        // Validar se o CNPJ possui um formato válido - Xablau
        if (Util::validaEstruturaCnpj($cnpj) && Util::validaCnpj($cnpj)) {
            $this->cnpj = $cnpj;
        } else {
            throw new Exception('CNPJ inválido');
        }
    }

    /*
    private function validaCnpj($cnpj) {
        if(strlen($cnpj) === 18 && strpos($cnpj, ".") === 2 && strpos($cnpj, ".", 3) === 6 && strpos($cnpj, "/") === 10 && strpos($cnpj, "-") === 15) {
            return true;
        }
        return false;
    }
    */

    public function setCpf($cpf) {
        if (Util::validarCPF($cpf)) {
            $this->cpf = $cpf;
        } else {
            throw new Exception('CPF inválido');
        }
        
    }

    public function setTelefone($telefone)
    {
        $this->telefone = $telefone;
    }
}