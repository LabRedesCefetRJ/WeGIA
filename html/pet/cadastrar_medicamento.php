<?php

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if(!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])){
    header ("Location: ../index.php");
    exit;
}

$config_path = "config.php";
if(file_exists($config_path)){
    require_once($config_path);
} else {
    $max_depth = 10; 
    $current_depth = 0;
    while($current_depth < $max_depth){
        $config_path = "../" . $config_path;
        if(file_exists($config_path)) {
            require_once($config_path);
            break;
        }
        $current_depth++;
    }
    if($current_depth >= $max_depth) {
        die("Arquivo de configuração não encontrado.");
    }
}
require_once "../../dao/Conexao.php";
$pdo = Conexao::connect();

try {
    $id_pessoa = $_SESSION['id_pessoa'];

    $stmt = $pdo->prepare("SELECT id_cargo FROM funcionario WHERE id_pessoa = ?");
    $stmt->execute([$id_pessoa]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        $id_cargo = $resultado['id_cargo'];

        // Check if user has proper permissions
        $stmt = $pdo->prepare(
            "SELECT p.id_acao FROM permissao p 
            JOIN acao a ON p.id_acao = a.id_acao 
            JOIN recurso r ON p.id_recurso = r.id_recurso 
            WHERE p.id_cargo = ? 
            AND a.descricao = 'LER, GRAVAR E EXECUTAR' 
            AND r.descricao = 'Saúde Pet'"
        );
        $stmt->execute([$id_cargo]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado && $resultado['id_acao'] == 7) { 
            $permissao = $resultado['id_acao'];
        } else {
            $msg = "Você não tem as permissões necessárias para essa página.";
            header("Location: ../home.php?msg_c=" . urlencode($msg));
            exit;
        }
    } else {
        $permissao = 1;
        $msg = "Você não tem as permissões necessárias para essa página.";
        header("Location: ../home.php?msg_c=" . urlencode($msg));
        exit;
    }
} catch (PDOException $e) {
    error_log("Erro de banco de dados: " . $e->getMessage());
    header("Location: ../error.php?msg=Erro ao acessar o banco de dados");
    exit;
}

$stmt = $pdo->prepare("SELECT p.id_pet, p.nome FROM pet p");
$stmt->execute();
$nome = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT p.id_pet FROM pet p");
$stmt->execute();
$idsPets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT id_pet FROM pet_ficha_medica");
$stmt->execute();
$idsPetsFichaMedica = $stmt->fetchAll(PDO::FETCH_ASSOC);

$idPet = array();
$idPetsCadastrados = array();
$idsVerificados = array();
$nomesCertos = array();

foreach($idsPets as $valor){
    array_push($idPet, $valor['id_pet']);
}

// adiciona o id do saudePet a um array
foreach($idsPetsFichaMedica as $value){
    array_push($idPetsCadastrados, $value['id_pet']);
}

//pego um array e se não tiver no array cadastrado, add no verificado//
foreach($idPet as $val){
    if(!in_array($val, $idPetsCadastrados))
     {
         array_push($idsVerificados, $val);
     }
}

