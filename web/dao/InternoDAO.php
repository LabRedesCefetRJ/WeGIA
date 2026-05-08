<?php
require_once('../classes/Interno.php');
require_once('Conexao.php');
require_once('../Functions/funcoes.php');

class InternoDAO
{
    public function formatoDataDMY($data)
    {
        $data_arr = explode("-", $data);

        $datad = $data_arr[2] . '/' . $data_arr[1] . '/' . $data_arr[0];

        return $datad;
    }
    public function incluir($interno)
    {
        $sql = 'call cadinterno(:nome,:sobrenome,:cpf,:senha,:sexo,:telefone,:data_nascimento,:imagem,:cep,:estado,:cidade,:bairro,:logradouro,:numero_endereco,:complemento,:ibge,:registro_geral,:orgao_emissor,:data_expedicao,:nome_pai,:nome_mae,:tipo_sanguineo,:nome_contato_urgente,:telefone_contato_urgente_1,:telefone_contato_urgente_2,:telefone_contato_urgente_3,:observacao,:certidao,:curatela,:inss,:loas,:bpc,:funrural,:saf,:sus,:certidao_casamento,:ctps,:titulo)';
        $sql = str_replace("'", "\'", $sql);
        $pdo = Conexao::connect();
        $stmt = $pdo->prepare($sql);
        $senha = $interno->getSenha();
        $nome = $interno->getNome();
        $sobrenome = $interno->getSobrenome();
        $cpf = $interno->getCpf();
        $sexo = $interno->getSexo();
        $telefone = $interno->getTelefone();
        $nascimento = $interno->getDataNascimento();
        $imagem = $interno->getImagem();
        $cep = $interno->getCep();
        $cidade = $interno->getCidade();
        $bairro = $interno->getBairro();
        $logradouro = $interno->getLogradouro();
        $numeroEndereco = $interno->getNumeroEndereco();
        $complemento = $interno->getComplemento();
        $rg = $interno->getRegistroGeral();
        $orgaoEmissor = $interno->getOrgaoEmissor();
        $nomePai = $interno->getNomePai();
        $nomeMae = $interno->getNomeMae();
        $sangue = $interno->getTipoSanguineo();
        $nomeContatoUrgente = $interno->getNomeContatoUrgente();
        $telefone1 = $interno->getTelefoneContatoUrgente1();
        $telefone2 = $interno->getTelefoneContatoUrgente2();
        $telefone3 = $interno->getTelefoneContatoUrgente3();
        $observacao = $interno->getObservacao();
        $ibge = $interno->getIbge();
        $dataExpedicao = $interno->getDataExpedicao();
        $certidao = $interno->getCertidaoNascimento();
        $curatela = $interno->getCuratela();
        $inss = $interno->getInss();
        $loas = $interno->getLoas();
        $bpc = $interno->getBpc();
        $funrural = $interno->getFunrural();
        $saf = $interno->getSaf();
        $sus = $interno->getSus();
        $certidaoCasamento = $interno->getCertidaoCasamento();
        $ctps = $interno->getCtps();
        $titulo = $interno->getTitulo();
        $estado = $interno->getEstado();

        $stmt->bindParam(':senha', $senha);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':sobrenome', $sobrenome);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->bindParam(':sexo', $sexo);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':data_nascimento', $nascimento);
        $stmt->bindParam(':imagem', $imagem);
        $stmt->bindParam(':cep', $cep);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':cidade', $cidade);
        $stmt->bindParam(':bairro', $bairro);
        $stmt->bindParam(':logradouro', $logradouro);
        $stmt->bindParam(':numero_endereco', $numeroEndereco);
        $stmt->bindParam(':complemento', $complemento);
        $stmt->bindParam(':registro_geral', $rg);
        $stmt->bindParam(':orgao_emissor', $orgaoEmissor);
        $stmt->bindParam(':data_expedicao', $dataExpedicao);
        $stmt->bindParam(':nome_pai', $nomePai);
        $stmt->bindParam(':nome_mae', $nomeMae);
        $stmt->bindParam(':tipo_sanguineo', $sangue);
        $stmt->bindParam(':nome_contato_urgente', $nomeContatoUrgente);
        $stmt->bindParam(':telefone_contato_urgente_1', $telefone1);
        $stmt->bindParam(':telefone_contato_urgente_2', $telefone2);
        $stmt->bindParam(':telefone_contato_urgente_3', $telefone3);
        $stmt->bindParam(':observacao', $observacao);
        $stmt->bindParam(':ibge', $ibge);
        $stmt->bindParam(':certidao', $certidao);
        $stmt->bindParam(':curatela', $curatela);
        $stmt->bindParam(':inss', $inss);
        $stmt->bindParam(':loas', $loas);
        $stmt->bindParam(':bpc', $bpc);
        $stmt->bindParam(':funrural', $funrural);
        $stmt->bindParam(':saf', $saf);
        $stmt->bindParam(':sus', $sus);
        $stmt->bindParam(':certidao_casamento', $certidaoCasamento);
        $stmt->bindParam(':ctps', $ctps);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->execute();
        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $idPessoa = $linha['MAX(id_pessoa)'];
        }
        return $idPessoa;
    }

    // excluir
    public function excluir($idinterno)
    {
        $sql = 'call excluirinterno(:idi)';
        $sql = str_replace("'", "\'", $sql);
        $pdo = Conexao::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':idi', $idinterno);

        $stmt->execute();
    }

    public function alterarImagem($interno)
    {
        $sql = 'update pessoa as p inner join interno as i on p.id_pessoa=i.id_pessoa set imagem=:imagem where id_interno=:id_interno';

        $sql = str_replace("'", "\'", $sql);
        $pdo = Conexao::connect();
        $stmt = $pdo->prepare($sql);

        $stmt = $pdo->prepare($sql);
        $imagem = $interno->getImagem();
        $id_interno = $interno->getIdInterno();

        $stmt->bindParam(':imagem', $imagem);
        $stmt->bindParam(':id_interno', $id_interno);
        $stmt->execute();
    }

    // Editar
    public function alterar($interno)
    {
        $sql = 'update pessoa as p inner join interno as i on p.id_pessoa=i.id_pessoa set p.senha=:senha,p.nome=:nome, p.sobrenome=:sobrenome,p.cpf=:cpf,p.sexo=:sexo,p.telefone=:telefone,p.data_nascimento=:data_nascimento,p.imagem=:imagem,p.cep=:cep,p.estado=:estado,p.cidade=:cidade,p.bairro=:bairro,p.logradouro=:logradouro,p.numero_endereco=:numero_endereco,p.complemento=:complemento,p.ibge=:ibge,p.registro_geral=:registro_geral,p.orgao_emissor=:orgao_emissor,p.data_expedicao=:data_expedicao,p.nome_pai=:nome_pai,p.nome_mae=:nome_mae,p.tipo_sanguineo=:tipo_sanguineo,i.nome_contato_urgente=:nome_contato_urgente,i.telefone_contato_urgente_1=:telefone_contato_urgente_1,i.telefone_contato_urgente_2=:telefone_contato_urgente_2,i.telefone_contato_urgente_3=:telefone_contato_urgente_3,i.observacao=:observacao,i.certidao_nascimento=:certidao,i.curatela=:curatela,i.inss=:inss,i.loas=:loas,i.bpc=:bpc,i.funrural=:funrural,i.saf=:saf,i.sus=:sus,i.certidao_casamento=:certidao_casamento,i.ctps=:ctps,i.titulo=:titulo where i.id_interno=:id_interno';

        $sql = str_replace("'", "\'", $sql);
        $pdo = Conexao::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare($sql);
        $certidao_casamento = $interno->getCertidaoCasamento();
        $ctps = $interno->getCtps();
        $titulo = $interno->getTitulo();
        $nome = $interno->getNome();
        $sobrenome = $interno->getsobrenome();
        $cpf = $interno->getCpf();
        $sexo = $interno->getSexo();
        $telefone = $interno->getTelefone();
        $nascimento = $interno->getDataNascimento();
        $imagem = $interno->getImagem();
        $cep = $interno->getCep();
        $cidade = $interno->getCidade();
        $bairro = $interno->getBairro();
        $logradouro = $interno->getLogradouro();
        $numeroEndereco = $interno->getNumeroEndereco();
        $complemento = $interno->getComplemento();
        $rg = $interno->getRegistroGeral();
        $orgaoEmissor = $interno->getOrgaoEmissor();
        $nomePai = $interno->getNomePai();
        $nomeMae = $interno->getNomeMae();
        $sangue = $interno->getTipoSanguineo();
        $nomeContatoUrgente = $interno->getNomeContatoUrgente();
        $telefone1 = $interno->getTelefoneContatoUrgente1();
        $telefone2 = $interno->getTelefoneContatoUrgente2();
        $telefone3 = $interno->getTelefoneContatoUrgente3();
        $ibge = $interno->getIbge();
        $dataExpedicao = $interno->getDataExpedicao();
        $certidao = $interno->getCertidaoNascimento();
        $curatela = $interno->getCuratela();
        $inss = $interno->getInss();
        $loas = $interno->getLoas();
        $bpc = $interno->getBpc();
        $funrural = $interno->getFunrural();
        $saf = $interno->getSaf();
        $sus = $interno->getSus();
        $idInterno = $interno->getIdInterno();
        $estado = $interno->getEstado();
        $observacao = $interno->getObservacao();

        $stmt->bindParam(':id_interno', $idInterno);
        $stmt->bindParam(':senha', $senha);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':sobrenome', $sobrenome);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->bindParam(':sexo', $sexo);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':data_nascimento', $nascimento);
        $stmt->bindParam(':imagem', $imagem);
        $stmt->bindParam(':cep', $cep);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':cidade', $cidade);
        $stmt->bindParam(':bairro', $bairro);
        $stmt->bindParam(':logradouro', $logradouro);
        $stmt->bindParam(':numero_endereco', $numeroEndereco);
        $stmt->bindParam(':complemento', $complemento);
        $stmt->bindParam(':registro_geral', $rg);
        $stmt->bindParam(':orgao_emissor', $orgaoEmissor);
        $stmt->bindParam(':data_expedicao', $dataExpedicao);
        $stmt->bindParam(':nome_pai', $nomePai);
        $stmt->bindParam(':nome_mae', $nomeMae);
        $stmt->bindParam(':tipo_sanguineo', $sangue);
        $stmt->bindParam(':nome_contato_urgente', $nomeContatoUrgente);
        $stmt->bindParam(':telefone_contato_urgente_1', $telefone1);
        $stmt->bindParam(':telefone_contato_urgente_2', $telefone2);
        $stmt->bindParam(':telefone_contato_urgente_3', $telefone3);
        $stmt->bindParam(':observacao', $observacao);
        $stmt->bindParam(':ibge', $ibge);
        $stmt->bindParam(':certidao', $certidao);
        $stmt->bindParam(':curatela', $curatela);
        $stmt->bindParam(':inss', $inss);
        $stmt->bindParam(':loas', $loas);
        $stmt->bindParam(':bpc', $bpc);
        $stmt->bindParam(':funrural', $funrural);
        $stmt->bindParam(':saf', $saf);
        $stmt->bindParam(':sus', $sus);
        $stmt->bindParam(':certidao_casamento', $certidao_casamento);
        $stmt->bindParam(':ctps', $ctps);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->execute();
    }
    public function listarTodos()
    {
        $internos = array();
        $pdo = Conexao::connect();
        $consulta = $pdo->query("SELECT p.nome,p.sobrenome,p.cpf,i.id_interno FROM pessoa p INNER JOIN interno i ON p.id_pessoa = i.id_pessoa");
        $produtos = array();
        $x = 0;
        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            if ($linha['cpf'] === "Não informado") {
                $internos[$x] = array('cpf' => $linha['cpf'], 'nome' => $linha['nome'], 'sobrenome' => $linha['sobrenome'], 'id' => $linha['id_interno']);
            } else {
                $internos[$x] = array('cpf' => mask($linha['cpf'], '###.###.###-##'), 'nome' => $linha['nome'], 'sobrenome' => $linha['sobrenome'], 'id' => $linha['id_interno']);
            }
            $x++;
        }

        return json_encode($internos);
    }


    public function listarTodos2()
    {
        $internos = array();
        $pdo = Conexao::connect();
        $consulta = $pdo->query("SELECT p.nome,p.sobrenome,p.cpf,i.id_interno FROM pessoa p INNER JOIN interno i ON p.id_pessoa = i.id_pessoa");
        $produtos = array();
        $x = 0;
        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            if ($linha['cpf'] === "Não informado") {
                $internos[$x] = array('cpf' => $linha['cpf'], 'nome' => $linha['nome'], 'sobrenome' => $linha['sobrenome'], 'id' => $linha['id_interno']);
            } else {
                $internos[$x] = array('cpf' => mask($linha['cpf'], '###.###.###-##'), 'nome' => $linha['nome'], 'sobrenome' => $linha['sobrenome'], 'id' => $linha['id_interno']);
            }
            $x++;
        }

        return $internos;
    }

    public function listar($id)
    {
        echo $id;
        $pdo = Conexao::connect();
        $sql = "SELECT p.imagem,p.nome,p.sobrenome,p.cpf, p.senha, p.sexo, p.telefone,p.data_nascimento, p.cep,p.cidade,p.bairro,p.logradouro,p.numero_endereco,p.complemento,p.ibge,p.registro_geral,p.orgao_emissor,p.data_expedicao,p.nome_pai,p.nome_mae,p.tipo_sanguineo,i.nome_contato_urgente,i.telefone_contato_urgente_1,i.telefone_contato_urgente_2,i.telefone_contato_urgente_3,i.observacao,i.certidao_nascimento,i.curatela,i.inss,i.loas,i.bpc,i.funrural,i.saf,i.sus,i.id_interno,i.certidao_casamento,i.ctps,i.titulo,d.imgdoc,d.descricao,d.id_documento FROM pessoa p LEFT JOIN interno i ON p.id_pessoa = i.id_pessoa left join documento d on p.id_pessoa=d.id_pessoa WHERE i.id_interno=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);

        $stmt->execute();
        $interno = array();
        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($linha['cpf'] === "Não informado") {
                $interno[] = array('ctps' => $linha['ctps'], 'titulo' => $linha['titulo'], 'casamento' => $linha['certidao_casamento'], 'imagem' => $linha['imagem'], 'nome' => $linha['nome'], 'sobrenome' => $linha['sobrenome'], 'cpf' => $linha['cpf'], 'senha' => $linha['senha'], 'sexo' => $linha['sexo'], 'telefone' => $linha['telefone'], 'data_nascimento' => $linha['data_nascimento'], 'cep' => $linha['cep'], 'cidade' => $linha['cidade'], 'bairro' => $linha['bairro'], 'logradouro' => $linha['logradouro'], 'numero_endereco' => $linha['numero_endereco'], 'complemento' => $linha['complemento'], 'ibge' => $linha['ibge'], 'registro_geral' => $linha['registro_geral'], 'orgao_emissor' => $linha['orgao_emissor'], 'data_expedicao' => $linha['data_expedicao'], 'nome_pai' => $linha['nome_pai'], 'nome_mae' => $linha['nome_mae'], 'tipo_sanguineo' => $linha['tipo_sanguineo'], 'nome_contato_urgente' => $linha['nome_contato_urgente'], 'telefone_contato_urgente_1' => $linha['telefone_contato_urgente_1'], 'telefone_contato_urgente_2' => $linha['telefone_contato_urgente_2'], 'telefone_contato_urgente_3' => $linha['telefone_contato_urgente_3'], 'observacao' => $linha['observacao'], 'certidao' => $linha['certidao_nascimento'], 'curatela' => $linha['curatela'], 'inss' => $linha['inss'], 'loas' => $linha['loas'], 'bpc' => $linha['bpc'], 'funrural' => $linha['funrural'], 'saf' => $linha['saf'], 'sus' => $linha['sus'], 'idInterno' => $linha['id_interno'], 'imgdoc' => $linha['imgdoc'], 'descricao' => $linha['descricao'], 'id_documento' => $linha['id_documento']);
            } else {
                $interno[] = array('ctps' => $linha['ctps'], 'titulo' => $linha['titulo'], 'casamento' => $linha['certidao_casamento'], 'imagem' => $linha['imagem'], 'nome' => $linha['nome'], 'sobrenome' => $linha['sobrenome'], 'cpf' => mask($linha['cpf'], '###.###.###-##'), 'senha' => $linha['senha'], 'sexo' => $linha['sexo'], 'telefone' => $linha['telefone'], 'data_nascimento' => $linha['data_nascimento'], 'cep' => $linha['cep'], 'cidade' => $linha['cidade'], 'bairro' => $linha['bairro'], 'logradouro' => $linha['logradouro'], 'numero_endereco' => $linha['numero_endereco'], 'complemento' => $linha['complemento'], 'ibge' => $linha['ibge'], 'registro_geral' => $linha['registro_geral'], 'orgao_emissor' => $linha['orgao_emissor'], 'data_expedicao' => $linha['data_expedicao'], 'nome_pai' => $linha['nome_pai'], 'nome_mae' => $linha['nome_mae'], 'tipo_sanguineo' => $linha['tipo_sanguineo'], 'nome_contato_urgente' => $linha['nome_contato_urgente'], 'telefone_contato_urgente_1' => $linha['telefone_contato_urgente_1'], 'telefone_contato_urgente_2' => $linha['telefone_contato_urgente_2'], 'telefone_contato_urgente_3' => $linha['telefone_contato_urgente_3'], 'observacao' => $linha['observacao'], 'certidao' => $linha['certidao_nascimento'], 'curatela' => $linha['curatela'], 'inss' => $linha['inss'], 'loas' => $linha['loas'], 'bpc' => $linha['bpc'], 'funrural' => $linha['funrural'], 'saf' => $linha['saf'], 'sus' => $linha['sus'], 'idInterno' => $linha['id_interno'], 'imgdoc' => $linha['imgdoc'], 'descricao' => $linha['descricao'], 'id_documento' => $linha['id_documento']);
            }
        }

        return json_encode($interno);
    }

    public function listarCPF()
    {
        $cpfs = array();
        $pdo = Conexao::connect();
        $consulta = $pdo->query("SELECT cpf from pessoa p INNER JOIN interno i ON(p.id_pessoa=i.id_pessoa)");
        $x = 0;
        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $cpfs[$x] = array('cpf' => $linha['cpf']);
            $x++;
        }

        return json_encode($cpfs);
    }
}
