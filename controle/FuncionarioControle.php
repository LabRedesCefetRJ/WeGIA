<?php
if (session_status() === PHP_SESSION_NONE)
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
include_once ROOT . "/dao/Conexao.php";
include_once ROOT . '/classes/Funcionario.php';
include_once ROOT . '/classes/QuadroHorario.php';
include_once ROOT . '/dao/FuncionarioDAO.php';
include_once ROOT . '/dao/QuadroHorarioDAO.php';
include_once ROOT . '/dao/PermissaoDAO.php';
require_once ROOT . '/classes/Util.php';
require_once dirname(__FILE__, 2) . '/html/geral/msg.php';


class FuncionarioControle
{

    /**
     * Recebe uma data no formato dd/mm/yyyy e retorna no formato yyyy/mm/dd
     */
    public function formatoDataYMD($data): string // <-- Deveria estar em uma classe modelo de data, ou mesmo usar as funções nativas do PHP para essa funcionalidade
    {
        $dataArray = explode("/", $data);

        $dataAmericana = $dataArray[2] . '-' . $dataArray[1] . '-' . $dataArray[0];

        return $dataAmericana;
    }

    /**
     * Recebe dois parâmetros no formato HH:mm e retorna a soma deles de acordo com o sistema sexagesimal.
     */
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

    /**
     * Recebe como parâmetros as horas de entrada e saida do expediente no formato HH:mm e retorna o total de tempo entre elas.
     */
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

