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

?>

<!DOCTYPE html>
<html class="fixed">

<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Benefícios</title>
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
                    <h2>Regras de benefícios</h2>

                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li>
                                <a href="../../home.php">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li><span>Páginas</span></li>
                            <li><span>Benefícios</span></li>
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

                                <h2 class="panel-title">Regras do Programa Sócio Amigo Doador</h2>
                            </header>
                            <div class="panel-body">
                                <p class="text-muted">Gerencie as regras de benefícios para sócios. Crie, edite, ative ou desative as regras conforme necessário.</p>

                                <div class="mb-3" style="margin-bottom: 20px;">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCriarRegra">
                                        <i class="fa fa-plus"></i> Nova Regra
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover" id="tabelaRegras">
                                        <thead>
                                            <tr>
                                                <th width="5%" class="text-center">#</th>
                                                <th width="15%" class="text-center">Valor por Ponto</th>
                                                <th width="15%" class="text-center">Pontos Máximos</th>
                                                <th width="15%" class="text-center">Duração (Meses)</th>
                                                <th width="15%" class="text-center">Janela de Análise (Meses)</th>
                                                <th width="10%" class="text-center">Status</th>
                                                <th width="25%" class="text-center">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="corpoTabela">
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">
                                                    <i class="fa fa-spinner fa-spin"></i> Carregando regras...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- Modal Criar Regra -->
                <div class="modal fade" id="modalCriarRegra" tabindex="-1" role="dialog" aria-labelledby="modalCriarRegraLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-primary">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="modalCriarRegraLabel">Nova Regra de Benefício</h4>
                            </div>
                            <div class="modal-body">
                                <form id="formularioCriarRegra">
                                    <div class="form-group">
                                        <label for="valuePerPoint">Valor por Ponto (R$) <span class="obrig">*</span></label>
                                        <input type="number" class="form-control" id="valuePerPoint" name="valuePerPoint" step="0.01" min="0.01" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="maxPointsConcurrent">Pontos Máximos Simultâneos <span class="obrig">*</span></label>
                                        <input type="number" class="form-control" id="maxPointsConcurrent" name="maxPointsConcurrent" min="1" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="durationPointMonths">Duração dos Pontos (Meses) <span class="obrig">*</span></label>
                                        <input type="number" class="form-control" id="durationPointMonths" name="durationPointMonths" min="1" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="analysisWindowMonths">Janela de Análise (Meses) <span class="obrig">*</span></label>
                                        <input type="number" class="form-control" id="analysisWindowMonths" name="analysisWindowMonths" min="1" required>
                                    </div>
                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" id="activeCriar" name="active" checked> Regra Ativa
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="btnSalvarNovaRegra">
                                    <i class="fa fa-save"></i> Salvar Regra
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Editar Regra -->
                <div class="modal fade" id="modalEditarRegra" tabindex="-1" role="dialog" aria-labelledby="modalEditarRegraLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-info">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="modalEditarRegraLabel">Editar Regra de Benefício</h4>
                            </div>
                            <div class="modal-body">
                                <form id="formularioEditarRegra">
                                    <input type="hidden" id="idRegra" name="id">
                                    <div class="form-group">
                                        <label for="valuePerPointEditar">Valor por Ponto (R$) <span class="obrig">*</span></label>
                                        <input type="number" class="form-control" id="valuePerPointEditar" name="valuePerPoint" step="0.01" min="0.01" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="maxPointsConcurrentEditar">Pontos Máximos Simultâneos <span class="obrig">*</span></label>
                                        <input type="number" class="form-control" id="maxPointsConcurrentEditar" name="maxPointsConcurrent" min="1" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="durationPointMonthsEditar">Duração dos Pontos (Meses) <span class="obrig">*</span></label>
                                        <input type="number" class="form-control" id="durationPointMonthsEditar" name="durationPointMonths" min="1" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="analysisWindowMonthsEditar">Janela de Análise (Meses) <span class="obrig">*</span></label>
                                        <input type="number" class="form-control" id="analysisWindowMonthsEditar" name="analysisWindowMonths" min="1" required>
                                    </div>
                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" id="activeEditar" name="active"> Regra Ativa
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-info" id="btnAtualizarRegra">
                                    <i class="fa fa-refresh"></i> Atualizar Regra
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
                                <p>Tem certeza que deseja deletar esta regra de benefício? <strong>Esta ação não pode ser desfeita.</strong></p>
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
                    let regraParaDeletar = null;

                    $(document).ready(function() {
                        carregarRegras();

                        // Evento: Salvar nova regra
                        $('#btnSalvarNovaRegra').click(function() {
                            const dados = {
                                nomeClasse: 'SocioBenefitControle',
                                metodo: 'createBenefitRule',
                                valuePerPoint: parseFloat($('#valuePerPoint').val()),
                                maxPointsConcurrent: parseInt($('#maxPointsConcurrent').val()),
                                durationPointMonths: parseInt($('#durationPointMonths').val()),
                                analysisWindowMonths: parseInt($('#analysisWindowMonths').val()),
                                active: $('#activeCriar').is(':checked')
                            };

                            requisicaoAjax(dados, function() {
                                $('#modalCriarRegra').modal('hide');
                                $('#formularioCriarRegra')[0].reset();
                                carregarRegras();
                                mostrarNotificacao('Regra criada com sucesso!', 'success', 3000);
                            });
                        });

                        // Evento: Atualizar regra
                        $('#btnAtualizarRegra').click(function() {
                            const dados = {
                                nomeClasse: 'SocioBenefitControle',
                                metodo: 'updateBenefitRule',
                                id: parseInt($('#idRegra').val()),
                                valuePerPoint: parseFloat($('#valuePerPointEditar').val()),
                                maxPointsConcurrent: parseInt($('#maxPointsConcurrentEditar').val()),
                                durationPointMonths: parseInt($('#durationPointMonthsEditar').val()),
                                analysisWindowMonths: parseInt($('#analysisWindowMonthsEditar').val()),
                                active: $('#activeEditar').is(':checked')
                            };

                            requisicaoAjax(dados, function() {
                                $('#modalEditarRegra').modal('hide');
                                carregarRegras();
                                mostrarNotificacao('Regra atualizada com sucesso!', 'success', 3000);
                            });
                        });

                        // Evento: Confirmar deleção
                        $('#btnConfirmarDelecao').click(function() {
                            if (regraParaDeletar) {
                                const dados = {
                                    nomeClasse: 'SocioBenefitControle',
                                    metodo: 'deleteBenefitRule',
                                    id: regraParaDeletar
                                };

                                requisicaoAjax(dados, function() {
                                    $('#modalConfirmarDelecao').modal('hide');
                                    regraParaDeletar = null;
                                    carregarRegras();
                                    mostrarNotificacao('Regra deletada com sucesso!', 'success', 3000);
                                });
                            }
                        });
                    });

                    function carregarRegras() {
                        $.ajax({
                            type: 'POST',
                            url: '<?php echo WWW; ?>controle/control.php',
                            contentType: 'application/json',
                            data: JSON.stringify({
                                nomeClasse: 'SocioBenefitControle',
                                metodo: 'getBenefitRules'
                            }),
                            dataType: 'json',
                            success: function(data) {
                                renderizarTabela(data);
                            },
                            error: function(xhr) {
                                const response = xhr.responseJSON || {};
                                
                                // Trata caso especial: nenhuma regra encontrada (não é erro, é informação)
                                if (response.error === 'Nenhuma regra de benefício encontrada.') {
                                    $('#corpoTabela').html('<tr><td colspan="7" class="text-center text-muted"><i class="fa fa-info-circle"></i> ' + response.error + '</td></tr>');
                                } else {
                                    mostrarNotificacao(response.error || 'Erro ao carregar regras', 'error');
                                    $('#corpoTabela').html('<tr><td colspan="7" class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> Erro ao carregar regras</td></tr>');
                                }
                            }
                        });
                    }

                    function renderizarTabela(regras) {
                        let html = '';

                        if (!Array.isArray(regras) || regras.length === 0) {
                            html = '<tr><td colspan="7" class="text-center text-muted">Nenhuma regra encontrada</td></tr>';
                        } else {
                            regras.forEach(function(regra, index) {
                                const statusBadge = regra.active ? 
                                    '<span class="label label-success" style="font-size: 13px; padding: 6px 10px;">Ativa</span>' : 
                                    '<span class="label label-danger" style="font-size: 13px; padding: 6px 10px;">Inativa</span>';
                                
                                const botaoToggleStatus = regra.active ?
                                    `<button class="btn btn-sm btn-warning" onclick="alternarStatus(${regra.id}, false)" title="Desativar"><i class="fa fa-toggle-on"></i> Desativar</button>` :
                                    `<button class="btn btn-sm btn-success" onclick="alternarStatus(${regra.id}, true)" title="Ativar"><i class="fa fa-toggle-off"></i> Ativar</button>`;

                                html += `
                                    <tr>
                                        <td class="text-center">${regra.id}</td>
                                        <td class="text-center">R$ ${parseFloat(regra.valuePerPoint).toFixed(2)}</td>
                                        <td class="text-center">${regra.maxPointsConcurrent}</td>
                                        <td class="text-center">${regra.durationPointMonths}</td>
                                        <td class="text-center">${regra.analysisWindowMonths}</td>
                                        <td class="text-center">${statusBadge}</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-info" onclick="editarRegra(${regra.id})" title="Editar"><i class="fa fa-edit"></i></button>
                                            ${botaoToggleStatus}
                                            <button class="btn btn-sm btn-danger" onclick="confirmarDelecao(${regra.id})" title="Deletar"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                `;
                            });
                        }

                        $('#corpoTabela').html(html);
                    }

                    function editarRegra(id) {
                        $.ajax({
                            type: 'POST',
                            url: '<?php echo WWW; ?>controle/control.php',
                            contentType: 'application/json',
                            data: JSON.stringify({
                                nomeClasse: 'SocioBenefitControle',
                                metodo: 'getBenefitRules'
                            }),
                            dataType: 'json',
                            success: function(regras) {
                                const regra = regras.find(r => r.id === id);
                                if (regra) {
                                    $('#idRegra').val(regra.id);
                                    $('#valuePerPointEditar').val(regra.valuePerPoint);
                                    $('#maxPointsConcurrentEditar').val(regra.maxPointsConcurrent);
                                    $('#durationPointMonthsEditar').val(regra.durationPointMonths);
                                    $('#analysisWindowMonthsEditar').val(regra.analysisWindowMonths);
                                    $('#activeEditar').prop('checked', regra.active);
                                    $('#modalEditarRegra').modal('show');
                                }
                            }
                        });
                    }

                    function alternarStatus(id, ativar) {
                        const metodo = ativar ? 'activateBenefitRule' : 'deactivateBenefitRule';
                        const dados = {
                            nomeClasse: 'SocioBenefitControle',
                            metodo: metodo,
                            id: id
                        };

                        requisicaoAjax(dados, function() {
                            carregarRegras();
                            const msg = ativar ? 'Regra ativada com sucesso!' : 'Regra desativada com sucesso!';
                            mostrarNotificacao(msg, 'success', 3000);
                        });
                    }

                    function confirmarDelecao(id) {
                        regraParaDeletar = id;
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