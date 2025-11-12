<?php

class Util
{
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
     * Verifica se um CNPJ é válido
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

    // esta função formata para o bd
    public function formatoDataYMD($data)
    {
        $data_arr = explode("/", $data);

        $datac = $data_arr[2] . '-' . $data_arr[1] . '-' . $data_arr[0];

        return $datac;
    }

    // esta função formata para exibir no view
    public function formatoDataDMY($data)
    {
        $data_arr = explode("-", $data);

        $datad = explode(' ', $data_arr[2])[0] . '/' . $data_arr[1] . '/' . $data_arr[0];

        return $datad;
    }

    // converte para real
    public function virgula($valor)
    {
        $conta = strlen($valor);

        switch ($conta) {

            case "1":

                $retorna = "0,0$valor";

                break;

            case "2":

                $retorna = "0,$valor";

                break;

            case "3":

                $d1 = substr("$valor", 0, 1);

                $d2 = substr("$valor", -2, 2);

                $retorna = "$d1,$d2";

                break;

            case "4":

                $d1 = substr("$valor", 0, 2);

                $d2 = substr("$valor", -2, 2);

                $retorna = "$d1,$d2";

                break;

            case "5":

                $d1 = substr("$valor", 0, 3);

                $d2 = substr("$valor", -2, 2);

                $retorna = "$d1,$d2";

                break;

            case "6":

                $d1 = substr("$valor", 1, 3);

                $d2 = substr("$valor", -2, 2);

                $d3 = substr("$valor", 0, 1);

                $retorna = "$d3.$d1,$d2";

                break;

            case "7":

                $d1 = substr("$valor", 2, 3);

                $d2 = substr("$valor", -2, 2);

                $d3 = substr("$valor", 0, 2);

                $retorna = "$d3.$d1,$d2";

                break;

            case "8":

                $d1 = substr("$valor", 3, 3);

                $d2 = substr("$valor", -2, 2);

                $d3 = substr("$valor", 0, 3);

                $retorna = "$d3.$d1,$d2";

                break;
        }

        return $retorna;
    }

    public function trataDataExtenso($data)
    {
        $data_arr = explode("-", $data);

        // leitura das datas

        $dia = $data_arr[2];

        $mes = $data_arr[1];

        $ano = $data_arr[0];

        // $semana = date('w');

        // configuração mes

        switch ($mes) {

            case 1:
                $mes = "Janeiro";

                break;

            case 2:
                $mes = "Fevereiro";

                break;

            case 3:
                $mes = "Março";

                break;

            case 4:
                $mes = "Abril";

                break;

            case 5:
                $mes = "Maio";

                break;

            case 6:
                $mes = "Junho";

                break;

            case 7:
                $mes = "Julho";

                break;

            case 8:
                $mes = "Agosto";

                break;

            case 9:
                $mes = "Setembro";

                break;

            case 10:
                $mes = "Outubro";

                break;

            case 11:
                $mes = "Novembro";

                break;

            case 12:
                $mes = "Dezembro";

                break;
        }

        // configura??o semana

        /*switch ($semana) {

            case 0:
                $semana = "DOMINGO";

                break;

            case 1:
                $semana = "SEGUNDA FEIRA";

                break;

            case 2:
                $semana = "TER?A-FEIRA";

                break;

            case 3:
                $semana = "QUARTA-FEIRA";

                break;

            case 4:
                $semana = "QUINTA-FEIRA";

                break;

            case 5:
                $semana = "SEXTA-FEIRA";

                break;

            case 6:
                $semana = "S?BADO";

                break;
        }*/

        // Agora basta imprimir na tela...

        // print ("$semana, $dia DE $mes DE $ano");

        $data = $dia . ' de ' . $mes . ' de ' . $ano;

        return $data;
    }

    // retorna o intervalo em dias
    public function Intervalo_data($data_inicio, $data_termino)
    {

        // data inicial
        $data_arr = explode("-", $data_inicio);

        $datac = $data_arr[2] . '-' . $data_arr[1] . '-' . $data_arr[0];

        $d = $data_arr[2];

        $m = $data_arr[1];

        $A = $data_arr[0];

        $data_arr2 = explode("-", $data_termino);

        $d2 = $data_arr2[2];

        $m2 = $data_arr2[1];

        $A2 = $data_arr2[0];

        $diaI = $d;

        $mesI = $m;

        $anoI = $A;

        $diaF = $d2;

        $mesF = $m2;

        $anoF = $A2;

        $dataI = $A . "/" . $m . "/" . $d;

        $dataF = $A2 . "/" . $m2 . "/" . $d2;

        $secI = strtotime($dataI);

        $secF = strtotime($dataF);
        $intervalo = $secF - $secI;

        $dias = $intervalo / 3600 / 24;
        return $dias;
    }

    public function anuenio($dias)
    {
        $quantidadeAnos = $dias / 365;

        return $quantidadeAnos;
    }

    public function trienio($dias)
    {
        $quantidadeAnos = $dias / (365 * 3);

        return $quantidadeAnos;
    }

