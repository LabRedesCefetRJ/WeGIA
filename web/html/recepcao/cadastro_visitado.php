<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 12, 7);

require_once ROOT . '/classes/Csrf.php';
require_once ROOT . '/dao/VisitadoDAO.php';

require_once ROOT . "/html/personalizacao_display.php";

$tipoInicial = filter_input(INPUT_GET, 'tipo', FILTER_UNSAFE_RAW);
$tipoInicial = VisitadoDAO::tipoValido($tipoInicial) ? $tipoInicial : '';
?>


<!DOCTYPE html>

<html class="fixed">

<head>
    <!-- Basic -->
    <meta charset="UTF-8">

    <title>Cadastro de Visitado</title>

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
    <link rel="stylesheet" href="<?php echo WWW; ?>/assets/stylesheets/skins/default.css" />

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
    <script src="<?php echo WWW; ?>Functions/testaCPF.js"></script>

    <!-- printThis -->
    <script src="<?php echo WWW; ?>assets/vendor/jasonday-printThis-f73ca19/printThis.js"></script>


    <style type="text/css">
        .select {
            position: absolute;
            width: 235px;
        }

        .panel-body {
            margin-bottom: 15px;
        }

        img {
            margin-left: 11px;
        }

        /* print styles*/
        @media print {
            .printable {
                display: block;
            }

            .screen {
                display: none;
            }
        }

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

        #area1 {
            display: block;

        }

        #area2 {
            display: none;
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
                    <h2>Cadastro de Visitado</h2>
                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li>
                                <a href="<?php echo WWW; ?>html/home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li><span>Cadastro de Visitado</span></li>
                        </ol>
                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>

                <!-- start: page -->
                <?php
                if (isset($_SESSION['msg_c']) && !empty($_SESSION['msg_c'])) {
                    echo ('<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['msg_c']) . '</div>');
                    $_SESSION['msg_c'] = "";
                } else if (isset($_SESSION['msg_e']) && !empty($_SESSION['msg_e'])) {
                    echo ('<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['msg_e']) . '</div>');
                    $_SESSION['msg_e'] = "";
                }
                ?>

                <div id="alertaVisitado"></div>

                <p>
                    <a href="registro_entrada.php" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Voltar ao registro de entrada
                    </a>
                    <a href="cadastro_setor.php" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Criar setor
                    </a>
                </p>

                <section class="panel">
                    <header class="panel-heading">
                        <div class="panel-actions">
                            <a href="#" class="fa fa-caret-down"></a>
                        </div>
                        <h2 class="panel-title">Adicionar visitado</h2><br>
                        <label for="tipo">Selecione o tipo:</label>
                        <select name="select_tipo" id="tipo">
                            <option value="" selected disabled></option>
                            <option value="atendido">Atendido</option>
                            <option value="funcionario">Funcionário</option>
                            <option value="pet">Pet</option>
                            <option value="setor">Setor</option>
                            <option value="voluntario">Voluntário</option>
                        </select>
                        <p class="text-muted" style="margin:10px 0 0;">Somente aparecem registros que ainda não foram cadastrados como visitado.</p>
                    </header>
                    <div class="panel-body">
                        <table class="table table-bordered table-striped mb-none" id="datatable-default">
                            <thead>
                                <tr>
                                    <th>Foto</th>
                                    <th>Nome</th>
                                    <th>Identificador</th>
                                    <th class="text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="tabela">
                            </tbody>
                        </table>
                    </div>
                    <br>
                </section>
            </section>
        </div>
    </section>

    <!-- end: page -->
    <!-- Vendor -->
    <script src="../../Functions/onlyNumbers.js"></script>
    <script src="../../Functions/onlyChars.js"></script>
    <script src="../../Functions/mascara.js"></script>
    <script src="../../Functions/lista.js"></script>
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

    <script>
        const WWW_BASE = <?= json_encode(WWW) ?>;
        const fotoSemFoto = WWW_BASE + "img/semfoto.png";
        const csrfToken = <?= json_encode(Csrf::generateToken()) ?>;
        const tipoInicial = <?= json_encode($tipoInicial) ?>;

        function escaparHtml(texto) {
            return $("<div>").text(texto == null ? "" : texto).html();
        }

        function fotoHtml(item) {
            const src = item.imagem ? ("data:image;base64," + item.imagem) : fotoSemFoto;
            return `<img src="${src}" alt="Foto" class="rounded" style="width:40px;height:40px;object-fit:cover;">`;
        }

        function mostrarAlerta(tipo, mensagem) {
            $("#alertaVisitado").html(
                `<div class="alert alert-${tipo}" role="alert">${escaparHtml(mensagem)}</div>`
            );
        }

        function carregar(tipo) {
            if (!tipo) return;

            $.ajax({
                url: "./listar_candidatos_visitado.php",
                method: "GET",
                data: { tipo: tipo },
                dataType: "json",
                success: function(dados) {
                    const tabela = $("#datatable-default").DataTable();

                    tabela.clear();

                    $.each(dados, function(i, item) {
                        const nome = [item.nome, item.sobrenome].filter(Boolean).join(" ");

                        tabela.row.add([
                            fotoHtml(item),
                            escaparHtml(nome),
                            escaparHtml(item.identificador || "—"),
                            `<div class="text-center">
                                <button type="button" class="btn btn-primary btn-sm btn-adicionar-visitado" data-tipo="${escaparHtml(item.tipo)}" data-id="${Number(item.ref_id)}">
                                    <i class="fa fa-plus"></i> Adicionar como visitado
                                </button>
                            </div>`
                        ]);
                    });

                    tabela.draw();
                },
                error: function(xhr) {
                    const erro = xhr.responseJSON && xhr.responseJSON.erro ? xhr.responseJSON.erro : "Não foi possível carregar a lista.";
                    mostrarAlerta("danger", erro);
                }
            });
        }

        $(function() {
            $("#header").load("<?php echo WWW; ?>html/header.php");
            $(".menuu").load("<?php echo WWW; ?>html/menu.php");

            $("#datatable-default").DataTable();

            const tipoSalvo = localStorage.getItem("tipoCadastroVisitado");
            const tipoAtual = tipoInicial || tipoSalvo;

            if (tipoAtual && $("#tipo option[value='" + tipoAtual + "']").length) {
                $("#tipo").val(tipoAtual);
                carregar(tipoAtual);
            }

            $("#tipo").on("change", function() {
                const tipo = $(this).val();
                localStorage.setItem("tipoCadastroVisitado", tipo);
                $("#alertaVisitado").empty();
                carregar(tipo);
            });

            $(document).on("click", ".btn-adicionar-visitado", function() {
                const $botao = $(this);

                $botao.prop("disabled", true);

                $.ajax({
                    url: "../../controle/control.php",
                    method: "POST",
                    dataType: "json",
                    data: {
                        nomeClasse: "VisitadoControle",
                        metodo: "adicionar",
                        csrf_token: csrfToken,
                        tipo: $botao.data("tipo"),
                        id: $botao.data("id")
                    },
                    success: function(resposta) {
                        $("#datatable-default").DataTable().row($botao.closest("tr")).remove().draw(false);
                        mostrarAlerta("success", resposta.mensagem || "Visitado cadastrado com sucesso");
                    },
                    error: function(xhr) {
                        const erro = xhr.responseJSON && xhr.responseJSON.erro ? xhr.responseJSON.erro : "Erro ao cadastrar o visitado.";
                        mostrarAlerta("danger", erro);
                        $botao.prop("disabled", false);
                    }
                });
            });
        });
    </script>

    <div align="right">
        <iframe src="https://www.wegia.org/software/footer/pessoa.html" width="200" height="60" style="border:none;"></iframe>
    </div>
</body>

</html>