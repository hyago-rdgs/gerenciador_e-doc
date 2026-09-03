<table class="data">
    <thead>
        <tr>
            <th>Movimentação</th>
            <th>Documento</th>
            <th>Tipo</th>
            <th>Origem</th>
            <th>Destino</th>
            <th>Responsável</th>
            <th>Situação</th>
            <th>Registrado por</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($movimentacoes as $movimentacao): ?>
            <tr>
                <td>
                    <?= htmlspecialchars(
                        $movimentacao['protocolo'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                    <div class="small muted">
                        <?= date(
                            'd/m/Y H:i',
                            strtotime($movimentacao['data_movimentacao'])
                        ); ?>
                    </div>
                </td>
                <td>
                    <?= htmlspecialchars(
                        $movimentacao['documento_titulo'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                    <div class="small muted">
                        <?= htmlspecialchars(
                            $movimentacao['documento_protocolo'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </div>
                </td>
                <td>
                    <?= htmlspecialchars(
                        $movimentacao['tipo_label'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </td>
                <td>
                    <?= htmlspecialchars(
                        $movimentacao['origem_label'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </td>
                <td>
                    <?= htmlspecialchars(
                        $movimentacao['destino_label'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </td>
                <td>
                    <?= htmlspecialchars(
                        $movimentacao['responsavel_nome']
                            ?? 'Não se aplica',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </td>
                <td>
                    <?= htmlspecialchars(
                        $movimentacao['situacao_label'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                    <?php if (!empty($movimentacao['data_prevista_devolucao'])): ?>
                        <div class="small muted">
                            Prevista:
                            <?= date(
                                'd/m/Y',
                                strtotime(
                                    $movimentacao['data_prevista_devolucao']
                                )
                            ); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($movimentacao['data_devolucao'])): ?>
                        <div class="small muted">
                            Devolvida:
                            <?= date(
                                'd/m/Y H:i',
                                strtotime($movimentacao['data_devolucao'])
                            ); ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <?= htmlspecialchars(
                        $movimentacao['usuario_nome'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
