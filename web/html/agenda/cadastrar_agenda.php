<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
Util::definirFusoHorario();
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit();
} else {
    session_regenerate_id();
}

// require_once "../permissao/permissao.php";
// permissao($_SESSION['id_pessoa'], 103);

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once "../personalizacao_display.php";
?>
<!doctype html>
<html class="fixed">

<head>
    <meta charset="UTF-8">
    <title>Agenda</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="../../assets/vendor/select2/select2.css" />
    <link rel="stylesheet" href="../../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
    <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
    <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
    <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
    <link rel="icon" href='<?php display_campo("Logo", 'file'); ?>' type="image/x-icon">
    <script src="../../assets/vendor/modernizr/modernizr.js"></script>

    <style>
        /* ── FullCalendar ── */
        :root {
            --fc-border-color: #e4e9ef;
            --fc-page-bg-color: #fff;
            --fc-today-bg-color: rgba(0, 136, 204, 0.07);
            --fc-button-bg-color: #0088cc;
            --fc-button-border-color: #007ab8;
            --fc-button-hover-bg-color: #007ab8;
            --fc-button-hover-border-color: #006fa3;
            --fc-button-active-bg-color: #006fa3;
            --fc-button-active-border-color: #005c87;
            --fc-event-bg-color: #0088cc;
            --fc-event-border-color: #007ab8;
            --fc-event-text-color: #fff;
            --fc-list-event-hover-bg-color: #f0f8ff;
            --fc-highlight-color: rgba(0, 136, 204, 0.12);
        }
        .fc .fc-toolbar-title { font-weight: 600; color: #2d3a4a; }
        .fc .fc-button {
            border-radius: 4px; font-weight: 600; padding: 6px 14px;
            text-transform: uppercase; letter-spacing: 0.04em;
            box-shadow: none !important;
            transition: background-color 0.15s, border-color 0.15s;
        }
        .fc .fc-button:focus { outline: none; box-shadow: 0 0 0 3px rgba(0,136,204,.25) !important; }
        .fc .fc-col-header-cell-cushion { font-weight: 700; text-transform: uppercase; color: #607080; letter-spacing: .05em; padding: 8px 4px; }
        .fc .fc-daygrid-day-number { color: #2d3a4a; font-weight: 500; padding: 6px 8px; }
        .fc .fc-day-today .fc-daygrid-day-number {
            background-color: #0088cc; color: #fff; border-radius: 50%;
            width: 26px; height: 26px; display: flex; align-items: center;
            justify-content: center; padding: 0; margin: 4px 6px;
        }
        .fc-event { cursor: pointer; border-radius: 4px !important; font-weight: 500 !important; padding: 2px 6px !important; border-left: 3px solid rgba(0,0,0,.15) !important; transition: filter .15s; }
        .fc-event:hover { filter: brightness(.92); }
        .fc .fc-daygrid-day-frame { min-height: 100px; }
        .fc .fc-scrollgrid { border-radius: 6px; overflow: hidden; }

        /* ── Abas ── */
        .nav-tabs > li > a { font-weight: 600; color: #607080; border-radius: 6px 6px 0 0; }
        .nav-tabs > li.active > a,
        .nav-tabs > li.active > a:focus,
        .nav-tabs > li.active > a:hover { color: #0088cc; border-top: 3px solid #0088cc; }

        /* ── Tabelas ── */
        .table thead th {
            background-color: #f0f4f8;
            color: #2d3a4a;
            font-weight: 700;
            letter-spacing: .03em;
            border-bottom: 2px solid #d0dbe7;
            white-space: nowrap;
            padding: 12px 14px;
        }
        .table tbody td {
            padding: 11px 14px;
            vertical-align: middle;
            color: #2d3a4a;
        }
        .table tbody tr:hover { background-color: #f0f7ff; }
        .table-striped > tbody > tr:nth-of-type(odd) { background-color: #fafcff; }

        /* DataTables overrides */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            padding: 4px 8px;
            border: 1px solid #d0dbe7;
            border-radius: 4px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #0088cc !important;
            border-color: #007ab8 !important;
            color: #fff !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #e8f4fb !important;
            border-color: #b3d9f0 !important;
            color: #0088cc !important;
        }

        /* Badges */
        .badge-ativo, .badge-inativo { font-size: 0.88rem; padding: 6px 14px; }
        .badge-ativo   { background-color: #27ae60; }
        .badge-inativo { background-color: #95a5a6; }
        .badge-membro {
            display: inline-block; background-color: #eaf4fb; color: #0088cc;
            border: 1px solid #b3d9f0; border-radius: 20px; padding: 3px 10px;
            font-size: 0.8rem; font-weight: 600; margin: 2px 3px 2px 0; white-space: nowrap;
        }
        .membros-cell { line-height: 2; }
        .col-status, .col-acoes { text-align: center !important; vertical-align: middle !important; }
        .btn-acao {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            padding: 0;
            border-radius: 5px;
            font-size: 13px;
            margin: 0 2px;
        }
        .acoes-grupo { display: flex; align-items: center; justify-content: center; gap: 4px; }

        /* Select2 dentro de modal */
        .select2-container { width: 100% !important; }
        .select2-container .select2-selection--single {
            height: 34px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 34px;
            color: #555;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px;
        }

        /* ── Padrão de modal do sistema ── */
        .modal-header-padrao {
            background-color: #337ab7;
            border-bottom-color: #2e6da4;
        }
        .modal-header-padrao .modal-title {
            font-weight: 500;
            color: #fff;
        }
        .modal-header-padrao .close,
        .modal-header-padrao .close span {
            color: #fff;
            opacity: 1;
            text-shadow: none;
        }
        .modal-header-padrao .close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            margin-top: -6px;
            border-radius: 999px;
            background-color: transparent;
            filter: brightness(1);
            transition: background-color 0.18s ease, filter 0.18s ease;
        }
        .modal-header-padrao .close:hover,
        .modal-header-padrao .close:focus {
            background-color: rgba(255, 255, 255, 0.1);
            filter: brightness(1.08);
            outline: none;
        }
        .modal-header-padrao .close:hover span,
        .modal-header-padrao .close:focus span {
            filter: brightness(1.08);
        }

        /* ── Painel de membros inline ── */
        .membros-panel {
            background: #f8fbff; border: 1px solid #e4e9ef;
            border-radius: 6px; padding: 16px; margin-top: 8px;
        }

        
        /* ── Toolbar acima do calendário (agenda + equipes arrastáveis) ── */
        .cal-toolbar {
            display: flex; align-items: center; flex-wrap: wrap; gap: 12px;
            background: #f8fbff; border: 1px solid #e4e9ef;
            border-radius: 6px; padding: 10px 16px; margin-bottom: 14px;
        }
        .cal-toolbar-group { display: flex; align-items: center; gap: 8px; }
        .cal-toolbar-equipes { flex: 1; }
        .cal-toolbar-label {
            font-weight: 700; font-size: 1rem; text-transform: uppercase;
            letter-spacing: .06em; color: #607080; white-space: nowrap;
        }
        .cal-toolbar-select { width: 180px !important; }
        .cal-toolbar-divider { width: 1px; height: 28px; background: #d0dbe7; margin: 0 4px; flex-shrink: 0; }
        .cal-sidebar-hint { font-size: 1rem; color: #8fa0b0; white-space: nowrap; }
        #sidebar-equipes-lista { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
        .equipe-card {
            border-radius: 5px; padding: 6px 10px; margin-bottom: 0;
            cursor: grab; color: #fff; font-weight: 600; font-size: 0.82rem;
            user-select: none; display: flex; align-items: center; gap: 6px;
            box-shadow: 0 1px 4px rgba(0,0,0,.14);
            transition: filter .15s, box-shadow .15s;
        }
        .equipe-card:hover  { filter: brightness(.88); box-shadow: 0 3px 8px rgba(0,0,0,.2); }
        .equipe-card:active { cursor: grabbing; }

    </style>
</head>

<body>
<section class="body">
    <div id="header"></div>
    <div class="inner-wrapper">
        <aside id="sidebar-left" class="sidebar-left menuu"></aside>

        <section role="main" class="content-body">
            <header class="page-header">
                <h2>Agenda</h2>
                <div class="right-wrapper pull-right">
                    <ol class="breadcrumbs">
                        <li><a href="../home.php"><i class="fa fa-home"></i></a></li>
                        <li><span>Agenda</span></li>
                    </ol>
                    <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                </div>
            </header>

            <div class="row">
                <div class="col-md-12">
                    <section class="panel">
                        <header class="panel-heading">
                            <h2 class="panel-title">Gerenciamento de Agenda</h2>
                        </header>
                        <div class="panel-body">

                            <ul class="nav nav-tabs" id="abas-agenda">
                                <li class="active"><a href="#tab-calendario" data-toggle="tab"><i class="fa fa-calendar mr-xs"></i> Calendário</a></li>
                                <li><a href="#tab-agendas"    data-toggle="tab"><i class="fa fa-list mr-xs"></i> Agendas</a></li>
                                <li><a href="#tab-equipes"   data-toggle="tab"><i class="fa fa-users mr-xs"></i> Equipes</a></li>
                                <li><a href="#tab-alocacoes" data-toggle="tab"><i class="fa fa-clock-o mr-xs"></i> Alocações</a></li>
                            </ul>

                            <div class="tab-content" style="padding-top:20px;">

                                <!-------- CALENDÁRIO -------->
                                <div class="tab-pane active" id="tab-calendario">

                                    <div id="msg-calendario" class="alert alert-success alert-dismissible" role="alert" style="display:none;">
                                        <button type="button" class="close" aria-label="Fechar" onclick="ocultarMsg('msg-calendario'); return false;">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <span id="msg-calendario-texto"></span>
                                    </div>

                                    <div class="cal-toolbar">
                                        <div class="cal-toolbar-group">
                                            <span class="cal-toolbar-label"><i class="fa fa-list-alt mr-xs"></i>Agenda</span>
                                            <select class="form-control input-sm cal-toolbar-select" id="sidebar-agenda-select">
                                                <option value="">Selecione...</option>
                                            </select>
                                        </div>
                                        <div class="cal-toolbar-divider"></div>
                                        <div class="cal-toolbar-group cal-toolbar-equipes">
                                            <span class="cal-toolbar-label"><i class="fa fa-users mr-xs"></i>Equipes</span>
                                            <div id="sidebar-equipes-lista">
                                                <span class="text-muted" style="font-size:.8rem;">Carregando...</span>
                                            </div>
                                            <span class="cal-sidebar-hint">arraste para o calendário</span>
                                        </div>
                                    </div>
                                    <div id="calendar"></div>
                                </div>

                                <!-------- AGENDAS -------->
                                <div class="tab-pane" id="tab-agendas">

                                    <div id="msg-agendas" class="alert alert-success alert-dismissible" role="alert" style="display:none;">
                                        <button type="button" class="close" aria-label="Fechar" onclick="ocultarMsg('msg-agendas'); return false;">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <span id="msg-agendas-texto"></span>
                                    </div>

                                    <div class="clearfix mb-md">
                                        <button class="btn btn-primary pull-right" id="btn-nova-agenda">
                                            <i class="fa fa-plus mr-xs"></i> Nova Agenda
                                        </button>
                                    </div>
                                    <table class="table table-bordered table-striped table-hover mb-none" id="dt-agendas">
                                        <thead>
                                            <tr>
                                                <th>Descrição</th>
                                                <th style="width:110px;" class="col-status">Status</th>
                                                <th style="width:100px;" class="col-acoes">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-agendas"></tbody>
                                    </table>
                                </div>

                                <!-------- EQUIPES -------->
                                <div class="tab-pane" id="tab-equipes">

                                    <div id="msg-equipes" class="alert alert-success alert-dismissible" role="alert" style="display:none;">
                                        <button type="button" class="close" aria-label="Fechar" onclick="ocultarMsg('msg-equipes'); return false;">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <span id="msg-equipes-texto"></span>
                                    </div>

                                    <div class="clearfix mb-md">
                                        <button class="btn btn-primary pull-right" id="btn-nova-equipe">
                                            <i class="fa fa-plus mr-xs"></i> Nova Equipe
                                        </button>
                                    </div>
                                    <table class="table table-bordered table-striped table-hover mb-none" id="dt-equipes">
                                        <thead>
                                            <tr>
                                                <th style="width:180px;">Nome</th>
                                                <th>Descrição</th>
                                                <th>Membros</th>
                                                <th style="width:100px;" class="col-status">Status</th>
                                                <th style="width:130px;" class="col-acoes">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-equipes"></tbody>
                                    </table>
                                </div>

                                <!-------- ALOCAÇÕES -------->
                                <div class="tab-pane" id="tab-alocacoes">

                                    <div id="msg-alocacoes" class="alert alert-success alert-dismissible" role="alert" style="display:none;">
                                        <button type="button" class="close" aria-label="Fechar" onclick="ocultarMsg('msg-alocacoes'); return false;">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <span id="msg-alocacoes-texto"></span>
                                    </div>

                                    <div class="clearfix mb-md">
                                        <button class="btn btn-primary pull-right" id="btn-nova-alocacao">
                                            <i class="fa fa-plus mr-xs"></i> Nova Alocação
                                        </button>
                                    </div>
                                    <table class="table table-bordered table-striped table-hover mb-none" id="dt-alocacoes">
                                        <thead>
                                            <tr>
                                                <th>Agenda</th>
                                                <th>Equipe</th>
                                                <th style="width:120px;">Data início</th>
                                                <th style="width:120px;">Data fim</th>
                                                <th style="width:150px;">Lembrete</th>
                                                <th style="width:100px;" class="col-acoes">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-alocacoes"></tbody>
                                    </table>
                                </div>

                            </div><!-- /tab-content -->
                        </div><!-- /panel-body -->
                    </section>
                </div>
            </div>

        </section>
    </div>
</section>

<!-- ══════════════════════════════════════════
     MODAL — AGENDA
══════════════════════════════════════════ -->
<div class="modal fade" id="modal-agenda" tabindex="-1" role="dialog" aria-labelledby="modal-agenda-titulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-padrao">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-agenda-titulo">Nova Agenda</h4>
            </div>
            <div class="modal-body">
                <div id="modal-agenda-erro" class="alert alert-danger alert-dismissible" style="display:none;" role="alert">
                    <button type="button" class="close" aria-label="Fechar" onclick="ocultarErroModal('modal-agenda-erro'); return false;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <span id="modal-agenda-erro-texto"></span>
                </div>

                <input type="hidden" id="agenda-id">
                <div class="form-group">
                    <label class="control-label">Descrição <sup class="text-danger">*</sup></label>
                    <input type="text" class="form-control" id="agenda-descricao" maxlength="255">
                </div>
                <div class="form-group">
                    <label class="control-label">Status <sup class="text-danger">*</sup></label>
                    <select class="form-control" id="agenda-status"></select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-salvar-agenda">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     MODAL — EQUIPE
══════════════════════════════════════════ -->
<div class="modal fade" id="modal-equipe" tabindex="-1" role="dialog" aria-labelledby="modal-equipe-titulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-padrao">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-equipe-titulo">Nova Equipe</h4>
            </div>
            <div class="modal-body">
                <div id="modal-equipe-erro" class="alert alert-danger alert-dismissible" style="display:none;" role="alert">
                    <button type="button" class="close" aria-label="Fechar" onclick="ocultarErroModal('modal-equipe-erro'); return false;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <span id="modal-equipe-erro-texto"></span>
                </div>

                <input type="hidden" id="equipe-id">
                <div class="form-group">
                    <label class="control-label">Nome <sup class="text-danger">*</sup></label>
                    <input type="text" class="form-control" id="equipe-nome" maxlength="100">
                </div>
                <div class="form-group">
                    <label class="control-label">Descrição</label>
                    <textarea class="form-control" id="equipe-descricao" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label class="control-label">Status <sup class="text-danger">*</sup></label>
                    <select class="form-control" id="equipe-status"></select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-salvar-equipe">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     MODAL — MEMBROS
══════════════════════════════════════════ -->
<div class="modal fade" id="modal-membros" tabindex="-1" role="dialog" aria-labelledby="modal-membros-titulo" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-padrao">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-membros-titulo">Membros da Equipe: <span id="membros-equipe-nome"></span></h4>
            </div>
            <div class="modal-body">
                <div id="modal-membros-erro" class="alert alert-danger alert-dismissible" style="display:none;" role="alert">
                    <button type="button" class="close" aria-label="Fechar" onclick="ocultarErroModal('modal-membros-erro'); return false;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <span id="modal-membros-erro-texto"></span>
                </div>

                <div id="modal-membros-sucesso" class="alert alert-success alert-dismissible" style="display:none;" role="alert">
                    <button type="button" class="close" aria-label="Fechar" onclick="ocultarErroModal('modal-membros-sucesso'); return false;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <span id="modal-membros-sucesso-texto"></span>
                </div>

                <input type="hidden" id="membros-equipe-id">

                <div class="membros-panel mb-md">
                    <h5 style="margin-top:0; font-weight:700; color:#2d3a4a;">Adicionar Membro</h5>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label">Pessoa <sup class="text-danger">*</sup></label>
                                <select class="form-control" id="membro-pessoa"></select>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label">Início do turno <sup class="text-danger">*</sup></label>
                                <input type="time" class="form-control" id="membro-inicio">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label">Fim do turno <sup class="text-danger">*</sup></label>
                                <input type="time" class="form-control" id="membro-fim">
                            </div>
                        </div>
                        <div class="col-sm-2" style="padding-top:25px;">
                            <button class="btn btn-success btn-block" id="btn-adicionar-membro">
                                <i class="fa fa-plus"></i> Adicionar
                            </button>
                        </div>
                    </div>
                </div>

                <table class="table table-bordered table-hover table-condensed">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Início turno</th>
                            <th>Fim turno</th>
                            <th style="width:100px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-membros">
                        <tr><td colspan="4" class="text-center text-muted">Nenhum membro ativo.</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     MODAL — ALOCAÇÃO
══════════════════════════════════════════ -->
<div class="modal fade" id="modal-alocacao" tabindex="-1" role="dialog" aria-labelledby="modal-alocacao-titulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-padrao">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-alocacao-titulo">Nova Alocação</h4>
            </div>
            <div class="modal-body">
                <div id="modal-alocacao-erro" class="alert alert-danger alert-dismissible" style="display:none;" role="alert">
                    <button type="button" class="close" aria-label="Fechar" onclick="ocultarErroModal('modal-alocacao-erro'); return false;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <span id="modal-alocacao-erro-texto"></span>
                </div>

                <input type="hidden" id="alocacao-id">
                <div class="form-group">
                    <label class="control-label">Agenda <sup class="text-danger">*</sup></label>
                    <select class="form-control" id="alocacao-agenda"></select>
                </div>
                <div class="form-group">
                    <label class="control-label">Equipe <sup class="text-danger">*</sup></label>
                    <select class="form-control" id="alocacao-equipe"></select>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label">Data de início <sup class="text-danger">*</sup></label>
                            <input type="date" class="form-control" id="alocacao-inicio">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label">Data de fim <sup class="text-danger">*</sup></label>
                            <input type="date" class="form-control" id="alocacao-fim">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label">Lembrete</label>
                    <input type="datetime-local" class="form-control" id="alocacao-lembrete">
                    <span class="help-block">Opcional. Data/hora para envio de lembrete.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-salvar-alocacao">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     MODAL — DETALHE EVENTO CALENDÁRIO
══════════════════════════════════════════ -->
<div class="modal fade" id="modal-evento" tabindex="-1" role="dialog" aria-labelledby="modal-evento-titulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-padrao">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-evento-titulo"></h4>
            </div>
            <div class="modal-body">
                <p><strong><i class="fa fa-calendar mr-xs"></i> Início:</strong> <span id="modal-evento-inicio"></span></p>
                <p><strong><i class="fa fa-calendar-check-o mr-xs"></i> Fim:</strong> <span id="modal-evento-fim"></span></p>
                <p><strong><i class="fa fa-users mr-xs"></i> Equipe:</strong> <span id="modal-evento-equipe"></span></p>
                <p id="modal-evento-lembrete-row"><strong><i class="fa fa-bell mr-xs"></i> Lembrete:</strong> <span id="modal-evento-lembrete"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast flutuante do calendário -->
<!-- Vendor -->
<script src="../../assets/vendor/jquery/jquery.min.js"></script>
<script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
<script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
<script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
<script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
<script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
<script src="../../assets/vendor/select2/select2.js"></script>
<script src="../../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
<script src="../../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
<script src="../../assets/javascripts/theme.js"></script>
<script src="../../assets/javascripts/theme.custom.js"></script>
<script src="../../assets/javascripts/theme.init.js"></script>
<script src="../../assets/vendor/fullcalendar/dist/index.global.min.js"></script>
<script src="../../assets/vendor/fullcalendar/packages/core/locales/pt-br.global.min.js"></script>

<script>
var API = '../../controle/control.php';

/* ── Helpers para calendar.addEvent() sem duplicatas ────────
   Eventos criados otimisticamente são rastreados em
   _calPendingEvents. _calRefetch() os remove antes de buscar
   do servidor, evitando duplicatas no calendário.           */
var _calPendingEvents = [];
function _pad(n) { return n < 10 ? '0' + n : '' + n; }
function _calAddEvent(data) {
    if (window._calendar) _calPendingEvents.push(window._calendar.addEvent(data));
}
function _calRefetch() {
    $.each(_calPendingEvents, function (_, e) { if (e) e.remove(); });
    _calPendingEvents = [];
    if (window._calendar) window._calendar.refetchEvents();
}

/* ── Sidebar de equipes (drag-and-drop externo) ──────────── */

var CORES_EQUIPE  = ['#337ab7','#5cb85c','#d9534f','#f0ad4e','#5bc0de','#777777'];
var _equipeCorMap  = {};   /* id_equipe → cor; fonte única para toolbar e calendário */
var _draggableInst = null;

function carregarSidebarAgendas() {
    api('listarAgendas').done(function (agendas) {
        var opts = '<option value="">Selecione uma agenda...</option>';
        $.each(agendas, function (_, a) {
            opts += '<option value="' + a.id + '">' + a.descricao + '</option>';
        });
        $('#sidebar-agenda-select').html(opts);
    });
}

function carregarSidebarEquipes() {
    api('listarEquipes').done(function (equipes) {
        _equipeCorMap = {};
        var html = '';
        $.each(equipes, function (idx, e) {
            var cor = CORES_EQUIPE[idx % CORES_EQUIPE.length];
            _equipeCorMap[String(e.id)] = cor;   /* registra a cor pelo id */
            html += '<div class="equipe-card" data-id="' + e.id
                  + '" data-nome="' + e.nome
                  + '" data-cor="' + cor
                  + '" style="background:' + cor + ';">'
                  + '<i class="fa fa-users"></i> ' + e.nome
                  + '</div>';
        });
        $('#sidebar-equipes-lista').html(
            html || '<p class="text-muted" style="font-size:0.9rem; display: flex; align-items: center;">Nenhuma equipe cadastrada.</p>'
        );
        if (!_draggableInst) {
            _draggableInst = new FullCalendar.Draggable(
                document.getElementById('sidebar-equipes-lista'),
                {
                    itemSelector: '.equipe-card',
                    eventData: function (cardEl) {
                        return {
                            title:         cardEl.getAttribute('data-nome'),
                            color:         cardEl.getAttribute('data-cor'),
                            extendedProps: { id_equipe: cardEl.getAttribute('data-id') }
                        };
                    }
                }
            );
        }
        /* Aplica cores nos eventos já renderizados (caso calendário carregou antes do mapa) */
        if (window._calendar) {
            $.each(window._calendar.getEvents(), function (_, evt) {
                var cor = _equipeCorMap[String(evt.extendedProps.id_equipe)];
                if (cor) evt.setProp('color', cor);
            });
        }
    });
}

$(function () {
    $("#header").load("../header.php");
    $(".menuu").load("../menu.php");
    dtInit('dt-agendas');
    dtInit('dt-equipes');
    dtInit('dt-alocacoes');
});

/* ── Utilitários ─────────────────────────────────────────── */

function api(metodo, params) {
    return $.get(API, $.extend({ metodo: metodo, nomeClasse: 'AgendaControle' }, params));
}

function apiPost(metodo, data) {
    return $.post(API, $.extend({ metodo: metodo, nomeClasse: 'AgendaControle' }, data));
}

function exibirMsgAba(idAba, texto, tipo) {
    tipo = tipo || 'success';
    var $a = $('#' + idAba);
    $a.removeClass('alert-success alert-danger alert-warning').addClass('alert-' + tipo);
    $('#' + idAba + '-texto').text(texto);
    $a.stop(true, true).show();
    clearTimeout($a.data('_timer'));
    $a.data('_timer', setTimeout(function () { $a.fadeOut(400); }, 10000));
}

function ocultarMsg(id) {
    $('#' + id).hide();
}

function exibirErroModal(idErro, texto) {
    $('#' + idErro + '-texto').text(texto);
    $('#' + idErro).show();
}

function ocultarErroModal(id) {
    $('#' + id).hide();
}

function confirmar(msg, cb) {
    if (window.confirm(msg)) cb();
}

function fmtDatetime(str) {
    if (!str) return '—';
    return new Date(str).toLocaleString('pt-BR', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });
}

function fmtDate(str) {
    if (!str) return '—';
    var p = str.substring(0, 10).split('-');
    return p[2] + '/' + p[1] + '/' + p[0];
}

function fmtTime(str) {
    if (!str) return '—';
    return str.substring(0, 5); /* HH:MM */
}

/* ── DataTables ──────────────────────────────────────────── */

var dtOpts = {
    language: {
        url: false,
        sEmptyTable:      'Nenhum registro encontrado',
        sInfo:            'Exibindo _START_ a _END_ de _TOTAL_ registros',
        sInfoEmpty:       'Exibindo 0 a 0 de 0 registros',
        sInfoFiltered:    '(filtrado de _MAX_ registros no total)',
        sLengthMenu:      'Exibir _MENU_ registros',
        sLoadingRecords:  'Carregando...',
        sProcessing:      'Processando...',
        sSearch:          'Buscar:',
        sZeroRecords:     'Nenhum registro encontrado',
        oPaginate: { sFirst:'Primeiro', sLast:'Último', sNext:'Próximo', sPrevious:'Anterior' }
    },
    pageLength: 10,
    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Todos']],
    autoWidth: false,
    responsive: true,
    columnDefs: [{ targets: 'no-sort', orderable: false }]
};

function dtInit(id) {
    var $t = $('#' + id);
    if ($.fn.DataTable.isDataTable($t)) {
        $t.DataTable().destroy();
    }
    $t.dataTable($.extend(true, {}, dtOpts));
}

/* ── Select2 ─────────────────────────────────────────────── */

function initSelect2(selector, placeholder) {
    var $el = $(selector);
    if ($el.hasClass('select2-hidden-accessible')) {
        $el.select2('destroy');
    }
    $el.select2({
        dropdownParent: $el.closest('.modal'),
        placeholder: placeholder || 'Selecione...',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function () { return 'Nenhum resultado encontrado'; },
            searching: function () { return 'Buscando...'; }
        }
    });
}

/* ── Calendário ──────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', function () {
    var cal = new FullCalendar.Calendar(document.getElementById('calendar'), {
        locale: 'pt-br',
        initialView: 'dayGridMonth',
        headerToolbar: { left:'prev,next today', center:'title', right:'dayGridMonth,timeGridWeek,timeGridDay,listMonth' },
        buttonText: { today:'Hoje', month:'Mês', week:'Semana', day:'Dia', list:'Lista' },
        navLinks: true,
        editable: false,
        droppable: true,
        selectable: true,
        selectMirror: true,
        select: function (info) {
            var startStr = info.startStr.substring(0, 10);
            var endStr;
            if (info.allDay) {
                /* FC entrega fim exclusivo em all-day: subtrai 1 dia */
                var d = info.endStr.substring(0, 10).split('-');
                var dt = new Date(+d[0], +d[1] - 1, +d[2] - 1);
                endStr = dt.getFullYear() + '-' + _pad(dt.getMonth() + 1) + '-' + _pad(dt.getDate());
                if (endStr < startStr) endStr = startStr;
            } else {
                endStr = info.endStr.substring(0, 10);
                if (endStr < startStr) endStr = startStr;
            }
            $('#modal-alocacao-titulo').text('Nova Alocação');
            $('#alocacao-id').val('');
            $('#alocacao-inicio').val(startStr);
            $('#alocacao-fim').val(endStr);
            $('#alocacao-lembrete').val('');
            ocultarErroModal('modal-alocacao-erro');
            carregarSelectsAlocacao(null, null);
            $('#modal-alocacao').modal('show');
            $('#modal-alocacao').one('hidden.bs.modal', function () {
                if (window._calendar) window._calendar.unselect();
            });
        },
        dayMaxEvents: true,
        events: {
            url: API, method: 'GET',
            extraParams: { metodo:'listarTodasAlocacoes', nomeClasse:'AgendaControle' },
            failure: function () { exibirMsgAba('msg-agendas', 'Erro ao carregar eventos do calendário.', 'danger'); }
        },
        eventClick: function (info) {
            var e = info.event, p = e.extendedProps;
            $('#modal-evento-titulo').text(e.title);
            $('#modal-evento-inicio').text(fmtDate(e.startStr));
            $('#modal-evento-fim').text(fmtDate(p.fim_display));
            $('#modal-evento-equipe').text(p.equipe || '—');
            if (p.lembrete) { $('#modal-evento-lembrete').text(fmtDatetime(p.lembrete)); $('#modal-evento-lembrete-row').show(); }
            else            { $('#modal-evento-lembrete-row').hide(); }
            $('#modal-evento').modal('show');
        },
        eventDidMount: function (info) {
            var cor = _equipeCorMap[String(info.event.extendedProps.id_equipe)];
            if (cor) {
                info.el.style.backgroundColor = cor;
                info.el.style.setProperty('border-left-color', cor, 'important');
            }
        },
        eventReceive: function (info) {
            var event    = info.event;
            var idEquipe = event.extendedProps.id_equipe;
            var inicio   = event.startStr.substring(0, 10);
            var idAgenda = $('#sidebar-agenda-select').val();

            if (!idAgenda) {
                event.remove();
                exibirMsgAba('msg-calendario', 'Selecione uma Agenda na barra lateral antes de arrastar.', 'warning');
                return;
            }
            if (!idEquipe) { event.remove(); return; }

            apiPost('incluirAlocacao', {
                id_agenda: idAgenda,
                id_equipe: idEquipe,
                inicio:    inicio,
                fim:       inicio
            }).done(function (r) {
                /* Sincroniza o id com o servidor para que _calRefetch() possa remover corretamente */
                event.setProp('id', String(r.id));
                event.setExtendedProp('fim_display', inicio);
                var nomeEq = $('#sidebar-equipes-lista .equipe-card[data-id="' + idEquipe + '"]').attr('data-nome') || '';
                event.setExtendedProp('equipe', nomeEq);
                _calPendingEvents.push(event);
                exibirMsgAba('msg-calendario', 'Alocação criada com sucesso!', 'success');
                carregarAlocacoes();
            }).fail(function (xhr) {
                /* Rollback: remove o evento otimista e exibe o motivo do erro */
                event.remove();
                var msg = (xhr.responseJSON && xhr.responseJSON.erro)
                    ? xhr.responseJSON.erro
                    : 'Erro ao salvar alocação. Tente novamente.';
                exibirMsgAba('msg-calendario', msg, 'danger');
            });
        }
    });
    cal.render();
    window._calendar = cal;
    carregarSidebarAgendas();
    carregarSidebarEquipes();
});

$('#abas-agenda a[href="#tab-calendario"]').on('shown.bs.tab', function () {
    if (window._calendar) window._calendar.updateSize();
    carregarSidebarAgendas();
    carregarSidebarEquipes();
});

/* ── Agendas ─────────────────────────────────────────────── */

function carregarAgendas() {
    api('listarAgendas').done(function (dados) {
        var html = '';
        $.each(dados || [], function (_, a) {
            var badge = a.status && a.status.toLowerCase() === 'ativo'
                ? '<span class="badge badge-ativo">' + a.status + '</span>'
                : '<span class="badge badge-inativo">' + (a.status || '—') + '</span>';
            html += '<tr>'
                + '<td>' + a.descricao + '</td>'
                + '<td class="col-status">' + badge + '</td>'
                + '<td class="col-acoes"><div class="acoes-grupo">'
                + '<button class="btn btn-xs btn-info btn-acao btn-editar-agenda" data-id="' + a.id + '" title="Editar"><i class="fa fa-pencil"></i></button>'
                + '<button class="btn btn-xs btn-danger btn-acao btn-excluir-agenda" data-id="' + a.id + '" title="Excluir"><i class="fa fa-trash"></i></button>'
                + '</div></td></tr>';
        });
        $('#tbody-agendas').html(html);
        dtInit('dt-agendas');
    });
}

function carregarStatusAgenda(selecionado) {
    api('listarStatus').done(function (dados) {
        var opts = '<option value="">Selecione...</option>';
        $.each(dados, function (_, s) {
            opts += '<option value="' + s.id + '"' + (s.id == selecionado ? ' selected' : '') + '>' + s.descricao + '</option>';
        });
        $('#agenda-status').html(opts);
        initSelect2('#agenda-status', 'Selecione o status...');
    });
}

$('#abas-agenda a[href="#tab-agendas"]').on('shown.bs.tab', carregarAgendas);

$('#btn-nova-agenda').on('click', function () {
    $('#modal-agenda-titulo').text('Nova Agenda');
    $('#agenda-id').val('');
    $('#agenda-descricao').val('');
    ocultarErroModal('modal-agenda-erro');
    carregarStatusAgenda(null);
    $('#modal-agenda').modal('show');
});

$(document).on('click', '.btn-editar-agenda', function () {
    var id = $(this).data('id');
    api('listarAgendaPorId', { id: id }).done(function (a) {
        $('#modal-agenda-titulo').text('Editar Agenda');
        $('#agenda-id').val(a.id);
        $('#agenda-descricao').val(a.descricao);
        ocultarErroModal('modal-agenda-erro');
        carregarStatusAgenda(a.id_status);
        $('#modal-agenda').modal('show');
    });
});

$(document).on('click', '.btn-excluir-agenda', function () {
    var id = $(this).data('id');
    confirmar('Excluir esta agenda?', function () {
        api('excluirAgenda', { id: id }).done(function (r) {
            exibirMsgAba('msg-agendas', r.msg || 'Excluído com sucesso.', 'success');
            carregarAgendas();
            _calRefetch();
        }).fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.erro) ? xhr.responseJSON.erro : 'Erro ao excluir.';
            exibirMsgAba('msg-agendas', msg, 'danger');
        });
    });
});

