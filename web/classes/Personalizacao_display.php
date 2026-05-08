<?php

if (file_exists("dao/Conexao.php")){
    require_once "dao/Conexao.php";
}elseif (file_exists("../dao/Conexao.php")) {
    require_once "../dao/Conexao.php";
}elseif (file_exists("../../dao/Conexao.php")){
    require_once "../../dao/Conexao.php";
}

define('NO_DATA', "Nenhum conteúdo selecionado");

class Display_campo{

    private $campo;
    private $tipo;
    private $conteudo;


// Constructor

    public function __construct($c,$t){
        $this
            ->setCampo($c)
            ->setTipo($t)
        ;
    }


// Getters e Setters

    public function getCampo()
    {
        return $this->campo;
    }

    public function setCampo($campo)
    {
        $this->campo = $campo;

        return $this;
    }

    public function getTipo()
    {
        return $this->tipo;
    }

    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

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

    // Metodos

    private function PDO(){
        return Conexao::connect();
    }

    private function getQuery($q){
        $pdo = $this->PDO();
        $res = $pdo->query($q);
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    //Começar por aqui
    public function display_txt(){
        $result = $this->getQuery("select * from selecao_paragrafo where nome_campo='" . $this->getCampo() . "';");
        if (count($result) == 1){
            $this->setConteudo($result[0]['paragrafo']);
            echo('
            <div><h1>' . $this->getCampo() . '</h1></div>
            <p>' . nl2br(htmlspecialchars($this->getConteudo())) . '</p>
            ');
        }else{
            $this->setConteudo(NO_DATA);
            echo('
            <div><h1>' . $this->getCampo() . '</h1></div>
            <p>' . nl2br(htmlspecialchars($this->getConteudo())) . '</p>
            ');
        }
    }

    public function display_str(){
        $result = $this->getQuery("select * from selecao_paragrafo where nome_campo='" . $this->getCampo() . "';");
        if (count($result) == 1){
            $this->setConteudo($result[0]['paragrafo']);
            echo(nl2br(htmlspecialchars($this->getConteudo())));
        }else{
            $this->setConteudo(NO_DATA);
            echo(nl2br(htmlspecialchars($this->getConteudo())));
        }
    }

    public function display_file(){
        // Procura o arquivo baseado no nome do campo (não funcionar com carrossel)
        $nome_campo = $this->getCampo();

        $pdo = Conexao::connect();

        $stmt = $pdo->prepare("
        select i.imagem as arquivo
        from campo_imagem c
        inner join tabela_imagem_campo ic on c.id_campo = ic.id_campo
        inner join imagem i on ic.id_imagem = i.id_imagem
        where c.nome_campo=:nomeCampo");

        $stmt->bindValue(':nomeCampo', $nome_campo);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) == 1){
            $this->setConteudo(gzuncompress($result[0]['arquivo']));
            echo('data:image;base64,'.$this->getConteudo());
        }else{
            $this->setConteudo(NO_DATA);
            echo($this->getConteudo());
        }
    }

    public function display_file_user(){
        // Procura o arquivo baseado no nome do campo (não funcionar com carrossel)
        $nome_campo = $this->getCampo();
        $result = $this->getQuery("
        select i.imagem as arquivo
        from campo_imagem c
        inner join tabela_imagem_campo ic on c.id_campo = ic.id_campo
        inner join imagem i on ic.id_imagem = i.id_imagem
        where c.nome_campo='$nome_campo';");
        if (count($result) == 1){
            $this->setConteudo(gzuncompress($result[0]['arquivo']));
            echo('data:image;base64,'.$this->getConteudo());
        }else{
            $this->setConteudo(NO_DATA);
            echo($this->getConteudo());
        }
    }

    public function getCar(){
        // Retorna uma array de arquivos
        $nome_campo = $this->getCampo();
        $result = $this->getQuery("
        select c.id_campo as id, c.nome_campo as nome, i.imagem as arquivo, i.tipo as tipo
        from campo_imagem c
        inner join tabela_imagem_campo ic on c.id_campo = ic.id_campo
        inner join imagem i on ic.id_imagem = i.id_imagem
        where c.nome_campo='$nome_campo';");
        if ($result){
            return $result;
        }
        return false;
    }

    public function display_err(){
        echo("O tipo selecionado não existe. Tipos: [txt, str, file, car]");
    }

    public function display(){
        switch ($this->getTipo()){
            case "txt":
                $this->display_txt();
            break;
            case "str":
                $this->display_str();
            break;
            case "file":
                $this->display_file();
            break;
            case "car":
                return $this->getCar();
            break;
            default:
                $this->display_err();
        }
    }
}