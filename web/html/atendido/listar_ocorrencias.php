<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}else{
    session_regenerate_id();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$id_pessoa = $_SESSION['id_pessoa'];

$stmt = mysqli_prepare($conexao, "SELECT id_cargo FROM funcionario WHERE id_pessoa = ?");
mysqli_stmt_bind_param($stmt, 'i', $id_pessoa);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($resultado && mysqli_num_rows($resultado) > 0) {
    $row = mysqli_fetch_array($resultado);
    $id_cargo = $row['id_cargo'];

    $stmt = mysqli_prepare($conexao, "SELECT id_acao FROM permissao WHERE id_cargo = ? AND id_recurso = 12");
    mysqli_stmt_bind_param($stmt, 'i', $id_cargo);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $permissao = mysqli_fetch_array($resultado);
        if ($permissao['id_acao'] < 7) {
            $msg = "Você não tem as permissões necessárias para essa página.";
            header("Location: ../../home.php?msg_c=$msg");
            exit;
        }
        $permissao = $permissao['id_acao'];
    } else {
        $msg = "Você não tem as permissões necessárias para essa página.";
        header("Location: ../../home.php?msg_c=$msg");
        exit;
    }
} else {
    $msg = "Você não tem as permissões necessárias para essa página.";
    header("Location: ./home.php?msg_c=$msg");
    exit;
}


include_once '../../classes/Cache.php';
// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once "../personalizacao_display.php";
require_once "../../controle/Atendido_ocorrenciaControle.php";
require_once "../../controle/Atendido_ocorrenciaDocControle.php";
//   $pdo = Conexao::connect();
//   $nome = $pdo->query("SELECT a.idatendido, p.nome, p.sobrenome FROM pessoa p JOIN atendido a ON(p.id_pessoa=a.pessoa_id_pessoa)")->fetchAll(PDO::FETCH_ASSOC);
//   $tipo = $pdo->query("SELECT * FROM atendido_ocorrencia_tipos.descricao WHERE ")->fetchAll(PDO::FETCH_ASSOC);

// $sessao_ocorrencia = $_SESSION['atendido_ocorrencia'];
// var_dump($sessao_ocorrencia);
// $id_atendido = $sessao_ocorrencia.atendido_idatendido;
// // echo $sessao_ocorrencia['atendido_idatendido'];
// echo $id_atendido;



/*require_once ROOT."/controle/FuncionarioControle.php";
  $cpf1 = new FuncionarioControle;
  $cpf1->listarCpf();*/

// require_once ROOT."/controle/SaudeControle.php";
/*$cpf = new AtendidoControle;
  $cpf->listarCpf();*/

/*require_once ROOT."/controle/EnderecoControle.php";
  $endereco = new EnderecoControle;
  $endereco->listarInstituicao();*/


$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    echo json_encode(['erro' => 'O id da ocorrência informado está fora dos limites permitidos.']);
    exit();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Atendido_ocorrenciaDAO.php';
$atendidoOcorrenciaDao = new Atendido_ocorrenciaDAO();
$atendidoOcorrencia = $atendidoOcorrenciaDao->listar($id);

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

<!-- Theme CSS -->
<link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon">

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

<!-- jquery functions -->