$('#btn-salvar-agenda').on('click', function () {
    var id        = $('#agenda-id').val();
    var descricao = $.trim($('#agenda-descricao').val());
    var status    = $('#agenda-status').val();

    ocultarErroModal('modal-agenda-erro');
    if (!descricao) { exibirErroModal('modal-agenda-erro', 'Informe a descrição.'); return; }
    if (!status)    { exibirErroModal('modal-agenda-erro', 'Selecione o status.'); return; }

    var dados = { descricao: descricao, id_status: status };
    if (id) dados.id = id;

    apiPost(id ? 'alterarAgenda' : 'incluirAgenda', dados).done(function (r) {
        $('#modal-agenda').modal('hide');
        exibirMsgAba('msg-agendas', r.msg || 'Salvo com sucesso.', 'success');
        carregarAgendas();
        _calRefetch();
    }).fail(function (xhr) {
        var msg = (xhr.responseJSON && xhr.responseJSON.erro) ? xhr.responseJSON.erro : 'Erro ao salvar.';
        exibirErroModal('modal-agenda-erro', msg);
    });
});

/* ── Equipes ─────────────────────────────────────────────── */

function renderTabelaEquipes(equipes, membros) {
    var memPorEquipe = {};
    $.each(membros || [], function (_, m) {
        if (!memPorEquipe[m.id_equipe]) memPorEquipe[m.id_equipe] = [];
        memPorEquipe[m.id_equipe].push(m.nome_completo.trim());
    });
    var html = '';
    $.each(equipes || [], function (_, e) {
        var badge = e.status && e.status.toLowerCase() === 'ativo'
            ? '<span class="badge badge-ativo">' + e.status + '</span>'
            : '<span class="badge badge-inativo">' + (e.status || '—') + '</span>';
        var nomes = memPorEquipe[e.id] || [];
        var membrosHtml = nomes.length
            ? $.map(nomes, function (n) { return '<span class="badge-membro">' + n + '</span>'; }).join('')
            : '<span class="text-muted">Nenhum membro</span>';
        html += '<tr>'
            + '<td><strong>' + e.nome + '</strong></td>'
            + '<td>' + (e.descricao || '—') + '</td>'
            + '<td class="membros-cell">' + membrosHtml + '</td>'
            + '<td class="col-status">' + badge + '</td>'
            + '<td class="col-acoes"><div class="acoes-grupo">'
            + '<button class="btn btn-xs btn-success btn-acao btn-membros-equipe" data-id="' + e.id + '" data-nome="' + e.nome + '" title="Membros"><i class="fa fa-users"></i></button>'
            + '<button class="btn btn-xs btn-info btn-acao btn-editar-equipe" data-id="' + e.id + '" data-nome="' + e.nome + '" data-descricao="' + (e.descricao||'') + '" data-status="' + e.id_status + '" title="Editar"><i class="fa fa-pencil"></i></button>'
            + '<button class="btn btn-xs btn-danger btn-acao btn-excluir-equipe" data-id="' + e.id + '" title="Excluir"><i class="fa fa-trash"></i></button>'
            + '</div></td></tr>';
    });
    $('#tbody-equipes').html(html);
    dtInit('dt-equipes');
}

