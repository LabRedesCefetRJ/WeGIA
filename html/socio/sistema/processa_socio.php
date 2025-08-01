<?php
//Considerar transformar em POO posteriormente

//requisitar conexão com o BD
require_once('../../../dao/Conexao.php');

//Escolher qual ação executar
$acao = trim($_REQUEST['acao']);

if (!$acao || empty($acao)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Ação não definida']);
    exit();
}

switch ($acao) {
    case 'cadastrar':
        cadastrar();
        break;
    case 'atualizar':
        atualizar();
        break;
    case 'buscarPorCpf':
        buscarPorCpf();
        break;
    default:
        http_response_code(400);
        echo json_encode(['erro' => 'Ação inválida']);
        exit();
}

/**
 * Realiza os procedimentos necessários para inserir um novo sócio no banco de dados da aplicação
 */
function cadastrar()
{

    $dados = extrairPost();

    try {

        $pdo = Conexao::connect();
        $pdo->beginTransaction();

        $idInterno = verificarInterno($dados['cpf']);
        if (!$idInterno) {
            //criar pessoa
            $sqlPessoa = 'INSERT INTO pessoa(cpf, nome, telefone, data_nascimento, cep, estado, cidade, bairro, logradouro, numero_endereco, complemento, ibge) VALUES(:cpf, :nome, :telefone, :dataNascimento, :cep, :estado, :cidade, :bairro, :logradouro, :numeroEndereco, :complemento, :ibge)';

            $stmtPessoa = $pdo->prepare($sqlPessoa);

            $stmtPessoa->bindParam(':cpf', $dados['cpf']);
            $stmtPessoa->bindParam(':nome', $dados['nome']);
            $stmtPessoa->bindParam(':telefone', $dados['telefone']);
            $stmtPessoa->bindParam(':dataNascimento', $dados['dataNascimento']);
            $stmtPessoa->bindParam(':cep', $dados['cep']);
            $stmtPessoa->bindParam(':estado', $dados['uf']);
            $stmtPessoa->bindParam(':cidade', $dados['cidade']);
            $stmtPessoa->bindParam(':bairro', $dados['bairro']);
            $stmtPessoa->bindParam(':logradouro', $dados['rua']);
            $stmtPessoa->bindParam(':numeroEndereco', $dados['numero']);
            $stmtPessoa->bindParam(':complemento', $dados['complemento']);
            $stmtPessoa->bindParam(':ibge', $dados['ibge']);

            $stmtPessoa->execute();
            $idPessoa = $pdo->lastInsertId();
        } else {
            $idPessoa = $idInterno;
        }

        //criar socio
        $idSocioStatus = 3; //Define o status do sócio como Inativo temporariamente

        $tagSolicitante = $pdo->query("SELECT * FROM socio_tag WHERE tag='Solicitante'")->fetch(PDO::FETCH_ASSOC);

        $idSocioTag = $tagSolicitante['id_sociotag']; //Define o grupo do sócio como Solicitante

        $dataAtual = new DateTime();

        // Verificar se o dia informado já passou neste mês
        if ($dados['dataVencimento'] <= $dataAtual->format('d')) {
            // Se o dia informado já passou, começar a partir do próximo mês
            $dataAtual->modify('first day of next month');
        }

        // Clonar a data atual para evitar modificar o objeto original
        $dataReferencia = clone $dataAtual;

        // Definir o dia do vencimento para o dia informado
        $dataReferencia->setDate($dataReferencia->format('Y'), $dataReferencia->format('m'), $dados['dataVencimento']);

        $dataReferencia = $dataReferencia->format('Y-m-d');

        $sqlSocio = 'INSERT INTO socio(id_pessoa, id_sociostatus, id_sociotipo, id_sociotag, email, valor_periodo, data_referencia) VALUES(:idPessoa, :idSocioStatus, :idSocioTipo, :idSocioTag, :email, :valor, :dataReferencia)';

        $stmtSocio = $pdo->prepare($sqlSocio);

        $stmtSocio->bindParam(':idPessoa', $idPessoa);
        $stmtSocio->bindParam(':idSocioStatus', $idSocioStatus);
        $stmtSocio->bindParam(':idSocioTipo', $dados['periodicidade']);
        $stmtSocio->bindParam(':idSocioTag', $idSocioTag);
        $stmtSocio->bindParam(':email', $dados['email']);
        $stmtSocio->bindParam(':valor', $dados['valor']);
        $stmtSocio->bindParam(':dataReferencia', $dataReferencia);

        $stmtSocio->execute();

        //registrar no socio_log
        $idSocio = $pdo->lastInsertId();

        $sqlRegistrarSocioLog = "INSERT INTO socio_log (id_socio, descricao)
        VALUES (:idSocio,'Inscrição recente')";

        $stmtSocioLog = $pdo->prepare($sqlRegistrarSocioLog);
        $stmtSocioLog->bindParam(':idSocio', $idSocio);

        if ($stmtSocioLog->execute()) {
            $pdo->commit();
            http_response_code(200);
            echo json_encode(['retorno' => 'Cadastrado com sucesso!']);
            exit();
        } else {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar sócio no sistema']);
            exit();
        }
    } catch (PDOException $e) {
        error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
        http_response_code($e->getCode());
        echo json_encode(['erro' => 'Erro ao cadastrar sócio no sistema']);
        exit();
    }
}
/**
 * Atualiza os dados de um sócio do sistema
 */
