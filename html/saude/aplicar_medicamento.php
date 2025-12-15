<?php
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  //Mudar para session
  if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
  }

  $idPessoa = filter_var($_SESSION['id_pessoa'], FILTER_VALIDATE_INT);

  if (!$idPessoa || $idPessoa < 1) {
    http_response_code(400);
    echo json_encode(['erro' => 'O id do usuário não está dentro dos limites permitidos.']);
    exit();
  }

  if (!isset($_SESSION['id_fichamedica'])) {
    header('Location: ../../controle/control.php?metodo=listarUm&nomeClasse=SaudeControle&nextPage=../html/saude/aplicar_medicamento.php');
  }

  //verificar se o usuário possui as permissões necessárias para acessar a página
  require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
  permissao($_SESSION['id_pessoa'], 5);

  include_once '../../classes/Cache.php';
  require_once "../personalizacao_display.php";

  require_once ROOT . "/controle/SaudeControle.php";

  $id = filter_input(INPUT_GET, 'id_fichamedica', FILTER_VALIDATE_INT);

  if (!$id || $id < 1) {
    http_response_code(400);
    echo json_encode(['erro' => 'O id da ficha médica informado não está dentro dos limites permitidos.']);
    exit();
  }
  
  require_once "../../dao/Conexao.php";
  $pdo = Conexao::connect();
  
  $stmtPessoaPaciente = $pdo->prepare("SELECT id_pessoa FROM saude_fichamedica WHERE id_fichamedica = :id_fichamedica");
  $stmtPessoaPaciente->bindValue(':id_fichamedica', $id, PDO::PARAM_INT);
  $stmtPessoaPaciente->execute();
  $paciente = $stmtPessoaPaciente->fetch(PDO::FETCH_ASSOC);

  if (!$paciente || !isset($paciente['id_pessoa'])) {
      http_response_code(404);
      echo 'Ficha médica não encontrada ou não associada a uma pessoa.';
      exit();
  }
  $idPessoaPaciente = $paciente['id_pessoa']; 


  $cache = new Cache();
  $teste = $cache->read($id);
  $_SESSION['id_upload_med'] = $id;

  if (!isset($teste)) {
    header('Location: ../../controle/control.php?metodo=listarUm&nomeClasse=SaudeControle&nextPage=../html/saude/aplicar_medicamento.php?id_fichamedica=' . $id . '&id=' . $id);
  }

  $stmtExibirMedicamento = $pdo->prepare("SELECT * FROM saude_medicacao sm JOIN saude_atendimento sa ON(sm.id_atendimento=sa.id_atendimento) JOIN saude_fichamedica sf ON(sa.id_fichamedica=sf.id_fichamedica) WHERE sm.saude_medicacao_status_idsaude_medicacao_status = 1 and sf.id_fichamedica=:idFichaMedica");

  $stmtExibirMedicamento->bindValue(':idFichaMedica', $id, PDO::PARAM_INT);
  $stmtExibirMedicamento->execute();

  $exibimedparaenfermeiro = json_encode($stmtExibirMedicamento->fetchAll(PDO::FETCH_ASSOC));

  $medicamentoenfermeiro = $pdo->query("SELECT * FROM saude_medicacao");
  $medstatus = $pdo->query("SELECT * FROM saude_medicacao_status");

  $stmtPessoa = $pdo->prepare("SELECT nome FROM pessoa p JOIN funcionario f ON(p.id_pessoa = f.id_pessoa) WHERE f.id_pessoa =:idPessoa ");

  $stmtPessoa->bindValue(':idPessoa', $idPessoa);

  $stmtPessoa->execute();

  $id_funcionario = $stmtPessoa->fetch(PDO::FETCH_ASSOC)['nome'];

  $stmtProntuarioPublico = $pdo->prepare("SELECT descricao FROM saude_fichamedica_descricoes WHERE id_fichamedica=:idFichaMedica");

  $stmtProntuarioPublico->bindValue('idFichaMedica', $id);
  $stmtProntuarioPublico->execute();

  $prontuariopublico = json_encode($stmtProntuarioPublico->fetchAll(PDO::FETCH_ASSOC));

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

