<?php
// Cookie de sessão (GHSA-76r2-qcmq-cjrv): as páginas que incluem este
// arquivo o fazem antes do session_start().
require_once __DIR__ . DIRECTORY_SEPARATOR . 'sessaoCookie.php';

// Evita envio duplicado
if (headers_sent()) {
    return;
}

// Clickjacking
header('X-Frame-Options: DENY');
header("Content-Security-Policy: frame-ancestors 'none'");

// Outros headers recomendados
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

