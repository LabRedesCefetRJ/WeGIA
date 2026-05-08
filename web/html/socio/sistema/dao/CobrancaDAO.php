<?php
    require_once('../../../../dao/Conexao.php');

    class CobrancaDAO{
        public function inserirCobranca(Cobranca $cobranca){
            //print_r($cobranca);
            $sql = 'INSERT INTO cobrancas (descricao, data_pagamento, valor_pago, id_socio) VALUES (:descricao, :dataPagamento, :valorPago, :idSocio)';

            try {
                $pdo = Conexao::connect();
                $stmt = $pdo->prepare($sql);

                $descricao = json_encode($cobranca->getDescricao());
                $stmt->bindParam(':descricao', $descricao);

                $dataPagamento = $cobranca->getDataPagamento();
                $stmt->bindParam(':dataPagamento', $dataPagamento);

                $valorPago = $cobranca->getValorPagamento();
                $stmt->bindParam('valorPago', $valorPago);

                $idSocio = $cobranca->getIdSocio();
                $stmt->bindParam(':idSocio', $idSocio);

                $stmt->execute();

            } catch (PDOException $e) {
                $e->getMessage();
            }
        }
    }
?>