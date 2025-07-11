<?php

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
}

$config_path = "config.php";
if (file_exists($config_path)) {
    require_once($config_path);
} else {
    while (true) {
        $config_path = "../" . $config_path;
        if (file_exists($config_path)) break;
    }
    require_once($config_path);
}

require_once '../../controle/AvisoNotificacaoControle.php';

$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$id_pessoa = mysqli_real_escape_string($conexao, $_SESSION['id_pessoa']);
$resultado = mysqli_query($conexao, "SELECT * FROM funcionario WHERE id_pessoa=$id_pessoa");
if (!is_null($resultado)) {
    $id_cargo = mysqli_fetch_array($resultado);
    if (!is_null($id_cargo)) {
        $id_cargo = $id_cargo['id_cargo'];
    }
    //Alterar essa busca pelo resultado
    $resultado = mysqli_query($conexao, "SELECT * FROM permissao p JOIN acao a ON(p.id_acao=a.id_acao) JOIN recurso r ON(p.id_recurso=r.id_recurso) WHERE id_cargo=$id_cargo AND p.id_acao >=5  AND p.id_recurso=5");
    if (!is_bool($resultado) and mysqli_num_rows($resultado)) {
        $permissao = mysqli_fetch_array($resultado);
        $permissao = $permissao['id_acao'];
    } else {
        $permissao = 1;
        $msg = "Você não tem as permissões necessárias para essa página.";
        header("Location: ../home.php?msg_c=$msg");
    }
} else {
    $permissao = 1;
    $msg = "Você não tem as permissões necessárias para essa página.";
    header("Location: ../../home.php?msg_c=$msg");
}

// Adiciona a Função display_campo($nome_campo, $tipo_campo)
require_once "../personalizacao_display.php";

$avisoNotificacaoControle = new AvisoNotificacaoControle();
$recentes = $avisoNotificacaoControle->listarRecentes($id_pessoa);
$historicos = $avisoNotificacaoControle->listarHistoricos($id_pessoa);

/**Transforma as datas para o formato brasileiro e protege contra XSS*/
function formataEProtege(array $intercorrencias){
    foreach($intercorrencias as $num => $intercorrencia){
        $data = new DateTime($intercorrencia['data']);
        $intercorrencias[$num]['data'] = $data->format('d/m/Y h:i:s');
        $intercorrencias[$num]['descricao'] = htmlspecialchars($intercorrencia['descricao']);
    }

    return $intercorrencias;
}

$recentesJSON =  json_encode(
    formataEProtege($recentes)
);
$historicoJSON = json_encode(
    formataEProtege($historicos)
);

echo "<script>let recentes = $recentesJSON; let historico = $historicoJSON</script>";

?>

<!doctype html>
<html class="fixed">

