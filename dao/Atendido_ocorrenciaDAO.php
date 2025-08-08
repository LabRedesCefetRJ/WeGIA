<!-- BEGIN 
		declare ido int;
        INSERT INTO atendido_ocorrencia(atendido_idatendido, atendido_ocorrencia_tipos_idatendido_ocorrencia_tipos, funcionario_id_funcionario, data, descricao)
        values (idatendido, id_ocorrencia, id_funcionario, data, descricao);
	
        SELECT max(id_ocorrencia) into ido from atendido_ocorrencia;
        
END
INSERT INTO `atendido_ocorrencia` (`idatendido_ocorrencias`, `atendido_idatendido`, `atendido_ocorrencia_tipos_idatendido_ocorrencia_tipos`, `funcionario_id_funcionario`, `data`, `descricao`) VALUES ('1', '4', '1', '1', '2021-11-11', 'lalallalala'); -->
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


    public function listarTodosComAnexo($id_ocorrencia)
    {
        /*Função não está sendo utilizada em nenhum local da aplicação. */
        try {
            $Despachos = array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("SELECT arquivo FROM atendido_ocorrencia_doc  WHERE idatendido_ocorrencia_doc=$id_memorando");
            $x = 0;

            while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
                $Despachos[$x] = array('idatendido_ocorrencia_doc' => $linha['idatendido_ocorrencia_doc']);
                $x++;
            }
        } catch (PDOException $e) {
            echo 'Error:' . $e->getMessage();
        }
        return json_encode($Despachos);
    }


    public function incluir($ocorrencia)
    {
        try {
            $sql = "INSERT INTO `atendido_ocorrencia` (`idatendido_ocorrencias`, `atendido_idatendido`, `atendido_ocorrencia_tipos_idatendido_ocorrencia_tipos`, `funcionario_id_funcionario`, `data`, `descricao`) values (default, :atendido_idatendido, :id_tipos_ocorrencia, :funcionario_idfuncionario, :datao, :descricao)";
            //$sql = str_replace("'", "\'", $sql); 
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare($sql);
            // $idatendido_ocorrencias=$ocorrencia->getIdatendido_ocorrencias();
            $atendido_idatendido = $ocorrencia->getAtendido_idatendido();
            $id_tipos_ocorrencia = $ocorrencia->getId_tipos_ocorrencia();
            $funcionario_idfuncionario = $ocorrencia->getFuncionario_idfuncionario();
            $datao = $ocorrencia->getData();
            $descricao = $ocorrencia->getDescricao();


            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':atendido_idatendido', $atendido_idatendido);
            $stmt->bindParam(':funcionario_idfuncionario', $funcionario_idfuncionario);
            $stmt->bindParam(':id_tipos_ocorrencia', $id_tipos_ocorrencia);
            // $stmt->bindParam(':idatendido_ocorrencias',$id_atendido_ocorrencias);
            $stmt->bindParam(':datao', $datao);
            // $stmt->bindParam(':nome',$nome);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    public function incluirArquivos($arquivos){

        for($i = 1; $i < count($arquivos["name"]); $i = $i + 1){

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

    public function listarAnexo($id_ocorrencia)
    {
        try {
            $Anexo = array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("SELECT arquivo, arquivo_nome, idatendido_ocorrencia_doc FROM atendido_ocorrencia_doc WHERE atentido_ocorrencia_idatentido_ocorrencias=$id_ocorrencia");
            $linha = $consulta->fetchAll(PDO::FETCH_ASSOC);

            foreach($linha as $arquivo){
                $decode = base64_decode(gzuncompress($arquivo['arquivo']));
                $Anexo[$arquivo['idatendido_ocorrencia_doc']] = $decode;
            }
        } catch (PDOException $e) {
            echo 'Error:' . $e->getMessage();
        }
        return $Anexo;
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
                where ao.idatendido_ocorrencias = :id";// <-- Aproveitar a query nova para buscar os dados quando a ocorrência não possuir um documento 
            }

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $paciente = array();
            while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $paciente[] = array(
                    'nome_atendido' => $linha['nome_atendido'], 'arquivo_nome' => isset($linha['arquivo_nome'])?$linha['arquivo_nome']:null, 'arquivo_extensao' => isset($linha['arquivo_extensao'])?$linha['arquivo_extensao']:null, 'idatendido_ocorrencias' => $linha['idatendido_ocorrencias'], 'sobrenome_atendido' => $linha['sobrenome_atendido'], 'atendido_ocorrencia_tipos_idatendido_ocorrencia_tipos' => $linha['descricao_tipo'], 'funcionario_id_funcionario' => $linha['func'],
                    'data' => $linha['data'], 'descricao' => $linha['descricao_ocorrencia'], 'idatendido_ocorrencia_doc' => isset($linha['idatendido_ocorrencia_doc'])?$linha['idatendido_ocorrencia_doc']:null);
            }
        } catch (Exception $e) {
            require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
            Util::tratarException($e);
        }
        return json_encode($paciente);
    }
}
?>