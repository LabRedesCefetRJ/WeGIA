<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
Util::definirFusoHorario();
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header('Location: ../../index.php');
    exit;
}

require_once '../permissao/permissao.php'; 
require_once '../personalizacao_display.php';
require_once '../geral/msg.php';
require_once '../../classes/Csrf.php';
require_once '../../dao/Conexao.php';

$idFiliado = filter_input(INPUT_GET, 'id_filiado', FILTER_VALIDATE_INT);
$idFuncionario = filter_input(INPUT_GET, 'id_funcionario', FILTER_VALIDATE_INT);

permissao($_SESSION['id_pessoa'], 11, 7);

if (!$idFiliado || $idFiliado < 1 || !$idFuncionario || $idFuncionario < 1) {
    http_response_code(400);
    exit('Os dados da filiação informada não são válidos.');
}

try {
    $pdo = Conexao::connect();

    $stmt = $pdo->prepare(
        'SELECT fi.id_filiado, fi.id_parentesco,
                par.descricao AS parentesco, p.cpf, p.nome, p.sexo, p.email, p.telefone,
                p.data_nascimento, p.cep, p.estado, p.cidade, p.bairro, p.logradouro,
                p.numero_endereco, p.complemento, p.ibge, p.registro_geral,
                p.orgao_emissor, p.data_expedicao,
                pf.nome AS nome_funcionario, pf.sobrenome AS sobrenome_funcionario
         FROM filiacao fi
         INNER JOIN funcionario responsavel ON responsavel.id_pessoa = fi.id_pessoa
         INNER JOIN pessoa p ON p.id_pessoa = fi.id_filiado
         INNER JOIN pessoa pf ON pf.id_pessoa = fi.id_pessoa
         INNER JOIN parentesco par ON par.id_parentesco = fi.id_parentesco
         WHERE fi.id_filiado = :id_filiado AND responsavel.id_funcionario = :id_funcionario'
    );
    $stmt->execute([
        ':id_filiado' => $idFiliado,
        ':id_funcionario' => $idFuncionario,
    ]);
    $filiacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$filiacao) {
        http_response_code(404);
        exit('A filiação informada não foi encontrada.');
    }

    $parentescos = $pdo->query('SELECT id_parentesco, descricao FROM parentesco ORDER BY descricao ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Erro ao buscar as informações da filiação.');
}

$h = static function ($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
};
$dataHoje = date('Y-m-d');
$nomeFuncionario = trim(($filiacao['nome_funcionario'] ?? '') . ' ' . ($filiacao['sobrenome_funcionario'] ?? ''));
$perfilUrl = 'profile_funcionario.php?id_funcionario=' . (int)$idFuncionario . '#filiacao';
?>
<!doctype html>
<html class="fixed">

<head>
    <meta charset="UTF-8">
    <title>Editar Filiação</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <link rel="icon" href="<?php display_campo('Logo', 'file'); ?>" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
    <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
    <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
    <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
    <script src="../../assets/vendor/modernizr/modernizr.js"></script>
</head>

