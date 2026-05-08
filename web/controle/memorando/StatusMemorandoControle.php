<?php

require_once '../../classes/memorando/StatusMemorando.php';
require_once '../../dao/memorando/StatusMemorandoDAO.php';

 class StatusMemorandoControle{

    /**
     * Retorna um objeto do tipo StatusMemorando que é equivalente ao dado armazenado no banco de dados que possuí o id passado como 
     * parâmetro, caso não exista um objeto equivalente retorna null.
     */
    public function getPorId(int $id){
        try{
            if($id < 1){
                throw new InvalidArgumentException('O id de um memorando não pode ser menor que 1.');
            }

            $statusMemorandoDAO = new StatusMemorandoDAO();
            $resultado = $statusMemorandoDAO->getPorId($id);

            if(!$resultado){
                return null;
            }

            $statusMemorando = new StatusMemorando($resultado['status_atual'], $resultado['id_status_memorando']);
            return $statusMemorando;
        }catch(Exception $e){
            echo 'Erro ao buscar o status do memorando: '.$e->getMessage();
        }
    }
 }