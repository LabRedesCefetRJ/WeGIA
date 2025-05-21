<?php
include_once("conexao.php");
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
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$situacao = $mysqli->query("SELECT * FROM situacao");
$cargo = $mysqli->query("SELECT * FROM cargo");
$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$id_pessoa = $_SESSION['id_pessoa'];
$resultado = mysqli_query($conexao, "SELECT * FROM funcionario WHERE id_pessoa=$id_pessoa");
if (!is_null($resultado)) {
  $id_cargo = mysqli_fetch_array($resultado);
  if (!is_null($id_cargo)) {
    $id_cargo = $id_cargo['id_cargo'];
  }
  $resultado = mysqli_query($conexao, "SELECT * FROM permissao p JOIN acao a ON(p.id_acao=a.id_acao) JOIN recurso r ON(p.id_recurso=r.id_recurso) WHERE id_cargo=$id_cargo AND a.descricao = 'LER, GRAVAR E EXECUTAR' AND r.descricao='Cadastrar Pet'");
    if(!is_bool($resultado) and mysqli_num_rows($resultado))
    {
        $permissao = mysqli_fetch_array($resultado);
        if($permissao['id_acao'] < 5)
        {
            $msg = "Você não tem as permissões necessárias para essa página.";
            header("Location: ../../home.php?msg_c=$msg");
        }
        $permissao = $permissao['id_acao'];
    }
    else
    {
        $permissao = 1;
          $msg = "Você não tem as permissões necessárias para essa página.";
          header("Location: ../../home.php?msg_c=$msg");
    }	
    
    }
    else
    {
        $permissao = 1;
        $msg = "Você não tem as permissões necessárias para essa página.";
        header("Location: ../../home.php?msg_c=$msg");
    }	

// Pega o CPF passado via GET
$cpf = $_GET["cpf"];

// Lógica para listar pets
$sqlConsultaPet = "SELECT id_pet, nome FROM pet;";
$resultadoConsultaPet = mysqli_query($conexao, $sqlConsultaPet);

