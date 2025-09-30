<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
include_once ROOT . "/dao/Conexao.php";
include_once ROOT . '/classes/Funcionario.php';
include_once ROOT . '/classes/QuadroHorario.php';
include_once ROOT . '/dao/FuncionarioDAO.php';
include_once ROOT . '/dao/QuadroHorarioDAO.php';
include_once ROOT . '/dao/PermissaoDAO.php';
require_once ROOT . '/classes/Util.php';

class FuncionarioControle
{

    public function formatoDataYMD($data)
    {
        $data_arr = explode("/", $data);

        $datac = $data_arr[2] . '-' . $data_arr[1] . '-' . $data_arr[0];

        return $datac;
    }

    function somarHoras($subtotal1, $subtotal2)
    {
        $hora1 = explode(":", $subtotal1);
        $hora2 = explode(":", $subtotal2);


        if (sizeof($hora1) > 1 && sizeof($hora2) > 1) {
            $tempoTotal = (intval($hora1[0]) * 60) + (intval($hora2[0]) * 60) + intval($hora1[1]) + intval($hora2[1]);

            $horaTotal = floor($tempoTotal / 60);
            $minutoTotal = $tempoTotal % 60;

            if (strlen($minutoTotal) == 1) {
                $minutoTotal = "0" . $minutoTotal;
            }

            if (strlen($horaTotal) == 1) {
                $horaTotal = "0" . $horaTotal;
            }

            $final = $horaTotal . ":" . $minutoTotal;



            return $final;
        }
        return '';
    }

    function calcularHora($entrada, $saida)
    {
        $hora1 = explode(":", $entrada);
        $hora2 = explode(":", $saida);
        if (sizeof($hora1) > 1 && sizeof($hora2) > 1) {
            $horaTotal = ((intval($hora2[0]) * 60) + intval($hora2[1])) - ((intval($hora1[0]) * 60) + intval($hora1[1]));

            $horaTotall = floor($horaTotal / 60);
            $minutoTotal = $horaTotal % 60;

            if (strlen($minutoTotal) == 1) {
                $minutoTotal = "0" . $minutoTotal;
            }

            if (strlen($horaTotall) == 1) {
                $horaTotal = "0" . $horaTotal;
            }

            $final = $horaTotall . ":" . $minutoTotal;
            return $final;
        }

        return '';
    }

    function geraChave()
    {

        return '';
    }

