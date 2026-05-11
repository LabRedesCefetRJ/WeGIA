<?php
include_once(ROOT . '/classes/DescricaoFichamedica.php');
include_once(ROOT . '/dao/DescricaoDAO.php');

class DescricaoControle
{
    private $tamanho = 1000;

    /**
     * Recebe um texto como parâmetro e realiza os processos necessários para incluí-lo como a descrição de um prontuário público de um paciente.
     */
    public function incluir(string $texto)
    {
        $idPessoa = trim($_POST['nome']);

        if (!$idPessoa || !is_numeric($idPessoa)) {
            http_response_code(400);
            exit('Erro, o id da pessoa selecionada não pode ser nulo ou diferente de um número.');
        }

        $idDescricao = new DescricaoDAO();
        $textoArray = $this->divideStringEmArrays($texto, $this->tamanho);
        $idDescricao->incluir($textoArray, $idPessoa);
    }

    /**
     * Recebe como parâmetros uma string e um valor inteiro referente a quantidade máxima de caracteres de uma string, retorna um array de strings
     */
    function divideStringEmArrays(string $string, int $tamanho)
    {
        $arrayResultante = [];
        $tamanhoString = strlen($string);

        // Verifica se a string é maior que o tamanho definido
        if ($tamanhoString <= $tamanho) {
            $arrayResultante[] = $string;
        } else {
            $numArrays = ceil($tamanhoString / $tamanho);

            for ($i = 0; $i < $numArrays; $i++) {
                $inicio = $i * $tamanho;
                $arrayResultante[] = substr($string, $inicio, $tamanho);
            }
        }

        return $arrayResultante;
    }

    /**
     * Recebe dois parâmetros, o id da ficha médica que será alterada e o novo texto, instancia um objeto do tipo DescricaoDAO e chama o método alterar, repassando o id informado e um array da string do texto informado.
     */
    public function alterarProntuario(int $idFicha, string $texto)
    {
        $descricaoDAO = new DescricaoDAO();
        $textoArray = $this->divideStringEmArrays($texto, $this->tamanho);
        $descricaoDAO->alterar($idFicha, $textoArray);
    }
}