// Lógica para adicionar na tabela pessoa
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cpf = !empty($_POST["cpf"]) ? $_POST["cpf"] : NULL;
    $nome = !empty($_POST["nome"]) ? $_POST["nome"] : NULL;
    $sobrenome = !empty($_POST["sobrenome"]) ? $_POST["sobrenome"] : NULL;
    $sexo = !empty($_POST["gender"]) ? $_POST["gender"] : NULL;
    $telefone = !empty($_POST["telefone"]) ? $_POST["telefone"] : NULL;
    $data_nascimento = !empty($_POST["nascimento"]) ? $_POST["nascimento"] : NULL;
    $imagem = !empty($_POST["imgperfil"]) ? $_POST["imgperfil"] : NULL;
    $cep = !empty($_POST["cep"]) ? $_POST["cep"] : NULL;
    $estado = !empty($_POST["uf"]) ? $_POST["uf"] : NULL;
    $cidade = !empty($_POST["cidade"]) ? $_POST["cidade"] : NULL;
    $bairro = !empty($_POST["bairro"]) ? $_POST["bairro"] : NULL;
    $logradouro = !empty($_POST["logradouro"]) ? $_POST["logradouro"] : NULL;
    $numero_endereco = !empty($_POST["numero_endereco"]) ? $_POST["numero_endereco"] : NULL;
    $complemento = !empty($_POST["complemento"]) ? $_POST["complemento"] : NULL;

    if (empty($cpf) || empty($nome) || empty($sobrenome)) {
        die('Campos obrigatórios não preenchidos.');
    }

    $sqlAdicionarPessoa = "
        INSERT INTO pessoa (
            cpf, 
            nome, 
            sobrenome, 
            sexo, 
            telefone, 
            data_nascimento, 
            imagem, 
            cep, 
            estado, 
            cidade, 
            bairro, 
            logradouro, 
            numero_endereco, 
            complemento
        ) 
        VALUES (
            :cpf,
            :nome,
            :sobrenome,
            :sexo,
            :telefone,
            :data_nascimento,
            :imagem,
            :cep,
            :estado,
            :cidade,
            :bairro,
            :logradouro,
            :numero_endereco,
            :complemento
        )
    ";

    $dsn = 'mysql:host=localhost;dbname=wegia;charset=utf8';  
    $username = 'wegiauser'; 
    $password = 'senha';

    try {
        $conexao = new PDO($dsn, $username, $password);
        $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conexao->prepare($sqlAdicionarPessoa);
        
        $stmt->bindParam(':cpf', $cpf);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':sobrenome', $sobrenome);
        $stmt->bindParam(':sexo', $sexo);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':data_nascimento', $data_nascimento);
        $stmt->bindParam(':imagem', $imagem);
        $stmt->bindParam(':cep', $cep);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':cidade', $cidade);
        $stmt->bindParam(':bairro', $bairro);
        $stmt->bindParam(':logradouro', $logradouro);
        $stmt->bindParam(':numero_endereco', $numero_endereco);
        $stmt->bindParam(':complemento', $complemento);
        
        $stmt->execute();

        header('Location: ./informacao_adotantes.php');
    } catch (PDOException $e) {
        echo 'Erro ao conectar ao banco de dados: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastro de Adotante</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/select2/select2.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/stylesheets/theme.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>/assets/stylesheets/skins/default.css" />
    <link rel="stylesheet" href="<?php echo WWW;?>assets/stylesheets/theme-custom.css">

    <script src="<?php echo WWW;?>assets/vendor/modernizr/modernizr.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/jquery-autosize/jquery.autosize.js"></script>
    <script src="<?php echo WWW;?>assets/javascripts/theme.js"></script>
    <script src="<?php echo WWW;?>assets/javascripts/theme.custom.js"></script>
    <script src="<?php echo WWW;?>assets/javascripts/theme.init.js"></script>
    <script src="<?php echo WWW;?>Functions/onlyNumbers.js"></script>
    <script src="<?php echo WWW;?>Functions/onlyChars.js"></script>
    <script src="<?php echo WWW;?>Functions/mascara.js"></script>
    <script src="<?php echo WWW;?>Functions/testaCPF.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/jasonday-printThis-f73ca19/printThis.js"></script>
 
    <script>
      $(function(){
          $("#header").load("<?php echo WWW;?>html/header.php");
          $(".menuu").load("<?php echo WWW;?>html/menu.php");
      });
    </script>

  <script src="../../../assets/vendor/modernizr/modernizr.js"></script>
  <script src="../../../Functions/onlyNumbers.js"></script>
  <script src="../../../Functions/onlyChars.js"></script>
  <script src="../../../Functions/mascara.js"></script>
  <script src="../../../Functions/lista.js"></script>
  <script src="<?php echo WWW; ?>Functions/testaCPF.js"></script>
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="../../../assets/vendor/jquery/jquery.min.js"></script>
  <script src="https://requirejs.org/docs/release/2.3.6/r.js"></script>
  <script src="../../../assets/vendor/jquery/jquery.js"></script>
  <script src="../../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
  <script src="../../../assets/vendor/bootstrap/js/bootstrap.js"></script>
  <script src="../../../assets/vendor/nanoscroller/nanoscroller.js"></script>
  <script src="../../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
  <script src="../../../assets/vendor/magnific-popup/magnific-popup.js"></script>
  <script src="../../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
  
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

  iframe {
    display: none;
  }

  #display_image {

    min-height: 250px;
    margin: 0 auto;
    border: 1px solid black;
    background-position: center;
    background-size: cover;
    background-image: url("../../../img/semfoto.png")
  }

  #display_image:after {

    content: "";
    display: block;
    padding-bottom: 100%;
  }

  .div-estado {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    width: 100%;
    max-width: 400px;
    padding: 5px;
  }

  .label-input100 {
      margin-right: 10px;
      margin-left: 30%;
      white-space: nowrap;
      text-align: center;
      display: inline-block;
  }

  .form-control {
      flex-grow: 1;
      min-width: 150px;
      padding: 5px;
  }