    public function msgbox($msn)
    {
        echo '<script>alert("' . $msn . '");</script>';
    }

    public function redirecionamentopage($caminho)
    {
        echo '<script>window.location="' . $caminho . '";</script>';
    }

    public function MsgboxSimNaoNovoCad($msn, $caminhopagesim)
    {

        // $d='default.php?pg=view/home/home.php';
        echo '<script>



						



						if (confirm("' . $msn . '")){



							window.location="' . $caminhopagesim . '";



						}else{



							window.location="' . $caminhopagesim . '";



						}



						</script>';
    }

    public function seguranca($idusuario, $caminhopage)
    {
        if ($idusuario == '') {

            echo '<script>window.location="' . $caminhopage . '";</script>';
        }
    }

    public function dias_mes($mes, $ano)
    {
        if (fmod($ano, 4) == 0) {

            $dias[2] = 28;
        } else {

            $dias[2] = 29;
        }

        $dias[1] = 31;

        $dias[3] = 31;

        $dias[4] = 30;

        $dias[5] = 31;

        $dias[6] = 30;

        $dias[7] = 31;

        $dias[8] = 31;

        $dias[9] = 30;

        $dias[10] = 31;

        $dias[11] = 30;

        $dias[12] = 31;

        return $dias[$mes];
    }

    public function mes_extenso($nmes)
    {
        $meses = [
            '1' => 'Janeiro',
            '2' => 'Fevereiro',
            '3' => 'Março',
            '4' => 'Abril',
            '5' => 'Maio',
            '6' => 'Junho',
            '7' => 'Julho',
            '8' => 'Agosto',
            '9' => 'Setembro',
            '10' => 'Outubro',
            '11' => 'Novembro',
            '12' => 'Dezembro'
        ];

        return $meses[$nmes];
    }

    public function codigoRadomico()
    {
        $aux = time();

        $codigo = date('Ymd') . "_" . date('his') . "_" . substr(md5($aux), 0, 7);

        return $codigo;
    }

    /*public function anti_injection($sql)
    {

        // remove palavras que contenham sintaxe sql
        $sql = preg_replace(sql_regcase("/(from|select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/"), "", $sql);

        $sql = trim($sql); // limpa espaços vazio

        $sql = strip_tags($sql); // tira tags html e php

        $sql = addslashes($sql); // Adiciona barras invertidas a uma string

        return $sql;
    }*/ //Utilizar prepared statments

    /* ---------------------------------------------Métodos de mensagens em css---------------------- */
    public function msgSucess($msg)
    {
        $alert = "<div class = \"alert alert-success\">

        <button class = \"close\" data-dismiss = \"alert\">&times;

        </button>

        <strong>Sucesso!</strong>$msg</div>";

        return $alert;
    }

    public function diaSemanaExtenso($data)
    {
        $ano = substr("$data", 0, 4);

        $mes = substr("$data", 5, -3);

        $dia = substr("$data", 8, 9);

        $diasemana = date("w", mktime(0, 0, 0, $mes, $dia, $ano));

        switch ($diasemana) {

            case "0":
                $diasemana = "Domingo";

                break;

            case "1":
                $diasemana = "Segunda-Feira";

                break;

            case "2":
                $diasemana = "Terça-Feira";

                break;

            case "3":
                $diasemana = "Quarta-Feira";

                break;

            case "4":
                $diasemana = "Quinta-Feira";

                break;

            case "5":
                $diasemana = "Sexta-Feira";

                break;

            case "6":
                $diasemana = "Sábado";

                break;
        }

        return "$diasemana";
    }

    public function diaSemanaAbreviado($data)
    {
        $ano = $this->obterAno($data);

        $mes = $this->obterMes($data);

        $dia = $this->obterDia($data);

        $diasemana = date("w", mktime(0, 0, 0, $mes, $dia, $ano));

        switch ($diasemana) {

            case "0":
                $diasemana = "DOM";

                break;

            case "1":
                $diasemana = "SEG";

                break;

            case "2":
                $diasemana = "TER";

                break;

            case "3":
                $diasemana = "QUA";

                break;

            case "4":
                $diasemana = "QUI";

                break;

            case "5":
                $diasemana = "SEX";

                break;

            case "6":
                $diasemana = "SAB";

                break;
        }

        return $diasemana;
    }

    public function diaSemanaAbreviadoIndice($indice)
    {
        switch ($indice) {

            case "0":
                $indice = "DOM";

                break;

            case "1":
                $indice = "SEG";

                break;

            case "2":
                $indice = "TER";

                break;

            case "3":
                $indice = "QUA";

                break;

            case "4":
                $indice = "QUI";

                break;

            case "5":
                $indice = "SEX";

                break;

            case "6":
                $indice = "SAB";

                break;
        }

        return $indice;
    }

