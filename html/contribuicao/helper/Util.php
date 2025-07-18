<?php
class Util
{
    /**
     * Retorna um número com a quantidade de algarismos informada no parâmetro
     */
    public static function gerarNumeroDocumento($tamanho)
    {
        $numeroDocumento = '';

        for ($i = 0; $i < $tamanho; $i++) {
            $numeroDocumento .= rand(0, 9);
        }

        return intval($numeroDocumento);
    }

    /**
     * Gera um código completamente aleatório (Xablau)
     */
    public static function gerarCodigoAleatorio()
    {
        $numeroDePartes = rand(2, 5); // entre 2 e 5 partes
        $codigo = [];

        for ($i = 0; $i < $numeroDePartes; $i++) {
            $tamanho = rand(8, 20); // cada parte terá entre 8 e 20 caracteres
            $codigo[] = bin2hex(random_bytes(intval($tamanho / 2)));
        }

        return implode('-', $codigo);
    }

    /**
     * Retorna apenas os números de um CPF
     */
    public static function limpaCpf($cpf)
    {
        return preg_replace('/\D/', '', $cpf);
    }

    /**
     * Retorna apenas os números de um telefone
     */
    public static function limpaTelefone(string $telefone)
    {
        return preg_replace('/\D/', '', $telefone);
    }

    public static function mensalidadeInterna(int $intervalo, int $qtd_p, string $diaVencimento)
    {
        $datasVencimento = [];

        if (empty($diaVencimento)) {
            echo json_encode('O dia de vencimento de uma parcela não pode ser vazio');
            exit();
        }

        $dia = explode('-', $diaVencimento)[2];

        // Pegar a data informada
        $dataAtual = new DateTime($diaVencimento);

        // Iterar sobre a quantidade de parcelas
        for ($i = 0; $i < $qtd_p; $i++) {
            // Clonar a data atual para evitar modificar o objeto original
            $dataVencimento = clone $dataAtual;

            //incremento de meses
            $incremento = $intervalo * $i;

            // Adicionar os meses de acordo com o índice da parcela
            $dataVencimento->modify("+{$incremento} month");

            //verificar se o dia de dataVencimento é diferente de $dia, se forem diferentes
            //subtrair um mês e modificar para o último dia
            if ($dataVencimento->format('d') != $dia) {
                $dataVencimento->modify('last day of previous month');
            }

            // Adicionar a data formatada ao array
            $datasVencimento[] = $dataVencimento->format('Y-m-d');
        }
        return $datasVencimento;
    }

    /**
     * Recebe como parâmetro o caminho de um diretório e retorna a lista dos caminhos dos arquivos internos
     */
    public static function listarArquivos(string $diretorio)
    {
        // Verifica se o diretório existe
        if (!is_dir($diretorio)) {
            return false;
        }

        // Abre o diretório
        $arquivos = scandir($diretorio);

        // Remove os diretórios '.' e '..' da lista de arquivos
        $arquivos = array_diff($arquivos, array('.', '..'));

        return $arquivos;
    }

    public static function validarCPF(string $cpf){
        //Limpar formatação
        $cpfLimpo = preg_replace('/[^0-9]/', '', $cpf);

        //Validação do tamanho da string informada
        if (strlen($cpfLimpo) != 11) {
            return false;
        }

        // Validação de CPFs conhecidos como inválidos
        if (preg_match('/(\d)\1{10}/', $cpfLimpo)) {
            return false;
        }

        //Validação do primeiro dígito verificador
        $soma = 0;
        $resto = 0;

        for ($i = 0; $i < 9; $i++) { // Cálculo da soma
            $soma += $cpfLimpo[$i] * (10 - $i);
        }

        $resto = $soma % 11;

        $digitoVerificador1 = $resto < 2 ? 0 : 11 - $resto;

        if ($digitoVerificador1 != $cpfLimpo[9]) {
            return false;
        }

        //Validação do segundo dígito verificador
        $soma = 0;
        $resto = 0;
        //$digitoVerificador2 = 0;

        for ($i = 0; $i < 10; $i++) { // Cálculo da soma
            $soma += $cpfLimpo[$i] * (11 - $i);
        }

        $resto = $soma % 11;

        $digitoVerificador2 = $resto < 2 ? 0 : 11 - $resto;

        if ($digitoVerificador2 != $cpfLimpo[10]) {
            return false;
        }

        //Retornar resultado
        return true;
    }
}
