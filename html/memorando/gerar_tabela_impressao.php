<?php

// Esse arquivo contém a função para gerar e retornar uma tabela com os dados dos memorandos que serão impressos

function gerarTabelaMemorando($despachos, $anexos){
    $tabela = "";
    foreach($despachos as $despacho) {
        $id = $despacho->id;
        $remetente = $despacho->remetente;
        $destinatario = $despacho->destinatario;
        $dataSeparada = explode('-', $despacho->data);
        $diaHora = explode(' ', $dataSeparada[2]);
        $data = "{$diaHora[0]}/{$dataSeparada[1]}/{$dataSeparada[0]} às {$diaHora[1]}";
        $texto = $despacho->texto;
        $tabela .= "<table class='table table-bordered table-striped mb-none'>";
        
        $tabela .= "<tr>
                        <th>Remetente:</th>
                         <td>$remetente</td>
                         <th>Destinatário:</th>
                         <td>$destinatario</td>
                    </tr>
                    <tr>
                        <th colspan='2'>Despacho:</th>
                        <th>Data:</th>
                        <td>$data</td>
                    </tr>
                    <tr>
                        <td colspan='4' id='texto$id'>
                            <p>$texto</p>
                        </td>
                    </tr>
                    <tr>
                        <th colspan='4'>Anexos:</th>
                    </tr>";
        $trAnexos = "<tr><td colspan='5'>";
        foreach($anexos as $anexo) {
            if($anexo->id_despacho == $id){
                $nome = $anexo->nome;
                $extensao = $anexo->extensao;
                $trAnexos .= "<p>$nome$extensao</p>";
            }
        }
        $trAnexos .= "</td></tr>";

        $tabela .= $trAnexos;

        $tabela .= '</table>';
    }
    return $tabela;
}