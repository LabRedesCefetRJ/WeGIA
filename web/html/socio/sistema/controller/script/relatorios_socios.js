//modificar
$(document).ready(function () {
    function formatDateForDisplay(dateValue) {
        if (!dateValue) {
            return "-";
        }

        const date = new Date(dateValue + "T00:00:00");
        return isNaN(date.getTime()) ? dateValue : date.toLocaleDateString("pt-BR");
    }

    function getDataGeracao() {
        return new Date().toLocaleString("pt-BR");
    }

    function getQuantasPaginas(totalRegistros) {
        return Math.max(1, Math.ceil(totalRegistros / 25));
    }

    function buildResumoFiltros(payload, socios) {
        const statusLabel = $("#status option:selected").text() || "Todas as opções";
        const tagLabel = $("#tag option:selected").text() || "Todas as opções";
        const tipoSocio = $("#tipo_socio option:selected").text() || "Todas as opções";
        const tipoPessoa = $("#tipo_pessoa option:selected").text() || "Todas as opções";
        const operador = $("#operador option:selected").text() || "Maior que";
        const valor = $("#valor").val() || "0";
        const dataSelecao = $("#data-contribuicao").val() || "qualquer";
        const dataInicio = formatDateForDisplay($("#data_inicio").val());
        const dataFim = formatDateForDisplay($("#data_fim").val());

        const filtros = [
            `Tipo de sócio: ${tipoSocio}`,
            `Tipo de pessoa: ${tipoPessoa}`,
            `Status: ${statusLabel}`,
            `Tag: ${tagLabel}`,
            `Valor: ${operador} ${valor}`,
            `Contribuição: ${dataSelecao === "qualquer" ? "Qualquer" : dataSelecao === "partir" ? `A partir de ${dataInicio}` : dataSelecao === "ate" ? `Até ${dataFim}` : `Entre ${dataInicio} e ${dataFim}`}`,
            `Quantidade de registros: ${socios.length}`,
            `Data/hora de geração: ${getDataGeracao()}`,
            `Páginas estimadas: ${getQuantasPaginas(socios.length)}`
        ];

        return filtros.map((item) => `<span class="badge-resumo">${item}</span>`).join("");
    }

    function imprimirRelatorio() {
        const panel = document.querySelector(".relatorio-panel");
        if (!panel) return;

        const dataGeracao = getDataGeracao();
        const paginas = getQuantasPaginas(document.querySelectorAll(".relatorio-table tbody tr").length);

        const meta = document.createElement("div");
        meta.className = "relatorio-meta-print";
        meta.innerHTML = `
            <div><strong>Data e hora de geração:</strong> ${dataGeracao}</div>
            <div><strong>Quantidade de páginas:</strong> ${paginas}</div>
        `;

        const existingMeta = panel.querySelector(".relatorio-meta-print");
        if (existingMeta) {
            existingMeta.remove();
        }

        panel.querySelector(".panel-body").appendChild(meta);
        window.print();
    }

    $(document).on("submit", "#form_relatorio", function (e) {
        e.preventDefault();
        $(".resultado").html("");

        var payload = {
            tipo_socio: $("#tipo_socio").val(),
            tipo_pessoa: $("#tipo_pessoa").val(),
            operador: $("#operador").val(),
            valor: $("#valor").val(),
            tag: $("#tag").val(),
            status: $("#status").val(),
            suposicao: $("#sup").val(),
            "data-contribuicao": $("#data-contribuicao").val(),
            "data_inicio": $("#data_inicio").val(),
            "data_fim": $("#data_fim").val()
        };

        $.ajax({
            url: "get_relatorios_socios.php",
            method: "GET",
            data: payload,
            dataType: "json"
        })
            .done(function (socios) {
                if (!socios || !socios.length) {
                    $(".resultado").html(`
                        <div class="panel panel-default relatorio-panel empty-state">
                            <div class="panel-body">
                                <h4 class="text-primary"><i class="fa fa-info-circle"></i> Nenhum resultado encontrado</h4>
                                <p class="mb-0">Ajuste os filtros e tente novamente para visualizar o relatório esperado.</p>
                            </div>
                        </div>
                    `);
                    return;
                }

                var tabela = "";
                var estrutura_tab = "";

                for (let socio of socios) {
                    socio.sobrenome = socio.sobrenome || "";

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
                                <td>${socio.nome} ${socio.sobrenome}</td>
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
                                <td>${socio.nome} ${socio.sobrenome}</td>
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

                const resumoFiltros = buildResumoFiltros(payload, socios);

                $(".resultado").html(`
                    <div class="panel panel-default relatorio-panel">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa-solid fa-user"></i> Relatório de Sócios</h3>
                            <button class="btn btn-default print-button" type="button"><i class="fa fa-print"></i> Imprimir</button>
                        </div>
                        <div class="panel-body">
                            <div class="resumo-relatorio">
                                ${resumoFiltros}
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover relatorio-table">
                                    <thead>
                                        ${estrutura_tab}
                                    </thead>
                                    <tbody>
                                        ${tabela}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `);

                $(document).off("click", ".print-button").on("click", ".print-button", function (event) {
                    event.preventDefault();
                    imprimirRelatorio();
                });
            })
            .fail(function (xhr, status, error) {
                console.error("Erro na requisição:", error);
                $(".resultado").html(`
                    <div class="panel panel-default relatorio-panel error-state">
                        <div class="panel-body">
                            <h4><i class="fa fa-exclamation-triangle"></i> Não foi possível carregar o relatório</h4>
                            <p class="mb-0">Tente novamente em instantes ou ajuste os parâmetros informados.</p>
                        </div>
                    </div>
                `);
            });
    });

    const dataSelect = document.getElementById('data-contribuicao');
    const dataInicio = document.getElementById('data_inicio');
    const dataFim = document.getElementById('data_fim');

    const labelInicio = document.createElement('label');
    labelInicio.textContent = 'Início:';
    labelInicio.className = 'date-label';
    labelInicio.style.display = 'none';

    const labelFim = document.createElement('label');
    labelFim.textContent = 'Fim:';
    labelFim.className = 'date-label';
    labelFim.style.display = 'none';

    dataInicio.parentNode.insertBefore(labelInicio, dataInicio);
    dataFim.parentNode.insertBefore(labelFim, dataFim);

    function updateDateFields() {
        const value = dataSelect.value;

        if (value === 'qualquer') {
            labelInicio.style.display = 'none';
            dataInicio.style.display = 'none';
            labelFim.style.display = 'none';
            dataFim.style.display = 'none';
        } else if (value === 'partir') {
            labelInicio.style.display = 'none';
            dataInicio.style.display = 'inline-block';
            labelFim.style.display = 'none';
            dataFim.style.display = 'none';
        } else if (value === 'ate') {
            labelInicio.style.display = 'none';
            dataInicio.style.display = 'none';
            labelFim.style.display = 'none';
            dataFim.style.display = 'inline-block';
        } else if (value === 'entre') {
            labelInicio.style.display = 'inline-block';
            dataInicio.style.display = 'inline-block';
            labelFim.style.display = 'inline-block';
            dataFim.style.display = 'inline-block';
        }
    }

    dataSelect.addEventListener('change', updateDateFields);
    updateDateFields();
});
