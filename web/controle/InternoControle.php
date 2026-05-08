<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(0);
ini_set('display_errors', 0);

require_once '../classes/Interno.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once '../dao/InternoDAO.php';
require_once '../classes/Documento.php';
require_once '../dao/DocumentoDAO.php';
require_once 'DocumentoControle.php';
include_once '../classes/Cache.php';

require_once ROOT . "/controle/InternoControle.php";
require_once ROOT . "/controle/FuncionarioControle.php";
$listaInternos = new InternoControle();
$listaInternos->listarTodos2();

class InternoControle
{
    //OpenRedirect nos métodos
    public function formatoDataYMD($data)
    {
        $data_arr = explode("/", $data);

        $datac = $data_arr[2] . '-' . $data_arr[1] . '-' . $data_arr[0];

        return $datac;
    }
    public function verificar()
    {
        extract($_REQUEST);

        if ((!isset($nome)) || (empty($nome))) {
            $msg = "Nome do interno não informado. Por favor, informe um nome!";
            header('Location: ../html/atendido/Cadastro_Atendido.php?msg=' . $msg);
        }
        if ((!isset($sobrenome)) || (empty($sobrenome))) {
            $msg = "Sobrenome do interno não informado. Por favor, informe um sobrenome!";
            header('Location: ../html/atendido/Cadastro_Atendido.php?msg=' . $msg);
        }
        if ((!isset($sexo)) || (empty($sexo))) {
            $msg .= "Sexo do interno não informado. Por favor, informe um sexo!";
            header('Location: ../html/atendido/Cadastro_Atendido.php?msg=' . $msg);
        }
        if ((!isset($nascimento)) || (empty($nascimento))) {
            $msg .= "Data de nascimento do interno não informado. Por favor, informe uma data de nascimento!";
            header('Location: ../html/atendido/Cadastro_Atendido.php?msg=' . $msg);
        }
        if (isset($naoPossuiCpf)) {
            $internos = $_SESSION['internos2'];
            $j = 0;
            for ($i = 0; $i < count($internos); $i++) {
                if ($nome == $internos[$i]['nome']) {
                    $j++;
                }
            }
            if ($j == 0) {
                $numeroCPF = $nome . "ni";
            } else {
                $numeroCPF = $nome . $j . "ni";
            }
        } elseif ((!isset($numeroCPF)) || (empty($numeroCPF))) {
            $msg .= "CPF do interno não informado. Por favor, informe um CPF!";
            header('Location: ../html/atendido/Cadastro_atendido.php?msg=' . $msg);
        }
        $telefone = '';
        $senha = 'null';
        $numeroCPF = str_replace(".", '', $numeroCPF);
        $numeroCPF = str_replace("-", "", $numeroCPF);
        $interno = new Interno($numeroCPF, $nome, $sobrenome, $sexo, $nascimento, '', '', '', '', '', '', '', $telefone, '', '', '', '', '', '', '', '', '');

        return $interno;
    }

    public function listarTodos($redirect = true)
    {
        try {
            $internoDAO = new InternoDAO();
            $internos = $internoDAO->listarTodos();

            $_SESSION['internos'] = $internos;
            if ($redirect) {
                header('Location: ' . '../html/informacao_interno.php');
            }
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarTodos2()
    {
        try {
            $internoDAO = new InternoDAO();
            $internos = $internoDAO->listarTodos2();

            $_SESSION['internos2'] = $internos;
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarUm()
    {
        extract($_REQUEST);
        $cache = new Cache();
        $infInterno = $cache->read($id);
        if (!$infInterno) {
            try {
                $internoDAO = new InternoDAO();
                $infInterno = $internoDAO->listar($id);

                $_SESSION['interno'] = $infInterno;
                $cache->save($id, $infInterno, '15 seconds');
                header('Location:' . '../html/informacao_interno.php');
            } catch (Exception $e) {
                Util::tratarException($e);
            }
        } else {
            header('Location:' . '../html/informacao_interno.php');
        }
    }

    public function listarCpf()
    {
        try {
            $internosDAO = new InternoDAO();
            $internoscpf = $internosDAO->listarCPF();
            $_SESSION['cpf_interno'] = $internoscpf;
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function comprimir($documParaCompressao)
    {
        $documento_zip = gzcompress($documParaCompressao);
        return $documento_zip;
    }

    public function incluir()
    {
        try {
            $interno = $this->verificar();
            $intDAO = new AtendidoDAO();
            $docDAO = new DocumentoDAO();
            $idPessoa = $intDAO->incluir($interno, $interno->getCpf());
            $_SESSION['msg'] = "Interno cadastrado com sucesso";
            $_SESSION['proxima'] = "Cadastrar outro interno";
            $_SESSION['link'] = "../html/atendido/Cadastro_Atendido.php";
            header("Location: ../html/sucesso.php");
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
    public function alterar()
    {
        try {
            extract($_REQUEST);
            $interno = $this->verificar();
            $interno->setIdInterno($idInterno);
            $AtendidoDAO = new AtendidoDAO();

            $AtendidoDAO->alterar($interno);
            header("Location: ../html/Profile_Atendido.php?id=" . $idInterno);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function excluir()
    {
        try {
            extract($_REQUEST);
            $AtendidoDAO = new AtendidoDAO();

            $AtendidoDAO->excluir($id);
            $this->listarTodos(false);
            header("Location: ../html/Informacao_Atendido.php");
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
