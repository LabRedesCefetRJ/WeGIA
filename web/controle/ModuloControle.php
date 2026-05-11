<?php
include_once '../classes/Modulo.php';
include_once '../dao/ModuloDAO.php';
class ModuloControle
{
    /**
     * Extrai o recurso do fomulário e altera a visibilidade deles, módulos com a checkbox marcada permaneceram na aplicação, enquanto os outros serão ocultados.
     */
    public function alterar_modulos_visiveis(){

        $recurso = $_REQUEST['recurso'];
        $nextPage = filter_var(trim($_REQUEST['nextPage']), FILTER_VALIDATE_URL);

        if(!$recurso || empty($recurso)){
            exit('Erro ao mudar a visibilidade do módulo, um recurso não pode ser vazio.');
        }
        if(!$nextPage){
            exit('Erro ao mudar a visibilidade do módulo, a URL da próxima página não é válida.');
        }

        try {
            $modulo = new ModuloDAO();
            $modulo->alterar_modulos_visiveis($recurso);
            header('Location:'.$nextPage.'?msg_c=Visualização atualizada com sucesso.');
        } catch (PDOException $e) {
            echo 'Erro ao mudar a visibilidade do módulo: '. $e->getMessage();
        }
    }
}