<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
  exit();
} else {
  session_regenerate_id();
}

require_once "../permissao/permissao.php";
permissao($_SESSION['id_pessoa'], 63, 7);

$id_pet = filter_input(INPUT_GET, 'id_pet', FILTER_SANITIZE_NUMBER_INT);

if (!$id_pet || $id_pet < 1) {
  http_response_code(400);
  echo json_encode('O id do pet informado não é válido.');
  exit();
}

if (!isset($_SESSION['pet'])) {
  header('Location: ../../controle/control.php?modulo=pet&metodo=listarUm&nomeClasse=PetControle&nextPage=' . WWW . '/html/pet/profile_pet.php?id_pet=' . htmlspecialchars($id_pet));
} else {
  $petDados = $_SESSION['pet'];
  unset($_SESSION['pet']);
  $pet = json_encode($petDados);
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';

require_once "../personalizacao_display.php";
require_once "../../dao/Conexao.php";
require_once "../geral/msg.php";

// Lógica para listar os adotantes

try {
  $pdo = Conexao::connect();

  $sqlListarAdotantes = "SELECT id_pessoa, nome, sobrenome FROM pessoa;";

  $stmt = $pdo->prepare($sqlListarAdotantes);
  $stmt->execute();

  $resultadosListarAdotantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Lógica para buscar ficha médica
  $fichaMedica = null;

  // Também vamos buscar dados da adoção do pet
  $adocaoPet = null;

  $stmtFicha = $pdo->prepare("SELECT id_ficha_medica, castrado, necessidades_especiais FROM pet_ficha_medica WHERE id_pet = :idPet");
  $stmtFicha->bindParam(':idPet', $id_pet, PDO::PARAM_INT);
  $stmtFicha->execute();

  $fichaMedica = $stmtFicha->fetch(PDO::FETCH_ASSOC);

  // BUSCA ADOÇÃO DO PET
  $stmtAdocao = $pdo->prepare("
          SELECT a.data_adocao, a.id_pessoa, p.nome
          FROM pet_adocao a 
          INNER JOIN pessoa p ON a.id_pessoa = p.id_pessoa 
          WHERE a.id_pet = :idPet
          LIMIT 1
      ");
  $stmtAdocao->bindParam(':idPet', $id_pet, PDO::PARAM_INT);
  $stmtAdocao->execute();

  $adocaoPet = $stmtAdocao->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  Util::tratarException($e);
  exit();
}
?>

<!-- Agora injetamos os dados da adoção para o JS -->
<script>
  const adocaoPet = <?php echo json_encode($adocaoPet ?? null); ?>;
</script>

<!doctype html>
<html class="fixed">

<head>
  <!-- Basic -->
  <meta charset="UTF-8">
  <title>Perfil Pet</title>
  <meta name="keywords" content="HTML5 Admin Template" />
  <meta name="description" content="Porto Admin - Responsive HTML5 Template">
  <meta name="author" content="okler.net">
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <!-- Web Fonts  -->
  <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
  <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">
  <!-- Vendor CSS -->
  <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
  <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
  <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
  <link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
  <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">
  <!-- Theme CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
  <!-- Skin CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
  <!-- Theme Custom CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
  <link rel="stylesheet" href="../../css/profile-theme.css" />
  <!-- Head Libs -->
  <script src="../../assets/vendor/modernizr/modernizr.js"></script>
  <script src="../../Functions/onlyNumbers.js"></script>
  <script src="../../Functions/onlyChars.js"></script>
  <script src="../../Functions/mascara.js"></script>
  <script src="../../Functions/lista.js"></script>
  <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
  <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
  <link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
  <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon" id="logo-icon">
  <!-- Specific Page Vendor CSS -->
  <link rel="st
      alert('oi');ylesheet" href="../../assets/vendor/select2/select2.css" />
  <link rel="stylesheet" href="../../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
  <!-- Theme CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
  <!-- Skin CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
  <!-- Theme Custom CSS -->
  <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
  <!-- Head Libs -->
  <script src="../../assets/vendor/modernizr/modernizr.js"></script>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
  <!-- Vendor -->
  <script src="../../assets/vendor/jquery/jquery.min.js"></script>
  <script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
  <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
  <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
  <script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
  <script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
  <script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
  <!-- printThis -->
  <script src="<?php echo WWW; ?>assets/vendor/jasonday-printThis-f73ca19/printThis.js"></script>
  <link rel="stylesheet" href="../../assets/print.css">
  <!-- jkeditor -->
  <script src="<?php echo WWW; ?>assets/vendor/ckeditor/ckeditor.js"></script>

  <style type="text/css">
      .panel-title a {
          color: inherit;      /* Mantém a cor do texto do painel */
          text-decoration: none; /* Remove sublinhado */
          display: block;       /* Faz o link ocupar toda a largura do header */
          cursor: pointer;      /* Mantém cursor de clique */
        }

        .panel-title a:hover {
          color: inherit;       /* Mantém a cor ao passar o mouse */
          text-decoration: none; /* Mantém sem sublinhado */
        }

    .btn span.fa-check {
      opacity: 0;
    }

    .btn.active span.fa-check {
      opacity: 1;
    }

    #frame {
      width: 100%;
    }

    .obrig {
      color: rgb(255, 0, 0);
    }

    .form-control {
      padding: 0 12px;
    }

    .btn i {
      color: white;
    }

    .select {
      position: absolute;
      width: 235px;
      /*print styles*/
    }

    #div_texto {
      width: 100%;
    }

    #cke_outras_informacoes {
      height: 500px;
    }

    .cke_inner {
      height: 500px;
    }

    #cke_1_contents {
      height: 455px !important;
    }

    #secFichaMedica {
      margin: 0 0 0 15px;
    }
  </style>
  <!-- jquery functions -->
  <script>
    let pet = <?= $pet ?>;
    let iNascimento;
    let iAcolhimento;

    //Informações Pet
    function editar_informacoes_pet() {
      $("#nomeForm").prop('disabled', false);
      $("#especificas").prop('disabled', false);
      $("#radioM").prop('disabled', false);
      $("#radioF").prop('disabled', false);
      $("#nascimento").prop('disabled', false);
      $("#acolhimento").prop('disabled', false);
      $("#cor").prop('disabled', false);
      $("#especie").prop('disabled', false);
      $("#raca").prop('disabled', false);
      $("#editarPet").html('Cancelar').removeClass('btn-secondary').addClass('btn-danger');
      $("#salvarPet").prop('disabled', false);
      $("#editarPet").removeAttr('onclick');
      $("#editarPet").attr('onclick', "return cancelar_informacoes_pet()");
    }

    function cancelar_informacoes_pet() {

      $.each(pet, function(i, item) {
        $("#nomeForm").val(item.nome).prop('disabled', true);
        $("#especificas").val(item.especificas).prop('disabled', true);
        if (item.sexo == "M") {
          $("#radioM").prop('checked', true).prop('disabled', true);
          $("#radioF").prop('checked', false).prop('disabled', true);
        } else if (item.sexo == "F") {
          $("#radioM").prop('checked', false).prop('disabled', true)
          $("#radioF").prop('checked', true).prop('disabled', true);
        }
        $("#nascimento").val(item.nascimento).prop('disabled', true);
        $("#acolhimento").val(item.acolhimento).prop('disabled', true);
        $("#cor").val(item.cor).prop('disabled', true);
        $("#especie").val(item.especie).prop('disabled', true);
        $("#raca").val(item.raca).prop('disabled', true);
        $("#editarPet").html('Editar').removeClass('btn-danger').addClass('btn-secondary');
        $("#salvarPet").prop('disabled', true);
        $("#editarPet").removeAttr('onclick');
        $("#editarPet").attr('onclick', "return editar_informacoes_pet()");
      })
    }

    $(function() {
      $.each(pet, function(i, item) {
        //Informações pet
        $("#nomeForm").val(item.nome).prop('disabled', true);
        if (item.sexo == "M") {
          $("#radioM").prop('checked', true).prop('disabled', true);
          $("#radioF").prop('checked', false).prop('disabled', true);
        } else if (item.sexo == "F") {
          $("#radioM").prop('checked', false).prop('disabled', true)
          $("#radioF").prop('checked', true).prop('disabled', true);
        }
        $("#nascimento").val(item.nascimento).prop('disabled', true);
        iNascimento = item.nascimento;
        $("#acolhimento").val(item.acolhimento).prop('disabled', true);
        iAcolhimento = item.acolhimento;
        $("#especie").val(item.especie).prop('disabled', true);
        $("#cor").val(item.cor).prop('disabled', true);
        $("#raca").val(item.raca).prop('disabled', true);
        $("#especificas").val(item.especificas).prop('disabled', true);

      })
    });

    // Ajuste de data
    function alterardate(data) {
      var date = data.split("-");
      alert(date);
      return data;
    }

    $(function() {
      $("#header").load("../header.php");
      $(".menuu").load("../menu.php");
    });

    const fichaMedica = {
      castrado: <?= json_encode(isset($fichaMedica['castrado']) ? htmlspecialchars($fichaMedica['castrado']) : '') ?>,
      texto: <?= json_encode(isset($fichaMedica['necessidades_especiais']) ? htmlspecialchars($fichaMedica['necessidades_especiais']) : '') ?>
    };

    function editar_ficha_medica() {
      // Habilita os campos
      document.getElementById('castradoS').disabled = false;
      document.getElementById('castradoN').disabled = false;
      document.getElementById('despacho').disabled = false;
      document.getElementById('salvarFichaMedica').disabled = false;

      const btnEditar = document.getElementById('editarFichaMedica');
      btnEditar.innerHTML = 'Cancelar';
      btnEditar.classList.remove('btn-secondary');
      btnEditar.classList.add('btn-danger');

      btnEditar.setAttribute('onclick', 'return cancelar_ficha_medica()');
    }

  function cancelar_ficha_medica() {
    // Restaura valores antigos
    if (fichaMedica.castrado === "s") {
      document.getElementById('castradoS').checked = true;
      document.getElementById('castradoN').checked = false;
    } else {
      document.getElementById('castradoS').checked = false;
      document.getElementById('castradoN').checked = true;
    }

      document.getElementById('despacho').value = fichaMedica.texto;

      // Desabilita os campos
      document.getElementById('castradoS').disabled = true;
      document.getElementById('castradoN').disabled = true;
      document.getElementById('despacho').disabled = true;
      document.getElementById('salvarFichaMedica').disabled = true;

      const btnEditar = document.getElementById('editarFichaMedica');
      btnEditar.innerHTML = 'Editar';
      btnEditar.classList.remove('btn-danger');
      btnEditar.classList.add('btn-secondary');

      btnEditar.setAttribute('onclick', 'return editar_ficha_medica()');
    }

    // Desabilita campos ao carregar
    document.addEventListener("DOMContentLoaded", function() {

      document.getElementById('castradoS').disabled = true;
      document.getElementById('castradoN').disabled = true;
      document.getElementById('despacho').disabled = true;
      document.getElementById('salvarFichaMedica').disabled = true;
    });

    document.addEventListener("DOMContentLoaded", () => {

      const adotadoS = document.querySelector("#adotadoS");
      const adotadoN = document.querySelector("#adotadoN");
      const dataAdocao = document.querySelector("#dataAdocao");
      const editarAdocao = document.querySelector("#editarAdocao");
      const salvarAdocao = document.querySelector("#submit_adocao");
      const adotante_input = document.querySelector("#adotante_input");

  const idPet = window.location.href.split("=")[1];

  // Desabilita campos inicialmente
  adotadoS.disabled = true;
  adotadoN.disabled = true;
  dataAdocao.disabled = true;
  salvarAdocao.disabled = true;
  adotante_input.disabled = true;

  let valoresOriginais = {};

  // Preencher dados da adoção
  fetch('../../controle/pet/ControleObterAdotante.php', {
    method: "POST",
    body: JSON.stringify({ id: idPet }),
    headers: { "Content-Type": "application/json" }
  })
  .then(resp => resp.json())
  .then(resp => {
    if (resp.adotado) {
      adotadoS.checked = true;
      dataAdocao.value = resp.data_adocao;
      adotante_input.value = resp.id_pessoa || "";
    } else {
      adotadoN.checked = true;
      dataAdocao.value = "";
      adotante_input.selectedIndex = 0;
    }

    valoresOriginais = {
      adotadoS: adotadoS.checked,
      adotadoN: adotadoN.checked,
      dataAdocao: dataAdocao.value,
      adotante_input: adotante_input.value
    };
  });

  // Inicializa o atributo data-editando
  editarAdocao.dataset.editando = "false";

  // Botão Editar/Cancela usando data-attribute
  editarAdocao.addEventListener("click", (e) => {
    e.preventDefault();

    const isEditando = editarAdocao.dataset.editando === "false";

        adotadoS.disabled = !isEditando;
        adotadoN.disabled = !isEditando;
        dataAdocao.disabled = !isEditando;
        salvarAdocao.disabled = !isEditando;
        adotante_input.disabled = !isEditando;

    editarAdocao.innerHTML = isEditando ? "Cancelar" : "Editar Adoção";
    editarAdocao.classList.toggle("btn-danger", isEditando);
    editarAdocao.classList.toggle("btn-primary", !isEditando);

    // Atualiza o data-attribute
    editarAdocao.dataset.editando = isEditando ? "true" : "false";

    if (!isEditando) {
      // Restaurar valores originais
      adotadoS.checked = valoresOriginais.adotadoS;
      adotadoN.checked = valoresOriginais.adotadoN;
      dataAdocao.value = valoresOriginais.dataAdocao;
      adotante_input.value = valoresOriginais.adotante_input;
    }
  });

  // Marcar "Não" para excluir adoção
  adotadoN.addEventListener("change", () => {
    dataAdocao.value = '';
    adotante_input.selectedIndex = 0;

    fetch('../../controle/pet/ControleObterAdotante.php', {
      method: 'POST',
      body: JSON.stringify({ comando: 'excluir', id_pet: idPet }),
      headers: { "Content-Type": "application/json" }
    })
    .then(resp => resp.json())
    .then(data => {
      if (data.status !== 'ok') {
        alert("Erro ao excluir adoção.");
      }
    });
  });
});


    //Atendimento pet

    //Exibir as opções dos medicamentos
    document.addEventListener("DOMContentLoaded", async () => {
      let opcoesMedicamento = document.querySelector("#selectMedicamento");
      let url = '../../controle/pet/controleGetMedicamento.php';

      const opcoes = {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json;charset=UTF-8'
        }
      };

      try {
        let resposta = await fetch(url, opcoes);
        let dados = await resposta.json();

        // Limpa as opções anteriores
        while (opcoesMedicamento.firstChild) {
          opcoesMedicamento.removeChild(opcoesMedicamento.firstChild);
        }

        // Cria a opção padrão "Selecionar"
        const opcaoNull = document.createElement('option');
        opcaoNull.text = "Selecionar";
        opcaoNull.disabled = true;
        opcaoNull.selected = true;
        opcoesMedicamento.appendChild(opcaoNull);

        // Adiciona os dados recebidos como opções
        dados.forEach(dado => {
          const opcao = document.createElement('option');
          opcao.textContent = dado.nome_medicamento;
          opcao.dataset.id = dado.id_medicamento;
          opcoesMedicamento.appendChild(opcao);
        });

      } catch (erro) {
        alert(erro);
      }
    });
    document.addEventListener("DOMContentLoaded", async () => {
      const select = document.querySelector("#selectMedicamento");
      const tabela = document.querySelector("#dep-tab"); // tbody

      // Array para armazenar os IDs dos medicamentos já inseridos
      const medicamentosAdicionados = [];

      select.addEventListener("change", async (e) => {
        const selectedOption = e.target.selectedOptions[0];
        const idMedicamento = selectedOption.dataset.id;

    // Se já foi adicionado, não faz nada
    if (medicamentosAdicionados.includes(idMedicamento)) {
      // Volta para a opção nula
      select.selectedIndex = 0;
      return;
    }

        const Medicamento = {
          id: idMedicamento,
          nomeClasse: document.querySelector("#classeAtendimento").value,
          metodo: "obterMedicamentoPet",
          modulo: document.querySelector("#moduloAtendimento").value
        };

        try {
          const resposta = await fetch("../../controle/control.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json"
            },
            body: JSON.stringify(Medicamento)
          });

          const medicamentoObtido = await resposta.json();

          // Cria a nova linha
          const linhaTabela = document.createElement("tr");

          // Coluna Medicamento (nome)
          const tdNomeMedicamento = document.createElement("td");
          tdNomeMedicamento.textContent = medicamentoObtido.nome_medicamento;

          // Coluna Ação (preserva seu conteúdo atual)
          const tdAcao = document.createElement("td");
          tdAcao.textContent = medicamentoObtido.aplicacao || "";

          // Coluna Excluir (botão)
          const tdExcluir = document.createElement("td");
          const btnExcluir = document.createElement("button");
          btnExcluir.textContent = "Excluir";
          btnExcluir.classList.add("btn", "btn-danger", "btn-sm");

          btnExcluir.addEventListener("click", () => {
            linhaTabela.remove();
            const index = medicamentosAdicionados.indexOf(idMedicamento);
            if (index > -1) medicamentosAdicionados.splice(index, 1);
          });

          tdExcluir.appendChild(btnExcluir);

          linhaTabela.append(tdNomeMedicamento, tdAcao, tdExcluir);
          tabela.appendChild(linhaTabela);

      // Registra o id no array
      medicamentosAdicionados.push(idMedicamento);

      // Volta para a option null
      select.selectedIndex = 0;

    } catch (erro) {
      alert("Erro ao obter medicamento. ");
    }
  });

      const formAtendimento = document.querySelector("#formAtendimento");
      formAtendimento.addEventListener("submit", async (e) => {
        e.preventDefault();

        let atendimento = {
          dataAtendimento: document.querySelector("#dataAtendimento").value,
          descricaoAtendimento: document.querySelector("#descricaoAtendimento").value,
          medicamentos: medicamentosAdicionados,
          idpet: document.querySelector("#idPet").value,
          nomeClasse: document.querySelector("#classeAtendimento").value,
          metodo: document.querySelector("#metodoAtendimento").value,
          modulo: document.querySelector("#moduloAtendimento").value
        };

        try {
          const resposta = await fetch("../../controle/control.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json"
            },
            body: JSON.stringify(atendimento)
          });

          const resultado = await resposta.json();

      if (resultado.sucesso) {
        location.reload();
      } else {
        alert(resultado.erro ?? "Erro ao registrar atendimento.");
      }
    } catch (erro) {
      alert("Erro no envio");
    }
  });
});



    document.addEventListener("DOMContentLoaded", async () => {

      document.querySelector("#doc").addEventListener("submit", async (e) => {
        e.preventDefault();

        let fichaMedica = {
          id_pet: document.querySelector("#idPet").value,
          necessidadesEspeciais: document.querySelector("#despacho").value,
          castrado: document.querySelector('input[name="castrado"]:checked')?.value || '',
          metodo: "modificarFichaMedicaPet",
          nomeClasse: "controleSaudePet",
          modulo: "pet"
        };

        const url = "../../controle/control.php";
        const opcoes = {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json;charset=UTF-8'
          },
          body: JSON.stringify(fichaMedica)
        }

        try {

          let resposta = await fetch(url, opcoes);
          let info = await resposta.json();

          if (info.status === "sucesso") {
            window.location.href = info.redirect;
          } else {
            alert("Erro ao salvar ficha médica!");
          }

            } catch(erro){
                alert(erro);
            }
        
    });
  });

