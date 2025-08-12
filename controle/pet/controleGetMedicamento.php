<?php

require_once '../../classes/session.php';
require_once './MedicamentoControle.php';
header( "Content-Type: application/json;charset=UTF-8" );

$c = new MedicamentoControle();

$p = $c->listarMedicamento();

http_response_code(200);
die(json_encode($p));