<?php

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

session_start();

if(!isset($_SESSION['usuario'])){
    header ("Location: ../index.php");
    exit;
}
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once "../permissao/permissao.php";
require_once "../../dao/Conexao.php";
require_once dirname(__FILE__, 3) . '/html/permissao/permissao.php';

try {
    $id_pessoa = $_SESSION['id_pessoa'];
    // Exemplo: recurso 5 = Saúde Pet, ação 7 = LER, GRAVAR E EXECUTAR
    permissao($id_pessoa, 5, 7); 
} catch (Exception $e) {
    error_log("Erro de banco de dados: " . $e->getMessage());
    header("Location: ../error.php?msg=Erro ao acessar o banco de dados");
    exit;
}


require_once ROOT."/controle/SaudeControle.php";
require_once ROOT."/html/personalizacao_display.php";

?>

<!DOCTYPE html>
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
    <script src="<?php echo WWW;?>assets/vendor/jasonday-printThis-f73ca19/printThis.js"></script>
    <link rel="stylesheet" href="../../assets/stylesheets/print.css">
    <!-- jkeditor -->
    <script src="<?php echo WWW;?>assets/vendor/ckeditor/ckeditor.js"></script>
    <!-- jquery functions -->

    <script>

        $(function() {

        $("#header").load("../header.php");
        $(".menuu").load("../menu.php");
        });

   
