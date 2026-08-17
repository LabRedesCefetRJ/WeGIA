USE `wegia`;

-- =====================================================
-- Gateway de assinaturas (Preapproval) do Mercado Pago — Recorrência
--
-- Instalações novas já recebem esta linha via BD/wegia002.sql. Este script é
-- para bancos de dados de instalações já em produção: rode manualmente para
-- habilitar a plataforma MercadoPago no meio de pagamento "Recorrência"
-- (Contribuição > Meios de Pagamento). Idempotente — seguro rodar mais de uma vez.
--
-- Data: 2026-08-17
-- =====================================================

INSERT INTO `contribuicao_gatewayPagamento` (plataforma, endPoint, token, status)
SELECT 'MercadoPago', 'https://api.mercadopago.com/preapproval', 'coloque o token aqui', 0
WHERE NOT EXISTS (
    SELECT 1 FROM `contribuicao_gatewayPagamento`
    WHERE plataforma = 'MercadoPago' AND endPoint = 'https://api.mercadopago.com/preapproval'
);
