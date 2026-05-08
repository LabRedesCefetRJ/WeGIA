<?php

$config_path = "config.php";
if (file_exists($config_path)) {
    require_once($config_path);
} else {
    while (true) {
        $config_path = "../" . $config_path;
        if (file_exists($config_path)) break;
    }
    require_once($config_path);
}

require_once ROOT . "/dao/Conexao.php";
require_once ROOT . "/classes/Atendido_ocorrencia.php";
require_once ROOT . "/Functions/funcoes.php";
require_once ROOT . "/dao/Atendido_ocorrenciaDAO.php";



class Atendido_ocorrenciaDAO
{

    public function atualizarOcorrencia(int $idOcorrencia, int $idAtendido, string $data, string $descricao): bool
    {
        $pdo = Conexao::connect();

        $sql = "UPDATE atendido_ocorrencia
            SET data = :data,
                descricao = :descricao
            WHERE idatendido_ocorrencias = :id
              AND atendido_idatendido = :id_atendido";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':data', $data);
        $stmt->bindValue(':descricao', $descricao);
        $stmt->bindValue(':id', $idOcorrencia, PDO::PARAM_INT);
        $stmt->bindValue(':id_atendido', $idAtendido, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function excluirOcorrencia(int $idOcorrencia): bool
    {
        $pdo = Conexao::connect();

        // se tiver anexos, apaga primeiro
        $sqlArq = "DELETE FROM atendido_ocorrencia_doc 
               WHERE atentido_ocorrencia_idatentido_ocorrencias = :id";
        $stmtArq = $pdo->prepare($sqlArq);
        $stmtArq->bindValue(':id', $idOcorrencia, PDO::PARAM_INT);
        $stmtArq->execute();

        $sql = "DELETE FROM atendido_ocorrencia 
            WHERE idatendido_ocorrencias = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $idOcorrencia, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function buscarIdAtendidoPorOcorrencia(int $idOcorrencia): ?int
    {
        $pdo = Conexao::connect();

        $sql = "SELECT atendido_idatendido 
            FROM atendido_ocorrencia 
            WHERE idatendido_ocorrencias = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $idOcorrencia, PDO::PARAM_INT);
        $stmt->execute();

        $id = $stmt->fetchColumn();
        return $id !== false ? (int)$id : null;
    }

    public function listarTodos()
    {

        try {
            $ocorrencias = array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("SELECT ao.idatendido_ocorrencias, p.nome, p.sobrenome, ao.data, ao.atendido_ocorrencia_tipos_idatendido_ocorrencia_tipos FROM pessoa p INNER JOIN atendido a ON(p.id_pessoa=a.pessoa_id_pessoa) INNER JOIN atendido_ocorrencia ao ON (a.idatendido=ao.atendido_idatendido)");
            // $produtos = Array();
            $x = 0;
            while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
                //formatar data
                $data = new DateTime($linha['data']);
                $ocorrencias[$x] = array('idatendido_ocorrencias' => $linha['idatendido_ocorrencias'], 'nome' => $linha['nome'], 'sobrenome' => $linha['sobrenome'], 'atendido_ocorrencia_tipos_idatendido_ocorrencia_tipos' => $linha['atendido_ocorrencia_tipos_idatendido_ocorrencia_tipos'], 'data' => $data->format('d/m/Y'));
                $x++;
            }
        } catch (PDOException $e) {
            echo 'Error:' . $e->getMessage();
        }
        return json_encode($ocorrencias);
    }


    public function listarTodosComAnexo(int $idOcorrencia)
    {
        $pdo = Conexao::connect();

        $stmt = $pdo->prepare("SELECT arquivo FROM atendido_ocorrencia_doc  WHERE idatendido_ocorrencia_doc =:idOcorrencia");
        $stmt->bindParam(':idOcorrencia', $idOcorrencia, PDO::PARAM_INT);
        $stmt->execute();

        $despachos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return json_encode($despachos);
    }


    public function incluir($ocorrencia)
    {
        $pdo = Conexao::connect();

        $atendido_idatendido = $ocorrencia->getAtendido_idatendido();
        $id_tipos_ocorrencia = $ocorrencia->getId_tipos_ocorrencia();

        // Verifica se já existe falecimento para este atendido
        if ($id_tipos_ocorrencia == 2) {
            $stmtVerifica = $pdo->prepare("SELECT COUNT(*) FROM atendido_ocorrencia WHERE atendido_idatendido = :idAtendido AND atendido_ocorrencia_tipos_idatendido_ocorrencia_tipos = 2");
            $stmtVerifica->execute([':idAtendido' => $atendido_idatendido]);
            $count = $stmtVerifica->fetchColumn();

            if ($count > 0) {
                header('Location: ../html/atendido/cadastro_ocorrencia.php');
                $_SESSION['mensagem_erro'] = "Já existe uma ocorrência de falecimento registrada para este atendido.";
                exit;
            }
        }

        // Continuação da inserção
        $sql = "INSERT INTO atendido_ocorrencia 
                (atendido_idatendido, atendido_ocorrencia_tipos_idatendido_ocorrencia_tipos, funcionario_id_funcionario, data, descricao) 
                VALUES (:atendido_idatendido, :id_tipos_ocorrencia, :funcionario_idfuncionario, :datao, :descricao)";

        $stmt = $pdo->prepare($sql);

        $funcionario_idfuncionario = $ocorrencia->getFuncionario_idfuncionario();
        $datao = $ocorrencia->getData();
        $descricao = $ocorrencia->getDescricao();

        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':atendido_idatendido', $atendido_idatendido);
        $stmt->bindParam(':funcionario_idfuncionario', $funcionario_idfuncionario);
        $stmt->bindParam(':id_tipos_ocorrencia', $id_tipos_ocorrencia);
        $stmt->bindParam(':datao', $datao);

        $stmt->execute();
    }

