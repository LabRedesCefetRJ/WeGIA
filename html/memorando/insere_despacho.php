<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
//Inicia a sessão e redireciona o usuário para a página inicial
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: " . WWW . "index.php");
    exit(401);
} else {
    session_regenerate_id();
}

require_once dirname(__FILE__, 2). DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

$id_pessoa = filter_var($_SESSION['id_pessoa'], FILTER_SANITIZE_NUMBER_INT);

if(!$id_pessoa || $id_pessoa < 1){
    http_response_code(400);
    echo json_encode(['erro' => 'O id da pessoa informado não é válido.']);
    exit();
}

permissao($id_pessoa, 3);

require_once ROOT . "/controle/FuncionarioControle.php";
require_once ROOT . "/controle/memorando/MemorandoControle.php";

$funcionarios = new FuncionarioControle;
$funcionarios->listarTodos2();

$listarInativos = new MemorandoControle;
$listarInativos->listarIdTodosInativos();

$idMemorando = filter_input(INPUT_GET, 'id_memorando', FILTER_VALIDATE_INT);

if(!$idMemorando || $idMemorando < 1){
    http_response_code(400);
    echo json_encode(['erro' => 'O id informado do memorando não é válido.']);
    exit();
}

$issetMemorando = new MemorandoControle;
$issetMemorando->issetMemorando($idMemorando); //tomar cuidado com XSS

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";
?>

<!DOCTYPE html>

<html class="fixed">