function atualizar()
{
    $dados = extrairPost();

    //Verifica se o sócio é um funcionário ou atendido
    if (verificarInterno($dados['cpf'])) {
        http_response_code(403);
        echo json_encode(['erro' => 'Você não possuí permissão para alterar os dados desse CPF']);
        exit();
    }

    try {
        $pdo = Conexao::connect();
        $pdo->beginTransaction();

        //registrar no socio_log

        $sqlRegistrarSocioLog = "INSERT INTO socio_log (id_socio, descricao)
        VALUES (
            (SELECT s.id_socio
             FROM socio s
             JOIN pessoa p ON s.id_pessoa = p.id_pessoa
             WHERE p.cpf =:cpf),
            'Atualização recente'
        )";

        $stmtSocioLog = $pdo->prepare($sqlRegistrarSocioLog);
        $stmtSocioLog->bindParam(':cpf', $dados['cpf']);
        $stmtSocioLog->execute();

        //atualizar os dados de pessoa
        $sqlAtualizarPessoa =
            'UPDATE pessoa 
        SET 
            nome=:nome, 
            telefone=:telefone, 
            data_nascimento=:dataNascimento, 
            cep=:cep, 
            estado=:estado, 
            cidade=:cidade, 
            bairro=:bairro, 
            logradouro=:logradouro, 
            numero_endereco=:numeroEndereco, 
            complemento=:complemento, 
            ibge=:ibge
        WHERE cpf=:cpf';

        $stmtPessoa = $pdo->prepare($sqlAtualizarPessoa);

        $stmtPessoa->bindParam(':nome', $dados['nome']);
        $stmtPessoa->bindParam(':telefone', $dados['telefone']);
        $stmtPessoa->bindParam(':dataNascimento', $dados['dataNascimento']);
        $stmtPessoa->bindParam(':cep', $dados['cep']);
        $stmtPessoa->bindParam(':estado', $dados['uf']);
        $stmtPessoa->bindParam(':cidade', $dados['cidade']);
        $stmtPessoa->bindParam(':bairro', $dados['bairro']);
        $stmtPessoa->bindParam(':logradouro', $dados['rua']);
        $stmtPessoa->bindParam(':numeroEndereco', $dados['numero']);
        $stmtPessoa->bindParam(':complemento', $dados['complemento']);
        $stmtPessoa->bindParam(':ibge', $dados['ibge']);
        $stmtPessoa->bindParam(':cpf', $dados['cpf']);

        $stmtPessoa->execute();

        //atualizar os dados de socio

        //verificar se possuí o status de Ativo
        $sqlBuscaAtivo = "SELECT s.id_sociotag FROM socio s JOIN pessoa p ON (s.id_pessoa=p.id_pessoa) WHERE cpf=:cpf AND s.id_sociostatus =0";

        $stmtStatusAtivo = $pdo->prepare($sqlBuscaAtivo);
        $stmtStatusAtivo->bindParam(':cpf', $dados['cpf']);
        $stmtStatusAtivo->execute();

        if ($stmtStatusAtivo->rowCount() > 0) {
            $idSocioTag = $stmtStatusAtivo->fetch(PDO::FETCH_ASSOC)['id_sociotag'];
        } else {
            $tagSolicitante = $pdo->query("SELECT * FROM socio_tag WHERE tag='Solicitante'")->fetch(PDO::FETCH_ASSOC);
            $idSocioTag = $tagSolicitante['id_sociotag']; //Define o grupo do sócio como Solicitante
        }

        $sqlAtualizarSocio =
            'UPDATE socio s 
        JOIN pessoa p ON s.id_pessoa = p.id_pessoa
        SET 
            s.email = :email, 
            s.valor_periodo = :valor, 
            s.data_referencia = :dataReferencia, 
            s.id_sociotipo =:periodicidade, 
            s.id_sociotag =:tag
        WHERE p.cpf = :cpf';

        $dataAtual = new DateTime();

        // Verificar se o dia informado já passou neste mês
        if ($dados['dataVencimento'] <= $dataAtual->format('d')) {
            // Se o dia informado já passou, começar a partir do próximo mês
            $dataAtual->modify('first day of next month');
        }

        // Clonar a data atual para evitar modificar o objeto original
        $dataReferencia = clone $dataAtual;

        // Definir o dia do vencimento para o dia informado
        $dataReferencia->setDate($dataReferencia->format('Y'), $dataReferencia->format('m'), $dados['dataVencimento']);

        $dataReferencia = $dataReferencia->format('Y-m-d');

        $stmtSocio = $pdo->prepare($sqlAtualizarSocio);

        $stmtSocio->bindParam(':email', $dados['email']);
        $stmtSocio->bindParam(':valor', $dados['valor']);
        $stmtSocio->bindParam(':dataReferencia', $dataReferencia);
        $stmtSocio->bindParam(':cpf', $dados['cpf']);
        $stmtSocio->bindParam(':periodicidade', $dados['periodicidade']);
        $stmtSocio->bindParam(':tag', $idSocioTag);

        if ($stmtSocio->execute()) {
            $pdo->commit();
            http_response_code(200);
            echo json_encode(['retorno' => 'Atualizado com sucesso!']);
            exit();
        } else {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar sócio no sistema']);
            exit();
        }
    } catch (PDOException $e) {
        error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
        http_response_code($e->getCode());
        echo json_encode(['erro' => 'Erro ao cadastrar sócio no sistema']);
        exit();
    }
}