// pego o id e nome e se estiver tiver dentro do verificado, add ele aos nomes
//certos
foreach($nome as $va)
{
    if(in_array($va["id_pet"], $idsVerificados))
    {
        array_push($nomesCertos, $va);
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

    <title>Cadastro medicamento para pets</title>
        
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="icon" href="<?php display_campo("Logo",'file');?>" type="image/x-icon" id="logo-icon">

    <!-- Specific Page Vendor CSS -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/select2/select2.css" />
    <link rel="stylesheet" href="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/stylesheets/theme.css" />

    <!-- Skin CSS -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/stylesheets/skins/default.css" />

    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/stylesheets/theme-custom.css"> 

    <!-- Head Libs -->
    <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/modernizr/modernizr.js"></script>

    <!-- Vermelho dos campos obrigatórios -->
    <style type="text/css">
	  .obrig {
      color: rgb(255, 0, 0);
         }
    </style>
        
    <!-- Vendor -->
    <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
    <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
        
    <!-- Specific Page Vendor -->
    <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/jquery-autosize/jquery.autosize.js"></script>
        
    <!-- Theme Base, Components and Settings -->
    <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/theme.js"></script>
        
    <!-- Theme Custom -->
    <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/theme.custom.js"></script>
        
    <!-- Theme Initialization Files -->
    <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/theme.init.js"></script>


    <!-- javascript functions -->
    <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>Functions/onlyNumbers.js"></script>
    <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>Functions/onlyChars.js"></script>
    <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>Functions/mascara.js"></script>

    <!-- jkeditor -->
    <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/ckeditor/ckeditor.js"></script>
        
    <!-- jquery functions -->

    <script>

        $(function(){
            var pet= JSON.parse('<?php echo json_encode($nomesCertos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>');
            $.each(pet,function(i,item){
                console.log("ID: " + item.id_pet);
                console.log("NOME: " + item.nome);
                $("#destinatario")
                    .append($("<option>").val(item.id_pet).text(item.nome));
            });
            $("#header").load("../header.php");
            $(".menuu").load("../menu.php");


            var editor = CKEDITOR.replace('despacho');
            
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
                    <h2>Cadastro medicamento para pets</h2>
                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li>
                                <a href="../home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li><span>Pet</span></li>
                            <li><span>Cadastro medicamento para pets</span></li>
                        </ol>
                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>
               

                <div class="row">
                    <div class="col-md-8 col-lg-12">
                        <div class="tabs">
                            <ul class="nav nav-tabs tabs-primary">
                                <li class="active">
                                    <a href="#overview" data-toggle="tab">Cadastro medicamento pet</a>
                                </li>
                            </ul>
                                <div id="overview" class="tab-pane active">
                                    <form class="form-horizontal" id="doc" method="POST" action="../../controle/control.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <section class="panel">  
                                        <header class="panel-heading">
                                            <div class="panel-actions">
                                                <a href="#" class="fa fa-caret-down"></a>
                                            </div>
                                            <h2 class="panel-title">Medicamento do Pet</h2>
                                        </header>
                                        <div class="panel-body">    
                                            <h5 class="obrig">Campos Obrigatórios(*)</h5>
                                            <br>
                                            <div id="medicamento" class="tab-pane">
                                            <section class="panel">
                                                <div id="divMedicamento" class="panel-body">
                                                    <form class="form-horizontal" method="post" action="../../controle/control.php">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="nomeClasse" value="MedicamentoControle">
                                                    <input type="hidden" name="metodo" value="adicionarMedicamento">
                                                    <input type="hidden" name="modulo" value="pet">
                                                    <fieldset>

                                                        <div class="form-group">
                                                        <label class="col-md-3 control-label" for="profileCompany">Nome:<sup class="obrig">*</sup></label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="nomeMedicamento" id="nomeMedicamento" required>
                                                            <input type="hidden" name="id" id="oculto">
                                                        </div>
                                                        </div>

                                                        <div class="form-group">
                                                        <label class="col-md-3 control-label" for="profileCompany">Aplicação:<sup class="obrig">*</sup></label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="aplicacaoMedicamento" id="aplicacaoMedicamento" required>
                                                        </div>
                                                        </div>
                                                        
                                                        </br>
                                                        </fieldset>
                                                    </form>
                                                </div>
                                            </section>                  
                                            </div> 

                                            <div class="form-group">
                                                <div class="form-group">
                                                <div class='col-md-6' id='div_texto' style="height: 499px;"><!--necessidades especiais?-->
                                                    <label for="texto" id="etiqueta_despacho" style="padding-left: 15px;">Descricão:<sup class="obrig">*</sup></label>
                                                    <textarea cols='30' rows='5' required id='despacho' name='descricaoMedicamento' class='form-control'></textarea>
                                                </div>
                                            </div>
                                            <br>
                                        </div> 
                                            <div class="panel-footer">
                                                <div class='row'>
                                                    <div class="col-md-9 col-md-offset-3">
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
    <!-- end: page -->
    <!-- Vendor -->
        <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/select2/select2.js"></script>
        <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
        <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
        <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
        
        <!-- Theme Base, Components and Settings -->
        <!-- <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/theme.js"></script> -->
        
        <!-- Theme Custom -->
        <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/theme.custom.js"></script>
        
        <!-- Theme Initialization Files -->
        <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/theme.init.js"></script>
        <!-- Examples -->
        <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/tables/examples.datatables.default.js"></script>
        <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
        <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/tables/examples.datatables.tabletools.js"></script>

        <script>
            let oculto = document.querySelector("#oculto");
            let marca = window.location.href + '';
            marca = marca.split('=');
            if(marca[1]){
                oculto.value = marca[1];
            }
        </script>
        <div align="right">
	        <iframe src="https://www.wegia.org/software/footer/pet.html" width="200" height="60" style="border:none;"></iframe>
        </div>
        <!--fim-->
    </body>
</html>