<?php
    session_start();
    if (!isset($_SESSION['usuario'])){
        header("Location: ../../index.php");
	}

	// Diz ao programa de correção de estoque se deve ou não mostrar os avisos
	define("AVISO", true);
	define("DEBUG", false);
	
	// Verifica Permissão do Usuário
	require_once '../permissao/permissao.php';
	permissao($_SESSION['id_pessoa'], 9);

	require_once '../../dao/Conexao.php';
	
	function last_key($array){
		return array_search(end($array), $array);
	}

	function repara_estoque(){
		// Essa função não cobre entradas/saídas que não tenham produtos em estoque
		$pdo = Conexao::connect();
		$result = "success";
		$log = '';
		$changed = 0;
		$added = 0;
		$warns = 0;
		$updates = 0;
		try{
			$entrada = $pdo->query("SELECT ie.id_produto as id, ie.qtd, e.id_almoxarifado as almox from ientrada ie inner join entrada e on e.id_entrada = ie.id_entrada")->fetchAll(PDO::FETCH_ASSOC);
			$saida = $pdo->query("SELECT isa.id_produto as id, isa.qtd, sa.id_almoxarifado as almox from isaida isa inner join saida sa on sa.id_saida = isa.id_saida")->fetchAll(PDO::FETCH_ASSOC);
			foreach ($entrada as $item){
				extract($item);
				$prod = $pdo->query("SELECT * from estoque WHERE id_produto=$id AND id_almoxarifado=$almox;")->fetch(PDO::FETCH_ASSOC);
				if (!$prod){
					// Cria um registro de estoque caso não tenha
					$pdo->exec("INSERT INTO estoque (id_produto, id_almoxarifado, qtd) VALUES ($id , $almox , 0)");
					$desc = $pdo->query("SELECT descricao, codigo, oculto FROM produto WHERE id_produto=$id;")->fetch(PDO::FETCH_ASSOC);
					$almoxarifado = $pdo->query("SELECT descricao_almoxarifado as descr FROM almoxarifado WHERE id_almoxarifado=$almox;")->fetch(PDO::FETCH_ASSOC);
					extract($desc);
					$log .= "Registro de estoque criado para $descricao | $codigo ".($oculto ? "[Oculto] " : "")."no almoxarifado ".$almoxarifado["descr"]."\n";
					$added++;
				}
			}
			foreach ($saida as $item){
				extract($item);
				$prod = $pdo->query("SELECT * from estoque WHERE id_produto=$id AND id_almoxarifado=$almox;")->fetch(PDO::FETCH_ASSOC);
				if (!$prod){
					// Cria um registro de estoque caso não tenha
					$pdo->exec("INSERT INTO estoque (id_produto, id_almoxarifado, qtd) VALUES ($id , $almox , 0)");
					$desc = $pdo->query("SELECT descricao, codigo, oculto FROM produto WHERE id_produto=$id;")->fetch(PDO::FETCH_ASSOC);
					$almoxarifado = $pdo->query("SELECT descricao_almoxarifado as descr FROM almoxarifado WHERE id_almoxarifado=$almox;")->fetch(PDO::FETCH_ASSOC);
					extract($desc);
					$log .= "Registro de estoque criado para $descricao | $codigo ".($oculto ? "[Oculto]" : "")." no almoxarifado ".$almoxarifado["descr"]."\n";
					$added++;
				}
			}
			$estoque = $pdo->query("SELECT * FROM estoque;")->fetchAll(PDO::FETCH_ASSOC);
			foreach ($estoque as $key => $item){
				// Percorre cada registro no estoque
				extract($item);
				$entrada = $pdo->query("SELECT ientrada.qtd from ientrada inner join entrada on entrada.id_entrada = ientrada.id_entrada where ientrada.id_produto=$id_produto and entrada.id_almoxarifado=$id_almoxarifado")->fetchAll(PDO::FETCH_ASSOC);
				$saida = $pdo->query("SELECT isaida.qtd from isaida inner join saida on saida.id_saida = isaida.id_saida where isaida.id_produto=$id_produto and saida.id_almoxarifado=$id_almoxarifado")->fetchAll(PDO::FETCH_ASSOC);
				$qtd_total = 0;
				foreach ($entrada as $item){
					// Adiciona o total de entradas
					$qtd_total += $item['qtd'];
				}
				foreach ($saida as $item){
					// Subtrai o total de saídas
					$qtd_total -= $item['qtd'];
				}
				if (DEBUG)
					echo("ID: $id_produto ALMOX: $id_almoxarifado QTD: $qtd => $qtd_total<br/>");
				if ($qtd != $qtd_total){
					// Se a quantidade em estoque for diferente de entrada - saída
					$desc = $pdo->query("SELECT descricao, codigo, oculto FROM produto WHERE id_produto=".$id_produto)->fetch(PDO::FETCH_ASSOC);
					extract($desc);
					$pdo->exec("UPDATE estoque SET qtd=$qtd_total WHERE id_produto=$id_produto AND id_almoxarifado=$id_almoxarifado;");
					$changed++;
					$log .= "Estoque de $descricao | $codigo ".($oculto ? "[Oculto] " : "" )." alterado de $qtd para $qtd_total\n";
					continue;
				}
				if ($qtd_total < 0 && AVISO){
					// Se houverem registros em estoque com quantidade negativa
					$desc = $pdo->query("SELECT descricao, codigo, oculto FROM produto WHERE id_produto=".$id_produto)->fetch(PDO::FETCH_ASSOC);
					extract($desc);
					$log .= "AVISO: $descricao | $codigo ".($oculto ? "[Oculto] " : "" )." possui estoque negativo ($qtd_total)\n";
					$warns++;
				}
			}


			// Cabeçalho do log
			$logHeader = "";
			if ($changed){
				$s= $changed > 1 ? "s":'';
				$logHeader .= "$changed Linha$s Corrigida$s\n";
			}
			if ($added){
				$s= $added > 1 ? "s":'';
				$logHeader .= "$added Linha$s Adicionada$s\n";
			}
			if ($warns && AVISO){
				$s= $warns > 1 ? "s":'';
				$logHeader .= "$warns Aviso$s\n";
				$result = "warning";
			}
			if ($updates){
				$s= $updates > 1 ? "s":'';
				$logHeader .= "$updates Linha$s Atualizada$s\n";
			}
			if ($logHeader){
				$logHeader .= "\n";
			}
			$log = $logHeader . (($changed + $added + $updates) == 0 ? "Não houveram alterações no Banco de Dados\n" : "") . $log;
		}catch (Exception $e){
			$result = "error";
			$log = "Erro: \n\n{$e->getMessage()}";
		}
		return [$result, $log];
	}

	function corrige_estoque(){
		$somaEntrada = [];
		$somaSaida = [];
		$somaTotal = [];
		$result = "success";
		$log = '';
		$pdo = Conexao::connect();
		try{
			// Quantidade de itens na entrada
			$ientrada = $pdo->query("
			SELECT ie.id_produto, SUM(ie.qtd) as qtd, e.id_almoxarifado 
			FROM ientrada ie 
			INNER JOIN entrada e ON e.id_entrada = ie.id_entrada 
			GROUP BY CONCAT(ie.id_produto, e.id_almoxarifado) 
			ORDER BY ie.id_produto;
			")->fetchAll(PDO::FETCH_ASSOC);
			foreach ($ientrada as $key => $item){
				extract($item);
				if (isset($somaEntrada[$id_produto][$id_almoxarifado])){
					$somaEntrada[$id_produto][$id_almoxarifado] += floatval($qtd);
				}else{
					$somaEntrada[$id_produto][$id_almoxarifado] = floatval($qtd);
				}
			}
			// Quantidade de itens na saida
			$isaida = $pdo->query("
			SELECT isa.id_produto, SUM(isa.qtd) as qtd, s.id_almoxarifado 
			FROM isaida isa 
			INNER JOIN saida s ON s.id_saida = isa.id_saida 
			GROUP BY CONCAT(isa.id_produto, s.id_almoxarifado) 
			ORDER BY isa.id_produto;
			")->fetchAll(PDO::FETCH_ASSOC);
			foreach ($isaida as $key => $item){
				extract($item);
				if (isset($somaSaida[$id_produto][$id_almoxarifado])){
					$somaSaida[$id_produto][$id_almoxarifado] += floatval($qtd);
				}else{
					$somaSaida[$id_produto][$id_almoxarifado] = floatval($qtd);
				}
			}
			// Guarda a quantidade entrada - quantidade saida
			$cont = (last_key($somaEntrada) >= last_key($somaSaida) ? last_key($somaEntrada) : last_key($somaSaida));
			for ($id = 0; $id <= $cont; $id++){
				
				if (isset($somaEntrada[$id]) || isset($somaSaida[$id])){
					foreach ($somaEntrada[$id] as $id_almox => $qtd){
						$qtdEntrada = (isset($somaEntrada[$id][$id_almox]) ? $somaEntrada[$id][$id_almox] : 0);
						$qtdSaida = (isset($somaSaida[$id][$id_almox]) ? $somaSaida[$id][$id_almox] : 0);
						if (isset($somaTotal[$id][$id_almox])){
							$somaTotal[$id][$id_almox] += $qtdEntrada - $qtdSaida;
						}else{
							$somaTotal[$id][$id_almox] = $qtdEntrada - $qtdSaida;
						}
					}
				}
			}

			$changed = 0;
			$added = 0;
			$warns = 0;
			$updates = 0;
			foreach ($somaTotal as $id => $val){
				// Percorre as linhas
				foreach ($val as $almox => $qtd){
					// Percorre as colunas

					$estoque = $pdo->query("SELECT qtd FROM estoque WHERE id_produto=$id AND id_almoxarifado=$almox")->fetch(PDO::FETCH_ASSOC);
					$desc = $pdo->query("SELECT descricao, codigo, oculto FROM produto WHERE id_produto=$id;")->fetch(PDO::FETCH_ASSOC);
					$almoxarifado = $pdo->query("SELECT descricao_almoxarifado FROM almoxarifado WHERE id_almoxarifado=$almox;")->fetch(PDO::FETCH_ASSOC);

					if ($desc && $almoxarifado){
						// Caso o produto e o almoxarifado estejam cadastrados
						extract($desc);
						extract($almoxarifado);
						echo("ID: $id ALMOXARIFADO: $almox | ".(isset($estoque['qtd']) ? $estoque['qtd'] : 0)." => $qtd (".(isset($somaEntrada[$id][$almox]) ? $somaEntrada[$id][$almox] : 0 )." - ".(isset($somaSaida[$id][$almox]) ? $somaSaida[$id][$almox] : 0 ).")<br/>");
						if ($estoque){
							// Caso o produto esteja em estoque
							$qtd_atual = $estoque['qtd'];
							if ($qtd_atual != $qtd){
								// Se a quantidade em estoque não bater com a entrada e a saida
								$dif = $qtd - $qtd_atual;
								$changed += $pdo->exec("UPDATE estoque SET qtd=$qtd WHERE id_produto=$id AND id_almoxarifado=$almox;");
								$log .= abs($dif)." itens ($descricao | $codigo ".($oculto ? "[Oculto]" : "" ).") ".($dif > 0 ? "adicionados no" : "retirados do")." almoxarifado $descricao_almoxarifado.\n";
							}else{
								// Caso a quantidade dos registros existentes esteja certa
								$pdo->exec("UPDATE estoque SET qtd=$qtd WHERE id_produto=$id AND id_almoxarifado=$almox;");
								$updates++;
								if ($qtd < 0 && AVISO){
									// Caso a quantidade dos registros existentes esteja certa e seja negativa
									$warns++;
									$log .= "AVISO: $descricao | $codigo ".($oculto ? "[Oculto]" : "" )." possui estoque negativo no almoxarifado $descricao_almoxarifado\n";
								}
							}
						}else{
							// Caso o produto não esteja em estoque
							$added += $pdo->exec("INSERT INTO estoque (id_produto, id_almoxarifado, qtd) VALUES ( $id , $almox , $qtd );");
							if ($qtd < 0){
								// Caso a quantidade seja negativa
								$log .= "Registro criado: $descricao | $codigo ".($oculto ? "[Oculto]" : "" )." possui ".$somaSaida[$id][$almox]." saídas e ".$somaEntrada[$id][$almox]." entradas. O estoque está negativo ($qtd).\n";
								$warns++;
							}else{
								$log .= "Registro criado: $descricao | $codigo ".($oculto ? "[Oculto]" : "" ).". $qtd unidades adicionadas no almoxarifado $descricao_almoxarifado.\n";
							}
						}
					}else{
						// Caso o produto e o almoxarifado não estejam cadastrados
						$warns ++;
						
						if (AVISO){
							$log .= "AVISO: Existem $qtd itens (".$somaEntrada[$id][$almox]." entradas, ".$somaSaida[$id][$almox]." saidas)".(!$desc ? " associados a um produto não cadastrado de ID $id" : '').(!$almoxarifado ? " armazenados em um almoxarifado não cadastrado de ID $almox" : '')."\n";
						}
					}
				}
			}
			$estoque = $pdo->query("SELECT * FROM estoque;")->fetchAll(PDO::FETCH_ASSOC);
			foreach ($estoque as $key => $item){
				extract($item);
				$entrada = $pdo->query("SELECT ientrada.qtd from ientrada inner join entrada on entrada.id_entrada = ientrada.id_entrada where ientrada.id_produto=$id_produto and entrada.id_almoxarifado=$id_almoxarifado")->fetchAll(PDO::FETCH_ASSOC);
				$saida = $pdo->query("SELECT isaida.qtd from isaida inner join saida on saida.id_saida = isaida.id_saida where isaida.id_produto=$id_produto and saida.id_almoxarifado=$id_almoxarifado")->fetchAll(PDO::FETCH_ASSOC);
				$qtd_total = 0;
				foreach ($entrada as $item){
					$qtd_total += $item['qtd'];
				}
				foreach ($saida as $item){
					$qtd_total -= $item['qtd'];
				}
				echo("ID: $id_produto ALMOX: $id_almoxarifado QTD: $qtd => $qtd_total<br/>");
				// Para cada item em estoque
				continue;
				if (!isset($somaEntrada[$item['id_produto']][$item['id_almoxarifado']]) && !isset($somaEntrada[$item['id_produto']][$item['id_almoxarifado']]) && $item['qtd'] != 0){
					// Verifica se há registros de entrada nem saida para o produto e zera caso não esteja
					$desc = $pdo->query("SELECT descricao, codigo, oculto FROM produto WHERE id_produto=".$item['id_produto'])->fetch(PDO::FETCH_ASSOC);
					extract($desc);
					if (AVISO){
						$log .= "AVISO: ".$item['id_produto']." | $descricao | $codigo ".($oculto ? "[Oculto] " : "" )."continha ".$item['qtd']." itens sem registro de entrada e saida e seu estoque foi zerado.\n";
					}
					$warns++;
					$pdo->exec("UPDATE estoque SET qtd=0 WHERE id_produto=".$item['id_produto']." AND id_almoxarifado=".$item['id_almoxarifado']);
					$changed++;
				}
			}
			$logHeader = "";
			if ($changed){
				$logHeader .= "$changed Linhas Corrigidas\n";
			}
			if ($added){
				$logHeader .= "$added Linhas Adicionadas\n";
			}
			if ($warns && AVISO){
				$logHeader .= "$warns Avisos\n";
				$result = "warning";
			}
			if ($updates){
				$logHeader .= "$updates Linhas Atualizadas\n";
			}
			if ($logHeader){
				$logHeader .= "\n";
			}
			$log = $logHeader . (($changed + $added + $updates) == 0 ? "Não houveram alterações no Banco de Dados\n" : "") . $log;
		}catch (Exception $e){
			$result = "error";
			$log = "Erro: \n{$e->getMessage()}";
		}
		
		return [$result, $log];
	}

	function success(){
		header("Location: ./configuracao_geral.php?tipo=success&mensagem=Reparo realizado com sucesso!");
	}
	
	function warning(){
		header("Location: ./configuracao_geral.php?tipo=warning&mensagem=Reparo realizado com sucesso! Aviso:");
	}
	
	function error(){
		header("Location: ./configuracao_geral.php?tipo=error&mensagem=Houve um erro ao executar o reparo:");
	}
	
	$result = repara_estoque();
	$log = $result[1];
	$_SESSION['log']=$log;


	// Debug
	if (DEBUG){
		var_dump($result);
		die();
	}

	switch ($result[0]){
		case "warning":
			warning();
		break;
		case "success":
			success();
		break;
		case "error":
		default:
			error();
	}
?>