document.addEventListener("DOMContentLoaded", async () => {
    async function listarVacina(){
    let vacina = document.querySelector("#vacina");
    let url = '../../controle/control.php?modulo=pet&nomeClasse=controleSaudePet&metodo=listarVacina';

    try {
        let resposta = await fetch(url);
        let info = await resposta.json();

        while(vacina.firstChild) vacina.removeChild(vacina.firstChild);

        const opcaoNull = document.createElement('option');
        opcaoNull.text = "Selecionar";
        opcaoNull.disabled = true;
        opcaoNull.selected = true;
        vacina.appendChild(opcaoNull);

        info.forEach(dado => {
            const opcao = document.createElement('option');
            opcao.dataset.id = dado.id_vacina;
            opcao.text = dado.nome;
            vacina.appendChild(opcao);
        });

    } catch (erro) {
        alert("Erro ao carregar vacinas: " + erro);
    }

}

   
    listarVacina();

    let nomePet = document.querySelector("#nome");
    let url = '../../controle/control.php?modulo=pet&nomeClasse=PetControle&metodo=listarPets';

    try {
        let resposta = await fetch(url);
        let info = await resposta.json();

        while(nomePet.firstChild) nomePet.removeChild(nomePet.firstChild);

        const opcaoNull = document.createElement('option');
        opcaoNull.text = "Selecionar";
        opcaoNull.disabled = true;
        opcaoNull.selected = true;
        nomePet.appendChild(opcaoNull);

        info.forEach(dado => {
            const opcao = document.createElement('option');
            opcao.dataset.id = dado.id_pet;
            opcao.text = dado.nome;
            nomePet.appendChild(opcao);
        });

    } catch (erro) {
        alert("Erro ao carregar pets: " + erro);
    }
    let idFichaMedica = "";
    // Quando mudar o pet
    nomePet.addEventListener("change", async (e) => {
    idPet = e.target.selectedOptions[0].dataset.id;
    let urlFicha = `../../controle/control.php?modulo=pet&nomeClasse=controleSaudePet&metodo=getFichaMedicaPet&idPet=${idPet}`;

    try {
        let resposta = await fetch(urlFicha);
        dados = await resposta.json();
    } catch (erro) {
        alert("Erro ao carregar pets: " + erro);
    }

    // Seleciona o elemento pai
    const finalizarForm = document.querySelector("#finalizarForm");
    // Limpa todos os filhos
    finalizarForm.innerHTML = "";

    if(dados){
        idFichaMedica = dados['id_ficha_medica'];


        // Reconstrói apenas o botão Enviar
        const rowDiv = document.createElement("div");
        rowDiv.className = "row";

        const colDiv = document.createElement("div");
        colDiv.className = "col-md-9 col-md-offset-3 d-flex justify-content-end gap-2";

        // input hidden de idFichaMedica
        const hiddenInput = document.createElement("input");
        hiddenInput.type = "hidden";
        hiddenInput.name = "idFichaMedica";
        hiddenInput.id = "idFichaMedica";
        hiddenInput.value = idFichaMedica;
    
        // botão enviar
        const enviarBtn = document.createElement("input");
        enviarBtn.type = "submit";
        enviarBtn.className = "btn btn-primary";
        enviarBtn.id = "enviar";
        enviarBtn.value = "Salvar";

        colDiv.appendChild(hiddenInput);
        colDiv.appendChild(enviarBtn);
        rowDiv.appendChild(colDiv);
        finalizarForm.appendChild(rowDiv);

        const vacina = document.querySelector("#vacina");
        vacina.disabled = false;
        const dataVacinacao = document.querySelector("#dataDeVacinacao")
        dataVacinacao.disabled = false;
        const btnModal = document.querySelector("#btnModal")
        btnModal.setAttribute("data-toggle", "modal");


    } else {
        // Caso não exista ficha médica, adiciona a mensagem e botão de cadastrar
        const vacina = document.querySelector("#vacina");
        vacina.disabled = true;
        const dataVacinacao = document.querySelector("#dataDeVacinacao")
        dataVacinacao.disabled = true;
        const btnModal = document.querySelector("#btnModal")
        btnModal.setAttribute("data-toggle", "#");

        const rowDiv = document.createElement("div");
        rowDiv.className = "row";

        const colDiv = document.createElement("div");
        colDiv.className = "col-md-9 col-md-offset-3 d-flex flex-column gap-2";

        const mensagem = document.createElement("p");
        mensagem.textContent = "É necessário que o animal possua uma ficha médica para registrar a vacinação ou a vermifugação";

        const link = document.createElement("a");
        link.href = "./cadastro_ficha_medica_pet.php";

        const botao = document.createElement("input");
        botao.type = "button";
        botao.value = "Cadastrar Ficha médica";
        botao.className = "btn btn-primary";

        link.appendChild(botao);
        colDiv.appendChild(mensagem);
        colDiv.appendChild(link);
        rowDiv.appendChild(colDiv);
        finalizarForm.appendChild(rowDiv);
    }
});



    // Submit do formulário

  

    const formVacina = document.querySelector("#cadastroVacina");
    formVacina.addEventListener("submit", async (e) => {
    e.preventDefault();

    let vacinacao = {
        nomeVacina: document.querySelector("#nomeVacina").value,
        marcaVacina: document.querySelector("#marcaVacina").value,
        metodo: "cadastroVacina",
        nomeClasse: "controleSaudePet",
        modulo: "pet"
    };


    let idFicha = document.querySelector("#idFichaMedica").value;


        const url = "../../controle/control.php";
        const opcoes = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=UTF-8'
            },
            body: JSON.stringify(vacinacao)
        };

        try {
            let resposta = await fetch(url, opcoes);

            // Verifica se o status HTTP é 200
            if (!resposta.ok) {
                throw new Error("Erro na requisição: " + resposta.status);
            }

            let info = await resposta.json();
            

            // Se o backend retornar {status: "sucesso"}, recarrega a página
            if (info.status && info.status.toLowerCase() === "sucesso") {
               $('#docFormModal').modal('hide');
               listarVacina();

            } else {
                alert(info.mensagem || "Ocorreu um problema ao salvar.");
            }

        } catch (erro) {
            alert("Erro ao cadastrar Vacina: " + erro);
        }
    
});

const formVacinaPet = document.querySelector("#doc");

formVacinaPet.addEventListener("submit", async (e) => {
    e.preventDefault();

    // Monta os dados que serão enviados
    const vacinacaoPet = {
        idFichaMedica: document.querySelector("#idFichaMedica").value,
        idVacina: document.querySelector("#vacina").selectedOptions[0]?.dataset.id || '',
        dataVacinacao: document.querySelector("#dataDeVacinacao").value,
        metodo: "cadastroVacinacao",
        nomeClasse: "controleSaudePet",
        modulo: "pet"
    };

    const url = "../../controle/control.php";
    const opcoes = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json;charset=UTF-8'
        },
        body: JSON.stringify(vacinacaoPet)
    };

    try {
        const resposta = await fetch(url, opcoes);

        if (!resposta.ok) {
            throw new Error("Erro na requisição: " + resposta.status);
        }

        const info = await resposta.json();

        if (info.status && info.status.toLowerCase() === "sucesso") {
            window.location.reload();
        } else {
            alert(info.mensagem || "Ocorreu um problema ao salvar a vacinação.");
        }

    } catch (erro) {
        alert("Erro ao registrar vacinação: " + erro);
    }
});

})


























