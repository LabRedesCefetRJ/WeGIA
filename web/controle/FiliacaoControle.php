<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'FiliacaoDAO.php';

class FiliacaoControle
{
    private function validarAcessoEFormulario(int $idFiliado, int $idFuncionario, FiliacaoDAO $dao): array
    {
        if ($idFiliado < 1 || $idFuncionario < 1) {
            throw new InvalidArgumentException('Os dados da filiação informada não são válidos.', 400);
        }

        $filiacao = $dao->buscarFiliacaoAutorizada($idFiliado, $idFuncionario);
        if (!$filiacao) {
            throw new InvalidArgumentException('A filiação não foi encontrada.', 404);
        }

        return $filiacao;
    }

    private function redirecionarEdicao(int $idFiliado, int $idFuncionario, string $aba = 'informacoes-pessoais'): void
    {
        header('Location: ../html/funcionario/filiacao_editar.php?id_filiado=' . urlencode((string)$idFiliado) . '&id_funcionario=' . urlencode((string)$idFuncionario) . '#' . $aba);
        exit;
    }

    public function buscarPessoa(): void
    {
        $cpf = trim((string)filter_input(INPUT_GET, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS));
        if ($cpf === '') {
            echo json_encode([]);
            return;
        }
        
        $dao = new FiliacaoDAO();
        $pessoa = $dao->buscarPessoaPorCpf($cpf);
        
        header('Content-Type: application/json');
        echo json_encode($pessoa ?: []);
    }

