<?php
if(session_status() === PHP_SESSION_NONE)
  session_start();

if (!isset($_SESSION['usuario'])){
  header("Location: ../erros/login_erro/");
  exit(403);
}else{
  session_regenerate_id();
}

$id_pessoa = filter_var($_SESSION['id_pessoa'], FILTER_SANITIZE_NUMBER_INT);

if(!$id_pessoa || $id_pessoa < 1){
  http_response_code(412);
  echo json_encode(['erro' => 'O id do funcionário não é válido.']);
  exit();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($id_pessoa, 4, 7);

require_once ("../conexao.php");
// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";
?>
<!DOCTYPE html>
<html class="fixed">

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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
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

  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <link type="text/css" rel="stylesheet" charset="UTF-8" href="https://translate.googleapis.com/translate_static/css/translateelement.css">

  <!-- javascript functions -->
  <script src="<?php echo WWW; ?>Functions/onlyNumbers.js"></script>
  <script src="<?php echo WWW; ?>Functions/onlyChars.js"></script>
  <script src="<?php echo WWW; ?>Functions/mascara.js"></script>
  <script src="<?php echo WWW; ?>html/socio/sistema/controller/script/geracao_auto_v3.js"></script>
  <script type="text/javascript">
    $(function() {
      $("#header").load("<?php echo WWW; ?>html/header.php");
      $(".menuu").load("<?php echo WWW; ?>html/menu.php");
    });
  </script>
</head>

<body>
  <?php require_once("./controller/import_conteudo_psociogeracao.php"); ?>
  <?php require_once("./controller/import_scripts.php"); ?>
  <?php require_once("./controller/import_scripts_socio.php"); ?>

  <div align="right">
    <iframe src="https://www.wegia.org/software/footer/socio.html" width="200" height="60" style="border:none;"></iframe>
  </div>
</body>

</html>