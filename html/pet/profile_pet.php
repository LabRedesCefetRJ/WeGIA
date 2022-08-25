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
    header('Location: ../../controle/control.php?modulo=pet&metodo=listarUm&nomeClasse=PetControle&nextPage=../html/pet/profile_pet.php?id_pet=' . $id_pet . '&id_pet=' . $id_pet);
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
  permissao($_SESSION['id_pessoa'], 11, 7);
  require_once "../personalizacao_display.php";
  require_once "../../dao/Conexao.php";
  require_once "../geral/msg.php";
 
  
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
        $("#editarPet").html('Cancelar');
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
          $("#editarPet").html('Editar');
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
            $("#reservista1").show();
            $("#reservista2").show();
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
          <div class="row">
            <div class="col-md-4 col-lg-3">
              <section class="panel">
                <div class="panel-body">
                  <div class="thumb-info mb-md">
                    <?php
                      $conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                      $id_pessoa = $_SESSION['id_pessoa'];
                      $donoimagem = $_GET['id_pet'];
                      $resultado = mysqli_query($conexao, "SELECT p.id_pet_foto AS id_foto, pf.arquivo_foto_pet AS 'imagem' FROM pet p, pet_foto pf WHERE p.id_pet_foto=pf.id_pet_foto and p.id_pet=$donoimagem");
                      $petImagem = mysqli_fetch_array($resultado);
                      if (isset($_SESSION['id_pessoa']) and !empty($_SESSION['id_pessoa'])) 
                      {
                        if($petImagem['imagem']){
                          $foto = $petImagem['imagem'];
                          $id_foto = $petImagem['id_foto'];
                          if ($foto != null and $foto != "")
                          {
                            $foto = 'data:image;base64,' . $foto;
                          }
                        }
                        else 
                        {
                          $foto = WWW . "img/semfoto.png";
                        }
                      }
                      echo "<img src='$foto' id='imagem' class='rounded img-responsive' alt='John Doe'>";
                    ?>
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
                              <input type="hidden" name="id_foto" value=<?php echo $id_foto ?>>
                              <input type="submit" id="formsubmit" value="Alterar imagem">
                            </div>
                          </div> 
                        </form>
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
                    <a href="#arquivosPet" data-toggle="tab">Exames do Pet</a>
                  </li>
                </ul>

                <div class="tab-content">
                  <!--Aba de Informações Pessoais-->
                  <div id="overview" class="tab-pane active">
                    <form class="form-horizontal" method="post" action="../../controle/control.php">
                      <input type="hidden" name="nomeClasse" value="PetControle">
                      <input type="hidden" name="metodo" value="alterarPetDados">
                      <input type="hidden" name="modulo" value="pet">
                      <h4 class="mb-xlg">Informações Pet</h4>
                      <fieldset>

                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileFirstName">Nome</label>
                          <div class="col-md-8">
                            <input type="text" class="form-control" name="nome" id="nomeForm" onkeypress="return Onlychars(event)" required>
                          </div>
                        </div>

                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileLastName">Sexo</label>
                          <div class="col-md-8">
                            <label><input type="radio" name="gender" id="radioM" id="M" value="M" style="margin-top: 10px; margin-left: 15px;" onclick="return exibir_reservista()"> <i class="fa fa-male" style="font-size: 20px;"> Masculino</i></label>
                            <label><input type="radio" name="gender" id="radioF" id="F" value="F" style="margin-top: 10px; margin-left: 15px;" onclick="return esconder_reservista()"> <i class="fa fa-female" style="font-size: 20px;"> Feminino</i> </label>
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
                          <label class="col-md-3 control-label" for="inputSuccess">Especie</label>
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
                          <label class="col-md-3 control-label" for="inputSuccess">Raca</label>
                          <div class="col-md-6">
                            <select class="form-control input-lg mb-md" name="raca" id="raca">
                              <?php
                                $raca = mysqli_query($conexao, "SELECT id_pet_raca AS id_raca, descricao AS 'raca' FROM pet_raca");
                                foreach ($raca as $valor) {
                                  echo "<option value=".$valor['id_raca']." >".$valor['raca']."</option>";
                                }
                              ?>
                            </select>
                          </div>
                        </div>

                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileFirstName">Características Específicas</label>
                          <div class="col-md-8">
                            <input type="text" class="form-control" name="especificas" id="especificas" >
                          </div>
                        </div>
                        </br>
                        <input type="hidden" name="id_pet" value=<?php echo $_GET['id_pet'] ?>>
                        <button type="button" class="btn btn-primary" id="editarPet" onclick="return editar_informacoes_pet()">Editar</button>
                        <input type="submit" class="btn btn-primary" disabled="true" value="Salvar" id="salvarPet">
                      </fieldset>
                    </form>
                    <br>
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
                            if($p['id_ficha_medica']){
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
                                        <!--<a href="data:application/pdf;base64,$valor[arquivo_exame]" title="Baixar" download="$valor[descricao_exame].$valor[arquivo_extensao]">-->
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
                          if($p['id_ficha_medica']){
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

                
              <!-- Aba de arquivos -->

                <!-- Aba endereço -->
                                
                <!-- end: page -->
              </div> 
            </div>
          </div>
        </section>
      </div>
    </section>
    <!--script pedro-->
    <script type="text/javascript">
      //============pedro

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
     
      function addTipoExame()
      {
        url = '../../dao/pet/adicionar_tipo_exame.php';
        var tipo_exame = window.prompt("Cadastre um novo tipo de exame:");
        if (!tipo_exame) 
        {
          return
        }
        tipo_exame = tipo_exame.trim();
        if (tipo_exame == '') 
        {
          return
        }
        data = 'tipo_exame=' + tipo_exame;
        $.ajax(
        {
          type: "POST",
          url: url,
          data: data,
          success: function(response) 
          {
            gerarTipoExamePet(response);
          },
          dataType: 'text'
        })
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
      switchForm("editar_cargaHoraria", false)
    </script>
    <div align="right">
	  <iframe src="https://www.wegia.org/software/footer/funcionario.html" width="200" height="60" style="border:none;"></iframe>
    </div>
  </body>
</html>