</style>
</head>
<body>
  <div id="header"></div>
  <div class="inner-wrapper">
    <aside id="sidebar-left" class="sidebar-left menuu"></aside>

    <section role="main" class="content-body">
      <header class="page-header">
        <h2>Cadastro</h2>
        <div class="right-wrapper pull-right">
          <ol class="breadcrumbs">
            <li>
              <a href="../../home.php">
                <i class="fa fa-home"></i>
              </a>
            </li>
            <li><span>Cadastros</span></li>
            <li><span>Adotante</span></li>
          </ol>
          <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
        </div>
      </header>
      <div class="row" id="formulario">
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
                    <input type="submit" class="btn btn-primary stylebutton" onclick="submitButtonStyle(this)" style="display: none;" id="okButton" id="botima" value="Ok">
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
                <a href="#overview" data-toggle="tab">Cadastro do Adotante</a>
              </li>
            </ul>

            <div class="tab-content">
              <div id="overview" class="tab-pane active">
                <form class="form-horizontal" id="form-adotante" method="POST" action="cadastro_adotante.php">
                  <h4 class="mb-xlg">Informações Pessoais</h4>
                  <h5 class="obrig">Campos Obrigatórios(*)</h5>
                  
                  <div class="form-group">
                    <label class="col-md-3 control-label" for="profileFirstName">Nome<sup class="obrig">*</sup></label>
                    <div class="col-md-8">
                      <input type="text" class="form-control" name="nome" id="profileFirstName" id="nome" onkeypress="return Onlychars(event)" required>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-md-3 control-label">Sobrenome<sup class="obrig">*</sup></label>
                    <div class="col-md-8">
                      <input type="text" class="form-control" name="sobrenome" id="sobrenome" onkeypress="return Onlychars(event)" required>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-md-3 control-label" for="profileLastName">Sexo<sup class="obrig">*</sup></label>
                    <div class="col-md-8">
                      <label><input type="radio" name="gender" id="radioM" id="M" value="m" style="margin-top: 10px; margin-left: 15px;" required><i class="fa fa-male" style="font-size: 20px;"></i></label>
                      <label><input type="radio" name="gender" id="radioF" id="F" value="f" style="margin-top: 10px; margin-left: 15px;" ><i class="fa fa-female" style="font-size: 20px;"></i> </label>
                    </div>
                  </div>
                  
                  <div class="form-group">
                    <label class="col-md-3 control-label" for="telefone">Telefone<sup class="obrig">*</sup></label>
                    <div class="col-md-8">
                      <input type="text" class="form-control" maxlength="14" minlength="14" name="telefone" id="telefone" placeholder="Ex: (22)99999-9999" onkeypress="return Onlynumbers(event)" onkeyup="mascara('(##)#####-####',this,event)">
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-md-3 control-label" for="nascimento">Nascimento<sup class="obrig">*</sup></label>
                    <div class="col-md-8">
                      <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="nascimento" id="nascimento" max="<?php echo date('Y-m-d')?>" required>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-md-3 control-label" for="cpf">Número do CPF<sup class="obrig">*</sup></label>
                    <div class="col-md-6">
                      <input type="text" class="form-control" id="cpf" id="cpf" name="cpf" readonly placeholder="Ex: 222.222.222-22" maxlength="14" onblur="validarCPF(this.value)" onkeypress="return Onlynumbers(event)" onkeyup="mascara('###.###.###-##', this, event)" value="<?php
                                                                                                                                                                                                                                                                          if (isset($cpf) && !is_null(trim($cpf))) {
                                                                                                                                                                                                                                                                            echo $cpf;
                                                                                                                                                                                                                                                                          } ?>" required>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-md-3 control-label" for="profileCompany"></label>
                    <div class="col-md-6">
                      <p id="cpfInvalido" style="display: none; color: #b30000">CPF INVÁLIDO!</p>
                    </div>
                  </div>

                  <!-- ENDEREÇO -->
                  <hr class="dotted short">
                  <h4 class="mb-xlg doch4">Endereço</h4>                  

                  <div class="form-group">
                    <label class="col-md-3 control-label" for="cep">CEP<sup class="obrig">*</sup></label>
                    <div class="col-md-8">
                      <input type="text" class="form-control" maxlength="14" minlength="14" name="cep" id="cep" placeholder="Ex: 00000-000" onkeypress="return Onlynumbers(event)" onkeyup="mascara('#####-###',this,event)">
                    </div>
                  </div>                                                                                                                                                                                                                                                                                                                   

                  <div class="form-group">
                    <div class="div-estado">
                          <span class="label-input100">Estado <span class="text-danger">*</span></span>
                          <select class="form-control" id="uf" name="uf">
                              <option value="Selecione sua unidade federativa" disabled></option>
                              <option value="AC">Acre</option>
                              <option value="AL">Alagoas</option>
                              <option value="AP">Amapá</option>
                              <option value="AM">Amazonas</option>
                              <option value="BA">Bahia</option>
                              <option value="CE">Ceará</option>
                              <option value="DF">Distrito Federal</option>
                              <option value="ES">Espírito Santo</option>
                              <option value="GO">Goiás</option>
                              <option value="MA">Maranhão</option>
                              <option value="MT">Mato Grosso</option>
                              <option value="MS">Mato Grosso do Sul</option>
                              <option value="MG">Minas Gerais</option>
                              <option value="PA">Pará</option>
                              <option value="PB">Paraíba</option>
                              <option value="PR">Paraná</option>
                              <option value="PE">Pernambuco</option>
                              <option value="PI">Piauí</option>
                              <option value="RJ">Rio de Janeiro</option>
                              <option value="RN">Rio Grande do Norte</option>
                              <option value="RS">Rio Grande do Sul</option>
                              <option value="RO">Rondônia</option>
                              <option value="RR">Roraima</option>
                              <option value="SC">Santa Catarina</option>
                              <option value="SP">São Paulo</option>
                              <option value="RS">Sergipe</option>
                              <option value="TO">Tocantins</option>
                          </select>
                          <br>
                      </div>
                  </div>

                  <div class="form-group">
                    <label class="col-md-3 control-label" for="cidade">Cidade<sup class="obrig">*</sup></label>
                    <div class="col-md-8">
                      <input type="text" class="form-control" maxlength="30" name="cidade" id="cidade" required>
                    </div>
                  </div>     
                  
                  <div class="form-group">
                    <label class="col-md-3 control-label" for="bairro">Bairro<sup class="obrig">*</sup></label>
                    <div class="col-md-8">
                      <input type="text" class="form-control" maxlength="30" name="bairro" id="bairro" required>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-md-3 control-label" for="logradouro">Logradouro<sup class="obrig">*</sup></label>
                    <div class="col-md-8">
                      <input type="text" class="form-control" maxlength="30" name="logradouro" id="logradouro" required>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-md-3 control-label" for="numero_endereco">Número<sup class="obrig">*</sup></label>
                    <div class="col-md-8">
                      <input type="number" class="form-control" maxlength="9999" minlength="0" name="numero_endereco" id="numero_endereco" required>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-md-3 control-label" for="complemento">Complemento<sup class="obrig">*</sup></label>
                    <div class="col-md-8">
                      <input type="text" class="form-control" maxlength="9999" name="complemento" id="complemento" required>
                    </div>
                  </div>

                  <div class="panel-footer">
                    <div class="row">
                      <div class="col-md-9 col-md-offset-3">
                      <input id="enviar" type="submit" class="btn btn-primary" value="Salvar" onclick="enviarFormularios()">
                        <input type="reset" class="btn btn-default">
                      </div>
                    </div>
                  </div>

                </form>
                <iframe name="frame"></iframe>
            </section>
          </div>
        </section>

        <!-- SCRIPTS IMPORTANTES -->
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
      <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
      <script src="../../../assets/vendor/jquery/jquery.min.js"></script>
      <script src="https://requirejs.org/docs/release/2.3.6/r.js"></script>
      <script src="../../../assets/vendor/modernizr/modernizr.js"></script>
      <script src="../../../Functions/onlyNumbers.js"></script>
      <script src="../../../Functions/onlyChars.js"></script>
      <script src="../../../Functions/mascara.js"></script>
      <script src="../../../Functions/lista.js"></script>
      <script src="<?php echo WWW; ?>Functions/testaCPF.js"></script>
      <script src="../../../assets/vendor/jquery/jquery.js"></script>
      <script src="../../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
      <script src="../../../assets/vendor/bootstrap/js/bootstrap.js"></script>
      <script src="../../../assets/vendor/nanoscroller/nanoscroller.js"></script>
      <script src="../../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
      <script src="../../../assets/vendor/magnific-popup/magnific-popup.js"></script>
      <script src="../../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

      <!-- SCRIPTS  -->  
      <script defer>

        // Limita o número de caracteres do input com id "numero_endereco"
        var inputDoNumeroResidencial = document.getElementById("numero_endereco");
        inputDoNumeroResidencial.addEventListener("input", function(){
          if(inputDoNumeroResidencial.value.length >= 4){
            inputDoNumeroResidencial.value = inputDoNumeroResidencial.value.slice(0, 4);
          }
        });

        // Adiciona a imagem do adotante
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

        function okDisplay() {
          document.getElementById("okButton").style.backgroundColor = "#0275d8"; //azul
          document.getElementById("okText").textContent = "Confirme o arquivo selecionado";
          $("#okButton").css("display", "inline");
          $("#nome").prop('disabled', true);
          $("#radioM").prop('disabled', true);
          $("#radioF").prop('disabled', true);
          $("#nascimento").prop('disabled', true);
          $("#sobrenome").prop('disabled', true);
          $("#telefone").prop('disabled', true);
          $("#cep").prop('disabled', true);
          $("#cidade").prop('disabled', true);
          $("#bairro").prop('disabled', true);
          $("#logradouro").prop('disabled', true);
          $("#numero_endereco").prop('disabled', true);
          $("#complemento").prop('disabled', true);
        }

        function submitButtonStyle(_this) {
          _this.style.backgroundColor = "#5cb85c"; //verde
          document.getElementById("okText").textContent = "Arquivo confirmado";
          $("#nome").prop('disabled', false);
          $("#radioM").prop('disabled', false);
          $("#radioF").prop('disabled', false);
          $("#nascimento").prop('disabled', false);
          $("#sobrenome").prop('disabled', false);
          $("#telefone").prop('disabled', false);
          $("#cep").prop('disabled', false);
          $("#cidade").prop('disabled', false);
          $("#bairro").prop('disabled', false);
          $("#logradouro").prop('disabled', false);
          $("#numero_endereco").prop('disabled', false);
          $("#complemento").prop('disabled', false);
        }

        // Funções para submeter o funcionário
        function enviarFormularios(){
          document.getElementById("form-adotante").submit();
          document.getElementById("formsubmit").submit();
        }
      </script>                                                                                                                                                                                                                                                                        

    <div align="right">
      <iframe src="https://www.wegia.org/software/footer/pet.html" width="200" height="60" style="border:none;"></iframe>
    </div>
</body>
</html>