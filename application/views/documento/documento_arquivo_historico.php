<?php if ($versoes): ?>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light text-uppercase">
                <tr class="small text-secondary">
                    <th class="px-3 py-3">Versão</th>
                    <th>Arquivo</th>
                    <th>Tamanho</th>
                    <th>Cadastro</th>
                    <th class="px-3 text-end">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($versoes as $indice => $versao): ?>
                    <tr>
                        <td class="ps-3">
                            <span class="badge text-bg-secondary">
                                v<?= (int) $versao['versao']; ?>
                            </span>
                            <?php if ($indice === 0): ?>
                                <span class="badge text-bg-primary ms-1">Atual</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($versao['nome_original'], ENT_QUOTES, 'UTF-8'); ?>
                        </td>
                        <td>
                            <?= number_format($versao['tamanho'] / 1024, 1, ',', '.'); ?> KB
                        </td>
                        <td>
                            <?= date('d/m/Y H:i', strtotime($versao['cadastro'])); ?>
                        </td>
                        <td class="pe-3 text-end">
                            <a class="btn btn-sm btn-light border"
                                href="<?= base_url(
                                    'documento/acessar_arquivo/' .
                                    $documento['codigo'] . '/' .
                                    $versao['codigo']
                                ); ?>"
                                target="_blank" rel="noopener"
                                title="Abrir versão">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p class="text-body-secondary text-center py-4 mb-0">
        Nenhuma versão encontrada.
    </p>
<?php endif; ?>
