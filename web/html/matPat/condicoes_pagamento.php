<?php
// Compartilhado pelo cadastro e pelo modal de edição de origens.
$prefixoPagamento = $prefixoPagamento ?? '';
$dadosPagamento = $dadosPagamento ?? [];
$condicaoPagamento = is_string($dadosPagamento['condicao_pagamento'] ?? null) ? $dadosPagamento['condicao_pagamento'] : '';
$vencimentosPagamento = is_string($dadosPagamento['vencimentos_pagamento'] ?? null) ? $dadosPagamento['vencimentos_pagamento'] : '';
?>
<details class="condicoes-pagamento" style="margin: 15px 0; border: 1px solid #ddd; border-radius: 4px; padding: 12px;">
    <summary style="cursor: pointer; color: #444; font-weight: 600;">
        Condições de pagamento <small style="font-weight: normal;">(opcional)</small>
        <span data-resumo-pagamento style="display: block; font-weight: normal; color: #666; margin-top: 4px;"></span>
    </summary>
    <div class="row" style="margin-top: 15px;">
        <div class="col-sm-6">
            <div class="form-group" style="margin-left: 0; margin-right: 0;">
                <label for="<?= $prefixoPagamento ?>condicao_pagamento">Condição de pagamento</label>
                <select class="form-control" id="<?= $prefixoPagamento ?>condicao_pagamento" name="condicao_pagamento">
                    <?php foreach (['' => 'Não informado', 'a_vista' => 'À vista', 'a_prazo' => 'A prazo', 'parcelado' => 'Parcelado'] as $valor => $rotulo): ?>
                        <option value="<?= $valor ?>" <?= $condicaoPagamento === $valor ? 'selected' : '' ?>><?= $rotulo ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-sm-6" data-campo-vencimentos hidden>
            <div class="form-group" style="margin-left: 0; margin-right: 0;">
                <label for="<?= $prefixoPagamento ?>vencimentos_pagamento" data-rotulo-vencimentos>Prazo em dias</label>
                <input class="form-control" type="text" id="<?= $prefixoPagamento ?>vencimentos_pagamento" name="vencimentos_pagamento"
                    maxlength="255" value="<?= htmlspecialchars($vencimentosPagamento, ENT_QUOTES, 'UTF-8') ?>"
                    aria-describedby="<?= $prefixoPagamento ?>ajuda-vencimentos" disabled>
                <small id="<?= $prefixoPagamento ?>ajuda-vencimentos" class="help-block" data-ajuda-vencimentos></small>
            </div>
        </div>
    </div>
</details>
