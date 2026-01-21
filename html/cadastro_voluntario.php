<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
}
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
$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$id_pessoa = $_SESSION['id_pessoa'];
$resultado = mysqli_query($conexao, "SELECT * FROM funcionario WHERE id_pessoa=$id_pessoa");
if (!is_null($resultado)) {
  $id_cargo = mysqli_fetch_array($resultado);
  if (!is_null($id_cargo)) {
    $id_cargo = $id_cargo['id_cargo'];
  }
  $resultado = mysqli_query($conexao, "SELECT * FROM permissao WHERE id_cargo=$id_cargo and id_recurso=13");
  if (!is_bool($resultado) and mysqli_num_rows($resultado)) {
    $permissao = mysqli_fetch_array($resultado);
    if ($permissao['id_acao'] < 3) {
      $msg = "Você não tem as permissões necessárias para essa página.";
      header("Location: ./home.php?msg_c=$msg");
    }
    $permissao = $permissao['id_acao'];
  } else {
    $permissao = 1;
    $msg = "Você não tem as permissões necessárias para essa página.";
    header("Location: ./home.php?msg_c=$msg");
  }
} else {
  $permissao = 1;
  $msg = "Você não tem as permissões necessárias para essa página.";
  header("Location: ./home.php?msg_c=$msg");
}
// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once "personalizacao_display.php";
?>

<!doctype html>
<html class="fixed">

<head>
  <!-- Basic -->
  <meta charset="UTF-8">

  <title>Cadastro de Doador</title>

  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

  <!-- Vendor CSS -->
  <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.css" />
  <link rel="stylesheet" href="../assets/vendor/font-awesome/css/font-awesome.css" />
  <link rel="stylesheet" href="../assets/vendor/magnific-popup/magnific-popup.css" />
  <link rel="stylesheet" href="../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
  <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">

  <!-- Theme CSS -->
  <link rel="stylesheet" href="../assets/stylesheets/theme.css" />

  <!-- Skin CSS -->
  <link rel="stylesheet" href="../assets/stylesheets/skins/default.css" />

  <!-- Theme Custom CSS -->
  <link rel="stylesheet" href="../assets/stylesheets/theme-custom.css">

  <!-- Head Libs -->
  <script src="../assets/vendor/modernizr/modernizr.js"></script>

  <!-- Javascript functions -->

  <script src="../assets/vendor/jquery/jquery.min.js"></script>

  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

  <!-- Functions -->
  <script src="../Functions/mascara.js"></script>
  <script src="../Functions/onlyNumbers.js"></script>
  <script src="../Functions/onlyChars.js"></script>

</head>

