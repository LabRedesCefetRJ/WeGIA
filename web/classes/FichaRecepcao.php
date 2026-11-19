<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Visitado.php';

/**
 * Monta as fichas exibidas nos modais do histórico de visitas do módulo Recepção.
 *
 * A ficha sai do servidor já com os dados sensíveis mascarados (CPF e RG), para que o valor completo
 * nunca chegue ao navegador. Os valores não são escapados: quem exibe é responsável por isso.
 *
 * Formato: ['titulo' => string, 'nome' => string, 'foto' => ?string (base64), 'secoes' => [
 *     ['titulo' => string, 'campos' => [['rotulo' => string, 'valor' => ?string, 'link' => ?string], ...]], ...]]
 * Um campo com valor null é exibido como "Não informado".
 */
class FichaRecepcao
{
    public static function visitante(array $pessoa): array
    {
        $telefone = self::texto($pessoa['telefone'] ?? null);
        $email = self::texto($pessoa['email'] ?? null);
        $emailValido = $email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;

        return [
            'titulo' => 'Visitante',
            'nome' => self::nomeCompleto($pessoa),
            'foto' => self::texto($pessoa['imagem'] ?? null),
            'secoes' => [
                [
                    'titulo' => 'Dados pessoais',
                    'campos' => [
                        self::campo('Nome completo', self::nomeCompleto($pessoa)),
                        self::campo('Sexo', self::rotuloSexo($pessoa['sexo'] ?? null)),
                        self::campo('Data de nascimento', self::formatarData($pessoa['data_nascimento'] ?? null)),
                    ],
                ],
                [
                    'titulo' => 'Documentos',
                    'campos' => [
                        self::campo('CPF', Visitado::mascararCpf($pessoa['cpf'] ?? null)),
                        self::campo('RG', Visitado::mascararRg($pessoa['registro_geral'] ?? null)),
                    ],
                ],
                [
                    'titulo' => 'Contato',
                    'campos' => [
                        self::campo('Telefone', $telefone, self::linkWhatsapp($telefone)),
                        self::campo('E-mail', $email, $emailValido ? 'mailto:' . $email : null),
                    ],
                ],
                [
                    'titulo' => 'Endereço',
                    'campos' => [
                        self::campo('Logradouro', self::texto($pessoa['logradouro'] ?? null)),
                        self::campo('Número', self::texto($pessoa['numero_endereco'] ?? null)),
                        self::campo('Complemento', self::texto($pessoa['complemento'] ?? null)),
                        self::campo('Bairro', self::texto($pessoa['bairro'] ?? null)),
                        self::campo('Cidade', self::cidadeUf($pessoa['cidade'] ?? null, $pessoa['estado'] ?? null)),
                        self::campo('CEP', self::texto($pessoa['cep'] ?? null)),
                    ],
                ],
            ],
        ];
    }

    /**
     * Atendido: só o necessário para identificar a pessoa na recepção (sem endereço, contato ou dados de saúde).
     */
    public static function atendido(array $pessoa): array
    {
        $idade = self::idade($pessoa['data_nascimento'] ?? null);

        return [
            'titulo' => 'Atendido',
            'nome' => self::nomeCompleto($pessoa),
            'foto' => self::texto($pessoa['imagem'] ?? null),
            'secoes' => [[
                'titulo' => 'Dados do atendido',
                'campos' => [
                    self::campo('Nome completo', self::nomeCompleto($pessoa)),
                    self::campo('Idade', $idade === null ? null : $idade . ($idade === 1 ? ' ano' : ' anos')),
                    self::campo('CPF', Visitado::mascararCpf($pessoa['cpf'] ?? null)),
                    self::campo('Tipo', self::texto($pessoa['atendido_tipo'] ?? null)),
                    self::campo('Situação', self::texto($pessoa['atendido_status'] ?? null)),
                ],
            ]],
        ];
    }

    /**
     * Funcionário ou voluntário visitado.
     */
    public static function colaborador(array $pessoa, string $titulo): array
    {
        return [
            'titulo' => $titulo,
            'nome' => self::nomeCompleto($pessoa),
            'foto' => self::texto($pessoa['imagem'] ?? null),
            'secoes' => [[
                'titulo' => 'Dados do ' . mb_strtolower($titulo, 'UTF-8'),
                'campos' => [
                    self::campo('Nome completo', self::nomeCompleto($pessoa)),
                    self::campo('CPF', Visitado::mascararCpf($pessoa['cpf'] ?? null)),
                    self::campo('Cargo', self::texto($pessoa['cargo'] ?? null)),
                    self::campo('Situação', self::texto($pessoa['situacao'] ?? null)),
                ],
            ]],
        ];
    }

