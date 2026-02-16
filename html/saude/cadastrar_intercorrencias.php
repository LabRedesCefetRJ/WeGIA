<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
extract($_REQUEST);

if (session_status() === PHP_SESSION_NONE)
  session_start();

if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
  exit();
}

if (!isset($_SESSION['id_fichamedica'])) {
  header('Location: ../../controle/control.php?metodo=listarUm&nomeClasse=SaudeControle&nextPage=../html/saude/cadastrar_intercorrencias.php');
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

require_once "./verifica_permissao_saude.php";

include_once '../../classes/Cache.php';
require_once "../personalizacao_display.php";

require_once ROOT . "/controle/SaudeControle.php";

if (!is_numeric($_GET['id_fichamedica']) || $_GET['id_fichamedica'] < 1) {
  header("Location: ../home.php?msg_c=O parâmetro informado é incorreto, informe um inteiro positivo maior ou igual a 1.");
  exit();
}

$id = $_GET['id_fichamedica'];
$cache = new Cache();
$teste = $cache->read($id);
$_SESSION['id_upload_med'] = $id;
require_once "../../dao/Conexao.php";
$pdo = Conexao::connect();

if (!isset($teste)) {
  header('Location: ../../controle/control.php?metodo=listarUm&nomeClasse=SaudeControle&nextPage=../html/saude/cadastrar_intercorrencias.php?id_fichamedica=' . $id . '&id=' . $id);
}

$teste = $pdo->query("SELECT nome, f.id_funcionario FROM pessoa p JOIN funcionario f ON(p.id_pessoa = f.id_pessoa) WHERE f.id_pessoa = " . $_SESSION['id_pessoa'])->fetchAll(PDO::FETCH_ASSOC);
$id_funcionario = $teste[0]['nome'];
$funcionario_id = $teste[0]['id_funcionario'];

$sqlPaciente = "SELECT id_pessoa FROM saude_fichamedica WHERE id_fichamedica =:idFichaMedica";

$stmtPaciente = $pdo->prepare($sqlPaciente);

$stmtPaciente->bindValue(':idFichaMedica', $_GET['id_fichamedica']);

if (!$stmtPaciente->execute()) {
  http_response_code(500);
  echo json_encode(['erro' => 'Erro ao buscar paciente']);
  exit();
}

$idPaciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);

?>
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
<!-- <script src="<?php echo WWW; ?>assets/javascripts/theme.js"></script> -->

<!-- Theme Custom -->
<script src="<?php echo WWW; ?>assets/javascripts/theme.custom.js"></script>

<!-- Theme Initialization Files -->
<script src="<?php echo WWW; ?>assets/javascripts/theme.init.js"></script>

<!-- javascript functions -->
<script src="<?php echo WWW; ?>Functions/onlyNumbers.js"></script>
<script src="<?php echo WWW; ?>Functions/onlyChars.js"></script>
<script src="<?php echo WWW; ?>Functions/mascara.js"></script>

<!-- jkeditor -->
<script src="<?php echo WWW; ?>assets/vendor/ckeditor/ckeditor.js"></script>

<!-- Specific Page Vendor CSS -->
<link rel="stylesheet" href="../../assets/vendor/select2/select2.css" />
<link rel="stylesheet" href="../../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />


<script>
  $(function() {

    $("#header").load("../header.php");
    $(".menuu").load("../menu.php");
  });
</script>

<style type="text/css">
  .select {
    position: absolute;
    width: 235px;
  }

  .select-table-filter {
    width: 140px;
    float: left;
  }

  .panel-body {
    margin-bottom: 15px;
  }

  #div_texto {
    width: 100%;
  }

  #cke_despacho {
    height: 500px;
  }

  .cke_inner {
    height: 500px;
  }

  #cke_1_contents {
    height: 455px !important;
  }

  .col-md-3 {
    width: 10%;
  }
</style>


<!doctype html>
<html class="fixed">

