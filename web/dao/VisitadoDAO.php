<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once ROOT . "/dao/Conexao.php";
require_once ROOT . "/classes/Visitado.php";
require_once ROOT . "/classes/Setor.php";

class VisitadoDAO
{
    private PDO $pdo;

    private const VINCULOS = [
        'atendido' => ['tabela' => 'atendido', 'campoPessoa' => 'pessoa_id_pessoa', 'rotulo' => 'Atendido'],
        'funcionario' => ['tabela' => 'funcionario', 'campoPessoa' => 'id_pessoa', 'rotulo' => 'Funcionário'],
        'voluntario' => ['tabela' => 'voluntario', 'campoPessoa' => 'id_pessoa', 'rotulo' => 'Voluntário'],
    ];

    private const CPF_ADMIN = 'admin';

    private const TIPOS_NAO_PESSOA = [
        'pet' => 'Pet',
        'setor' => 'Setor',
    ];

    public function __construct(?PDO $pdo = null)
    {
        is_null($pdo) ? $this->pdo = Conexao::connect() : $this->pdo = $pdo;
    }

    public static function tipoValido(?string $tipo): bool
    {
        return $tipo !== null && (isset(self::VINCULOS[$tipo]) || isset(self::TIPOS_NAO_PESSOA[$tipo]));
    }

    private function exigirTipoValido(?string $tipo): string
    {
        if (!self::tipoValido($tipo)) {
            throw new InvalidArgumentException('Tipo de visitado inválido.', 412);
        }

        return $tipo;
    }

    private function condicaoVinculo(string $aliasPessoa, ?string $filtro = null): string
    {
        if ($filtro !== null && $filtro !== '') {
            if (!isset(self::VINCULOS[$filtro])) {
                throw new InvalidArgumentException('Filtro de visitado inválido.', 412);
            }
            $vinculos = [self::VINCULOS[$filtro]];
        } else {
            $vinculos = array_values(self::VINCULOS);
        }

        $condicoes = array_map(function ($v) use ($aliasPessoa) {
            return "EXISTS (SELECT 1 FROM {$v['tabela']} WHERE {$v['tabela']}.{$v['campoPessoa']} = {$aliasPessoa}.id_pessoa)";
        }, $vinculos);

        return '(' . implode(' OR ', $condicoes) . ')';
    }

    public function obterIdInstituicao()
    {
        $sql = "SELECT id_visitado FROM visitado WHERE id_pessoa IS NULL AND id_pet IS NULL AND id_setor IS NULL ORDER BY id_visitado LIMIT 1";
        $idVisitado = $this->pdo->query($sql)->fetchColumn();

        if ($idVisitado !== false) {
            return (int) $idVisitado;
        }

        $this->pdo->exec("INSERT INTO visitado (id_pessoa, id_pet, id_setor) VALUES (NULL, NULL, NULL)");

        return (int) $this->pdo->lastInsertId();
    }

