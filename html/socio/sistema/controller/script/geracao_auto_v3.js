$(document).ready(function () {

    // Geração para sócio único
    function procurar_desejado(id_socio) {
        $.get("./get_socio.php", {
            "id": id_socio
        })
            .done(function (dados) {
                var socios = JSON.parse(dados);
                if (socios) {
                    function montaTabelaInicial(data_inicial, periodicidade_socio, parcelas, valor, nome_socio) {

                        console.log('Data selecionada: ' + data_inicial);

                        function dataAtualFormatada(data_r) {
                            var data = new Date(data_r),
                                dia = data.getDate().toString(),
                                diaF = (dia.length == 1) ? '0' + dia : dia,
                                mes = (data.getMonth()+1).toString(), //+1 pois no getMonth Janeiro começa com zero.
                                mesF = (mes.length == 1) ? '0' + mes : mes,
                                anoF = data.getFullYear();
                            return diaF + "/" + mesF + "/" + anoF;
                        }

                        $(".detalhes_unico").html("");
                        $("#btn_wpp").off();
                        $("#btn_wpp").css("display", "none");
                        $("#btn_geracao_unica").attr("disabled", false);
                        $("#btn_geracao_unica").text("Confirmar geração");
                        referenciaAccordion = nome_socio.replace(/[^a-zA-Zs]/g, "") + Math.round(Math.random() * 100000000);
                        var tabela = ``;
                        var dataV_formatada = data_inicial;

                        var arrayDataSegmentsA = dataV_formatada.split('-');
                        let mesAA = parseInt(arrayDataSegmentsA[1]) - 1;

                        let total = 0;

                        for (i = 0; i < parcelas; i++) {

                            console.log(mesAA);
                            let data = new Date(arrayDataSegmentsA[0], mesAA, arrayDataSegmentsA[2]);

                            //Incrementar meses
                            data.setMonth(data.getMonth() + i*periodicidade_socio);

                            if(data.getDate() != arrayDataSegmentsA[2]){
                                data.setDate(0);
                            }

                            const dataFormatada = dataAtualFormatada(data);

                            tabela += `<tr><td>${i + 1}/${parcelas}</td><td>${dataFormatada}</td><td>R$ ${valor}</td></tr>`;

                            total += valor;
                        }
                        tabela += `<tr><td colspan='2'>Total: </td><td>R$ ${total}</td></tr>`;
                        $(".detalhes_unico").append(`
                        <br>
                        <div class="card-body">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Parcela</th>
                                        <th>Data de vencimento</th>
                                        <th>Valor parcela</td>
                                    </tr>
                                </thead>
                                <tbody>${tabela}</tbody>
                            </table>
                        </div>
                        `)
                    }

                    function montaTabelaInicialAlterado(data_inicial, data_inicial_br, periodicidade_socio, parcelas, valor, nome_socio) {

                        function dataAtualFormatada(data_r) {
                            var data = new Date(data_r),
                                dia = data.getDate().toString(),
                                diaF = (dia.length == 1) ? '0' + dia : dia,
                                mes = (data.getMonth() + 1).toString(), //+1 pois no getMonth Janeiro começa com zero.
                                mesF = (mes.length == 1) ? '0' + mes : mes,
                                anoF = data.getFullYear();
                            return diaF + "/" + mesF + "/" + anoF;
                        }

                        $(".detalhes_unico").html("");
                        $("#btn_wpp").off();
                        $("#btn_wpp").css("display", "none");
                        $("#btn_geracao_unica").attr("disabled", false);
                        $("#btn_geracao_unica").text("Confirmar geração");


                        referenciaAccordion = nome_socio.replace(/[^a-zA-Zs]/g, "") + Math.round(Math.random() * 100000000);
                        var tabela = ``;
                        var dataV = data_inicial_br;
                        var dataV_formatada = data_inicial;

                        var arrayDataSegmentsA = dataV_formatada.split('-');
                        var mesAA = arrayDataSegmentsA[1] - 1;
                        var total = 0;

                        for (i = 0; i < parcelas; i++) {
                            tabela += `<tr><td>${i + 1}/${parcelas}</td><td>${dataV}</td><td>R$ ${valor}</td></tr>`
                            // var arrayDataSegments = dataV_formatada.split('-');
                            // var mes = arrayDataSegments[1]-1;

                            var novaData = new Date(arrayDataSegmentsA[0], mesAA, arrayDataSegmentsA[2]);

                            novaData.setMonth(novaData.getMonth() + periodicidade_socio);
                            dataV_formatada = `${novaData.getFullYear()}-${novaData.getMonth()}-${novaData.getDate()}`;
                            dataV = `${dataAtualFormatada(novaData)}`;
                            total += valor;

                            mesAA += periodicidade_socio;
                        }
                        tabela += `<tr><td colspan='2'>Total: </td><td>R$ ${total}</td></tr>`;
                    }


                    $(".configs_unico").css("display", "block");

                    var tipo;

                    $("#tipo_geracao").change(function () {
                        if ($(this).val() == 0) {
                            $("#num_parcelas").val(1);
                            $("#num_parcelas").prop('disabled', true);
                            // $("#sobrenome").prop('disabled', false);
                        }
                        else {
                            $("#num_parcelas").val();
                            $("#num_parcelas").prop('disabled', false);
                        }
                    })

                    $("#btn_confirma").click(function () {

                        var inputParcelas = $("#num_parcelas").val();
                        var inputData = $("#data_vencimento").val();
                        var inputValor = $("#valor_u").val();
                        var tipo_boleto = $("#tipo_geracao").val();
                        $("#btn_geracao_unica").css("display", "inline");

                        tipo = Number(tipo_boleto);
                        periodicidade_socio = 1;

                        if (inputParcelas <= 0 || inputParcelas == null || inputValor <= 0 || inputValor == null || inputData == '') {
                            alert("Dados inválidos, tente novamente!");
                        }
                        montaTabelaInicial(inputData, tipo, inputParcelas, Number($("#valor_u").val()), socios[0].nome);
                    })

                    montaTabelaInicialAlterado('', '', '', '', '', socios[0].nome);

                    $(".div_btn_gerar").css("display", "block");

                    $("#btn_geracao_unica").click(function (event) {
                        //Ligação com a nova API, posteriormente passar a URL indicando para a refatoração em POO
                        const tipoGeracao = document.getElementById('tipo_geracao').value;

                        const btnGeracaoUnica = event.target;

                        btnGeracaoUnica.disabled = true;

                        let url = '';

                        switch(tipoGeracao){
                            case '0': url = '../../contribuicao/controller/control.php?nomeClasse=ContribuicaoLogController&metodo=criarBoleto'; break;
                            case '1': url = '../../contribuicao/controller/control.php?nomeClasse=ContribuicaoLogController&metodo=criarCarne'; break; 
                            case '2': url = '../../contribuicao/controller/control.php?nomeClasse=ContribuicaoLogController&metodo=criarCarne'; break; 
                            case '3': url = '../../contribuicao/controller/control.php?nomeClasse=ContribuicaoLogController&metodo=criarCarne'; break; 
                            case '6': url = '../../contribuicao/controller/control.php?nomeClasse=ContribuicaoLogController&metodo=criarCarne'; break; 
                            default: alert('O tipo de geração escolhido é inválido'); return;
                        }

                        const valor = document.getElementById('valor_u').value;
                        const socio = document.getElementById('id_pesquisa').value;
                        const dia = document.getElementById('data_vencimento').value;
                        const parcela = document.getElementById('num_parcelas').value;

                        const cpfCnpj = socio.split('|')[1];

                        console.log(dia);

                        $.post(url, {
                            "documento_socio": cpfCnpj,
                            "valor": valor,
                            "dia": dia,
                            "parcelas": parcela,
                            "tipoGeracao": tipoGeracao
                        }).done(function (r) {
                            const resposta = JSON.parse(r);
                            if (resposta.link) {
                                console.log(resposta.link);
                                // Redirecionar o usuário para o link do boleto em uma nova aba
                                window.open(resposta.link, '_blank');
                            } else if (resposta.erro){
                                alert('Erro: '+ resposta.erro);
                            }else {
                                alert("Ops! Ocorreu um problema na geração da sua forma de pagamento, tente novamente, se o erro persistir contate o suporte.");
                            }

                            btnGeracaoUnica.disabled = false;
                        });
                    });
                } else {

                    alert(`Para gerar carnês/boletos para o sócio desejado você deve completar o cadastro dele primeiro com os seguintes dados: valor por período, data de referência e a periodicidade.`);
                }
            })
            .fail(function (dados) {
                alert("Erro na obtenção de dados.");
            })
    }
    $("#geracao").change(function () {
        var tipo_desejado = $(this).val();
        procurar_desejados(tipo_desejado);
    })
    $("#btn_gerar_unico").click(function () {
        var id_socio = $("#id_pesquisa").val().split("|")[2];
        procurar_desejado(id_socio);

        $("#btn_gerar_unico").css("display", "none");

        // $("#num_parcelas").val("");
        // $("#data_vencimento").val("");
        // $("#valor_u").val("");
        // $("#tipo_geracao").val("7");
        // $("#num_parcelas").prop('disabled', false);
    })

    //Excluir após migração
    function geracao_unica_antiga() {
        $(".box-geracaounica").prepend('<div class="overlay"> <i class="fa fa-refresh fa-spin"></i> </div>');
        $.post("./controller/query_geracao_auto.php", {
            "query": `SELECT * FROM doacao_boleto_info AS bi JOIN sistema_pagamento AS sp ON (bi.id_sistema = sp.id) JOIN doacao_boleto_regras AS br ON (br.id = bi.id_regras)  WHERE nome_sistema = 'BOLETOFACIL'`
        })
            .done(function (dados_api) {
                $("#num_parcelas").prop('disabled', true);
                $("#data_vencimento").prop('disabled', true);
                $("#valor_u").prop('disabled', true);
                $("#tipo_geracao").prop('disabled', true);
                $("#num_parcelas").prop('disabled', true);
                $("#id_pesquisa").prop('disabled', true);
                $("#btn_confirma").css("display", "none");
                $("#btn_voltar").css("display", "block");
                $("#btn_voltar").click(function () {
                    location.reload();
                    $("#num_parcelas").val();
                    $("#data_vencimento").val();
                    $("#valor_u").val();
                    $("#tipo_geracao").val(0);
                })
                carneBoletos = [];
                function montaTabela(nome_socio, carne, tipo_socio, telefone, datasVencimento) {
                    $(".detalhes_unico").html("");
                    $("#btn_wpp").css("display", "inline-block");
                    referenciaAccordion = nome_socio.replace(/[^a-zA-Zs]/g, "") + Math.round(Math.random() * 100000000);
                    let i;
                    var tabela = ``;
                    var qtd_parcelas = carne[0].qtdBoletos;
                    // var link_wpp;
                    //var texto = `Olá, ${nome_socio.split(" ")[0]}. Muito obrigado por estar contribuindo com a nossa organização! Confira seus boletos:`;
                    for (i = 0; i < qtd_parcelas; i++) {
                        tabela += `<tr><td>${i + 1}/${qtd_parcelas}</td><td>${datasVencimento[i]}</td><td>${tipo_socio}</td><td>${carne[0].valor}</td><tr>`;
                        //  texto += `\nParcela (${i+1}/${qtd_parcelas} - ${datasVencimento[i]}): ${boleto.checkoutUrl}`;
                    }
                    // texto = window.encodeURIComponent(texto);
                    // link_wpp = `https://api.whatsapp.com/send?phone=55${telefone.replace(/\D/g, "")}&text=${texto}`;
                    $(".detalhes_unico").append(`
                                     <br>
                                    <div class="card-body">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                <th>Parcela</th>
                                                <th>Data de vencimento</th>
                                                <th>Tipo de carnê</th>
                                                <th>Valor</th>
                                            </tr>
                                            </thead>
                                            <tbody>${tabela}</tbody>
                                        </table>
                                    </div>
                                    `)
                    // $("#btn_wpp").click(function(event){
                    //     var win = window.open(link_wpp, '_blank');
                    //     win.focus();
                    // })
                }

                function baixarRemessa(nomeArq) {

                    var link = document.createElement('a');
                    link.setAttribute("href", "./download_remessa.php?file=" + nomeArq);

                    link.style.display = "none";
                    link.click();
                }

                function CadastraCobrancas(carneBoletos, id, tipo) {

                    var arrayDataVencimentoIni = carneBoletos[0].dataVencimentoIni.split('/');
                    var diaVencIni = arrayDataVencimentoIni[0];
                    var mesVencIni = arrayDataVencimentoIni[1];
                    var anoVencIni = arrayDataVencimentoIni[2];
                    var DataVencIni = anoVencIni + mesVencIni + diaVencIni;
                    var arrayDataVencimentoFin = carneBoletos[0].dataVencimentoFin.split('/');
                    var diaVencIni = arrayDataVencimentoFin[0];
                    var mesVencIni = arrayDataVencimentoFin[1];
                    var anoVencIni = arrayDataVencimentoFin[2];
                    var DataVencFin = anoVencIni + mesVencIni + diaVencIni;

                    /*for(const [i, boleto] of carneBoletos.entries()){
                    
                        $.post("./cadastro_cobrancas_geracao.php",{
                            "codigo": boleto.codigo,
                            "descricao": "O Lar Abrigo Amor a Jesus, agradece sua contribuição ao Projeto Sócio  Amigos do Laje.Deus abençoe!",
                            "id_socio": id,
                            "data_vencimento": DataVencFin,
                            "data_emissao": boleto.dataAtual,
                            "data_pagamento": '0000-00-00',
                            "valor": boleto.valor,
                            "valor_pago": 0.00,
                            "status": "Agurdando Pagamento",
                            "link_cobranca": "",//boleto.checkoutUrl,
                            "link_boleto":  "", //boleto.installmentLink,
                            "linha_digitavel": "" //boleto.billetDetails.barcodeNumber
                        })
                    }*/
                    for (const [i, boleto] of carneBoletos.entries()) {

                        valor = parseFloat(boleto.valor)
                        $.ajax({
                            url: "./cadastro_remessas_geracao.php",
                            type: "POST",
                            data: {
                                "codigo": boleto.codigo,
                                "id_socio": id,
                                "data_vencimento_inicial": DataVencIni,
                                "data_vencimento_final": DataVencFin,
                                "data_emissao": boleto.dataAtual,
                                "nosso_num_seq": boleto.sequencial_final,
                                "tipo_carne": tipo,
                                "quantidade_boletos": boleto.qtdBoletos,
                                "valor": parseFloat(boleto.valor)
                            },
                            async: false, // Tornar a solicitação síncrona.
                            success: function (res) {
                                console.log(res)
                                baixarRemessa(boleto.filename);
                            },
                            error: function (xhr, status, error) {
                                console.log("Ocorreu um erro no cadastro da remessa: ", error, status)
                            }
                        });
                    }
                }

                function geraRef(socioNome) {
                    return socioNome.replace(/[^a-zA-Zs]/g, "").replace(" ", "") + Math.round(Math.random() * 100000000);
                }
                function verificaCpf(cpf) {
                    let padrao_cpf = /^(\d{3}\.){2}\d{3}-\d{2}$/;
                    // Se o texto corresponder ao padrão do cpf, retornará true. Caso contrario, false.
                    return cpf.match(padrao_cpf) != null;

                }
                function somaMes(dataV_formatada, meses) {
                    let d = new Date(dataV_formatada[0], dataV_formatada[1], dataV_formatada[2]); // Ano/mês/dia
                    let ano = d.getFullYear();
                    let mes = d.getMonth() + meses;
                    let dia = d.getDate();

                    // Verifica se a soma dos meses ultrapassou dezembro
                    if (mes > 12) {
                        ano += Math.floor((mes - 1) / 12);
                        mes = (mes - 1) % 12 + 1;
                    }
                    dia = ("0" + dia).slice(-2).toString();
                    mes = ("0" + mes).slice(-2).toString();
                    ano = ano.toString();
                    return [ano, mes, dia];
                }

                function gerarBoleto(qtdMeses, tipoCarne) {
                    var inputValor = $("#valor_u").val();
                    var inputParcelas = $("#num_parcelas").val();
                    var inputData = $("#data_vencimento").val();
                    inputValor = inputValor.replace(",", ".");
                    valorCobrado = parseFloat(inputValor);
                    valorCobrado = valorCobrado.toFixed(2);

                    var teste = inputData.split('-');
                    teste[2] = ("0" + teste[2]).slice(-2);
                    teste[1] = ("0" + teste[1]).slice(-2);
                    var dataV = teste[2] + "/" + teste[1] + "/" + teste[0];
                    var datasArray = [];
                    datasArray.push(dataV);

                    var dataV_formatada = inputData.split('-');

                    var parcelas = inputParcelas;

                    verificaCpf(socio.cpf) ? tipo_inscricao = 1 : tipo_inscricao = 2;
                    for (i = 0; i < parcelas - 1; i++) {
                        dataV_formatada = somaMes(dataV_formatada, qtdMeses);
                        dataV = dataV_formatada[2] + "/" + dataV_formatada[1] + "/" + dataV_formatada[0];
                        datasArray.push(dataV);
                    }

                    $.ajax({
                        url: "geracao_remessa.php",
                        type: "POST",
                        data: {
                            dataVencimento: datasArray,
                            tipo_inscricao: tipo_inscricao,
                            valor: valorCobrado,
                            nome: socio.nome,
                            cidade: socio.cidade,
                            logradouro: socio.logradouro,
                            bairro: socio.bairro,
                            numero_end: socio.numero_endereco,
                            estado: socio.estado,
                            cep: socio.cep,
                            complemento: socio.complemento,
                            cpf_cnpj: socio.cpf
                        },
                        async: false, // Torna a solicitação síncrona
                        success: function (dadosBoleto) {
                            dadosBoleto = JSON.parse(dadosBoleto)

                            if (dadosBoleto.parar == true) {
                                alert(`A quantidade máxima de boletos foi gerada. O boleto atual não pôde ser feito. Espere até amanhã para continuar.`)
                            }
                            else {
                                carneBoletos.push(dadosBoleto);
                                CadastraCobrancas(carneBoletos, socio.id_socio, qtdMeses);
                                console.log(carneBoletos);
                                montaTabela(socio.nome, carneBoletos, `${tipoCarne}`, socio.telefone, datasArray);
                                carneBoletos = [];
                            }
                        },
                        error: function () {
                            alert(`Houve um erro ao gerar o carnê de ${socio.nome}, verifique se os dados são válidos ou entre em contato com um administrador.`)

                        }
                    });



                    /*
                    $.ajax({
                        type: "GET",
                        url: `${apiData.api}token=${apiData.token_api}&description=${apiData.agradecimento}&amount=${$("#valor_u").val()}&dueDate=${dataV}&maxOverdueDays=${apiData.max_dias_venc}&installments=1&payerName=${socio.nome}&payerCpfCnpj=${socio.cpf}&payerEmail=${socio.email}&payerPhone=${socio.telefone}&billingAddressStreet=${socio.logradouro}&billingAddressNumber=${socio.numero_endereco}&billingAddressComplement=${socio.complemento}&billingAddressNeighborhood=${socio.bairro}&billingAddressCity=${socio.cidade}&billingAddressState=${socio.estado}&billingAddressPostcode=${socio.cep}&fine=${apiData.multa}&interest=${apiData.juros}&paymentTypes=BOLETO&notifyPayer=TRUE&reference=${geraRef(socio.nome)}`,
                        async: false,
                        success : function(dadosBoleto) {
                            for(boleto of dadosBoleto.data.charges){
                                carneBoletos.push(boleto);
                            }
                        },
                        error : function(){
                            alert(`Houve um erro ao gerar o carnê de ${socio.nome}, verifique se os dados são válidos ou entre em contato com um administrador.`)
                        }
                    })
                    */
                }



                var apiData = JSON.parse(dados_api)[0];

                for (socio of socios) {
                    switch (tipo) {
                        case 0:
                            gerarBoleto(0, "casual (avulso)");
                            break;
                        case 1:
                            gerarBoleto(1, "mensal");
                            break;
                        case 2:
                            gerarBoleto(2, "bimestral");
                            break;
                        case 3:
                            gerarBoleto(3, "trimestral");
                            break;
                        case 6:
                            gerarBoleto(6, "semestral");
                            break;

                    }
                }
                $(".box-geracaounica .overlay").remove();
            })
        $(this).off();
        $(this).attr("disabled", true);
        $(this).text("Gerado com sucesso, escolha um novo sócio e aperte continuar para gerar mais");
    }
})
