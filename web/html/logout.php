<?php

session_start();

session_destroy();
require_once "../config.php";

header("Set-Cookie: PHPSESSID=; expires=".(time() - 3600).";path=/; domain=".DB_HOST.";SameSite=Strict;HttpOnly=On;Secure");
header("Location: ../index.php");

?>