<!-- Theme Base, Components and Settings -->
<!-- <script src="<?php echo WWW; ?>assets/javascripts/theme.js"></script> -->

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

    $("#header").load("../header.php");
    $(".menuu").load("../menu.php");
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

  .cke_inner {
    height: 500px;
  }

  #cke_1_contents {
    height: 455px !important;
  }

  .col-md-3 {
    width: 10%;
  }
</style>


<!doctype html>
<html class="fixed">

<head>
  <!-- Basic -->
  <meta charset="UTF-8">
  <title>Aplicar medicamento</title>
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

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

  <script>
    $(function() {
      // pega no SaudeControle, listarUm //

      if (localStorage.getItem("currentTab") === "2") {
        $("#tab1").removeClass("active");
        $("#tab2").addClass("active");
        $("#overview").removeClass("active");
        $("#atendimento_enfermeiro").addClass("active");

      }

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

          $("#exibirtipo").show();
          $("#sangue").text("Sangue: " + item.tipo_sanguineo);
          $("#sangue").val(item.tipo_sanguineo);

        }
      });
    });

    function limparInputDataTime() {
      const dataHora = document.getElementById("dataHora")
      dataHora.value = "";
    }

    function carregarMedicamentosParaAplicar(modoSelecionar = false) {
      const exibimedparaenfermeiro = <?= $exibimedparaenfermeiro ?>;
      const tabela = document.getElementById("tabela");
      
      tabela.innerHTML = "";

      exibimedparaenfermeiro.forEach(function(item) {
        const tr = document.createElement("tr");
        tr.className = `item ${item.medicamento}`;

        const td1 = document.createElement("td");
        td1.className = "txt-center";
        td1.textContent = item.medicamento;

        const td2 = document.createElement("td");
        td2.className = "txt-center";
        td2.textContent = item.dosagem;

        const td3 = document.createElement("td");
        td3.className = "txt-center";
        td3.textContent = item.horario;

        const td4 = document.createElement("td");
        td4.className = "txt-center";
        td4.textContent = item.duracao;

        const td5 = document.createElement("td");
        td5.style.textAlign = "center";
        td5.style.verticalAlign = "middle";

        if (modoSelecionar) {
            const checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.checked = true; 
            checkbox.className = "chk-med-bulk"; 
            
            checkbox.setAttribute("data-idMedicacao", item.id_medicacao);
            checkbox.setAttribute("data-idPessoa", item.id_pessoa);
            checkbox.setAttribute("data-idFuncionario", item.id_funcionario);
            
            td5.appendChild(checkbox);

        } else {
            const a = document.createElement("a");
            a.title = "Aplicar medicamento";

            const button = document.createElement("button");
            button.className = "btn btn-primary";
            button.type = "button";
            button.setAttribute("data-toggle", "modal");
            button.setAttribute("data-target", "#modalHorarioAplicacao");
            button.setAttribute("data-idMedicacao", item.id_medicacao);
            button.setAttribute("data-idPessoa", item.id_pessoa);
            button.setAttribute("data-idFuncionario", item.id_funcionario);
            button.innerHTML = "<i class='glyphicon glyphicon-hand-up'></i>";
            button.addEventListener("click", function() {
              enviarInformacoesParaModal(this);
            });

            a.appendChild(button);
            td5.appendChild(a);
        }

        tr.append(td1, td2, td3, td4, td5);
        tabela.appendChild(tr);
      });
    }

    function formatarDataHoraBr(data) {

      data = data.split(" ");

      const hour = data[1].split(":");

      const parts = data[0].split('-'); // Supondo que a data esteja no formato 'YYYY-MM-DD'

      // Converte para uma nova data no fuso horário local
      const dataObj = new Date(parts[0], parts[1] - 1, parts[2], hour[0], hour[1], hour[2]);

      const options = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
      };
      const horaFormatada = dataObj.toLocaleTimeString('pt-BR', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      });
      const dataFormatada = dataObj.toLocaleDateString('pt-BR', options);

      return `${dataFormatada} ${horaFormatada}`
    }

    function carregarAplicacoes(id_fichamedica) {
      const url = `../../controle/control.php?nomeClasse=${encodeURIComponent("MedicamentoPacienteControle")}&metodo=${encodeURIComponent("listarMedicamentosAplicadosPorIdDaFichaMedica")}&id_fichamedica=${encodeURIComponent(id_fichamedica)}`;
      fetch(url)
        .then(res => res.json())
        .then(medaplicadas => {
          const tabela = document.getElementById("exibiaplicacao");

          limparContainer(tabela);

          medaplicadas.forEach(item => {

            item.aplicacao = formatarDataHoraBr(item.aplicacao);
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
    }

    $(function() {
      var prontuariopublico = <?= $prontuariopublico ?>;
      stringConcatenada = "";
      $.each(prontuariopublico, function(i, item) {
        stringConcatenada += item.descricao;
      });
      $("#prontuario_publico")
        .append($("<tr>")
          .append($("<td>")).html(stringConcatenada)
        )
    });

    function limparContainer(container) {
      while (container.firstChild) {
        container.removeChild(container.firstChild);
      }
    }

    function enviarInformacoesParaModal(botao) {
      let id_pessoa = botao.getAttribute("data-idPessoa");
      let id_medicacao = botao.getAttribute("data-idMedicacao");
      let id_funcionario = botao.getAttribute("data-idFuncionario");


      const input_id_pessoa = document.getElementById("id_pessoa");
      const input_id_medicacao = document.getElementById("id_medicacao");
      const input_id_funcionario = document.getElementById("id_funcionario");

      input_id_pessoa.value = id_pessoa;
      input_id_medicacao.value = id_medicacao;
      input_id_funcionario.value = id_funcionario;
    }

    function mostrarErro(mensagem) {
        const divErro = document.getElementById("msg_erro_modal");
        if (divErro) {
            divErro.innerText = mensagem;
            divErro.style.display = "block";
        } else {
            alert(mensagem);
        }
    }
    async function enviarDataHoraAplicacaoMedicamento(event) {
      event.preventDefault();

      const dataHoraInput = document.getElementById("dataHora");
      const divErro = document.getElementById("msg_erro_modal");
      
      if (divErro) {
          divErro.style.display = "none"; 
          divErro.innerText = "";
      }
      if (!dataHoraInput.value) {
          mostrarErro("Por favor, preencha a data e hora.");
          return;
      }
      
      const anoDigitado = parseInt(dataHoraInput.value.substring(0, 4));
      const anoMinimo = 1929;

      if (anoDigitado < anoMinimo) {
          mostrarErro("Data inválida: O ano não pode ser anterior a 1929.");
          return; 
      }

      const agoraString = getDataLocalAtual();
      const dataSelecionada = new Date(dataHoraInput.value);
      const dataLimite = new Date(agoraString);

      if (dataSelecionada > dataLimite) {
          mostrarErro("A data e hora da aplicação não pode ser no futuro. Ajuste para o momento atual.");
          return;
      }
      let idPessoaFuncionario = <?= $idPessoa ?>;
      let arrayPromessas = [];

      const isModoSelecao = document.querySelectorAll('.chk-med-bulk').length > 0;

      if (isModoSelecao) {
        const checkboxesMarcados = document.querySelectorAll('.chk-med-bulk:checked');
        
        if (checkboxesMarcados.length === 0) {
             mostrarErro("Nenhum medicamento selecionado.");
             return;
        }

        checkboxesMarcados.forEach(function(chk) {
            const dados = {
                nomeClasse: encodeURIComponent("MedicamentoPacienteControle"),
                metodo: encodeURIComponent("inserirAplicacao"),
                id_medicacao: chk.getAttribute('data-idMedicacao'),
                id_pessoa: chk.getAttribute('data-idPessoa'),
                id_pessoa_funcionario: idPessoaFuncionario,
                dataHora: dataHoraInput.value
            };
            const dadosJson = JSON.stringify(dados);
            
            // Adiciona a requisição ao array de promessas
            arrayPromessas.push(
                fetch(`../../controle/control.php?`, {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: dadosJson
                }).then(res => res.json())
            );
        });

      } else {
        const form = event.target;
        const formData = new FormData(form);

        const dados = {
            nomeClasse: encodeURIComponent("MedicamentoPacienteControle"),
            metodo: encodeURIComponent("inserirAplicacao"),
            id_medicacao: formData.get('id_medicacao'),
            id_pessoa: formData.get('id_pessoa'),
            id_pessoa_funcionario: idPessoaFuncionario,
            dataHora: formData.get('dataHora')
        };
        const dadosJson = JSON.stringify(dados);
        
        arrayPromessas.push(
            fetch(`../../controle/control.php?`, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: dadosJson
            }).then(res => res.json())
        );
      }

      // Executa todas as requisições (seja 1 ou várias)
      Promise.all(arrayPromessas)
        .then(resultados => {
          limparInputDataTime();
          const id_fichamedica = document.getElementById("id_fichamedica").value;
          carregarAplicacoes(id_fichamedica);

          const modal = document.getElementById('modalHorarioAplicacao');
          if (modal) {
            $('#modalHorarioAplicacao').modal('hide')
          }
        })
        .catch(erro => {
          console.error('Erro ao enviar:', erro);
          alert('Erro ao conectar com o servidor.');
        });
    }


    function getDataLocalAtual() {
      const agora = new Date();
      const adicionarZero = numero => numero.toString().padStart(2, '0');

      const ano = agora.getFullYear();
      const mes = adicionarZero(agora.getMonth() + 1);
      const dia = adicionarZero(agora.getDate());
      const horas = adicionarZero(agora.getHours());
      const minutos = adicionarZero(agora.getMinutes());

      return `${ano}-${mes}-${dia}T${horas}:${minutos}`;
    }

    function definirDataHoraAtualSeVazio(campo) {
      const nowString = getDataLocalAtual();
      
      if (!campo.value) {
        campo.value = nowString;
      }
    }

    function usuarioAlterouDataHora() {
      const divErro = document.getElementById('msg_erro_modal');
      if (divErro) {
        divErro.style.display = 'none';
      }
    }
    async function enviarMedicacaoSOS(event) {
      event.preventDefault(); 

      const dadosForm = {
        medicamento: document.getElementById('nome_medicacao').value,
        dosagem: document.getElementById('dosagem_sos').value,
        horario: document.getElementById('horario_medicacao_sos').value,
        duracao: document.getElementById('duracao_medicacao_sos').value,
        status_id: 1 // 1 = "Prescrito" (padrão)
      };

      const id_pessoa_paciente = <?= $idPessoaPaciente; ?>;
      const id_pessoa_funcionario = <?= $idPessoa; ?>; 
      

      if (!dadosForm.medicamento || !dadosForm.dosagem || !dadosForm.horario || !dadosForm.duracao) {
        alert('Por favor, preencha todos os campos do Medicamento SOS.');
        return;
      }
      const payload = {
        nomeClasse: "MedicamentoPacienteControle",
        metodo: "cadastrarMedicacaoSOS",

        id_pessoa_paciente: id_pessoa_paciente,
        id_pessoa_funcionario: id_pessoa_funcionario,
        medicamento: dadosForm.medicamento,
        dosagem: dadosForm.dosagem,
        horario: dadosForm.horario,
        duracao: dadosForm.duracao,
        status_id: dadosForm.status_id
      };

      try {
        const response = await fetch(`../../controle/control.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!response.ok || data.status === "erro") {
          throw new Error(data.mensagem || 'Erro desconhecido ao cadastrar.');
        }

        location.reload();

      } catch (error) {
        console.error('Erro ao cadastrar SOS:', error);
        alert('Falha ao cadastrar: ' + error.message);
      }
    }

    $(document).ready(function() {
      const botaoSOS = document.getElementById('botao_cadastrar_sos');
      if (botaoSOS) {
        botaoSOS.addEventListener('click', enviarMedicacaoSOS);
      }
      
      $('#modalHorarioAplicacao').on('show.bs.modal', function(e) {
        const dataHoraInput = document.getElementById("dataHora");
        const nowString = getDataLocalAtual();
        
        if (!dataHoraInput.value) {
          dataHoraInput.value = nowString;
        }

        // Limpa a mensagem de erro ao abrir
        const divErro = document.getElementById("msg_erro_modal");
        if (divErro) {
          divErro.style.display = "none";
        }
      });

      $('#modalHorarioAplicacao').on('hidden.bs.modal', function(e) {
        // Garante que o campo esteja limpo ao fechar
        limparInputDataTime();
      });
    });

  </script>
  <style type="text/css">
    .obrig {
      color: rgb(255, 0, 0);
    }

    #prontuario_publico tr p {
      padding: 5px 10px 5px 10px;
      word-wrap: break-word;
    }
    #form_medicacao_sos{
      padding: 10px;
    }
    @media(max-width:768px) {
      #prontuario_publico tr p {
        max-width: 250px;
        word-wrap: break-word;
      }
    }
  </style>

</head>

<body>
  <input type="hidden" id="id_fichamedica" value="<?= $id ?>">
  <section class="body">
    <div id="header"></div>
    <!-- end: header -->
    <div class="inner-wrapper">
      <!-- start: sidebar -->
      <aside id="sidebar-left" class="sidebar-left menuu"></aside>
      <!-- end: sidebar -->
      <section role="main" class="content-body">
        <header class="page-header">
          <h2>Aplicar medicamento</h2>
          <div class="right-wrapper pull-right">
            <ol class="breadcrumbs">
              <li>
                <a href="../index.php">
                  <i class="fa fa-home"></i>
                </a>
              </li>
              <li><span>Aplicar medicamento</span></li>
            </ol>
            <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
          </div>
        </header>
        <div class="row">
          <div class="col-md-4 col-lg-3">
            <section class="panel">
              <div class="panel-body">

                <div class="thumb-info mb-md">
                  <img id="imagem" alt="John Doe">
                </div>
              </div>
            </section>
          </div>
          <div class="col-md-8 col-lg-8">
            <div class="tabs">
              <ul class="nav nav-tabs tabs-primary">
                <li class="active" id="tab1">
                  <a href="#overview" data-toggle="tab">Informações Pessoais</a>
                </li>
                <li id="tab2">
                  <a href="#atendimento_enfermeiro" data-toggle="tab">Aplicações enfermeiro</a>
                </li>
              </ul>

              <div class="tab-content">
                <div id="overview" class="tab-pane active">
                  <form class="form-horizontal" method="post" action="../../controle/control.php">
                    <input type="hidden" name="nomeClasse" value="SaudeControle">
                    <section class="panel">
                      <header class="panel-heading">
                        <div class="panel-actions">
                          <a href="#" class="fa fa-caret-down"></a>
                        </div>
                        <h2 class="panel-title">Informações pessoais</h2>
                      </header>

                      <div class="panel-body">
                        <hr class="dotted short">
                        <fieldset>

                          <div class="form-group">
                            <label class="col-md-3 control-label" for="profileFirstName">Nome</label>
                            <div class="col-md-8">
                              <input type="text" class="form-control" disabled name="nome" id="nome">
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-md-3 control-label" for="profileLastName">Sexo</label>
                            <div class="col-md-8">
                              <label><input type="radio" name="gender" id="radioM" id="M" disabled value="m" style="margin-top: 10px; margin-left: 15px;"> <i class="fa fa-male" style="font-size: 20px;"> </i></label>
                              <label><input type="radio" name="gender" id="radioF" disabled id="F" value="f" style="margin-top: 10px; margin-left: 15px;"> <i class="fa fa-female" style="font-size: 20px;"> </i> </label>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-md-3 control-label" for="profileCompany">Nascimento</label>
                            <div class="col-md-8">
                              <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="nascimento" disabled id="nascimento" max=<?php echo date('Y-m-d'); ?>>
                            </div>
                          </div>

                          <div class="form-group" id="exibirtipo" style="display:none;">
                            <label class="col-md-3 control-label" for="inputSuccess">Tipo sanguíneo</label>
                            <div class="col-md-6">
                              <input class="form-control input-lg mb-md" name="tipoSanguineo" disabled id="sangue">
                            </div>
                          </div>

                          <div class="col-md-12">
                            <table class="table table-bordered table-striped mb-none">
                              <thead>
                                <tr style="font-size:15px;">
                                  <th>Prontuário público</th>
                                </tr>
                              </thead>
                              <tbody id="prontuario_publico" style="font-size:15px">

                              </tbody>
                            </table>
                          </div>

                      </div>
                    </section>
                  </form>
                </div>


                <!-- aba de cadastrar de medicação e atendimento enfermeiro -->
                <div id="atendimento_enfermeiro" class="tab-pane">

                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-up" data-toggle="collapse"></a>
                      </div>
                      <h2 class="panel-title">Cadastrar Medicamento SOS</h2>
                    </header>

                    <div class="panel-body collapse">
                      <form id="form_medicacao_sos" class="form-horizontal form-bordered" onsubmit="event.preventDefault();">

                        <div class="form-group" id="primeira_medicacao">
                          <label class="col-md-3 control-label" for="nome_medicacao">
                            Medicamento:<sup class="obrig">*</sup>
                          </label>
                          <div class="col-md-6">
                            <input type="text" class="form-control meddisabled" name="nome_medicacao" id="nome_medicacao" required>
                          </div>
                        </div>

                        <div class="form-group">
                          <label class="col-md-3 control-label" for="dosagem_sos">
                            Dosagem:<sup class="obrig">*</sup>
                          </label>
                          <div class="col-md-6">
                            <input type="text" class="form-control" name="dosagem_sos" id="dosagem_sos" required>
                          </div>
                        </div>

                        <div class="form-group">
                          <label class="col-md-3 control-label" for="horario_medicacao_sos">
                            Horário:<sup class="obrig">*</sup>
                          </label>
                          <div class="col-md-6">
                            <input type="time" class="form-control" name="horario_medicacao_sos" id="horario_medicacao_sos" required>
                          </div>
                        </div>

                        <div class="form-group">
                          <label class="col-md-3 control-label" for="duracao_medicacao_sos">
                            Duração:<sup class="obrig">*</sup>
                          </label>
                          <div class="col-md-6">
                            <input type="text" class="form-control" name="duracao_medicacao_sos" id="duracao_medicacao_sos" required>
                          </div>
                        </div>

                        <br>
                        <br>
                        <button type="button" class="btn btn-success" id="botao_cadastrar_sos">
                          Cadastrar medicação
                        </button>
                      </form>
                    </div>
                  </section>

                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Aplicar medicamento</h2>
                    </header>

                    <div class="panel-body">
                      <hr class="dotted short">

                      <table class="table table-bordered table-striped mb-none" id="datatable-default">
                        <thead>
                          <tr>
                            <th class='txt-center' width='30%' id="med">Medicações</th>
                            <th class='txt-center' width='15%'>Dosagem</th>
                            <th class='txt-center' width='15%'>Horário</th>
                            <th class='txt-center' width='15%'>Duração</th>
                            <th class='txt-center'>Aplicar</th>
                          </tr>
                        </thead>
                        <tbody id="tabela">

                        </tbody>
                      </table>
                      
                      <div id="div-botao-aplicar-global" style="text-align: right; margin-top: 10px; margin-bottom: 20px;"></div>

                      <div class="modal fade" id="modalHorarioAplicacao" tabindex="-1" role="dialog" aria-labelledby="tituloModal">
                        <div class="modal-dialog">
                          <div class="modal-content">

                            <form onsubmit="enviarDataHoraAplicacaoMedicamento(event)">
                              <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title" id="tituloModal">Escolher Data e Hora</h4>
                              </div>

                              <div class="modal-body">
                                <input type="datetime-local" 
                                       id="dataHora" 
                                       name="dataHora" 
                                       onfocus="definirDataHoraAtualSeVazio(this)" 
                                       onclick="definirDataHoraAtualSeVazio(this)"
                                       oninput="usuarioAlterouDataHora()" 
                                       required 
                                       class="form-control">

                                <div id="msg_erro_modal" style="color: #d9534f; font-family: 'Open Sans', sans-serif; font-weight: bold; margin-top: 10px; display: none;"></div>

                                <input type="hidden" id="id_funcionario" name="id_funcionario">
                                <input type="hidden" id="id_medicacao" name="id_medicacao">
                                <input type="hidden" id="id_pessoa" name="id_pessoa">
                              </div>

                              <div class="modal-footer">
                                <button type="submit" class="btn btn-primary" id="enviar_medicação" formnovalidate>Enviar</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="limparInputDataTime()">Cancelar</button>
                              </div>
                            </form>

                          </div>
                        </div>
                      </div>
                      <br />

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
      <div class="modal fade" id="editimg" role="dialog">
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

    <script>
      function aplicarMedicacao() {
        if (!window.confirm("Tem certeza que deseja aplicar essa medicação?")) {
          return false;
        }
      }

      function variosMed() {
        localStorage.setItem("currentTab", "2");
        alert("Medicamento aplicado com sucesso!");
      }
      carregarMedicamentosParaAplicar(false);
      const id_fichamedica = document.getElementById("id_fichamedica").value;
      carregarAplicacoes(id_fichamedica);

  $(document).ready(function() {

    // Injetar botão "Selecionar Múltiplos"
    let htmlButton = `
        <div style="clear: both; text-align: right; margin-top: 50px; margin-bottom: 10px;">
            <button id="btnSelecionarMultiplos" title="Seleciona múltiplos medicamentos para aplicar de uma vez só" class="btn btn-primary">Selecionar Múltiplos</button>
        </div>
    `;

    $('#datatable-default_filter').after(htmlButton);

    $(document).on('click', '#btnSelecionarMultiplos', function() {
        $(this).hide();

        carregarMedicamentosParaAplicar(true);

        $('#div-botao-aplicar-global').remove();

        let divBotao = $('<div id="div-botao-aplicar-global" style="text-align: right; margin-top: 20px; display: flex; flex-direction: row; justify-content: flex-end; margin-bottom: 10px; clear: both;"></div>');

        let btnApply = $('<button class="btn btn-primary" style="margin-right: 5px;" data-toggle="modal" data-target="#modalHorarioAplicacao"><i class="glyphicon glyphicon-hand-up"></i> Aplicar Selecionados</button>');
        btnApply.click(function() {
            enviarInformacoesParaModal(this);
        });

        let btnCancel = $('<button class="btn btn-danger" style="height: 35px;" title="Cancelar Seleção"><i class="bi bi-x-lg"></i></button>');

        btnCancel.click(function() {
            $('#btnSelecionarMultiplos').show();
            divBotao.remove();
            carregarMedicamentosParaAplicar(false);
        });

        divBotao.append(btnApply);
        divBotao.append(btnCancel);

        // Insere depois da div responsiva da tabela (ficando antes do footer)
        $('.table-responsive').after(divBotao);
      });
    });

    </script>
    
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
    <script src="../geral/post.js"></script>
    <script src="../geral/formulario.js"></script>

    <div align="right">
      <iframe src="https://www.wegia.org/software/footer/saude.html" width="200" height="60" style="border:none;"></iframe>
    </div>
</body>

</html>