    private function validarDados(int $idFuncionario, int $idParentesco, string $nome, string $email): void
    {
        if ($idFuncionario < 1) {
            throw new InvalidArgumentException('O id do funcionário informado não é válido.', 400);
        }
        if ($idParentesco < 1) {
            throw new InvalidArgumentException('Selecione um parentesco válido.', 412);
        }
        Util::validarNomePessoaOuLancar($nome, 'nome', 412);
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('O e-mail informado não é válido.', 412);
        }
    }

    public function cadastrar(): void
    {
        $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);
        $idParentesco = filter_input(INPUT_POST, 'id_parentesco', FILTER_VALIDATE_INT);
        $idFiliado = filter_input(INPUT_POST, 'id_filiado', FILTER_VALIDATE_INT);
        $cpf = trim((string)filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS));
        $genero = filter_input(INPUT_POST, 'genero', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
        $nome = trim((string)filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS));
        $email = trim((string)filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $telefone = trim((string)filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS));

        $redirect = '../html/funcionario/profile_funcionario.php?id_funcionario=' . urlencode((string)$idFuncionario) . '#filiacao';

        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? '')) {
                throw new InvalidArgumentException('Token de segurança inválido.', 400);
            }
            $this->validarDados((int)$idFuncionario, (int)$idParentesco, $nome, $email);

            $dao = new FiliacaoDAO();
            $dao->cadastrarFiliacao((int)$idFuncionario, (int)$idParentesco, (int)$idFiliado, $cpf, $genero, $nome, $email, $telefone);

            $_SESSION['msg'] = 'Filiação adicionada com sucesso!';
            $_SESSION['tipo'] = 'success';
        } catch (Exception $e) {
            $_SESSION['msg'] = $e->getMessage();
            $_SESSION['tipo'] = 'err';
        }

        header("Location: $redirect");
        exit;
    }

    public function editar(): void
    {
        $idFiliado = filter_input(INPUT_POST, 'id_filiado', FILTER_VALIDATE_INT);
        $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);
        $idParentesco = filter_input(INPUT_POST, 'id_parentesco', FILTER_VALIDATE_INT);
        $cpf = trim((string)filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS));
        $genero = filter_input(INPUT_POST, 'genero', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
        $nome = trim((string)filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS));
        $email = trim((string)filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $telefone = trim((string)filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS));
        $redirect = '../html/funcionario/profile_funcionario.php?id_funcionario=' . urlencode((string)$idFuncionario) . '#filiacao';

        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? '')) {
                throw new InvalidArgumentException('Token de segurança inválido.', 400);
            }
            if (!$idFiliado || $idFiliado < 1) {
                throw new InvalidArgumentException('O id da filiação informado não é válido.', 400);
            }
            $this->validarDados((int)$idFuncionario, (int)$idParentesco, $nome, $email);

            $dao = new FiliacaoDAO();
            $rowCount = $dao->editar((int)$idFiliado, (int)$idFuncionario, (int)$idParentesco, $cpf, $genero, $nome, $email, $telefone);
            
            if ($rowCount !== 1) {
                throw new InvalidArgumentException('A filiação não foi encontrada ou não foi alterada.', 404);
            }
            $_SESSION['msg'] = 'Filiação atualizada com sucesso!';
            $_SESSION['tipo'] = 'success';
        } catch (Exception $e) {
            $_SESSION['msg'] = $e->getMessage();
            $_SESSION['tipo'] = 'err';
        }

        header("Location: $redirect");
        exit;
    }

    public function editarInfoPessoal(): void
    {
        $idFiliado = (int)filter_input(INPUT_POST, 'id_filiado', FILTER_VALIDATE_INT);
        $idFuncionario = (int)filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);
        $idParentesco = (int)filter_input(INPUT_POST, 'id_parentesco', FILTER_VALIDATE_INT);
        $nome = trim((string)filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS));
        $genero = filter_input(INPUT_POST, 'genero', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
        $email = trim((string)filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $telefone = trim((string)filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS));
        $dataNascimento = trim((string)filter_input(INPUT_POST, 'data_nascimento', FILTER_UNSAFE_RAW));

        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? '')) {
                throw new InvalidArgumentException('Token de segurança inválido.', 400);
            }
            if ($idParentesco < 1) {
                throw new InvalidArgumentException('Selecione um parentesco válido.', 412);
            }
            
            $dao = new FiliacaoDAO();
            $this->validarAcessoEFormulario($idFiliado, $idFuncionario, $dao);
            
            Util::validarNomePessoaOuLancar($nome, 'nome', 412);

            if ($genero !== null && $genero !== '' && !Util::validarGenero($genero)) {
                throw new InvalidArgumentException('O gênero informado não é válido.', 412);
            }
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('O e-mail informado não é válido.', 412);
            }
            if ($telefone !== '' && !preg_match('/^(?:\+55\s?)?\(?[1-9][0-9]\)?\s?(?:9[0-9]{4}|[2-8][0-9]{3})-?[0-9]{4}$/', $telefone)) {
                throw new InvalidArgumentException('O telefone informado não está em um formato válido.', 412);
            }
            if ($dataNascimento !== '') {
                $nascimento = DateTime::createFromFormat('!Y-m-d', $dataNascimento);
                if (!$nascimento || $nascimento->format('Y-m-d') !== $dataNascimento || $nascimento > new DateTime('today')) {
                    throw new InvalidArgumentException('A data de nascimento informada não é válida.', 412);
                }
            }

            $dao->editarInfoPessoal($idFiliado, $idFuncionario, $idParentesco, $nome, $genero, $email, $telefone, $dataNascimento);
            
            $_SESSION['msg'] = 'Informações pessoais atualizadas!';
            $_SESSION['tipo'] = 'success';
        } catch (Exception $e) {
            $_SESSION['msg'] = $e->getMessage();
            $_SESSION['tipo'] = 'err';
        }

        $this->redirecionarEdicao($idFiliado, $idFuncionario, 'informacoes-pessoais');
    }

    public function editarDocumentacao(): void
    {
        $idFiliado = (int)filter_input(INPUT_POST, 'id_filiado', FILTER_VALIDATE_INT);
        $idFuncionario = (int)filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);
        $cpf = trim((string)filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS));
        $rg = trim((string)filter_input(INPUT_POST, 'rg', FILTER_SANITIZE_SPECIAL_CHARS));
        $orgaoEmissor = trim((string)filter_input(INPUT_POST, 'orgao_emissor', FILTER_SANITIZE_SPECIAL_CHARS));
        $dataExpedicao = trim((string)filter_input(INPUT_POST, 'data_expedicao', FILTER_UNSAFE_RAW));

        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? '')) {
                throw new InvalidArgumentException('Token de segurança inválido.', 400);
            }
            
            $dao = new FiliacaoDAO();
            $filiacao = $this->validarAcessoEFormulario($idFiliado, $idFuncionario, $dao);

            if ($cpf !== '' && !Util::validarCPF($cpf)) {
                throw new InvalidArgumentException('O CPF informado não é válido.', 412);
            }
            if ($dataExpedicao !== '') {
                $expedicao = DateTime::createFromFormat('!Y-m-d', $dataExpedicao);
                if (!$expedicao || $expedicao->format('Y-m-d') !== $dataExpedicao || $expedicao > new DateTime('today')) {
                    throw new InvalidArgumentException('A data de expedição informada não é válida.', 412);
                }
                if (!empty($filiacao['data_nascimento']) && $dataExpedicao <= $filiacao['data_nascimento']) {
                    throw new InvalidArgumentException('A data de expedição deve ser posterior à data de nascimento.', 412);
                }
            }

            $dao->editarDocumentacao($idFiliado, $idFuncionario, $cpf, $rg, $orgaoEmissor, $dataExpedicao);
            
            $_SESSION['msg'] = 'Documentação atualizada!';
            $_SESSION['tipo'] = 'success';
        } catch (Exception $e) {
            $_SESSION['msg'] = $e->getMessage();
            $_SESSION['tipo'] = 'err';
        }

        $this->redirecionarEdicao($idFiliado, $idFuncionario, 'documentacao');
    }

    public function editarEndereco(): void
    {
        $idFiliado = (int)filter_input(INPUT_POST, 'id_filiado', FILTER_VALIDATE_INT);
        $idFuncionario = (int)filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);
        $cep = trim((string)filter_input(INPUT_POST, 'cep', FILTER_UNSAFE_RAW));
        $estado = trim((string)filter_input(INPUT_POST, 'estado', FILTER_UNSAFE_RAW));
        $cidade = trim((string)filter_input(INPUT_POST, 'cidade', FILTER_UNSAFE_RAW));
        $bairro = trim((string)filter_input(INPUT_POST, 'bairro', FILTER_UNSAFE_RAW));
        $logradouro = trim((string)filter_input(INPUT_POST, 'logradouro', FILTER_UNSAFE_RAW));
        $numero = trim((string)filter_input(INPUT_POST, 'numero_endereco', FILTER_UNSAFE_RAW));
        $complemento = trim((string)filter_input(INPUT_POST, 'complemento', FILTER_UNSAFE_RAW));
        $ibge = trim((string)filter_input(INPUT_POST, 'ibge', FILTER_UNSAFE_RAW));

        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? '')) {
                throw new InvalidArgumentException('Token de segurança inválido.', 400);
            }
            
            $dao = new FiliacaoDAO();
            $this->validarAcessoEFormulario($idFiliado, $idFuncionario, $dao);
            
            if ($cep !== '' && !preg_match('/^\d{5}-?\d{3}$/', $cep)) {
                throw new InvalidArgumentException('O CEP informado não está em um formato válido.', 412);
            }

            $dao->editarEndereco($idFiliado, $idFuncionario, $cep, $estado, $cidade, $bairro, $logradouro, $numero, $complemento, $ibge);
            
            $_SESSION['msg'] = 'Endereço atualizado!';
            $_SESSION['tipo'] = 'success';
        } catch (Exception $e) {
            $_SESSION['msg'] = $e->getMessage();
            $_SESSION['tipo'] = 'err';
        }

        $this->redirecionarEdicao($idFiliado, $idFuncionario, 'endereco');
    }

    public function excluir(): void
    {
        $idFiliado = filter_input(INPUT_POST, 'id_filiado', FILTER_VALIDATE_INT);
        $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);
        $redirect = '../html/funcionario/profile_funcionario.php?id_funcionario=' . urlencode((string)$idFuncionario) . '#filiacao';

        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? '')) {
                throw new InvalidArgumentException('Token de segurança inválido.', 400);
            }
            if (!$idFiliado || !$idFuncionario || $idFuncionario < 1) {
                throw new InvalidArgumentException('Os dados da filiação informada não são válidos.', 400);
            }

            $dao = new FiliacaoDAO();
            $rowCount = $dao->excluir((int)$idFiliado, (int)$idFuncionario);
            
            if ($rowCount !== 1) {
                throw new InvalidArgumentException('A filiação não foi encontrada.', 404);
            }
            
            $_SESSION['msg'] = 'Filiação excluída com sucesso!';
            $_SESSION['tipo'] = 'success';
        } catch (Exception $e) {
            $_SESSION['msg'] = $e->getMessage();
            $_SESSION['tipo'] = 'err';
        }
 
        header("Location: $redirect");
        exit;
    }
}