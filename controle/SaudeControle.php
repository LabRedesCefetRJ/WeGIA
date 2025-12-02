<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

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
        try{
            $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));

            $regex = '#^(\.\./html/saude/(administrar_medicamento|informacao_saude|listar_cadastro_intercorrencia|listar_sinais_vitais)\.php)$#';

            $SaudeDAO = new SaudeDAO();
            $pacientes = $SaudeDAO->listarTodos();

            $_SESSION['saude'] = $pacientes;

            preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . WWW . 'html/home.php');
        }catch(PDOException $e){
            Util::tratarException($e);
        }
    }

    

    public function listarUm()
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));

        $regex = '#^(\.\./html/saude/(aplicar_medicamento|sinais_vitais|cadastrar_intercorrencias|profile_paciente)\.php(\?id_fichamedica=\d+)?)$#';

        try {
            $cache = new Cache();
            $infSaude = $cache->read($id);

            if (!$infSaude) {
                $SaudeDAO = new SaudeDAO();
                $infSaude = $SaudeDAO->listar($id);
                $_SESSION['id_fichamedica'] = $infSaude;
                $cache->save($id, $infSaude, '1 seconds');
            }

            preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . '../html/home.php');
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function alterarImagem()
    {
        $id_fichamedica = filter_input(INPUT_POST, 'id_fichamedica', FILTER_SANITIZE_NUMBER_INT);
        try {
            if (!$id_fichamedica || $id_fichamedica < 1)
                throw new InvalidArgumentException('O id da ficha médica informado é inválido.', 422);

            $imagem = file_get_contents($_FILES['imgperfil']['tmp_name']);
            $SaudeDAO = new SaudeDAO();

            $SaudeDAO->alterarImagem($id_fichamedica, $imagem);
            header("Location: ../html/saude/profile_paciente.php?id_fichamedica=" . htmlspecialchars($id_fichamedica));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Instancia um objeto do tipo Saude que recebe informações do formulário de cadastro e chama os métodos de DAO e Controller necessários para que uma ficha médica nova seja criada.
     */
    public function incluir()
    {
        try {
            $saude = $this->verificar();
            $texto_descricao = $saude->getTexto();

            $saudeDao = new SaudeDAO();
            $descricao = new DescricaoControle();

            $saudeDao->incluir($saude);
            $descricao->incluir($texto_descricao);

            $_SESSION['msg'] = "Ficha médica cadastrada com sucesso!";
            $_SESSION['proxima'] = "Cadastrar outra ficha.";
            $_SESSION['link'] = "../html/saude/cadastro_ficha_medica.php";
            header("Location: ../html/saude/informacao_saude.php");
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function alterarInfPessoal()
    {
        $tipoSanguineo = filter_input(INPUT_POST, 'tipoSanguineo', FILTER_SANITIZE_SPECIAL_CHARS);
        $idFichamedica = filter_input(INPUT_POST, 'id_fichamedica', FILTER_SANITIZE_SPECIAL_CHARS);

        try {
            if (!$idFichamedica || $idFichamedica < 1)
                throw new InvalidArgumentException('O id da ficha médica fornecido é inválido.', 422);

            $paciente = new Saude('', '', '', '', '', '', '', '', '', '', $tipoSanguineo, '', '', '', '', '', '', '', '', '', '', '');
            $paciente->setId_pessoa($idFichamedica);
            $SaudeDAO = new SaudeDAO();

            $SaudeDAO->alterarInfPessoal($paciente);
            header("Location: ../html/saude/profile_paciente.php?id_fichamedica=" . htmlspecialchars($idFichamedica));
        } catch (PDOException $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Pega as informações do formulário de edição do prontuário e instancia um objeto do tipo DescricaoControle, chamando o método alterarProntuario e passando as informações necessárias, caso a alteração seja bem sucedida redireciona o usuário para a página de exibição das informações do paciente.
     */
    public function alterarProntuario()
    {
        $idFichamedica = filter_input(INPUT_POST, 'id_fichamedica', FILTER_SANITIZE_NUMBER_INT);
        $textoProntuario = trim($_POST['textoProntuario']);

        try {
            if (!$idFichamedica || $idFichamedica < 1)
                throw new InvalidArgumentException('O id da ficha médica fornecido é inválido.', 422);

            $descricao = new DescricaoControle();

            $descricao->alterarProntuario($idFichamedica, $textoProntuario);
            header("Location: ../html/saude/profile_paciente.php?id_fichamedica=" . htmlspecialchars($idFichamedica));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Extraí os dados da requisição e instancia um objeto SaudeDAO, em seguida chama a função adicionarProntuarioAoHistorico passando os parâmetros $id_fichamedica e $id_paciente, redireciona o usuário para a página profile_paciente.php e atribuí uma string para a varivável msg da sessão.
     */
    public function adicionarProntuarioAoHistorico()
    {
        $idFichamedica = filter_input(INPUT_POST, 'id_fichamedica', FILTER_SANITIZE_NUMBER_INT);
        $idPaciente = filter_input(INPUT_POST, 'id_paciente', FILTER_SANITIZE_NUMBER_INT);

        try {
            if (!$idFichamedica || $idFichamedica < 1)
                throw new InvalidArgumentException('O id da ficha médica fornecido é inválido.', 422);

            if (!$idPaciente || $idPaciente < 1)
                throw new InvalidArgumentException('O id do paciente fornecido é inválido.', 422);

            $saudeDao = new SaudeDAO();
            $saudeDao->adicionarProntuarioAoHistorico($idFichamedica, $idPaciente);
            $_SESSION['msg'] = "Prontuário público adicionado ao histórico com sucesso";
        } catch (Exception $e) {
            Util::tratarException($e);
            $e instanceof PDOException ? $_SESSION['msg'] = 'Erro ao manipular o banco de dados da aplicação.' : $_SESSION['msg'] = "Ops! Ocorreu o seguinte erro ao tentar inserir o prontuário público: $e";
        }

        header("Location: ../html/saude/profile_paciente.php?id_fichamedica=" . htmlspecialchars($idFichamedica));
    }

    /**
     * Recebe como parâmetro o id de um paciente, instancia um objeto do tipo SaudeDAO e chama o método listarProntuariosDoHistorico passando o id do paciente informado, em caso de sucesso retorna os prontuários do histórico e em caso de falha da um echo na mensagem do erro.
     */
    public function listarProntuariosDoHistorico(int $idPaciente)
    {
        try {
            if (!$idPaciente || $idPaciente < 1)
                throw new InvalidArgumentException('O id do paciente fornecido é inválido.', 422);

            $saudeDao = new SaudeDAO();
            $prontuariosHistorico = $saudeDao->listarProntuariosDoHistorico($idPaciente);
            return $prontuariosHistorico;
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Pode receber ou não o id de uma ficha médica do histórico, em caso negativo pega a informação do 'idHistorico' da requisição do tipo GET que o chamou. 
     * 
     * Instancia um objeto do tipo SaudeDAO e chama o método listarDescricoesHistoricoPorId passando o id da ficha médica do histórico, em caso de sucesso faz um echo do JSON das descrições, em caso de falha da um echo na mensagem do erro.
     */
    public function listarProntuarioHistoricoPorId(?int $idHistorico = null)
    {
        header('Content-Type: application/json');

        if(is_null($idHistorico))
            $idHistorico = filter_input(INPUT_GET, 'idHistorico', FILTER_SANITIZE_NUMBER_INT);

        try {
            if(!$idHistorico || $idHistorico < 1)
                throw new InvalidArgumentException('O id do histórico fornecido é inválido.', 422);

            $saudeDao = new SaudeDAO();
            $descricoes = $saudeDao->listarDescricoesHistoricoPorId($idHistorico);
            echo json_encode($descricoes);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
