<div class="row g-3">
    <div class="col-12 col-md-6">
        <span class="small text-body-secondary d-block">Tipo de documento</span>
        <span class="fw-semibold">
            <?= htmlspecialchars($tipo_documento['nome'], ENT_QUOTES, 'UTF-8'); ?>
        </span>
    </div>

    <div class="col-12 col-md-6">
        <span class="small text-body-secondary d-block">Localização</span>
        <span class="fw-semibold">
            <?= htmlspecialchars(
                $localizacao['classificacao'] . ' — ' . $localizacao['nome'],
                ENT_QUOTES,
                'UTF-8'
            ); ?>
        </span>
    </div>

    <div class="col-12 col-md-6">
        <span class="small text-body-secondary d-block">Título</span>
        <span><?= htmlspecialchars($documento['titulo'], ENT_QUOTES, 'UTF-8'); ?></span>
    </div>

    <?php if (!empty($documento['numero_identificacao'])): ?>
        <div class="col-12 col-md-6">
            <span class="small text-body-secondary d-block">Número de identificação</span>
            <span><?= htmlspecialchars($documento['numero_identificacao'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($documento['data_documento'])): ?>
        <div class="col-12 col-md-6">
            <span class="small text-body-secondary d-block">Data do documento</span>
            <span><?= date('d/m/Y', strtotime($documento['data_documento'])); ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($documento['descricao'])): ?>
        <div class="col-12">
            <span class="small text-body-secondary d-block">Descrição</span>
            <span><?= nl2br(htmlspecialchars($documento['descricao'], ENT_QUOTES, 'UTF-8')); ?></span>
        </div>
    <?php endif; ?>
</div>

<?php if ($campos_metadados): ?>
    <hr class="my-4">

    <h3 class="h6 fw-semibold mb-3">Metadados</h3>

    <div class="row g-3">
        <?php foreach ($campos_metadados as $campo): ?>
            <?php
            $valor = $metadados[$campo['metadado_codigo']] ?? '';

            if (is_array($valor)) {
                $valor = implode(', ', $valor);
            }
            ?>

            <?php if (trim((string) $valor) !== ''): ?>
                <div class="col-12 col-md-6">
                    <span class="small text-body-secondary d-block">
                        <?= htmlspecialchars($campo['nome'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span><?= htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>