<?php
// O registro de entrada passou a ser feito em etapas numa única página (issue #2157); este endereço antigo leva para lá.
$cpf = preg_replace('/\D/', '', is_string($_GET['cpf'] ?? null) ? $_GET['cpf'] : '');
header('Location: registro_entrada.php' . (strlen($cpf) === 11 ? '?cpf=' . $cpf : ''), true, 302);
exit();