function carregarEquipes() {
    api('listarEquipes').done(function (equipes) {
        api('listarTodosMembrosAtivos')
            .done(function (membros) { renderTabelaEquipes(equipes, membros); })
            .fail(function ()        { renderTabelaEquipes(equipes, []); });
    });
}

function carregarStatusEquipe(selecionado) {
    api('listarEquipeStatus').done(function (dados) {
        var opts = '<option value="">Selecione...</option>';
        $.each(dados, function (_, s) {
            opts += '<option value="' + s.id + '"' + (s.id == selecionado ? ' selected' : '') + '>' + s.descricao + '</option>';
        });
        $('#equipe-status').html(opts);
        initSelect2('#equipe-status', 'Selecione o status...');
    });
}

$('#abas-agenda a[href="#tab-equipes"]').on('shown.bs.tab', carregarEquipes);

$('#btn-nova-equipe').on('click', function () {
    $('#modal-equipe-titulo').text('Nova Equipe');
    $('#equipe-id').val(''); $('#equipe-nome').val(''); $('#equipe-descricao').val('');
    ocultarErroModal('modal-equipe-erro');
    carregarStatusEquipe(null);
    $('#modal-equipe').modal('show');
});

$(document).on('click', '.btn-editar-equipe', function () {
    var $b = $(this);
    $('#modal-equipe-titulo').text('Editar Equipe');
    $('#equipe-id').val($b.data('id'));
    $('#equipe-nome').val($b.data('nome'));
    $('#equipe-descricao').val($b.data('descricao'));
    ocultarErroModal('modal-equipe-erro');
    carregarStatusEquipe($b.data('status'));
    $('#modal-equipe').modal('show');
});

