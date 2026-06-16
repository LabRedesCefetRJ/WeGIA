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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
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

        /* Select2 v3 — anula o ::after do tema Porto que duplica o × no select simples */
        .select2-container .select2-choice abbr.select2-search-choice-close:after { content: none; }

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

        /* ── Alertas de erro/sucesso nos modais ── */
        .modal-alert-animado {
            display: block !important;
            max-height: 0;
            margin-bottom: 0;
            padding-top: 0;
            padding-bottom: 0;
            border-width: 0;
            opacity: 0;
            visibility: hidden;
            overflow: hidden;
            transition: max-height 0.25s ease, opacity 0.25s ease,
                        margin-bottom 0.25s ease, padding 0.25s ease,
                        border-width 0.25s ease, visibility 0.25s ease;
        }
        .modal-alert-animado.in {
            max-height: 120px;
            margin-bottom: 20px;
            padding-top: 15px;
            padding-bottom: 15px;
            border-width: 1px;
            opacity: 1;
            visibility: visible;
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
                                            <span class="cal-toolbar-label"><i class="fa fa-users mr-xs"></i>Equipe</span>
                                            <select class="form-control input-sm cal-toolbar-select" id="sidebar-equipe-select" disabled>
                                                <option value="">Selecione a agenda primeiro...</option>
                                            </select>
                                            <div id="sidebar-drag-container">
                                                <div class="equipe-card" id="sidebar-equipe-card" style="display:none;"
                                                     data-id="" data-nome="" data-cor="">
                                                    <i class="fa fa-users"></i>
                                                    <span id="sidebar-equipe-card-nome"></span>
                                                </div>
                                            </div>
                                            <span class="cal-sidebar-hint" id="sidebar-drag-hint" style="display:none;">arraste para o calendário</span>
                                        </div>
                                        <div class="cal-toolbar-divider"></div>
                                        <div class="cal-toolbar-group">
                                            <button class="btn btn-danger btn-sm" id="btn-download-mensal" type="button" title="Baixar relatório PDF da agenda deste mês" style="font-family: 'Montserrat', sans-serif; font-weight: 700;">
                                                <i<i class="bi bi-file-pdf-fill"></i>
                                                PDF
                                            </button>
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
                                        <button class="btn btn-primary btn-sm pull-right" id="btn-nova-agenda">
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

                                    <div class="cal-toolbar mb-md">
                                        <div class="cal-toolbar-group">
                                            <span class="cal-toolbar-label"><i class="fa fa-list-alt mr-xs"></i>Agenda</span>
                                            <select class="form-control input-sm cal-toolbar-select" id="filtro-equipe-agenda">
                                                <option value="">Todas as agendas</option>
                                            </select>
                                        </div>
                                        <div class="cal-toolbar-divider"></div>
                                        <div class="cal-toolbar-group">
                                            <span class="cal-toolbar-label"><i class="fa fa-toggle-on mr-xs"></i>Status</span>
                                            <select class="form-control input-sm cal-toolbar-select" id="filtro-equipe-status">
                                                <option value="ativo">Ativas</option>
                                                <option value="">Todas</option>
                                                <option value="inativo">Inativas</option>
                                            </select>
                                        </div>
                                        <div style="flex:1;"></div>
                                        <button class="btn btn-primary btn-sm" id="btn-nova-equipe">
                                            <i class="fa fa-plus mr-xs"></i> Nova Equipe
                                        </button>
                                    </div>
                                    <table class="table table-bordered table-striped table-hover mb-none" id="dt-equipes">
                                        <thead>
                                            <tr>
                                                <th style="width:160px;">Nome</th>
                                                <th>Descrição</th>
                                                <th>Membros</th>
                                                <th style="width:110px;" class="text-center">Turno</th>
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

                                    <div class="cal-toolbar mb-md">
                                        <div class="cal-toolbar-group">
                                            <span class="cal-toolbar-label"><i class="fa fa-list-alt mr-xs"></i>Agenda</span>
                                            <select class="form-control input-sm cal-toolbar-select" id="filtro-alocacao-agenda">
                                                <option value="">Selecione...</option>
                                            </select>
                                        </div>
                                        <div class="cal-toolbar-group">
                                            <span class="cal-toolbar-label"><i class="fa fa-users mr-xs"></i>Equipe</span>
                                            <select class="form-control input-sm cal-toolbar-select" id="filtro-alocacao-equipe" disabled>
                                                <option value="">Selecione a agenda...</option>
                                            </select>
                                        </div>
                                        <div style="flex:1;"></div>
                                        <button class="btn btn-primary btn-sm" id="btn-nova-alocacao">
                                            <i class="fa fa-plus mr-xs"></i> Nova Alocação
                                        </button>
                                    </div>

                                    <table class="table table-bordered table-striped table-hover mb-none" id="dt-alocacoes">
                                        <thead>
                                            <tr>
                                                <th>Equipe</th>
                                                <th style="width:120px;">Data início</th>
                                                <th style="width:120px;">Data fim</th>
                                                <th style="width:110px;" class="text-center">Horário</th>
                                                <th style="width:90px;" class="text-center">Intervalo</th>
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
                <div id="modal-agenda-erro" class="alert alert-danger alert-dismissible modal-alert-animado" role="alert">
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
                <div class="form-group" id="agenda-status-grupo">
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
                <div id="modal-equipe-erro" class="alert alert-danger alert-dismissible modal-alert-animado" role="alert">
                    <button type="button" class="close" aria-label="Fechar" onclick="ocultarErroModal('modal-equipe-erro'); return false;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <span id="modal-equipe-erro-texto"></span>
                </div>

                <input type="hidden" id="equipe-id">
                <div class="form-group">
                    <label class="control-label">Agenda <sup class="text-danger">*</sup></label>
                    <select class="form-control" id="equipe-agenda"></select>
                </div>
                <div class="form-group">
                    <label class="control-label">Nome <sup class="text-danger">*</sup></label>
                    <input type="text" class="form-control" id="equipe-nome" maxlength="100">
                </div>
                <div class="form-group">
                    <label class="control-label">Descrição</label>
                    <textarea class="form-control" id="equipe-descricao" rows="2"></textarea>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label">Início do turno <sup class="text-danger">*</sup></label>
                            <input type="time" class="form-control" id="equipe-inicio-turno">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label">Fim do turno <sup class="text-danger">*</sup></label>
                            <input type="time" class="form-control" id="equipe-fim-turno">
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top: 15px;">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="control-label"><i class="fa fa-sitemap mr-xs"></i> Divisões</label>
                            <div id="wrapper-divisoes-equipe" style="margin-bottom: 10px;">
                                </div>
                            <button type="button" class="btn btn-default btn-sm" id="btn-nova-divisao-input">
                                <i class="fa fa-plus text-success"></i> Adicionar Divisão
                            </button>
                            <p class="help-block" style="font-size: 11px;">Permite organizar os membros em subgrupos</p>
                        </div>
                    </div>
                </div>
                <div class="form-group" id="equipe-status-grupo">
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
                <div id="modal-membros-erro" class="alert alert-danger alert-dismissible modal-alert-animado" role="alert">
                    <button type="button" class="close" aria-label="Fechar" onclick="ocultarErroModal('modal-membros-erro'); return false;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <span id="modal-membros-erro-texto"></span>
                </div>

                <div id="modal-membros-sucesso" class="alert alert-success alert-dismissible modal-alert-animado" role="alert">
                    <button type="button" class="close" aria-label="Fechar" onclick="ocultarErroModal('modal-membros-sucesso'); return false;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <span id="modal-membros-sucesso-texto"></span>
                </div>

                <input type="hidden" id="membros-equipe-id">

                <div class="membros-panel mb-md">
                    <h5 style="margin-top:0; font-weight:700; color:#2d3a4a;">Adicionar Membro</h5>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="control-label">Pessoa <sup class="text-danger">*</sup></label>
                                <select class="form-control" id="membro-pessoa"></select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label">Divisão</label>
                                <select class="form-control" id="membro-divisao" disabled>
                                    <option value="">Sem divisão</option>
                                </select>
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
                            <th style="width:110px;" class="text-center">Turno</th>
                            <th style="width:160px;">Divisão</th> <th style="width:120px;">Ações</th> </tr>
                    </thead>
                    <tbody id="tbody-membros">
                        <tr><td colspan="4" class="text-center text-muted">Nenhum membro ativo.</td></tr>
                    </tbody>
                </table>

                <div id="secao-membros-inativos" style="display:none; margin-top:8px;">
                    <h6 style="font-weight:700; color:#95a5a6; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px;">
                        <i class="fa fa-ban mr-xs"></i> Inativos
                    </h6>
                    <table class="table table-bordered table-condensed" style="margin-bottom:0;">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th style="width:100px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-membros-inativos"></tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-link btn-xs" id="btn-toggle-inativos" style="margin-top:6px; padding:0; color:#95a5a6; display:none;">
                    <i class="fa fa-chevron-down mr-xs"></i><span id="toggle-inativos-label">Ver inativos</span>
                </button>
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
                <div id="modal-alocacao-erro" class="alert alert-danger alert-dismissible modal-alert-animado" role="alert">
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
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label class="control-label">Data de início <sup class="text-danger">*</sup></label>
                            <input type="date" class="form-control" id="alocacao-inicio">
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label class="control-label">Data de fim <sup class="text-danger">*</sup></label>
                            <input type="date" class="form-control" id="alocacao-fim">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="control-label">Intervalo</label>
                            <input type="number" class="form-control" id="alocacao-intervalo" min="0" value="0">
                        </div>
                    </div>
                </div>
                <div class="form-group" style="margin-top:10px;">
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
                <input type="hidden" id="modal-evento-id">
                <input type="hidden" id="modal-evento-id-agenda">
                <input type="hidden" id="modal-evento-id-equipe">
                <p><strong><i class="fa fa-calendar mr-xs"></i> Início:</strong> <span id="modal-evento-inicio"></span></p>
                <p><strong><i class="fas fa-calendar-check mr-xs"></i> Fim:</strong> <span id="modal-evento-fim"></span></p>
                <p style="margin-bottom:6px;">
                    <strong><i class="fa fa-bell mr-xs"></i> Lembrete:</strong>
                    <span id="modal-evento-lembrete-texto" style="color:#555;"></span>
                </p>
                <hr style="margin:10px 0;">
                <p style="margin-bottom:6px;"><strong><i class="fa fa-users mr-xs"></i> Pessoas na equipe:</strong></p>
                <div id="modal-evento-membros" style="color:#555;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-info" id="btn-ir-equipes">
                    <i class="fa fa-users mr-xs"></i> Editar na aba Equipes
                </button>
                <button type="button" class="btn btn-info" id="btn-ir-alocacoes">
                    <i class="fa fa-pencil mr-xs"></i> Editar na aba Alocações
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     MODAL — LEMBRETE RÁPIDO (pós-arrastar)
══════════════════════════════════════════ -->
<div class="modal fade" id="modal-lembrete-rapido" tabindex="-1" role="dialog" aria-labelledby="modal-lembrete-rapido-titulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-padrao">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-lembrete-rapido-titulo">
                    <i class="fa fa-bell mr-xs"></i> Adicionar Lembrete
                </h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="lembrete-rapido-id">
                <p style="color:#666; margin-bottom:12px;">Deseja definir um lembrete para esta alocação?</p>
                <div class="form-group">
                    <label class="control-label">Data e hora</label>
                    <input type="datetime-local" class="form-control" id="lembrete-rapido-input">
                </div>
                <div class="form-group">
                    <textarea class="form-control input-sm" id="lembrete-rapido-mensagem" rows="2"
                        maxlength="255" placeholder="Mensagem (opcional)"></textarea>
                </div>
                <hr style="margin:10px 0 8px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="control-label" style="font-size:12px; color:#888;">
                        <i class="fa fa-cog mr-xs"></i> Preferência ao arrastar equipe
                    </label>
                    <select class="form-control input-sm" id="lembrete-rapido-pref">
                        <option value="always">Sempre perguntar</option>
                        <option value="never">Nunca perguntar</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" id="btn-lembrete-rapido-pular">Pular</button>
                <button type="button" class="btn btn-primary" id="btn-lembrete-rapido-salvar">
                    <i class="fa fa-save mr-xs"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     MODAL — CONFIRMAÇÃO
══════════════════════════════════════════ -->
<div class="modal fade" id="modal-confirmar" tabindex="-1" role="dialog" aria-labelledby="modal-confirmar-titulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-padrao">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-confirmar-titulo">Confirmar</h4>
            </div>
            <div class="modal-body">
                <p id="modal-confirmar-msg" style="margin:0; font-size:1.2rem;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn-confirmar-ok">Confirmar</button>
            </div>
        </div>
    </div>
</div>

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

var _calPendingEvents = [];
var _eventoAtual = null;
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

var CORES_EQUIPE = [
    '#337ab7', '#5cb85c', '#d9534f', '#f0ad4e', '#5bc0de',
    '#8e44ad', '#e67e22', '#1abc9c', '#2c3e50', '#e74c3c', 
    '#34495e', '#9b59b6', '#16a085', '#27ae60', '#f39c12', 
    '#d35400', '#c0392b', '#7f8c8d', '#ff9ff3', '#01a3a4'
];

var _equipeCorMap  = {};   
var _draggableInst = null;

function gerarCorEquipe(id) {
    var index = id % CORES_EQUIPE.length;
    return CORES_EQUIPE[index];
}

function preCarregarEquipesCores() {
    api('listarEquipes').done(function (equipes) {
        $.each(equipes, function (idx, e) {
            if (!_equipeCorMap[String(e.id)]) {
                // Chama a nossa nova função que usa o ID
                _equipeCorMap[String(e.id)] = gerarCorEquipe(e.id);
            }
        });
        if (window._calendar) {
            window._calendar.getEvents().forEach(function (evt) {
                var cor = _equipeCorMap[String(evt.extendedProps.id_equipe)];
                if (cor) evt.setProp('color', cor);
            });
        }
    });
}

function preCarregarEquipesCores() {
    api('listarEquipes').done(function (equipes) {
        $.each(equipes, function (idx, e) {
            if (!_equipeCorMap[String(e.id)]) {
                _equipeCorMap[String(e.id)] = CORES_EQUIPE[idx % CORES_EQUIPE.length];
            }
        });
        if (window._calendar) {
            window._calendar.getEvents().forEach(function (evt) {
                var cor = _equipeCorMap[String(evt.extendedProps.id_equipe)];
                if (cor) evt.setProp('color', cor);
            });
        }
    });
}

function carregarSidebarAgendas() {
    api('listarAgendas').done(function (agendas) {
        var opts = '<option value="">Selecione uma agenda</option>';
        $.each(agendas, function (_, a) {
            if (!a.status || a.status.toLowerCase() !== 'ativo') return;
            opts += '<option value="' + a.id + '">' + a.descricao + '</option>';
        });
        $('#sidebar-agenda-select').html(opts);
        initSelect2('#sidebar-agenda-select', 'Selecione uma agenda...');
    });
}

function carregarSelectAgendaEquipe(selecionado) {
    api('listarAgendas').done(function (dados) {
        var opts = '<option value="">Selecione...</option>';
        $.each(dados, function (_, a) {
            if (!a.status || a.status.toLowerCase() !== 'ativo') return;
            opts += '<option value="' + a.id + '"' + (a.id == selecionado ? ' selected' : '') + '>' + a.descricao + '</option>';
        });
        $('#equipe-agenda').html(opts);
        initSelect2('#equipe-agenda', 'Selecione a agenda');
    });
}

function carregarSidebarEquipes(idAgenda) {
    if (!idAgenda) {
        $('#sidebar-equipe-select')
            .html('<option value="">Selecione uma equipe</option>')
            .prop('disabled', true);
        initSelect2('#sidebar-equipe-select', 'Selecione uma equipe...');
        atualizarCardEquipe(null);
        return;
    }
    api('listarEquipes', { id_agenda: idAgenda }).done(function (equipes) {
        _equipeCorMap = {};
        var opts = '<option value="">Selecione uma equipe</option>';
        
        $.each(equipes, function (_, e) {
            if (!e.status || e.status.toLowerCase() !== 'ativo') return;
            
            // Chama a função matemática usando o ID da equipe, sem depender da ordem (idx)
            var cor = gerarCorEquipe(e.id);
            
            _equipeCorMap[String(e.id)] = cor;
            opts += '<option value="' + e.id + '">' + e.nome + '</option>';
        });
        
        $('#sidebar-equipe-select').html(opts).prop('disabled', false);
        initSelect2('#sidebar-equipe-select', 'Selecione uma equipe...');
        atualizarCardEquipe(null);
        
        /* Aplica cores nos eventos já renderizados */
        if (window._calendar) {
            $.each(window._calendar.getEvents(), function (_, evt) {
                var cor = _equipeCorMap[String(evt.extendedProps.id_equipe)];
                if (cor) evt.setProp('color', cor);
            });
        }
    });
}

function atualizarCardEquipe(idEquipe) {
    var $card = $('#sidebar-equipe-card');
    if (!idEquipe) {
        $card.hide();
        $('#sidebar-drag-hint').hide();
        return;
    }
    var nome = $('#sidebar-equipe-select option[value="' + idEquipe + '"]').text();
    var cor  = _equipeCorMap[String(idEquipe)] || CORES_EQUIPE[0];
    $card.attr('data-id', idEquipe)
         .attr('data-nome', nome)
         .attr('data-cor', cor)
         .css('background', cor)
         .show();
    $('#sidebar-equipe-card-nome').text(nome);
    $('#sidebar-drag-hint').show();
}

$(function () {
    $("#header").load("../header.php");
    $(".menuu").load("../menu.php");
    dtInit('dt-agendas');
    dtInit('dt-equipes');
    dtInit('dt-alocacoes');
    initSelect2('#filtro-equipe-status', 'Status');
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

var _alertModalTimers = {};

function exibirErroModal(idErro, texto) {
    $('#' + idErro + '-texto').text(texto);
    $('#' + idErro).addClass('in');
    clearTimeout(_alertModalTimers[idErro]);
    _alertModalTimers[idErro] = setTimeout(function () { ocultarErroModal(idErro); }, 10000);
}

function ocultarErroModal(id) {
    clearTimeout(_alertModalTimers[id]);
    $('#' + id).removeClass('in');
}

function exibirSucessoModal(id, texto) {
    $('#' + id + '-texto').text(texto);
    $('#' + id).addClass('in');
    clearTimeout(_alertModalTimers[id]);
    _alertModalTimers[id] = setTimeout(function () { $('#' + id).removeClass('in'); }, 10000);
}

function confirmar(msg, cb) {
    $('#modal-confirmar-msg').text(msg);
    $('#btn-confirmar-ok').off('click').on('click', function () {
        $('#modal-confirmar').modal('hide');
        cb();
    });
    $('#modal-confirmar').modal('show');
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

/* Formata o turno com ícone: ☀ diurno (mesmo dia) ou noturno (vira o dia).
   iconeAbaixo=true coloca o ícone em uma linha própria, abaixo do horário. */
function fmtTurno(inicio, fim, iconeAbaixo) {
    if (!inicio || !fim) return '—';
    var icone = fim <= inicio
        ? '<i class="fa fa-moon-o text-muted" title="Vira o dia (termina no dia seguinte)"></i>'
        : '<i class="fa fa-sun-o text-muted" title="Plantão diurno (mesmo dia)"></i>';
    var separador = iconeAbaixo ? '<br>' : ' ';
    return fmtTime(inicio) + ' – ' + fmtTime(fim) + separador + icone;
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
    var $modal = $el.closest('.modal');
    var opts = {
        placeholder: placeholder || 'Selecione...',
        allowClear: true,
        width: $modal.length ? '100%' : 'resolve',
        language: {
            noResults: function () { return 'Nenhum resultado encontrado'; },
            searching: function () { return 'Buscando...'; }
        }
    };
    if ($modal.length) opts.dropdownParent = $modal;
    $el.select2(opts);
}

/* ── Calendário ──────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', function () {
    var cal = new FullCalendar.Calendar(document.getElementById('calendar'), {
        locale: 'pt-br',
        initialView: 'dayGridMonth',
        headerToolbar: { left:'prev,next today', center:'title', right:'dayGridMonth,timeGridWeek,timeGridDay,listMonth' },
        buttonText: { today:'Hoje', month:'Mês', week:'Semana', day:'Dia', list:'Lista' },
        navLinks: true,
        /* Eventos como blocos sólidos: plantão noturno (ex.: 19h->07h) atravessa a meia-noite
           e ocupa visualmente os dois dias. */
        eventDisplay: 'block',
        displayEventTime: false,
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
            $('#alocacao-intervalo').val('0');
            ocultarErroModal('modal-alocacao-erro');
            carregarSelectsAlocacao(null, null);
            $('#modal-alocacao').modal('show');
            $('#modal-alocacao').one('hidden.bs.modal', function () {
                if (window._calendar) window._calendar.unselect();
            });
        },
        dayMaxEvents: true,
        events: function (fetchInfo, successCallback, failureCallback) {
            var idAgenda = $('#sidebar-agenda-select').val();
            if (!idAgenda) { successCallback([]); return; }
            $.get(API, { metodo: 'listarAlocacoesPorAgenda', nomeClasse: 'AgendaControle', id_agenda: idAgenda })
                .done(function (data) { successCallback(Array.isArray(data) ? data : []); })
                .fail(function () {
                    failureCallback();
                    exibirMsgAba('msg-calendario', 'Erro ao carregar eventos do calendário.', 'danger');
                });
        },
        eventClick: function (info) {
            var e = info.event, p = e.extendedProps;
            _eventoAtual = { id: e.id, start: e.startStr, p: p };
            $('#modal-evento-id').val(e.id);
            $('#modal-evento-id-agenda').val(p.id_agenda || '');
            $('#modal-evento-id-equipe').val(p.id_equipe || '');
            $('#modal-evento-titulo').text(e.title);
            $('#modal-evento-inicio').text(fmtDatetime(e.startStr));
            $('#modal-evento-fim').text(fmtDatetime(p.fim_display || e.endStr));

            if (p.lembrete) {
                var dt = new Date(p.lembrete.replace(' ', 'T'));
                var texto = dt.toLocaleDateString('pt-BR') + ' às ' + dt.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                $('#modal-evento-lembrete-texto').text(texto);
            } else {
                $('#modal-evento-lembrete-texto').html('<em style="color:#aaa;">Nenhum lembrete definido.</em>');
            }

            $('#modal-evento-membros').html('<em style="color:#aaa;">Carregando...</em>');
            if (p.id_equipe) {
                api('listarMembrosPorEquipe', { id_equipe: p.id_equipe }).done(function (membros) {
                    if (!membros || !membros.length) {
                        $('#modal-evento-membros').html('<em style="color:#aaa;">Nenhuma pessoa na equipe.</em>');
                        return;
                    }
                    var html = '<ul style="margin:0; padding-left:18px;">';
                    $.each(membros, function (_, m) {
                        html += '<li>' + m.nome + ' ' + (m.sobrenome || '')
                              + (m.cargo ? ' <small class="text-muted">- ' + m.cargo + '</small>' : '') + '</li>';
                    });
                    html += '</ul>';
                    $('#modal-evento-membros').html(html);
                }).fail(function () {
                    $('#modal-evento-membros').html('<em class="text-danger">Erro ao carregar pessoas.</em>');
                });
            } else {
                $('#modal-evento-membros').html('<em style="color:#aaa;">—</em>');
            }

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
                /* Remove o evento otimista (all-day) e busca do servidor o evento já com
                   o horário do turno — inclusive plantão noturno que ocupa os dois dias. */
                event.remove();
                _calRefetch();
                exibirMsgAba('msg-calendario', 'Alocação criada com sucesso!', 'success');
                carregarAlocacoes();

                var pref = localStorage.getItem('wegia_lembrete_modal_pref') || 'always';
                if (pref !== 'never') {
                    $('#lembrete-rapido-id').val(r.id);
                    $('#lembrete-rapido-input').val('');
                    $('#lembrete-rapido-mensagem').val('');
                    $('#lembrete-rapido-pref').val(pref);
                    $('#modal-lembrete-rapido').modal('show');
                }
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

    /* Redimensiona o calendário */
    var _calContentBody = document.querySelector('.content-body');
    if (_calContentBody) {
        _calContentBody.addEventListener('transitionend', function (e) {
            if (window._calendar) window._calendar.updateSize();
        });
    }

    _draggableInst = new FullCalendar.Draggable(
        document.getElementById('sidebar-drag-container'),
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

    preCarregarEquipesCores();
    carregarSidebarAgendas();
    carregarSidebarEquipes();
});

$('#abas-agenda a[href="#tab-calendario"]').on('shown.bs.tab', function () {
    if (window._calendar) window._calendar.updateSize();
    carregarSidebarAgendas();
    carregarSidebarEquipes($('#sidebar-agenda-select').val() || null);
});

$(document).on('change', '#sidebar-agenda-select', function () {
    var idAgenda = $(this).val() || null;
    carregarSidebarEquipes(idAgenda);
    _calRefetch();
});

$(document).on('change', '#sidebar-equipe-select', function () {
    atualizarCardEquipe($(this).val() || null);
});

/* ── Agendas ─────────────────────────────────────────────── */

var _defaultStatusAgendaId = null;

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

function carregarStatusAgenda(selecionado, autoSelecionarPrimeiro) {
    api('listarStatus').done(function (dados) {
        var opts = '<option value="">Selecione...</option>';
        $.each(dados, function (_, s) {
            opts += '<option value="' + s.id + '"' + (s.id == selecionado ? ' selected' : '') + '>' + s.descricao + '</option>';
        });
        $('#agenda-status').html(opts);
        initSelect2('#agenda-status', 'Selecione o status...');
        if (autoSelecionarPrimeiro && dados.length) {
            _defaultStatusAgendaId = String(dados[0].id);
            $('#agenda-status').val(dados[0].id).trigger('change');
        }
    });
}

$('#abas-agenda a[href="#tab-agendas"]').on('shown.bs.tab', carregarAgendas);

$('#btn-nova-agenda').on('click', function () {
    $('#modal-agenda-titulo').text('Nova Agenda');
    $('#agenda-id').val('');
    $('#agenda-descricao').val('');
    ocultarErroModal('modal-agenda-erro');
    $('#agenda-status-grupo').hide();
    carregarStatusAgenda(null, true);
    $('#modal-agenda').modal('show');
});

$(document).on('click', '.btn-editar-agenda', function () {
    var id = $(this).data('id');
    api('listarAgendaPorId', { id: id }).done(function (a) {
        $('#modal-agenda-titulo').text('Editar Agenda');
        $('#agenda-id').val(a.id);
        $('#agenda-descricao').val(a.descricao);
        ocultarErroModal('modal-agenda-erro');
        $('#agenda-status-grupo').show();
        carregarStatusAgenda(a.id_status);
        $('#modal-agenda').modal('show');
    });
});

$(document).on('click', '.btn-excluir-agenda', function () {
    var id = $(this).data('id');
    confirmar('Excluir esta agenda? Todas as equipes e alocações vinculadas também serão excluídas.', function () {
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
    var status    = id ? $('#agenda-status').val() : (_defaultStatusAgendaId || $('#agenda-status option:not([value=""])').first().val());

    ocultarErroModal('modal-agenda-erro');
    if (!descricao) { exibirErroModal('modal-agenda-erro', 'Informe a descrição.'); return; }
    if (id && !status) { exibirErroModal('modal-agenda-erro', 'Selecione o status.'); return; }

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
    var html = '';
    $.each(equipes || [], function (_, e) {
        var mems = $.grep(membros || [], function(m) { return String(m.id_equipe) === String(e.id); });
        
        var grupos = { 'Sem divisão': [] };
        $.each(mems, function(_, m) {
            var divNome = m.nome_divisao || 'Sem divisão'; 
            if (!grupos[divNome]) grupos[divNome] = [];
            
            var cargoHtml = m.cargo ? ' <span style="color:#999; font-size:0.85em;">- ' + m.cargo + '</span>' : '';
            var infoMembro = m.nome_completo.trim() + cargoHtml;
            grupos[divNome].push(infoMembro);
        });

        var membrosHtml = '';
        $.each(grupos, function(divNome, membrosInfo) {
            if (membrosInfo.length > 0) {
                if (divNome === 'Sem divisão') {
                    membrosHtml += '<div style="margin-bottom:4px;">' + membrosInfo.join(' | ') + '</div>';
                } else {
                    membrosHtml += '<div style="margin-bottom:4px;">' +
                                   '<small style="font-weight:700; color:#0088cc; text-transform:uppercase;">' + divNome + ':</small><br>' +
                                   membrosInfo.join(' | ') + 
                                   '</div>';
                }
            }
        });

        var badge = e.status && e.status.toLowerCase() === 'ativo'
            ? '<span class="badge badge-ativo">' + e.status + '</span>'
            : '<span class="badge badge-inativo">' + (e.status || '—') + '</span>';

        html += '<tr>'
            + '<td><strong>' + e.nome + '</strong></td>'
            + '<td>' + (e.descricao || '—') + '</td>'
            + '<td class="membros-cell">' + (membrosHtml || '<span class="text-muted">Nenhum membro</span>') + '</td>'
            + '<td class="text-center">' + fmtTurno(e.inicio_turno, e.fim_turno) + '</td>'
            + '<td class="col-status">' + badge + '</td>'
            + '<td class="col-acoes"><div class="acoes-grupo">'
            + '<button class="btn btn-xs btn-success btn-acao btn-membros-equipe" data-id="' + e.id + '" data-nome="' + e.nome + '" title="Membros"><i class="fa fa-users"></i></button>'

            + '<button class="btn btn-xs btn-info btn-acao btn-editar-equipe" '
            + 'data-id="' + e.id + '" '
            + 'data-nome="' + e.nome + '" '
            + 'data-descricao="' + (e.descricao || '') + '" '
            + 'data-status="' + (e.id_status || '') + '" '
            + 'data-agenda="' + (e.id_agenda || '') + '" '
            + 'data-inicio-turno="' + (e.inicio_turno ? e.inicio_turno.substring(0, 5) : '') + '" '
            + 'data-fim-turno="' + (e.fim_turno ? e.fim_turno.substring(0, 5) : '') + '" '
            + 'title="Editar"><i class="fa fa-pencil"></i></button>'
            
            + '<button class="btn btn-xs btn-danger btn-acao btn-excluir-equipe" data-id="' + e.id + '" title="Excluir"><i class="fa fa-trash"></i></button>'
            + '</div></td></tr>';
    });
    $('#tbody-equipes').html(html);
    dtInit('dt-equipes');
}

function carregarFiltroEquipeAgenda() {
    api('listarAgendas').done(function (agendas) {
        var opts = '<option value="">Todas as agendas</option>';
        $.each(agendas, function (_, a) {
            if (!a.status || a.status.toLowerCase() !== 'ativo') return;
            opts += '<option value="' + a.id + '">' + a.descricao + '</option>';
        });
        $('#filtro-equipe-agenda').html(opts);
        initSelect2('#filtro-equipe-agenda', 'Todas as agendas');
    });
}

function carregarEquipes() {
    var idAgenda     = $('#filtro-equipe-agenda').val() || null;
    var filtroStatus = $('#filtro-equipe-status').val();
    var params       = idAgenda ? { id_agenda: idAgenda } : {};
    api('listarEquipes', params).done(function (equipes) {
        var filtradas = filtroStatus
            ? $.grep(equipes, function (e) { return (e.status || '').toLowerCase() === filtroStatus; })
            : equipes;
        api('listarTodosMembrosAtivos')
            .done(function (membros) { renderTabelaEquipes(filtradas, membros); })
            .fail(function ()        { renderTabelaEquipes(filtradas, []); });
    });
}

var _defaultStatusEquipeId = null;

function carregarStatusEquipe(selecionado, autoSelecionarPrimeiro) {
    api('listarEquipeStatus').done(function (dados) {
        var opts = '<option value="">Selecione...</option>';
        $.each(dados, function (_, s) {
            opts += '<option value="' + s.id + '"' + (s.id == selecionado ? ' selected' : '') + '>' + s.descricao + '</option>';
        });
        $('#equipe-status').html(opts);
        initSelect2('#equipe-status', 'Selecione o status...');
        if (autoSelecionarPrimeiro && dados.length) {
            _defaultStatusEquipeId = String(dados[0].id);
            $('#equipe-status').val(dados[0].id).trigger('change');
        }
    });
}

$('#abas-agenda a[href="#tab-equipes"]').on('shown.bs.tab', function () {
    carregarFiltroEquipeAgenda();
    carregarEquipes();
});

$(document).on('change', '#filtro-equipe-agenda, #filtro-equipe-status', carregarEquipes);

$('#btn-nova-equipe').on('click', function () {
    $('#modal-equipe-titulo').text('Nova Equipe');
    $('#equipe-id').val(''); $('#equipe-nome').val(''); $('#equipe-descricao').val('');
    $('#equipe-inicio-turno').val(''); $('#equipe-fim-turno').val('');
    ocultarErroModal('modal-equipe-erro');
    $('#equipe-status-grupo').hide();
    $('#wrapper-divisoes-equipe').empty();
    carregarSelectAgendaEquipe(null);
    carregarStatusEquipe(null, true);
    $('#modal-equipe').modal('show');
});

$(document).on('click', '.btn-editar-equipe', function () {
    var $b = $(this);
    
    $('#equipe-id').val($b.data('id'));
    $('#equipe-nome').val($b.data('nome'));
    $('#equipe-descricao').val($b.data('descricao'));
    $('#equipe-inicio-turno').val($b.data('inicio-turno'));
    $('#equipe-fim-turno').val($b.data('fim-turno'));
    
    carregarSelectAgendaEquipe($b.data('agenda'));
    carregarStatusEquipe($b.data('status'));
    
    ocultarErroModal('modal-equipe-erro');
    $('#equipe-status-grupo').show();
    
    $('#wrapper-divisoes-equipe').empty();
    api('listarDivisoesPorEquipe', { id_equipe: $b.data('id') }).done(function(divisoes) {
        $.each(divisoes || [], function(_, div) {
            var html = '<div class="input-group mb-xs div-input-wrapper">' +
                       '<input type="text" class="form-control input-sm input-nome-divisao" value="' + div.nome + '" data-id="' + div.id + '" placeholder="Nome da divisão">' +
                       '<span class="input-group-btn">' +
                       '<button class="btn btn-danger btn-sm btn-remover-divisao-input" type="button" title="Remover"><i class="fa fa-trash"></i></button>' +
                       '</span>' +
                       '</div>';
            $('#wrapper-divisoes-equipe').append(html);
        });
    });

    // 5. Abre o modal
    $('#modal-equipe').modal('show');
});

$('#btn-salvar-equipe').on('click', function () {
    var id           = $('#equipe-id').val();
    var id_agenda    = $('#equipe-agenda').val();
    var nome         = $.trim($('#equipe-nome').val());
    var descricao    = $.trim($('#equipe-descricao').val());
    var status       = id ? $('#equipe-status').val() : (_defaultStatusEquipeId || $('#equipe-status option:not([value=""])').first().val());
    var inicio_turno = $('#equipe-inicio-turno').val();
    var fim_turno    = $('#equipe-fim-turno').val();

    ocultarErroModal('modal-equipe-erro');
    if (!id_agenda)    { exibirErroModal('modal-equipe-erro', 'Selecione a agenda.'); return; }
    if (!nome)         { exibirErroModal('modal-equipe-erro', 'Informe o nome da equipe.'); return; }
    if (!inicio_turno) { exibirErroModal('modal-equipe-erro', 'Informe o horário de início do turno.'); return; }
    if (!fim_turno)    { exibirErroModal('modal-equipe-erro', 'Informe o horário de fim do turno.'); return; }
    if (id && !status) { exibirErroModal('modal-equipe-erro', 'Selecione o status.'); return; }

    var divisoesArray = [];
    $('.input-nome-divisao').each(function() {
        var $input = $(this);
        var val = $.trim($input.val());
        var idDiv = $input.data('id') || '';
        
        if(val) {
            divisoesArray.push({ id: idDiv, nome: val });
        }
    });

    var dados = { 
        nome: nome, 
        descricao: descricao, 
        id_status: status, 
        id_agenda: id_agenda, 
        inicio_turno: inicio_turno, 
        fim_turno: fim_turno,
        divisoes: divisoesArray
    };

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

$(document).on('click', '.btn-excluir-equipe', function () {
    var id = $(this).data('id');
    
    confirmar('Deseja realmente excluir esta equipe? Todas as alocações vinculadas a ela também serão removidas.', function () {
        api('excluirEquipe', { id: id })
            .done(function (r) {
                exibirMsgAba('msg-equipes', r.msg || 'Equipe excluída com sucesso.', 'success');
                carregarEquipes(); 
                _calRefetch();
            })
            .fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.erro) ? xhr.responseJSON.erro : 'Erro ao excluir a equipe.';
                exibirMsgAba('msg-equipes', msg, 'danger');
            });
    });
});

$('#btn-nova-divisao-input').on('click', function() {
    var html = '<div class="input-group mb-xs div-input-wrapper">' +
               '<input type="text" class="form-control input-sm input-nome-divisao" placeholder="Nome da divisão">' +
               '<span class="input-group-btn">' +
               '<button class="btn btn-danger btn-sm btn-remover-divisao-input" type="button" title="Remover"><i class="fa fa-trash"></i></button>' +
               '</span>' +
               '</div>';
    $('#wrapper-divisoes-equipe').append(html);
});

$(document).on('click', '.btn-remover-divisao-input', function(e) {
    e.preventDefault();
    $(this).closest('.div-input-wrapper').remove();
});

/* ── Membros ─────────────────────────────────────────────── */    

var _divisoesEquipeAtual = [];

function carregarDivisoes(idEquipe) {
    api('listarDivisoesPorEquipe', { id_equipe: idEquipe }).done(function(divisoes) {
        _divisoesEquipeAtual = divisoes || [];
        
        var html = '';
        if (_divisoesEquipeAtual.length === 0) {
            html = '<span class="text-muted" style="font-size:12px;">Nenhuma divisão cadastrada.</span>';
        } else {
            $.each(_divisoesEquipeAtual, function(_, div) {
                html += '<span class="badge badge-membro" style="font-size:12px; padding:6px 10px; background:#eaf4fb; color:#0088cc; border:1px solid #b3d9f0;">' + div.nome + 
                        ' <a href="#" class="btn-excluir-divisao text-danger" data-id="' + div.id + '" style="margin-left:5px;" title="Excluir"><i class="fa fa-times"></i></a></span>';
            });
        }
        $('#lista-divisoes').html(html);

        var $selectDiv = $('#membro-divisao');
        $selectDiv.empty().append('<option value="">Sem divisão</option>');

        if (_divisoesEquipeAtual.length > 0) {
            $.each(_divisoesEquipeAtual, function(_, div) {
                $selectDiv.append('<option value="' + div.id + '">' + div.nome + '</option>');
            });
            $selectDiv.prop('disabled', false); // Habilita se a equipe tem divisões
        } else {
            $selectDiv.prop('disabled', true); // Mantém bloqueado
        }
        
        initSelect2('#membro-divisao', 'Sem divisão');
        
        carregarMembros(idEquipe);
    });
}

function carregarMembros(idEquipe) {
    api('listarMembrosPorEquipe', { id_equipe: idEquipe }).done(function (dados) {
        var html = '';
        if (!dados || !dados.length) {
            html = '<tr><td colspan="4" class="text-center text-muted">Nenhum membro ativo.</td></tr>';
        } else {
            $.each(dados, function (_, m) {
                var nomeDivisao = 'Sem divisão';
                if (m.id_divisao) {
                    $.each(_divisoesEquipeAtual, function(_, d) {
                        if (d.id == m.id_divisao) nomeDivisao = d.nome;
                    });
                }

                html += '<tr>'
                    + '<td>' + m.nome + ' ' + (m.sobrenome || '') + (m.cargo ? ' - ' + m.cargo : '') + '</td>'
                    + '<td class="text-center">' + fmtTurno(m.inicio_turno, m.fim_turno, true) + '</td>'
                    + '<td class="td-divisao" data-id-divisao="' + (m.id_divisao || '') + '">' + nomeDivisao + '</td>'
                    + '<td>'
                    + '<button class="btn btn-xs btn-info btn-acao mr-xs btn-editar-divisao-membro" data-id="' + m.id + '" title="Alterar Divisão"><i class="fa fa-sitemap"></i></button>'
                    + '<button class="btn btn-xs btn-warning btn-acao mr-xs btn-inativar-membro" data-id="' + m.id + '" title="Inativar"><i class="fa fa-ban"></i></button>'
                    + '<button class="btn btn-xs btn-danger btn-acao btn-excluir-membro" data-id="' + m.id + '" title="Remover"><i class="fa fa-trash"></i></button>'
                    + '</td></tr>';
            });
        }
        $('#tbody-membros').html(html);
    });

    $('#modal-membros').on('hidden.bs.modal', function () {
        if ($('#abas-agenda a[href="#tab-equipes"]').parent().hasClass('active')) {
            carregarEquipes();
        }
    });

    // Histórico de inativos
    api('listarHistoricoMembrosPorEquipe', { id_equipe: idEquipe }).done(function (todos) {
        var inativos = $.grep(todos || [], function (m) { return String(m.ativo) === '0'; });
        if (!inativos.length) {
            $('#btn-toggle-inativos').hide();
            $('#secao-membros-inativos').hide();
            return;
        }
        var html = '';
        $.each(inativos, function (_, m) {
            html += '<tr>'
                + '<td>' + m.nome + ' ' + (m.sobrenome || '') + '</td>'
                + '<td>'
                + '<button class="btn btn-xs btn-success btn-acao btn-reativar-membro" data-id="' + m.id + '" title="Reativar"><i class="fa fa-check"></i></button>'
                + '<button class="btn btn-xs btn-danger btn-acao btn-excluir-membro" data-id="' + m.id + '" title="Remover"><i class="fa fa-trash"></i></button>'
                + '</td></tr>';
        });
        $('#tbody-membros-inativos').html(html);
        $('#btn-toggle-inativos').show();
    });
}

$(document).on('click', '.btn-membros-equipe', function () {
    var id = $(this).data('id'), nome = $(this).data('nome');
    $('#membros-equipe-id').val(id);
    $('#membros-equipe-nome').text(nome);
    ocultarErroModal('modal-membros-erro');
    ocultarErroModal('modal-membros-sucesso');
    $('#secao-membros-inativos').hide();
    $('#btn-toggle-inativos').hide();
    $('#toggle-inativos-label').text('Ver inativos');
    $('#btn-toggle-inativos .fa').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    _carregarSelectPessoas(id);
    carregarDivisoes(id);
    $('#modal-membros').modal('show');
});

$('#btn-adicionar-membro').on('click', function () {
    var idEquipe = $('#membros-equipe-id').val();
    var idPessoa = $('#membro-pessoa').val();
    var idDivisao = $('#membro-divisao').val(); 

    ocultarErroModal('modal-membros-erro');
    if (!idPessoa) { exibirErroModal('modal-membros-erro', 'Selecione uma pessoa.'); return; }

    apiPost('incluirMembro', { 
        id_equipe: idEquipe, 
        id_pessoa: idPessoa,
        id_divisao: idDivisao ? idDivisao : null 
    })
    .done(function (r) {
        ocultarErroModal('modal-membros-erro');
        exibirSucessoModal('modal-membros-sucesso', r.msg || 'Membro adicionado com sucesso.');
        
        $('#membro-pessoa').val('').trigger('change');
        
        $('#membro-divisao').val('').trigger('change'); 
        
        carregarMembros(idEquipe);
        _carregarSelectPessoas(idEquipe);
        carregarEquipes();
    })
    .fail(function (xhr) {
        var msg = (xhr.responseJSON && xhr.responseJSON.erro) ? xhr.responseJSON.erro : 'Erro ao adicionar membro.';
        exibirErroModal('modal-membros-erro', msg);
    });
});
// EVENTO: Transforma o texto da divisão em um Select2
$(document).on('click', '.btn-editar-divisao-membro', function () {
    var $btn = $(this);
    var $tr = $btn.closest('tr');
    var $tdDivisao = $tr.find('.td-divisao');
    var idDivisaoAtual = $tdDivisao.attr('data-id-divisao');

    $btn.removeClass('btn-info btn-editar-divisao-membro')
        .addClass('btn-success btn-salvar-divisao-membro')
        .attr('title', 'Salvar Divisão')
        .html('<i class="fa fa-save"></i>');

    var selectHtml = '<select class="form-control input-sm select-inline-divisao" style="width:100%;">';
    selectHtml += '<option value="">Sem divisão</option>';
    $.each(_divisoesEquipeAtual, function(_, div) {
        var selected = (div.id == idDivisaoAtual) ? 'selected' : '';
        selectHtml += '<option value="' + div.id + '" ' + selected + '>' + div.nome + '</option>';
    });
    selectHtml += '</select>';

    // Joga o select na célula e inicia o plugin Select2
    $tdDivisao.html(selectHtml);
    $tdDivisao.find('.select-inline-divisao').select2({
        dropdownParent: $('#modal-membros') 
    }); 
});

$(document).on('click', '.btn-salvar-divisao-membro', function () {
    var $btn = $(this);
    var $tr = $btn.closest('tr');
    var $tdDivisao = $tr.find('.td-divisao');
    var idMembro = $btn.data('id');
    var $select = $tdDivisao.find('select');
    
    var novaDivisaoId = $select.val();
    var novaDivisaoNome = $select.find('option:selected').text();

    apiPost('atribuirDivisaoMembro', { 
        id_membro: idMembro, 
        id_divisao: novaDivisaoId ? novaDivisaoId : null 
    })
    .done(function(r) {
        exibirSucessoModal('modal-membros-sucesso', r.msg || 'Divisão atualizada!');
        
        $select.select2('destroy');
        $tdDivisao.attr('data-id-divisao', novaDivisaoId).text(novaDivisaoNome);
        
        // Devolve o botão azul de edição
        $btn.removeClass('btn-success btn-salvar-divisao-membro')
            .addClass('btn-info btn-editar-divisao-membro')
            .attr('title', 'Alterar Divisão')
            .html('<i class="fa fa-sitemap"></i>');
    })
    .fail(function(xhr) {
        var msg = (xhr.responseJSON && xhr.responseJSON.erro) ? xhr.responseJSON.erro : 'Erro ao atualizar divisão.';
        exibirErroModal('modal-membros-erro', msg);
    });
});

$(document).on('click', '.btn-inativar-membro', function () {
    var id = $(this).data('id'), idEquipe = $('#membros-equipe-id').val();
    confirmar('Inativar este membro?', function () {
        api('inativarMembro', { id: id })
            .done(function (r) {
                exibirSucessoModal('modal-membros-sucesso', r.msg || 'Membro inativado.');
                carregarMembros(idEquipe);
                _carregarSelectPessoas(idEquipe);
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
                exibirSucessoModal('modal-membros-sucesso', r.msg || 'Membro removido.');
                carregarMembros(idEquipe);
                _carregarSelectPessoas(idEquipe);
                carregarEquipes();
            })
            .fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.erro) ? xhr.responseJSON.erro : 'Erro ao remover.';
                exibirErroModal('modal-membros-erro', msg);
            });
    });
});

$(document).on('click', '#btn-toggle-inativos', function () {
    var $sec = $('#secao-membros-inativos');
    var aberto = $sec.is(':visible');
    $sec.slideToggle(180);
    $('#toggle-inativos-label').text(aberto ? 'Ver inativos' : 'Ocultar inativos');
    $(this).find('.fa').toggleClass('fa-chevron-down', aberto).toggleClass('fa-chevron-up', !aberto);
});

$(document).on('click', '.btn-reativar-membro', function () {
    var id = $(this).data('id'), idEquipe = $('#membros-equipe-id').val();
    confirmar('Reativar este membro?', function () {
        api('reativarMembro', { id: id })
            .done(function (r) {
                exibirSucessoModal('modal-membros-sucesso', r.msg || 'Membro reativado.');
                carregarMembros(idEquipe);
                _carregarSelectPessoas(idEquipe);
                carregarEquipes();
            })
            .fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.erro) ? xhr.responseJSON.erro : 'Erro ao reativar.';
                exibirErroModal('modal-membros-erro', msg);
            });
    });
});

