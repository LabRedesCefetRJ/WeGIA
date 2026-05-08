<div class="wrap-input100">
  <label for="nome" class="label-input100">Nome <span class="obrigatorio">*</span></label>
  <input type="text" class="input100" name="nome" id="nome" placeholder="Informe seu nome completo">
</div>
<div class="wrap-input100">
  <label for="data_nascimento" class="label-input100">Data de Nascimento <span class="obrigatorio">*</span></label>
  <input type="text" class="input100" name="data_nascimento" id="data_nascimento" placeholder="dd/mm/aaaa">
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
  function aplicarMascara(input, tipo) {
    let isDeleting = false;

    input.addEventListener('beforeinput', function (e) {
      isDeleting = e.inputType === 'deleteContentBackward' || e.inputType === 'deleteContentForward';
    });

    input.addEventListener('input', function () {
      let cursorPos = input.selectionStart;
      let value = input.value;
      let especiais = tipo === 'telefone' ? ['(', ')', '-', ' '] : ['/'];

      if (isDeleting && especiais.includes(value[cursorPos - 1])) {
        const before = value.slice(0, cursorPos - 1);
        const after = value.slice(cursorPos);
        value = before + after;
        cursorPos--;
      }

      let raw = value.replace(/\D/g, '');
      let masked = '';
      let count = 0;

      if (tipo === 'telefone') {
        if (raw.length > 11) raw = raw.slice(0, 11);

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
      } else if (tipo === 'data') {
        if (raw.length > 8) raw = raw.slice(0, 8);

        // Validação avançada de dia e mês
        let dia = raw.slice(0, 2);
        let mes = raw.slice(2, 4);
        let ano = raw.slice(4, 8);

        // Corrigir dia inválido
        if (dia.length >= 1) {
          if (parseInt(dia[0]) > 3) {
            dia = '0' + dia[0];
            raw = dia + raw.slice(1);
          } else if (dia.length === 2) {
            if (dia[0] === '3' && !'01'.includes(dia[1])) {
              dia = '3';
              raw = dia + raw.slice(2);
            }
          }
        }

        // Corrigir mês inválido
        if (mes.length >= 1) {
          if (parseInt(mes[0]) > 1) {
            mes = '0' + mes[0];
            raw = raw.slice(0, 2) + mes + raw.slice(3);
          } else if (mes.length === 2) {
            if (mes[0] === '1' && !'012'.includes(mes[1])) {
              mes = '1';
              raw = raw.slice(0, 2) + mes + raw.slice(4);
            }
          }
        }

        // Reaplica com dados validados
        masked = raw.replace(/^(\d{0,2})(\d{0,2})(\d{0,4})/, function (_, d, m, a) {
          let result = '';
          if (d) result += d;
          if (d.length === 2) {
            result += '/';
            count += 1;
          }
          if (m) result += m;
          if (m.length === 2) {
            result += '/';
            count += 1;
          }
          if (a) result += a;
          return result;
        });
      }

      input.value = masked;

      if (!isDeleting) {
        cursorPos += count;
      }

      setTimeout(() => {
        input.setSelectionRange(cursorPos, cursorPos);
      }, 0);

      isDeleting = false;
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    const telefone = document.getElementById('telefone');
    const dataNascimento = document.getElementById('data_nascimento');

    if (telefone) aplicarMascara(telefone, 'telefone');
    if (dataNascimento) aplicarMascara(dataNascimento, 'data');
  });
</script>
