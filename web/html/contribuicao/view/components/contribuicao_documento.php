<fieldset class="contribution-person-type mb-4">
    <legend class="form-label">Tipo de pessoa</legend>
    <div class="d-flex flex-wrap gap-2">
        <div class="form-check contribution-choice">
            <input class="form-check-input radio" type="radio" id="op_cpf" value="fisica" name="opcao" checked>
            <label class="form-check-label" for="op_cpf"><i class="fa fa-user" aria-hidden="true"></i> Pessoa física</label>
        </div>
        <div class="form-check contribution-choice">
            <input class="form-check-input radio" type="radio" id="op_cnpj" value="juridica" name="opcao">
            <label class="form-check-label" for="op_cnpj"><i class="fa fa-building" aria-hidden="true"></i> Pessoa jurídica</label>
        </div>
    </div>
</fieldset>

<div id="cpf" class="wrap-input100 validate-input bg1 contribution-field" data-validate="Digite um documento válido!">
    <label for="dcpf" class="form-label"><i class="fa fa-id-card-o" aria-hidden="true"></i> CPF <span class="obrigatorio">*</span></label>
    <input class="form-control" type="text" name="dcpf" id="dcpf" placeholder="Ex.: 222.222.222-22" oninput="mascaraCPF(this)" maxlength="14" required inputmode="numeric" autocomplete="off" aria-describedby="avisa_cpf">
    <span id="avisa_cpf" class="contribution-field-feedback" role="status" aria-live="polite"></span>
</div>

<div id="cnpj" class="wrap-input100 validate-input bg1 hidden contribution-field" data-validate="Digite um documento válido!">
    <label for="dcnpj" class="form-label"><i class="fa fa-id-card-o" aria-hidden="true"></i> CNPJ <span class="obrigatorio">*</span></label>
    <input class="form-control" type="text" name="dcpf" id="dcnpj" maxlength="18" placeholder="Ex.: 22.222.222/2222-22" oninput="mascaraCNPJ(this)" inputmode="numeric" autocomplete="off" aria-describedby="avisa_cnpj">
    <span id="avisa_cnpj" class="contribution-field-feedback" role="status" aria-live="polite"></span>
</div>

<?= $captchaGoogle->getWidget() ?>

<div class="container-contact100-form-btn">
    <button type="button" class="btn btn-primary btn-lg" id="consultar-btn">
        Avançar <i class="fa fa-arrow-right ms-2" aria-hidden="true"></i>
    </button>
</div>
<div class="container-contact100-form-btn">
    <button type="button" class="btn btn-outline-secondary" id="volta-valor">
        <i class="fa fa-arrow-left me-2" aria-hidden="true"></i> Voltar
    </button>
</div>