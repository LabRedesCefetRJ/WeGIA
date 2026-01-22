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
                        var dataV_formatada = data_inicial;

                        var arrayDataSegmentsA = dataV_formatada.split('-');
                        let mesAA = parseInt(arrayDataSegmentsA[1]) - 1;

                        let total = 0;

                        for (i = 0; i < parcelas; i++) {

                            let data = new Date(arrayDataSegmentsA[0], mesAA, arrayDataSegmentsA[2]);

                            //Incrementar meses
                            data.setMonth(data.getMonth() + i * periodicidade_socio);

                            if (data.getDate() != arrayDataSegmentsA[2]) {
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
                            $("#escolha-modo").css("display", "none");
                        }
                        else {
                            $("#num_parcelas").val();
                            $("#num_parcelas").prop('disabled', false);
                            $("#escolha-modo").css("display", "block");
                        }
                    })

                    $("#btn_confirma").click(function () {

                        let inputParcelas = $("#num_parcelas").val();
                        let inputData = $("#data_vencimento").val();
                        let inputValor = $("#valor_u").val();
                        let tipo_boleto = $("#tipo_geracao").val();
                        $("#btn_geracao_unica").css("display", "inline");

                        tipo = Number(tipo_boleto);

                        const modo = $('input[name="escolha-modo"]:checked').val();
                        if (modo === 'fim-ano') {
                            inputParcelas = calcularParcelasAteFinalAno(inputData, tipo);
                        }

                        console.log('Parcelas: ', inputParcelas);

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

                        switch (tipoGeracao) {
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
                        const csrfToken = document.getElementsByName('csrf_token').item(0).value;
                        let parcela = document.getElementById('num_parcelas').value;

                        //verificar se fim-ano está selecionado, caso esteja, o número de parcelas deve ser calculado automaticamente
                        const modo = $('input[name="escolha-modo"]:checked').val();
                        if (modo === 'fim-ano') {
                            parcela = calcularParcelasAteFinalAno(dia, tipo);
                        }

                        const cpfCnpj = socio.split('|')[1];

                        console.log(dia);

                        $.post(url, {
                            "documento_socio": cpfCnpj,
                            "valor": valor,
                            "dia": dia,
                            "parcelas": parcela,
                            "tipoGeracao": tipoGeracao,
                            "csrf_token": csrfToken
                        }).done(function (r) {
                            const resposta = JSON.parse(r);
                            if (resposta.link) {
                                console.log(resposta.link);
                                // Redirecionar o usuário para o link do boleto em uma nova aba
                                window.open(resposta.link, '_blank');
                            } else if (resposta.erro) {
                                alert('Erro: ' + resposta.erro);
                            } else {
                                alert("Ops! Ocorreu um problema na geração da sua forma de pagamento, tente novamente, se o erro persistir contate o suporte.");
                            }

                            btnGeracaoUnica.disabled = false;
                        }).fail(function (xhr, status, errorThrown) {
                            console.error("Erro AJAX:", status, errorThrown);
                            alert("Erro no servidor: " + (xhr.responseText || "Erro desconhecido"));
                        }).always(function () {
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

    })

    /**
 * Calcula quantas parcelas cabem até o final do ano
 * @param {string} dataInicial Data da primeira parcela
 * @param {string|number} periodicidade Pode ser 'mensal', 'bimestral', 'trimestral', 'semestral' ou um número de meses
 * @returns {number} Quantidade de parcelas até 31 de dezembro
 */
    function calcularParcelasAteFinalAno(dataStr, periodicidade) {
        const periodicidades = {
            mensal: 1,
            bimestral: 2,
            trimestral: 3,
            semestral: 6
        };

        // Normaliza periodicidade
        let intervalo;
        if (typeof periodicidade === "string") {
            intervalo = periodicidades[periodicidade.toLowerCase()];
            if (!intervalo) {
                throw new Error("Periodicidade inválida. Use mensal, bimestral, etc.");
            }
        } else if (typeof periodicidade === "number") {
            intervalo = parseInt(periodicidade);
            if (isNaN(intervalo) || intervalo <= 0 || intervalo > 12) {
                throw new Error("Periodicidade numérica inválida.");
            }
        } else {
            throw new Error("Periodicidade em formato inválido.");
        }

        //
        const [ano, mes, dia] = dataStr.split("-").map(Number);

        console.log('Mês inicial:', mes);

        let mesesRestantes = 12 - mes; // Até dezembro
        let parcelas = 1; // Conta a primeira parcela

        console.log('Meses restantes: ', mesesRestantes);

        while (mesesRestantes >= intervalo) {
            parcelas++;
            mesesRestantes -= intervalo;
        }

        return parcelas;
    }

    //console.log('Número de parcelas: ', calcularParcelasAteFinalAno('2025-01-28', 6));

    function toggleParcelas() {
        console.log("Togle ativado");
        const valor = $('input[name="escolha-modo"]:checked').val();
        if (valor === 'personalizado') {
            $('#parcelas-quantidade').show();
        } else {
            $('#parcelas-quantidade').hide();
        }
    }

    // Inicializa no carregamento
    toggleParcelas();

    // Atualiza ao mudar seleção
    $('input[name="escolha-modo"]').on('change', toggleParcelas);
})
