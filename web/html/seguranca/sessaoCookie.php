<?php
/*
 * GHSA-76r2-qcmq-cjrv: configura o cookie de sessão (PHPSESSID) antes de
 * qualquer session_start()/session_regenerate_id(). Precisa ser incluído
 * por toda página que inicia a sessão — direto ou via security_headers.php.
 *
 * Fica no código (e não em .htaccess/.user.ini) para valer em qualquer
 * servidor: Apache com ou sem AllowOverride, nginx, mod_php ou PHP-FPM.
 *
 * - HttpOnly: o JavaScript da página não consegue ler o cookie, então um
 *   XSS não rouba a sessão.
 * - SameSite=Lax: o cookie não acompanha requisições disparadas por outros
 *   sites (POST, fetch, iframe), só a navegação normal por link — Lax e não
 *   Strict para não quebrar o retorno dos gateways de pagamento.
 * - use_strict_mode: o PHP recusa IDs de sessão que ele não gerou
 *   (session fixation).
 * - Secure: só quando a requisição chegou por HTTPS, para não derrubar o
 *   login de instalações ainda servidas por HTTP.
 *
 * Páginas que passam por seguranca/sessionStart.php definem os próprios
 * parâmetros (mais restritivos) com session_set_cookie_params(), que têm
 * prioridade sobre estes.
 */

// Configurar a sessão depois de iniciada (ou depois de enviar headers) gera
// warning e não tem efeito: nesses casos não há o que fazer aqui.
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    $requisicaoHttps = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');

    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', '1');

    if ($requisicaoHttps) {
        ini_set('session.cookie_secure', '1');
    }
}