/**
 * Retorna os dados de um sócio que possua um CPF equivalente ao informado
 */
function buscarPorCpf()
{
    $cpf = trim(filter_input(INPUT_GET, 'cpf'));

    if (!$cpf || empty($cpf)) {
        http_response_code(400);
        echo json_encode(['erro' => 'CPF não definido']);
        exit();
    }

    //Verifica se o sócio é um funcionário ou atendido
    if (verificarInterno($cpf, true)) {
        http_response_code(403);
        echo json_encode(['erro' => 'Você não possui permissão para alterar os dados desse CPF']);
        exit();
    }

    try {
        $pdo = Conexao::connect();

        $socio = [];

        $sqlBuscarPorCpf =
            'SELECT 
        p.nome, 
        p.telefone, 
        p.data_nascimento, 
        p.cep, 
        p.estado, 
        p.cidade, 
        p.bairro, 
        p.logradouro, 
        p.numero_endereco, 
        p.complemento, 
        p.ibge, 
        s.email, 
        s.data_referencia, 
        s.id_sociotipo 
    FROM pessoa p JOIN socio s ON(s.id_pessoa=p.id_pessoa) 
    WHERE p.cpf=:cpf';

        $stmt = $pdo->prepare($sqlBuscarPorCpf);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $socio = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        echo json_encode(['retorno' => $socio]);
    } catch (PDOException $e) {
        error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
        http_response_code($e->getCode());
        echo json_encode(['erro' => 'Erro ao buscar sócio no sistema']);
        exit();
    }
}

/**
 * Verifica se é um funcionário ou um atendido
 */
function verificarInterno(string $cpf, bool $socio = false)
{
    $sqlVerificarInterno = 'SELECT p.id_pessoa FROM pessoa p ';

    if ($socio) {
        $sqlVerificarInterno .= 'JOIN socio s ON (s.id_pessoa=p.id_pessoa) ';
    }

    $sqlVerificarInterno .= 'LEFT JOIN atendido a ON(a.pessoa_id_pessoa=p.id_pessoa) LEFT JOIN funcionario f ON(f.id_pessoa=p.id_pessoa) WHERE p.cpf=:cpf AND (a.pessoa_id_pessoa IS NOT NULL OR f.id_pessoa IS NOT NULL)';

    try {
        $pdo = Conexao::connect();

        $stmt = $pdo->prepare($sqlVerificarInterno);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->execute();

        $interno = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            return $interno['id_pessoa'];
        }

        return false;
    } catch (PDOException $e) {
        error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
        http_response_code($e->getCode());
        echo json_encode(['erro' => 'Erro ao verificar sócio no sistema']);
        exit();
    }
}

/**
 * Pega os dados do formulário e retorna um array caso todas as informações passem pelas validações
 */
