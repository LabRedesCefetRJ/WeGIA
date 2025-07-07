<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inclusão segura do config.php
$config_path = "config.php";
$max_depth = 2000;
$depth = 0;

while (!file_exists($config_path) && $depth < $max_depth) {
    $config_path = "../" . $config_path;
    $depth++;
}

if ($depth === $max_depth || !realpath($config_path) || basename($config_path) !== 'config.php') {
    die("Erro ao localizar o arquivo de configuração.");
}

require_once($config_path);

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: " . WWW . "html/index.php");
    exit();
}

// Função para negar acesso com redirecionamento
function negar_acesso($msg = "Você não tem as permissões necessárias para essa página.") {
    header("Location: ../../home.php?msg_c=" . urlencode($msg));
    exit();
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id_pessoa = $_SESSION['id_pessoa'];

    // Obter cargo
    $stmt = $pdo->prepare("SELECT id_cargo FROM funcionario WHERE id_pessoa = :id_pessoa");
    $stmt->execute(['id_pessoa' => $id_pessoa]);
    $id_cargo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$id_cargo) {
        negar_acesso();
    }

    // Verificar permissão
    $stmt = $pdo->prepare("
        SELECT a.id_acao FROM permissao p
        JOIN acao a ON p.id_acao = a.id_acao
        JOIN recurso r ON p.id_recurso = r.id_recurso
        WHERE p.id_cargo = :id_cargo 
          AND a.descricao = 'LER, GRAVAR E EXECUTAR'
          AND r.descricao = 'Cadastrar Pet'
    ");
    $stmt->execute(['id_cargo' => $id_cargo['id_cargo']]);
    $permissao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$permissao || $permissao['id_acao'] < 5) {
        negar_acesso();
    }

} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}


// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";
?>

<!DOCTYPE html>

<html class="fixed">
<head>
    <!-- Basic -->
    <meta charset="UTF-8">

    <title>Cadastro de Adotante</title>
        
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <!-- <link rel="icon" href="<?php //display_campo("Logo",'file');?>" type="image/x-icon" id="logo-icon"> -->

    <!-- Specific Page Vendor CSS -->
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/select2/select2.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="<?php echo WWW;?>assets/stylesheets/theme.css" />

    <!-- Skin CSS -->
    <link rel="stylesheet" href="<?php echo WWW;?>/assets/stylesheets/skins/default.css" />

    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="<?php echo WWW;?>assets/stylesheets/theme-custom.css">

    <!-- Head Libs -->
    <script src="<?php echo WWW;?>assets/vendor/modernizr/modernizr.js"></script>
        
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
    <script src="<?php echo WWW;?>Functions/testaCPF.js"></script>

    <!-- printThis -->
    <script src="<?php echo WWW;?>assets/vendor/jasonday-printThis-f73ca19/printThis.js"></script>

        
    <!-- jquery functions -->

    <script>
    $(function(){
        $("#header").load("<?php echo WWW;?>html/header.php");
        $(".menuu").load("<?php echo WWW;?>html/menu.php");
    });

    </script>
 
    <style type="text/css">
        .select{
            position: absolute;
            width: 235px;
        }

        .panel-body
        {
            margin-bottom: 15px;
        }

        img
        {
          margin-left:11px;
        }
        /* print styles*/
        @media print {
            .printable {
                display: block;
             }
            .screen {
                display: none;
            }
        }
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
       #area1{
            display: block;

        }
        #area2{
            display: none;
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
                        <h2>Cadastro</h2>
                        <div class="right-wrapper pull-right">
                            <ol class="breadcrumbs">
                                <li>
                                    <a href="<?php echo WWW;?>html/home.php">
                                        <i class="fa fa-home"></i>
                                    </a>
                                </li>
                                <li><span>Digite seu CPF</span></li>
                            </ol>
                            <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                        </div>
                    </header>
                
                    <!-- start: page -->
 

                        <section class="panel" >
                        <?php
									
                                    if (isset($_GET['msg_c'])) {
                                        $msg_raw = $_GET['msg_c'];
                                    
                                        if (strpos($msg_raw, '<') === false && strpos($msg_raw, '>') === false) {
                                            $msg = htmlspecialchars($msg_raw, ENT_QUOTES, 'UTF-8');
                                            echo('<div class="alert alert-success" role="alert">' . $msg . '</div>');
                                        }
                                    
                                    } else if (isset($_GET['msg_e'])) {
                                        $msg_raw = $_GET['msg_e'];
                                    
                                        if (strpos($msg_raw, '<') === false && strpos($msg_raw, '>') === false) {
                                            $msg = htmlspecialchars($msg_raw, ENT_QUOTES, 'UTF-8');
                                            echo('<div class="alert alert-danger" role="alert">' . $msg . '</div>');
                                        }
                                    }
                                    
                                    
							?>
                            <header class="panel-heading">
                                <h2 class="panel-title">Digite seu CPF</h2>
                            </header>
                            <div class="panel-body">
                           
                            <form method="GET" action="./cadastro_adotante.php">
                                    <!-- <label class="col-md-3 control-label" for="cpf">Número do CPF<sup class="obrig">*</sup></label> -->
                                    <input type="text" class="form-control" id="cpf" id="cpf" name="cpf" placeholder="Ex: 222.222.222-22" maxlength="14" onblur="validarCPF(this.value)" onkeypress="return Onlynumbers(event)" onkeyup="mascara('###.###.###-##',this,event)" required>
                                    <p id="cpfInvalido" style="display: none; color: #b30000">CPF INVÁLIDO!</p>
                                    <br>
                                    <input type='submit' value='Enviar' id='enviar' class='mb-xs mt-xs mr-xs btn btn-primary'> 
                                </form>
                            </div>
                        </section>
                </section>
            </div>
        </section>
        <script>
            function validarCPF(strCPF) {

                if (!testaCPF(strCPF)) {
                $('#cpfInvalido').show();
                document.getElementById("enviar").disabled = true;

                } else {
                $('#cpfInvalido').hide();

                document.getElementById("enviar").disabled = false;
                }
            }
        </script>
    <!-- end: page -->
    <!-- Vendor -->
        <script src="../../Functions/onlyNumbers.js"></script>
        <script src="../../Functions/onlyChars.js"></script>
        <script src="../../Functions/mascara.js"></script>
        <script src="../../Functions/lista.js"></script>
        <script src="<?php echo WWW;?>assets/vendor/select2/select2.js"></script>
        <script src="<?php echo WWW;?>assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
        <script src="<?php echo WWW;?>assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
        <script src="<?php echo WWW;?>assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
        
        <!-- Theme Base, Components and Settings -->
        <script src="<?php echo WWW;?>assets/javascripts/theme.js"></script>
        
        <!-- Theme Custom -->
        <script src="<?php echo WWW;?>assets/javascripts/theme.custom.js"></script>
        
        <!-- Theme Initialization Files -->
        <script src="<?php echo WWW;?>assets/javascripts/theme.init.js"></script>
        <!-- Examples -->
        <script src="<?php echo WWW;?>assets/javascripts/tables/examples.datatables.default.js"></script>
        <script src="<?php echo WWW;?>assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
        <script src="<?php echo WWW;?>assets/javascripts/tables/examples.datatables.tabletools.js"></script>
      
	<div align="right">
		<iframe src="https://www.wegia.org/software/footer/pet.html" width="200" height="60" style="border:none;"></iframe>
	</div>   
    </body>

</html>


