<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Conexao.php';

enum SelecaoParagrafo{
    case Agradecimento;
    case Cnpj;
}

class SelecaoParagrafoDAO
{
    private static PDO $pdo;

    // Inicializa a conexão estática
    public function __construct(?PDO $pdo = null)
    {
        if (empty(self::$pdo)) {
            self::$pdo = $pdo ?? Conexao::connect();
        }
    }

    /**
     * Retorna o texto de seleção salvo no banco de dados da aplicação.
     */
    public static function getSelecao(SelecaoParagrafo $selecaoParagrafo): ?string
    {
        // garante que a conexão está inicializada
        if (empty(self::$pdo)) {
            self::$pdo = Conexao::connect();
        }

        if(!isset($selecaoParagrafo))
            throw new InvalidArgumentException('A seleção informada não é válida.', 412);

        $idSelecao = null;

        match($selecaoParagrafo){
            SelecaoParagrafo::Agradecimento => $idSelecao = 7,
            SelecaoParagrafo::Cnpj => $idSelecao = 8,
        };

        if(!isset($idSelecao))
            throw new InvalidArgumentException('O id da seleção informado não é válido.', 412);

        $sql = 'SELECT paragrafo FROM selecao_paragrafo WHERE id_selecao=:idSelecao';

        $stmt = self::$pdo->prepare($sql);
        $stmt->bindValue(':idSelecao', $idSelecao, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() != 1)
            return null;

        $selecao = $stmt->fetch(PDO::FETCH_ASSOC)['paragrafo'];
        return $selecao;
    }
}