    public function verificarHorario()
    {
        extract($_REQUEST);
        if ((!isset($escala)) || (empty($escala))) {
            $escala = null;
        }
        if ((!isset($tipoCargaHoraria)) || (empty($tipoCargaHoraria))) {
            $tipoCargaHoraria = null;
        }
        if ((!isset($entrada1)) || (empty($entrada1))) {
            $entrada1 = '';
        }
        if ((!isset($saida1)) || (empty($saida1))) {
            $saida1 = '';
        }
        if ((!isset($entrada2)) || (empty($entrada2))) {
            $entrada2 = '';
        }
        if ((!isset($saida2)) || (empty($saida2))) {
            $saida2 = '';
        }

        $subtotal1 = $this->calcularHora($entrada1, $saida1);
        $subtotal2 = $this->calcularHora($entrada2, $saida2);
        $total = $this->somarHoras($subtotal1, $subtotal2);

        $diasTrabalhados = array();
        $folgas = array();


        if (isset($folgaSeg)) {
            array_push($folgas, $folgaSeg);
        }
        if (isset($folgaTer)) {
            array_push($folgas, $folgaTer);
        }

        if (isset($folgaQua)) {
            array_push($folgas, $folgaQua);
        }
        if (isset($folgaQui)) {
            array_push($folgas, $folgaQui);
        }
        if (isset($folgaSex)) {
            array_push($folgas, $folgaSex);
        }
        if (isset($folgaSab)) {
            array_push($folgas, $folgaSab);
        }
        if (isset($folgaDom)) {
            array_push($folgas, $folgaDom);
        }
        if (isset($folgaAlternado)) {
            array_push($folgas, $folgaAlternado);
        }

        $folga = implode(",", $folgas);

        if (isset($trabSeg)) {
            array_push($diasTrabalhados, $trabSeg);
        }
        if (isset($trabTer)) {
            array_push($diasTrabalhados, $trabTer);
        }

        if (isset($trabQua)) {
            array_push($diasTrabalhados, $trabQua);
        }
        if (isset($trabQui)) {
            array_push($diasTrabalhados, $trabQui);
        }
        if (isset($trabSex)) {
            array_push($diasTrabalhados, $trabSex);
        }
        if (isset($trabSab)) {
            array_push($diasTrabalhados, $trabSab);
        }
        if (isset($trabDom)) {
            array_push($diasTrabalhados, $trabDom);
        }

        $diasMultiplicados = count($diasTrabalhados);

        if ($total) {
            $arrayHorasDiarias = explode(":", $total);
            $minutosDiarios = intval($arrayHorasDiarias[0]) * 60 + intval($arrayHorasDiarias[1]);
            $minutosDiarios = $minutosDiarios * $diasMultiplicados;
            $minutosDiarios = $minutosDiarios * 4;

            $horaTotal = floor($minutosDiarios / 60);
            $minutoTotal = $minutosDiarios % 60;

            if (strlen($minutoTotal) == 1) {
                $minutoTotal = "0" . $minutoTotal;
            }

            if (strlen($horaTotal) == 1) {
                $horaTotal = "0" . $horaTotal;
            }

            $carga_horaria = $horaTotal . ":" . $minutoTotal;


            if (isset($plantao)) {
                $dias_trabalhados = $plantao;
                $carga_horaria = 174;
            }
        } else {
            $dias_trabalhados = null;
            $carga_horaria = null;
        }

        $dias_trabalhados = implode(",", $diasTrabalhados);


        $horario = new QuadroHorario();

        $horario->setEscala($escala);
        $horario->setTipo($tipoCargaHoraria);
        $horario->setCarga_horaria($carga_horaria);
        $horario->setEntrada1($entrada1);
        $horario->setSaida1($saida1);
        $horario->setEntrada2($entrada2);
        $horario->setSaida2($saida2);
        $horario->setTotal($total);
        $horario->setDias_trabalhados($dias_trabalhados);
        $horario->setFolga($folga);

        return $horario;
    }

