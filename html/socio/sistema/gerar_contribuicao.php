<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
require("../conexao.php");
require("../../contribuicao/php/preencheForm.php");
// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";
session_start();
if (!isset($_SESSION['usuario'])) header("Location: ../erros/login_erro/");
$id = $_SESSION['usuario'];
$id_pessoa = $_SESSION['id_pessoa'];
$resultado = mysqli_query($conexao, "SELECT `imagem`, `nome` FROM `pessoa` WHERE id_pessoa=$id_pessoa");
$pessoa = mysqli_fetch_array($resultado);
$nome = $pessoa['nome'];

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

if (!isset($_SESSION['usuario'])) {
    header("Location: " . WWW . "index.php");
}
$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$id_pessoa = $_SESSION['id_pessoa'];
$resultado = mysqli_query($conexao, "SELECT * FROM funcionario WHERE id_pessoa=$id_pessoa");
if (!is_null($resultado)) {
    $id_cargo = mysqli_fetch_array($resultado);
    if (!is_null($id_cargo)) {
        $id_cargo = $id_cargo['id_cargo'];
    }
    $resultado = mysqli_query($conexao, "SELECT * FROM permissao WHERE id_cargo=$id_cargo and id_recurso=4");
    if (!is_bool($resultado) and mysqli_num_rows($resultado)) {
        $permissao = mysqli_fetch_array($resultado);
        if ($permissao['id_acao'] < 7) {
            $msg = "Você não tem as permissões necessárias para essa página.";
            header("Location: " . WWW . "/html/home.php?msg_c=$msg");
        }
        $permissao = $permissao['id_acao'];
    } else {
        $permissao = 1;
        $msg = "Você não tem as permissões necessárias para essa página.";
        header("Location: " . WWW . "/html/home.php?msg_c=$msg");
    }
} else {
    $permissao = 1;
    $msg = "Você não tem as permissões necessárias para essa página.";
    header("Location: " . WWW . "/html/home.php?msg_c=$msg");
}
// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Gerar boleto/carnê</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="controller/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="controller/bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="controller/bower_components/Ionicons/css/ionicons.min.css">
    <script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script>
    <!-- Theme style -->
    <link rel="stylesheet" href="controller/dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="controller/dist/css/skins/_all-skins.min.css">
    <!-- Morris chart -->
    <link rel="stylesheet" href="controller/bower_components/morris.js/morris.css">
    <!-- jvectormap -->
    <link rel="stylesheet" href="controller/bower_components/jvectormap/jquery-jvectormap.css">
    <!-- Date Picker -->
    <link rel="stylesheet" href="controller/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="controller/bower_components/bootstrap-daterangepicker/daterangepicker.css">
    <!-- bootstrap wysihtml5 - text editor -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
    <link rel="stylesheet" href="controller/css/animacoes.css">
    <link rel="stylesheet" href="controller/css/tabelas.css">
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@700&display=swap" rel="stylesheet">
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="icon" href="" type="image/x-icon" id="logo-icon">

    <!-- Specific Page Vendor CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/select2/select2.css" />
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/theme.css" />

    <!-- Skin CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/skins/default.css" />

    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/theme-custom.css">

    <!-- Head Libs -->
    <script src="<?php echo WWW; ?>assets/vendor/modernizr/modernizr.js"></script>

    <!-- Vendor -->
    <script src="<?php echo WWW; ?>assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

    <!-- Specific Page Vendor -->
    <script src="<?php echo WWW; ?>assets/vendor/jquery-autosize/jquery.autosize.js"></script>

    <!-- Theme Base, Components and Settings -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.js"></script>

    <!-- Theme Custom -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.custom.js"></script>

    <!-- Theme Initialization Files -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.init.js"></script>


    <!-- javascript functions -->
    <script src="<?php echo WWW; ?>Functions/onlyNumbers.js"></script>
    <script src="<?php echo WWW; ?>Functions/onlyChars.js"></script>
    <script src="<?php echo WWW; ?>Functions/mascara.js"></script>
    <script src="<?php echo WWW; ?>html/contribuicao/js/geraboleto.js"></script>
    <script type="text/javascript">
        $(function() {
            $("#header").load("<?php echo WWW; ?>html/header.php");
            $(".menuu").load("<?php echo WWW; ?>html/menu.php");
        });
    </script>
</head>