function _carregarSelectPessoas(idEquipe) {
    api('listarPessoas', { id_equipe: idEquipe }).done(function (dados) {
        var opts = '<option value="">Selecione uma pessoa</option>';
        $.each(dados, function (_, p) {
            var label = p.cargo ? p.nome_completo + ' - ' + p.cargo : p.nome_completo;
            opts += '<option value="' + p.id_pessoa + '">' + label + '</option>';
        });
        $('#membro-pessoa').html(opts);
        initSelect2('#membro-pessoa', 'Selecione uma pessoa');
    });
}

/* ── Detalhe do evento (somente visualização) ────────────── */

$('#btn-ir-equipes').on('click', function () {
    if (!_eventoAtual) return;
    var idAgenda = _eventoAtual.p.id_agenda;
    var idEquipe = _eventoAtual.p.id_equipe;
    $('#modal-evento').modal('hide');
    $('#abas-agenda a[href="#tab-equipes"]').tab('show');
    if (idAgenda) $('#filtro-equipe-agenda').val(idAgenda).trigger('change');
    api('listarEquipes', idAgenda ? { id_agenda: idAgenda } : {}).done(function (equipes) {
        var eq = null;
        $.each(equipes, function (_, e) { if (String(e.id) === String(idEquipe)) { eq = e; return false; } });
        if (!eq) return;
        $('#modal-equipe-titulo').text('Editar Equipe');
        $('#equipe-id').val(eq.id);
        $('#equipe-nome').val(eq.nome);
        $('#equipe-descricao').val(eq.descricao || '');
        $('#equipe-inicio-turno').val(eq.inicio_turno || '');
        $('#equipe-fim-turno').val(eq.fim_turno || '');
        ocultarErroModal('modal-equipe-erro');
        $('#equipe-status-grupo').show();
        carregarSelectAgendaEquipe(eq.id_agenda);
        carregarStatusEquipe(eq.id_status);
        $('#modal-equipe').modal('show');
    });
});

