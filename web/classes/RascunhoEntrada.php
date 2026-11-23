<?php

/**
 * Entrada de visita em andamento (registro em etapas da Recepção), guardada na sessão do usuário.
 *
 * Fica salva enquanto a sessão existir: o recepcionista pode recarregar a página, sair para outra tela (por exemplo,
 * cadastrar um visitado ou registrar uma saída) e voltar à etapa em que parou. Só existe uma entrada em andamento
 * por vez; ela termina ao ser registrada ou descartada.
 *
 * Os visitantes continuam na chave 'visitantes_entrada', a mesma usada por VisitanteControle::obterIdsSelecionados.
 */
class RascunhoEntrada
{
    public const ETAPA_VISITANTES = 1;
    public const ETAPA_VISITADOS = 2;
    public const ETAPA_REVISAO = 3;

    public const TAMANHO_MAXIMO_DESCRICAO = 255;

    private const CHAVE_VISITANTES = 'visitantes_entrada';
    private const CHAVE_DADOS = 'rascunho_entrada';

    public static function idsVisitantes(): array
    {
        return self::idsValidos($_SESSION[self::CHAVE_VISITANTES] ?? []);
    }

    public static function idsVisitados(): array
    {
        return self::idsValidos(self::dados()['visitados']);
    }

    public static function descricao(): string
    {
        return self::dados()['descricao'];
    }

    public static function etapa(): int
    {
        return self::dados()['etapa'];
    }

    public static function iniciadoEm(): ?string
    {
        return self::dados()['iniciado_em'];
    }

    public static function vazio(): bool
    {
        return empty(self::idsVisitantes()) && empty(self::idsVisitados()) && self::descricao() === '';
    }

    public static function adicionarVisitante(int $idVisitante): void
    {
        $ids = self::idsVisitantes();
        if (!in_array($idVisitante, $ids, true)) {
            $ids[] = $idVisitante;
        }
        self::definirVisitantes($ids);
    }

    public static function removerVisitante(int $idVisitante): void
    {
        self::definirVisitantes(array_values(array_filter(self::idsVisitantes(), fn($id) => $id !== $idVisitante)));
    }

    public static function definirVisitantes(array $ids): void
    {
        $_SESSION[self::CHAVE_VISITANTES] = self::idsValidos($ids);
        self::tocar();
    }

    public static function definirVisitados(array $ids): void
    {
        self::alterar(['visitados' => self::idsValidos($ids)]);
    }

    public static function adicionarVisitado(int $idVisitado): void
    {
        self::definirVisitados(array_merge(self::idsVisitados(), [$idVisitado]));
    }

    public static function definirDescricao(string $descricao): void
    {
        self::alterar(['descricao' => $descricao]);
    }

    public static function definirEtapa(int $etapa): void
    {
        self::alterar(['etapa' => max(self::ETAPA_VISITANTES, min(self::ETAPA_REVISAO, $etapa))]);
    }

    public static function descartar(): void
    {
        unset($_SESSION[self::CHAVE_VISITANTES], $_SESSION[self::CHAVE_DADOS]);
    }

    private static function dados(): array
    {
        $dados = $_SESSION[self::CHAVE_DADOS] ?? [];
        $dados = is_array($dados) ? $dados : [];

        return [
            'visitados' => is_array($dados['visitados'] ?? null) ? $dados['visitados'] : [],
            'descricao' => is_string($dados['descricao'] ?? null) ? $dados['descricao'] : '',
            'etapa' => (int) ($dados['etapa'] ?? self::ETAPA_VISITANTES) ?: self::ETAPA_VISITANTES,
            'iniciado_em' => is_string($dados['iniciado_em'] ?? null) ? $dados['iniciado_em'] : null,
        ];
    }

    private static function alterar(array $campos): void
    {
        $_SESSION[self::CHAVE_DADOS] = array_merge(self::dados(), $campos);
        self::tocar();
    }

    /** Marca o início da entrada na primeira alteração, para a página mostrar desde quando ela está em andamento. */
    private static function tocar(): void
    {
        $dados = self::dados();
        if ($dados['iniciado_em'] === null) {
            $dados['iniciado_em'] = date('Y-m-d H:i:s');
            $_SESSION[self::CHAVE_DADOS] = $dados;
        }
    }

    private static function idsValidos($ids): array
    {
        if (!is_array($ids)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $ids), fn($id) => $id > 0)));
    }
}
