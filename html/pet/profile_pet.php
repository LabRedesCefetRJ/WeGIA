<?php

  ini_set('display_errors',1);
  ini_set('display_startup_erros',1);
  error_reporting(E_ALL);
  //extract($_REQUEST);
  session_start();
  require_once "../../dao/Conexao.php";
  $pdo = Conexao::connect();
  if (!isset($_SESSION['usuario'])) 
  {
    header("Location: ../index.php");
  } 
  else if (!isset($_SESSION['pet'])) 
  {
    $id_pet = $_GET['id_pet'];
    header('Location: ../../controle/control.php?modulo=pet&metodo=listarUm&nomeClasse=PetControle&nextPage='. WWW. '/html/pet/profile_pet.php?id_pet=' . $id_pet . '&id_pet=' . $id_pet);
  }  
  else 
  {
    $petDados = $_SESSION['pet'];
    unset($_SESSION['pet']);
    $pet = json_encode($petDados);
 
  }
  $config_path = "config.php";
  if (file_exists($config_path)) 
  {
    require_once($config_path);
  } 
  else 
  {
    while (true) 
    {
      $config_path = "../" . $config_path;
      if (file_exists($config_path)) break;
    }
    require_once($config_path);
  }
  require_once "../permissao/permissao.php";
  permissao($_SESSION['id_pessoa'], 63, 7);
  require_once "../personalizacao_display.php";
  require_once "../../dao/Conexao.php";
  require_once "../geral/msg.php";
 
  // Lógica para listar os adotantes
  $dsn = 'mysql:host=localhost;dbname=wegia;charset=utf8';  
  $username = 'wegiauser'; 
  $password = 'senha';

  try {
      $conexao = new PDO($dsn, $username, $password);
      $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $sqlListarAdotantes = "SELECT id_pessoa, nome, sobrenome FROM pessoa;";
      
      $stmt = $conexao->prepare($sqlListarAdotantes);
      $stmt->execute();

      $resultadosListarAdotantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

  } catch (PDOException $e) {
      echo 'Erro ao conectar ao banco de dados: ' . $e->getMessage();
  }

  // Lógica para adicionar no banco de dados
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $adotante = !empty($_POST["adotante_input"]) ? $_POST["adotante_input"] : NULL;
    $dataAdocao = !empty($_POST["dataAdocao"]) ? $_POST["dataAdocao"] : NULL;
    $idPet = !empty($_POST["id_pet"]) ? $_POST["id_pet"] : NULL;

    if ($adotante && $dataAdocao && $idPet) {
        $sqlAdicionarPetAdotado = "INSERT INTO pet_adocao (id_pessoa, id_pet, data_adocao) VALUES (:adotante, :idPet, :dataAdocao)";

        try {
            $conexao = new PDO($dsn, $username, $password);
            $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $conexao->prepare($sqlAdicionarPetAdotado);
            $stmt->bindParam(':adotante', $adotante);
            $stmt->bindParam(':idPet', $idPet);
            $stmt->bindParam(':dataAdocao', $dataAdocao);

            $stmt->execute();
            
            header('Location: profile_pet.php?id_pet='.$idPet);
            exit;
        } catch (PDOException $e) {
            echo 'Erro ao conectar ao banco de dados: ' . $e->getMessage();
        }
    } else {
        echo "Por favor, preencha todos os campos corretamente.";
    }
}


?>