document.addEventListener("DOMContentLoaded", async () => {

    async function listarVermifugo() {
        let vermifugo = document.querySelector("#vermifugo");
        let url = '../../controle/control.php?modulo=pet&nomeClasse=controleSaudePet&metodo=listarVermifugo';

        try {
            let resposta = await fetch(url);
            let info = await resposta.json();

            while (vermifugo.firstChild) vermifugo.removeChild(vermifugo.firstChild);

            const opcaoNull = document.createElement('option');
            opcaoNull.text = "Selecionar";
            opcaoNull.disabled = true;
            opcaoNull.selected = true;
            vermifugo.appendChild(opcaoNull);

           
            info.forEach(dado => {
                const opcao = document.createElement('option');
                opcao.dataset.id = dado.id_vermifugo;
                opcao.text = dado.nome;
                vermifugo.appendChild(opcao);
            });

        } catch (erro) {
            alert("Erro ao carregar vermífugos: " + erro);
        }
    }

    listarVermifugo();

    let nomePet = document.querySelector("#nomePet2");
    let urlPets = '../../controle/control.php?modulo=pet&nomeClasse=PetControle&metodo=listarPets';

    try {
        let resposta = await fetch(urlPets);
        let info = await resposta.json();

        while (nomePet.firstChild) nomePet.removeChild(nomePet.firstChild);

        const opcaoNull = document.createElement('option');
        opcaoNull.text = "Selecionar";
        opcaoNull.disabled = true;
        opcaoNull.selected = true;
        nomePet.appendChild(opcaoNull);

        info.forEach(dado => {
            const opcao = document.createElement('option');
            opcao.dataset.id = dado.id_pet;
            opcao.text = dado.nome;
            nomePet.appendChild(opcao);
        });

    } catch (erro) {
        alert("Erro ao carregar pets: " + erro);
    }

    let idFichaMedica = "";

    nomePet.addEventListener("change", async (e) => {
        let idPet = e.target.selectedOptions[0].dataset.id;
        let urlFicha = `../../controle/control.php?modulo=pet&nomeClasse=controleSaudePet&metodo=getFichaMedicaPet&idPet=${idPet}`;

        try {
            let resposta = await fetch(urlFicha);
            dados = await resposta.json();
        } catch (erro) {
            alert("Erro ao carregar ficha médica: " + erro);
        }
        document.querySelector("#idFichaMedicaVermifugo").value = dados['id_ficha_medica'];
        
        const finalizarForm = document.querySelector("#finalizarFormVermifugo");
        finalizarForm.innerHTML = "";
       
        if (dados) {
            idFichaMedica = dados['id_ficha_medica'];

            const rowDiv = document.createElement("div");
            rowDiv.className = "row";

            const colDiv = document.createElement("div");
            colDiv.className = "col-md-9 col-md-offset-3 d-flex justify-content-end gap-2";

            const hiddenInput = document.createElement("input");
            hiddenInput.type = "hidden";
            hiddenInput.name = "idFichaMedica";
            hiddenInput.id = "idFichaMedicaVermifugo";
            hiddenInput.value = idFichaMedica;
      

            const enviarBtn = document.createElement("input");
            enviarBtn.type = "submit";
            enviarBtn.className = "btn btn-primary";
            enviarBtn.id = "enviar";
            enviarBtn.value = "Salvar";

            colDiv.appendChild(hiddenInput);
            colDiv.appendChild(enviarBtn);
            rowDiv.appendChild(colDiv);
            finalizarForm.appendChild(rowDiv);

            const vermifugo = document.querySelector("#vermifugo");
            vermifugo.disabled = false;

            const dataVermifugacao = document.querySelector("#dataDeVermifugacao");
            dataVermifugacao.disabled = false;

            const btnModal = document.querySelector("#btnModalVermifugo");
            btnModal.setAttribute("data-toggle", "modal");

        } else {
            const vermifugo = document.querySelector("#vermifugo");
            vermifugo.disabled = true;

            const dataVermifugacao = document.querySelector("#dataDeVermifugacao");
            dataVermifugacao.disabled = true;

            const btnModal = document.querySelector("#btnModalVermifugo");
            btnModal.setAttribute("data-toggle", "#");

            const rowDiv = document.createElement("div");
            rowDiv.className = "row";

            const colDiv = document.createElement("div");
            colDiv.className = "col-md-9 col-md-offset-3 d-flex flex-column gap-2";

            const mensagem = document.createElement("p");
            mensagem.textContent = "É necessário que o animal possua uma ficha médica para registrar a vacinação ou a vermifugação";

            const link = document.createElement("a");
            link.href = "./cadastro_ficha_medica_pet.php";

            const botao = document.createElement("input");
            botao.type = "button";
            botao.value = "Cadastrar Ficha médica";
            botao.className = "btn btn-primary";

            link.appendChild(botao);
            colDiv.appendChild(mensagem);
            colDiv.appendChild(link);
            rowDiv.appendChild(colDiv);
            finalizarForm.appendChild(rowDiv);
        }
    });

    const formVermifugo = document.querySelector("#cadastroVermifugo");
    formVermifugo.addEventListener("submit", async (e) => {
    e.preventDefault();

    let vermifugacao = {
        nomeVermifugo: document.querySelector("#nomeVermifugo").value,
        marcaVermifugo: document.querySelector("#marcaVermifugo").value,
        metodo: "cadastroVermifugo",
        nomeClasse: "controleSaudePet",
        modulo: "pet"
    };
 

    //let idFicha = document.querySelector("#idFichaMedica").value;

    const url = "../../controle/control.php";
    const opcoes = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json;charset=UTF-8'
        },
        body: JSON.stringify(vermifugacao)
    };

    try {
        let resposta = await fetch(url, opcoes);

        if (!resposta.ok) {
            throw new Error("Erro na requisição: " + resposta.status);
        }

        let info = await resposta.json();

        if (info.status && info.status.toLowerCase() === "sucesso") {
            $('#docFormModalVermifugo').modal('hide');
            listarVermifugo(); // Função para atualizar a lista de vermífugos
        } else {
            alert(info.mensagem || "Ocorreu um problema ao salvar.");
        }

    } catch (erro) {
        alert("Erro ao cadastrar Vermífugo: " + erro);
    }
});


    const formVermifugacaoPet = document.querySelector("#docVermifugacao");

    formVermifugacaoPet.addEventListener("submit", async (e) => {
        e.preventDefault();
        
        const vermifugacaoPet = {
            idFichaMedica: document.querySelector("#idFichaMedicaVermifugo").value,
            idVermifugo: document.querySelector("#vermifugo").selectedOptions[0]?.dataset.id || '',
            dataVermifugacao: document.querySelector("#dataDeVermifugacao").value,
            metodo: "cadastroVermifugacao",
            nomeClasse: "controleSaudePet",
            modulo: "pet"
        };
        
      
        const url = "../../controle/control.php";
        const opcoes = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=UTF-8'
            },
            body: JSON.stringify(vermifugacaoPet)
        };

        try {
            const resposta = await fetch(url, opcoes);
            if (!resposta.ok) throw new Error("Erro na requisição: " + resposta.status);

            const info = await resposta.json();
            if (info.status && info.status.toLowerCase() === "sucesso") {
                window.location.reload();
            } else {
                alert(info.mensagem || "Ocorreu um problema ao salvar a vermifugação.");
            }

        } catch (erro) {
            alert("Erro ao registrar vermifugação: " + erro);
        }
    });

});





    </script>    
    
    

    <style type="text/css">
       
        .select{
            position: absolute;
            width: 235px;
        }
        .select-table-filter{
            width: 140px;
            float: left;
        }
        .panel-body{
            margin-bottom: 15px;
        }
        img{
        	margin-left:10px;
        }
        #div_texto
        {
            width: 100%;
        }
        #cke_despacho
        {
            height: 500px;
        }
        .cke_inner
        {
            height: 500px;
        }
        #cke_1_contents
        {
            height: 455px !important;
        }
        .col-md-3 {
            width: 10%;
        }
        .panel-footer .col-md-9 {
            display: flex;
            justify-content: flex-end; /* alinha à direita */
            gap: 10px; /* espaço entre os botões */
            }

    </style>