$(document).on('click', '.btn-excluir-equipe', function () {
    var id = $(this).data('id');
    confirmar('Excluir esta equipe?', function () {
        api('excluirEquipe', { id: id }).done(function (r) {
            exibirMsgAba('msg-equipes', r.msg || 'Excluído com sucesso.', 'success');
            carregarEquipes();
        }).fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.erro) ? xhr.responseJSON.erro : 'Erro ao excluir.';
            exibirMsgAba('msg-equipes', msg, 'danger');
        });
    });
});

$('#btn-salvar-equipe').on('click', function () {
    var id        = $('#equipe-id').val();
    var nome      = $.trim($('#equipe-nome').val());
    var descricao = $.trim($('#equipe-descricao').val());
    var status    = $('#equipe-status').val();

    ocultarErroModal('modal-equipe-erro');
    if (!nome)   { exibirErroModal('modal-equipe-erro', 'Informe o nome da equipe.'); return; }
    if (!status) { exibirErroModal('modal-equipe-erro', 'Selecione o status.'); return; }

    var dados = { nome: nome, descricao: descricao, id_status: status };
    if (id) dados.id = id;

    apiPost(id ? 'alterarEquipe' : 'incluirEquipe', dados).done(function (r) {
        $('#modal-equipe').modal('hide');
        exibirMsgAba('msg-equipes', r.msg || 'Salvo com sucesso.', 'success');
        carregarEquipes();
    }).fail(function (xhr) {
        var msg = (xhr.responseJSON && xhr.responseJSON.erro) ? xhr.responseJSON.erro : 'Erro ao salvar.';
        exibirErroModal('modal-equipe-erro', msg);
    });
});

