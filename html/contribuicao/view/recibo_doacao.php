<?php
session_start();
$title = 'Gerar Recibo de Doação';
require_once __DIR__ . '/templates/header.php';
?>
<div class="container-contact100">
    <div class="wrap-contact100">
        <h2>Gerar Recibo de Doação</h2>
        <form id="formulario-recibo" autocomplete="off">
            <div class="form-group">
                <label>CPF do Sócio</label>
                <input type="text" name="cpf" id="cpf" maxlength="14" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Data de Início</label>
                <input type="date" name="data_inicio" id="data_inicio" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Data de Fim</label>
                <input type="date" name="data_fim" id="data_fim" class="form-control" required>
            </div>
            <input type="hidden" name="csrf_token" id="csrf_token">
            <button type="submit" class="btn btn-primary">Gerar Recibo</button>
        </form>
        <div id="mensagem-resultado" class="mt-3" style="display:none">
            <div class="alert" id="alert-box">
                <span id="mensagem-texto"></span>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="../public/js/recibo.js"></script>
<?php require_once __DIR__ . '/templates/footer.php'; ?>