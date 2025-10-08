<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'pet' . DIRECTORY_SEPARATOR . 'PetDAO.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'pet' . DIRECTORY_SEPARATOR . 'Pet.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';

class PetControle
{
    private $petDAO;
    private $petClasse;

    public function __construct()
    {
        $this->petDAO = new PetDAO();
        $this->petClasse = new PetClasse();
    }

    private function verificar()
    {
        $sexo = strtoupper(filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_SPECIAL_CHARS));
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
        $nascimento = filter_input(INPUT_POST, 'nascimento', FILTER_SANITIZE_SPECIAL_CHARS);
        $acolhimento = filter_input(INPUT_POST, 'acolhimento', FILTER_SANITIZE_SPECIAL_CHARS);
        $especie = filter_input(INPUT_POST, 'especie', FILTER_SANITIZE_SPECIAL_CHARS);
        $cor = filter_input(INPUT_POST, 'cor', FILTER_SANITIZE_SPECIAL_CHARS);
        $caracEsp = filter_input(INPUT_POST, 'caracEsp', FILTER_SANITIZE_SPECIAL_CHARS);

        // Validações
        if (!isset($nome) || strlen($nome) < 3) {
            header("Location: ../../html/pet/cadastro_pet.php?msg=Nome não informado ou inválido!");
            return;
        }

        if (!isset($nascimento) || empty($nascimento)) {
            header("Location: ../../html/pet/cadastro_pet.php?msg=Data de nascimento não informada!");
            return;
        }

        $dataAtual = new DateTime();
        $dataNascimento = new DateTime($nascimento);

        if ($dataAtual < $dataNascimento) {
            header("Location: ../../html/pet/cadastro_pet.php?msg=Data de nascimento é inválida!");
            return;
        }

        if (!isset($acolhimento) || empty($acolhimento)) {
            header("Location: ../../html/pet/cadastro_pet.php?msg=Data de acolhimento não informada!");
            return;
        }

        $dataAcolhimento = new DateTime($acolhimento);

        if ($dataAtual < $dataAcolhimento) {
            header("Location: ../../html/pet/cadastro_pet.php?msg=Data de acolhimento é inválida!");
            return;
        }

        if ($sexo != 'M' && $sexo != 'F') {
            header("Location: ../../html/pet/cadastro_pet.php?msg=O sexo informado é inválido!");
            return;
        }

        if (!isset($especie) || strlen($especie) < 3) {
            header("Location: ../../html/pet/cadastro_pet.php?msg=Espécie não informada ou inválida");
            return;
        }

        if (!isset($raca) || empty($raca)) {
            header("Location: ../../html/pet/cadastro_pet.php?msg=Raça não informada ou inválida!");
            return;
        }

        if (!isset($cor) || empty($cor)) {
            header("Location: ../../html/pet/cadastro_pet.php?msg=Cor não informada ou inválida!");
            return;
        }

        if (!isset($caracEsp)) {
            $caracEsp = '';
        }

        // Trata a imagem de perfil
        $imgperfil = '';
        $nomeImagem = ['', ''];

        if (isset($_FILES['imgperfil']) && $_FILES['imgperfil']['error'] == UPLOAD_ERR_OK) {
            $tmpName = $_FILES['imgperfil']['tmp_name'];
            $imgperfil = base64_encode(file_get_contents($tmpName));

            $nomeImagemCompleto = $_FILES['imgperfil']['name'];
            $nomeImagem = explode('.', $nomeImagemCompleto);

            if (count($nomeImagem) < 2) {
                $nomeImagem[1] = ''; // extensão vazia se não houver
            }
        }