<!doctype html>
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
    <link rel="stylesheet" href="../../assets/print.css">
    <!-- jkeditor -->
    <script src="<?php echo WWW;?>assets/vendor/ckeditor/ckeditor.js"></script>

    <style type="text/css">
      .btn span.fa-check 
      {
        opacity: 0;
      }
      .btn.active span.fa-check 
      {
        opacity: 1;
      }
      #frame
      {
        width: 100%;
      }
      .obrig
      {
        color: rgb(255, 0, 0);
      }
      .form-control
      {
        padding: 0 12px;
      }
      .btn i
      {
        color: white;
      }
      .select{
			position: absolute;
			width: 235px;
       /*print styles*/
      }
      #div_texto
      {
        width: 100%;
      }
      #cke_outras_informacoes
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
      #secFichaMedica{
        margin: 0 0 0 15px;
      }
    </style>
    <!-- jquery functions -->
    <script>
      let pet = <?= $pet ?>;
      let iNascimento;
      let iAcolhimento;
      

		

	
      //Informações Pet
      function editar_informacoes_pet() 
      {
        $("#nomeForm").prop('disabled', false);
        $("#especificas").prop('disabled', false);
        $("#radioM").prop('disabled', false);
        $("#radioF").prop('disabled', false);
        $("#nascimento").prop('disabled', false);
        $("#acolhimento").prop('disabled', false);
        $("#cor").prop('disabled', false);
        $("#especie").prop('disabled', false);
        $("#raca").prop('disabled', false);
        $("#editarPet").html('Cancelar').removeClass('btn-secondary').addClass('btn-danger');
        $("#salvarPet").prop('disabled', false);
        $("#editarPet").removeAttr('onclick');
        $("#editarPet").attr('onclick', "return cancelar_informacoes_pet()");
      }
      function cancelar_informacoes_pet() 
      { 
        
        $.each(pet, function(i, item) 
        {
          $("#nomeForm").val(item.nome).prop('disabled', true);
          $("#especificas").val(item.especificas).prop('disabled', true);
          if (item.sexo == "M") 
            {
              $("#radioM").prop('checked', true).prop('disabled', true);
              $("#radioF").prop('checked', false).prop('disabled', true);
            } 
          else if (item.sexo == "F") 
            {
              $("#radioM").prop('checked', false).prop('disabled', true)
              $("#radioF").prop('checked', true).prop('disabled', true);
            }
          $("#nascimento").val(item.nascimento).prop('disabled', true);
          $("#acolhimento").val(item.acolhimento).prop('disabled', true);
          $("#cor").val(item.cor).prop('disabled', true);
          $("#especie").val(item.especie).prop('disabled', true);
          $("#raca").val(item.raca).prop('disabled', true);
          $("#editarPet").html('Editar').removeClass('btn-danger').addClass('btn-secondary');
          $("#salvarPet").prop('disabled', true);
          $("#editarPet").removeAttr('onclick');
          $("#editarPet").attr('onclick', "return editar_informacoes_pet()");
        })
      }

      
      $(function() 
      {
        $.each(pet, function(i, item) 
        {
          //Informações pet
          $("#nomeForm").val(item.nome).prop('disabled', true);
          if (item.sexo == "M") 
          {
            $("#radioM").prop('checked', true).prop('disabled', true);
            $("#radioF").prop('checked', false).prop('disabled', true);
          } 
          else if (item.sexo == "F") 
          {
            $("#radioM").prop('checked', false).prop('disabled', true)
            $("#radioF").prop('checked', true).prop('disabled', true);
          }
          $("#nascimento").val(item.nascimento).prop('disabled', true);
          iNascimento = item.nascimento;
          $("#acolhimento").val(item.acolhimento).prop('disabled', true);
          iAcolhimento = item.acolhimento;
          $("#especie").val(item.especie).prop('disabled', true);
          $("#cor").val(item.cor).prop('disabled', true);
          $("#raca").val(item.raca).prop('disabled', true);
          $("#especificas").val(item.especificas).prop('disabled', true);
          
        })
      });

         // Ajuste de data
         function alterardate(data) 
      {
        var date = data.split("-");
        alert(date);
        return data;
      }

      
      
      $(function() 
      {
        $("#header").load("../header.php");
        $(".menuu").load("../menu.php");
      });

      
    </script>
  </head>
  <body>
    <section class="body">
      <div id="header"></div>
      <!-- end: header -->
      <div class="inner-wrapper">
        <!-- start: sidebar -->
        <aside id="sidebar-left" class="sidebar-left menuu"></aside>
        <!-- end: sidebar -->
        <section role="main" class="content-body">
          <header class="page-header">
            <h2>Perfil</h2>
            <div class="right-wrapper pull-right">
              <ol class="breadcrumbs">
                <li>
                  <a href="../home.php">
                    <i class="fa-solid fa-home"></i>
                  </a>
                </li>
                <li><span>Páginas</span></li>
                <li><span>Perfil</span></li>
              </ol>
              <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
            </div>
          </header>
          <!-- start: page -->
          <!-- Mensagem -->
          <?php getMsgSession("msg", "tipo"); ?>
          <!----pedro-->
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
                              <form class="form-horizontal" method="POST" action="../../controle/control.php" enctype="multipart/form-data">
                                <input type="hidden" name="nomeClasse" value="PetControle">
                                <input type="hidden" name="metodo" value="alterarImagem">
                                <input type="hidden" name="modulo" value="pet">
                                <div class="form-group">
                                  <label class="col-md-4 control-label" for="imgperfil">Carregue nova imagem de perfil:</label>
                                  <div class="col-md-8">
                                    <input type="file" name="imgperfil" size="60" id="imgform" class="form-control">
                                  </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                              <input type="hidden" name="id_pet" value=<?php echo $_GET['id_pet'] ?>>
                              <input type="hidden" name="id_foto" id="id_foto" >
                              <input type="submit" id="formsubmit" value="Alterar imagem">
                            </div>
                          </div> 
                        </form>
                      </div>
                      </div>
                    </div>
        <!----pedrofim-->

          <div class="row">
            <div class="col-md-4 col-lg-3">
              <section class="panel">
                <div class="panel-body">
                  <div class="thumb-info mb-md">
                    
                    <?php
                    echo "<img  id='imagem' class='rounded img-responsive' alt='John Doe'>";
                      //Pedro
                      $id_pessoa = $_SESSION['id_pessoa'];
                      $donoimagem = $_GET['id_pet'];

                      //
                      $conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                      if (isset($_SESSION['id_pessoa']) and !empty($_SESSION['id_pessoa'])) 
                      {
                        echo "
                        <script>
                          let id_foto = document.querySelector('#id_foto');
                          let img = document.querySelector('#imagem');
                          fetch('./foto.php',{
                            method: 'POST',
                            body: JSON.stringify({'id':".$donoimagem."})
                          }).then((resp)=>{
                            return resp.json()
                          }).then((resp)=>{
                            let petImagem = resp;
                            let foto;
                            
                            if(petImagem){
                              foto = petImagem['imagem'];
                              id_foto.value = petImagem['id_foto'];
                              if(foto != null && foto != ''){
                                foto = 'data:image;base64,'+foto;
                              }
                            }else{
                              foto = '../../img/semfoto.png';
                            }
                            img.src = foto;
                          });
                        </script>
                      ";
                      }
                      ?>
                    <i class="fas fa-camera-retro btn btn-info btn-lg" data-toggle="modal" data-target="#myModal"></i>
                    
                  </div>
                  <div class="widget-toggle-expand mb-md">
                    <div class="widget-header">
                      <div class="widget-content-expanded">
                        <ul class="simple-todo-list"></ul>
                      </div>
                    </div>
                  </div>
                </div>
              </section>
            </div>
            <div class="col-md-8 col-lg-8">
              <div class="tabs">
                <ul class="nav nav-tabs tabs-primary">
                  <li class="active">
                    <a href="#overview" data-toggle="tab">Informações do Pet</a>
                  </li>
                  <li>
                    <a href="#ficha_medica" data-toggle="tab">Ficha Médica</a>
                  </li>
                  <li>
                    <a href="#atendimento" data-toggle="tab">Atendimento</a>
                  </li>
                  <li>
                    <a href="#historico_medico" data-toggle="tab">Histórico Médico</a>
                  </li>
                  <li>
                    <a href="#arquivosPet" data-toggle="tab">Exames do Pet</a>
                  </li>
                  <li>
                    <a href="#adocao" data-toggle="tab">Adoção</a>
                  </li>
                </ul>

                <div class="tab-content">
                  <!--Aba de Informações Pet-->
                  <div id="overview" class="tab-pane active">
                    <form class="form-horizontal" method="post" action="../../controle/control.php">
                      <div class="myModal print">  
                        <input type="hidden" name="nomeClasse" value="PetControle">
                        <input type="hidden" name="metodo" value="alterarPetDados">
                        <input type="hidden" name="modulo" value="pet">
                          
                        <h4 class="mb-xlg">Informações Pet</h4>
                        <fieldset>
                          <div class="form-group">
                            <label class="col-md-3 control-label" for="nome">Nome</label>
                            <div class="col-md-8">
                              <input type="text" class="form-control" name="nome" id="nomeForm" onkeypress="return Onlychars(event)" required>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-md-3 control-label" for="profileLastName">Sexo</label>
                            <div class="col-md-8">
                              <label><input type="radio" name="gender" id="radioM" id="M" value="M" style="margin-top: 10px; margin-left: 15px;" > <i class="fa fa-mars" style="font-size: 20px;"></i></label>
                              <label><input type="radio" name="gender" id="radioF" id="F" value="F" style="margin-top: 10px; margin-left: 15px;" > <i class="fa fa-venus" style="font-size: 20px;"></i></label>
                            </div>
                          </div>
                          
                          <div class="form-group">
                            <label class="col-md-3 control-label" for="profileCompany">Nascimento</label>
                            <div class="col-md-8">
                              <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="nascimento" id="nascimento" max=<?php echo date('Y-m-d');?> required>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-md-3 control-label" for="profileCompany">Acolhimento</label>
                            <div class="col-md-8">
                              <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="acolhimento" id="acolhimento" max=<?php echo date('Y-m-d'); ?> required>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-md-3 control-label" for="inputSuccess">Cor</label>
                            <div class="col-md-6">
                              <select class="form-control input-lg mb-md" name="cor" id="cor">
                                <?php
                                  $cor = mysqli_query($conexao, "SELECT id_pet_cor AS id_cor, descricao AS 'cor' FROM pet_cor");
                                  foreach ($cor as $valor) {
                                    echo "<option value=".$valor['id_cor']." >".$valor['cor']."</option>";
                                  }
                                ?>
                              </select>
                            </div>
                          </div>
                          
                          <div class="form-group">
                            <label class="col-md-3 control-label" for="inputSuccess">Espécie</label>
                            <div class="col-md-6">
                              <select class="form-control input-lg mb-md" name="especie" id="especie">
                                <?php
                                  $especie = mysqli_query($conexao, "SELECT id_pet_especie AS id_especie, descricao AS 'especie' FROM pet_especie");
                                  foreach ($especie as $valor) {
                                    echo "<option value=".$valor['id_especie']." >".$valor['especie']."</option>";
                                  }
                                ?>
                              </select>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-md-3 control-label" for="inputSuccess">Raça</label>
                            <div class="col-md-6">
                              <select class="form-control input-lg mb-md" name="raca" id="raca">
                                <?php
                                  $raca = mysqli_query($conexao, "SELECT id_pet_raca AS id_raca, descricao AS 'raca' FROM pet_raca");
                                  foreach ($raca as $valor) {
                                    echo "<option value=".$valor['id_raca']." >".$valor['raca']."</option>";
                                  }        /* .col-md-3 {
                                    width: 10%;
                                } */
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-md-3 control-label" for="especificas">Características Específicas</label>
                            <div class="col-md-8">
                              <textarea name="especificas" class="form-control" id="especificas"></textarea>
                            </div>
                          </div>
                      </div>
                          </br>
                          <input type="hidden" name="id_pet" value=<?php echo $_GET['id_pet'] ?>>
                          <button type="button" class="not-printable btn btn-primary" id="editarPet" onclick="return editar_informacoes_pet()">Editar</button>
                          <input type="submit" class="not-printable btn btn-primary" disabled="true" value="Salvar" id="salvarPet">
                          <button type="button" class="not-printable mb-xs mt-xs mr-xs btn btn-default" id="btnPrint">Imprimir <i class="fa-solid fa-print" style = "color:black"></i></button>
                        </fieldset>
                      
                    </form>
                </div>
                <!--Arquivos-->
                <div id="arquivosPet" class="tab-pane">
                  <section class="panel">
                      <header class="panel-heading">
                        <div class="panel-actions">
                          <a href="#" class="fa fa-caret-down"></a>
                        </div>
                        <h2 class="panel-title">Arquivos do Pet</h2>
                      </header>
                      <div class="panel-body">
                        <table class="table table-bordered table-striped mb-none">
                          <thead>
                            <tr>
                              <th>Arquivo</th>
                              <th>Data</th>
                              <th>Ação</th>
                            </tr>
                          </thead>
                          <tbody id="doc-tab">
                            <?php
                            $idPet = $_GET['id_pet'];
                            $pd = $pdo->query("SELECT id_ficha_medica FROM pet_ficha_medica WHERE id_pet =".$idPet);
                            $p = $pd->fetch();
                            if($p != false){
                              $id_ficha_medica = $p['id_ficha_medica'];
                              $exames = $pdo->query("SELECT *, pte.descricao_exame AS 'arkivo' FROM pet_exame pe JOIN pet_tipo_exame pte ON 
                              pe.id_tipo_exame = pte.id_tipo_exame WHERE pe.id_ficha_medica = ".$p['id_ficha_medica']);
                              $exame = $exames->fetchAll();
                              if($exame){
                                forEach($exame as $valor){
                                  $data = explode('-',$valor['data_exame']);
                                  $data = $data[2].'-'.$data[1].'-'.$data[0];
                                  $arkivo = $valor['arkivo'];
                                  echo <<<HTML
                                    <tr id="tr$valor[id_exame]">
                                      <td><p id="ark$valor[id_exame]">$arkivo</p></td>
                                      <td>$data</td>
                                      <td style="display: flex; justify-content: space-evenly;">
                                        <a href="data:$valor[arquivo_extensao];base64,$valor[arquivo_exame]" title="Baixar" download="$valor[descricao_exame].$valor[arquivo_extensao]">
                                          <button class="btn btn-primary">
                                            <i class="fas fa-download"></i>
                                          </button>
                                        </a>
                                        <a onclick="excluirArquivo($valor[id_exame])" href="#" title="Excluir">
                                          <button class="btn btn-danger">
                                            <i class="fas fa-trash-alt"></i>
                                          </button>
                                        </a>
                                      </td>
                                    </tr>
                                  HTML;
                                }
                              }
                            }
                            ?>
                          </tbody>
                        </table><br>
                        <!-- Button trigger modal -->
                        
                        <?php
                          if($p != false){
                            echo <<<HTML
                              <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#docFormModal">
                                Adicionar
                              </button>
                            HTML;
                          }else{
                            echo <<<HTML
                              <p>É necessário que o animal possua uma ficha médica para poder registrar os exames!</p>
                              <a href="./cadastro_ficha_medica_pet.php?id_pet=$_GET[id_pet]"><input class ="btn btn-primary" 
                              type="button" value='Cadastrar Ficha médica'></a>
                            HTML;
                          }
                        ?>

                        <!-- Modal Form Documentos -->
                        <div class="modal fade" id="docFormModal" tabindex="-1" role="dialog" aria-labelledby="docFormModalLabel" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header" style="display: flex;justify-content: space-between;">
                                <h5 class="modal-title" id="exampleModalLabel">Adicionar Arquivo</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <form action='../../controle/control.php' method='post' enctype='multipart/form-data'>
                                <div class="modal-body" style="padding: 15px 40px">
                                  <div class="form-group" style="display: grid;">
                                    <label class="my-1 mr-2" for="tipoDocumento">Tipo de Arquivo</label><br>
                                    <div style="display: flex;">
                                      <select name="id_tipo_exame" class="custom-select my-1 mr-sm-2" id="tipoExame" required>
                                        <option selected disabled>Selecionar Tipo</option>
                                        <?php
                                          foreach ($pdo->query("SELECT * FROM pet_tipo_exame ORDER BY descricao_exame ASC;")->fetchAll(PDO::FETCH_ASSOC) as $item) 
                                          {
                                            echo ("<option value=" . $item["id_tipo_exame"] . ">" . $item["descricao_exame"] . "</option>");
                                          }
                                        ?>
                                      </select>
                                      <a onclick="addTipoExame()" style="margin: 0 20px;" id="btn_adicionar_tipo_remuneracao"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label for="arquivoDocumento">Arquivo</label>
                                    <input name="arquivo" type="file" class="form-control-file" id="arquivoDocumento" accept="png;jpeg;jpg;pdf;docx;doc;odp" required>
                                  </div>
                                  <input type="hidden" name="modulo" value="pet">
                                  <input type="hidden" name="nomeClasse" value="PetControle">
                                  <input type="hidden" name="metodo" value="incluirExamePet">
                                  <input type="hidden" name="id_ficha_medica" value="<?= $id_ficha_medica ?>">
                                  <input type="hidden" name="id_pet" value="<?= $_GET['id_pet'] ?>">
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                  <input type="submit" value="Enviar" class="btn btn-primary">
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                      </div>
                  </section>                  
                </div>

                <!-- Ficha Medica-->
                <div id="ficha_medica" class="tab-pane">
                  <section id="secFichaMedica"> <!--Corrigir o problema da impressão-->
                      <h4 class="mb-xlg" id="fm">Ficha Médica</h4>
                      <div id="divFichaMedica">
                        <form class="form-horizontal" method="post" action="../../controle/control.php">
                          <input type="hidden" name="nomeClasse" value="controleSaudePet">
                          <input type="hidden" name="metodo" value="modificarFichaMedicaPet">
                          <input type="hidden" name="modulo" value="pet">
                          <fieldset>
                            <!--Castrado-->
                            <div class="form-group">
                              <label class="col-md-3 control-label" for="profileLastName">Animal Castrado:</label>
                              <div class="col-md-8">
                                <label><input type="radio" name="castrado" id="castradoS" value="S" style="margin-top: 10px; margin-left: 15px;" > <i class="fa" style="font-size: 20px;">Sim</i></label>
                                <label><input type="radio" checked name="castrado" id="castradoN" value="N" style="margin-top: 10px; margin-left: 15px;" > <i class="fa" style="font-size: 20px;">Não</i></label>
                              </div>
                            </div>

                            <!--Vermifugado-->
                            <div class="form-group">
                              <label class="col-md-3 control-label" for="profileLastName">Vermifugado:</label>
                              <div class="col-md-8">
                                <label><input type="radio" name="vermifugado" id="vermifugadoS" value="S" style="margin-top: 10px; margin-left: 15px;" > <i class="fa" style="font-size: 20px;">Sim</i></label>
                                <label><input type="radio" checked name="vermifugado" id="vermifugadoN" value="N" style="margin-top: 10px; margin-left: 15px;" > <i class="fa" style="font-size: 20px;">Não</i></label>
                              </div>
                              <div class="form-group" id="div_vermifugado"> 
                                <label class="col-md-3 control-label" for="dVermifugado">Data de vermifugação:<sup class="obrig">*</sup></label>
                                <div class="col-md-8">
                                  <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="dVermifugado" id="dVermifugado" max=<?php echo date('Y-m-d');?> required>
                                </div>                                              
                              </div>
                            </div>

                            <!--Vacinado-->
                            <div class="form-group">
                              <label class="col-md-3 control-label" for="vacinado">Vacinado:</label>
                              <div class="col-md-8">
                                <label><input type="radio" name="vacinado" id="vacinadoS" value="S" style="margin-top: 10px; margin-left: 15px;" > <i class="fa" style="font-size: 20px;">Sim</i></label>
                                <label><input type="radio" checked name="vacinado" id="vacinadoN" value="N" style="margin-top: 10px; margin-left: 15px;" > <i class="fa" style="font-size: 20px;">Não</i></label>                     
                              </div>
                              <div class="form-group" id="div_vacinado">
                                <label class="col-md-3 control-label" for="dVacinado">Data de vacinação:<sup class="obrig">*</sup></label>
                                <div class="col-md-8">
                                  <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="dVacinado" id="dVacinado" max=<?php echo date('Y-m-d');?> required>
                                </div>                                              
                              </div>
                            </div>

                            <!--Outras informacoes-->
                            <div class="form-group">
                              <div class="form-group">
                                <label for="texto" id="etiqueta_despacho" class="col-md-3 control-label">Outras informações:</label>
                                <div class="col-md-8">
                                  <textarea name="texto" class="form-control col-md-8" id="despacho"></textarea>
                                </div>
                              </div>
                            </div>
                            </br>
                            <div class="buttons">
                              <input type="hidden" name="id_pet" value=<?php echo $_GET['id_pet'] ?>>
                              <input type="hidden" name="id_ficha_medica" id="id_ficha_medica">
                              <button type="button" class="d-print-none btn btn-primary" id="editarFichaMedica" >Editar Ficha Médica</button>
                              <input type="submit" class="d-print-none btn btn-primary" value="Salvar Ficha Médica" id="salvarFichaMedica">
                              <button type="button" class="d-print-none mb-xs mt-xs mr-xs btn btn-default" id="btnPrint2">Imprimir <i class="fa-solid fa-print" style = "color:black"></i></button>
                            </div>
                          </fieldset>
                        </form>
                    </div>
                  </section>
                  <!-- </div> -->
                </div>

                <!--atendimento-->
                <div id="atendimento" class="tab-pane">
                  <section class="panel">
                      <header class="panel-heading">
                        <div class="panel-actions">
                          <a href="#" class="fa fa-caret-down"></a>
                        </div>
                        <h2 class="panel-title">Atendimento</h2>
                      </header>
                      <div id="divAtendimento" class="panel-body">
                        <form class="form-horizontal" method="post" action="../../controle/control.php">
                          <input type="hidden" name="nomeClasse" value="AtendimentoControle">
                          <input type="hidden" name="metodo" value="registrarAtendimento">
                          <input type="hidden" name="modulo" value="pet">
                          <fieldset>

                            <div class="form-group"> 
                              <div class="col-md-8">           
                                <a href="./cadastrar_medicamento.php?pga=<?php echo $_GET["id_pet"]?>"><button type="button" class="btn btn-success" id="cadastrarMedicamento">Cadastrar Medicamento</button></a>
                              </div>
                            </div>

                            <div class="form-group">
                              <label class="col-md-3 control-label" for="profileCompany">Data do Atendimento<sup class="obrig">*</sup></label>
                              <div class="col-md-8">
                                <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="dataAtendimento" id="dataAtendimento" max=<?php echo date('Y-m-d');?> required>
                              </div>
                            </div>

                            <div class="form-group">
                              <!--<div class='col-md-6' id='div_texto'>
                                <label for="texto" id="etiqueta_despacho" style="padding-left: 15px;">Descrição do Atendimento:<sup class="obrig">*</sup></label>
                                <textarea cols='30' rows='5' required id='descricaoAtendimento' name="descricaoAtendimento" class='form-control'></textarea>
                              </div>-->
                              <label class="col-md-3 control-label" for="descricaoAtendimento">Descricao Atendimento<sup class="obrig">*</sup></label>
                              <div class="col-md-8">
                                <textarea name="descricaoAtendimento" class="form-control" id="descricaoAtendimento"></textarea>
                              </div>
                            </div>

                            <div class="form-group">
                              <label class="col-md-3 control-label" for="inputSuccess">Medicamento</label>
                              
                              <div class="col-md-6">
                                <select class="form-control input-lg mb-md" name="selectMedicamento" id="selectMedicamento">
                                  <option value="Selecione" disabled selected>Selecione</option>
                                </select>
                              </div>
                              <button type="button" class="btn btn-success" id="prescreverMedicacao">Prescrever medicação</button>
                              <input type="hidden" name="medics" id="medics">
                              <table class="table table-bordered table-striped mb-none" id="tabmed">
                                <thead>
                                  <tr style="font-size:15px;">
                                    <th>Medicação</th>
                                    <th>Ação</th>
                                  </tr>
                                </thead>
                                <tbody id="dep-tab" style="font-size:15px">
                                
                                </tbody>
                              </table>
                            </div>

                            
                            </br>
                            <input type="hidden" name="id_pet" value=<?php echo $_GET['id_pet'] ?>>
                            <input type="submit" class="btn btn-primary" value="Salvar Atendimento" id="salvarAtendimento">
                          </fieldset>
                        </form>
                      </div>
                  </section>                  
                </div>
                <!-- fim atendimento -->

                <!-- Historico medico -->
                <div id="historico_medico" class="tab-pane">
                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Histórico Médico</h2>
                    </header>
               
                    <div class="panel-body">
                      <hr class="dotted short">
                      <div class="form-group" id="tab_atendimento" >
                      <table class="table table-bordered table-striped mb-none">
                        <thead>
                          <tr style="font-size:15px;">
                            <th>Data do atendimento</th>
                            <th>Descrições</th>
                            <th>Ação</th>
                          </tr>
                        </thead>
                        <tbody id="tab_historico" style="font-size:15px">                
                            
                        </tbody>
                      </table>                
                      </div>          
                    </div>
                  </section>
                </div>
                <!-- fim historico medico -->

                <!-- Adoção -->
                <div id="adocao" class="tab-pane">
                    <section class="panel">
                        <header class="panel-heading">
                            <div class="panel-actions">
                                <a href="#" class="fa fa-caret-down"></a>
                            </div>
                            <h2 class="panel-title">Adoção do Pet</h2>
                        </header>
                        <div class="panel-body">
                            <form class="form-horizontal" id="form_adocao" method="post" action="">
                                <fieldset>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label" for="profileLastName">Adotado</label>
                                        <div class="col-md-8">
                                            <label><input type="radio" name="adotado" id="adotadoS" value="S" style="margin-top: 10px; margin-left: 15px;"> <i class="fa" style="font-size: 20px;">Sim</i></label>
                                            <label><input type="radio" checked name="adotado" id="adotadoN" value="N" style="margin-top: 10px; margin-left: 15px;"> <i class="fa" style="font-size: 20px;">Não</i></label>
                                        </div>
                                    </div>
                                    <div id="dadosAdocao">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="profileName">Nome do Adotante</label>
                                            <div class="col-md-8">
                                                <select class="form-control input-lg mb-md" name="adotante_input" id="adotante_input" required>
                                                    <option selected disabled value="">Selecionar</option>
                                                    <?php
                                                    // Lista todos os adotantes
                                                    foreach ($resultadosListarAdotantes as $resultado) {
                                                        if ($resultado["id_pessoa"] != 1 && $resultado["id_pessoa"] != 2) {
                                                            echo "<option value='" . $resultado["id_pessoa"] . "'>" . $resultado["nome"] . " " . $resultado["sobrenome"] . "</option>";
                                                        }
                                                    }

                                                    if (count($resultadosListarAdotantes) == 0 || !array_filter($resultadosListarAdotantes, function ($adotante) {
                                                        return $adotante["id_pessoa"] != 1 && $adotante["id_pessoa"] != 2;
                                                    })) {
                                                        echo "<option value=''>Adotantes não encontrados.</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- DATA ADOÇÃO -->
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="profileCompany">Data da adoção</label>
                                            <div class="col-md-8">
                                                <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="dataAdocao" id="dataAdocao" max="<?php echo date('Y-m-d'); ?>" required>
                                            </div>
                                        </div>

                                    </div>
                                    </br>

                                    <input type="hidden" name="id_pet" value="<?php echo $idPet; ?>">
                                    <button type="button" class="btn btn-primary" id="editarAdocao" onclick="return editarAdocaoPet()">Editar Adoção</button>
                                    <button type="submit" class="btn btn-primary" id="submit_adocao" name="submit_adocao" onclick="return enviarFormulário(event)">Salvar</button>
                                </fieldset>
                            </form>
                        </div>
                    </section>
                </div>
                <!-- fim adocao-->

              </div> 
            </div>
          </div>
        </section>
      </div>
    </section>
    
    <script type="text/javascript">
      const botaoAdocao = document.getElementById("submit_adocao");
      botaoAdocao.addEventListener("click", function() {
          alert("Adoção registrada com sucesso!");
      });

      //Adoção
      let nomeAdotante = document.querySelector("#nomeAdotante");
      let adotadoS = document.querySelector("#adotadoS");
      let adotadoN = document.querySelector("#adotadoN");
      let dataAdocao = document.querySelector("#dataAdocao");
      let editarAdocao = document.querySelector("#editarAdocao");
      let salvarAdocao = document.querySelector("#salvarAdocao");
      let adotante_input = document.querySelector("#adotante_input");

      adotadoS.disabled = true;
      adotadoN.disabled = true;
      
      function editarAdocaoPet(){
        if(editarAdocao.innerHTML == "Editar Adoção"){
          adotadoS.disabled = false;
          adotadoN.disabled = false;
          dataAdocao.disabled = false;
          salvarAdocao.disabled = false;
          adotante_input.disabled = false;
          editarAdocao.innerHTML = "Cancelar";
        }else{
          document.location.reload();
        }
      }

      if( adotadoN.checked == true){
        document.querySelector("#dadosAdocao").style.display = "none";
      }

      adotadoS.addEventListener("click", ()=>{
        document.querySelector("#dadosAdocao").style.display = "";
        nomeAdotante.value = '';
        adotante_input.value = '';
        dataAdocao.value = '';
      })

      adotadoN.addEventListener("click", ()=>{
        document.querySelector("#dadosAdocao").style.display = "none";
        nomeAdotante.value = '*';
        adotante_input.value = '*';
        dataAdocao.value = '1111-11-11';
      })


      (()=>{
        adotadoS.disabled = true;
        adotadoN.disabled = true;
        nomeAdotante.disabled = true;
        dataAdocao.disabled = true;
        salvarAdocao.disabled = true;
        adotante_input.disabled = true;
      })();
      

     
      let dadoRG = '';
      rgAdotante.addEventListener("input", ()=>{
        let rg = [];
        let rg2 = '';
        dadoRG = rgAdotante.value.split('');

        dadoRG.forEach( (valor, i) =>{
          if( valor >= 0 && valor <= 9){
            rg[i] = valor;
          }
        })

        rg.forEach( valor => {
          if( rg2.length == 2 ){
            rg2 += '.'+valor; 
          }else if(rg2.length == 6 ){
            rg2 += '.'+valor;
          }else if(rg2.length == 10){
            rg2 += '-'+valor;
          }else if(rg2.length >= 12){
            rg2 += '';
          }else{
            rg2 += valor;
          }
        })
        rgAdotante.value = rg2;

        if(rgAdotante.value.length == 12){
          fetchRG = rgAdotante.value;
          fetch('../../controle/pet/RGControle.php',{
            method:'POST',
            body: JSON.stringify({'rg': fetchRG })
          }).then( 
            resp => {return resp.json()} 
          ).then( 
            resp => {nomeAdotante.value = resp}
          )
        }else{
          nomeAdotante.value = '';
        }
      });

      (()=>{
        let id = window.location.href;
        id = id.split("=");
        id = id[1];
        fetch('../../controle/pet/ControleObterAdotante.php', {
          method:"POST",
          body:JSON.stringify({id})
        })
        .then( resp =>{
          return resp.json();
        })
        .then( resp => {
          if(resp.adotado){
            adotadoS.checked = true;
            dadosAdocao.style.display = '';
            nomeAdotante.value = resp.nome;
            rgAdotante.value = resp.rg;
            dataAdocao.value = resp.data_adocao;
          }
        })
      })();
            
      //Arquivo
      function excluirArquivo(dado){
        let trId = document.querySelector("#tr"+dado);
        let arkivo = document.querySelector("#ark"+dado).innerHTML;
        let response = window.confirm('Deseja realmente excluir o arquivo "' + arkivo + '"?');
        
        if(response === true){
            fetch('../../controle/pet/PetExameControle.php', {
            method: 'POST',
            body: JSON.stringify({"idExamePet":dado, "metodo":"excluir"})
          }).then(
            (resp) =>{ return resp.json() }
          ).then(
            (resp) =>{
              alert(resp);
              trId.remove();
            }
          )
        }
      } 
     
  async function addTipoExame() {
  const url = '../../dao/pet/adicionar_tipo_exame.php';
  let tipoExame = window.prompt("Cadastre um novo tipo de exame:");
  
  if (!tipoExame) {
    return;
  }
  
  tipoExame = tipoExame.trim();
  if (tipoExame === '') {
    return;
  }

  const data = new URLSearchParams({ tipo_exame: tipoExame });

  try {
    const response = await fetch(url, {
      method: 'POST',
      body: data,
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    });

    if (!response.ok) {
      throw new Error('Network response was not ok');
    }

    const responseText = await response.text();
    gerarTipoExamePet(responseText);
  } catch (error) {
    console.error('Error adding tipo exame:', error);
  }
}

      function gerarTipoExamePet(response)
      {
        var tipoExame = JSON.parse(response);
        $('#tipoExame').empty();
        $('#tipoExame').append('<option selected disabled>Selecionar...</option>');
        $.each(tipoExame, function(i, item) 
        {
          $('#tipoExame').append('<option value="' + item.id_tipo_exame + '">' + item.descricao_exame + '</option>');
        });
      }
      //Funções que fazem a impressão
      $(function(){
        $("#btnPrint").click(function () {
          $(".print").printThis();
        }); 
        $("#btnPrint2").click(function () {
          $(".tab-content").printThis({
            loadCSS: "../../assets/stylesheets/print.css"
          });
        }); 
      });

      //fichaMedica==================================================
      let castradoS = document.querySelector("#castradoS");
      let vacinadoS = document.querySelector("#vacinadoS");
      let vermifugadoS = document.querySelector("#vermifugadoS");
      let informacoes = document.querySelector("#despacho");
      let salvarFichaMedica = document.querySelector("#salvarFichaMedica");
      let editarFichaMedica = document.querySelector("#editarFichaMedica");
      let dVacinado = document.querySelector("#dVacinado");
      let dVermifugado = document.querySelector("#dVermifugado");
      let divVermifugado = document.querySelector("#div_vermifugado");
      let divVacinado = document.querySelector("#div_vacinado");
      let id_ficha_medica = document.querySelector("#id_ficha_medica");

      //let editor = CKEDITOR.replace('despacho');
      
      let dadoId = window.location + '';
      dadoId = dadoId.split('=');
      let id = dadoId[1];
      let dado = { 
        'id': id,
        'metodo': 'getFichaMedicaPet'
      };

      fetch("../../controle/pet/controleGetPet.php",{
        method: "POST",
        body: JSON.stringify(dado)
      }).then( resp => {
        return resp.json()
      }).then( resp => {
        if(resp[0].castrado == 's' || resp[0].castrado == 'S'){
          castradoS.checked = true;
        }
        
        id_ficha_medica.value = resp[0].id_ficha_medica;

         if(resp[0].necessidades_especiais){
           console.log(resp[0].necessidades_especiais);
           let infoPet = resp[0].necessidades_especiais;
           infoPet = infoPet.replace("<p>", '');
           infoPet = infoPet.replace("</p>", '');

          informacoes.value = infoPet;
         }
        
        if( resp[1].id_vacinacao){
          vacinadoS.checked = true;
          dVacinado.value = resp[1].data_vacinacao;
        }else{
          divVacinado.innerHTML = '';
        }
        
        if( resp[2].id_vermifugacao){
          vermifugadoS.checked = true;
          dVermifugado.value = resp[2].data_vermifugacao;
        }else{
          divVermifugado.innerHTML = '';
        }
      });

      vacinadoS.addEventListener('click', ()=>{
        divVacinado.innerHTML = `<label class="col-md-3 control-label" for="dVacinado">Data de Vacinação:<sup class="obrig">*</sup></label>
                                 <div class="col-md-8">
                                   <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="dVacinado" id="dVacinado" max=<?php echo date('Y-m-d');?> required>
                                 </div>`;
      })

      vacinadoN.addEventListener('click', ()=>{
        divVacinado.innerHTML = '';
      })

      vermifugadoS.addEventListener('click', ()=>{
        divVermifugado.innerHTML = `<label class="col-md-3 control-label" for="dataVermifugado">Data de Vermifugação:<sup class="obrig">*</sup></label>
                                    <div class="col-md-8">
                                      <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="dVermifugado" id="dVermifugado" max=<?php echo date('Y-m-d');?> required>
                                    </div>`;
      })

      vermifugadoN.addEventListener('click', ()=>{
        divVermifugado.innerHTML = ``;
      })
      
      vacinadoS.disabled = true;
      vermifugadoS.disabled = true;
      castradoS.disabled = true;
      vacinadoN.disabled = true;
      vermifugadoN.disabled = true;
      castradoN.disabled = true;
      salvarFichaMedica.disabled = true;
      dVermifugado.disabled = true;        
      dVacinado.disabled = true;   
      informacoes.disabled = true;  
      
      //verificar se possui ficha medica=============================
      dado = {
        'id_pet': id,
        'metodo': 'fichaMedicaPetExiste'
      };

      fetch('../../controle/pet/controleGetPet.php', {
        method: 'POST',
        body: JSON.stringify(dado)
      }).then(
        resp => { return resp.json();}
      ).then(
        resp => {
          if(resp.total != 1){
            corpo = `
            <p>É necessário que o animal possua uma ficha médica para poder usar esta aba!</p>
            <a href="./cadastro_ficha_medica_pet.php?id_pet=${id}">
              <input class="btn btn-primary" type="button" value="Cadastrar Ficha médica">
            </a>
            `;
            document.querySelector("#divFichaMedica").innerHTML = corpo;
            document.querySelector("#divAtendimento").innerHTML = corpo;
            document.querySelector("#divMedicamento").innerHTML = corpo;
          }
        }
      )

      //Atualizar Ficha Medica
      vacinadoS.addEventListener('click', ()=>{
        divVacinado.style.display = '';
      })      
      vermifugadoS.addEventListener('click', ()=>{
        divVermifugado.style.display = '';
      })    
      
      vacinadoN.addEventListener('click', ()=>{
        divVacinado.style.display = 'none';
      })      
      vermifugadoN.addEventListener('click', ()=>{
        divVermifugado.style.display = 'none';
      })    
      

      editarFichaMedica.addEventListener('click', ()=>{        
        if( editarFichaMedica.innerHTML != "Cancelar"){

          $(editarFichaMedica).html('Cancelar').removeClass('btn-secondary').addClass('btn-danger');       
          vacinadoS.disabled = false;
          vermifugadoS.disabled = false;
          castradoS.disabled = false;
          vacinadoN.disabled = false;
          vermifugadoN.disabled = false;
          castradoN.disabled = false;  
          salvarFichaMedica.disabled = false;
          dVacinado.disabled = false;
          dVermifugado.disabled = false;
          informacoes.disabled = false;
        }else{
          location.reload();
        }
      })

      //Atendimento
      let dataAtendimento = document.querySelector("#dataAtendimento");
      let descricaoAtendimento = document.querySelector("#descricaoAtendimento");
      let selectMedicamento = document.querySelector("#selectMedicamento");
      let prescreverMedicacao = document.querySelector("#prescreverMedicacao");
      let depTab = document.querySelector("#dep-tab");
      let medics = document.querySelector("#medics");

      window.addEventListener("load", ()=>{
        fetch("../../controle/pet/controleGetMedicamento.php").then(
          resp=>{
            return resp.json();
          }
        ).then(
          resp=>{
            let corpo;
            resp.forEach( (valor)=>{
              corpo +=`<option value='${valor.id_medicamento}?${valor.nome_medicamento}' >${valor.nome_medicamento}</option>`;
            })
            selectMedicamento.innerHTML += corpo;
          }
        )
      })

      prescreverMedicacao.addEventListener("click", ()=>{
        let dadoMed = selectMedicamento.value;
        dadoMed = dadoMed.split("?");

        let array = medics.value.split("?");
        let vrfcr = array.find( (valor) => dadoMed[0] == valor);

        if( selectMedicamento.value != "Selecione" && dadoMed[0] != vrfcr ){
          depTab.innerHTML += `<tr id="dadoMed${dadoMed[0]}" class="tabmed">
            <td>${dadoMed[1]}</td>
            <td style="display: flex; justify-content: space-evenly;">
            <button  id="bMed" class="btn btn-danger dadoMed${dadoMed[0]}">
              <i class="fas fa-trash-alt"></i>
            </button>
            </td>
          </tr>
        `;

          // a terminar
          medics.value += `${dadoMed[0]}?`;

          document.querySelectorAll("#bMed").forEach( valor => {
              valor.addEventListener("click", (e)=>{
                let idClass = valor.classList+'';
                idClass = idClass.split(" ");
                idClass = idClass[(idClass.length) - 1];
                document.querySelector(`#${idClass}`).remove();
                
                let vrfcr = idClass.replace("dadoMed", '');
                console.log(vrfcr);
                medics.value = medics.value.replace(vrfcr+"?", '');
                console.log(medics.value);
                
                e.preventDefault();
              })
          })
        }else{
          alert("Você não selecionou um medicamento ou já selecionou este!");
        }
        console.log(medics.value);
      })

      //Fim Atendimento
      //historico_medico
      let tabHist = document.querySelector("#tab_historico");
      let tabAtendimento = document.querySelector("#tab_historico");
      let id_pet = window.location+'';
      id_pet = id_pet.split("=");
      
      fetch("../../controle/pet/ControleHistorico.php",{
        method: 'POST',
        body: JSON.stringify({
          'metodo': "getHistoricoPet",
          'id_pet': id_pet[1] 
        })
      }).then(
        resp=>{
          return resp.json();
        }
      ).then(
        resp=>{
          let atendimento = resp;
          console.log(resp);
          atendimento.forEach( valor =>{
            let data = valor['data_atendimento'].split('-');
            tabAtendimento.innerHTML += `
              <tr>
                <td>${data[2]}-${data[1]}-${data[0]}</td>
                <td>${valor['descricao']}</td>
                <td style="display: flex; justify-content: space-evenly;">
                  <a href="./historico_pet.php?id_historico=${valor['id_pet_atendimento']}" title="vizualizar">
                    <button class="btn btn-primary" id="teste">
                      <i class="fa fa-arrow-up-right-from-square"></i>
                    </button>
                  </a>
                </td>
              </tr>
            `;
          })

          let td = document.querySelectorAll("td");
          let th = document.querySelectorAll("th");
          td.forEach( al =>{
            al.style.textAlign = "center";
          })
          th.forEach( ah =>{
            ah.style.textAlign = "center";
          })
        }
      )      
      //fim historico_medico

      //=============================================================
    </script>
    <!-- Vendor -->
    <script src="../../assets/vendor/select2/select2.js"></script>
    <script src="../../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
    <script src="../../assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
    <script src="../../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
    <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
    <!-- Theme Base, Components and Settings -->
    <script src="../../assets/javascripts/theme.js"></script>
    <!-- Theme Custom -->
    <script src="../../assets/javascripts/theme.custom.js"></script>
    <!-- Metodo Post -->
    <script src="../geral/post.js"></script>
    <!-- Theme Initialization Files -->
    <script src="../../assets/javascripts/theme.init.js"></script>
    <!-- Examples -->
    <script src="../../assets/javascripts/tables/examples.datatables.default.js"></script>
    <script src="../../assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
    <script src="../../assets/javascripts/tables/examples.datatables.tabletools.js"></script>

    <!-- JavaScript Custom -->
    <script src="../geral/post.js"></script>
    <script src="../geral/formulario.js"></script>
    <script>
      var formState = [];
      function switchButton(idForm) 
      {
        if (!formState[idForm]) 
        {
          $("#botaoEditar_" + idForm).text("Editar").prop("class", "btn btn-primary");
        } 
        else 
        {
          $("#botaoEditar_" + idForm).text("Cancelar").prop("class", "btn btn-danger");
        }
      }
      function switchForm(idForm, setState = null) 
      {
        if (setState !== null) 
        {
          formState[idForm] = !setState;
        }
        if (formState[idForm]) 
        {
          formState[idForm] = false;
          disableForm(idForm);
        } 
        else 
        {
          formState[idForm] = true;
          enableForm(idForm);
        }
        switchButton(idForm);
      }
      //switchForm("editar_cargaHoraria", false)
    </script>
    <div align="right">
	  <iframe src="https://www.wegia.org/software/footer/pet.html" width="200" height="60" style="border:none;"></iframe>
    </div>
  </body>
</html>
