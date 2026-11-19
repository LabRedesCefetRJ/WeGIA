<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once ROOT . "/dao/Conexao.php";
require_once ROOT . "/classes/Visita.php";
require_once ROOT . "/classes/Visitado.php";
require_once ROOT . "/dao/SelecaoParagrafoDAO.php";
require_once ROOT . "/dao/VisitadoDAO.php";

class VisitaDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        is_null($pdo) ? $this->pdo = Conexao::connect() : $this->pdo = $pdo;
    }

    public function incluir($visita)
    {
        $ids = $this->incluirMultiplos(
            $visita->getId_Visitante(),
            [$visita->getId_Visitado()],
            $visita->getDescricao() ?? ''
        );

        return $ids[0] ?? null;
    }

    public function incluirMultiplos($idsVisitante, array $idsVisitado, string $descricao = '')
    {
        $idsVisitante = array_values(array_unique(array_map('intval', (array) $idsVisitante)));
        $idsVisitado = array_values(array_unique(array_map('intval', $idsVisitado)));

        if (empty($idsVisitante) || empty($idsVisitado)) {
            return [];
        }

        (new VisitadoDAO($this->pdo))->validarElegiveis($idsVisitado);
        $this->validarVisitantesNaoVisitados($idsVisitante, $idsVisitado);

        $this->pdo->beginTransaction();

        try {
            $horarioEntrada = date('Y-m-d H:i:s');

            $sqlVisita = "INSERT INTO visita (id_visitante, id_visitado, horario_entrada, status, descricao) VALUES (:id_visitante, :id_visitado, :horario_entrada, 'ativo', :descricao)";

            $stmtVisita = $this->pdo->prepare($sqlVisita);

            $idsVisitaInseridas = [];

            foreach ($idsVisitante as $idVisitante) {
                foreach ($idsVisitado as $idVisitado) {
                    $stmtVisita->bindValue(':id_visitante', $idVisitante, PDO::PARAM_INT);
                    $stmtVisita->bindValue(':id_visitado', $idVisitado, PDO::PARAM_INT);
                    $stmtVisita->bindValue(':horario_entrada', $horarioEntrada);

                    if ($descricao === '') {
                        $stmtVisita->bindValue(':descricao', null, PDO::PARAM_NULL);
                    } else {
                        $stmtVisita->bindValue(':descricao', $descricao, PDO::PARAM_STR);
                    }

                    $stmtVisita->execute();

                    $idsVisitaInseridas[] = $this->pdo->lastInsertId();
                }
            }

            $this->pdo->commit();
            return $idsVisitaInseridas;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function validarVisitantesNaoVisitados(array $idsVisitante, array $idsVisitado): void
    {
        $placeholdersVisitante = implode(',', array_fill(0, count($idsVisitante), '?'));
        $placeholdersVisitado = implode(',', array_fill(0, count($idsVisitado), '?'));

        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM visitante vt
             INNER JOIN visitado vd ON vd.id_pessoa = vt.id_pessoa
             WHERE vt.id_visitante IN ($placeholdersVisitante)
               AND vd.id_visitado IN ($placeholdersVisitado)"
        );

        $indice = 1;
        foreach ($idsVisitante as $idVisitante) {
            $stmt->bindValue($indice++, $idVisitante, PDO::PARAM_INT);
        }
        foreach ($idsVisitado as $idVisitado) {
            $stmt->bindValue($indice++, $idVisitado, PDO::PARAM_INT);
        }

        $stmt->execute();

        if ((int) $stmt->fetchColumn() > 0) {
            throw new InvalidArgumentException('Uma pessoa não pode ser visitante e visitado na mesma visita.', 412);
        }
    }

    public function encerrar($idVisita)
    {
        return $this->encerrarMultiplos([$idVisita]);
    }

    public function encerrarMultiplos(array $idsVisita)
    {
        $idsVisita = array_values(array_unique(array_map('intval', $idsVisita)));

        if (empty($idsVisita)) {
            return 0;
        }

        $this->pdo->beginTransaction();

        try {
            $horarioSaida = date('Y-m-d H:i:s');

            $placeholders = implode(',', array_fill(0, count($idsVisita), '?'));

            $sqlVisita = "UPDATE visita SET horario_saida = ?, status = 'inativo' WHERE status = 'ativo' AND id_visita IN ($placeholders)";

            $stmtVisita = $this->pdo->prepare($sqlVisita);

            $stmtVisita->bindValue(1, $horarioSaida);
            $indice = 2;
            foreach ($idsVisita as $idVisita) {
                $stmtVisita->bindValue($indice++, $idVisita, PDO::PARAM_INT);
            }

            $stmtVisita->execute();

            $linhasAfetadas = $stmtVisita->rowCount();

            $this->pdo->commit();
            return $linhasAfetadas;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function listarTodos()
    {
        $visitas = array();

        $consulta = $this->pdo->prepare("
            SELECT
                visita.id_visita,
                visita.horario_entrada,
                visita.descricao,
                pessoa_visitante.nome AS visitante_nome,
                pessoa_visitante.sobrenome AS visitante_sobrenome,
                pessoa_visitante.cpf AS visitante_cpf,
                pessoa_visitante.imagem AS visitante_imagem,
                COALESCE(pessoa_visitado.nome, pet.nome, setor.descricao) AS visitado_nome,
                pessoa_visitado.sobrenome AS visitado_sobrenome,
                pessoa_visitado.cpf AS visitado_cpf,
                pessoa_visitado.imagem AS visitado_imagem

            FROM visita

            INNER JOIN visitante
                ON visita.id_visitante = visitante.id_visitante

            INNER JOIN pessoa AS pessoa_visitante
                ON visitante.id_pessoa = pessoa_visitante.id_pessoa

            INNER JOIN visitado
                ON visita.id_visitado = visitado.id_visitado

            LEFT JOIN pessoa AS pessoa_visitado
                ON pessoa_visitado.id_pessoa = visitado.id_pessoa

            LEFT JOIN pet
                ON pet.id_pet = visitado.id_pet

            LEFT JOIN setor
                ON setor.id_setor = visitado.id_setor

            WHERE visita.status = 'ativo'
            ORDER BY visita.horario_entrada DESC
        "); 

        $consulta->execute();

        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $visitas[] = array(
                'id_visita' => htmlspecialchars($linha['id_visita'] ?? '', ENT_QUOTES, 'UTF-8'),
                'horario_entrada' => htmlspecialchars($linha['horario_entrada'] ?? '', ENT_QUOTES, 'UTF-8'),
                'descricao' => htmlspecialchars($linha['descricao'] ?? '', ENT_QUOTES, 'UTF-8'),
                'visitante_nome' => htmlspecialchars($linha['visitante_nome'] ?? '', ENT_QUOTES, 'UTF-8'),
                'visitante_sobrenome' => htmlspecialchars($linha['visitante_sobrenome'] ?? '', ENT_QUOTES, 'UTF-8'),
                'visitante_cpf' => htmlspecialchars($linha['visitante_cpf'] ?? '', ENT_QUOTES, 'UTF-8'),
                'visitante_imagem' => $linha['visitante_imagem'] ?? '',
                'visitado_nome' => htmlspecialchars($linha['visitado_nome'] ?? 'Instituição', ENT_QUOTES, 'UTF-8'),
                'visitado_sobrenome' => htmlspecialchars($linha['visitado_sobrenome'] ?? '', ENT_QUOTES, 'UTF-8'),
                'visitado_cpf' => htmlspecialchars($linha['visitado_cpf'] ?? '', ENT_QUOTES, 'UTF-8'),
                'visitado_imagem' => $linha['visitado_imagem'] ?? ''
            );
        }

        return $visitas;
    }

    /** Categorias de visitado aceitas no filtro do histórico. */
    public const CATEGORIAS_VISITADO = ['atendido', 'funcionario', 'voluntario', 'pet', 'setor', 'instituicao'];

    public const SITUACAO_EM_ANDAMENTO = 'andamento';
    public const SITUACAO_ENCERRADA = 'encerrada';

    /**
     * Categoria do visitado (depende do alias "visitado" na consulta). Uma pessoa com mais de um vínculo
     * é classificada pelo primeiro encontrado: atendido, funcionário e voluntário.
     */
    private const SQL_CATEGORIA_VISITADO = "CASE
                    WHEN visitado.id_pessoa IS NOT NULL AND EXISTS (SELECT 1 FROM atendido cat_a WHERE cat_a.pessoa_id_pessoa = visitado.id_pessoa) THEN 'atendido'
                    WHEN visitado.id_pessoa IS NOT NULL AND EXISTS (SELECT 1 FROM funcionario cat_f WHERE cat_f.id_pessoa = visitado.id_pessoa) THEN 'funcionario'
                    WHEN visitado.id_pessoa IS NOT NULL AND EXISTS (SELECT 1 FROM voluntario cat_v WHERE cat_v.id_pessoa = visitado.id_pessoa) THEN 'voluntario'
                    WHEN visitado.id_pessoa IS NOT NULL THEN 'pessoa'
                    WHEN visitado.id_pet IS NOT NULL THEN 'pet'
                    WHEN visitado.id_setor IS NOT NULL THEN 'setor'
                    ELSE 'instituicao'
                END";

    private ?string $nomeInstituicao = null;

    private function nomeInstituicao(): string
    {
        if ($this->nomeInstituicao === null) {
            $titulo = trim((string) SelecaoParagrafoDAO::getSelecao(SelecaoParagrafo::Titulo));
            $this->nomeInstituicao = $titulo !== '' ? $titulo : 'Instituição';
        }

        return $this->nomeInstituicao;
    }

    /**
     * Busca as visitas que atendem à condição e as agrupa por entrada. Uma entrada com vários visitantes e
     * visitados gera uma linha de visita por par, todas com o mesmo horário de entrada e a mesma descrição
     * (ver incluirMultiplos), e é essa a chave do agrupamento.
     *
     * Os valores são devolvidos sem escape: quem exibe é responsável por escapar.
     */
    private function buscarGrupos(string $condicao, array $parametros = []): array
    {
        $consulta = $this->pdo->prepare("
            SELECT
                visita.id_visita,
                visita.id_visitante,
                visita.id_visitado,
                visita.horario_entrada,
                visita.horario_saida,
                visita.descricao,
                pessoa_visitante.nome AS visitante_nome,
                pessoa_visitante.sobrenome AS visitante_sobrenome,
                pessoa_visitante.cpf AS visitante_cpf,
                COALESCE(pessoa_visitado.nome, pet.nome, setor.descricao) AS visitado_nome,
                pessoa_visitado.sobrenome AS visitado_sobrenome,
                pessoa_visitado.cpf AS visitado_cpf,
                pet.sexo AS pet_sexo,
                pet_especie.descricao AS pet_especie,
                pet_raca.descricao AS pet_raca,
                pet_cor.descricao AS pet_cor,
                CASE
                    WHEN visitado.id_pessoa IS NOT NULL THEN 'pessoa'
                    WHEN visitado.id_pet IS NOT NULL THEN 'pet'
                    WHEN visitado.id_setor IS NOT NULL THEN 'setor'
                    ELSE 'instituicao'
                END AS visitado_tipo,
                " . self::SQL_CATEGORIA_VISITADO . " AS visitado_categoria

            FROM visita

            INNER JOIN visitante
                ON visita.id_visitante = visitante.id_visitante

            INNER JOIN pessoa AS pessoa_visitante
                ON visitante.id_pessoa = pessoa_visitante.id_pessoa

            INNER JOIN visitado
                ON visita.id_visitado = visitado.id_visitado

            LEFT JOIN pessoa AS pessoa_visitado
                ON pessoa_visitado.id_pessoa = visitado.id_pessoa

            LEFT JOIN pet
                ON pet.id_pet = visitado.id_pet

            LEFT JOIN pet_especie
                ON pet_especie.id_pet_especie = pet.id_pet_especie

            LEFT JOIN pet_raca
                ON pet_raca.id_pet_raca = pet.id_pet_raca

            LEFT JOIN pet_cor
                ON pet_cor.id_pet_cor = pet.id_pet_cor

            LEFT JOIN setor
                ON setor.id_setor = visitado.id_setor

            WHERE $condicao
            ORDER BY visita.horario_entrada DESC, visita.id_visita ASC
        ");

        foreach ($parametros as $nome => $valor) {
            $consulta->bindValue($nome, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $consulta->execute();

        $grupos = [];

        foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $chave = $linha['horario_entrada'] . '|' . ($linha['descricao'] ?? '');

            if (!isset($grupos[$chave])) {
                $grupos[$chave] = [
                    'ids_visita' => [],
                    'horario_entrada' => $linha['horario_entrada'],
                    'horario_saida' => null,
                    'em_andamento' => false,
                    'descricao' => $linha['descricao'] ?? '',
                    'visitantes' => [],
                    'visitados' => [],
                ];
            }

            $grupo = &$grupos[$chave];

            $grupo['ids_visita'][] = (int) $linha['id_visita'];

            // A saída é registrada para a entrada inteira; se alguma visita ainda estiver aberta, a entrada está em andamento.
            if ($linha['horario_saida'] === null) {
                $grupo['em_andamento'] = true;
            } elseif ($grupo['horario_saida'] === null || $linha['horario_saida'] > $grupo['horario_saida']) {
                $grupo['horario_saida'] = $linha['horario_saida'];
            }

            $grupo['visitantes'][(int) $linha['id_visitante']] = [
                'id_visitante' => (int) $linha['id_visitante'],
                'nome' => $linha['visitante_nome'],
                'sobrenome' => $linha['visitante_sobrenome'],
                'tipo' => 'pessoa',
                'identificador' => Visitado::identificadorPessoa($linha['visitante_cpf'] ?? null),
            ];

            switch ($linha['visitado_tipo']) {
                case 'pessoa':
                    $identificadorVisitado = Visitado::identificadorPessoa($linha['visitado_cpf'] ?? null);
                    break;
                case 'pet':
                    $identificadorVisitado = Visitado::identificadorPet($linha['pet_especie'], $linha['pet_raca'], $linha['pet_cor'], $linha['pet_sexo']);
                    break;
                default:
                    $identificadorVisitado = Visitado::IDENTIFICADOR_VAZIO;
            }

            $nomeVisitado = $linha['visitado_tipo'] === 'instituicao' ? $this->nomeInstituicao() : $linha['visitado_nome'];

            $grupo['visitados'][(int) $linha['id_visitado']] = [
                'id_visitado' => (int) $linha['id_visitado'],
                'nome' => $nomeVisitado ?? 'Instituição',
                'sobrenome' => $linha['visitado_sobrenome'],
                'tipo' => $linha['visitado_tipo'],
                'categoria' => $linha['visitado_categoria'],
                'identificador' => $identificadorVisitado,
            ];

            unset($grupo);
        }

        foreach ($grupos as &$grupo) {
            if ($grupo['em_andamento']) {
                $grupo['horario_saida'] = null;
            }
            $grupo['visitantes'] = array_values($grupo['visitantes']);
            $grupo['visitados'] = array_values($grupo['visitados']);
        }
        unset($grupo);

        return array_values($grupos);
    }

    public function listarTodosAgrupados()
    {
        $escapar = function ($item) {
            return [
                'nome' => htmlspecialchars($item['nome'] ?? '', ENT_QUOTES, 'UTF-8'),
                'sobrenome' => htmlspecialchars($item['sobrenome'] ?? '', ENT_QUOTES, 'UTF-8'),
                'tipo' => htmlspecialchars($item['tipo'] ?? '', ENT_QUOTES, 'UTF-8'),
                'identificador' => htmlspecialchars($item['identificador'] ?? '', ENT_QUOTES, 'UTF-8'),
            ];
        };

        $resultado = [];
        foreach ($this->buscarGrupos("visita.status = 'ativo'") as $grupo) {
            $resultado[] = [
                'ids_visita' => $grupo['ids_visita'],
                'horario_entrada' => htmlspecialchars($grupo['horario_entrada'] ?? '', ENT_QUOTES, 'UTF-8'),
                'descricao' => htmlspecialchars($grupo['descricao'] ?? '', ENT_QUOTES, 'UTF-8'),
                'visitantes' => array_map($escapar, $grupo['visitantes']),
                'visitados' => array_map($escapar, $grupo['visitados']),
            ];
        }

        return $resultado;
    }

    /**
     * Histórico de entradas agrupadas, mais recentes primeiro.
     *
     * Filtros aceitos (já validados pelo controle):
     * - data_inicio, data_fim: 'Y-m-d', aplicados ao dia da entrada (intervalo fechado);
     * - id_visitado: entradas em que esse visitado foi visitado (a entrada aparece completa);
     * - categoria: uma de CATEGORIAS_VISITADO, com o mesmo comportamento de id_visitado;
     * - situacao: SITUACAO_EM_ANDAMENTO ou SITUACAO_ENCERRADA.
     *
     * Os valores são devolvidos sem escape.
     */
    public function listarHistorico(array $filtros = []): array
    {
        $condicoes = ['1 = 1'];
        $parametros = [];

        if (!empty($filtros['data_inicio'])) {
            $condicoes[] = 'visita.horario_entrada >= :inicio';
            $parametros[':inicio'] = $filtros['data_inicio'] . ' 00:00:00';
        }

        if (!empty($filtros['data_fim'])) {
            $condicoes[] = 'visita.horario_entrada < :fimExclusivo';
            $parametros[':fimExclusivo'] = (new DateTimeImmutable($filtros['data_fim']))->modify('+1 day')->format('Y-m-d 00:00:00');
        }

        if (($filtros['situacao'] ?? '') === self::SITUACAO_EM_ANDAMENTO) {
            $condicoes[] = 'visita.horario_saida IS NULL';
        } elseif (($filtros['situacao'] ?? '') === self::SITUACAO_ENCERRADA) {
            $condicoes[] = 'visita.horario_saida IS NOT NULL';
        }

        // Os filtros de visitado selecionam a entrada inteira, para que os demais visitados dela continuem aparecendo.
        $condicoesVisitado = [];

        if (!empty($filtros['id_visitado'])) {
            $condicoesVisitado[] = 'filtro.id_visitado = :idVisitado';
            $parametros[':idVisitado'] = (int) $filtros['id_visitado'];
        }

        if (!empty($filtros['categoria']) && in_array($filtros['categoria'], self::CATEGORIAS_VISITADO, true)) {
            $condicoesVisitado[] = '(' . self::SQL_CATEGORIA_VISITADO . ') = :categoria';
            $parametros[':categoria'] = $filtros['categoria'];
        }

        if ($condicoesVisitado) {
            $condicoes[] = "(visita.horario_entrada, COALESCE(visita.descricao, '')) IN (
                SELECT filtro.horario_entrada, COALESCE(filtro.descricao, '')
                FROM visita AS filtro
                INNER JOIN visitado ON visitado.id_visitado = filtro.id_visitado
                WHERE " . implode(' AND ', $condicoesVisitado) . "
            )";
        }

        return $this->buscarGrupos(implode(' AND ', $condicoes), $parametros);
    }

    /**
     * Visitados que já receberam alguma visita, para o filtro do histórico (inclui os que hoje estão inativos).
     */
    public function listarVisitadosComVisitas(): array
    {
        $linhas = $this->pdo->query("
            SELECT DISTINCT
                visitado.id_visitado,
                CASE
                    WHEN pessoa.id_pessoa IS NOT NULL THEN CONCAT_WS(' ', pessoa.nome, NULLIF(pessoa.sobrenome, ''))
                    ELSE COALESCE(pet.nome, setor.descricao)
                END AS nome,
                " . self::SQL_CATEGORIA_VISITADO . " AS categoria
            FROM visita
            INNER JOIN visitado ON visitado.id_visitado = visita.id_visitado
            LEFT JOIN pessoa ON pessoa.id_pessoa = visitado.id_pessoa
            LEFT JOIN pet ON pet.id_pet = visitado.id_pet
            LEFT JOIN setor ON setor.id_setor = visitado.id_setor
        ")->fetchAll(PDO::FETCH_ASSOC);

        $visitados = array_map(function ($linha) {
            return [
                'id_visitado' => (int) $linha['id_visitado'],
                'nome' => $linha['categoria'] === 'instituicao' ? $this->nomeInstituicao() : (string) $linha['nome'],
                'categoria' => $linha['categoria'],
            ];
        }, $linhas);

        usort($visitados, fn($a, $b) => strcasecmp($a['nome'], $b['nome']));

        return $visitados;
    }
}
