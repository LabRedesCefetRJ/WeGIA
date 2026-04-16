(function (window, document) {
  "use strict";

  const digitos = (valor) => (valor || "").replace(/\D/g, "");

  const formatar = (valor) => {
    const cep = digitos(valor).slice(0, 8);
    return cep.length > 5 ? cep.slice(0, 5) + "-" + cep.slice(5) : cep;
  };

  const el = (ids) => {
    for (let i = 0; i < ids.length; i += 1) {
      const campo = document.getElementById(ids[i]);
      if (campo) return campo;
    }
    return null;
  };

  window.inicializarValidacaoCepFormulario = function (config) {
    const form = document.getElementById(config.formId);
    const cep = document.getElementById(config.cepId || "cep");

    if (!form || !cep) return;

    const campos = {
      rua: el(config.ruaIds || ["rua"]),
      bairro: el(config.bairroIds || ["bairro"]),
      cidade: el(config.cidadeIds || ["cidade"]),
      estado: el(config.estadoIds || ["uf", "estado"]),
      ibge: el(config.ibgeIds || ["ibge"])
    };

    const mensagem = document.createElement("p");
    mensagem.style.display = "none";
    mensagem.style.color = "#b30000";
    mensagem.style.margin = "6px 0 0";
    cep.parentNode.appendChild(mensagem);

    let status = "vazio";
    let cepValidado = "";

    const msg = (texto) => {
      mensagem.textContent = texto || "";
      mensagem.style.display = texto ? "block" : "none";
      cep.setCustomValidity(texto || "");
    };

    const limpar = () => {
      Object.values(campos).forEach(campo => { if (campo) campo.value = ""; });
    };

    const carregando = () => {
      Object.values(campos).forEach(campo => { if (campo) campo.value = "..."; });
    };

    const enderecoOk = () => {
      return Object.values(campos).every(campo => 
        campo && campo.value.trim() !== "" && campo.value !== "..."
      );
    };

    const invalido = (texto) => {
      status = "invalido";
      cepValidado = "";
      msg(texto || "CEP inválido.");
    };

    const buscar = async (valor) => {
      const cepLimpo = digitos(valor);
      cep.value = formatar(cepLimpo);

      if (!cepLimpo) {
        limpar();
        status = "vazio";
        cepValidado = "";
        msg("");
        return false;
      }

      if (cepLimpo.length !== 8) {
        limpar();
        invalido("CEP inválido.");
        return false;
      }

      carregando();
      status = "carregando";
      msg("");

      try {
        const response = await fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`);
        const conteudo = await response.json();

        if (conteudo.erro) {
          limpar();
          invalido("CEP inválido.");
          return;
        }

        if (campos.rua) campos.rua.value = conteudo.logradouro || "";
        if (campos.bairro) campos.bairro.value = conteudo.bairro || "";
        if (campos.cidade) campos.cidade.value = conteudo.localidade || "";
        if (campos.estado) campos.estado.value = conteudo.uf || "";
        if (campos.ibge) campos.ibge.value = conteudo.ibge || "";

        if (!enderecoOk()) {
          limpar();
          invalido("CEP inválido.");
          return;
        }

        status = "valido";
        cepValidado = cepLimpo;
        msg("");
        return true;

      } catch (error) {
        limpar();
        invalido("Não foi possível validar o CEP.");
        return false;
      }
    };

    cep.addEventListener("input", () => {
      cep.value = formatar(cep.value);
      const apenasDigitos = digitos(cep.value);
      
      if (!apenasDigitos) {
        limpar();
        status = "vazio";
      } else if (apenasDigitos !== cepValidado) {
        status = "pendente";
      }
      msg("");
    });

    cep.addEventListener("keydown", (evento) => {
      if (evento.key === "Enter") {
        evento.preventDefault();
        buscar(cep.value);
      }
    });

form.addEventListener("submit", async (evento) => {
      const atualCep = digitos(cep.value);

      if (!atualCep) return; 

      if (status === "valido" && atualCep === cepValidado) return;

      evento.preventDefault();

      if (status === "carregando") return; 

      const sucesso = await buscar(atualCep);

      if (sucesso) {

        form.submit();
      } else {
        cep.reportValidity();
        cep.focus();
      }
    });
  };
}(window, document));