$('#btn-ir-alocacoes').on('click', function () {
    if (!_eventoAtual) return;
    var ev = _eventoAtual, p = ev.p;
    $('#modal-evento').modal('hide');
    $('#abas-agenda a[href="#tab-alocacoes"]').tab('show');
    if (p.id_agenda) $('#filtro-alocacao-agenda').val(p.id_agenda).trigger('change');
    $('#modal-alocacao-titulo').text('Editar Alocação');
    $('#alocacao-id').val(ev.id);
    $('#alocacao-inicio').val((p.inicio_original || ev.start).substring(0, 10));
    $('#alocacao-fim').val((p.fim_original || p.fim_display || '').substring(0, 10));
    $('#alocacao-lembrete').val(p.lembrete ? p.lembrete.replace(' ', 'T').substring(0, 16) : '');
    $('#alocacao-intervalo').val(p.intervalo || 0);
    ocultarErroModal('modal-alocacao-erro');
    carregarSelectsAlocacao(p.id_agenda, p.id_equipe);
    $('#modal-alocacao').modal('show');
});

/* ── Lembrete rápido (pós-arrastar) ─────────────────────── */

$('#lembrete-rapido-pref').on('change', function () {
    localStorage.setItem('wegia_lembrete_modal_pref', $(this).val());
});