document.addEventListener("DOMContentLoaded", async ()=>{

  const info = {
  metodo: "getHistoricoPet",
  modulo: "pet",
  nomeClasse: "controleSaudePet",
  idpet: document.querySelector("#idPet").value
};

    const container = document.querySelector("#divHistoricoAtendimento");

    // Função para decodificar HTML escapado
    function decodeHtml(html) {
      const txt = document.createElement("textarea");
      txt.innerHTML = html;
      return txt.value;
    }

    try {
      const resp = await fetch("../../controle/control.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
          },
        body: JSON.stringify(info)
      });

        const data = await resp.json();

      // Limpa conteúdo anterior
      container.textContent = "";

      // Verifica se não há dados
  if (!data || data.length === 0) {
        const msg = document.createElement("p");
        msg.textContent = "Nenhum histórico de atendimento encontrado.";
        container.appendChild(msg);
        return;
      }

  // Cria um painel para cada atendimento, mesmo que a data seja igual
  data.forEach((item, index) => {
    // Painel principal
    const panel = document.createElement("div");
    panel.classList.add("panel", "panel-default");
    panel.style.marginBottom = "10px";

    // Cabeçalho
    const header = document.createElement("div");
    header.classList.add("panel-heading");
    header.style.cursor = "pointer";
    header.dataset.toggle = "collapse";
    header.dataset.target = `#atendimento${index + 1}`;

        const headerText = document.createElement("strong");
        headerText.textContent = "Data: " + item.data_atendimento;
        header.appendChild(headerText);

        // Corpo colapsável
        const collapseDiv = document.createElement("div");
        collapseDiv.id = `atendimento${index + 1}`;
        collapseDiv.classList.add("panel-collapse", "collapse");

        const body = document.createElement("div");
        body.classList.add("panel-body");

          // Descrição do atendimento
          const pDescTitle = document.createElement("p");
          const strongDesc = document.createElement("strong");
          strongDesc.textContent = "Descrição do Atendimento:";
          pDescTitle.appendChild(strongDesc);
          body.appendChild(pDescTitle);

        const pDesc = document.createElement("p");
        pDesc.textContent = item.descricao_atendimento || "Não informado.";
        body.appendChild(pDesc);

        body.appendChild(document.createElement("hr"));

        // Lista de medicações
        const pMedTitle = document.createElement("p");
        const strongMed = document.createElement("strong");
        strongMed.textContent = "Medicações Utilizadas:";
        pMedTitle.appendChild(strongMed);
        body.appendChild(pMedTitle);

    const lista = document.createElement("ul");

    // Caso haja medicação
    if (item.nome_medicamento || item.aplicacao || item.descricao_medicamento) {
      const li = document.createElement("li");

      const nome = document.createElement("div");
      nome.innerHTML = `<strong>Nome:</strong> ${item.nome_medicamento || "Medicamento"}`;
      li.appendChild(nome);

      const aplicacao = document.createElement("div");
      aplicacao.innerHTML = `<strong>Aplicação:</strong> ${item.aplicacao || "Não informada"}`;
      li.appendChild(aplicacao);

      const descricao = document.createElement("div");
      descricao.innerHTML = `<strong>Descrição:</strong> ${decodeHtml(item.descricao_medicamento || "")}`;
      li.appendChild(descricao);

      lista.appendChild(li);
    } else {
      const li = document.createElement("li");
      li.textContent = "Nenhuma medicação registrada.";
      lista.appendChild(li);
    }

    body.appendChild(lista);
    collapseDiv.appendChild(body);
    panel.appendChild(header);
    panel.appendChild(collapseDiv);
    container.appendChild(panel);
  });

} catch (erro) {
  
  container.textContent = "Erro ao carregar histórico de atendimento.";
}

})

