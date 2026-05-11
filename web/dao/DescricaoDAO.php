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
require_once ROOT . "/classes/Saude.php";
require_once ROOT . "/Functions/funcoes.php";

class DescricaoDAO
{
    private $pdo;

    public function __construct()
    {
        try{
            $this->pdo = Conexao::connect();
        }catch(PDOException $e){
            echo 'Erro ao instanciar o objeto do tipo DescricaoDAO: '.$e->getMessage();
        }
    }

    public function incluir($texto, $id_pessoa)
    {
        try {
            $query = "SELECT `id_fichamedica` FROM `saude_fichamedica` WHERE `id_pessoa` =:idPessoa";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':idPessoa', $id_pessoa);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo 'Erro: <b>  na tabela saude_fichamedica = ' . $query . '</b> <br /><br />' . $e->getMessage();
        }
        $id_fichamedica = $result['id_fichamedica'];
        foreach ($texto as $txt) {
            try {
                $query = "INSERT INTO `saude_fichamedica_descricoes` (`id_fichamedica`, `descricao`) VALUES (:id_fichamedica, :descricao)";
                $stmt = $this->pdo->prepare($query);
                $stmt->bindParam(':id_fichamedica', $id_fichamedica);
                $stmt->bindParam(':descricao', $txt);
                $stmt->execute();
            } catch (PDOException $e) {
                echo 'Erro: <b>  na tabela saude_fichamedica_descricao = ' . $query . '</b> <br /><br />' . $e->getMessage();
            }
        }
    }

    /**
     * Recebe dois parâmetros, o id da ficha médica e um array com as strings de texto que formaram o novo conteúdo, chama a função loopAlteracao passando os devidos parâmetros.
     */
    public function alterar($idFicha, $texto)
    {
        try {
            $sql1 = "SELECT id_descricao FROM saude_fichamedica_descricoes WHERE id_fichamedica =:idFicha";
           
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql1);
            $stmt->bindParam(':idFicha', $idFicha);
            $stmt->execute();
            $descricoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if($this->loopAlteracao($descricoes, $texto, $idFicha)){
                $this->pdo->commit();
            }else{
                $this->pdo->rollBack();
            }

        } catch (PDOException $e) {
            echo 'Erro ao tentar alterar a descrição do prontuário: '.$e->getMessage();
        }
    }

    /**
     * Estrutura de repetição responsável por fazer as mudanças necessárias na base de dados para realizar a alteração do prontuário público, retorna true ao final do try e false caso entre no catch
     */
    private function loopAlteracao($descricoes, $texto, $idFicha)
    {
        $sql2 = "UPDATE saude_fichamedica_descricoes SET descricao =:descricao WHERE id_descricao =:id";
        $sql3 = "DELETE FROM saude_fichamedica_descricoes WHERE id_descricao=:id";
        $sql4 = "INSERT INTO saude_fichamedica_descricoes(id_fichamedica, descricao) VALUES (:id, :descricao)";
        try {
            foreach ($descricoes as $indice => $descricao) {
                //Adicionar verificação para atribuir uma descrição vazia a partir do momento que o indice passar do número de elementos do texto
                if ($texto[$indice]) {
                    $stmt2 = $this->pdo->prepare($sql2);
                    $stmt2->bindParam(':descricao', $texto[$indice]);
                    $stmt2->bindParam(':id', $descricao['id_descricao']);

                    $stmt2->execute();
                } else {
                    $stmt3 = $this->pdo->prepare($sql3);
                    $stmt3->bindParam(':id', $descricao['id_descricao']);

                    $stmt3->execute();
                }
            }

            //Adicionar verificação para detectar se todo o texto enviado foi de fato alterado, em caso negativo fazer um INSERT INTO com o conteúdo restante.
            $tamanhoTexto = count(($texto));
            if ($tamanhoTexto > $indice) {

                for ($i = ++$indice; $i < $tamanhoTexto; $i++) {
                    $stmt4 = $this->pdo->prepare($sql4);
                    $stmt4->bindParam(':id', $idFicha);
                    $stmt4->bindParam(':descricao', $texto[$i]);

                    $stmt4->execute();
                }
            }

             return true;

        } catch (PDOException $e) {
            echo 'Erro ao manipular a edição do texto de um prontuário público: '.$e->getMessage();
            return false;
        }
    }
}
