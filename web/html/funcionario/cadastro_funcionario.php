<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
Util::definirFusoHorario();
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE)
  session_start();

if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
  exit();
} else {
  session_regenerate_id();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

permissao($_SESSION['id_pessoa'], 11, 3);

require_once ROOT . "/controle/FuncionarioControle.php";
$listaCPF = new FuncionarioControle;
$listaCPF->listarCpf();

require_once ROOT . "/controle/AtendidoControle.php";
$listaCPF2 = new AtendidoControle;
$listaCPF2->listarCpf();
$cpf = $_GET['cpf'];
$funcionario = new FuncionarioDAO;
$informacoesFunc = $funcionario->listarPessoaExistente($cpf);

require_once "../../classes/Funcionario.php";
require_once ROOT . "/html/geral/msg.php";
$dataNascimentoMaxima = Funcionario::getDataNascimentoMaxima();
$dataNascimentoMinima = Funcionario::getDataNascimentoMinima();

// Inclui display de Campos
require_once "../personalizacao_display.php";

$erro = null;
if (isset($_SESSION['erro'])) {
  $erro = $_SESSION['erro'];
  unset($_SESSION['erro']);
}

$oldInput = getSessionFormData();
$fieldErrors = getSessionFormErrors();

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$situacao = $mysqli->query("SELECT * FROM situacao");
$cargo = $mysqli->query("SELECT * FROM cargo");

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
?>
<!DOCTYPE html>
<html class="fixed">

<head>
  <!-- Basic -->
  <meta charset="UTF-8">
  <title>Cadastro de Funcionário</title>
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

  <!-- Web Fonts  -->
  <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

  <!-- Vendor CSS -->
  <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
  <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
  <link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
  <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon">

  <!-- Theme CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />

  <!-- Skin CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />

  <!-- Theme Custom CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">

  <!--JS Functions-->
  <script src="<?php echo WWW; ?>Functions/cargos.js"></script>

</head>

<body>
  <!-- start: header -->
  <div id="header"></div>
  <!-- end: header -->
  <div class="inner-wrapper">
    <!-- start: sidebar -->
    <aside id="sidebar-left" class="sidebar-left menuu"></aside>

    <section role="main" class="content-body">
      <header class="page-header">
        <h2>Cadastro</h2>
        <div class="right-wrapper pull-right">
          <ol class="breadcrumbs">
            <li>
              <a href="../home.php">
                <i class="fa fa-home"></i>
              </a>
            </li>
            <li><span>Cadastros</span></li>
            <li><span>Funcionário</span></li>
          </ol>
          <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
        </div>
      </header>
      <!-- start: page -->
      <div class="row" id="formulario">
        <?php sessionMsg(); ?>
        <?php if ($erro): ?>
          <div style="color: red; font-weight: bold; text-align:center">
            <?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>
        <form action="#" method="POST" id="formsubmit" enctype="multipart/form-data" target="frame">
          <div class="col-md-4 col-lg-3">
            <section class="panel">
              <div class="panel-body">
                <div class="thumb-info mb-md">
                  <?php
                  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    if (isset($_FILES['imgperfil'])) {
                      $image = file_get_contents($_FILES['imgperfil']['tmp_name']);
                      $_SESSION['imagem'] = $image;
                      echo '<img src="data:image/gif;base64,' . base64_encode($image) . '" class="rounded img-responsive" alt="John Doe">';
                    }
                  }
                  ?>

                  <input type="file" class="image_input form-control" onclick="okDisplay()" name="imgperfil" id="imgform">
                  <div id="display_image" class="thumb-info mb-md"></div>
                  <div id="botima">
                    <h5 id="okText"></h5>
                    <input type="submit" class="btn btn-primary stylebutton" onclick="submitButtonStyle(this)" id="okButton" value="Ok">
                  </div>
                </div>
              </div>
            </section>
          </div>
        </form>
        <div class="col-md-8 col-lg-8">
          <div class="tabs">
            <ul class="nav nav-tabs tabs-primary">
              <li class="active">
                <a href="#overview" data-toggle="tab">Cadastro de Funcionário</a>
              </li>
            </ul>
            <div class="tab-content">
              <div id="overview" class="tab-pane active">
                <form class="form-horizontal" id="formPrincipal" method="POST" action="../../controle/control.php" onsubmit="return validarFuncionario()">
                  <div id="clientValidationAlert" class="alert alert-danger alert-dismissible" style="display:none;">
                    <button type="button" class="close" onclick="$('#clientValidationAlert').hide()">&times;</button>
                    <span id="clientValidationAlertText"></span>
                  </div>
                  <h4 class="mb-xlg">Informações Pessoais</h4>
                  <h5 class="obrig">Campos Obrigatórios(*)</h5>
                  <div class="form-group">
                    <label class="col-md-3 control-label" for="profileFirstName">Nome<sup class="obrig">*</sup></label>
                    <div class="col-md-6">
                      <input type="text" class="form-control<?= isset($fieldErrors['nome']) ? ' is-invalid' : '' ?>" name="nome" id="nome" onkeypress="return Onlychars(event)" required value="<?= htmlspecialchars($oldInput['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <p id="error_nome" class="help-block text-danger" style="display: <?= isset($fieldErrors['nome']) ? 'block' : 'none' ?>;">
                        <?= isset($fieldErrors['nome']) ? htmlspecialchars($fieldErrors['nome'], ENT_QUOTES, 'UTF-8') : '' ?>
                      </p>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-md-3 control-label">Sobrenome<sup class="obrig">*</sup></label>
                    <div class="col-md-6">
                      <input type="text" class="form-control<?= isset($fieldErrors['sobrenome']) ? ' is-invalid' : '' ?>" name="sobrenome" id="sobrenome" onkeypress="return Onlychars(event)" required value="<?= htmlspecialchars($oldInput['sobrenome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <p id="error_sobrenome" class="help-block text-danger" style="display: <?= isset($fieldErrors['sobrenome']) ? 'block' : 'none' ?>;">
                        <?= isset($fieldErrors['sobrenome']) ? htmlspecialchars($fieldErrors['sobrenome'], ENT_QUOTES, 'UTF-8') : '' ?>
                      </p>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-md-3 control-label" for="profileLastName">Sexo<sup class="obrig">*</sup></label>
                    <div class="col-md-6">
                      <label><input type="radio" name="gender" id="radioM" value="m" style="margin-top: 10px; margin-left: 15px;" onclick="return exibir_reservista()" required <?= isset($oldInput['gender']) && $oldInput['gender'] === 'm' ? 'checked' : '' ?>><i class="fa fa-male" style="font-size: 20px;"></i></label>
                      <label><input type="radio" name="gender" id="radioF" value="f" style="margin-top: 10px; margin-left: 15px;" onclick="return esconder_reservista()" <?= isset($oldInput['gender']) && $oldInput['gender'] === 'f' ? 'checked' : '' ?>><i class="fa fa-female" style="font-size: 20px;"></i> </label>
                    </div>
                  </div>
                  <div class="form-group">
                      <label class="col-md-3 control-label" for="email">E-mail</label>
                      <div class="col-md-6">
                          <input type="email" class="form-control" name="email" id="email"
                          placeholder="Ex: usuario@email.com">
                      </div>
                  </div>
                  <div class="form-group">
                    <label class="col-md-3 control-label" for="telefone">Telefone<sup class="obrig">*</sup></label>
                    <div class="col-md-6">
                      <input type="text" class="form-control<?= isset($fieldErrors['telefone']) ? ' is-invalid' : '' ?>" maxlength="14" minlength="14" name="telefone" id="telefone" placeholder="Ex: (22)99999-9999" onkeypress="return Onlynumbers(event)" onkeyup="mascara('(##)#####-####',this,event)" value="<?= htmlspecialchars($oldInput['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <p id="error_telefone" class="help-block text-danger" style="display: <?= isset($fieldErrors['telefone']) ? 'block' : 'none' ?>;">
                        <?= isset($fieldErrors['telefone']) ? htmlspecialchars($fieldErrors['telefone'], ENT_QUOTES, 'UTF-8') : '' ?>
                      </p>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-md-3 control-label" for="profileCompany">Nascimento<sup class="obrig">*</sup></label>
                    <div class="col-md-6">
                      <input type="date" name="nascimento" id="nascimento" class="form-control<?= isset($fieldErrors['nascimento']) ? ' is-invalid' : '' ?>" min="<?= $dataNascimentoMinima ?>" max="<?= $dataNascimentoMaxima ?>" required value="<?= htmlspecialchars($oldInput['nascimento'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <p id="error_nascimento" class="help-block text-danger" style="display: <?= isset($fieldErrors['nascimento']) ? 'block' : 'none' ?>;">
                        <?= isset($fieldErrors['nascimento']) ? htmlspecialchars($fieldErrors['nascimento'], ENT_QUOTES, 'UTF-8') : '' ?>
                      </p>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-md-3 control-label" for="cns">CNS</label>
                    <div class="col-md-6">
                      <input type="text" class="form-control" maxlength="15" name="cns" id="cns" placeholder="Ex: 123456789012345" onkeypress="return Onlynumbers(event)">
                      <small class="form-text text-muted">Cadastro Nacional de Saúde</small>
                    </div>
                  </div>
                  <hr class="dotted short">
                  <h4 class="mb-xlg doch4">Documentação</h4>

                 <div id="grupoRG">
                    <div class="form-group">
                      <label class="col-md-3 control-label">Número do RG</label>
                      <div class="col-md-6">
                        <input type="text" class="form-control<?= isset($fieldErrors['rg']) ? ' is-invalid' : '' ?>" name="rg" id="rg"
                          onkeypress="return Onlynumbers(event)"
                          placeholder="Ex: 22.222.222-2"
                          onkeyup="mascara('##.###.###-#',this,event)" value="<?= htmlspecialchars($oldInput['rg'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <p id="error_rg" class="help-block text-danger" style="display: <?= isset($fieldErrors['rg']) ? 'block' : 'none' ?>;">
                        <?= isset($fieldErrors['rg']) ? htmlspecialchars($fieldErrors['rg'], ENT_QUOTES, 'UTF-8') : '' ?>
                      </p>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3 control-label">Órgão Emissor</label>
                      <div class="col-md-6">
                        <input type="text" class="form-control<?= isset($fieldErrors['orgao_emissor']) ? ' is-invalid' : '' ?>" name="orgao_emissor" id="orgao_emissor"
                          onkeypress="return Onlychars(event)" value="<?= htmlspecialchars($oldInput['orgao_emissor'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          <p id="erro_orgao" style="display:none; color:#b30000;">
                            Preencha o órgão emissor
                          </p>
                          <p id="error_orgao_emissor" class="help-block text-danger" style="display: <?= isset($fieldErrors['orgao_emissor']) ? 'block' : 'none' ?>;">
                            <?= isset($fieldErrors['orgao_emissor']) ? htmlspecialchars($fieldErrors['orgao_emissor'], ENT_QUOTES, 'UTF-8') : '' ?>
                          </p>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3 control-label">Data de expedição</label>
                      <div class="col-md-6">
                        <input type="date" class="form-control<?= isset($fieldErrors['data_expedicao']) ? ' is-invalid' : '' ?>"
                          name="data_expedicao" id="data_expedicao"
                          max="<?php echo date('Y-m-d'); ?>" value="<?= htmlspecialchars($oldInput['data_expedicao'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          <p id="erro_dataExp" style="display:none; color: #b30000;">
                            Preencha a data de expedição
                          </p>
                        
                        <p id="dataNascInvalida" style="display: block; color: #b30000">
                          Selecione a data de nascimento primeiro!
                        </p>
                        <p id="error_data_expedicao" class="help-block text-danger" style="display: <?= isset($fieldErrors['data_expedicao']) ? 'block' : 'none' ?>;">
                          <?= isset($fieldErrors['data_expedicao']) ? htmlspecialchars($fieldErrors['data_expedicao'], ENT_QUOTES, 'UTF-8') : '' ?>
                        </p>
                      </div>
                    </div>
                  </div>

                  
                  <div class="form-group">
                    <label class="col-md-3 control-label" for="cpf">Número do CPF<sup class="obrig">*</sup></label>
                    <div class="col-md-6">
                      <input type="text" class="form-control<?= isset($fieldErrors['cpf']) ? ' is-invalid' : '' ?>" id="cpf" name="cpf" placeholder="Ex: 222.222.222-22" maxlength="14" onblur="validarCPF(this.value)" onkeypress="return Onlynumbers(event)" onkeyup="mascara('###.###.###-##', this, event)" value="<?= htmlspecialchars($oldInput['cpf'] ?? ($cpf ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                      <p id="error_cpf" class="help-block text-danger" style="display: <?= isset($fieldErrors['cpf']) ? 'block' : 'none' ?>;">
                        <?= isset($fieldErrors['cpf']) ? htmlspecialchars($fieldErrors['cpf'], ENT_QUOTES, 'UTF-8') : '' ?>
                      </p>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-md-3 control-label" for="profileCompany"></label>
                    <div class="col-md-6">
                      <p id="cpfInvalido" style="display: none; color: #b30000">CPF INVÁLIDO!</p>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-md-3 control-label" for="profileCompany">Data de Admissão<sup class="obrig">*</sup></label>
                    <div class="col-md-6">
                      <input type="date" class="form-control<?= isset($fieldErrors['data_admissao']) ? ' is-invalid' : '' ?>"
                        name="data_admissao"
                        id="data_admissao"
                        max="<?php echo date('Y-m-d'); ?>"
                        required value="<?= htmlspecialchars($oldInput['data_admissao'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <p id="error_data_admissao" class="help-block text-danger" style="display: <?= isset($fieldErrors['data_admissao']) ? 'block' : 'none' ?>;">
                        <?= isset($fieldErrors['data_admissao']) ? htmlspecialchars($fieldErrors['data_admissao'], ENT_QUOTES, 'UTF-8') : '' ?>
                      </p>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-md-3 control-label" for="inputSuccess">Situação<sup class="obrig">*</sup></label>
                    <a onclick="adicionar_situacao()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                    <div class="col-md-6">
                      <select class="form-control input-lg mb-md" name="situacao" id="situacao" required>
                        <option selected disabled>Selecionar</option>
                        <?php
                        while ($row = $situacao->fetch_array(MYSQLI_NUM)) {
                          $selected = isset($oldInput['situacao']) && $oldInput['situacao'] == $row[0] ? ' selected' : '';
                          echo "<option value=\"" . htmlspecialchars($row[0]) . "\"" . $selected . ">" . htmlspecialchars($row[1]) . "</option>";
                        }                            ?>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-md-3 control-label" for="inputSuccess">Cargo<sup class="obrig">*</sup></label>
                    <a onclick="adicionar_cargo()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                    <div class="col-md-6">
                      <select class="form-control input-lg mb-md" name="cargo" id="cargo" required>
                        <option selected disabled>Selecionar</option>
                        <?php
                        while ($row = $cargo->fetch_array(MYSQLI_NUM)) {
                          $selected = isset($oldInput['cargo']) && $oldInput['cargo'] == $row[0] ? ' selected' : '';
                          echo "<option value=\"" . htmlspecialchars($row[0]) . "\"" . $selected . ">" . htmlspecialchars($row[1]) . "</option>";
                        }
                        ?>
                      </select>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-md-3 control-label">Escala<sup class="obrig">*</sup></label>
                    <div class="col-md-6">
                      <select class="form-control input-lg mb-md" name="escala" id="escala_input" required>
                        <option selected disabled value="">Selecionar</option>
                        <?php
                        $pdo = Conexao::connect();
                        $escala = $pdo->query("SELECT * FROM escala_quadro_horario;")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($escala as $key => $value) {
                          $selected = isset($oldInput['escala']) && $oldInput['escala'] == $value['id_escala'] ? ' selected' : '';
                          echo ("<option value=\"" . htmlspecialchars($value['id_escala']) . "\"" . $selected . ">" . htmlspecialchars($value['descricao']) . "</option>");
                        }
                        ?>
                      </select>
                    </div>
                    <a href="../quadro_horario/adicionar_escala.php"><i class="fas fa-plus w3-xlarge"></i></a>
                  </div>
                  <div class="form-group">
                    <label class="col-md-3 control-label">Tipo<sup class="obrig">*</sup></label>
                    <div class="col-md-6">
                      <select class="form-control input-lg mb-md" name="tipoCargaHoraria" id="tipoCargaHoraria_input" required>
                        <option selected disabled value="">Selecionar</option>
                        <?php
                        $pdo = Conexao::connect();
                        $tipo = $pdo->query("SELECT * FROM tipo_quadro_horario;")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($tipo as $key => $value) {
                          $selected = isset($oldInput['tipoCargaHoraria']) && $oldInput['tipoCargaHoraria'] == $value['id_tipo'] ? ' selected' : '';
                          echo ("<option value=\"" . htmlspecialchars($value['id_tipo']) . "\"" . $selected . ">" . htmlspecialchars($value['descricao']) . "</option>");
                        }
                        ?>
                      </select>
                    </div>
                    <a href="../quadro_horario/adicionar_tipo_quadro_horario.php"><i class="fas fa-plus w3-xlarge"></i></a>
                  </div>
                  <div class="form-group" id="reservista1" style="display: none">
                    <label class="col-md-3 control-label">Número do certificado reservista</label>
                    <div class="col-md-6">
                      <input type="text" name="certificado_reservista_numero" class="form-control num_reservista"
                        pattern="\d*" inputmode="numeric" maxlength="9" placeholder="123456789" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                      <small>Formato: 123456789</small>
                    </div>
                  </div>

                  <div class="form-group" id="reservista2" style="display: none">
                    <label class="col-md-3 control-label">Série do certificado reservista</label>
                    <div class="col-md-6">
                      <input type="text" name="certificado_reservista_serie" class="form-control serie_reservista"
                        pattern="\d*" inputmode="numeric" maxlength="3" placeholder="001" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                      <small>Formato: 001</small>
                    </div>
                  </div>

                  <div class="panel-footer">
                    <div class="row">
                      <div class="col-md-9 col-md-offset-3">
                        <?= Csrf::inputField() ?>
                        <input type="hidden" name="nomeClasse" value="FuncionarioControle">
                        <input type="hidden" name="metodo" value="incluir">
                        <input id="enviar" type="submit" class="btn btn-primary" value="Salvar" onclick="validarFuncionario()">
                        <input type="reset" class="btn btn-default">
                      </div>
                    </div>
                  </div>
                </form>
                <iframe name="frame"></iframe>
                <!-- end: page -->
    </section>
  </div>
  </section>
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

  <!-- JQuery Online -->
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

  <!-- JQuery Local -->
  <script src="../../assets/vendor/jquery/jquery.min.js"></script>
  <script src="https://requirejs.org/docs/release/2.3.6/r.js"></script>
  <style type="text/css">
    .btn span.fa-check {
      opacity: 0;
    }

    .btn.active span.fa-check {
      opacity: 1;
    }

    .obrig {
      color: rgb(255, 0, 0);
    }

    .is-invalid {
      border-color: #a94442 !important;
    }

    iframe {
      display: none;
    }

    #display_image {

      min-height: 250px;
      margin: 0 auto;
      border: 1px solid black;
      background-position: center;
      background-size: cover;
      background-image: url("../../img/semfoto.png")
    }


    #display_image:after {

      content: "";
      display: block;
      padding-bottom: 100%;
    }

  #grupoRG .form-group {

    margin-bottom: 20px;
  }

  </style>
  <script type="text/javascript">
    var clickcont = 0;
    $("#botima").toggle();
    $("#imgform").click(function(e) {
      if (clickcont == 0) {
        $("#botima").toggle();
      }
      clickcont = clickcont + 1;
    });

    function okDisplay() {
      document.getElementById("okButton").style.backgroundColor = "#0275d8"; //azul
      document.getElementById("okText").textContent = "Confirme o arquivo selecionado";
      $("#profileFirstName").prop('disabled', true);
      $("#sobrenome").prop('disabled', true);
      $("#radioM").prop('disabled', true);
      $("#radioF").prop('disabled', true);
      $("#telefone").prop('disabled', true);
      $("#nascimento").prop('disabled', true);
      $("#rg").prop('disabled', true);
      $("#orgao_emissor").prop('disabled', true);
      $("#data_expedicao").prop('disabled', true);
      $("#data_admissao").prop('disabled', true);
      $("#situacao").prop('disabled', true);
      $("#cargo").prop('disabled', true);
      $("#escala_input").prop('disabled', true);
      $("#tipoCargaHoraria_input").prop('disabled', true);
    }

    function submitButtonStyle(_this) {
      _this.style.backgroundColor = "#5cb85c"; //verde
      document.getElementById("okText").textContent = "Arquivo confirmado";
      $("#profileFirstName").prop('disabled', false);
      $("#sobrenome").prop('disabled', false);
      $("#radioM").prop('disabled', false);
      $("#radioF").prop('disabled', false);
      $("#telefone").prop('disabled', false);
      $("#nascimento").prop('disabled', false);
      $("#rg").prop('disabled', false);
      $("#orgao_emissor").prop('disabled', false);
      $("#data_expedicao").prop('disabled', false);
      $("#data_admissao").prop('disabled', false);
      $("#situacao").prop('disabled', false);
      $("#cargo").prop('disabled', false);
      $("#escala_input").prop('disabled', false);
      $("#tipoCargaHoraria_input").prop('disabled', false);
    }

    function funcao1() {
      var send = $("#enviar");
      var cpfs = <?php echo $_SESSION['cpf_funcionario']; ?>;
      var cpf_funcionario = $("#cpf").val();
      var cpf_funcionario_correto = cpf_funcionario.replace(".", "");
      var cpf_funcionario_correto1 = cpf_funcionario_correto.replace(".", "");
      var cpf_funcionario_correto2 = cpf_funcionario_correto1.replace(".", "");
      var cpf_funcionario_correto3 = cpf_funcionario_correto2.replace("-", "");
      var apoio = 0;
      $.each(cpfs, function(i, item) {
        if (item.cpf == cpf_funcionario_correto3) {
          alert("Cadastro não realizado! O CPF informado já está cadastrado no sistema");
          apoio = 1;
          send.attr('disabled', 'disabled');
        }
      });

      if (apoio == 0) {}
    }

    function clearValidationFeedback() {
      $('.form-control').removeClass('is-invalid');
      $('.help-block.text-danger').hide();
      $('#clientValidationAlert').hide();
      $('#clientValidationAlertText').text('');
    }

    function showClientAlert(message) {
      $('#clientValidationAlertText').text(message);
      $('#clientValidationAlert').show();
    }

    function showFieldError(fieldId, message) {
      $('#' + fieldId).addClass('is-invalid');
      $('#error_' + fieldId).text(message).show();
    }

    function validarFuncionario() {
      clearValidationFeedback();

      var cpf_cadastrado = (<?php echo isset($_SESSION['cpf_funcionario']) ? $_SESSION['cpf_funcionario'] : '[]'; ?>).concat(<?php echo isset($_SESSION['cpf_interno']) ? $_SESSION['cpf_interno'] : '[]'; ?>);
      var cpf = (($("#cpf").val()).replaceAll(".", "")).replaceAll("-", "");
      var cpfDuplicado = false;
      $.each(cpf_cadastrado, function(i, item) {
        if (item.cpf == cpf) {
          cpfDuplicado = true;
          return false;
        }
      });

      if (cpfDuplicado) {
        showClientAlert('Cadastro não realizado! O CPF informado já está cadastrado no sistema.');
        showFieldError('cpf', 'CPF já cadastrado.');
        return false;
      }

      var sexo = document.querySelector('input[name="gender"]:checked');
      if (!sexo) {
        showClientAlert('O sexo do funcionário é obrigatório.');
        return false;
      }

      var dt_nasc = document.getElementById('nascimento').value;
      var dt_admissao = document.getElementById('data_admissao').value;

      if (dt_nasc) {
        let dataNascimentoMaxima = "<?= $dataNascimentoMaxima ?>";
        let dataNascimentoMinima = "<?= $dataNascimentoMinima ?>";
        if (dt_nasc > dataNascimentoMaxima) {
          showClientAlert('A data de nascimento não pode ser posterior ao permitido.');
          showFieldError('nascimento', 'Data de nascimento fora do intervalo permitido.');
          return false;
        }
        if (dt_nasc < dataNascimentoMinima) {
          showClientAlert('A data de nascimento não pode ser anterior ao permitido.');
          showFieldError('nascimento', 'Data de nascimento fora do intervalo permitido.');
          return false;
        }
      }

      if (dt_nasc && dt_admissao) {
        var nascimentoObj = new Date(dt_nasc);
        var admissaoObj = new Date(dt_admissao);
        var minAdmissao = new Date(nascimentoObj);
        minAdmissao.setFullYear(minAdmissao.getFullYear() + 14);

        if (admissaoObj < minAdmissao) {
          showClientAlert('A data de admissão deve respeitar a idade mínima de 14 anos do funcionário.');
          showFieldError('data_admissao', 'Data de admissão deve respeitar 14 anos mínimos.');
          return false;
        }
      }

      var data_expedicao = document.getElementById('data_expedicao').value;
      if (dt_nasc && data_expedicao && data_expedicao < dt_nasc) {
        showClientAlert('A data de expedição não pode ser anterior à data de nascimento.');
        showFieldError('data_expedicao', 'Data de expedição deve ser posterior à data de nascimento.');
        return false;
      }

      return true;
    }

    function numero_residencial() {

      if ($("#numResidencial").prop('checked')) {

        document.getElementById("numero_residencia").disabled = true;

      } else {

        document.getElementById("numero_residencia").disabled = false;

      }
    }

    function exibir_reservista() {

      $("#reservista1").show();
      $("#reservista2").show();
    }

    function esconder_reservista() {

      $('.num_reservista').val("");
      $('.serie_reservista').val("");

      $("#reservista1").hide();
      $("#reservista2").hide();
    }

    function limpa_formulário_cep() {
      //Limpa valores do formulário de cep.
      document.getElementById('rua').value = ("");
      document.getElementById('bairro').value = ("");
      document.getElementById('cidade').value = ("");
      document.getElementById('uf').value = ("");
      document.getElementById('ibge').value = ("");
    }

    function meu_callback(conteudo) {
      if (!("erro" in conteudo)) {
        //Atualiza os campos com os valores.
        document.getElementById('rua').value = (conteudo.logradouro);
        document.getElementById('bairro').value = (conteudo.bairro);
        document.getElementById('cidade').value = (conteudo.localidade);
        document.getElementById('uf').value = (conteudo.uf);
        document.getElementById('ibge').value = (conteudo.ibge);
      } //end if.
      else {
        //CEP não Encontrado.
        limpa_formulário_cep();
        alert("CEP não encontrado.");
      }
    }

    function pesquisacep(valor) {

      //Nova variável "cep" somente com dígitos.
      var cep = valor.replace(/\D/g, '');

      //Verifica se campo cep possui valor informado.
      if (cep != "") {

        //Expressão regular para validar o CEP.
        var validacep = /^[0-9]{8}$/;

        //Valida o formato do CEP.
        if (validacep.test(cep)) {

          //Preenche os campos com "..." enquanto consulta webservice.
          document.getElementById('rua').value = "...";
          document.getElementById('bairro').value = "...";
          document.getElementById('cidade').value = "...";
          document.getElementById('uf').value = "...";
          document.getElementById('ibge').value = "...";

          //Cria um elemento javascript.
          var script = document.createElement('script');

          //Sincroniza com o callback.
          script.src = 'https://viacep.com.br/ws/' + cep + '/json/?callback=meu_callback';

          //Insere script no documento e carrega o conteúdo.
          document.body.appendChild(script);

        } //end if.
        else {
          //cep é inválido.
          limpa_formulário_cep();
          alert("Formato de CEP inválido.");
        }
      } //end if.
      else {
        //cep sem valor, limpa formulário.
        limpa_formulário_cep();
      }

    };

    function validarCPF(strCPF) {

      if (!testaCPF(strCPF)) {
        $('#cpfInvalido').show();
        document.getElementById("enviar").disabled = true;

      } else {
        $('#cpfInvalido').hide();

        document.getElementById("enviar").disabled = false;
      }
    }

    function gerarSituacao() {
      url = '../../dao/exibir_situacao.php';
      $.ajax({
        data: '',
        type: "POST",
        url: url,
        async: true,
        success: function(response) {
          var situacoes = response;
          $('#situacao').empty();
          $('#situacao').append('<option selected disabled>Selecionar</option>');
          $.each(situacoes, function(i, item) {
            $('#situacao').append('<option value="' + item.id_situacao + '">' + item.situacoes + '</option>');
          });
        },
        dataType: 'json'
      });
    }

    function adicionar_situacao() {
      url = '../../dao/adicionar_situacao.php';
      var situacao = window.prompt("Cadastre uma Nova Situação:");
      if (!situacao) {
        return
      }
      situacao = situacao.trim();
      if (situacao == '') {
        return
      }

      data = 'situacao=' + situacao;

      console.log(data);
      $.ajax({
        type: "POST",
        url: url,
        data: data,
        success: function(response) {
          gerarSituacao();
        },
        dataType: 'text'
      })
    }

    // Delimitando a data mínima para a data de expedição da identidade por meio da data de nascimento
    $("#nascimento").on('change', function() {
      if ($(this).val()) {
        $('#data_expedicao').prop('disabled', false);
        $('#dataNascInvalida').hide();
        const nascimento = new Date($(this).val());
        const dataMinimaExpedicao = new Date(nascimento);
        dataMinimaExpedicao.setDate(dataMinimaExpedicao.getDate() + 1);

        $('#data_expedicao').attr('min', dataMinimaExpedicao.toISOString().split('T')[0]);
      } else {
        $('#data_expedicao').prop('disabled', true).val('');
        $('#dataNascInvalida').show();
      }
    });

    // Desabilitando o input Data de Expedição quando o formulário é resetado
    $('input[type="reset"]').on('click', function() {
      $('#data_expedicao').val('').prop('disabled', true);
      $('#data_expedicao').removeAttr('min');
    });

    $(function() {

      $("#header").load("../header.php");
      $(".menuu").load("../menu.php");
    });
  </script>
  <!-- Head Libs -->
  <script src="../../assets/vendor/modernizr/modernizr.js"></script>

  <!-- javascript functions -->
  <script src="../../Functions/onlyNumbers.js"></script>
  <script src="../../Functions/validacoes-cns.js"></script>
  <script src="../../Functions/onlyChars.js"></script>
  <script src="../../Functions/mascara.js"></script>
  <script src="../../Functions/lista.js"></script>
  <script src="<?php echo WWW; ?>Functions/testaCPF.js"></script>
  <script language="JavaScript">
    var numValidos = "0123456789-()";
    var num1invalido = "78";
    var i;

    function validarTelefone() {
      //Verificando quantos dígitos existem no campo, para controlarmos o looping;
      digitos = document.form1.telefone.value.length;

      for (i = 0; i < digitos; i++) {
        if (numValidos.indexOf(document.form1.telefone.value.charAt(i), 0) == -1) {
          alert("Apenas números são permitidos no campo Telefone!");
          document.form1.telefone.select();
          return false;
        }
        if (i == 0) {
          if (num1invalido.indexOf(document.form1.telefone.value.charAt(i), 0) != -1) {
            alert("Número de telefone inválido!");
            document.form1.telefone.select();
            return false;
          }
        }
      }
    }
  </script>
  <!-- Vendor -->
  <script src="../../assets/vendor/jquery/jquery.js"></script>
  <script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
  <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
  <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
  <script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
  <script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
  <script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

  <!-- img form -->
  <script>
    const image_input = document.querySelector(".image_input");
    var uploaded_image;

    image_input.addEventListener('change', function() {
      const reader = new FileReader();
      reader.addEventListener('load', () => {
        uploaded_image = reader.result;
        document.querySelector("#display_image").style.backgroundImage = `url(${uploaded_image})`;
      });
      reader.readAsDataURL(this.files[0]);
    });
  </script>
