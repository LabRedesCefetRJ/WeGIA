<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if(session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}else{
    session_regenerate_id();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

require_once "../../dao/Conexao.php";
$pdo = Conexao::connect();

$id_pessoa = $_SESSION['id_pessoa'];

// Usando prepared statements para a primeira consulta
$stmt = $pdo->prepare("SELECT id_cargo FROM funcionario WHERE id_pessoa = :id_pessoa");
$stmt->bindParam(':id_pessoa', $id_pessoa, PDO::PARAM_INT);
$stmt->execute();

$id_cargo = $stmt->fetchColumn();

if ($id_cargo !== false) {
    // Usando prepared statements para a segunda consulta
    $stmt = $pdo->prepare("
        SELECT p.id_acao 
        FROM permissao p 
        JOIN acao a ON p.id_acao = a.id_acao 
        JOIN recurso r ON p.id_recurso = r.id_recurso 
        WHERE p.id_cargo = :id_cargo AND r.id_recurso = '51'
    ");
    $stmt->bindParam(':id_cargo', $id_cargo, PDO::PARAM_INT);
    $stmt->execute();

    $permissao = $stmt->fetchColumn();

    if ($permissao === false || $permissao < 7) {
        $msg = "Você não tem as permissões necessárias para essa página.";
        header("Location: ../home.php?msg_c=" . urlencode($msg));
        exit();
    }
} else {
    $msg = "Você não tem as permissões necessárias para essa página.";
    header("Location: ../home.php?msg_c=" . urlencode($msg));
    exit();
}

      

// caso paciente ser atendido //
$nome = $pdo->query("SELECT p.id_pessoa, p.nome, p.sobrenome FROM pessoa p JOIN atendido a ON(p.id_pessoa=a.pessoa_id_pessoa) WHERE a.atendido_status_idatendido_status = 1")->fetchAll(PDO::FETCH_ASSOC);

$idsPessoas = $pdo->query("SELECT p.id_pessoa FROM pessoa p JOIN atendido a ON(p.id_pessoa=a.pessoa_id_pessoa) WHERE a.atendido_status_idatendido_status = 1")->fetchAll(PDO::FETCH_ASSOC);

$idsPessoasFichasMedicas = $pdo->query("SELECT id_pessoa FROM saude_fichamedica")->fetchAll(PDO::FETCH_ASSOC);

$idPes = array();
$idPesCadastradas = array();
$idsVerificados = array();
$nomesCertos = array();

// add o id pessoa da pessoa em um array//
foreach($idsPessoas as $valor){
    array_push($idPes, $valor['id_pessoa']);
}

// add o id da saude num array//
foreach($idsPessoasFichasMedicas as $value){
    array_push($idPesCadastradas, $value['id_pessoa']);
}

//pego um array e se não tiver no array cadastrado, add no verificado//
foreach($idPes as $val){
    if(!in_array($val, $idPesCadastradas))
     {
         array_push($idsVerificados, $val);
     }
}

// pego o id, nome e sobrenome e se o tiver dentro do verificado, add ele aos nomes
//certos
foreach($nome as $va)
{
    if(in_array($va["id_pessoa"], $idsVerificados))
    {
        array_push($nomesCertos, $va);
    }
}

// caso o paciente seja funcionario //
$nome_funcionario = $pdo->query("SELECT p.id_pessoa, p.nome, p.sobrenome FROM pessoa p JOIN funcionario f ON(p.id_pessoa=f.id_pessoa) WHERE p.id_pessoa != 1 AND f.id_situacao = 1")->fetchAll(PDO::FETCH_ASSOC);

$idsPessoas2 = $pdo->query("SELECT p.id_pessoa FROM pessoa p JOIN funcionario f ON(p.id_pessoa=f.id_pessoa) WHERE p.id_pessoa != 1 AND f.id_situacao = 1")->fetchAll(PDO::FETCH_ASSOC);

$idsPessoasFichasMedicas2 = $pdo->query("SELECT id_pessoa FROM saude_fichamedica")->fetchAll(PDO::FETCH_ASSOC);

$idFun = array();
$idPesCadastradas2 = array();
$idsVerificados2 = array();
$nomesCertos2 = array();

foreach($idsPessoas2 as $valor2){
    array_push($idFun, $valor2['id_pessoa']);
}

foreach($idsPessoasFichasMedicas2 as $value2){
    array_push($idPesCadastradas2, $value2['id_pessoa']);
}

foreach($idFun as $val2){
    if(!in_array($val2, $idPesCadastradas2))
     {
         array_push($idsVerificados2, $val2);
     }
}

foreach($nome_funcionario as $va2)
{
    if(in_array($va2["id_pessoa"], $idsVerificados2))
    {
        array_push($nomesCertos2, $va2);
    }
}

require_once ROOT."/controle/SaudeControle.php";
require_once ROOT."/html/personalizacao_display.php";
?>

<!DOCTYPE html>
<html class="fixed">
<head>
    <!-- Basic -->
    <meta charset="UTF-8">

    <title>Cadastro ficha médica</title>
        
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="icon" href="<?php display_campo("Logo",'file');?>" type="image/x-icon" id="logo-icon">
 
    <!-- Specific Page Vendor CSS -->
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/select2/select2.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="<?php echo WWW;?>assets/stylesheets/theme.css" />

    <!-- Skin CSS -->
    <link rel="stylesheet" href="<?php echo WWW;?>assets/stylesheets/skins/default.css" />

    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="<?php echo WWW;?>assets/stylesheets/theme-custom.css"> 

    <!-- Head Libs -->
    <script src="<?php echo WWW;?>assets/vendor/modernizr/modernizr.js"></script>

    <!-- Vermelho dos campos obrigatórios -->
    <style type="text/css">
	  .obrig {
      color: rgb(255, 0, 0);
         }
    </style>
        
    <!-- Vendor -->
    <script src="<?php echo WWW;?>assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
        
    <!-- Specific Page Vendor -->
    <script src="<?php echo WWW;?>assets/vendor/jquery-autosize/jquery.autosize.js"></script>
        
    <!-- Theme Base, Components and Settings -->
    <script src="<?php echo WWW;?>assets/javascripts/theme.js"></script>
        
    <!-- Theme Custom -->
    <script src="<?php echo WWW;?>assets/javascripts/theme.custom.js"></script>
        
    <!-- Theme Initialization Files -->
    <script src="<?php echo WWW;?>assets/javascripts/theme.init.js"></script>


    <!-- javascript functions -->
    <script src="<?php echo WWW;?>Functions/onlyNumbers.js"></script>
    <script src="<?php echo WWW;?>Functions/onlyChars.js"></script>
    <script src="<?php echo WWW;?>Functions/mascara.js"></script>

    <!-- jkeditor -->
    <script src="<?php echo WWW;?>assets/vendor/ckeditor/ckeditor.js"></script>
        
    <!-- jquery functions -->

    <script>

        $(function(){
            var funcionario=[];
            $.each(funcionario,function(i,item){
                $("#destinatario")
                    .append($("<option id="+item.id_pessoa+" value="+item.id_pessoa+" name="+item.id_pessoa+">"+item.nome+" "+item.sobrenome+"</option>"));
            });
            $("#header").load("../header.php");
            $(".menuu").load("../menu.php");


            var editor = CKEDITOR.replace('despacho');
            editor.on('required', function(e){
                alert("Por favor, informe o prontuário público!");
                e.cancel();
            });
            
        });
    </script>    
    
    

    <style type="text/css">
        .select{
            position: absolute;
            width: 235px;
        }
        .select-table-filter{
            width: 140px;
            float: left;
        }
        .panel-body{
            margin-bottom: 15px;
        }
        img{
        	margin-left:10px;
        }
        #div_texto
        {
            width: 100%;
        }
        #cke_despacho
        {
            height: 500px;
        }
        .cke_inner
        {
            height: 500px;
        }
        #cke_1_contents
        {
            height: 455px !important;
        }
        .col-md-3 {
            width: 10%;
        }
    </style>
</head>

<body>
    <section class="body">
        <!-- start: header -->
        <div id="header"></div>
        <!-- end: header -->
        <div class="inner-wrapper">
            <!-- start: sidebar -->
            <aside id="sidebar-left" class="sidebar-left menuu"></aside>
            <!-- end: sidebar -->
            <section role="main" class="content-body">
                <header class="page-header">
                    <h2>Cadastro ficha médica</h2>
                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li>
                                <a href="../home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li><span>Cadastro ficha médica</span></li>
                        </ol>
                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>
               

                <div class="row">
                    <div class="col-md-8 col-lg-12">
                        <div class="tabs">
                            <ul class="nav nav-tabs tabs-primary">
                                <li class="active">
                                    <a href="#overview" data-toggle="tab">Cadastro ficha médica</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div id="overview" class="tab-pane active">
                                    <form class="form-horizontal"  id="doc" method="POST" action="../../controle/control.php">
                                    <section class="panel">  
                                        <header class="panel-heading">
                                            <div class="panel-actions">
                                                <a href="#" class="fa fa-caret-down"></a>
                                            </div>
                                            <h2 class="panel-title">Informações Pessoais</h2>
                                        </header>
                                        <div class="panel-body">    
                                            <h5 class="obrig">Campos Obrigatórios(*)</h5>
                                            <br>

                                            <div class="form-group" id="teste">
                                                <label class="col-md-3 control-label" for="profileLastName" style="padding-left:29px;">Selecione:<sup class="obrig">*</sup></label> 
                                                <div class="col-md-8">
                                                <label><input type="radio" name="gender" id="bolinha_atendido" value="atendido" style="margin-top: 10px; margin-left: 15px;" onclick="return exibirAtendido()" required>  Atendido</label>
                                                <label><input type="radio" name="gender" id="bolinha_funcionario" value="f" style="margin-top: 10px; margin-left: 15px;" onclick="return exibirFuncionario()" required>  Funcionário </label>
                                                </div>
                                            </div>
                                
                                            <div class="form-group">
                                                <div id="clicado" style="display:none;">
                                                    <label class="col-md-3 control-label" for="inputSuccess" style="padding-left:29px;">Paciente:<sup class="obrig">*</sup></label>
                                                    <div class="col-md-6">
                                                        <select class="form-control input-lg mb-md" name="nome" id="nome" required>
                                                            <option selected disabled>Selecionar</option>
                                                            
                                                        </select><br>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class='col-md-6' id='div_texto' style="height: 499px;">
                                                    <label for="texto" id="etiqueta_despacho" style="padding-left: 15px;">Prontuário público:<sup class="obrig">*</sup></label>
                                                    <textarea cols='30' rows='5' required id='despacho' name='texto' class='form-control'></textarea>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="panel-footer">
                                                <div class='row'>
                                                    <div class="col-md-9 col-md-offset-3">
                                                        <input type="hidden" name="nomeClasse" value="SaudeControle">
                                                        <input type="hidden" name="metodo" value="incluir">
                                                        <input id="enviar" type="submit" class="btn btn-primary" value="Enviar">
                                                    </div>
                                                </div>
                                                </form>
                                            </div>
                                        </div>
                                    </section>
                                </div>      <!-- </form> -->
                            </div> 
                        </div>
                    </div>
                </div>
                <!-- </div> -->
            
            </section>
        </div>
    </section><!--section do body-->
    <?php
          $nomesCertos = json_encode($nomesCertos);
          $nomesCertos2 = json_encode($nomesCertos2);
    ?>
    <script>
       
        function exibirAtendido(){

            var atendido = <?= $nomesCertos ?>;
            $("#nome").empty();
            $.each(atendido, function(i, item){
                $("#nome").append($("<option value="+item.id_pessoa +">").text(item.nome + " " + item.sobrenome));
            })
            $("#clicado").show(); 
        }

        function exibirFuncionario(){
            
            var funcionario = <?= $nomesCertos2 ?>;
            $("#nome").empty();
            $.each(funcionario, function(i, item){
                $("#nome").append($("<option value="+item.id_pessoa +">").text(item.nome + " " + item.sobrenome));
            })
            $("#clicado").show();
        }

    </script>
        
    <!-- end: page -->
    <!-- Vendor -->
        <script src="<?php echo WWW;?>assets/vendor/select2/select2.js"></script>
        <script src="<?php echo WWW;?>assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
        <script src="<?php echo WWW;?>assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
        <script src="<?php echo WWW;?>assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
        
        <!-- Theme Base, Components and Settings -->
        <!-- <script src="<?php echo WWW;?>assets/javascripts/theme.js"></script> -->
        
        <!-- Theme Custom -->
        <script src="<?php echo WWW;?>assets/javascripts/theme.custom.js"></script>
        
        <!-- Theme Initialization Files -->
        <script src="<?php echo WWW;?>assets/javascripts/theme.init.js"></script>
        <!-- Examples -->
        <script src="<?php echo WWW;?>assets/javascripts/tables/examples.datatables.default.js"></script>
        <script src="<?php echo WWW;?>assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
        <script src="<?php echo WWW;?>assets/javascripts/tables/examples.datatables.tabletools.js"></script>
	
	<div align="right">
	<iframe src="https://www.wegia.org/software/footer/saude.html" width="200" height="60" style="border:none;"></iframe>
	</div>
    </body>
</html>
