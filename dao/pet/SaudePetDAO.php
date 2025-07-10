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
   

    public function incluirVacinaVermifugo($pdo){
        $pd = $pdo->prepare("SELECT COUNT(id_vacina) AS total FROM pet_vacina");
        $pd->execute();
        $p = $pd->fetch();

        if( $p['total'] == '0'){
            $pd = $pdo->prepare("INSERT INTO pet_vacina(nome,marca) VALUES( 'Vacina V3', 'genérica')");
            $pd->execute();
        }

        $pd = $pdo->prepare("SELECT COUNT(id_vermifugo) AS total FROM pet_vermifugo");
        $pd->execute();
        $p = $pd->fetch();
        
        if( $p['total'] == '0'){
            $pd = $pdo->prepare("INSERT INTO pet_vermifugo(nome,marca) VALUES( 'Vermivet Composto', 'Biovet')");
            $pd->execute();
        }
    }

    public function listarTodos(){
        try{
            $pets=array();
            $pdo = Conexao::connect();
            $pd = $pdo->query("SELECT fm.id_ficha_medica AS 'id_ficha_medica', p.id_pet AS 'id_pet', p.nome AS 'nome', pr.descricao AS 'raca',
            pc.descricao AS 'cor', fm.necessidades_especiais AS 'necessidades_especiais' FROM pet p INNER JOIN pet_ficha_medica fm ON fm.id_pet = p.id_pet JOIN pet_raca pr ON p.id_pet_raca = pr.id_pet_raca JOIN pet_cor pc ON p.id_pet_cor = pc.id_pet_cor");
            $pd->execute();
            $x=0;
            while($linha = $pd->fetch(PDO::FETCH_ASSOC)){
                $pets[$x]=array('id_ficha_medica'=>$linha['id_ficha_medica'],'id_pet'=>$linha['id_pet'],'nome'=>$linha['nome'],'raca'=>$linha['raca'],'cor'=>$linha['cor'],'necessidades_especiais'=>$linha['necessidades_especiais']);
                $x++;
            }
            } catch (PDOException $e){
                echo 'Error:' . $e->getMessage();
            }
        return json_encode($pets);
    }

    public function getPet($id_pet){
        $pdo = Conexao::connect();
        
        $pd = $pdo->prepare( "SELECT nome AS 'nome' FROM pet WHERE id_pet = :id_pet");

        $pd->bindValue('id_pet', $id_pet);
        $pd->execute();
        $p = $pd->fetch();
            
        return $p;
    }

    public function getFichaMedicaPet($id_pet){
        $pdo = Conexao::connect();
        $pd = $pdo->prepare("SELECT * FROM pet_ficha_medica WHERE id_pet = :id_pet");
        $pd->bindValue("id_pet", $id_pet);
        $pd->execute();
        $p = $pd->fetch();

        $pd = $pdo->prepare("SELECT * FROM pet_vacinacao WHERE id_ficha_medica = :id_ficha_medica");
        $pd->bindValue("id_ficha_medica", $p['id_ficha_medica']);
        $pd->execute();
        $d = $pd->fetch();

        $pd = $pdo->prepare("SELECT * FROM pet_vermifugacao WHERE id_ficha_medica_vermifugo = :id_ficha_medica");
        $pd->bindValue("id_ficha_medica", $p['id_ficha_medica']);
        $pd->execute();
        $o = $pd->fetch();

        return [$p, $d, $o];
    }

    public function fichaMedicaPetExiste($id_pet){
        $pdo = Conexao::connect();
        $pd = $pdo->prepare("SELECT COUNT(id_ficha_medica) AS total FROM pet_ficha_medica WHERE id_pet = :id_pet");
        $pd->bindValue("id_pet", $id_pet);
        $pd->execute();
        return $pd->fetch();
    }

    public function modificarFichaMedicaPet($dados){
        extract($dados);
        $pdo = Conexao::connect();
    
        // Verifica se a ficha médica existe
        $pd = $pdo->prepare("SELECT id_ficha_medica FROM pet_ficha_medica WHERE id_pet = :id_pet");
        $pd->bindValue(":id_pet", $id_pet);
        $pd->execute();
        $ficha = $pd->fetch();
    
        if ($ficha) {
            $id_ficha_medica = $ficha['id_ficha_medica'];
            // Atualiza ficha médica existente
            $pd = $pdo->prepare("UPDATE pet_ficha_medica SET castrado = :castrado, necessidades_especiais = :texto WHERE id_ficha_medica = :id_ficha_medica");
            $pd->bindValue(":castrado", $castrado);
            $pd->bindValue(":texto", $texto);
            $pd->bindValue(":id_ficha_medica", $id_ficha_medica);
            $pd->execute();
        } else {
            // Cria nova ficha médica
            $pd = $pdo->prepare("INSERT INTO pet_ficha_medica(id_pet, castrado, necessidades_especiais) VALUES(:id_pet, :castrado, :texto)");
            $pd->bindValue(":id_pet", $id_pet);
            $pd->bindValue(":castrado", $castrado);
            $pd->bindValue(":texto", $texto);
            $pd->execute();
            $id_ficha_medica = $pdo->lastInsertId();
        }
    
        // Atualiza ou insere vermifugação
        if ($vermifugado == "S") {
            $pd = $pdo->prepare("SELECT COUNT(*) AS total FROM pet_vermifugacao WHERE id_ficha_medica_vermifugo = :id_ficha_medica");
            $pd->bindValue(":id_ficha_medica", $id_ficha_medica);
            $pd->execute();
            $existe = $pd->fetchColumn();
    
            if ($existe) {
                $pd = $pdo->prepare("UPDATE pet_vermifugacao SET data_vermifugacao = :data WHERE id_ficha_medica_vermifugo = :id_ficha_medica");
                $pd->bindValue(":data", $dVermifugado);
                $pd->bindValue(":id_ficha_medica", $id_ficha_medica);
                $pd->execute();
            } else {
                $pd = $pdo->prepare("INSERT INTO pet_vermifugacao(id_vermifugo, id_ficha_medica_vermifugo, data_vermifugacao) VALUES(:id_vermifugo, :id_ficha_medica, :data)");
                $pd->bindValue(":id_vermifugo", $this->vermifugoId());
                $pd->bindValue(":id_ficha_medica", $id_ficha_medica);
                $pd->bindValue(":data", $dVermifugado);
                $pd->execute();
            }
        }
    
        // Atualiza ou insere vacinação
        if ($vacinado == "S") {
            $pd = $pdo->prepare("SELECT COUNT(*) AS total FROM pet_vacinacao WHERE id_ficha_medica = :id_ficha_medica");
            $pd->bindValue(":id_ficha_medica", $id_ficha_medica);
            $pd->execute();
            $existe = $pd->fetchColumn();
    
            if ($existe) {
                $pd = $pdo->prepare("UPDATE pet_vacinacao SET data_vacinacao = :data WHERE id_ficha_medica = :id_ficha_medica");
                $pd->bindValue(":data", $dVacinado);
                $pd->bindValue(":id_ficha_medica", $id_ficha_medica);
                $pd->execute();
            } else {
                $pd = $pdo->prepare("INSERT INTO pet_vacinacao(id_vacina, id_ficha_medica, data_vacinacao) VALUES(:id_vacina, :id_ficha_medica, :data)");
                $pd->bindValue(":id_vacina", $this->vacinaId());
                $pd->bindValue(":id_ficha_medica", $id_ficha_medica);
                $pd->bindValue(":data", $dVacinado);
                $pd->execute();
            }
        }
    
        header("Location: ../../html/pet/profile_pet.php?id_pet=".$id_pet);
    }
    

    public function foiVacinado($id){
        $pdo = Conexao::connect();
        $pd = $pdo->prepare("SELECT count(id_ficha_medica) AS total FROM pet_vacinacao 
        WHERE id_ficha_medica = :id");
        $pd->bindValue("id", $id);
        $pd->execute();
        $p = $pd->fetch();
        if( $p['total'] != 0){
            return true;
        }
    }
    
    public function foiVermifugado($id){
        $pdo = Conexao::connect();
        $pd = $pdo->prepare("SELECT count(id_ficha_medica_vermifugo) AS total FROM pet_vermifugacao 
        WHERE id_ficha_medica_vermifugo = :id");
        $pd->bindValue("id", $id);
        $pd->execute();
        $p = $pd->fetch();
        if( $p['total'] != 0){
            return true;
        }
    }

    public function vacinaId(){
        $pdo = Conexao::connect();
        $pd = $pdo->prepare("SELECT id_vacina FROM pet_vacina");
        $pd->execute();
        $p = $pd->fetch();

        return $p['id_vacina'];
    }

    public function vermifugoId(){
        $pdo = Conexao::connect();
        $pd = $pdo->prepare("SELECT id_vermifugo FROM pet_vermifugo");
        $pd->execute();
        $p = $pd->fetch();

        return $p['id_vermifugo'];
    }

    // $nomeMedicamento, $descricaoMedicamento, $aplicacaoMedicamento
    public function adicionarMedicamento( $nome, $descricao, $aplicacao){
        $pdo = Conexao::connect();
        $pd = $pdo->prepare("INSERT INTO pet_medicamento(nome_medicamento, descricao_medicamento, 
              aplicacao) VALUES(:nome, :descricao, :aplicacao)");
        $pd->bindValue("nome", $nome);
        $pd->bindValue("descricao", $descricao);
        $pd->bindValue("aplicacao", $aplicacao);
        $pd->execute();
        
    }

    public function listarMedicamento(){
        header( "Content-Type: application/json;charset=UTF-8" );
        $pdo = Conexao::connect();
        $linhas = [];
        $sql = <<<SQL
                    SELECT *
                    FROM pet_medicamento
                SQL;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $linhas = $stmt->fetchAll( PDO::FETCH_ASSOC );
        return $linhas;
    }

    public function registrarAtendimento(){
        extract($_REQUEST);
        $medics = explode('?', $medics);
        $pdo = Conexao::connect();
        
        $pd = $pdo->prepare("SELECT id_ficha_medica FROM pet_ficha_medica WHERE id_pet=:id_pet");
        $pd->bindValue(":id_pet", $id_pet);
        $pd->execute();
        $p = $pd->fetch();

        $pd = $pdo->prepare("INSERT INTO pet_atendimento(id_ficha_medica, data_atendimento, 
        descricao) VALUES( :id_ficha_medica, :dataAtendimento, :descricao) ");
        $pd->bindValue("id_ficha_medica", $p["id_ficha_medica"]);
        $pd->bindValue("dataAtendimento", $dataAtendimento);
        $pd->bindValue("descricao", $descricaoAtendimento);
        $pd->execute();

        //$pd = $pdo->prepare("SELECT id_pet_atendimento FROM pet_atendimento");
        $pd = $pdo->prepare("SELECT MAX(id_pet_atendimento) FROM pet_atendimento");
        $pd->execute();
        $p = $pd->fetchAll();
        
        foreach( $medics as $valor){
            if( $valor != ''){
                $pd = $pdo->prepare("INSERT INTO pet_medicacao(id_medicamento, id_pet_atendimento) 
                VALUES( :id_medicamento, :id_pet_atendimento)");
                $pd->bindValue("id_medicamento", $valor);
                $pd->bindValue("id_pet_atendimento", $p[0][0]);
                $pd->execute();
            }
        }
    }

    public function getHistoricoPet($id){
        $pdo = Conexao::connect();
        $pd = $pdo->prepare("SELECT id_ficha_medica FROM pet_ficha_medica WHERE id_pet = :id");
        $pd->bindValue("id", $id);
        $pd->execute();
        $p = $pd->fetch();

        $idFichaMedica = $p['id_ficha_medica'];
        
        $pd = $pdo->prepare("SELECT * FROM pet_atendimento WHERE id_ficha_medica = :id_ficha_medica");
        $pd->bindValue("id_ficha_medica", $idFichaMedica);
        $pd->execute();
        $p = $pd->fetchAll();

        //id_medicamento data_medicacao
        $pd = $pdo->prepare("SELECT * FROM pet_medicacao WHERE id_pet_atendimento = 
        :id_pet_atendimento");
        $pd->bindValue("id_pet_atendimento", $p[0]['id_pet_atendimento']);
        $pd->execute();
        $q = $pd->fetchAll();

        return $p;
    }

    public function getAtendimentoPet($id){
        $pdo = Conexao::connect();
        $pd = $pdo->prepare("SELECT * FROM pet_atendimento WHERE id_pet_atendimento = :id_atendimento");
        $pd->bindValue("id_atendimento", $id);
        $pd->execute();
        $p = $pd->fetch();

        $pd = $pdo->prepare("SELECT  * FROM pet_medicacao p JOIN pet_medicamento pm WHERE 
        p.id_pet_atendimento = :id_atendimento AND p.id_medicamento = pm.id_medicamento");
        $pd->bindValue("id_atendimento", $id);
        $pd->execute();
        $o = $pd->fetchAll();

        return [$p, $o];
    }
    public function dataAplicacao($dados){
        $dados = explode("|", $dados);
        $pdo = Conexao::connect();
        $pd = $pdo->prepare("UPDATE pet_medicacao SET data_medicacao = :dataMed 
        WHERE id_medicacao = :idMed");
        $pd->bindValue("dataMed", $dados[0]);
        $pd->bindValue("idMed", $dados[1]);
        $pd->execute();        
        
        return $dados;
    }
}
