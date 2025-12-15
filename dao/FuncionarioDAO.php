<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once ROOT . "/dao/Conexao.php";
require_once ROOT . "/classes/Funcionario.php";
require_once ROOT . "/Functions/funcoes.php";

class FuncionarioDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        is_null($pdo) ? $this->pdo = Conexao::connect() : $this->pdo = $pdo;
    }

    public function verificaAdm($id)
    {
        $buscaAdm = 'SELECT * FROM pessoa WHERE id_pessoa=:id AND adm_configurado=1';

        $stmt = $this->pdo->prepare($buscaAdm);
        $stmt->bindParam(':id', $id);

        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            return true;
        }

        return false;
    }

    public function getIdFuncionarioComIdPessoa($idPessoa)
    {
        $stmt = $this->pdo->prepare("
                SELECT id_funcionario 
                FROM pessoa p 
                JOIN funcionario f ON (p.id_pessoa = f.id_pessoa) 
                WHERE f.id_pessoa = :id_pessoa
            ");
        $stmt->bindParam(":id_pessoa", $idPessoa);
        $stmt->execute();
        $func = $stmt->fetch(PDO::FETCH_ASSOC);

        return $func["id_funcionario"];
    }

    public function listarIdPessoa($cpf)
    {
        try {
            $stmtConsulta = $this->pdo->prepare("SELECT id_pessoa from pessoa WHERE cpf=:cpf");
            $stmtConsulta->bindParam(':cpf', $cpf);
            $stmtConsulta->execute();
            $linha = $stmtConsulta->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Util::tratarException($e);
        }

        return $linha['id_pessoa'];
    }

    public function listarSobrenome($cpf)
    {
        try {
            $stmtConsulta = $this->pdo->prepare("SELECT sobrenome from pessoa WHERE cpf=:cpf");
            $stmtConsulta->bindParam(':cpf', $cpf);
            $stmtConsulta->execute();
            $linha = $stmtConsulta->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Util::tratarException($e);
        }

        return $linha['sobrenome'];
    }

    public function listarSituacao($cpf)
    {
        try {
            $stmtConsulta = $this->pdo->prepare("SELECT situacao from pessoa WHERE cpf=:cpf");
            $stmtConsulta->bindParam(':cpf', $cpf);
            $stmtConsulta->execute();
            $linha = $stmtConsulta->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Util::tratarException($e);
        }

        return $linha['situacao'];
    }

    public function formatoDataDMY($data)
    {
        if ($data) {
            $data_arr = explode("-", $data);

            $datad = $data_arr[2] . '/' . $data_arr[1] . '/' . $data_arr[0];

            return $datad;
        }
        return "Sem informação";
    }

    public function selecionarCadastro(string $cpf)
    {
        $sql = "
        SELECT 
            p.id_pessoa,
            f.id_pessoa AS funcionario_id
        FROM pessoa p
        LEFT JOIN funcionario f ON f.id_pessoa = p.id_pessoa
        WHERE p.cpf = :cpf
        LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':cpf', $cpf, PDO::PARAM_STR);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado;
    }


    public function incluir($funcionario, $cpf)
    {
        $sql = 'call cadfuncionario(:nome,:sobrenome,:cpf,:senha,:sexo,:telefone,:data_nascimento,:imagem,:cep,:estado,:cidade,:bairro,:logradouro,:numero_endereco,:complemento,:ibge,:registro_geral,:orgao_emissor,:data_expedicao,:nome_pai,:nome_mae,:tipo_sangue,:data_admissao,:pis,:ctps,:uf_ctps,:numero_titulo,:zona,:secao,:certificado_reservista_numero,:certificado_reservista_serie,:id_situacao,:id_cargo)';

        $sql = str_replace("'", "\'", $sql);

        $stmt = $this->pdo->prepare($sql);
        $nome = $funcionario->getNome();
        $sobrenome = $funcionario->getSobrenome();
        // $cpf=$funcionario->getCpf();
        $senha = $funcionario->getSenha();
        $sexo = $funcionario->getSexo();
        $telefone = $funcionario->getTelefone();
        $nascimento = $funcionario->getDataNascimento();
        $imagem = $funcionario->getImagem();
        $cep = $funcionario->getCep();
        $estado = $funcionario->getEstado();
        $cidade = $funcionario->getCidade();
        $bairro = $funcionario->getBairro();
        $logradouro = $funcionario->getLogradouro();
        $numeroEndereco = $funcionario->getNumeroEndereco();
        $complemento = $funcionario->getComplemento();
        $ibge = $funcionario->getIbge();
        $rg = $funcionario->getRegistroGeral();
        $orgaoEmissor = $funcionario->getOrgaoEmissor();
        $dataExpedicao = $funcionario->getDataExpedicao();
        $nomePai = $funcionario->getNomePai();
        $nomeMae = $funcionario->getNomeMae();
        $sangue = $funcionario->getTipoSanguineo();
        //$valeTransporte=$funcionario->getVale_transporte();
        $dataAdmissao = $funcionario->getData_admissao();
        $pis = $funcionario->getPis();
        $ctps = $funcionario->getCtps();
        $ufCtps = $funcionario->getUf_ctps();
        $numeroTitulo = $funcionario->getNumero_titulo();
        $zona = $funcionario->getZona();
        $secao = $funcionario->getSecao();
        $certificadoReservistaNumero = $funcionario->getCertificado_reservista_numero();
        $certificadoReservistaSerie = $funcionario->getCertificado_reservista_serie();
        $situacao = $funcionario->getId_situacao();
        $cargo = $funcionario->getId_cargo();

        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':sobrenome', $sobrenome);
        $stmt->bindParam(':id_cargo', $cargo);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->bindParam(':senha', $senha);
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
        $stmt->bindParam(':ibge', $ibge);
        $stmt->bindParam(':registro_geral', $rg);
        $stmt->bindParam(':orgao_emissor', $orgaoEmissor);
        $stmt->bindParam(':nome_pai', $nomePai);
        $stmt->bindParam(':nome_mae', $nomeMae);
        $stmt->bindParam(':tipo_sangue', $sangue);
        //$stmt->bindParam(':vale_transporte', $valeTransporte);
        $stmt->bindParam(':data_admissao', $dataAdmissao);
        $stmt->bindParam(':pis', $pis);
        $stmt->bindParam(':ctps', $ctps);
        $stmt->bindParam(':uf_ctps', $ufCtps);
        $stmt->bindParam(':numero_titulo', $numeroTitulo);
        $stmt->bindParam(':zona', $zona);
        $stmt->bindParam(':secao', $secao);
        $stmt->bindParam(':certificado_reservista_numero', $certificadoReservistaNumero);
        $stmt->bindParam(':certificado_reservista_serie', $certificadoReservistaSerie);
        $stmt->bindParam(':id_situacao', $situacao);
        $stmt->bindParam(':data_expedicao', $dataExpedicao);
        $stmt->execute();
    }

    // incluirExistente

    public function incluirExistente($funcionario, $idPessoa, $sobrenome)
    {
        $sql = "UPDATE pessoa set sobrenome=:sobrenome, sexo=:sexo,orgao_emissor=:orgao_emissor,registro_geral=:registro_geral,data_expedicao=:data_expedicao WHERE id_pessoa=:id_pessoa;";

        $sql2 = "INSERT INTO funcionario(id_pessoa,id_cargo,id_situacao,data_admissao,certificado_reservista_numero,certificado_reservista_serie, ctps)
            values(:id_pessoa,:id_cargo,:id_situacao,:data_admissao,:certificado_reservista_numero,:certificado_reservista_serie, 'NULL')";

        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare($sql);
        $stmt2 = $this->pdo->prepare($sql2);

        $nome = $funcionario->getNome();
        $sobrenome = $funcionario->getSobrenome();
        $cpf = $funcionario->getCpf();
        $sexo = $funcionario->getSexo();
        $telefone = $funcionario->getTelefone();
        $nascimento = $funcionario->getDataNascimento();
        $imagem = $funcionario->getImagem();
        $rg = $funcionario->getRegistroGeral();
        $orgao_emissor = $funcionario->getOrgaoEmissor();
        $data_expedicao = $funcionario->getDataExpedicao();
        $dataAdmissao = $funcionario->getData_admissao();
        $certificadoReservistaNumero = $funcionario->getCertificado_reservista_numero();
        $certificadoReservistaSerie = $funcionario->getCertificado_reservista_serie();
        $situacao = $funcionario->getId_situacao();
        $cargo = $funcionario->getId_cargo();

        $stmt->bindParam(':id_pessoa', $idPessoa);
        $stmt->bindParam(':registro_geral', $rg);
        $stmt->bindParam(':orgao_emissor', $orgao_emissor);
        $stmt->bindParam(':data_expedicao', $data_expedicao);
        $stmt->bindParam(':sobrenome', $sobrenome);
        $stmt->bindParam(':sexo', $sexo);

        $stmt2->bindParam(':id_pessoa', $idPessoa);
        $stmt2->bindParam(':id_cargo', $cargo);
        $stmt2->bindParam(':id_situacao', $situacao);
        $stmt2->bindParam(':data_admissao', $dataAdmissao);
        $stmt2->bindParam(':certificado_reservista_numero', $certificadoReservistaNumero);
        $stmt2->bindParam(':certificado_reservista_serie', $certificadoReservistaSerie);

        $stmt->execute();
        if ($stmt2->execute()) {
            $this->pdo->commit();
        } else {
            $this->pdo->rollBack();
            throw new LogicException('Erro, não foi possível concluir a operação de cadastro.', 500);
        }
    }

    // excluir
    public function excluir($id_funcionario)
    {
        try {
            // $sql = 'call excluirfuncionario(:id_funcionario)';
            $sql = 'UPDATE funcionario set id_situacao = 2 where id_funcionario = :id_funcionario';
            $sql = str_replace("'", "\'", $sql);

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':id_funcionario', $id_funcionario);

            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    // Editar
    public function alterarInfPessoal($funcionario)
    {
        $sql = 'update pessoa as p inner join funcionario as f on p.id_pessoa=f.id_pessoa set nome=:nome,sobrenome=:sobrenome,sexo=:sexo,telefone=:telefone,data_nascimento=:data_nascimento,nome_pai=:nome_pai,nome_mae=:nome_mae,tipo_sanguineo=:tipo_sanguineo where id_funcionario=:id_funcionario';

        $sql = str_replace("'", "\'", $sql);

        $stmt = $this->pdo->prepare($sql);

        $stmt = $this->pdo->prepare($sql);
        $nome = $funcionario->getNome();
        $sobrenome = $funcionario->getSobrenome();
        $id_funcionario = $funcionario->getId_funcionario();
        $sexo = $funcionario->getSexo();
        $telefone = $funcionario->getTelefone();
        $nascimento = $funcionario->getDataNascimento();
        $nomePai = $funcionario->getNomePai();
        $nomeMae = $funcionario->getNomeMae();
        $sangue = $funcionario->getTipoSanguineo();

        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':sobrenome', $sobrenome);
        $stmt->bindParam(':id_funcionario', $id_funcionario);
        $stmt->bindParam(':sexo', $sexo);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':data_nascimento', $nascimento);
        $stmt->bindParam(':nome_pai', $nomePai);
        $stmt->bindParam(':nome_mae', $nomeMae);
        $stmt->bindParam(':tipo_sanguineo', $sangue);
        $stmt->execute();
    }

    public function alterarImagem($id_funcionario, $imagem)
    {
        $imagem = base64_encode($imagem);
        try {

            $id_pessoa = (($this->pdo->query("SELECT id_pessoa FROM funcionario WHERE id_funcionario=$id_funcionario"))->fetch(PDO::FETCH_ASSOC))["id_pessoa"];

            $sql = "UPDATE pessoa SET imagem = :imagem WHERE id_pessoa = :id_pessoa;";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_pessoa', $id_pessoa);
            $stmt->bindValue(':imagem', $imagem);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoa = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    public function alterarSenha($id_pessoa, $nova_senha)
    {
        try {
            $sql = 'update pessoa set senha=:nova_senha where id_pessoa=:id_pessoa';

            $sql = str_replace("'", "\'", $sql);

            $stmt = $this->pdo->prepare($sql);

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':id_pessoa', $id_pessoa);
            $stmt->bindParam(':nova_senha', $nova_senha);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    public function alterarEndereco($funcionario)
    {
        try {
            $sql = 'update pessoa as p inner join funcionario as f on p.id_pessoa=f.id_pessoa set cep=:cep,estado=:estado,cidade=:cidade,bairro=:bairro,logradouro=:logradouro,numero_endereco=:numero_endereco,complemento=:complemento,ibge=:ibge where id_funcionario=:id_funcionario';

            $sql = str_replace("'", "\'", $sql);


            $stmt = $this->pdo->prepare($sql);

            $id_funcionario = $funcionario->getId_funcionario();
            $cep = $funcionario->getCep();
            $estado = $funcionario->getEstado();
            $cidade = $funcionario->getCidade();
            $bairro = $funcionario->getBairro();
            $logradouro = $funcionario->getLogradouro();
            $numero_endereco = $funcionario->getNumeroEndereco();
            $complemento = $funcionario->getComplemento();
            $ibge = $funcionario->getIbge();

            $stmt->bindParam(':id_funcionario', $id_funcionario);
            $stmt->bindParam(':cep', $cep);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':cidade', $cidade);
            $stmt->bindParam(':bairro', $bairro);
            $stmt->bindParam(':logradouro', $logradouro);
            $stmt->bindParam(':numero_endereco', $numero_endereco);
            $stmt->bindParam(':complemento', $complemento);
            $stmt->bindParam(':ibge', $ibge);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    public function alterarDocumentacao($funcionario)
    {
        try {

            $sql = 'update pessoa as p inner join funcionario as f on p.id_pessoa=f.id_pessoa set registro_geral=:registro_geral,orgao_emissor=:orgao_emissor,data_expedicao=:data_expedicao where id_funcionario=:id_funcionario';

            $sql = str_replace("'", "\'", $sql);


            $stmt = $this->pdo->prepare($sql);

            //$cpf=$funcionario->getCpf();
            $id_funcionario = $funcionario->getId_funcionario();
            $registro_geral = $funcionario->getRegistroGeral();
            $orgao_emissor = $funcionario->getOrgaoEmissor();
            $data_expedicao = $funcionario->getDataExpedicao();
            $data_admissao = $funcionario->getData_admissao();

            //$stmt->bindParam(':cpf',$cpf);
            $stmt->bindParam(':id_funcionario', $id_funcionario);
            $stmt->bindParam(':registro_geral', $registro_geral);
            $stmt->bindParam(':orgao_emissor', $orgao_emissor);
            $stmt->bindParam(':data_expedicao', $data_expedicao);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    public function alterarOutros($funcionario)
    {
        try {

            $sql = 'update pessoa as p inner join funcionario as f on p.id_pessoa=f.id_pessoa set f.pis=:pis,f.ctps=:ctps,f.uf_ctps=:uf_ctps,f.numero_titulo=:numero_titulo,f.zona=:zona, f.secao=:secao,f.certificado_reservista_numero=:certificado_reservista_numero,f.certificado_reservista_serie=:certificado_reservista_serie,f.id_situacao=:id_situacao,f.id_cargo=:id_cargo where f.id_funcionario=:id_funcionario';

            $sql = str_replace("'", "\'", $sql);


            $stmt = $this->pdo->prepare($sql);

            $id_funcionario = $funcionario->getId_funcionario();
            $id_cargo = $funcionario->getId_cargo();
            $pis = $funcionario->getPis();
            $ctps = $funcionario->getCtps();
            $uf_ctps = $funcionario->getUf_ctps();
            $numero_titulo = $funcionario->getNumero_titulo();
            $zona = $funcionario->getZona();
            $secao = $funcionario->getSecao();
            $certificado_reservista_numero = $funcionario->getCertificado_reservista_numero();
            $certificado_reservista_serie = $funcionario->getCertificado_reservista_serie();
            $id_situacao = $funcionario->getId_situacao();

            if ($id_situacao == 2) {
                $id_cargo = 2;
            }
            $stmt->bindParam(':id_funcionario', $id_funcionario);
            $stmt->bindParam(':id_cargo', $id_cargo);
            $stmt->bindParam(':pis', $pis);
            $stmt->bindParam(':ctps', $ctps);
            $stmt->bindParam(':uf_ctps', $uf_ctps);
            $stmt->bindParam(':numero_titulo', $numero_titulo);
            $stmt->bindParam(':zona', $zona);
            $stmt->bindParam(':secao', $secao);
            $stmt->bindParam(':certificado_reservista_numero', $certificado_reservista_numero);
            $stmt->bindParam(':certificado_reservista_serie', $certificado_reservista_serie);
            $stmt->bindParam(':id_situacao', $id_situacao);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }


    public function listarTodos($situacao)
    {
        $funcionarios = array();

        $consulta = $this->pdo->prepare("SELECT f.id_funcionario, p.nome, p.sobrenome,p.cpf, c.cargo, s.situacoes FROM pessoa p 
            JOIN funcionario f ON p.id_pessoa = f.id_pessoa JOIN cargo c ON c.id_cargo=f.id_cargo JOIN situacao s 
            ON f.id_situacao=s.id_situacao where s.id_situacao =:situacao");
        $consulta->bindParam(':situacao', $situacao);
        $consulta->execute();

        $x = 0;
        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $funcionarios[$x] = array('id_funcionario' => htmlspecialchars($linha['id_funcionario']), 'cpf' => htmlspecialchars($linha['cpf']), 'nome' => htmlspecialchars($linha['nome']), 'sobrenome' => htmlspecialchars($linha['sobrenome']), 'situacao' => htmlspecialchars($linha['situacoes']), 'cargo' => htmlspecialchars($linha['cargo']));
            $x++;
        }

        return $funcionarios;
    }

    public function listarTodos2()
    {
        require_once ROOT . "/dao/memorando/UsuarioDAO.php";
        $usuario = new UsuarioDAO();
        $id_usuario = $usuario->obterUsuario($_SESSION['usuario'])[0]["id_pessoa"];
        $funcionarios = array();

        $consulta = $this->pdo->prepare("SELECT p.id_pessoa, p.nome, p.sobrenome FROM funcionario f INNER JOIN pessoa p ON f.id_pessoa = p.id_pessoa WHERE p.id_pessoa!=:idUsuario");
        $consulta->bindValue(':idUsuario', $id_usuario);
        $consulta->execute();

        $x = 0;
        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $funcionarios[$x] = array('id_pessoa' => htmlspecialchars($linha['id_pessoa']), 'nome' => htmlspecialchars($linha['nome']), 'sobrenome' => htmlspecialchars($linha['sobrenome']));
            $x++;
        }

        return $funcionarios;
    }

    public function listarCPF()
    {
        $cpfs = array();

        $consulta = $this->pdo->query("SELECT f.id_funcionario, p.cpf from pessoa p INNER JOIN funcionario f ON(p.id_pessoa=f.id_pessoa)");
        $x = 0;
        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $cpfs[$x] = array('cpf' => $linha['cpf'], 'id' => $linha['id_funcionario']);
            $x++;
        }

        return $cpfs;
    }

    //Consultar um utilizando o id
    public function listar($id_funcionario)
    {
        $sql = "SELECT p.imagem,p.nome,p.sobrenome,p.cpf,p.senha,p.sexo,p.telefone,p.data_nascimento,p.cep,p.ibge,p.estado,p.cidade,p.bairro,p.logradouro,p.numero_endereco,p.complemento,p.ibge,p.registro_geral,p.orgao_emissor,p.data_expedicao,p.nome_pai,p.nome_mae,p.tipo_sanguineo,f.id_funcionario,f.data_admissao,f.pis,f.ctps,f.uf_ctps,f.numero_titulo,f.zona,f.secao,f.certificado_reservista_numero,f.certificado_reservista_serie,s.id_situacao,s.situacoes,c.id_cargo,c.cargo,qh.escala,qh.tipo,qh.carga_horaria,qh.entrada1,qh.saida1,qh.entrada2,qh.saida2,qh.total,qh.dias_trabalhados,qh.folga 
            FROM pessoa p 
            INNER JOIN funcionario f ON p.id_pessoa = f.id_pessoa 
            LEFT JOIN quadro_horario_funcionario qh ON qh.id_funcionario = f.id_funcionario 
            LEFT JOIN situacao s ON s.id_situacao = f.id_situacao 
            LEFT JOIN cargo c ON c.id_cargo = f.id_cargo 
            WHERE f.id_funcionario = :id_funcionario";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_funcionario', $id_funcionario);

        $stmt->execute();
        $funcionario = array();

        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $funcionario[] = array('imagem' => $linha['imagem'], 'cpf' => $linha['cpf'], 'nome' => $linha['nome'], 'sobrenome' => $linha['sobrenome'], 'sexo' => $linha['sexo'], 'data_nascimento' => $this->formatoDataDMY($linha['data_nascimento']), 'registro_geral' => $linha['registro_geral'], 'orgao_emissor' => $linha['orgao_emissor'], 'data_expedicao' => $this->formatoDataDMY($linha['data_expedicao']), 'nome_mae' => $linha['nome_mae'], 'nome_pai' => $linha['nome_pai'], 'tipo_sanguineo' => $linha['tipo_sanguineo'], 'senha' => $linha['senha'], 'telefone' => $linha['telefone'], 'cep' => $linha['cep'], 'estado' => $linha['estado'], 'ibge' => $linha['ibge'], 'cidade' => $linha['cidade'], 'bairro' => $linha['bairro'], 'logradouro' => $linha['logradouro'], 'numero_endereco' => $linha['numero_endereco'], 'complemento' => $linha['complemento'], 'id_funcionario' => $linha['id_funcionario'], 'data_admissao' => $this->formatoDataDMY($linha['data_admissao']), 'pis' => $linha['pis'], 'ctps' => $linha['ctps'], 'uf_ctps' => $linha['uf_ctps'], 'numero_titulo' => $linha['numero_titulo'], 'zona' => $linha['zona'], 'secao' => $linha['secao'], 'certificado_reservista_numero' => $linha['certificado_reservista_numero'], 'certificado_reservista_serie' => $linha['certificado_reservista_serie'], 'id_situacao' => $linha['id_situacao'], 'situacao' => $linha['situacao'], 'escala' => $linha['escala'], 'tipo' => $linha['tipo'], 'carga_horaria' => $linha['carga_horaria'], 'entrada1' => $linha['entrada1'], 'saida1' => $linha['saida1'], 'entrada2' => $linha['entrada2'], 'saida2' => $linha['saida2'], 'total' => $linha['total'], 'dias_trabalhados' => $linha['dias_trabalhados'], 'folga' => $linha['folga'], 'id_cargo' => $linha['id_cargo'], 'cargo' => $linha['cargo']);
        }

        return $funcionario;
    }

    public function listarPessoaExistente($cpf)
    {
        try {
            $sql = "SELECT id_pessoa,nome,sobrenome,sexo,telefone,data_nascimento,cpf,imagem,registro_geral,orgao_emissor,data_expedicao FROM `pessoa` WHERE cpf = :cpf";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':cpf', $cpf);
            $stmt->execute();
            $funcionario = array();

            while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $funcionario[] = array('imagem' => $linha['imagem'], 'id_pessoa' => $linha['id_pessoa'], 'cpf' => $linha['cpf'], 'nome' => $linha['nome'], 'sobrenome' => $linha['sobrenome'], 'sexo' => $linha['sexo'], 'data_nascimento' => $this->formatoDataDMY($linha['data_nascimento']), 'registro_geral' => $linha['registro_geral'], 'orgao_emissor' => $linha['orgao_emissor'], 'data_expedicao' => $this->formatoDataDMY($linha['data_expedicao']), 'telefone' => $linha['telefone']);
            }
        } catch (PDOException $e) {
            echo 'Error: ' .  $e->getMessage();
        }
        return json_encode($funcionario);
    }

    public function retornaId($cpf)
    {
        try {

            $sql = "SELECT f.id_funcionario FROM pessoa p INNER JOIN funcionario f ON p.id_pessoa = f.id_pessoa WHERE p.cpf = :cpf";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':cpf', $cpf);

            $stmt->execute();
            while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $idFuncionario = $linha['id_funcionario'];
            }
        } catch (PDOException $e) {
            echo 'Error: ' .  $e->getMessage();
        }

        return $idFuncionario;
    }

    public function getSenhaByIdPessoa(int $idPessoa)
    {
        $stmt = $$this->pdo->prepare("SELECT senha FROM pessoa where id_pessoa=:idPessoa");
        $stmt->bindValue(':idPessoa', $idPessoa, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC)['senha'];
    }
}
