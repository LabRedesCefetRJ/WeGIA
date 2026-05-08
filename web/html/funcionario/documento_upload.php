<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';

const DOCUMENTO_FUNCIONARIO_MIMES = [
    'jpg' => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'png' => ['image/png'],
    'pdf' => ['application/pdf'],
    'doc' => ['application/msword', 'application/CDFV2', 'application/x-ole-storage', 'application/octet-stream'],
    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
    'odp' => ['application/vnd.oasis.opendocument.presentation', 'application/zip'],
];

function responderJson(int $codigo, array $payload): void
{
    http_response_code($codigo);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit();
}

function formatarTamanhoUpload(string $valor): string
{
    $valor = strtoupper(trim($valor));
    if ($valor === '') {
        return '';
    }

    $unidade = substr($valor, -1);
    if (in_array($unidade, ['K', 'M', 'G'], true)) {
        return substr($valor, 0, -1) . ' ' . $unidade . 'B';
    }

    return $valor;
}

function obterMensagemErroUpload(int $codigoErro): string
{
    return match ($codigoErro) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'O arquivo selecionado excede o limite permitido de ' . formatarTamanhoUpload((string)ini_get('upload_max_filesize')) . '.',
        UPLOAD_ERR_PARTIAL => 'O upload do arquivo foi enviado parcialmente.',
        UPLOAD_ERR_NO_FILE => 'Selecione um arquivo antes de enviar.',
        default => 'Erro ao enviar o documento.',
    };
}

function normalizarNomeArquivo(string $nomeArquivo): string
{
    $nomeArquivo = basename(trim($nomeArquivo));
    // Remove caracteres de controle invisiveis, como quebra de linha, tab e NULL.
    $nomeArquivo = preg_replace('/[[:cntrl:]]+/', '', $nomeArquivo);
    $nomeArquivo = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '_', $nomeArquivo);

    if ($nomeArquivo === '' || $nomeArquivo === '.' || $nomeArquivo === '..') {
        throw new InvalidArgumentException('O nome do arquivo enviado é inválido.', 400);
    }

    if (strlen($nomeArquivo) > 256) {
        throw new InvalidArgumentException('O nome do arquivo excede o limite permitido.', 400);
    }

    return $nomeArquivo;
}

function validarPermissaoFuncionario(PDO $pdo, int $idPessoa, int $idRecurso = 11, int $idAcao = 7): void
{
    $sql = 'SELECT permissao.id_acao
            FROM funcionario
            JOIN permissao ON permissao.id_cargo = funcionario.id_cargo
            WHERE funcionario.id_pessoa = :idPessoa
              AND permissao.id_recurso = :idRecurso
            LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':idPessoa', $idPessoa, PDO::PARAM_INT);
    $stmt->bindValue(':idRecurso', $idRecurso, PDO::PARAM_INT);
    $stmt->execute();

    $permissao = $stmt->fetchColumn();
    if ($permissao === false || (int)$permissao < $idAcao) {
        throw new LogicException('Você não tem as permissões necessárias para essa operação.', 403);
    }
}

function validarEntidadesRelacionadas(PDO $pdo, int $idFuncionario, int $idDocFuncional): void
{
    $stmtFuncionario = $pdo->prepare('SELECT 1 FROM funcionario WHERE id_funcionario = :idFuncionario');
    $stmtFuncionario->bindValue(':idFuncionario', $idFuncionario, PDO::PARAM_INT);
    $stmtFuncionario->execute();

    if (!$stmtFuncionario->fetchColumn()) {
        throw new InvalidArgumentException('O funcionário informado não é válido.', 400);
    }

    $stmtDocumento = $pdo->prepare('SELECT 1 FROM funcionario_docfuncional WHERE id_docfuncional = :idDocFuncional');
    $stmtDocumento->bindValue(':idDocFuncional', $idDocFuncional, PDO::PARAM_INT);
    $stmtDocumento->execute();

    if (!$stmtDocumento->fetchColumn()) {
        throw new InvalidArgumentException('O tipo de documento informado não é válido.', 400);
    }
}