$('#btn-lembrete-rapido-pular').on('click', function () {
    localStorage.setItem('wegia_lembrete_modal_pref', $('#lembrete-rapido-pref').val());
    $('#modal-lembrete-rapido').modal('hide');
});

$('#btn-lembrete-rapido-salvar').on('click', function () {
    var id       = $('#lembrete-rapido-id').val();
    var val      = $('#lembrete-rapido-input').val();
    var mensagem = $.trim($('#lembrete-rapido-mensagem').val());
    localStorage.setItem('wegia_lembrete_modal_pref', $('#lembrete-rapido-pref').val());
    if (!val) { $('#modal-lembrete-rapido').modal('hide'); return; }
    apiPost('salvarLembrete', { id: id, lembrete: val.replace('T', ' '), mensagem: mensagem })
        .done(function () {
            $('#modal-lembrete-rapido').modal('hide');
            _calRefetch();
            carregarAlocacoes();
        })
        .fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.erro) ? xhr.responseJSON.erro : 'Erro ao salvar lembrete.';
            alert(msg);
        });
});

/* ── Alocações ───────────────────────────────────────────── */

function carregarFiltroAlocacaoAgenda() {
    api('listarAgendas').done(function (agendas) {
        var opts = '<option value=""></option>';
        $.each(agendas, function (_, a) {
            if (!a.status || a.status.toLowerCase() !== 'ativo') return;
            opts += '<option value="' + a.id + '">' + a.descricao + '</option>';
        });
        $('#filtro-alocacao-agenda').html(opts);
        initSelect2('#filtro-alocacao-agenda', 'Selecione a agenda...');
    });
}

