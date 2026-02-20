<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

extract($_REQUEST);

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
  exit(401);
} else {
  session_regenerate_id();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

require_once "../../dao/Conexao.php";
$pdo = Conexao::connect();

$id_pessoa = filter_var($_SESSION['id_pessoa'], FILTER_VALIDATE_INT);

if (!$id_pessoa || $id_pessoa < 1) {
  http_response_code(400);
  echo json_encode(['erro' => 'O id da pessoa informado é inválido.']);
  exit();
}

//Verifica permissão do usuário
permissao($id_pessoa, 52, 7);

include_once '../../classes/Cache.php';
require_once "../personalizacao_display.php";

require_once ROOT . "/controle/SaudeControle.php";

//sanitizar entrada
$id_fichamedica = filter_input(INPUT_GET, 'id_fichamedica', FILTER_VALIDATE_INT);

if (!$id_fichamedica || $id_fichamedica < 1) {
  echo json_encode(['erro' => 'O id da ficha médica informado não é válido.']);
  exit(400);
}

$cache = new Cache();
$teste = $cache->read($id_fichamedica);
require_once "../../dao/Conexao.php";
$pdo = Conexao::connect();

if (!isset($teste)) {
  header('Location: ../../controle/control.php?metodo=listarUm&nomeClasse=SaudeControle&nextPage=../html/saude/profile_paciente.php?id_fichamedica=' . $id_fichamedica . '&id=' . $id_fichamedica);
}

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$stmtEnfermidades = $pdo->prepare("SELECT sf.id_CID, sf.data_diagnostico, sf.status, stc.descricao FROM saude_enfermidades sf JOIN saude_tabelacid stc ON sf.id_CID = stc.id_CID WHERE stc.CID NOT LIKE 'T78.4%' AND sf.status = 1 AND id_fichamedica=:idFichaMedica");

$stmtEnfermidades->bindValue(':idFichaMedica', $id_fichamedica, PDO::PARAM_INT);

$stmtEnfermidades->execute();

$enfermidades = $stmtEnfermidades->fetchAll(PDO::FETCH_ASSOC);

//Formata data das enfermidades para o formato brasileiro
require_once '../../classes/Util.php';
$util = new Util();

foreach ($enfermidades as $index => $enfermidade) {
  $enfermidades[$index]['data_diagnostico'] = $util->formatoDataDMY($enfermidade['data_diagnostico']);
  $enfermidades[$index]['descricao'] = htmlspecialchars($enfermidade['descricao']);
}

$enfermidades = json_encode($enfermidades);

$stmtAlergias = $pdo->prepare("SELECT sf.id_CID, sf.data_diagnostico, sf.status, stc.descricao FROM saude_enfermidades sf JOIN saude_tabelacid stc ON sf.id_CID = stc.id_CID WHERE stc.CID LIKE 'T78.4%' AND sf.status = 1 AND id_fichamedica=:idFichaMedica");
$stmtAlergias->bindValue(':idFichaMedica', $id_fichamedica, PDO::PARAM_INT);
$stmtAlergias->execute();

$alergias = $stmtAlergias->fetchAll(PDO::FETCH_ASSOC);

foreach ($alergias as $index => $alergia) {
  $alergias[$index]['descricao'] = htmlspecialchars($alergia['descricao']);
}

$alergias = json_encode($alergias);

$stmtSinaisVitais = $pdo->prepare("SELECT data, saturacao, pressao_arterial, frequencia_cardiaca, frequencia_respiratoria, temperatura, hgt, observacao, p.nome, p.sobrenome FROM saude_sinais_vitais sv JOIN funcionario f ON(sv.id_funcionario = f.id_funcionario) JOIN pessoa p ON (f.id_pessoa = p.id_pessoa) WHERE sv.id_fichamedica =:idFichaMedica");

$stmtSinaisVitais->bindValue(':idFichaMedica', $id_fichamedica, PDO::PARAM_INT);
$stmtSinaisVitais->execute();

$sinaisvitais = $stmtSinaisVitais->fetchAll(PDO::FETCH_ASSOC);

foreach ($sinaisvitais as $key => $value) {
  //formata data e observacao
  $data = new DateTime($value['data']);
  $sinaisvitais[$key]['data'] = $data->format('d/m/Y h:i:s');
  $sinaisvitais[$key]['observacao'] = htmlspecialchars($value['observacao'] ?? '');
}

$sinaisvitais = json_encode($sinaisvitais);

$sqlDescricaoMedica = "SELECT
    a.id_atendimento AS id_atendimento,
    a.descricao AS descricao,
    a.data_atendimento AS data_atendimento,
    m.nome AS medicoNome,
    p.nome AS enfermeiraNome,
    p.sobrenome AS enfermeiraSobrenome,
    a.anulado AS anulado,
    a.data_anulacao AS data_anulacao,
    a.motivo_anulacao AS motivo_anulacao,
    TRIM(CONCAT(COALESCE(pAnulador.nome, ''), ' ', COALESCE(pAnulador.sobrenome, ''))) AS anulado_por
  FROM saude_atendimento a
  JOIN funcionario f ON (a.id_funcionario = f.id_funcionario)
  JOIN pessoa p ON (p.id_pessoa = f.id_pessoa)
  JOIN saude_medicos m ON (a.id_medico = m.id_medico)
  LEFT JOIN funcionario fAnulador ON (a.id_funcionario_anulacao = fAnulador.id_funcionario)
  LEFT JOIN pessoa pAnulador ON (pAnulador.id_pessoa = fAnulador.id_pessoa)
  WHERE a.id_fichamedica = :idFichaMedica";

$stmtDescricaoMedica = $pdo->prepare($sqlDescricaoMedica);

$stmtDescricaoMedica->bindValue(':idFichaMedica', $id_fichamedica, PDO::PARAM_INT);
$stmtDescricaoMedica->execute();

$descricao_medica = $stmtDescricaoMedica->fetchAll(PDO::FETCH_ASSOC);

foreach ($descricao_medica as $key => $value) {
  $descricao_medica[$key]['id_atendimento'] = (int)($value['id_atendimento'] ?? 0);
  $descricao_medica[$key]['anulado'] = (int)($value['anulado'] ?? 0);

  if (!empty($value['data_atendimento'])) {
    $data = new DateTime($value['data_atendimento']);
    $descricao_medica[$key]['data_atendimento'] = $data->format('d/m/Y');
  } else {
    $descricao_medica[$key]['data_atendimento'] = '';
  }

  if (!empty($value['data_anulacao'])) {
    $dataAnulacao = new DateTime($value['data_anulacao']);
    $descricao_medica[$key]['data_anulacao'] = $dataAnulacao->format('d/m/Y H:i:s');
  } else {
    $descricao_medica[$key]['data_anulacao'] = '';
  }

  $descricao_medica[$key]['motivo_anulacao'] = isset($value['motivo_anulacao']) ? trim((string)$value['motivo_anulacao']) : '';
  $descricao_medica[$key]['anulado_por'] = isset($value['anulado_por']) ? trim((string)$value['anulado_por']) : '';
}

$descricao_medica = json_encode($descricao_medica);

$stmtMedicacoes = $pdo->prepare("SELECT id_medicacao, data_atendimento, medicamento, dosagem, horario, duracao, st.descricao, sm.saude_medicacao_status_idsaude_medicacao_status as id_status FROM saude_atendimento sa JOIN saude_medicacao sm ON (sa.id_atendimento=sm.id_atendimento) JOIN saude_medicacao_status st ON (sm.saude_medicacao_status_idsaude_medicacao_status = st.idsaude_medicacao_status)  WHERE id_fichamedica=:idFichaMedica");

$stmtMedicacoes->bindValue(':idFichaMedica', $id_fichamedica, PDO::PARAM_INT);
$stmtMedicacoes->execute();

$exibimed = $stmtMedicacoes->fetchAll(PDO::FETCH_ASSOC);

foreach ($exibimed as $key => $value) {
  //formata data
  $data = new DateTime($value['data_atendimento']);
  $exibimed[$key]['data_atendimento'] = $data->format('d/m/Y');
}

$exibimed = json_encode($exibimed);

$stmtProntuarioPublico = $pdo->prepare("SELECT descricao FROM saude_fichamedica_descricoes WHERE id_fichamedica=:idFichaMedica");

$stmtProntuarioPublico->bindValue(':idFichaMedica', $id_fichamedica, PDO::PARAM_INT);
$stmtProntuarioPublico->execute();

$prontuariopublico = $stmtProntuarioPublico->fetchAll(PDO::FETCH_ASSOC);
$prontuarioPHP = $prontuariopublico;
$prontuariopublico = json_encode($prontuariopublico);

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$tabelacid_enfermidades = $mysqli->query("SELECT * FROM saude_tabelacid WHERE CID NOT LIKE 'T78.4%'");
$tabelacid_alergias = $mysqli->query("SELECT * FROM saude_tabelacid WHERE CID LIKE 'T78.4%'");
$ultima_alergia = $mysqli->query("SELECT * FROM saude_tabelacid WHERE CID LIKE 'T78.4%' ORDER BY CID DESC LIMIT 1");
$cargoMedico = $mysqli->query("SELECT * FROM pessoa p JOIN funcionario f ON (p.id_pessoa=f.id_pessoa) WHERE f.id_cargo = 3");
$cargoEnfermeiro = $mysqli->query("SELECT * FROM pessoa p JOIN funcionario f ON (p.id_pessoa=f.id_pessoa) WHERE f.id_cargo = 4");

try {
  $tipoexame = $pdo->query("SELECT * FROM saude_exame_tipos ORDER BY descricao ASC")->fetchAll(PDO::FETCH_ASSOC);
  $medicamentoenfermeiro = $mysqli->query("SELECT * FROM saude_medicacao");
  $medstatus = $mysqli->query("SELECT * FROM saude_medicacao_status");

  //aplicar statement
  $stmtFuncionario = $pdo->prepare("SELECT nome FROM pessoa p JOIN funcionario f ON(p.id_pessoa = f.id_pessoa) WHERE f.id_pessoa =:idPessoa");

  $stmtFuncionario->bindValue(':idPessoa', $id_pessoa, PDO::PARAM_INT);
  $stmtFuncionario->execute();

  $funcionarioNome = $stmtFuncionario->fetch(PDO::FETCH_ASSOC)['nome'];

  $stmtProcuraIdPaciente = $pdo->prepare("SELECT id_pessoa FROM saude_fichamedica WHERE id_fichamedica =:idFicha");

  $stmtProcuraIdPaciente->bindValue(':idFicha', $id_fichamedica, PDO::PARAM_INT);
  $stmtProcuraIdPaciente->execute();
  $idPaciente = $stmtProcuraIdPaciente->fetch(PDO::FETCH_ASSOC)['id_pessoa'];
} catch (PDOException $e) {
  echo $e->getMessage();
}

  $stmtAtendido = $pdo->prepare("SELECT p.data_nascimento FROM pessoa p JOIN atendido a ON p.id_pessoa = a.pessoa_id_pessoa WHERE a.pessoa_id_pessoa = :idPessoa");
  $stmtAtendido->bindValue('idPessoa', $idPaciente, PDO::PARAM_INT);
  $stmtAtendido->execute();

  $dadosAtendido = $stmtAtendido->fetch(PDO::FETCH_ASSOC);
  $data_nasc_atendido = $dadosAtendido['data_nascimento'] ?? '1900-01-01';

  $dataAtual = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));

?>
<!-- Vendor -->
<script src="<?php echo WWW; ?>assets/vendor/jquery/jquery.min.js"></script>
<script src="<?php echo WWW; ?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
<script src="<?php echo WWW; ?>assets/vendor/bootstrap/js/bootstrap.js"></script>
<script src="<?php echo WWW; ?>assets/vendor/nanoscroller/nanoscroller.js"></script>
<script src="<?php echo WWW; ?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script src="<?php echo WWW; ?>assets/vendor/magnific-popup/magnific-popup.js"></script>
<script src="<?php echo WWW; ?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