/* ── Membros ─────────────────────────────────────────────── */

function carregarMembros(idEquipe) {
    api('listarMembrosPorEquipe', { id_equipe: idEquipe }).done(function (dados) {
        var html = '';
        if (!dados || !dados.length) {
            html = '<tr><td colspan="4" class="text-center text-muted">Nenhum membro ativo.</td></tr>';
        } else {
            $.each(dados, function (_, m) {
                html += '<tr>'
                    + '<td>' + m.nome + ' ' + (m.sobrenome || '') + '</td>'
                    + '<td>' + fmtTime(m.inicio_turno) + '</td>'
                    + '<td>' + fmtTime(m.fim_turno) + '</td>'
                    + '<td>'
                    + '<button class="btn btn-xs btn-warning btn-acao mr-xs btn-inativar-membro" data-id="' + m.id + '" title="Inativar"><i class="fa fa-ban"></i></button>'
                    + '<button class="btn btn-xs btn-danger btn-acao btn-excluir-membro" data-id="' + m.id + '" title="Remover"><i class="fa fa-trash"></i></button>'
                    + '</td></tr>';
            });
        }
        $('#tbody-membros').html(html);
    });
}

$(document).on('click', '.btn-membros-equipe', function () {
    var id = $(this).data('id'), nome = $(this).data('nome');
    $('#membros-equipe-id').val(id);
    $('#membros-equipe-nome').text(nome);
    $('#membro-inicio').val(''); $('#membro-fim').val('');
    ocultarErroModal('modal-membros-erro');
    ocultarErroModal('modal-membros-sucesso');
    api('listarPessoas').done(function (dados) {
        var opts = '<option value="">Selecione uma pessoa...</option>';
        $.each(dados, function (_, p) {
            opts += '<option value="' + p.id_pessoa + '">' + p.nome_completo + '</option>';
        });
        $('#membro-pessoa').html(opts);
        initSelect2('#membro-pessoa', 'Selecione uma pessoa...');
    });
    carregarMembros(id);
    $('#modal-membros').modal('show');
});

