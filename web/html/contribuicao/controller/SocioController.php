<?php
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'sessaoCookie.php';
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once '../model/Socio.php';
require_once '../model/ContribuicaoLogCollection.php';
require_once '../dao/SocioDAO.php';
require_once '../dao/ContribuicaoLogDAO.php';
require_once '../dao/ConexaoDAO.php';
require_once '../../../dao/PessoaDAO.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'service' . DIRECTORY_SEPARATOR . 'CaptchaGoogleService.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Cache.php';

class SocioController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = ConexaoDAO::conectar();
    }

    public function criarSocio()
    {
        try {
            // A confirmação de captcha acontece em verificarCadastroSocio()
            // (confirmação do CPF) e é reaproveitada uma única vez em
            // ContribuicaoLogController/RecorrenciaController (confirmação
            // do pagamento) — esse método fica no meio do caminho e não
            // deve consumir nem rearmar essa sessão, só conferir que ela existe.
            $this->aplicarRateLimitPublico('criarSocio');
            $this->exigirCpfConfirmadoPorCaptcha(trim((string) filter_input(INPUT_POST, 'documento_socio')));

            $pessoaDao = new PessoaDAO($this->pdo);

            $verificacaoExistenciaPessoa = $pessoaDao->verificarExistencia(trim(filter_input(INPUT_POST, 'documento_socio')));

            $socio = new Socio();
            $camposInformados = [];

            if (!is_null($verificacaoExistenciaPessoa)) {

                $socio = $this->socioAPartirDaPessoa($verificacaoExistenciaPessoa)
                    ->setValor(trim(filter_input(INPUT_POST, 'valor')))
                    ->setTags($this->extrairTagsPost());

                // Fluxo público: a pessoa já existe, mas pode estar sem dados
                // obrigatórios (ex: e-mail, sem o qual não recebe o pagamento).
                // Só os campos vazios são aceitos do formulário — os já
                // preenchidos não podem ser trocados por aqui. Validados antes
                // de criar o sócio, pra não deixar um cadastro pela metade.
                //
                // Funcionário/atendido não pode ter o cadastro alterado pela
                // rota pública (ex: e-mail trocado por terceiro). Os campos
                // enviados são só ignorados, sem erro, pra a resposta não
                // revelar que o CPF pertence a alguém interno da instituição.
                if (!isset($_SESSION['usuario']) && !(new SocioDAO($this->pdo))->verificarInternoPorDocumento($verificacaoExistenciaPessoa->getCpf())) {
                    $camposInformados = $this->extrairCamposFaltantesPost($this->camposFaltantesPessoa($verificacaoExistenciaPessoa));
                }
            } else {
                $dados = $this->extrairPost();

                $socio
                    ->setNome($dados['nome'])
                    ->setSobrenome($dados['sobrenome'])
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
                    ->setIbge($dados['ibge'])
                    ->setEmail($dados['email'])
                    ->setValor($dados['valor'])
                    ->setTags($dados['tags']);
            }

            $socioDao = new SocioDAO($this->pdo);

            if (!is_null($verificacaoExistenciaPessoa)) {
                $socioDao->criarSocioPessoaPreExistente($socio, $verificacaoExistenciaPessoa->getIdpessoa());

                // criarSocioPessoaPreExistente() só cria a linha de sócio; os
                // campos que faltavam vão para a pessoa. atualizarSocio()
                // ignora tudo que vier null (inclusive valor e tags).
                if (!empty($camposInformados)) {
                    $socioCompletado = $this->aplicarCamposFaltantes(
                        (new Socio())->setDocumento($socio->getDocumento()),
                        $camposInformados
                    );
                    $socioDao->atualizarSocio($socioCompletado);
                }
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

                $_SESSION['captcha'] = ['validated' => true, 'timeout' => time() + 30];
            }

            $dados = $this->extrairPost();
            $socio = new Socio();
            $socio
                ->setNome($dados['nome'])
                ->setSobrenome($dados['sobrenome'])
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
                ->setIbge($dados['ibge'])
                ->setValor($dados['valor'])
                ->setTags($dados['tags']);

            $socioDao = new SocioDAO($this->pdo);

            //Verifica se o sócio é um funcionário ou atendido
            if ($socioDao->verificarInternoPorDocumento($socio->getDocumento()))
                throw new LogicException('Você não possui permissão para alterar os dados desse CPF', 403);

            $this->pdo->beginTransaction();
            $socioDao->registrarLogPorDocumento($socio->getDocumento(), 'Atualização recente', Util::getUserIp(), Util::getUserAgent());

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
        $documento = trim(filter_input(INPUT_POST, 'documento_socio'));
        $nome = trim(filter_input(INPUT_POST, 'nome'));
        $sobrenome = trim(filter_input(INPUT_POST, 'sobrenome'));
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
        $email = isset($_POST['email']) ? trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)) : null;
        $valor = trim(filter_input(INPUT_POST, 'valor'));

        $opcaoSelecionada = trim(filter_input(INPUT_POST, 'opcao', FILTER_SANITIZE_SPECIAL_CHARS));
        $method = trim(filter_input(INPUT_POST, 'metodo', FILTER_SANITIZE_SPECIAL_CHARS));

        //validar dados (considerar separar em uma função própria)
        try {
            $tags = $this->extrairTagsPost();

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
            if ($method != 'atualizarSocio' && (!$email || empty($email))) {
                throw new InvalidArgumentException('O email informado não está em um formato válido.', 400);
            }

            return [
                'cpf' => $documento,
                'nome' => $nome,
                'sobrenome' => $sobrenome,
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
                'tags' => $tags,
            ];
        } catch (InvalidArgumentException $e) {
            Util::tratarException($e);
        }
    }

    private function extrairTagsPost(): array
    {
        $tags = $_POST['tags'] ?? $_POST['tags_ids'] ?? $_POST['id_sociotag'] ?? [];

        if (is_string($tags)) {
            $tags = trim($tags);

            if ($tags === '') {
                return [];
            }

            $jsonTags = json_decode($tags, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($jsonTags)) {
                $tags = $jsonTags;
            } else {
                $tags = explode(',', $tags);
            }
        }

        if (!is_array($tags)) {
            throw new InvalidArgumentException('As tags informadas são inválidas.', 400);
        }

        $tagsNormalizadas = [];

        foreach ($tags as $tag) {
            if ($tag === null || $tag === '') {
                continue;
            }

            if (!is_numeric($tag) || (int) $tag < 1) {
                throw new InvalidArgumentException('Todas as tags devem ser ids inteiros maiores que 0.', 400);
            }

            $tagId = (int) $tag;
            $tagsNormalizadas[$tagId] = $tagId;
        }

        return array_values($tagsNormalizadas);
    }

    /**
     * Extraí o documento de um sócio da requisição e retorna os dados pertecentes a esse sócio.
     */
    public function buscarPorDocumento() //<-- Verificar trechos de código que usam esse método
    {
        $documento = filter_input(INPUT_GET, 'documento');

        try {
            // Hoje a rota é privada (control.php exige login), então o limite
            // não se aplica; fica como proteção caso volte a ser pública.
            $this->aplicarRateLimitPublico('buscarPorDocumento');

            if (!$documento || empty($documento))
                throw new InvalidArgumentException('O documento informado é inválido.', 400);

            $socioDao = new SocioDAO();
            $socio = $socioDao->buscarPorDocumento($documento);

            if (!$socio || is_null($socio)) {
                http_response_code(404);

                //informar se existe uma pessoa
                $pessoaDao = new PessoaDAO($this->pdo);
                $pessoaExists = $pessoaDao->verificarExistencia($documento);

                echo json_encode([
                    'resultado' => 'Sócio não encontrado',
                    'pessoaExists' => $pessoaExists instanceof PessoaDTOSocio ? true : false,
                ]);

                exit();
            }

            echo json_encode(['resultado' => $socio]);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Campos obrigatórios de um cadastro de sócio para fins de cobrança.
     * Mesmo conjunto exigido em extrairPost()/extrairCamposFaltantesPost().
     *
     * O e-mail entra na lista porque o fluxo público só entrega o link do
     * pagamento por e-mail: um sócio sem e-mail pode informá-lo pelo
     * completarCadastroSocio(). Como essa rota só aceita os campos que estão
     * faltando, um e-mail já cadastrado não pode ser trocado por ela.
     */
    private const CAMPOS_OBRIGATORIOS_SOCIO = ['nome', 'email', 'telefone', 'cep', 'rua', 'bairro', 'uf', 'cidade', 'numero'];

    /**
     * Monta um Socio com os dados de uma pessoa já cadastrada no sistema
     * (que ainda não é sócia), pra reaproveitar o cadastro existente.
     */
    private function socioAPartirDaPessoa(PessoaDTOSocio $pessoa): Socio
    {
        return (new Socio())
            ->setNome($pessoa->getNome())
            ->setSobrenome($pessoa->getSobrenome())
            ->setDataNascimento($pessoa->getDataNascimento())
            ->setTelefone($pessoa->getTelefone())
            ->setEstado($pessoa->getEstado())
            ->setCidade($pessoa->getCidade())
            ->setBairro($pessoa->getBairro())
            ->setComplemento($pessoa->getComplemento())
            ->setCep($pessoa->getCep())
            ->setNumeroEndereco($pessoa->getNumeroEndereco())
            ->setLogradouro($pessoa->getLogradouro())
            ->setDocumento($pessoa->getCpf())
            ->setIbge($pessoa->getIbge())
            ->setEmail($pessoa->getEmail());
    }

    /**
     * Aplica num Socio os campos que estavam faltando, já validados por
     * extrairCamposFaltantesPost().
     */
    private function aplicarCamposFaltantes(Socio $socio, array $dados): Socio
    {
        foreach ($dados as $campo => $valor) {
            match ($campo) {
                'nome'     => $socio->setNome($valor),
                'email'    => $socio->setEmail($valor),
                'telefone' => $socio->setTelefone($valor),
                'cep'      => $socio->setCep($valor),
                'rua'      => $socio->setLogradouro($valor),
                'bairro'   => $socio->setBairro($valor),
                'uf'       => $socio->setEstado($valor),
                'cidade'   => $socio->setCidade($valor),
                'numero'   => $socio->setNumeroEndereco($valor),
            };
        }

        return $socio;
    }

    /**
     * Verifica quais campos obrigatórios de um sócio já cadastrado estão vazios.
     */
    private function camposFaltantes(Socio $socio): array
    {
        $valores = [
            'nome'     => $socio->getNome(),
            'email'    => $socio->getEmail(),
            'telefone' => $socio->getTelefone(),
            'cep'      => $socio->getCep(),
            'rua'      => $socio->getLogradouro(),
            'bairro'   => $socio->getBairro(),
            'uf'       => $socio->getEstado(),
            'cidade'   => $socio->getCidade(),
            'numero'   => $socio->getNumeroEndereco(),
        ];

        return $this->camposVaziosEm($valores);
    }

    /**
     * Igual a camposFaltantes(), mas direto dos dados de uma pessoa já
     * cadastrada (ainda não sócia). Não monta um Socio de propósito: os
     * setters do Socio validam formato, e um cadastro antigo fora do padrão
     * travaria a consulta do CPF antes mesmo de saber o que falta.
     */
    private function camposFaltantesPessoa(PessoaDTOSocio $pessoa): array
    {
        return $this->camposVaziosEm([
            'nome'     => $pessoa->getNome(),
            'email'    => $pessoa->getEmail(),
            'telefone' => $pessoa->getTelefone(),
            'cep'      => $pessoa->getCep(),
            'rua'      => $pessoa->getLogradouro(),
            'bairro'   => $pessoa->getBairro(),
            'uf'       => $pessoa->getEstado(),
            'cidade'   => $pessoa->getCidade(),
            'numero'   => $pessoa->getNumeroEndereco(),
        ]);
    }

    private function camposVaziosEm(array $valores): array
    {
        $faltantes = [];

        foreach (self::CAMPOS_OBRIGATORIOS_SOCIO as $campo) {
            if (!isset($valores[$campo]) || trim((string) $valores[$campo]) === '') {
                $faltantes[] = $campo;
            }
        }

        return $faltantes;
    }

    /**
     * Tempo (em segundos) que a confirmação de captcha feita ao digitar o
     * CPF (verificarCadastroSocio) continua valendo pra confirmação final
     * da contribuição, via o reaproveitamento de sessão que
     * CaptchaGoogleService::validate() já faz sozinho. A sessão fica
     * amarrada ao documento verificado (ver armarSessaoCaptcha()), então só
     * vale pra fechar a contribuição do mesmo sócio que passou por aqui.
     *
     * A proteção vem do uso único e da amarração ao documento, não da
     * duração: a janela só precisa cobrir o tempo de preencher o resto do
     * fluxo. Com 30s, quem já tinha cadastro completo e pagava com cartão
     * (digitando número, validade e CVV) estourava o prazo e o pagamento
     * falhava com "captcha inválido".
     */
    private const CAPTCHA_SEGUNDOS_DADOS_PRONTOS = 300;
    private const CAPTCHA_SEGUNDOS_CAMPOS_FALTANTES = 600;
    private const CAPTCHA_SEGUNDOS_CADASTRO_ZERO = 900;

    /**
     * Amarra a sessão de captcha reaproveitada ao documento que foi de fato
     * verificado — CaptchaGoogleService::temSessaoValidada() exige esse
     * mesmo documento na hora de consumir a sessão em criarBoleto/criarCarne/
     * criarQRCode/processarCartaoCredito/criarAssinatura. Sem isso, um único
     * solve pra um CPF qualquer destravaria a geração de pagamento pra
     * qualquer outro sócio dentro da janela de validade.
     */
    private function armarSessaoCaptcha(int $segundos, string $documento): void
    {
        $_SESSION['captcha'] = [
            'validated' => true,
            'timeout' => time() + $segundos,
            'documento' => preg_replace('/\D/', '', $documento),
        ];
    }

    /**
     * Rotas públicas (GHSA-7fc5-jh7f-grpq / GHSA-53m3-4933-cmmp): limita a 10
     * tentativas por minuto por IP, pra dificultar varredura em massa de CPFs
     * e escrita em massa no cadastro. Só se aplica a quem não está logado.
     */
    private function aplicarRateLimitPublico(string $rota): void
    {
        if (isset($_SESSION['usuario'])) {
            return;
        }

        $cache = new Cache();
        $chaveLimite = 'rate_limit_' . $rota . '_' . ($_SERVER['REMOTE_ADDR'] ?? 'desconhecido');
        $tentativas = (int) ($cache->read($chaveLimite) ?? 0);

        if ($tentativas >= 10) {
            http_response_code(429);
            echo json_encode(['erro' => 'Muitas tentativas. Tente novamente em alguns instantes.']);
            exit();
        }

        $cache->save($chaveLimite, $tentativas + 1, '1 minute');
    }

    /**
     * As rotas públicas de escrita (criarSocio/completarCadastroSocio) não
     * pedem captcha próprio: dependem da confirmação do CPF feita em
     * verificarCadastroSocio(). Como cada rota pode ser chamada direto,
     * pulando essa etapa, o servidor precisa conferir que ela aconteceu para
     * este mesmo documento — sem consumir a sessão, que o pagamento usa depois.
     */
    private function exigirCpfConfirmadoPorCaptcha(string $documento): void
    {
        if (isset($_SESSION['usuario'])) {
            return;
        }

        $captchaGoogle = new CaptchaGoogleService();
        if (!$captchaGoogle->possuiSessaoValidadaPara($documento)) {
            throw new InvalidArgumentException('Confirme o CPF/CNPJ (captcha) antes de continuar.', 412);
        }
    }

    /**
     * Versão de buscarPorDocumento() que não expõe os dados já cadastrados do
     * sócio: informa apenas se ele existe e, se existir, quais campos
     * obrigatórios ainda faltam preencher. Rota pública, usada no fluxo de
     * contribuição pra decidir se falta completar o cadastro.
     */
    public function verificarCadastroSocio()
    {
        $documento = filter_input(INPUT_POST, 'documento');

        try {
            $autenticado = isset($_SESSION['usuario']);

            if (!$autenticado) {
                $this->aplicarRateLimitPublico('verificarCadastroSocio');

                // Confirmação do CPF: sempre exige um token novo (nunca
                // reaproveita sessão pra si mesma), e é essa validação que
                // fica armada abaixo pra ser reaproveitada uma única vez na
                // confirmação final da contribuição.
                $captchaGoogle = new CaptchaGoogleService();
                if (!$captchaGoogle->validarTokenFresco())
                    throw new InvalidArgumentException('O token do captcha não é válido.', 412);
            }

            if (!$documento || empty($documento))
                throw new InvalidArgumentException('O documento informado é inválido.', 400);

            $socioDao = new SocioDAO();
            $socio = $socioDao->buscarPorDocumento($documento);

            if (!$socio || is_null($socio)) {
                http_response_code(404);

                //informar se existe uma pessoa
                $pessoaDao = new PessoaDAO($this->pdo);
                $pessoaExists = $pessoaDao->verificarExistencia($documento);
                $existePessoa = $pessoaExists instanceof PessoaDTOSocio;

                // Pessoa já cadastrada: os dados são reaproveitados, mas os
                // obrigatórios que estiverem vazios (ex: e-mail) são pedidos
                // antes do pagamento, igual ao sócio com cadastro incompleto.
                $camposFaltantesPessoa = $existePessoa
                    ? $this->camposFaltantesPessoa($pessoaExists)
                    : [];

                if (!$autenticado) {
                    // Pessoa nova: precisa preencher o cadastro inteiro, janela maior.
                    $this->armarSessaoCaptcha(match (true) {
                        !$existePessoa => self::CAPTCHA_SEGUNDOS_CADASTRO_ZERO,
                        empty($camposFaltantesPessoa) => self::CAPTCHA_SEGUNDOS_DADOS_PRONTOS,
                        default => self::CAPTCHA_SEGUNDOS_CAMPOS_FALTANTES,
                    }, $documento);
                }

                echo json_encode([
                    'existeSocio' => false,
                    'existePessoa' => $existePessoa,
                    'camposFaltantes' => $camposFaltantesPessoa,
                ]);

                exit();
            }

            $camposFaltantes = $this->camposFaltantes($socio);

            if (!$autenticado) {
                $this->armarSessaoCaptcha(empty($camposFaltantes)
                    ? self::CAPTCHA_SEGUNDOS_DADOS_PRONTOS
                    : self::CAPTCHA_SEGUNDOS_CAMPOS_FALTANTES, $documento);
            }

            echo json_encode([
                'existeSocio' => true,
                'camposFaltantes' => $camposFaltantes,
            ]);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Valida e extrai do POST somente os campos indicados como faltantes.
     */
    private function extrairCamposFaltantesPost(array $camposFaltantes): array
    {
        $valores = [];

        foreach ($camposFaltantes as $campo) {
            if (!in_array($campo, self::CAMPOS_OBRIGATORIOS_SOCIO, true)) {
                continue;
            }

            $valor = trim((string) filter_input(INPUT_POST, $campo));

            switch ($campo) {
                case 'nome':
                    if (!$valor || strlen($valor) < 3)
                        throw new InvalidArgumentException('O nome informado não pode ser vazio.', 400);
                    break;

                case 'email':
                    if (!$valor || !filter_var($valor, FILTER_VALIDATE_EMAIL))
                        throw new InvalidArgumentException('O e-mail informado não é válido.', 400);
                    break;

                case 'telefone':
                    if (!$valor || (strlen($valor) != 14 && strlen($valor) != 15))
                        throw new InvalidArgumentException('O telefone informado não está no formato correto.', 400);

                    if (strlen($valor) === 15) {
                        $celularNumeros = preg_replace('/\D/', '', $valor);

                        if ($celularNumeros[2] != 9)
                            throw new InvalidArgumentException('O número de celular informado não é válido.', 400);
                    }
                    break;

                case 'cep':
                    if (!$valor || strlen($valor) != 9)
                        throw new InvalidArgumentException('O CEP informado não está no formato válido.', 400);
                    break;

                case 'rua':
                    if (!$valor)
                        throw new InvalidArgumentException('A rua informada não pode ser vazia.', 400);
                    break;

                case 'bairro':
                    if (!$valor)
                        throw new InvalidArgumentException('O bairro informado não pode ser vazio.', 400);
                    break;

                case 'uf':
                    if (!$valor || strlen($valor) != 2)
                        throw new InvalidArgumentException('O Estado informado não pode ser vazio.', 400);
                    break;

                case 'cidade':
                    if (!$valor)
                        throw new InvalidArgumentException('A cidade informada não pode ser vazia.', 400);
                    break;

                case 'numero':
                    if (!$valor)
                        throw new InvalidArgumentException('O número da residência informado não pode ser vazio.', 400);
                    break;
            }

            $valores[$campo] = $valor;
        }

        return $valores;
    }

    /**
     * Versão de atualizarSocio() que atualiza somente os campos que
     * verificarCadastroSocio() acusou como faltantes, sem exigir o
     * formulário completo. Rota pública, usada no fluxo de contribuição.
     */
    public function completarCadastroSocio()
    {
        try {
            // A confirmação de captcha acontece em verificarCadastroSocio()
            // (confirmação do CPF) e é reaproveitada uma única vez em
            // ContribuicaoLogController/RecorrenciaController (confirmação
            // do pagamento) — esse método fica no meio do caminho e não
            // deve consumir nem rearmar essa sessão, só conferir que ela existe.
            $this->aplicarRateLimitPublico('completarCadastroSocio');

            $documento = trim((string) filter_input(INPUT_POST, 'documento_socio'));

            if (!$documento || empty($documento))
                throw new InvalidArgumentException('O documento informado é inválido.', 400);

            $this->exigirCpfConfirmadoPorCaptcha($documento);

            $socioDao = new SocioDAO($this->pdo);

            $existente = $socioDao->buscarPorDocumento($documento);

            if (!$existente)
                throw new InvalidArgumentException('Sócio não encontrado para o documento informado.', 404);

            $camposFaltantes = $this->camposFaltantes($existente);

            if (empty($camposFaltantes)) {
                http_response_code(200);
                echo json_encode(['mensagem' => 'Cadastro já está completo.']);
                return;
            }

            $dados = $this->extrairCamposFaltantesPost($camposFaltantes);

            // Funcionário/atendido não pode ter o cadastro alterado pela rota
            // pública. Como em criarSocio(), os campos são só ignorados, com a
            // mesma resposta (e a mesma validação acima) de qualquer sócio: um
            // 403 revelaria que o CPF é de alguém interno e travaria o
            // pagamento de quem já contribuiu uma vez com dados faltando.
            if ($socioDao->verificarInternoPorDocumento($documento)) {
                http_response_code(200);
                echo json_encode(['mensagem' => 'Cadastro atualizado com sucesso!']);
                return;
            }

            $socio = $this->aplicarCamposFaltantes((new Socio())->setDocumento($documento), $dados);

            $this->pdo->beginTransaction();
            $socioDao->registrarLogPorDocumento($documento, 'Cadastro completado (dados que faltavam)', Util::getUserIp(), Util::getUserAgent());

            if (!$socioDao->atualizarSocio($socio)) {
                $this->pdo->rollBack();
                throw new LogicException('Erro ao atualizar sócio no sistema', 500);
            }

            $this->pdo->commit();
            http_response_code(200);
            echo json_encode(['mensagem' => 'Cadastro atualizado com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Extraí o documento de um sócio da requisição e retorna a lista dos boletos pertecentes a esse sócio.
     */
    public function exibirBoletosPorCpf()
    {
        try {
            if (!Csrf::validateToken($_GET['csrf_token'] ?? null))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);

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
                $documentoArquivo = isset(explode('_', $boleto)[1]) ? explode('_', $boleto)[1] : null;
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
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function sincronizarStatusSocios() //Usar esse método para atualizar o status dos sócios 
    {
        try {
            $socioDao = new SocioDAO($this->pdo);
            $socioDao->sincronizarStatusSocios();

            http_response_code(200);
            echo json_encode(['mensagem' => 'Status dos sócios sincronizados com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Soft delete de um sócio, alterando o status para inativo.
     */
    public function deletarSocio()
    {
        try {
            $idSocio = filter_input(INPUT_POST, 'id_socio', FILTER_VALIDATE_INT);

            if (!$idSocio) {
                throw new InvalidArgumentException('O ID do sócio é inválido.', 400);
            }

            $socioDao = new SocioDAO($this->pdo);
            $socioDao->softDeleteSocio($idSocio);

            http_response_code(200);
            echo json_encode(['mensagem' => 'Sócio deletado com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }   
}
