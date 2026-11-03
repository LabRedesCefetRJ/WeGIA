<?php
if (session_status() == PHP_SESSION_NONE)
    session_start();

include_once ROOT . '/classes/Origem.php';
include_once ROOT . '/dao/OrigemDAO.php';
require_once ROOT . '/classes/Util.php';
require_once ROOT . '/service/PermissaoFornecedorService.php';
require_once ROOT . '/classes/OrigemNavegacao.php';
require_once ROOT . '/classes/Csrf.php';

class OrigemControle
{
    private $campoErro = null;
    private function exigirPermissao(int $acao): void
    {
        $servico = new PermissaoFornecedorService();
        if (!$servico->permite((int) ($_SESSION['id_pessoa'] ?? 0), $acao)) {
            header('Location: ' . WWW . 'html/home.php?msg_c=' . urlencode(
                'Você não tem permissão para realizar esta ação em origens.'
            ));
            exit;
        }
    }

    /**
     * Valida e sanitiza os dados de entrada antes de criar o objeto Origem.
     */
    public function verificar()
    {
        $this->exigirPermissao(3);
        $entrada = [];
        foreach (['nome', 'telefone', 'cpf', 'cnpj'] as $campo) {
            $this->campoErro = $campo;
            $valor = $_POST[$campo] ?? '';
            if (!is_string($valor)) {
                throw new InvalidArgumentException('Valor inválido para ' . $campo . '.');
            }
            $entrada[$campo] = trim($valor);
        }
        $nome = $entrada['nome'];
        $telefone = $entrada['telefone'];
        $cpf = $entrada['cpf'];
        $cnpj = $entrada['cnpj'];

        $this->campoErro = 'nome';
        if ($nome === '') {
            throw new InvalidArgumentException('Nome da origem não informado. Por favor, informe um nome!');
        }
        if (mb_strlen($nome, 'UTF-8') > 100) {
            throw new InvalidArgumentException('O nome da origem deve ter no máximo 100 caracteres.');
        }

        $this->campoErro = 'telefone';
        if ($telefone !== '') {
            // Aceita telefone brasileiro com DDD, com ou sem máscara.
            // Rejeita letras em vez de removê-las silenciosamente.
            if (!preg_match('/^[0-9() .-]+$/D', $telefone)) {
                throw new InvalidArgumentException('Informe um telefone válido com DDD (10 ou 11 números).');
            }
            $numeros = preg_replace('/[^0-9]/', '', $telefone);
            if (!in_array(strlen($numeros), [10, 11], true)) {
                throw new InvalidArgumentException('Informe um telefone válido com DDD (10 ou 11 números).');
            }
            $tamanhoPrefixo = strlen($numeros) === 11 ? 5 : 4;
            $telefone = '(' . substr($numeros, 0, 2) . ') '
                . substr($numeros, 2, $tamanhoPrefixo) . '-' . substr($numeros, -4);
        }

        $this->campoErro = 'cpf';
        if ($cpf !== '' && !Util::validarCPF($cpf)) {
            throw new InvalidArgumentException('CPF inválido!');
        }

        $this->campoErro = 'cnpj';
        if ($cnpj !== '' && !Util::validaCnpj($cnpj)) {
            throw new InvalidArgumentException('CNPJ inválido!');
        }

        $cpf = $cpf !== '' ? $cpf : null;
        $cnpj = $cnpj !== '' ? $cnpj : null;
        $telefone = $telefone !== '' ? $telefone : null;

        $dados = [];
        $rotulos = ['inscricao_estadual' => 'Inscrição estadual', 'email' => 'E-mail', 'cep' => 'CEP', 'logradouro' => 'Logradouro', 'numero' => 'Número', 'complemento' => 'Complemento', 'bairro' => 'Bairro', 'cidade' => 'Cidade', 'uf' => 'UF'];
        $limites = ['inscricao_estadual' => 30, 'email' => 254, 'cep' => 9, 'logradouro' => 255, 'numero' => 20, 'complemento' => 100, 'bairro' => 100, 'cidade' => 100, 'uf' => 2];
        foreach ($limites as $campo => $limite) {
            $this->campoErro = $campo;
            $valor = $_POST[$campo] ?? '';
            if (!is_string($valor)) {
                throw new InvalidArgumentException('Valor inválido para ' . $campo . '.');
            }
            $valor = trim($valor);
            if (mb_strlen($valor, 'UTF-8') > $limite) {
                throw new InvalidArgumentException($rotulos[$campo] . ': informe no máximo ' . $limite . ' caracteres.');
            }
            $dados[$campo] = $valor === '' ? null : $valor;
        }
        $this->campoErro = 'email';
        if ($dados['email'] !== null && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Informe um e-mail válido, como nome@exemplo.com.');
        }
        $this->campoErro = 'cep';
        if ($dados['cep'] !== null) {
            $cep = str_replace('-', '', $dados['cep']);
            if (!preg_match('/^[0-9]{8}$/D', $cep)) {
                throw new InvalidArgumentException('Informe um CEP com 8 números.');
            }
            $dados['cep'] = substr($cep, 0, 5) . '-' . substr($cep, 5);
        }
        $this->campoErro = 'uf';
        if ($dados['uf'] !== null) {
            $dados['uf'] = strtoupper($dados['uf']);
            if (!in_array($dados['uf'], explode(' ', 'AC AL AP AM BA CE DF ES GO MA MT MS MG PA PB PR PE PI RJ RN RS RO RR SC SP SE TO'), true)) {
                throw new InvalidArgumentException('Informe uma UF brasileira válida, como RJ ou SP.');
            }
        }

        $dados = array_merge($dados, Origem::validarCondicoesPagamento($_POST));
        $this->campoErro = null;
        return new Origem($nome, $cnpj, $cpf, $telefone, $dados);
    }