$('#btn-adicionar-membro').on('click', function () {
    var idEquipe = $('#membros-equipe-id').val();
    var idPessoa = $('#membro-pessoa').val();
    var inicio   = $('#membro-inicio').val();
    var fim      = $('#membro-fim').val();

    ocultarErroModal('modal-membros-erro');
    if (!idPessoa) { exibirErroModal('modal-membros-erro', 'Selecione uma pessoa.'); return; }
    if (!inicio)   { exibirErroModal('modal-membros-erro', 'Informe o horário de início do turno.'); return; }
    if (!fim)      { exibirErroModal('modal-membros-erro', 'Informe o horário de fim do turno.'); return; }
    if (inicio >= fim) { exibirErroModal('modal-membros-erro', 'O horário de início deve ser menor que o horário de fim.'); return; }

    apiPost('incluirMembro', { id_equipe: idEquipe, id_pessoa: idPessoa, inicio_turno: inicio, fim_turno: fim })
        .done(function (r) {
            ocultarErroModal('modal-membros-erro');
            $('#modal-membros-sucesso-texto').text(r.msg || 'Membro adicionado com sucesso.');
            $('#modal-membros-sucesso').show();
            $('#membro-pessoa').val('').trigger('change');
            $('#membro-inicio').val(''); $('#membro-fim').val('');
            carregarMembros(idEquipe);
            carregarEquipes();
        })
        .fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.erro) ? xhr.responseJSON.erro : 'Erro ao adicionar membro.';
            exibirErroModal('modal-membros-erro', msg);
        });
});

