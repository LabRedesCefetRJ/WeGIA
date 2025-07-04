<?php
$path = 'dao/pet/AdocaoPet.php';

if (file_exists($path)) {
    require_once $path;
} else {
    while (true) {
        $path = '../' . $path;
        if (file_exists($path)) {
            break;
        }
    }
    require_once $path;
}

class AdocaoControle {
    public function excluirAdocaoPet($id_pet) {
        
        $c = new AdocaoPet();
        return $c->excluirAdocao($id_pet);
    }

    public function obterAdotante($id) {
        $c = new AdocaoPet();
        return $c->exibirAdotante($id);
    }

    public function nomeAdotante($id_pessoa) {
        $c = new AdocaoPet();
        return $c->nomeAdotantePorId($id_pessoa);
    }

    public function modificarAdocao() {
        extract($_REQUEST);
        $c = new AdocaoPet();

        if ($adotado === 'S') {
            $id_pessoa = $_POST['adotante_input'] ?? null;
            $dataAdocao = $_POST['dataAdocao'] ?? null;

            if ($id_pessoa && $dataAdocao) {
                $c->inserirAdocao($id_pet, $id_pessoa, $dataAdocao);
            }
        } else if ($adotado === 'N') {
            $c->excluirAdocao($id_pet);
        }

        header('Location: ../../html/pet/profile_pet.php?id_pet=' . $id_pet);
    }
}

$a = new AdocaoControle();