    public function verificarFuncionario()
    {
        extract($_REQUEST);

        if ((!isset($nome)) || (empty($nome))) {
            $msg = "Nome do funcionario não informado. Por favor, informe um nome!";
            header('Location: ../html/funcionario.html?msg=' . $msg);
        }
        if ((!isset($sobrenome)) || (empty($sobrenome))) {
            $msg = "Sobrenome do funcionario não informado. Por favor, informe um sobrenome!";
            header('Location: ../html/funcionario.html?msg=' . $msg);
        }
        if ((!isset($gender)) || (empty($gender))) {
            $msg .= "Sexo do funcionario não informado. Por favor, informe um sexo!";
            header('Location: ../html/funcionario.html?msg=' . $msg);
        }
        if ((!isset($cargo)) || (empty($cargo))) {
            $msg .= "Cargo do funcionario não informado. Por favor, informe um cargo!";
            header('Location: ../html/funcionario.html?msg=' . $msg);
        }
        if ((!isset($telefone)) || (empty($telefone))) {
            $telefone = 'null';
        }
        if ((!isset($nascimento)) || (empty($nascimento))) {
            $msg .= "Data de nascimento do funcionario não informado. Por favor, informe uma data de nascimento!";
            header('Location: ../html/funcionario.html?msg=' . $msg);
        }
        if ((!isset($nome_pai)) || (empty($nome_pai))) {
            $nome_pai = '';
        }
        if ((!isset($nome_mae)) || (empty($nome_mae))) {
            $nome_mae = '';
        }
        if ((!isset($sangue)) || (empty($sangue))) {
            $sangue = '';
        }
        if ((!isset($cep)) || empty(($cep))) {
            $cep = '';
        }
        if ((!isset($uf)) || empty(($uf))) {
            $uf = '';
        }
        if ((!isset($cidade)) || empty(($cidade))) {
            $cidade = '';
        }
        if ((!isset($bairro)) || empty(($bairro))) {
            $bairro = '';
        }
        if ((!isset($rua)) || empty(($rua))) {
            $rua = '';
        }
        if ((!isset($numero_residencia)) || empty(($numero_residencia))) {
            $numero_residencia = "";
        }
        if ((!isset($complemento)) || (empty($complemento))) {
            $complemento = '';
        }
        if ((!isset($ibge)) || (empty($ibge))) {
            $ibge = '';
        }
        if ((!isset($rg)) || empty(($rg))) {
            $msg .= "RG do funcionario não informado. Por favor, informe um rg!";
            header('Location: ../html/funcionario.html?msg=' . $msg);
        }
        if ((!isset($orgao_emissor)) || empty(($orgao_emissor))) {
            $msg .= "Órgão emissor do funcionario não informado. Por favor, informe o órgão emissor!";
            header('Location: ../html/funcionario.html?msg=' . $msg);
        }
        if ((!isset($data_expedicao)) || (empty($data_expedicao))) {
            $msg .= "Data de expedição do rg do funcionario não informado. Por favor, informe um data de expedição!";
            header('Location: ../html/funcionario.html?msg=' . $msg);
        }
        if ((!isset($cpf)) || (empty($cpf))) {
            $msg .= "CPF do funcionario não informado. Por favor, informe um CPF!";
            header('Location: ../html/funcionario.html?msg=' . $msg);
        }
        if ((!isset($pis)) || (empty($pis))) {
            $pis = '';
        }
        if ((!isset($ctps)) || (empty($ctps))) {
            $ctps = '';
        }
        if ((!isset($uf_ctps)) || (empty($uf_ctps))) {
            $uf_ctps = '';
        }
        if ((!isset($titulo_eleitor)) || (empty($titulo_eleitor))) {
            $titulo_eleitor = '';
        }
        if ((!isset($zona_eleitoral)) || (empty($zona_eleitoral))) {
            $zona_eleitoral = '';
        }
        if ((!isset($secao_titulo_eleitor)) || (empty($secao_titulo_eleitor))) {
            $secao_titulo_eleitor = '';
        }

        if ((!isset($data_admissao)) || (empty($data_admissao))) {
            $msg .= "Data de Admissão do funcionario não informada. Por favor, informe a data de admissao!";
            header('Location: ../html/funcionario.html?msg=' . $msg);
        }
        if ((!isset($situacao)) || (empty($situacao))) {
            $msg .= "Situação do funcionario não informada. Por favor, informe a situação!";
            header('Location: ../html/funcionario.html?msg=' . $msg);
        }

        if ((!isset($certificado_reservista_numero)) || (empty($certificado_reservista_numero))) {
            $certificado_reservista_numero = '';
        }
        if ((!isset($certificado_reservista_serie)) || (empty($certificado_reservista_serie))) {
            $certificado_reservista_serie = '';
        }
        session_start();
        if ((!isset($_SESSION['imagem'])) || (empty($_SESSION['imagem']))) {
            $imgperfil = '';
        } else {
            $imgperfil = base64_encode($_SESSION['imagem']);
            unset($_SESSION['imagem']);
        }

        $senha = $this->geraChave($cpf);
        $funcionario = new Funcionario($cpf, $nome, $sobrenome, $gender, $nascimento, $rg, $orgao_emissor, $data_expedicao, $nome_mae, $nome_pai, $sangue, $senha, $telefone, $imgperfil, $cep, $uf, $cidade, $bairro, $rua, $numero_residencia, $complemento, $ibge);
        $funcionario->setData_admissao($data_admissao);
        $funcionario->setPis($pis);
        $funcionario->setCtps($ctps);
        $funcionario->setUf_ctps($uf_ctps);
        $funcionario->setNumero_titulo($titulo_eleitor);
        $funcionario->setZona($zona_eleitoral);
        $funcionario->setSecao($secao_titulo_eleitor);
        $funcionario->setCertificado_reservista_numero($certificado_reservista_numero);
        $funcionario->setCertificado_reservista_serie($certificado_reservista_serie);
        $funcionario->setId_situacao($situacao);
        $funcionario->setId_cargo($cargo);

        return $funcionario;
    }

