<?php

namespace api\utils;

class Cpf
{
    /**
     * Normaliza o CPF removendo máscara e formatando para padrão xxx.xxx.xxx-xx
     * 
     * @param string $cpf CPF com ou sem máscara
     * @return string CPF formatado no padrão xxx.xxx.xxx-xx
     */
    public static function normalize(string $cpf): string
    {
        // Remove prefixo 'cpf=' se existir
        $cpf = str_starts_with($cpf, 'cpf=') ? substr($cpf, 4) : $cpf;
        
        // Remove todos os caracteres não numéricos (máscara)
        $cpfNumerico = preg_replace('/\D/', '', $cpf);
        
        // Formata para o padrão xxx.xxx.xxx-xx
        if (strlen($cpfNumerico) === 11) {
            return substr($cpfNumerico, 0, 3) . '.' . substr($cpfNumerico, 3, 3) . '.' . substr($cpfNumerico, 6, 3) . '-' . substr($cpfNumerico, 9, 2);
        }
        
        return $cpf; // Retorna original se não tiver 11 dígitos
    }

    /**
     * Valida o CPF usando o algoritmo de dígitos verificadores
     * 
     * @param string $cpf CPF com ou sem máscara
     * @return bool true se o CPF é válido, false caso contrário
     */
    public static function validate(string $cpf): bool
    {
        // Remove caracteres não numéricos
        $cpfNumerico = preg_replace('/\D/', '', $cpf);
        
        // Verifica se tem 11 dígitos
        if (strlen($cpfNumerico) !== 11) {
            return false;
        }
        
        // Verifica se não é uma sequência repetida (ex: 111.111.111-11)
        if (preg_match('/^(\d)\1{10}$/', $cpfNumerico)) {
            return false;
        }
        
        // Calcula o primeiro dígito verificador
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += (int)$cpfNumerico[$i] * (10 - $i);
        }
        
        $primeiroDigito = 11 - ($soma % 11);
        $primeiroDigito = $primeiroDigito > 9 ? 0 : $primeiroDigito;
        
        if ((int)$cpfNumerico[9] !== $primeiroDigito) {
            return false;
        }
        
        // Calcula o segundo dígito verificador
        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += (int)$cpfNumerico[$i] * (11 - $i);
        }
        
        $segundoDigito = 11 - ($soma % 11);
        $segundoDigito = $segundoDigito > 9 ? 0 : $segundoDigito;
        
        if ((int)$cpfNumerico[10] !== $segundoDigito) {
            return false;
        }
        
        return true;
    }
}
