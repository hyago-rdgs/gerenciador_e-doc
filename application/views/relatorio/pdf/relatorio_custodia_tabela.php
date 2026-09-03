<table class="data">
    <thead>
        <tr>
            <th>Retirada</th>
            <th>Documento</th>
            <th>Origem</th>
            <th>Responsável</th>
            <th>Previsão / devolução</th>
            <th>Situação</th>
            <th>Dias</th>
            <th>Registrado por</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($custodias as $custodia): ?>
            <tr>
                <td>
                    <?= htmlspecialchars(
                        $custodia['protocolo'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                    <div class="small muted">
                        <?= date(
                            'd/m/Y H:i',
                            strtotime($custodia['data_movimentacao'])
                        ); ?>
                    </div>
                </td>
                <td>
                    <?= htmlspecialchars(
                        $custodia['documento_titulo'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                    <div class="small muted">
                        <?= htmlspecialchars(
                            $custodia['documento_protocolo'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                        ·
                        <?= htmlspecialchars(
                            $custodia['tipo_documento'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </div>
                </td>
                <td>
                    <?= htmlspecialchars(
                        $custodia['origem_label'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </td>
                <td>
                    <?= htmlspecialchars(
                        $custodia['responsavel_nome'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                    <?php if (!empty($custodia['responsavel_contato'])): ?>
                        <div class="small muted">
                            <?= htmlspecialchars(
                                $custodia['responsavel_contato'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    Prevista:
                    <?= !empty($custodia['data_prevista_devolucao'])
                        ? date(
                            'd/m/Y',
                            strtotime(
                                $custodia['data_prevista_devolucao']
                            )
                        )
                        : 'Não informada'; ?>
                    <?php if (!empty($custodia['data_devolucao'])): ?>
                        <div class="small muted">
                            Devolvida:
                            <?= date(
                                'd/m/Y H:i',
                                strtotime($custodia['data_devolucao'])
                            ); ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <?= htmlspecialchars(
                        $custodia['situacao_label'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </td>
                <td>
                    <?= number_format(
                        (int) $custodia['dias_custodia'],
                        0,
                        ',',
                        '.'
                    ); ?> em custódia
                    <?php if ((int) $custodia['dias_atraso'] > 0): ?>
                        <div class="small muted">
                            <?= number_format(
                                (int) $custodia['dias_atraso'],
                                0,
                                ',',
                                '.'
                            ); ?> em atraso
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <?= htmlspecialchars(
                        $custodia['usuario_nome'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
