<?php
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'ContatoInstituicao.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class ContactController
{

    public function getSupportContact()
    {
        try {
            //return JSON for contact
            $supportContact = ContatoInstituicao::listarPorId(1, new ContatoInstituicaoMySQL(Conexao::connect()));

            echo json_encode($supportContact);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