</head>
<body>
<section class="body">
    <div id="header"></div>
    <div class="inner-wrapper">
        <aside id="sidebar-left" class="sidebar-left menuu"></aside>
        <section role="main" class="content-body">
            <header class="page-header">
                <h2>Cadastro Vacinação e Vermifugação</h2>
                <div class="right-wrapper pull-right">
                    <ol class="breadcrumbs">
                        <li><a href="../home.php"><i class="fa fa-home"></i></a></li>
                        <li><span>Pet</span></li>
                        <li><span>Cadastro Vacinação e Vermifugação</span></li>
                    </ol>
                    <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
                </div>
            </header>

            <div class="row">
                <div class="col-md-12">
                    <div class="tabs">
                        <!-- Nav Tabs -->
                        <ul class="nav nav-tabs tabs-primary">
                            <li class="active"><a href="#tabVacinacao" data-toggle="tab">Vacinação</a></li>
                            <li><a href="#tabVermifugacao" data-toggle="tab">Vermifugação</a></li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content">
                            <!-- Vacinação -->
                            <div id="tabVacinacao" class="tab-pane active">
                                <form class="form-horizontal" id="doc">
                                    <section class="panel">
                                        <header class="panel-heading">
                                            <div class="panel-actions">
                                                <a href="#" class="fa fa-caret-down"></a>
                                            </div>
                                            <h2 class="panel-title">Informações do Pet - Vacinação</h2>
                                        </header>
                                        <div class="panel-body">
                                            <h5 class="obrig">Campos Obrigatórios(*)</h5>
                                            <br>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" style="padding-left:29px;">Pet atendido:<sup class="obrig">*</sup></label>
                                                <div class="col-md-6">
                                                    <select class="form-control input-lg mb-md" name="nome" id="nome" required></select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" style="padding-left:29px;">Vacina <sup class="obrig">*</sup></label>
                                                <div class="col-md-6" style="display: flex; align-items: center; gap: 5px;">
                                                    <select class="form-control input-lg mb-md" name="vacina" id="vacina" style="flex:1;" disabled required></select>
                                                    <a href="#" data-target="#docFormModal" title="Adicionar Vacina" id="btnModal" style="padding:0 12px; display:flex; align-items:center;">
                                                        <i class="fas fa-plus w3-xlarge"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="dataDeVacinacao">Data de Vacinação<sup class="obrig">*</sup></label>
                                                <div class="col-md-8">
                                                    <input type="date" class="form-control" name="dataDeVacinacao" id="dataDeVacinacao" max=<?php echo date('Y-m-d'); ?> disabled required>
                                                </div>
                                            </div>
                                            <br>
                                        </div>
                                        <div class="panel-footer" id="finalizarForm">
                                            <div class="row">
                                                <div class="col-md-9 col-md-offset-3 d-flex justify-content-end gap-2">
                                                    <input type="hidden" name="idFichaMedica" id="idFichaMedicaVacina" value="">
                                                    <input id="enviar" type="submit" class="btn btn-primary" value="Salvar" disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                </form>
                            </div>

                            <!-- Vermifugação -->
                            <div id="tabVermifugacao" class="tab-pane">
                                <form class="form-horizontal" id="docVermifugacao">
                                    <section class="panel">
                                        <header class="panel-heading">
                                            <div class="panel-actions">
                                                <a href="#" class="fa fa-caret-down"></a>
                                            </div>
                                            <h2 class="panel-title">Informações do Pet - Vermifugação</h2>
                                        </header>
                                        <div class="panel-body">
                                            <h5 class="obrig">Campos Obrigatórios(*)</h5>
                                            <br>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" style="padding-left:29px;">Pet atendido:<sup class="obrig">*</sup></label>
                                                <div class="col-md-6">
                                                    <select class="form-control input-lg mb-md" name="nome" id="nomePet2" required></select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" style="padding-left:29px;">Vermífugo <sup class="obrig">*</sup></label>
                                                <div class="col-md-6" style="display: flex; align-items: center; gap: 5px;">
                                                    <select class="form-control input-lg mb-md" name="vermifugo" id="vermifugo" style="flex:1;" disabled required></select>
                                                    <a href="#" data-target="#docFormModalVermifugo" title="Adicionar Vermífugo" id="btnModalVermifugo" style="padding:0 12px; display:flex; align-items:center;">
                                                        <i class="fas fa-plus w3-xlarge"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="dataDeVermifugacao">Data de Vermifugação<sup class="obrig">*</sup></label>
                                                <div class="col-md-8">
                                                    <input type="date" class="form-control" name="dataDeVermifugacao" id="dataDeVermifugacao" max=<?php echo date('Y-m-d'); ?> disabled required>
                                                </div>
                                            </div>
                                            <br>
                                        </div>
                                        <div class="panel-footer" id="finalizarFormVermifugo">
                                            <div class="row">
                                                <div class="col-md-9 col-md-offset-3 d-flex justify-content-end gap-2">
                                                    <input type="hidden" name="idFichaMedica" id="idFichaMedicaVermifugo" value="">
                                                    <input id="enviarVermifugo" type="submit" class="btn btn-primary" value="Salvar" disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</section>

