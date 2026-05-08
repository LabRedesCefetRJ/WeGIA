$(document).ready(function(){
    // Iniciando
    // Funcões do expansable
    // geracao multipla
    // $("#btn_geracao_auto").attr("disabled", true);
    // function procurar_desejados(tipo_desejado){
    //     switch(Number(tipo_desejado)){
    //         case 0: var td = "2,3,6,7,8,9,10,11"; break; //Todos
    //         case 1: var td = "6,7"; break; //Bimestrais
    //         case 2: var td = "8,9"; break; //Trimestrais
    //         case 3: var td = "10,11"; break; //Semestrais
    //         case 4: var td = "2,3"; break; //Mensais
    //     }
    //     $.post("./controller/query_geracao_auto.php", {
    //         "query": `SELECT * FROM socio s JOIN pessoa p ON p.id_pessoa = s.id_pessoa WHERE s.id_sociostatus NOT IN (1,2,3,4) AND s.id_sociotipo in (${td}) AND s.valor_periodo <> '' AND s.data_referencia <> '0000-00-00'`
    //     })
    //         .done(function(dados){
    //             var socios = JSON.parse(dados);
    //             console.log(socios.length, socios);
    //             var estimacao_tempo_conclusao = socios.length / 1.737 >= 60 ? ((socios.length / 1.737)/60).toFixed(0) + " minuto(s) : " + ((socios.length / 1.737)%60).toFixed(0) + " segundo(s) para geração." : (socios.length / 1.737).toFixed(2) + " segundo(s) para geração.";
    //             $("#estimacao_tempo").html(estimacao_tempo_conclusao);

    //             if(socios){
    //                 $("#btn_geracao_auto").attr("disabled", false);
    //                 $("#btn_geracao_auto").click(function(event){
    //                     $("#btn_geracao_auto").attr("disabled", true);
    //                     $("#estimacao_tempo").css("color", "orange");
    //                     $(".box-geracao").prepend('<div class="overlay"> <i class="fa fa-refresh fa-spin"></i> </div>');
    //                     $.post("./controller/query_geracao_auto.php",{
    //                         "query": `SELECT * FROM doacao_boleto_info AS bi JOIN sistema_pagamento AS sp ON (bi.id_sistema = sp.id) JOIN doacao_boleto_regras AS br ON (br.id = bi.id_regras)  WHERE nome_sistema = 'BOLETOFACIL'`
    //                     })
    //                         .done(function(dados_api){
    //                             carneBoletos = [];
    //                             function montaTabela(nome_socio, carne, tipo_socio){
    //                                 referenciaAccordion = nome_socio.replace(/[^a-zA-Zs]/g, "") + Math.round(Math.random()*100000000);
    //                                 console.log(nome_socio, tipo_socio, carne);
    //                                 var tabela = ``;
    //                                 var qtd_parcelas = carne.length;
    //                                 for(const [i, boleto] of carne.entries()){
    //                                     tabela += `<tr><td>${i+1}/${qtd_parcelas}</td><td>${boleto.dueDate}</td><td><a target='_blank' href='${boleto.installmentLink}'>${boleto.installmentLink}</a></td><td>${boleto.payNumber}</td><tr>`;
    //                                 }
    //                                 if(tipo_socio == "mensal"){
    //                                     tabela += `<tr><td colspan='2'>Link carnê completo:</td><td colspan='2'><a target='_blank' href='${carne[0].link}'>${carne[0].link}</a></td></tr>`;
    //                                 }
    //                                 $(".detalhes").append(`
    //                                 <div class="accordion" id="accordionExample">
    //                                 <div class="card">
    //                                   <div class="card-header" id="headingThree">
    //                                     <h2 class="mb-0">
    //                                       <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse${referenciaAccordion}" aria-expanded="false" aria-controls="collapseThree">
    //                                         ${nome_socio} - Sócio ${tipo_socio}
    //                                       </button>
    //                                     </h2>
    //                                   </div>
    //                                   <div id="collapse${referenciaAccordion}" class="collapse" aria-labelledby="headingThree" data-parent="#accordionExample">
    //                                     <div class="card-body">
    //                                     <table class="table table-striped table-hover">
    //                                     <thead>
    //                                         <tr>
    //                                           <th>Parcela</th>
    //                                           <th>Data de vencimento</th>
    //                                           <th>Link parcela</th>
    //                                           <th>Código de pagamento</th>
    //                                       </tr>
    //                                   </thead>
    //                                   <tbody>${tabela}</tbody>
    //                               </table>
    //                                     </div>
    //                                   </div>
    //                                 </div>
    //                               </div>
    //                                 `)
    //                             }
    //                             function geraRef(socioNome){
    //                                 return socioNome.replace(/[^a-zA-Zs]/g, "").replace(" ", "") + Math.round(Math.random()*100000000);
    //                             }
    //                             function gerarDataParcelas(Now, tipo, dia_preferencial){
    //                                 var NovaData = new Date(Now);
    //                                 var fimDoAno = new Date(String(Now.getFullYear()), '11', '31');
    //                                 var proximoAno = new Date(String(Now.getFullYear() + 1), '11', '31');
    //                                 var retornoDatas = {
    //                                     dataV: null,
    //                                     dataV_formatada: null,
    //                                     parcelas: null
    //                                 };
    //                                 if(tipo == 1){
    //                                     var parcelas = 0;
    //                                     while((NovaData.getMonth() + 1) % 2 != 0){
    //                                         NovaData.setMonth(NovaData.getMonth() + 1);
    //                                     }
                                        
    //                                     if(Now.getMonth() == NovaData.getMonth() && dia_preferencial <= Now.getDate()){
    //                                         NovaData.setMonth(NovaData.getMonth() + 2);
    //                                     }
    //                                     var Mes = NovaData.getMonth() + 1;
    //                                     if(Mes <= 6){
    //                                         parcelas = 7 - Math.floor(Mes/2);
    //                                     }else{
    //                                         for(var dataHoje = new Date(NovaData); dataHoje < proximoAno; dataHoje.setMonth(dataHoje.getMonth() + 2)){
    //                                             parcelas++;
    //                                         }
    //                                     }
    //                                 }else if(tipo == 2){
    //                                     var parcelas = 0;
    //                                     while((NovaData.getMonth() + 1) % 3 != 0){
    //                                         NovaData.setMonth(NovaData.getMonth() + 1);
    //                                     }
                                        
    //                                     if(Now.getMonth() == NovaData.getMonth() && dia_preferencial <= Now.getDate()){
    //                                         NovaData.setMonth(NovaData.getMonth() + 3);
    //                                     }
    //                                     var Mes = NovaData.getMonth() + 1;
    //                                     if(Mes <= 6){
    //                                         parcelas = 5 - Math.floor(Mes/3);
    //                                     }else{
    //                                         for(var dataHoje = new Date(NovaData); dataHoje < proximoAno; dataHoje.setMonth(dataHoje.getMonth() + 3)){
    //                                             parcelas++;
    //                                         }
    //                                     }
    //                                 }else if(tipo == 3){
    //                                     var parcelas = 0;
    //                                     while((NovaData.getMonth() + 1) % 6 != 0){
    //                                         NovaData.setMonth(NovaData.getMonth() + 1);
    //                                     }
                                        
    //                                     if(Now.getMonth() == NovaData.getMonth() && dia_preferencial <= Now.getDate()){
    //                                         NovaData.setMonth(NovaData.getMonth() + 6);
    //                                     }
    //                                     var Mes = NovaData.getMonth() + 1;
    //                                     if(Mes <= 6){
    //                                         parcelas = 3 - Math.floor(Mes/6);
    //                                     }else{
    //                                         for(var dataHoje = new Date(NovaData); dataHoje < proximoAno; dataHoje.setMonth(dataHoje.getMonth() + 6)){
    //                                             parcelas++;
    //                                         }
    //                                     }
    //                                 }else{
    //                                     var parcelas = 0;
    //                                     if(dia_preferencial <= Now.getDate()){
    //                                         NovaData.setMonth(NovaData.getMonth() + 1);
    //                                     }
    //                                     Mes = NovaData.getMonth() + 1;
    //                                     if(Mes <= 6){
    //                                         parcelas = 13 - Mes;
    //                                     }else{
    //                                         for(var dataHoje = new Date(Now); dataHoje < proximoAno; dataHoje.setMonth(dataHoje.getMonth() + 1)){
    //                                             parcelas++;
    //                                         }
    //                                     }
    //                                 }
    //                                 retornoDatas.dataV = (String(`${dia_preferencial}/${NovaData.getMonth() + 1}/${NovaData.getFullYear()}`));
    //                                 retornoDatas.dataV_formatada = (String(`${NovaData.getFullYear()}-${NovaData.getMonth()}-${dia_preferencial}`));
    //                                 retornoDatas.parcelas = parcelas;
    //                                 return retornoDatas;
    //                             }
    //                             var apiData = JSON.parse(dados_api)[0];
    //                             for(socio of socios){
    //                                 switch(Number(socio.id_sociotipo)){
    //                                     case 2: case 3:
    //                                         var Now = new Date();
    //                                         var dadosDataParcelas = gerarDataParcelas(Now, 4, socio.data_referencia.split("-")[2]);
    //                                         var dataV = dadosDataParcelas['dataV'];
    //                                         var dataV_formatada = dadosDataParcelas['dataV_formatada'];
    //                                         var parcelas = dadosDataParcelas['parcelas'];
    //                                         $.ajax({
    //                                             type: "GET",
    //                                             url: `${apiData.api}token=${apiData.token_api}&description=${apiData.agradecimento}&amount=${socio.valor_periodo}&dueDate=${dataV}&maxOverdueDays=${apiData.max_dias_venc}&installments=${parcelas}&payerName=${socio.nome}&payerCpfCnpj=${socio.cpf}&payerEmail=${socio.email}&payerPhone=${socio.telefone}&billingAddressStreet=${socio.logradouro}&billingAddressNumber=${socio.numero_endereco}&billingAddressComplement=${socio.complemento}&billingAddressNeighborhood=${socio.bairro}&billingAddressCity=${socio.cidade}&billingAddressState=${socio.estado}&billingAddressPostcode=${socio.cep}&fine=${apiData.multa}&interest=${apiData.juros}&paymentTypes=BOLETO&notifyPayer=TRUE&reference=${geraRef(socio.nome)}`,
    //                                             async: false,
    //                                             success : function(dadosBoleto) {
    //                                                 for(boleto of dadosBoleto.data.charges){
    //                                                     carneBoletos.push(boleto);
    //                                                 }
    //                                             },
    //                                             error : function(){
    //                                                 alert(`Houve um erro ao gerar o carnê de ${socio.nome}, verifique se os dados são válidos ou entre em contato com um administrador.`)
    //                                             }
    //                                         });
    //                                         montaTabela(socio.nome, carneBoletos, 'mensal');
    //                                         carneBoletos = [];
    //                                     break;
    //                                     case 6: case 7:
    //                                         var Now = new Date();
    //                                         var dadosDataParcelas = gerarDataParcelas(Now, 1, socio.data_referencia.split("-")[2]);
    //                                         var dataV = dadosDataParcelas['dataV'];
    //                                         var dataV_formatada = dadosDataParcelas['dataV_formatada'];
    //                                         var parcelas = dadosDataParcelas['parcelas'];
    //                                         for(i = 0; i < parcelas; i++){
    //                                             $.ajax({
    //                                                 type: "GET",
    //                                                 url: `${apiData.api}token=${apiData.token_api}&description=${apiData.agradecimento}&amount=${socio.valor_periodo}&dueDate=${dataV}&maxOverdueDays=${apiData.max_dias_venc}&installments=1&payerName=${socio.nome}&payerCpfCnpj=${socio.cpf}&payerEmail=${socio.email}&payerPhone=${socio.telefone}&billingAddressStreet=${socio.logradouro}&billingAddressNumber=${socio.numero_endereco}&billingAddressComplement=${socio.complemento}&billingAddressNeighborhood=${socio.bairro}&billingAddressCity=${socio.cidade}&billingAddressState=${socio.estado}&billingAddressPostcode=${socio.cep}&fine=${apiData.multa}&interest=${apiData.juros}&paymentTypes=BOLETO&notifyPayer=TRUE&reference=${geraRef(socio.nome)}`,
    //                                                 async: false,
    //                                                 success : function(dadosBoleto) {
    //                                                     for(boleto of dadosBoleto.data.charges){
    //                                                         carneBoletos.push(boleto);
    //                                                     }
    //                                                 },
    //                                                 error : function(){
    //                                                     alert(`Houve um erro ao gerar o carnê de ${socio.nome}, verifique se os dados são válidos ou entre em contato com um administrador.`)
    //                                                 }
    //                                             });
    //                                             var arrayDataSegments = dataV_formatada.split('-');
    //                                             var novaData = new Date(arrayDataSegments[0], arrayDataSegments[1], arrayDataSegments[2]);
    //                                             novaData.setMonth(novaData.getMonth() + 2);
    //                                             dataV_formatada = `${novaData.getFullYear()}-${novaData.getMonth()}-${novaData.getDate()}`;
    //                                             dataV = `${novaData.getDate()}/${novaData.getMonth()+1}/${novaData.getFullYear()}`;
    //                                         }
    //                                         montaTabela(socio.nome, carneBoletos, 'bimestral');
    //                                         carneBoletos = [];
    //                                     break;
    //                                     case 8: case 9:
    //                                         var Now = new Date();
    //                                         var dadosDataParcelas = gerarDataParcelas(Now, 2, socio.data_referencia.split("-")[2]);
    //                                         var dataV = dadosDataParcelas['dataV'];
    //                                         var dataV_formatada = dadosDataParcelas['dataV_formatada'];
    //                                         var parcelas = dadosDataParcelas['parcelas'];
    //                                         for(i = 0; i < parcelas; i++){
    //                                             $.ajax({
    //                                                 type: "GET",
    //                                                 url: `${apiData.api}token=${apiData.token_api}&description=${apiData.agradecimento}&amount=${socio.valor_periodo}&dueDate=${dataV}&maxOverdueDays=${apiData.max_dias_venc}&installments=1&payerName=${socio.nome}&payerCpfCnpj=${socio.cpf}&payerEmail=${socio.email}&payerPhone=${socio.telefone}&billingAddressStreet=${socio.logradouro}&billingAddressNumber=${socio.numero_endereco}&billingAddressComplement=${socio.complemento}&billingAddressNeighborhood=${socio.bairro}&billingAddressCity=${socio.cidade}&billingAddressState=${socio.estado}&billingAddressPostcode=${socio.cep}&fine=${apiData.multa}&interest=${apiData.juros}&paymentTypes=BOLETO&notifyPayer=TRUE&reference=${geraRef(socio.nome)}`,
    //                                                 async: false,
    //                                                 success : function(dadosBoleto) {
    //                                                     for(boleto of dadosBoleto.data.charges){
    //                                                         carneBoletos.push(boleto);
    //                                                     }
    //                                                 },
    //                                                 error : function(){
    //                                                     alert(`Houve um erro ao gerar o carnê de ${socio.nome}, verifique se os dados são válidos ou entre em contato com um administrador.`)
    //                                                 }
    //                                             });
    //                                             var arrayDataSegments = dataV_formatada.split('-');
    //                                             var novaData = new Date(arrayDataSegments[0], arrayDataSegments[1], arrayDataSegments[2]);
    //                                             novaData.setMonth(novaData.getMonth() + 3);
    //                                             dataV_formatada = `${novaData.getFullYear()}-${novaData.getMonth()}-${novaData.getDate()}`;
    //                                             dataV = `${novaData.getDate()}/${novaData.getMonth()+1}/${novaData.getFullYear()}`;
    //                                         }
    //                                         montaTabela(socio.nome, carneBoletos, 'trimestral');
    //                                         carneBoletos = [];
    //                                     break;
    //                                     case 10: case 11:
    //                                         var Now = new Date();
    //                                         var dadosDataParcelas = gerarDataParcelas(Now, 3, socio.data_referencia.split("-")[2]);
    //                                         var dataV = dadosDataParcelas['dataV'];
    //                                         var dataV_formatada = dadosDataParcelas['dataV_formatada'];
    //                                         var parcelas = dadosDataParcelas['parcelas'];
    //                                         for(i = 0; i < parcelas; i++){
    //                                             $.ajax({
    //                                                 type: "GET",
    //                                                 url: `${apiData.api}token=${apiData.token_api}&description=${apiData.agradecimento}&amount=${socio.valor_periodo}&dueDate=${dataV}&maxOverdueDays=${apiData.max_dias_venc}&installments=1&payerName=${socio.nome}&payerCpfCnpj=${socio.cpf}&payerEmail=${socio.email}&payerPhone=${socio.telefone}&billingAddressStreet=${socio.logradouro}&billingAddressNumber=${socio.numero_endereco}&billingAddressComplement=${socio.complemento}&billingAddressNeighborhood=${socio.bairro}&billingAddressCity=${socio.cidade}&billingAddressState=${socio.estado}&billingAddressPostcode=${socio.cep}&fine=${apiData.multa}&interest=${apiData.juros}&paymentTypes=BOLETO&notifyPayer=TRUE&reference=${geraRef(socio.nome)}`,
    //                                                 async: false,
    //                                                 success : function(dadosBoleto) {
    //                                                     for(boleto of dadosBoleto.data.charges){
    //                                                         carneBoletos.push(boleto);
    //                                                     }
    //                                                 },
    //                                                 error : function(){
    //                                                     alert(`Houve um erro ao gerar o carnê de ${socio.nome}, verifique se os dados são válidos ou entre em contato com um administrador.`)
    //                                                 }
    //                                             });
    //                                             var arrayDataSegments = dataV_formatada.split('-');
    //                                             var novaData = new Date(arrayDataSegments[0], arrayDataSegments[1], arrayDataSegments[2]);
    //                                             novaData.setMonth(novaData.getMonth() + 6);
    //                                             dataV_formatada = `${novaData.getFullYear()}-${novaData.getMonth()}-${novaData.getDate()}`;
    //                                             dataV = `${novaData.getDate()}/${novaData.getMonth()+1}/${novaData.getFullYear()}`;
    //                                         }
    //                                         montaTabela(socio.nome, carneBoletos, 'semestral');
    //                                         carneBoletos = [];
    //                                     break;

    //                                 }
    //                             }
    //                             $(".box-geracao .overlay").remove();
    //                             $("#estimacao_tempo").css("color", "green").html("Geração concluída!");
    //                         })
    //                         event.stopPropagation();
    //                         $(this).off();
    //                 });
    //             }else{
    //                 console.log("SEM SÓCIOS DA CATEGORIA.");
    //                 $("#btn_geracao_auto").attr("disabled", true);
    //             } 
    //         })
    //         .fail(function(dados){
    //             alert("Erro na obtenção de dados.");
    //         })
    // }




    // Geração para sócio único



    function procurar_desejado(id_socio){
        $.post("./controller/query_geracao_auto.php", {
            "query": `SELECT * FROM socio s JOIN pessoa p ON p.id_pessoa = s.id_pessoa WHERE s.id_sociostatus NOT IN (1,2,3,4) AND s.id_socio = ${id_socio}`
        })
            .done(function(dados){
                var socios = JSON.parse(dados);
                if(socios){
                    function montaTabelaInicial(data_inicial, data_inicial_br, periodicidade_socio, parcelas, valor, nome_socio){
                        
                        function dataAtualFormatada(data_r){
                            var data = new Date(data_r),
                                dia  = data.getDate().toString(),
                                diaF = (dia.length == 1) ? '0'+dia : dia,
                                mes  = (data.getMonth()+1).toString(), //+1 pois no getMonth Janeiro começa com zero.
                                mesF = (mes.length == 1) ? '0'+mes : mes,
                                anoF = data.getFullYear();
                            return diaF+"/"+mesF+"/"+anoF;
                        }

                        $(".detalhes_unico").html("");
                        $("#btn_wpp").off();
                        $("#btn_wpp").css("display", "none");
                        $("#btn_geracao_unica").attr("disabled", false);
                        $("#btn_geracao_unica").text("Confirmar geração");
                        console.log(data_inicial, periodicidade_socio, parcelas, valor);
                        console.log("parcelas:"+parcelas);
                        referenciaAccordion = nome_socio.replace(/[^a-zA-Zs]/g, "") + Math.round(Math.random()*100000000);
                        var tabela = ``;
                        var dataV = data_inicial_br;
                        var dataV_formatada = data_inicial;
                        var total = 0;
                        for(i = 0; i < parcelas; i++){
                            tabela += `<tr><td>${i+1}/${parcelas}</td><td>${dataV}</td><td>R$ ${valor}</td></tr>`
                            var arrayDataSegments = dataV_formatada.split('-');
                            var novaData = new Date(arrayDataSegments[0], arrayDataSegments[1], arrayDataSegments[2]);
                            novaData.setMonth(novaData.getMonth() + periodicidade_socio);
                            dataV_formatada = `${novaData.getFullYear()}-${novaData.getMonth()}-${novaData.getDate()}`;
                            dataV = `${dataAtualFormatada(novaData)}`;
                            total += valor;
                        }
                        tabela += `<tr><td colspan='2'>Total: </td><td>R$ ${total}</td></tr>`;
                        $(".detalhes_unico").append(`
                        <div class="accordion" id="accordionExample">
                        <div class="card">
                          <div class="card-header" id="headingThree">
                            <h2 class="mb-0">
                              <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse${referenciaAccordion}" aria-expanded="false" aria-controls="collapseThree">
                                Detalhes parcelas sócio atual: ${nome_socio}
                              </button>
                            </h2>
                          </div>
                          <div id="collapse${referenciaAccordion}" class="collapse" aria-labelledby="headingThree" data-parent="#accordionExample">
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
                          </div>
                        </div>
                      </div>
                        `)
                    }

                    function gerarDataParcelas(Now, tipo, dia_preferencial){
                        var NovaData = new Date(Now);
                        var fimDoAno = new Date(String(Now.getFullYear()), '11', '31');
                        var proximoAno = new Date(String(Now.getFullYear() + 1), '11', '31');
                        var retornoDatas = {
                            dataV: null,
                            dataV_formatada: null,
                            parcelas: null
                        };
                        if(tipo == 1){
                            var parcelas = 0;
                            while((NovaData.getMonth() + 1) % 2 != 0){
                                NovaData.setMonth(NovaData.getMonth() + 1);
                            }
                            
                            if(Now.getMonth() == NovaData.getMonth() && dia_preferencial <= Now.getDate()){
                                NovaData.setMonth(NovaData.getMonth() + 2);
                            }
                            var Mes = NovaData.getMonth() + 1;
                            if(Mes <= 6){
                                parcelas = 7 - Math.floor(Mes/2);
                            }else{
                                for(var dataHoje = new Date(NovaData); dataHoje < proximoAno; dataHoje.setMonth(dataHoje.getMonth() + 2)){
                                    parcelas++;
                                }
                            }
                        }else if(tipo == 2){
                            var parcelas = 0;
                            while((NovaData.getMonth() + 1) % 3 != 0){
                                NovaData.setMonth(NovaData.getMonth() + 1);
                            }
                            
                            if(Now.getMonth() == NovaData.getMonth() && dia_preferencial <= Now.getDate()){
                                NovaData.setMonth(NovaData.getMonth() + 3);
                            }
                            var Mes = NovaData.getMonth() + 1;
                            if(Mes <= 6){
                                parcelas = 5 - Math.floor(Mes/3);
                            }else{
                                for(var dataHoje = new Date(NovaData); dataHoje < proximoAno; dataHoje.setMonth(dataHoje.getMonth() + 3)){
                                    parcelas++;
                                }
                            }
                        }else if(tipo == 3){
                            var parcelas = 0;
                            while((NovaData.getMonth() + 1) % 6 != 0){
                                NovaData.setMonth(NovaData.getMonth() + 1);
                            }
                            
                            if(Now.getMonth() == NovaData.getMonth() && dia_preferencial <= Now.getDate()){
                                NovaData.setMonth(NovaData.getMonth() + 6);
                            }
                            var Mes = NovaData.getMonth() + 1;
                            if(Mes <= 6){
                                parcelas = 3 - Math.floor(Mes/6);
                            }else{
                                for(var dataHoje = new Date(NovaData); dataHoje < proximoAno; dataHoje.setMonth(dataHoje.getMonth() + 6)){
                                    parcelas++;
                                }
                            }
                        }else if(tipo == 6){
                            var parcelas = 1;
                            diaAtual = Now.getDate() + 3;
                            if(diaAtual == 29 || diaAtual == 30 || diaAtual == 31)
                            {
                                diaAtual = 3;
                                var Mes = NovaData.getMonth() + 2;
                            }
                            dia_preferencial = diaAtual;
                            console.log(NovaData);
                        }else{
                            var parcelas = 0;
                            if(dia_preferencial <= Now.getDate()){
                                NovaData.setMonth(NovaData.getMonth() + 1);
                            }
                            Mes = NovaData.getMonth() + 1;
                            if(Mes <= 6){
                                parcelas = 13 - Mes;
                            }else{
                                for(var dataHoje = new Date(Now); dataHoje < proximoAno; dataHoje.setMonth(dataHoje.getMonth() + 1)){
                                    parcelas++;
                                }
                            }
                        }
                        Number.prototype.zeroAntes = function() {
                            return (this < 10 ? '0' : '') + this;
                        }
                        retornoDatas.dataV = (String(`${dia_preferencial}/${(NovaData.getMonth() + 1).zeroAntes()}/${NovaData.getFullYear()}`));
                        retornoDatas.dataV_formatada = (String(`${NovaData.getFullYear()}-${(NovaData.getMonth()).zeroAntes()}-${dia_preferencial}`));
                        retornoDatas.dataV_formatada_v2 = (String(`${NovaData.getFullYear()}-${(NovaData.getMonth() + 1).zeroAntes()}-${dia_preferencial}`));
                        retornoDatas.parcelas = parcelas;
                        console.log(retornoDatas);
                        console.log(dia_preferencial);
                        return retornoDatas;
                    }

                    console.log(socios.length, socios);
                    $(".configs_unico").css("display", "block");
                    console.log(socios[0].id_sociotipo);
                    var tipo;
                    var periodicidade_socio;
                    if(socios[0].id_sociotipo != 4 && socios[0].id_sociotipo != 5){
                        switch(Number(socios[0].id_sociotipo)){
                            case 0: case 1: 
                                $("#tipo_geracao").val("1");
                                console.log("tes1");
                                tipo = 6;
                                periodicidade_socio = 0;
                            break;
                            case 2: case 3:
                                $("#tipo_geracao").val("2");
                                tipo = 4;
                                periodicidade_socio = 1;
                            break;
                            case 6: case 7:
                                $("#tipo_geracao").val("3");
                                tipo = 1;
                                periodicidade_socio = 2;
                            break;
                            case 8: case 9:
                                $("#tipo_geracao").val("4");
                                tipo = 2;
                                periodicidade_socio = 3;
                            break;
                            case 10: case 11:
                                $("#tipo_geracao").val("5");
                                tipo = 3;
                                periodicidade_socio = 6;
                            break;
                            default:
                                $("#tipo_geracao").val("1");
                                tipo = 6;
                                periodicidade_socio = 0;
                            break;
                        }
                    }
                    if(socios[0].valor_periodo != "" && socios[0].valor_periodo != null){
                        $("#valor_u").val(socios[0].valor_periodo);
                    }else $("#valor_u").val(30);
                    var Now = new Date();
                    var dataParcelas;
                    var data;
                    var num_parcelas;
                    var dia_preferencial;
                    if(socios[0].data_referencia != "0000-00-00" && socios[0].data_referencia != null){
                        dia_preferencial = socios[0].data_referencia.split("-")[2];
                        dataParcelas = gerarDataParcelas(Now, tipo, dia_preferencial);
                        data_formatada = dataParcelas.dataV_formatada;
                        data_formatada_br = dataParcelas.dataV;
                        num_parcelas = dataParcelas.parcelas;
                    }else{
                        dia_preferencial = 10;
                        dataParcelas = gerarDataParcelas(Now, tipo, dia_preferencial);
                        data_formatada = dataParcelas.dataV_formatada;
                        data_formatada_br = dataParcelas.dataV;
                        num_parcelas = dataParcelas.parcelas;
                    } 
                    $("#tipo_geracao").change(function(){
                            switch(Number($(this).val())){
                                case 1: 
                                    console.log("teste1");
                                    tipo = 6;
                                    dataParcelas = gerarDataParcelas(Now, tipo, 10);
                                    data_formatada = dataParcelas.dataV_formatada;
                                    data_formatada_br = dataParcelas.dataV;
                                    num_parcelas = dataParcelas.parcelas;
                                    periodicidade_socio = 0;
                                    $("#data_vencimento").val(dataParcelas.dataV_formatada_v2);
                                    montaTabelaInicial(data_formatada, data_formatada_br, periodicidade_socio, num_parcelas, Number($("#valor_u").val()), socios[0].nome);
                                break;
                                case 2:
                                    tipo = 4;
                                    dataParcelas = gerarDataParcelas(Now, tipo, 10);
                                    data_formatada = dataParcelas.dataV_formatada;
                                    data_formatada_br = dataParcelas.dataV;
                                    num_parcelas = dataParcelas.parcelas;
                                    periodicidade_socio = 1;
                                    $("#data_vencimento").val(dataParcelas.dataV_formatada_v2);
                                    montaTabelaInicial(data_formatada, data_formatada_br, periodicidade_socio, num_parcelas, Number($("#valor_u").val()), socios[0].nome);
                                break;
                                case 3:
                                    tipo = 1;
                                    dataParcelas = gerarDataParcelas(Now, tipo, 10);
                                    data_formatada = dataParcelas.dataV_formatada;
                                    data_formatada_br = dataParcelas.dataV;
                                    num_parcelas = dataParcelas.parcelas;
                                    periodicidade_socio = 2;
                                    $("#data_vencimento").val(dataParcelas.dataV_formatada_v2);
                                    montaTabelaInicial(data_formatada, data_formatada_br, periodicidade_socio, num_parcelas, Number($("#valor_u").val()), socios[0].nome);
                                break;
                                case 4:
                                    tipo = 2;
                                    dataParcelas = gerarDataParcelas(Now, tipo, 10);
                                    data_formatada = dataParcelas.dataV_formatada;
                                    data_formatada_br = dataParcelas.dataV;
                                    num_parcelas = dataParcelas.parcelas;
                                    periodicidade_socio = 3;
                                    $("#data_vencimento").val(dataParcelas.dataV_formatada_v2);
                                    montaTabelaInicial(data_formatada, data_formatada_br, periodicidade_socio, num_parcelas, Number($("#valor_u").val()), socios[0].nome);
                                break;
                                case 5:
                                    tipo = 3;
                                    dataParcelas = gerarDataParcelas(Now, tipo, 10);
                                    data_formatada = dataParcelas.dataV_formatada;
                                    data_formatada_br = dataParcelas.dataV;
                                    num_parcelas = dataParcelas.parcelas;
                                    periodicidade_socio = 6;
                                    $("#data_vencimento").val(dataParcelas.dataV_formatada_v2);
                                    montaTabelaInicial(data_formatada, data_formatada_br, periodicidade_socio, num_parcelas, Number($("#valor_u").val()), socios[0].nome);
                                break;
                                default:
                                    tipo = 6;
                                    dataParcelas = gerarDataParcelas(Now, tipo, 10);
                                    data_formatada = dataParcelas.dataV_formatada;
                                    data_formatada_br = dataParcelas.dataV;
                                    num_parcelas = dataParcelas.parcelas;
                                    periodicidade_socio = 0;
                                    $("#data_vencimento").val(dataParcelas.dataV_formatada_v2);
                                    montaTabelaInicial(data_formatada, data_formatada_br, periodicidade_socio, num_parcelas, Number($("#valor_u").val()), socios[0].nome);
                                break;
                            }
                            if(socios[0].data_referencia != "0000-00-00" && socios[0].data_referencia != null){
                                var dia_preferencial = socios[0].data_referencia.split("-")[2];
                                dataParcelas = gerarDataParcelas(Now, tipo, dia_preferencial);
                                data_formatada = dataParcelas.dataV_formatada;
                                data_formatada_br = dataParcelas.dataV;
                                $("#data_vencimento").val(dataParcelas.dataV_formatada_v2);
                                num_parcelas = dataParcelas.parcelas;
                            }
                            $("#data_vencimento").val(dataParcelas.dataV_formatada_v2);
                            $("#num_parcelas").val(`Número de parcelas: ${num_parcelas}`);
                            montaTabelaInicial(data_formatada, data_formatada_br, periodicidade_socio, num_parcelas, Number($("#valor_u").val()), socios[0].nome);
                    })

                    $("#valor_u").change(function(){
                        montaTabelaInicial(data_formatada, data_formatada_br, periodicidade_socio, num_parcelas, Number($("#valor_u").val()), socios[0].nome);
                    })

                    $("#data_vencimento").change(function(){
                        dataParcelas = gerarDataParcelas(Now, tipo, $(this).val().split("-")[2]);
                        data = dataParcelas.dataV_formatada_v2;
                        num_parcelas = dataParcelas.parcelas;
                        montaTabelaInicial(data_formatada, data_formatada_br, periodicidade_socio, num_parcelas, Number($("#valor_u").val()), socios[0].nome);
                    })

                    $("#data_vencimento").val(dataParcelas.dataV_formatada_v2);
                    
                    $("#num_parcelas").val(`Número de parcelas: ${num_parcelas}`);
                    montaTabelaInicial(data_formatada, data_formatada_br, periodicidade_socio, num_parcelas, Number($("#valor_u").val()), socios[0].nome);
                    console.log($("#tipo_geracao").val());
                    
                    $(".div_btn_gerar").css("display", "block");
                    
                    $("#btn_geracao_unica").click(function(event){
                        $(".box-geracaounica").prepend('<div class="overlay"> <i class="fa fa-refresh fa-spin"></i> </div>');
                        $.post("./controller/query_geracao_auto.php",{
                            "query": `SELECT * FROM doacao_boleto_info AS bi JOIN sistema_pagamento AS sp ON (bi.id_sistema = sp.id) JOIN doacao_boleto_regras AS br ON (br.id = bi.id_regras)  WHERE nome_sistema = 'BOLETOFACIL'`
                        })
                            .done(function(dados_api){
                                carneBoletos = [];
                                function montaTabela(nome_socio, carne, tipo_socio, telefone){
                                    console.log(telefone);
                                    $(".detalhes_unico").html("");
                                    $("#btn_wpp").css("display", "inline-block");
                                    referenciaAccordion = nome_socio.replace(/[^a-zA-Zs]/g, "") + Math.round(Math.random()*100000000);
                                    console.log(nome_socio, tipo_socio, carne);
                                    var tabela = ``;
                                    var qtd_parcelas = carne.length;
                                    var link_wpp;
                                    var texto = `Olá ${nome_socio.split(" ")[0]}, obrigado por estar contribuindo com o LAJE, confira seus boletos:`;
                                    for(const [i, boleto] of carne.entries()){
                                        tabela += `<tr><td>${i+1}/${qtd_parcelas}</td><td>${boleto.dueDate}</td><td><a target='_blank' href='${boleto.installmentLink}'>${boleto.installmentLink}</a></td><td>${boleto.payNumber}</td><tr>`;
                                        texto += `\nParcela (${i+1}/${qtd_parcelas} - ${boleto.dueDate}): ${boleto.installmentLink}`;
                                    }
                                    if(tipo_socio == "mensal"){
                                        tabela += `<tr><td colspan='2'>Link carnê completo:</td><td colspan='2'><a target='_blank' href='${carne[0].link}'>${carne[0].link}</a></td></tr>`;
                                        texto += `\nCarnê completo: ${boleto.installmentLink}`;
                                    }
                                    texto = window.encodeURIComponent(texto);
                                    link_wpp = `https://api.whatsapp.com/send?phone=55${telefone.replace(/\D/g, "")}&text=${texto}`;
                                    $(".detalhes_unico").append(`
                                    <div class="accordion" id="accordionExample">
                                    <div class="card">
                                      <div class="card-header" id="headingThree">
                                        <h2 class="mb-0">
                                          <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse${referenciaAccordion}" aria-expanded="false" aria-controls="collapseThree">
                                            ${nome_socio} - Sócio ${tipo_socio} [BOLETOS GERADOS!]
                                          </button>
                                        </h2>
                                      </div>
                                      <div id="collapse${referenciaAccordion}" class="collapse" aria-labelledby="headingThree" data-parent="#accordionExample">
                                        <div class="card-body">
                                        <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                              <th>Parcela</th>
                                              <th>Data de vencimento</th>
                                              <th>Link parcela</th>
                                              <th>Código de pagamento</th>
                                          </tr>
                                      </thead>
                                      <tbody>${tabela}</tbody>
                                  </table>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                    `)
                                    $("#btn_wpp").click(function(event){
                                        var win = window.open(link_wpp, '_blank');
                                        win.focus();
                                    })
                                }

                                function geraRef(socioNome){
                                    return socioNome.replace(/[^a-zA-Zs]/g, "").replace(" ", "") + Math.round(Math.random()*100000000);
                                }
                                
                                var apiData = JSON.parse(dados_api)[0];
                                
                                for(socio of socios){
                                    switch(tipo){
                                        case 6:
                                            var Now = new Date();
                                            var dadosDataParcelas = gerarDataParcelas(Now, 6, $("#data_vencimento").val().split("-")[2]);
                                            var dataV = dadosDataParcelas['dataV'];
                                            var dataV_formatada = dadosDataParcelas['dataV_formatada'];
                                            var parcelas = dadosDataParcelas['parcelas']; 

                                            console.log("datav" + dataV);
                                            console.log("datav f" + dataV_formatada);
                                            console.log("p" + parcelas);
                                            //aqui q tem q mudar as parcelas
                                            console.log("teste - "+parcelas);
                                            $.ajax({
                                                type: "GET",
                                                url: `${apiData.api}token=${apiData.token_api}&description=${apiData.agradecimento}&amount=${$("#valor_u").val()}&dueDate=${dataV}&maxOverdueDays=${apiData.max_dias_venc}&installments=${parcelas}&payerName=${socio.nome}&payerCpfCnpj=${socio.cpf}&payerEmail=${socio.email}&payerPhone=${socio.telefone}&billingAddressStreet=${socio.logradouro}&billingAddressNumber=${socio.numero_endereco}&billingAddressComplement=${socio.complemento}&billingAddressNeighborhood=${socio.bairro}&billingAddressCity=${socio.cidade}&billingAddressState=${socio.estado}&billingAddressPostcode=${socio.cep}&fine=${apiData.multa}&interest=${apiData.juros}&paymentTypes=BOLETO&notifyPayer=TRUE&reference=${geraRef(socio.nome)}`,
                                                async: false,
                                                success : function(dadosBoleto) {
                                                    for(boleto of dadosBoleto.data.charges){
                                                        carneBoletos.push(boleto);
                                                    }
                                                },
                                                error : function(){
                                                    alert(`Houve um erro ao gerar o carnê de ${socio.nome}, verifique se os dados são válidos ou entre em contato com um administrador.`)
                                                }
                                            });
                                            montaTabela(socio.nome, carneBoletos, 'casual (avulso)', socio.telefone);
                                            carneBoletos = [];
                                        break;
                                        case 4:
                                            var Now = new Date();
                                            var dadosDataParcelas = gerarDataParcelas(Now, 4, $("#data_vencimento").val().split("-")[2]);
                                            var dataV = dadosDataParcelas['dataV'];
                                            var dataV_formatada = dadosDataParcelas['dataV_formatada'];
                                            var parcelas = dadosDataParcelas['parcelas'];
                                            $.ajax({
                                                type: "GET",
                                                url: `${apiData.api}token=${apiData.token_api}&description=${apiData.agradecimento}&amount=${$("#valor_u").val()}&dueDate=${dataV}&maxOverdueDays=${apiData.max_dias_venc}&installments=${parcelas}&payerName=${socio.nome}&payerCpfCnpj=${socio.cpf}&payerEmail=${socio.email}&payerPhone=${socio.telefone}&billingAddressStreet=${socio.logradouro}&billingAddressNumber=${socio.numero_endereco}&billingAddressComplement=${socio.complemento}&billingAddressNeighborhood=${socio.bairro}&billingAddressCity=${socio.cidade}&billingAddressState=${socio.estado}&billingAddressPostcode=${socio.cep}&fine=${apiData.multa}&interest=${apiData.juros}&paymentTypes=BOLETO&notifyPayer=TRUE&reference=${geraRef(socio.nome)}`,
                                                async: false,
                                                success : function(dadosBoleto) {
                                                    for(boleto of dadosBoleto.data.charges){
                                                        carneBoletos.push(boleto);
                                                    }
                                                },
                                                error : function(){
                                                    alert(`Houve um erro ao gerar o carnê de ${socio.nome}, verifique se os dados são válidos ou entre em contato com um administrador.`)
                                                }
                                            });
                                            montaTabela(socio.nome, carneBoletos, 'mensal', socio.telefone);
                                            carneBoletos = [];
                                        break;
                                        case 1:
                                            var Now = new Date();
                                            var dadosDataParcelas = gerarDataParcelas(Now, 1, $("#data_vencimento").val().split("-")[2]);
                                            var dataV = dadosDataParcelas['dataV'];
                                            var dataV_formatada = dadosDataParcelas['dataV_formatada'];
                                            var parcelas = dadosDataParcelas['parcelas'];
                                            for(i = 0; i < parcelas; i++){
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
                                                });
                                                var arrayDataSegments = dataV_formatada.split('-');
                                                var novaData = new Date(arrayDataSegments[0], arrayDataSegments[1], arrayDataSegments[2]);
                                                novaData.setMonth(novaData.getMonth() + 2);
                                                dataV_formatada = `${novaData.getFullYear()}-${novaData.getMonth()}-${novaData.getDate()}`;
                                                dataV = `${novaData.getDate()}/${novaData.getMonth()+1}/${novaData.getFullYear()}`;
                                            }
                                            montaTabela(socio.nome, carneBoletos, 'bimestral', socio.telefone);
                                            carneBoletos = [];
                                        break;
                                        case 2:
                                            var Now = new Date();
                                            var dadosDataParcelas = gerarDataParcelas(Now, 2, $("#data_vencimento").val().split("-")[2]);
                                            var dataV = dadosDataParcelas['dataV'];
                                            var dataV_formatada = dadosDataParcelas['dataV_formatada'];
                                            var parcelas = dadosDataParcelas['parcelas'];
                                            for(i = 0; i < parcelas; i++){
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
                                                });
                                                var arrayDataSegments = dataV_formatada.split('-');
                                                var novaData = new Date(arrayDataSegments[0], arrayDataSegments[1], arrayDataSegments[2]);
                                                novaData.setMonth(novaData.getMonth() + 3);
                                                dataV_formatada = `${novaData.getFullYear()}-${novaData.getMonth()}-${novaData.getDate()}`;
                                                dataV = `${novaData.getDate()}/${novaData.getMonth()+1}/${novaData.getFullYear()}`;
                                            }
                                            montaTabela(socio.nome, carneBoletos, 'trimestral', socio.telefone);
                                            carneBoletos = [];
                                        break;
                                        case 3:
                                            var Now = new Date();
                                            var dadosDataParcelas = gerarDataParcelas(Now, 3, $("#data_vencimento").val().split("-")[2]);
                                            var dataV = dadosDataParcelas['dataV'];
                                            var dataV_formatada = dadosDataParcelas['dataV_formatada'];
                                            var parcelas = dadosDataParcelas['parcelas'];
                                            for(i = 0; i < parcelas; i++){
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
                                                });
                                                var arrayDataSegments = dataV_formatada.split('-');
                                                var novaData = new Date(arrayDataSegments[0], arrayDataSegments[1], arrayDataSegments[2]);
                                                novaData.setMonth(novaData.getMonth() + 6);
                                                dataV_formatada = `${novaData.getFullYear()}-${novaData.getMonth()}-${novaData.getDate()}`;
                                                dataV = `${novaData.getDate()}/${novaData.getMonth()+1}/${novaData.getFullYear()}`;
                                            }
                                            montaTabela(socio.nome, carneBoletos, 'semestral', socio.telefone);
                                            carneBoletos = [];
                                        break;

                                    }
                                }
                                $(".box-geracaounica .overlay").remove();
                            })
                            $(this).off();
                            $(this).attr("disabled", true);
                            $(this).text("Gerado com sucesso, escolha um novo sócio e aperte continuar para gerar mais");
                    });
                }else{
                    console.log("SEM SÓCIOS DA CATEGORIA.");
                    alert(`Para gerar carnês/boletos para o sócio desejado você deve completar o cadastro dele primeiro com os seguintes dados: valor por período, data de referência e a periodicidade.`);
                } 
            })
            .fail(function(dados){
                alert("Erro na obtenção de dados.");
            })
    }
    $("#geracao").change(function(){
        var tipo_desejado = $(this).val();
        procurar_desejados(tipo_desejado);
    })
    $("#btn_gerar_unico").click(function(){
        var id_socio =  $("#id_pesquisa").val().split("|")[2];
        procurar_desejado(id_socio);
    })
})