    public function verificarExistente()
    {
        extract($_REQUEST);
        if ((!isset($nome)) || (empty($nome))) {
            $nome = '';
        }
        if ((!isset($sobrenome)) || (empty($sobrenome))) {
            $sobrenome = '';
        }
        if ((!isset($gender)) || (empty($gender))) {
            $gender = '';
        }
        if ((!isset($cargo)) || (empty($cargo))) {
            $msg .= "Cargo do funcionario não informado. Por favor, informe um cargo!";
            header('Location: ../html/funcionario.html?msg=' . $msg);
        }
        if ((!isset($telefone)) || (empty($telefone))) {
            $telefone = 'null';
        }
        if ((!isset($nascimento)) || (empty($nascimento))) {
            $nascimento = '';
        }
        if ((!isset($nome_pai)) || (empty($nome_pai))) {
            $nome_pai = '';
        }
        if ((!isset($nome_mae)) || (empty($nome_mae))) {
            $nome_mae = '';
        }
        if ((!isset($sangue)) || (empty($sangue))) {
            $sangue = '';
        }
        if ((!isset($cep)) || empty(($cep))) {
            $cep = '';
        }
        if ((!isset($uf)) || empty(($uf))) {
            $uf = '';
        }
        if ((!isset($cidade)) || empty(($cidade))) {
            $cidade = '';
        }
        if ((!isset($bairro)) || empty(($bairro))) {
            $bairro = '';
        }
        if ((!isset($rua)) || empty(($rua))) {
            $rua = '';
        }
        if ((!isset($numero_residencia)) || empty(($numero_residencia))) {
            $numero_residencia = "";
        }
        if ((!isset($complemento)) || (empty($complemento))) {
            $complemento = '';
        }
        if ((!isset($ibge)) || (empty($ibge))) {
            $ibge = '';
        }
        if ((!isset($rg)) || empty(($rg))) {
            $msg .= "RG do funcionario não informado. Por favor, informe um rg!";
            header('Location: ../html/funcionario.html?msg=' . $msg);
        }
        if ((!isset($orgao_emissor)) || empty(($orgao_emissor))) {
            $msg .= "Órgão emissor do funcionario não informado. Por favor, informe o órgão emissor!";
            header('Location: ../html/funcionario.html?msg=' . $msg);
        }
        if ((!isset($data_expedicao)) || (empty($data_expedicao))) {
            $msg .= "Data de expedição do rg do funcionario não informado. Por favor, informe um data de expedição!";
            header('Location: ../html/funcionario.html?msg=' . $msg);
        }
        if ((!isset($cpf)) || (empty($cpf))) {
            $cpf = '';
        }
        if ((!isset($pis)) || (empty($pis))) {
            $pis = '';
        }
        if ((!isset($ctps)) || (empty($ctps))) {
            $ctps = 'NULL';
        }
        if ((!isset($uf_ctps)) || (empty($uf_ctps))) {
            $uf_ctps = '';
        }
        if ((!isset($titulo_eleitor)) || (empty($titulo_eleitor))) {
            $titulo_eleitor = '';
        }
        if ((!isset($zona_eleitoral)) || (empty($zona_eleitoral))) {
            $zona_eleitoral = '';
        }
        if ((!isset($secao_titulo_eleitor)) || (empty($secao_titulo_eleitor))) {
            $secao_titulo_eleitor = '';
        }

        if ((!isset($data_admissao)) || (empty($data_admissao))) {
            $msg .= "Data de Admissão do funcionario não informada. Por favor, informe a data de admissão!";
            header('Location: ../html/funcionario.html?msg=' . $msg);
        }
        if ((!isset($situacao)) || (empty($situacao))) {
            $msg .= "Situação do funcionario não informada. Por favor, informe a situação!";
            header('Location: ../html/funcionario.html?msg=' . $msg);
        }

        if ((!isset($certificado_reservista_numero)) || (empty($certificado_reservista_numero))) {
            $certificado_reservista_numero = '';
        }
        if ((!isset($certificado_reservista_serie)) || (empty($certificado_reservista_serie))) {
            $certificado_reservista_serie = '';
        }
        session_start();
        if ((!isset($_SESSION['imagem'])) || (empty($_SESSION['imagem']))) {
            $imgperfil = '';
        } else {
            $imgperfil = base64_encode($_SESSION['imagem']);
            unset($_SESSION['imagem']);
        }

        $senha = $this->geraChave($cpf);
        $funcionario = $funcionario = new Funcionario($cpf, $nome, $sobrenome, $gender, $nascimento, $rg, $orgao_emissor, $data_expedicao, $nome_mae, $nome_pai, $sangue, $senha, $telefone, $imgperfil, $cep, $uf, $cidade, $bairro, $rua, $numero_residencia, $complemento, $ibge);
        $funcionario->setData_admissao($data_admissao);
        $funcionario->setPis($pis);
        $funcionario->setCtps($ctps);
        $funcionario->setUf_ctps($uf_ctps);
        $funcionario->setNumero_titulo($titulo_eleitor);
        $funcionario->setZona($zona_eleitoral);
        $funcionario->setSecao($secao_titulo_eleitor);
        $funcionario->setCertificado_reservista_numero($certificado_reservista_numero);
        $funcionario->setCertificado_reservista_serie($certificado_reservista_serie);
        $funcionario->setId_situacao($situacao);
        $funcionario->setId_cargo($cargo);



        return $funcionario;
    }

