<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: " . WWW . "index.php");
    exit();
} else {
    session_regenerate_id();
}

$id_pessoa = filter_var($_SESSION['id_pessoa'], FILTER_SANITIZE_NUMBER_INT);

if (!$id_pessoa || $id_pessoa < 1) {
    http_response_code(400);
    echo json_encode(['erro' => 'O id do usuário é inválido.']);
    exit();
}

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($_SESSION['id_pessoa'], 12, 3);

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once ROOT . "/dao/Conexao.php";
require_once ROOT . "/controle/Atendido_ocorrenciaControle.php";
require_once ROOT . "/html/personalizacao_display.php";
require_once ROOT . '/classes/Util.php';

try {
    $pdo = Conexao::connect();
    $nome = $pdo->query("SELECT a.idatendido, p.nome, p.sobrenome FROM pessoa p JOIN atendido a ON(p.id_pessoa=a.pessoa_id_pessoa) ORDER BY p.nome ASC, p.sobrenome ASC")->fetchAll(PDO::FETCH_ASSOC);
    $tipo = $pdo->query("SELECT * FROM atendido_ocorrencia_tipos")->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT id_funcionario FROM funcionario WHERE id_pessoa=:idPessoa");
    $stmt->bindValue(':idPessoa', $id_pessoa, PDO::PARAM_INT);
    $stmt->execute();

    $id_funcionario = $stmt->fetch(PDO::FETCH_ASSOC)['id_funcionario'];

    $atendido_id = filter_input(INPUT_GET, 'atendido_id', FILTER_SANITIZE_NUMBER_INT);

    $ocorrencia_msg = filter_input(INPUT_GET, 'ocorrencia_msg', FILTER_SANITIZE_SPECIAL_CHARS);

    if ($atendido_id !== null && $atendido_id !== false && $atendido_id < 1)
        throw new InvalidArgumentException('O id do paciente não é válido.', 412);
} catch (Exception $e) {
    Util::tratarException($e);
    exit();
}
?>

<!DOCTYPE html>
<html class="fixed">

