//Verificação de existência de um sócio no formulário de cadastro
// Variável para armazenar o ID da pessoa encontrada
let idPessoaEncontrada = null;

document.getElementById('cpf_cnpj').addEventListener('blur', async function () {
    const documento = this.value.trim();

    if (documento === "") {
        desbloquearCamposPessoa();
        idPessoaEncontrada = null;
        return;
    }

    try {

        const urlSocio =
            "../../contribuicao/controller/control.php" +
            "?nomeClasse=SocioController&metodo=buscarPorDocumento" +
            "&documento=" + encodeURIComponent(documento);

        const responseSocio = await fetch(urlSocio, { method: "GET" });
        const jsonSocio = await responseSocio.json();

        // Documento já pertence a um sócio
        if (responseSocio.ok) {
            alert("⚠️ Este CPF/CNPJ já pertence a um sócio cadastrado!");
            return;
        }

        // Erro inesperado
        if (responseSocio.status !== 404) {
            console.error(
                "Erro ao consultar sócio:",
                jsonSocio?.resultado || responseSocio.statusText
            );
            return;
        }

        // Não existe pessoa cadastrada, não consultar PessoaController
        if (!jsonSocio.pessoaExists) {
            desbloquearCamposPessoa();
            idPessoaEncontrada = null;
            return;
        }

        // Existe uma pessoa cadastrada, buscar seus dados
        const urlPessoa =
            "../../../controle/control.php" +
            "?nomeClasse=PessoaControle&metodo=buscarPorDocumento" +
            "&documento=" + encodeURIComponent(documento);

        const responsePessoa = await fetch(urlPessoa, { method: "GET" });

        if (!responsePessoa.ok) {
            console.error("Erro ao consultar pessoa:", responsePessoa.status);
            return;
        }

        const jsonPessoa = await responsePessoa.json();

        idPessoaEncontrada = jsonPessoa.id_pessoa || null;

        if (jsonPessoa.nome) {
            document.getElementById('socio_nome').value = jsonPessoa.nome;
        }

        if (jsonPessoa.sobrenome) {
            document.getElementById('socio_sobrenome').value = jsonPessoa.sobrenome;
        }

        if (jsonPessoa.email) {
            document.getElementById('email').value = jsonPessoa.email;
        }

        if (jsonPessoa.telefone) {
            document.getElementById('telefone').value = jsonPessoa.telefone;
        }

        if (jsonPessoa.data_nascimento) {
            document.getElementById('data_nasc').value =
                formatarDataParaInput(jsonPessoa.data_nascimento);
        }

        if (jsonPessoa.cep) {
            document.getElementById('cep').value = jsonPessoa.cep;
        }

        if (jsonPessoa.bairro) {
            document.getElementById('bairro').value = jsonPessoa.bairro;
        }

        if (jsonPessoa.logradouro) {
            document.getElementById('rua').value = jsonPessoa.logradouro;
        }

        if (jsonPessoa.estado) {
            document.getElementById('estado').value = jsonPessoa.estado;
        }

        if (jsonPessoa.cidade) {
            document.getElementById('cidade').value = jsonPessoa.cidade;
        }

        if (jsonPessoa.numero_endereco) {
            document.getElementById('numero').value = jsonPessoa.numero_endereco;
        }

        if (jsonPessoa.complemento) {
            document.getElementById('complemento').value = jsonPessoa.complemento;
        }

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
        'numero',
        'complemento'
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
        'numero',
        'complemento'
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
