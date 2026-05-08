<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'Conexao.php';

class PetDAO
{

    private $pdo;

    public function __construct(?PDO $pdo = null)
    {
        is_null($pdo) ? $this->pdo = Conexao::connect() : $this->pdo = $pdo;
    }

    /** $nImagem -> nome original da imagem  */
    public function adicionarPet(
        $nome,
        $nascimento,
        $acolhimento,
        $sexo,
        $caracEsp,
        $especie,
        $raca,
        $cor,
        $imgperfil,
        $nImagem
    ) {


        // Inserção da imagem de perfil, se existir
        if ($imgperfil != '') {
            $pd = $this->pdo->prepare("INSERT INTO pet_foto(arquivo_foto_pet, arquivo_foto_pet_nome, arquivo_foto_pet_extensao) 
                VALUES(:conteudo, :nome_foto, :extensao)");
            $pd->bindValue(':conteudo', $imgperfil);
            $pd->bindValue(':nome_foto', $nImagem[0]);
            $pd->bindValue(':extensao', $nImagem[1]);
            $pd->execute();

            $idFoto = $this->pdo->lastInsertId();
        } else {
            $idFoto = null;
        }

        // Inserção do pet
        $pd = $this->pdo->prepare("INSERT INTO pet(nome, data_nascimento, data_acolhimento, sexo,
            caracteristicas_especificas, id_pet_foto, id_pet_especie, id_pet_raca, id_pet_cor) 
            VALUES(:nome, :nascimento, :acolhimento, :sexo, :especificas, :id_foto, 
            :id_especie, :id_raca, :id_cor)");
        $pd->bindValue(':nome', $nome);
        $pd->bindValue(':nascimento', $nascimento);
        $pd->bindValue(':acolhimento', $acolhimento);
        $pd->bindValue(':sexo', $sexo);
        $pd->bindValue(':especificas', $caracEsp);
        $pd->bindValue(':id_foto', $idFoto);
        $pd->bindValue(':id_especie', $especie);
        $pd->bindValue(':id_raca', $raca);
        $pd->bindValue(':id_cor', $cor);
        $pd->execute();

        //Captura o ID do pet inserido
        $idPet = $this->pdo->lastInsertId();
    }


    public function listarUm($id_pet)
    {

        $pd = $this->pdo->prepare("SELECT * FROM pet WHERE id_pet=:id");
        $pd->bindValue(":id", $id_pet);
        $pd->execute();
        $p = $pd->fetchAll();
        foreach ($p as $valor) {
            $array[] = array(
                'nome' => $valor['nome'],
                'sexo' => $valor['sexo'],
                'acolhimento' => $valor['data_acolhimento'],
                'nascimento' => $valor['data_nascimento'],
                'especificas' => $valor['caracteristicas_especificas'],
                'raca' => $valor['id_pet_raca'],
                'especie' => $valor['id_pet_especie'],
                'cor' => $valor['id_pet_cor']
            );
        }
        return $array;
    }

    public function listarTodos()
    {
        $pets = array();

        $pd = $this->pdo->prepare("SELECT p.id_pet AS 'id', p.nome AS 'nome', pr.descricao AS 'raca', pe.descricao AS 'especie',
            pc.descricao AS 'cor' FROM pet p JOIN pet_raca pr ON p.id_pet_raca = pr.id_pet_raca JOIN pet_especie pe 
            ON p.id_pet_especie = pe.id_pet_especie JOIN pet_cor pc ON p.id_pet_cor = pc.id_pet_cor");
        $pd->execute();
        $x = 0;
        while ($linha = $pd->fetch(PDO::FETCH_ASSOC)) {
            $pets[$x] = array('id' => htmlspecialchars($linha['id']), 'nome' => htmlspecialchars($linha['nome']), 'raca' => htmlspecialchars($linha['raca']), 'cor' => htmlspecialchars($linha['cor']));
            $x++;
        }

        return $pets;
    }

    public function alterarFotoPet($arkivo, $nome, $extensao, $id_foto, $id_pet)
    {

        $pd = $this->pdo->prepare("SELECT id_pet_foto FROM pet WHERE id_pet =:id_pet");
        $pd->bindValue(":id_pet", $id_pet);
        $pd->execute();
        $p = $pd->fetch();

        if ($p['id_pet_foto'] == NULL) {

            $pd = $this->pdo->prepare("INSERT INTO pet_foto( arquivo_foto_pet, arquivo_foto_pet_nome, 
                arquivo_foto_pet_extensao) VALUES( :conteudo, :nome_foto, :extensao)");
            $pd->bindValue(':conteudo', $arkivo);
            $pd->bindValue(':nome_foto', $nome);
            $pd->bindValue(':extensao', $extensao);
            $pd->execute();

            $id = $this->pdo->query("SELECT id_pet_foto FROM pet_foto");
            foreach ($id as $valor) {
                $idNovaFoto = $valor['id_pet_foto'];
            }

            $pd = $this->pdo->prepare("UPDATE pet SET id_pet_foto = :id_pet_foto WHERE pet.id_pet =:id_pet");
            $pd->bindValue(":id_pet_foto", $idNovaFoto);
            $pd->bindValue(":id_pet", $id_pet);
            $pd->execute();
        } else {

            $pd = $this->pdo->prepare("UPDATE pet_foto set arquivo_foto_pet = :arquivo, 
                    arquivo_foto_pet_nome = :nome, arquivo_foto_pet_extensao = :extensao WHERE 
                    id_pet_foto = :id_foto");
            $pd->bindValue(':arquivo', $arkivo);
            $pd->bindValue(':nome', $nome);
            $pd->bindValue(':extensao', $extensao);
            $pd->bindValue(':id_foto', $id_foto);
            $pd->execute();
        }
    }

    public function alterarPet(
        $nome,
        $nascimento,
        $acolhimento,
        $sexo,
        $caracEsp,
        $especie,
        $raca,
        $cor,
        $id_pet
    ) {


        $pd = $this->pdo->prepare("UPDATE pet SET nome = :nome, data_nascimento = :nascimento, 
                data_acolhimento = :acolhimento, sexo = :sexo,
                caracteristicas_especificas = :especificas, id_pet_especie = :id_especie,
                id_pet_raca = :id_raca, id_pet_cor = :id_cor WHERE id_pet = :id_pet");
        $pd->bindValue(':nome', $nome);
        $pd->bindValue(':nascimento', $nascimento);
        $pd->bindValue(':acolhimento', $acolhimento);
        $pd->bindValue(':sexo', $sexo);
        $pd->bindValue(':especificas', $caracEsp);
        $pd->bindValue(':id_especie', $especie);
        $pd->bindValue(':id_raca', $raca);
        $pd->bindValue(':id_cor', $cor);
        $pd->bindValue(':id_pet', $id_pet);
        $pd->execute();
    }

    public function incluirExamePet(
        $id_ficha_medica,
        $id_tipo_exame,
        $data_exame,
        $arquivo_exame,
        $nameFile
    ) {

        $pd = $this->pdo->prepare("INSERT INTO pet_exame(id_ficha_medica, id_tipo_exame, data_exame, 
            arquivo_exame, arquivo_nome, arquivo_extensao) VALUES(:id_ficha_medica, :id_tipo_exame, 
            :data_exame, :arquivo_exame, :arquivo_nome, :arquivo_extensao)");
        $pd->bindValue(":id_ficha_medica", $id_ficha_medica);
        $pd->bindValue(":id_tipo_exame", $id_tipo_exame);
        $pd->bindValue(":data_exame", $data_exame);
        $pd->bindValue(":arquivo_exame", $arquivo_exame);
        $pd->bindValue(":arquivo_nome", $nameFile[0]);
        $pd->bindValue(":arquivo_extensao", $nameFile[1]);
        $pd->execute();
    }

    public function excluirExamePet($id_exame)
    {
        try {

            $pd = $this->pdo->prepare("DELETE FROM pet_exame WHERE id_exame = :id_exame");
            $pd->bindValue(":id_exame", $id_exame);
            $pd->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function listarPets()
    {
        $linhas = [];

        $sql = "SELECT *
                    FROM pet";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $linhas;
    }
}
