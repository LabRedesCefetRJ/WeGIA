<?php
require_once ROOT . "/dao/Conexao.php";
require_once ROOT . "/classes/Util.php";

class Item
{

    private $relatorio;
    private $origem;
    private $destino;
    private $tipo;
    private $responsavel;
    private $periodo; // Array('inicio' => data de inicio, 'fim' => data de fim)
    private $almoxarifado;
    private $query;
    private $paramsExternos = [];
    private $mostrarZerado;
    private $DDL_cmd;

    // Constructor

    public function __construct($relat, $o_d, $t, $resp, $p, $a, $z = false)
    {
        $this
            ->setRelatorio($relat)
            ->setOrigem($o_d)
            ->setDestino($o_d)
            ->setTipo($t)
            ->setResponsavel($resp)
            ->setPeriodo($p)
            ->setAlmoxarifado($a)
            ->setMostrarZerado($z)
        ;
    }

    // Metodos

    public function hasValue()
    {
        return ($this->getOrigem()
            || $this->getTipo()
            || $this->getResponsavel()
            || $this->getPeriodo()['inicio']
            || $this->getPeriodo()['fim']
            || $this->getAlmoxarifado()
            || $this->getMostrarZerado()
        );
    }

    private function param($params, $cont)
    {
        if ($cont) {
            return $params . 'AND';
        }
        return $params;
    }

