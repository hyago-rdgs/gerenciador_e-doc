<?php if ($total_paginas > 1): ?>
    <?php
    $parametros = $filtros;
    $gerar_url = function ($pagina) use ($parametros) {
        $parametros['pagina'] = $pagina;
        return '?' . http_build_query($parametros);
    };
    $inicio = max(1, $pagina_atual - 2);
    $fim = min($total_paginas, $pagina_atual + 2);
    ?>
    <nav class="mt-4" aria-label="<?= htmlspecialchars($aria_paginacao, ENT_QUOTES, 'UTF-8'); ?>">
        <ul class="pagination pagination-sm justify-content-center mb-0">
            <li class="page-item <?= $pagina_atual <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link"
                    href="<?= htmlspecialchars($gerar_url(max(1, $pagina_atual - 1)), ENT_QUOTES, 'UTF-8'); ?>">
                    Anterior
                </a>
            </li>

            <?php if ($inicio > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= htmlspecialchars($gerar_url(1), ENT_QUOTES, 'UTF-8'); ?>">1</a>
                </li>
                <?php if ($inicio > 2): ?>
                    <li class="page-item disabled">
                        <span class="page-link border-0 bg-transparent text-muted">&hellip;</span>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($pagina = $inicio; $pagina <= $fim; $pagina++): ?>
                <li class="page-item <?= $pagina === $pagina_atual ? 'active' : ''; ?>">
                    <a class="page-link" href="<?= htmlspecialchars($gerar_url($pagina), ENT_QUOTES, 'UTF-8'); ?>">
                        <?= $pagina; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <?php if ($fim < $total_paginas): ?>
                <?php if ($fim < $total_paginas - 1): ?>
                    <li class="page-item disabled">
                        <span class="page-link border-0 bg-transparent text-muted">&hellip;</span>
                    </li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link"
                        href="<?= htmlspecialchars($gerar_url($total_paginas), ENT_QUOTES, 'UTF-8'); ?>">
                        <?= $total_paginas; ?>
                    </a>
                </li>
            <?php endif; ?>

            <li class="page-item <?= $pagina_atual >= $total_paginas ? 'disabled' : ''; ?>">
                <a class="page-link"
                    href="<?= htmlspecialchars($gerar_url(min($total_paginas, $pagina_atual + 1)), ENT_QUOTES, 'UTF-8'); ?>">
                    Próxima
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>