<script>
$(document).ready(function() {

  $('#nascimento').on('change', function() {
    var dataNasc = $(this).val();
    var dataAdmInput = $('#data_admissao');

    if (dataNasc) {
      var minAdm = new Date(dataNasc);
          minAdm.setFullYear(minAdm.getFullYear() + 14);
      dataAdmInput.removeAttr('min');
    }
  });

  // esconde os campos
  $("#orgao_emissor").closest(".form-group").hide();
  $("#data_expedicao").closest(".form-group").hide();

  // RG
  function validarRGCampos() {
    var rg = $("#rg").val().trim();

    if (rg !== "") {
      // mostra campos
      $("#orgao_emissor").closest(".form-group").fadeIn(150);
      $("#data_expedicao").closest(".form-group").fadeIn(150);

      // torna obrigatório
      $("#orgao_emissor").attr("required", true);
      $("#data_expedicao").attr("required", true);

      // adiciona * no label (se ainda não tiver)
      if (!$("#orgao_emissor").closest(".form-group").find(".obrig").length) {
        $("#orgao_emissor").closest(".form-group").find("label")
          .append('<sup class="obrig">*</sup>');
      }

      if (!$("#data_expedicao").closest(".form-group").find(".obrig").length) {
        $("#data_expedicao").closest(".form-group").find("label")
          .append('<sup class="obrig">*</sup>');
      }

    } else {
      // esconde campos
      $("#orgao_emissor").closest(".form-group").fadeOut(150);
      $("#data_expedicao").closest(".form-group").fadeOut(150);

      // limpa valores
      $("#orgao_emissor").val("");
      $("#data_expedicao").val("");

      // remove required
      $("#orgao_emissor").removeAttr("required");
      $("#data_expedicao").removeAttr("required");

      // remove *
      $("#orgao_emissor").closest(".form-group").find(".obrig").remove();
      $("#data_expedicao").closest(".form-group").find(".obrig").remove();
    }
  }

  // quando digitar RG
  $("#rg").on("input", validarRGCampos);

  validarRGCampos();

});
</script>

  <div align="right">
    <iframe src="https://www.wegia.org/software/footer/pessoa.html" width="200" height="60" style="border:none;"></iframe>
  </div>
</body>

</html>
