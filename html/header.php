<?php
if (session_status() === PHP_SESSION_NONE)
	session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';

$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$id_pessoa = filter_var($_SESSION['id_pessoa'], FILTER_SANITIZE_NUMBER_INT);

if (!$id_pessoa || $id_pessoa < 1) {
	http_response_code(412);
	header("Location: ../index.php");
	exit();
}

// ===== Consulta pessoa (imagem e nome) =====
$sqlPessoa = "SELECT imagem, nome FROM pessoa WHERE id_pessoa = ?";
$stmtPessoa = $conexao->prepare($sqlPessoa);
$stmtPessoa->bind_param("i", $id_pessoa);
$stmtPessoa->execute();

$resultPessoa = $stmtPessoa->get_result();
$pessoa = $resultPessoa->fetch_assoc();

$stmtPessoa->close();


// ===== Consulta cargo =====
$sqlCargo = "
    SELECT c.cargo, c.id_cargo
    FROM pessoa p
    JOIN funcionario f ON f.id_pessoa = p.id_pessoa
    JOIN cargo c ON c.id_cargo = f.id_cargo
    WHERE p.id_pessoa = ?
";

$stmtCargo = $conexao->prepare($sqlCargo);
$stmtCargo->bind_param("i", $id_pessoa);
$stmtCargo->execute();

$resultCargo = $stmtCargo->get_result();
$cargo = $resultCargo->fetch_assoc();

$stmtCargo->close();


// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';
require_once ROOT . "/html/personalizacao_display.php";
?>

<head>
	<style>
		.alerta,
		.userbox {
			display: inline;
		}

		.alerta a {
			margin-right: 30px;
			color: #3498db;
		}

		.badge {
			font-size: 1rem;
			position: absolute;
			top: 5px;
			background-color: red;
		}

		.fa.fa-bell {
			font-size: 2rem;
		}

		@media(max-width:768px) {
			.alerta {
				margin-left: 30px;
				display: inline-flex;
				position: relative;
				height: 50px;
				padding-top: 20px;
			}

		}
	</style>
</head>

<header class="header">
	<div class="logo-container">
		<a href="<?php echo WWW; ?>html/home.php" class="logo">
			<img src="<?php display_campo("Logo", 'file'); ?>" height="35" alt="Porto Admin" />
		</a>
		<div class="visible-xs toggle-sidebar-left" data-toggle-class="sidebar-left-opened" data-target="html" data-fire-event="sidebar-left-opened">
			<i style="margin-top: 8px;" class="fa fa-bars" aria-label="Toggle sidebar"></i>
		</div>
	</div>

	<!-- start: search & user box -->
	<div class="header-right">
		<span class="separator"></span>
		<div class="alerta">
			<?php
			$idCargo = $cargo['id_cargo'];
			$idModuloSaude = 5;
			$resultado = mysqli_query($conexao, "SELECT * FROM permissao p JOIN acao a ON(p.id_acao=a.id_acao) JOIN recurso r ON(p.id_recurso=r.id_recurso) WHERE id_cargo=$idCargo AND r.id_recurso=$idModuloSaude");

			if (!is_bool($resultado) and mysqli_num_rows($resultado)) {
				$permissao = mysqli_fetch_array($resultado);
				if ($permissao['id_acao'] >= 5) {
					require_once ROOT . '/controle/AvisoNotificacaoControle.php';
					$avisoNotificacaoControle = new AvisoNotificacaoControle();
					$quantidadeNotificações = $avisoNotificacaoControle->quantidadeRecentes($id_pessoa);
					$paginaIntercorrencia = WWW . 'html/saude/intercorrencia_visualizar.php';
					if ($quantidadeNotificações > 0) {
						echo '<a href="' . $paginaIntercorrencia . '">Intercorrências <i class="fa fa-bell" aria-hidden="true"></i><span class="badge">' . $quantidadeNotificações . '</span></a>'; //Corrigir endereço
					} else {
						echo '<a href="' . $paginaIntercorrencia . '">Intercorrências <i class="fa fa-bell" aria-hidden="true"></i></a>';
					}
				}
			}

			?>
		</div>
		<div id="userbox" class="userbox">
			<a href="#" data-toggle="dropdown">
				<figure class="profile-picture">
					<?php
					if (isset($_SESSION['id_pessoa']) and !empty($_SESSION['id_pessoa'])) {
						$foto = $pessoa['imagem'];
						if ($foto != null and $foto != "")
							$foto = 'data:image;base64,' . $foto;
						else $foto = WWW . "img/semfoto.png";
					}

					?>
					<img src="<?php echo ($foto); ?>" alt="Joseph Doe" class="img-circle" />
				</figure>
				<div class="profile-info" data-lock-name="John Doe" data-lock-email="johndoe@okler.com">
					<span class="name"><?php echo ($pessoa['nome']); ?></span>
					<span class="role"><?php echo ($cargo['cargo']); ?></span>
				</div>
				<i class="fa custom-caret"></i>
			</a>

			<div class="dropdown-menu">
				<ul class="list-unstyled">
					<li class="divider"></li>
					<li>
						<a role="menuitem" tabindex="-1" href="<?php echo WWW; ?>html/alterar_senha.php"><i class="glyphicon glyphicon-lock"></i> Alterar senha</a>
					</li>
					<li>
						<a role="menuitem" tabindex="-1" href="<?php echo WWW; ?>html/logout.php"><i class="fa fa-power-off"></i> Sair da sessão</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<!-- end: search & user box -->
</header>