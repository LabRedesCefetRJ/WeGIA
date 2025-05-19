<?php
    $path = 'dao/pet/AdocaoPet.php';

    if(file_exists($path)){
        require_once $path;
    }else{
        while(true){
            $path = '../' . $path;
            if(file_exists($path)){
                break;
            }
        }
        require_once $path;
    }

    class AdocaoControle{
        
        public function obterAdotante($id){
            $c = new AdocaoPet();
            $r = $c->exibirAdotante($id);

            return $r;

        }

        public function nomeAdotante($rg){
            $c = new AdocaoPet();
            return $c->nomeAdotante($rg);
        }

        public function modificarAdocao(){
            //$idPet, $rg, $data_adocao
            extract($_REQUEST);
            $c = new AdocaoPet();
            if($adotado == 'S'){
                if($rgAdotante || $dataAdocao){
                    $c->inserirAdocao($id_pet, $rgAdotante, $dataAdocao);
                    header('Location: ../../html/pet/profile_pet.php?id_pet='.$id_pet);
                }else{
                    header('Location: ../../html/pet/profile_pet.php?id_pet='.$id_pet);
                }
            }else if($adotado == 'N'){
                $c->excluirAdocao($id_pet);
                header('Location: ../../html/pet/profile_pet.php?id_pet='.$id_pet);
            }
        }

    }

    $a = new AdocaoControle();
    
