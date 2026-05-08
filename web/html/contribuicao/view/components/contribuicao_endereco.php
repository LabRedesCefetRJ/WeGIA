<div class="wrap-input100">
    <label for="cep" class="label-input100">CEP <span class="obrigatorio">*</span></label>
    <input type="text" class="input100" name="cep" id="cep" placeholder="Informe o CEP do seu endereço" onkeypress="$(this).mask('00000-000')" onblur="pesquisacep(this.value)">
</div>
<div class="wrap-input100">
    <label for="rua" class="label-input100">Rua <span class="obrigatorio">*</span></label>
    <input type="text" class="input100" name="rua" id="rua" placeholder="Informe o nome da sua rua">
</div>
<div class="wrap-input100">
    <label for="numero" class="label-input100">Número <span class="obrigatorio">*</span></label>
    <input type="text" class="input100" name="numero" id="numero" placeholder="Informe o número da sua residência">
</div>
<div class="wrap-input100">
    <label for="bairro" class="label-input100">Bairro <span class="obrigatorio">*</span></label>
    <input type="text" class="input100" name="bairro" id="bairro" placeholder="Informe o nome do seu bairro">
</div>
<div class="wrap-input100 validate-input bg1">
    <span class="label-input100">Estado <span class="obrigatorio">*</span></span>
    <select class="wrap-input100 validate-input bg1" id="uf" name="uf">
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
    </select><br>
</div>
<div class="wrap-input100">
    <label for="cidade" class="label-input100">Cidade <span class="obrigatorio">*</span></label>
    <input type="text" class="input100" name="cidade" id="cidade" placeholder="Informe o nome da sua cidade">
</div>
<div class="wrap-input100">
    <label for="complemento" class="label-input100">Complemento</label>
    <input type="text" class="input100" name="complemento" id="complemento" placeholder="Caso julgue interessante, forneça um complemento">
</div>

<input type="hidden" name="ibge" id="ibge" value="">
<div class="container-contact100-form-btn">
    <button class="contact100-form-btn" id="avanca-endereco">
        AVANÇAR
        <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
    </button>
</div>

<div class="container-contact100-form-btn">
    <button class="contact100-form-btn btn-voltar" id="volta-contato">
        <i style="margin-right: 15px; " class="fa fa-long-arrow-left m-l-7" aria-hidden="true"></i>
        VOLTAR
    </button>
</div>