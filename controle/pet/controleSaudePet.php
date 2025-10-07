<?php
$config_path = "config.php";
while (!file_exists($config_path)) {
    $config_path = "../" . $config_path;
}
require_once($config_path);

$SaudePetDAO_path = "dao/pet/SaudePetDAO.php";
while (!file_exists($SaudePetDAO_path)) {
    $SaudePetDAO_path = "../" . $SaudePetDAO_path;
}
require_once($SaudePetDAO_path);

$SaudePet_path = "classes/pet/SaudePet.php";
while (!file_exists($SaudePet_path)) {
    $SaudePet_path = "../" . $SaudePet_path;
}
require_once($SaudePet_path);

include_once ROOT."/dao/Conexao.php";
include_once "../../classes/util.php";

class controleSaudePet
{
    public function verificar()
    {
        extract($_REQUEST);

        if (empty($nome)) {
            header("Location: ../../html/pet/cadastro_ficha_medica_pet.php?msg=Nome não informado!");
            return;
        }
        if (empty($castrado)) {
            header("Location: ../../html/pet/cadastro_ficha_medica_pet.php?msg=Estado da castração não informado!");
            return;
        }

        $saudePet = new SaudePet($nome, $texto, $castrado);

        if (empty($vacinado)) {
            header("Location: ../../html/pet/cadastro_ficha_medica_pet.php?msg=Estado da vacinação não informado!");
            return;
        } elseif ($vacinado === 's' && empty($dVacinado)) {
            header("Location: ../../html/pet/cadastro_ficha_medica_pet.php?msg=Data da vacinação não informada!");
            return;
        } elseif ($vacinado === 's') {
            $saudePet->setDataVacinado($dVacinado);
        }

        if (empty($vermifugado)) {
            header("Location: ../../html/pet/cadastro_ficha_medica_pet.php?msg=Estado da vermifugação não informado!");
            return;
        } elseif ($vermifugado === 's' && empty($dVermifugado)) {
            header("Location: ../../html/pet/cadastro_ficha_medica_pet.php?msg=Data da vermifugação não informada!");
            return;
        } elseif ($vermifugado === 's') {
            $saudePet->setDataVermifugado($dVermifugado);
        }

        $saudePet->setNome($nome);
        $saudePet->setTexto($texto);
        $saudePet->setCastrado($castrado);
        $saudePet->setVacinado($vacinado);
        $saudePet->setVermifugado($vermifugado);

        return $saudePet;
    }

    // ------------------------ Vacinação ------------------------
    public function cadastroVacinacao()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $idVacina = $input['idVacina'] ?? null;
            $idFichaMedica = $input['idFichaMedica'] ?? null;
            $dataVacinacao = $input['dataVacinacao'] ?? null;

            if (!$idVacina || !$idFichaMedica || !$dataVacinacao) {
                throw new Exception('Alguma informação não foi enviada', 400);
            }

            $saudePetDao = new SaudePetDao();
            $resultado = $saudePetDao->cadastroVacinacao($idVacina, $idFichaMedica, $dataVacinacao);

            if ($resultado > 0) {
                echo json_encode(['status' => 'sucesso', 'mensagem' => 'Vacinação registrada com sucesso']);
            } else {
                throw new Exception('Erro ao registrar vacinação no banco', 500);
            }
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function cadastroVacina()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $nome = $input['nomeVacina'] ?? null;
            $marca = $input['marcaVacina'] ?? null;

            if (!$nome || !$marca) {
                throw new Exception('Alguma informação não foi enviada', 400);
            }