document.addEventListener("DOMContentLoaded", async () => {

const info2 = {
  metodo: "getHistoricoVacinacao",
  modulo: "pet",
  nomeClasse: "controleSaudePet",
  idpet: document.querySelector("#idPet").value
};

const containerVacinacao = document.querySelector("#divHistoricoVacinacao");

// Função para decodificar HTML escapado
function decodeHtml(html) {
  const txt = document.createElement("textarea");
  txt.innerHTML = html;
  return txt.value;
}

try {
  const resp2 = await fetch("../../controle/control.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(info2)
  });

  const data2 = await resp2.json();

  // Limpa conteúdo anterior
  containerVacinacao.textContent = "";

  if (!data2 || data2.length === 0) {
    const msg = document.createElement("p");
    msg.textContent = "Nenhum histórico de vacinação encontrado.";
    containerVacinacao.appendChild(msg);
    return;
  }

  let count = 0;

  data2.forEach(item => {
    count++;

    // Painel principal
    const panel = document.createElement("div");
    panel.classList.add("panel", "panel-default");
    panel.style.marginBottom = "10px";

    // Cabeçalho
    const header = document.createElement("div");
    header.classList.add("panel-heading");

    const h4 = document.createElement("h4");
    h4.classList.add("panel-title");

    const link = document.createElement("a");
    link.setAttribute("data-toggle", "collapse");
    link.setAttribute("href", `#vacinacao${count}`);
    link.textContent = `Vacina: ${item.nome || "Não informada"}`;

    h4.appendChild(link);
    header.appendChild(h4);

    // Corpo colapsável (fechado por padrão)
    const collapseDiv = document.createElement("div");
    collapseDiv.id = `vacinacao${count}`;
    collapseDiv.classList.add("panel-collapse", "collapse");

    const body = document.createElement("div");
    body.classList.add("panel-body");

    // Conteúdo do corpo
    const pMarca = document.createElement("p");
    pMarca.innerHTML = `<strong>Marca:</strong> ${item.marca || "Não informada"}`;
    body.appendChild(pMarca);

    const pData = document.createElement("p");
    pData.innerHTML = `<strong>Data da Vacinação:</strong> ${item.data_vacinacao || "Não informada"}`;
    body.appendChild(pData);

    collapseDiv.appendChild(body);
    panel.appendChild(header);
    panel.appendChild(collapseDiv);
    containerVacinacao.appendChild(panel);
  });

} catch (erro) {
  alert("Erro ao buscar histórico de vacinação:", erro);
  containerVacinacao.textContent = "Erro ao carregar histórico de vacinação.";
}
})

