<?php
$config_path = realpath("../config.php");
if($config_path){
    require_once($config_path);
}

if (file_exists("classes/Personalizacao_display.php")){
     require_once "classes/Personalizacao_display.php";
}elseif (file_exists("../classes/Personalizacao_display.php")) {
     require_once "../classes/Personalizacao_display.php";
}elseif (file_exists("../../classes/Personalizacao_display.php")){
     require_once "../../classes/Personalizacao_display.php";
}
/*

    Esta função recebe como parametro:
        o nome do campo (Título, Subtítulo, Rodapé, etc);
        o modo como setá exibido:

            txt: será exibido como um pedaço de texto, com o nome do campo na tag <h1> e o conteúdo em uma tag <p>
            str: exibe puramente o conteúdo, sem nome do campo e sem nenhuma tag

            file: exibe apenas o nome do arquivo de imagem, que está contido em ./img/database_images/

    */
function display_campo($campo, $tipo){
    $display = new Display_campo($campo, $tipo);
    $display->display();
}

function display_carrossel($campo){
    $car = new Display_campo($campo,"car");
    $files = $car->getCar();
    if ($files){
        foreach ($files as $key => $val){
            echo('<div class="item ' . ($key == 0 ? 'active' : '') . '"><img src="data:image/'.$val["tipo"].';base64,' . gzuncompress($val["arquivo"]) . '" ></div>');
        }
    }else{
        echo("Não há imagens no Banco de dados para este Campo!");
    }
}

?>