    /**Extrai os dados de uma requisição e retorna um objeto do tipo Funcionario*/
    public function verificarFuncionario()
    {
        extract($_REQUEST);

        if ((!isset($nome)) || (empty($nome))) {
            http_response_code(412);
            header('Location: ../html/funcionario.html?msg=Nome do funcionario não informado. Por favor, informe um nome!');
            exit();
        }

        if ((!isset($sobrenome)) || (empty($sobrenome))) {
            http_response_code(412);
            header('Location: ../html/funcionario.html?msg=Sobrenome do funcionario não informado. Por favor, informe um sobrenome!');
            exit();
        }
        if ((!isset($gender)) || (empty($gender))) {
            http_response_code(412);
            header('Location: ../html/funcionario.html?msg=Sexo do funcionario não informado. Por favor, informe um sexo!');
            exit();
        }

        if ((!isset($cargo)) || (empty($cargo))) {
            http_response_code(412);
            header('Location: ../html/funcionario.html?msg=Cargo do funcionario não informado. Por favor, informe um cargo!');
            exit();
        }

        if ((!isset($telefone)) || (empty($telefone))) {
            $telefone = 'null';
        }

        if ((!isset($nascimento)) || (empty($nascimento))) {
            http_response_code(412);
            header('Location: ../html/funcionario.html?msg=Data de nascimento do funcionario não informado. Por favor, informe uma data de nascimento!');
            exit();
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
            http_response_code(412);
            header('Location: ../html/funcionario.html?msg=RG do funcionario não informado. Por favor, informe um rg!');
            exit();
        }

        if ((!isset($orgao_emissor)) || empty(($orgao_emissor))) {
            http_response_code(412);
            header('Location: ../html/funcionario.html?msg=Órgão emissor do funcionario não informado. Por favor, informe o órgão emissor!');
            exit();
        }

        if ((!isset($data_expedicao)) || (empty($data_expedicao))) {
            http_response_code(412);
            header('Location: ../html/funcionario.html?msg=Data de expedição do rg do funcionario não informado. Por favor, informe um data de expedição!');
            exit();
        }

        if ((!isset($cpf)) || (empty($cpf))) {
            http_response_code(412);
            header('Location: ../html/funcionario.html?msg=CPF do funcionario não informado. Por favor, informe um CPF!');
            exit();
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
            http_response_code(412);
            header('Location: ../html/funcionario.html?msg=Data de Admissão do funcionario não informada. Por favor, informe a data de admissao!');
            exit();
        }

        if ((!isset($situacao)) || (empty($situacao))) {
            http_response_code(412);
            header('Location: ../html/funcionario.html?msg=Situação do funcionario não informada. Por favor, informe a situação!');
            exit();
        }

        if ((!isset($certificado_reservista_numero)) || (empty($certificado_reservista_numero))) {
            $certificado_reservista_numero = '';
        }

        if ((!isset($certificado_reservista_serie)) || (empty($certificado_reservista_serie))) {
            $certificado_reservista_serie = '';
        }

        if (!Util::validarCPF($cpf)) {
            http_response_code(412);
            header('Location: ../html/funcionario.html?msg=O CPF informado é inválido.');
            exit();
        }

        if (strtotime($data_expedicao) < strtotime(($nascimento))) {
            http_response_code(412);
            $_SESSION['erro'] = 'A data de expedição é anterior ao nascimento. Por favor, informa uma data válida!';
            header('Location: ../html/funcionario/cadastro_funcionario.php?cpf=' . htmlspecialchars($cpf));
            exit;
        }


        if ((!isset($_SESSION['imagem'])) || (empty($_SESSION['imagem']))) {
            $imgperfil = '';
        } else {
            $imgperfil = base64_encode($_SESSION['imagem']);
            unset($_SESSION['imagem']);
        }

        $senha = '';
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

        if ((!isset($_SESSION['imagem'])) || (empty($_SESSION['imagem']))) {
            $imgperfil = '';
        } else {
            $imgperfil = base64_encode($_SESSION['imagem']);
            unset($_SESSION['imagem']);
        }

        $senha = '';
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

    public function verificarSenha()
    {
        try {
            extract($_REQUEST);
            $nova_senha = hash('sha256', $nova_senha);
            $confirmar_senha = hash('sha256', $confirmar_senha);
            $senha_antiga = hash('sha256', $senha_antiga);
            if ($nova_senha != $confirmar_senha) {
                return 1;
            } else {
                $pdo = Conexao::connect();
                $funcionarioDAO = new FuncionarioDAO();
                $senha = $funcionarioDAO->getSenhaByIdPessoa($id_pessoa);

                if ($senha != $senha_antiga) {
                    return 2;
                }
            }
            return 3;
        } catch (Exception $e) {
            Util::tratarException($e);
        }
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
        try {
            extract($_REQUEST);

            isset($_GET['select_situacao']) === false ? $situacao_selecionada = 1 : $situacao_selecionada = $_GET['select_situacao'];

            $funcionariosDAO = new FuncionarioDAO();
            $funcionarios = $funcionariosDAO->listarTodos($situacao_selecionada);

            $whitePages =
                [
                    '../html/funcionario/informacao_funcionario.php',
                    WWW . "html/funcionario/informacao_funcionario.php",
                    '../html/geral/editar_permissoes.php',
                ];

            $_SESSION['funcionarios'] = json_encode($funcionarios);

            isset($nextPage) && in_array($nextPage, $whitePages) ? header('Location: ' . $nextPage) : header('Location: ' . WWW . 'html/home.php');
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarTodos2()
    {
        try {
            $funcionariosDAO = new FuncionarioDAO();
            $funcionarios = $funcionariosDAO->listarTodos2();
            $_SESSION['funcionarios2'] = json_encode($funcionarios);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarCpf()
    {
        try {
            $funcionariosDAO = new FuncionarioDAO();
            $funcionarioscpf = $funcionariosDAO->listarCPF();
            $_SESSION['cpf_funcionario'] = json_encode($funcionarioscpf);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
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

            $_SESSION['funcionario'] = json_encode($funcionario);

            header('Location:' . WWW . "/html/funcionario/profile_funcionario.php?id_funcionario=" . htmlspecialchars($idFuncionario));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function getIdFuncionarioComIdPessoa()
    {
        try {
            header('Content-Type: application/json');
            $id_pessoa = filter_input(INPUT_GET, 'id_pessoa', FILTER_SANITIZE_NUMBER_INT);

            if (!$id_pessoa || $id_pessoa < 1)
                throw new InvalidArgumentException('O id de uma pessoa não pode ser menor que 1.', 412);

            $funcionarioDAO = new FuncionarioDAO;
            $id_funcionario = $funcionarioDAO->getIdFuncionarioComIdPessoa($id_pessoa);
            echo json_encode($id_funcionario);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function adicionarPermissao()
    {
        try {
            //adicionar csrf
            $cargo = filter_input(INPUT_POST, 'cargo', FILTER_SANITIZE_NUMBER_INT);
            $acao = filter_input(INPUT_POST, 'acao', FILTER_SANITIZE_NUMBER_INT);
            $recursos = filter_input(INPUT_POST, 'recurso', FILTER_VALIDATE_INT, [
                'flags' => FILTER_REQUIRE_ARRAY,
                'options' => ['min_range' => 1]
            ]);

            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

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
            if (!isset($recursos))
                $recursos = [];

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
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Util::tratarException($e);
        }
    }

    public function selecionarCadastro()
    {
        try {
            $cpf = filter_input(INPUT_GET, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);

            if (!Util::validarCPF($cpf))
                throw new InvalidArgumentException("O CPF informado não é válido.", 412);

            $funcionario = new FuncionarioDAO();
            $resultado = $funcionario->selecionarCadastro($cpf);

            // CPF não existe na tabela pessoa
            if (!$resultado) {
                header('Location: ../html/funcionario/cadastro_funcionario.php?cpf=' . urlencode($cpf));
                exit;
            }

            // CPF existe, mas ainda não é funcionário
            if ($resultado['funcionario_id'] === null) {
                header('Location: ../html/funcionario/cadastro_funcionario_pessoa_existente.php?cpf=' . urlencode($cpf));
                exit;
            }

            // Já é funcionário
            header('Location: ../html/funcionario/pre_cadastro_funcionario.php?msg_e=' . urlencode('Erro, Funcionário já cadastrado no sistema.'));
            exit;
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function incluir()
    {
        try {
            $funcionario = $this->verificarFuncionario();
            $horario = $this->verificarHorario();
            $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);

            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            if (!Util::validarCPF($cpf))
                throw new InvalidArgumentException('O CPF informado não é válido', 412);

            if ($funcionario->getDataNascimento() > Funcionario::getDataNascimentoMaxima() || $funcionario->getDataNascimento() < Funcionario::getDataNascimentoMinima())
                throw new InvalidArgumentException('A data de nascimento de um funcionário não está dentro dos limites permitidos.', 412);

            $funcionarioDAO = new FuncionarioDAO();
            $horarioDAO = new QuadroHorarioDAO();

            $funcionarioDAO->incluir($funcionario, $cpf);
            $horarioDAO->incluir($horario);

            $_SESSION['msg']  = "Funcionário cadastrado com sucesso";
            $_SESSION['tipo'] = "success";

            header("Location: ../html/funcionario/profile_funcionario.php?id_funcionario=" . (int)$idFuncionario);
            exit;
        } catch (PDOException $e) {
            $msg = "Não foi possível registrar o funcionário<br>" . $e->getMessage();
            echo $msg;
        }
    }


    public function incluirExistente()
    {
        try {
            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            $funcionario = $this->verificarExistente();
            $idPessoa = filter_input(INPUT_POST, 'id_pessoa', FILTER_SANITIZE_NUMBER_INT);
            $sobrenome = filter_input(INPUT_POST, 'sobrenome', FILTER_SANITIZE_SPECIAL_CHARS);

            $funcionarioDAO = new FuncionarioDAO();

            $funcionarioDAO->incluirExistente($funcionario, $idPessoa, $sobrenome);
            $_SESSION['proxima'] = "Cadastrar outro funcionario";
            $_SESSION['link'] = "../html/funcionario/cadastro_funcionario.php";
            header("Location: ../html/funcionario/informacao_funcionario.php");
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }


    public function alterarInfPessoal()
    {
        try {
            extract($_REQUEST);

            $id_funcionario = filter_var($id_funcionario, FILTER_VALIDATE_INT);

            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            if (!$id_funcionario || $id_funcionario < 1)
                throw new InvalidArgumentException('O id do funcionário não está dentro dos limites permitidos.', 412);

            if ($nascimento > Funcionario::getDataNascimentoMaxima() || $nascimento < Funcionario::getDataNascimentoMinima())
                throw new InvalidArgumentException('A data de nascimento de um funcionário não está dentro dos limites permitidos.', 412);

    
        $erros = [];

        if (!isset($nome) || trim($nome) === '') {
            $erros[] = "Nome do funcionário não pode ser vazio.";
        }
        if (!isset($sobrenome) || trim($sobrenome) === '') {
            $erros[] = "Sobrenome do funcionário não pode ser vazio.";
        }
        if (!isset($gender) || ($gender !== 'm' && $gender !== 'f')) {
            $erros[] = "Sexo do funcionário é obrigatório.";
        }
        if (!isset($nascimento) || trim($nascimento) === '') {
            $erros[] = "Data de nascimento é obrigatória.";
        }
        if (!isset($telefone) || trim($telefone) === '') {
            $erros[] = "Telefone do funcionário é obrigatório.";
        }

        if (!empty($erros)) {
            setSessionMsg(implode(' ', $erros), 'err');
            header("Location: ../html/funcionario/profile_funcionario.php?id_funcionario=" . (int)$id_funcionario);
            exit();
        }

        if ($nascimento === '') {
            $nascimento = null;
        }

        $funcionario = new Funcionario(
            '',
            $nome,
            $sobrenome,
            $gender,
            $nascimento,
            '',
            '',
            '',
            $nome_mae,
            $nome_pai,
            $sangue,
            '',
            $telefone,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        );
            $funcionario->setId_funcionario($id_funcionario);
    
        $funcionarioDAO = new FuncionarioDAO();


            $funcionarioDAO->alterarInfPessoal($funcionario);
            header("Location: ../html/funcionario/profile_funcionario.php?id_funcionario=" . urlencode($id_funcionario));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }



    /**
     * Altera a chave de acesso ao sistema de determinado usuário, permite que administradores configurados possam alterar a senha de outras pessoas
     */
    public function alterarSenha()
    {
        $id_pessoa = filter_input(INPUT_POST, 'id_pessoa', FILTER_SANITIZE_NUMBER_INT);
        $nova_senha = filter_input(INPUT_POST, 'nova_senha');
        $redir = filter_input(INPUT_POST, 'redir', FILTER_SANITIZE_SPECIAL_CHARS);

        try {
            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            if (!$id_pessoa || $id_pessoa < 1)
                throw new InvalidArgumentException('O id da pessoa informado não é válido.', 400);

            $funcionarioDAO = new FuncionarioDAO();

            if ($id_pessoa != $_SESSION['id_pessoa'] && !$funcionarioDAO->verificaAdm($_SESSION['id_pessoa']))
                throw new LogicException('Operação negada: O usuário logado não é o mesmo de que se deseja alterar a senha', 401);

            $minLength = 8;
            $regex = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{" . $minLength . ",}$/";

            if (!preg_match($regex, $nova_senha))
                throw new InvalidArgumentException('A senha informada não atende aos requisitos mínimos estabelecidos.', 412);

            $nova_senha = hash('sha256', $nova_senha);
            if (isset($redir)) {
                $page = $redir;
                $verificacao = $this->verificarSenhaConfig();
            } else {
                $verificacao = $this->verificarSenha();
                $page = "alterar_senha.php";
            }
            if ($verificacao == 1 || $verificacao == 2) {
                header("Location: " . WWW . 'html/' . htmlspecialchars($page) . '?verificacao=' . htmlspecialchars($verificacao));
                exit();
            } else {
                $funcionarioDAO->alterarSenha($id_pessoa, $nova_senha);

                $conexao =  mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                $resultado = mysqli_query($conexao, "UPDATE pessoa set adm_configurado=1 where cpf='admin'");
                $resultado = mysqli_query($conexao, "SELECT original from selecao_paragrafo where id_selecao = 1");
                $registro = mysqli_fetch_array($resultado);

                $registro['original'] == 1 ? header("Location: " . WWW . 'html/' . htmlspecialchars($page) . '?verificacao=' . htmlspecialchars($verificacao) . "&redir_config=true") : header("Location: " . WWW . 'html/' . htmlspecialchars($page) . '.php?verificacao=' . htmlspecialchars($verificacao));
            }
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function alterarOutros()
    {
        try {
            extract($_REQUEST);

            $idPessoa = filter_var($_SESSION['id_pessoa'], FILTER_SANITIZE_NUMBER_INT);
            $idFuncionario = filter_var($id_funcionario, FILTER_SANITIZE_NUMBER_INT);

            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            if (!$idPessoa || $idPessoa < 1)
                throw new InvalidArgumentException('O id do usuário fornecido não é válido.', 412);

            if (!$idFuncionario || $idFuncionario < 1)
                throw new InvalidArgumentException('O id do funcionário fornecido é inválido.', 412);

            $funcionario = new Funcionario('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare('SELECT adm_configurado FROM pessoa WHERE id_pessoa=:idPessoa');
            $stmt->bindValue(':idPessoa', $idPessoa, PDO::PARAM_INT);
            $stmt->execute();

            $adm_configurado = $stmt->fetch(PDO::FETCH_ASSOC)['adm_configurado'];

            $stmt = $pdo->prepare('SELECT id_cargo FROM funcionario WHERE id_funcionario=:idFuncionario');
            $stmt->bindValue(':idFuncionario', $idFuncionario);
            $stmt->execute();

            $cargo_anterior_funcionario = $stmt->fetch(PDO::FETCH_ASSOC)['id_cargo'];

            if (!$adm_configurado && $cargo_anterior_funcionario == 1)
                throw new InvalidArgumentException("O usuário, mesmo como administrador, não pode alterar esse funcionário", 403);

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

            $funcionarioDAO->alterarOutros($funcionario);
            header("Location: ../html/funcionario/profile_funcionario.php?id_funcionario=" . urlencode($id_funcionario));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function alterarImagem()
    {
        try {
            $idFuncionario = filter_var($_REQUEST['id_funcionario'], FILTER_SANITIZE_NUMBER_INT);

            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            if (!$idFuncionario || $idFuncionario < 1)
                throw new InvalidArgumentException('O id do funcionário fornecido é inválido.', 412);

            $img = file_get_contents($_FILES['imgperfil']['tmp_name']);
            $funcionarioDAO = new FuncionarioDAO();

            $funcionarioDAO->alterarImagem($idFuncionario, $img);
            header("Location: ../html/funcionario/profile_funcionario.php?id_funcionario=" . urlencode($idFuncionario));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function alterarDocumentacao()
    {
        try {
            $data_expedicao = filter_var($_REQUEST['data_expedicao'], FILTER_UNSAFE_RAW);
            $id_funcionario = filter_var($_REQUEST['id_funcionario'], FILTER_SANITIZE_NUMBER_INT);
            $rg = filter_var($_REQUEST['rg'], FILTER_SANITIZE_SPECIAL_CHARS);
            $orgao_emissor = filter_var($_REQUEST['orgao_emissor'], FILTER_SANITIZE_SPECIAL_CHARS);

            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            //validar datas
            $dateFormatYMD = '/^\d{4}-\d{2}-\d{2}$/';

            if (!preg_match($dateFormatYMD, $data_expedicao))
                throw new InvalidArgumentException('A data de expedição informada não está no formato correto.', 412);

            $dataExpedicaoArray = explode('-', $data_expedicao);
            if (!checkdate($dataExpedicaoArray[1], $dataExpedicaoArray[2], $dataExpedicaoArray[0]))
                throw new InvalidArgumentException('A data de expedição informada não é válida.', 412);

            if (!$id_funcionario || $id_funcionario < 1)
                throw new InvalidArgumentException('O id do funcionário informado não é válido.', 412);

            if (!$rg || strlen(preg_replace('/\D/', '', $rg)) != 9) //Posteriormente, incrementar essa validação na classe Util, tal como CPF e CNPJ
                throw new InvalidArgumentException('O RG informado não é válido.', 412);

            if (!$orgao_emissor || strlen($orgao_emissor) > 255)
                throw new InvalidArgumentException('O tamanho do orgão emissor fornecido não é válido.', 412);

            $formatar = new Util();

            if ($_SESSION['data_nasc']) {
                if (strtotime($data_expedicao) < strtotime($formatar->formatoDataYMD($_SESSION['data_nasc']))) {
                    echo 'A data de expedição é anterior à do nascimento. Por favor, informe uma data válida!';
                    header("Location: ../html/funcionario/profile_funcionario.php?&id_funcionario=" . urlencode($id_funcionario));
                    exit;
                }
                unset($_SESSION['data_nasc']);
            }

            $funcionario = new Funcionario('', '', '', '', '', $rg, $orgao_emissor, $data_expedicao, '', '', '', '', '', '', '', '', '', '', '', '', '', '');

            $funcionario->setId_funcionario($id_funcionario);

            $funcionarioDAO = new FuncionarioDAO();

            $funcionarioDAO->alterarDocumentacao($funcionario);
            header("Location: ../html/funcionario/profile_funcionario.php?id_funcionario=" . urlencode($id_funcionario));
        } catch (Exception $e) {
            Util::tratarException($e);
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
            setSessionMsg("Endereço atualizado com sucesso!", "sccs");
            header("Location: ../html/funcionario/profile_funcionario.php?id_funcionario=" . (int)$id_funcionario);
            exit();
        } catch (PDOException $e) {
            setSessionMsg("Erro ao atualizar endereço.", "err");
            header("Location: ../html/funcionario/profile_funcionario.php?id_funcionario=" . (int)$id_funcionario);
            exit();
        }
    }


    public function alterarCargaHoraria()
    {
        try {
            $id_funcionario = filter_var($_REQUEST['id_funcionario'], FILTER_SANITIZE_NUMBER_INT);

            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            if (!$id_funcionario || $id_funcionario < 1)
                throw new InvalidArgumentException('O id do funcionário informado não é válido.', 412);

            $carga_horaria = $this->verificarHorario();
            $quadroHorarioDAO = new QuadroHorarioDAO();

            $quadroHorarioDAO->alterar($carga_horaria, $id_funcionario);

            $_SESSION['msg'] = "Informações do funcionário alteradas com sucesso!";
            $_SESSION['proxima'] = "Ver lista de funcionario";
            $_SESSION['link'] = "../html/funcionario/informacao_funcionario.php";
            header("Location: ../html/sucesso.php");
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Pega o parâmetro id_funcionario da requisição e altera o status para inativo do funcionário de id equivalente.
     */
    public function excluir()
    {
        $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);

        try {
            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            if (!$idFuncionario || $idFuncionario < 1) {
                throw new InvalidArgumentException('O id do funcionário fornecido é inválido.', 400);
            }

            $funcionarioDAO = new FuncionarioDAO();

            $funcionarioDAO->excluir($idFuncionario);
            header("Location:../controle/control.php?metodo=listarTodos&nomeClasse=FuncionarioControle&nextPage=../html/funcionario/informacao_funcionario.php");
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
