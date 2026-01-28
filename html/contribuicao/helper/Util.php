<?php
/**futuramente essa classe deverá ser depreciada, a classe `/classes/Util.php` deve ser usada no seu lugar*/
//Transferir eventuais métodos exclusivos para a classe Util central do projeto.
class Util
{

    /**
     * Registra o log de erro e emite um JSON para o cliente
     */
    public static function tratarException(Exception $e): void
    {
        //Armazena exceção em um arquivo de log
        error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
        http_response_code($e->getCode());
        //Adicionar futuramente verificação para outras exceções que precisem de uma mensagem personalizada
        if ($e instanceof PDOException) {
            echo json_encode(['erro' => 'Erro no servidor ao manipular o banco de dados']);
        } else { //mensagem padrão
            echo json_encode(['erro' => $e->getMessage()]);
        }
    }

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

        // Remove os diretórios '.' e '..' e o arquivo index.php da lista de arquivos
        $arquivos = array_diff($arquivos, array('.', '..', 'index.php',));

        return $arquivos;
    }

    public static function validarCPF(string $cpf)
    {
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

    public static function verificarRegras($valor, array $conjuntoRegrasPagamento)
    {
        if ($conjuntoRegrasPagamento && count($conjuntoRegrasPagamento) > 0) {
            foreach ($conjuntoRegrasPagamento as $regraPagamento) {
                if ($regraPagamento['id_regra'] == 1) {
                    if ($valor < $regraPagamento['valor']) {
                        echo json_encode(['erro' => "O valor informado está abaixo do permitido (R\${$regraPagamento['valor']})."]);
                        exit;
                    }
                } else if ($regraPagamento['id_regra'] == 2) {
                    if ($valor > $regraPagamento['valor']) {
                        echo json_encode(['erro' => "O valor informado está acima do permitido (R\${$regraPagamento['valor']})."]);
                        exit;
                    }
                }
            }
        }
    }

    /**
     * Valida se a ESTRUTURA de um CNPJ é válido
     */
    public static function validaEstruturaCnpj($cnpj)
    {
        if (strlen($cnpj) === 18 && strpos($cnpj, ".") === 2 && strpos($cnpj, ".", 3) === 6 && strpos($cnpj, "/") === 10 && strpos($cnpj, "-") === 15) {
            return true;
        }
        return false;
    }

    /**
     * Valida se um CNPJ é válido
     */
    public static function validaCNPJ($cnpj)
    {
        // Remove caracteres não numéricos
        $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);

        // Verifica se tem 14 dígitos
        if (strlen($cnpjLimpo) != 14) {
            return false;
        }

        // Elimina CNPJs com todos os dígitos iguais (ex: 11111111111111)
        if (preg_match('/(\d)\1{13}/', $cnpjLimpo)) {
            return false;
        }

        // Calcula o primeiro dígito verificador
        $peso1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;
        for ($i = 0; $i < 12; $i++) {
            $soma += $cnpjLimpo[$i] * $peso1[$i];
        }
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;

        // Calcula o segundo dígito verificador
        $peso2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;
        for ($i = 0; $i < 13; $i++) {
            $soma += $cnpjLimpo[$i] * $peso2[$i];
        }
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;

        // Verifica se os dígitos calculados conferem com os do CNPJ informado
        return ($cnpjLimpo[12] == $digito1 && $cnpjLimpo[13] == $digito2);
    }

    /**
     * Pega o IP da requisição do usuário.
     */
    public static function getUserIp(): ?string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);

                foreach ($ips as $ip) {
                    $ip = trim($ip);

                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6))
                        return $ip;
                }
            }
        }

        return null;
    }
}