$(document).on('click', '.btn-inativar-membro', function () {
    var id = $(this).data('id'), idEquipe = $('#membros-equipe-id').val();
    confirmar('Inativar este membro?', function () {
        api('inativarMembro', { id: id })
            .done(function (r) {
                $('#modal-membros-sucesso-texto').text(r.msg || 'Membro inativado.');
                $('#modal-membros-sucesso').show();
                carregarMembros(idEquipe);
                carregarEquipes();
            })
            .fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.erro) ? xhr.responseJSON.erro : 'Erro ao inativar.';
                exibirErroModal('modal-membros-erro', msg);
            });
    });
});

$(document).on('click', '.btn-excluir-membro', function () {
    var id = $(this).data('id'), idEquipe = $('#membros-equipe-id').val();
    confirmar('Remover este membro permanentemente?', function () {
        api('excluirMembro', { id: id })
            .done(function (r) {
                $('#modal-membros-sucesso-texto').text(r.msg || 'Membro removido.');
                $('#modal-membros-sucesso').show();
                carregarMembros(idEquipe);
                carregarEquipes();
            })
            .fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.erro) ? xhr.responseJSON.erro : 'Erro ao remover.';
                exibirErroModal('modal-membros-erro', msg);
            });
    });
});

/* ── Alocações ───────────────────────────────────────────── */

