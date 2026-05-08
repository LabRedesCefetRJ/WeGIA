 <?php
    session_start();
    if (!isset($_SESSION['usuario'])) die("Você não está logado(a).");

    require_once '../../permissao/permissao.php';
    permissao($_SESSION['id_pessoa'], 4, 3);
    
    require("../conexao.php");
    if (!isset($_POST) or empty($_POST)) {
        $data = file_get_contents("php://input");
        $data = json_decode($data, true);
        $_POST = $data;
    } else if (is_string($_POST)) {
        $_POST = json_decode($_POST, true);
    }
    $conexao->set_charset("utf8");
    extract($_REQUEST);

    $sql = "SELECT * FROM cobrancas c JOIN socio s ON c.id_socio = s.id_socio JOIN pessoa p ON s.id_pessoa = p.id_pessoa WHERE codigo = ?";

    $stmt = mysqli_prepare($conexao, $sql);

    mysqli_stmt_bind_param($stmt, 's', $codigo);

    mysqli_stmt_execute($stmt);

    // Obter o resultado do statement
    $resultado = mysqli_stmt_get_result($stmt);

    while ($detalhes = mysqli_fetch_assoc($resultado)) {
        $dados[] = $detalhes;
    }

    // Fechar o primeiro statement
    mysqli_stmt_close($stmt);

    // Fechar a conexão
    mysqli_close($conexao);

    echo json_encode($dados);
    ?>