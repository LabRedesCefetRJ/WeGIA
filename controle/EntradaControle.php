<?php
include_once '../classes/Entrada.php';
include_once '../dao/EntradaDAO.php';
include_once '../classes/Origem.php';
include_once '../dao/OrigemDAO.php';
include_once '../classes/Almoxarifado.php';
include_once '../dao/AlmoxarifadoDAO.php';
include_once '../classes/TipoEntrada.php';
include_once '../dao/TipoEntradaDAO.php';
include_once '../classes/Ientrada.php';
include_once '../dao/IentradaDAO.php';
include_once '../classes/Produto.php';
include_once '../dao/ProdutoDAO.php';
class EntradaControle
{
    public function verificar(){
        session_start();
        extract($_REQUEST);
        date_default_timezone_set('America/Sao_Paulo');
        $horadata = date('Y-m-d H:i');
        $horadata = explode(" ", $horadata);
        $data = $horadata[0];
        $hora = $horadata[1];
        $valor_total = $total_total;
        $responsavel = $_SESSION['id_pessoa'];
        $entrada = new Entrada($data,$hora,$valor_total,$responsavel);
        
        return $entrada;
    }
    
    public function listarTodos(){
        extract($_REQUEST);
        $entradaDAO= new EntradaDAO();
        $origens = $entradaDAO->listarTodos();
        session_start();
        $_SESSION['entrada']=$origens;
        header('Location: ../html/matPat/listar_entrada.php');
    }

    public function listarTodosComProdutos(){
        extract($_REQUEST);
        $entradaDAO= new EntradaDAO();
        $origens = $entradaDAO->listarTodosComProdutos();
        session_start();
        $_SESSION['entrada']=$origens;
        header('Location: ../html/matPat/listar_entrada.php');
    }
    
    public function incluir(){
        extract($_REQUEST);
        $entrada = $this->verificar();

        $entradaDAO = new EntradaDAO();
        $origemDAO = new OrigemDAO();
        $almoxarifadoDAO = new AlmoxarifadoDAO();
        $TipoEntradaDAO = new TipoEntradaDAO();
        $origem = explode("-", $origem);
        $origem = $origem[0];
        $origem = $origemDAO->listarUm($origem);
        $almoxarifado = $almoxarifadoDAO->listarUm($almoxarifado);
        $TipoEntrada =$TipoEntradaDAO->listarUm($tipo_entrada);

        try{
            $entrada->set_origem($origem);
            $entrada->set_almoxarifado($almoxarifado);
            $entrada->set_tipo($TipoEntrada);

            $entradaDAO->incluir($entrada);

            $id_responsavel = $entradaDAO->ultima();
            $id_responsavel = implode("",$id_responsavel);

            $x = 1;
            $id = "id";
            $qtdd = "qtd";
            $valor_unitario = "valor_unitario";
            while($x<=$conta){
                if(isset(${$id.$x})){
                    $ientrada = new Ientrada(${$qtdd.$x},${$valor_unitario.$x});
                    $ientradaDAO = new IentradaDAO();
                    $produtoDAO = new ProdutoDAO();
                    $produto = $produtoDAO->listarUm(${$id.$x});
                    $entrada = $entradaDAO->listarUm($id_responsavel);


                    $ientrada->setId_produto($produto);
                    $ientrada->setId_entrada($entrada);
                    $ientrada = $ientradaDAO->incluir($ientrada);

                }
            $x++;
            header('Location: ../html/matPat/cadastro_entrada.php');
            }
        } catch (PDOException $e){
            $msg= "Não foi possível adicionar a entrada"."<br>".$e->getMessage();
            echo $msg;
        }
    }

    public function listarId(){
        extract($_REQUEST);
        try{
            $entradaDAO = new EntradaDAO();
            $entrada = $entradaDAO->listarId($id_entrada);
            session_start();
            $_SESSION['entrada'] = $entrada;
            echo $_SESSION['entrada'];
            header('Location: ' . $nextPage);
        } catch (PDOException $e) {
            echo "ERROR: " . $e->getMessage();
        }
    }
}
