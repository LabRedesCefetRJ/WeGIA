<?php
$config_path = realpath("../config.php");
if($config_path){
    require_once($config_path);
}

class Campo {

    private $id;
    private $tipo;
    private $nome;
    private $conteudo;
    

// Construct

    public function __construct($id,$t,$n,$c){
        $this
            ->setId($id)
            ->setTipo($t)
            ->setNome($n)
            ->setConteudo($c)
        ;
    }

// Getters e Setters
    

    public function getTipo()
    {
        return $this->tipo;
    }

    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;

        return $this;
    }

    public function getConteudo()
    {
        return $this->conteudo;
    }

    public function setConteudo($conteudo)
    {
        $this->conteudo = $conteudo;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

// Metodos


    public function display_img(){
        // Caso o tipo seja uma Imagem
        $id = $this->getId();
        echo('
        <tr onclick="post(' . "'personalizacao_selecao.php', {tipo: 'img', id: $id}" . ')">
            <td class="v-center"><div>' . $this->getNome() . '</div></td>
            <td><img id="img-1" src="data:image;base64,' . gzuncompress($this->getConteudo()) . '" width="100%"></td>
        </tr>');
    }

    //Começar por aqui
    public function display_txt(){
         // Caso o tipo seja um Texto
         echo('
         <form action="personalizacao_upload.php" method="post">
                 <tr id="txt-' . $this->getId() . '">
                 <td class="v-center" style="padding: 20px 0;">
                     <div style="display:flex; flex-direction:column;">
                         <button title="Editar" class="btn btn-primary" type="button" onclick="tr_select(' . "'txt-". $this->getId() . "'" . ')"><i class="fas fa-edit"></i></button>
                         <button title="Mudar Texto" class="btn btn-success" type="submit" style="display: none;"><i class="fas fa-check"></i></button>
                     </div>
                 </td>
                 <td class="v-center"><div>' . $this->getNome() . '</div></td>
                 <td>' . htmlspecialchars($this->getConteudo()) . '</td>
                 <td style="display: none;"><textarea name="txt" class="text-area" rows="5"></textarea><input style="display: none;" name="id" value="' . $this->getId() . '" readonly></td>
             </tr>
         </form>');
    }

    public function display_car_selection(){
        // Caso a seleção seja para um carrossel
        // Nesta função, Nome == Conteudo
        echo('
        <tr onclick="addToSelection(this)">
            <td class="v-center"><div><button title="Selecionar" class="btn btn-light" type="button"><i class="far fa-square"></i></button></div></td>
            <td class="v-center"><div>' . $this->getNome() . '</div></td>
            <td>
                <img id="img-' . $this->getId() . '" src="data:image;base64,' . gzuncompress($this->getConteudo()) . '" width="100%">
            </td>
        </tr>');
    }

    public function display_img_selection(){
        // Caso a seleção seja para um campo de imagem comum
        // Nesta função, Nome contém o nome do arquivo e conteudo o id do campo que conterá a imagem
        $id = $this->getId();
        $id_campo = $id[1];
        $id = $id[0];
        $args = "'personalizacao_upload.php', {selecao: $id, campo: $id_campo}";
        echo('
        <tr onclick="post('.$args.')">
            <td class="v-center"><div>' . $this->getNome() . '</div></td>
            <td>
                <img id="img-' . $id . '" src="data:image;base64,' . gzuncompress($this->getConteudo()) . '" width="100%">
            </td>
        </tr>');
    }

    public function display_img_simple(){
        // Caso o tipo seja uma Imagem
        $id = $this->getId();
        echo('
        <tr id="'.$id.'" onclick="addToSelection(this)">
            <td class="v-center"><div>' . $this->getNome() . '</div></td>
            <td><img id="img-1" src="data:image;base64,' . gzuncompress($this->getConteudo()) . '" width="100%"></td>
        </tr>');
    }

    public function display(){
        $tipo = $this->getTipo();

        switch ($tipo){
            case 'img':
                $this->display_img();
            break;
            case 'txt':
                $this->display_txt();
            break;
            case 'img-select':
                $this->display_img_selection();
            break;
            case 'car-select':
                $this->display_car_selection();
            break;
            case 'img-simple':
                $this->display_img_simple();
            break;
        }
    }

}