function carregarAlocacoes() {
    var idAgenda = $('#filtro-alocacao-agenda').val();
    var idEquipe = $('#filtro-alocacao-equipe').val();

    if (!idAgenda || !idEquipe) {
        var msgPlaceholder = !idAgenda ? 'Selecione uma agenda e uma equipe para visualizar as alocações.' : 'Selecione uma equipe para visualizar as alocações.';
        $('#tbody-alocacoes').html('<tr><td colspan="7" class="text-center text-muted">' + msgPlaceholder + '</td></tr>');
        dtInit('dt-alocacoes');
        return; 
    }

    api('listarTodasAlocacoes').done(function (dados) {
        var lista = dados || [];
        
        lista = $.grep(lista, function(al) { 
            return String(al.id_agenda) === String(idAgenda) && String(al.id_equipe) === String(idEquipe); 
        });

        var html = '';
        if (lista.length === 0) {
            html = '<tr><td colspan="7" class="text-center text-muted">Nenhuma alocação encontrada para esta equipe.</td></tr>';
        } else {
            $.each(lista, function(_, al) {
                var intervalo = parseInt(al.intervalo) || 0;
                var turno = fmtTime(al.inicio_turno) + ' – ' + fmtTime(al.fim_turno);
                
                html += '<tr>'
                    + '<td>' + al.equipe + '</td>'
                    + '<td>' + fmtDate(al.start) + '</td>'
                    + '<td>' + fmtDate(al.fim_display) + '</td>'
                    + '<td class="text-center">' + turno + '</td>'
                    + '<td class="text-center">' + intervalo + (intervalo === 1 ? ' dia' : ' dias') + '</td>'
                    + '<td>' + fmtDatetime(al.lembrete) + '</td>'
                    + '<td class="col-acoes"><div class="acoes-grupo">'
                    + '<button class="btn btn-xs btn-info btn-acao btn-editar-alocacao" '
                    + 'data-id="'+al.id+'" data-agenda="'+al.id_agenda+'" data-equipe="'+al.id_equipe+'" '
                    + 'data-inicio="'+(al.start||'').substring(0,10)+'" data-fim="'+(al.fim_display||'').substring(0,10)+'" '
                    + 'data-lembrete="'+(al.lembrete||'').replace(' ','T').substring(0,16)+'" data-intervalo="'+intervalo+'" '
                    + 'title="Editar"><i class="fa fa-pencil"></i></button>'
                    + '<button class="btn btn-xs btn-danger btn-acao btn-excluir-alocacao" data-id="'+al.id+'" title="Excluir"><i class="fa fa-trash"></i></button>'
                    + '</div></td></tr>';
            });
        }
        $('#tbody-alocacoes').html(html);
        dtInit('dt-alocacoes');
    });
}

