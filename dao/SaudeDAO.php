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

class SaudeDAO
{
    /**
     * Recebe um objeto do tipo Saude e cria uma ficha médica no banco de dados linkando o id do paciente no campo id_pessoa da tabela.
     */
    public function incluir($saude)
    {
        try {
            //$sql = "call saude_cad_fichamedica(:nome,null)";
            $sql = "INSERT INTO saude_fichamedica(id_pessoa) VALUES (:id_pessoa)"; //continuar daqui...
            $pdo = Conexao::connect();

            $pdo->beginTransaction();

            $stmt = $pdo->prepare($sql);

            $idPessoa = $saude->getNome();
            $stmt->bindParam(':id_pessoa', $idPessoa);

            if ($stmt->execute()) {
                $pdo->commit();
            } else {
                $pdo->rollBack();
            }

            //$stmt->execute();

            //$pdo->close();//método depreciado


        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        } finally {
            $pdo = null; // <-- Forma atual
        }
    }
    public function alterarImagem($id_fichamedica, $imagem)
    {
        $imagem = base64_encode($imagem);
        try {
            $pdo = Conexao::connect();
            $id_pessoa = (($pdo->query("SELECT id_pessoa FROM saude_fichamedica WHERE id_fichamedica=$id_fichamedica"))->fetch(PDO::FETCH_ASSOC))["id_pessoa"];

            $sql = "UPDATE pessoa SET imagem = :imagem WHERE id_pessoa = :id_pessoa;";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_pessoa', $id_pessoa);
            $stmt->bindValue(':imagem', $imagem);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoa = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }
    public function alterar($saude)
    { //Verificar possibilidade de desativação do método
        try {
            $sql = 'update pessoa as p inner join saude_fichamedica as sf on p.id_pessoa=sf.id_pessoa set p.imagem=:imagem where sf.id_pessoa=:id_pessoa';

            $sql = str_replace("'", "\'", $sql);
            $pdo = Conexao::connect();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // $nome=$atendido->getNome();
            // $sobrenome=$atendido->getSobrenome();
            // $cpf=$atendido->getCpf();
            // $sexo=$atendido->getSexo();
            // $telefone=$atendido->getTelefone();
            // $nascimento=$atendido->getDataNascimento();

            // $stmt->bindParam('nome',$nome);
            // $stmt->bindParam('sobrenome',$sobrenome);
            // $stmt->bindParam(':cpf',$cpf);
            // $stmt->bindParam(':sexo',$sexo);
            // $stmt->bindParam(':telefone',$telefone);
            // $stmt->bindParam(':data_nascimento',$nascimento);
            /*$stmt->execute();
            $pdo->commit();
            $pdo->close();*/

            // mysqli_stmt_close($stmt);
            // mysqli_close($pdo);

        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }


    public function listarTodos()
    {

        try {
            $pacientes = array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("SELECT s.id_fichamedica, p.imagem, p.nome, p.sobrenome 
                FROM pessoa p 
                INNER JOIN saude_fichamedica s ON s.id_pessoa = p.id_pessoa 
                LEFT JOIN atendido a ON a.pessoa_id_pessoa = p.id_pessoa 
                LEFT JOIN funcionario f ON f.id_pessoa = p.id_pessoa
                WHERE 
                (a.atendido_status_idatendido_status IS NULL OR a.atendido_status_idatendido_status != 2)
                AND (f.id_situacao IS NULL OR f.id_situacao != 2);
            ");
            // $produtos = Array();
            $x = 0;
            while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {

                $pacientes[$x] = array('id_fichamedica' => $linha['id_fichamedica'], 'imagem' => $linha['imagem'], 'nome' => $linha['nome'], 'sobrenome' => $linha['sobrenome']);
                $x++;
            }
            //$pdo->commit();
            //$pdo->close();
        } catch (PDOException $e) {
            echo 'Error:' . $e->getMessage();
        }
        return json_encode($pacientes);
    }

    public function listar($id)
    {
        try {
            echo $id;
            $pdo = Conexao::connect();
            /*$sql = "SELECT p.imagem,p.nome,p.sobrenome,p.cpf, p.senha, p.sexo, p.telefone,p.data_nascimento, p.cep,p.estado,p.cidade,p.bairro,p.logradouro,p.numero_endereco,p.complemento,p.ibge,p.registro_geral,p.orgao_emissor,p.data_expedicao,p.nome_pai,p.nome_mae,p.tipo_sanguineo, d.id_documento FROM pessoa p LEFT JOIN atendido a ON p.id_pessoa = a.pessoa_id_pessoa left join documento d on p.id_pessoa=d.id_pessoa WHERE a.idatendido=:id";*/ ///

            $sql = "SELECT p.nome,p.sobrenome,p.imagem,p.sexo,p.data_nascimento,p.tipo_sanguineo FROM pessoa p 
            JOIN saude_fichamedica sf ON p.id_pessoa = sf.id_pessoa 
            WHERE sf.id_fichamedica=:id";
            // $sql = "SELECT nome from pessoa where id ='1'";

            // $sql = "SELECT p.nome, p.sobrenome, p.sexo, p.data_nascimento FROM pessoa p JOIN funcionario f ON p.id_pessoa=f.id_pessoa JOIN saude_fichamedica sf ON f.id_pessoa = sf.id_pessoa WHERE sf.id_fichamedica=:id";
            // o tipo sanguinio e a imagem vou ter que pegar de outra forma //

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id);

            $stmt->execute();
            $paciente = array();
            // $pacienteFuncionario=array();
            while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $paciente[] = array('nome' => $linha['nome'], 'imagem' => $linha['imagem'], 'sobrenome' => $linha['sobrenome'], 'sexo' => $linha['sexo'], 'data_nascimento' => $linha['data_nascimento'], 'tipo_sanguineo' => $linha['tipo_sanguineo']);
                // $paciente[]=array('nome'=>$linha['nome']);
                // $pacienteFuncionario[]=array('nome'=>$linha['nome'],'sobrenome'=>$linha['sobrenome'],'sexo'=>$linha['sexo'],'data_nascimento'=>$linha['data_nascimento']);
                // 'imagem'=>$linha['imagem']
                // 'tipo_sanguineo'=>$linha['tipo_sanguineo']
                // 'id_fichamedica'=>$linha['id_fichamedica']

            }
        } catch (PDOException $e) {
            echo 'Error: ' .  $e->getMessage();
        }
        return json_encode($paciente);
        // return json_encode($pacienteFuncionario);
    }

    public function alterarInfPessoal($paciente)
    {
        try {
            $sql = 'update pessoa as p inner join saude_fichamedica as s on p.id_pessoa=s.id_pessoa set tipo_sanguineo=:tipo_sanguineo where id_fichamedica=:id_fichamedica';

            $sql = str_replace("'", "\'", $sql);
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare($sql);
            $stmt = $pdo->prepare($sql);
            $id_fichamedica = $paciente->getId_pessoa();
            $tipoSanguineo = $paciente->getTipoSanguineo();
            $stmt->bindParam(':id_fichamedica', $id_fichamedica);
            $stmt->bindParam(':tipo_sanguineo', $tipoSanguineo);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    /**Recebe como parâmetro o id de uma ficha médica e o id de um paciente, instancia um objeto do tipo PDO e insere no banco de dados uma nova linha em saude_fichamedica_historico */
    public function adicionarProntuarioAoHistorico($idFicha, $idPaciente)
    {
        $sql2 = "INSERT INTO saude_fichamedica_historico (id_pessoa, data) VALUES (:idPessoa, :data)";

        try {
            $pdo = Conexao::connect();

            date_default_timezone_set('America/Sao_Paulo');
            $data = date('Y-m-d H:i:s');

            $pdo->beginTransaction();
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->bindParam(':idPessoa', $idPaciente);
            $stmt2->bindParam(':data', $data);
            $stmt2->execute();

            $ultimoID = $pdo->lastInsertId();

            if ($this->insercaoDescricaoHistoricoEmCadeia($idFicha, $pdo, $ultimoID)) {
                $pdo->commit();
            } else {
                $pdo->rollBack();
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }


    /**Recebe como parâmetros o id de uma ficha médica, um objeto do tipo PDO e o id de uma ficha médica do histórico, puxa todas as descrições correspondentes ao id da ficha médica informada e insere na tabela saude_fichamedica_historico_descricoes com o campo id_fichamedica_historico com o valor passado para o idFichaHistorico */
    public function insercaoDescricaoHistoricoEmCadeia($idFicha, PDO $pdo, $idFichaHistorico)
    {
        $sql1 = "SELECT descricao from saude_fichamedica_descricoes WHERE id_fichamedica=:idFicha";
        $sql2 = "INSERT INTO saude_fichamedica_historico_descricoes (id_fichamedica_historico, descricao) VALUES (:idFichaHistorico, :descricao)";
        try {
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->bindParam(':idFicha', $idFicha);
            $stmt1->execute();

            $descricoes = $stmt1->fetchAll(PDO::FETCH_ASSOC);
            $stmt2 = $pdo->prepare($sql2);

            foreach ($descricoes as $descricao) {
                $texto = $descricao['descricao'];
                $stmt2->bindParam(":idFichaHistorico", $idFichaHistorico);
                $stmt2->bindParam(":descricao", $texto);
                $stmt2->execute();
            }
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**Recebe como parâmetro o id de um paciente e retorna um array com todos id's dos prontuários públicos salvos no histórico daquele paciente. */
    public function listarProntuariosDoHistorico($idPaciente)
    {
        $sql = 'SELECT id_fichamedica_historico as idHistorico, data FROM saude_fichamedica_historico WHERE id_pessoa=:idPaciente';

        try {
            $pdo = Conexao::connect();

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idPaciente', $idPaciente);
            $stmt->execute();

            $prontuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $prontuarios;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**Recebe como parâmetro o id de uma fica médica no histórico e retorna um array com todas as descrições correspondentes aquele prontuário público */
    public function listarDescricoesHistoricoPorId($idHistorico)
    {
        $sql = 'SELECT descricao FROM saude_fichamedica_historico_descricoes WHERE id_fichamedica_historico=:idHistorico';

        try {
            $pdo = Conexao::connect();

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idHistorico', $idHistorico);
            $stmt->execute();

            $descricoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $descricoes;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
