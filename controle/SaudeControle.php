<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';

require_once ROOT . '/classes/Saude.php';
require_once ROOT . '/dao/SaudeDAO.php';
require_once 'DescricaoControle.php';
require_once ROOT . '/classes/Documento.php';
require_once ROOT . '/dao/DocumentoDAO.php';
require_once 'DocumentoControle.php';
include_once ROOT . '/classes/Cache.php';
include_once ROOT . "/dao/Conexao.php";

class SaudeControle
{
    public function verificar()
    {
        extract($_REQUEST);

        if ((!isset($texto)) || (empty($texto))) {
            $msg .= "Descricao do paciente não informada. Por favor, informe a descricao!";
            header('Location: ../html/saude/cadastro_ficha_medica.php?msg=' . $msg);
        }
        if ((!isset($cpf)) || (empty($cpf))) {
            $cpf = "";
        }
        if ((!isset($nome)) || (empty($nome))) {
            $nome = "";
        }
        if ((!isset($sobrenome)) || (empty($sobrenome))) {
            $sobrenome = "";
        }
        if ((!isset($sexo)) || (empty($sexo))) {
            $sexo = "";
        }
        if ((!isset($dataNascimento)) || (empty($dataNascimento))) {
            $dataNascimento = "";
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
        // if((!isset($uf)) || empty(($uf))){
        //     $uf = '';
        // }
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
        if ((!isset($complemento)) || (empty($complemento))) {
            $complemento = '';
        }
        if ((!isset($ibge)) || (empty($ibge))) {
            $ibge = '';
        }
        if ((!isset($telefone)) || (empty($telefone))) {
            $telefone = 'null';
        }
        if ((!isset($estado)) || (empty($estado))) {
            $estado = 'null';
        }
        if ((!isset($_SESSION['imagem'])) || (empty($_SESSION['imagem']))) {
            $imagem = '';
        } else {
            $imagem = base64_encode($_SESSION['imagem']);
            unset($_SESSION['imagem']);
        }
        $senha = 'null';
        $saude = new Saude($cpf, $nome, $sobrenome, $sexo, $dataNascimento, $registroGeral, $orgaoEmissor, $dataExpedicao, $nomeMae, $nomePai, $tipoSanguineo, $senha, $telefone, $imagem, $cep, $estado, $cidade, $bairro, $logradouro, $numeroEndereco, $complemento, $ibge);

        // $saude->setNome($nome);
        $saude->setTexto($texto);
        $saude->setEnfermidade($enfermidade);
        $saude->setData_diagnostico($data_diagnostico);
        $saude->setIntStatus($intStatus);
        return $saude;
    }

    /**
     * Atribui à chave 'saude' da sessão o resultado da pesquisa de todos os pacientes registrados no banco de dados da aplicação.
     */
    public function listarTodos()
    {
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));

        $regex = '#^(\.\./html/saude/(administrar_medicamento|informacao_saude|listar_cadastro_intercorrencia|listar_sinais_vitais)\.php)$#';

        $SaudeDAO = new SaudeDAO();
        $pacientes = $SaudeDAO->listarTodos();

        if (session_status() === PHP_SESSION_NONE)
            session_start();

        $_SESSION['saude'] = $pacientes;

        preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . WWW . 'html/home.php');
    }

    public function listarUm()
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));

        $regex = '#^(\.\./html/saude/(aplicar_medicamento|sinais_vitais|cadastrar_intercorrencias|profile_paciente)\.php(\?id_fichamedica=\d+)?)$#';

        $cache = new Cache();
        $infSaude = $cache->read($id);

        if (!$infSaude) {
            try {
                $SaudeDAO = new SaudeDAO();
                $infSaude = $SaudeDAO->listar($id);
                session_start();
                $_SESSION['id_fichamedica'] = $infSaude;
                $cache->save($id, $infSaude, '1 seconds');

                if (preg_match($regex, $nextPage)) {
                    header('Location:' . htmlspecialchars($nextPage));
                } else {
                    header('Location:' . '../html/home.php');
                }
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        } else {
            if (preg_match($regex, $nextPage)) {
                header('Location:' . htmlspecialchars($nextPage));
            } else {
                header('Location:' . '../html/home.php');
            }
        }
    }

    public function alterarImagem()
    {
        extract($_REQUEST);
        $imagem = file_get_contents($_FILES['imgperfil']['tmp_name']);
        $SaudeDAO = new SaudeDAO();
        try {
            $SaudeDAO->alterarImagem($id_fichamedica, $imagem);
            header("Location: ../html/saude/profile_paciente.php?id_fichamedica=" . $id_fichamedica);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Instancia um objeto do tipo Saude que recebe informações do formulário de cadastro e chama os métodos de DAO e Controller necessários para que uma ficha médica nova seja criada.
     */
    public function incluir()
    {
        $saude = $this->verificar();
        $texto_descricao = $saude->getTexto();

        $saudeDao = new SaudeDAO();
        $descricao = new DescricaoControle();

        try {
            $saudeDao->incluir($saude);
            $descricao->incluir($texto_descricao);

            $_SESSION['msg'] = "Ficha médica cadastrada com sucesso!";
            $_SESSION['proxima'] = "Cadastrar outra ficha.";
            $_SESSION['link'] = "../html/saude/cadastro_ficha_medica.php";
            header("Location: ../html/saude/informacao_saude.php");
        } catch (PDOException $e) {
            $msg = "Não foi possível registrar o paciente <form> <input type='button' value='Voltar' onClick='history.go(-1)'> </form>" . "<br>" . $e->getMessage();
            echo $msg;
        }
    }

    public function alterarInfPessoal()
    {
        extract($_REQUEST);
        // $paciente = new Saude('',$nome,$sobrenome,$sexo,$nascimento,'','','','','',$tipoSanguineo,'','',$imagem,'','','','','','','','');
        $paciente = new Saude('', '', '', '', '', '', '', '', '', '', $tipoSanguineo, '', '', '', '', '', '', '', '', '', '', '');
        $paciente->setId_pessoa($id_fichamedica);
        //echo $funcionario->getId_Funcionario();
        $SaudeDAO = new SaudeDAO();
        try {
            $SaudeDAO->alterarInfPessoal($paciente);
            header("Location: ../html/saude/profile_paciente.php?id_fichamedica=" . $id_fichamedica);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Pega as informações do formulário de edição do prontuário e instancia um objeto do tipo DescricaoControle, chamando o método alterarProntuario e passando as informações necessárias, caso a alteração seja bem sucedida redireciona o usuário para a página de exibição das informações do paciente.
     */
    public function alterarProntuario()
    {

        extract($_REQUEST);

        $descricao = new DescricaoControle();
        try {
            $descricao->alterarProntuario($id_fichamedica, $textoProntuario);
            header("Location: ../html/saude/profile_paciente.php?id_fichamedica=" . $id_fichamedica);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Extraí os dados da requisição e instancia um objeto SaudeDAO, em seguida chama a função adicionarProntuarioAoHistorico passando os parâmetros $id_fichamedica e $id_paciente, redireciona o usuário para a página profile_paciente.php e atribuí uma string para a varivável msg da sessão.
     */
    public function adicionarProntuarioAoHistorico()
    {
        extract($_REQUEST);
        session_start();
        $saudeDao = new SaudeDAO();
        try {
            $saudeDao->adicionarProntuarioAoHistorico($id_fichamedica, $id_paciente);
            $_SESSION['msg'] = "Prontuário público adicionado ao histórico com sucesso";
            header("Location: ../html/saude/profile_paciente.php?id_fichamedica=" . $id_fichamedica);
        } catch (Error $e) {
            $erro = $e->getMessage();
            $_SESSION['msg'] = "Ops! Ocorreu o seguinte erro ao tentar inserir o prontuário público: $erro";
            header("Location: ../html/saude/profile_paciente.php?id_fichamedica=" . $id_fichamedica);
        }
    }

    /**
     * Recebe como parâmetro o id de um paciente, instancia um objeto do tipo SaudeDAO e chama o método listarProntuariosDoHistorico passando o id do paciente informado, em caso de sucesso retorna os prontuários do histórico e em caso de falha da um echo na mensagem do erro.
     */
    public function listarProntuariosDoHistorico($idPaciente)
    {
        $saudeDao = new SaudeDAO();

        try {
            $prontuariosHistorico = $saudeDao->listarProntuariosDoHistorico($idPaciente);
            return $prontuariosHistorico;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Pode receber ou não o id de uma ficha médica do histórico, em caso negativo pega a informação do 'idHistorico' da requisição do tipo GET que o chamou. 
     * 
     * Instancia um objeto do tipo SaudeDAO e chama o método listarDescricoesHistoricoPorId passando o id da ficha médica do histórico, em caso de sucesso faz um echo do JSON das descrições, em caso de falha da um echo na mensagem do erro.
     */
    public function listarProntuarioHistoricoPorId($idHistorico = -1)
    {
        header('Content-Type: application/json');

        if ($idHistorico == -1) {
            $idHistorico = $_GET['idHistorico'];
        }

        try {
            $saudeDao = new SaudeDAO();
            $descricoes = $saudeDao->listarDescricoesHistoricoPorId($idHistorico);
            echo json_encode($descricoes);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
