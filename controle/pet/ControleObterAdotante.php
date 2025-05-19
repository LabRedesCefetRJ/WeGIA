<?php
require_once 'AdocaoControle.php';

$post = json_decode(file_get_contents("php://input"));

foreach( $post as $value){
    $id = $value;
}

$id = $a->obterAdotante($id);

echo json_encode($id);