<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once '../model/Socio.php';
require_once '../model/ContribuicaoLogCollection.php';
require_once '../dao/SocioDAO.php';
require_once '../dao/ContribuicaoLogDAO.php';
require_once '../helper/Util.php';
require_once '../dao/ConexaoDAO.php';
require_once '../../../dao/PessoaDAO.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'service' . DIRECTORY_SEPARATOR . 'CaptchaGoogleService.php';

class SocioController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = ConexaoDAO::conectar();
    }

    public function criarSocio()
    {
        $dados = $this->extrairPost();

        try {
            //captcha
            if (!isset($_SESSION['usuario'])) {
                $captchaGoogle = new CaptchaGoogleService();
                if (!$captchaGoogle->validate())
                    throw new InvalidArgumentException('O token do captcha não é válido.', 412);

                $_SESSION['captcha'] = ['validated' => true, 'timeout' => time()+30];
            }

            $pessoaDao = new PessoaDAO($this->pdo);

            $verificacaoExistenciaPessoa = $pessoaDao->verificarExistencia($dados['cpf']);

            $socio = new Socio();

            if (!is_null($verificacaoExistenciaPessoa)) {

                $socio
                    ->setNome($verificacaoExistenciaPessoa->getNome())
                    ->setDataNascimento($verificacaoExistenciaPessoa->getDataNascimento())
                    ->setTelefone($verificacaoExistenciaPessoa->getTelefone())
                    ->setCidade($verificacaoExistenciaPessoa->getCidade())
                    ->setBairro($verificacaoExistenciaPessoa->getBairro())
                    ->setComplemento($verificacaoExistenciaPessoa->getComplemento())
                    ->setCep($verificacaoExistenciaPessoa->getCep())
                    ->setNumeroEndereco($verificacaoExistenciaPessoa->getNumeroEndereco())
                    ->setLogradouro($verificacaoExistenciaPessoa->getLogradouro())
                    ->setDocumento($verificacaoExistenciaPessoa->getCpf())
                    ->setIbge($verificacaoExistenciaPessoa->getIbge());
            } else {
                $socio
                    ->setNome($dados['nome'])
                    ->setDataNascimento($dados['dataNascimento'])
                    ->setTelefone($dados['telefone'])
                    ->setEstado($dados['uf'])
                    ->setCidade($dados['cidade'])
                    ->setBairro($dados['bairro'])
                    ->setComplemento($dados['complemento'])
                    ->setCep($dados['cep'])
                    ->setNumeroEndereco($dados['numero'])
                    ->setLogradouro($dados['rua'])
                    ->setDocumento($dados['cpf'])
                    ->setIbge($dados['ibge']);
            }

            $socio
                ->setEmail($dados['email'])
                ->setValor($dados['valor']);

            $socioDao = new SocioDAO();

            if (!is_null($verificacaoExistenciaPessoa)) {
                $socioDao->criarSocioPessoaPreExistente($socio, $verificacaoExistenciaPessoa->getIdpessoa());
            } else {
                $socioDao->criarSocio($socio);
            }

            http_response_code(200);
            echo json_encode(['mensagem' => 'Sócio criado com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function atualizarSocio()
    {
        try {
            //captcha
            if (!isset($_SESSION['usuario'])) {
                $captchaGoogle = new CaptchaGoogleService();
                if (!$captchaGoogle->validate())
                    throw new InvalidArgumentException('O token do captcha não é válido.', 412);

                $_SESSION['captcha'] = ['validated' => true, 'timeout' => time()+30];
            }

            $dados = $this->extrairPost();
            $socio = new Socio();
            $socio
                ->setNome($dados['nome'])
                ->setDataNascimento($dados['dataNascimento'])
                ->setTelefone($dados['telefone'])
                ->setEmail($dados['email'])
                ->setEstado($dados['uf'])
                ->setCidade($dados['cidade'])
                ->setBairro($dados['bairro'])
                ->setComplemento($dados['complemento'])
                ->setCep($dados['cep'])
                ->setNumeroEndereco($dados['numero'])
                ->setLogradouro($dados['rua'])
                ->setDocumento($dados['cpf'])
                ->setIbge($dados['ibge'])
                ->setValor($dados['valor']);

            $socioDao = new SocioDAO($this->pdo);

            //Verifica se o sócio é um funcionário ou atendido
            if ($socioDao->verificarInternoPorDocumento($socio->getDocumento()))
                throw new LogicException('Você não possui permissão para alterar os dados desse CPF', 403);

            $this->pdo->beginTransaction();
            $socioDao->registrarLogPorDocumento($socio->getDocumento(), 'Atualização recente');

            if (!$socioDao->atualizarSocio($socio)) {
                $this->pdo->rollBack();
                throw new LogicException('Erro ao atualizar sócio no sistema', 500);
            }

            $this->pdo->commit();
            http_response_code(200);
            echo json_encode(['mensagem' => 'Atualizado com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Pega os dados do formulário e retorna um array caso todas as informações passem pelas validações
     */
    function extrairPost()
    {
        //extrair dados da requisição (considerar separar em uma função própria)
        $documento = trim(filter_input(INPUT_POST, 'documento_socio'));
        $nome = trim(filter_input(INPUT_POST, 'nome'));
        $telefone = trim(filter_input(INPUT_POST, 'telefone'));
        $dataNascimento = trim(filter_input(INPUT_POST, 'data_nascimento'));
        $cep = trim(filter_input(INPUT_POST, 'cep'));
        $rua = trim(filter_input(INPUT_POST, 'rua'));
        $bairro = trim(filter_input(INPUT_POST, 'bairro'));
        $uf = trim(filter_input(INPUT_POST, 'uf'));
        $cidade = trim(filter_input(INPUT_POST, 'cidade'));
        $complemento = trim(filter_input(INPUT_POST, 'complemento'));
        $numero = trim(filter_input(INPUT_POST, 'numero'));
        $ibge = trim(filter_input(INPUT_POST, 'ibge'));
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $valor = trim(filter_input(INPUT_POST, 'valor'));

        $opcaoSelecionada = trim(filter_input(INPUT_POST, 'opcao', FILTER_SANITIZE_SPECIAL_CHARS));

        //validar dados (considerar separar em uma função própria)
        try {
            //validação do documento
            if ($opcaoSelecionada == 'fisica' && !Util::validarCPF($documento)) {
                throw new InvalidArgumentException('O CPF informado é inválido', 400);
            } else if ($opcaoSelecionada == 'juridica' && !Util::validaCNPJ($documento)) {
                throw new InvalidArgumentException('O CNPJ informado é inválido', 400);
            } else if ($opcaoSelecionada != 'fisica' && $opcaoSelecionada != 'juridica') {
                throw new InvalidArgumentException('O tipo de sócio selecionado é inválido.', 400);
            }

            //validação do nome
            if (!$nome || strlen($nome) < 3) {
                throw new InvalidArgumentException('O nome informado não pode ser vazio.', 400);
            }

            //validação do telefone
            if (!$telefone) {
                throw new InvalidArgumentException('O telefone não foi informado.', 400);
            } elseif (strlen($telefone) != 14 && strlen($telefone) != 15) {
                throw new InvalidArgumentException('O telefone informado não está no formato correto.', 400);
            } elseif (strlen($telefone) === 15) {
                $celularNumeros = preg_replace('/\D/', '', $telefone);

                if ($celularNumeros[2] != 9) {
                    throw new InvalidArgumentException('O número de celular informado não é válido.', 400);
                }
            }

            //validação da data de nascimento
            $hoje = new DateTime();
            $hoje = $hoje->format('Y-m-d');

            if ($dataNascimento > $hoje) {
                throw new InvalidArgumentException('A data de nascimento não pode ser maior que a data atual.', 400);
            }

            //validação do CEP
            if (!$cep || strlen($cep) != 9) {
                throw new InvalidArgumentException('O CEP informado não está no formato válido.', 400);
            }

            //validação da rua
            if (!$rua || empty($rua)) {
                throw new InvalidArgumentException('A rua informada não pode ser vazia.', 400);
            }

            //validação do bairro
            if (!$bairro || empty($bairro)) {
                throw new InvalidArgumentException('O bairro informado não pode ser vazio.', 400);
            }

            //validação do estado
            if (!$uf || strlen($uf) != 2) {
                throw new InvalidArgumentException('O Estado informada não pode ser vazio.', 400);
            }

            //validação da cidade
            if (!$cidade || empty($cidade)) {
                throw new InvalidArgumentException('A cidade informada não pode ser vazia.', 400);
            }

            //validação do número da residência
            if (!$numero || empty($numero)) {
                throw new InvalidArgumentException('O número da residência informada não pode ser vazio.', 400);
            }

            //validação do email
            if (!$email || empty($email)) {
                throw new InvalidArgumentException('O email informado não está em um formato válido.', 400);
            }

            return [
                'cpf' => $documento,
                'nome' => $nome,
                'telefone' => $telefone,
                'dataNascimento' => $dataNascimento,
                'cep' => $cep,
                'rua' => $rua,
                'bairro' => $bairro,
                'uf' => $uf,
                'cidade' => $cidade,
                'complemento' => $complemento,
                'numero' => $numero,
                'ibge' => $ibge,
                'email' => $email,
                'valor' => $valor,
            ];
        } catch (InvalidArgumentException $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Extraí o documento de um sócio da requisição e retorna os dados pertecentes a esse sócio.
     */
    public function buscarPorDocumento()
    {
        $documento = filter_input(INPUT_GET, 'documento');

        try {
            if (!$documento || empty($documento))
                throw new InvalidArgumentException('O documento informado não é válido.', 400);

            $socioDao = new SocioDAO();
            $socio = $socioDao->buscarPorDocumento($documento);

            if (!$socio || is_null($socio)) {
                echo json_encode(['resultado' => 'Sócio não encontrado']);
                exit();
            }

            echo json_encode(['resultado' => $socio]);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Extraí o documento de um sócio da requisição e retorna a lista dos boletos pertecentes a esse sócio.
     */
    public function exibirBoletosPorCpf()
    {
        // Extrair dados da requisição
        $doc = trim($_GET['documento']);
        $docLimpo = preg_replace('/\D/', '', $doc);

        // Caminho para o diretório de PDFs
        $path = '../pdfs/';

        // Listar arquivos no diretório
        $arrayBoletos = Util::listarArquivos($path);

        if (!$arrayBoletos) {
            $mensagemErro = json_encode(['erro' => 'O diretório de armazenamento de PDFs não existe']);
            echo $mensagemErro;
            exit();
        }

        $boletosEncontrados = [];

        //Pegar coleção de contribuição log
        $contribuicaoLogDao = new ContribuicaoLogDAO();
        $contribuicaoLogCollection = $contribuicaoLogDao->listarPorDocumento($doc);

        foreach ($arrayBoletos as $boleto) {
            // Extrair o documento do nome do arquivo
            $documentoArquivo = explode('_', $boleto)[1];
            if ($documentoArquivo == $docLimpo) {
                $boletosEncontrados[] = $boleto;
            } else if ($contribuicaoLogCollection) {
                $partes = explode('_', $boleto)[0];
                $documentoArquivo = str_replace('-', '_', $partes);
                foreach ($contribuicaoLogCollection as $contribuicaoLog) {
                    if ($documentoArquivo == $contribuicaoLog->getCodigo()) {
                        $boletosEncontrados[] = $boleto;
                    }
                }
            }
        }

        // Retornar JSON com os boletos encontrados
        echo json_encode($boletosEncontrados);
    }
}
