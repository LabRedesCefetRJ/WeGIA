<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
//arquivos necessários
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SaudePet.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'SaudePetDAO.php';

class controleSaudePet
{
    public function verificar()
    {
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
        $castrado = filter_input(INPUT_POST, 'castrado', FILTER_SANITIZE_SPECIAL_CHARS);
        $vacinado = filter_input(INPUT_POST,  'vacinado', FILTER_SANITIZE_SPECIAL_CHARS);
        $dVacinado = filter_input(INPUT_POST,  'dVacinado', FILTER_SANITIZE_SPECIAL_CHARS);
        $texto = filter_input(INPUT_POST, 'texto', FILTER_SANITIZE_SPECIAL_CHARS);
        $vermifugado = filter_input(INPUT_POST, 'vermifugado', FILTER_SANITIZE_SPECIAL_CHARS);
        $dVermifugado = filter_input(INPUT_POST, 'dVermifugado', FILTER_SANITIZE_SPECIAL_CHARS);

        if ((!isset($nome)) || (empty($nome))) {
            $msg = "Nome não informado!";
            header("Location: ../../html/pet/cadastro_ficha_medica_pet.php?msg=" . $msg);
            return;
        }

        if ((!isset($castrado)) || (empty($castrado))) {
            $msg = "Estado da castração não informado!";
            header("Location: ../../html/pet/cadastro_ficha_medica_pet.php?msg=" . $msg);
            return;
        }

        $saudePet = new SaudePet($nome, $texto, $castrado);

        if ((!isset($vacinado)) || (empty($vacinado))) {
            $msg = "Estado da vacinação não informado!";
            header("Location: ../../html/pet/cadastro_ficha_medica_pet.php?msg=" . $msg);
            return;
        } else if ($vacinado == 's' && (!isset($dVacinado) || empty($dVacinado))) {
            $msg = "Data da vacinação não informado!";
            header("Location: ../../html/pet/cadastro_ficha_medica_pet.php?msg=" . $msg);
            return;
        } else if ($vacinado == 's') {
            $saudePet->setDataVacinado($dVacinado);
        }

        if ((!isset($vermifugado)) || (empty($vermifugado))) {
            $msg = "Estado da vermifugação não informado!";
            header("Location: ../../html/pet/cadastro_ficha_medica_pet.php?msg=" . $msg);
            return;
        } else if ($vermifugado == 's' && (!isset($dVermifugado) || empty($dVermifugado))) {
            $msg = "Data da vermifugação não informado!";
            header("Location: ../../html/pet/cadastro_ficha_medica_pet.php?msg=" . $msg);
            return;
        } else if ($vermifugado == 's') {
            $saudePet->setDataVermifugado($dVermifugado);
        }

        $saudePet->setNome($nome);
        $saudePet->setTexto($texto);
        $saudePet->setCastrado($castrado);
        $saudePet->setVacinado($vacinado);
        $saudePet->setVermifugado($vermifugado);

        return $saudePet;
    }


    public function listarTodos()
    {
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^(\.\./html/pet/(informacao_saude_pet)\.php)$#';

        try{
        $SaudePetDAO = new SaudePetDAO();
        $pets = $SaudePetDAO->listarTodos();

        $_SESSION['saudepet'] = json_encode($pets);

        preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . WWW . 'html/home.php');
        }catch(Exception $e){

        }
    }

    public function incluir()
    {
        try {
            $pet = $this->verificar();
            $intDAO = new SaudePetDAO();
            //$idSaudePet = $intDAO->incluir($pet); <-- Criar a função incluir em SaudePetDAO
            $_SESSION['msg'] = "Ficha médica cadastrada com sucesso!";
            $_SESSION['proxima'] = "Cadastrar outra ficha.";
            $_SESSION['link'] = "../html/saude/cadastro_ficha_medica_pet.php";
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function getPet($id)
    {
        $saudePetDAO = new SaudePetDAO();
        return $saudePetDAO->getPet($id);
    }

    public function getFichaMedicaPet()
    {
        $idPet = filter_var($_REQUEST['idPet'], FILTER_SANITIZE_NUMBER_INT);
        $saudePetDAO = new SaudePetDAO();

        header('Content-Type: application/json; charset=utf-8');

        $dados = $saudePetDAO->getFichaMedicaPet($idPet);

        if (!$dados) {
            // sempre retorna um objeto JSON válido
            $dados = [
                "id_ficha_medica" => null,
                "necessidades_especiais" => "",
                "castrado" => ""
            ];
        }

        echo json_encode($dados);
    }

    public function fichaMedicaPetExiste(int $idPet)
    {
        try {
            if (!$idPet || $idPet < 1)
                throw new InvalidArgumentException('O id da ficha médica do pet fornecido não é válido.', 422);

            $saudePetDAO = new SaudePetDAO();
            return $saudePetDAO->fichaMedicaPetExiste($idPet);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function modificarFichaMedicaPet()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $id_pet = filter_var($input['id_pet'], FILTER_SANITIZE_NUMBER_INT) ?? null;
        $descricao = filter_var($input['necessidadesEspeciais'], FILTER_SANITIZE_SPECIAL_CHARS) ?? null;
        $castrado = filter_var($input['castrado'], FILTER_SANITIZE_SPECIAL_CHARS) ?? null;

        try {
            $saudePetDAO = new SaudePetDAO();
            $ok = $saudePetDAO->modificarFichaMedicaPet($id_pet, $descricao, $castrado);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                "status"   => $ok ? "sucesso" : "erro",
                "redirect" => "../../html/pet/profile_pet.php?id_pet=" . htmlspecialchars($id_pet)
            ]);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function getHistoricoPet()
    {
        $json = file_get_contents('php://input');
        $idPet = filter_var(json_decode($json, true)['idpet'], FILTER_SANITIZE_NUMBER_INT);

        try {
            if (!$idPet || $idPet < 1)
                throw new InvalidArgumentException('O id do pet fornecido não é válido.', 422);

            $saudePetDAO = new SaudePetDAO();
            $resultado = $saudePetDAO->getHistoricoPet($idPet);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($resultado);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function getAtendimentoPet(int $idPet)
    {
        try {
            if (!$idPet || $idPet < 1)
                throw new InvalidArgumentException('O id da ficha médica do pet fornecido não é válido.', 422);

            $saudePetDAO = new SaudePetDAO();
            return $saudePetDAO->getAtendimentoPet($idPet);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function dataAplicacao($dados)
    {
        try {
            //data|id <-- Procurar uma forma mais eficaz de transportar os dados pela aplicação, transformar essa string em array ou objeto
            $saudePetDAO = new SaudePetDAO();
            return $saudePetDAO->dataAplicacao($dados);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
