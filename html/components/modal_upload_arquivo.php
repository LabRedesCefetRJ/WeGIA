<?php
if (!isset($modalUploadConfig) || !is_array($modalUploadConfig)) {
    throw new InvalidArgumentException('A configuração do modal de upload não foi informada.');
}

$button = $modalUploadConfig['button'] ?? [];
$modal = $modalUploadConfig['modal'] ?? [];
$form = $modalUploadConfig['form'] ?? [];
$select = $modalUploadConfig['select'] ?? [];
$file = $modalUploadConfig['file'] ?? [];

$buttonLabel = $button['label'] ?? 'Adicionar';
$buttonOnClick = $button['onclick'] ?? '';

$modalId = $modal['id'] ?? 'docFormModal';
$modalLabelId = $modal['label_id'] ?? ($modalId . 'Label');
$modalTitle = $modal['title'] ?? 'Adicionar arquivo';

$formId = $form['id'] ?? 'arquivoUploadForm';
$formAction = $form['action'] ?? '';
$formMethod = strtolower($form['method'] ?? 'post');
$formEnctype = $form['enctype'] ?? 'multipart/form-data';
$formOnSubmit = $form['onsubmit'] ?? '';
$hiddenFields = $form['hidden_fields'] ?? [];

$selectId = $select['id'] ?? 'tipoDocumento';
$selectName = $select['name'] ?? 'tipo_documento';
$selectLabel = $select['label'] ?? 'Tipo de arquivo';
$selectPlaceholder = $select['placeholder'] ?? 'Selecionar';
$selectOptions = $select['options'] ?? [];
$selectValueKey = $select['value_key'] ?? 'id';
$selectLabelKey = $select['label_key'] ?? 'descricao';
$selectRequired = array_key_exists('required', $select) ? (bool)$select['required'] : true;
$selectAddOnClick = $select['add_button_onclick'] ?? '';
$selectAddTitle = $select['add_button_title'] ?? 'Adicionar tipo';

$fileId = $file['id'] ?? 'arquivoDocumento';
$fileName = $file['name'] ?? 'arquivo';
$fileLabel = $file['label'] ?? 'Arquivo';
$fileAccept = $file['accept'] ?? '.png,.jpeg,.jpg,.pdf,.docx,.doc,.odp';
$fileHelp = $file['help'] ?? 'Formatos aceitos: PNG, JPG, PDF, DOC, DOCX e ODP.';
$fileRequired = array_key_exists('required', $file) ? (bool)$file['required'] : true;
$fileMaxSizeBytes = isset($file['max_size_bytes']) ? (int)$file['max_size_bytes'] : 0;
?>

<button
    type="button"
    class="btn btn-primary"
    data-toggle="modal"
    data-target="#<?= htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8') ?>"
    <?= $buttonOnClick ? 'onclick="' . htmlspecialchars($buttonOnClick, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
    <?= htmlspecialchars($buttonLabel, ENT_QUOTES, 'UTF-8') ?>
</button>

<div class="modal fade upload-modal" id="<?= htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8') ?>" tabindex="-1" role="dialog" aria-labelledby="<?= htmlspecialchars($modalLabelId, ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="<?= htmlspecialchars($modalLabelId, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($modalTitle, ENT_QUOTES, 'UTF-8') ?>
                </h4>
            </div>

            <form
                id="<?= htmlspecialchars($formId, ENT_QUOTES, 'UTF-8') ?>"
                action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>"
                method="<?= htmlspecialchars($formMethod, ENT_QUOTES, 'UTF-8') ?>"
                enctype="<?= htmlspecialchars($formEnctype, ENT_QUOTES, 'UTF-8') ?>"
                <?= $formOnSubmit ? 'onsubmit="' . htmlspecialchars($formOnSubmit, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                <?php foreach ($hiddenFields as $name => $value): ?>
                    <?php
                    $hiddenValue = $value;
                    $hiddenId = '';
                    if (is_array($value)) {
                        $hiddenValue = $value['value'] ?? '';
                        $hiddenId = $value['id'] ?? '';
                    }
                    ?>
                    <input
                        type="hidden"
                        name="<?= htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8') ?>"
                        value="<?= htmlspecialchars((string)$hiddenValue, ENT_QUOTES, 'UTF-8') ?>"
                        <?= $hiddenId !== '' ? 'id="' . htmlspecialchars((string)$hiddenId, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                <?php endforeach; ?>

                <div class="modal-body">
                    <div
                        id="atendidoDocFormError"
                        class="alert alert-danger alert-dismissible fade"
                        style="display: none;"
                        role="alert">
                        <button type="button" class="close" aria-label="Fechar" onclick="limparErroModalDocumento(); return false;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <span id="atendidoDocFormErrorText"></span>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="<?= htmlspecialchars($selectId, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($selectLabel, ENT_QUOTES, 'UTF-8') ?><?php if ($selectRequired): ?> <sup class="obrig">*</sup><?php endif; ?>
                        </label>
                        <div class="input-group">
                            <select
                                class="form-control"
                                name="<?= htmlspecialchars($selectName, ENT_QUOTES, 'UTF-8') ?>"
                                id="<?= htmlspecialchars($selectId, ENT_QUOTES, 'UTF-8') ?>"
                                <?= $selectRequired ? 'required' : '' ?>>
                                <option value="" selected disabled><?= htmlspecialchars($selectPlaceholder, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php foreach ($selectOptions as $option): ?>
                                    <?php
                                    $optionValue = $option[$selectValueKey] ?? '';
                                    $optionLabel = $option[$selectLabelKey] ?? '';
                                    ?>
                                    <option value="<?= htmlspecialchars((string)$optionValue, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars((string)$optionLabel, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <?php if ($selectAddOnClick): ?>
                                <span class="input-group-btn">
                                    <button
                                        type="button"
                                        class="btn btn-default"
                                        onclick="<?= htmlspecialchars($selectAddOnClick, ENT_QUOTES, 'UTF-8') ?>"
                                        title="<?= htmlspecialchars($selectAddTitle, ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="fa fa-plus text-primary" aria-hidden="true"></i>
                                    </button>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="<?= htmlspecialchars($fileId, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($fileLabel, ENT_QUOTES, 'UTF-8') ?><?php if ($fileRequired): ?> <sup class="obrig">*</sup><?php endif; ?>
                        </label>
                        <input
                            name="<?= htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') ?>"
                            type="file"
                            class="form-control"
                            id="<?= htmlspecialchars($fileId, ENT_QUOTES, 'UTF-8') ?>"
                            accept="<?= htmlspecialchars($fileAccept, ENT_QUOTES, 'UTF-8') ?>"
                            <?= $fileMaxSizeBytes > 0 ? 'data-max-size-bytes="' . htmlspecialchars((string)$fileMaxSizeBytes, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
                            <?= $fileRequired ? 'required' : '' ?>>
                        <p class="help-block">
                            <span class="text-danger">Formatos aceitos:</span>
                            <?= htmlspecialchars($fileHelp, ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="glyphicon glyphicon-upload" aria-hidden="true"></span>
                        Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
