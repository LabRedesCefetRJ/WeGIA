<?php
require_once dirname(__FILE__).'/Pessoa.php';

class PessoaDTOSocio extends Pessoa implements JsonSerializable{
    
    public function jsonSerialize(): mixed
    {
        return [
            'cpf' => $this->getCpf(),
            'nome' => $this->getNome(),
            'sobrenome' => $this->getSobrenome(),
            'sexo' => $this->getSexo(),
            'data_nascimento' => $this->getDataNascimento(),
            'telefone' => $this->getTelefone(),
            'cep' => $this->getCep(),
            'estado' => $this->getEstado(),
            'cidade' => $this->getCidade(),
            'bairro' => $this->getBairro(),
            'logradouro' => $this->getLogradouro(),
            'numero_endereco' => $this->getNumeroEndereco(),
            'complemento' => $this->getComplemento()
        ];
    }

}