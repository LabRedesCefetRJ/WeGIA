<fieldset id="dia-vencimento" class="wrap-input100 validate-input bg1 contribution-field">
    <legend class="form-label"><i class="fa fa-calendar" aria-hidden="true"></i> Escolha o dia de vencimento <span class="obrigatorio">*</span></legend>
    <div class="d-flex flex-wrap gap-2">
        <?php foreach ([1, 5, 10, 15, 20, 25] as $dia): ?>
            <div class="form-check form-check-inline contribution-day-choice">
                <input class="form-check-input" type="radio" value="<?= $dia ?>" name="dia" id="dia<?= $dia ?>">
                <label class="form-check-label" for="dia<?= $dia ?>"><?= $dia ?></label>
            </div>
        <?php endforeach; ?>
    </div>
</fieldset>