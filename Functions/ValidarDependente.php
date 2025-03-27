<?php
function ValidarCPFDependente ($array, $infCpf) {
    return array_find($array, function ($valor) {
        if($valor == $infCpf) {
            return true;
        }
    });
}
?>