<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_SESSION["usuario"])) {
  header("Location: ../../index.php");
  exit();
} else {
  session_regenerate_id();
}

//Verifica permissão do usuário
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once  dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';
$pdo = Conexao::connect();
$idDependente = filter_input(INPUT_POST, 'id_dependente', FILTER_SANITIZE_NUMBER_INT);

try {
  if (!$idDependente || $idDependente < 1) {
    throw new InvalidArgumentException('O id do dependente informado não é válido.', 400);
  }

  $sql = "SELECT doc.nome_docdependente AS descricao, ddoc.data, ddoc.id_doc FROM funcionario_dependentes_docs ddoc LEFT JOIN funcionario_docdependentes doc ON doc.id_docdependentes = ddoc.id_docdependentes WHERE ddoc.id_dependente=:idDependente";

  $stmtDependente = $pdo->prepare($sql);
  $stmtDependente->bindParam(':idDependente', $idDependente);
  $stmtDependente->execute();

  $dependente = $stmtDependente->fetchAll(PDO::FETCH_ASSOC);

  foreach ($dependente as $key => $value) {
    //Formatar data
    $data = new DateTime($value['data']);
    $dependente[$key]['data'] = $data->format('d/m/Y');
  }

  $dependente = json_encode($dependente);

  echo $dependente;
} catch (Exception $e) {
  Util::tratarException($e);
}
