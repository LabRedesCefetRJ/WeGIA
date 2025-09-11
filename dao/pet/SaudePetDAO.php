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
    public function getFichaMedicaPet($id_pet) {
        try {
            $pdo = Conexao::connect();
    
            $sql = "SELECT * FROM pet_ficha_medica WHERE id_pet = :id_pet";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_pet', $id_pet, PDO::PARAM_INT);
            $stmt->execute();
    
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    
        } catch (PDOException $e) {
            // retorne null e trate o erro no controle
            return null;
        }
    }
    
    public function fichaMedicaPetExiste($id_pet){
        $pdo = Conexao::connect();
        $pd = $pdo->prepare("SELECT COUNT(id_ficha_medica) AS total FROM pet_ficha_medica WHERE id_pet = :id_pet");
        $pd->bindValue("id_pet", $id_pet);
        $pd->execute();
        return $pd->fetch();
    }

    public function modificarFichaMedicaPet($id_pet, $descricao, $castrado) {
        $pdo = Conexao::connect();
    
        $pd = $pdo->prepare("SELECT id_ficha_medica FROM pet_ficha_medica WHERE id_pet = :id_pet");
        $pd->bindValue(":id_pet", $id_pet);
        $pd->execute();
        $ficha = $pd->fetch();
    
        if ($ficha) {
            $pd = $pdo->prepare("UPDATE pet_ficha_medica 
                SET castrado = :castrado, necessidades_especiais = :texto 
                WHERE id_ficha_medica = :id_ficha_medica");
            $pd->bindValue(":castrado", $castrado);
            $pd->bindValue(":texto", $descricao);
            $pd->bindValue(":id_ficha_medica", $ficha['id_ficha_medica']);
            return $pd->execute();
        } else {
            $pd = $pdo->prepare("INSERT INTO pet_ficha_medica (id_pet, castrado, necessidades_especiais) 
                VALUES (:id_pet, :castrado, :texto)");
            $pd->bindValue(":id_pet", $id_pet);
            $pd->bindValue(":castrado", $castrado);
            $pd->bindValue(":texto", $descricao);
            return $pd->execute();
        }
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

    public function obterMedicamento($id){
        $pdo = Conexao::connect();
        $linha = [];
        try{
            $sql = "SELECT * 
                    FROM pet_medicamento
                    where id_medicamento = :idMedicamento";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam("idMedicamento", $id);
        $stmt->execute();
        $linha = $stmt->fetch();

        }catch(PDOException $e){
            http_response_code(400);
            die(json_encode(["Erro ao obter o medicamento. {$e->getMessage()}"]));
        }
        return $linha;
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
        
       
    }

    
    public function getHistoricoPet($id){
        $pdo = Conexao::connect();
    
        // Buscar a ficha médica do pet
        $pd = $pdo->prepare("SELECT id_ficha_medica FROM pet_ficha_medica WHERE id_pet = :id");
        $pd->bindValue("id", $id);
        $pd->execute();
        $result = $pd->fetch();
    
        if(!$result){
            return [];
        }
    
        $idFichaMedica = $result['id_ficha_medica'];
    
        // Buscar atendimentos + medicações
        $sql = "SELECT 
                a.id_ficha_medica,
                a.data_atendimento,
                a.descricao AS descricao_atendimento,
                m.nome_medicamento,
                m.descricao_medicamento,
                m.aplicacao
            FROM pet_atendimento a
            LEFT JOIN pet_medicacao pm ON a.id_pet_atendimento = pm.id_pet_atendimento
            LEFT JOIN pet_medicamento m ON pm.id_medicamento = m.id_medicamento
            WHERE a.id_ficha_medica = :idFichaMedica
            ORDER BY a.data_atendimento DESC";
    
        $pd = $pdo->prepare($sql);
        $pd->bindValue("idFichaMedica", $idFichaMedica);
        $pd->execute();
        $dados = $pd->fetchAll(PDO::FETCH_ASSOC);
    
        return $dados;
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
    public function registrar_atendimento_pet(int $idpet, string $dataAtendimento, string $descricao, array $medicamentos): bool {
        $pdo = Conexao::connect();
        $idFichaMedica = null;
    
        try {
            // Buscar ID da ficha médica
            $sql = "SELECT id_ficha_medica as idFichaMedica
                    FROM pet_ficha_medica
                    WHERE id_pet = :idpet";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idpet', $idpet, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
    
            $idFichaMedica = $result ? (int)$result['idFichaMedica'] : null;
    
            if ($idFichaMedica === null) {
                error_log("Ficha médica não encontrada para o pet $idpet.");
                return false;
            }
    
        } catch(PDOException $e) {
            error_log("Erro ao buscar ficha médica: " . $e->getMessage());
            return false;
        }
    
        try {
            // Inserir atendimento
            $sql = "INSERT INTO pet_atendimento (id_ficha_medica, data_atendimento, descricao)
                    VALUES (:id_ficha_medica, :data_atendimento, :descricao)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_ficha_medica', $idFichaMedica, PDO::PARAM_INT);
            $stmt->bindParam(':data_atendimento', $dataAtendimento);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->execute();
            $idPetAtendimento = (int)$pdo->lastInsertId();
    
            // Inserir medicamentos (se houver)
            if (!empty($medicamentos)) {
                foreach ($medicamentos as $medicamento) {
                    try {
                        $sql = "INSERT INTO pet_medicacao (id_medicamento, id_pet_atendimento)
                                VALUES (:medicamento, :idPetAtendimento)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':medicamento', $medicamento, PDO::PARAM_INT);
                        $stmt->bindParam(':idPetAtendimento', $idPetAtendimento, PDO::PARAM_INT);
                        $stmt->execute();
                    } catch (PDOException $e) {
                        error_log("Erro ao registrar medicação para atendimento $idPetAtendimento: " . $e->getMessage());
                        return false;
                    }
                }
            }
    
            return true;
    
        } catch (PDOException $e) {
            error_log("Erro ao registrar atendimento: " . $e->getMessage());
            return false;
        }
    }
}    