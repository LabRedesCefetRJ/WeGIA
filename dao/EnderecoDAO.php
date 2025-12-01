<?php

error_reporting(0);
ini_set('display_errors', 0 );
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Conexao.php';

class EnderecoDAO
{
    public function incluirEndereco($endereco)
    {
        var_dump($endereco);
        try {
            $sql = 'call insendereco_inst(:nome,:numero_endereco,:logradouro,:bairro,:cidade,:estado,:cep,:complemento,:ibge)';
            
            $sql = str_replace("'", "\'", $sql);

            $pdo = Conexao::connect();
            $stmt = $pdo->prepare($sql);
            $nome=$endereco->getNome();
            $cep=$endereco->getCep();
            $estado=$endereco->getEstado();
            $cidade=$endereco->getCidade();
            $bairro=$endereco->getBairro();
            $logradouro=$endereco->getLogradouro();
            $numero_endereco=$endereco->getNumeroEndereco();        
            $complemento=$endereco->getComplemento();
            $ibge=$endereco->getIbge();

            $stmt->bindParam(':nome',$nome);
            $stmt->bindParam(':cep',$cep);
            $stmt->bindParam(':estado',$estado);
            $stmt->bindParam(':cidade',$cidade);
            $stmt->bindParam(':bairro',$bairro);
            $stmt->bindParam(':logradouro',$logradouro);
            $stmt->bindParam(':numero_endereco',$numero_endereco);        
            $stmt->bindParam(':complemento',$complemento);
            $stmt->bindParam(':ibge',$ibge);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    public function listarInstituicao()
    {
        try{
            $endereco=array();
            $pdo = Conexao::connect();
            $id = new EnderecoDAO;
            $numeroId = $id->listarId();
            $numeroId = $numeroId[0]['id'];
            $consulta = $pdo->query("SELECT bairro, cep, cidade, complemento, estado, ibge, logradouro, nome, numero_endereco FROM endereco_instituicao WHERE id_inst='$numeroId'");
            $endereco = Array();
            $x=0;
            while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){
                $endereco[$x]=array('bairro'=>$linha['bairro'],'cep'=>$linha['cep'],'nome'=>$linha['nome'],'cidade'=>$linha['cidade'], 'complemento'=>$linha['complemento'], 'estado'=>$linha['estado'], 'ibge'=>$linha['ibge'], 'logradouro'=>$linha['logradouro'], 'numero_endereco'=>$linha['numero_endereco']);
                $x++;
            }
            } catch (PDOException $e){
                echo 'Error:' . $e->getMessage();
            }
            return json_encode($endereco); //refatorar esse método, DAO não deve retornar JSON!
    }

    public function alterarEndereco($endereco)
    {
        try{

            $id = new EnderecoDAO;
            $numeroId = $id->listarId();
            $numeroId = $numeroId[0]['id'];
            $sql = "update endereco_instituicao set nome=:nome,numero_endereco=:numero_endereco,logradouro=:logradouro,bairro=:bairro,cidade=:cidade,estado=:estado,cep=:cep,complemento=:complemento,ibge=:ibge where id_inst='$numeroId'";
            
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare($sql);
            
            $stmt = $pdo->prepare($sql);
            $nome=$endereco->getNome();
            $cep=$endereco->getCep();
            $estado=$endereco->getEstado();
            $cidade=$endereco->getCidade();
            $bairro=$endereco->getBairro();
            $logradouro=$endereco->getLogradouro();
            $numero_endereco=$endereco->getNumeroEndereco();        
            $complemento=$endereco->getComplemento();
            $ibge=$endereco->getIbge();

            $stmt->bindParam(':nome',$nome);
            $stmt->bindParam(':cep',$cep);
            $stmt->bindParam(':estado',$estado);
            $stmt->bindParam(':cidade',$cidade);
            $stmt->bindParam(':bairro',$bairro);
            $stmt->bindParam(':logradouro',$logradouro);
            $stmt->bindParam(':numero_endereco',$numero_endereco);        
            $stmt->bindParam(':complemento',$complemento);
            $stmt->bindParam(':ibge',$ibge);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    public function listarId()
    {
        try{
            $id=array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("SELECT id_inst FROM endereco_instituicao");
            $x=0;
            while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){
                $id[$x]=array('id'=>$linha['id_inst']);
                $x++;
            }
            } catch (PDOException $e){
                echo 'Error:' . $e->getMessage();
            }
            return $id;
    }
}
?>