document.addEventListener("DOMContentLoaded", async () => {
  


// Dados para requisição
const info3 = {
  metodo: "getHistoricoVermifugacao",
  modulo: "pet",
  nomeClasse: "controleSaudePet",
  idpet: document.querySelector("#idPet").value
};

const containerVermifugacao = document.querySelector("#divHistoricoVermifugacao");

// Função para decodificar HTML escapado, caso necessário
function decodeHtml(html) {
  const txt = document.createElement("textarea");
  txt.innerHTML = html;
  return txt.value;
}

try {
  const resp3 = await fetch("../../controle/control.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(info3)
  });

  const data3 = await resp3.json();

  // Limpa conteúdo anterior
  containerVermifugacao.textContent = "";

  if (!data3 || data3.length === 0) {
    const msg = document.createElement("p");
    msg.textContent = "Nenhum histórico de vermifugação encontrado.";
    containerVermifugacao.appendChild(msg);
    return;
  }

  let count = 0;

  data3.forEach(item => {
    count++;

    // Painel principal
    const panel = document.createElement("div");
    panel.classList.add("panel", "panel-default");
    panel.style.marginBottom = "10px";

    // Cabeçalho
    const header = document.createElement("div");
    header.classList.add("panel-heading");

    const h4 = document.createElement("h4");
    h4.classList.add("panel-title");

    const link = document.createElement("a");
    link.setAttribute("data-toggle", "collapse");
    link.setAttribute("href", `#vermifugacao${count}`);
    link.textContent = `Vermífugo: ${item.nome || "Não informado"}`;

    h4.appendChild(link);
    header.appendChild(h4);

    // Corpo colapsável (fechado por padrão)
    const collapseDiv = document.createElement("div");
    collapseDiv.id = `vermifugacao${count}`;
    collapseDiv.classList.add("panel-collapse", "collapse");

    const body = document.createElement("div");
    body.classList.add("panel-body");

    // Conteúdo do corpo
    const pMarca = document.createElement("p");
    pMarca.innerHTML = `<strong>Marca:</strong> ${item.marca || "Não informada"}`;
    body.appendChild(pMarca);

    const pData = document.createElement("p");
    pData.innerHTML = `<strong>Data da Vermifugação:</strong> ${item.data_vermifugacao || "Não informada"}`;
    body.appendChild(pData);

    collapseDiv.appendChild(body);
    panel.appendChild(header);
    panel.appendChild(collapseDiv);
    containerVermifugacao.appendChild(panel);
  });

} catch (erro) {
  alert("Erro ao buscar histórico de vermifugação");
  containerVermifugacao.textContent = "Erro ao carregar histórico de vermifugação.";
}
});



//ADICIONAR TIPO DE EXAME


// Função que faz o fetch e já popula o select
async function fetchAndPopulateTiposExame() {
    const select = document.getElementById('tipoExame');
    select.innerHTML = '<option value="" selected disabled>Selecionar Tipo</option>';

    try {
        let resposta = await fetch('../../controle/control.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                modulo: "pet",
                nomeClasse: "controleSaudePet",
                metodo: "listarTipoExame"
            })
        });

        if (!resposta.ok) throw new Error("Erro na requisição");

        let dados = await resposta.json();

        dados.forEach(item => {
            const option = document.createElement("option");
            option.value = item.id_tipo_exame;
            option.textContent = item.descricao_exame;
            select.appendChild(option);
        });

    } catch (erro) {
        console.error("Erro ao buscar tipos de exame:", erro);
        alert("Não foi possível carregar os tipos de exame.");
    }
}

// Função que escuta o evento do modal
function initModalListener() {
    $('#docFormModal').on('shown.bs.modal', function () {
        fetchAndPopulateTiposExame();
    });
}

// inicializa o listener quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', initModalListener);



window.addTipoExame = async function () {
  try {
    let tipoExame = prompt("Cadastre um novo tipo de exame:");

    if (tipoExame === null) return; // Usuário cancelou
    tipoExame = tipoExame.trim();
    if (tipoExame === '') return; // Entrada vazia

    const url = '../../controle/control.php';

    const data = {
      metodo: "cadastroTipoExame",
      modulo: "pet",
      nomeClasse: "controleSaudePet",
      descricaoExame: tipoExame
    };

    

    const response = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    let resultado;
    try {
      resultado = await response.json();
    } catch (jsonError) {
      throw new Error('Resposta do servidor não é JSON válido');
    }

    if (!response.ok || resultado.status === 'erro') {
      throw new Error(resultado.mensagem || 'Erro ao adicionar tipo de exame');
    }


    // 🔥 Depois de cadastrar, atualiza o select
    await fetchAndPopulateTiposExame();

  } catch (error) {
    alert('Erro ao adicionar tipo de exame: ' + error.message);
  }
}