            $saudePetDao = new SaudePetDao();
            if ($saudePetDao->cadastroVacina($nome, $marca) > 0) {
                echo json_encode(['status' => 'sucesso', 'mensagem' => 'Informação inserida com sucesso']);
            } else {
                throw new Exception('Erro ao inserir no banco', 500);
            }
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarVacina()
    {
        try {
            header("Content-Type: application/json; charset=utf-8");
            $saudePetDao = new SaudePetDAO();
            $registros = $saudePetDao->listarVacina();
            echo json_encode($registros, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    // ------------------------ Vermifugação ------------------------
    public function cadastroVermifugacao()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $idVermifugo = $input['idVermifugo'] ?? null;
            $idFichaMedica = $input['idFichaMedica'] ?? null;
            $dataVermifugacao = $input['dataVermifugacao'] ?? null;

            if (!$idVermifugo || !$idFichaMedica || !$dataVermifugacao) {
                throw new Exception('Alguma informação não foi enviada', 400);
            }

            $saudePetDao = new SaudePetDao();
            $resultado = $saudePetDao->cadastroVermifugacao($idVermifugo, $idFichaMedica, $dataVermifugacao);

            if ($resultado > 0) {
                echo json_encode(['status' => 'sucesso', 'mensagem' => 'Vermifugação registrada com sucesso']);
            } else {
                throw new Exception('Erro ao registrar vermifugação no banco', 500);
            }
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function cadastroVermifugo()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $nome = $input['nomeVermifugo'] ?? null;
            $marca = $input['marcaVermifugo'] ?? null;

            if (!$nome || !$marca) {
                throw new Exception('Alguma informação não foi enviada', 400);
            }

            $saudePetDao = new SaudePetDao();
            if ($saudePetDao->cadastroVermifugo($nome, $marca) > 0) {
                echo json_encode(['status' => 'sucesso', 'mensagem' => 'Informação inserida com sucesso']);
            } else {
                throw new Exception('Erro ao inserir no banco', 500);
            }
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarVermifugo()
    {
        try {
            header("Content-Type: application/json; charset=utf-8");
            $saudePetDao = new SaudePetDAO();
            $registros = $saudePetDao->listarVermifugo();
            echo json_encode($registros, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
    // ------------------------ Fichas médicas ------------------------
    public function listarTodos()
    {
        try {
            extract($_REQUEST);
            $SaudePetDAO = new SaudePetDAO();
            $pets = $SaudePetDAO->listarTodos();

            session_start();
            $_SESSION['saudepet'] = $pets;
            header('Location: ' . $nextPage);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function incluir()
    {
        try {
            $pet = $this->verificar();
            $intDAO = new SaudePetDAO();
            $idSaudePet = $intDAO->incluir($pet);

            session_start();
            $_SESSION['msg'] = "Ficha médica cadastrada com sucesso!";
            $_SESSION['proxima'] = "Cadastrar outra ficha.";
            $_SESSION['link'] = "../html/saude/cadastro_ficha_medica_pet.php";
        } catch (PDOException $e) {
            Util::tratarException($e);
        }
    }

    public function getFichaMedicaPet()
    {
        try {
            $idPet = $_REQUEST['idPet'] ?? null;
            if (!$idPet) throw new Exception("ID do pet não informado", 400);

            $saudePetDAO = new SaudePetDAO();
            $dados = $saudePetDAO->getFichaMedicaPet($idPet);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($dados ?: []);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function modificarFichaMedicaPet()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $id_pet = $input['id_pet'] ?? null;
            $descricao = $input['necessidadesEspeciais'] ?? null;
            $castrado = $input['castrado'] ?? null;

            if (!$id_pet) throw new Exception("ID do pet não informado", 400);

            $saudePetDAO = new SaudePetDAO();
            $ok = $saudePetDAO->modificarFichaMedicaPet($id_pet, $descricao, $castrado);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                "status"   => $ok ? "sucesso" : "erro",
                "redirect" => "../../html/pet/profile_pet.php?id_pet=" . $id_pet
            ]);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    // ------------------------ Histórico ------------------------
    public function getHistoricoPet()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $idpet = $data['idpet'] ?? null;

            if (!$idpet) throw new Exception("ID do pet não informado", 400);

            $saudePetDAO = new SaudePetDAO();
            $resultado = $saudePetDAO->getHistoricoPet($idpet);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($resultado);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function getAtendimentoPet($id)
    {
        try {
            $saudePetDAO = new SaudePetDAO();
            return $saudePetDAO->getAtendimentoPet($id);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function dataAplicacao($dados)
    {
        try {
            $saudePetDAO = new SaudePetDAO();
            return $saudePetDAO->dataAplicacao($dados);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function getHistoricoVacinacao()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $idpet = $input['idpet'] ?? null;

            if (!$idpet) throw new Exception("ID do pet não informado", 400);

            $saudePetDAO = new SaudePetDAO();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($saudePetDAO->getHistoricoVacinacao($idpet), JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function getHistoricoVermifugacao()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $idpet = $input['idpet'] ?? null;

            if (!$idpet) throw new Exception("ID do pet não informado", 400);

            $saudePetDAO = new SaudePetDAO();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($saudePetDAO->getHistoricoVermifugacao($idpet), JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    // ------------------------ Tipos de exame ------------------------
    public function cadastroTipoExame()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $descricao = $input['descricaoExame'] ?? null;

            if (!$descricao) throw new Exception("Descrição do exame não foi enviada", 400);

            $saudePetDAO = new SaudePetDAO();
            $id = $saudePetDAO->adicionarTipoExame($descricao);
            $lista = $saudePetDAO->listarTipoExame();

            echo json_encode([
                'status' => 'sucesso',
                'id'     => $id,
                'dados'  => $lista
            ]);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarTipoExame()
    {
        try {
            $saudePetDao = new SaudePetDAO();
            $registros = $saudePetDao->listarTipoExame();

            header("Content-Type: application/json; charset=utf-8");
            echo json_encode($registros, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