    public function incluirArquivos($arquivos)
    {

        for ($i = 1; $i < count($arquivos["name"]); $i = $i + 1) {

            $anexo2 = $arquivos["tmp_name"][$i];

            if (isset($anexo2) && !empty($anexo2)) {
                if ($arquivos['error'][$i] !== UPLOAD_ERR_OK) {
                    die("Houve um erro no upload do arquivo. Código de erro: " . $arquivos['error']);
                }

                $extensao_nome = strtolower(pathinfo($arquivos["name"][$i], PATHINFO_EXTENSION));
                $arquivo_nome = str_replace("." . $extensao_nome, "", $arquivos["name"][$i]);
                $arquivo_b64 = base64_encode(file_get_contents($arquivos['tmp_name'][$i]));

                $tipos_permitidos = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

                if (in_array($extensao_nome, $tipos_permitidos)) {
                    $pdo = Conexao::connect();
                    $consulta = $pdo->query("SELECT max(idatendido_ocorrencias) from atendido_ocorrencia;")->fetch(PDO::FETCH_ASSOC);
                    $id = $consulta['max(idatendido_ocorrencias)'];
                    $prep = $pdo->prepare("INSERT INTO atendido_ocorrencia_doc (atentido_ocorrencia_idatentido_ocorrencias, data, arquivo_nome, arquivo_extensao, arquivo) VALUES ( :atentido_ocorrencia_idatentido_ocorrencias, :data, :arquivo_nome , :arquivo_extensao, :arquivo )");

                    //$prep->bindValue(":ida", $idatendido);
                    //$prep->bindValue(":idd", $atentido_ocorrencia_idatentido_ocorrencias);
                    $prep->bindValue(":atentido_ocorrencia_idatentido_ocorrencias", $id);
                    $prep->bindValue(":arquivo_nome", $arquivo_nome);
                    $prep->bindValue(":arquivo_extensao", $extensao_nome);
                    $prep->bindParam(":arquivo", gzcompress($arquivo_b64), PDO::PARAM_LOB);

                    $dataDocumento = date('Y/m/d');
                    $prep->bindValue(":data", $dataDocumento);

                    $prep->execute();
                }
            }
        }
    }