    public function incluir(string $tipo, int $id)
    {
        $tipo = $this->exigirTipoValido($tipo);

        $this->pdo->beginTransaction();

        try {
            if ($tipo === 'pet') {
                $campo = 'id_pet';
                $sqlElegivel = "SELECT 1 FROM pet WHERE id_pet = :id LIMIT 1";
                $mensagemInexistente = 'Pet não encontrado.';
            } elseif ($tipo === 'setor') {
                $campo = 'id_setor';
                $sqlElegivel = "SELECT 1 FROM setor WHERE id_setor = :id AND status = " . Setor::STATUS_ATIVO . " LIMIT 1";
                $mensagemInexistente = 'Setor não encontrado.';
            } else {
                $campo = 'id_pessoa';
                $sqlElegivel = "SELECT 1 FROM pessoa p WHERE p.id_pessoa = :id AND (p.cpf IS NULL OR p.cpf <> '" . self::CPF_ADMIN . "') AND " . $this->condicaoVinculo('p', $tipo) . " LIMIT 1";
                $mensagemInexistente = 'Pessoa não encontrada como ' . strtolower(self::VINCULOS[$tipo]['rotulo']) . '.';
            }

            $stmtElegivel = $this->pdo->prepare($sqlElegivel);
            $stmtElegivel->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtElegivel->execute();

            if ($stmtElegivel->fetchColumn() === false) {
                throw new InvalidArgumentException($mensagemInexistente, 404);
            }

            $stmtExistente = $this->pdo->prepare("SELECT id_visitado FROM visitado WHERE {$campo} = :id LIMIT 1");
            $stmtExistente->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtExistente->execute();

            if ($stmtExistente->fetchColumn() !== false) {
                throw new InvalidArgumentException('Visitado já cadastrado.', 409);
            }

            $stmtVisitado = $this->pdo->prepare("INSERT INTO visitado ({$campo}) VALUES (:id)");
            $stmtVisitado->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtVisitado->execute();

            $idVisitado = $this->pdo->lastInsertId();

            $this->pdo->commit();

            return $idVisitado;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function listarCandidatos(string $tipo): array
    {
        $tipo = $this->exigirTipoValido($tipo);

        if ($tipo === 'pet') {
            $sql = "SELECT pet.id_pet AS ref_id, pet.nome, pet.sexo, especie.descricao AS especie, raca.descricao AS raca, cor.descricao AS cor, foto.arquivo_foto_pet AS imagem
                    FROM pet
                    LEFT JOIN pet_especie especie ON especie.id_pet_especie = pet.id_pet_especie
                    LEFT JOIN pet_raca raca ON raca.id_pet_raca = pet.id_pet_raca
                    LEFT JOIN pet_cor cor ON cor.id_pet_cor = pet.id_pet_cor
                    LEFT JOIN pet_foto foto ON foto.id_pet_foto = pet.id_pet_foto
                    WHERE NOT EXISTS (SELECT 1 FROM visitado v WHERE v.id_pet = pet.id_pet)
                    ORDER BY pet.nome";
            $linhas = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function ($linha) {
                return [
                    'ref_id' => (int) $linha['ref_id'],
                    'tipo' => 'pet',
                    'nome' => $linha['nome'],
                    'sobrenome' => '',
                    'identificador' => Visitado::identificadorPet($linha['especie'], $linha['raca'], $linha['cor'], $linha['sexo']),
                    'imagem' => $linha['imagem'] ?? null,
                ];
            }, $linhas);
        }

        if ($tipo === 'setor') {
            $sql = "SELECT s.id_setor AS ref_id, s.descricao
                    FROM setor s
                    WHERE s.status = " . Setor::STATUS_ATIVO . "
                      AND NOT EXISTS (SELECT 1 FROM visitado v WHERE v.id_setor = s.id_setor)
                    ORDER BY s.descricao";
            $linhas = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function ($linha) {
                return [
                    'ref_id' => (int) $linha['ref_id'],
                    'tipo' => 'setor',
                    'nome' => $linha['descricao'],
                    'sobrenome' => '',
                    'identificador' => Visitado::IDENTIFICADOR_VAZIO,
                    'imagem' => null,
                ];
            }, $linhas);
        }

        $sql = "SELECT p.id_pessoa AS ref_id, p.nome, p.sobrenome, p.cpf, p.imagem
                FROM pessoa p
                WHERE " . $this->condicaoVinculo('p', $tipo) . "
                  AND (p.cpf IS NULL OR p.cpf <> '" . self::CPF_ADMIN . "')
                  AND NOT EXISTS (SELECT 1 FROM visitado v WHERE v.id_pessoa = p.id_pessoa)
                ORDER BY p.nome, p.sobrenome";
        $linhas = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($linha) use ($tipo) {
            return [
                'ref_id' => (int) $linha['ref_id'],
                'tipo' => $tipo,
                'nome' => $linha['nome'],
                'sobrenome' => $linha['sobrenome'] ?? '',
                'identificador' => Visitado::identificadorPessoa($linha['cpf'] ?? null),
                'imagem' => $linha['imagem'] ?? null,
            ];
        }, $linhas);
    }

    public function listarTodos(?string $filtro = null, array $idsVisitantes = []): array
    {
        $filtro = $filtro === null ? '' : $filtro;

        if ($filtro !== '') {
            $this->exigirTipoValido($filtro);
        }

        $condicaoPessoa = "(v.id_pessoa IS NOT NULL AND " . $this->condicaoVinculo('p', isset(self::VINCULOS[$filtro]) ? $filtro : null) . ")";
        $condicaoPet = "v.id_pet IS NOT NULL";
        $condicaoSetor = "(v.id_setor IS NOT NULL AND s.id_setor IS NOT NULL)";

        if ($filtro === 'pet') {
            $condicaoTipo = $condicaoPet;
        } elseif ($filtro === 'setor') {
            $condicaoTipo = $condicaoSetor;
        } elseif ($filtro === '') {
            $condicaoTipo = "($condicaoPessoa OR $condicaoPet OR $condicaoSetor)";
        } else {
            $condicaoTipo = $condicaoPessoa;
        }

        $idsVisitantes = array_values(array_unique(array_filter(array_map('intval', $idsVisitantes), fn($id) => $id > 0)));

        $condicaoVisitantes = '';
        $parametrosVisitantes = [];
        if (!empty($idsVisitantes)) {
            foreach ($idsVisitantes as $i => $idVisitante) {
                $parametrosVisitantes[':visitante' . $i] = $idVisitante;
            }
            $condicaoVisitantes = " AND (v.id_pessoa IS NULL OR v.id_pessoa NOT IN (SELECT vt.id_pessoa FROM visitante vt WHERE vt.id_visitante IN (" . implode(',', array_keys($parametrosVisitantes)) . ")))";
        }

        $sql = "SELECT v.id_visitado, v.id_pessoa, v.id_pet, v.id_setor,
                       p.nome AS pessoa_nome, p.sobrenome AS pessoa_sobrenome, p.cpf AS pessoa_cpf, p.imagem AS pessoa_imagem,
                       pet.nome AS pet_nome, pet.sexo AS pet_sexo, especie.descricao AS pet_especie, raca.descricao AS pet_raca, cor.descricao AS pet_cor, foto.arquivo_foto_pet AS pet_imagem,
                       s.descricao AS setor_descricao
                FROM visitado v
                LEFT JOIN pessoa p ON p.id_pessoa = v.id_pessoa
                LEFT JOIN pet ON pet.id_pet = v.id_pet
                LEFT JOIN pet_especie especie ON especie.id_pet_especie = pet.id_pet_especie
                LEFT JOIN pet_raca raca ON raca.id_pet_raca = pet.id_pet_raca
                LEFT JOIN pet_cor cor ON cor.id_pet_cor = pet.id_pet_cor
                LEFT JOIN pet_foto foto ON foto.id_pet_foto = pet.id_pet_foto
                LEFT JOIN setor s ON s.id_setor = v.id_setor AND s.status = " . Setor::STATUS_ATIVO . "
                WHERE (v.id_pessoa IS NULL OR v.id_pessoa != :idUsuario)
                  AND $condicaoTipo
                  $condicaoVisitantes
                ORDER BY COALESCE(p.nome, pet.nome, s.descricao)";

        $consulta = $this->pdo->prepare($sql);
        $consulta->bindValue(':idUsuario', (int) ($_SESSION['id_pessoa'] ?? 0), PDO::PARAM_INT);
        foreach ($parametrosVisitantes as $nome => $valor) {
            $consulta->bindValue($nome, $valor, PDO::PARAM_INT);
        }
        $consulta->execute();

        $visitados = [];
        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            if ($linha['id_pessoa'] !== null) {
                $visitados[] = [
                    'id_visitado' => (int) $linha['id_visitado'],
                    'nome' => $linha['pessoa_nome'],
                    'sobrenome' => $linha['pessoa_sobrenome'] ?? '',
                    'identificador' => Visitado::identificadorPessoa($linha['pessoa_cpf'] ?? null),
                    'imagem' => $linha['pessoa_imagem'] ?? null,
                ];
            } elseif ($linha['id_pet'] !== null) {
                $visitados[] = [
                    'id_visitado' => (int) $linha['id_visitado'],
                    'nome' => $linha['pet_nome'],
                    'sobrenome' => '',
                    'identificador' => Visitado::identificadorPet($linha['pet_especie'], $linha['pet_raca'], $linha['pet_cor'], $linha['pet_sexo']),
                    'imagem' => $linha['pet_imagem'] ?? null,
                ];
            } else {
                $visitados[] = [
                    'id_visitado' => (int) $linha['id_visitado'],
                    'nome' => $linha['setor_descricao'],
                    'sobrenome' => '',
                    'identificador' => Visitado::IDENTIFICADOR_VAZIO,
                    'imagem' => null,
                ];
            }
        }

        return $visitados;
    }
}