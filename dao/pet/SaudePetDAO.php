<?php

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
    public function incluir($saudePet){
        try {
            $id_pet = $saudePet->getNome();
            $castrado = $saudePet->getCastrado();
            $necEsp = $saudePet->getTexto();
            $vacinado = $saudePet->getVacinado();
            $vermifugado = $saudePet->getVermifugado();

            $pdo = Conexao::connect();
                    
            $sql = "INSERT INTO pet_ficha_medica(id_pet, castrado, necessidades_especiais) 
                    VALUES(:id_pet,:castrado,:necessidades_especiais)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_pet',$id_pet);
            $stmt->bindParam(':castrado',$castrado);
            $stmt->bindParam(':necessidades_especiais',$necEsp);
            $stmt->execute();

            $this->incluirVacinaVermifugo($pdo);

            $pd = $pdo->prepare("SELECT id_ficha_medica FROM pet_ficha_medica 
                      WHERE id_pet = :id_pet");
            $pd->bindParam(":id_pet", $id_pet);
            $pd->execute();
            $p = $pd->fetch();
            $fmp = $p['id_ficha_medica'];

            if($vacinado == 's'){
                $dataVacinado = $saudePet->getDataVacinado();

                $pd = $pdo->prepare("INSERT INTO pet_vacinacao(id_vacina, id_ficha_medica, 
                      data_vacinacao) VALUES(:id_vacina, :id_ficha_medica, :data_vacinacao)");
                $pd->bindValue("id_vacina", $this->vacinaId());
                $pd->bindValue("id_ficha_medica", $fmp);
                $pd->bindValue(':data_vacinacao', $dataVacinado);
                $pd->execute();
            }

            if($vermifugado == 's'){
                $dataVermifugado = $saudePet->getDataVermifugado();

                $pd = $pdo->prepare("INSERT INTO pet_vermifugacao(id_vermifugo, id_ficha_medica_vermifugo, 
                      data_vermifugacao) VALUES(:id_vermifugo, :id_ficha_medica_vermifugo, :data_vermifugacao)");
                $pd->bindValue("id_vermifugo", $this->vermifugoId());
                $pd->bindValue("id_ficha_medica_vermifugo", $fmp);
                $pd->bindValue(':data_vermifugacao',$dataVermifugado);
                $pd->execute();
            }
            
            header('Location: ../html/pet/profile_pet.php?id_pet='.$id_pet);
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pet_ficha_medica = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }        
    }

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
        $pd = $pdo->prepare("UPDATE pet_ficha_medica SET castrado = :castrado, necessidades_especiais = 
        :texto WHERE id_pet = :id_pet");
        $pd->bindValue("id_pet", $id_pet);
        $pd->bindValue("castrado", $castrado);
        $pd->bindValue("texto", $texto);
        $pd->execute();

        if( $vermifugado == "S"){
            if( $this->foiVermifugado($id_ficha_medica) == true){
                $pd = $pdo->prepare("UPDATE pet_vermifugacao SET data_vermifugacao = :dataVerm WHERE 
                id_ficha_medica_vermifugo = :id_ficha_medica");
                $pd->bindValue(":id_ficha_medica", $id_ficha_medica);
                $pd->bindValue("dataVerm", $dVermifugado);
                $pd->execute();
            }else{
                $pd = $pdo->prepare("INSERT INTO pet_vermifugacao(id_vermifugo, id_ficha_medica_vermifugo, 
                data_vermifugacao) VALUES(:id_vermifugo, :id_ficha_medica, :dataVerm)");
                $pd->bindValue("id_vermifugo", $this->vermifugoId());
                $pd->bindValue("id_ficha_medica", $id_ficha_medica);
                $pd->bindValue("dataVerm", $dVermifugado);
                $pd->execute();                
            }
        }
        if( $vacinado == "S"){
            if($this->foiVacinado($id_ficha_medica) == true){
                $pd = $pdo->prepare("UPDATE pet_vacinacao SET data_vacinacao = :dataVaci WHERE 
                id_ficha_medica = :id_ficha_medica");
                $pd->bindValue(":id_ficha_medica", $id_ficha_medica);
                $pd->bindValue("dataVaci", $dVacinado);
                $pd->execute();
            }else{
                $pd = $pdo->prepare("INSERT INTO pet_vacinacao(id_vacina, id_ficha_medica, data_vacinacao) 
                VALUES(:id_vacina, :id_ficha_medica, :dataVaci)");
                $pd->bindValue("id_vacina", $this->vacinaId());
                $pd->bindValue("id_ficha_medica", $id_ficha_medica);
                $pd->bindValue("dataVaci", $dVacinado);
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
        $pdo = Conexao::connect();
        $pd = $pdo->prepare("SELECT * FROM pet_medicamento");
        $pd->execute();
        $p = $pd->fetchAll();
        
        return $p;
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
