<?php
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