    public function retornarIdPessoa($id_funcionario)
    {
        /*Avaliar se este método está sendo utilizado em alguma parte do sistema, do contrário considerar sua remoção, tal como do método retornarIdPessoa da classe FuncionarioDAO */
        $funcionariosDAO = new FuncionarioDAO();
        $pessoa = $funcionariosDAO->retornarIdPessoa($id_funcionario);
        $_SESSION['id_pessoaa'] = $pessoa;
    }

    public function verificarSenha()
    {
        extract($_REQUEST);
        $nova_senha = hash('sha256', $nova_senha);
        $confirmar_senha = hash('sha256', $confirmar_senha);
        $senha_antiga = hash('sha256', $senha_antiga);
        if ($nova_senha != $confirmar_senha) {
            return 1;
        } else {
            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            $senha = $mysqli->query("SELECT senha FROM pessoa where id_pessoa=" . $id_pessoa);
            while ($row = $senha->fetch_array(MYSQLI_NUM)) {
                if ($row[0] != $senha_antiga) {
                    return 2;
                }
            }
        }
        return 3;
    }
    public function verificarSenhaConfig()
    {
        extract($_REQUEST);
        $nova_senha = hash('sha256', $nova_senha);
        $confirmar_senha = hash('sha256', $confirmar_senha);
        if ($nova_senha != $confirmar_senha) {
            return 1;
        } else {
            return 3;
        }
    }

    public function listarTodos()
    {
        extract($_REQUEST);
        $situacao = $_GET['select_situacao'];
        $funcionariosDAO = new FuncionarioDAO();
        $funcionarios = $funcionariosDAO->listarTodos($situacao);

        $whitePages =
            [
                '../html/funcionario/informacao_funcionario.php',
                WWW . "html/funcionario/informacao_funcionario.php",
                '../html/geral/editar_permissoes.php',
            ];

        session_start();
        $_SESSION['funcionarios'] = $funcionarios;

        if (in_array($nextPage, $whitePages)) {
            header('Location: ' . $nextPage);
        } else {
            header('Location: ' . WWW . 'html/home.php');
        }
    }