    private function lerAlmoxarifados(): array
    {
        $this->campoErro = 'almoxarifados';
        $valores = $_POST['almoxarifados'] ?? [];
        if (!is_array($valores)) {
            throw new InvalidArgumentException('A seleção de almoxarifados deve ser uma lista.');
        }

        $ids = [];
        foreach ($valores as $valor) {
            if ((!is_string($valor) && !is_int($valor))
                || !preg_match('/^[1-9][0-9]*$/D', (string) $valor)
                || strlen((string) $valor) > 10
                || (int) $valor > 2147483647) {
                throw new InvalidArgumentException('Seleção de almoxarifados inválida. Selecione novamente.');
            }
            $ids[] = (int) $valor;
        }

        return array_values(array_unique($ids));
    }

    private function responderJson(array $dados, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function validarCsrf(): void
    {
        if (!Csrf::validateToken($_POST['csrf_token'] ?? null)) {
            throw new InvalidArgumentException(
                'Token CSRF inválido ou ausente.',
                403
            );
        }
    }

    public function listarTodos()
    {
        $this->exigirPermissao(5);
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/(matPat)/(listar_origem)\.php)$#';

        try {
            $origemDAO = new OrigemDAO();
            $origens = $origemDAO->listarTodos();

            $_SESSION['origem'] = $origens;

            preg_match($regex, $nextPage) ? header('Location: ' . htmlspecialchars($nextPage, ENT_QUOTES, 'UTF-8')) : header('Location: ' . WWW . 'html/home.php');
            exit;
        } catch (PDOException $e) {
            error_log("Erro ao listar origens: " . $e->getMessage());
            echo "Erro ao listar origens. Tente novamente mais tarde.";
        }
    }

    public function listarId_Nome()
    {
        $this->exigirPermissao(3);

        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/(matPat)/(cadastro_entrada)\.php)$#';