function extrairPost()
{
    //extrair dados da requisição (considerar separar em uma função própria)
    $cpf = trim(filter_input(INPUT_POST, 'cpf'));
    $nome = trim(filter_input(INPUT_POST, 'nome'));
    $telefone = trim(filter_input(INPUT_POST, 'telefone'));
    $dataNascimento = trim(filter_input(INPUT_POST, 'data_nascimento'));
    $cep = trim(filter_input(INPUT_POST, 'cep'));
    $rua = trim(filter_input(INPUT_POST, 'rua'));
    $bairro = trim(filter_input(INPUT_POST, 'bairro'));
    $uf = trim(filter_input(INPUT_POST, 'uf'));
    $cidade = trim(filter_input(INPUT_POST, 'cidade'));
    $complemento = trim(filter_input(INPUT_POST, 'complemento'));
    $numero = trim(filter_input(INPUT_POST, 'numero'));
    $ibge = trim(filter_input(INPUT_POST, 'ibge'));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $valor = trim(filter_input(INPUT_POST, 'valor'));
    $periodicidade = trim(filter_input(INPUT_POST, 'periodicidade'));
    $dataVencimento = trim(filter_input(INPUT_POST, 'data_vencimento'));

    //validar dados (considerar separar em uma função própria)
    try {
        //validação do CPF
        require_once('../../../classes/Util.php');
        $util = new Util();

        if (!$util->validarCPF($cpf)) {
            throw new InvalidArgumentException('O CPF informado não é válido');
        }

        //validação do nome
        if (!$nome || strlen($nome) < 3) {
            throw new InvalidArgumentException('O nome informado não pode ser vazio');
        }

        //validação do telefone
        if (!$telefone || strlen($telefone) != 14) { //considerar melhorar a validação posteriormente
            throw new InvalidArgumentException('O telefone informado não está no formato válido');
        }

        //validação da data de nascimento
        $hoje = new DateTime();
        $hoje = $hoje->format('Y-m-d');

        if ($dataNascimento > $hoje) {
            throw new InvalidArgumentException('A data de nascimento não pode ser maior que a data atual');
        }

        //validação do CEP
        if (!$cep || strlen($cep) != 9) {
            throw new InvalidArgumentException('O CEP informado não está no formato válido');
        }

        //validação da rua
        if (!$rua || empty($rua)) {
            throw new InvalidArgumentException('A rua informada não pode ser vazia');
        }

        //validação do bairro
        if (!$bairro || empty($bairro)) {
            throw new InvalidArgumentException('O bairro informado não pode ser vazio');
        }

        //validação do estado
        if (!$uf || strlen($uf) != 2) {
            throw new InvalidArgumentException('O Estado informada não pode ser vazio');
        }

        //validação da cidade
        if (!$cidade || empty($cidade)) {
            throw new InvalidArgumentException('A cidade informada não pode ser vazia');
        }

        //validação do número da residência
        if (!$numero || empty($numero)) {
            throw new InvalidArgumentException('O número da residência informada não pode ser vazio');
        }

        //validação do email
        if (!$email || empty($email)) {
            throw new InvalidArgumentException('O email informado não está em um formato válido');
        }

        //validação do valor
        if (!$valor || $valor < 30) { //Pegar o valor de maneira dinâmica posteriormente
            throw new InvalidArgumentException('O valor informada deve ser de no mínimo 30 reais.');
        }

        //Validação da periodicidade
        $periodosValidos = ['2', '6', '8', '10'];

        if (!in_array($periodicidade, $periodosValidos)) {
            throw new InvalidArgumentException('O período escolhido não é válido');
        }

        //validação da data de vencimento
        $diasValidos = ['1', '5', '10', '15', '20'];

        if (!in_array($dataVencimento, $diasValidos)) {
            throw new InvalidArgumentException('O dia escolhido não é válido');
        }
    } catch (InvalidArgumentException $e) {
        error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
        http_response_code(400);
        echo json_encode(['erro' => $e->getMessage()]);
        exit();
    }

    return [
        'cpf' => $cpf,
        'nome' => $nome,
        'telefone' => $telefone,
        'dataNascimento' => $dataNascimento,
        'cep' => $cep,
        'rua' => $rua,
        'bairro' => $bairro,
        'uf' => $uf,
        'cidade' => $cidade,
        'complemento' => $complemento,
        'numero' => $numero,
        'ibge' => $ibge,
        'email' => $email,
        'valor' => $valor,
        'periodicidade' => $periodicidade,
        'dataVencimento' => $dataVencimento,
    ];
}
