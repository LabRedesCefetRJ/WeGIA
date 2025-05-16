<?php
    $conexao = 'dao/Conexao.php';

    if( file_exists($conexao)){
        require_once $conexao;
    }else{
        while(true){
            $conexao = '../' . $conexao;
            if( file_exists($conexao)){
                break;
            }
        }
        require_once $conexao;
    }

    class AdocaoPet{

        public function exibirAdotante($idPet) {
            $pdo = Conexao::connect();
        
            // Verifica se o pet foi adotado
            $stmt = $pdo->prepare("SELECT id_pessoa, data_adocao FROM pet_adocao WHERE id_pet = :id_pet");
            $stmt->bindValue(':id_pet', $idPet);
            $stmt->execute();
            $adocao = $stmt->fetch(PDO::FETCH_ASSOC);
        
            if (!$adocao) {
                return ['adotado' => false];
            }
        
            $id_pessoa = $adocao['id_pessoa'];
            $data_adocao = $adocao['data_adocao'];
        
            // Busca o nome e RG do adotante
            $stmt = $pdo->prepare("SELECT registro_geral AS rg, nome, sobrenome FROM pessoa WHERE id_pessoa = :id");
            $stmt->bindValue(':id', $id_pessoa);
            $stmt->execute();
            $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);
        
            if (!$pessoa) {
                return ['adotado' => false]; // Pessoa não encontrada
            }
        
            $nome_completo = $pessoa['nome'] . ' ' . $pessoa['sobrenome'];
            $rg = $pessoa['rg'];
        
            return [
                'adotado' => true,
                'nome' => $nome_completo,
                'rg' => $rg,
                'id_pessoa' => $id_pessoa,
                'data_adocao' => $data_adocao
            ];
        }
        
        public function inserirAdocao($id_pet, $rg, $data_adocao){
            $pdo = Conexao::connect();
            $pd = $pdo->prepare("SELECT COUNT(*) AS 'total' FROM pet_adocao WHERE id_pet = :id_pet");
            $pd->bindValue('id_pet', $id_pet);
            $pd->execute();
            $p = $pd->fetchAll();

            foreach ($p as $value) {
                $total = $value['total'];
            }

            $pd = $pdo->prepare("SELECT id_pessoa FROM pessoa WHERE registro_geral = :rg");
            $pd->bindValue("rg", $rg);
            $pd->execute();
            $p = $pd->fetchAll();
            
            foreach($p as $value){
                $id_pessoa = $value['id_pessoa'];
            }

            if($total == 0){
                $pd = $pdo->prepare("INSERT INTO pet_adocao(id_pessoa, id_pet, data_adocao) 
                VALUES(:id_pessoa, :id_pet, :data_adocao)");
                $pd->bindValue(":id_pessoa", $id_pessoa);
                $pd->bindValue(":id_pet", $id_pet);
                $pd->bindValue(":data_adocao", $data_adocao);
                $pd->execute();                
            }else{
                $pd = $pdo->prepare("UPDATE pet_adocao SET id_pessoa = :id_pessoa, 
                data_adocao = :data_adocao WHERE id_pet = :id_pet");
                $pd->bindValue(":id_pessoa", $id_pessoa);
                $pd->bindValue(":id_pet", $id_pet);
                $pd->bindValue(":data_adocao", $data_adocao);
                $pd->execute();
            }
        }

        public function nomeAdotante($rg){
            $pdo = Conexao::connect();
            $pd = $pdo->prepare("SELECT nome, sobrenome FROM pessoa WHERE registro_geral = :rg");
            $pd->bindValue(":rg", $rg);
            $pd->execute();
            $p = $pd->fetchAll();

            foreach( $p as $value){
                return $value['nome'] . ' ' . $value['sobrenome'];
            }

        }

        public function excluirAdocao($id_pet){
            $pdo = Conexao::connect();

            $pd = $pdo->prepare("DELETE FROM pet_adocao WHERE id_pet = :id_pet");
            $pd->bindValue('id_pet', $id_pet);
            $pd->execute();
            return 1;
        }
    }

    $c = new AdocaoPet();
    // $r = $c->exibirAdotante(63);

    //$c->nomeAdotante('32.950.525-7');

    // if( $r == false){
    //     echo 'erro';
    // }else{
    //     var_dump($r);
    // }