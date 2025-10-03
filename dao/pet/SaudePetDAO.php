<?php
//MODIFICADO
$config_path = "config.php";
if(file_exists($config_path)){
    require_once($config_path);
}else{
    while(true){
        $config_path = "../" . $config_path;
        if(file_exists($config_path)) break;
    }
    require_once($config_path);
}
require_once ROOT."/dao/Conexao.php";
require_once ROOT."/classes/pet/Pet.php";
require_once ROOT."/Functions/funcoes.php";

class SaudePetDAO
{
    public function listarTodos() {
        $pdo = Conexao::connect();
        try {
            $sql = "SELECT fm.id_ficha_medica AS id_ficha_medica, p.id_pet AS id_pet, p.nome AS nome,
                           pr.descricao AS raca, pc.descricao AS cor, fm.necessidades_especiais
                    FROM pet p
                    INNER JOIN pet_ficha_medica fm ON fm.id_pet = p.id_pet
                    JOIN pet_raca pr ON p.id_pet_raca = pr.id_pet_raca
                    JOIN pet_cor pc ON p.id_pet_cor = pc.id_pet_cor";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao listar pets.");
        }
    }

    public function getFichaMedicaPet($id_pet) {
        try {
            $pdo = Conexao::connect();
            $sql = "SELECT * FROM pet_ficha_medica WHERE id_pet = :id_pet";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_pet', $id_pet, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter ficha médica do pet.");
        }
    }

    public function modificarFichaMedicaPet($id_pet, $descricao, $castrado) {
        try {
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare("SELECT id_ficha_medica FROM pet_ficha_medica WHERE id_pet = :id_pet");
            $stmt->bindValue(":id_pet", $id_pet);
            $stmt->execute();
            $ficha = $stmt->fetch();

            if ($ficha) {
                $stmt = $pdo->prepare("UPDATE pet_ficha_medica
                    SET castrado = :castrado, necessidades_especiais = :texto
                    WHERE id_ficha_medica = :id_ficha_medica");
                $stmt->bindValue(":castrado", $castrado);
                $stmt->bindValue(":texto", $descricao);
                $stmt->bindValue(":id_ficha_medica", $ficha['id_ficha_medica']);
                return $stmt->execute();
            } else {
                $stmt = $pdo->prepare("INSERT INTO pet_ficha_medica (id_pet, castrado, necessidades_especiais)
                    VALUES (:id_pet, :castrado, :texto)");
                $stmt->bindValue(":id_pet", $id_pet);
                $stmt->bindValue(":castrado", $castrado);
                $stmt->bindValue(":texto", $descricao);
                return $stmt->execute();
            }
        } catch (PDOException $e) {
            throw new Exception("Erro ao modificar ficha médica do pet.");
        }
    }

    public function adicionarMedicamento($nome, $descricao, $aplicacao) {
        try {
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare("INSERT INTO pet_medicamento(nome_medicamento, descricao_medicamento, aplicacao)
                                   VALUES(:nome, :descricao, :aplicacao)");
            $stmt->bindValue(":nome", $nome);
            $stmt->bindValue(":descricao", $descricao);
            $stmt->bindValue(":aplicacao", $aplicacao);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao adicionar medicamento.");
        }
    }

    public function listarMedicamento() {
        try {
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare("SELECT * FROM pet_medicamento");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao listar medicamentos.");
        }
    }

    public function obterMedicamento($id) {
        try {
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare("SELECT * FROM pet_medicamento WHERE id_medicamento = :idMedicamento");
            $stmt->bindParam(":idMedicamento", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter medicamento.");
        }
    }

    public function registrarAtendimento($id_pet, $dataAtendimento, $descricaoAtendimento, $medics = '') {
        try {
            $pdo = Conexao::connect();

            $stmt = $pdo->prepare("SELECT id_ficha_medica FROM pet_ficha_medica WHERE id_pet = :id_pet");
            $stmt->bindValue(":id_pet", $id_pet);
            $stmt->execute();
            $ficha = $stmt->fetch();
            if (!$ficha) {
                throw new Exception("Ficha médica não encontrada.");
            }

            $stmt = $pdo->prepare("INSERT INTO pet_atendimento(id_ficha_medica, data_atendimento, descricao)
                                   VALUES(:id_ficha_medica, :dataAtendimento, :descricao)");
            $stmt->bindValue(":id_ficha_medica", $ficha["id_ficha_medica"]);
            $stmt->bindValue(":dataAtendimento", $dataAtendimento);
            $stmt->bindValue(":descricao", $descricaoAtendimento);
            $stmt->execute();

            // Retorna o ID do atendimento inserido
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erro ao registrar atendimento.");
        }
    }

    public function getHistoricoPet($id_pet) {
        try {
            $pdo = Conexao::connect();

            $stmt = $pdo->prepare("SELECT id_ficha_medica FROM pet_ficha_medica WHERE id_pet = :id");
            $stmt->bindValue(":id", $id_pet);
            $stmt->execute();
            $ficha = $stmt->fetch();
            if (!$ficha) return [];

            $stmt = $pdo->prepare("SELECT a.id_ficha_medica, a.data_atendimento, a.descricao AS descricao_atendimento,
                                          m.nome_medicamento, m.descricao_medicamento, m.aplicacao
                                   FROM pet_atendimento a
                                   LEFT JOIN pet_medicacao pm ON a.id_pet_atendimento = pm.id_pet_atendimento
                                   LEFT JOIN pet_medicamento m ON pm.id_medicamento = m.id_medicamento
                                   WHERE a.id_ficha_medica = :idFichaMedica
                                   ORDER BY a.data_atendimento DESC");
            $stmt->bindValue(":idFichaMedica", $ficha['id_ficha_medica']);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter histórico do pet.");
        }
    }

    public function getAtendimentoPet($id_atendimento) {
        try {
            $pdo = Conexao::connect();

            $stmt = $pdo->prepare("SELECT * FROM pet_atendimento WHERE id_pet_atendimento = :id_atendimento");
            $stmt->bindValue(":id_atendimento", $id_atendimento);
            $stmt->execute();
            $atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SELECT m.* FROM pet_medicacao pm
                                   JOIN pet_medicamento m ON pm.id_medicamento = m.id_medicamento
                                   WHERE pm.id_pet_atendimento = :id_atendimento");
            $stmt->bindValue(":id_atendimento", $id_atendimento);
            $stmt->execute();
            $medicamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [$atendimento, $medicamentos];
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter atendimento do pet.");
        }
    }

    public function dataAplicacao($dados) {
        try {
            $dadosArr = explode("|", $dados);
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare("UPDATE pet_medicacao SET data_medicacao = :dataMed WHERE id_medicacao = :idMed");
            $stmt->bindValue(":dataMed", $dadosArr[0]);
            $stmt->bindValue(":idMed", $dadosArr[1]);
            $stmt->execute();
            return $dadosArr;
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar data de aplicação do medicamento.");
        }
    }

  public function registrar_atendimento_pet(int $idpet, string $dataAtendimento, string $descricao, array $medicamentos): bool {
        try {
            $pdo = Conexao::connect();

            // Buscar ID da ficha médica
            $stmt = $pdo->prepare("SELECT id_ficha_medica FROM pet_ficha_medica WHERE id_pet = :idpet");
            $stmt->bindParam(':idpet', $idpet, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                throw new Exception("Ficha médica não encontrada para o pet.");
            }

            $idFichaMedica = (int)$result['id_ficha_medica'];

            // Inserir atendimento
            $stmt = $pdo->prepare("INSERT INTO pet_atendimento (id_ficha_medica, data_atendimento, descricao)
                                   VALUES (:id_ficha_medica, :data_atendimento, :descricao)");
            $stmt->bindParam(':id_ficha_medica', $idFichaMedica, PDO::PARAM_INT);
            $stmt->bindParam(':data_atendimento', $dataAtendimento);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->execute();
            $idPetAtendimento = (int)$pdo->lastInsertId();

            // Inserir medicamentos
            if (!empty($medicamentos)) {
                foreach ($medicamentos as $medicamento) {
                    $stmt = $pdo->prepare("INSERT INTO pet_medicacao (id_medicamento, id_pet_atendimento)
                                           VALUES (:medicamento, :idPetAtendimento)");
                    $stmt->bindParam(':medicamento', $medicamento, PDO::PARAM_INT);
                    $stmt->bindParam(':idPetAtendimento', $idPetAtendimento, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }

            return true;

        } catch (PDOException $e) {
            throw new Exception("Erro ao registrar atendimento do pet.");
        }
    }

    public function cadastroVacina(string $nome, string $marca): int {
        try {
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare("INSERT INTO pet_vacina (nome, marca) VALUES (:nome, :marca)");
            $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
            $stmt->bindParam(':marca', $marca, PDO::PARAM_STR);
            $stmt->execute();
            return (int)$pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erro ao cadastrar vacina.");
        }
    }

    public function listarVacina(): array {
        try {
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare("SELECT * FROM pet_vacina");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao listar vacinas.");
        }
    }

    public function cadastroVacinacao(int $idVacina, int $idFichaMedica, string $dataVacinacao): int {
        try {
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare("INSERT INTO pet_vacinacao (id_vacina, id_ficha_medica, data_vacinacao)
                                   VALUES (:id_vacina, :id_ficha_medica, :data_vacinacao)");
            $stmt->bindParam(':id_vacina', $idVacina, PDO::PARAM_INT);
            $stmt->bindParam(':id_ficha_medica', $idFichaMedica, PDO::PARAM_INT);
            $stmt->bindParam(':data_vacinacao', $dataVacinacao, PDO::PARAM_STR);
            $stmt->execute();
            return (int)$pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erro ao cadastrar vacinação.");
        }
    }

    public function cadastroVermifugo(string $nome, string $marca): int {
        try {
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare("INSERT INTO pet_vermifugo (nome, marca) VALUES (:nome, :marca)");
            $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
            $stmt->bindParam(':marca', $marca, PDO::PARAM_STR);
            $stmt->execute();
            return (int)$pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erro ao cadastrar vermifugo.");
        }
    }

    public function listarVermifugo(): array {
        try {
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare("SELECT * FROM pet_vermifugo");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao listar vermifugos.");
        }
    }

    public function cadastroVermifugacao(int $idVermifugo, int $idFichaMedica, string $dataVermifugacao): int {
        try {
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare("INSERT INTO pet_vermifugacao (id_vermifugo, id_ficha_medica_vermifugo, data_vermifugacao)
                                   VALUES (:id_vermifugo, :id_ficha_medica, :data_vermifugacao)");
            $stmt->bindParam(':id_vermifugo', $idVermifugo, PDO::PARAM_INT);
            $stmt->bindParam(':id_ficha_medica', $idFichaMedica, PDO::PARAM_INT);
            $stmt->bindParam(':data_vermifugacao', $dataVermifugacao, PDO::PARAM_STR);
            $stmt->execute();
            return (int)$pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erro ao cadastrar vermifugação.");
        }
    }

    public function getHistoricoVacinacao(int $idPet): array {
        try {
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare("SELECT id_ficha_medica FROM pet_ficha_medica WHERE id_pet = :idPet");
            $stmt->bindParam(':idPet', $idPet, PDO::PARAM_INT);
            $stmt->execute();
            $ficha = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$ficha) return [];

            $stmt = $pdo->prepare("SELECT pv.data_vacinacao, v.nome, v.marca
                                   FROM pet_vacinacao pv
                                   JOIN pet_vacina v ON v.id_vacina = pv.id_vacina
                                   WHERE pv.id_ficha_medica = :idFichaMedica");
            $stmt->bindParam(':idFichaMedica', $ficha['id_ficha_medica'], PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter histórico de vacinação.");
        }
    }

    public function getHistoricoVermifugacao(int $idPet): array {
        try {
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare("SELECT id_ficha_medica FROM pet_ficha_medica WHERE id_pet = :idPet");
            $stmt->bindParam(':idPet', $idPet, PDO::PARAM_INT);
            $stmt->execute();
            $ficha = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$ficha) return [];

            $stmt = $pdo->prepare("SELECT pv.data_vermifugacao, v.nome, v.marca
                                   FROM pet_vermifugacao pv
                                   JOIN pet_vermifugo v ON v.id_vermifugo = pv.id_vermifugo
                                   WHERE pv.id_ficha_medica_vermifugo = :idFichaMedica");
            $stmt->bindParam(':idFichaMedica', $ficha['id_ficha_medica'], PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter histórico de vermifugação.");
        }
    }

    public function adicionarTipoExame(string $descricao): int {
        try {
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare("INSERT INTO pet_tipo_exame(descricao_exame) VALUES (:tipoExame)");
            $stmt->bindParam(':tipoExame', $descricao);
            $stmt->execute();
            return (int)$pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erro ao adicionar tipo de exame.");
        }
    }

    public function listarTipoExame(): array {
        try {
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare("SELECT id_tipo_exame, descricao_exame FROM pet_tipo_exame");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao listar tipos de exame.");
        }
    }
}    

