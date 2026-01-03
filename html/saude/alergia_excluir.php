<?php
if(session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../index.php");
    exit();
}else{
    session_regenerate_id();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 53, 7);

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'EnfermidadeSaude.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

extract($_GET);
$id_fichamedica = isset($_GET['id_fichamedica']) ? $_GET['id_fichamedica'] : null;

$enfermidade = new EnfermidadeSaude($id_doc);

try {
    if($enfermidade->delete($id_fichamedica) === false)
        throw new LogicException('Erro na operação de remoção de uma enfermidade de um paciente.', 500);

    $sql = "SELECT sf.id_CID, sf.data_diagnostico, sf.status, stc.descricao FROM saude_enfermidades sf JOIN saude_tabelacid stc ON sf.id_CID = stc.id_CID WHERE stc.CID LIKE 'T78.4%' AND sf.status = 1 AND id_fichamedica=:idFichaMedica";

    $pdo = Conexao::connect();
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idFichaMedica', $id_fichamedica, PDO::PARAM_INT);
    $stmt->execute();

    $alergias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $alergias = json_encode($alergias);
    echo $alergias;
} catch (Exception $e) {
    Util::tratarException($e);
}
