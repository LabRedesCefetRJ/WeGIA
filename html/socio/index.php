<?php
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