    public function listarAnexo(int $idOcorrencia)
    {
        $anexo = array();
        $pdo = Conexao::connect();
        $stmt = $pdo->prepare("SELECT arquivo, arquivo_nome, idatendido_ocorrencia_doc FROM atendido_ocorrencia_doc WHERE atentido_ocorrencia_idatentido_ocorrencias=:idOcorrencia");

        $stmt->bindParam(':idOcorrencia', $idOcorrencia, PDO::PARAM_INT);
        $stmt->execute();

        $linha = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($linha as $arquivo) {
            $decode = base64_decode(gzuncompress($arquivo['arquivo']));
            $anexo[$arquivo['idatendido_ocorrencia_doc']] = $decode;
        }

        return $anexo;
    }

    public function listar($id)
    {
        try {
            $pdo = Conexao::connect();

            $sqlBuscaArquivos = "SELECT * FROM atendido_ocorrencia_doc WHERE atentido_ocorrencia_idatentido_ocorrencias =:id";

            $stmt1 = $pdo->prepare($sqlBuscaArquivos);
            $stmt1->bindParam(':id', $id);
            $stmt1->execute();

            if ($stmt1->fetch(PDO::FETCH_ASSOC)) {
                $sql = "SELECT p.nome as nome_atendido, p.sobrenome as sobrenome_atendido,ao.data,ao.descricao as descricao_tipo, aod.arquivo_nome, aod.arquivo_extensao, aod.idatendido_ocorrencia_doc, ao.idatendido_ocorrencias, aod.arquivo,pp.nome as func,aot.descricao as descricao_ocorrencia from pessoa p join atendido a on (a.pessoa_id_pessoa = p.id_pessoa)
                join atendido_ocorrencia ao on (ao.atendido_idatendido = a.idatendido)
                join atendido_ocorrencia_tipos aot on (ao.atendido_ocorrencia_tipos_idatendido_ocorrencia_tipos = aot.idatendido_ocorrencia_tipos) 
                join funcionario f on (ao.funcionario_id_funcionario = f.id_funcionario)
                join pessoa pp on (f.id_pessoa = pp.id_pessoa)
                join atendido_ocorrencia_doc aod on (aod.atentido_ocorrencia_idatentido_ocorrencias = ao.idatendido_ocorrencias)
                where ao.idatendido_ocorrencias = :id"; // <-- Query Não retorna dados quando a ocorrência não possuí um documento
            } else {
                $sql = "SELECT p.nome as nome_atendido, p.sobrenome as sobrenome_atendido,ao.data,ao.descricao as descricao_tipo, ao.idatendido_ocorrencias, pp.nome as func,aot.descricao as descricao_ocorrencia from pessoa p join atendido a on (a.pessoa_id_pessoa = p.id_pessoa)
                join atendido_ocorrencia ao on (ao.atendido_idatendido = a.idatendido)
                join atendido_ocorrencia_tipos aot on (ao.atendido_ocorrencia_tipos_idatendido_ocorrencia_tipos = aot.idatendido_ocorrencia_tipos) 
                join funcionario f on (ao.funcionario_id_funcionario = f.id_funcionario)
                join pessoa pp on (f.id_pessoa = pp.id_pessoa)
                where ao.idatendido_ocorrencias = :id"; // <-- Aproveitar a query nova para buscar os dados quando a ocorrência não possuir um documento 
            }

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $paciente = array();
            while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $paciente[] = array(
                    'nome_atendido' => $linha['nome_atendido'],
                    'arquivo_nome' => isset($linha['arquivo_nome']) ? $linha['arquivo_nome'] : null,
                    'arquivo_extensao' => isset($linha['arquivo_extensao']) ? $linha['arquivo_extensao'] : null,
                    'idatendido_ocorrencias' => $linha['idatendido_ocorrencias'],
                    'sobrenome_atendido' => $linha['sobrenome_atendido'],
                    'atendido_ocorrencia_tipos_idatendido_ocorrencia_tipos' => $linha['descricao_tipo'],
                    'funcionario_id_funcionario' => $linha['func'],
                    'data' => $linha['data'],
                    'descricao' => $linha['descricao_ocorrencia'],
                    'idatendido_ocorrencia_doc' => isset($linha['idatendido_ocorrencia_doc']) ? $linha['idatendido_ocorrencia_doc'] : null
                );
            }
        } catch (Exception $e) {
            require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
            Util::tratarException($e);
        }
        return json_encode($paciente);
    }
}