<body>
    <?php require_once("./controller/import_conteudo_gerarcontribuicao.php"); ?>
    <?php require_once("./controller/import_scripts.php"); ?>
    <?php require_once("./controller/import_scripts_socio.php"); ?>
    <script>
        function retorna_parecela() {
            var now = new Date;
            var mes = now.getMonth() + 1;


            var parcelas = 0;
            if (mes <= 6) {
                parcelas = 13 - mes;
            } else {
                parcelas = (12 - mes) + 12;
            }
            console.log(parcelas);
            return parcelas;
        }

        function retorna_dataV_tipo(dia, tipo) {
            var dia = dia;
            var now = new Date;
            if (tipo == "unico") {
                var diaA = now.getDate() + 3;
                if (dia == 29 || dia == 30 || dia == 31) {
                    diaA = 3;
                    var mesA = now.getMonth() + 2;
                }
                var mesA = now.getMonth() + 1;
                var anoA = now.getFullYear();
                var DataV = diaA + "/" + mesA + "/" + anoA;

                return DataV;
            } else {
                var diaA = now.getDate();
                if (dia <= diaA) {
                    var mes_atual = now.getMonth() + 2;
                } else {
                    var mes_atual = now.getMonth() + 1;
                }

                var ano_atual = now.getFullYear();
                if (tipo == "mensal_proximo") {
                    ano_atual++;
                    var DataV = dia + "/01/" + ano_atual;
                } else {
                    var DataV = dia + "/" + mes_atual + "/" + ano_atual;
                }

                return DataV;
            }

        }
        var now = new Date;
        var dv = retorna_dataV_tipo(now.getDate(), "unico");
        console.log(dv);
        $(".datas").html(`<span>Data de vencimento para boleto gerado hoje: ${dv}</span>`);

        $("#tipo_geracao").change(function() {
            switch (this.value) {
                case "1":
                    $(".valor").html(`<label for="pessoa">Valor</label><input type="text" class="form-control" id="valor_u" name="valor" placeholder="Valor único" required>`);
                    $(".dta").html("");
                    var dv = retorna_dataV_tipo(now.getDate(), "unico");
                    console.log(dv);
                    $(".datas").html(`<span>Data de vencimento para boleto gerado hoje: ${dv}</span>`);
                    $("#dataV").val(dv);
                    break;
                case "2":
                    $(".valor").html(`<label for="pessoa">Valor</label>
            <select class="form-control" name="valor" id="valor">
                    <option value=''>Selecione um valor mensal</option>
                    <option value = '<?php echo $valminparc ?>'>R$<?php echo $valminparc ?></option>
                    <option value = '50.00'>R$50,00</option>
                    <option value = '100.00'>R$100,00</option>
                    <option value = '150.00'>R$150,00</option>
                    <option value = '200.00'>R$200,00</option>
                    <option value = '250.00'>R$250,00</option>
                    <option value = '300.00'>R$300,00</option>
                    <option value = '500.00'>R$500,00</option>
                    <option value = '<?php echo $valmaxparc ?>'>R$<?php echo $valmaxparc ?></option>
                </select>`);
                    $(".dta").html(`<label for="pessoa">Dia de vencimento mensal</label>
            <select class="form-control" name="dta" id="dta">
                    <?php
                    for ($i = 0; $i < 5; $i++) {
                        if ($arrayData[$i] != 0) {
                            echo "<option value ='" . $arrayData[$i] . "' id='op" . $i . "'>" . $arrayData[$i] . "</option>";
                        }
                    }
                    ?>
                </select>`);

                    var dv = retorna_dataV_tipo($("#dta").val(), "mensal_proximo");
                    console.log(dv);
                    $(".datas").html(`<span>Data de vencimento da primeira parcela do carnê: ${dv}, as demais parcelas também respeitarão o dia da primeira parcela.</span>`);
                    $("#dataV").val(dv);
                    $("#dta").change(function() {
                        var dv = retorna_dataV_tipo($("#dta").val(), "mensal_proximo");
                        console.log(dv);
                        $(".datas").html(`<span>Data de vencimento da primeira parcela do carnê: ${dv}, as demais parcelas também respeitarão o dia da primeira parcela.</span>`);
                        $("#dataV").val(dv);
                    })
                    break;
                case "3":
                    $(".valor").html(`<label for="pessoa">Valor</label>
            <select class="form-control" name="valor" id="valor">
                    <option value=''>Selecione um valor mensal</option>
                    <option value = '<?php echo $valminparc ?>'>R$<?php echo $valminparc ?></option>
                    <option value = '50.00'>R$50,00</option>
                    <option value = '100.00'>R$100,00</option>
                    <option value = '150.00'>R$150,00</option>
                    <option value = '200.00'>R$200,00</option>
                    <option value = '250.00'>R$250,00</option>
                    <option value = '300.00'>R$300,00</option>
                    <option value = '500.00'>R$500,00</option>
                    <option value = '<?php echo $valmaxparc ?>'>R$<?php echo $valmaxparc ?></option>
                </select>`);
                    $(".dta").html(`<label for="pessoa">Dia de vencimento mensal</label>
            <select class="form-control" name="dta" id="dta">
                    <?php
                    for ($i = 0; $i < 5; $i++) {
                        if ($arrayData[$i] != 0) {
                            echo "<option value ='" . $arrayData[$i] . "' id='op" . $i . "'>" . $arrayData[$i] . "</option>";
                        }
                    }
                    ?>
                </select>`);
                    var dv = retorna_dataV_tipo($("#dta").val(), "mensal");
                    console.log(dv);
                    $(".datas").html(`<span>Data de vencimento da primeira parcela do carnê: ${dv}, as demais parcelas também respeitarão o dia da primeira parcela.</span>`);
                    $("#dataV").val(dv);
                    $("#dta").change(function() {
                        var dv = retorna_dataV_tipo($("#dta").val(), "mensal");
                        console.log(dv);
                        $(".datas").html(`<span>Data de vencimento da primeira parcela do carnê: ${dv}, as demais parcelas também respeitarão o dia da primeira parcela.</span>`);
                        $("#dataV").val(dv);
                        $("#parcelas").val(retorna_parecela());
                    })
                    break;
            }
        })
    </script>
</body>

</html>