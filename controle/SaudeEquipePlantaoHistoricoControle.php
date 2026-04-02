<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'service' . DIRECTORY_SEPARATOR . 'SaudeEquipePlantaoHistoricoService.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class SaudeEquipePlantaoHistoricoControle
{
    private SaudeEquipePlantaoHistoricoService $service;

    public function __construct(?SaudeEquipePlantaoHistoricoService $service = null)
    {
        $this->service = $service ?? new SaudeEquipePlantaoHistoricoService();
    }

    public function listarHistoricoEscalasMensais(): void
    {
        $dados = $this->dadosRequisicao();

        try {
            $limite = $this->inteiroOpcional($dados, 'limite');
            $historico = $this->service->listarHistoricoEscalasMensais($limite ?? 120);
            $this->jsonSucesso($historico);
        } catch (Throwable $e) {
            Util::tratarException($e);
        }
    }

    private function dadosRequisicao(): array
    {
        $dados = [];

        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $dados = json_decode($json, true) ?: [];
        }

        if (!empty($_REQUEST)) {
            $dados = array_merge($dados, $_REQUEST);
        }

        return $dados;
    }

    private function inteiroOpcional(array $dados, string $campo): ?int
    {
        if (!isset($dados[$campo]) || $dados[$campo] === '' || is_null($dados[$campo])) {
            return null;
        }

        $valor = filter_var($dados[$campo], FILTER_VALIDATE_INT);

        if ($valor === false) {
            return null;
        }

        return (int) $valor;
    }

    private function jsonSucesso($dados, string $mensagem = 'OK'): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'ok',
            'mensagem' => $mensagem,
            'dados' => $dados
        ]);
        exit();
    }
}
