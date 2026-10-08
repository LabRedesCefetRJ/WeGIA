<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

$idAlmoxarifado = filter_input(INPUT_GET, 'id_almoxarifado', FILTER_VALIDATE_INT);
$destino = WWW . 'controle/control.php?nomeClasse=ProdutoControle'
    . '&metodo=listarDisponiveisRelatorioPorAlmoxarifado'
    . '&id_almoxarifado=' . urlencode((string) $idAlmoxarifado);

header('Location: ' . $destino, true, 307);
exit;