<body>
    <section class="body">
        <div id="header"></div>
        <div class="inner-wrapper">
            <aside id="sidebar-left" class="sidebar-left menuu"></aside>
            <section role="main" class="content-body">
                <header class="page-header">
                    <h2>Editar Filiação</h2>
                    <div class="right-wrapper pull-right">
                        <ol class="breadcrumbs">
                            <li><a href="home.php"><i class="fa fa-home"></i></a></li>
                            <li><span>Páginas</span></li>
                            <li><span>Filiação</span></li>
                        </ol>
                        <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                    </div>
                </header>

                <?php sessionMsg(); ?>
                <div class="panel">
                    <div class="panel-body">
                        <h3>Filiação de <?= $h($nomeFuncionario) ?></h3>
                        <p class="text-muted">Atualize os dados de <?= $h($filiacao['nome']) ?> nas abas abaixo.</p>

                        <div class="tabs">
                            <ul class="nav nav-tabs tabs-primary">
                                <li class="active"><a href="#informacoes-pessoais" data-toggle="tab">Informações Pessoais</a></li>
                                <li><a href="#documentacao" data-toggle="tab">Documentação</a></li>
                                <li><a href="#endereco" data-toggle="tab">Endereço</a></li>
                            </ul>

                            <div class="tab-content">
                                <div id="informacoes-pessoais" class="tab-pane active">
                                    <form class="form-horizontal" action="../../controle/control.php" method="post">
                                        <?= Csrf::inputField() ?>
                                        <input type="hidden" name="nomeClasse" value="FiliacaoControle">
                                        <input type="hidden" name="metodo" value="editarInfoPessoal">
                                        
                                        <input type="hidden" name="id_filiado" value="<?= (int)$idFiliado ?>">
                                        <input type="hidden" name="id_funcionario" value="<?= (int)$idFuncionario ?>">

                                        <h4 class="mb-xlg">Informações Pessoais</h4>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="parentesco">Parentesco</label>
                                            <div class="col-md-6">
                                                <select class="form-control" name="id_parentesco" id="parentesco" required>
                                                    <option value="" disabled>Selecionar...</option>
                                                    <?php foreach ($parentescos as $parentesco): ?>
                                                        <option value="<?= (int)$parentesco['id_parentesco'] ?>" <?= (int)$parentesco['id_parentesco'] === (int)$filiacao['id_parentesco'] ? 'selected' : '' ?>><?= $h($parentesco['descricao']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="nome">Nome</label>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="nome" id="nome" value="<?= $h($filiacao['nome']) ?>" required minlength="2">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="genero">Gênero</label>
                                            <div class="col-md-6">
                                                <select class="form-control" name="genero" id="genero">
                                                    <option value="">Não informado</option>
                                                    <option value="m" <?= $filiacao['sexo'] === 'm' ? 'selected' : '' ?>>Masculino</option>
                                                    <option value="f" <?= $filiacao['sexo'] === 'f' ? 'selected' : '' ?>>Feminino</option>
                                                    <option value="o" <?= $filiacao['sexo'] === 'o' ? 'selected' : '' ?>>Outro</option>
                                                    <option value="n" <?= $filiacao['sexo'] === 'n' ? 'selected' : '' ?>>Prefiro não informar</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="profileCompany">Telefone</label>
                                            <div class="col-md-6">
                                            <input type="text" class="form-control" maxlength="14" minlength="13" name="telefone" id="telefone" placeholder="Ex: (22)99999-9999" oninput="formatarTelefone(this)" value="<?= $h($filiacao['telefone']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="email">E-mail</label>
                                            <div class="col-md-6">
                                                <input type="email" class="form-control" name="email" id="email" placeholder="Ex: usuario@email.com" value="<?= $h($filiacao['email']) ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="data_nascimento">Data de nascimento</label>
                                            <div class="col-md-6">
                                                <input type="date" class="form-control" name="data_nascimento" id="data_nascimento" max="<?= $dataHoje ?>" value="<?= $h($filiacao['data_nascimento']) ?>">
                                            </div>
                                        </div>
                                        <div class="form-group center">
                                            <a href="<?= $h($perfilUrl) ?>" class="btn btn-default">Voltar</a>
                                            <button type="submit" class="btn btn-primary">Salvar informações pessoais</button>
                                        </div>
                                    </form>
                                </div>

                                <div id="documentacao" class="tab-pane">
                                    <form class="form-horizontal" action="../../controle/control.php" method="post">
                                        <?= Csrf::inputField() ?>
                                        <input type="hidden" name="nomeClasse" value="FiliacaoControle">
                                        <input type="hidden" name="metodo" value="editarDocumentacao">
                                        <input type="hidden" name="id_filiado" value="<?= (int)$idFiliado ?>">
                                        <input type="hidden" name="id_funcionario" value="<?= (int)$idFuncionario ?>">

                                        <h4 class="mb-xlg">Documentação</h4>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="cpf">CPF</label>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="cpf" id="cpf" maxlength="14" placeholder="Ex: 222.222.222-22" value="<?= $h($filiacao['cpf']) ?>" oninput="this.value = this.value.replace(/[^0-9.-]/g, '')">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="rg">RG</label>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="rg" id="rg" maxlength="30" placeholder="Ex: 22.222.222-2" value="<?= $h($filiacao['registro_geral']) ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="orgao_emissor">Local de expedição</label>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="orgao_emissor" id="orgao_emissor" placeholder="Ex: Detran-RJ" value="<?= $h($filiacao['orgao_emissor']) ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="data_expedicao">Data de expedição</label>
                                            <div class="col-md-6">
                                                <input type="date" class="form-control" name="data_expedicao" id="data_expedicao" max="<?= $dataHoje ?>" value="<?= $h($filiacao['data_expedicao']) ?>">
                                            </div>
                                        </div>
                                        <div class="form-group center">
                                            <a href="<?= $h($perfilUrl) ?>" class="btn btn-default">Voltar</a>
                                            <button type="submit" class="btn btn-primary">Salvar documentação</button>
                                        </div>
                                    </form>
                                </div>

                                <div id="endereco" class="tab-pane">
                                    <form class="form-horizontal" action="../../controle/control.php" method="post" id="formEnderecoFiliacao">
                                        <?= Csrf::inputField() ?>
                                        <input type="hidden" name="nomeClasse" value="FiliacaoControle">
                                        <input type="hidden" name="metodo" value="editarEndereco">
                                        <input type="hidden" name="id_filiado" value="<?= (int)$idFiliado ?>">
                                        <input type="hidden" name="id_funcionario" value="<?= (int)$idFuncionario ?>">

                                        <h4 class="mb-xlg">Endereço</h4>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="cep">CEP</label>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="cep" id="cep" maxlength="9" placeholder="Ex: 22222-222" value="<?= $h($filiacao['cep']) ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="estado">Estado</label>
                                            <div class="col-md-6"><input type="text" class="form-control" name="estado" id="estado" value="<?= $h($filiacao['estado']) ?>"></div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="cidade">Cidade</label>
                                            <div class="col-md-6"><input type="text" class="form-control" name="cidade" id="cidade" value="<?= $h($filiacao['cidade']) ?>"></div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="bairro">Bairro</label>
                                            <div class="col-md-6"><input type="text" class="form-control" name="bairro" id="bairro" value="<?= $h($filiacao['bairro']) ?>"></div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="logradouro">Logradouro</label>
                                            <div class="col-md-6"><input type="text" class="form-control" name="logradouro" id="logradouro" value="<?= $h($filiacao['logradouro']) ?>"></div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="numero_endereco">Número</label>
                                            <div class="col-md-3"><input type="text" class="form-control" name="numero_endereco" id="numero_endereco" value="<?= $h($filiacao['numero_endereco']) ?>"></div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="complemento">Complemento</label>
                                            <div class="col-md-6"><input type="text" class="form-control" name="complemento" id="complemento" value="<?= $h($filiacao['complemento']) ?>"></div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="ibge">IBGE</label>
                                            <div class="col-md-3"><input type="text" class="form-control" name="ibge" id="ibge" value="<?= $h($filiacao['ibge']) ?>"></div>
                                        </div>
                                        <div class="form-group center">
                                            <a href="<?= $h($perfilUrl) ?>" class="btn btn-default">Voltar</a>
                                            <button type="submit" class="btn btn-primary">Salvar endereço</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </section>

    <script src="../../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="../../assets/javascripts/theme.js"></script>
    <script src="../../assets/javascripts/theme.custom.js"></script>
    <script src="../../assets/javascripts/theme.init.js"></script>
    <script src="../../Functions/cep_form_validation.js"></script>
    <script>
        $(function () {
            $('#header').load('../header.php');
            $('.menuu').load('../menu.php');

            if (window.location.hash) {
                $('.nav-tabs a[href="' + window.location.hash + '"]').tab('show');
            }

            inicializarValidacaoCepFormulario({
                formId: 'formEnderecoFiliacao',
                ruaIds: ['logradouro']
            });
        });

        function formatarTelefone(input) {
            let v = input.value.replace(/\D/g, "");
            if (v.length > 11) v = v.substring(0, 11);
            let formatado = v;
            if (v.length > 10) { 
                formatado = v.replace(/^(\d{2})(\d{5})(\d{4}).*/, "($1)$2-$3");
                } else if (v.length > 6) { 
                formatado = v.replace(/^(\d{2})(\d{4})(\d{1,4}).*/, "($1)$2-$3");
                } else if (v.length > 2) { 
                formatado = v.replace(/^(\d{2})(\d{1,5})/, "($1)$2");
                } else if (v.length > 0) { 
                formatado = v.replace(/^(\d{1,2})/, "($1");
                }
            input.value = formatado;
        }
    </script>
</body>

</html>