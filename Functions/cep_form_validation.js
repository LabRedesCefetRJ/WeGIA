(function (window, document) {
  "use strict";

  var ctx = null;

  function digitos(valor) { return (valor || "").replace(/\D/g, ""); }
  function formatar(valor) {
    var cep = digitos(valor).slice(0, 8);
    return cep.length > 5 ? cep.slice(0, 5) + "-" + cep.slice(5) : cep;
  }
  function el(ids) {
    var i, campo;
    for (i = 0; i < ids.length; i += 1) {
      campo = document.getElementById(ids[i]);
      if (campo) { return campo; }
    }
    return null;
  }
  function msg(texto) {
    ctx.mensagem.textContent = texto || "";
    ctx.mensagem.style.display = texto ? "block" : "none";
    ctx.cep.setCustomValidity(texto || "");
  }
  function limpar() {
    ctx.rua.value = "";
    ctx.bairro.value = "";
    ctx.cidade.value = "";
    ctx.estado.value = "";
    ctx.ibge.value = "";
  }
  function carregando() {
    ctx.rua.value = "...";
    ctx.bairro.value = "...";
    ctx.cidade.value = "...";
    ctx.estado.value = "...";
    ctx.ibge.value = "...";
  }
  function enderecoOk() {
    return !!(
      ctx.rua.value.trim() &&
      ctx.bairro.value.trim() &&
      ctx.cidade.value.trim() &&
      ctx.estado.value.trim() &&
      ctx.ibge.value.trim() &&
      ctx.rua.value !== "..." &&
      ctx.bairro.value !== "..." &&
      ctx.cidade.value !== "..." &&
      ctx.estado.value !== "..." &&
      ctx.ibge.value !== "..."
    );
  }
  function invalido(texto) {
    ctx.status = "invalido";
    ctx.cepValidado = "";
    msg(texto || "CEP inválido.");
  }
  function buscar(valor) {
    var cep = digitos(valor);
    var script;

    if (!ctx) { return; }
    ctx.cep.value = formatar(cep);

    if (!cep) {
      limpar();
      ctx.status = "vazio";
      ctx.cepValidado = "";
      msg("");
      return;
    }
    if (cep.length !== 8) {
      limpar();
      invalido("CEP inválido.");
      return;
    }

    carregando();
    ctx.status = "carregando";
    ctx.cepConsultado = cep;
    msg("");

    script = document.createElement("script");
    script.src = "https://viacep.com.br/ws/" + cep + "/json/?callback=meu_callback";
    script.async = true;
    script.onerror = function () {
      limpar();
      invalido("Nao foi possivel validar o CEP.");
    };
    document.body.appendChild(script);
  }
  function podeSalvar() {
    var cep = digitos(ctx.cep.value);

    if (!cep) {
      ctx.status = "vazio";
      msg("");
      return true;
    }
    if (cep.length !== 8) {
      limpar();
      invalido("CEP inválido.");
      return false;
    }
    if (ctx.cepValidado !== cep && ctx.status !== "carregando") {
      buscar(ctx.cep.value);
      msg("Aguarde a validação do CEP.");
      return false;
    }
    if (ctx.status === "carregando") {
      msg("Aguarde a validação do CEP.");
      return false;
    }
    if (ctx.status !== "valido" || !enderecoOk()) {
      invalido("CEP inválido.");
      return false;
    }

    msg("");
    return true;
  }

  window["limpa_formulário_cep"] = function () { if (ctx) { limpar(); } };
  window.pesquisacep = buscar;
  window.meu_callback = function (conteudo) {
    if (!ctx) { return; }
    if (!conteudo || conteudo.erro) {
      limpar();
      invalido("CEP inválido.");
      return;
    }

    ctx.rua.value = conteudo.logradouro || "";
    ctx.bairro.value = conteudo.bairro || "";
    ctx.cidade.value = conteudo.localidade || "";
    ctx.estado.value = conteudo.uf || "";
    ctx.ibge.value = conteudo.ibge || "";

    if (!enderecoOk()) {
      limpar();
      invalido("CEP inválido.");
      return;
    }

    ctx.status = "valido";
    ctx.cepValidado = ctx.cepConsultado;
    msg("");
  };

  window.inicializarValidacaoCepFormulario = function (config) {
    var form = document.getElementById(config.formId);
    var cep = document.getElementById(config.cepId || "cep");
    var mensagem;

    if (!form || !cep) { return; }

    mensagem = document.createElement("p");
    mensagem.style.display = "none";
    mensagem.style.color = "#b30000";
    mensagem.style.margin = "6px 0 0";
    cep.parentNode.appendChild(mensagem);

    ctx = {
      formulario: form,
      cep: cep,
      rua: el(config.ruaIds || ["rua"]),
      bairro: el(config.bairroIds || ["bairro"]),
      cidade: el(config.cidadeIds || ["cidade"]),
      estado: el(config.estadoIds || ["uf", "estado"]),
      ibge: el(config.ibgeIds || ["ibge"]),
      mensagem: mensagem,
      status: "vazio",
      cepValidado: "",
      cepConsultado: ""
    };

    cep.onkeydown = null;
    cep.addEventListener("input", function () {
      cep.value = formatar(cep.value);
      if (!digitos(cep.value)) {
        limpar();
        ctx.status = "vazio";
      } else if (digitos(cep.value) !== ctx.cepValidado) {
        ctx.status = "pendente";
      }
      msg("");
    });
    cep.addEventListener("keydown", function (evento) {
      if (evento.key === "Enter") {
        evento.preventDefault();
        buscar(cep.value);
      }
    });
    form.addEventListener("submit", function (evento) {
      if (!podeSalvar()) {
        evento.preventDefault();
        cep.reportValidity();
        cep.focus();
      }
    });
  };
}(window, document));