<head>
    <!-- Basic -->
    <meta charset="UTF-8">

    <title>Cadastro Ocorrência</title>

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

    <!-- Vermelho dos campos obrigatórios -->
    <style type="text/css">
        .obrig {
            color: rgb(255, 0, 0);
        }
    </style>

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
            $("#header").load("../header.php");
            $(".menuu").load("../menu.php");

            CKEDITOR.replace('despacho');
        });
    </script>
    <script>
        (function($) {
            $.fn.uploader = function(options) {
                let settings = $.extend({
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

                let uploadId = 1;
                //atualiza a mensagem
                $(".file-uploader__message-area p").text(
                    options.MessageAreaText || settings.MessageAreaText
                );

                // cria e adiciona a lista de arquivos e a lista de entrada oculta
                let fileList = $('<ul class="file-list"></ul>');
                let hiddenInputs = $('<div class="hidden-inputs hidden"></div>');
                $(".file-uploader__message-area").after(fileList);
                $(".file-list").after(hiddenInputs);

                //ao escolher um arquivo, adicione o nome à lista e copie a entrada do arquivo para as entradas ocultas
                $(".file-chooser__input").on("change", function() {
                    let files = document.querySelector(".file-chooser__input").files;

                    for (let i = 0; i < files.length; i++) {
                        console.log(files[i]);

                        let file = files[i];
                        let fileName = file.name.match(/([^\\\/]+)$/)[0];

                        //limpe qualquer condição de erro
                        $(".file-chooser").removeClass("error");
                        $(".error-message").remove();

                        //validate the file
                        //valide o arquivo

                        let check = checkFile(fileName);
                        if (check === "valid") {

                            //mova o 'real' para a lista oculta
                            $(".hidden-inputs").append($(".file-chooser__input"));

                            //insira um clone após os hiddens (copie os manipuladores de eventos também)

                            $(".file-chooser").append(
                                $(".file-chooser__input").clone({
                                    withDataAndEvents: true
                                })
                            );

                            //adicione o nome e um botão de remoção à lista de arquivos
                            $(".file-list").append(
                                '<li style="list-style-type: none;"><span class="file-list__name">' +
                                fileName +
                                '</span></li>'
                            );
                            $(".file-list").find("li:last").show(800);

                            $(".hidden-inputs .file-chooser__input")
                                .removeClass("file-chooser__input")
                                .attr("data-uploadId", uploadId);

                            //atualize a área de mensagem
                            $(".file-uploader__message-area").text(
                                options.MessageAreaTextWithFiles ||
                                settings.MessageAreaTextWithFiles
                            );
                            uploadId++;

                        } else {
                            //indica que o arquivo não está ok
                            $(".file-chooser").addClass("error");
                            let errorText =
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
                });


                let checkFile = function(fileName) {
                    let accepted = "invalid",
                        acceptedFileTypes =
                        this.acceptedFileTypes || settings.acceptedFileTypes,
                        regex;

                    for (let i = 0; i < acceptedFileTypes.length; i++) {
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
            $(".fileUploader").uploader({
                MessageAreaText: "No files selected. Please select a file."
            });
        });

        function gerarOcorrencia() {
            url = '../../dao/exibir_ocorrencia.php';
            $.ajax({
                data: '',
                type: "POST",
                url: url,
                async: true,
                success: function(response) {
                    let ocorrencias = response;
                    $('#id_tipos_ocorrencia').empty();
                    $('#id_tipos_ocorrencia').append('<option selected disabled>Selecionar</option>');
                    $.each(ocorrencias, function(i, item) {
                        $('#id_tipos_ocorrencia').append('<option value="' + item.idatendido_ocorrencia_tipos + '">' + item.descricao + '</option>');
                    });
                },
                dataType: 'json'
            });
        }

        function adicionar_ocorrencia() {
            url = '../../dao/adicionar_ocorrencia.php';
            let ocorrencia = window.prompt("Cadastre uma Nova Ocorrência:");
            if (!ocorrencia) {
                return
            }
            ocorrencia = ocorrencia.trim();
            if (ocorrencia == '') {
                return
            }

            data = 'atendido_ocorrencia_tipos=' + ocorrencia;

            console.log(data);
            $.ajax({
                type: "POST",
                url: url,
                data: data,
                dataType: 'json',

                success: function(response) {
                    gerarOcorrencia(); // fluxo normal
                },

                error: function(xhr) {
                    // Se o PHP retornou na faixa 400
                    if (xhr.status >= 400 && xhr.status < 500) {
                        try {
                            let erro = JSON.parse(xhr.responseText);
                            alert(erro.erro);
                        } catch (e) {
                            alert("Ocorreu um erro na requisição.");
                        }
                        return;
                    }

                    // Outros erros (500 etc.)
                    alert("Erro inesperado ao cadastrar ocorrência.");
                }
            });
        }
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
                    <h2>Cadastro Ocorrência</h2>
                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li>
                                <a href="../home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li><span>Cadastro Ocorrência</span></li>
                        </ol>
                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>

                <div class="row">
                    <div class="col-md-8 col-lg-12">
                        <div class="tabs">
                            <ul class="nav nav-tabs tabs-primary">
                                <li class="active">
                                    <a href="#overview" data-toggle="tab">Cadastro Ocorrência</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div id="overview" class="tab-pane active">

                                    <section class="panel">
                                        <header class="panel-heading">
                                            <div class="panel-actions">
                                                <a href="#" class="fa fa-caret-down"></a>
                                            </div>
                                            <h2 class="panel-title">Informações </h2>
                                        </header>
                                        <div class="panel-body">
                                            <?php if ($ocorrencia_msg == 'cadastro-sucesso'): ?>
                                                <div class="alert alert-success text-center alert-dismissible" role="alert">
                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    Ocorrência cadastrada com sucesso!
                                                </div>
                                            <?php elseif ($ocorrencia_msg == 'cadastro-falha'): ?>
                                                <div class="alert alert-danger text-center alert-dismissible" role="alert">
                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    Falha ao cadastrar ocorrência.
                                                </div>
                                            <?php elseif ($ocorrencia_msg == 'data-anterior-nascimento'): ?>
                                                <div class="alert alert-danger text-center alert-dismissible" role="alert">
                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    Erro: A data da ocorrência não pode ser anterior à data de nascimento!
                                                </div>
                                            <?php elseif ($ocorrencia_msg == 'data-formato-invalido'): ?>
                                                <div class="alert alert-danger text-center alert-dismissible" role="alert">
                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    Erro no formato da data. Verifique a data da ocorrência.
                                                </div>
                                            <?php elseif ($ocorrencia_msg == 'id-invalido'): ?>
                                                <div class="alert alert-danger text-center alert-dismissible" role="alert">
                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    ID do atendido inválido!
                                                </div>
                                            <?php endif; ?>
                                            <form class="form-horizontal" method="post" action="../../controle/control.php" enctype="multipart/form-data">
                                                <h5 class="obrig">Campos Obrigatórios(*)</h5>
                                                <br>
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="profileLastName">Atendido:<sup class="obrig">*</sup></label>
                                                    <div class="col-md-6">
                                                        <?php if ($atendido_id) :
                                                            $atendido_nome = '';
                                                            foreach ($nome as $item) {
                                                                if ($item['idatendido'] == $atendido_id) {
                                                                    $atendido_nome = $item['nome'] . ' ' . $item['sobrenome'];
                                                                    break;
                                                                }
                                                            }
                                                        ?>
                                                            <input type="hidden" name="atendido_idatendido" value="<?= htmlspecialchars($atendido_id) ?>">
                                                            <input type="text" class="form-control input-lg mb-md" value="<?= htmlspecialchars($atendido_nome) ?>" disabled>
                                                        <?php else : ?>
                                                            <select class="form-control input-lg mb-md" name="atendido_idatendido" id="atendido_idatendido" required>
                                                                <option selected disabled>Selecionar</option>
                                                                <?php
                                                                foreach ($nome as $key => $value) {
                                                                    echo "<option value=\"" . htmlspecialchars($nome[$key]['idatendido']) . "\">" . htmlspecialchars($nome[$key]['nome']) . " " . htmlspecialchars($nome[$key]['sobrenome']) . "</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="profileLastName">Tipo de ocorrência:<sup class="obrig">*</sup></label>
                                                    <div class="col-md-6">
                                                        <select class="form-control input-lg mb-md" name="id_tipos_ocorrencia" id="id_tipos_ocorrencia" required>
                                                            <option selected disabled>Selecionar</option>
                                                            <?php
                                                            foreach ($tipo as $key => $value) {
                                                                echo "<option value=" . htmlspecialchars($tipo[$key]['idatendido_ocorrencia_tipos']) . ">" . htmlspecialchars($tipo[$key]['descricao']) .  "</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <a onclick="adicionar_ocorrencia()"><i class="fas fa-plus w3-xlarge adicionar" style="margin-top: 0.75vw"></i></a>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="profileCompany">Data da ocorrência:<sup class="obrig">*</sup></label>
                                                    <div class="col-md-4">
                                                        <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="data" id="data" max=<?php echo date('Y-m-d'); ?> required>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for=arquivo id=etiqueta_arquivo class='col-md-3 control-label'>Arquivo </label>

                                                    <div class="file-chooser">
                                                        <input type="file" multiple name='arquivos[]' class="file-chooser__input">
                                                    </div><br>
                                                    <div class="file-uploader__message-area">
                                                        <!-- <p>Select a file to upload</p> -->
                                                    </div>
                                                </div>


                                                <div class="form-group">
                                                    <div class='col-md-6' id='div_texto' style="height: 499px;">

                                                        <label for="texto" id="descricao" style="padding-left: 15px;">Descrição ocorrência<sup class="obrig">*</sup></label>
                                                        <textarea cols='30' rows='5' id='despacho' name='descricao' class='form-control' onkeypress="return Onlychars(event)" required></textarea>

                                                    </div>
                                                </div>

                                                <br>
                                                <div class="panel-footer">
                                                    <div class='row'>
                                                        <div class="col-md-9 col-md-offset-3">
                                                            <input type="hidden" name="id_funcionario" value="<?= htmlspecialchars_decode($id_funcionario); ?>">
                                                            <input type="hidden" name="nomeClasse" value="Atendido_ocorrenciaControle">
                                                            <input type="hidden" name="metodo" value="incluir">
                                                            <input id="enviar" type="submit" class="btn btn-primary" value="Enviar">
                                                        </div>
                                                    </div>
                                            </form>
                                        </div>
                                </div>
                            </div>
                            <!-- </form> -->
                        </div>

                    </div>
                </div>
        </div>
        </div>
    </section>
    </section>
    </div>
    </section><!--section do body-->

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
        <iframe src="https://www.wegia.org/software/footer/pessoa.html" width="200" height="60" style="border:none;"></iframe>
    </div>
</body>

</html>