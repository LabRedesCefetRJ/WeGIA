$(document).ready(function () {
    $(document).on("submit", "#form_relatorio", function (e) {
        e.preventDefault();
        $(".resultado").html("");

        // coletar dados do formulário
        var payload = {
            tipo_socio: $("#tipo_socio").val(),
            tipo_pessoa: $("#tipo_pessoa").val(),
            operador: $("#operador").val(),
            valor: $("#valor").val(),
            tag: $("#tag").val(),
            status: $("#status").val(),
            suposicao: $("#sup").val()
        };

        $.ajax({
            url: "get_relatorios_socios.php",
            method: "GET",
            data: payload,
            dataType: "json" // garante que retorno já vem como objeto JS
        })
            .done(function (socios) {
                if (!socios) {
                    $(".resultado").html("<p>Nenhum resultado encontrado.</p>");
                    return;
                }

                var tabela = "";
                var estrutura_tab = "";

                for (let socio of socios) {
                    if (payload.suposicao === "s") {
                        estrutura_tab = `
                            <tr>
                                <th scope="col" width="25%">Nome</th>
                                <th scope="col">CPF/CNPJ</th>
                                <th scope="col">Último Vencimento</th>
                                <th scope="col">Telefone</th>
                                <th scope="col" width="14%">Tipo Sócio</th>                            
                                <th scope="col" width="12%" class="tot">Valor/Período</th>
                            </tr>`;

                        let valor_periodo = socio.valor;
                        let p_periodicidade = "sem informação/ocasional";

                        if (socio.provavel_periodicidade >= 28 && socio.provavel_periodicidade <= 49) {
                            p_periodicidade = "Mensal";
                        } else if (socio.provavel_periodicidade > 49 && socio.provavel_periodicidade <= 70) {
                            p_periodicidade = "Bimestral";
                        } else if (socio.provavel_periodicidade > 70 && socio.provavel_periodicidade <= 100) {
                            p_periodicidade = "Trimestral";
                        } else if (socio.provavel_periodicidade > 100 && socio.provavel_periodicidade <= 200) {
                            p_periodicidade = "Semestral";
                        }

                        tabela += `
                            <tr>
                                <td>${socio.nome}</td>
                                <td>${socio.cpf}</td>
                                <td>${socio.data_formatada ?? ""}</td>
                                <td>${socio.telefone ?? ""}</td>
                                <td>Provavelmente ${p_periodicidade}</td>
                                <td>${valor_periodo ?? ""}</td>
                            </tr>`;
                    } else {
                        estrutura_tab = `
                            <tr>
                                <th scope="col" width="25%">Nome</th>
                                <th scope="col">CPF/CNPJ</th>
                                <th scope="col">Telefone</th>
                                <th scope="col">E-mail</th>
                                <th scope="col" width="14%">Tipo Sócio</th>
                                <th scope="col" width="14%">TAG</th>                               
                                <th scope="col" width="12%" class="tot">Valor/Período</th>
                                <th scope="col" width="12%" class="tot">Status</th>
                            </tr>`;

                        tabela += `
                            <tr>
                                <td>${socio.nome}</td>
                                <td>${socio.cpf}</td>
                                <td>${socio.telefone ?? ""}</td>
                                <td>${socio.email ?? ""}</td>
                                <td>${socio.tipo ?? ""}</td>
                                <td>${socio.tag ?? ""}</td>
                                <td>${socio.valor_periodo ?? ""}</td>
                                <td>${socio.status ?? ""}</td>
                            </tr>`;
                    }
                }

                let valor = $('#valor').val() || '0';

                $(".resultado").html(`
                    <div class="tab-content">
                        <div class="descricao">
                            <h3>Relatório de Sócios</h3>
                            <ul>Sócios: ${$("#tipo_socio option:selected").text()}</ul>
                            <ul>Pessoas: ${$("#tipo_pessoa option:selected").text()}</ul>
                            <ul>Quantidade: ${socios.length}</ul>
                            <ul>Valor: ${$("#operador option:selected").text()} R$ ${valor}</ul>
                            <button style="float: right;" class="mb-xs mt-xs mr-xs btn btn-default print-button" onclick="window.print();">Imprimir</button>
                        </div>
                        <h4>Resultado</h4>
                        <table class="table table-striped">
                            <thead class="thead-dark">
                                ${estrutura_tab}
                            </thead>
                            <tbody>
                                ${tabela}
                            </tbody>
                        </table>
                    </div>
                `);
            })
            .fail(function (xhr, status, error) {
                console.error("Erro na requisição:", error);
                $(".resultado").html("<p>Erro ao carregar relatório.</p>");
            });
    });
});