<!-- Specific Page Vendor -->
<script src="<?php echo WWW; ?>assets/vendor/jquery-autosize/jquery.autosize.js"></script>

<!-- Theme Custom -->
<script src="<?php echo WWW; ?>assets/javascripts/theme.custom.js"></script>

<!-- Theme Initialization Files -->
<script src="<?php echo WWW; ?>assets/javascripts/theme.init.js"></script>

<!-- javascript functions -->
<script src="<?php echo WWW; ?>Functions/onlyNumbers.js"></script>
<script src="<?php echo WWW; ?>Functions/onlyChars.js"></script>
<script src="<?php echo WWW; ?>Functions/mascara.js"></script>

<!-- jkeditor -->
<script src="<?php echo WWW; ?>assets/vendor/ckeditor/ckeditor.js"></script>

<!-- Specific Page Vendor CSS -->
<link rel="stylesheet" href="../../assets/vendor/select2/select2.css" />
<link rel="stylesheet" href="../../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />


<script>
  $(function() {
    localStorage.setItem("id_ficha_medica", 'null')

    $("#header").load("../header.php");
    $(".menuu").load("../menu.php");

    var editor = CKEDITOR.replace('despacho');
    editor.on('required', function(e) {
      alert("Por favor, informe a descrição!");
      e.cancel();
    });

  });
</script>

<style type="text/css">
  .select {
    position: absolute;
    width: 235px;
  }

  .select-table-filter {
    width: 140px;
    float: left;
  }

  .panel-body {
    margin-bottom: 15px;
  }

  #div_texto {
    width: 100%;
  }


  #cke_despacho {
    height: 500px;
  }

  #cke_5_contents {
    height: 87% !important;
  }

  .cke_inner {
    height: 500px;
  }

  #cke_1_contents {
    height: 455px !important;
  }

  .col-md-3 {
    width: 10%;
  }

  .titulo-prontuario {
    font-weight: bold;
  }

  .panel-informacoes-gerais {
    border-width: 1px;
    border-style: solid;
    border-color: #428bca;
  }

  .text-bold {
    font-weight: bold;
  }

  .btn-document {
    margin-right: 10px;
  }

  .disabled-fix {
    pointer-events: none;
    /* Impede o clique */
    opacity: 0.6;
    /* Deixa o botão mais escuro */
    cursor: not-allowed;
    /* Muda o cursor para indicar que está desativado */
  }

  .small-text {
    font-size: small;
  }

  .celula-observacao {
    white-space: pre-wrap;
  }

</style>


<!DOCTYPE html>
<html class="fixed">

<head>
  <!-- Basic -->
  <meta charset="UTF-8">
  <title>Informações paciente</title>
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <!-- Web Fonts  -->
  <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
  <!-- Vendor CSS -->
  <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
  <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
  <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
  <link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />

  <link rel="stylesheet" type="text/css" href="../../css/profile-theme.css">
  </script>
  <script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
  <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
  <script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
  <script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
  <script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
  <!-- Theme CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
  <!-- Skin CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
  <!-- Theme Custom CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
  <!-- Head Libs -->
  <script src="../../assets/vendor/modernizr/modernizr.js"></script>
  <script src="../../Functions/lista.js"></script>
  <!-- JavaScript Functions -->
  <script src="../../Functions/enviar_dados.js"></script>
  <script src="../../Functions/mascara.js"></script>
  <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">
  <script src="script/profile_paciente_script/funcoes/enfermidades_funcoes.js" defer></script>
  <script>
    function excluirimg(id) {
      $("#excluirimg").modal('show');
      $('input[name="id_documento"]').val(id);
    }

    function editimg(id, descricao) {
      $('#teste').val(descricao).prop('selected', true);
      $('input[name="id_documento"]').val(id);
      $("#editimg").modal('show');
    }

    $(function() {
      // pega no SaudeControle, listarUm //
      var interno = <?php echo $_SESSION['id_fichamedica']; ?>;

      $.each(interno, function(i, item) {
        if (i = 1) {
          $("#formulario").append($("<input type='hidden' name='id_fichamedica' value='" + item.id + "'>"));
          $("#nome").text("Nome: " + item.nome + ' ' + item.sobrenome);
          $("#nome").val(item.nome + " " + item.sobrenome);

          if (item.imagem != "" && item.imagem != null) {
            $("#imagem").attr("src", "data:image/gif;base64," + item.imagem);
          } else {
            $("#imagem").attr("src", "../../img/semfoto.png");
          }
          if (item.sexo == "m") {
            $("#sexo").html("Sexo: <i class='fa fa-male'></i>  Masculino");
            $("#radioM").prop('checked', true);
          } else if (item.sexo == "f") {
            $("#sexo").html("Sexo: <i class='fa fa-female'>  Feminino");
            $("#radioF").prop('checked', true);
          }

          $("#nascimento").text("Data de nascimento: " + item.data_nascimento);
          $("#nascimento").val(item.data_nascimento);

          if (item.tipo_sanguineo == null || item.tipo_sanguineo == "") {
            $("#adicionartipo").show();

          } else if (item.tipo_sanguineo != null && item.tipo_sanguineo != "") {
            $("#sangue").text("Sangue: " + item.tipo_sanguineo);
            $("#sangue").val(item.tipo_sanguineo);
            $("#exibirtipo").show();
          }
        }
      });
    });

    // exame //
    async function gerarExames() {
      const docfuncional = await listarExamesPorId(<?php echo $_GET["id_fichamedica"]; ?>);

      const depTab = document.getElementById("dep-tab");

      while (depTab.firstChild) {
        depTab.removeChild(depTab.firstChild);
      }
      docfuncional.forEach(item => {
        const tr = document.createElement("tr");

        // coluna arquivo_nome
        const tdArquivo = document.createElement("td");
        tdArquivo.textContent = item.arquivo_nome;
        tr.appendChild(tdArquivo);

        // coluna descricao
        const tdDesc = document.createElement("td");
        tdDesc.textContent = item.descricao;
        tr.appendChild(tdDesc);

        // coluna data
        const tdData = document.createElement("td");
        tdData.textContent = item.data;
        tr.appendChild(tdData);

        // coluna ações
        const tdAcoes = document.createElement("td");
        tdAcoes.style.display = "flex";
        tdAcoes.style.justifyContent = "space-evenly";

        // botão download
        const linkDownload = document.createElement("a");
        linkDownload.onclick = (e) => {
          e.preventDefault;
          baixarArquivo(item.id_exame);
        }
        linkDownload.title = "Visualizar ou Baixar";
        const btnDownload = document.createElement("button");
        btnDownload.className = "btn btn-primary";
        btnDownload.innerHTML = "<i class='fas fa-download'></i>";
        linkDownload.appendChild(btnDownload);

        // botão excluir
        const linkExcluir = document.createElement("a");
        linkExcluir.href = "#";
        linkExcluir.title = "Excluir";
        linkExcluir.onclick = function(e) {
          e.preventDefault();
          deletar_exame(item.id_exame);
        };
        const btnExcluir = document.createElement("button");
        btnExcluir.className = "btn btn-danger";
        btnExcluir.innerHTML = "<i class='fas fa-trash-alt'></i>";
        linkExcluir.appendChild(btnExcluir);

        tdAcoes.appendChild(linkDownload);
        tdAcoes.appendChild(linkExcluir);
        tr.appendChild(tdAcoes);

        depTab.appendChild(tr);
      });
    };

    // enfermidade //
    $(function() {
      let alergias = <?= $alergias ?>;
      $.each(alergias, function(i, item) {
        $("#doc-tab-alergias")
          .append($("<tr>")
            .append($("<td>").text(item.descricao))
            .append($("<td style='display: flex; justify-content: space-evenly;'>")
              .append($("<a onclick='removerAlergia(" + item.id_CID + ")' href='#' title='Inativar'><button class='btn btn-dark'><i class='glyphicon glyphicon-remove'></i></button></a>"))

            )
          )
      });
    });



    function listarAlergias(alergias) {
      $("#doc-tab-alergias").empty();
      $.each(alergias, function(i, item) {
        $("#doc-tab-alergias")
          .append($("<tr>")
            .append($("<td>").text(item.descricao))
            .append($("<td style='display: flex; justify-content: space-evenly;'>")
              .append($("<a onclick='removerAlergia(" + item.id_CID + ")' href='#' title='Inativar'><button class='btn btn-dark'><i class='glyphicon glyphicon-remove'></i></button></a>"))
            )
          )
      });
    }

    //descricao medica 
    $(function() {
      var descricao_medica = <?= $descricao_medica ?>;
      $.each(descricao_medica, function(i, item) {
        const atendimentoAnulado = Number(item.anulado) === 1;
        const statusTexto = atendimentoAnulado ?
          `Anulado${item.data_anulacao ? ' em ' + item.data_anulacao : ''}` :
          'Ativo';
        const motivoAnulacao = atendimentoAnulado && item.motivo_anulacao ? item.motivo_anulacao : 'Não informado';
        const anuladoPor = atendimentoAnulado && item.anulado_por ? item.anulado_por : 'Não informado';

        const tr = $("<tr>");
        tr.attr("data-status", atendimentoAnulado ? "anulado" : "ativo");

        tr.append($("<td>").text(item.medicoNome));
        tr.append($("<td>").text(item.enfermeiraNome + ' ' + item.enfermeiraSobrenome));
        tr.append($("<td>").html(item.descricao));
        tr.append($("<td>").text(item.data_atendimento));
        tr.append(
          $("<td class='coluna-status'>")
            .text(statusTexto)
            .attr("title", atendimentoAnulado && item.motivo_anulacao ? ("Motivo: " + item.motivo_anulacao) : "")
        );
        tr.append($("<td class='coluna-anulacao hidden coluna-motivo-anulacao'>").text(motivoAnulacao));
        tr.append($("<td class='coluna-anulacao hidden coluna-anulado-por'>").text(anuladoPor));

        const tdAcao = $("<td class='coluna-acao' style='text-align: center; vertical-align: middle;'>");
        if (!atendimentoAnulado) {
          tdAcao.append(
            $("<a href='#' title='Anular atendimento'>")
              .on("click", function(e) {
                e.preventDefault();
                anularAtendimento(item.id_atendimento);
              })
              .append($("<button class='btn btn-warning'>").append($("<i class='glyphicon glyphicon-ban-circle'></i>")))
          );
        } else {
          tdAcao.text("Sem ações");
        }

        tr.append(tdAcao);
        $("#de-tab").append(tr);
      });

      aplicarFiltroHistoricoAtendimento();
    });

    function aplicarFiltroHistoricoAtendimento() {
      const filtro = document.getElementById("filtro-historico-atendimento");
      const valorFiltro = filtro ? filtro.value : "ativo";
      const mostrarDetalhesAnulacao = valorFiltro === "anulado";
      const linhas = document.querySelectorAll("#de-tab tr");
      const colunasAnulacao = document.querySelectorAll(".coluna-anulacao");
      let totalVisivel = 0;

      colunasAnulacao.forEach(function(coluna) {
        coluna.classList.toggle("hidden", !mostrarDetalhesAnulacao);
      });

      linhas.forEach(function(linha) {
        const status = linha.getAttribute("data-status");
        const mostrar = valorFiltro === "todos" || status === valorFiltro;
        linha.style.display = mostrar ? "" : "none";
        if (mostrar) {
          totalVisivel++;
        }
      });

      const avisoSemResultados = document.getElementById("historico-sem-resultados");
      if (avisoSemResultados) {
        avisoSemResultados.classList.toggle("hidden", totalVisivel > 0);
      }
    }

    $(function() {
      $("#filtro-historico-atendimento").on("change", function() {
        aplicarFiltroHistoricoAtendimento();
      });
    });

    function limitarTextoComContador(campo, contadorId) {
      campo.value = campo.value.replace(/<|>/g, '');

      if (campo.maxLength > 0 && campo.value.length > campo.maxLength) {
        campo.value = campo.value.slice(0, campo.maxLength);
      }

      const contadorElemento = document.getElementById(contadorId);
      if (contadorElemento) {
        contadorElemento.textContent = campo.value.length;
      }
    }

    function anularAtendimento(idAtendimento) {
      const id = parseInt(idAtendimento, 10);
      if (!id || id < 1) {
        window.alert("Não foi possível identificar o atendimento.");
        return;
      }

      const inputAtendimento = document.getElementById("id_atendimento_anular");
      const motivoInput = document.getElementById("motivo_anulacao");
      const erroMotivo = document.getElementById("erro_motivo_anulacao");

      if (!inputAtendimento || !motivoInput) {
        window.alert("Não foi possível processar a anulação.");
        return;
      }

      inputAtendimento.value = String(id);
      motivoInput.value = "";
      limitarTextoComContador(motivoInput, "contador-caracteres-motivo-anulacao");
      if (erroMotivo) {
        erroMotivo.classList.add("hidden");
      }

      $("#modal-anular-atendimento").modal("show");
    }

    function confirmarAnulacaoAtendimento() {
      const formAnulacao = document.getElementById("form-anular-atendimento");
      const motivoInput = document.getElementById("motivo_anulacao");
      const erroMotivo = document.getElementById("erro_motivo_anulacao");

      if (!formAnulacao || !motivoInput) {
        window.alert("Não foi possível processar a anulação.");
        return;
      }

      const motivo = motivoInput.value.trim();

      if (!motivo) {
        if (erroMotivo) {
          erroMotivo.textContent = "Informe o motivo da anulação.";
          erroMotivo.classList.remove("hidden");
        }
        return;
      }

      if (motivo.length > 255) {
        if (erroMotivo) {
          erroMotivo.textContent = "O motivo deve ter no máximo 255 caracteres.";
          erroMotivo.classList.remove("hidden");
        }
        return;
      }

      if (erroMotivo) {
        erroMotivo.classList.add("hidden");
      }

      formAnulacao.submit();
    }

    $(function() {
      const motivoInput = document.getElementById("motivo_anulacao");
      if (motivoInput) {
        limitarTextoComContador(motivoInput, "contador-caracteres-motivo-anulacao");
      }
    });

    $(function() {
      var exibimed = <?= $exibimed ?>;
      $.each(exibimed, function(i, item) {
        $("#exibimed")
          .append($("<tr>")
            .append($("<td style='text-align: center; vertical-align: middle;'>").text(item.data_atendimento))
            .append($("<td style='text-align: center; vertical-align: middle;'>").text(item.medicamento + ", " + item.dosagem + ", " + item.horario + ", " + item.duracao + "."))
            .append($("<td style='text-align: center; vertical-align: middle;'>").text(item.descricao))
            .append($("<td style='text-align: center; vertical-align: middle;'>")
              .append($("<a onclick='editarStatusMedico(" + item.id_medicacao + ")' href='#'title='Editar'><button class='btn btn-primary' id='teste'><i class='glyphicon glyphicon-pencil'></i></button></a>"))
            )
          )
      });
    });

    // listar aplicacao enfermeiro
    document.addEventListener("DOMContentLoaded", () => {
      let id_ficha_medica = <?php echo $_GET["id_fichamedica"]; ?>;
      const url = `../../controle/control.php?nomeClasse=${encodeURIComponent("MedicamentoPacienteControle")}&metodo=${encodeURIComponent("listarMedicamentosAplicadosPorIdDaFichaMedica")}&id_fichamedica=${encodeURIComponent(id_ficha_medica)}`;
      fetch(url)
        .then(res => res.json())
        .then(medaplicadas => {
          const tabela = document.getElementById("exibiaplicacao");

          medaplicadas.forEach(item => {

            item.aplicacao = formatarDataBr(item.aplicacao)

            const tr = document.createElement("tr");

            const td1 = document.createElement("td");
            td1.textContent = item.nomeFuncionario;

            const td2 = document.createElement("td");
            td2.textContent = item.medicamento;

            const td3 = document.createElement("td");
            td3.textContent = item.aplicacao;

            tr.append(td1, td2, td3);
            tabela.appendChild(tr);
          });
        })
        .catch(err => {
          console.error("Erro ao carregar aplicações:", err);
        });
    })


    $(function() {
      $('#datatable-docfuncional').DataTable({
        "order": [
          [0, "asc"]
        ]
      });
      $('.datatable-docfuncional').DataTable({
        "order": [
          [0, "asc"]
        ]
      });
    });

    function escrevermed() {

      let nome_medicacao = window.prompt("Informe a medicação:");
      $("#primeira_medicacao").remove();
      $("#mais_medicacoes").show();
      $(".meddisabled").val(nome_medicacao);
    }

    function carregarIntercorrencias() {
      let id = <?php echo $_GET['id_fichamedica']; ?>;
      const url = `../../controle/control.php?nomeClasse=${encodeURIComponent("AvisoControle")}&metodo=${encodeURIComponent("listarIntercorrenciaPorIdDaFichaMedica")}&id_fichamedica=${encodeURIComponent(id)}`;
      fetch(url)
        .then(res => res.json())
        .then(intercorrencias => {
          const tbody = document.getElementById("doc-tab-intercorrencias");

          intercorrencias.forEach(item => {
            const tr = document.createElement("tr");

            const td1 = document.createElement("td");
            td1.textContent = item.descricao;

            const td2 = document.createElement("td");
            td2.textContent = item.data;

            tr.append(td1, td2);
            tbody.appendChild(tr);
          });
        })
        .catch(err => {
          console.error("Erro ao carregar aplicações:", err);
        });
    }
  </script>
  <style type="text/css">
    .obrig {
      color: rgb(255, 0, 0);
    }

    .btn-edicaoProntuario {
      margin-top: 10px;
    }

    .hidden {
      display: none;
    }
  </style>

