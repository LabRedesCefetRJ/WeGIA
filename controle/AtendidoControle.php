<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';

require_once ROOT . '/classes/Atendido.php';
require_once ROOT . '/dao/AtendidoDAO.php';
require_once ROOT . '/classes/Documento.php';
require_once ROOT . '/dao/DocumentoDAO.php';
require_once ROOT . '/controle/DocumentoControle.php';
include_once ROOT . '/classes/Cache.php';
require_once ROOT . '/classes/Util.php';
include_once ROOT . "/dao/Conexao.php";

class AtendidoControle
{

    public function formatoDataYMD($data)
    {
        $data_arr = explode("/", $data);

        $datac = $data_arr[2] . '-' . $data_arr[1] . '-' . $data_arr[0];

        return $datac;
    }

    /**
     * Extrai os dados da requisição e realiza a validação dos seus valores
     */
    public function verificar()
    {
        extract($_REQUEST);
        if ((!isset($cpf) || empty($cpf)) && (!isset($semCpf) || $semCpf = '0')) {
            $msg .= "cpf do atendido não informado. Por favor, informe o cpf!";
            header('Location: ../html/atendido/Cadastro_Atendido.php?msg=' . $msg);
            exit();
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
            exit();
        }
        if ((!isset($nascimento) || empty($nascimento)) && (!isset($semCpf) || $semCpf = '0')) {
            $msg .= "Nascimento do atendido não informado. Por favor, informe a data!";
            header('Location: ../html/atendido/Cadastro_Atendido.php?msg=' . $msg);
            exit();
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

        if ((!isset($_SESSION['imagem'])) || (empty($_SESSION['imagem']))) {
            $imgperfil = '';
        } else {
            $imgperfil = base64_encode($_SESSION['imagem']);
            unset($_SESSION['imagem']);
        }

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

        if ((!isset($_SESSION['imagem'])) || (empty($_SESSION['imagem']))) {
            $imgperfil = '';
        } else {
            $imgperfil = base64_encode($_SESSION['imagem']);
            unset($_SESSION['imagem']);
        }

        $senha = 'null';
        $atendido = new Atendido($cpf, $nome, $sobrenome, $sexo, $nascimento, $registroGeral, $orgaoEmissor, $dataExpedicao, $nomeMae, $nomePai, $tipoSanguineo, $senha, $telefone, $imgperfil, $cep, $uf, $cidade, $bairro, $logradouro, $numeroEndereco, $complemento, $ibge);
        $atendido->setIntTipo($intTipo);
        $atendido->setIntStatus($intStatus);
        return $atendido;
    }

    /**
     * Insere na chave 'atendidos' da variável de sessão todos os atendidos registrados no banco de dados da aplicação
     */
    public function listarTodos()
    {
        $status = filter_input(INPUT_GET, 'select_status', FILTER_SANITIZE_NUMBER_INT);

        try {
            if (isset($status) && $status < 1)
                throw new InvalidArgumentException('O id do status fornecido não é válido.', 412);

            $AtendidoDAO = new AtendidoDAO();
            $atendidos = $AtendidoDAO->listarTodos($status);

            $_SESSION['atendidos'] = $atendidos;

            if (isset($_GET['nextPage'])) {
                $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
                $regex = '#^((\.\./|' . WWW . ')html/atendido/(Informacao_Atendido|cadastro_ocorrencia|listar_ocorrencias_ativas)\.php)$#';
                preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . '../html/home.php');
            }
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarTodos2()
    {
        extract($_REQUEST);
        try {
            $AtendidoDAO = new AtendidoDAO();
            $atendidos = $AtendidoDAO->listarTodos2();

            $_SESSION['atendidos2'] = $atendidos;
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Atribui a chave 'atendido' do array da variável de sessão as informações de um atendido.
     */
    public function listarUm()
    {
        require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/atendido/Profile_Atendido\.php(\?id=\d+|\?idatendido=\d+(\&id=\d+)?)?)$#';

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        try {
            if (!$id || $id < 1)
                throw new InvalidArgumentException('O id fornecido é inválido.', 400);

            $cache = new Cache();
            $infAtendido = $cache->read($id);
            if (!$infAtendido) {
                $AtendidoDAO = new AtendidoDAO();
                $infAtendido = $AtendidoDAO->listar($id);

                $_SESSION['atendido'] = $infAtendido;
                $cache->save($id, $infAtendido, '15 seconds');
            }
            preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . '../html/home.php');
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**Atribui a chave 'cpf_atendido' do array da variável de sessão os valores dos CPF's dos atendidos registrados no sistema */
    public function listarCpf()
    {
        try {
            $atendidosDAO = new AtendidoDAO();
            $atendidoscpf = $atendidosDAO->listarcpf();

            $_SESSION['cpf_atendido'] = $atendidoscpf;
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Recebe como parâmetro a string de um documento e realiza o processo necessário para retornar a sua compressão
     */
    public function comprimir(string $documento)
    {
        try {
            if (empty($documento) || strlen($documento))
                throw new InvalidArgumentException('Não é possível comprimir um documento vazio.', 400);

            $documentoZip = gzcompress($documento);

            if ($documentoZip === false)
                throw new LogicException('Falha ao comprimir o documento informado.', 500);

            return $documentoZip;
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function selecionarCadastro()
    {
        $cpf = $_GET['cpf'];
        $validador = new Util();

        try {
            if (!$validador->validarCPF($cpf))
                throw new InvalidArgumentException('Erro, o CPF informado não é válido', 400);

            $atendido = new AtendidoDAO();
            $atendido->selecionarCadastro($cpf);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function incluir()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $atendido  = $this->verificar();
            $cpf       = $_GET['cpf'] ?? '';
            $cpf       = trim($cpf);
            $validador = new Util();
            $pdo       = Conexao::connect();

            if (!empty($cpf)) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM pessoa WHERE cpf = ?");
                $stmt->execute([$cpf]);
                $count = $stmt->fetchColumn();

                if ($count > 0) {
                    throw new InvalidArgumentException('Erro: CPF já cadastrado no sistema.', 400);
                }

                if (!$validador->validarCPF($cpf)) {
                    throw new InvalidArgumentException('Erro, o CPF informado não é válido', 400);
                }
            }

            $dataNascimento = $atendido->getDataNascimento();
            if (!empty($dataNascimento)) {
                if (
                    $dataNascimento > Atendido::getDataNascimentoMaxima() ||
                    $dataNascimento < Atendido::getDataNascimentoMinima()
                ) {
                    throw new InvalidArgumentException(
                        'Erro, a data de nascimento informada está fora dos limites permitidos.',
                        400
                    );
                }
            }

            $intDAO     = new AtendidoDAO();
            $idAtendido = $intDAO->incluir($atendido, $cpf);

            $_SESSION['msg']  = "Atendido cadastrado com sucesso";
            $_SESSION['tipo'] = "success";

            header("Location: ../html/atendido/Profile_Atendido.php?idatendido=" . (int)$idAtendido);
            exit;
        } catch (PDOException $e) {
            Util::tratarException($e);
        }
    }


    public function incluirSemCpf()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $atendido = $this->verificar();
            $cpf      = null;

            $intDAO     = new AtendidoDAO();
            $idAtendido = $intDAO->incluir($atendido, $cpf);

            $_SESSION['msg']  = "Atendido cadastrado sem CPF com sucesso";
            $_SESSION['tipo'] = "success";

            header("Location: ../html/atendido/Profile_Atendido.php?idatendido=" . (int)$idAtendido);
            exit();
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }





    public function incluirExistente()
    {
        $atendido = $this->verificarExistente();
        $idPessoa = $_GET['id_pessoa'];
        $sobrenome = $_GET['sobrenome'];

        try {
            $atendidoDAO = new AtendidoDAO();
            $atendidoDAO->incluirExistente($atendido, $idPessoa, $sobrenome);

            $_SESSION['msg'] = "Atendido cadastrado com sucesso";
            $_SESSION['proxima'] = "Cadastrar outro atendido";
            $_SESSION['link'] = "../html/atendido/cadastro_atendido.php";

            header("Location: ../html/atendido/Informacao_Atendido.php");
            exit;
        } catch (RuntimeException $e) {
            $_SESSION['mensagem_erro'] = $e->getMessage();
            header("Location: ../html/atendido/processo_aceitacao.php");
            exit;
        } catch (PDOException $e) {
            Util::tratarException($e);
            exit;
        } catch (Exception $e) {
            Util::tratarException($e);
            exit;
        }
    }


    public function alterar()
    {
        extract($_REQUEST);
        try {
            $atendido = $this->verificar();
            $atendido->setidatendido($idatendido);
            $AtendidoDAO = new AtendidoDAO();

            $AtendidoDAO->alterar($atendido);
            header("Location: ../html/Profile_Atendido.php?id=" . htmlspecialchars($idatendido));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function excluir()
    {
        extract($_REQUEST);
        try {
            $AtendidoDAO = new AtendidoDAO();

            $AtendidoDAO->excluir($idatendido);
            header("Location:../controle/control.php?metodo=listarTodos&nomeClasse=AtendidoControle&nextPage=../html/atendido/Informacao_Atendido.php");
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function alterarInfPessoal()
    {
        extract($_REQUEST);

        $idatendido = filter_var($idatendido ?? 0, FILTER_VALIDATE_INT);
        if (!$idatendido || $idatendido < 1) {
            $_SESSION['msg'] = "ID do atendido inválido!";
            $_SESSION['tipo'] = "error";
            header("Location: ../html/atendido/Profile_Atendido.php?idatendido=0");
            exit;
        }

        try {
            $pdo = Conexao::connect();

            if (!empty($nascimento)) {
                $sql_expedicao = "SELECT p.data_expedicao FROM atendido a JOIN pessoa p ON a.pessoa_id_pessoa = p.id_pessoa WHERE a.idatendido = :idatendido";
                $stmt_expedicao = $pdo->prepare($sql_expedicao);
                $stmt_expedicao->bindParam(':idatendido', $idatendido, PDO::PARAM_INT);
                $stmt_expedicao->execute();
                $atendido_doc = $stmt_expedicao->fetch(PDO::FETCH_ASSOC);

                if ($atendido_doc && !empty($atendido_doc['data_expedicao'])) {
                    try {
                        $data_nascimento_obj = new DateTime($nascimento);
                        $data_expedicao_obj = new DateTime($atendido_doc['data_expedicao']);

                        if ($data_nascimento_obj > $data_expedicao_obj) {
                            $_SESSION['msg'] = "Erro: Data de nascimento posterior à expedição do documento!";
                            $_SESSION['tipo'] = "error";
                            header("Location: ../html/atendido/Profile_Atendido.php?idatendido=" . $idatendido);
                            exit;
                        }
                    } catch (Exception $e) {
                        error_log("Erro DateTime validação: " . $e->getMessage());
                        $_SESSION['msg'] = "Formato de data inválido!";
                        $_SESSION['tipo'] = "error";
                        header("Location: ../html/atendido/Profile_Atendido.php?idatendido=" . $idatendido);
                        exit;
                    }
                }
            }

            $cpf = trim($_POST['cpf'] ?? '');
            if (!empty($cpf)) {
                $sql_cpf_atual = "SELECT p.cpf FROM atendido a JOIN pessoa p ON a.pessoa_id_pessoa = p.id_pessoa WHERE a.idatendido = :idatendido";
                $stmt_cpf = $pdo->prepare($sql_cpf_atual);
                $stmt_cpf->bindParam(':idatendido', $idatendido, PDO::PARAM_INT);
                $stmt_cpf->execute();
                $cpfAtual = $stmt_cpf->fetchColumn();

                if ($cpfAtual !== null && $cpfAtual !== '') {
                    $_POST['cpf'] = '';
                } else {
                    $validador = new Util();
                    if (!$validador->validarCPF($cpf)) {
                        throw new InvalidArgumentException('CPF inválido', 400);
                    }

                    $stmt_unico = $pdo->prepare("SELECT COUNT(*) FROM pessoa WHERE cpf = ? AND id_pessoa != (SELECT pessoa_id_pessoa FROM atendido WHERE idatendido = ?)");
                    $stmt_unico->execute([$cpf, $idatendido]);
                    if ($stmt_unico->fetchColumn() > 0) {
                        throw new InvalidArgumentException('CPF já cadastrado em outro atendido', 400);
                    }
                }
            }

            $campos = ['cpf', 'nome', 'sobrenome', 'sexo', 'data_nascimento', 'telefone', 'nome_mae', 'nome_pai', 'tipo_sanguineo'];
            $setClause = [];
            $params = [':idatendido' => $idatendido];

            foreach ($campos as $campo) {
                if (isset($_POST[$campo]) && $_POST[$campo] !== '') {
                    $setClause[] = "p.`$campo` = :" . $campo;
                    $params[":$campo"] = $_POST[$campo];
                }
            }

            if (empty($setClause)) {
                $_SESSION['msg'] = "Nenhum dado para atualizar!";
                header("Location: ../html/atendido/Profile_Atendido.php?idatendido=" . $idatendido);
                exit;
            }

            $sql_update = "
            UPDATE pessoa p 
            JOIN atendido a ON p.id_pessoa = a.pessoa_id_pessoa 
            SET " . implode(', ', $setClause) . " 
            WHERE a.idatendido = :idatendido
        ";

            $stmt = $pdo->prepare($sql_update);
            $stmt->execute($params);

            $_SESSION['msg'] = "Informações pessoais atualizadas com sucesso!";
            $_SESSION['tipo'] = "success";
            header("Location: ../html/atendido/Profile_Atendido.php?idatendido=" . $idatendido);
            exit;
        } catch (PDOException $e) {
            error_log("Erro DAO alterarInfPessoal: " . $e->getMessage());
            $_SESSION['msg'] = "Erro no banco de dados: " . $e->getMessage();
            $_SESSION['tipo'] = "error";
            header("Location: ../html/atendido/Profile_Atendido.php?idatendido=" . $idatendido);
            exit;
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }




    public function alterarDocumentacao()
    {
        extract($_REQUEST);
        try {
            if ($dataExpedicao && $idatendido) {
                $pdo = Conexao::connect();
                $sql_nascimento = "SELECT p.data_nascimento FROM atendido a JOIN pessoa p ON a.pessoa_id_pessoa = p.id_pessoa WHERE a.idatendido = :idatendido";
                $stmt_nascimento = $pdo->prepare($sql_nascimento);
                $stmt_nascimento->bindParam(':idatendido', $idatendido);
                $stmt_nascimento->execute();
                $atendido_data = $stmt_nascimento->fetch(PDO::FETCH_ASSOC);

                if ($atendido_data && $atendido_data['data_nascimento']) {
                    $data_nascimento = new DateTime($atendido_data['data_nascimento']);
                    $data_expedicao_obj = new DateTime($dataExpedicao);
                    if ($data_expedicao_obj <= $data_nascimento)
                        throw new InvalidArgumentException('A data de expedição do documento não pode ser anterior ou igual à data de nascimento!', 400);
                }
            }

            $pdo = Conexao::connect();
            $sql_atual = "SELECT cpf, sexo, registro_geral, orgao_emissor, data_expedicao, telefone 
                      FROM pessoa p 
                      JOIN atendido a ON p.id_pessoa = a.pessoa_id_pessoa 
                      WHERE a.idatendido = :idatendido";
            $stmt_atual = $pdo->prepare($sql_atual);
            $stmt_atual->bindParam(':idatendido', $idatendido);
            $stmt_atual->execute();
            $dados_atuais = $stmt_atual->fetch(PDO::FETCH_ASSOC);

            $cpf_final = !empty($cpf) ? $cpf : $dados_atuais['cpf'];
            $sexo_final = $dados_atuais['sexo'];
            $telefone = $dados_atuais['telefone'] ?? '';

            $atendido = new Atendido(
                $cpf_final,
                '',
                '',
                $sexo_final,
                '',
                $registroGeral ?: $dados_atuais['registro_geral'],
                $orgaoEmissor ?: $dados_atuais['orgao_emissor'],
                $dataExpedicao ?: $dados_atuais['data_expedicao'],
                '',
                '',
                '',
                'null',
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

            $atendido->setIdatendido($idatendido);
            $atendidoDAO = new AtendidoDAO();
            $atendidoDAO->alterarDocumentacao($atendido);

            $_SESSION['msg'] = "Documentação atualizada com sucesso!";
            $_SESSION['tipo'] = "success";
            header("Location: ../html/atendido/Profile_Atendido.php?idatendido=" . htmlspecialchars($idatendido));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }



    public function alterarImagem()
    {
        extract($_REQUEST);
        try {
            if (!$idatendido || $idatendido < 1)
                throw new InvalidArgumentException('O id do atendido informado não é válido.', 412);

            $img = file_get_contents($_FILES['imgperfil']['tmp_name']);
            $atendidoDAO = new AtendidoDAO();

            $atendidoDAO->alterarImagem($idatendido, $img);
            header("Location: ../html/atendido/Profile_Atendido.php?idatendido=" . htmlspecialchars($idatendido));
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
        try {
            $atendido = new Atendido('', '', '', '', '', '', '', '', '', '', '', '', '', '', $cep, $estado, $cidade, $bairro, $rua, $numero_residencia, $complemento, $ibge);
            $atendido->setIdatendido($idatendido);
            $atendidoDAO = new AtendidoDAO();

            $atendidoDAO->alterarEndereco($atendido);
            header("Location: ../html/atendido/Profile_Atendido.php?idatendido=" . htmlspecialchars($idatendido));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function alterarStatus()
    {
        $id = filter_input(INPUT_POST, 'idatendido', FILTER_SANITIZE_NUMBER_INT);
        $operacao = filter_input(INPUT_POST, 'operacao', FILTER_SANITIZE_SPECIAL_CHARS);

        try {
            if (!$id || $id < 1)
                throw new InvalidArgumentException('O id informado não é válido.', 412);

            if ($operacao != 'desativar' && $operacao != 'ativar')
                throw new InvalidArgumentException('A operação informada é inválida.', 412);

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
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