<body>
  <section class="body">
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
                <a href="home.php">
                  <i class="fa fa-home"></i>
                </a>
              </li>
              <li><span>Páginas</span></li>
              <li><span>Perfil</span></li>
            </ol>
            <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
          </div>
        </header>

        <!-- start: page -->
        <div class="row">
          <div class="col-md-4 col-lg-3">
            <section class="panel">
              <div class="panel-body">
                <div class="thumb-info mb-md">
                  <img src="../img/semfoto.jpg" class="rounded img-responsive" alt="John Doe">
                  <i class="fas fa-camera-retro btn btn-info btn-lg" data-toggle="modal" data-target="#myModal"></i>
                  <div class="container">
                    <div class="modal fade" id="myModal" role="dialog">
                      <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Adicionar uma Foto</h4>
                          </div>
                          <div class="modal-body">
                            <form action="/action_page.php">
                              <div class="form-group">
                                <label class="col-md-4 control-label" for="imgperfil">Carregue uma imagem de perfil:</label>
                                <div class="col-md-8">
                                  <input type="file" name="imgperfil" size="60" id="imgform" class="form-control">
                                </div>
                              </div>
                              <div class="modal-footer">
                                <input type="submit" id="formsubmit">
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="widget-toggle-expand mb-md">
                    <div class="widget-header">
                      <div class="widget-content-expanded">
                        <ul class="simple-todo-list"></ul>
                      </div>
                    </div>
                  </div>

                  <hr class="dotted short">
                  <h6 class="text-muted"></h6>
                  <div class="clearfix">
                    <a class="text-uppercase text-muted pull-right" href="#">(View All)</a>
                  </div>
                </div>
            </section>
          </div>
          <div class="col-md-8 col-lg-8">
            <div class="tabs">
              <ul class="nav nav-tabs tabs-primary">
                <li class="active">
                  <a href="#overview" data-toggle="tab">Cadastro de Voluntario</a>
                </li>
              </ul>
              <div class="tab-content">
                <div id="overview" class="tab-pane active">
                  <form class="form-horizontal" method="post" action="#">
                    <h4 class="mb-xlg">Informações Pessoais</h4>
                    <div class="form-group">
                      <label class="col-md-3 control-label" for="profileFirstName">Nome completo</label>
                      <div class="col-md-8">
                        <input type="text" class="form-control" id="profileFirstName" required>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-md-3 control-label" for="profileLastName">Sexo</label>
                      <div class="col-md-8">
                        <input type="radio" name="gender" id="radio" value="male" style="margin-top: 10px; margin-left: 15px;" required><i class="fa fa-male" style="font-size: 20px;"></i>
                        <input type="radio" name="gender" id="radio" value="female" style="margin-top: 10px; margin-left: 15px;" required><i class="fa fa-female" style="font-size: 20px;"></i>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-md-3 control-label" for="profileCompany">Telefone</label>
                      <div class="col-md-8">
                        <input type="text" class="form-control" minlength="11" id="profileCompany">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-md-3 control-label" for="profileCompany">Nascimento</label>
                      <div class="col-md-8">
                        <input type="date" class="form-control" id="profileCompany">
                      </div>
                    </div>

                    <hr class="dotted short">
                    <h4 class="mb-xlg">Endereço</h4>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="cep">CEP</label>
                      <div class="col-md-8">
                        <input type="text" name="cep" value="" size="10" onblur="pesquisacep(this.value);" class="form-control" id="cep">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-md-3 control-label" for="uf">Estado</label>
                      <div class="col-md-8">
                        <input type="text" name="uf" size="60" class="form-control" id="uf">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-md-3 control-label" for="cidade">Cidade</label>
                      <div class="col-md-8">
                        <input type="text" size="40" class="form-control" id="cidade">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-md-3 control-label" for="bairro">Bairro</label>
                      <div class="col-md-8">
                        <input type="text" name="bairro" size="40" class="form-control" id="bairro">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-md-3 control-label" for="rua">Logradouro</label>
                      <div class="col-md-8">
                        <input type="text" name="rua" size="2" class="form-control" id="rua">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-md-3 control-label" for="profileCompany">Número</label>
                      <div class="col-md-8">
                        <input type="number" name="numero" class="form-control" id="profileCompany">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-md-3 control-label" for="profileCompany">Complemento</label>
                      <div class="col-md-8">
                        <input type="text" class="form-control" id="profileCompany">
                      </div>
                    </div><br />

                    <hr class="dotted short">
                    <h4 class="mb-xlg doch4">Documentação</h4>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="profileCompany">Número do CPF</label>
                      <div class="col-md-6">
                        <input type="text" name="cpf" class="form-control" id="profileCompany">
                      </div>
                    </div><br>

                    <hr class="dotted short">
                    <h4 class="mb-xlg doch4">Senha</h4>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="profileCompany">Senha</label>
                      <div class="col-md-6">
                        <input type="password" name="senha" class="form-control" id="profileCompany">
                      </div>
                    </div>

                    <div class="panel-footer">
                      <div class="row">
                        <div class="col-md-9 col-md-offset-3">
                          <input type="submit" class="btn btn-primary">
                          <input type="reset" class="btn btn-default">
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <!-- end: page -->
      </section>
    </div>
  </section>

  <script type="text/javascript">
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
      } else {
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

        } else {
          //cep é inválido.
          limpa_formulário_cep();
          alert("Formato de CEP inválido.");
        }
      } else {
        //cep sem valor, limpa formulário.
        limpa_formulário_cep();
      }
    };
  </script>
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

    $(function() {
      $("#header").load("header.php");
      $(".menuu").load("menu.php");
    });
  </script>

  <script src="../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
  <script src="../assets/vendor/bootstrap/js/bootstrap.js"></script>
  <script src="../assets/vendor/nanoscroller/nanoscroller.js"></script>
  <script src="../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
  <script src="../assets/vendor/magnific-popup/magnific-popup.js"></script>
  <script src="../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

  <script type="text/javascript">
  </script>

</body>

</html>