$(document).on('change', '#filtro-alocacao-agenda', function () {
    var idAgenda = $(this).val();
    var $selEquipe = $('#filtro-alocacao-equipe');
    
    if (!idAgenda) {
        $selEquipe.html('<option value="">Selecione a agenda...</option>').prop('disabled', true);
        initSelect2('#filtro-alocacao-equipe', 'Selecione a agenda...');
        carregarAlocacoes(); 
        return;
    }

    api('listarEquipes', { id_agenda: idAgenda }).done(function (equipes) {
        var opts = '<option value=""></option>'; 
        $.each(equipes, function (_, e) {
            if (e.status && e.status.toLowerCase() === 'ativo') {
                opts += '<option value="' + e.id + '">' + e.nome + '</option>';
            }
        });
        $selEquipe.html(opts).prop('disabled', false);
        initSelect2('#filtro-alocacao-equipe', 'Selecione a equipe...');
    });
});

$(document).on('change', '#filtro-alocacao-equipe', carregarAlocacoes);

function carregarSelectEquipePorAgenda(idAgenda, selEquipe) {
    var $sel = $('#alocacao-equipe');
    if (!idAgenda) {
        $sel.html('<option value="">Selecione a agenda primeiro...</option>').prop('disabled', true);
        initSelect2('#alocacao-equipe', 'Selecione a agenda primeiro...');
        return;
    }
    api('listarEquipes', { id_agenda: idAgenda }).done(function (equipes) {
        var opts = '<option value="">Selecione...</option>';
        $.each(equipes, function (_, e) {
            if (!e.status || e.status.toLowerCase() !== 'ativo') return;
            opts += '<option value="' + e.id + '"' + (e.id == selEquipe ? ' selected' : '') + '>' + e.nome + '</option>';
        });
        $sel.html(opts).prop('disabled', false);
        initSelect2('#alocacao-equipe', 'Selecione a equipe...');
    });
}

