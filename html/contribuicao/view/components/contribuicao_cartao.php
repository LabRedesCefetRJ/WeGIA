<h3 class="text-center mb-4">Informações do Cartão de Crédito</h3>
<div class="row">
    <div class="col-md-12">
        <div class="wrap-input100 validate-input" data-validate="Por favor, insira o número do cartão">
            <span class="label-input100">Número do Cartão<span class="obrigatorio">*</span></span>
            <input class="input100" type="text" id="card_number" name="card_number" 
                   placeholder="•••• •••• •••• ••••" maxlength="23">
            <span class="focus-input100"></span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="wrap-input100 validate-input" data-validate="Por favor, insira o nome impresso no cartão">
            <span class="label-input100">Nome no Cartão<span class="obrigatorio">*</span></span>
            <input class="input100" type="text" id="card_holder_name" name="card_holder_name" 
                   placeholder="Como consta no cartão">
            <span class="focus-input100"></span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="wrap-input100 validate-input" data-validate="Validade obrigatória">
            <span class="label-input100">Validade<span class="obrigatorio">*</span></span>
            <div class="d-flex">
                <input class="input100 mr-2" type="text" id="card_exp_month" name="card_exp_month" 
                       placeholder="MM" maxlength="2" style="width: 70px;">
                <span class="align-self-center">/</span>
                <input class="input100 ml-2" type="text" id="card_exp_year" name="card_exp_year" 
                       placeholder="AA" maxlength="4" style="width: 90px;">
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="wrap-input100 validate-input" data-validate="CVV obrigatório">
            <span class="label-input100">CVV<span class="obrigatorio">*</span></span>
            <input class="input100" type="password" id="card_cvv" name="card_cvv" 
                   placeholder="•••" maxlength="4">
            <span class="focus-input100"></span>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between mt-4">
    <button id="btn-voltar-endereco" class="btn btn-outline-secondary btn-lg">
        <i class="fa fa-arrow-left mr-2"></i> Voltar
    </button>
    <button id="btn-finalizar" class="btn btn-primary btn-lg px-5">
        Finalizar <i class="fa fa-arrow-right ml-2"></i>
    </button>
</div>