<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once ROOT . '/classes/Csrf.php';
require_once ROOT . '/classes/Util.php';
require_once ROOT . '/classes/Visitante.php';
require_once ROOT . '/classes/Visitado.php';
require_once ROOT . '/classes/RascunhoEntrada.php';
include_once ROOT . '/dao/Conexao.php';
include_once ROOT . '/dao/VisitanteDAO.php';
include_once ROOT . '/dao/VisitadoDAO.php';
include_once ROOT . '/dao/VisitaDAO.php';
require_once ROOT . '/html/permissao/permissao.php';

/**
 * Registro de entrada em etapas (registro_entrada.php) e registro de saída (registro_saida.php), por requisições
 * fetch que respondem em JSON, sem recarregar a página.
 *
 * A entrada em andamento fica na sessão (RascunhoEntrada); cada ação devolve o estado atualizado dela.
 */
class RegistroVisitaControle
{
    private const RECURSO_RECEPCAO = 20;
    private const ACAO_LER = 5;
    private const ACAO_LER_GRAVAR = 7;

    private const SEXOS = ['m', 'f', 'o'];

    /* ---------- entrada em andamento ---------- */

    /** Estado atual da entrada em andamento (GET). */
    public function estado()
    {
        try {
            $this->exigirPermissao(self::ACAO_LER_GRAVAR);
            $this->responder($this->montarEstado());
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /** Salva visitados, descrição e etapa (POST). Os visitados vêm como lista separada por vírgula. */
    public function salvar()
    {
        try {
            $this->exigirPost(self::ACAO_LER_GRAVAR);

            if (isset($_POST['visitados'])) {
                $idsVisitado = $this->lerListaDeIds('visitados', 'Visitado inválido.');
                (new VisitadoDAO())->validarElegiveis($idsVisitado);
                RascunhoEntrada::definirVisitados($idsVisitado);
            }

            if (isset($_POST['descricao'])) {
                RascunhoEntrada::definirDescricao($this->lerDescricao());
            }

            if (isset($_POST['etapa'])) {
                $etapa = filter_var($_POST['etapa'], FILTER_VALIDATE_INT, ['options' => ['min_range' => RascunhoEntrada::ETAPA_VISITANTES, 'max_range' => RascunhoEntrada::ETAPA_REVISAO]]);
                if ($etapa === false)
                    throw new InvalidArgumentException('Etapa inválida.', 412);

                if ($etapa > RascunhoEntrada::ETAPA_VISITANTES && empty(RascunhoEntrada::idsVisitantes()))
                    throw new InvalidArgumentException('Adicione ao menos um visitante para continuar.', 412);

                RascunhoEntrada::definirEtapa($etapa);
            }

            $this->responder($this->montarEstado());
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /** Inclui um visitado na entrada sem mexer nos demais (POST id_visitado), usado por cadastro_visitado.php. */
    public function adicionarVisitado()
    {
        try {
            $this->exigirPost(self::ACAO_LER_GRAVAR);
            $idVisitado = $this->lerId('id_visitado', 'Visitado inválido.');

            (new VisitadoDAO())->validarElegiveis([$idVisitado]);
            RascunhoEntrada::adicionarVisitado($idVisitado);

            $this->responder($this->montarEstado());
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /** Descarta a entrada em andamento (POST). */
    public function descartar()
    {
        try {
            $this->exigirPost(self::ACAO_LER_GRAVAR);
            RascunhoEntrada::descartar();
            $this->responder($this->montarEstado());
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /** Registra a entrada em andamento: uma visita para cada par visitante × visitado (POST). */
    public function registrar()
    {
        try {
            $this->exigirPost(self::ACAO_LER_GRAVAR);

            $idsVisitante = RascunhoEntrada::idsVisitantes();
            if (empty($idsVisitante))
                throw new InvalidArgumentException('Adicione ao menos um visitante para registrar a entrada.', 412);

            if (count((new VisitanteDAO())->buscarResumoPorIds($idsVisitante)) !== count($idsVisitante))
                throw new InvalidArgumentException('Um ou mais visitantes não estão mais cadastrados. Revise os visitantes.', 412);

            $idsVisitado = RascunhoEntrada::idsVisitados();
            if (empty($idsVisitado))
                $idsVisitado = [(new VisitadoDAO())->obterIdInstituicao()];

            $idsVisita = (new VisitaDAO())->incluirMultiplos($idsVisitante, $idsVisitado, RascunhoEntrada::descricao());
            if (empty($idsVisita))
                throw new PDOException('Erro ao registrar a entrada.', 500);

            RascunhoEntrada::descartar();

            $this->responder([
                'mensagem' => 'Entrada registrada com sucesso.',
                'visitas' => count($idsVisita),
                'estado' => $this->montarEstado(),
            ]);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /* ---------- visitantes ---------- */

    /**
     * Situação de um CPF (GET): 'visitante' (já cadastrado), 'pessoa' (pessoa do sistema que ainda não é visitante,
     * com os dados para completar o cadastro) ou 'novo'.
     */
    public function buscarCpf()
    {
        try {
            $this->exigirPermissao(self::ACAO_LER_GRAVAR);

            $cpf = $this->lerCpf($_GET['cpf'] ?? null);
            $visitanteDAO = new VisitanteDAO();

            $idVisitante = $visitanteDAO->buscarIdPorCpf($cpf);
            if ($idVisitante !== null) {
                $resumo = $visitanteDAO->buscarResumoPorIds([$idVisitante]);
                $this->responder([
                    'situacao' => 'visitante',
                    'cpf' => $cpf,
                    'visitante' => $this->resumoVisitante($resumo[0]),
                    'na_entrada' => in_array($idVisitante, RascunhoEntrada::idsVisitantes(), true),
                ]);
                return;
            }

            $pessoa = $visitanteDAO->buscarPessoaParaCadastro($cpf);
            $this->responder($pessoa === null
                ? ['situacao' => 'novo', 'cpf' => $cpf]
                : ['situacao' => 'pessoa', 'cpf' => $cpf, 'pessoa' => $pessoa]);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /** Adiciona um visitante já cadastrado à entrada (POST id_visitante). */
    public function adicionarVisitante()
    {
        try {
            $this->exigirPost(self::ACAO_LER_GRAVAR);
            $idVisitante = $this->lerId('id_visitante', 'O visitante informado é inválido.');

            if (empty((new VisitanteDAO())->buscarResumoPorIds([$idVisitante])))
                throw new InvalidArgumentException('Visitante não encontrado.', 404);

            RascunhoEntrada::adicionarVisitante($idVisitante);
            $this->responder($this->montarEstado());
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /** Remove um visitante da entrada (POST id_visitante). */
    public function removerVisitante()
    {
        try {
            $this->exigirPost(self::ACAO_LER_GRAVAR);
            RascunhoEntrada::removerVisitante($this->lerId('id_visitante', 'O visitante informado é inválido.'));

            if (empty(RascunhoEntrada::idsVisitantes()))
                RascunhoEntrada::definirEtapa(RascunhoEntrada::ETAPA_VISITANTES);

            $this->responder($this->montarEstado());
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Cadastra o visitante (CPF novo ou pessoa já existente no sistema) e o adiciona à entrada (POST multipart).
     * Para pessoas existentes, o DAO só completa os campos vazios; nada do que já está cadastrado é sobrescrito.
     */
    public function cadastrarVisitante()
    {
        try {
            $this->exigirPost(self::ACAO_LER_GRAVAR);

            $cpf = $this->lerCpf($_POST['cpf'] ?? null);
            $visitanteDAO = new VisitanteDAO();

            if ($visitanteDAO->buscarIdPorCpf($cpf) !== null)
                throw new InvalidArgumentException('Este CPF já pertence a um visitante cadastrado. Busque o CPF novamente para adicioná-lo.', 409);

            $visitante = $this->montarVisitante($cpf);
            $idVisitante = $visitanteDAO->selecionarCadastro($cpf) === 'PESSOA_EXISTENTE'
                ? $visitanteDAO->incluirExistente($visitante, $cpf)
                : $visitanteDAO->incluir($visitante, $cpf);

            if (!$idVisitante)
                throw new PDOException('Erro ao cadastrar o visitante.', 500);

            RascunhoEntrada::adicionarVisitante((int) $idVisitante);
            $this->responder($this->montarEstado());
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /* ---------- saída ---------- */

    /** Registra a saída de uma entrada (POST ids_visita, separados por vírgula). */
    public function registrarSaida()
    {
        try {
            $this->exigirPost(self::ACAO_LER);

            $idsVisita = $this->lerListaDeIds('ids_visita', 'Visita inválida.');
            if (empty($idsVisita))
                throw new InvalidArgumentException('Informe a visita que está saindo.', 412);

            if (!(new VisitaDAO())->encerrarMultiplos($idsVisita))
                throw new InvalidArgumentException('Esta visita não está mais em andamento. Atualize a lista.', 409);

            $this->responder(['mensagem' => 'Saída registrada com sucesso.']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /* ---------- apoio ---------- */

    private function montarEstado(): array
    {
        // Visitantes na ordem em que foram adicionados; quem deixou de existir sai da entrada.
        $porId = [];
        foreach ((new VisitanteDAO())->buscarResumoPorIds(RascunhoEntrada::idsVisitantes()) as $visitante)
            $porId[(int) $visitante['id_visitante']] = $visitante;

        $idsVisitante = array_values(array_filter(RascunhoEntrada::idsVisitantes(), fn($id) => isset($porId[$id])));
        if (count($idsVisitante) !== count(RascunhoEntrada::idsVisitantes()))
            RascunhoEntrada::definirVisitantes($idsVisitante);

        $visitantes = array_map(fn($id) => $porId[$id], $idsVisitante);

        // Os visitados vêm da mesma lista da etapa 2, que só tem quem ainda pode receber visitas.
        $idsVisitado = RascunhoEntrada::idsVisitados();
        $visitados = [];
        if (!empty($idsVisitado)) {
            foreach ((new VisitadoDAO())->listarTodos('', $idsVisitante) as $visitado) {
                if (in_array($visitado['id_visitado'], $idsVisitado, true)) {
                    $visitados[] = [
                        'id_visitado' => $visitado['id_visitado'],
                        'nome' => trim($visitado['nome'] . ' ' . $visitado['sobrenome']),
                        'identificador' => $visitado['identificador'],
                    ];
                }
            }
            if (count($visitados) !== count($idsVisitado))
                RascunhoEntrada::definirVisitados(array_column($visitados, 'id_visitado'));
        }

        $etapa = empty($visitantes) ? RascunhoEntrada::ETAPA_VISITANTES : RascunhoEntrada::etapa();

        return [
            'etapa' => $etapa,
            'em_andamento' => !RascunhoEntrada::vazio(),
            'iniciado_em' => RascunhoEntrada::vazio() ? null : RascunhoEntrada::iniciadoEm(),
            'visitantes' => array_map([$this, 'resumoVisitante'], $visitantes),
            'visitados' => $visitados,
            'descricao' => RascunhoEntrada::descricao(),
        ];
    }

    private function resumoVisitante(array $visitante): array
    {
        return [
            'id_visitante' => (int) $visitante['id_visitante'],
            'nome' => trim($visitante['nome'] . ' ' . $visitante['sobrenome']),
            'cpf' => Visitado::mascararCpf($visitante['cpf'] ?? null),
            'foto' => !empty($visitante['imagem']) ? $visitante['imagem'] : null,
        ];
    }

    private function montarVisitante(string $cpf): Visitante
    {
        $texto = fn(string $campo): string => is_string($_POST[$campo] ?? null) ? trim($_POST[$campo]) : '';

        foreach (['nome' => 'nome', 'sobrenome' => 'sobrenome', 'gender' => 'sexo', 'nascimento' => 'data de nascimento'] as $campo => $rotulo) {
            if ($texto($campo) === '')
                throw new InvalidArgumentException("O campo {$rotulo} é obrigatório.", 412);
        }

        if (!in_array($texto('gender'), self::SEXOS, true))
            throw new InvalidArgumentException('O sexo informado é inválido.', 412);

        $nascimento = DateTimeImmutable::createFromFormat('!Y-m-d', $texto('nascimento'));
        if (!$nascimento || $nascimento->format('Y-m-d') !== $texto('nascimento')
            || $texto('nascimento') < Visitante::getDataNascimentoMinima() || $texto('nascimento') > Visitante::getDataNascimentoMaxima())
            throw new InvalidArgumentException('A data de nascimento informada é inválida.', 412);

        $numero = $texto('numero_residencia');
        if ($numero !== '' && !ctype_digit($numero))
            throw new InvalidArgumentException('O número do endereço deve conter apenas dígitos.', 412);

        return new Visitante(
            $cpf, $texto('nome'), $texto('sobrenome'), $texto('gender'), $texto('nascimento'),
            null, null, null, '', '', '', '', null, null, $this->lerImagem(),
            $texto('cep'), $texto('uf'), $texto('cidade'), $texto('bairro'), $texto('rua'), $numero, $texto('complemento'), $texto('ibge')
        );
    }

    private function lerImagem(): ?string
    {
        $arquivo = $_FILES['imgperfil'] ?? null;
        if (!$arquivo || ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE)
            return null;

        if ($arquivo['error'] !== UPLOAD_ERR_OK || empty($arquivo['tmp_name']))
            throw new InvalidArgumentException('Não foi possível receber a foto. Tente outra imagem.', 412);

        if (@getimagesize($arquivo['tmp_name']) === false)
            throw new InvalidArgumentException('A foto enviada não é uma imagem válida.', 412);

        return file_get_contents($arquivo['tmp_name']);
    }

    private function lerCpf($valor): string
    {
        $digitos = preg_replace('/\D/', '', is_string($valor) ? $valor : '');

        if (strlen($digitos) !== 11 || !Util::validarCPF($digitos))
            throw new InvalidArgumentException('O CPF informado não é válido.', 412);

        // Mesmo formato gravado pelos cadastros do sistema.
        return vsprintf('%s.%s.%s-%s', [substr($digitos, 0, 3), substr($digitos, 3, 3), substr($digitos, 6, 3), substr($digitos, 9, 2)]);
    }

    private function lerDescricao(): string
    {
        $descricao = is_string($_POST['descricao'] ?? null) ? trim($_POST['descricao']) : '';

        if (mb_strlen($descricao) > RascunhoEntrada::TAMANHO_MAXIMO_DESCRICAO)
            throw new InvalidArgumentException('A descrição deve ter no máximo ' . RascunhoEntrada::TAMANHO_MAXIMO_DESCRICAO . ' caracteres.', 412);

        return $descricao;
    }

    private function lerId(string $campo, string $erro): int
    {
        $id = filter_var($_POST[$campo] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false)
            throw new InvalidArgumentException($erro, 412);

        return $id;
    }

    private function lerListaDeIds(string $campo, string $erro): array
    {
        $valor = is_string($_POST[$campo] ?? null) ? trim($_POST[$campo]) : '';
        if ($valor === '')
            return [];

        $ids = [];
        foreach (explode(',', $valor) as $parte) {
            $id = filter_var(trim($parte), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false)
                throw new InvalidArgumentException($erro, 412);
            $ids[] = $id;
        }

        return array_values(array_unique($ids));
    }

    private function exigirPermissao(int $acao): void
    {
        permissao($_SESSION['id_pessoa'], self::RECURSO_RECEPCAO, $acao);
    }

    private function exigirPost(int $acao): void
    {
        $this->exigirPermissao($acao);

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST')
            throw new InvalidArgumentException('Método não permitido.', 405);

        if (!Csrf::validateToken(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null))
            throw new InvalidArgumentException('O Token CSRF informado é inválido. Recarregue a página.', 403);
    }

    private function responder(array $dados): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
