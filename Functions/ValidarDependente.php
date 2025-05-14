<?php
function ValidarCPFRelacionados ($array, $infCpf) {
    foreach ($array as $cpf) {
        if($cpf == $infCpf) {
            return true;
        }
    }
    return false;
}
?>