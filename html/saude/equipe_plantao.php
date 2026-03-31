<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}

session_regenerate_id();

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'personalizacao_display.php';

permissao($_SESSION['id_pessoa'], 5, 5);

$mesAtual = (int) date('m');
$anoAtual = (int) date('Y');
?>
<!doctype html>
<html class="fixed">
<head>
    <meta charset="UTF-8">
    <title>Gestao de Plantao</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
    <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
    <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" />
    <link rel="stylesheet" href="./style/equipe_plantao.css" />

    <link rel="icon" href="<?php display_campo('Logo', 'file'); ?>" type="image/x-icon" id="logo-icon">

    <script src="../../assets/vendor/modernizr/modernizr.js"></script>
    <script src="../../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.global.min.js"></script>

    <script>
        $(function() {
            $("#header").load("../header.php");
            $(".menuu").load("../menu.php");
        });

        window.plantaoConfig = {
            mesAtual: <?php echo $mesAtual; ?>,
            anoAtual: <?php echo $anoAtual; ?>,
            endpoint: '../../controle/control.php',
            nomeClasse: 'SaudeEquipePlantaoControle'
        };
    </script>
</head>
<body>
<section class="body">
    <div id="header"></div>

    <div class="inner-wrapper">
        <aside id="sidebar-left" class="sidebar-left menuu"></aside>

        <section role="main" class="content-body">
            <header class="page-header">
                <h2>Gestao de Plantao</h2>
                <div class="right-wrapper pull-right">
                    <ol class="breadcrumbs">
                        <li><a href="../index.php"><i class="fa fa-home"></i></a></li>
                        <li><span>Modulo Saude</span></li>
                        <li><span>Equipe de Plantao</span></li>
                    </ol>
                    <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                </div>
            </header>

            <div id="globalMessage" class="alert alert-info alert-inline" role="alert"></div>

            <section class="panel painel-main">
                <header class="panel-heading">
                    <h2 class="panel-title">Escala Mensal - Operacao 12x36 por turno</h2>
                </header>
                <div class="panel-body">
                    <div class="page-actions">
                        <div class="form-group">
                            <label for="filtroMes">Mes</label>
                            <select id="filtroMes" class="form-control"></select>
                        </div>
                        <div class="form-group">
                            <label for="filtroAno">Ano</label>
                            <select id="filtroAno" class="form-control"></select>
                        </div>
                        <button id="btnCarregar" class="btn btn-default btn-sm" type="button"><i class="fa fa-refresh"></i> Descartar edicao</button>
                        <button id="btnEditarEscala" class="btn btn-default btn-sm" type="button" disabled><i class="fa fa-pencil"></i> Editar escala</button>
                        <button id="btnApagarEscala" class="btn btn-default btn-sm" type="button" disabled><i class="fa fa-trash"></i> Apagar escala</button>
                        <button id="btnSalvarEscala" class="btn btn-primary btn-sm" type="button"><i class="fa fa-save"></i> Salvar escala</button>
                        <button id="btnVisualizarImpressao" class="btn btn-warning btn-sm" type="button"><i class="fa fa-search"></i> Visualizar impressao</button>
                        <button id="btnImprimirDireto" class="btn btn-warning btn-sm" type="button"><i class="fa fa-print"></i> Imprimir</button>
                    </div>

                    <section class="schedule-toolbar">
                        <div class="toolbar-block">
                            <div class="toolbar-label">Selecionar escala</div>
                            <div class="toolbar-controls">
                                <div class="toolbar-field toolbar-field-sm">
                                    <label class="toolbar-field-label" for="loteTurno">Turno</label>
                                    <select id="loteTurno" class="form-control input-sm toolbar-mini-select">
                                        <option value="DIA">Dia 07:00-19:00</option>
                                        <option value="NOITE">Noite 19:00-07:00</option>
                                    </select>
                                </div>
                                <div class="toolbar-field">
                                    <label class="toolbar-field-label" for="loteEquipe">Equipe para aplicar</label>
                                    <select id="loteEquipe" class="form-control input-sm toolbar-select"></select>
                                </div>
                                <div class="toolbar-actions">
                                    <button id="btnAplicarEquipeSelecionados" class="btn btn-primary btn-sm" type="button">Aplicar</button>
                                    <button id="btnLimparEquipeSelecionados" class="btn btn-default btn-sm" type="button">Limpar equipe</button>
                                    <button id="btnLimparSelecao" class="btn btn-default btn-sm" type="button">Limpar selecao</button>
                                    <button id="btnAbrirDiaSelecionado" class="btn btn-default btn-sm" type="button">Editar dia</button>
                                </div>
                            </div>
                            <div class="small-help">1. Escolha o turno. 2. Selecione um ou mais dias no calendario. 3. Aplique a equipe ou abra a edicao do plantao.</div>
                        </div>
                        <div class="toolbar-divider"></div>
                        <div class="toolbar-block">
                            <div class="toolbar-label">Gerar dinamicamente escala</div>
                            <div class="toolbar-controls">
                                <div class="toolbar-field toolbar-field-sm">
                                    <label class="toolbar-field-label" for="escala12x36Turno">Turno</label>
                                    <select id="escala12x36Turno" class="form-control input-sm toolbar-mini-select">
                                        <option value="DIA">Dia 07:00-19:00</option>
                                        <option value="NOITE">Noite 19:00-07:00</option>
                                    </select>
                                </div>
                                <div class="toolbar-field toolbar-field-sm">
                                    <label class="toolbar-field-label" for="escala12x36DiaInicial">Dia inicial</label>
                                    <select id="escala12x36DiaInicial" class="form-control input-sm toolbar-mini-select"></select>
                                </div>
                                <div class="toolbar-field">
                                    <label class="toolbar-field-label" for="escala12x36EquipeA">Equipe do primeiro plantao</label>
                                    <select id="escala12x36EquipeA" class="form-control input-sm toolbar-select"></select>
                                </div>
                                <div class="toolbar-field">
                                    <label class="toolbar-field-label" for="escala12x36EquipeB">Equipe alternada</label>
                                    <select id="escala12x36EquipeB" class="form-control input-sm toolbar-select"></select>
                                </div>
                                <div class="toolbar-actions">
                                    <button id="btnGerar12x36" class="btn btn-info btn-sm" type="button">Gerar 12x36</button>
                                    <button id="btnDesfazerLocal" class="btn btn-default btn-sm" type="button">Desfazer</button>
                                </div>
                            </div>
                            <div class="small-help">Escolha o turno, informe de qual dia a alternancia comeca e defina as duas equipes da sequencia 12x36.</div>
                        </div>
                    </section>

                    <div class="row">
                        <div class="col-md-12">
                            <div id="plantaoCalendar"></div>
                        </div>
                    </div>

                    <div class="row secondary-panels">
                        <div class="col-md-12">
                            <section class="panel painel-side">
                                <header class="panel-heading">
                                    <h3 class="panel-title">Equipes cadastradas</h3>
                                </header>
                                <div class="panel-body">
                                    <button id="btnNovaEquipe" class="btn btn-success btn-sm" type="button"><i class="fa fa-plus"></i> Nova equipe</button>
                                    <div id="listaEquipesContainer" style="margin-top: 8px;"></div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </section>
        </section>
    </div>
