<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'ContatoInstituicaoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'ContatoInstituicaoMySQL.php';

class ContatoInstituicao
{
    //atributos primitivos
    private int $id;
    private string $descricao;
    private string $contato;

    private int $limiteCampo = 256;

    //abstração de persistência
    private ContatoInstituicaoDAO $contatoInstituicaoDao;

    public function __construct(string $descricao, string $contato, ?ContatoInstituicaoDAO $contatoInstituicaoDao = null)
    {
        $this->setDescricao($descricao)->setContato($contato);

        if (isset($contatoInstituicaoDao))
            $this->contatoInstituicaoDao = $contatoInstituicaoDao;
    }

    //métodos de comportamento

    /**
     * Cria a persistência de um novo contato no banco de dados da instituição.
     */
    public function incluir()
    {
        if (!isset($this->contatoInstituicaoDao))
            $this->contatoInstituicaoDao = new ContatoInstituicaoMySQL(Conexao::connect());

        return $this->contatoInstituicaoDao->incluir($this);
    }

    /**
     * Retorna um objeto do tipo ContatoInstituicao de acordo com o quê está salvo no banco de dados.
     */
    public static function listarPorId(int $id, ?ContatoInstituicaoDAO $contatoInstituicaoDao = null)
    {
        if (!isset($contatoInstituicaoDao))
            $contatoInstituicaoDao = new ContatoInstituicaoMySQL(Conexao::connect());

        return $contatoInstituicaoDao->listarPorId($id);
    }

    /**
     * Retorna um array de objetos do tipo ContatoInstituicao de acordo com o quê está salvo no banco de dados.
     */
    public static function listarTodos(?ContatoInstituicaoDAO $contatoInstituicaoDao = null)
    {
        if (!isset($contatoInstituicaoDao))
            $contatoInstituicaoDao = new ContatoInstituicaoMySQL(Conexao::connect());

        return $contatoInstituicaoDao->listarTodos();
    }

    //métodos acessores

    public function setId(int $id)
    {
        if ($id < 1)
            throw new InvalidArgumentException('O id informado não está dentro dos limites permitidos.', 412);

        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Verifica se a descrição informada está dentro do limite máximo de caracteres da aplicação, em caso positivo atribui o valor informado para a propriedade $descricao da classe
     */
    public function setDescricao(string $descricao)
    {
        if (empty($descricao) || strlen($descricao) < 1)
            throw new InvalidArgumentException('A descrição de um contato não pode estar vazia.', 412);

        if (strlen($descricao) > $this->limiteCampo)
            throw new InvalidArgumentException("A descrição não pode ultrapassar o limite de {$this->limiteCampo} caracteres.", 412);

        $this->descricao = $descricao;
        return $this;
    }

    public function getDescricao()
    {
        return $this->descricao;
    }

    /**
     * Verifica se o contato informado possui um formato de e-mail ou de telefone válido, em caso positivo atribui o valor informado para a propriedade $contato da classe
     */
    public function setContato(string $contato)
    {
        if (empty($contato) || strlen($contato) < 1)
            throw new InvalidArgumentException('A descrição de um contato não pode estar vazia.', 412);

        if (strlen($contato) > $this->limiteCampo)
            throw new InvalidArgumentException("Um contato não pode ter mais de {$this->limiteCampo} caracteres.", 412);

        //validar formato de e-mail
        if (filter_var($contato, FILTER_VALIDATE_EMAIL)) {
            $this->contato = trim($contato);
            return $this;
        }

        //validar formato de telefone
        $telefoneValidado = Util::validarTelefone($contato);

        if ($telefoneValidado === false)
            throw new InvalidArgumentException('O contato informado não cumpre os formatos válidos para e-mail e telefone.', 412);

        $this->contato = $telefoneValidado;
        return $this;
    }

    public function getContato()
    {
        return $this->contato;
    }
}
