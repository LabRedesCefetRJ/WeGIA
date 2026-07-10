<?php
require_once ROOT . '/classes/Produto.php';
require_once ROOT . '/dao/Conexao.php';
require_once ROOT . '/Functions/funcoes.php';
class ProdutoDAO
{
	private PDO $pdo;

	public function __construct(?PDO $pdo = null)
	{
		if (is_null($pdo)) {
			$this->pdo = $pdo = Conexao::connect();
		} else {
			$this->pdo = $pdo;
		}
	}
	public function incluir($produto)
	{
		$pdo = Conexao::connect();
		// Verifica se o produto já existe 
		$stmtExistente = $pdo->prepare("
				SELECT id_produto, oculto, ativo 
				FROM produto 
				WHERE descricao = :descricao
			");
		$stmtExistente->bindValue(':descricao', trim($produto->getDescricao()), PDO::PARAM_STR);
		$stmtExistente->execute();
		$existente = $stmtExistente->fetch(PDO::FETCH_ASSOC);

		if ($existente) {
    		$oculto = (bool) intval($existente['oculto']);
    		$ativo = (bool) intval($existente['ativo']);

    		if ($oculto) {
        		header("Location: " . WWW . "html/matPat/restaurar_produto.php?id_produto=" . urlencode($existente['id_produto']));
        		exit;
    		}

    		if (!$ativo) {
        		header("Location: " . WWW . "html/matPat/listar_produto.php?tipo=arquivado&flag=warn&msg=" . urlencode("A descrição inserida já existe como produto arquivado. Reative o produto para utilizá-lo novamente."));
				exit;
    		}

    		header("Location: " . WWW . "html/matPat/cadastro_produto.php?flag=warn&msg=" . urlencode("A descrição inserida já existe!"));
    		exit;
		}

		if ($produto->getCodigo() !== null && $produto->getCodigo() !== '') {
			
			$sql = "SELECT id_produto
				FROM produto
				WHERE codigo = :codigo
				AND oculto = 0
				AND ativo = 1";
		
			$stmtCodigo = $pdo->prepare($sql);

			$codigo = $produto->getCodigo();

			$stmtCodigo->bindValue(':codigo', $codigo, PDO::PARAM_STR);
			$stmtCodigo->execute();

			if ($stmtCodigo->fetch(PDO::FETCH_ASSOC)) {
    			$_SESSION['erro_produto'] = "O código do produto informado já existe. Por favor, informe um código diferente!";
    			header("Location: " . WWW . "html/matPat/cadastro_produto.php");
    			exit;
			}
		}

		$sql = "INSERT INTO produto (id_categoria_produto, id_unidade, descricao, codigo, preco)
					VALUES (:id_categoria_produto, :id_unidade, :descricao, :codigo, :preco)";
		$stmt = $pdo->prepare($sql);

		$stmt->bindValue(':id_categoria_produto', $produto->get_categoria_produto(), PDO::PARAM_INT);
		$stmt->bindValue(':id_unidade', $produto->get_unidade(), PDO::PARAM_INT);
		$stmt->bindValue(':descricao', preg_replace('/\s+/', ' ', trim($produto->getDescricao())), PDO::PARAM_STR);
		$stmt->bindValue(':codigo', $produto->getCodigo(), PDO::PARAM_STR);
		$stmt->bindValue(':preco', $produto->getPreco(), PDO::PARAM_STR);
		$stmt->execute();
	}

	public function excluir($id_produto)
	{
		$sql = 'DELETE FROM produto WHERE id_produto = :id_produto';

		$pdo = Conexao::connect();
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':id_produto', $id_produto);
		$stmt->execute();
	}

	public function listarTodos()
	{
		$produtos = array();
		$pdo = Conexao::connect();
		$consulta = $pdo->query("SELECT p.id_produto,p.preco,p.descricao,p.codigo,c.descricao_categoria,u.descricao_unidade 
				FROM produto p 
				INNER JOIN categoria_produto c ON p.id_categoria_produto = c.id_categoria_produto
				INNER JOIN unidade u ON u.id_unidade = p.id_unidade
				WHERE oculto=false
				AND p.ativo = 1
				ORDER BY p.descricao");
		$x = 0;
		while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
			$produtos[$x] = array('id_produto' => $linha['id_produto'], 'preco' => $linha['preco'], 'descricao' => $linha['descricao'], 'codigo' => $linha['codigo'], 'descricao_categoria' => $linha['descricao_categoria'], 'descricao_unidade' => $linha['descricao_unidade']);
			$x++;
		}

		return json_encode($produtos);
	}

