<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario'])) { header("Location: ../../index.php"); exit(); }
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

require_once ROOT . "/controle/VoluntarioControle.php";
require_once ROOT . "/classes/Voluntario.php";
require_once ROOT . "/html/personalizacao_display.php";
$dataNascimentoMaxima = Voluntario::getDataNascimentoMaxima();
$dataNascimentoMinima = Voluntario::getDataNascimentoMinima();

$erro = null;
if (isset($_SESSION['erro'])) { $erro = $_SESSION['erro']; unset($_SESSION['erro']); }
if (isset($_GET['msg'])) { $erro = $_GET['msg']; }

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$situacao = $mysqli->query("SELECT * FROM situacao");
require_once ROOT . '/classes/Csrf.php';
?>
<!DOCTYPE html>
<html class="fixed">
<head>
 <meta charset="UTF-8">
 <title>Cadastro de Voluntário</title>
 <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
 <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
 <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
 <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
 <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
 <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
 <script src="../../assets/vendor/jquery/jquery.min.js"></script>
</head>
<body>
 <div id="header"></div>
 <div class="inner-wrapper">
 <aside id="sidebar-left" class="sidebar-left menuu"></aside>
 <section role="main" class="content-body">
 <header class="page-header">
 <h2>Cadastro Voluntário</h2>
 </header>
 <div class="row" id="formulario">
 <?php if ($erro): ?>
 <div style="color: red; font-weight: bold; text-align:center"><?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?></div>
 <?php endif; ?>
 <div class="col-md-12 col-lg-12">
 <form class="form-horizontal" method="POST" action="../../controle/control.php">
 <div class="panel-body">
 <h4 class="mb-xlg">Informações Pessoais</h4>
 <div class="form-group">
 <label class="col-md-3 control-label">Nome *</label>
 <div class="col-md-6"><input type="text" class="form-control" name="nome" required></div>
 </div>
 <div class="form-group">
 <label class="col-md-3 control-label">Sobrenome *</label>
 <div class="col-md-6"><input type="text" class="form-control" name="sobrenome" required></div>
 </div>
 <div class="form-group">
 <label class="col-md-3 control-label">CPF *</label>
 <div class="col-md-6"><input type="text" class="form-control" name="cpf" maxlength="14" required></div>
 </div>
 <div class="form-group">
 <label class="col-md-3 control-label">Sexo *</label>
 <div class="col-md-6">
 <input type="radio" name="gender" value="m" required> M
 <input type="radio" name="gender" value="f" required> F
 </div>
 </div>
 <div class="form-group">
 <label class="col-md-3 control-label">Nascimento *</label>
 <div class="col-md-6"><input type="date" class="form-control" name="nascimento" min="<?= $dataNascimentoMinima ?>" max="<?= $dataNascimentoMaxima ?>" required></div>
 </div>
 <div class="form-group">
 <label class="col-md-3 control-label">RG *</label>
 <div class="col-md-6"><input type="text" class="form-control" name="rg" required></div>
 </div>
 <div class="form-group">
 <label class="col-md-3 control-label">Órgão Emissor *</label>
 <div class="col-md-6"><input type="text" class="form-control" name="orgao_emissor" required></div>
 </div>
 <div class="form-group">
 <label class="col-md-3 control-label">Data Expedição RG*</label>
 <div class="col-md-6"><input type="date" class="form-control" name="data_expedicao" required></div>
 </div>
 <hr>
 <h4 class="mb-xlg">Detalhes do Voluntariado</h4>
 <div class="form-group">
 <label class="col-md-3 control-label">Data de Admissão *</label>
 <div class="col-md-6"><input type="date" class="form-control" name="data_admissao" required></div>
 </div>
 <div class="form-group">
 <label class="col-md-3 control-label">Situação *</label>
 <div class="col-md-6">
 <select class="form-control" name="situacao" required>
 <option selected disabled>Selecionar</option>
 <?php while ($row = $situacao->fetch_array(MYSQLI_NUM)) { echo "<option value=" . $row[0] . ">" . htmlspecialchars($row[1]) . "</option>"; } ?>
 </select>
 </div>
 </div>
 </div>
 <div class="panel-footer">
 <?= Csrf::inputField() ?>
 <input type="hidden" name="nomeClasse" value="VoluntarioControle">
 <input type="hidden" name="metodo" value="incluir">
 <button type="submit" class="btn btn-primary">Salvar</button>
 </div>
 </form>
 </div>
 </div>
 </section>
 </div>
 <script>
 $(function() {
 $("#header").load("../header.php");
 $(".menuu").load("../menu.php");
 });
 </script>
</body>
</html>