    /**
     * Pessoa visitada que não tem mais vínculo de atendido, funcionário ou voluntário.
     */
    public static function pessoa(array $pessoa): array
    {
        return [
            'titulo' => 'Pessoa',
            'nome' => self::nomeCompleto($pessoa),
            'foto' => self::texto($pessoa['imagem'] ?? null),
            'secoes' => [[
                'titulo' => 'Dados da pessoa',
                'campos' => [
                    self::campo('Nome completo', self::nomeCompleto($pessoa)),
                    self::campo('CPF', Visitado::mascararCpf($pessoa['cpf'] ?? null)),
                ],
            ]],
        ];
    }

    public static function pet(array $pet): array
    {
        $sexo = strtoupper(trim((string) ($pet['sexo'] ?? '')));

        return [
            'titulo' => 'Pet',
            'nome' => (string) ($pet['nome'] ?? ''),
            'foto' => self::texto($pet['imagem'] ?? null),
            'secoes' => [[
                'titulo' => 'Dados do pet',
                'campos' => [
                    self::campo('Nome', self::texto($pet['nome'] ?? null)),
                    self::campo('Espécie', self::texto($pet['especie'] ?? null)),
                    self::campo('Raça', self::texto($pet['raca'] ?? null)),
                    self::campo('Cor', self::texto($pet['cor'] ?? null)),
                    self::campo('Sexo', $sexo === 'M' ? 'Macho' : ($sexo === 'F' ? 'Fêmea' : null)),
                ],
            ]],
        ];
    }

    public static function nomeCompleto(array $pessoa): string
    {
        return trim(($pessoa['nome'] ?? '') . ' ' . ($pessoa['sobrenome'] ?? ''));
    }

    public static function formatarData(?string $data): ?string
    {
        $data = self::texto($data);
        if ($data === null) {
            return null;
        }

        $objeto = DateTime::createFromFormat('!Y-m-d', substr($data, 0, 10));

        return $objeto ? $objeto->format('d/m/Y') : null;
    }

    public static function idade(?string $dataNascimento, ?DateTimeInterface $referencia = null): ?int
    {
        $dataNascimento = self::texto($dataNascimento);
        if ($dataNascimento === null) {
            return null;
        }

        $nascimento = DateTimeImmutable::createFromFormat('!Y-m-d', substr($dataNascimento, 0, 10));
        if (!$nascimento) {
            return null;
        }

        $referencia = $referencia ?? new DateTimeImmutable('today');
        if ($nascimento > $referencia) {
            return null;
        }

        return $nascimento->diff($referencia)->y;
    }

    /**
     * Link do WhatsApp para um telefone brasileiro (10 ou 11 dígitos, com ou sem o 55 na frente).
     */
    public static function linkWhatsapp(?string $telefone): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $telefone);

        if (strlen($digitos) === 10 || strlen($digitos) === 11) {
            $digitos = '55' . $digitos;
        } elseif (!((strlen($digitos) === 12 || strlen($digitos) === 13) && strpos($digitos, '55') === 0)) {
            return null;
        }

        return 'https://wa.me/' . $digitos;
    }

    private static function rotuloSexo(?string $sexo): ?string
    {
        switch (strtolower(trim((string) $sexo))) {
            case 'm':
                return 'Masculino';
            case 'f':
                return 'Feminino';
            case 'o':
                return 'Outro';
            default:
                return null;
        }
    }

    private static function cidadeUf(?string $cidade, ?string $uf): ?string
    {
        $partes = array_filter([self::texto($cidade), self::texto($uf)]);

        return $partes ? implode(' - ', $partes) : null;
    }

    private static function campo(string $rotulo, ?string $valor, ?string $link = null): array
    {
        return ['rotulo' => $rotulo, 'valor' => $valor, 'link' => $valor === null ? null : $link];
    }

    private static function texto($valor): ?string
    {
        $valor = trim((string) $valor);

        return $valor === '' ? null : $valor;
    }
}
