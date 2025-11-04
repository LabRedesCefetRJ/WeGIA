<?php
$config_path = "config.php";
if (file_exists($config_path)) {
    require_once($config_path);
} else {
    while (true) {
        $config_path = "../" . $config_path;
        if (file_exists($config_path)) break;
    }
    require_once($config_path);
}

//include_once "/dao/Conexao.php";
require_once ROOT . '/classes/Atendido.php';
require_once ROOT . '/dao/AtendidoDAO.php';
require_once ROOT . '/classes/Documento.php';
require_once ROOT . '/dao/DocumentoDAO.php';
require_once 'DocumentoControle.php';
include_once ROOT . '/classes/Cache.php';
require_once ROOT . '/classes/Util.php';

include_once ROOT . "/dao/Conexao.php";

//require_once ROOT."/controle/AtendidoControle.php";
//$listaAtendidos = new AtendidoControle();
//$listaAtendidos->listarTodos2();

class AtendidoControle
{

    public function formatoDataYMD($data)
    {
        $data_arr = explode("/", $data);

        $datac = $data_arr[2] . '-' . $data_arr[1] . '-' . $data_arr[0];

        return $datac;
    }

    public function verificar()
    {
        extract($_REQUEST);
        if ((!isset($cpf)) || (empty($cpf))) {
            $msg .= "cpf do atendido não informado. Por favor, informe o cpf!";
            header('Location: ../html/atendido/Cadastro_Atendido.php?msg=' . $msg);
        }
        if ((!isset($nome)) || (empty($nome))) {
            $nome = "";
        }
        if ((!isset($sobrenome)) || (empty($sobrenome))) {
            $sobrenome = "";
        }
        if ((!isset($sexo)) || (empty($sexo))) {
            $msg .= "Sexo do atendido não informado. Por favor, informe o sexo!";
            header('Location: ../html/atendido/Cadastro_Atendido.php?msg=' . $msg);
        }
        if ((!isset($nascimento)) || (empty($nascimento))) {
            $msg .= "Nascimento do atendido não informado. Por favor, informe a data!";
            header('Location: ../html/atendido/Cadastro_Atendido.php?msg=' . $msg);
        }
        if ((!isset($registroGeral)) || (empty($registroGeral))) {
            $registroGeral = "";
        }
        if ((!isset($orgaoEmissor)) || empty(($orgaoEmissor))) {
            $orgaoEmissor = "";
        }
        if ((!isset($dataExpedicao)) || (empty($dataExpedicao))) {
            $dataExpedicao = "";
        }
        if ((!isset($nomePai)) || (empty($nomePai))) {
            $nomePai = '';
        }
        if ((!isset($nomeMae)) || (empty($nomeMae))) {
            $nomeMae = '';
        }
        if ((!isset($tipoSanguineo)) || (empty($tipoSanguineo))) {
            $tipoSanguineo = '';
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
        if ((!isset($logradouro)) || empty(($logradouro))) {
            $logradouro = '';
        }
        if ((!isset($numeroEndereco)) || empty(($numeroEndereco))) {
            $numeroEndereco = '';
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
        if ((!isset($telefone)) || (empty($telefone))) {
            $telefone = 'null';
        }
        session_start();
        if ((!isset($_SESSION['imagem'])) || (empty($_SESSION['imagem']))) {
            $imgperfil = '';
        } else {
            $imgperfil = base64_encode($_SESSION['imagem']);
            unset($_SESSION['imagem']);
        }


        // $cpf=str_replace(".", '', $cpf);
        // $cpf=str_replace("-", "", $cpf);
        //$nascimento=str_replace("-", "", $nascimento);
        $senha = 'null';
        $atendido = new Atendido($cpf, $nome, $sobrenome, $sexo, $nascimento, $registroGeral, $orgaoEmissor, $dataExpedicao, $nomeMae, $nomePai, $tipoSanguineo, $senha, $telefone, $imgperfil, $cep, $uf, $cidade, $bairro, $logradouro, $numeroEndereco, $complemento, $ibge);
        $atendido->setIntTipo($intTipo);
        $atendido->setIntStatus($intStatus);
        return $atendido;
    }

    public function verificarExistente()
    {
        extract($_REQUEST);
        if ((!isset($cpf)) || (empty($cpf))) {
            $cpf = "";
        }
        if ((!isset($nome)) || (empty($nome))) {
            $nome = '';
        }
        if ((!isset($sobrenome)) || (empty($sobrenome))) {
            $sobrenome = '';
        }
        if ((!isset($sexo)) || (empty($sexo))) {
            $sexo = '';
        }
        if ((!isset($nascimento)) || (empty($nascimento))) {
            $nascimento = '';
        }
        if ((!isset($registroGeral)) || (empty($registroGeral))) {
            $registroGeral = "";
        }
        if ((!isset($orgaoEmissor)) || empty(($orgaoEmissor))) {
            $orgaoEmissor = "";
        }
        if ((!isset($dataExpedicao)) || (empty($dataExpedicao))) {
            $dataExpedicao = "";
        }
        if ((!isset($nomePai)) || (empty($nomePai))) {
            $nomePai = '';
        }
        if ((!isset($nomeMae)) || (empty($nomeMae))) {
            $nomeMae = '';
        }
        if ((!isset($tipoSanguineo)) || (empty($tipoSanguineo))) {
            $tipoSanguineo = '';
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
        if ((!isset($logradouro)) || empty(($logradouro))) {
            $logradouro = '';
        }
        if ((!isset($numeroEndereco)) || empty(($numeroEndereco))) {
            $numeroEndereco = '';
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
        if ((!isset($telefone)) || (empty($telefone))) {
            $telefone = 'null';
        }
        session_start();
        if ((!isset($_SESSION['imagem'])) || (empty($_SESSION['imagem']))) {
            $imgperfil = '';
        } else {
            $imgperfil = base64_encode($_SESSION['imagem']);
            unset($_SESSION['imagem']);
        }


        // $cpf=str_replace(".", '', $cpf);
        // $cpf=str_replace("-", "", $cpf);
        //$nascimento=str_replace("-", "", $nascimento);
        $senha = 'null';
        $atendido = new Atendido($cpf, $nome, $sobrenome, $sexo, $nascimento, $registroGeral, $orgaoEmissor, $dataExpedicao, $nomeMae, $nomePai, $tipoSanguineo, $senha, $telefone, $imgperfil, $cep, $uf, $cidade, $bairro, $logradouro, $numeroEndereco, $complemento, $ibge);
        $atendido->setIntTipo($intTipo);
        $atendido->setIntStatus($intStatus);
        return $atendido;
    }




    public function listarTodos()
    {
        require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/atendido/(Informacao_Atendido|cadastro_ocorrencia|listar_ocorrencias_ativas)\.php)$#';

        $status = filter_input(INPUT_GET, 'select_status', FILTER_SANITIZE_NUMBER_INT);

        if (isset($status) && $status < 1) {
            http_response_code(400);
            echo json_encode(['erro' => 'O id do status fornecido não é válido.']);
            exit();
        }

        $AtendidoDAO = new AtendidoDAO();
        $atendidos = $AtendidoDAO->listarTodos($status);
        if (session_status() === PHP_SESSION_NONE)
            session_start();

        $_SESSION['atendidos'] = $atendidos;

        preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . '../html/home.php');
    }

    public function listarTodos2()
    {
        extract($_REQUEST);
        $AtendidoDAO = new AtendidoDAO();
        $atendidos = $AtendidoDAO->listarTodos2();
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['atendidos2'] = $atendidos;
    }

    public function listarUm()
    {
        require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/atendido/Profile_Atendido\.php(\?id=\d+|\?idatendido=\d+(\&id=\d+)?)?)$#';

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        if(!$id || $id < 1){
            http_response_code(400);
            echo json_encode(['erro' => 'O id fornecido é inválido.']);
            exit();
        }

        $cache = new Cache();
        $infAtendido = $cache->read($id);
        if (!$infAtendido) {
            try {
                $AtendidoDAO = new AtendidoDAO();
                $infAtendido = $AtendidoDAO->listar($id);

                if(session_status() ===PHP_SESSION_NONE)
                    session_start();

                $_SESSION['atendido'] = $infAtendido;
                $cache->save($id, $infAtendido, '15 seconds');
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }

        preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . '../html/home.php');
    }

    public function listarCpf()
    {
        extract($_REQUEST);
        $atendidosDAO = new AtendidoDAO();
        $atendidoscpf = $atendidosDAO->listarcpf();
        $_SESSION['cpf_atendido'] = $atendidoscpf;
    }

    public function comprimir($documParaCompressao)
    {
        $documento_zip = gzcompress($documParaCompressao);
        return $documento_zip;
    }

    public function selecionarCadastro()
    {
        $cpf = $_GET['cpf'];
        $validador = new Util();

        if (!$validador->validarCPF($cpf)) {
            http_response_code(400);
            exit('Erro, o CPF informado não é válido');
        }

        $atendido = new AtendidoDAO();
        $atendido->selecionarCadastro($cpf);
    }


    public function incluir()
{
    $atendido = $this->verificar();
    $cpf = $_GET['cpf'];
    $validador = new Util();

    $pdo = Conexao::connect();

    if (!empty($cpf)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pessoa WHERE cpf = ?");
        $stmt->execute([$cpf]);
        $count = $stmt->fetchColumn();


        if ($count > 0) {
            http_response_code(400);
            exit('Erro: CPF já cadastrado no sistema.');
        }
    }

    if (!$validador->validarCPF($cpf)) {
        http_response_code(400);
        exit('Erro, o CPF informado não é válido');
    }

    if ($atendido->getDataNascimento() > Atendido::getDataNascimentoMaxima() || $atendido->getDataNascimento() < Atendido::getDataNascimentoMinima()) {
        http_response_code(400);
        exit('Erro, a data de nascimento informada está fora dos limites permitidos.');
    }

    $intDAO = new AtendidoDAO();
    
    try {
        $idatendido = $intDAO->incluir($atendido, $cpf);
        $_SESSION['msg'] = "Atendido cadastrado com sucesso";
        $_SESSION['proxima'] = "Cadastrar outro atendido";
        $_SESSION['link'] = "../html/atendido/Cadastro_Atendido.php";
        header("Location: ../html/atendido/Informacao_Atendido.php");
    } catch (PDOException $e) {
        $msg = "Não foi possível registrar o atendido <form> <input type='button' value='Voltar' onClick='history.go(-1)'> </form>" . "<br>" . $e->getMessage();
        echo $msg;
    }
}

    public function incluirSemCpf()
{
    $atendido = $this->verificar();  // Extrai dados do formulário

    // Passa o CPF vazio ou NULL
    $cpf = '';

    $intDAO = new AtendidoDAO();
    try {
        $intDAO->incluir($atendido, $cpf);
        $_SESSION['msg'] = "Atendido cadastrado sem CPF com sucesso";
        header('Location: ../html/atendido/Informacao_Atendido.php');
        exit();
    } catch (PDOException $e) {
        echo "Erro ao cadastrar: " . $e->getMessage();
    }
}




    public function incluirExistente()
    {
        $atendido = $this->verificarExistente();
        $idPessoa = $_GET['id_pessoa'];
        $sobrenome = $_GET['sobrenome'];
        $atendidoDAO = new AtendidoDAO();

        try {
            $atendidoDAO->incluirExistente($atendido, $idPessoa, $sobrenome);
            // $horarioDAO->incluir($horario);
            //$beneficiadosDAO->incluir($beneficiados);
            //$epiDAO->incluir($epi);
            $_SESSION['msg'] = "Atendido cadastrado com sucesso";
            $_SESSION['proxima'] = "Cadastrar outro atendido";
            $_SESSION['link'] = "../html/atendido/cadastro_atendido.php";
            header("Location: ../html/atendido/Informacao_Atendido.php");
        } catch (PDOException $e) {
            $msg = "Não foi possível registrar o atendido" . "<br>" . $e->getMessage();
            echo $msg;
        }
    }

    public function alterar()
    {
        extract($_REQUEST);
        $atendido = $this->verificar();
        $atendido->setidatendido($idatendido);
        $AtendidoDAO = new AtendidoDAO();
        try {
            $AtendidoDAO->alterar($atendido);
            header("Location: ../html/Profile_Atendido.php?id=" . $idatendido);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function excluir()
    {
        extract($_REQUEST);
        $AtendidoDAO = new AtendidoDAO();
        try {
            $AtendidoDAO->excluir($idatendido);
            header("Location:../controle/control.php?metodo=listarTodos&nomeClasse=AtendidoControle&nextPage=../html/atendido/Informacao_Atendido.php");
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    public function alterarInfPessoal()
    {
        extract($_REQUEST);

        if ($nascimento && $idatendido) {
            try {
                $pdo = Conexao::connect();
                
                // Buscar data de expedição atual do atendido
                $sql_expedicao = "SELECT p.data_expedicao 
                                FROM atendido a 
                                JOIN pessoa p ON a.pessoa_id_pessoa = p.id_pessoa 
                                WHERE a.idatendido = :idatendido";
                $stmt_expedicao = $pdo->prepare($sql_expedicao);
                $stmt_expedicao->bindParam(':idatendido', $idatendido);
                $stmt_expedicao->execute();
                $atendido_doc = $stmt_expedicao->fetch(PDO::FETCH_ASSOC);
                
                if ($atendido_doc && $atendido_doc['data_expedicao']) {
                    $data_nascimento_obj = new DateTime($nascimento);
                    $data_expedicao_obj = new DateTime($atendido_doc['data_expedicao']);
                    
                    if ($data_nascimento_obj > $data_expedicao_obj) {
                        $_SESSION['msg'] = "Erro: A data de nascimento não pode ser posterior à data de expedição do documento!";
                        $_SESSION['tipo'] = "error";
                        header("Location: ../html/atendido/Profile_Atendido.php?idatendido=" . $idatendido);
                        exit;
                    }
                }
            } catch (PDOException $e) {
                die( json_encode( ["Erro ao consultar o banco de dados para verificação das datas de nascimento e expedição.{$e->getMessage()}"] ) );
            }
        }

        $atendido = new Atendido('', $nome, $sobrenome, $sexo, $nascimento, '', '', '', '', '', $tipoSanguineo, '', $telefone, '', '', '', '', '', '', '', '', '');
        $atendido->setIdatendido($idatendido);
        //echo $funcionario->getId_Funcionario();
        $atendidoDAO = new AtendidoDAO();
        try {
            $atendidoDAO->alterarInfPessoal($atendido);
            //echo "<script type='javascript'>alert('Email enviado com Sucesso!');</script>";
            //echo "javascript:window.location='index.php';</script>";
            header("Location: ../html/atendido/Informacao_Atendido.php");
            //sleep(2);
            // header("Location: ../html/atendido/Profile_Atendido.php?idatendido=".$idatendido);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function alterarDocumentacao()
    {
        extract($_REQUEST);
        //$cpf=str_replace(".", '', $cpf);
        //$cpf=str_replace("-", "", $cpf);

        if ($dataExpedicao && $idatendido) {
            try {
                $pdo = Conexao::connect();
                
                // Buscar data de nascimento atual do atendido
                $sql_nascimento = "SELECT p.data_nascimento 
                                FROM atendido a 
                                JOIN pessoa p ON a.pessoa_id_pessoa = p.id_pessoa 
                                WHERE a.idatendido = :idatendido";
                $stmt_nascimento = $pdo->prepare($sql_nascimento);
                $stmt_nascimento->bindParam(':idatendido', $idatendido);
                $stmt_nascimento->execute();
                $atendido_data = $stmt_nascimento->fetch(PDO::FETCH_ASSOC);
                
                if ($atendido_data && $atendido_data['data_nascimento']) {
                    $data_nascimento = new DateTime($atendido_data['data_nascimento']);
                    $data_expedicao_obj = new DateTime($dataExpedicao);
                    
                    if ($data_expedicao_obj <= $data_nascimento) {
                        //$_SESSION['msg'] = "Erro: A data de expedição do documento não pode ser anterior à data de nascimento!";
                        //$_SESSION['tipo'] = "error";
                        //header("Location: ../html/atendido/Profile_Atendido.php?idatendido=" . $idatendido);
                        //exit;
                        die( json_encode( ['A data de expedição do documento não pode ser anterior ou igual à data de nascimento!'] ) );
                    }
                }
            } catch (PDOException $e) {
                die( json_encode( ["Erro ao consultar o banco de dados para verificação das datas de nascimento e expedição.{$e->getMessage()}"] ) );
            }
        }

        $atendido = new Atendido($cpf, '', '', '', '', $registroGeral, $orgaoEmissor, $dataExpedicao, '', '', '', '', '', '', '', '', '', '', '', '', '', '');

        $atendido->setIdatendido($idatendido);

        $atendidoDAO = new atendidoDAO();
        try {
            $atendidoDAO->alterarDocumentacao($atendido);
            header("Location: ../html/atendido/Profile_Atendido.php?idatendido=" . $idatendido);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function alterarImagem()
    {
        extract($_REQUEST);
        $img = file_get_contents($_FILES['imgperfil']['tmp_name']);
        $atendidoDAO = new AtendidoDAO();
        try {
            $atendidoDAO->alterarImagem($idatendido, $img);
            header("Location: ../html/atendido/Profile_Atendido.php?idatendido=" . $idatendido);
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
        $atendido = new Atendido('', '', '', '', '', '', '', '', '', '', '', '', '', '', $cep, $estado, $cidade, $bairro, $rua, $numero_residencia, $complemento, $ibge);
        $atendido->setIdatendido($idatendido);
        $atendidoDAO = new AtendidoDAO();
        try {
            $atendidoDAO->alterarEndereco($atendido);
            header("Location: ../html/atendido/Profile_Atendido.php?idatendido=" . $idatendido);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function alterarStatus()
    {
        $id = filter_input(INPUT_POST, 'idatendido', FILTER_SANITIZE_NUMBER_INT);
        $operacao = filter_input(INPUT_POST, 'operacao', FILTER_SANITIZE_STRING);

        if (!$id || $id < 1) {
            http_response_code(400);
            echo json_encode(['erro' => 'O id informado não é válido' . " $id"]);
            exit();
        }

        if ($operacao != 'desativar' && $operacao != 'ativar') {
            http_response_code(400);
            echo json_encode(['erro' => 'A operação informada é inválida']);
            exit();
        }

        try {
            $status = null;

            switch ($operacao) {
                case 'desativar':
                    $status = 2;
                    break;
                case 'ativar':
                    $status = 1;
                    break;
            }

            $atendidoDAO = new AtendidoDAO();
            $atendidoDAO->alterarStatus($id, $status);

            header('Location: ./control.php?metodo=listarTodos&nomeClasse=AtendidoControle&nextPage=../html/atendido/Informacao_Atendido.php');
        } catch (PDOException) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro no servidor ao alterar o status do atendido.']);
            exit();
        }
    }
}
