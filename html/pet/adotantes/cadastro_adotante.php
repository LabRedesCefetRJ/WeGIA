<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

// Inclusão segura do config.php
$config_path = "config.php";
$max_depth = 5;
$depth = 0;

while (!file_exists($config_path) && $depth < $max_depth) {
    $config_path = "../" . $config_path;
    $depth++;
}

if ($depth === $max_depth || !realpath($config_path) || basename($config_path) !== 'config.php') {
    die("Erro ao localizar o arquivo de configuração.");
}
require_once($config_path);

// Função utilitária
function negar_acesso($msg = "Você não tem as permissões necessárias para essa página.") {
    header("Location: ../../home.php?msg_c=" . urlencode($msg));
    exit();
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id_pessoa = $_SESSION['id_pessoa'];

    // Obtem o cargo
    $stmt = $pdo->prepare("SELECT id_cargo FROM funcionario WHERE id_pessoa = :id_pessoa");
    $stmt->execute(['id_pessoa' => $id_pessoa]);
    $id_cargo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$id_cargo) negar_acesso();

    // Verifica permissão
    $stmt = $pdo->prepare("
        SELECT a.id_acao 
        FROM permissao p 
        JOIN acao a ON p.id_acao = a.id_acao 
        JOIN recurso r ON p.id_recurso = r.id_recurso 
        WHERE p.id_cargo = :id_cargo 
        AND a.descricao = 'LER, GRAVAR E EXECUTAR' 
        AND r.descricao = 'Cadastrar Pet'
    ");
    $stmt->execute(['id_cargo' => $id_cargo['id_cargo']]);
    $permissao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$permissao || $permissao['id_acao'] < 5) negar_acesso();

    // Consulta dados adicionais
    $situacao = $pdo->query("SELECT * FROM situacao");
    $cargo = $pdo->query("SELECT * FROM cargo");

} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Pega o CPF via GET (protegido)
$cpf = isset($_GET['cpf']) ? htmlspecialchars($_GET['cpf'], ENT_QUOTES, 'UTF-8') : '';

// Consulta lista de pets
$sqlConsultaPet = "SELECT id_pet, nome FROM pet";
$resultadoConsultaPet = $pdo->query($sqlConsultaPet);


// Adicionar nova pessoa
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $dados = [
    'cpf' => !empty($_POST['cpf']) ? trim($_POST['cpf']) : null,
    'nome' => !empty($_POST['nome']) ? trim($_POST['nome']) : null,
    'sobrenome' => !empty($_POST['sobrenome']) ? trim($_POST['sobrenome']) : null,
    'gender' => !empty($_POST['gender']) ? trim($_POST['gender']) : null,
    'telefone' => !empty($_POST['telefone']) ? trim($_POST['telefone']) : null,
    'nascimento' => !empty($_POST['nascimento']) ? trim($_POST['nascimento']) : null,
    'imgperfil' => !empty($_POST['imagem_base64']) ? trim($_POST['imagem_base64']) : null,
    'cep' => !empty($_POST['cep']) ? trim($_POST['cep']) : null,
    'estado' => !empty($_POST['estado']) ? trim($_POST['estado']) : null,
    'cidade' => !empty($_POST['cidade']) ? trim($_POST['cidade']) : null,
    'bairro' => !empty($_POST['bairro']) ? trim($_POST['bairro']) : null,
    'logradouro' => !empty($_POST['logradouro']) ? trim($_POST['logradouro']) : null,
    'numero_endereco' => !empty($_POST['numero_endereco']) ? trim($_POST['numero_endereco']) : null,
    'complemento' => !empty($_POST['complemento']) ? trim($_POST['complemento']) : null
];


  // Validação dos campos obrigatórios (sem 'imgperfil')

  if (
    is_null($dados['cpf']) ||
    is_null($dados['nome']) ||
    is_null($dados['sobrenome']) ||
    is_null($dados['gender']) ||
    is_null($dados['telefone']) ||
    is_null($dados['nascimento']) ||
    is_null($dados['cep']) ||
    is_null($dados['estado']) ||
    is_null($dados['cidade']) ||
    is_null($dados['bairro']) ||
    is_null($dados['logradouro']) ||
    is_null($dados['numero_endereco'])
) {
  echo "<script>";
  echo "alert('Campos obrigatórios não preenchidos. Dados recebidos: " . json_encode($dados) . "');";
  echo "window.history.back();";
  echo "</script>";
  exit;
   
}



  try {
      $stmt = $pdo->prepare("
          INSERT INTO pessoa (
              cpf, nome, sobrenome, sexo, telefone, data_nascimento, imagem, 
              cep, estado, cidade, bairro, logradouro, numero_endereco, complemento
          ) VALUES (
              :cpf, :nome, :sobrenome, :sexo, :telefone, :data_nascimento, :imagem, 
              :cep, :estado, :cidade, :bairro, :logradouro, :numero_endereco, :complemento
          )
      ");
      
      $stmt->execute([
          ':cpf' => $dados['cpf'],
          ':nome' => $dados['nome'],
          ':sobrenome' => $dados['sobrenome'],
          ':sexo' => $dados['gender'],
          ':telefone' => $dados['telefone'],
          ':data_nascimento' => $dados['nascimento'],
          ':imagem' => $dados['imgperfil'], // Pode ser null
          ':cep' => $dados['cep'],
          ':estado' => $dados['estado'],
          ':cidade' => $dados['cidade'],
          ':bairro' => $dados['bairro'],
          ':logradouro' => $dados['logradouro'],
          ':numero_endereco' => $dados['numero_endereco'],
          ':complemento' => $dados['complemento'],
      ]);

      header("Location: ./informacao_adotantes.php");
      exit();

  } catch (PDOException $e) {
      echo "Erro ao inserir: " . $e->getMessage();
  }
}

