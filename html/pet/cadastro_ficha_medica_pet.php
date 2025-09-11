<?php

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

session_start();

if(!isset($_SESSION['usuario'])){
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
        
        if ($resultado && $resultado['id_acao'] >= 7) {
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

require_once ROOT."/controle/SaudeControle.php";
require_once ROOT."/html/personalizacao_display.php";

?>

<!DOCTYPE html>
<html class="fixed">
<head>
 <!-- Basic -->
 <meta charset="UTF-8">
    <title>Perfil Pet</title>
    <meta name="keywords" content="HTML5 Admin Template" />
    <meta name="description" content="Porto Admin - Responsive HTML5 Template">
    <meta name="author" content="okler.net">
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <!-- Web Fonts  -->
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">
    <!-- Theme CSS -->
    <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
    <!-- Skin CSS -->
    <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
    <link rel="stylesheet" href="../../css/profile-theme.css" />
    <!-- Head Libs -->
    <script src="../../assets/vendor/modernizr/modernizr.js"></script>
    <script src="../../Functions/onlyNumbers.js"></script>
    <script src="../../Functions/onlyChars.js"></script>
    <script src="../../Functions/mascara.js"></script>
    <script src="../../Functions/lista.js"></script>
    <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">
    <!-- Specific Page Vendor CSS -->
    <link rel="st
      alert('oi');ylesheet" href="../../assets/vendor/select2/select2.css" />
    <link rel="stylesheet" href="../../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
    <!-- Theme CSS -->
    <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
    <!-- Skin CSS -->
    <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
    <!-- Head Libs -->
    <script src="../../assets/vendor/modernizr/modernizr.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <!-- Vendor -->
    <script src="../../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
    <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
    <!-- printThis -->
    <script src="<?php echo WWW;?>assets/vendor/jasonday-printThis-f73ca19/printThis.js"></script>
    <link rel="stylesheet" href="../../assets/stylesheets/print.css">
    <!-- jkeditor -->
    <script src="<?php echo WWW;?>assets/vendor/ckeditor/ckeditor.js"></script>
    <!-- jquery functions -->

    <script>

        $(function() {

        $("#header").load("../header.php");
        $(".menuu").load("../menu.php");
        });


document.addEventListener("DOMContentLoaded", async () => {

    document.querySelector("#editarFichaMedica").addEventListener("click", () => {
    // Habilita textarea
    document.querySelector("#despacho").removeAttribute("disabled");

    // Habilita os radios de castrado
    document.querySelectorAll('input[name="castrado"]').forEach(input => {
        input.removeAttribute("disabled");
    });
});


    let nomePet = document.querySelector("#nome");
    let url = '../../controle/control.php?modulo=pet&nomeClasse=PetControle&metodo=listarPets';

    try {
        let resposta = await fetch(url);
        let dados = await resposta.json();

        while(nomePet.firstChild) nomePet.removeChild(nomePet.firstChild);

        const opcaoNull = document.createElement('option');
        opcaoNull.text = "Selecionar";
        opcaoNull.disabled = true;
        opcaoNull.selected = true;
        nomePet.appendChild(opcaoNull);

        dados.forEach(dado => {
            const opcao = document.createElement('option');
            opcao.dataset.id = dado.id_pet;
            opcao.text = dado.nome;
            nomePet.appendChild(opcao);
        });

    } catch (erro) {
        alert("Erro ao carregar pets: " + erro);
    }
    let idPet = "";
    // Quando mudar o pet
    nomePet.addEventListener("change", async (e) => {
        let descricao = document.querySelector("#despacho");
        descricao.textContent = "";

        let castradoS = document.querySelector("#radioS");
        let castradoN = document.querySelector("#radioN");
        castradoS.checked = false;
        castradoN.checked = false;

        idPet = e.target.selectedOptions[0].dataset.id;
        let urlFicha = `../../controle/control.php?modulo=pet&nomeClasse=controleSaudePet&metodo=getFichaMedicaPet&idPet=${idPet}`;

        try {
            let resposta = await fetch(urlFicha);
            let dados = await resposta.json();

            if(dados){
                let idFichaMedica = document.querySelector("#idFichaMedica");
                if(idFichaMedica) idFichaMedica.value = dados['id_ficha_medica'];

                descricao.textContent = dados['necessidades_especiais'];

                if(dados['castrado'] === 'S') castradoS.checked = true;
                else castradoN.checked = true;
            }

        } catch (erro) {
            alert("Erro ao carregar pets: " + erro);
        }
    });

    // Submit do formulário
    document.querySelector("#doc").addEventListener("submit", async (e) => {
    e.preventDefault();

    let fichaMedica = {
        id_pet: idPet,
        necessidadesEspeciais: document.querySelector("#despacho").value,
        castrado: document.querySelector('input[name="castrado"]:checked')?.value || '',
        metodo: "modificarFichaMedicaPet",
        nomeClasse: "controleSaudePet",
        modulo: "pet"
    };

    console.log(fichaMedica);

    let idFicha = document.querySelector("#idFichaMedica").value;

    if (idFicha > 0) {
        const url = "../../controle/control.php";
        const opcoes = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=UTF-8'
            },
            body: JSON.stringify(fichaMedica)
        };

        try {
            let resposta = await fetch(url, opcoes);

            // Verifica se o status HTTP é 200
            if (!resposta.ok) {
                throw new Error("Erro na requisição: " + resposta.status);
            }

            let info = await resposta.json();
            console.log(info);

            // Se o backend retornar {status: "sucesso"}, recarrega a página
            if (info.status && info.status.toLowerCase() === "sucesso") {
                location.reload(); // recarrega a página
            } else {
                alert(info.mensagem || "Ocorreu um problema ao salvar.");
            }

        } catch (erro) {
            alert("Erro ao salvar ficha médica: " + erro);
        }
    }
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
                    <h2>Cadastro ficha médica pets</h2>
                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li>
                                <a href="../home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li><span>Pet</span></li>
                            <li><span>Cadastro ficha médica pets</span></li>
                        </ol>
                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>
               

                <div class="row">
                    <div class="col-md-8 col-lg-12">
                        <div class="tabs">
                            <ul class="nav nav-tabs tabs-primary">
                                <li class="active">
                                    <a href="#overview" data-toggle="tab">Cadastro ficha médica pets</a>
                                </li>
                            </ul>
                                <div id="overview" class="tab-pane active">
                                    <form class="form-horizontal" id="doc">
                                    <!-- Campos existentes -->

                                    <section class="panel">  
                                        <header class="panel-heading">
                                            <div class="panel-actions">
                                                <a href="#" class="fa fa-caret-down"></a>
                                            </div>
                                            <h2 class="panel-title">Informações do Pet</h2>
                                        </header>
                                        <div class="panel-body">    
                                            <h5 class="obrig">Campos Obrigatórios(*)</h5>
                                            <br>

                                            <div class="form-group">
                                                <div id="clicado">
                                                    <label class="col-md-3 control-label" for="inputSuccess" style="padding-left:29px;">Pet atendido:<sup class="obrig">*</sup></label> 
                                                    <div class="col-md-6">
                                                        <select class="form-control input-lg mb-md" name="nome" id="nome"  required>
                                                            
                                                            
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="castrado">Animal Castrado<sup class="obrig">*</sup></label>
                                                <div class="col-md-8">
                                                    <label><input type="radio" name="castrado" id="radioS" id="S" value="s" style="margin-top: 10px; margin-left: 15px;margin-right: 5px;" required disabled><i class="fa fa" style="font-size: 18px;">Sim</i></label>
                                                    <label><input type="radio" name="castrado" id="radioN" id="N" value="n" style="margin-top: 10px; margin-left: 15px;margin-right: 5px;" disabled><i class="fa fa" style="font-size: 18px;">Não</i></label>
                                                </div>
                                            </div>
                                          

                                            <div class="form-group">
                                                <div class="form-group">
                                                <div class='col-md-6' id='div_texto' style="height: 499px;"><!--necessidades especiais?-->
                                                    <label for="texto" id="etiqueta_despacho" style="padding-left: 15px;">Outras informações:</label>
                                                    <textarea cols='30' rows='5' id='despacho' name='texto' class='form-control' disabled></textarea>
                                                </div>
                                            </div>
                                            <br>
                                        </div> 
                                            <div class="panel-footer">
                                                <div class='row'>
                                                    <div class="col-md-9 col-md-offset-3">
                                                        <input type="hidden" name="idFichaMedica" id="idFichaMedica" value="">
                                                        <input id="enviar" type="submit" class="btn btn-primary" value="Enviar">
                                                        <button type="button" id="editarFichaMedica" class="not-printable btn btn-primary">Editar</button>
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
          $nomesCertos = json_encode($nomesCertos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    ?>
    
    <script>
        let pets = JSON.parse('<?= $nomesCertos ?>');
            $.each(pets, function(i, item){
                $("#nome").append($("<option value="+item.id_pet +">").text(item.nome));
            })
    </script>
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

        <!--Pedro-->
        <script>
           

        </script>
            <div align="right">
	            <iframe src="https://www.wegia.org/software/footer/pet.html" width="200" height="60" style="border:none;"></iframe>
            </div>
        <!--fim-->
    </body>
</html>
<?php
//Pedro
/*if(isset($_GET['id_pet'])){
    echo <<<HTML
        <script>
            let opcao = document.querySelectorAll("option");
            let id = $_GET[id_pet];
            opcao.forEach(valor=>{
                if(valor.value == id){
                    valor.selected = true;
                }
            })
        </script>
    HTML;
}*/
//===========================
?>