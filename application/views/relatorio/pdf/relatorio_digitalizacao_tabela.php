<table class="data">
    <thead>
        <tr>
            <th>Documento</th>
            <th>Tipo</th>
            <th>Localização</th>
            <th>Situação</th>
            <th>Arquivos</th>
            <th>Versões</th>
            <th>Armazenamento</th>
            <th>Último arquivo</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($documentos as $documento): ?>
            <tr>
                <td>
                    <?= htmlspecialchars(
                        $documento['titulo'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                    <div class="small muted">
                        <?= htmlspecialchars(
                            $documento['protocolo'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                        <?php if (!empty($documento['numero_identificacao'])): ?>
                            ·
                            <?= htmlspecialchars(
                                $documento['numero_identificacao'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <?= htmlspecialchars(
                        $documento['tipo_documento'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </td>
                <td>
                    <?= htmlspecialchars(
                        $documento['localizacao_label'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </td>
                <td>
                    <?= htmlspecialchars(
                        $documento['situacao_digital'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </td>
                <td>
                    <?= number_format(
                        (int) $documento['total_arquivos'],
                        0,
                        ',',
                        '.'
                    ); ?>
                </td>
                <td>
                    <?= number_format(
                        (int) $documento['total_versoes'],
                        0,
                        ',',
                        '.'
                    ); ?>
                </td>
                <td>
                    <?= htmlspecialchars(
                        $documento['tamanho_label'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </td>
                <td>
                    <?= !empty($documento['ultimo_arquivo_em'])
                        ? date(
                            'd/m/Y H:i',
                            strtotime($documento['ultimo_arquivo_em'])
                        )
                        : '—'; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