if (!isset($cpf)) {
  // Redireciona direto se o CPF estiver ausente
  header("Location: pre_cadastro_adotante.php");
  exit();
}



// Verifica no banco
$stmt = $pdo->prepare("SELECT COUNT(*) FROM pessoa WHERE cpf = ?");
$stmt->execute([$cpf]);
$existe = $stmt->fetchColumn();

if ($existe > 0) {
  // Exibe alerta e redireciona com JavaScript
  echo "
  <script>
      alert('Este CPF já está cadastrado!');
      window.location.href = 'pre_cadastro_adotante.php';
  </script>
  ";
  exit();
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
      <form class="form-horizontal" id="form-adotante" method="POST" action="cadastro_adotante.php" enctype="multipart/form-data" >
 
        <div class="col-md-8 col-lg-8">
          <div class="tabs">
            <ul class="nav nav-tabs tabs-primary">
              <li class="active">
                <a href="#overview" data-toggle="tab">Cadastro do Adotante</a>
              </li>
            </ul>

            <div class="tab-content">
              <div id="overview" class="tab-pane active">
                
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
                      <input type="text" class="form-control" maxlength="14" minlength="14" name="cep" id="cep" placeholder="Ex: 00000-000" onkeypress="return Onlynumbers(event)" onkeyup="mascara('#####-###',this,event)" onblur="BuscaCEP(this.value)">
                    </div>
                  </div>                                                                    
                                                                                                                                                                                                                                                                                                                

                  <div class="form-group">
                  <label class="col-md-3 control-label" for="estado">Estado<sup class="obrig">*</sup></label>
                    <div class="col-md-8">
                      <input type="text" class="form-control" maxlength="30" name="estado" id="estado" required readonly>
                      </div>
                  </div>

                  <div class="form-group">
                    <label class="col-md-3 control-label" for="cidade">Cidade<sup class="obrig">*</sup></label>
                    <div class="col-md-8">
                      <input type="text" class="form-control" maxlength="30" name="cidade" id="cidade" required readonly>
                    </div>
                  </div>     
                  
                  <div class="form-group">
                    <label class="col-md-3 control-label" for="bairro">Bairro<sup class="obrig">*</sup></label>
                    <div class="col-md-8">
                      <input type="text" class="form-control" maxlength="30" name="bairro" id="bairro" required readonly>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-md-3 control-label" for="logradouro">Logradouro<sup class="obrig">*</sup></label>
                    <div class="col-md-8">
                      <input type="text" class="form-control" maxlength="30" name="logradouro" id="logradouro" required readonly>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-md-3 control-label" for="numero_endereco">Número<sup class="obrig">*</sup></label>
                    <div class="col-md-8">
                      <input type="number" class="form-control" maxlength="9999" minlength="0" name="numero_endereco" id="numero_endereco" required>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-md-3 control-label" for="complemento">Complemento<sup class="obrig"></sup></label>
                    <div class="col-md-8">
                      <input type="text" class="form-control" maxlength="9999" name="complemento" id="complemento">
                    </div>
                  </div>

                  <div class="panel-footer">
                    <div class="row">
                      <div class="col-md-9 col-md-offset-3">
                      <input id="enviar" type="submit" class="btn btn-primary" value="Salvar">
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

        async function BuscaCEP(cep) {
  try {
    cep = cep.replace(/\D/g, '');

    const estado = document.querySelector("#estado");
    const cidade = document.querySelector("#cidade");
    const bairro = document.querySelector("#bairro");
    const logradouro = document.querySelector("#logradouro");
    const cepInput = document.querySelector("#cep");

    // Limpa os valores, mas garante que todos os campos sejam editáveis
    estado.value = '';
    cidade.value = '';
    bairro.value = '';
    logradouro.value = '';
    estado.readOnly = false;
    cidade.readOnly = false;
    bairro.readOnly = false;
    logradouro.readOnly = false;

    if (cep.length !== 8) return;

    const url = `https://viacep.com.br/ws/${cep}/json/`;
    const dadosJSON = await fetch(url);
    const dados = await dadosJSON.json();

    if (dados.erro) {
      alert("CEP não encontrado. Você pode preencher o endereço manualmente.");
      return;
    }

    // Preenche os campos com os dados do CEP, se existirem
    if (dados.uf) estado.value = dados.uf;
    if (dados.localidade) cidade.value = dados.localidade;
    if (dados.bairro && dados.bairro.trim() !== '') bairro.value = dados.bairro;
    if (dados.logradouro && dados.logradouro.trim() !== '') logradouro.value = dados.logradouro;

    // Garante que todos continuem editáveis
    estado.readOnly = false;
    cidade.readOnly = false;
    bairro.readOnly = false;
    logradouro.readOnly = false;

  } catch (erro) {
    console.error("Erro ao buscar o CEP:", erro);
    alert("Erro ao buscar o CEP. Você pode preencher o endereço manualmente.");
    estado.readOnly = false;
    cidade.readOnly = false;
    bairro.readOnly = false;
    logradouro.readOnly = false;
  }
}




</script>                                                                          

    <div align="right">
      <iframe src="https://www.wegia.org/software/footer/pet.html" width="200" height="60" style="border:none;"></iframe>
    </div>
</body>
</html>