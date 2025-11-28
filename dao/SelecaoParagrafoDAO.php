<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Conexao.php';

class SelecaoParagrafoDAO{
    private PDO $pdo;

    public function __construct(?PDO $pdo=null){
        isset($pdo) ? $this->pdo = $pdo : $this->pdo = Conexao::connect();
    }

    /**
     * Retorna o texto de agradecimento ao doador salvo no banco de dados da aplicação.
     */
    public function getAgradecimentoDoador():?string{
        $sql = 'SELECT paragrafo FROM selecao_paragrafo WHERE id_selecao=7';
        $query = $this->pdo->query($sql);
        
        if($query->rowCount() != 1)
            return null;

        $agradecimento = $query->fetch(PDO::FETCH_ASSOC)['paragrafo'];
        return $agradecimento;
    }
}