</section>

<div class="modal fade" id="modalEquipePlantao" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="tituloModalEquipe">Cadastro de equipe</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="equipeId">
                <div class="row">
                    <div class="col-md-7">
                        <div class="form-group">
                            <label for="equipeNome">Nome da equipe</label>
                            <input type="text" id="equipeNome" class="form-control" maxlength="120">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="checkbox" style="margin-top: 28px;">
                            <label>
                                <input type="checkbox" id="equipeAtiva" checked> Equipe ativa
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="equipeDescricao">Descricao</label>
                    <textarea id="equipeDescricao" class="form-control" rows="2" maxlength="255"></textarea>
                </div>
                <div class="form-group">
                    <label>Tecnicos fixos da equipe</label>
                    <div id="checkTecnicosEquipe" class="well" style="max-height: 260px; overflow: auto; margin-bottom: 0;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarEquipeModal">Salvar equipe</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDiaPlantao" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="tituloModalDia">Edicao do dia</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalDiaNumero">
                <div class="row">
                    <div class="col-md-3">
                        <label>Data</label>
                        <input id="modalDiaData" class="form-control" readonly>
                        <div id="statusDiaPersistencia" class="modal-day-status"></div>
                    </div>
                    <div class="col-md-3">
                        <label for="modalDiaTurno">Turno</label>
                        <select id="modalDiaTurno" class="form-control">
                            <option value="DIA">Plantao do dia 07:00-19:00</option>
                            <option value="NOITE">Plantao da noite 19:00-07:00</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="modalDiaEquipe">Equipe do turno</label>
                        <select id="modalDiaEquipe" class="form-control"></select>
                    </div>
                    <div class="col-md-3">
                        <label for="modalDiaObs">Observacao</label>
                        <input id="modalDiaObs" class="form-control" maxlength="255" placeholder="Opcional">
                    </div>
                </div>

                <div class="row" style="margin-top: 10px;">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-primary btn-sm" id="btnAplicarDiaLocal">Aplicar alteracao local</button>
                        <button type="button" class="btn btn-default btn-sm" id="btnLimparDiaLocal">Remover equipe do dia</button>
                        <button type="button" class="btn btn-info btn-sm" id="btnSalvarDiaAgora">Salvar dia agora no banco</button>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Membros fixos:</strong></p>
                        <ul id="listaMembrosFixos" class="list-plain"></ul>

                        <p style="margin-top: 10px;"><strong>Membros finais do plantao:</strong></p>
                        <ul id="listaMembrosPlantao" class="list-plain"></ul>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Adicionados no dia:</strong></p>
                        <ul id="listaAdicionadosDia" class="list-plain"></ul>

                        <p style="margin-top: 10px;"><strong>Removidos no dia:</strong></p>
                        <ul id="listaRemovidosDia" class="list-plain"></ul>
                    </div>
                </div>

                <hr>

                <p><strong>Ajuste dinamico de membro no turno</strong></p>
                <div class="row">
                    <div class="col-md-5">
                        <label for="ajusteTecnico">Tecnico</label>
                        <select id="ajusteTecnico" class="form-control"></select>
                    </div>
                    <div class="col-md-3">
                        <label for="ajusteTipo">Acao</label>
                        <select id="ajusteTipo" class="form-control">
                            <option value="ADICIONAR">Adicionar</option>
                            <option value="REMOVER">Remover</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="ajusteObservacao">Observacao</label>
                        <input id="ajusteObservacao" class="form-control" maxlength="255" placeholder="Opcional">
                    </div>
                </div>
                <div class="row" style="margin-top: 8px;">
                    <div class="col-md-12">
                        <button class="btn btn-primary btn-sm" type="button" id="btnSalvarAjusteDia">Salvar ajuste</button>
                        <button class="btn btn-default btn-sm" type="button" id="btnRemoverAjusteDia">Remover ajuste do tecnico</button>
                    </div>
                </div>

                <hr>

                <p><strong>Historico de alteracoes</strong></p>
                <div class="log-table">
                    <table class="table table-bordered table-condensed">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Acao</th>
                                <th>Usuario</th>
                                <th>Descricao</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaLogsDia"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script src="../../assets/javascripts/theme.js"></script>
<script src="../../assets/javascripts/theme.custom.js"></script>
<script src="../../assets/javascripts/theme.init.js"></script>
<script src="./script/equipe_plantao.js"></script>

<div align="right">
    <iframe src="https://www.wegia.org/software/footer/saude.html" width="200" height="60" style="border:none;"></iframe>
</div>
</body>
</html>
