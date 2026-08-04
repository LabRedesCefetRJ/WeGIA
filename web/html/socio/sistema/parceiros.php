<?php
//Página de benefícios para sócios, onde o administrador pode criar, editar e deletar regras de benefícios
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../erros/login_erro/");
    exit();
} else {
    session_regenerate_id();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($_SESSION['id_pessoa'], 4, 7);

require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'config.php';

require("../conexao.php");
// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once ROOT . "/html/personalizacao_display.php";


// Requisição autenticada para serviços da API
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'Functions' . DIRECTORY_SEPARATOR . 'authenticatedRequest.php';
?>

<!DOCTYPE html>
<html class="fixed">

<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Gerencie as parcerias</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="controller/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="controller/bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="controller/dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="controller/dist/css/skins/_all-skins.min.css">
    <!-- Morris chart -->
    <link rel="stylesheet" href="controller/bower_components/morris.js/morris.css">
    <!-- jvectormap -->
    <link rel="stylesheet" href="controller/bower_components/jvectormap/jquery-jvectormap.css">
    <!-- Date Picker -->
    <link rel="stylesheet" href="controller/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="controller/bower_components/bootstrap-daterangepicker/daterangepicker.css">
    <!-- bootstrap wysihtml5 - text editor -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@700&display=swap" rel="stylesheet">
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="<?php echo WWW; ?>assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <!--<link rel="icon" href="<?php //display_campo("Logo",'file');
                                ?>" type="image/x-icon">-->

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

    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <!-- javascript functions -->

    <script type="text/javascript">
        $(function() {
            $("#header").load("<?php echo WWW; ?>html/header.php");
            $(".menuu").load("<?php echo WWW; ?>html/menu.php");
        });
    </script>

    <style>
        .hidden {
            display: none;
        }

        .obrig {
            color: red;
        }

        .box-body {
            padding: 0;
        }

        .logo-parceiro {
            width: 48px;
            height: 48px;
            object-fit: contain;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 2px;
        }
    </style>
</head>

<body>

    <section class="body">

        <!-- start: header -->
        <header id="header" class="header print-hide">

            <!-- end: search & user box -->
        </header>

        <!-- end: header -->
        <div class="inner-wrapper">
            <!-- start: sidebar -->
            <aside id="sidebar-left" class="sidebar-left menuu"></aside>
            <!-- end: sidebar -->

            <section role="main" class="content-body">
                <header class="page-header">
                    <h2>Parceiros Institucionais</h2>

                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li>
                                <a href="../../home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li><span>Páginas</span></li>
                            <li><span>Parceiros Institucionais</span></li>
                        </ol>

                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>

                <!-- start: page -->

                <!-- Container para alertas -->
                <div id="alertContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999; width: 400px; max-width: 90vw;"></div>

                <div class="row">
                    <div class="col-md-12">
                        <section class="panel panel-featured panel-featured-primary">
                            <header class="panel-heading">
                                <div class="panel-actions">
                                    <a href="#" class="panel-action panel-action-toggle" data-panel-toggle></a>
                                </div>

                                <h2 class="panel-title">Lista de Parceiros Associados</h2>
                            </header>
                            <div class="panel-body">
                                <p class="text-muted">Gerencie as informações das instituições parceiras da sua organização.</p>

                                <div class="mb-3" style="margin-bottom: 20px;">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCriarParceiro">
                                        <i class="fa fa-plus"></i> Nova parceria
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover" id="tabelaRegras">
                                        <thead>
                                            <tr>
                                                <th width="5%" class="text-center">#</th>
                                                <th style="width: 60px;" class="text-center">Logo</th>
                                                <th width="15%" class="text-center">CNPJ</th>
                                                <th class="text-center">Razão Social</th>
                                                <th width="10%" class="text-center">Status</th>
                                                <th width="25%" class="text-center">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="corpoTabela">
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">
                                                    <i class="fa fa-spinner fa-spin"></i> Carregando parceiros...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- Modal Criar Parceiro -->
                <div class="modal fade" id="modalCriarParceiro" tabindex="-1" role="dialog" aria-labelledby="modalCriarParceiroLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-primary">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="modalCriarParceiroLabel">Nova parceria</h4>
                            </div>
                            <div class="modal-body">
                                <form id="formularioCriarParceiro">
                                    <div class="form-group">
                                        <label for="logo">Logo</label>
                                        <input type="file" class="form-control" id="logo" name="logo">
                                    </div>
                                    <div class="form-group">
                                        <label for="cnpj">CNPJ <span class="obrig">*</span></label>
                                        <input type="text" class="form-control" id="cnpj" name="cnpj" placeholder="12.345.678/9000-00" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="razao_social">Razão Social <span class="obrig">*</span></label>
                                        <input type="text" class="form-control" id="razao_social" name="razao_social" placeholder="Insira o nome da instituição parceira" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="telefone">Telefone</label>
                                        <input type="text" class="form-control" id="telefone" name="telefone" placeholder="(22) 91234-5678">
                                    </div>

                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="parceiro@email.com">
                                    </div>

                                    <!-- Site/redes sociais para divulgação-->
                                    <div class="form-group">
                                        <label for="divulgacao">Divulgação <i class="fa-solid fa-globe"></i> <i class="fa-brands fa-instagram"></i></label>
                                        <input type="text" class="form-control" id="divulgacao" name="divulgacao" placeholder="https://site.parceiro.com.br">
                                    </div>

                                    <div class="form-group">
                                        <label for="descricao">Descrição</label>
                                        <textarea class="form-control" id="descricao" name="descricao" placeholder="Descrição da parceria" rows="3"></textarea>
                                    </div>

                                    <h4>Endereço</h4>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="form-group mb-2 col-xs-6">
                                                <label for="cep">CEP</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                                    <input type="text" id="cep" class="form-control" placeholder="" name="cep">
                                                </div>
                                                <div class="status_cep col-xs-12"></div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group mb-2 col-xs-8">
                                                <label for="rua">Rua</label>
                                                <input type="text" class="form-control" id="rua" name="rua" placeholder="">
                                            </div>
                                            <div class="form-group col-xs-4">
                                                <label for="numero_endereco">Número</label>
                                                <input type="number" class="form-control" min="0" id="numero_endereco" name="numero_endereco" placeholder="">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group mb-2 col-xs-6">
                                                <label for="complemento">Complemento</label>
                                                <input type="text" class="form-control" id="complemento" name="complemento" placeholder="">
                                            </div>
                                            <div class="form-group col-xs-6">
                                                <label for="bairro">Bairro</label>
                                                <input type="text" class="form-control" id="bairro" name="bairro" placeholder="">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group mb-2 col-xs-6">
                                                <label for="estado">Estado</label>
                                                <input type="text" class="form-control" id="estado" name="estado" placeholder="">
                                            </div>
                                            <div class="form-group col-xs-6">
                                                <label for="cidade">Cidade</label>
                                                <input type="text" class="form-control" id="cidade" name="cidade" placeholder="">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="form-group mb-2 col-xs-12">
                                                <label for="localizacao">Localização - Google Maps <i class="fa-solid fa-location-dot"></i></label>
                                                <input type="text" class="form-control" name="localizacao" id="localizacao" placeholder="https://www.google.com/maps/place/endereco+da+instituicao">
                                            </div>
                                        </div>
                                    </div>

                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="btnSalvarNovaParceria">
                                    <i class="fa fa-save"></i> Cadastrar parceria
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Editar Parceiro -->
                <div class="modal fade" id="modalEditarParceiro" tabindex="-1" role="dialog" aria-labelledby="modalEditarParceiroLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-info">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="modalEditarParceiroLabel">Editar dados do parceiro</h4>
                            </div>
                            <div class="modal-body">
                                <form id="formularioEditarParceiro">
                                    <input type="hidden" id="idParceiro" name="id">
                                    <div class="form-group">
                                        <label for="logo">Logo</label>
                                        <input type="file" class="form-control" id="logoEditar" name="logo">
                                    </div>
                                    <div class="form-group">
                                        <label for="cnpj">CNPJ <span class="obrig">*</span></label>
                                        <input type="text" class="form-control" id="cnpjEditar" name="cnpj" placeholder="12.345.678/9000-00" disabled required>
                                    </div>
                                    <div class="form-group">
                                        <label for="razao_social">Razão Social <span class="obrig">*</span></label>
                                        <input type="text" class="form-control" id="razao_socialEditar" name="razao_social" placeholder="Insira o nome da instituição parceira" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="telefoneEditar">Telefone</label>
                                        <input type="text" class="form-control" id="telefoneEditar" name="telefone" placeholder="(22) 91234-5678">
                                    </div>

                                    <div class="form-group">
                                        <label for="emailEditar">Email</label>
                                        <input type="email" class="form-control" id="emailEditar" name="email" placeholder="parceiro@email.com">
                                    </div>

                                    <!-- Site/redes sociais para divulgação-->
                                    <div class="form-group">
                                        <label for="divulgacaoEditar">Divulgação <i class="fa-solid fa-globe"></i> <i class="fa-brands fa-instagram"></i></label>
                                        <input type="text" class="form-control" id="divulgacaoEditar" name="divulgacao" placeholder="https://site.parceiro.com.br">
                                    </div>

                                    <div class="form-group">
                                        <label for="descricaoEditar">Descrição</label>
                                        <textarea class="form-control" id="descricaoEditar" name="descricao" placeholder="Descrição da parceria" rows="3"></textarea>
                                    </div>

                                    <h4>Endereço</h4>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="form-group mb-2 col-xs-6">
                                                <label for="cepEditar">CEP</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                                    <input type="text" id="cepEditar" class="form-control" placeholder="" name="cep">
                                                </div>
                                                <div class="status_cep col-xs-12"></div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group mb-2 col-xs-8">
                                                <label for="ruaEditar">Rua</label>
                                                <input type="text" class="form-control" id="ruaEditar" name="rua" placeholder="">
                                            </div>
                                            <div class="form-group col-xs-4">
                                                <label for="numero_enderecoEditar">Número</label>
                                                <input type="number" class="form-control" min="0" id="numero_enderecoEditar" name="numero_endereco" placeholder="">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group mb-2 col-xs-6">
                                                <label for="complementoEditar">Complemento</label>
                                                <input type="text" class="form-control" id="complementoEditar" name="complemento" placeholder="">
                                            </div>
                                            <div class="form-group col-xs-6">
                                                <label for="bairroEditar">Bairro</label>
                                                <input type="text" class="form-control" id="bairroEditar" name="bairro" placeholder="">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group mb-2 col-xs-6">
                                                <label for="estadoEditar">Estado</label>
                                                <input type="text" class="form-control" id="estadoEditar" name="estado" placeholder="">
                                            </div>
                                            <div class="form-group col-xs-6">
                                                <label for="cidadeEditar">Cidade</label>
                                                <input type="text" class="form-control" id="cidadeEditar" name="cidade" placeholder="">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="form-group mb-2 col-xs-12">
                                                <label for="localizacaoEditar">Localização - Google Maps <i class="fa-solid fa-location-dot"></i></label>
                                                <input type="text" class="form-control" name="localizacao" id="localizacaoEditar" placeholder="https://www.google.com/maps/place/endereco+da+instituicao">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-info" id="btnAtualizarParceiro">
                                    <i class="fa fa-refresh"></i> Atualizar Parceiro
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Confirmar Exclusão -->
                <div class="modal fade" id="modalConfirmarDelecao" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarDelecaoLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-danger">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="modalConfirmarDelecaoLabel">Confirmar Exclusão</h4>
                            </div>
                            <div class="modal-body">
                                <p>Tem certeza que deseja deletar este parceiro associado? <strong>Esta ação não pode ser desfeita.</strong></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-danger" id="btnConfirmarDelecao">
                                    <i class="fa fa-trash"></i> Deletar Regra
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    let parceiroParaDeletar = null;

                    //Alterar scripts para parceiros institucionais
                    $(document).ready(function() {
                        carregarParceiros();

                        // Evento: Salvar nova parceria
                        $('#btnSalvarNovaParceria').click(async function() {

                            const form = document.getElementById('formularioCriarParceiro');

                            // Validação HTML5
                            if (!form.checkValidity()) {
                                form.reportValidity();
                                return;
                            }

                            const payload = {
                                cnpj: $('#cnpj').val().replace(/\D/g, ''),
                                razao_social: $('#razao_social').val().trim(),
                                email: $('#email').val().trim(),
                                telefone: $('#telefone').val().replace(/\D/g, ''),
                                endereco: {
                                    cep: $('#cep').val().trim(),
                                    estado: $('#estado').val().trim(),
                                    cidade: $('#cidade').val().trim(),
                                    bairro: $('#bairro').val().trim(),
                                    logradouro: $('#rua').val().trim(),
                                    numero: $('#numero_endereco').val().trim(),
                                    complemento: $('#complemento').val().trim()
                                },
                                localizacao: $('#localizacao').val().trim(),
                                divulgacao: $('#divulgacao').val().trim(),
                                descricao: $('#descricao').val().trim()
                            };

                            try {

                                const response = await authenticatedRequest(() =>
                                    fetch(`${apiServer}socios/parceiros`, {
                                        method: 'POST',
                                        credentials: 'include',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-Client-Type': 'web'
                                        },
                                        body: JSON.stringify(payload)
                                    })
                                );

                                const result = await response.json();

                                if (!response.ok) {
                                    mostrarNotificacao(
                                        result.error || 'Erro ao cadastrar parceiro.',
                                        'error',
                                        5000
                                    );
                                    return;
                                }

                                // Executar apenas em caso de sucesso

                                //caso exista logo, enviar para o endpoint de upload
                                const logoFile = $('#logo')[0].files[0];
                                if (logoFile) {
                                    const formData = new FormData();
                                    formData.append('logo', logoFile);
                                    formData.append('id_socio_parceiro', result.socio_parceiro.id);

                                    try {
                                        await authenticatedRequest(() =>
                                            fetch(`${apiServer}socios/parceiros/logo`, {
                                                method: 'POST',
                                                credentials: 'include',
                                                headers: {
                                                    'X-Client-Type': 'web'
                                                },
                                                body: formData
                                            })
                                        );
                                    } catch (e) {
                                        console.error('Erro ao enviar o logo:', e);
                                        mostrarNotificacao(
                                            'Não foi possível enviar o logo.',
                                            'error',
                                            5000
                                        );
                                    }
                                }

                                $('#modalCriarParceiro').modal('hide');
                                form.reset();
                                carregarParceiros();

                                mostrarNotificacao(
                                    'Parceiro cadastrado com sucesso!',
                                    'success',
                                    3000
                                );

                            } catch (e) {

                                mostrarNotificacao(
                                    'Não foi possível conectar à API.',
                                    'error',
                                    5000
                                );

                            }

                        });

                        // Evento: Atualizar parceiro
                        $('#btnAtualizarParceiro').click(async function() {

                            const form = document.getElementById('formularioEditarParceiro');

                            // Validação HTML5
                            if (!form.checkValidity()) {
                                form.reportValidity();
                                return;
                            }

                            const payload = {
                                id_socio_parceiro: parseInt($('#idParceiro').val(), 10),
                                razao_social: $('#razao_socialEditar').val().trim(),
                                cnpj: $('#cnpjEditar').val().replace(/\D/g, ''),
                                telefone: $('#telefoneEditar').val().replace(/\D/g, ''),
                                email: $('#emailEditar').val().trim(),
                                endereco: {
                                    cep: $('#cepEditar').val().trim(),
                                    estado: $('#estadoEditar').val().trim(),
                                    cidade: $('#cidadeEditar').val().trim(),
                                    bairro: $('#bairroEditar').val().trim(),
                                    logradouro: $('#ruaEditar').val().trim(),
                                    numero_endereco: $('#numero_enderecoEditar').val().trim(),
                                    complemento: $('#complementoEditar').val().trim()
                                },
                                localizacao: $('#localizacaoEditar').val().trim(),
                                divulgacao: $('#divulgacaoEditar').val().trim(),
                                descricao: $('#descricaoEditar').val().trim()
                            };

                            try {

                                const response = await authenticatedRequest(() =>
                                    fetch(`${apiServer}/socios/parceiros`, {
                                        method: 'PUT',
                                        credentials: 'include',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-Client-Type': 'web'
                                        },
                                        body: JSON.stringify(payload)
                                    })
                                );

                                const result = await response.json();

                                if (!response.ok) {
                                    mostrarNotificacao(
                                        result.error || 'Erro ao atualizar parceiro.',
                                        'error',
                                        5000
                                    );
                                    return;
                                }

                                // Executar apenas em caso de sucesso

                                //caso exista logo, enviar para o endpoint de upload
                                const logoFile = $('#logoEditar')[0].files[0];
                                if (logoFile) {
                                    const formData = new FormData();
                                    formData.append('logo', logoFile);
                                    formData.append('id_socio_parceiro', payload.id_socio_parceiro);

                                    try {
                                        await authenticatedRequest(() =>
                                            fetch(`${apiServer}socios/parceiros/logo`, {
                                                method: 'POST',
                                                credentials: 'include',
                                                headers: {
                                                    'X-Client-Type': 'web'
                                                },
                                                body: formData
                                            })
                                        );
                                    } catch (e) {
                                        console.error('Erro ao enviar o logo:', e);
                                        mostrarNotificacao(
                                            'Não foi possível enviar o logo.',
                                            'error',
                                            5000
                                        );
                                    }
                                }

                                $('#modalEditarParceiro').modal('hide');
                                carregarParceiros();

                                mostrarNotificacao(
                                    'Parceiro atualizado com sucesso!',
                                    'success',
                                    3000
                                );

                            } catch (e) {

                                mostrarNotificacao(
                                    'Não foi possível conectar à API.',
                                    'error',
                                    5000
                                );

                            }

                        });

                        // Evento: Confirmar deleção
                        $('#btnConfirmarDelecao').click(async function() {

                            if (!parceiroParaDeletar) {
                                return;
                            }

                            try {

                                const response = await authenticatedRequest(() =>
                                    fetch(`${apiServer}/socios/parceiros/${parceiroParaDeletar}`, {
                                        method: 'DELETE',
                                        credentials: 'include',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-Client-Type': 'web'
                                        }
                                    })
                                );

                                const result = await response.json();

                                if (!response.ok) {
                                    mostrarNotificacao(
                                        result.error || result.message || 'Erro ao excluir parceiro.',
                                        'error',
                                        5000
                                    );
                                    return;
                                }

                                $('#modalConfirmarDelecao').modal('hide');
                                parceiroParaDeletar = null;

                                carregarParceiros();

                                mostrarNotificacao(
                                    'Parceiro excluído com sucesso!',
                                    'success',
                                    3000
                                );

                            } catch (e) {

                                mostrarNotificacao(
                                    'Não foi possível conectar à API.',
                                    'error',
                                    5000
                                );

                            }

                        });
                    });

                    function carregarParceiros() {
                        $.ajax({
                            type: 'GET',
                            url: '<?= API_BASE_URL . 'socios/parceiros' ?>',
                            success: function(data) {
                                console.log(data);
                                renderizarTabela(data.socio_parceiros);
                            },
                            error: function(xhr) {
                                const response = xhr.responseJSON || {};

                                // Trata caso especial: nenhum parceiro encontrado (não é erro, é informação)
                                if (response.error === 'Nenhuma parceiro institucional encontrado.') {
                                    $('#corpoTabela').html('<tr><td colspan="7" class="text-center text-muted"><i class="fa fa-info-circle"></i> ' + response.error + '</td></tr>');
                                } else {
                                    mostrarNotificacao(response.error || 'Erro ao carregar parceiros', 'error');
                                    $('#corpoTabela').html('<tr><td colspan="7" class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> Erro ao carregar parceiros</td></tr>');
                                }
                            }
                        });
                    }

                    function renderizarTabela(parceiros) {
                        let html = '';

                        if (!Array.isArray(parceiros) || parceiros.length === 0) {
                            html = '<tr><td colspan="7" class="text-center text-muted">Nenhuma regra encontrada</td></tr>';
                        } else {
                            parceiros.forEach(function(parceiro, index) {
                                const statusBadge = parceiro.ativo ?
                                    `
                                        <div style="display:flex;justify-content:center;align-items:center;">
                                            <span class="label label-success" style="font-size:13px;padding:6px 10px;">
                                                Ativo
                                            </span>
                                        </div>
                                    ` :
                                    `
                                        <div style="display:flex;justify-content:center;align-items:center;">
                                            <span class="label label-danger" style="font-size:13px;padding:6px 10px;">
                                                Inativo
                                            </span>
                                        </div>
                                    `;

                                const botaoToggleStatus = parceiro.ativo ?
                                    `<button class="btn btn-sm btn-warning" onclick="alternarStatus(${parceiro.id}, false)" title="Desativar"><i class="fa fa-toggle-on"></i> Desativar</button>` :
                                    `<button class="btn btn-sm btn-success" onclick="alternarStatus(${parceiro.id}, true)" title="Ativar"><i class="fa fa-toggle-off"></i> Ativar</button>`;

                                html += `
                                        <tr>
                                            <td class="text-center">${parceiro.id}</td>
                                            <td class="text-center" style="width: 60px;">
                                                <img
                                                    src="${apiServer}/socios/parceiros/${parceiro.id}/logo"
                                                    alt="Logo"
                                                    class="logo-parceiro"
                                                    onerror="this.outerHTML='<div class=&quot;text-muted&quot; style=&quot;width:48px;height:48px;display:flex;align-items:center;justify-content:center;font-size:11px;&quot;>Sem logo</div>';">
                                            </td>
                                            <td class="text-center">${parceiro.cnpj}</td>
                                            <td class="text-center">${parceiro.razao_social}</td>
                                            <td class="text-center">${statusBadge}</td> 
                                            <td class="text-center"> <button class="btn btn-sm btn-info btn-editar" title="Editar" data-id="${parceiro.id}" data-cnpj="${parceiro.cnpj}" data-razao-social="${parceiro.razao_social}" data-telefone="${parceiro.telefone}" data-email="${parceiro.email}" data-divulgacao="${parceiro.divulgacao}" data-descricao="${parceiro.descricao}" data-cep="${parceiro.cep}" data-rua="${parceiro.logradouro}" data-numero-endereco="${parceiro.numero_endereco}" data-complemento="${parceiro.complemento}" data-bairro="${parceiro.bairro}" data-estado="${parceiro.estado}" data-cidade="${parceiro.cidade}" data-localizacao="${parceiro.localizacao}"> <i class="fa fa-edit"></i> </button> ${botaoToggleStatus} <button class="btn btn-sm btn-danger" onclick="confirmarDelecao(${parceiro.id})" title="Deletar"><i class="fa fa-trash"></i></button> </td>
                                        </tr>
                                    `;
                            });
                        }

                        $('#corpoTabela').html(html);

                        $('#corpoTabela').on('click', '.btn-editar', function() {
                            editarParceiro(this);
                        });
                    }

                    function editarParceiro(botao) {
                        const $botao = $(botao);

                        // Limpa o formulário
                        $('#formularioEditarParceiro')[0].reset();

                        // Identificação
                        $('#idParceiro').val($botao.data('id'));

                        // Dados principais
                        $('#cnpjEditar').val($botao.data('cnpj'));
                        $('#razao_socialEditar').val($botao.data('razao-social'));
                        $('#telefoneEditar').val($botao.data('telefone'));
                        $('#emailEditar').val($botao.data('email'));
                        $('#divulgacaoEditar').val($botao.data('divulgacao'));
                        $('#descricaoEditar').val($botao.data('descricao'));

                        // Endereço
                        $('#cepEditar').val($botao.data('cep'));
                        $('#ruaEditar').val($botao.data('rua'));
                        $('#numero_enderecoEditar').val($botao.data('numero-endereco'));
                        $('#complementoEditar').val($botao.data('complemento'));
                        $('#bairroEditar').val($botao.data('bairro'));
                        $('#estadoEditar').val($botao.data('estado'));
                        $('#cidadeEditar').val($botao.data('cidade'));
                        $('#localizacaoEditar').val($botao.data('localizacao'));

                        // Limpa o campo de upload de arquivo
                        $('#logoEditar').val('');

                        // Abre o modal
                        $('#modalEditarParceiro').modal('show');
                    }

                    async function alternarStatus(id, ativar) {

                        const payload = {
                            id_socio_parceiro: id,
                            novo_status: ativar ? 1 : 0
                        };

                        try {

                            const response = await authenticatedRequest(() =>
                                fetch(`${apiServer}/socios/parceiros`, {
                                    method: 'PATCH',
                                    credentials: 'include',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-Client-Type': 'web'
                                    },
                                    body: JSON.stringify(payload)
                                })
                            );

                            const result = await response.json();

                            if (!response.ok) {
                                mostrarNotificacao(
                                    result.error || result.message || 'Erro ao alterar o status do parceiro.',
                                    'error',
                                    5000
                                );
                                return;
                            }

                            carregarParceiros();

                            mostrarNotificacao(
                                ativar ?
                                'Parceiro ativado com sucesso!' :
                                'Parceiro desativado com sucesso!',
                                'success',
                                3000
                            );

                        } catch (e) {

                            mostrarNotificacao(
                                'Não foi possível conectar à API.',
                                'error',
                                5000
                            );

                        }
                    }

                    function confirmarDelecao(id) {
                        parceiroParaDeletar = id;
                        $('#modalConfirmarDelecao').modal('show');
                    }

                    function requisicaoAjax(dados, callbackSucesso) {
                        $.ajax({
                            type: 'POST',
                            url: '<?php echo WWW; ?>controle/control.php',
                            contentType: 'application/json',
                            data: JSON.stringify(dados),
                            dataType: 'json',
                            success: function(response) {
                                callbackSucesso();
                            },
                            error: function(xhr) {
                                const response = xhr.responseJSON || {};
                                mostrarNotificacao(response.mensagem || 'Erro na operação', 'error', 5000);
                            }
                        });
                    }

                    function mostrarNotificacao(mensagem, tipo, duracao = 5000) {
                        const tipoClasse = tipo === 'success' ? 'alert-success' : 'alert-danger';
                        const icone = tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

                        const alertaHtml = `
                            <div class="alert ${tipoClasse} alert-dismissible fade in" role="alert" style="margin: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <i class="fa ${icone}" style="margin-right: 8px;"></i> <strong>${mensagem}</strong>
                            </div>
                        `;

                        const $alerta = $(alertaHtml);
                        $('#alertContainer').append($alerta);

                        // Auto-fechar após o tempo especificado
                        if (duracao > 0) {
                            setTimeout(function() {
                                $alerta.fadeOut('slow', function() {
                                    $(this).remove();
                                });
                            }, duracao);
                        }
                    }
                </script>

                <!-- end: page -->
            </section>
        </div>

        <div align="right">
            <iframe src="https://www.wegia.org/software/footer/socio.html" width="200" height="60" style="border:none;"></iframe>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="controller/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
</body>

</html>