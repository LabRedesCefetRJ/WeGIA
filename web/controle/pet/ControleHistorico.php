<?php
require_once "./controleSaudePet.php";

$dados = json_decode(file_get_contents("php://input"));
foreach($dados as $valor){
    $a[] = $valor;
}

$metodo = $a[0];

$c = new controleSaudePet();
echo json_encode($c->$metodo($a[1]));