<?php
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

permissao($_SESSION['id_pessoa'], 23, 5);
// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";

include_once ROOT . '/dao/Conexao.php';
include_once ROOT . '/dao/OrigemDAO.php';

$pdo = Conexao::connect();

$stmtAlmoxarifados = $pdo->query("
   SELECT id_almoxarifado, descricao_almoxarifado
   FROM almoxarifado
   WHERE ativo = 1
   ORDER BY descricao_almoxarifado
");

$almoxarifados = json_encode($stmtAlmoxarifados->fetchAll(PDO::FETCH_ASSOC));

if (!isset($_SESSION['origem'])) {
   header('Location: ' . WWW . 'controle/control.php?metodo=listarTodos&nomeClasse=OrigemControle&nextPage=' . WWW . 'html/matPat/listar_origem.php');
} else {
   $origem = $_SESSION['origem'];
   unset($_SESSION['origem']);
}
?>
<!doctype html>
<html class="fixed">

<head>
   <!-- Basic -->
   <meta charset="UTF-8">
   <title>Informações</title>
   <!-- Mobile Metas -->
   <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
   <!-- Vendor CSS -->
   <link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap/css/bootstrap.css" />
   <link rel="stylesheet" href="<?= WWW ?>assets/vendor/font-awesome/css/font-awesome.css" />
   <link rel="stylesheet" href="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.css" />
   <link rel="stylesheet" href="<?= WWW ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
   <!-- Specific Page Vendor CSS -->
   <link rel="stylesheet" href="<?= WWW ?>assets/vendor/select2/select2.css" />
   <link rel="stylesheet" href="<?= WWW ?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
   <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">
   <!-- Theme CSS -->
   <link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme.css" />
   <!-- Skin CSS -->
   <link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/skins/default.css" />
   <!-- Theme Custom CSS -->
   <link rel="stylesheet" href="<?= WWW ?>assets/stylesheets/theme-custom.css">
   <!-- Head Libs -->
   <script src="<?= WWW ?>assets/vendor/modernizr/modernizr.js"></script>
   <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
   <!-- Vendor -->
   <script src="<?= WWW ?>assets/vendor/jquery/jquery.min.js"></script>
   <script src="<?= WWW ?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
   <script src="<?= WWW ?>assets/vendor/bootstrap/js/bootstrap.js"></script>
   <script src="<?= WWW ?>assets/vendor/nanoscroller/nanoscroller.js"></script>
   <script src="<?= WWW ?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
   <script src="<?= WWW ?>assets/vendor/magnific-popup/magnific-popup.js"></script>
   <script src="<?= WWW ?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
   <!-- javascript functions -->
   <script src="<?= WWW ?>Functions/onlyNumbers.js"></script>
   <script src="<?= WWW ?>Functions/onlyChars.js"></script>
   <script src="<?= WWW ?>Functions/enviar_dados.js"></script>
   <script src="<?= WWW ?>Functions/mascara.js"></script>
   <!-- jquery functions -->
   <script>
      function excluir(id) {
         window.location.replace('<?= WWW ?>controle/control.php?metodo=excluir&nomeClasse=OrigemControle&id_origem=' + id);
      }
   </script>
   <script>
      var almoxarifados = <?php echo $almoxarifados; ?>;
      var origens = <?php echo $origem; ?>;

      function excluir(id) {
         window.location.replace('<?= WWW ?>controle/control.php?metodo=excluir&nomeClasse=OrigemControle&id_origem=' + id);
      }

      function abrirModalEditarOrigem(index) {
         var origem = origens[index];

         $('#edit_id_origem').val(origem.id_origem);
         $('#edit_nome').val(origem.nome_origem || '');
         $('#edit_cnpj').val(origem.cnpj || '');
         $('#edit_cpf').val(origem.cpf || '');
         $('#edit_telefone').val(origem.telefone || '');

         $('#edit_almoxarifados').empty();

         var almoxarifadosOrigem = origem.almoxarifados || [];

         $.each(almoxarifados, function(i, almoxarifado) {
            var marcado = almoxarifadosOrigem.includes(String(almoxarifado.id_almoxarifado)) ? 'checked' : '';

            $('#edit_almoxarifados').append(
               '<div class="checkbox">' +
                  '<label>' +
                     '<input type="checkbox" name="almoxarifados[]" value="' + almoxarifado.id_almoxarifado + '" ' + marcado + '> ' +
                     almoxarifado.descricao_almoxarifado +
                  '</label>' +
               '</div>'
            );
         });

         $('#modalEditarOrigem').modal('show');
      }

      $(function() {
         $.each(origens, function(i, item) {
            $('#tabela')
               .append($('<tr />')
                  .append($('<td />').text(item.nome_origem || ''))
                  .append($('<td />').text(item.cnpj || ''))
                  .append($('<td />').text(item.cpf || ''))
                  .append($('<td />').text(item.telefone || ''))
                  .append($('<td />')
                     .html(
                        '<i class="fas fa-edit" style="cursor:pointer; margin-right:10px;" onclick="abrirModalEditarOrigem(' + i + ')"></i>' +
                        '<i class="fas fa-trash-alt" style="cursor:pointer;" onclick="excluir(' + item.id_origem + ')"></i>'
                     )
                  )
               );
         });

         $("#header").load("<?= WWW ?>html/header.php");
         $(".menuu").load("<?= WWW ?>html/menu.php");
      });
</script>
</head>

<body>
   <section class="body">
      <!-- start: header -->
      <div id="header"></div>
      <!-- end: header -->
      <div class="inner-wrapper">
         <!-- start: sidebar -->
         <aside id="sidebar-left" class="sidebar-left menuu"></aside>
         <!-- end: sidebar -->
         <section role="main" class="content-body">
            <header class="page-header">
               <h2>Informações</h2>
               <div class="right-wrapper pull-right">
                  <ol class="breadcrumbs">
                     <li>
                        <a href="<?= WWW ?>html/home.php">
                           <i class="fa fa-home"></i>
                        </a>
                     </li>
                     <li><span>Informações Origem</span></li>
                  </ol>
                  <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
               </div>
            </header>
            <!-- start: page -->
            <section class="panel">
               <header class="panel-heading">
                  <div class="panel-actions">
                     <a href="#" class="fa fa-caret-down"></a>
                  </div>
                  <h2 class="panel-title">Origem</h2>
               </header>
               <div class="panel-body">
                  <table class="table table-bordered table-striped mb-none" id="datatable-default">
                     <thead>
                        <tr>
                           <th>Pessoa/Empresa</th>
                           <th>CNPJ</th>
                           <th>CPF</th>
                           <th>Telefone</th>
                           <th>Ação</th>
                        </tr>
                     </thead>
                     <tbody id="tabela">
                     </tbody>
                  </table>
               </div>
               <br>
            </section>
         </section>

         <div class="modal fade" id="modalEditarOrigem" tabindex="-1" role="dialog" aria-labelledby="modalEditarOrigemLabel">
            <div class="modal-dialog" role="document">
               <div class="modal-content">
                  <form method="post" action="<?= WWW ?>controle/control.php">
                     <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                           <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="modalEditarOrigemLabel">Editar origem</h4>
                     </div>

                     <div class="modal-body">
                        <input type="hidden" name="nomeClasse" value="OrigemControle">
                        <input type="hidden" name="metodo" value="alterar">
                        <input type="hidden" name="id_origem" id="edit_id_origem">

                        <div class="form-group">
                           <label>Nome</label>
                           <input type="text" class="form-control" name="nome" id="edit_nome" required>
                        </div>

                        <div class="form-group">
                           <label>CNPJ</label>
                           <input type="text" class="form-control" name="cnpj" id="edit_cnpj">
                        </div>

                        <div class="form-group">
                           <label>CPF</label>
                           <input type="text" class="form-control" name="cpf" id="edit_cpf">
                        </div>

                        <div class="form-group">
                           <label>Telefone</label>
                           <input type="text" class="form-control" name="telefone" id="edit_telefone">
                        </div>

                        <div class="form-group">
                           <label>Almoxarifados relacionados</label>
                           <div id="edit_almoxarifados"></div>
                        </div>
                     </div>

                     <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar alterações</button>
                     </div>
                  </form>
               </div>
            </div>
         </div>

         <!-- end: page -->
         <!-- Specific Page Vendor -->
         <script src="<?= WWW ?>assets/vendor/select2/select2.js"></script>
         <script src="<?= WWW ?>assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
         <script src="<?= WWW ?>assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
         <script src="<?= WWW ?>assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
         <!-- Theme Base, Components and Settings -->
         <script src="<?= WWW ?>assets/javascripts/theme.js"></script>
         <!-- Theme Custom -->
         <script src="<?= WWW ?>assets/javascripts/theme.custom.js"></script>
         <!-- Theme Initialization Files -->
         <script src="<?= WWW ?>assets/javascripts/theme.init.js"></script>
         <!-- Examples -->
         <script src="<?= WWW ?>assets/javascripts/tables/examples.datatables.default.js"></script>
         <script src="<?= WWW ?>assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
         <script src="<?= WWW ?>assets/javascripts/tables/examples.datatables.tabletools.js"></script>
         <div align="right">
            <iframe src="https://www.wegia.org/software/footer/matPat.html" width="200" height="60" style="border:none;"></iframe>
         </div>
</body>

</html>