function carregarSelectsAlocacao(selAgenda, selEquipe) {
    api('listarAgendas').done(function (agendas) {
        var opts = '<option value="">Selecione...</option>';
        $.each(agendas, function (_, a) {
            if (!a.status || a.status.toLowerCase() !== 'ativo') return;
            opts += '<option value="' + a.id + '"' + (a.id == selAgenda ? ' selected' : '') + '>' + a.descricao + '</option>';
        });
        $('#alocacao-agenda').html(opts);
        initSelect2('#alocacao-agenda', 'Selecione a agenda...');
        carregarSelectEquipePorAgenda(selAgenda, selEquipe);
    });
}

$(document).on('change', '#alocacao-agenda', function () {
    carregarSelectEquipePorAgenda($(this).val() || null, null);
});

$('#abas-agenda a[href="#tab-alocacoes"]').on('shown.bs.tab', function () {
    carregarFiltroAlocacaoAgenda();
    carregarAlocacoes();
});

$(document).on('change', '#filtro-alocacao-agenda', carregarAlocacoes);

$('#btn-nova-alocacao').on('click', function () {
    $('#modal-alocacao-titulo').text('Nova Alocação');
    $('#alocacao-id').val(''); $('#alocacao-inicio').val(''); $('#alocacao-fim').val('');
    $('#alocacao-lembrete').val(''); $('#alocacao-intervalo').val('0');
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
    $('#alocacao-intervalo').val($b.data('intervalo') || 0);
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
    var intervalo  = parseInt($('#alocacao-intervalo').val()) || 0;
    if (intervalo < 0) intervalo = 0;

    ocultarErroModal('modal-alocacao-erro');
    if (!agenda) { exibirErroModal('modal-alocacao-erro', 'Selecione a agenda.'); return; }
    if (!equipe) { exibirErroModal('modal-alocacao-erro', 'Selecione a equipe.'); return; }
    if (!inicio) { exibirErroModal('modal-alocacao-erro', 'Informe a data/hora de início.'); return; }
    if (!fim)    { exibirErroModal('modal-alocacao-erro', 'Informe a data/hora de fim.'); return; }
    if (inicio > fim) { exibirErroModal('modal-alocacao-erro', 'O início não pode ser maior que o fim.'); return; }

    var dados = {
        id_agenda: agenda, id_equipe: equipe,
        inicio:    inicio,
        fim:       fim,
        intervalo: intervalo,
        lembrete:  lembrete ? lembrete.replace('T', ' ') : ''
    };
    if (id) dados.id = id;

    apiPost(id ? 'alterarAlocacao' : 'incluirAlocacao', dados)
        .done(function (r) {
            $('#modal-alocacao').modal('hide');
            exibirMsgAba('msg-alocacoes', r.msg || 'Salvo com sucesso.', 'success');
            carregarAlocacoes();
            _calRefetch();
        })
        .fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.erro) ? xhr.responseJSON.erro : 'Erro ao salvar.';
            exibirErroModal('modal-alocacao-erro', msg);
        });
});

