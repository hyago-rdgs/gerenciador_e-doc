<table class="data">
    <thead>
        <tr>
            <th>Protocolo</th>
            <th>Documento</th>
            <th>Tipo</th>
            <th>Localização</th>
            <th>Data documento</th>
            <th>Cadastro</th>
            <th>Digitalização</th>
            <th>Arquivos</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($documentos as $documento): ?>
            <tr>
                <td class="small">
                    <?= htmlspecialchars(
                        $documento['protocolo'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $documento['titulo'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>

                    <?php if (!empty($documento['numero_identificacao'])): ?>
                        <div class="small muted">
                            <?= htmlspecialchars(
                                $documento['numero_identificacao'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </div>
                    <?php endif; ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $documento['tipo_documento'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </td>

                <td>
                    <span class="small muted">
                        <?= htmlspecialchars(
                            $documento['localizacao_classificacao'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </span>
                    <br>
                    <?= htmlspecialchars(
                        $documento['localizacao'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </td>

                <td class="center">
                    <?= !empty($documento['data_documento'])
                        ? date(
                            'd/m/Y',
                            strtotime($documento['data_documento'])
                        )
                        : '-'; ?>
                </td>

                <td class="center">
                    <?= date(
                        'd/m/Y H:i',
                        strtotime($documento['cadastro'])
                    ); ?>
                </td>

                <td class="center">
                    <?= (int) $documento['total_arquivos'] > 0
                        ? 'Com arquivo'
                        : 'Sem arquivo'; ?>
                </td>

                <td class="right">
                    <?= number_format(
                        (int) $documento['total_arquivos'],
                        0,
                        ',',
                        '.'
                    ); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
