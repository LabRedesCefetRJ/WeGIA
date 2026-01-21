function chamaModal(tr) {
    codigo_cobranca = tr[0].childNodes[0].innerHTML;
    $.post("get_detalhes_cobranca.php", { "codigo": codigo_cobranca }).done(function (resultadoBusca) {
        dadosCobranca = JSON.parse(resultadoBusca);
        console.log(resultadoBusca);


        var modal_codigo_html = `
        <div class="modal fade" id="detalharSocioModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
       <div class="modal-content">
          <div class="modal-header">
             <h5 class="modal-title" id="exampleModalLabel"></h5>
          </div>
          <div class="modal-body">
             <div class="box box-info box-solid boxDetalhes">
                <div class="box-header">
                   <h3 class="box-title"><i class="fa fa-list" aria-hidden="true"></i> Opções cobrança</h3>
                </div>
                <div class="box-body">
                   
                <a id="btn_importar_xlsx_cobranca" onclick="codigo_barras('${dadosCobranca[0].codigo}')" class="btn btn-app">
                <i class="fas fa-barcode"></i> Código pagamento online
              </a>

              <a id="btn_importar_xlsx_cobranca" onclick="detalhar_socio('${dadosCobranca[0].id_socio}')" class="btn btn-app">
              <i class="fas fa-user"></i> Detalhar sócio
            </a>

            <a target="_blank" href="${dadosCobranca[0].link_cobranca}" id="btn_importar_xlsx_cobranca" class="btn btn-app">
            <i class="fas fa-file-alt"></i> Link da cobrança
            </a>

            <a target="_blank" href="${dadosCobranca[0].link_boleto}" id="btn_importar_xlsx_cobranca" class="btn btn-app">
            <i class="fas fa-file-alt"></i> Link do boleto
            </a>

            <a href="./deletar_cobranca.php?cobranca=${dadosCobranca[0].codigo}" id="btn_importar_xlsx_cobranca" class="btn btn-app">
            <i class="fas fa-trash-alt"></i> Deletar cobrança
            </a>

            

           
    
                      </div>
                      <div class="modal-footer">
                         <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                      </div>
                   </form>
                </div>
                <!-- /.box-body -->
                <!-- Loading (remove the following to stop the loading)-->
                <!-- end loading -->
             </div>
          </div>
       </div>
    </div>
        `;
        // inputs = $(modal_detalhes_html).find("input");
        // console.log(inputs);
        // for(input of inputs){
        //   console.log(input);
        // }
        $(".boxCodigo").prepend(
            '<div class="overlay"> <i class="fa fa-refresh fa-spin"></i> </div>'
        );

        setTimeout(function () {
            $(".boxCodigo .overlay").remove();
        }, 600);
        $(modal_codigo_html).modal("toggle");

    })

}
function criarBotoes() {
    return `<button id="manageBtn" type="button" onclick="chamaModal($(this).closest('tr'))" class="btn btn-success btn-xs"><i class="far fa-address-card"></i> +Informações</button>`;
}
$(document).ready(function () {
    // Cadastro de cobraças/sócios/pessoa
    function cadastro_cobrancas_socio_xlsx(tabela) {
        log = {
            cadastrados: tabela.length,
            html_log: ""
        };
        for (linha of tabela) {
            if (valida_cpf_cnpj(linha['CPF/CNPJ Cliente'])) {

                if (linha['CPF/CNPJ Cliente'].length == 14) {
                    var pessoa = "fisica";
                } else var pessoa = "juridica";
                if (typeof linha['E-mail'] == 'undefined') {
                    var email = "";
                } else var email = linha['E-mail'];
                if (typeof linha['Telefone'] == 'undefined') {
                    var telefone = '';
                } else var telefone = linha['Telefone'];
                if (typeof linha['Descrição'] == 'undefined') {
                    var descricao = '';
                } else var descricao = linha['Descrição'];
                if (typeof linha['Emissão'] == 'undefined') {
                    var data_emissao = '';
                } else var data_emissao = linha['Emissão'];
                if (typeof linha['Vencimento'] == 'undefined') {
                    var data_vencimento = '';
                } else var data_vencimento = linha['Vencimento'];
                if (typeof linha['Valor'] == 'undefined') {
                    var valor = '';
                } else var valor = linha['Valor'];
                if (typeof linha['Valor Pago'] == 'undefined') {
                    var valor_pago = '';
                } else var valor_pago = linha['Valor Pago'];
                if (typeof linha['Data Pgto.'] == 'undefined') {
                    var data_pagamento = '';
                } else var data_pagamento = linha['Data Pgto.'];
                if (typeof linha['Link da Cobrança'] == 'undefined') {
                    var link_cobranca = '';
                } else var link_cobranca = linha['Link da Cobrança'];
                if (typeof linha['Link do Boleto'] == 'undefined') {
                    var link_boleto = '';
                } else var link_boleto = linha['Link do Boleto'];
                if (typeof linha['Linha Digitável'] == 'undefined') {
                    var linha_digitavel = '';
                } else var linha_digitavel = linha['Linha Digitável'];
                if (typeof linha['Código'] == 'undefined') {
                    var codigo = '';
                } else var codigo = linha['Código'];
                if (typeof linha['Status'] == 'undefined') {
                    var status = '';
                } else var status = linha['Status'];





                var dados = {
                    "socio_nome": linha['Nome Cliente'],
                    "pessoa": pessoa,
                    "email": email,
                    "telefone": telefone,
                    "cpf_cnpj": linha['CPF/CNPJ Cliente'],
                    "descricao": descricao,
                    "data_emissao": data_emissao,
                    "data_vencimento": data_vencimento,
                    "data_pagamento": data_pagamento,
                    "valor": valor,
                    "valor_pago": valor_pago,
                    "link_cobranca": link_cobranca,
                    "link_boleto": link_boleto,
                    "linha_digitavel": linha_digitavel,
                    "status": status,
                    "codigo": codigo,
                    "valor_periodo": null,
                    "data_referencia": null
                };
                // var dados = JSON.stringify(dados);

                $.ajax({
                    async: false,
                    url: "cadastro_cobranca.php",
                    data: dados,
                    type: "POST",
                    success: function (resp) {
                        var r = JSON.parse(resp);
                        if (r) {
                            log.html_log += "<p style='margin: 0.2em' class='text-green'> <b>[CADASTRADO]</b> - " + linha['Nome Cliente'] + "</p> ";
                        } else {
                            log.cadastrados--;
                            log.html_log += "<p style='margin: 0.2em' class='text-danger'> <b>[ERRO: POSSUI CAD/ARQUIVO MAL FORMATADO.]</b> - " + linha['Nome Cliente'] + " </p>";
                        }
                    },
                    error: function (e) {
                        log.cadastrados--;
                        log.html_log += "<p style='margin: 0.2em' class='danger'> <b>[ERRO: CON.]</b> - " + linha['Nome Cliente'] + " </p>";
                        console.dir(e);
                    }
                });
            } else {
                console.log("erro cpf");
                log.cadastrados--;
                log.html_log += "<p style='margin: 0.2em' class='text-danger'> <b>[ERRO: CPF/CNPJ]</b> - " + linha['Nome Cliente'] + " </p>";
            }
        }
        log.html_log = "Total cadastrados = " + log.cadastrados + "/" + tabela.length + log.html_log;
        return log;
    }

    // Função de cadastro de sócios
    function cadastro_socios_xlsx(tabela) {
        log = {
            cadastrados: tabela.length,
            html_log: ""
        };
        for (linha of tabela) {
            if (valida_cpf_cnpj(linha['CPF/CNPJ'])) {

                if (linha['CPF/CNPJ'].length == 14) {
                    var pessoa = "fisica";
                } else var pessoa = "juridica";
                if (typeof linha['EMAIL'] == 'undefined' || linha['EMAIL'] == '' || linha['EMAIL'] == undefined || typeof linha['EMAIL'] == undefined) {
                    var email = "";
                } else var email = linha['EMAIL'];
                if (typeof linha['COMPLEMENTO'] == 'undefined') {
                    var complemento = "";
                } else var complemento = linha['COMPLEMENTO'];
                var data_nasc = "imp";
                if (typeof linha['TELEFONE'] == 'undefined') {
                    var telefone = '';
                } else var telefone = linha['TELEFONE'].replace(" ", "");



                var dados = {
                    "socio_nome": linha['NOME/RAZÃO SOCIAL'],
                    "pessoa": pessoa,
                    "email": email,
                    "telefone": telefone,
                    "cpf_cnpj": linha['CPF/CNPJ'],
                    "rua": linha['ENDEREÇO'],
                    "numero": linha['NÚMERO'],
                    "complemento": complemento,
                    "bairro": linha['BAIRRO'],
                    "estado": linha['UF'],
                    "cidade": linha['CIDADE'],
                    "status": 4,
                    "contribuinte": null,
                    "data_nasc": data_nasc,
                    "cep": linha['CEP'],
                    "valor_periodo": null,
                    "data_referencia": null
                };
                // var dados = JSON.stringify(dados);

                $.ajax({
                    async: false,
                    url: "cadastro_socio.php",
                    data: dados,
                    type: "POST",
                    success: function (resp) {
                        var r = JSON.parse(resp);
                        if (r) {
                            log.html_log += "<p style='margin: 0.2em' class='text-green'> <b>[CADASTRADO]</b> - " + linha['NOME/RAZÃO SOCIAL'] + "</p> ";
                        } else {
                            log.cadastrados--;
                            log.html_log += "<p style='margin: 0.2em' class='text-danger'> <b>[ERRO: POSSUI CAD/ARQUIVO MAL FORMATADO.]</b> - " + linha['NOME/RAZÃO SOCIAL'] + " </p>";
                        }
                    },
                    error: function (e) {
                        log.cadastrados--;
                        log.html_log += "<p style='margin: 0.2em' class='danger'> <b>[ERRO: CON.]</b> - " + linha['NOME/RAZÃO SOCIAL'] + " </p>";
                        console.dir(e);
                    }
                });
            } else {
                console.log("erro cpf");
                log.cadastrados--;
                log.html_log += "<p style='margin: 0.2em' class='text-danger'> <b>[ERRO: CPF/CNPJ]</b> - " + linha['NOME/RAZÃO SOCIAL'] + " </p>";
            }
        }
        log.html_log = "Total cadastrados = " + log.cadastrados + "/" + tabela.length + log.html_log;
        return log;
    }
    // Setando máscaras dos inputs
    if (!$("#check_veri_cpf").prop("checked")) {
        if ($("#pessoa").val() == "fisica") {
            $("#cpf_cnpj").mask("999.999.999-99");
        } else {
            $("#cpf_cnpj").mask("99.999.999/9999-99");
        }
    }


    $("#cep").mask("99999-999");
    function modalSimples(titulo, msg, tipo) {
        switch (tipo) {
            case "erro": cor = "danger"; break;
            case "alerta": cor = "warning"; break;
            case "sucesso": cor = "success"; break;
            case "normal": cor = ""; break;
        }
        var id = Math.floor(Math.random() * 10);
        var html = '<div class="modal modal-' + cor + ' fade in" id="modal' + id + '" style="display: none; padding-right: 17px;"> <div class="modal-dialog"> <div class="modal-content"> <div class="modal-header"> <h4 class="modal-title">' + titulo + '</h4> </div> <div class="modal-body text-center"><div class="overlay"> <i style="margin: 0 auto; font-size: 40px" class="fa fa-user-plus"></i> </div> <h3>' + msg + '</h3> </div> <div class="modal-footer"> <button type="button" class="btn btn-outline pull-left .btn_fecharModal' + id + '" data-dismiss="modal">Fechar</button> </div> </div> <!-- /.modal-content --> </div> <!-- /.modal-dialog --> </div>';
        $("body").append(html);
        $("#modal" + id).modal("toggle");
        // if(tipo == "sucesso"){
        //     setTimeout(function(){
        //         location.reload();
        //     },1000);
        // }
    }
    function resetaForm(form) {
        $(form).each(function () {
            this.reset();
        });
    }
    function valida_cpf(cpf_inp) {
        var exp = /\.|\-/g;

        var cpf = cpf_inp.replace(exp, '').toString();

        if (cpf.length == 11) {

            var v = [];

            //Calcula o primeiro dígito de verificação.
            v[0] = 1 * cpf[0] + 2 * cpf[1] + 3 * cpf[2];
            v[0] += 4 * cpf[3] + 5 * cpf[4] + 6 * cpf[5];
            v[0] += 7 * cpf[6] + 8 * cpf[7] + 9 * cpf[8];
            v[0] = v[0] % 11;
            v[0] = v[0] % 10;

            //Calcula o segundo dígito de verificação.
            v[1] = 1 * cpf[1] + 2 * cpf[2] + 3 * cpf[3];
            v[1] += 4 * cpf[4] + 5 * cpf[5] + 6 * cpf[6];
            v[1] += 7 * cpf[7] + 8 * cpf[8] + 9 * v[0];
            v[1] = v[1] % 11;
            v[1] = v[1] % 10;

            //Retorna Verdadeiro se os dígitos de verificação são os esperados.

            if ((v[0] != cpf[9]) || (v[1] != cpf[10])) { return false }

            else if (cpf[0] == cpf[1] && cpf[1] == cpf[2] && cpf[2] == cpf[3] && cpf[3] == cpf[4] && cpf[4] == cpf[5] && cpf[5] == cpf[6] && cpf[6] == cpf[7] && cpf[7] == cpf[8] && cpf[8] == cpf[9] && cpf[9] == cpf[10]) { return false }

            else { return true }


        } else { return false }
    }
    $(document).on("submit", "#frm_novo_socio", function (e) {
        e.preventDefault();
        var DesabilitaverificaCpf = $("#check_veri_cpf").prop("checked");
        var socio_nome = $("#socio_nome").val();
        var pessoa_tipo = $("#pessoa").val();
        var contribuinte = $("#contribuinte").val();
        var status = $("#status").val();
        var email = $("#email").val();
        var telefone = $("#telefone").val();
        var cpf_cnpj = $("#cpf_cnpj").val();
        var rua = $("#rua").val();
        var numero = $("#numero").val();
        var complemento = $("#complemento").val();
        var bairro = $("#bairro").val();
        var estado = $("#estado").val();
        var cidade = $("#cidade").val();
        var tag = $("#tags").val();
        var data_nasc = $("#data_nasc").val();
        var cep = $("#cep").val();
        var data_referencia = $("#data_referencia").val();
        var valor_periodo = $("#valor_periodo").val();
        var tipo_contribuicao = $("#tipo_contribuicao").val();
        const csrf = document.querySelector('input[name="csrf_token"]').value;
        // Requisição POST - AJAX
        if (valida_cpf_cnpj(cpf_cnpj)) {
            $.post("./cadastro_socio.php", {
                "socio_nome": socio_nome,
                "pessoa": pessoa_tipo,
                "contribuinte": contribuinte,
                "status": status,
                "email": email,
                "tag": tag,
                "telefone": telefone,
                "cpf_cnpj": cpf_cnpj,
                "rua": rua,
                "numero": numero,
                "complemento": complemento,
                "bairro": bairro,
                "estado": estado,
                "cidade": cidade,
                "data_nasc": data_nasc,
                "cep": cep,
                "data_referencia": data_referencia,
                "valor_periodo": valor_periodo,
                "tipo_contribuicao": tipo_contribuicao,
                "csrf_token": csrf
            }).done(function (resultadoCadastro) {
                var resultado = JSON.parse(resultadoCadastro);
                if (resultado) {
                    $(".socioModal").append(
                        '<div class="overlay"> <i style="font-size: 72px; color: green;" class="fa fa-refresh fa-spin"></i> </div>'
                    );
                    setTimeout(function () {
                        $("#adicionarSocioModal").modal("toggle");
                        $(".socioModal .overlay").remove();
                        $("#qtd_socios").html(Number($("#qtd_socios").html()) + 1);
                        resetaForm("#frm_novo_socio");
                    }, 1000);
                }
            }).fail(function (resposta) {
                console.log(resposta);
                resposta = JSON.parse(resposta.responseText)
                modalSimples("Status", `Erro ao cadastrar sócio, ${resposta.erro}.`, "erro");
            });
        } else {
            if (DesabilitaverificaCpf == true) {

                //adicionar verificação se cpf_cnpj está preenchido
                if (!cpf_cnpj) {
                    //gerar número de cpf_cnpj aleatório
                    cpf_cnpj = '';
                    for (let i = 0; i < 14; i++) {
                        cpf_cnpj += Math.floor(Math.random() * 9).toString();
                    }
                    console.log('O documento é: ' + cpf_cnpj);
                }

                $.post("./cadastro_socio.php", {
                    "socio_nome": socio_nome,
                    "pessoa": pessoa_tipo,
                    "contribuinte": contribuinte,
                    "status": status,
                    "email": email,
                    "tag": tag,
                    "telefone": telefone,
                    "cpf_cnpj": cpf_cnpj,
                    "rua": rua,
                    "numero": numero,
                    "complemento": complemento,
                    "bairro": bairro,
                    "estado": estado,
                    "cidade": cidade,
                    "data_nasc": data_nasc,
                    "cep": cep,
                    "data_referencia": data_referencia,
                    "valor_periodo": valor_periodo,
                    "tipo_contribuicao": tipo_contribuicao,
                    "csrf_token": csrf
                }).done(function (resultadoCadastro) {
                    var resultado = JSON.parse(resultadoCadastro);
                    if (resultado) {
                        $(".socioModal").append(
                            '<div class="overlay"> <i style="font-size: 72px; color: green;" class="fa fa-refresh fa-spin"></i> </div>'
                        );
                        setTimeout(function () {
                            $("#adicionarSocioModal").modal("toggle");
                            $(".socioModal .overlay").remove();
                            $("#qtd_socios").html(Number($("#qtd_socios").html()) + 1);
                            resetaForm("#frm_novo_socio");
                        }, 1000);
                    }
                }).fail(function (resposta) {
                    console.log(resposta);
                    resposta = JSON.parse(resposta.responseText)
                    modalSimples("Status", `Erro ao cadastrar sócio, ${resposta.erro}.`, "erro");
                });
            } else {
                modalSimples("Status", "O CPF/CNPJ informado é inválido!", "erro");
            }
        }

    });
    $(document).on("submit", "#frm_editar_socio", function (e) {
        e.preventDefault();
        var DesabilitaverificaCpf = $("#check_veri_cpf").prop("checked");
        var id_socio = $("#id_socio").val();
        var socio_nome = $("#socio_nome").val();
        var pessoa_tipo = $("#pessoa").val();
        var contribuinte = $("#contribuinte").val();
        var status = $("#status").val();
        var email = $("#email").val();
        var telefone = $("#telefone").val();
        var cpf_cnpj = $("#cpf_cnpj").val();
        var rua = $("#rua").val();
        var numero = $("#numero").val();
        var complemento = $("#complemento").val();
        var bairro = $("#bairro").val();
        var tag = $("#tags").val();
        var estado = $("#estado").val();
        var cidade = $("#cidade").val();
        var data_nasc = $("#data_nasc").val();
        var cep = $("#cep").val();
        var data_referencia = $("#data_referencia").val();
        var valor_periodo = $("#valor_periodo").val();
        var tipo_contribuicao = $("#tipo_contribuicao").val();
        // Requisição POST - AJAX
        if (valida_cpf_cnpj(cpf_cnpj)) {
            $.post("./processa_edicao_socio.php", {
                "id_socio": id_socio,
                "socio_nome": socio_nome,
                "pessoa": pessoa_tipo,
                "contribuinte": contribuinte,
                "status": status,
                "email": email,
                "telefone": telefone,
                "cpf_cnpj": cpf_cnpj,
                "rua": rua,
                "tag": tag,
                "numero": numero,
                "complemento": complemento,
                "bairro": bairro,
                "estado": estado,
                "cidade": cidade,
                "data_nasc": data_nasc,
                "cep": cep,
                "data_referencia": data_referencia,
                "valor_periodo": valor_periodo,
                "tipo_contribuicao": tipo_contribuicao
            }).done(function (resultadoCadastro) {
                var resultado = JSON.parse(resultadoCadastro);
                if (resultado) {
                    $(".socioModal").append(
                        '<div class="overlay"> <i style="font-size: 72px; color: green;" class="fa fa-refresh fa-spin"></i> </div>'
                    );
                    setTimeout(function () {
                        resetaForm("#frm_editar_socio");
                        window.location.replace("./");
                    }, 1000);
                } else {
                    modalSimples("Status", "Erro ao editar sócio, verifique os dados e tente novamente.", "erro");
                }
            });
        } else {
            if (DesabilitaverificaCpf == true) {
                $.post("./processa_edicao_socio.php", {
                    "id_socio": id_socio,
                    "socio_nome": socio_nome,
                    "pessoa": pessoa_tipo,
                    "contribuinte": contribuinte,
                    "status": status,
                    "email": email,
                    "telefone": telefone,
                    "cpf_cnpj": cpf_cnpj,
                    "rua": rua,
                    "tag": tag,
                    "numero": numero,
                    "complemento": complemento,
                    "bairro": bairro,
                    "estado": estado,
                    "cidade": cidade,
                    "data_nasc": data_nasc,
                    "cep": cep,
                    "data_referencia": data_referencia,
                    "valor_periodo": valor_periodo,
                    "tipo_contribuicao": tipo_contribuicao
                }).done(function (resultadoCadastro) {
                    var resultado = JSON.parse(resultadoCadastro);
                    if (resultado) {
                        $(".socioModal").append(
                            '<div class="overlay"> <i style="font-size: 72px; color: green;" class="fa fa-refresh fa-spin"></i> </div>'
                        );
                        setTimeout(function () {
                            resetaForm("#frm_editar_socio");
                            window.location.replace("./");
                        }, 1000);
                    } else {
                        modalSimples("Status", "Erro ao editar sócio, verifique os dados e tente novamente.", "erro");
                    }
                });
            } else {
                modalSimples("Status", "O CPF/CNPJ informado é inválido!", "erro");
            }
        }

    });

    $(document).on("submit", "#frm_nova_cobranca", function (e) {
        e.preventDefault();
        var socio_nome = $("#socio_nome_ci").val().split("|")[0];
        var cpf_cnpj = $("#socio_nome_ci").val().split("|")[1];
        var socio_id = $("#socio_nome_ci").val().split("|")[2];
        var local_recepcao = $("#local_recepcao").val();
        var receptor = $("#receptor").val();
        var valor = $("#valor_cobranca").val();
        var forma_doacao = $("#forma_doacao").val();
        var data_doacao = $("#data_doacao").val();
        // Requisição POST - AJAX
        $.post("./cadastro_cobranca_m.php", {
            "socio_nome": socio_nome,
            "socio_id": socio_id,
            "local_recepcao": local_recepcao,
            "receptor": receptor,
            "data_doacao": data_doacao,
            "valor": valor,
            "forma_doacao": forma_doacao
        }).done(function (resultadoCadastro) {
            var resultado = JSON.parse(resultadoCadastro);
            if (resultado) {
                $(".cobrancaModal").append(
                    '<div class="overlay"> <i style="font-size: 72px; color: green;" class="fa fa-refresh fa-spin"></i> </div>'
                );
                setTimeout(function () {
                    $("#adicionarCobrancaModal").modal("toggle");
                    $(".cobrancaModal .overlay").remove();
                    resetaForm("#frm_nova_cobranca");
                }, 1000);
            } else {
                modalSimples("Status", "Erro ao cadastrar cobranca, verifique os dados e tente novamente.", "erro");
            }
        });
    });

    // Validação de CEP e API de CEP
    $("#cep").blur(function () {
        //Nova variável "cep" somente com dígitos.
        var cep = $(this).val().replace(/\D/g, '');
        //Verifica se campo cep possui valor informado.
        if (cep != "") {

            //Expressão regular para validar o CEP.
            var validacep = /^[0-9]{8}$/;

            //Valida o formato do CEP.
            if (validacep.test(cep)) {

                //simbolo de carregando enquanto consulta webservice.
                $(".endereco").prepend(
                    '<div class="overlay"> <i class="fa fa-refresh fa-spin"></i> </div>'
                );

                setTimeout(function () {
                    $(".endereco .overlay").remove();
                }, 600);

                //Consulta o webservice viacep.com.br/
                $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function (dados) {

                    if (!("erro" in dados)) {
                        //Atualiza os campos com os valores da consulta.
                        $("#rua").val(dados.logradouro);
                        $("#bairro").val(dados.bairro);
                        $("#cidade").val(dados.localidade);
                        $("#estado").val(dados.uf);
                        $(".status_cep").html('');
                    } //end if.
                    else {
                        //CEP pesquisado não foi encontrado.
                        $("#cep").val("");
                        // modalSimples("Erro", "Cep não encontrado.", "erro");
                        $(".status_cep").html('<p class="text-red animacao2">Cep não encontrado.</p>');
                    }
                });
            } //end if.
            else {
                //cep é inválido.
                $("#cep").val("");
                // modalSimples("Erro", "Cep inválido.", "erro");
                $(".status_cep").html('<p class="text-red animacao2">Cep inválido.</p>');
            }
        }
    });
    $("#pessoa").change(function () {
        if ($(this).val() == "juridica") {
            $("#label_cpf_cnpj").html("CNPJ");
            if (!$("#check_veri_cpf").prop("checked")) {
                $("#cpf_cnpj").mask("99.999.999/9999-99");
            } else $("#cpf_cnpj").unmask();
            $("#cpf_cnpj").val("");
            $(".div_nasc").html("");
        } else {
            $("#label_cpf_cnpj").html("CPF");
            if (!$("#check_veri_cpf").prop("checked")) {
                $("#cpf_cnpj").mask("999.999.999-99");
            } else $("#cpf_cnpj").unmask();
            $("#cpf_cnpj").val("");
            $(".div_nasc").append('<div class="form-group col-xs-4 animacao2"> <label for="valor">Data de nascimento</label> <input type="date" class="form-control" id="data_nasc" name="data_nasc"> </div>');
        }
    })
    // Removendo máscara ao apertar opção de desligar máscara
    $("#check_veri_cpf").change(function () {
        if ($(this).prop("checked")) {
            $("#cpf_cnpj").unmask();
            $("#label_cpf_cnpj").html("Identificação");
            $("#cpf_cnpj").val("");
        } else {
            if ($("#pessoa").val() == "juridica") {
                $("#label_cpf_cnpj").html("CNPJ");
                $("#cpf_cnpj").mask("99.999.999/9999-99");
                $("#cpf_cnpj").val("");
            } else {
                $("#label_cpf_cnpj").html("CPF");
                $("#cpf_cnpj").mask("999.999.999-99");
                $("#cpf_cnpj").val("");
            }
        }
    })
    // Máscara telefone/celular
    $("#telefone").keydown(function () {
        try {
            $("#telefone").unmask();
        } catch (e) { }

        var tamanho = $("#telefone").val().length;

        if (tamanho <= 9) {
            $("#telefone").mask("(99)9999-9999");

        } else {
            $("#telefone").mask("(99)99999-9999");
        }

        // ajustando foco
        var elem = this;
        setTimeout(function () {
            // mudo a posição do seletor
            elem.selectionStart = elem.selectionEnd = 10000;
        }, 0);
        // reaplico o valor para mudar o foco
        var currentValue = $(this).val();
        $(this).val('');
        $(this).val(currentValue);
    });
    // Configuração tabela sócios
    $(document).ready(function () {
        $('#example').DataTable({
            "processing": true,
            "language": {
                "sEmptyTable": "Nenhum sócio encontrado no sistema.",
                "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                "sInfoPostFix": "",
                "sInfoThousands": ".",
                "sLengthMenu": "_MENU_ sócios por página",
                "sLoadingRecords": "Carregando...",
                "sProcessing": "Processando...",
                "sZeroRecords": "Nenhum sócio encontrado",
                "sSearch": "Pesquisar",
                "oPaginate": {
                    "sNext": "Próximo",
                    "sPrevious": "Anterior",
                    "sFirst": "Primeiro",
                    "sLast": "Último"
                },
                "oAria": {
                    "sSortAscending": ": Ordenar colunas de forma ascendente",
                    "sSortDescending": ": Ordenar colunas de forma descendente"
                }
            }
        });
    });
    // Tabela aniversariantes
    $(document).ready(function () {
        $('#tb_aniversario').DataTable({
            "processing": true,
            "searching": false,
            "language": {
                "sEmptyTable": "Nenhum sócio faz aniversário no mês atual.",
                "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                "sInfoPostFix": "",
                "sInfoThousands": ".",
                "sLengthMenu": "_MENU_ sócios por página",
                "sLoadingRecords": "Carregando...",
                "sProcessing": "Processando...",
                "sZeroRecords": "Nenhum sócio faz aniversário no mês atual.",
                "sSearch": "Pesquisar",
                "oPaginate": {
                    "sNext": "Próximo",
                    "sPrevious": "Anterior",
                    "sFirst": "Primeiro",
                    "sLast": "Último"
                },
                "oAria": {
                    "sSortAscending": ": Ordenar colunas de forma ascendente",
                    "sSortDescending": ": Ordenar colunas de forma descendente"
                }
            }
        });
    });
    // Tabela doações
    $(document).ready(function () {
        $('#tbDoacoes').DataTable({
            "processing": true,
            "searching": false,
            "language": {
                "sEmptyTable": "Nenhuma doação encontrada no sistema.",
                "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                "sInfoPostFix": "",
                "sInfoThousands": ".",
                "sLengthMenu": "_MENU_ doações por página",
                "sLoadingRecords": "Carregando...",
                "sProcessing": "Processando...",
                "sZeroRecords": "Nenhum sócio faz aniversário no mês atual.",
                "sSearch": "Pesquisar",
                "oPaginate": {
                    "sNext": "Próximo",
                    "sPrevious": "Anterior",
                    "sFirst": "Primeiro",
                    "sLast": "Último"
                },
                "oAria": {
                    "sSortAscending": ": Ordenar colunas de forma ascendente",
                    "sSortDescending": ": Ordenar colunas de forma descendente"
                }
            }
        });
    });
    // Tabela cobranças
    $(document).ready(function () {
        $('#tbCobrancas').DataTable({
            "processing": true,
            "searching": true,
            "ajax": "processa_cobrancas_tabela.php",
            "columnDefs": [{ "render": criarBotoes, "data": null, "targets": [8] }],
            "language": {
                "sEmptyTable": "Nenhuma cobrança encontrada no sistema.",
                "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                "sInfoPostFix": "",
                "sInfoThousands": ".",
                "sLengthMenu": "_MENU_ cobranças por página",
                "sLoadingRecords": "Carregando...",
                "sProcessing": "Processando...",
                "sZeroRecords": "Nenhuma cobrança encontrada no sistema.",
                "sSearch": "Pesquisar",
                "oPaginate": {
                    "sNext": "Próximo",
                    "sPrevious": "Anterior",
                    "sFirst": "Primeiro",
                    "sLast": "Último"
                },
                "oAria": {
                    "sSortAscending": ": Ordenar colunas de forma ascendente",
                    "sSortDescending": ": Ordenar colunas de forma descendente"
                }
            }
        });
    });

    // Tabela contribuições
    $(document).ready(function () {
        $('#tabela-contribuicoes').DataTable({
            "processing": true,
            "searching": true,
            "ajax": "../../contribuicao/controller/control.php?nomeClasse=ContribuicaoLogController&metodo=getContribuicoesLogJSON",
            "columns": [
                { "data": "codigo" },
                { "data": "nomeSocio" },
                { "data": "plataforma" },
                {
                    "data": "meio", "render": function (data, type, row) {
                        return data == 'Carne' ? "Carnê" : data;
                    }
                },
                { "data": "dataGeracao", "render": (data, type) => { return formataDataBr(data, type) } },
                { "data": "dataVencimento", "render": (data, type) => { return formataDataBr(data, type) } },
                { "data": "dataPagamento", "render": (data, type) => { return formataDataBr(data, type) } },
                { "data": "valor" },
                {
                    "data": "status", "render": function (data, type, row) {
                        return data == 1 ? "Pago" : "Pendente";
                    }
                },
            ],
            "order": [[4, 'desc']],  // Ordena pela primeira coluna (dataGeracao) de forma decrescente
            "language": {
                "sEmptyTable": "Nenhuma contribuição encontrada no sistema.",
                "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                "sInfoPostFix": "",
                "sInfoThousands": ".",
                "sLengthMenu": "_MENU_ contribuições por página",
                "sLoadingRecords": "Carregando...",
                "sProcessing": "Processando...",
                "sZeroRecords": "Nenhuma contribuição encontrada no sistema.",
                "sSearch": "Pesquisar",
                "oPaginate": {
                    "sNext": "Próximo",
                    "sPrevious": "Anterior",
                    "sFirst": "Primeiro",
                    "sLast": "Último"
                },
                "oAria": {
                    "sSortAscending": ": Ordenar colunas de forma ascendente",
                    "sSortDescending": ": Ordenar colunas de forma descendente"
                }
            }
        });
    });

    function formataDataBr(data, type) {
        if (type === 'display' || type === 'filter') {
            if (!data) {
                return data;
            }
            let dateParts = data.split("-");
            return `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;
        }
        return data;
    }

    // Modal tabela sócios
    $("#btn_socios").click(function () {
        $("#modalSocios").modal("toggle");
    });
    // Modal opções bd
    $("#btn_bd").click(function () {
        $("#bdModal").modal("toggle");
        $("#bdModal .modal-body").prepend(
            '<div class="alert alert-warning animacao3 alerta_bd" role="alert"> <i class="fa fa-question-circle"></i> Atenção! As modificações feitas aqui são permanentes. </div>'
        );
        setTimeout(function () {
            $(".alerta_bd").hide();
        }, 5700);
    });
    // Botão deletar sócios
    $("#btn_deletarSocios").click(function () {
        $(".bd_box").prepend('<div class="overlay"> <i class="fa fa-refresh fa-spin"></i> </div>');
        $.get("./controller/deletar_socios.php", { "chave": "h)8^#w4T<HaN-GSc&BM3[<?mvG[R?b" })
            .done(function (resultado) {
                var r = JSON.parse(resultado);
                if (r) {
                    console.log("teste");
                    setTimeout(function () {
                        $(".bd_box .overlay").hide();
                    }, 1000);
                    $("#bdModal").modal("toggle");
                    location.reload();
                }
            });
    });
    $("#btn_add_socio").on("click", function () {
        $("#adicionarSocioModal").modal("toggle");
    });
    $("#btn_importar_xlsx").click(function () {
        $("#modal_importar_xlsx").modal("toggle");
    });
    $("#btn_importar_xlsx_cobranca").click(function () {
        $("#modal_importar_xlsx_cobranca").modal("toggle");
    });
    var arquivo = document.getElementById('arquivo_xlsx');
    if (typeof arquivo !== 'undefined' && arquivo !== null) {
        arquivo.onchange = function (e) {
            var ext = this.value.match(/\.([^\.]+)$/)[1];
            switch (ext) {
                case 'xlsx':
                case 'xls':
                    console.log("extensão ok");
                    break;
                default:
                    modalSimples("Status", "Extensão inválida!", "erro");
                    this.value = '';
            }
        }
    }

    var arquivo = document.getElementById('arquivo_xlsx_cobranca');
    if (typeof arquivo !== 'undefined' && arquivo !== null) {
        arquivo.onchange = function (e) {
            var ext = this.value.match(/\.([^\.]+)$/)[1];
            switch (ext) {
                case 'xlsx':
                case 'xls':
                    console.log("extensão ok");
                    break;
                default:
                    modalSimples("Status", "Extensão inválida!", "erro");
                    this.value = '';
            }
        }
    }

    // Upload de cobranças xlsx
    $(document).on("submit", "#form_xlsx_cobranca", function (e) {
        e.preventDefault();
        var $form = $(this);
        uploadArquivosCobranca($form);
        $(".barra_envio").css("width", "0" + "%");
    });

    //   Função para deletar o diretório de tabelas de sócios e cobranças por motivos de segurança
    function deletar_diretorio_tabelas() {
        $.post('./controller/deletar_diretorio_tabelas.php')
            .done(function (r) {
                if (r) {
                    return true;
                }
                return false;
            })
    }

    function uploadArquivosCobranca($form) {
        deletar_diretorio_tabelas();
        var dados = new FormData($form[0]);
        var request = new XMLHttpRequest();
        $(".box_xlsx").prepend('<div class="overlay"> <i class="fa fa-refresh fa-spin"></i> </div>');
        request.upload.addEventListener("progress", function (e) {
            var porcentagem = e.loaded / e.total * 100;
            $(".barra_envio").css("width", porcentagem + "%");
        });

        request.open('post', './controller/controla_xlsx_cobranca.php');
        request.send(dados);
        request.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                var r = JSON.parse(this.response);
                console.log(r);
                /* requisição */
                var url = r.url;
                var oReq = new XMLHttpRequest();
                oReq.open("GET", url, true);
                oReq.responseType = "arraybuffer";

                oReq.onload = function (e) {
                    var arraybuffer = oReq.response;

                    /* convertendo dados para binário */
                    var data = new Uint8Array(arraybuffer);
                    var arr = new Array();
                    for (var i = 0; i != data.length; ++i) arr[i] = String.fromCharCode(data[i]);
                    var bstr = arr.join("");

                    /* chamando api para conveter tabela xlsx */
                    var workbook = XLSX.read(bstr, {
                        type: "binary"
                    });

                    //   ----
                    var first_sheet_name = workbook.SheetNames[0];

                    var worksheet = workbook.Sheets[first_sheet_name];
                    var tabela = (XLSX.utils.sheet_to_json(worksheet, {
                        raw: true
                    }));
                    console.log(tabela);
                    var log = cadastro_cobrancas_socio_xlsx(tabela);
                    console.log(log.cadastrados + " - " + tabela.length);
                    if (log.cadastrados == tabela.length) {
                        $(".box_xlsx .overlay").remove();
                        $("#modal_importar_xlsx").modal("toggle");
                        modalSimples("Status", 'Importação bem sucedida. <div  class="box box-default"> <div class="box-header with-border"> <h3 class="box-title">Log de importação</h3> <div class="box-tools pull-right"> <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i> </button> </div> </div>  <div class="box-body"> <div style="font-size: 12px; color:black; overflow: auto; max-height: 340px; text-justify: left;" class="log">' + log.html_log + '</div> </div> </div>', "sucesso");
                        resetaForm("#form_xlsx");
                        $(".barra_envio").css("width", "0" + "%");
                        // location.reload();
                        deletar_diretorio_tabelas();
                    } else {
                        $("#modal_importar_xlsx").modal("toggle");
                        $("#qtd_socios").html(Number($("#qtd_socios").html()) + log.cadastrados);
                        modalSimples("Status", 'Não foi possível concluir a importação por completo. <div  class="box box-default"> <div class="box-header with-border"> <h3 class="box-title">Log de importação</h3> <div class="box-tools pull-right"> <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i> </button> </div> </div>  <div class="box-body"> <div style="font-size: 12px; color:black; overflow: auto; max-height: 340px; text-justify: left;" class="log">' + log.html_log + '</div> </div> </div>', "normal");
                        $(".box_xlsx .overlay").remove();
                        resetaForm("#form_xlsx");
                        $(".barra_envio").css("width", "0" + "%");
                        deletar_diretorio_tabelas();
                    }
                }

                oReq.send();


            }
        }
    }
    $("#btn_perfil").click(function () {
        $("#modalPerfil").modal("toggle");
    });
    $("#btn_Boletofacil").click(function () {
        $("#modalBoletofacil").modal("toggle");
    });
    $("#btn_aniversariantes").click(function () {
        $("#modal_aniversariantes").modal("toggle");
    });
    $("#btn_cadastro_cobranca").click(function () {
        $("#adicionarCobrancaModal").modal("toggle");
    });
    // $("#btn_graficos").click(function(){
    //      $("#modal_graficos").modal("toggle");
    // });
});