<head>
  <!-- Basic -->
  <meta charset="UTF-8">
  <title>Intercorrências</title>
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <!-- Web Fonts  -->
  <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
  <!-- Vendor CSS -->
  <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
  <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
  <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
  <link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />

  <link rel="stylesheet" type="text/css" href="../../css/profile-theme.css">
  </script>
  <script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
  <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
  <script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
  <script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
  <script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
  <!-- Theme CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
  <!-- Skin CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
  <!-- Theme Custom CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
  <!-- Head Libs -->
  <script src="../../assets/vendor/modernizr/modernizr.js"></script>
  <script src="../../Functions/lista.js"></script>
  <!-- JavaScript Functions -->
  <script src="../../Functions/enviar_dados.js"></script>
  <script src="../../Functions/mascara.js"></script>
  <script src="../../Functions/onlyNumbers.js"></script>
  <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

  <script>
    $(function() {
      var interno = <?php echo $_SESSION['id_fichamedica']; ?>;
      $.each(interno, function(i, item) {
        if (i = 1) {
          if (item.imagem != "" && item.imagem != null) {
            $("#imagem").attr("src", "data:image/gif;base64," + item.imagem);
          } else {
            $("#imagem").attr("src", "../../img/semfoto.png");
          }
        }
      });
    });

    function carregarIntercorrencias() {
      let id = <?php echo $_GET['id_fichamedica']; ?>;
      const url = `../../controle/control.php?nomeClasse=${encodeURIComponent("AvisoControle")}&metodo=${encodeURIComponent("listarIntercorrenciaPorIdDaFichaMedica")}&id_fichamedica=${encodeURIComponent(id)}`;
      fetch(url)
        .then(res => res.json())
        .then(intercorrencias => {
          const tbody = document.getElementById("doc-tab-intercorrencias");

          while (tbody.firstChild) {
            tbody.removeChild(tbody.firstChild)
          }

          intercorrencias.forEach(item => {
            const tr = document.createElement("tr");

            const td1 = document.createElement("td");
            td1.textContent = item.descricao;

            const td2 = document.createElement("td");
            td2.textContent = item.data;

            tr.append(td1, td2);
            tbody.appendChild(tr);
          });
        })
        .catch(err => {
          console.error("Erro ao carregar aplicações:", err);
        });
    }
  </script>

  <style type="text/css">
    .obrig {
      color: rgb(255, 0, 0);
    }

    #btn-cadastrar-emergencia {
      margin-top: 10px;
    }

    .custom-input {
      color: #555555;
      background-color: #fff;
      background-image: none;
      border: 1px solid #ccc;
      border-radius: 4px;
      -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
      box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
      -webkit-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
      -o-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
      -webkit-transition: border-color ease-in-out .15s, -webkit-box-shadow ease-in-out .15s;
      transition: border-color ease-in-out .15s, -webkit-box-shadow ease-in-out .15s;
      transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
      transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s, -webkit-box-shadow ease-in-out .15s;
    }

    .custom-input:focus {
      border-color: #86b7fe;
      box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    table td,
    table th {
      word-wrap: break-word;
      white-space: normal;
    }

    table {
      table-layout: fixed;
      width: 100%;
    }
  </style>

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
          <h2>Intercorrências</h2>
          <div class="right-wrapper pull-right">
            <ol class="breadcrumbs">
              <li>
                <a href="../index.php">
                  <i class="fa fa-home"></i>
                </a>
              </li>
              <li><span>Intercorrências</span></li>
            </ol>
            <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
          </div>
        </header>

        <!-- start: page -->
        <div class="row">
          <div class="col-md-4 col-lg-3">
            <section class="panel">
              <div class="panel-body">
                <div class="thumb-info mb-md">
                  <img id="imagem" alt="John Doe">
                </div>
              </div>
            </section>
          </div>

          <div class="col-md-9 col-lg-9">
            <div class="tabs">
              <ul class="nav nav-tabs tabs-primary">
                <li id="tab1" class="active">
                  <a href="#cadastro_emergencia" data-toggle="tab">Informar Intercorrência</a>
                </li>
              </ul>

              <div class="tab-content">
                <div id="cadastro_emergencia" class="tab-pane active in">
                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Cadastro de intercorrências</h2>
                    </header>
                    <div class="panel-body">
                      <form method="post" action="../../controle/control.php">
                        <input type="hidden" name="nomeClasse" value="AvisoControle">
                        <input type="hidden" name="metodo" value="incluir">
                        <input type="hidden" name="idpaciente" value="<?php echo $idPaciente['id_pessoa']; ?>">
                        <input type="hidden" name="idfuncionario" value="<?php echo $funcionario_id; ?>">
                        <input type="hidden" name="idfichamedica" value="<?php echo $id; ?>">

                        <div class="form-group">
                          <label for="descricao_emergencia">Descrição da Intercorrência</label>
                          <textarea class="form-control" id="descricao_emergencia" name="descricao_emergencia" cols="30" rows="10" placeholder="Insira aqui a descrição do ocorrido..." required></textarea>
                        </div>

                        <input type="submit" id="btn-cadastrar-emergencia" class="btn btn-primary" value="Cadastrar">
                      </form>

                      <hr class="dotted short">

                      <div class="form-group" id="exibirintercorrencias">
                        <table class="table table-bordered table-striped" id="datatable-intercorrencias">
                          <thead>
                            <tr style="font-size:15px;">
                              <th>Descrição</th>
                              <th>Data</th>
                            </tr>
                          </thead>
                          <tbody id="doc-tab-intercorrencias">
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </section>
                </div>
              </div> <!-- end tab-content -->
            </div> <!-- end tabs -->
          </div> <!-- end col -->
        </div> <!-- end row -->
      </section> <!-- end content-body -->
    </div> <!-- end inner-wrapper -->

    <!-- Rodapé -->
    <div align="right">
      <iframe src="https://www.wegia.org/software/footer/saude.html" width="200" height="60" style="border:none;"></iframe>
    </div>
  </section>

  <script>
    carregarIntercorrencias();
  </script>
  <script>
    (function() {
      const idFichaMedica = <?php echo (int)$id; ?>;
      const idFuncionario = <?php echo (int)$funcionario_id; ?>;
      const idPaciente = <?php echo isset($idPaciente['id_pessoa']) ? (int)$idPaciente['id_pessoa'] : 0; ?>;
      const textarea = document.getElementById('descricao_emergencia');
      const storageKey = `intercorrencia_rascunho:${idFuncionario}:${idPaciente}:${idFichaMedica}`;
      let autosaveTimer = null;
      let lastSavedValue = '';

      function carregarRascunho() {
        try {
          const descricao = localStorage.getItem(storageKey);
          if (typeof descricao === 'string') {
            textarea.value = descricao;
            lastSavedValue = descricao;
          }
        } catch (err) {
          console.error('Erro ao carregar rascunho do localStorage:', err);
        }
      }

      function salvarOuLimparRascunho() {
        const descricao = textarea.value;
        if (descricao === lastSavedValue) {
          return;
        }

        try {
          if (descricao.trim() === '') {
            localStorage.removeItem(storageKey);
            lastSavedValue = '';
            return;
          }

          localStorage.setItem(storageKey, descricao);
          lastSavedValue = descricao;
        } catch (err) {
          console.error('Erro ao salvar rascunho no localStorage:', err);
        }
      }

      function agendarAutosave() {
        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(salvarOuLimparRascunho, 5000);
      }

      if (textarea) {
        textarea.addEventListener('input', agendarAutosave);

        const form = textarea.closest('form');
        if (form) {
          form.addEventListener('submit', function() {
            clearTimeout(autosaveTimer);
            try {
              localStorage.removeItem(storageKey);
            } catch (err) {
              console.error('Erro ao limpar rascunho do localStorage no submit:', err);
            }
          });
        }

        document.addEventListener('visibilitychange', function() {
          if (document.visibilityState === 'hidden') {
            salvarOuLimparRascunho();
          }
        });
        window.addEventListener('pagehide', salvarOuLimparRascunho);
        window.addEventListener('beforeunload', salvarOuLimparRascunho);

        carregarRascunho();
      }
    })();
  </script>
  <!-- Scripts únicos e organizados -->
  <script src="<?php echo WWW; ?>assets/vendor/select2/select2.js"></script>
  <script src="<?php echo WWW; ?>assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
  <script src="<?php echo WWW; ?>assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
  <script src="<?php echo WWW; ?>assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>

  <script src="<?php echo WWW; ?>assets/javascripts/theme.custom.js"></script>
  <script src="<?php echo WWW; ?>assets/javascripts/theme.js"></script>
  <script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.default.js"></script>
  <script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
  <script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.tabletools.js"></script>

  <!-- Importante para o funcionamento dos formulários -->
  <script src="../geral/post.js"></script>
  <script src="../geral/formulario.js"></script>
</body>

</html>