/*  Download do Relatório PDF */

$(document).on('click', '#btn-download-mensal', function () {
    var idAgenda = $('#sidebar-agenda-select').val();
    if (!idAgenda) {
        exibirMsgAba('msg-calendario', 'Selecione uma Agenda primeiro para baixar o relatório.', 'warning');
        return;
    }
    
    var dataView = window._calendar.getDate();
    var mes = dataView.getMonth() + 1; 
    var ano = dataView.getFullYear();
    
    var url = '../../service/relatorioGradeHorariosService.php?id_agenda=' + idAgenda + '&mes=' + mes + '&ano=' + ano;
    
    var $btn = $(this);
    var conteudoOriginal = $btn.html();
    
    $btn.css({
        'width': $btn.outerWidth() + 'px',
        'height': $btn.outerHeight() + 'px'
    });

    $btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

    fetch(url)
        .then(async response => {
            if (!response.ok) {
                const erroData = await response.json();
                throw new Error(erroData.erro || erroData.msg || 'Erro desconhecido ao gerar a agenda.');
            }
            return response.blob();
        })
        .then(blob => {
            const blobUrl = window.URL.createObjectURL(blob);
            window.open(blobUrl, '_blank');
        })
        .catch(error => {
            exibirMsgAba('msg-calendario', error.message, 'danger');
        })
        .finally(() => {
            $btn.html(conteudoOriginal)
                .prop('disabled', false)
                .css({ 'width': '', 'height': '' });
        });
});
</script>
</body>
</html>
