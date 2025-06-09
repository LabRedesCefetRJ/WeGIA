<?php
require_once '../model/Socio.php';
require_once '../model/ContribuicaoLogCollection.php';
require_once '../dao/SocioDAO.php';
require_once '../dao/ContribuicaoLogDAO.php';
require_once '../helper/Util.php';
require_once '../dao/ConexaoDAO.php';
require_once '../../../dao/PessoaDAO.php';

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
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => $e->getMessage()]);
        }
    }

    public function atualizarSocio()
    {
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

        try {
            $socioDao = new SocioDAO($this->pdo);

            //Verifica se o sócio é um funcionário ou atendido
            if ($socioDao->verificarInternoPorDocumento($socio->getDocumento())) {
                http_response_code(403);
                echo json_encode(['erro' => 'Você não possuí permissão para alterar os dados desse CPF']);
                exit();
            }

            $this->pdo->beginTransaction();
            $socioDao->registrarLogPorDocumento($socio->getDocumento(), 'Atualização recente');

            if ($socioDao->atualizarSocio($socio)) {
                $this->pdo->commit();
                http_response_code(200);
                echo json_encode(['mensagem' => 'Atualizado com sucesso!']);
                exit();
            } else {
                $this->pdo->rollBack();
                http_response_code(500);
                echo json_encode(['erro' => 'Erro ao atualizar sócio no sistema']);
                exit();
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => $e->getMessage()]);
            exit();
        }
    }

    /**
     * Pega os dados do formulário e retorna um array caso todas as informações passem pelas validações
     */
    function extrairPost()
    {
        //extrair dados da requisição (considerar separar em uma função própria)
        $cpf = trim(filter_input(INPUT_POST, 'documento_socio'));
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

        $opcaoSelecionada = trim(filter_input(INPUT_POST, 'opcao', FILTER_SANITIZE_STRING));

        //validar dados (considerar separar em uma função própria)
        try {
            //validação do CPF
            require_once('../helper/Util.php');
            $util = new Util();

            if ($opcaoSelecionada == 'fisica' && !$util->validarCPF($cpf)) {
                throw new InvalidArgumentException('O CPF informado não é válido');
            }/*else if ($opcaoSelecionada == 'juridica' && !$util->validarCNPJ($documento)){
                //lançar exceção para CNPJ inválido
            }else{
                //lançar exceção para opção inválida
            }*/

            //validação do nome
            if (!$nome || strlen($nome) < 3) {
                throw new InvalidArgumentException('O nome informado não pode ser vazio');
            }

            //validação do telefone
            if (!$telefone) {
                throw new InvalidArgumentException('O telefone não foi informado.');
            } elseif (strlen($telefone) != 14 && strlen($telefone) != 15) {
                throw new InvalidArgumentException('O telefone informado não está no formato correto.');
            } elseif (strlen($telefone) === 15) {
                $celularNumeros = preg_replace('/\D/', '', $telefone);

                if ($celularNumeros[2] != 9) {
                    throw new InvalidArgumentException('O número de celular informado não é válido.');
                }
            }

            //validação da data de nascimento
            $hoje = new DateTime();
            $hoje = $hoje->format('Y-m-d');

            if ($dataNascimento > $hoje) {
                throw new InvalidArgumentException('A data de nascimento não pode ser maior que a data atual');
            }

            //validação do CEP
            if (!$cep || strlen($cep) != 9) {
                throw new InvalidArgumentException('O CEP informado não está no formato válido');
            }

            //validação da rua
            if (!$rua || empty($rua)) {
                throw new InvalidArgumentException('A rua informada não pode ser vazia');
            }

            //validação do bairro
            if (!$bairro || empty($bairro)) {
                throw new InvalidArgumentException('O bairro informado não pode ser vazio');
            }

            //validação do estado
            if (!$uf || strlen($uf) != 2) {
                throw new InvalidArgumentException('O Estado informada não pode ser vazio');
            }

            //validação da cidade
            if (!$cidade || empty($cidade)) {
                throw new InvalidArgumentException('A cidade informada não pode ser vazia');
            }

            //validação do número da residência
            if (!$numero || empty($numero)) {
                throw new InvalidArgumentException('O número da residência informada não pode ser vazio');
            }

            //validação do email
            if (!$email || empty($email)) {
                throw new InvalidArgumentException('O email informado não está em um formato válido');
            }
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['erro' => $e->getMessage()]);
            exit();
        }

        return [
            'cpf' => $cpf,
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
    }

    /**
     * Extraí o documento de um sócio da requisição e retorna os dados pertecentes a esse sócio.
     */
    public function buscarPorDocumento()
    {
        $documento = filter_input(INPUT_GET, 'documento');

        if (!$documento || empty($documento)) {
            http_response_code(400);
            echo json_encode(['Erro' => 'O documento informado não é válido.']);
            exit;
        }

        try {
            $socioDao = new SocioDAO();
            $socio = $socioDao->buscarPorDocumento($documento);

            if (!$socio || is_null($socio)) {
                echo json_encode(['resultado' => 'Sócio não encontrado']);
                exit;
            }

            //print_r($socio); //Averiguar a melhor maneira de retornar um sócio para o requisitante
            echo json_encode(['resultado' => $socio]);
            exit();
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['Erro' => $e->getMessage()]);
            exit;
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