<head>
    <!-- Basic -->
    <meta charset="UTF-8">

    <title>Novo Memorando</title>

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

    <!-- Specific Page Vendor CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/select2/select2.css" />
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/theme.css" />

    <!-- Skin CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/skins/default.css" />

    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/stylesheets/theme-custom.css">

    <!-- Head Libs -->
    <script src="<?php echo WWW; ?>assets/vendor/modernizr/modernizr.js"></script>

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
    <script src="<?php echo WWW; ?>assets/javascripts/theme.js"></script>

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
            var funcionario = <?php echo $_SESSION['funcionarios2'] ?>;
            $.each(funcionario, function(i, item) {
                $("#destinatario").append($("<option id=" + item.id_pessoa + " value=" + item.id_pessoa + " name=" + item.id_pessoa + ">" + item.nome + " " + item.sobrenome + "</option>"));
            });
            $("#header").load("<?php echo WWW; ?>html/header.php");
            $(".menuu").load("<?php echo WWW; ?>html/menu.php");

            var id_memorando = <?= $idMemorando?>; //XSS
            $("#id_memorando").val(id_memorando);

            CKEDITOR.replace('despacho');
        });
    </script>

    <!-- script para upload multiplo de arquivos -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        (function($) {
            $.fn.uploader = function(options) {
                var settings = $.extend({
                        // MessageAreaText: "No files selected.",
                        // MessageAreaTextWithFiles: "File List:",
                        // DefaultErrorMessage: "Unable to open this file.",
                        // BadTypeErrorMessage: "We cannot accept this file type at this time.",
                        acceptedFileTypes: [
                            "pdf",
                            "php",
                            "odt",
                            "jpg",
                            "gif",
                            "jpeg",
                            "bmp",
                            "tif",
                            "tiff",
                            "png",
                            "xps",
                            "doc",
                            "docx",
                            "fax",
                            "wmp",
                            "ico",
                            "txt",
                            "cs",
                            "rtf",
                            "xls",
                            "xlsx"
                        ]
                    },
                    options
                );

                var uploadId = 1;
                //update the messaging
                //atualiza a mensagem
                $(".file-uploader__message-area p").text(
                    options.MessageAreaText || settings.MessageAreaText
                );

                //create and add the file list and the hidden input list
                // cria e adiciona a lista de arquivos e a lista de entrada oculta
                var fileList = $('<ul class="file-list"></ul>');
                var hiddenInputs = $('<div class="hidden-inputs hidden"></div>');
                $(".file-uploader__message-area").after(fileList);
                $(".file-list").after(hiddenInputs);

                //when choosing a file, add the name to the list and copy the file input into the hidden inputs
                //ao escolher um arquivo, adicione o nome à lista e copie a entrada do arquivo para as entradas ocultas
                $(".file-chooser__input").on("change", function() {
                    var files = document.querySelector(".file-chooser__input").files;

                    for (var i = 0; i < files.length; i++) {
                        console.log(files[i]);

                        var file = files[i];
                        // console.log(file);
                        var fileName = file.name.match(/([^\\\/]+)$/)[0];

                        //clear any error condition
                        //limpe qualquer condição de erro
                        $(".file-chooser").removeClass("error");
                        $(".error-message").remove();

                        //validate the file
                        //valide o arquivo

                        var check = checkFile(fileName);
                        if (check === "valid") {
                            // move the 'real' one to hidden list
                            //mova o 'real' para a lista oculta


                            $(".hidden-inputs").append($(".file-chooser__input"));

                            //importante


                            //insert a clone after the hiddens (copy the event handlers too)
                            //insira um clone após os hiddens (copie os manipuladores de eventos também)

                            $(".file-chooser").append(
                                $(".file-chooser__input").clone({
                                    withDataAndEvents: true
                                })
                            );

                            //add the name and a remove button to the file-list
                            //adicione o nome e um botão de remoção à lista de arquivos
                            $(".file-list").append(
                                '<li style="list-style-type: none;"><span class="file-list__name">' +
                                fileName +
                                '</span></li>'
                            );
                            $(".file-list").find("li:last").show(800);

                            //so the event handler works on the new "real" one
                            //então o manipulador de eventos funciona no novo "real"
                            $(".hidden-inputs .file-chooser__input")
                                .removeClass("file-chooser__input")
                                .attr("data-uploadId", uploadId);


                            //update the message area
                            //atualize a área de mensagem
                            $(".file-uploader__message-area").text(
                                options.MessageAreaTextWithFiles ||
                                settings.MessageAreaTextWithFiles
                            );
                            uploadId++;

                        } else {
                            //indicate that the file is not ok
                            //indica que o arquivo não está ok
                            $(".file-chooser").addClass("error");
                            var errorText =
                                options.DefaultErrorMessage || settings.DefaultErrorMessage;

                            if (check === "badFileName") {
                                errorText =
                                    options.BadTypeErrorMessage || settings.BadTypeErrorMessage;
                            }

                            $(".file-chooser__input").after(
                                '<p class="error-message">' + errorText + "</p>"
                            );
                        }
                    }

                    // $(".file-chooser__input").val("");

                });


                var checkFile = function(fileName) {
                    var accepted = "invalid",
                        acceptedFileTypes =
                        this.acceptedFileTypes || settings.acceptedFileTypes,
                        regex;

                    for (var i = 0; i < acceptedFileTypes.length; i++) {
                        regex = new RegExp("\\." + acceptedFileTypes[i] + "$", "i");

                        if (regex.test(fileName)) {
                            accepted = "valid";
                            break;
                        } else {
                            accepted = "badFileName";
                        }
                    }

                    return accepted;

                };

            };

        })($);

        //init
        $(document).ready(function() {
            console.log("hi");
            $(".fileUploader").uploader({
                MessageAreaText: "No files selected. Please select a file."
            });
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
    </style>
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
                    <h2>Novo Memorando</h2>
                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li>
                                <a href="<?= WWW ?>html/home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li><span>Novo Memorando</span></li>
                        </ol>
                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>
                <!-- start: page -->
                <!-- Caso o memorando tenha sido inserido-->
                <?php
                if (isset($_GET['msg'])) {
                    if ($_GET['msg'] == 'success') {
                        echo ('<div class="alert alert-success"><i class="fas fa-check mr-md"></i><a href="#" class="close" onclick="closeMsg()" data-dismiss="alert" aria-label="close">&times;</a>' . htmlspecialchars($_GET["sccs"]) . "</div>");
                    }
                }
                ?>

                <section class="panel">
                    <?php
                    if (in_array($idMemorando, $_SESSION['memorandoIdInativo']) || $_SESSION['isset_memorando'] == 1) { //Tomar cuidado com XSS
                    ?>
                        <script>
                            $(".panel").html("<p>Desculpe, você não tem acesso à essa página</p>");
                        </script>
                    <?php
                    } else {
                    ?>
                        <header class="panel-heading">
                            <h2 class="panel-title">Despachar memorando</h2>
                        </header>
                        <div class="panel-body">

                            <?php
                            echo "<form action='" . WWW . "controle/control.php' class='file-uploader' method='post' enctype='multipart/form-data'>";
                            ?>
                            <div class="form-group">
                                <label for=destinatario id=etiqueta_destinatario class='col-md-3 control-label'>Destino </label>
                                <div class='col-md-6'>
                                    <select name="destinatario" id="destinatario" required class='form-control mb-md'></select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for=arquivo id=etiqueta_arquivo class='col-md-3 control-label'>Arquivo </label>
                                <!-- <div class="file-uploader__message-area"></div>
                                <div class='col-md-6' class="file-chooser">
                                    <input type="file" multiple name="anexo[]" class="file-chooser__input" id="anexo">
                                </div> -->

                                <div class="file-chooser">
                                    <input type="file" multiple name='anexo[]' class="file-chooser__input" id='teste'>
                                </div><br>
                                <div class="file-uploader__message-area">
                                    <!-- <p>Select a file to upload</p> -->
                                </div>
                            </div>
                            <div class="form-group">
                                <label for=texto id=etiqueta_despacho class='col-md-3 control-label'>Despacho </label>
                                <div class='col-md-6' id='div_texto' style="height: 499px;">
                                    <textarea cols='30' rows='5' id='despacho' name='texto' required class='form-control'></textarea>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-9 col-md-offset-8'>
                                    <input type='hidden' value='DespachoControle' name='nomeClasse' class='mb-xs mt-xs mr-xs btn btn-default'>
                                </div>
                                <div class='col-md-9 col-md-offset-8'>
                                    <input type='hidden' value='incluir' name='metodo' class='mb-xs mt-xs mr-xs btn btn-default'>
                                </div>
                                <div class='col-md-9 col-md-offset-8'>
                                    <input type='hidden' name='id_memorando' id='id_memorando' class='mb-xs mt-xs mr-xs btn btn-default'>
                                </div>
                                <div class='col-md-9 col-md-offset-8'>
                                    <input type='hidden' name='modulo' value="memorando" class='mb-xs mt-xs mr-xs btn btn-default'>
                                </div>
                                <div class='col-md-9 col-md-offset-8'>
                                    <input type='submit' value='Enviar' name='enviar' id='enviar' class='mb-xs mt-xs mr-xs btn btn-primary'>
                                </div>
                            </div>
                            </form>
                        </div>
                    <?php
                    }
                    ?>
        </div>
    </section>
    </section>
    </div>
    </section>

    <!-- end: page -->
    <!-- Vendor -->
    <script src="<?php echo WWW; ?>assets/vendor/select2/select2.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>

    <!-- Theme Base, Components and Settings -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.js"></script>

    <!-- Theme Custom -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.custom.js"></script>

    <!-- Theme Initialization Files -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.init.js"></script>
    <!-- Examples -->
    <script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.default.js"></script>
    <script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
    <script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.tabletools.js"></script>

    <div align="right">
        <iframe src="https://www.wegia.org/software/footer/memorando.html" width="200" height="60" style="border:none;"></iframe>
    </div>
</body>

</html>