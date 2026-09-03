<div class="p-4">
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <span class="small text-body-secondary d-block">Usuário</span>
            <span class="fw-semibold">
                <?= htmlspecialchars(
                    $auditoria['usuario_nome'] ?? 'Sistema',
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </span>
            <?php if (!empty($auditoria['usuario_login'])): ?>
                <small class="text-body-secondary d-block">
                    <?= htmlspecialchars(
                        $auditoria['usuario_login'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </small>
            <?php endif; ?>
        </div>

        <div class="col-12 col-md-6">
            <span class="small text-body-secondary d-block">Data e hora</span>
            <span><?= date('d/m/Y H:i:s', strtotime($auditoria['cadastro'])); ?></span>
        </div>

        <div class="col-12 col-md-4">
            <span class="small text-body-secondary d-block">Módulo</span>
            <span><?= htmlspecialchars($auditoria['modulo'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>

        <div class="col-12 col-md-4">
            <span class="small text-body-secondary d-block">Ação</span>
            <span class="font-monospace">
                <?= htmlspecialchars($auditoria['acao'], ENT_QUOTES, 'UTF-8'); ?>
            </span>
        </div>

        <div class="col-12 col-md-4">
            <span class="small text-body-secondary d-block">Entidade</span>
            <span>
                <?= htmlspecialchars($auditoria['entidade'], ENT_QUOTES, 'UTF-8'); ?>
                <?php if ($auditoria['entidade_codigo'] !== NULL): ?>
                    #<?= (int) $auditoria['entidade_codigo']; ?>
                <?php endif; ?>
            </span>
        </div>

        <div class="col-12 col-md-4">
            <span class="small text-body-secondary d-block">Endereço IP</span>
            <span>
                <?= htmlspecialchars(
                    $auditoria['endereco_ip'] ?? 'Não informado',
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </span>
        </div>

        <div class="col-12 col-md-8">
            <span class="small text-body-secondary d-block">User agent</span>
            <span class="text-break">
                <?= htmlspecialchars(
                    $auditoria['user_agent'] ?? 'Não informado',
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <h3 class="h6 fw-semibold">Estado anterior</h3>

            <?php if ($auditoria['dados_anteriores_formatados'] !== NULL): ?>
                <pre class="small bg-body-tertiary border rounded p-3 mb-0 text-wrap"><?= htmlspecialchars(
                    $auditoria['dados_anteriores_formatados'],
                    ENT_QUOTES,
                    'UTF-8'
                ); ?></pre>
            <?php else: ?>
                <p class="text-body-secondary mb-0">Não se aplica.</p>
            <?php endif; ?>
        </div>

        <div class="col-12 col-xl-6">
            <h3 class="h6 fw-semibold">Estado posterior</h3>

            <?php if ($auditoria['dados_novos_formatados'] !== NULL): ?>
                <pre class="small bg-body-tertiary border rounded p-3 mb-0 text-wrap"><?= htmlspecialchars(
                    $auditoria['dados_novos_formatados'],
                    ENT_QUOTES,
                    'UTF-8'
                ); ?></pre>
            <?php else: ?>
                <p class="text-body-secondary mb-0">Não se aplica.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
