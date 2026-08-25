<?php if (!$metadados_pesquisa): ?>
    <div class="col-12">
        <div class="alert alert-light border mb-0">
            Este tipo de documento não possui metadados pesquisáveis.
        </div>
    </div>
<?php endif; ?>

<?php foreach ($metadados_pesquisa as $metadado): ?>
    <?php
    $codigo = (int) $metadado['metadado_codigo'];
    $id = 'metadado_' . $codigo;
    $valor = $filtros_metadados[$codigo] ?? '';
    $opcoes = array_values(
        array_filter(
            array_map(
                'trim',
                preg_split(
                    '/\r\n|\r|\n/',
                    (string) ($metadado['opcoes'] ?? '')
                )
            ),
            'strlen'
        )
    );
    ?>

    <div class="col-12 col-md-6">
        <label class="form-label" for="<?= $id; ?>">
            <?= htmlspecialchars($metadado['nome'], ENT_QUOTES, 'UTF-8'); ?>
        </label>

        <?php if ($metadado['tipo_campo'] === 'select' || $metadado['tipo_campo'] === 'radio'): ?>
            <select class="form-select" id="<?= $id; ?>" name="metadados[<?= $codigo; ?>]">
                <option value="">Todos</option>
                <?php foreach ($opcoes as $opcao): ?>
                    <option value="<?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8'); ?>"
                        <?= $valor === $opcao ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php elseif ($metadado['tipo_campo'] === 'checkbox' && $opcoes): ?>
            <div class="d-flex flex-wrap gap-3 pt-2" id="<?= $id; ?>">
                <?php foreach ($opcoes as $indice => $opcao): ?>
                    <div class="form-check">
                        <input class="form-check-input"
                            id="<?= $id . '_' . $indice; ?>"
                            name="metadados[<?= $codigo; ?>][]"
                            type="checkbox"
                            value="<?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8'); ?>"
                            <?= is_array($valor) && in_array($opcao, $valor, TRUE) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="<?= $id . '_' . $indice; ?>">
                            <?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8'); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($metadado['tipo_campo'] === 'checkbox'): ?>
            <select class="form-select" id="<?= $id; ?>" name="metadados[<?= $codigo; ?>]">
                <option value="">Todos</option>
                <option value="1" <?= $valor === '1' ? 'selected' : ''; ?>>Sim</option>
                <option value="0" <?= $valor === '0' ? 'selected' : ''; ?>>Não</option>
            </select>
        <?php else: ?>
            <input class="form-control"
                id="<?= $id; ?>"
                name="metadados[<?= $codigo; ?>]"
                type="<?= $metadado['tipo_campo'] === 'textarea' ? 'text' : htmlspecialchars($metadado['tipo_campo'], ENT_QUOTES, 'UTF-8'); ?>"
                value="<?= htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8'); ?>">
        <?php endif; ?>

        <?php if (!empty($metadado['descricao'])): ?>
            <div class="form-text">
                <?= htmlspecialchars($metadado['descricao'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