function excluirArquivo(dado){
        let trId = document.querySelector("#tr"+dado);
        let arkivo = document.querySelector("#ark"+dado).innerHTML;
        let response = window.confirm('Deseja realmente excluir o arquivo "' + arkivo + '"?');
        
        if(response === true){
            fetch('../../controle/pet/PetExameControle.php', {
            method: 'POST',
            body: JSON.stringify({"idExamePet":dado, "metodo":"excluir"}),
            headers: {
              "Content-Type": "application/json"
            }
          }).then(
            (resp) =>{ return resp.json() }
          ).then(
            (resp) =>{
              alert(resp);
              trId.remove();
            }
          )
        }
      } 

    </script>
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
          <h2>Perfil</h2>
          <div class="right-wrapper pull-right">
            <ol class="breadcrumbs">
              <li>
                <a href="../home.php">
                  <i class="fa-solid fa-home"></i>
                </a>
              </li>
              <li><span>Páginas</span></li>
              <li><span>Perfil</span></li>
            </ol>
            <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
          </div>
        </header>
        <!-- start: page -->
        <!-- Mensagem -->
        <?php getMsgSession("msg", "tipo"); ?>
        <!----pedro-->
        <div class="container">
          <div class="modal fade" id="myModal" role="dialog">
            <div class="modal-dialog">
              <!-- Modal content-->
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <h4 class="modal-title">Adicionar uma Foto</h4>
                </div>
                <div class="modal-body">
                  <form class="form-horizontal" method="POST" action="../../controle/control.php" enctype="multipart/form-data">
                    <input type="hidden" name="nomeClasse" value="PetControle">
                    <?= Csrf::inputField() ?>
                    <input type="hidden" name="metodo" value="alterarImagem">
                    <input type="hidden" name="modulo" value="pet">
                    <div class="form-group">
                      <label class="col-md-4 control-label" for="imgperfil">Carregue nova imagem de perfil:</label>
                      <div class="col-md-8">
                        <input type="file" name="imgperfil" size="60" id="imgform" class="form-control">
                      </div>
                    </div>
                </div>
                <div class="modal-footer">
                  <input type="hidden" name="id_pet" value=<?php echo htmlspecialchars($id_pet) ?>>
                  <input type="hidden" name="id_foto" id="id_foto">
                  <input type="submit" id="formsubmit" value="Alterar imagem">
                </div>
              </div>
              </form>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4 col-lg-3">
            <section class="panel">
              <div class="panel-body">
                <div class="thumb-info mb-md">

                  <?php
                  echo "<img  id='imagem' class='rounded img-responsive' alt='John Doe'>";

                  $id_pessoa = $_SESSION['id_pessoa'];
                  $donoimagem = htmlspecialchars($id_pet);

                  $conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                  if (isset($_SESSION['id_pessoa']) and !empty($_SESSION['id_pessoa'])) {
                    echo "
                        <script>
                          let id_foto = document.querySelector('#id_foto');
                          let img = document.querySelector('#imagem');
                          fetch('./foto.php',{
                            method: 'POST',
                            body: JSON.stringify({'id':" . $donoimagem . "})
                          }).then((resp)=>{
                            return resp.json()
                          }).then((resp)=>{
                            let petImagem = resp;
                            let foto;
                            
                            if(petImagem){
                              foto = petImagem['imagem'];
                              id_foto.value = petImagem['id_foto'];
                              if(foto != null && foto != ''){
                                foto = 'data:image;base64,'+foto;
                              }
                            }else{
                              foto = '../../img/semfoto.png';
                            }
                            img.src = foto;
                          });
                        </script>
                      ";
                      }
                      ?>
                    <i class="fas fa-camera-retro btn btn-info btn-lg" data-toggle="modal" data-target="#myModal"></i>
                    
                  </div>
                  <div class="widget-toggle-expand mb-md">
                    <div class="widget-header">
                      <div class="widget-content-expanded">
                        <ul class="simple-todo-list"></ul>
                      </div>
                    </div>
                  </div>
                </div>
              </section>
            </div>
            <div class="col-md-8 col-lg-8">
              <div class="tabs">
                <ul class="nav nav-tabs tabs-primary">
                  <li class="active">
                    <a href="#overview" data-toggle="tab">Informações do Pet</a>
                  </li>
                  <li>
                    <a href="#ficha_medica" data-toggle="tab">Ficha Médica</a>
                  </li>
                  
                  <li>
                    <a href="#atendimento" data-toggle="tab">Atendimento</a>
                  </li>
                  
                  <li>
                    <a href="#historico_medico" data-toggle="tab">Histórico Médico</a>
                  </li>
                  <li>
                    <a href="#arquivosPet" data-toggle="tab">Exames do Pet</a>
                  </li>
                    
                <li>
                  <a href="#adocao" data-toggle="tab">Adoção</a>
                </li>
              </ul>

              <div class="tab-content">
                <!--Aba de Informações Pet-->
                <div id="overview" class="tab-pane active">
                  <form class="form-horizontal" method="post" action="../../controle/control.php">
                    <div class="myModal print">
                      <input type="hidden" name="nomeClasse" value="PetControle">
                      <?= Csrf::inputField() ?>
                      <input type="hidden" name="metodo" value="alterarPetDados">
                      <input type="hidden" name="modulo" value="pet">

                      <h4 class="mb-xlg">Informações Pet</h4>
                      <fieldset>
                        <div class="form-group">
                          <label class="col-md-3 control-label" for="nome">Nome</label>
                          <div class="col-md-8">
                            <input type="text" class="form-control" name="nome" id="nomeForm" onkeypress="return Onlychars(event)" required>
                          </div>
                        </div>

                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileLastName">Sexo</label>
                          <div class="col-md-8">
                            <label><input type="radio" name="gender" id="radioM" id="M" value="M" style="margin-top: 10px; margin-left: 15px;"> <i class="fa fa-mars" style="font-size: 20px;"></i></label>
                            <label><input type="radio" name="gender" id="radioF" id="F" value="F" style="margin-top: 10px; margin-left: 15px;"> <i class="fa fa-venus" style="font-size: 20px;"></i></label>
                          </div>
                        </div>

                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileCompany">Nascimento</label>
                          <div class="col-md-8">
                            <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="nascimento" id="nascimento" max=<?php echo date('Y-m-d'); ?> required>
                          </div>
                        </div>

                        <div class="form-group">
                          <label class="col-md-3 control-label" for="profileCompany">Acolhimento</label>
                          <div class="col-md-8">
                            <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="acolhimento" id="acolhimento" max=<?php echo date('Y-m-d'); ?> required>
                          </div>
                        </div>

                        <div class="form-group">
                          <label class="col-md-3 control-label" for="inputSuccess">Cor</label>
                          <div class="col-md-6">
                            <select class="form-control input-lg mb-md" name="cor" id="cor">
                              <?php
                              $cor = mysqli_query($conexao, "SELECT id_pet_cor AS id_cor, descricao AS 'cor' FROM pet_cor");
                              foreach ($cor as $valor) {
                                echo "<option value=" . htmlspecialchars($valor['id_cor']) . " >" . htmlspecialchars($valor['cor']) . "</option>";
                              }
                              ?>
                            </select>
                          </div>
                        </div>

                        <div class="form-group">
                          <label class="col-md-3 control-label" for="inputSuccess">Espécie</label>
                          <div class="col-md-6">
                            <select class="form-control input-lg mb-md" name="especie" id="especie">
                              <?php
                              $especie = mysqli_query($conexao, "SELECT id_pet_especie AS id_especie, descricao AS 'especie' FROM pet_especie");
                              foreach ($especie as $valor) {
                                echo "<option value=" . htmlspecialchars($valor['id_especie']) . " >" . htmlspecialchars($valor['especie']) . "</option>";
                              }
                              ?>
                            </select>
                          </div>
                        </div>

                        <div class="form-group">
                          <label class="col-md-3 control-label" for="inputSuccess">Raça</label>
                          <div class="col-md-6">
                            <select class="form-control input-lg mb-md" name="raca" id="raca">
                              <?php
                              $raca = mysqli_query($conexao, "SELECT id_pet_raca AS id_raca, descricao AS 'raca' FROM pet_raca");
                              foreach ($raca as $valor) {
                                echo "<option value=" . htmlspecialchars($valor['id_raca']) . " >" . htmlspecialchars($valor['raca']) . "</option>";
                              }
                              ?>
                            </select>
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label" for="especificas">Características Específicas</label>
                          <div class="col-md-8">
                            <textarea name="especificas" class="form-control" id="especificas"></textarea>
                          </div>
                        </div>
                    </div>
                    </br>
                    <input type="hidden" name="id_pet" value=<?php echo htmlspecialchars($id_pet) ?>>
                    <button type="button" class="not-printable btn btn-primary" id="editarPet" onclick="return editar_informacoes_pet()">Editar</button>
                    <input type="submit" class="not-printable btn btn-primary" disabled="true" value="Salvar" id="salvarPet">
                    </fieldset>

                  </form>
                </div>
                <!--Arquivos-->
                <div id="arquivosPet" class="tab-pane">
                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Arquivos do Pet</h2>
                    </header>
                    <div class="panel-body">
                      <table class="table table-bordered table-striped mb-none">
                        <thead>
                          <tr>
                            <th>Arquivo</th>
                            <th>Data</th>
                            <th>Ação</th>
                          </tr>
                        </thead>
                        <tbody id="doc-tab">
                          <?php
                          $stmtExames = $pdo->prepare("SELECT *, pte.descricao_exame AS 'arkivo' FROM pet_exame pe JOIN pet_tipo_exame pte ON pe.id_tipo_exame = pte.id_tipo_exame JOIN pet_ficha_medica pfm ON pe.id_ficha_medica =pfm.id_ficha_medica  WHERE pfm.id_pet=:idPet");
                          $stmtExames->bindParam('idPet', $id_pet, PDO::PARAM_INT);
                          $stmtExames->execute();

                          $exame = $stmtExames->fetchAll();
                          if ($exame) {
                            foreach ($exame as $valor) {
                              $data = explode('-', $valor['data_exame']);
                              $data = $data[2] . '-' . $data[1] . '-' . $data[0];
                              $arkivo = $valor['arkivo'];
                              echo <<<HTML
                                    <tr id="tr$valor[id_exame]">
                                      <td><p id="ark$valor[id_exame]">$arkivo</p></td>
                                      <td>$data</td>
                                      <td style="display: flex; justify-content: space-evenly;">
                                        <a href="data:$valor[arquivo_extensao];base64,$valor[arquivo_exame]" title="Baixar" download="$valor[descricao_exame].$valor[arquivo_extensao]">
                                          <button class="btn btn-primary">
                                            <i class="fas fa-download"></i>
                                          </button>
                                        </a>
                                        <a onclick="excluirArquivo($valor[id_exame])" href="#" title="Excluir">
                                          <button class="btn btn-danger">
                                            <i class="fas fa-trash-alt"></i>
                                          </button>
                                        </a>
                                      </td>
                                    </tr>
                                  HTML;
                            }
                          }
                          ?>
                        </tbody>
                      </table><br>
                      <!-- Button trigger modal -->

                      <?php
                      if ($p != false) {
                        echo <<<HTML
                              <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#docFormModal">
                                Adicionar
                              </button>
                            HTML;
                      } else {
                        echo <<<HTML
                              <p>É necessário que o animal possua uma ficha médica para poder registrar os exames!</p>
                              <a href="./cadastro_ficha_medica_pet.php?id_pet=$_GET[id_pet]"><input class ="btn btn-primary" 
                              type="button" value='Cadastrar Ficha médica'></a>
                            HTML;
                      }
                      ?>

                        <!-- Modal Form Documentos -->
                        <div class="modal fade" id="docFormModal" tabindex="-1" role="dialog" aria-labelledby="docFormModalLabel" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header" style="display: flex;justify-content: space-between;">
                                <h5 class="modal-title" id="exampleModalLabel">Adicionar Arquivo</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                                <form id="formDocPet" action='../../controle/control.php' method='post' enctype='multipart/form-data'>
                                <div class="modal-body" style="padding: 15px 40px">
                                  <div class="form-group" style="display: grid;">
                                    <label class="my-1 mr-2" for="tipoExame">Tipo de Arquivo</label><br>
                                    <div style="display: flex;">
                                      <select name="id_tipo_exame" class="custom-select my-1 mr-sm-2" id="tipoExame" required>
                                        <option value="" selected disabled>Selecionar Tipo</option>
                                       
                                      </select>
                                      <a onclick="addTipoExame()" style="margin: 0 20px;" id="btn_adicionar_tipo_remuneracao">
                                        <i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i>
                                      </a>
                                    </div>
                                  </div>

                                  <div class="form-group">
                                    <label for="arquivoDocumento">Arquivo</label>
                                    <input name="arquivo" type="file" class="form-control-file" id="arquivoDocumento" accept="png,jpeg,jpg,pdf,docx,doc,odp" required>
                                  </div>

                                  <input type="hidden" name="modulo" value="pet">
                                  <input type="hidden" name="nomeClasse" value="PetControle">
                                  <input type="hidden" name="metodo" value="incluirExamePet">
                                  <input type="hidden" name="id_ficha_medica" value="<?= $id_ficha_medica ?>">
                                  <input type="hidden" name="id_pet" value="<?= $_GET['id_pet'] ?>">
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                  <input type="submit" value="Enviar" class="btn btn-primary">
                                </div>
                              </form>

                              <script>
                              document.getElementById('formDocPet').addEventListener('submit', function(e) {
                                // Verificar tipo de exame selecionado
                                const tipoExame = document.getElementById('tipoExame').value;
                                if (!tipoExame) {
                                  alert('Por favor, selecione um tipo de arquivo.');
                                  e.preventDefault();
                                  return;
                                }

                                // Verificar extensão do arquivo
                                const arquivo = document.getElementById('arquivoDocumento').files[0];
                                if (arquivo) {
                                  const extensoesPermitidas = ['png','jpeg','jpg','pdf','docx','doc','odp'];
                                  const extensao = arquivo.name.split('.').pop().toLowerCase();
                                  if (!extensoesPermitidas.includes(extensao)) {
                                    alert('Extensão de arquivo não permitida. Use: ' + extensoesPermitidas.join(', '));
                                    e.preventDefault();
                                    return;
                                  }
                                } else {
                                  alert('Por favor, selecione um arquivo.');
                                  e.preventDefault();
                                }
                              });
                              </script>

                            </div>
                          </div>
                        </div>
                      </div>
                  </section>                  
                </div>

                <!-- Ficha Medica-->

                <div id="ficha_medica" class="tab-pane">
                  <section id="secFichaMedica">
                    <h4 class="mb-xlg" id="fm">Ficha Médica</h4>
                    <div id="divFichaMedica">
                      <form class="form-horizontal" id="doc">
                        <input type="hidden" name="nomeClasse" value="controleSaudePet">
                        <input type="hidden" name="metodo" value="modificarFichaMedicaPet">
                        <input type="hidden" name="modulo" value="pet">
                        <fieldset>

                          <!--Castrado-->
                          <div class="form-group">
                        <label class="col-md-3 control-label" for="profileLastName">Animal Castrado:</label>
                        <div class="col-md-8">
                            <label>
                                <input type="radio" name="castrado" id="castradoS" value="s" style="margin-top: 10px; margin-left: 15px;"
                                <?php if (isset($fichaMedica['castrado']) && $fichaMedica['castrado'] === 's') echo 'checked'; ?> required>
                                <i class="fa" style="font-size: 20px;">Sim</i>
                            </label>
                            <label>
                                <input type="radio" name="castrado" id="castradoN" value="n" style="margin-top: 10px; margin-left: 15px;"
                                <?php if (!isset($fichaMedica['castrado']) || $fichaMedica['castrado'] === 'n') echo 'checked'; ?> required>
                                <i class="fa" style="font-size: 20px;">Não</i>
                              </label>
                            </div>
                          </div>

                          <!-- Necessidades Especiais (campo de texto) -->
                          <div class="form-group">
                            <div class="form-group">
                              <label for="texto" id="etiqueta_despacho" class="col-md-3 control-label">Outras informações:</label>
                              <div class="col-md-8">
                                <textarea name="texto" class="form-control col-md-8" id="despacho"><?php echo isset($fichaMedica['necessidades_especiais']) ? htmlspecialchars($fichaMedica['necessidades_especiais']) : ''; ?></textarea>
                              </div>
                            </div>
                          </div>

                          </br>
                          <div class="buttons">
                            <input type="hidden" name="id_pet" value="<?php echo $id_pet; ?>">
                            <input type="hidden" name="id_ficha_medica" id="id_ficha_medica" value="<?php echo isset($fichaMedica['id_ficha_medica']) ? htmlspecialchars($fichaMedica['id_ficha_medica']) : ''; ?>">
                            <button type="button" id="editarFichaMedica" class="not-printable btn btn-primary" onclick="return editar_ficha_medica()">Editar</button>
                            <input type="submit" class="d-print-none btn btn-primary" value="Salvar" id="salvarFichaMedica">
                          </div>
                        </fieldset>
                      </form>
                    </div>
                  </section>
                </div>

                <!--atendimento-->
                <div id="atendimento" class="tab-pane">
                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Atendimento</h2>
                    </header>
                    <div id="divAtendimento" class="panel-body">

                      <form class="form-horizontal" id="formAtendimento">
                        <input type="hidden" name="nomeClasse" value="AtendimentoControle" id="classeAtendimento">
                        <input type="hidden" name="metodo" value="registrarAtendimento" id="metodoAtendimento">
                        <input type="hidden" name="modulo" value="pet" id="moduloAtendimento">
                        <input type="hidden" name="id_pet" value=<?php echo htmlspecialchars($id_pet) ?> id="idPet">
                        <fieldset>

                          <div class="form-group">
                            <label class="col-md-3 control-label" for="profileCompany">Data do Atendimento<sup class="obrig">*</sup></label>
                            <div class="col-md-8">
                              <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="dataAtendimento" id="dataAtendimento" max=<?php echo date('Y-m-d'); ?> required>
                            </div>
                          </div>

                          <div class="form-group">

                            <label class="col-md-3 control-label" for="descricaoAtendimento">Descricao Atendimento<sup class="obrig">*</sup></label>
                            <div class="col-md-8">
                              <textarea name="descricaoAtendimento" class="form-control" id="descricaoAtendimento" required></textarea>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-md-3 control-label" for="inputSuccess">Medicamento</label>
                            <div class="col-md-6 d-flex align-items-center" style="display: flex; gap: 8px;">

                              <select class="form-control input-lg mb-md" name="selectMedicamento" id="selectMedicamento" style="flex: 1;">
                              </select>

                              <!-- Botão para cadastrar medicamento -->
                              <a href="cadastrar_medicamento.php" title="Cadastrar Novo Medicamento" style="padding: 0 12px;">
                                <i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i>
                              </a>

                            </div>

                            <table class="table table-bordered table-striped mb-none" id="tabmed">
                              <thead>
                                <tr style="font-size:15px;">
                                  <th>Medicação</th>
                                  <th>Ação</th>
                                  <th>Excluir</th> <!-- Coluna nova para o botão -->
                                </tr>
                              </thead>
                              <tbody id="dep-tab" style="font-size:15px">
                                <!-- Linhas dinâmicas -->
                              </tbody>
                            </table>

                            </br>
                            <div class="form-group">
                            </div>

                            <input type="submit" class="btn btn-primary" value="Salvar Atendimento" id="salvarAtendimento">
                        </fieldset>
                      </form>
                    </div>
                  </section>
                  <div id="historicoAtendimento" class="tab-pane">
                    <section class="panel">
                      <header class="panel-heading">
                        <div class="panel-actions">
                          <a href="#" class="fa fa-caret-down"></a>
                        </div>
                        <h2 class="panel-title">Histórico de Atendimento</h2>
                      </header>
                      <div id="divHistoricoAtendimento" class="panel-body">

                      </div>
                    </section>
                  </div>

                </div>

                <!-- fim atendimento -->

                <!-- Historico medico -->
     <div id="historico_medico" class="tab-pane">
  <section class="panel">
    <header class="panel-heading">
      <div class="panel-actions">
        <a href="#" class="fa fa-caret-down"></a>
      </div>
      <h2 class="panel-title">Histórico Médico</h2>
    </header>

    <div class="panel-body">
      <hr class="dotted short">

      <div class="panel-group" id="accordionHistorico">

        <!-- Histórico de Atendimento -->
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordionHistorico" href="#collapseAtendimento">
                Histórico de Atendimento
              </a>
            </h4>
          </div>
          <div id="collapseAtendimento" class="panel-collapse collapse">
            <div class="panel-body" id="divHistoricoAtendimento">
              <!-- Conteúdo do histórico de atendimento será carregado aqui -->
            </div>
          </div>
        </div>

        <!-- Histórico de Vacinação -->
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordionHistorico" href="#collapseVacinacao">
                Histórico de Vacinação
              </a>
            </h4>
          </div>
          <div id="collapseVacinacao" class="panel-collapse collapse">
            <div class="panel-body" id="divHistoricoVacinacao">
              <!-- Conteúdo do histórico de vacinação será carregado aqui -->
            </div>
          </div>
        </div>

        <!-- Histórico de Vermifugação -->
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordionHistorico" href="#collapseVermifugacao">
                Histórico de Vermifugação
              </a>
            </h4>
          </div>
          <div id="collapseVermifugacao" class="panel-collapse collapse">
            <div class="panel-body" id="divHistoricoVermifugacao">
              <!-- Conteúdo do histórico de vermifugação será carregado aqui -->
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>
</div>


                <!-- fim historico medico -->

                <!-- Adoção -->
                <div id="adocao" class="tab-pane">
                  <section class="panel">
                    <header class="panel-heading">
                      <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                      </div>
                      <h2 class="panel-title">Adoção do Pet</h2>
                    </header>
                    <div class="panel-body">
                      <form class="form-horizontal" id="form_adocao" method="post" action="../../controle/control.php">
                        <input type="hidden" name="nomeClasse" value="AdocaoControle">
                        <input type="hidden" name="metodo" value="modificarAdocao">
                        <input type="hidden" name="modulo" value="pet">
                        <fieldset>
                          <div class="form-group">
                            <label class="col-md-3 control-label" for="profileLastName">Adotado</label>
                            <div class="col-md-8">
                              <label><input type="radio" name="adotado" id="adotadoS" value="S" style="margin-top: 10px; margin-left: 15px;"> <i class="fa" style="font-size: 20px;">Sim</i></label>
                              <label><input type="radio" name="adotado" id="adotadoN" value="N" style="margin-top: 10px; margin-left: 15px;"> <i class="fa" style="font-size: 20px;">Não</i></label>
                            </div>
                          </div>
                          <div id="dadosAdocao">
                            <div class="form-group">
                              <label class="col-md-3 control-label" for="profileName">Nome do Adotante</label>
                              <div class="col-md-8">
                                <select class="form-control input-lg mb-md" name="adotante_input" id="adotante_input">
                                  <option selected disabled value="">Selecionar</option>
                                  <?php
                                  // Lista todos os adotantes
                                  foreach ($resultadosListarAdotantes as $resultado) {
                                    if ($resultado["id_pessoa"] != 1) {
                                      echo "<option value='" . htmlspecialchars($resultado["id_pessoa"]) . "'>" . htmlspecialchars($resultado["nome"]) . " " . htmlspecialchars($resultado["sobrenome"]) . "</option>";
                                    }
                                  }

                                  if (count($resultadosListarAdotantes) == 0 || !array_filter($resultadosListarAdotantes, function ($adotante) {
                                    return $adotante["id_pessoa"] != 1;
                                  })) {
                                    echo "<option value=''>Adotantes não encontrados.</option>";
                                  }
                                  ?>
                                </select>
                              </div>
                            </div>

                            <!-- DATA ADOÇÃO -->
                            <div class="form-group">
                              <label class="col-md-3 control-label" for="profileCompany">Data da adoção</label>
                              <div class="col-md-8">
                                <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="dataAdocao" id="dataAdocao" max="<?php echo date('Y-m-d'); ?>">
                              </div>
                            </div>

                          </div>
                          </br>

                          <input type="hidden" name="id_pet" value="<?php echo htmlspecialchars($idPet); ?>">
                          <button type="button" class="btn btn-primary" id="editarAdocao" >Editar</button>
                          <input type="submit" class="btn btn-primary" id="submit_adocao" name="submit_adocao" value = "Salvar">

                        </fieldset>
                      </form>
                    </div>
                  </section>
                </div>
                <!-- fim adocao-->

              </div>
            </div>
          </div>
      </section>
    </div>
  </section>

  <script type="text/javascript">
    //Arquivo
    function excluirArquivo(dado) {
      let trId = document.querySelector("#tr" + dado);
      let arkivo = document.querySelector("#ark" + dado).innerHTML;
      let response = window.confirm('Deseja realmente excluir o arquivo "' + arkivo + '"?');

      if (response === true) {
        fetch('../../controle/pet/PetExameControle.php', {
          method: 'POST',
          body: JSON.stringify({
            "idExamePet": dado,
            "metodo": "excluir"
          }),
          headers: {
            "Content-Type": "application/json"
          }
        }).then(
          (resp) => {
            return resp.json()
          }
        ).then(
          (resp) => {
            alert(resp);
            trId.remove();
          }
        )
      }
    }

    window.addTipoExame = async function() {
      try {
        let tipoExame = prompt("Cadastre um novo tipo de exame:");

        if (tipoExame === null) return; // Usuário cancelou

        tipoExame = tipoExame.trim();

        if (tipoExame === '') return; // Entrada vazia

        const url = '../../dao/pet/adicionar_tipo_exame.php';
        const data = new URLSearchParams({
          tipo_exame: tipoExame
        });

        const response = await fetch(url, {
          method: 'POST',
          body: data,
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          }
        });

        if (!response.ok) {
          throw new Error('Erro na resposta da rede');
        }

        const responseText = await response.text();
        gerarTipoExamePet(responseText);

      } catch (error) {
        console.error('Erro ao adicionar tipo de exame:', error);
      }
    }

    function gerarTipoExamePet(response) {
      const tipoExame = JSON.parse(response);
      const $tipoExame = $('#tipoExame');

      $tipoExame.empty();
      $tipoExame.append('<option selected disabled>Selecionar...</option>');

      $.each(tipoExame, function(i, item) {
        $tipoExame.append('<option value="' + item.id_tipo_exame + '">' + item.descricao_exame + '</option>');
      });


      //Funções que fazem a impressão
      $(function() {
        $("#btnPrint").click(function() {
          $(".print").printThis();
        });
        $("#btnPrint2").click(function() {
          $(".tab-content").printThis({
            loadCSS: "../../assets/stylesheets/print.css"
          });
        });
      });

      //fichaMedica==================================================
      let castradoS = document.querySelector("#castradoS");
      let vacinadoS = document.querySelector("#vacinadoS");
      let vermifugadoS = document.querySelector("#vermifugadoS");
      let informacoes = document.querySelector("#despacho");
      let salvarFichaMedica = document.querySelector("#salvarFichaMedica");
      let editarFichaMedica = document.querySelector("#editarFichaMedica");
      let dVacinado = document.querySelector("#dVacinado");
      let dVermifugado = document.querySelector("#dVermifugado");
      let divVermifugado = document.querySelector("#div_vermifugado");
      let divVacinado = document.querySelector("#div_vacinado");
      let id_ficha_medica = document.querySelector("#id_ficha_medica");

      //let editor = CKEDITOR.replace('despacho');

      let dadoId = window.location + '';
      dadoId = dadoId.split('=');
      let id = dadoId[1];
      let dado = {
        'id': id,
        'metodo': 'getFichaMedicaPet'
      };

      fetch("../../controle/pet/controleGetPet.php", {
        method: "POST",
        body: JSON.stringify(dado),
        headers: {
          "Content-Type": "application/json"
        }
      }).then(resp => {
        return resp.json()
      }).then(resp => {
        if (resp[0].castrado == 's' || resp[0].castrado == 'S') {
          castradoS.checked = true;
        }

        id_ficha_medica.value = resp[0].id_ficha_medica;

        if (resp[0].necessidades_especiais) {
          let infoPet = resp[0].necessidades_especiais;
          infoPet = infoPet.replace("<p>", '');
          infoPet = infoPet.replace("</p>", '');

          informacoes.value = infoPet;
        }

        if (resp[1].id_vacinacao) {
          vacinadoS.checked = true;
          dVacinado.value = resp[1].data_vacinacao;
        } else {
          divVacinado.innerHTML = '';
        }

        if (resp[2].id_vermifugacao) {
          vermifugadoS.checked = true;
          dVermifugado.value = resp[2].data_vermifugacao;
        } else {
          divVermifugado.innerHTML = '';
        }
      });

      vacinadoS.addEventListener('click', () => {
        divVacinado.innerHTML = `<label class="col-md-3 control-label" for="dVacinado">Data de Vacinação:<sup class="obrig">*</sup></label>
                                 <div class="col-md-8">
                                   <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="dVacinado" id="dVacinado" max=<?php echo date('Y-m-d'); ?> required>
                                 </div>`;
      })

      vacinadoN.addEventListener('click', () => {
        divVacinado.innerHTML = '';
      })

      vermifugadoS.addEventListener('click', () => {
        divVermifugado.innerHTML = `<label class="col-md-3 control-label" for="dataVermifugado">Data de Vermifugação:<sup class="obrig">*</sup></label>
                                    <div class="col-md-8">
                                      <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="dVermifugado" id="dVermifugado" max=<?php echo date('Y-m-d'); ?> required>
                                    </div>`;
      })

      vermifugadoN.addEventListener('click', () => {
        divVermifugado.innerHTML = ``;
      })

      vacinadoS.disabled = true;
      vermifugadoS.disabled = true;
      castradoS.disabled = true;
      vacinadoN.disabled = true;
      vermifugadoN.disabled = true;
      castradoN.disabled = true;
      salvarFichaMedica.disabled = true;
      dVermifugado.disabled = true;
      dVacinado.disabled = true;
      informacoes.disabled = true;

      //verificar se possui ficha medica=============================
      dado = {
        'id_pet': id,
        'metodo': 'fichaMedicaPetExiste'
      };

      fetch('../../controle/pet/controleGetPet.php', {
        method: 'POST',
        body: JSON.stringify(dado),
        headers: {
          "Content-Type": "application/json"
        }
      }).then(
        resp => {
          return resp.json();
        }
      ).then(
        resp => {
          if (resp.total != 1) {
            corpo = `
            <p>É necessário que o animal possua uma ficha médica para poder usar esta aba!</p>
            <a href="./cadastro_ficha_medica_pet.php?id_pet=${id}">
              <input class="btn btn-primary" type="button" value="Cadastrar Ficha médica">
            </a>
            `;
            document.querySelector("#divFichaMedica").innerHTML = corpo;
            document.querySelector("#divAtendimento").innerHTML = corpo;
            document.querySelector("#divMedicamento").innerHTML = corpo;
          }
        }
      )

      //Atualizar Ficha Medica
      vacinadoS.addEventListener('click', () => {
        divVacinado.style.display = '';
      })
      vermifugadoS.addEventListener('click', () => {
        divVermifugado.style.display = '';
      })

      vacinadoN.addEventListener('click', () => {
        divVacinado.style.display = 'none';
      })
      vermifugadoN.addEventListener('click', () => {
        divVermifugado.style.display = 'none';
      })

      editarFichaMedica.addEventListener('click', () => {
        if (editarFichaMedica.innerHTML != "Cancelar") {

          $(editarFichaMedica).html('Cancelar').removeClass('btn-secondary').addClass('btn-danger');
          vacinadoS.disabled = false;
          vermifugadoS.disabled = false;
          castradoS.disabled = false;
          vacinadoN.disabled = false;
          vermifugadoN.disabled = false;
          castradoN.disabled = false;
          salvarFichaMedica.disabled = false;
          dVacinado.disabled = false;
          dVermifugado.disabled = false;
          informacoes.disabled = false;
        } else {
          location.reload();
        }
      })
    }

    //Fim Atendimento
    //historico_medico
    let tabHist = document.querySelector("#tab_historico");
    let tabAtendimento = document.querySelector("#tab_historico");
    let id_pet = window.location + '';
    id_pet = id_pet.split("=");

    fetch("../../controle/pet/ControleHistorico.php", {
      method: 'POST',
      body: JSON.stringify({
        'metodo': "getHistoricoPet",
        'id_pet': id_pet[1]
      })
    }).then(
      resp => {
        return resp.json();
      }
    ).then(
      resp => {
        let atendimento = resp;
        atendimento.forEach(valor => {
          let data = valor['data_atendimento'].split('-');
          tabAtendimento.innerHTML += `
              <tr>
                <td>${data[2]}-${data[1]}-${data[0]}</td>
                <td>${valor['descricao']}</td>
                <td style="display: flex; justify-content: space-evenly;">
                  <a href="./historico_pet.php?id_historico=${valor['id_pet_atendimento']}" title="vizualizar">
                    <button class="btn btn-primary" id="teste">
                      <i class="fa fa-arrow-up-right-from-square"></i>
                    </button>
                  </a>
                </td>
              </tr>
            `;
        })

        let td = document.querySelectorAll("td");
        let th = document.querySelectorAll("th");
        td.forEach(al => {
          al.style.textAlign = "center";
        })
        th.forEach(ah => {
          ah.style.textAlign = "center";
        })
      }
    )
    //fim historico_medico

    //=============================================================
  </script>
  <!-- Vendor -->
  <script src="../../assets/vendor/select2/select2.js"></script>
  <script src="../../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
  <script src="../../assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
  <script src="../../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
  <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
  <!-- Theme Base, Components and Settings -->
  <script src="../../assets/javascripts/theme.js"></script>
  <!-- Theme Custom -->
  <script src="../../assets/javascripts/theme.custom.js"></script>
  <!-- Metodo Post -->
  <script src="../geral/post.js"></script>
  <!-- Theme Initialization Files -->
  <script src="../../assets/javascripts/theme.init.js"></script>
  <!-- Examples -->
  <script src="../../assets/javascripts/tables/examples.datatables.default.js"></script>
  <script src="../../assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
  <script src="../../assets/javascripts/tables/examples.datatables.tabletools.js"></script>

  <!-- JavaScript Custom -->
  <script src="../geral/post.js"></script>
  <script src="../geral/formulario.js"></script>
  <script>
    var formState = [];

    function switchButton(idForm) {
      if (!formState[idForm]) {
        $("#botaoEditar_" + idForm).text("Editar").prop("class", "btn btn-primary");
      } else {
        $("#botaoEditar_" + idForm).text("Cancelar").prop("class", "btn btn-danger");
      }
    }

    function switchForm(idForm, setState = null) {
      if (setState !== null) {
        formState[idForm] = !setState;
      }
      if (formState[idForm]) {
        formState[idForm] = false;
        disableForm(idForm);
      } else {
        formState[idForm] = true;
        enableForm(idForm);
      }
      switchButton(idForm);
    }
  </script>
  <div align="right">
    <iframe src="https://www.wegia.org/software/footer/pet.html" width="200" height="60" style="border:none;"></iframe>
  </div>
</body>

</html>