function validarMimeArquivo(string $caminhoTemporario, string $extensaoArquivo): void
{
    $mimesPermitidos = DOCUMENTO_FUNCIONARIO_MIMES[$extensaoArquivo] ?? null;
    if ($mimesPermitidos === null) {
        throw new InvalidArgumentException(
            'Tipo de arquivo não permitido. Apenas arquivos PNG, JPG, PDF, DOC, DOCX e ODP são aceitos.',
            400
        );
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeArquivo = $finfo->file($caminhoTemporario);

    if ($mimeArquivo === false) {
        throw new RuntimeException('Não foi possível identificar o tipo do arquivo enviado.', 500);
    }

    if (!in_array($mimeArquivo, $mimesPermitidos, true)) {
        throw new InvalidArgumentException('O tipo do arquivo enviado não é compatível com a extensão informada.', 400);
    }
}

function processarArquivoUpload(array $arquivo): array
{
    if (!isset($arquivo['error']) || $arquivo['error'] !== UPLOAD_ERR_OK) {
        $codigoErro = (int)($arquivo['error'] ?? UPLOAD_ERR_NO_FILE);
        $codigoHttp = in_array($codigoErro, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) ? 413 : 400;
        throw new InvalidArgumentException(obterMensagemErroUpload($codigoErro), $codigoHttp);
    }

    if (!isset($arquivo['tmp_name']) || !is_uploaded_file($arquivo['tmp_name'])) {
        throw new InvalidArgumentException('Arquivo inválido.', 400);
    }

    $nomeArquivo = normalizarNomeArquivo((string)($arquivo['name'] ?? ''));
    $extensaoArquivo = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));

    validarMimeArquivo($arquivo['tmp_name'], $extensaoArquivo);

    $conteudoArquivo = file_get_contents($arquivo['tmp_name']);
    if ($conteudoArquivo === false) {
        throw new RuntimeException('Não foi possível ler o arquivo enviado.', 500);
    }

    return [
        'nome' => $nomeArquivo,
        'extensao' => $extensaoArquivo,
        'conteudo' => gzcompress(base64_encode($conteudoArquivo)),
    ];
}

function listarDocumentosFuncionario(PDO $pdo, int $idFuncionario): array
{
    $sql = "SELECT f.id_fundocs, f.`data`, docf.nome_docfuncional
            FROM funcionario_docs f
            JOIN funcionario_docfuncional docf ON f.id_docfuncional = docf.id_docfuncional
            WHERE f.id_funcionario = :idFuncionario
            ORDER BY f.`data` DESC, f.id_fundocs DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':idFuncionario', $idFuncionario, PDO::PARAM_INT);
    $stmt->execute();

    $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($documentos as $index => $documento) {
        $documentos[$index]['data'] = (new DateTime($documento['data']))->format('d/m/Y H:i:s');
    }

    return $documentos;
}

try {
    if (!isset($_SESSION['usuario'])) {
        throw new LogicException('Sessão expirada.', 401);
    }

    session_regenerate_id();

    $idPessoa = filter_var(
        $_SESSION['id_pessoa'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($idPessoa === false || $idPessoa === null) {
        throw new InvalidArgumentException('O id da pessoa informado não é válido.', 400);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new InvalidArgumentException('Método de requisição inválido para upload de documento.', 405);
    }

    if (empty($_POST) && empty($_FILES)) {
        throw new InvalidArgumentException(
            'O arquivo selecionado excede o limite permitido de ' . formatarTamanhoUpload((string)ini_get('upload_max_filesize')) . '.',
            413
        );
    }

    $idFuncionario = filter_input(
        INPUT_POST,
        'id_funcionario',
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );
    $idDocFuncional = filter_input(
        INPUT_POST,
        'id_docfuncional',
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );
    $arquivo = $_FILES['arquivo'] ?? null;

    if ($idFuncionario === false || $idFuncionario === null) {
        throw new InvalidArgumentException('O funcionário informado não é válido.', 400);
    }

    if ($idDocFuncional === false || $idDocFuncional === null) {
        throw new InvalidArgumentException('O tipo de documento informado não é válido.', 400);
    }

    if ($arquivo === null) {
        throw new InvalidArgumentException('Selecione um arquivo antes de enviar.', 400);
    }

    $pdo = Conexao::connect();

    validarPermissaoFuncionario($pdo, $idPessoa);
    validarEntidadesRelacionadas($pdo, $idFuncionario, $idDocFuncional);

    $arquivoProcessado = processarArquivoUpload($arquivo);

    $stmt = $pdo->prepare(
        'INSERT INTO funcionario_docs (id_funcionario, id_docfuncional, extensao_arquivo, nome_arquivo, arquivo)
         VALUES (:idFuncionario, :idDocFuncional, :extensaoArquivo, :nomeArquivo, :arquivo)'
    );
    $stmt->bindValue(':idFuncionario', $idFuncionario, PDO::PARAM_INT);
    $stmt->bindValue(':idDocFuncional', $idDocFuncional, PDO::PARAM_INT);
    $stmt->bindValue(':extensaoArquivo', $arquivoProcessado['extensao']);
    $stmt->bindValue(':nomeArquivo', $arquivoProcessado['nome']);
    $stmt->bindValue(':arquivo', $arquivoProcessado['conteudo'], PDO::PARAM_LOB);
    $stmt->execute();

    responderJson(200, [
        'status' => 'sucesso',
        'documentos' => listarDocumentosFuncionario($pdo, $idFuncionario),
    ]);
} catch (Throwable $e) {
    error_log(sprintf(
        '[ERRO documento_upload.php funcionario] %s em %s:%d',
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    ));

    $codigo = $e->getCode();
    if ($codigo < 400 || $codigo > 599) {
        $codigo = 500;
    }

    $mensagem = $e instanceof PDOException
        ? 'Erro interno ao acessar o banco de dados.'
        : $e->getMessage();

    responderJson($codigo, [
        'status' => 'erro',
        'mensagem' => $mensagem,
        'erro' => $mensagem,
    ]);
}
