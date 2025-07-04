<?php

require_once '../classes/Cargo.php';
require_once '../dao/CargoDAO.php';

class CargoControle
{
    /**
     * Inseri no sistema um novo cargo com as descrições informadas pelo post
     */
    public function incluir()
    {

        // Determina se os dados foram enviados via JSON
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            // Recebe o JSON da requisição
            $json = file_get_contents('php://input');
            // Decodifica o JSON
            $data = json_decode($json, true);

            $cargoDescricao = trim(filter_var($data['cargo'], FILTER_SANITIZE_STRING));
        } else {
            // Recebe os dados do formulário normalmente
            $cargoDescricao = trim(filter_input(INPUT_POST, 'cargo', FILTER_SANITIZE_STRING));
        }

        try {
            $cargo = new Cargo((string)($cargoDescricao));
        } catch (InvalidArgumentException $e) {
            echo 'Erro ao adicionar cargo: ' . $e->getMessage();
            return;
        }

        if ($cargo) {
            $cargoDAO = new CargoDAO();
            $cargoDAO->incluir($cargo);
        }
    }

    /**
     * Retorna um JSON dos cargos registrados no BD da aplicação
     */
    public function listarTodos()
    {
        $cargosArray = [];

        $cargoDAO = new CargoDAO();
        $cargos = $cargoDAO->listarTodos();

        foreach ($cargos as $cargo) {
            $cargosArray[] = ['id_cargo' => $cargo->getId_cargo(), 'cargo' => $cargo->getCargo()];
        }

        $cargosJSON = json_encode($cargosArray);
        echo $cargosJSON;
    }

    /**
     * Retorna um JSON dos recursos do cargo com id equivalente ao passado pela requisição get
     */
    public function listarRecursos()
    {
        $cargo = trim(filter_input(INPUT_GET, 'cargo', FILTER_SANITIZE_NUMBER_INT));

        try {
            if (!$cargo || $cargo < 1) {
                throw new InvalidArgumentException('O id de um cargo deve ser um inteiro positivo maior ou igual a 1.', 400);
            }

            $cargoDao = new CargoDAO();

            $recursos = $cargoDao->listarRecursos($cargo);

            echo json_encode($recursos);
        } catch (Exception $e) {
            error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
            http_response_code($e->getCode());
            echo json_encode(['erro' => $e->getMessage()]);
        }
    }
}