<!-- Modais seguem como antes -->
<!-- Modal Vacina -->
<div class="modal fade" id="docFormModal" tabindex="-1" role="dialog" aria-labelledby="docFormModalLabel" aria-modal="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="cadastroVacina">
                <div class="modal-header" style="display:flex; justify-content:space-between;">
                    <h5 class="modal-title">Adicionar Vacina</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body" style="padding:15px 40px;">
                    <div class="form-group">
                        <label for="nomeVacina">Nome da Vacina</label>
                        <input type="text" name="nomeVacina" class="form-control" id="nomeVacina" placeholder="Digite o nome da vacina" required>
                    </div>
                    <div class="form-group">
                        <label for="marcaVacina">Marca da Vacina</label>
                        <input type="text" name="marcaVacina" class="form-control" id="marcaVacina" placeholder="Digite a marca da vacina" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <input type="submit" value="Salvar" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Vermífugo -->
<div class="modal fade" id="docFormModalVermifugo" tabindex="-1" role="dialog" aria-labelledby="docFormModalVermifugoLabel" aria-modal="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="cadastroVermifugo">
                <div class="modal-header" style="display:flex; justify-content:space-between;">
                    <h5 class="modal-title">Adicionar Vermífugo</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body" style="padding:15px 40px;">
                    <div class="form-group">
                        <label for="nomeVermifugo">Nome do Vermífugo</label>
                        <input type="text" name="nomeVermifugo" class="form-control" id="nomeVermifugo" placeholder="Digite o nome do vermífugo" required>
                    </div>
                    <div class="form-group">
                        <label for="marcaVermifugo">Marca do Vermífugo</label>
                        <input type="text" name="marcaVermifugo" class="form-control" id="marcaVermifugo" placeholder="Digite a marca do vermífugo" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <input type="submit" value="Salvar" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>
</div>

<!--section do body-->
   
    
    
    <!-- end: page -->
    <!-- Vendor -->
        <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/select2/select2.js"></script>
        <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
        <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
        <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
        
        <!-- Theme Base, Components and Settings -->
        <!-- <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/theme.js"></script> -->
        
        <!-- Theme Custom -->
        <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/theme.custom.js"></script>
        
        <!-- Theme Initialization Files -->
        <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/theme.init.js"></script>
        <!-- Examples -->
        <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/tables/examples.datatables.default.js"></script>
        <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
        <script src="<?php echo htmlspecialchars(WWW, ENT_QUOTES, 'UTF-8');?>assets/javascripts/tables/examples.datatables.tabletools.js"></script>

        <!--Pedro-->
        <script>
           

        </script>
            <div align="right">
	            <iframe src="https://www.wegia.org/software/footer/pet.html" width="200" height="60" style="border:none;"></iframe>
            </div>
        <!--fim-->
    </body>
</html>
<?php
