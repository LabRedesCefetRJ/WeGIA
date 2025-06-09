<div class="wrap-input100">
    <label for="nome" class="label-input100">Nome <span class="obrigatorio">*</span></label>
    <input type="text" class="input100" name="nome" id="nome" placeholder="Informe seu nome completo">
</div>
<div class="wrap-input100">
    <label for="data_nascimento" class="label-input100">Data de Nascimento <span class="obrigatorio">*</span></label>
    <input type="date" class="input100" name="data_nascimento" id="data_nascimento" min="1900-01-01" max="<?= date('Y-m-d') ?>">
</div>
<div class="wrap-input100">
    <label for="email" class="label-input100">E-mail <span class="obrigatorio">*</span></label>
    <input type="text" class="input100" name="email" id="email" placeholder="Informe seu e-mail">
</div>
<div class="wrap-input100">
    <label for="telefone" class="label-input100">Telefone <span class="obrigatorio">*</span></label>
    <input type="text" class="input100" name="telefone" id="telefone" placeholder="Informe seu número de telefone para contato" maxlength="15">
</div>
<div class="container-contact100-form-btn">
    <button class="contact100-form-btn btn-acao" id="avanca-contato">
        AVANÇAR
        <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
    </button>

    <div class="container-contact100-form-btn">
        <button class="contact100-form-btn btn-voltar" id="volta-cpf">
            <i style="margin-right: 15px; " class="fa fa-long-arrow-left m-l-7" aria-hidden="true"></i>
            VOLTAR
        </button>
    </div>
</div>

<script>
  const input = document.getElementById('telefone');
  let isDeleting = false;

  // Detecta se o usuário está apagando (melhor compatibilidade mobile)
  input.addEventListener('beforeinput', function (e) {
    isDeleting = e.inputType === 'deleteContentBackward' || e.inputType === 'deleteContentForward';
  });

  input.addEventListener('input', function () {
    let cursorPos = input.selectionStart;
    let value = input.value;
    const especiais = ['(', ')', '-', ' '];

    // Remove caractere especial se o cursor estiver sobre ele e estiver apagando
    if (isDeleting && especiais.includes(value[cursorPos - 1])) {
      const before = value.slice(0, cursorPos - 1);
      const after = value.slice(cursorPos);
      value = before + after;
      cursorPos--;
    }

    // Remove tudo que não for número
    let raw = value.replace(/\D/g, '');
    if (raw.length > 11) raw = raw.slice(0, 11);

    // Aplica a máscara e calcula os caracteres extras para ajustar o cursor
    let masked = '';
    let count = 0;

    if (raw.length <= 10) {
      masked = raw.replace(/^(\d{0,2})(\d{0,4})(\d{0,4})/, function (_, ddd, part1, part2) {
        let result = '';
        if (ddd) {
          result += '(' + ddd;
          count += 1;
        }
        if (ddd.length === 2) {
          result += ') ';
          count += 2;
        }
        if (part1) result += part1;
        if (part1.length === 4) {
          result += '-';
          count += 1;
        }
        if (part2) result += part2;
        return result;
      });
    } else {
      masked = raw.replace(/^(\d{0,2})(\d{0,5})(\d{0,4})/, function (_, ddd, part1, part2) {
        let result = '';
        if (ddd) {
          result += '(' + ddd;
          count += 1;
        }
        if (ddd.length === 2) {
          result += ') ';
          count += 2;
        }
        if (part1) result += part1;
        if (part1.length === 5) {
          result += '-';
          count += 1;
        }
        if (part2) result += part2;
        return result;
      });
    }

    input.value = masked;

    // Ajuste de cursor ao digitar (somente se não estiver apagando)
    if (!isDeleting) {
      cursorPos += count;
    }

    // Restaurar o cursor corretamente
    setTimeout(() => {
      input.setSelectionRange(cursorPos, cursorPos);
    }, 0);

    // Limpa a flag
    isDeleting = false;
  });
</script>