	//Consultar um utilizando o ID
	public function listarId($id_produto)
	{
		$pdo = Conexao::connect();
		$sql = "SELECT p.id_produto,p.preco,p.descricao,p.codigo,p.id_categoria_produto, c.descricao_categoria, p.id_unidade, u.descricao_unidade FROM produto p 
	        		INNER JOIN categoria_produto c ON p.id_categoria_produto = c.id_categoria_produto 
	        		INNER JOIN unidade u ON p.id_unidade = u.id_unidade 
	        		WHERE p.id_produto = :id_produto";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':id_produto', $id_produto);

		$stmt->execute();
		$produtos = array();
		while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$produtos[] = array('id_produto' => $linha['id_produto'], 'preco' => $linha['preco'], 'descricao' => $linha['descricao'], 'codigo' => $linha['codigo'], 'id_categoria_produto' => $linha['id_categoria_produto'], 'descricao_categoria' => $linha['descricao_categoria'], 'id_unidade' => $linha['id_unidade'], 'descricao_unidade' => $linha['descricao_unidade']);
		}

		return json_encode($produtos);
	}

	public function listarporCodigo($codigo)
	{
		$codigo = "%" . $codigo . "%";

		$pdo = Conexao::connect();
		$sql = "SELECT p.preco,p.descricao,p.codigo,c.descricao_categoria,u.descricao_unidade FROM produto p INNER JOIN categoria_produto c ON p.id_categoria_produto = c.id_categoria_produto
		            	INNER JOIN unidade u ON u.id_unidade = p.id_unidade WHERE p.codigo LIKE :codigo";
		$consulta = $pdo->prepare($sql);
		$consulta->execute(array(
			':codigo' => $codigo
		));
		$produtos = array();
		while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
			$produto = new Produto($linha['descricao'], $codigo, $linha['preco']);
			$produtos[] = $produto;
		}

		return $produtos;
	}
	public function listarporNome($descricao)
	{
		$descricao = "%" . $descricao . "%";

		$pdo = Conexao::connect();
		$sql = "SELECT p.preco,p.descricao,p.codigo,c.descricao_categoria,u.descricao_unidade FROM produto p INNER JOIN categoria_produto c ON p.id_categoria_produto = c.id_categoria_produto
		            	INNER JOIN unidade u ON u.id_unidade = p.id_unidade WHERE p.descricao LIKE :descricao";
		$consulta = $pdo->prepare($sql);
		$consulta->execute(array(
			':descricao' => $descricao
		));
		$produtos = array();
		while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
			$produto = new Produto($descricao, $linha['codigo'], $linha['preco']);
			$produtos[] = $produto;
		}

		return $produtos;
	}

	public function listarDescricao()
	{
    	$produtos = array();

    	$consulta = $this->pdo->query("
        	SELECT id_produto, descricao, codigo, preco 
        	FROM produto 
        	WHERE oculto = false
        	AND ativo = 1
        	ORDER BY descricao
    	");

    	$x = 0;

    	while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
        	$produtos[$x] = array(
            	'id_produto' => $linha['id_produto'],
            	'descricao' => $linha['descricao'],
            	'preco' => $linha['preco'],
            	'codigo' => $linha['codigo']
        	);

        	$x++;
    	}

    	return json_encode($produtos);
	}

	public function listarUm($id)
	{
		$pdo = Conexao::connect();
		$sql = "SELECT id_produto, descricao, codigo, preco FROM produto where id_produto = :id_produto";
		$consulta = $pdo->prepare($sql);
		$consulta->execute(array(
			':id_produto' => $id,
		));
		while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
			$produto = new Produto($linha['descricao'], $linha['codigo'], $linha['preco']);
			$produto->setId_produto($linha['id_produto']);
		}

		return $produto;
	}

	public function alterarProduto($produto)
	{
		$pdo = Conexao::connect();

		if ($produto->getCodigo() !== null && $produto->getCodigo() !== '') {
			$stmtCodigo = $pdo->prepare("
				SELECT id_produto
				FROM produto
				WHERE codigo = :codigo
				AND id_produto != :id_produto
				AND oculto = false
				AND ativo = 1
			");

			$stmtCodigo->bindValue(':codigo', $produto->getCodigo(), PDO::PARAM_STR);
			$stmtCodigo->bindValue(':id_produto', $produto->getId_produto(), PDO::PARAM_INT);
			$stmtCodigo->execute();

			if ($stmtCodigo->fetch(PDO::FETCH_ASSOC)) {
				$_SESSION['erro_produto'] = "O código do produto informado já existe. Por favor, informe um código diferente!";
				header("Location: " . WWW . "html/matPat/alterar_produto.php");
				exit;
			}
		}

		$sql = 'UPDATE produto  set id_categoria_produto=:id_categoria_produto, id_unidade=:id_unidade, descricao=:descricao, codigo=:codigo, preco=:preco WHERE id_produto=:id_produto';
		$sql = str_replace("'", "\'", $sql);

		$stmt = $pdo->prepare($sql);


		$id_categoria_produto = $produto->get_categoria_produto();
		$id_unidade = $produto->get_unidade();
		$descricao = $produto->getDescricao();
		$codigo = $produto->getCodigo();
		$preco = $produto->getPreco();
		$id_produto = $produto->getId_produto();

		$stmt->bindParam(':id_categoria_produto', $id_categoria_produto);
		$stmt->bindParam(':id_unidade', $id_unidade);
		$stmt->bindParam(':descricao', $descricao);
		$stmt->bindParam(':codigo', $codigo);
		$stmt->bindParam(':preco', $preco);
		$stmt->bindParam(':id_produto', $id_produto);

		$stmt->execute();
	}

	public function getProdutosPorAlmoxarifado(int $almoxarifadoId)
	{
    	$sql = "
        	SELECT 
            	produto.id_produto, 
            	produto.codigo, 
            	produto.descricao, 
            	estoque.qtd, 
            	produto.preco 
        	FROM produto
        	INNER JOIN estoque ON produto.id_produto = estoque.id_produto
        	WHERE estoque.qtd > 0
        	AND estoque.id_almoxarifado = :almoxarifadoId
        	AND produto.oculto = false
        	AND produto.ativo = 1
        	ORDER BY produto.descricao
    	";

    	$pdo = Conexao::connect();

    	$stmt = $pdo->prepare($sql);
    	$stmt->bindParam(':almoxarifadoId', $almoxarifadoId);
    	$stmt->execute();

    	return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function listarArquivados()
	{
    	$produtos = array();
    	$pdo = Conexao::connect();

    	$consulta = $pdo->query("
        	SELECT 
            	p.id_produto,
            	p.preco,
            	p.descricao,
            	p.codigo,
            	c.descricao_categoria,
            	u.descricao_unidade 
        	FROM produto p 
        	INNER JOIN categoria_produto c ON p.id_categoria_produto = c.id_categoria_produto
        	INNER JOIN unidade u ON u.id_unidade = p.id_unidade
        	WHERE p.oculto = false
          		AND p.ativo = 0
        	ORDER BY p.descricao
    	");

    	while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
        	$produtos[] = array(
            	'id_produto' => $linha['id_produto'],
            	'preco' => $linha['preco'],
            	'descricao' => $linha['descricao'],
            	'codigo' => $linha['codigo'],
            	'descricao_categoria' => $linha['descricao_categoria'],
            	'descricao_unidade' => $linha['descricao_unidade']
        	);
    	}

    	return json_encode($produtos);
	}

	public function possuiHistoricoOuEstoque(int $idProduto): bool
	{
    	$pdo = Conexao::connect();

    	$stmtEstoque = $pdo->prepare("
        	SELECT COALESCE(SUM(qtd), 0) AS qtd
        	FROM estoque
        	WHERE id_produto = :id_produto
    	");
    	$stmtEstoque->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
    	$stmtEstoque->execute();

    	$estoque = $stmtEstoque->fetch(PDO::FETCH_ASSOC);

    	if ($estoque && (int)$estoque['qtd'] > 0) {
        	return true;
    	}

    	$stmtEntrada = $pdo->prepare("
        	SELECT id_ientrada
        	FROM ientrada
        	WHERE id_produto = :id_produto
        	LIMIT 1
    	");
    	$stmtEntrada->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
    	$stmtEntrada->execute();

    	if ($stmtEntrada->fetch(PDO::FETCH_ASSOC)) {
        	return true;
    	}

    	$stmtSaida = $pdo->prepare("
        	SELECT id_isaida
        	FROM isaida
        	WHERE id_produto = :id_produto
        	LIMIT 1
    	");
    	$stmtSaida->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
    	$stmtSaida->execute();

    	return (bool)$stmtSaida->fetch(PDO::FETCH_ASSOC);
	}

	public function arquivar(int $idProduto): void
	{
   		if ($idProduto < 1) {
        	throw new InvalidArgumentException("ID do produto inválido.");
    	}

    	$pdo = Conexao::connect();
    	$pdo->beginTransaction();

    	try {
        	$stmtEntradasAfetadas = $pdo->prepare("
           		SELECT DISTINCT id_entrada
            	FROM ientrada
            	WHERE id_produto = :id_produto
              		AND oculto = false
        	");
        	$stmtEntradasAfetadas->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
        	$stmtEntradasAfetadas->execute();
        	$entradasAfetadas = $stmtEntradasAfetadas->fetchAll(PDO::FETCH_COLUMN);

        	$stmtSaidasAfetadas = $pdo->prepare("
            	SELECT DISTINCT id_saida
            	FROM isaida
            	WHERE id_produto = :id_produto
             		AND oculto = false
        	");
        	$stmtSaidasAfetadas->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
        	$stmtSaidasAfetadas->execute();
        	$saidasAfetadas = $stmtSaidasAfetadas->fetchAll(PDO::FETCH_COLUMN);

        	$stmtProduto = $pdo->prepare("
            	UPDATE produto
            	SET ativo = 0
            	WHERE id_produto = :id_produto
              		AND oculto = false
        	");
        	$stmtProduto->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
        	$stmtProduto->execute();

        	$stmtIentrada = $pdo->prepare("
            	UPDATE ientrada
            	SET oculto = true,
                	oculto_por_produto_inativo = 1
            	WHERE id_produto = :id_produto
              		AND oculto = false
        	");
        	$stmtIentrada->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
        	$stmtIentrada->execute();

        	$stmtIsaida = $pdo->prepare("
            	UPDATE isaida
            	SET oculto = true,
                	oculto_por_produto_inativo = 1
            	WHERE id_produto = :id_produto
              		AND oculto = false
        	");
        	$stmtIsaida->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
        	$stmtIsaida->execute();

        	foreach ($entradasAfetadas as $idEntrada) {
            	$this->recalcularEntradaPorId((int)$idEntrada, $pdo);
        	}

        	foreach ($saidasAfetadas as $idSaida) {
            	$this->recalcularSaidaPorId((int)$idSaida, $pdo);
        	}

        	$pdo->commit();
    	} catch (Exception $e) {
        	$pdo->rollBack();
        	throw $e;
    	}
	}

	public function desarquivar(int $idProduto): void
	{
    	if ($idProduto < 1) {
        	throw new InvalidArgumentException("ID do produto inválido.");
    	}

    	$pdo = Conexao::connect();
    	$pdo->beginTransaction();

    	try {
        	$stmtEntradasAfetadas = $pdo->prepare("
            	SELECT DISTINCT id_entrada
            	FROM ientrada
            	WHERE id_produto = :id_produto
              		AND oculto_por_produto_inativo = 1
        	");
        	$stmtEntradasAfetadas->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
        	$stmtEntradasAfetadas->execute();
        	$entradasAfetadas = $stmtEntradasAfetadas->fetchAll(PDO::FETCH_COLUMN);

        	$stmtSaidasAfetadas = $pdo->prepare("
            	SELECT DISTINCT id_saida
            	FROM isaida
            	WHERE id_produto = :id_produto
              		AND oculto_por_produto_inativo = 1
        	");
        	$stmtSaidasAfetadas->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
        	$stmtSaidasAfetadas->execute();
        	$saidasAfetadas = $stmtSaidasAfetadas->fetchAll(PDO::FETCH_COLUMN);

        	$stmtProduto = $pdo->prepare("
            	UPDATE produto
            	SET ativo = 1
            	WHERE id_produto = :id_produto
              		AND oculto = false
        	");
        	$stmtProduto->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
        	$stmtProduto->execute();

        	$stmtIentrada = $pdo->prepare("
            	UPDATE ientrada
            	SET oculto = false,
                	oculto_por_produto_inativo = 0
            	WHERE id_produto = :id_produto
              	AND oculto_por_produto_inativo = 1
        	");
        	$stmtIentrada->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
        	$stmtIentrada->execute();

        	$stmtIsaida = $pdo->prepare("
            	UPDATE isaida
            	SET oculto = false,
                	oculto_por_produto_inativo = 0
            	WHERE id_produto = :id_produto
              	AND oculto_por_produto_inativo = 1
        	");
        	$stmtIsaida->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
        	$stmtIsaida->execute();

        	foreach ($entradasAfetadas as $idEntrada) {
            	$this->recalcularEntradaPorId((int)$idEntrada, $pdo, true);
        	}

        	foreach ($saidasAfetadas as $idSaida) {
            	$this->recalcularSaidaPorId((int)$idSaida, $pdo, true);
        	}

        	$pdo->commit();
    	} catch (Exception $e) {
        	$pdo->rollBack();
        	throw $e;
    	}
	}

	private function recalcularEntradaPorId(int $idEntrada, PDO $pdo, bool $reativando = false): void
	{
    	$stmtTotal = $pdo->prepare("
        	SELECT COALESCE(SUM(qtd * valor_unitario), 0) AS total,
               COUNT(*) AS qtd_itens
        	FROM ientrada
        	WHERE id_entrada = :id_entrada
          		AND oculto = false
    	");
    	$stmtTotal->bindValue(':id_entrada', $idEntrada, PDO::PARAM_INT);
    	$stmtTotal->execute();

    	$dados = $stmtTotal->fetch(PDO::FETCH_ASSOC);

    	$total = $dados ? (float)$dados['total'] : 0;
    	$qtdItens = $dados ? (int)$dados['qtd_itens'] : 0;

    	$stmtUpdateTotal = $pdo->prepare("
        	UPDATE entrada
        	SET valor_total = :valor_total
        	WHERE id_entrada = :id_entrada
    	");
    	$stmtUpdateTotal->bindValue(':valor_total', $total);
    	$stmtUpdateTotal->bindValue(':id_entrada', $idEntrada, PDO::PARAM_INT);
    	$stmtUpdateTotal->execute();

    	if ($qtdItens === 0) {
        	$stmtArquivar = $pdo->prepare("
            	UPDATE entrada
            	SET ativo = 0,
                	arquivada_por_produto_inativo = 1
           		WHERE id_entrada = :id_entrada
              		AND ativo = 1
        	");
        	$stmtArquivar->bindValue(':id_entrada', $idEntrada, PDO::PARAM_INT);
        	$stmtArquivar->execute();
        	return;
    	}

    	if ($reativando) {
        	$stmtReativar = $pdo->prepare("
            	UPDATE entrada e
            	INNER JOIN almoxarifado a ON a.id_almoxarifado = e.id_almoxarifado
            	SET e.ativo = 1,
                	e.arquivada_por_produto_inativo = 0
            	WHERE e.id_entrada = :id_entrada
              		AND e.arquivada_por_produto_inativo = 1
              		AND a.ativo = 1
        	");
        	$stmtReativar->bindValue(':id_entrada', $idEntrada, PDO::PARAM_INT);
        	$stmtReativar->execute();
    	}
	}

	private function recalcularSaidaPorId(int $idSaida, PDO $pdo, bool $reativando = false): void
	{
    	$stmtTotal = $pdo->prepare("
        	SELECT COALESCE(SUM(qtd * valor_unitario), 0) AS total,
               COUNT(*) AS qtd_itens
        	FROM isaida
        	WHERE id_saida = :id_saida
          		AND oculto = false
    	");
    	$stmtTotal->bindValue(':id_saida', $idSaida, PDO::PARAM_INT);
    	$stmtTotal->execute();

    	$dados = $stmtTotal->fetch(PDO::FETCH_ASSOC);

    	$total = $dados ? (float)$dados['total'] : 0;
    	$qtdItens = $dados ? (int)$dados['qtd_itens'] : 0;

    	$stmtUpdateTotal = $pdo->prepare("
        	UPDATE saida
        	SET valor_total = :valor_total
        	WHERE id_saida = :id_saida
    	");
    	$stmtUpdateTotal->bindValue(':valor_total', $total);
    	$stmtUpdateTotal->bindValue(':id_saida', $idSaida, PDO::PARAM_INT);
    	$stmtUpdateTotal->execute();

    	if ($qtdItens === 0) {
        	$stmtArquivar = $pdo->prepare("
            	UPDATE saida
            	SET ativo = 0,
                	arquivada_por_produto_inativo = 1
            	WHERE id_saida = :id_saida
              		AND ativo = 1
        	");
        	$stmtArquivar->bindValue(':id_saida', $idSaida, PDO::PARAM_INT);
        	$stmtArquivar->execute();
        	return;
    	}

    	if ($reativando) {
        	$stmtReativar = $pdo->prepare("
            	UPDATE saida s
            	INNER JOIN almoxarifado a ON a.id_almoxarifado = s.id_almoxarifado
            	SET s.ativo = 1,
                	s.arquivada_por_produto_inativo = 0
            	WHERE s.id_saida = :id_saida
              		AND s.arquivada_por_produto_inativo = 1
              		AND a.ativo = 1
        	");
        	$stmtReativar->bindValue(':id_saida', $idSaida, PDO::PARAM_INT);
        	$stmtReativar->execute();
    	}
	}
}
