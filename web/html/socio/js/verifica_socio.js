//Verificação de existência de um sócio no formulário de cadastro
// Variável para armazenar o ID da pessoa encontrada
let idPessoaEncontrada = null;

document.getElementById('cpf_cnpj').addEventListener('blur', async function () {
    const documento = this.value.trim();

    if (documento === "") {
        desbloquearCamposPessoa(); // Desbloqueia os campos se o CPF for removido
        idPessoaEncontrada = null; // Limpa o ID da pessoa
        return; // evita requisição vazia
    }

    //Ir para o controller
    try {
        const urlSocio = "../../contribuicao/controller/control.php" +
            "?nomeClasse=SocioController&metodo=buscarPorDocumento" +
            "&documento=" + encodeURIComponent(documento);

        const responseSocio = await fetch(urlSocio, { method: "GET" });

        if (!responseSocio.ok) {
            console.error("Erro ao consultar sócio:", responseSocio.status);
            return;
        }

        const jsonSocio = await responseSocio.json();

        if (jsonSocio.resultado !== "Sócio não encontrado") {
            alert("⚠️ Este CPF/CNPJ já pertence a um sócio cadastrado!");
        }

        //pesquisar se existe alguma pessoa no sistema com o documento informado, se sim, preencher os campos do formulário com os dados encontrados, caso contrário, deixar os campos em branco para o usuário preencher
        const urlPessoa = "../../../controle/control.php" +
            "?nomeClasse=PessoaControle&metodo=buscarPorDocumento" +
            "&documento=" + encodeURIComponent(documento);

        const responsePessoa = await fetch(urlPessoa, { method: "GET" });

        if (!responsePessoa.ok && responsePessoa.status !== 404) { // 404 é esperado quando a pessoa não é encontrada
            console.error("Erro ao consultar pessoa:", responsePessoa.status);
            return;
        }

        // Se a pessoa não foi encontrada (404), não fazer nada
        if (responsePessoa.status === 404) {
            desbloquearCamposPessoa();
            idPessoaEncontrada = null; // Limpa o ID se a pessoa não for encontrada
            return;
        }

        const jsonPessoa = await responsePessoa.json();

        // Armazenar o ID da pessoa encontrada
        idPessoaEncontrada = jsonPessoa.id_pessoa || null;

        // Preencher os campos do formulário com os dados encontrados
        if (jsonPessoa.nome) {
            document.getElementById('socio_nome').value = jsonPessoa.nome || "";
        }

        if (jsonPessoa.sobrenome) {
            document.getElementById('socio_sobrenome').value = jsonPessoa.sobrenome || "";
        }

        if (jsonPessoa.email) {
            document.getElementById('email').value = jsonPessoa.email || "";
        }

        if (jsonPessoa.telefone) {
            document.getElementById('telefone').value = jsonPessoa.telefone || "";
        }

        if (jsonPessoa.data_nascimento) {
            // Formatar a data de nascimento para o formato esperado pelo input date (YYYY-MM-DD)
            const dataNasc = formatarDataParaInput(jsonPessoa.data_nascimento);
            document.getElementById('data_nasc').value = dataNasc;
        }

        if (jsonPessoa.cep) {
            document.getElementById('cep').value = jsonPessoa.cep || "";
        }

        if (jsonPessoa.bairro) {
            document.getElementById('bairro').value = jsonPessoa.bairro || "";
        }

        if(jsonPessoa.logradouro) {
            document.getElementById('rua').value = jsonPessoa.logradouro || "";
        }

        if (jsonPessoa.estado) {
            document.getElementById('estado').value = jsonPessoa.estado || "";
        }

        if (jsonPessoa.cidade) {
            document.getElementById('cidade').value = jsonPessoa.cidade || "";
        }

        if (jsonPessoa.numero_endereco) {
            document.getElementById('numero').value = jsonPessoa.numero_endereco || "";
        }

        // Bloquear os campos que foram preenchidos com os dados da pessoa
        bloquearCamposPessoa();

    } catch (e) {
        console.error("Erro na requisição:", e);
    }
});

/**
 * Formata a data para o formato esperado pelo input type="date" (YYYY-MM-DD)
 * @param {string} dataString - Data em formato ISO (YYYY-MM-DD) ou outro formato
 * @returns {string} - Data formatada para YYYY-MM-DD
 */
function formatarDataParaInput(dataString) {
    if (!dataString) return "";

    // Se já está no formato YYYY-MM-DD, retorna direto
    if (/^\d{4}-\d{2}-\d{2}$/.test(dataString)) {
        return dataString;
    }

    // Tenta converter de outros formatos
    const date = new Date(dataString);
    if (isNaN(date.getTime())) {
        return ""; // Data inválida
    }

    // Formata para YYYY-MM-DD
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

/**
 * Bloqueia os campos de dados pessoais para edição
 */
function bloquearCamposPessoa() {
    const camposParaBloq = [
        'socio_nome',
        'socio_sobrenome',
        //'email',
        'telefone',
        'data_nasc',
        'cep',
        'bairro',
        'rua',
        'estado',
        'cidade',
        'numero'
    ];

    camposParaBloq.forEach(id => {
        const elemento = document.getElementById(id);
        if (elemento) {
            elemento.readOnly = true;
            elemento.classList.add('campo-bloqueado');
        }
    });
}

/**
 * Desbloqueia os campos de dados pessoais para edição
 */
function desbloquearCamposPessoa() {
    const camposParaBloq = [
        'socio_nome',
        'socio_sobrenome',
        'email',
        'telefone',
        'data_nasc',
        'cep',
        'bairro',
        'rua',
        'estado',
        'cidade',
        'numero'
    ];

    camposParaBloq.forEach(id => {
        const elemento = document.getElementById(id);
        if (elemento) {
            elemento.readOnly = false;
            elemento.classList.remove('campo-bloqueado');
        }
    });
}

/**
 * Listener para o envio do formulário de novo sócio
 * Se uma pessoa foi encontrada, redireciona para um fluxo diferente
 */
document.addEventListener('DOMContentLoaded', function() {
    const formNoveSocio = document.getElementById('frm_novo_socio');
    
    if (formNoveSocio) {
        formNoveSocio.addEventListener('submit', function(e) {
            // Se uma pessoa foi encontrada, usar um fluxo diferente
            if (idPessoaEncontrada !== null && idPessoaEncontrada !== undefined) {
                e.preventDefault();
                
                // Criar um campo hidden para armazenar o ID da pessoa
                let inputIdPessoa = document.getElementById('id_pessoa_encontrada');
                
                if (!inputIdPessoa) {
                    inputIdPessoa = document.createElement('input');
                    inputIdPessoa.type = 'hidden';
                    inputIdPessoa.id = 'id_pessoa_encontrada';
                    inputIdPessoa.name = 'id_pessoa_encontrada';
                    formNoveSocio.appendChild(inputIdPessoa);
                }
                
                inputIdPessoa.value = idPessoaEncontrada;
                
                // Enviar o formulário manualmente
                formNoveSocio.submit();
            }
        });
    }
});
