<?php

namespace api\utils;

class Util
{
    /**
     * Normaliza o CPF removendo máscara e formatando para padrão xxx.xxx.xxx-xx
     * 
     * @param string $cpf CPF com ou sem máscara
     * @return string CPF formatado no padrão xxx.xxx.xxx-xx
     */
    public static function normalizeCpf(string $cpf): string
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
    public static function validateCpf(string $cpf): bool
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

    /**
     * Censura um e-mail preservando a primeira letra, as duas últimas letras
     * antes do @ e o domínio completo.
     *
     * Exemplos:
     * - joao.silva@gmail.com -> j*******va@gmail.com
     * - ab@gmail.com -> a*@gmail.com
     *
     * @param string $email E-mail a ser censurado
     * @return string|null E-mail censurado ou null se o valor for inválido
     */
    public static function censurarEmail(string $email): ?string
    {
        $email = trim($email);

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $partes = explode('@', $email, 2);
        if (count($partes) !== 2) {
            return null;
        }

        [$usuario, $dominio] = $partes;
        $tamanhoUsuario = strlen($usuario);

        if ($tamanhoUsuario === 0) {
            return null;
        }

        if ($tamanhoUsuario === 1) {
            $usuarioCensurado = $usuario;
        } elseif ($tamanhoUsuario === 2) {
            $usuarioCensurado = substr($usuario, 0, 1) . '*';
        } elseif ($tamanhoUsuario === 3) {
            $usuarioCensurado = substr($usuario, 0, 1) . '*' . substr($usuario, -1);
        } else {
            $usuarioCensurado = substr($usuario, 0, 1)
                . str_repeat('*', max(1, $tamanhoUsuario - 3))
                . substr($usuario, -2);
        }

        return $usuarioCensurado . '@' . $dominio;
    }

    /**
     * Normaliza o CNPJ removendo máscara e formatando para padrão xx.xxx.xxx/xxxx-xx
     * 
     * @param string $cnpj CNPJ com ou sem máscara
     * @return string CNPJ formatado no padrão xx.xxx.xxx/xxxx-xx
     */
    public static function normalizeCnpj(string $cnpj): string
    {
        // Remove prefixo 'cnpj=' se existir
        $cnpj = str_starts_with($cnpj, 'cnpj=') ? substr($cnpj, 5) : $cnpj;

        // Remove todos os caracteres não numéricos (máscara)
        $cnpjNumerico = preg_replace('/\D/', '', $cnpj);

        // Formata para o padrão xx.xxx.xxx/xxxx-xx
        if (strlen($cnpjNumerico) === 14) {
            return substr($cnpjNumerico, 0, 2) . '.' . substr($cnpjNumerico, 2, 3) . '.' . substr($cnpjNumerico, 5, 3) . '/' . substr($cnpjNumerico, 8, 4) . '-' . substr($cnpjNumerico, 12, 2);
        }

        return $cnpj; // Retorna original se não tiver 14 dígitos
    }

    /**
     * Valida o CNPJ usando o algoritmo de dígitos verificadores
     *
     * @param string $cnpj CNPJ com ou sem máscara
     * @return bool true se o CNPJ é válido, false caso contrário
     */
    public static function validateCnpj(string $cnpj): bool
    {
        // Remove caracteres não numéricos
        $cnpj = preg_replace('/\D/', '', $cnpj);

        // Deve ter exatamente 14 dígitos
        if (strlen($cnpj) !== 14) {
            return false;
        }

        // Rejeita sequências repetidas (00000000000000, 11111111111111, etc.)
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        // Validação do primeiro dígito verificador
        $pesos1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;

        for ($i = 0; $i < 12; $i++) {
            $soma += (int) $cnpj[$i] * $pesos1[$i];
        }

        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;

        // Validação do segundo dígito verificador
        $pesos2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;

        for ($i = 0; $i < 13; $i++) {
            $soma += (int) $cnpj[$i] * $pesos2[$i];
        }

        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;

        // Compara os dígitos calculados com os informados
        return $cnpj[12] == $digito1 && $cnpj[13] == $digito2;
    }
}
