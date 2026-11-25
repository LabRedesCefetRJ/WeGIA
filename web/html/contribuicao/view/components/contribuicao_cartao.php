<h2 class="h4 text-center mb-4"><i class="fa fa-credit-card" aria-hidden="true"></i> Informações do cartão de crédito</h2>
<div class="row">
    <div class="col-md-12">
        <div class="wrap-input100 validate-input contribution-field" data-validate="Por favor, insira o número do cartão">
            <label class="form-label" for="card_number">Número do cartão <span class="obrigatorio">*</span></label>
            <input class="form-control" type="text" id="card_number" name="card_number"
                   placeholder="0000 0000 0000 0000" maxlength="23" inputmode="numeric" autocomplete="cc-number">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="wrap-input100 validate-input contribution-field" data-validate="Por favor, insira o nome impresso no cartão">
            <label class="form-label" for="card_holder_name">Nome no cartão <span class="obrigatorio">*</span></label>
            <input class="form-control" type="text" id="card_holder_name" name="card_holder_name"
                   placeholder="Como consta no cartão" autocomplete="cc-name">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="wrap-input100 validate-input contribution-field">
            <label class="form-label" for="card_exp_month">Validade <span class="obrigatorio">*</span></label>
            <div class="d-flex align-items-center gap-2">
                <input class="form-control" type="text" id="card_exp_month" name="card_exp_month"
                       placeholder="MM" maxlength="2" inputmode="numeric" autocomplete="cc-exp-month" aria-label="Mês de validade">
                <span aria-hidden="true">/</span>
                <input class="form-control" type="text" id="card_exp_year" name="card_exp_year"
                       placeholder="AA" maxlength="4" inputmode="numeric" autocomplete="cc-exp-year" aria-label="Ano de validade">
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="wrap-input100 validate-input contribution-field">
            <label class="form-label" for="card_cvv">Código de segurança (CVV) <span class="obrigatorio">*</span></label>
            <input class="form-control" type="password" id="card_cvv" name="card_cvv"
                   placeholder="•••" maxlength="4" inputmode="numeric" autocomplete="cc-csc">
        </div>
    </div>
</div>

<div class="d-flex flex-wrap justify-content-between gap-2 mt-4">
    <button type="button" id="btn-voltar-endereco" class="btn btn-outline-secondary">
        <i class="fa fa-arrow-left me-2" aria-hidden="true"></i> Voltar
    </button>
    <button type="button" id="btn-finalizar" class="btn btn-primary btn-lg px-5">
        <i class="fa fa-lock me-2" aria-hidden="true"></i> Finalizar pagamento
    </button>
</div>