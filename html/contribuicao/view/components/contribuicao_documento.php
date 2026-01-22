<div class="centralizar">
    <input class="radio" type="radio" id="op_cpf" value="fisica" name="opcao" checked><label class="label" for="op_cpf">PESSOA FÍSICA</label>
    <input class="radio" type="radio" id="op_cnpj" value="juridica" name="opcao"><label class="label" for="op_cnpj">PESSOA JURÍDICA</label><br><br>
</div>

<div id="cpf" class="wrap-input100 validate-input bg1" data-validate="Digite um documento válido!">
    <span class="label-input100">Digite um documento CPF <span class="obrigatorio">*</span></span>
    <input class="input100" type="text" name="dcpf" id="dcpf" class="text required" placeholder="Ex: 222.222.222-22" oninput="mascaraCPF(this)" maxlength="14" required inputmode="numeric"><span id="avisa_cpf"></span>
</div>

<div id="cnpj" class="wrap-input100 validate-input bg1 hidden" data-validate="Digite um documento válido!">
    <span class="label-input100"> Digite um documento CNPJ <span class="obrigatorio">*</span></span>
    <input class="input100" type="text" name="dcpf" id="dcnpj" maxlength="18" class="form-control input-md" ng-m placeholder="Ex: 22.222.222/2222-22" oninput="mascaraCNPJ(this)"><span id="avisa_cnpj" inputmode="numeric"></span>
</div>
<div class="container-contact100-form-btn">
    <button class="contact100-form-btn" id="consultar-btn">
        AVANÇAR
        <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
    </button>
</div>
<div class="container-contact100-form-btn">
    <button class="contact100-form-btn" id="volta-valor">
        <i style="margin-right: 15px; " class="fa fa-long-arrow-left m-l-7" aria-hidden="true"></i> VOLTAR
    </button>
</div>