<div class="wrap-input100 contribution-field">
    <label for="cep" class="form-label"><i class="fa fa-map-marker" aria-hidden="true"></i> CEP <span class="obrigatorio">*</span></label>
    <input type="text" class="form-control" name="cep" id="cep" placeholder="Informe o CEP do seu endereço" onkeypress="$(this).mask('00000-000')" onblur="pesquisacep(this.value)" inputmode="numeric" autocomplete="postal-code">
</div>
<div class="wrap-input100 contribution-field">
    <label for="rua" class="form-label"><i class="fa fa-road" aria-hidden="true"></i> Rua <span class="obrigatorio">*</span></label>
    <input type="text" class="form-control" name="rua" id="rua" placeholder="Informe o nome da sua rua" autocomplete="address-line1">
</div>
<div class="wrap-input100 contribution-field">
    <label for="numero" class="form-label"><i class="fa fa-hashtag" aria-hidden="true"></i> Número <span class="obrigatorio">*</span></label>
    <input type="text" class="form-control" name="numero" id="numero" placeholder="Informe o número da sua residência">
</div>
<div class="wrap-input100 contribution-field">
    <label for="bairro" class="form-label"><i class="fa fa-map" aria-hidden="true"></i> Bairro <span class="obrigatorio">*</span></label>
    <input type="text" class="form-control" name="bairro" id="bairro" placeholder="Informe o nome do seu bairro">
</div>
<div class="wrap-input100 validate-input bg1 contribution-field">
    <label for="uf" class="form-label"><i class="fa fa-globe" aria-hidden="true"></i> Estado <span class="obrigatorio">*</span></label>
    <select class="form-select" id="uf" name="uf" autocomplete="address-level1">
        <option value="Selecione sua unidade federativa" disabled></option>
        <option value="AC">Acre</option>
        <option value="AL">Alagoas</option>
        <option value="AP">Amapá</option>
        <option value="AM">Amazonas</option>
        <option value="BA">Bahia</option>
        <option value="CE">Ceará</option>
        <option value="DF">Distrito Federal</option>
        <option value="ES">Espírito Santo</option>
        <option value="GO">Goiás</option>
        <option value="MA">Maranhão</option>
        <option value="MT">Mato Grosso</option>
        <option value="MS">Mato Grosso do Sul</option>
        <option value="MG">Minas Gerais</option>
        <option value="PA">Pará</option>
        <option value="PB">Paraíba</option>
        <option value="PR">Paraná</option>
        <option value="PE">Pernambuco</option>
        <option value="PI">Piauí</option>
        <option value="RJ">Rio de Janeiro</option>
        <option value="RN">Rio Grande do Norte</option>
        <option value="RS">Rio Grande do Sul</option>
        <option value="RO">Rondônia</option>
        <option value="RR">Roraima</option>
        <option value="SC">Santa Catarina</option>
        <option value="SP">São Paulo</option>
        <option value="RS">Sergipe</option>
        <option value="TO">Tocantins</option>
    </select>
</div>
<div class="wrap-input100 contribution-field">
    <label for="cidade" class="form-label"><i class="fa fa-building-o" aria-hidden="true"></i> Cidade <span class="obrigatorio">*</span></label>
    <input type="text" class="form-control" name="cidade" id="cidade" placeholder="Informe o nome da sua cidade" autocomplete="address-level2">
</div>
<div class="wrap-input100 contribution-field">
    <label for="complemento" class="form-label"><i class="fa fa-info-circle" aria-hidden="true"></i> Complemento</label>
    <input type="text" class="form-control" name="complemento" id="complemento" placeholder="Caso julgue interessante, forneça um complemento">
</div>

<input type="hidden" name="ibge" id="ibge" value="">
<div class="container-contact100-form-btn">
    <button type="button" class="btn btn-primary btn-lg" id="avanca-endereco">
        Avançar <i class="fa fa-arrow-right ms-2" aria-hidden="true"></i>
    </button>
</div>

<div class="container-contact100-form-btn">
    <button type="button" class="btn btn-outline-secondary" id="volta-contato">
        <i class="fa fa-arrow-left me-2" aria-hidden="true"></i> Voltar
    </button>
</div>