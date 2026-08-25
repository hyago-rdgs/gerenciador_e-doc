<?php if (!$campos_metadados): ?>
    <div class="alert alert-light border mb-0">Este tipo de documento não possui metadados visíveis.</div>
<?php endif; ?>

<?php foreach ($campos_metadados as $campo): ?>
    <?php
    $codigo = (int) $campo['metadado_codigo'];
    $id = 'metadado_' . $codigo;
    $obrigatorio = (int) $campo['obrigatorio'] === 1;
    $opcoes = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($campo['opcoes'] ?? ''))), 'strlen'));
    $valor = $campo['valor'] ?? '';

    $valores = json_decode(
        (string) $valor,
        TRUE
    );

    if (!is_array($valores)) {
        $valores = array_values(
            array_filter(
                array_map(
                    'trim',
                    explode(',', (string) $valor)
                ),
                'strlen'
            )
        );
    }
    ?>
    <div class="col-12 <?= $campo['tipo_campo'] === 'textarea' ? '' : 'col-lg-6'; ?>">
        <label class="form-label" for="<?= $id; ?>">
            <?= htmlspecialchars($campo['nome'], ENT_QUOTES, 'UTF-8'); ?>
            <?php if ($obrigatorio): ?><span class="text-danger" aria-label="obrigatório">*</span><?php endif; ?>
        </label>

        <?php if ($campo['tipo_campo'] === 'textarea'): ?>
            <textarea class="form-control" id="<?= $id; ?>" name="metadados[<?= $codigo; ?>]" rows="3" <?= $obrigatorio ? 'required' : ''; ?>><?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); ?></textarea>
        <?php elseif ($campo['tipo_campo'] === 'select'): ?>
            <select class="form-select" id="<?= $id; ?>" name="metadados[<?= $codigo; ?>]" <?= $obrigatorio ? 'required' : ''; ?>>
                <option value="">Selecione</option>
                <?php foreach ($opcoes as $opcao): ?>
                    <option value="<?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8'); ?>" <?= $valor === $opcao ? 'selected' : ''; ?>><?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>
        <?php elseif ($campo['tipo_campo'] === 'radio'): ?>
            <div id="<?= $id; ?>" class="d-flex flex-wrap gap-3 pt-2">
                <?php foreach ($opcoes as $indice => $opcao): ?>
                    <div class="form-check">
                        <input class="form-check-input" id="<?= $id . '_' . $indice; ?>" name="metadados[<?= $codigo; ?>]" type="radio" value="<?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8'); ?>" <?= $valor === $opcao ? 'checked' : ''; ?> <?= $obrigatorio ? 'required' : ''; ?>>
                        <label class="form-check-label" for="<?= $id . '_' . $indice; ?>"><?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8'); ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($campo['tipo_campo'] === 'checkbox' && $opcoes): ?>
            <div id="<?= $id; ?>" class="d-flex flex-wrap gap-3 pt-2">
                <?php foreach ($opcoes as $indice => $opcao): ?>
                    <div class="form-check">
                        <input class="form-check-input" id="<?= $id . '_' . $indice; ?>" name="metadados[<?= $codigo; ?>][]" type="checkbox" value="<?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8'); ?>" <?= in_array($opcao, $valores, TRUE) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="<?= $id . '_' . $indice; ?>"><?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8'); ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($campo['tipo_campo'] === 'checkbox'): ?>
            <input type="hidden" name="metadados[<?= $codigo; ?>]" value="0">
            <div class="form-check form-switch pt-2">
                <input class="form-check-input" id="<?= $id; ?>" name="metadados[<?= $codigo; ?>]" type="checkbox" value="1" <?= $valor === '1' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="<?= $id; ?>">Sim</label>
            </div>
        <?php else: ?>
            <input class="form-control" id="<?= $id; ?>" name="metadados[<?= $codigo; ?>]" type="<?= htmlspecialchars($campo['tipo_campo'], ENT_QUOTES, 'UTF-8'); ?>" value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); ?>" <?= $obrigatorio ? 'required' : ''; ?>>
        <?php endif; ?>

        <?php if (!empty($campo['descricao'])): ?>
            <div class="form-text"><?= htmlspecialchars($campo['descricao'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