    private function entrada()
    {
        if ($this->hasValue()) {
            $params = "WHERE ientrada.qtd > 0 AND ientrada.oculto=false ";
            $cont = 2;

            if ($this->getOrigem()) {
                $params = $this->param($params, $cont) . ' origem.id_origem = :idOrigem ';
                $this->paramsExternos[':idOrigem'] = $this->getOrigem();
                $cont++;
            }

            if ($this->getTipo()) {
                $params = $this->param($params, $cont) . ' tipo_entrada.id_tipo = :idTipo ';
                $this->paramsExternos[':idTipo'] = $this->getTipo();
                $cont++;
            }

            if ($this->getResponsavel()) {
                $params = $this->param($params, $cont) . ' pessoa.id_pessoa = :idPessoa ';
                $this->paramsExternos[':idPessoa'] = $this->getResponsavel();
                $cont++;
            }

            if ($this->getAlmoxarifado()) {
                $params = $this->param($params, $cont) . ' almoxarifado.id_almoxarifado = :idAlmoxarifado ';
                $this->paramsExternos[':idAlmoxarifado'] = $this->getAlmoxarifado();
                $cont++;
            }

            if ($this->getPeriodo()['inicio']) {
                $params = $this->param($params, $cont) . ' entrada.data >= :dataInicio ';
                $this->paramsExternos[':dataInicio'] = $this->getPeriodo()['inicio'];
                $cont++;
            }

            if ($this->getPeriodo()['fim']) {
                $params = $this->param($params, $cont) . ' entrada.data <= :dataFim ';
                $this->paramsExternos[':dataFim'] = $this->getPeriodo()['fim'];
                $cont++;
            }

            $this->setQuery("
                SELECT 
                    SUM(ientrada.qtd) as qtd_total, 
                    produto.descricao, 
                    SUM(ientrada.qtd*ientrada.valor_unitario) as valor_total, 
                    ientrada.valor_unitario, 
                    entrada.data as data,
                    unidade.descricao_unidade as unidade
                FROM ientrada 
                LEFT JOIN produto ON produto.id_produto = ientrada.id_produto 
                LEFT JOIN entrada ON entrada.id_entrada = ientrada.id_entrada 
                LEFT JOIN origem ON origem.id_origem = entrada.id_origem 
                LEFT JOIN tipo_entrada ON tipo_entrada.id_tipo = entrada.id_tipo 
                LEFT JOIN almoxarifado ON almoxarifado.id_almoxarifado = entrada.id_almoxarifado 
                LEFT JOIN pessoa ON pessoa.id_pessoa = entrada.id_responsavel
                LEFT JOIN unidade ON unidade.id_unidade = produto.id_unidade
                $params
                GROUP BY concat(ientrada.id_produto, ientrada.valor_unitario)
                ORDER BY produto.descricao
            ");
        } else {
            $this->setQuery("
                SELECT 
                    SUM(ientrada.qtd) as qtd_total, 
                    produto.descricao, 
                    SUM(ientrada.qtd*ientrada.valor_unitario) as valor_total, 
                    ientrada.valor_unitario, 
                    entrada.data as data,
                    unidade.descricao_unidade as unidade
                FROM ientrada 
                LEFT JOIN produto ON produto.id_produto = ientrada.id_produto 
                LEFT JOIN entrada ON entrada.id_entrada = ientrada.id_entrada
                LEFT JOIN unidade ON unidade.id_unidade = produto.id_unidade
                WHERE ientrada.qtd > 0 AND ientrada.oculto = false 
                GROUP BY concat(ientrada.id_produto, ientrada.valor_unitario)
                ORDER BY produto.descricao
            ");
        }
    }


    private function saida()
    {
        if ($this->hasValue()) {
            $params = "WHERE isaida.qtd > 0 AND isaida.oculto = false ";
            $cont = 2;

            if ($this->getDestino()) {
                $params = $this->param($params, $cont) . ' destino.id_destino = :idDestino ';
                $this->paramsExternos[':idDestino'] = $this->getDestino();
                $cont++;
            }

            if ($this->getTipo()) {
                $params = $this->param($params, $cont) . ' tipo_saida.id_tipo = :idTipo ';
                $this->paramsExternos[':idTipo'] = $this->getTipo();
                $cont++;
            }

            if ($this->getResponsavel()) {
                $params = $this->param($params, $cont) . ' pessoa.id_pessoa = :idResponsavel ';
                $this->paramsExternos[':idResponsavel'] = $this->getResponsavel();
                $cont++;
            }

            if ($this->getAlmoxarifado()) {
                $params = $this->param($params, $cont) . ' almoxarifado.id_almoxarifado = :idAlmoxarifado ';
                $this->paramsExternos[':idAlmoxarifado'] = $this->getAlmoxarifado();
                $cont++;
            }

            if ($this->getPeriodo()['inicio']) {
                $params = $this->param($params, $cont) . ' saida.data >= :dataInicio ';
                $this->paramsExternos[':dataInicio'] = $this->getPeriodo()['inicio'];
                $cont++;
            }

            if ($this->getPeriodo()['fim']) {
                $params = $this->param($params, $cont) . ' saida.data <= :dataFim ';
                $this->paramsExternos[':dataFim'] = $this->getPeriodo()['fim'];
                $cont++;
            }

            $this->setQuery("
                SELECT 
                    SUM(isaida.qtd) as qtd_total, 
                    produto.descricao, 
                    SUM(isaida.qtd * isaida.valor_unitario) as valor_total, 
                    isaida.valor_unitario, 
                    saida.data as data,
                    unidade.descricao_unidade as unidade
                FROM isaida 
                LEFT JOIN produto ON produto.id_produto = isaida.id_produto 
                LEFT JOIN saida ON saida.id_saida = isaida.id_saida 
                LEFT JOIN destino ON destino.id_destino = saida.id_destino 
                LEFT JOIN tipo_saida ON tipo_saida.id_tipo = saida.id_tipo 
                LEFT JOIN almoxarifado ON almoxarifado.id_almoxarifado = saida.id_almoxarifado 
                LEFT JOIN pessoa ON pessoa.id_pessoa = saida.id_responsavel 
                LEFT JOIN unidade ON unidade.id_unidade = produto.id_unidade
                $params
                GROUP BY concat(isaida.id_produto, isaida.valor_unitario)
                ORDER BY produto.descricao
            ");
        } else {
            $this->setQuery("
                SELECT 
                    SUM(ientrada.qtd) as qtd_total, 
                    produto.descricao, 
                    SUM(ientrada.qtd*ientrada.valor_unitario) as valor_total, 
                    ientrada.valor_unitario, 
                    entrada.data as data,
                    unidade.descricao_unidade as unidade
                FROM ientrada 
                LEFT JOIN produto ON produto.id_produto = ientrada.id_produto 
                LEFT JOIN entrada ON entrada.id_entrada = ientrada.id_entrada
                LEFT JOIN unidade ON unidade.id_unidade = produto.id_unidade
                WHERE ientrada.qtd > 0 AND ientrada.oculto = false
                GROUP BY concat(ientrada.id_produto, ientrada.valor_unitario)
                ORDER BY produto.descricao
            ");
        }
    }


    private function estoque()
    {
        if ($this->hasValue()) {
            $params = "WHERE oculto = false ";
            $cont = 1;

            if ($this->getAlmoxarifado()) {
                $params = $this->param($params, $cont) . " id_almoxarifado = :idAlmoxarifado ";
                $this->paramsExternos[':idAlmoxarifado'] = $this->getAlmoxarifado();
                $cont++;
            }

            $showZero = !!$this->getMostrarZerado();

            $table1 = [
                // Caso 0: não mostrar zerados
                "CREATE TEMPORARY TABLE IF NOT EXISTS tabela_produto_entrada 
                SELECT produto.id_produto, produto.preco, sum(qtd) as somatorio, produto.descricao, unidade.descricao_unidade as unidade, (sum(qtd) * ientrada.valor_unitario) as Total, 
                concat(ientrada.id_produto, valor_unitario) as kungfu 
                FROM ientrada, produto
                INNER JOIN unidade ON unidade.id_unidade = produto.id_unidade
                WHERE ientrada.id_produto = produto.id_produto
                GROUP BY kungfu 
                ORDER BY produto.descricao;
                ",

                // Caso 1: mostrar zerados
                "CREATE TEMPORARY TABLE IF NOT EXISTS tabela_produto_entrada 
                SELECT produto.id_produto, produto.preco, IFNULL(sum(qtd), 0) as somatorio, produto.descricao, unidade.descricao_unidade as unidade, (sum(qtd) * ientrada.valor_unitario) as Total, 
                concat(produto.id_produto, IFNULL(ientrada.valor_unitario, 0)) as kungfu 
                FROM produto 
                LEFT JOIN ientrada ON ientrada.id_produto = produto.id_produto 
                LEFT JOIN unidade ON unidade.id_unidade = produto.id_unidade
                GROUP BY kungfu 
                ORDER BY produto.descricao;
                "
            ];

            // DDL com tabelas temporárias
            $this->setDDL_cmd(
                $table1[(int)$showZero] .
                    "CREATE TEMPORARY TABLE IF NOT EXISTS tabelaPrecoMedio 
                SELECT id_produto, IFNULL(SUM(Total) / SUM(somatorio), preco) AS PrecoMedio 
                FROM tabela_produto_entrada 
                GROUP BY tabela_produto_entrada.descricao;
    
                CREATE TEMPORARY TABLE IF NOT EXISTS estoque_com_preco_atualizado 
                SELECT 
                    p.id_produto, p.id_categoria_produto, p.id_unidade, p.codigo, 
                    IFNULL(e.qtd, 0) AS qtd, p.descricao, u.descricao_unidade As unidade, pm.PrecoMedio, 
                    IFNULL(e.qtd * pm.PrecoMedio, 0) AS Total, 
                    a.id_almoxarifado, p.oculto 
                FROM tabelaPrecoMedio pm
                LEFT JOIN produto p ON pm.id_produto = p.id_produto
                LEFT JOIN unidade u ON u.id_unidade = p.id_unidade 
                LEFT JOIN estoque e ON e.id_produto = p.id_produto 
                LEFT JOIN almoxarifado a ON a.id_almoxarifado = e.id_almoxarifado
                WHERE p.id_produto = pm.id_produto;
                "
            );

            // Query principal segura
            $this->setQuery(
                "SELECT 
                    e.qtd AS qtd_total, 
                    e.descricao, 
                    e.Total AS valor_total, 
                    e.PrecoMedio,
                    e.unidade
                FROM estoque_com_preco_atualizado e 
                $params
                ORDER BY e.descricao;
                "
            );
        } else {
            // Parte sem filtros continua igual (sem entrada externa)
            $this->setDDL_cmd("
                CREATE TEMPORARY TABLE IF NOT EXISTS tabela1 
                SELECT produto.id_produto, SUM(qtd) AS somatorio, produto.descricao, unidade.descricao_unidade as unidade, (SUM(qtd) * ientrada.valor_unitario) AS Total, 
                CONCAT(ientrada.id_produto, valor_unitario) AS kungfu 
                FROM ientrada, produto
                INNER JOIN unidade ON unidade.id_unidade = produto.id_unidade
                WHERE ientrada.id_produto = produto.id_produto 
                GROUP BY kungfu 
                ORDER BY produto.descricao;
    
                CREATE TEMPORARY TABLE IF NOT EXISTS tabela2 
                SELECT id_produto, (SUM(Total) / SUM(somatorio)) AS PrecoMedio 
                FROM tabela1 
                GROUP BY tabela1.descricao;
    
                CREATE TEMPORARY TABLE IF NOT EXISTS estoque_com_preco_atualizado 
                SELECT estoque.id_produto, id_categoria_produto, id_unidade, codigo, qtd, descricao, PrecoMedio, (qtd * PrecoMedio) AS Total, produto.oculto
                FROM tabela2, estoque, produto
                WHERE produto.id_produto = estoque.id_produto 
                  AND estoque.id_produto = tabela2.id_produto;
            ");

            $this->setQuery("
                SELECT e.qtd AS qtd_total, e.descricao, e.Total AS valor_total, e.PrecoMedio, u.descricao_unidade as unidade
                FROM estoque_com_preco_atualizado e, unidade u
                WHERE qtd != 0 AND oculto = false AND u.id_unidade = e.id_unidade
                ORDER BY descricao;
            ");
        }
    }


    private function selecRelatorio()
    {
        switch ($this->getRelatorio()) {
            case 'entrada':
                $this->entrada();
                break;
            case 'saida':
                $this->saida();
                break;
            case 'estoque':
                $this->estoque();
                break;
        }
    }

    private function query()
    {
        $pdo = Conexao::connect();

        if ($this->getDDL_cmd()) {
            $pdo->exec($this->getDDL_cmd());
        }

        $res = $pdo->prepare($this->getQuery());

        foreach ($this->paramsExternos as $key => $value) {
            $res->bindValue($key, $value); // Bind seguro com chave e valor
        }

        $res->execute(); // Importante: executar a query após bind
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    public function display()
    {
        $this->selecRelatorio();
        $query = $this->query(); //<-- execução ocorre aqui
        $tot_val = 0;

        foreach ($query as $item) {
            if ($this->getRelatorio() == 'estoque') {
                $class = '';
                if ($item['qtd_total'] < 0) {
                    $item['valor_total'] = 0;
                    $class = 'class="table-danger"';
                }

                echo ('
                <tr ' . $class . '>
                    <td scope="row" class="align-right">' . htmlspecialchars($item['qtd_total']) . '</td>
                    <td>' . htmlspecialchars($item['descricao'], ENT_QUOTES, 'UTF-8') . '</td>
                    <td>R$ ' . number_format($item['PrecoMedio'], 2) . '</td>
                    <td>' . htmlspecialchars($item['unidade'], ENT_QUOTES, 'UTF-8') . '</td>
                    <td>R$ ' . number_format($item['valor_total'], 2) . '</td>
                </tr>
            ');
            } else {
                $util = new Util();
                echo ('
                <tr>
                    <td scope="row" class="align-right">' . htmlspecialchars($item['qtd_total']) . '</td>
                    <td>' . htmlspecialchars($item['descricao'], ENT_QUOTES, 'UTF-8') . '</td>
                    <td>' . htmlspecialchars($util->formatoDataDMY($item['data']), ENT_QUOTES, 'UTF-8') . '</td>
                    <td>R$ ' . number_format($item['valor_unitario'], 2) . '</td>
                    <td>' . htmlspecialchars($item['unidade'], ENT_QUOTES, 'UTF-8') . '</td>
                    <td>R$ ' . number_format($item['valor_total'], 2) . '</td>
                </tr>
            ');
            }

            $tot_val += $item['valor_total'];
        }

        echo ('
    <tr class="table-info">
        <td scope="row" colspan="'.(($this->getRelatorio() == 'estoque') ? 4: 5) .'">Valor total:</td>
        <td>R$ ' . number_format($tot_val, 2) . '</td>
    </tr>
    ');
    }


    // Getters e Setters

    public function getRelatorio()
    {
        return $this->relatorio;
    }

    public function setRelatorio($relatorio)
    {
        $this->relatorio = $relatorio;

        return $this;
    }

    public function getOrigem()
    {
        return $this->origem;
    }

    public function setOrigem($origem)
    {
        $this->origem = $origem;

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

    public function getResponsavel()
    {
        return $this->responsavel;
    }

    public function setResponsavel($responsavel)
    {
        $this->responsavel = $responsavel;

        return $this;
    }

    public function getPeriodo()
    {
        return $this->periodo;
    }

    public function setPeriodo($periodo)
    {
        $this->periodo = $periodo;

        return $this;
    }

    public function getAlmoxarifado()
    {
        return $this->almoxarifado;
    }

    public function setAlmoxarifado($almoxarifado)
    {
        $this->almoxarifado = $almoxarifado;

        return $this;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    public function getDestino()
    {
        return $this->destino;
    }

    public function setDestino($destino)
    {
        $this->destino = $destino;

        return $this;
    }

    public function getDDL_cmd()
    {
        return $this->DDL_cmd;
    }

    public function setDDL_cmd($DDL_cmd)
    {
        $this->DDL_cmd = $DDL_cmd;

        return $this;
    }

    public function getMostrarZerado()
    {
        return $this->mostrarZerado;
    }

    public function setMostrarZerado($mostrarZerado)
    {
        $this->mostrarZerado = $mostrarZerado;

        return $this;
    }
}