        // Define dados no objeto
        $this->petClasse->setNome($nome);
        $this->petClasse->setNascimento($nascimento);
        $this->petClasse->setAcolhimento($acolhimento);
        $this->petClasse->setSexo($sexo);
        $this->petClasse->setCaracteristicasEspecificas($caracEsp);
        $this->petClasse->setEspecie($especie);
        $this->petClasse->setRaca($raca);
        $this->petClasse->setCor($cor);
        $this->petClasse->setImgPerfil($imgperfil);
        $this->petClasse->setNomeImagem($nomeImagem);
    }

    public function incluir()
    {
        try {
            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);

            $this->verificar();
            // Salva no banco
            $this->petDAO->adicionarPet(
                $this->petClasse->getNome(),
                $this->petClasse->getNascimento(),
                $this->petClasse->getAcolhimento(),
                $this->petClasse->getSexo(),
                $this->petClasse->getCaracteristicasEspecificas(),
                $this->petClasse->getEspecie(),
                $this->petClasse->getRaca(),
                $this->petClasse->getCor(),
                $this->petClasse->getImgPerfil(),
                $this->petClasse->getNomeImagem()
            );

            // Redireciona
            header('Location: ../../WeGIA/html/pet/informacao_pet.php');
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarTodos()
    {
        try {
            $PetDAO = new PetDAO();
            $pets = $PetDAO->listarTodos();
            $_SESSION['pets'] = json_encode($pets);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarUmPet()
    {
        $idPet = filter_input(INPUT_GET, 'idPet', FILTER_SANITIZE_NUMBER_INT);

        try {
            if (!$idPet || $idPet < 1)
                throw new InvalidArgumentException('O id do pet fornecido é inválido.', 422);

            $petDAO = new PetDAO();
            $pet = $petDAO->listarUm($idPet);
            echo json_encode($pet);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarUm()
    {
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/pet/profile_pet\.php(\?id_pet=\d+)?)$#';

        $idPet = filter_input(INPUT_GET, 'id_pet', FILTER_SANITIZE_NUMBER_INT);

        try {
            if (!$idPet || $idPet < 1)
                throw new InvalidArgumentException('O id do pet fornecido é inválido.', 422);

            $petDAO = new PetDAO();
            $pet = $petDAO->listarUm($idPet);

            $_SESSION['pet'] = $pet;

            preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . WWW . 'html/home.php');
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function alterarImagem()
    {
        $idFoto = filter_input(INPUT_POST, 'id_foto', FILTER_SANITIZE_NUMBER_INT);
        $idPet = filter_input(INPUT_POST, 'id_pet', FILTER_SANITIZE_NUMBER_INT);
        $imgPet = base64_encode(file_get_contents($_FILES['imgperfil']['tmp_name']));
        $imgNome = $_FILES['imgperfil']['name'];
        $imgNome = explode('.', $imgNome);

        try {
            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);

            if (!$idPet || $idPet < 1)
                throw new InvalidArgumentException('O id do pet fornecido é inválido.', 422);

            if (!$idFoto || $idFoto < 1)
                throw new InvalidArgumentException('O id da foto fornecido é inválido.', 422);

            $petDAO = new PetDAO();
            $petDAO->alterarFotoPet($imgPet, $imgNome[0], $imgNome[1], $idFoto, $idPet);
            header('Location: ../../html/pet/profile_pet.php?id_pet=' . htmlspecialchars($idPet));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function alterarPetDados()
    {
        try {
            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);

            $this->verificar();
            $this->petDAO->alterarPet($this->petClasse->getNome(), $this->petClasse->getNascimento(), $this->petClasse->getAcolhimento(), $this->petClasse->getSexo(), $this->petClasse->getCaracteristicasEspecificas(), $this->petClasse->getEspecie(), $this->petClasse->getRaca(), $this->petClasse->getCor(), $this->petClasse->getId());
            header('Location: ../../html/pet/profile_pet.php?id_pet=' . htmlspecialchars($this->petClasse->getId()));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function incluirExamePet()
    {
        $idFichaMedica = filter_input(INPUT_POST, 'id_ficha_medica', FILTER_SANITIZE_NUMBER_INT);
        $idTipoExame = filter_input(INPUT_POST, 'id_tipo_exame', FILTER_SANITIZE_NUMBER_INT);
        $idPet = filter_input(INPUT_POST, 'id_pet', FILTER_SANITIZE_NUMBER_INT);
        $nameFile = explode(".", $_FILES['arquivo']['name']);
        $arquivoExame = base64_encode(file_get_contents($_FILES['arquivo']['tmp_name']));
        $dataExame = date("y-m-d");

        try {
            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);

            if (!$idFichaMedica || $idFichaMedica < 1)
                throw new InvalidArgumentException('O id da ficha médica do pet é inválido.', 422);

            if (!$idTipoExame || $idTipoExame < 1)
                throw new InvalidArgumentException('O id do tipo de exame é inválido.', 422);

            if (!$idPet || $idPet < 1)
                throw new InvalidArgumentException('O id do pet é inválido.', 422);

            $petDAO = new PetDAO();
            $petDAO->incluirExamePet($idFichaMedica, $idTipoExame, $dataExame, $arquivoExame, $nameFile);
            header("location: ../../html/pet/profile_pet.php?id_pet=" . htmlspecialchars($idPet));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarPets()
    {
        header("Content-Type: application/json; charset=utf-8");

        try {
            $petDao = new PetDAO();

            $registros = $petDao->listarPets();
            http_response_code(200);
            echo json_encode($registros, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
