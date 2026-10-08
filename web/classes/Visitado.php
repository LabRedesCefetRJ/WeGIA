<?php

class Visitado
{

    public const IDENTIFICADOR_VAZIO = '—';

    public static function identificadorPessoa(?string $cpf): string
    {
        $digitos = preg_replace('/\D/', '', (string) $cpf);

        if (strlen($digitos) !== 11) {
            return 'CPF não informado';
        }

        return 'CPF: ***.' . substr($digitos, 3, 3) . '.' . substr($digitos, 6, 3) . '-**';
    }

    public static function identificadorPet(?string $especie, ?string $raca, ?string $cor, ?string $sexo): string
    {
        $sexo = strtoupper(trim((string) $sexo));
        $rotuloSexo = $sexo === 'M' ? 'Macho' : ($sexo === 'F' ? 'Fêmea' : '');

        $partes = [];
        foreach (['Espécie' => $especie, 'Raça' => $raca, 'Cor' => $cor, 'Sexo' => $rotuloSexo] as $rotulo => $valor) {
            $valor = trim((string) $valor);
            if ($valor !== '') {
                $partes[] = $rotulo . ': ' . $valor;
            }
        }

        return $partes ? implode(' • ', $partes) : self::IDENTIFICADOR_VAZIO;
    }

    private $id_visitado;
    private $id_pessoa;
    private $id_pet;
    private $id_setor;

    public function __construct($id_visitado = null, $id_pessoa = null, $id_pet = null, $id_setor = null)
    {
        $this->id_visitado = $id_visitado;
        $this->id_pessoa = $id_pessoa;
        $this->id_pet = $id_pet;
        $this->id_setor = $id_setor;
    }

    public function getId_Visitado()
    {
        return $this->id_visitado;
    }
    public function getId_Pessoa()
    {
        return $this->id_pessoa;
    }
    public function getId_Pet()
    {
        return $this->id_pet;
    }
    public function getId_Setor()
    {
        return $this->id_setor;
    }

    public function setId_Visitado($id_visitado)
    {
        $this->id_visitado = $id_visitado;
    }
    public function setId_Pessoa($id_pessoa)
    {
        $this->id_pessoa = $id_pessoa;
    }
    public function setId_Pet($id_pet)
    {
        $this->id_pet = $id_pet;
    }
    public function setId_Setor($id_setor)
    {
        $this->id_setor = $id_setor;
    }
}