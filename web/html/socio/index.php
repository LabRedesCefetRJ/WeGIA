<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'sessaoCookie.php';
require("./conexao.php");
if (!$conexao) {
	header("Location: ./erros/bd_erro/");
	exit();
}
session_start();
if (isset($_SESSION['usuario'])) {
	header("Location: ./sistema/");
	exit();
} else {
	header("Location: /WeGIA/index.php");
	exit();
}
