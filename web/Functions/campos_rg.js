const requiredIndication = `<span class="obrig">*</span>`;

function getElements(rg="rg", orgao_emissor="orgao_emissor", data_expedicao="data_expedicao", labelRg="label_rg", dateExpedicao="label_data_expedicao", orgaoEmissor="label_orgao_emissor", div="campos_rg") {
    const rgInput = document.getElementById(rg);
    const orgaoEmissorInput = document.getElementById(orgao_emissor);
    const dataExpedicaoInput = document.getElementById(data_expedicao);
    const rgLabel = document.getElementById(labelRg);
    const dateExpedicaoLabel = document.getElementById(dateExpedicao);
    const orgaoEmissorLabel = document.getElementById(orgaoEmissor);
    const divElement = document.getElementById(div);
    return { rgElement: rgInput, orgaoEmissorElement: orgaoEmissorInput, dataExpedicaoElement: dataExpedicaoInput, rgLabel: rgLabel, dateExpedicaoLabel: dateExpedicaoLabel, orgaoEmissorLabel: orgaoEmissorLabel, div: divElement };    
}

function toggleElements(rg="rg", others="campos_rg") {
    console.log("toggleElements called");
    const { rgElement, orgaoEmissorElement, dataExpedicaoElement, rgLabel, dateExpedicaoLabel, orgaoEmissorLabel, div } = getElements(rg, 'orgao_emissor', 'data_expedicao', 'label_rg', 'label_data_expedicao', 'label_orgao_emissor', 'campos_rg');
    if (rgElement && div) {
        if (rgElement.value.trim() === "") {
            div.classList.add("hidden");
            rgElement.required = false;
            orgaoEmissorElement.required = false;
            dataExpedicaoElement.required = false;
            rgLabel.innerHTML = "Número do RG";
            dateExpedicaoLabel.innerHTML = "Data de expedição";
            orgaoEmissorLabel.innerHTML = "Órgão Emissor";
        } else {
            div.classList.remove("hidden");
            rgElement.required = true;
            orgaoEmissorElement.required = true;
            dataExpedicaoElement.required = true;
            rgLabel.innerHTML = "Número do RG" + requiredIndication;
            dateExpedicaoLabel.innerHTML = "Data de expedição" + requiredIndication;
            orgaoEmissorLabel.innerHTML = "Órgão Emissor" + requiredIndication;
        }
    }
}



const rg = getElements().rgElement;
rg.addEventListener("input", () => toggleElements());