    public function listarTodos2()
    {
        extract($_REQUEST);
        $funcionariosDAO = new FuncionarioDAO();
        $funcionarios = $funcionariosDAO->listarTodos2();
        $_SESSION['funcionarios2'] = $funcionarios;
    }

    public function listarCpf()
    {
        extract($_REQUEST);
        $funcionariosDAO = new FuncionarioDAO();
        $funcionarioscpf = $funcionariosDAO->listarCPF();
        $_SESSION['cpf_funcionario'] = $funcionarioscpf;
    }

    public function listarUm()
    {
        try {
            $idFuncionario = filter_input(INPUT_GET, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);

            if (!$idFuncionario || $idFuncionario < 1) {
                throw new InvalidArgumentException('O id do funcionário informado não é válido.', 400);
            }

            $funcionarioDAO = new FuncionarioDAO();
            $funcionario = $funcionarioDAO->listar($idFuncionario);

            if (session_start() === PHP_SESSION_NONE)
                session_start();

            $_SESSION['funcionario'] = $funcionario;

            header('Location:' . WWW . "/html/funcionario/profile_funcionario.php?id_funcionario=" . htmlspecialchars($idFuncionario));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function getIdFuncionarioComIdPessoa()
    {
        $id_pessoa = $_GET["id_pessoa"];

        header('Content-Type: application/json');

        try {

            $funcionarioDAO = new FuncionarioDAO;
            $id_funcionario = $funcionarioDAO->getIdFuncionarioComIdPessoa($id_pessoa);
            echo json_encode($id_funcionario);
        } catch (Exception $e) {
            echo 'Error:' . $e->getMessage();
        }
    }

    public function adicionarPermissao()
    {
        try {
            $cargo = filter_input(INPUT_POST, 'cargo', FILTER_SANITIZE_NUMBER_INT);
            $acao = filter_input(INPUT_POST, 'acao', FILTER_SANITIZE_NUMBER_INT);
            $recursos = filter_input(INPUT_POST, 'recurso', FILTER_VALIDATE_INT, [
                'flags' => FILTER_REQUIRE_ARRAY,
                'options' => ['min_range' => 1]
            ]);

            if (!$cargo || $cargo < 1) {
                throw new InvalidArgumentException('O valor do id do cargo informado não é válido.', 400);
            }

            if (!$acao || $acao < 1) {
                throw new InvalidArgumentException('O valor do id da ação informado não é válido.', 400);
            }

            $pdo = Conexao::connect();
            $permissao = new PermissaoDAO($pdo);

            $pdo->beginTransaction();

            // permissões atuais no banco
            $permissoesBd = $permissao->getPermissoesByCargo($cargo);
            $recursosBd = $permissoesBd ? array_column($permissoesBd, 'id_recurso') : [];

            // normalizar para int
            $recursos = array_map('intval', $recursos);

            // calcular diferenças
            $inserirPermissoes = array_diff($recursos, $recursosBd);
            $removerPermissoes = array_diff($recursosBd, $recursos);

            // remove permissões desmarcadas
            if (!empty($removerPermissoes)) {
                $permissao->removePermissoesByCargo($cargo, $removerPermissoes);
            }

            // adiciona novas permissões
            if (!empty($inserirPermissoes)) {
                if (!$permissao->adicionarPermissao($cargo, $acao, $inserirPermissoes)) {
                    throw new Exception('Falha no controle de transação', 500);
                }
            }

            $pdo->commit();

            header('Location:' . '../html/geral/editar_permissoes.php' . '?msg_c=Permissão efetivada com sucesso.');
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
            http_response_code($e->getCode());
            if ($e instanceof PDOException) {
                echo json_encode(['erro' => 'Erro no servidor ao adicionar a permissão do funcionário.']);
            } else {
                echo json_encode(['erro' => $e->getMessage()]);
            }
        }
    }


    public function selecionarCadastro()
    {
        $cpf = $_GET['cpf'];
        $validador = new Util();

        if (!$validador->validarCPF($cpf)) {
            http_response_code(400);
            exit('Erro, o CPF informado não é válido');
        }

        $funcionario = new FuncionarioDAO();
        $funcionario->selecionarCadastro($cpf);
    }

    public function incluir()
    {
        $funcionario = $this->verificarFuncionario();
        $horario = $this->verificarHorario();
        $cpf = $_GET['cpf'];
        $validador = new Util();

        if (!$validador->validarCPF($cpf)) {
            http_response_code(400);
            exit('Erro, o CPF informado não é válido');
        }

        if ($funcionario->getDataNascimento() > Funcionario::getDataNascimentoMaxima() || $funcionario->getDataNascimento() < Funcionario::getDataNascimentoMinima()) {
            http_response_code(400);
            exit('Erro, a data de nascimento de um funcionário não está dentro dos limites permitidos.');
        }

        $funcionarioDAO = new FuncionarioDAO();
        $horarioDAO = new QuadroHorarioDAO();

        try {
            $funcionarioDAO->incluir($funcionario, $cpf);
            $horarioDAO->incluir($horario);
            $_SESSION['proxima'] = "Cadastrar outro funcionario";
            $_SESSION['link'] = "../html/funcionario/cadastro_funcionario.php";
            header("Location: ../html/funcionario/informacao_funcionario.php");
        } catch (PDOException $e) {
            $msg = "Não foi possível registrar o funcionário" . "<br>" . $e->getMessage();
            echo $msg;
        }
    }

    public function incluirExistente()
    {
        $funcionario = $this->verificarExistente();
        $idPessoa = $_GET['id_pessoa'];
        $sobrenome = $_GET['sobrenome'];

        $funcionarioDAO = new FuncionarioDAO();

        try {
            $funcionarioDAO->incluirExistente($funcionario, $idPessoa, $sobrenome);
            $_SESSION['proxima'] = "Cadastrar outro funcionario";
            $_SESSION['link'] = "../html/funcionario/cadastro_funcionario.php";
            header("Location: ../html/funcionario/informacao_funcionario.php");
        } catch (PDOException $e) {
            $msg = "Não foi possível registrar o funcionário" . "<br>" . $e->getMessage();
            echo $msg;
        }
    }

    public function alterarInfPessoal()
    {
        extract($_REQUEST);
        $funcionario = new Funcionario('', $nome, $sobrenome, $gender, $nascimento, '', '', '', $nome_mae, $nome_pai, $sangue, '', $telefone, '', '', '', '', '', '', '', '', '');
        $funcionario->setId_funcionario($id_funcionario);
        $funcionarioDAO = new FuncionarioDAO();
        try {
            $funcionarioDAO->alterarInfPessoal($funcionario);
            header("Location: ../html/funcionario/profile_funcionario.php?id_funcionario=" . $id_funcionario);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function alterarSenha()
    {
        extract($_REQUEST);


        $funcionarioDAO = new FuncionarioDAO();

        if ($id_pessoa != $_SESSION['id_pessoa']) {
            try {
                if (!$funcionarioDAO->verificaAdm($_SESSION['id_pessoa'])) {
                    http_response_code(401);
                    exit('Operação negada: O usuário logado não é o mesmo de que se deseja alterar a senha');
                }
            } catch (PDOException $e) {
                echo $e->getMessage();
                exit();
            }
        }

        $nova_senha = hash('sha256', $nova_senha);
        if (isset($redir)) {
            $page = $redir;
            $verificacao = $this->verificarSenhaConfig();
        } else {
            $verificacao = $this->verificarSenha();
            $page = "alterar_senha.php";
        }
        if ($verificacao == 1) {
            header("Location: " . WWW . "html/$page?verificacao=" . $verificacao);
        } elseif ($verificacao == 2) {
            header("Location: " . WWW . "html/$page?verificacao=" . $verificacao);
        } else {
            try {
                $funcionarioDAO->alterarSenha($id_pessoa, $nova_senha);
                $conexao =  mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                $resultado = mysqli_query($conexao, "UPDATE pessoa set adm_configurado=1 where cpf='admin'");
                $resultado = mysqli_query($conexao, "SELECT original from selecao_paragrafo where id_selecao = 1");
                $registro = mysqli_fetch_array($resultado);
                if ($registro['original'] == 1) {
                    header("Location: " . WWW . "html/$page?verificacao=" . $verificacao . "&redir_config=true");
                } else  header("Location: " . WWW . "html/$page.php?verificacao=" . $verificacao);
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
    }

    public function alterarOutros()
    {
        extract($_REQUEST);
        $cpf = str_replace(".", '', $cpf);
        $cpf = str_replace("-", "", $cpf);

        $funcionario = new Funcionario('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');

        $funcionario->setId_funcionario($id_funcionario);
        $funcionario->setId_cargo($cargo);
        $funcionario->setPis($pis);
        $funcionario->setCtps($ctps);
        $funcionario->setUf_ctps($uf_ctps);
        $funcionario->setNumero_titulo($titulo_eleitor);
        $funcionario->setZona($zona_eleitoral);
        $funcionario->setSecao($secao_titulo_eleitor);
        $funcionario->setCertificado_reservista_numero($certificado_reservista_numero);
        $funcionario->setCertificado_reservista_serie($certificado_reservista_serie);
        $funcionario->setId_situacao($situacao);
        $funcionarioDAO = new FuncionarioDAO();
        try {
            $funcionarioDAO->alterarOutros($funcionario);
            header("Location: ../html/funcionario/profile_funcionario.php?id_funcionario=" . $id_funcionario);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function alterarImagem()
    {
        extract($_REQUEST);
        $img = file_get_contents($_FILES['imgperfil']['tmp_name']);
        $funcionarioDAO = new FuncionarioDAO();
        try {
            $funcionarioDAO->alterarImagem($id_funcionario, $img);
            header("Location: ../html/funcionario/profile_funcionario.php?id_funcionario=" . $id_funcionario);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function alterarDocumentacao()
    {
        extract($_REQUEST);

        $funcionario = new Funcionario($cpf, '', '', '', '', $rg, $orgao_emissor, $data_expedicao, '', '', '', '', '', '', '', '', '', '', '', '', '', '');

        $funcionario->setData_admissao($data_admissao);
        $funcionario->setId_funcionario($id_funcionario);

        $funcionarioDAO = new FuncionarioDAO();
        try {
            $funcionarioDAO->alterarDocumentacao($funcionario);
            header("Location: ../html/funcionario/profile_funcionario.php?id_funcionario=" . $id_funcionario);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function alterarEndereco()
    {
        extract($_REQUEST);
        if ((!isset($numero_residencia)) || empty(($numero_residencia))) {
            $numero_residencia = "null";
        }
        $funcionario = new Funcionario('', '', '', '', '', '', '', '', '', '', '', '', '', '', $cep, $uf, $cidade, $bairro, $rua, $numero_residencia, $complemento, $ibge);
        $funcionario->setId_funcionario($id_funcionario);
        $funcionarioDAO = new FuncionarioDAO();
        try {
            $funcionarioDAO->alterarEndereco($funcionario);
            header("Location: ../html/funcionario/profile_funcionario.php?id_funcionario=" . $id_funcionario);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function alterarCargaHoraria()
    {
        extract($_REQUEST);
        $carga_horaria = $this->verificarHorario();
        $quadroHorarioDAO = new QuadroHorarioDAO();
        try {
            $quadroHorarioDAO->alterar($carga_horaria, $id_funcionario);
            session_start();
            $_SESSION['msg'] = "Informações do funcionário alteradas com sucesso!";
            $_SESSION['proxima'] = "Ver lista de funcionario";
            $_SESSION['link'] = "../html/funcionario/informacao_funcionario.php";
            header("Location: ../html/sucesso.php");
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function excluir()
    {
        extract($_REQUEST);
        $funcionarioDAO = new FuncionarioDAO();
        try {
            $funcionarioDAO->excluir($id_funcionario);
            header("Location:../controle/control.php?metodo=listarTodos&nomeClasse=FuncionarioControle&nextPage=../html/funcionario/informacao_funcionario.php");
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