</head>

<body>
  <section class="body">
    <div id="header"></div>
    <!-- end: header -->
    <div class="inner-wrapper">
      <!-- start: sidebar -->
      <aside id="sidebar-left" class="sidebar-left menuu"></aside>
      <!-- end: sidebar -->
      <section role="main" class="content-body">
        <header class="page-header">
          <h2>Informações paciente</h2>
          <div class="right-wrapper pull-right">
            <ol class="breadcrumbs">
              <li>
                <a href="../index.php">
                  <i class="fa fa-home"></i>
                </a>
              </li>
              <li><span>Informações paciente</span></li>
            </ol>
            <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
          </div>
        </header>
        <!-- start: page -->
        <div class="row">
          <div class="col-md-4 col-lg-2">
            <section class="panel">
              <div class="panel-body">
                <div class="thumb-info mb-md">
                  <img id="imagem" alt="John Doe">
                </div>
              </div>
            </section>
          </div>
          <div class="col-md-8 col-lg-8">
            <?php
            if (session_status() === PHP_SESSION_NONE) {
              session_start();
            }

            if (isset($_SESSION['msg']) && !empty($_SESSION['msg'])) {
              $mensagem = $_SESSION['msg'];

              echo "<div class=\"alert alert-success\" role=\"alert\">
                  $mensagem
                  <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                  </button>
                </div>";

              unset($_SESSION['msg']);
            } else if (isset($_SESSION['msg_e']) && !empty($_SESSION['msg_e'])) {
              $mensagem = $_SESSION['msg_e'];

              echo "<div class=\"alert alert-danger\" role=\"alert\">
                  $mensagem
                  <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                  </button>
                </div>";

              unset($_SESSION['msg_e']);
            }
            ?>
            <div class="tabs">
              <ul class="nav nav-tabs tabs-primary">
                <li class="active">
                  <a href="#overview" data-toggle="tab">Informações Gerais</a>
                </li>
                <li>
                  <a href="#cadastro_alergias" data-toggle="tab">Alergias</a>
                </li>
                <li>
                  <a href="#cadastro_comorbidades" data-toggle="tab">Comorbidades</a>
                </li>
                <li>
                  <a href="#arquivo" data-toggle="tab">Exames</a>
                </li>
                <li>
                  <a href="#historico_medico" data-toggle="tab">Histórico do Paciente</a>
                </li>
                <li>
                <li>
                  <a href="#atendimento_medico" data-toggle="tab">Atendimento do Paciente</a>
                </li>
                <li>
                  <a href="#medicacoes_aplicadas" data-toggle="tab">Medicações Aplicadas</a>
                </li>
                <li>
                  <a href="#sinais_vitais" data-toggle="tab">Sinais Vitais</a>
                </li>
                <li>
                  <a href="#intercorrencias" data-toggle="tab">Intercorrências</a>
                </li>
              </ul>

              <div class="tab-content">

                <div id="overview" class="tab-pane active">

                  <?php
                  $pacienteOverview = json_decode($_SESSION['id_fichamedica'], true)[0];
                  //var_dump($pacienteOverview);exit;
                  ?>
                  <!-- Substituir o form abaixo por outra forma de visualização -->
                  <!--<form class="form-horizontal" method="post" action="../../controle/control.php">
                    <input type="hidden" name="nomeClasse" value="SaudeControle">-->
                  <section class="panel panel-primary">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a class="fa fa-caret-down" title="Mostrar/Ocultar"></a>
                      </div>
                      <h2 class="panel-title">Informações pessoais</h2>
                    </header>

                    <div class="panel-body panel-informacoes-gerais">

                      <div class="container">
                        <div class="row">
                          <p><span class="text-bold">Nome:</span> <?= $pacienteOverview['nome'] . ' ' . $pacienteOverview['sobrenome'] ?></p>
                        </div>
                        <div class="row">
                          <p><span class="text-bold">Sexo:</span>
                            <?= $pacienteOverview['sexo'] == 'f' ? '<i class="fa fa-female" style="font-size: 15px; color:deeppink;"> </i>' . ' Feminino' : '<i class="fa fa-male" style="font-size: 15px; color:darkblue"> </i>' . ' Masculino';
                            ?>
                          </p>
                        </div>
                        <div class="row">
                          <div class="col-md-3" style="padding-left: 0px;">
                            <p><span class="text-bold">Data de nascimento: </span><?= $util->formatoDataDMY($pacienteOverview['data_nascimento']) ?: 'Nao informada' ?></p>
                          </div>
                          <div class="col-md-3">
                            <p><span class="text-bold">Idade:</span>
                              <?php
                              $dataNascimentoRaw = $pacienteOverview['data_nascimento'] ?? null;
                              if (!empty($dataNascimentoRaw) && $dataNascimentoRaw !== '0000-00-00') {
                                try {
                                  $dataNascimento = new DateTime($dataNascimentoRaw);
                                  $hoje = new DateTime();
                                  $idade = $dataNascimento->diff($hoje)->y;
                                  echo $idade . ' anos';
                                } catch (Exception $e) {
                                  echo 'Nao informada';
                                }
                              } else {
                                echo 'Nao informada';
                              }
                              ?>
                            </p>
                          </div>
                        </div>
                        <div class="row">
                          <p><span class="text-bold">Tipo sanguíneo:</span> <?= ($pacienteOverview['tipo_sanguineo']) !== null ? $pacienteOverview['tipo_sanguineo'] : 'Indefinido' ?></p>
                        </div>

                        <div class="row">
                          <?php
                          $sqlDocumentosDownload = "SELECT ad.id_pessoa_arquivo as id, ad.atendido_docs_atendidos_idatendido_docs_atendidos as tipo_doc FROM atendido_documentacao ad JOIN atendido_docs_atendidos ada ON (ad.atendido_docs_atendidos_idatendido_docs_atendidos=ada.idatendido_docs_atendidos) JOIN atendido a ON (ad.atendido_idatendido=a.idatendido) JOIN pessoa p ON (a.pessoa_id_pessoa=p.id_pessoa) JOIN saude_fichamedica sf ON(p.id_pessoa=sf.id_pessoa) WHERE sf.id_fichamedica=:idFichaMedica AND ad.atendido_docs_atendidos_idatendido_docs_atendidos IN (1,2,3,5) ORDER BY tipo_doc ASC";

                          try {
                            $stmt = $pdo->prepare($sqlDocumentosDownload);
                            $stmt->bindValue(':idFichaMedica', $_GET['id_fichamedica']);
                            $stmt->execute();

                            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            $documentosDownload = [];

                            foreach ($resultados as $documento) {
                              $documentosDownload[$documento['tipo_doc']] =  $documento['id'];
                            }

                          } catch (PDOException $e) {
                            http_response_code(500);
                            echo json_encode(['erro' => 'Erro no servidor ao buscar documentações para download ' . $e->getMessage()]);
                            exit();
                          }
                          ?>

                          <!--Botão para baixar CPF -->
                          <?php
                          if (isset($documentosDownload[2])):
                          ?>
                            <a href="../atendido/documento_download.php?id_doc=<?= $documentosDownload[2] ?>" class="btn btn-primary btn-document" title="Clique para baixar">CPF <i class="fas fa-download"></i></a>
                          <?php
                          else:
                          ?>
                            <a href="#" class="btn btn-primary btn-document disabled-fix" title="Arquivo não disponível">CPF <i class="fas fa-download"></i></a>
                          <?php
                          endif;
                          ?>

                          <!-- Botão para baixar RG -->
                          <?php
                          if (isset($documentosDownload[1])):
                          ?>
                            <a href="../atendido/documento_download.php?id_doc=<?= $documentosDownload[1] ?>" class="btn btn-primary btn-document" title="Clique para baixar">RG <i class="fas fa-download"></i></a>
                          <?php
                          else:
                          ?>
                            <a href="#" class="btn btn-primary btn-document disabled-fix" title="Arquivo não disponível">RG <i class="fas fa-download"></i></a>
                          <?php
                          endif;
                          ?>

                          <!--Botão para baixar SUS-->
                          <?php
                          if (isset($documentosDownload[3])):
                          ?>
                            <a href="../atendido/documento_download.php?id_doc=<?= $documentosDownload[3] ?>" class="btn btn-primary btn-document" title="Clique para baixar">Cartão do SUS <i class="fas fa-download"></i></a>
                          <?php
                          else:
                          ?>
                            <a href="#" class="btn btn-primary btn-document disabled-fix" title="Arquivo não disponível">Cartão do SUS <i class="fas fa-download"></i></a>
                          <?php
                          endif;
                          ?>

                          <?php
                          if (isset($documentosDownload[5])):
                          ?>
                            <a href="../atendido/documento_download.php?id_doc=<?= $documentosDownload[5] ?>" class="btn btn-primary btn-document" title="Clique para baixar">Plano de saúde <i class="fas fa-download"></i></a>
                          <?php
                          else:
                          ?>
                            <a href="#" class="btn btn-primary btn-document disabled-fix" title="Arquivo não disponível">Plano de saúde <i class="fas fa-download"></i></a>
                          <?php
                          endif;
                          ?>

                        </div>
                      </div>

                    </div>
                  </section>
                  <!--</form>-->

                  <?php
                  $alergiasArray = json_decode($alergias, true);
                  if (count($alergiasArray) > 0):
                  ?>
                    <div id="lista-alergias" class="tab-pane">
                      <section class="panel panel-primary">
                        <header class="panel-heading">
                          <div class="panel-actions">
                            <a class="fa fa-caret-up" title="Mostrar/Ocultar"></a>
                          </div>
                          <h2 class="panel-title">Lista de Alergias</h2>
                        </header>

                        <div class="panel-body panel-informacoes-gerais" style="display: none;">

                          <table class="table table-hover">
                            <thead>
                              <th>#</th>
                              <th class="text-center">Descrição</th>
                            </thead>

                            <tbody>
                              <!--Lista de alergias -->
                              <?php
                              foreach ($alergiasArray as $index => $alergia):
                              ?>
                                <tr>
                                  <td><?= $index + 1 ?></td>
                                  <td class="text-center"><?= $alergia['descricao'] ?></td>
                                </tr>
                              <?php
                              endforeach;
                              ?>
                            </tbody>
                          </table>


                        </div>
                    </div>
                  <?php
                  endif;
                  ?>

                  <?php
                  $enfermidadesArray = json_decode($enfermidades, true);
                  if (count($enfermidadesArray) > 0):
                  ?>
                    <div id="lista-comorbidades" class="tab-pane">
                      <section class="panel panel-primary">
                        <header class="panel-heading">
                          <div class="panel-actions">
                            <a class="fa fa-caret-up" title="Mostrar/Ocultar"></a>
                          </div>
                          <h2 class="panel-title">Lista de Comorbidades</h2>
                        </header>

                        <div class="panel-body panel-informacoes-gerais" style="display: none;">

                          <table class="table table-hover">
                            <thead>
                              <th>#</th>
                              <th class="text-center">Descrição</th>
                            </thead>

                            <tbody>
                              <!--Lista de Comorbidades-->
                              <?php
                              foreach ($enfermidadesArray as $index => $enfermidade):
                              ?>
                                <tr>
                                  <td><?= $index + 1 ?></td>
                                  <td class="text-center"><?= $enfermidade['descricao'] ?></td>
                                </tr>
                              <?php
                              endforeach;
                              ?>
                            </tbody>
                          </table>
                        </div>
                    </div>
                  <?php
                  endif;
                  ?>

                  <?php
                  $medicamentosEmUso = [];
                  $medicamentosPaciente = json_decode($exibimed, true);
                  foreach ($medicamentosPaciente as $medicamento) {
                    if ($medicamento['id_status'] == 1) {
                      $medicamentosEmUso[] = $medicamento;
                    }
                  }

                  if (count($medicamentosEmUso) > 0):
                  ?>
                    <div id="lista-medicacoes-uso" class="tab-pane">
                      <section class="panel panel-primary">
                        <header class="panel-heading">
                          <div class="panel-actions">
                            <a class="fa fa-caret-up" title="Mostrar/Ocultar"></a>
                          </div>
                          <h2 class="panel-title">Lista de Medicações em uso</h2>
                        </header>

                        <div class="panel-body panel-informacoes-gerais" style="display: none;">

                          <table class="table table-hover">
                            <thead>
                              <th>#</th>
                              <th class="text-center">Descrição</th>
                            </thead>

                            <tbody>
                              <!--Lista de Medicamentos-->
                              <?php
                              foreach ($medicamentosEmUso as $index => $medicamento):
                              ?>
                                <tr>
                                  <td><?= $index + 1 ?></td>
                                  <td class="text-center"><?= htmlspecialchars($medicamento['medicamento'] . '|' . $medicamento['dosagem'] . '|' . $medicamento['horario'] . '|' . $medicamento['duracao']) ?></td>
                                </tr>
                              <?php
                              endforeach;
                              ?>
                            </tbody>
                          </table>

                        </div>
                    </div>
                  <?php
                  endif;
                  ?>

                  <?php
                  function pegarSinalVital($sql, $pdo)
                  {
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idFichaMedica', $_GET['id_fichamedica']);
                    $stmt->execute();

                    if ($stmt->rowCount() != 0) {
                      return  $stmt->fetchAll(PDO::FETCH_ASSOC);
                    }

                    return false;
                  }

                  function diaMes($dataCompleta)
                  {
                    $dataArray = explode('/', $dataCompleta);

                    return $dataArray[0] . '/' . $dataArray[1];
                  }

                  $sinaisVitaisArray = [];

                  $sqlSaturacao = "SELECT saturacao, data FROM `saude_sinais_vitais`  WHERE saude_sinais_vitais.id_fichamedica=:idFichaMedica AND saude_sinais_vitais.saturacao !='' ORDER BY data DESC LIMIT 5";

                  $sqlPressaoArterial = "SELECT pressao_arterial, data FROM `saude_sinais_vitais`  WHERE saude_sinais_vitais.id_fichamedica=:idFichaMedica AND saude_sinais_vitais.pressao_arterial !='' ORDER BY data DESC LIMIT 5";

                  $sqlFrequenciaCardiaca = "SELECT frequencia_cardiaca, data FROM `saude_sinais_vitais`  WHERE saude_sinais_vitais.id_fichamedica=:idFichaMedica AND saude_sinais_vitais.frequencia_cardiaca !='' ORDER BY data DESC LIMIT 5";

                  $sqlFrequenciaRespiratoria = "SELECT frequencia_respiratoria, data FROM `saude_sinais_vitais`  WHERE saude_sinais_vitais.id_fichamedica=:idFichaMedica AND saude_sinais_vitais.frequencia_respiratoria !='' ORDER BY data DESC LIMIT 5";

                  $sqlTemperatura = "SELECT temperatura, data FROM `saude_sinais_vitais`  WHERE saude_sinais_vitais.id_fichamedica=:idFichaMedica AND saude_sinais_vitais.temperatura !='' ORDER BY data DESC LIMIT 5";

                  $sqlHgt = "SELECT hgt, data FROM `saude_sinais_vitais`  WHERE saude_sinais_vitais.id_fichamedica=:idFichaMedica AND saude_sinais_vitais.hgt !='' ORDER BY data DESC LIMIT 5";

                  $sqlObservacao = "SELECT observacao, data FROM `saude_sinais_vitais`  WHERE saude_sinais_vitais.id_fichamedica=:idFichaMedica AND saude_sinais_vitais.observacao !='' ORDER BY data DESC LIMIT 5";


                  try {
                    $sinaisVitaisArray['saturacao'] = pegarSinalVital($sqlSaturacao, $pdo);
                    $sinaisVitaisArray['pressaoArterial'] = pegarSinalVital($sqlPressaoArterial, $pdo);
                    $sinaisVitaisArray['frequenciaCardiaca'] = pegarSinalVital($sqlFrequenciaCardiaca, $pdo);
                    $sinaisVitaisArray['frequenciaRespiratoria'] = pegarSinalVital($sqlFrequenciaRespiratoria, $pdo);
                    $sinaisVitaisArray['temperatura'] = pegarSinalVital($sqlTemperatura, $pdo);
                    $sinaisVitaisArray['hgt'] = pegarSinalVital($sqlHgt, $pdo);
                    $sinaisVitaisArray['observacao'] = pegarSinalVital($sqlObservacao, $pdo);
                  } catch (PDOException $e) {
                    http_response_code(500);
                    echo json_encode(['erro' => 'Erro ao buscar o histórico dos sinais vitais']);
                    exit();
                  }

                  if ($sinaisVitaisArray['saturacao'] || $sinaisVitaisArray['pressaoArterial'] || $sinaisVitaisArray['frequenciaCardiaca'] || $sinaisVitaisArray['frequenciaRespiratoria'] || $sinaisVitaisArray['temperatura'] || $sinaisVitaisArray['hgt']):
                  ?>
                    <div id="lista-sinais-vitais" class="tab-pane">
                      <section class="panel panel-primary">
                        <header class="panel-heading">
                          <div class="panel-actions">
                            <a class="fa fa-caret-up" title="Mostrar/Ocultar"></a>
                          </div>
                          <h2 class="panel-title">Informações vitais</h2>
                        </header>

                        <div class="panel-body panel-informacoes-gerais table-responsive" style="display: none;">

                          <table class="table table-hover small-text ">
                            <thead>
                              <th>#</th>
                              <th class="text-center">Saturação</th>
                              <th class="text-center">Pressão arterial</th>
                              <th class="text-center">Frequência cardíaca</th>
                              <th class="text-center">Frequência respiratória</th>
                              <th class="text-center">Temperatura</th>
                              <th class="text-center">HGT</th>
                              <th class="text-center">Observação</th>
                            </thead>

                            <tbody>
                              <!--Lista de Sinais Vitais-->
                              <?php
                              for ($i = 0; $i < 5; $i++):
                              ?>
                                <tr>
                                  <td><?= $i + 1 ?></td>
                                  <td class="text-center"><?= isset($sinaisVitaisArray['saturacao'][$i]['data']) ? $sinaisVitaisArray['saturacao'][$i]['saturacao'] . ' | ' . diaMes($util->formatoDataDMY($sinaisVitaisArray['saturacao'][$i]['data'])) : 'Sem registro' ?></td>
                                  <td class="text-center"><?= isset($sinaisVitaisArray['pressaoArterial'][$i]['data']) ? $sinaisVitaisArray['pressaoArterial'][$i]['pressao_arterial'] . ' | ' . diaMes($util->formatoDataDMY($sinaisVitaisArray['pressaoArterial'][$i]['data']))  : 'Sem registro' ?></td>
                                  <td class="text-center"><?= isset($sinaisVitaisArray['frequenciaCardiaca'][$i]['data']) ? $sinaisVitaisArray['frequenciaCardiaca'][$i]['frequencia_cardiaca'] . ' | ' . diaMes($util->formatoDataDMY($sinaisVitaisArray['frequenciaCardiaca'][$i]['data']))  : 'Sem registro' ?></td>
                                  <td class="text-center"><?= isset($sinaisVitaisArray['frequenciaRespiratoria'][$i]['data']) ? $sinaisVitaisArray['frequenciaRespiratoria'][$i]['frequencia_respiratoria'] . ' | ' . diaMes($util->formatoDataDMY($sinaisVitaisArray['frequenciaRespiratoria'][$i]['data'])) : 'Sem registro' ?></td>
                                  <td class="text-center"><?= isset($sinaisVitaisArray['temperatura'][$i]['data']) ? $sinaisVitaisArray['temperatura'][$i]['temperatura'] . ' | ' . diaMes($util->formatoDataDMY($sinaisVitaisArray['temperatura'][$i]['data'])) : 'Sem registro' ?></td>
                                  <td class="text-center"><?= isset($sinaisVitaisArray['hgt'][$i]['data']) ? $sinaisVitaisArray['hgt'][$i]['hgt'] . ' : ' . diaMes($util->formatoDataDMY($sinaisVitaisArray['hgt'][$i]['data'])) : 'Sem registro' ?></td>
                                  <td class="text-center celula-observacao"><?= isset($sinaisVitaisArray['observacao'][$i]['data']) ? htmlspecialchars($sinaisVitaisArray['observacao'][$i]['observacao']) . ' : ' . diaMes($util->formatoDataDMY($sinaisVitaisArray['observacao'][$i]['data'])) : 'Sem registro' ?></td>
                                </tr>
                              <?php
                              endfor;
                              ?>
                            </tbody>
                          </table>

                        </div>
                    </div>
                  <?php
                  endif;
                  ?>

                  <form action="../../controle/control.php" method="POST" id="editarProntuario">
                    <input type="hidden" name="nomeClasse" value="SaudeControle">
                    <input type="hidden" name="metodo" value="alterarProntuario">
                    <input type="hidden" name="id_fichamedica" value="<?php echo $_GET['id_fichamedica'] ?>">

                    <label for="textoProntuario" class="titulo-prontuario">Prontuário Público</label>
                    <textarea name="textoProntuario" class="form-control" required id="prontuario" cols="30" rows="10"><?php
                                                                                                                        $stringConcatenada = '';

                                                                                                                        foreach ($prontuarioPHP as $prontuario) {
                                                                                                                          $stringConcatenada .= $prontuario['descricao'];
                                                                                                                        }

                                                                                                                        echo $stringConcatenada;
                                                                                                                        ?></textarea>
                    <button id="btn-editarProntuario" class="btn btn-primary btn-edicaoProntuario" onclick="event.preventDefault(); editarProntuario();">Editar Prontuário</button>
                    <button id="btn-cancelarEdicao" class="btn btn-danger btn-edicaoProntuario hidden" onclick="event.preventDefault(); cancelarEdicao()">Cancelar</button>
                    <button type="submit" id="btn-confirmarEdicao" class="btn btn-success btn-edicaoProntuario hidden">Salvar</button>
                  </form>

                  <form action="../../controle/control.php" method="POST">
                    <input type="hidden" name="nomeClasse" value="SaudeControle">
                    <input type="hidden" name="metodo" value="adicionarProntuarioAoHistorico">
                    <input type="hidden" name="id_fichamedica" value="<?php echo $_GET['id_fichamedica'] ?>">
                    <input type="hidden" name="id_paciente" value="<?= $idPaciente ?>">

                    <button id="btn-adicionarAoHistorico" class="btn btn-primary btn-edicaoProntuario">Adicionar versão ao histórico</button>

                    <button id="btn-listarDoHistorico" class="btn btn-primary btn-edicaoProntuario" onclick="event.preventDefault(); listarProntuariosDoHistorico()">Listar prontuários do histórico</button>
                  </form>


                </div>

                <!-- Aba  de  alergias -->
                <div id="cadastro_alergias" class="tab-pane">
                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Alergias</h2>
                    </header>
                    <div class="panel-body">
                      <hr class="dotted short">

                      <div class="form-group" id="exibiralergias">
                        <table class="table table-bordered table-striped" id="datatable-alergias">
                          <thead>
                            <tr style="font-size:15px;">
                              <th>Descrição</th>
                              <th>Ação</th>
                            </tr>
                          </thead>
                          <tbody id="doc-tab-alergias">
                          </tbody>
                        </table>
                        <br>

                        <form action='alergia_upload.php' method='post' id='funcionarioDoc'>
                          <div class='col-md-12' id="div_alergia" style="display: none;">
                            <div class="form-group">
                              <label class="col-md-3 control-label" for="inputSuccess">Alergias</label>
                              <div class="col-md-6">
                                <select class="form-control input-lg mb-md" name="id_CID_alergia" id="id_CID_alergia">
                                  <option selected disabled>Selecionar</option>
                                  <?php
                                  $alergias_decoded = json_decode($alergias, true);
                                  while ($row = $tabelacid_alergias->fetch_array(MYSQLI_NUM)) {
                                    $rowIdCID = $row[0];
                                    $found = false;
                                    foreach ($alergias_decoded as $alergia) {
                                      var_dump($alergia['id_CID']);
                                      if (isset($alergia['id_CID']) && $alergia['id_CID'] == $rowIdCID) {
                                        $found = true;
                                        break;
                                      }
                                    }
                                    if (!$found) {
                                      echo "<option value=" . $row[0] . ">" . htmlspecialchars($row[2]) . "</option>";
                                    }
                                  } ?>
                                </select>
                              </div>
                              <a onclick="adicionar_alergia()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>

                            </div>
                            <div class="form-group">
                              <input type="button" onclick="alergia_upload()" class="btn btn-primary" id="salvarAlergia" value="Salvar" style="display: none;">
                            </div>
                          </div>
                        </form>
                      </div>
                  </section>
                </div>

                <!-- Aba  de  comorbidades -->
                <div id="cadastro_comorbidades" class="tab-pane">
                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Cadastro de comorbidades</h2>
                    </header>
                    <div class="panel-body">
                      <hr class="dotted short">

                      <table class="table table-bordered table-striped mb-none" id="datatable-dependente">
                        <thead>
                          <tr style="font-size:15px;">
                            <th>Comorbidades</th>
                            <th>Data</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody id="doc-tab">

                        </tbody>
                      </table>

                      <br>
                      <form id='form-enfermidade'>
                        <div class="form-group">
                          <div class="col-md-6">
                            <h5 class="obrig">Campos Obrigatórios(*)</h5>
                          </div>
                        </div>

                        <div class="form-group">
                          <label class="col-md-3 control-label" for="inputSuccess">Enfermidades<sup class="obrig">*</sup></label>
                          <div class="col-md-6">

                            <select class="form-control input-lg mb-md" name="id_CID" id="id_CID" required>
                              <option selected disabled>Qualquer coisa</option>
                              <?php
                              /*while ($row = $tabelacid_enfermidades->fetch_array(MYSQLI_NUM)) {
                                echo "<option value=" . $row[0] . ">" . htmlspecialchars($row[2]) . "</option>";
                              }*/                            ?>
                            </select>
                          </div>
                          <a onclick="adicionar_enfermidade()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                        </div>

                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileCompany">Data do diagnóstico<sup class="obrig">*</sup></label>
                          <div class="col-md-6">
                            <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="data_diagnostico" id="data_diagnostico" max=<?php echo date('Y-m-d'); ?> required>
                          </div>
                        </div>

                        <div class="form-group">
                          <label class="col-md-3 control-label" for="inputSuccess">Status<sup class="obrig">*</sup></label>
                          <div class="col-md-6">
                            <select class="form-control input-lg mb-md" name="intStatus" id="intStatus" required>
                              <option value="" selected disabled>Selecionar</option>
                              <option value="1">Ativo</option>
                              <option value="0">Inativo</option>
                            </select>
                          </div>
                        </div>

                        <div class="form-group">
                          <div class="col-md-6">
                            <input type="hidden" id="id_fichamedica_enfermidade" name="id_fichamedica" value=<?php echo $_GET['id_fichamedica'] ?>>
                            <input type="submit" class="btn btn-primary" value="Cadastrar" id="btn-cadastrar-enfermidade">
                          </div>
                        </div>
                      </form>
                    </div>
                  </section>
                </div>

                <!-- Aba de exames -->
                <div id="arquivo" class="tab-pane">
                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Exames</h2>
                    </header>
                    <div class="panel-body">
                      <hr class="dotted short">
                      <table class="table table-bordered table-striped mb-none">
                        <thead>
                          <tr style="font-size:15px;">
                            <th>Arquivo</th>
                            <th>Tipo exame</th>
                            <th>Data exame</th>
                            <th>Ação</th>
                          </tr>
                        </thead>
                        <tbody id="dep-tab" style="font-size:15px">

                        </tbody>
                      </table>
                      <br>
                      <!-- Button trigger modal -->
                      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#docFormModal" onclick="gerar_tipo_exame()">
                        Adicionar
                      </button>
                      <div class="modal fade" id="docFormModal" tabindex="-1" role="dialog" aria-labelledby="docFormModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">

                            <div class="modal-header" style="display: flex;justify-content: space-between;">
                              <h5 class="modal-title" id="exampleModalLabel">Adicionar exame</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>

                            <form id='ExameDocForm'>
                              <div class="modal-body" style="padding: 15px 40px">
                                <div class="form-group" style="display: grid;">

                                  <div class="form-group">
                                    <label for="arquivoDocumento">Exame</label>
                                    <input name="arquivo" type="file" class="form-control-file" id="documentoExame" accept="png;jpeg;jpg;pdf;docx;doc;odp" required>
                                  </div>

                                  <div class="form-group">

                                    <label for="arquivoDocumento">Tipo de exame</label>

                                    <div style="display: flex;">

                                      <select class="form-control input-lg mb-md" name="id_docfuncional" id="tipoDocumentoExame" style="width:170px;" required>
                                      </select>
                                      <a onclick="adicionar_tipo_exame()"><i class="fas fa-plus w3-xlarge" style="margin: 15px 15px 15px 15px;"></i></a>
                                    </div>

                                  </div>

                                  <input type="number" id="exame_id_fichamedica" name="id_fichamedica" value="<?= $_GET['id_fichamedica']; ?>" style='display: none;'>
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                  <input type="submit" value="Enviar" class="btn btn-primary">
                                </div>
                            </form>
                          </div>
                        </div>
                      </div>
                      <br />
                  </section>
                </div>

                <!-- aba de atendimento médico -->
                <div id="historico_medico" class="tab-pane">
                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Histórico do Paciente</h2>
                    </header>

                    <div class="panel-body">
                      <hr class="dotted short">
                      <div class="form-group">
                        <div class="row" style="margin-bottom: 15px;">
                          <div class="col-sm-12 col-md-4">
                            <label for="filtro-historico-atendimento">Filtrar histórico</label>
                            <select id="filtro-historico-atendimento" class="form-control">
                              <option value="todos">Todos</option>
                              <option value="ativo" selected>Ativos</option>
                              <option value="anulado">Anulados</option>
                            </select>
                          </div>
                        </div>
                        <div class="table-responsive" style="overflow-x: auto;">
                          <table class="table table-bordered table-striped mb-none">
                            <thead>
                              <tr style="font-size:15px;">
                                <th>Médico</th>
                                <th>Registro</th>
                                <th>Descrições</th>
                                <th>Data do atendimento</th>
                                <th>Status</th>
                                <th class="coluna-anulacao hidden">Motivo</th>
                                <th class="coluna-anulacao hidden">Anulador</th>
                                <th>Ação</th>
                              </tr>
                            </thead>
                            <tbody id="de-tab" style="font-size:15px">

                            </tbody>
                          </table>
                        </div>
                        <p id="historico-sem-resultados" class="text-muted hidden" style="margin-top: 10px;">Nenhum atendimento encontrado para o filtro selecionado.</p>
                        <div class="modal fade" id="modal-anular-atendimento" tabindex="-1" role="dialog" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header" style="display: flex; justify-content: space-between;">
                                <h5 class="modal-title">Anular atendimento</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <form method="post" action="anular_atendimento.php" id="form-anular-atendimento">
                                <div class="modal-body">
                                  <p>Essa ação não exclui o registro e ficará marcada como anulada.</p>
                                  <div class="form-group">
                                    <label for="motivo_anulacao">Motivo da anulação <sup class="obrig">*</sup></label>
                                    <textarea class="form-control" id="motivo_anulacao" name="motivo_anulacao" rows="4" maxlength="255" oninput="limitarTextoComContador(this, 'contador-caracteres-motivo-anulacao')" required></textarea>
                                    <div style="margin-top: 8px; text-align: right;">
                                      <small class="text-muted"><span id="contador-caracteres-motivo-anulacao">0</span> / 255</small>
                                    </div>
                                    <p id="erro_motivo_anulacao" class="text-danger hidden" style="margin-top: 8px;"></p>
                                  </div>
                                  <input type="hidden" name="id_fichamedica" value="<?php echo (int)$id_fichamedica; ?>">
                                  <input type="hidden" name="id_atendimento" id="id_atendimento_anular" value="">
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                  <button type="button" class="btn btn-warning" onclick="confirmarAnulacaoAtendimento()">Confirmar anulação</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="form-group">
                        <hr class="dotted short">
                        <table class="table table-bordered table-striped mb-none datatable-docfuncional" ">
                          <thead>
                            <tr style=" font-size:15px;">
                          <th>Data do atendimento</th>
                          <th>Medicações</th>
                          <th>Status</th>
                          <th>Ação</th>
                          </tr>
                          </thead>
                          <tbody id="exibimed" style="font-size:15px">

                          </tbody>
                        </table>
                      </div>

                      <div class="modal fade" id="testemed" tabindex="-1" role="dialog" aria-labelledby="docFormModalLabel" aria-hidden="true" style="display:none;">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">

                            <div class="modal-header" style="display: flex;justify-content: space-between;">
                              <h5 class="modal-title" id="exampleModalLabel">Alterar Status</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>

                            <form action='status_update.php' method='post' enctype='multipart/form-data' id='funcionarioDocForm'>
                              <div class="modal-body" style="padding: 15px 40px">
                                <div class="form-group" style="display: grid;">


                                  <label class="my-1 mr-2" for="tipoDocumento">Status</label><br>
                                  <div style="display: flex;">
                                    <input type="hidden" name="id_fichamedica" id="id_fichamedica" value="<?php echo $_GET['id_fichamedica']; ?>" />
                                    <select class="form-control input-lg mb-md" name="id_status" id="id_status" style="width:170px;" required>
                                      <option selected disabled>Selecionar</option>
                                      <?php
                                      while ($row = $medstatus->fetch_array(MYSQLI_NUM)) {
                                        echo "<option value=" . $row[0] . ">" . $row[1] . "</option>";
                                      }
                                      ?>
                                    </select>
                                  </div>
                                </div>

                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <input type="hidden" class="statusDoenca" name="id_medicacao">
                                <input type="submit" value="Enviar" class="btn btn-primary">
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>

                    </div>
                  </section>
                </div>

                <!-- aba de cadastro médico -->
                <div id="atendimento_medico" class="tab-pane">
                  <section class="panel" id="medicacao">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>

                      <h2 class="panel-title">Atendimento do paciente</h2>
                    </header>
                    <div class="panel-body">
                      <div class="form-group" id="escondermedicacao">

                        <form action='../../controle/control.php' method='post' enctype='multipart/form-data' id='form-atendimento-paciente'>
                          <input type="hidden" name="nomeClasse" value="AtendimentoPacienteControle">
                          <input type="hidden" name="metodo" value="cadastrarAtendimentoPaciente">
                          <hr class="dotted short">
                          <div class="form-group">
                            <div class="col-md-6">
                              <h5 class="obrig">Campos Obrigatórios(*)</h5>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-md-3 control-label" for="profileCompany" id="data_atendimento">Data do atendimento:<sup class="obrig">*</sup></label>
                            <div class="col-md-6">
                              <input type="date" class="form-control" maxlength="10" placeholder="dd/mm/aaaa" name="data_atendimento" id="data_atendimento" max=<?php echo date('Y-m-d');?> min=<?= htmlspecialchars($data_nasc_atendido) ?> required>
                            </div>

                          </div>


                          <!-- listar o funcionario, pessoa nome onde cargo = 3 -->
                          <div class="form-group">
                            <label class="col-md-3 control-label" for="inputSuccess">Usuário:</label>
                            <div class="col-md-8">
                              <input class="form-control" style="width:230px;" name="usuario" id="usuario" value="<?php echo $funcionarioNome; ?>" disabled="true">
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-md-3 control-label" for="inputSuccess">Médico:<sup class="obrig">*</sup></label>
                            <div class="col-md-6">

                              <select class="form-control input-lg mb-md" name="medicos" id="medicos" required>
                                <option selected disabled>Selecionar</option>

                              </select>
                            </div>
                            <a onclick="adicionar_medico()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                          </div>

                          <div class="form-group">
                            <label class="col-md-3 control-label" for="profileCompany" for="texto">Descrição:<sup class="obrig">*</sup></label>
                            <div class='col-md-6' id='div_texto' style="height: 499px;">
                              <textarea cols='30' rows='3' id='despacho' name='texto' class='form-control' value="teste" placeholder="teste" required></textarea>
                            </div>
                          </div>

                          <br>

                          <div class="form-group" id="primeira_medicacao">
                            <label class="col-md-3 control-label" for="inputSuccess">Medicamento:<sup class="obrig">*</sup></label>
                            <div class="col-md-6">
                              <input type="text" class="form-control meddisabled" name="nome_medicacao" id="nome_medicacao">
                            </div>

                          </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="profileCompany">Dosagem:<sup class="obrig">*</sup></label>
                        <div class="col-md-6">
                          <input type="text" class="form-control" name="dosagem" id="dosagem">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="profileCompany">Horário:<sup class="obrig">*</sup></label>
                        <div class="col-md-6">
                          <input type="time" class="form-control" name="horario_medicacao" id="horario_medicacao">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-3 control-label" for="profileCompany">Duração:<sup class="obrig">*</sup></label>
                        <div class="col-md-6">
                          <input type="text" class="form-control" name="duracao_medicacao" id="duracao_medicacao">
                        </div>
                      </div>

                      <br>
                      <br>
                      <button type="button" class="btn btn-success" id="botao">Cadastrar medicação</button>

                      <br>
                      <hr class="dotted short">
                      <table class="table table-bordered table-striped mb-none datatable-docfuncional" id="tabmed">
                        <thead>
                          <tr style="font-size:15px;">
                            <th>Medicação</th>
                            <th>Dosagem</th>
                            <th>Horário</th>
                            <th>Duração</th>
                            <th>Ação</th>
                          </tr>
                        </thead>
                        <tbody style="font-size:15px">

                        </tbody>
                      </table>
                      <br>
                      <br>
                      <input type="number" name="id_fichamedica" value="<?= $_GET['id_fichamedica']; ?>" style='display: none;'>
                      <input type="hidden" name="acervo">
                      <input type="submit" class="btn btn-primary" value="Cadastrar atendimento" id="salvar_bd">
                    </div>
                    </form>
                  </section>
                </div>

                <!-- Aba de medicações aplicadas -->
                <div id="medicacoes_aplicadas" class="tab-pane">
                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Medicações aplicadas</h2>
                    </header>

                    <div class="panel-body">
                      <hr class="dotted short">

                      <table class="table table-bordered table-striped mb-none" id="enf">
                        <thead>
                          <tr style="font-size:15px;">
                            <th>Responsável pela aplicação</th>
                            <th>Medicações aplicadas</th>
                            <th>Horário da aplicação</th>
                          </tr>
                        </thead>
                        <tbody id="exibiaplicacao" style="font-size:15px">

                        </tbody>
                      </table>

                      <br>
                      <br>

                      <input type="hidden" name="a_enf">
                  </section>
                </div>
                <div id="intercorrencias" class="tab-pane">
                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Intercorrências</h2>
                    </header>
                    <div class="panel-body">
                      <hr class="dotted short">

                      <div class="form-group" id="exibirintercorrencias">
                        <table class="table table-bordered table-striped" id="datatable-intercorrencias">
                          <thead>
                            <tr style="font-size:15px;">
                              <th>Descrição</th>
                              <th>Data</th>
                            </tr>
                          </thead>
                          <tbody id="doc-tab-intercorrencias">
                          </tbody>
                        </table>
                      </div>
                  </section>
                </div>

                <!-- Aba de medicações aplicadas -->
                <div id="sinais_vitais" class="tab-pane">
                  <header class="panel-heading">
                    <div class="panel-actions">
                      <a href="#" class="fa fa-caret-down"></a>
                    </div>
                    <h2 class="panel-title">Sinais Vitais Aferidos</h2>
                  </header>

                  <div class="panel-body">
                    <hr class="dotted short">

                    <table class="table table-bordered table-striped mb-none" id="tab-sin-vit">
                      <thead>
                        <tr style="font-size:15px;">
                          <th>Data</th>
                          <th>Aferidor</th>
                          <th>Saturação</th>
                          <th>Pressão arterial</th>
                          <th>Frequência cardíaca</th>
                          <th>Frequência repiratória</th>
                          <th>Temperatura</th>
                          <th>HGT</th>
                          <th>Observação</th>
                        </tr>
                      </thead>
                      <tbody id="exibe-sinais-vitais" style="font-size:15px">

                      </tbody>
                    </table>

                    <br>
                    <br>

                    <input type="hidden" name="a_enf">
                  </div>

                  <aside id="sidebar-right" class="sidebar-right">
                    <div class="nano">
                      <div class="nano-content">
                        <a href="#" class="mobile-close visible-xs">
                          Collapse <i class="fa fa-chevron-right"></i>
                        </a>
                        <div class="sidebar-right-wrapper">
                          <div class="sidebar-widget widget-calendar">
                            <h6>Upcoming Tasks</h6>
                            <div data-plugin-datepicker data-plugin-skin="dark"></div>
                            <ul>
                              <li>
                                <time datetime="2014-04-19T00:00+00:00">04/19/2014</time>
                                <span>Company Meeting</span>
                              </li>
                            </ul>
                          </div>
                          <div class="sidebar-widget widget-friends">
                            <h6>Friends</h6>
                            <ul>
                              <li class="status-online">
                                <figure class="profile-picture">
                                  <img src="../../img/semfoto.png" alt="Joseph Doe" class="img-circle">
                                </figure>
                                <div class="profile-info">
                                  <span class="name">Joseph Doe Junior</span>
                                  <span class="title">Hey, how are you?</span>
                                </div>
                              </li>
                              <li class="status-online">
                                <figure class="profile-picture">
                                  <img src="../../img/semfoto.png" alt="Joseph Doe" class="img-circle">
                                </figure>
                                <div class="profile-info">
                                  <span class="name">Joseph Doe Junior</span>
                                  <span class="title">Hey, how are you?</span>
                                </div>
                              </li>
                              <li class="status-offline">
                                <figure class="profile-picture">
                                  <img src="../../img/semfoto.png" alt="Joseph Doe" class="img-circle">
                                </figure>
                                <div class="profile-info">
                                  <span class="name">Joseph Doe Junior</span>
                                  <span class="title">Hey, how are you?</span>
                                </div>
                              </li>
                              <li class="status-offline">
                                <figure class="profile-picture">
                                  <img src="../../img/semfoto.png" alt="Joseph Doe" class="img-circle">
                                </figure>
                                <div class="profile-info">
                                  <span class="name">Joseph Doe Junior</span>
                                  <span class="title">Hey, how are you?</span>
                                </div>
                              </li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>
                  </aside>
      </section>
      <!-- Vendor -->
      <script src="../../assets/vendor/select2/select2.js"></script>
      <script src="../../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
      <script src="../../assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
      <script src="../../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
      <!-- Theme Custom -->
      <script src="../../assets/javascripts/theme.custom.js"></script>
      <!-- Theme Initialization Files -->
      <!-- Examples -->
      <script src="../../assets/javascripts/tables/examples.datatables.default.js"></script>
      <script src="../../assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
      <script src="../../assets/javascripts/tables/examples.datatables.tabletools.js"></script>
      <div class="modal fade" id="excluirimg" role="dialog">
        <div class="modal-dialog">
          <!-- Modal content-->
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">×</button>
              <h3>Excluir um Documento</h3>
            </div>
            <div class="modal-body">
              <p> Tem certeza que deseja excluir a imagem desse documento? Essa ação não poderá ser desfeita! </p>
              <form action="../../controle/control.php" method="GET">
                <input type="hidden" name="id_documento" id="excluirdoc">
                <input type="hidden" name="nomeClasse" value="DocumentoControle">
                <input type="hidden" name="metodo" value="excluir">
                <input type="hidden" name="id" value="">
                <input type="submit" value="Confirmar" class="btn btn-success">
                <button button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
              </form>
            </div>
          </div>
        </div>
      </div>
      <iv class="modal fade" id="editimg" role="dialog">
        <div class="modal-dialog">
          <!-- Modal content-->
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">×</button>
              <h3>Alterar um Documento</h3>
            </div>
            <div class="modal-body">
              <p> Selecione o benefício referente a nova imagem</p>
              <form action="../../controle/control.php" method="POST" enctype="multipart/form-data">
                <select name="descricao" id="teste">
                  <option value="Certidão de Nascimento">Certidão de Nascimento</option>
                  <option value="Certidão de Casamento">Certidão de Casamento</option>
                  <option value="Curatela">Curatela</option>
                  <option value="INSS">INSS</option>
                  <option value="LOAS">LOAS</option>
                  <option value="FUNRURAL">FUNRURAL</option>
                  <option value="Título de Eleitor">Título de Eleitor</option>
                  <option value="CTPS">CTPS</option>
                  <option value="SAF">SAF</option>
                  <option value="SUS">SUS</option>
                  <option value="BPC">BPC</option>
                  <option value="CPF">CPF</option>
                  <option value="Registro Geral">RG</option>
                </select><br />

                <p> Selecione a nova imagem</p>
                <div class="col-md-12">
                  <input type="file" name="doc" size="60" class="form-control">
                </div><br />
                <input type="hidden" name="id_documento" id="id_documento">
                <input type="hidden" name="id" value="">
                <input type="hidden" name="nomeClasse" value="DocumentoControle">
                <input type="hidden" name="metodo" value="alterar">
                <input type="submit" value="Confirmar" class="btn btn-success">
                <button button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
              </form>
            </div>
          </div>
        </div>
    </div>

    <script defer>
      function removerAlergia(id_doc) {
        if (!window.confirm("Tem certeza que deseja inativar essa enfermidade?")) {
          return false;
        }
        let url = "alergia_excluir.php?id_doc=" + id_doc + "&id_fichamedica=<?= $_GET['id_fichamedica'] ?>";
        let data = "";
        $.post(url, data, listarAlergias);
      }

      function editarStatusMedico(id_medicacao) {
        $("#testemed").modal('show');

        $(".statusDoenca").val(id_medicacao);
      }

      function aplicarMedicacao(id_doc) {
        if (!window.confirm("Tem certeza que deseja aplicar essa medicação?")) {
          return false;
        }

        let url = "mudarcor.php?id_doc=" + id_doc + "&id_fichamedica=<?= $_GET['id_fichamedica'] ?>";
        let data = "";
        $.post(url, data);
      }

      //Adicionar alergias
      $(document).ready(function() {
        $("#exibiralergias").append("<div class='col-md-6'><input type='button' class='btn btn-success' value='Adicionar alergia' id='addAlergia'></div>");
        $("#addAlergia").on('click', function() {
          $("#addAlergia").hide();
          $("#div_alergia").css("display", "block");
          $("#salvarAlergia").css("display", "block");
        })
      });

      async function gerar_tipo_exame() {
        const url = `../../controle/control.php?nomeClasse=${encodeURIComponent("ExameControle")}&metodo=${encodeURIComponent("listarTodosTiposDeExame")}`;

        try {
          const response = await fetch(url);

          if (!response.ok) {
            throw new Error('Erro na requisição');
          }

          const situacoes = await response.json();

          const select = document.getElementById('tipoDocumentoExame');

          while (select.firstChild) {
            select.removeChild(select.firstChild);
          }

          const defaultOption = document.createElement('option');
          defaultOption.value = "";
          defaultOption.disabled = true;
          defaultOption.selected = true;
          defaultOption.appendChild(document.createTextNode('Selecionar'));
          select.appendChild(defaultOption);

          situacoes.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id_exame_tipo;
            option.appendChild(document.createTextNode(item.descricao));
            select.appendChild(option);
          });

        } catch (error) {
          console.error('Erro ao carregar exames:', error);
        }
      }

      function adicionar_tipo_exame() {
        const url = '../../controle/control.php';
        let exame = window.prompt("Cadastre um novo exame: ");
        if (!exame) {
          return;
        }
        const data = {
          exame: exame,
          nomeClasse: "ExameControle",
          metodo: "inserirTipoExame"
        }
        fetch(url, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
          }).then(response => {
            if (!response.ok) {
              throw new Error('Erro na requisição');
            }
            return response.json();
          }).then(result => {
            gerar_tipo_exame();
          })
          .catch(error => {
            console.error('Erro ao enviar dados:', error);
          });
      }

      async function adicionar_exame() {
        const formData = new FormData();
        const documentos = document.getElementById("documentoExame");
        const idFichaMedica = document.getElementById("exame_id_fichamedica");
        const tipoDocumento = document.getElementById("tipoDocumentoExame");

        if (!documentos.files[0]) {
          window.alert("É necessário inserir um documento");
          return;
        }

        if (!tipoDocumento) {
          window.alert("É necessário escolher um tipo de exame");
          return;
        }

        formData.append("arquivo", documentos.files[0]);
        formData.append("tipoDocumento", tipoDocumento.value);
        formData.append("id_fichamedica", idFichaMedica.value);
        formData.append("nomeClasse", "ExameControle");
        formData.append("metodo", "inserirExame");

        let mensagem = "";

        try {
          const requisicao = await fetch("../../controle/control.php", {
            method: "POST",
            body: formData
          })
          if (requisicao.ok) {
            mensagem = "Exame adicionado com sucesso";
            gerarExames();
          }
        } catch (e) {
          mensagem = "Erro ao adicionar exame";
        } finally {
          documentos.value = '';
          tipoDocumento.value = "";
          $('#docFormModal').modal('hide');
          window.alert(mensagem);
        }
      }

      async function listarExamesPorId(id) {
        const url = `../../controle/control.php?id_fichamedica=${id}&nomeClasse=${encodeURIComponent("ExameControle")}&metodo=${encodeURIComponent("listarExamesPorId")}`;

        try {
          const response = await fetch(url);

          if (!response.ok) {
            throw new Error('Erro na requisição');
          }

          const dados = await response.json();

          return dados;

        } catch (error) {
          console.error('Erro ao carregar exames:', error);
        }
      }

      async function deletar_exame(id) {
        if (!window.confirm("Tem certeza que deseja remover esse exame?")) {
          return;
        }
        let url = `../../controle/control.php?id_exame=${id}&metodo=${encodeURIComponent("removerExame")}&nomeClasse=${encodeURIComponent("ExameControle")}`;

        let options = {
          method: "GET" //usei GET pois aparentemente o delete ta desabilitado
        }
        let mensagem = "";

        try {
          let response = await fetch(url, options);
          if (response.ok) {
            mensagem = "Exame deletado com sucesso";
          } else {
            throw new Error("Erro HTTP: " + response.status);
          }
        } catch (e) {
          mensagem = "Erro ao deletar exame";
        } finally {
          window.alert(mensagem);
          gerarExames();
        }
      }
      async function baixarArquivo(id) {
        try {
          const response = await fetch(`../../controle/control.php?nomeClasse=${encodeURIComponent("ExameControle")}&metodo=${encodeURIComponent("retornaArquivoPorId")}&id_exame=${id}`);
          if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = "exame_" + id;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
          }
        } catch (e) {
          window.alert("Erro ao Baixar arquivo");
        }
      }

      const formExames = document.getElementById("ExameDocForm");

      formExames.addEventListener("submit", async (e) => {
        e.preventDefault();
        adicionar_exame();
      })

      async function gerarMedicos() {
        medicos = await listarTodosOsMedicos()
        let length = medicos.length - 1;
        let select = document.getElementById("medicos");
        let possuiSemMedicoDefinido = false;
        while (select.firstChild) {
          select.removeChild(select.firstChild)
        }
        let selecionar = document.createElement("option");
        selecionar.value = "";
        selecionar.textContent = "Selecionar"
        selecionar.selected = true;
        selecionar.disabled = true;
        select.appendChild(selecionar)
        for (let i = 0; i <= length; i = i + 1) {
          if (Number(medicos[i].id_medico) === 0) {
            possuiSemMedicoDefinido = true;
          }
          let option = document.createElement("option");
          option.value = medicos[i].id_medico;
          option.textContent = medicos[i].nome;
          select.appendChild(option);
        }

        if (!possuiSemMedicoDefinido) {
          let optionSemMedico = document.createElement("option");
          optionSemMedico.value = "0";
          optionSemMedico.textContent = "Sem médico definido";
          select.appendChild(optionSemMedico);
        }
      }

      function adicionar_medico() {
        const url = '../../controle/control.php'

        let nome_medico = window.prompt("Insira o nome do médico:");
        let crm_medico = window.prompt("Insira o CRM do médico:");

        if (!nome_medico || !crm_medico) {
          return;
        }

        const data = {
          crm: crm_medico,
          nome: nome_medico,
          nomeClasse: "MedicoControle",
          metodo: "inserirMedico"
        }

        fetch(url, {
            method: "POST",
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
          })
          .then(response => {
            if (!response.ok) {
              throw new Error('Erro na requisição');
            }
            return response.json();
          })
          .then(result => {
            gerarMedicos();
          })
          .catch(error => {
            console.error('Erro ao enviar dados:', error);
          });
      }

      function gerar_alergia() {
        const url = '../../controle/control.php';
        $.ajax({
          data: {
            nomeClasse: "AlergiaControle",
            metodo: "listarTodasAsAlergias"
          },
          type: "POST",
          url: url,
          async: true,
          success: function(response) {
            var situacoes_alergia = response;
            let alergias = <?= $alergias; ?>;
            $('#id_CID_alergia').empty();
            $('#id_CID_alergia').append('<option selected disabled>Selecionar</option>');
            $.each(situacoes_alergia, function(i, item) {
              if (!(alergias.includes(item))) {
                $('#id_CID_alergia').append('<option value="' + item.id_CID + '">' + item.descricao + '</option>');
              }
            });
          },
          dataType: 'json'
        });
      }

      function adicionar_alergia() {
        url = 'adicionar_alergia.php';
        let nome_alergia = window.prompt("Insira o nome da alergia:");

        if (!nome_alergia || nome_alergia == '') {
          return;
        }
        data = {
          nome: nome_alergia
        };
        $.ajax({
          type: "POST",
          url: url,
          data: data,
          success: function(response) {
            gerar_alergia();
          },
          error: function(response) {
            // console.log(response);
          },
        })
      }

      function alergia_upload() {
        url = 'alergia_upload.php';
        let id_CID_alergia = $("#id_CID_alergia").val();
        let id_fichamedica = "<?= $id_fichamedica ?>";
        let data = {
          id_CID_alergia: id_CID_alergia,
          id_fichamedica: id_fichamedica
        };
        $.post({
          url: url,
          data: data,
          success: function(response) {
            location.reload();
          }
        })
      }

      // codigo para inserir medicacao na tabela do medico
      $(function() {
        function lerCamposMedicacao() {
          return {
            nome_medicacao: ($("#nome_medicacao").val() || "").trim(),
            dosagem: ($("#dosagem").val() || "").trim(),
            horario: ($("#horario_medicacao").val() || "").trim(),
            tempo: ($("#duracao_medicacao").val() || "").trim()
          };
        }

        function limparCamposMedicacao() {
          $("#nome_medicacao").val("");
          $("#dosagem").val("");
          $("#horario_medicacao").val("");
          $("#duracao_medicacao").val("");
        }

        function todosCamposMedicacaoPreenchidos(medicacao) {
          return (
            medicacao.nome_medicacao !== "" &&
            medicacao.dosagem !== "" &&
            medicacao.horario !== "" &&
            medicacao.tempo !== ""
          );
        }

        function algumCampoMedicacaoPreenchido(medicacao) {
          return (
            medicacao.nome_medicacao !== "" ||
            medicacao.dosagem !== "" ||
            medicacao.horario !== "" ||
            medicacao.tempo !== ""
          );
        }

        function adicionarLinhaMedicacao(medicacao) {
          $("#tabmed").find(".dataTables_empty").hide();
          $("#tabmed tbody").append(
            $("<tr>")
              .addClass("tabmed")
              .append($("<td>").text(medicacao.nome_medicacao))
              .append($("<td>").text(medicacao.dosagem))
              .append($("<td>").text(medicacao.horario))
              .append($("<td>").text(medicacao.tempo))
              .append(
                $("<td style='display: flex; justify-content: space-evenly;'>").append(
                  $("<button type='button' class='btn btn-danger'><i class='fas fa-trash-alt'></i></button>")
                )
              )
          );
        }

        function atualizarAcervoInputPelaTabela() {
          const tabelaMedicacao = [];

          $("#tabmed tbody tr.tabmed").each(function() {
            const colunas = $(this).find("td");
            if (colunas.length < 4) {
              return;
            }

            tabelaMedicacao.push({
              nome_medicacao: ($(colunas[0]).text() || "").trim(),
              dosagem: ($(colunas[1]).text() || "").trim(),
              horario: ($(colunas[2]).text() || "").trim(),
              tempo: ($(colunas[3]).text() || "").trim()
            });
          });

          $("input[name=acervo]").val(JSON.stringify(tabelaMedicacao));
          return tabelaMedicacao;
        }

        $("#botao").on("click", function() {
          const medicacao = lerCamposMedicacao();

          if (!todosCamposMedicacaoPreenchidos(medicacao)) {
            alert("Por favor, informe a medicação corretamente!");
            return;
          }

          adicionarLinhaMedicacao(medicacao);
          limparCamposMedicacao();
          atualizarAcervoInputPelaTabela();
        });

        $("#tabmed").on("click", "button", function(e) {
          e.preventDefault();
          $(this).closest("tr").remove();
          atualizarAcervoInputPelaTabela();
        });

        const formAtendimento = $("#form-atendimento-paciente");
        formAtendimento.on("submit", function(e) {
          const medicoSelecionado = $("#medicos").val();
          if (!medicoSelecionado) {
            e.preventDefault();
            alert('Selecione um médico. Se necessário, escolha "Sem médico definido".');
            return;
          }

          // Só considera medicações já adicionadas na tabela via botão
          // "Cadastrar medicação".
          const medicacaoDigitada = lerCamposMedicacao();
          if (algumCampoMedicacaoPreenchido(medicacaoDigitada)) {
            e.preventDefault();
            alert('Para incluir esta medicação, clique em "Cadastrar medicação" antes de salvar o atendimento.');
            return;
          }

          atualizarAcervoInputPelaTabela();
        });

      });


      $(function() {
        var sinaisvitais = <?= $sinaisvitais ?>;
        $("#sin-vit-tab").empty();

        $.each(sinaisvitais, function(i, item) {
          $("#exibe-sinais-vitais")
            .append($("<tr>")
              .append($("<td>").text(item.data))
              .append($("<td>").text(item.nome + " " + (item.sobrenome !== null ? item.sobrenome : "")))
              .append($("<td>").text(item.saturacao))
              .append($("<td>").text(item.pressao_arterial))
              .append($("<td>").text(item.frequencia_cardiaca))
              .append($("<td>").text(item.frequencia_respiratoria))
              .append($("<td>").text(item.temperatura))
              .append($("<td>").text(item.hgt))
              .append($("<td>").addClass("celula-observacao").text(item.observacao))
            )
        });
      });
      $(document).ready(function() {
        $('#tab-sin-vit').DataTable({
          "order": [
            [0, "desc"]
          ]
        });
      });



      var editor2 = CKEDITOR.replace('prontuario', {
        readOnly: true
      });

      editor2.on('required', function(e) {
        alert("Por favor, informe a descrição!");
        e.cancel();
      });


      function editarProntuario() {
        editor2.setReadOnly(false);
        document.getElementById('btn-editarProntuario').classList.add('hidden');
        document.getElementById('btn-adicionarAoHistorico').classList.add('hidden');
        document.getElementById('btn-listarDoHistorico').classList.add('hidden');
        document.getElementById('btn-cancelarEdicao').classList.remove('hidden');
        document.getElementById('btn-confirmarEdicao').classList.remove('hidden');
      }

      function cancelarEdicao() {
        editor2.setReadOnly(true);
        document.getElementById('btn-editarProntuario').classList.remove('hidden');
        document.getElementById('btn-adicionarAoHistorico').classList.remove('hidden');
        document.getElementById('btn-listarDoHistorico').classList.remove('hidden');
        document.getElementById('btn-cancelarEdicao').classList.add('hidden');
        document.getElementById('btn-confirmarEdicao').classList.add('hidden');
        location.reload();
      }

      function listarProntuariosDoHistorico() {
        const idPaciente = <?= $idPaciente ?>;
        window.location.href = `./historico_prontuarios.php?id_paciente=${idPaciente}`
      }

      //Formatar data para brasileiro
      function formatarDataBr(data) {
        let hour = null;

        // Verifica se existe parte de hora
        if (data.split(" ")[1] !== undefined && data.split(" ")[1] !== null) {
          const partes = data.split(" ");
          hour = partes[1].split(":");
          data = partes[0];
        }

        const parts = data.split('-'); // Supondo que a data esteja no formato 'YYYY-MM-DD'

        let dataFinal = "";
        let dataObj;

        if (hour !== null) {
          dataObj = new Date(parts[0], parts[1] - 1, parts[2], hour[0], hour[1], hour[2]);
          const horaFormatada = dataObj.toLocaleTimeString('pt-BR', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
          });
          dataFinal += " " + horaFormatada;
        } else {
          dataObj = new Date(parts[0], parts[1] - 1, parts[2]);
        }

        const options = {
          year: 'numeric',
          month: '2-digit',
          day: '2-digit'
        };

        const dataFormatada = dataObj.toLocaleDateString('pt-BR', options);
        dataFinal = dataFormatada + dataFinal;

        return dataFinal;
      }


      async function listarTodosOsMedicos() {
        const nomeClasse = 'MedicoControle';
        const metodo = 'listarTodosOsMedicos';

        const url = `../../controle/control.php?nomeClasse=${encodeURIComponent(nomeClasse)}&metodo=${encodeURIComponent(metodo)}`;

        try {
          const response = await fetch(url, {
            headers: {
              'Accept': 'application/json'
            }
          });

          if (!response.ok) {
            const data = await response.json();
            let erro = data.erro;
            throw new Error(`Erro na requisição: ${response.status} - ${erro}`);
          }

          const data = await response.json();

          return data ?? []; //Retorna um array vazio se `null`
        } catch (error) {
          console.error('Erro ao buscar médicos:', error.message);
          return [];
        }
      }

      document.addEventListener("DOMContentLoaded", async () => {
        await gerarEnfermidade();
        await gerarMedicos();
        await gerarExames();
        await gerarEnfermidadesDoPaciente();
        const btnCadastrarEnfermidade = document.getElementById('btn-cadastrar-enfermidade');

        // Adiciona validação ao clique do botão
        btnCadastrarEnfermidade.addEventListener('click', function(event) {
          event.preventDefault();

          let veriDataPassada = true;
          let veriDataFutura = true;

          const dataInput = document.getElementById("data_diagnostico");
          const dataValue = dataInput.value;

          if (!dataValue) {
            event.preventDefault();
            event.stopImmediatePropagation();
            alert("Por favor, preencha a data do diagnóstico.");
            return;
          }

          const dataDigitada = new Date(dataValue);
          const dataPaciente = new Date("<?= $data_nasc_atendido ?>T00:00:00");
          console.log(dataPaciente)

          const formatador = new Intl.DateTimeFormat('pt-BR');

          if (dataDigitada < dataPaciente) {
              event.preventDefault();
              event.stopImmediatePropagation();
              const nascFormatado = formatador.format(dataPaciente);
              alert("Data inválida: Não pode ser anterior à data de nascimento (" + nascFormatado + ").");
              veriDataPassada = false;
              return; 
          }

          // Verifica se a data é futura
          const partesData = dataValue.split('-');
          const dataSelecionada = new Date(partesData[0], partesData[1] - 1, partesData[2]);
          const dataAgora = new Date();
          dataAgora.setHours(0, 0, 0, 0); 

          if (dataSelecionada > dataAgora) {
            event.preventDefault();
            event.stopImmediatePropagation();
            alert("A data do diagnóstico não pode ser no futuro, ajuste para a data atual: " + formatador.format(dataAgora));
            veriDataFutura = false;
            return;
          }

          if(veriDataPassada && veriDataFutura){
              cadastrarEnfermidade(event);
          }else{
            return;
          }

        });
      })

      carregarIntercorrencias();
    </script>

    <!-- Vendor -->
    <script src="<?php echo WWW; ?>assets/vendor/select2/select2.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
    <script src="<?php echo WWW; ?>assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>

    <!-- Theme Custom -->
    <script src="<?php echo WWW; ?>assets/javascripts/theme.custom.js"></script>

    <script src="<?php echo WWW; ?>assets/javascripts/theme.js"></script>

    <!-- Theme Initialization Files -->
    <!-- Examples -->
    <script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.default.js"></script>
    <script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
    <script src="<?php echo WWW; ?>assets/javascripts/tables/examples.datatables.tabletools.js"></script>

    <!-- importante para a aba de exames -->
    <script src="../geral/formulario.js"></script>

    <div align="right">
      <iframe src="https://www.wegia.org/software/footer/saude.html" width="200" height="60" style="border:none;"></iframe>
    </div>
</body>

</html>