        try {
            $origemDAO = new OrigemDAO();
            $origens = $origemDAO->listarId_Nome();

            $_SESSION['origem'] = $origens;

            preg_match($regex, $nextPage) ? header('Location: ' . htmlspecialchars($nextPage, ENT_QUOTES, 'UTF-8')) : header('Location: ' . WWW . 'html/home.php');
            exit;
        } catch (PDOException $e) {
            error_log("Erro ao listar ID/Nome: " . $e->getMessage());
            echo "Erro ao listar origens. Tente novamente mais tarde.";
        }
    }

    public function incluir()
    {
        $this->exigirPermissao(3);

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            throw new InvalidArgumentException(
                'O cadastro exige uma requisição POST.',
                405
            );
        }

        $this->validarCsrf();

        try {
            $origem = $this->verificar();

            $almoxarifados = $this->lerAlmoxarifados();

            $origemDAO = new OrigemDAO();

            $this->campoErro = 'almoxarifados';
            $origemDAO->incluir($origem, $almoxarifados);

            $_SESSION['msg'] = 'Origem cadastrada com sucesso.';
            $_SESSION['flag'] = 'success';
            unset($_SESSION['origem'], $_SESSION['proxima'], $_SESSION['link'], $_SESSION['origem_formulario']);

            header('Location: ' . OrigemNavegacao::cadastro($_POST['origem_pagina'] ?? null));
            exit;
        } catch (PDOException $e) {
            error_log("Erro ao incluir origem: " . $e->getMessage());
            echo "Erro ao realizar o cadastro. Tente novamente mais tarde.";
        } catch (InvalidArgumentException $e) {
            // Guarda apenas os campos do formulário para restaurá-los após o redirecionamento.
            $campos = explode(' ', 'nome telefone cpf cnpj inscricao_estadual email cep logradouro numero complemento bairro cidade uf almoxarifados condicao_pagamento vencimentos_pagamento');
            $_SESSION['origem_campo_erro'] = $e instanceof OrigemValidacaoException ? $e->getCampo() : $this->campoErro;
            $_SESSION['origem_formulario'] = array_intersect_key($_POST, array_flip($campos));
            $_SESSION['msg'] = $e->getMessage();
            $_SESSION['flag'] = 'error';

            header('Location: ' . OrigemNavegacao::cadastro($_POST['origem_pagina'] ?? null));
            exit;

        } catch (Exception $e) {
            error_log("Erro geral: " . $e->getMessage() . 'Line ' . $e->getLine() . 'File ' . $e->getFile());
            echo "Erro inesperado. Contate o administrador do sistema.";
        }
    }

    public function excluir()
    {
        $this->exigirPermissao(3);

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            throw new InvalidArgumentException(
                'A exclusão exige uma requisição POST.',
                405
            );
        }

        $this->validarCsrf();

        $id_origem = isset($_POST['id_origem']) ? (int) $_POST['id_origem'] : 0;

        if ($id_origem <= 0) {
            throw new InvalidArgumentException(
                'ID de origem inválido.',
                400
            );
        }

        try {
            $origemDAO = new OrigemDAO();
            $origemDAO->excluir($id_origem);
        } catch (DomainException $e) {
            $_SESSION['msg'] = $e->getMessage();
            $_SESSION['flag'] = 'error';
        } catch (PDOException $e) {
            error_log("Erro ao excluir origem: " . $e->getMessage());
            $_SESSION['msg'] = 'Não foi possível excluir esta origem. Verifique se existem outros registros vinculados a ela e tente novamente.';
            $_SESSION['flag'] = 'error';
        }

        unset($_SESSION['origem']);
        header('Location: ' . WWW . 'html/matPat/listar_origem.php');
        exit;
    }

    public function listarPorAlmoxarifado()
    {
        $this->exigirPermissao(3);
        $id_almoxarifado = isset($_GET['id_almoxarifado'])
            ? (int) $_GET['id_almoxarifado']
            : 0;

        header('Content-Type: application/json; charset=utf-8');

        if ($id_almoxarifado <= 0) {
            echo json_encode(array());
            exit;
        }

        try {
            $origemDAO = new OrigemDAO();
            echo $origemDAO->listarPorAlmoxarifado($id_almoxarifado);
        } catch (PDOException $e) {
            error_log('Erro ao listar origens por almoxarifado: ' . $e->getMessage());
            $this->responderJson(['mensagem' => 'Não foi possível carregar as origens. Tente novamente mais tarde.'], 500);
        }
        exit;
    }

    public function alterar()
    {
        $this->exigirPermissao(3);

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            throw new InvalidArgumentException(
                'A alteração exige uma requisição POST.',
                405
            );
        }

        $this->validarCsrf();

        try {
            $id_origem = isset($_POST['id_origem']) ? (int) $_POST['id_origem'] : 0;
            $almoxarifados = $this->lerAlmoxarifados();

            if ($id_origem <= 0) {
                throw new Exception("Origem inválida.");
            }

            $origem = $this->verificar();
            $origem->setId_origem($id_origem);

            $origemDAO = new OrigemDAO();
            $this->campoErro = 'almoxarifados';
            $origemDAO->alterar($origem, $almoxarifados);

            if (($_POST['resposta'] ?? '') === 'json') {
                $this->responderJson(['destino' => WWW . 'html/matPat/listar_origem.php']);
            }

            $_SESSION['msg'] = "Origem alterada com sucesso.";
            header('Location: ' . WWW . 'html/matPat/listar_origem.php');
            exit;
        } catch (PDOException $e) {
            error_log("Erro ao alterar origem: " . $e->getMessage());
            if (($_POST['resposta'] ?? '') === 'json') {
                $this->responderJson(['mensagem' => 'Erro ao alterar origem. Tente novamente mais tarde.'], 500);
            }
            echo "Erro ao alterar origem. Tente novamente mais tarde.";
        } catch (Exception $e) {
            error_log("Erro ao alterar origem: " . $e->getMessage());
            if (($_POST['resposta'] ?? '') === 'json') {
                $this->responderJson([
                    'mensagem' => $e->getMessage(),
                    'campo' => $e instanceof OrigemValidacaoException ? $e->getCampo()
                        : ($e instanceof InvalidArgumentException ? $this->campoErro : null)
                ], 400);
            }
            echo "Erro ao alterar origem.";
        }
    }
}