<head>

    <style>
        .card {
            margin-top: 20px;
            background-color: white;
            padding: 10px;
            border-radius: 10px;
        }
    </style>

    <!-- Basic -->
    <meta charset="UTF-8">

    <title>Lista de Intercorrências</title>

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">

    <!-- Specific Page Vendor CSS -->
    <link rel="stylesheet" href="../../assets/vendor/select2/select2.css" />
    <link rel="stylesheet" href="../../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />

    <!-- Skin CSS -->
    <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />

    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">

    <!-- Head Libs -->
    <script src="../../assets/vendor/modernizr/modernizr.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">

    <!-- Vendor -->
    <script src="../../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
    <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

    <!-- Specific Page Vendor -->
    <script src="../../assets/vendor/jquery-autosize/jquery.autosize.js"></script>

    <!-- Theme Base, Components and Settings -->
    <script src="../../assets/javascripts/theme.js"></script>

    <!-- Theme Custom -->
    <script src="../../assets/javascripts/theme.custom.js"></script>

    <!-- Theme Initialization Files -->
    <script src="../../assets/javascripts/theme.init.js"></script>

    <!-- javascript functions -->
    <script src="../../Functions/onlyNumbers.js"></script>
    <script src="../../Functions/onlyChars.js"></script>
    <script src="../../Functions/enviar_dados.js"></script>
    <script src="../../Functions/mascara.js"></script>
    <!-- jquery functions -->
    <script>
        $(function() {
            $("#header").load("../header.php");
            $(".menuu").load("../menu.php");
        });

        function exibirRecentes() {
            let exibidos = document.getElementById('exibidos');
            let conteudo = recentes;
            let impressao = '';

            if (conteudo.length == 0) {
                impressao = '<br><p>Nenhum conteúdo disponível para ser visualizado foi encontrado no momemento.</p>';
            } else {
                impressao = conteudo.map(function(item) {
                    return `<div class="col-md-8">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><strong>Paciente:</strong> ${item.atendido_nome} ${item.atendido_sobrenome}</h5>
                                    <p class="card-text"><strong>Descrição:</strong> ${item.descricao}<br><strong>Registrada em:</strong> ${item.data}<br><strong>Responsável pelo registro:</strong> ${item.funcionario_nome} ${item.funcionario_sobrenome}</p>
                                    <form action="../../controle/control.php" method="POST">
                                        <input type="hidden" name="id_notificacao" value="${item.id_aviso_notificacao}">
                                        <input type="hidden" name="nomeClasse" value="AvisoNotificacaoControle">
                                        <input type="hidden" name="metodo" value="mudarStatus">
                                        <input type="submit" class="btn btn-primary" value="Confirmar Leitura">
                                    </form>
                                </div>
                            </div>
                        </div>`;
                }).join('\n');
            }

            exibidos.innerHTML = impressao; 
        }

        function exibirHistorico() {
            let exibidos = document.getElementById('exibidos');
            let conteudo = historico;
            let impressao = '';
            
            if (conteudo.length == 0) {
                impressao = '<br><p>Nenhum conteúdo disponível para ser visualizado foi encontrado no momemento.</p>';
            } else {

                impressao = conteudo.map(function(item) {
                    return `<div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                             <h5 class="card-title"><strong>Paciente:</strong> ${item.atendido_nome} ${item.atendido_sobrenome}</h5>
                              <p class="card-text"><strong>Descrição:</strong> ${item.descricao}<br><strong>Registrada em:</strong> ${item.data}<br><strong>Responsável pelo registro:</strong> ${item.funcionario_nome} ${item.funcionario_sobrenome}</p>
                         </div>
                    </div>
                </div>`;
                }).join('\n');
            }

            exibidos.innerHTML = impressao;
        }
    </script>
</head>

<body>
    <section class="body">
        <!-- start: header -->
        <div id="header"></div>
        <!-- end: header -->
        <div class="inner-wrapper">
            <!-- start: sidebar -->
            <aside id="sidebar-left" class="sidebar-left menuu"></aside>

            <!-- end: sidebar -->
            <section role="main" class="content-body">
                <header class="page-header">
                    <h2>Intercorrências</h2>

                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li><a href="../index.php"> <i class="fa fa-home"></i>
                                </a></li>
                            <li><span>Visualizar Intercorrências</span></li>
                        </ol>

                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>

                <!-- start: page -->

                </header>

                <!-- start: page -->

                <section class="container">
                    <button class="btn btn-primary" onclick="exibirRecentes();">Recentes</button>
                    <button class="btn btn-primary" onclick="exibirHistorico();">Histórico</button>

                    <div class="row" id="exibidos">
                        
                    </div>
                </section>

                <!-- end: page -->

                <!-- Vendor -->
                <script src="../../assets/vendor/select2/select2.js"></script>
                <script src="../../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
                <script src="../../assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
                <script src="../../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>

                <!-- Theme Base, Components and Settings -->
                <script src="../../assets/javascripts/theme.js"></script>

                <!-- Theme Custom -->
                <script src="../../assets/javascripts/theme.custom.js"></script>

                <!-- Theme Initialization Files -->
                <script src="../../assets/javascripts/theme.init.js"></script>


                <!-- Examples -->
                <script src="../../assets/javascripts/tables/examples.datatables.default.js"></script>
                <script src="../../assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
                <script src="../../assets/javascripts/tables/examples.datatables.tabletools.js"></script>

                <div align="right">
                    <iframe src="https://www.wegia.org/software/footer/saude.html" width="200" height="60" style="border:none; margin-top:150px;"></iframe>
                </div>
            </section>
    </section>
    <script>
        exibirRecentes();
    </script>
</body>

</html>