    public function ultimoDia($ano, $mes)
    {
        if (((fmod($ano, 4) == 0) && (fmod($ano, 100) != 0)) || (fmod($ano, 400) == 0)) {

            $dias_fevereiro = 29;
        } else {

            $dias_fevereiro = 28;
        }

        switch ($mes) {

            case 1:
                return 31;

                break;

            case 2:
                return $dias_fevereiro;

                break;

            case 3:
                return 31;

                break;

            case 4:
                return 30;

                break;

            case 5:
                return 31;

                break;

            case 6:
                return 30;

                break;

            case 7:
                return 31;

                break;

            case 8:
                return 31;

                break;

            case 9:
                return 30;

                break;

            case 10:
                return 31;

                break;

            case 11:
                return 30;

                break;

            case 12:
                return 31;

                break;
        }
    }

    public function obterMes($data)
    {
        $array = explode('-', $data);

        $ano = $array[0];

        $mes = $array[1];

        $dia = $array[2];

        return $mes;
    }

    public function obterAno($data)
    {
        $array = explode('-', $data);

        $ano = $array[0];

        $mes = $array[1];

        $dia = $array[2];

        return $ano;
    }

    public function obterDia($data)
    {
        $array = explode('-', $data);

        $ano = $array[0];

        $mes = $array[1];

        $dia = $array[2];

        return $dia;
    }

    public function obterDiasFeriado($ano = null)
    {
        if ($ano === null) {

            $ano = intval(date('Y'));
        }

        $pascoa = easter_date($ano); // Limite de 1970 ou ap�s 2037 da easter_date PHP consulta http://www.php.net/manual/pt_BR/function.easter-date.php

        $dia_pascoa = date('j', $pascoa);

        $mes_pascoa = date('n', $pascoa);

        $ano_pascoa = date('Y', $pascoa);

        $feriados = array(

            // Tatas Fixas dos feriados Nacionail Basileiras

            mktime(0, 0, 0, 1, 1, $ano), // Confraterniza��o Universal - Lei n� 662, de 06/04/49

            mktime(0, 0, 0, 4, 21, $ano), // Tiradentes - Lei n� 662, de 06/04/49

            mktime(0, 0, 0, 5, 1, $ano), // Dia do Trabalhador - Lei n� 662, de 06/04/49

            mktime(0, 0, 0, 9, 7, $ano), // Dia da Independ�ncia - Lei n� 662, de 06/04/49

            mktime(0, 0, 0, 10, 12, $ano), // N. S. Aparecida - Lei n� 6802, de 30/06/80

            mktime(0, 0, 0, 11, 2, $ano), // Todos os santos - Lei n� 662, de 06/04/49

            mktime(0, 0, 0, 11, 15, $ano), // Proclama��o da republica - Lei n� 662, de 06/04/49

            mktime(0, 0, 0, 12, 25, $ano), // Natal - Lei n� 662, de 06/04/49

            // These days have a date depending on easter

            mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 48, $ano_pascoa), // 2�feria Carnaval

            mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 47, $ano_pascoa), // 3�feria Carnaval

            mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 2, $ano_pascoa), // 6�feira Santa

            mktime(0, 0, 0, $mes_pascoa, $dia_pascoa, $ano_pascoa), // Pascoa

            mktime(0, 0, 0, $mes_pascoa, $dia_pascoa + 60, $ano_pascoa) // Corpus Cirist

        );

        sort($feriados);

        return $feriados;
    }

    public function verificarDataFeriado($data)
    {
        $ano = $this->obterAno($data);

        foreach ($this->obterDiasFeriado($ano) as $a) {

            if ($data == date("Y-m-d", $a)) {

                $feriado = 1;
            }
        }

        return $feriado;
    }

    /**
     * Recebe uma string como parâmetro e realiza os procedimentos necessários para validar o cpf informado
     * @return boolean
     */
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

    /**
     * Valida e formata números de telefone brasileiros.
     * 
     * Aceita números com ou sem máscara e retorna no formato:
     * (XX)91234-5678 para celular ou (XX)1234-5678 para fixo.
     * 
     * @param string $telefone
     * @return string|false Telefone formatado ou false se inválido.
     */
    public static function validarTelefone(string $telefone)
    {
        // Remove tudo que não for número
        $numeros = preg_replace('/\D/', '', $telefone);

        // Validação básica
        if (strlen($numeros) < 10 || strlen($numeros) > 11) {
            return false;
        }

        // DDD (dois primeiros dígitos)
        $ddd = substr($numeros, 0, 2);
        // Resto do número (após o DDD)
        $resto = substr($numeros, 2);

        // Verifica se é celular (11 dígitos e começa com 9)
        if (strlen($numeros) === 11 && $resto[0] === '9') {
            $parte1 = substr($resto, 0, 5);
            $parte2 = substr($resto, 5);
            return sprintf('(%s)%s-%s', $ddd, $parte1, $parte2);
        }

        // Verifica se é fixo (10 dígitos)
        if (strlen($numeros) === 10) {
            $parte1 = substr($resto, 0, 4);
            $parte2 = substr($resto, 4);
            return sprintf('(%s)%s-%s', $ddd, $parte1, $parte2);
        }

        // Se não passou em nenhum formato válido
        return false;
    }
}