function carregarAlocacoes() {
    api('listarTodasAlocacoes').done(function (dados) {
        var html = '';
        $.each(dados || [], function (_, al) {
            html += '<tr>'
                + '<td>' + al.agenda + '</td>'
                + '<td>' + al.equipe + '</td>'
                + '<td>' + fmtDate(al.start) + '</td>'
                + '<td>' + fmtDate(al.fim_display) + '</td>'
                + '<td>' + fmtDatetime(al.lembrete) + '</td>'
                + '<td class="col-acoes"><div class="acoes-grupo">'
                + '<button class="btn btn-xs btn-info btn-acao btn-editar-alocacao"'
                + ' data-id="' + al.id + '" data-agenda="' + al.id_agenda + '" data-equipe="' + al.id_equipe + '"'
                + ' data-inicio="' + (al.start||'').substring(0,10) + '"'
                + ' data-fim="'    + (al.fim_display||'').substring(0,10) + '"'
                + ' data-lembrete="' + (al.lembrete||'').replace(' ','T').substring(0,16) + '"'
                + ' title="Editar"><i class="fa fa-pencil"></i></button>'
                + '<button class="btn btn-xs btn-danger btn-acao btn-excluir-alocacao" data-id="' + al.id + '" title="Excluir"><i class="fa fa-trash"></i></button>'
                + '</div></td></tr>';
        });
        $('#tbody-alocacoes').html(html);
        dtInit('dt-alocacoes');
    });
}

function carregarSelectsAlocacao(selAgenda, selEquipe) {
    api('listarAgendas').done(function (agendas) {
        var opts = '<option value="">Selecione...</option>';
        $.each(agendas, function (_, a) {
            opts += '<option value="' + a.id + '"' + (a.id == selAgenda ? ' selected' : '') + '>' + a.descricao + '</option>';
        });
        $('#alocacao-agenda').html(opts);
        initSelect2('#alocacao-agenda', 'Selecione a agenda...');
    });
    api('listarEquipes').done(function (equipes) {
        var opts = '<option value="">Selecione...</option>';
        $.each(equipes, function (_, e) {
            opts += '<option value="' + e.id + '"' + (e.id == selEquipe ? ' selected' : '') + '>' + e.nome + '</option>';
        });
        $('#alocacao-equipe').html(opts);
        initSelect2('#alocacao-equipe', 'Selecione a equipe...');
    });
}

$('#abas-agenda a[href="#tab-alocacoes"]').on('shown.bs.tab', carregarAlocacoes);

$('#btn-nova-alocacao').on('click', function () {
    $('#modal-alocacao-titulo').text('Nova Alocação');
    $('#alocacao-id').val(''); $('#alocacao-inicio').val(''); $('#alocacao-fim').val(''); $('#alocacao-lembrete').val('');
    ocultarErroModal('modal-alocacao-erro');
    carregarSelectsAlocacao(null, null);
    $('#modal-alocacao').modal('show');
});

$(document).on('click', '.btn-editar-alocacao', function () {
    var $b = $(this);
    $('#modal-alocacao-titulo').text('Editar Alocação');
    $('#alocacao-id').val($b.data('id'));
    $('#alocacao-inicio').val($b.data('inicio'));
    $('#alocacao-fim').val($b.data('fim'));
    $('#alocacao-lembrete').val($b.data('lembrete') || '');
    ocultarErroModal('modal-alocacao-erro');
    carregarSelectsAlocacao($b.data('agenda'), $b.data('equipe'));
    $('#modal-alocacao').modal('show');
});

$(document).on('click', '.btn-excluir-alocacao', function () {
    var id = $(this).data('id');
    confirmar('Excluir esta alocação?', function () {
        api('excluirAlocacao', { id: id })
            .done(function (r) {
                exibirMsgAba('msg-alocacoes', r.msg || 'Excluído com sucesso.', 'success');
                carregarAlocacoes();
                _calRefetch();
            })
            .fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.erro) ? xhr.responseJSON.erro : 'Erro ao excluir.';
                exibirMsgAba('msg-alocacoes', msg, 'danger');
            });
    });
});

$('#btn-salvar-alocacao').on('click', function () {
    var id         = $('#alocacao-id').val();
    var agenda     = $('#alocacao-agenda').val();
    var equipe     = $('#alocacao-equipe').val();
    var inicio     = $('#alocacao-inicio').val();
    var fim        = $('#alocacao-fim').val();
    var lembrete   = $('#alocacao-lembrete').val();
    /* capturado antes do modal fechar para uso no addEvent */
    var agendaNome = $('#alocacao-agenda option[value="' + agenda + '"]').text();
    var equipNome  = $('#alocacao-equipe option[value="' + equipe + '"]').text();

    ocultarErroModal('modal-alocacao-erro');
    if (!agenda) { exibirErroModal('modal-alocacao-erro', 'Selecione a agenda.'); return; }
    if (!equipe) { exibirErroModal('modal-alocacao-erro', 'Selecione a equipe.'); return; }
    if (!inicio) { exibirErroModal('modal-alocacao-erro', 'Informe a data/hora de início.'); return; }
    if (!fim)    { exibirErroModal('modal-alocacao-erro', 'Informe a data/hora de fim.'); return; }
    if (inicio > fim) { exibirErroModal('modal-alocacao-erro', 'O início não pode ser maior que o fim.'); return; }

    var dados = {
        id_agenda: agenda, id_equipe: equipe,
        inicio:   inicio,
        fim:      fim,
        lembrete: lembrete ? lembrete.replace('T', ' ') : ''
    };
    if (id) dados.id = id;

    apiPost(id ? 'alterarAlocacao' : 'incluirAlocacao', dados)
        .done(function (r) {
            $('#modal-alocacao').modal('hide');
            exibirMsgAba('msg-alocacoes', r.msg || 'Salvo com sucesso.', 'success');
            carregarAlocacoes();
            if (!id && r.id) {
                /* Nova alocação: adiciona imediatamente no calendário.
                   FC usa fim exclusivo em all-day, então +1 dia. */
                var p    = fim.split('-');
                var fcDt = new Date(+p[0], +p[1] - 1, +p[2] + 1);
                _calAddEvent({
                    id: String(r.id),
                    title: equipNome,
                    start: inicio,
                    end: fcDt.getFullYear() + '-' + _pad(fcDt.getMonth() + 1) + '-' + _pad(fcDt.getDate()),
                    allDay: true,
                    extendedProps: {
                        equipe:      equipNome,
                        fim_display: fim,
                        lembrete:    lembrete ? lembrete.replace('T', ' ') : null,
                        id_agenda:   agenda,
                        id_equipe:   equipe
                    }
                });
            } else {
                /* Edição: sincroniza com o servidor (remove pendentes antes) */
                _calRefetch();
            }
        })
        .fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.erro) ? xhr.responseJSON.erro : 'Erro ao salvar.';
            exibirErroModal('modal-alocacao-erro', msg);
        });
});
</script>
</body>
</html>
