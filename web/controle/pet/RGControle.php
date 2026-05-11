<?php
require_once 'AdocaoControle.php';

$post = file_get_contents( 'php://input');

$dado = json_decode($post);

foreach( $dado as $value){
    $rgAdotante = $value;
}

$dado = $a->nomeAdotante($rgAdotante);

echo json_encode($dado);
//echo $dado;