<script>
    $(function() {
        // var funcionario=[];
        // $.each(funcionario,function(i,item){
        //     $("#destinatario")
        //         .append($("<option id="+item.id_pessoa+" value="+item.id_pessoa+" name="+item.id_pessoa+">"+item.nome+" "+item.sobrenome+"</option>"));
        // });
        $("#header").load("../header.php");
        $(".menuu").load("../menu.php");

        //var id_memorando = 1;
        //$("#id_memorando").val(id_memorando);

        CKEDITOR.replace('descricao');
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

    img {
        margin-left: 10px;
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

    #arquivos {
        padding-top: 5px;
    }
</style>


<!doctype html>
<html class="fixed">

<head>
    <!-- Basic -->
    <meta charset="UTF-8">
    <title>Informações </title>
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
    <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
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

    <script>
        function excluirimg(id) {
            $("#excluirimg").modal('show');
            $('input[name="id_documento"]').val(id);
        }

        function editimg(id, descricao) {
            $('#teste').val(descricao).prop('selected', true);
            $('input[name="id_documento"]').val(id);
            $("#editimg").modal('show');
        }




        $(function() {
            // pega no SaudeControle, listarUm
            var interno = <?= $atendidoOcorrencia ?>;


            console.log(interno);
            // console.log(arquivo);
            $.each(interno, function(i, item) {
                // Adiciona os campos do formulário
                $("#formulario").append($("<input type='hidden' name='id_fichamedica' value='" + item.id + "'>"));
                $("#nome").val(item.nome_atendido + " " + item.sobrenome_atendido);
                $("#tipo").val(item.descricao);
                $("#data").val(item.data);
                $("#descricao").val(item.atendido_ocorrencia_tipos_idatendido_ocorrencia_tipos);
                $("#autor").val(item.funcionario_id_funcionario);

                // Verifique se existe mais de um arquivo para o item
                if (Array.isArray(item.arquivos)) {
                    $.each(item.arquivos, function(j, arquivo) {
                        if (arquivo.nome != null && arquivo.extensao != null) {
                            $("#arquivos")
                                .append($("<tr>")
                                    .append($("<td>")
                                        .html("<a href='<?php echo WWW; ?>html/atendido/exibe_anexo.php?idatendido_ocorrencias=" + item.idatendido_ocorrencias + "&extensao=" + arquivo.extensao + "&idatendido_ocorrencia_doc=" + arquivo.idatendido_ocorrencia_doc + "&nome=" + arquivo.nome + "'>" + arquivo.nome + "." + arquivo.extensao + "</a>")));
                        }
                    });
                } else if (item.arquivo_nome != null && item.arquivo_extensao != null) {
                    // Para o caso de apenas um arquivo
                    $("#arquivos")
                        .append($("<tr>")
                            .append($("<td>")
                                .html("<a href='<?php echo WWW; ?>html/atendido/exibe_anexo.php?idatendido_ocorrencias=" + item.idatendido_ocorrencias + "&extensao=" + item.arquivo_extensao + "&idatendido_ocorrencia_doc=" + item.idatendido_ocorrencia_doc + "&nome=" + item.arquivo_nome + "'>" + item.arquivo_nome + "." + item.arquivo_extensao + "</a>")));
                }

            });
            //      $.each(despachoAnexo,function(i, item){
            // 	$("#"+item.arquivo)
            // 		.append($("<tr>")
            // 				.append($("<th colspan=4>")
            // 					.text("Anexos")));
            // });

        });
        //  });
        $(function() {
            $("#header").load("../header.php");
            $(".menuu").load("../menu.php");
        });
        //
    </script>

    <script src="controller/script/valida_cpf_cnpj.js"></script>
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
                    <h2>Informações da ocorrência</h2>
                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li>
                                <a href="../home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li><span>Informações da ocorrência</span></li>
                        </ol>
                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>


                <div class="row">
                    <div class="col-md-8 col-lg-12">
                        <div class="tabs">
                            <ul class="nav nav-tabs tabs-primary">
                                <li class="active">
                                    <a href="#overview" data-toggle="tab">Informações da ocorrência</a>
                                </li>
                                <!-- <li>
                                    <a href="#documentos" data-toggle="tab">Documentos</a>
                                </li> -->
                            </ul>
                            <div class="tab-content">
                                <div id="overview" class="tab-pane active">
                                    <form class="form-horizontal" id="doc" method="GET" action="../../controle/control.php">
                                        <section class="panel">
                                            <header class="panel-heading">
                                                <div class="panel-actions">
                                                    <a href="#" class="fa fa-caret-down"></a>
                                                </div>
                                                <h2 class="panel-title">Informações</h2>
                                            </header>
                                            <div class="panel-body">
                                                <!-- <h5 class="obrig">Campos Obrigatórios(*)</h5> -->
                                                <br>



                                                <div class="form-group">
                                                    <label class="col-md-2 control-label" for="profileFirstName">Nome</label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" disabled name="nome" id="nome" id="profileFirstName" onkeypress="return Onlychars(event)" required>
                                                        <?php
                                                        // foreach($nome as $key => $value)
                                                        // {
                                                        //     // echo "" . "" . $nome[$key]['nome'] . " " . $nome[$key]['sobrenome'];
                                                        //     // echo "<input type='text' class='form-control' disabled name='nome' id='nome' id='profileFirstName' onkeypress='return Onlychars(event)' value='$nome['nome']' required>"
                                                        // }
                                                        ?>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-2 control-label" for="profileFirstName">Tipo da ocorrência</label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" disabled name="nome" id="tipo" id="tipo" onkeypress="return Onlychars(event)" required>
                                                        <!-- <?php
                                                                foreach ($tipo as $key => $value) {
                                                                    echo "" . "" . $tipo[$key]['descricao'];
                                                                }
                                                                ?> -->
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-2 control-label" for="profileFirstName">Autor da ocorrência</label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" disabled name="autor" id="autor" id="autor" onkeypress="return Onlychars(event)" required>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label" for="profileCompany">Data</label>
                                                    <div class="col-md-6">
                                                        <input type="date" class="form-control" disabled maxlength="10" placeholder="dd/mm/aaaa" name="data" id="data" max=2021-06-11>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label" for="profileFirstName">Descrição</label>
                                                    <div class="col-md-8">
                                                        <textarea type="text" class="form-control" disabled name="autor" id="descricao" id="descricao" onkeypress="return Onlychars(event)" required>
                                                </textarea>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label" for="profileFirstName" id="arquivo">Arquivos:</label>
                                                    <div id="arquivos" class="col-md-8">

                                                    </div>
                                                </div>


                                                <!-- <div class="form-group">
                                                <label class="col-md-2 control-label" for="profileFirstName">Descrição</label>
                                                <div class="col-md-8">
                                                    <textarea type="text" class="form-control" disabled name="descricao" id="descricao" id="profileFirstName" onkeypress="return Onlychars(event)">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-2 control-label" for="profileFirstName">Descrição</label>
                                                <div class="col-md-8">
                                                    <textarea type="text" class="form-control" disabled name="descricao" id="descricao" id="profileFirstName" onkeypress="return Onlychars(event)">
                                                </div>
                                            </div>            -->







                                        </section>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </form>
                <!-- </div> -->


                <!-- end: page -->
                <!-- Vendor -->
                <script src="<?php echo WWW; ?>assets/vendor/select2/select2.js"></script>
                <script src="<?php echo WWW; ?>assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
                <script src="<?php echo WWW; ?>assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
                <script src="<?php echo WWW; ?>assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>

                <!-- Theme Base, Components and Settings -->
                <!-- <script src="<?php echo WWW; ?>assets/javascripts/theme.js"></script> -->

                <!-- Theme Custom -->
                <script src="<?php echo WWW; ?>assets/javascripts/theme.custom.js"></script>

                <!-- Theme Initialization Files -->
                <script src="<?php echo WWW; ?>assets/javascripts/theme.init.js"></script>
                <!-- Examples -->
                <script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.default.js"></script>
                <script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
                <script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.tabletools.js"></script>
</body>

</html>