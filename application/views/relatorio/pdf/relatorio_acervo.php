<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: sans-serif;
            font-size: 9pt;
            color: #212529;
        }

        h1 {
            font-size: 18pt;
            margin: 0 0 4px 0;
        }

        .muted {
            color: #6c757d;
        }

        .summary {
            width: 100%;
            margin: 14px 0;
            border-collapse: collapse;
        }

        .summary td {
            width: 25%;
            border: 1px solid #dee2e6;
            padding: 8px;
        }

        .summary strong {
            display: block;
            font-size: 14pt;
            margin-top: 3px;
        }

        .filters {
            border: 1px solid #dee2e6;
            padding: 8px;
            margin-bottom: 14px;
        }

        .data {
            width: 100%;
            border-collapse: collapse;
        }

        .data th,
        .data td {
            border: 1px solid #dee2e6;
            padding: 5px;
            vertical-align: top;
        }

        .data th {
            background: #f1f3f5;
            font-size: 8pt;
            text-transform: uppercase;
        }

        .small {
            font-size: 8pt;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }
    </style>
</head>

<body>
    <h1>Relatório de Acervo</h1>
    <div class="muted">
        e-Doc · Emitido em <?= htmlspecialchars($emitido_em, ENT_QUOTES, 'UTF-8'); ?>
    </div>

    <table class="summary">
        <tr>
            <td>
                <span class="muted">Documentos</span>
                <strong><?= number_format($resumo['total'], 0, ',', '.'); ?></strong>
            </td>
            <td>
                <span class="muted">Com arquivo digital</span>
                <strong><?= number_format($resumo['com_arquivo'], 0, ',', '.'); ?></strong>
            </td>
            <td>
                <span class="muted">Sem arquivo digital</span>
                <strong><?= number_format($resumo['sem_arquivo'], 0, ',', '.'); ?></strong>
            </td>
            <td>
                <span class="muted">Cobertura digital</span>
                <strong><?= number_format($resumo['cobertura_percentual'], 1, ',', '.'); ?>%</strong>
            </td>
        </tr>
    </table>

    <div class="filters">
        <strong>Filtros:</strong>
        <?= htmlspecialchars(implode(' · ', $filtros_descricao), ENT_QUOTES, 'UTF-8'); ?>
    </div>

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
                        <?= htmlspecialchars($documento['protocolo'], ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($documento['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php if (!empty($documento['numero_identificacao'])): ?>
                            <div class="small muted">
                                <?= htmlspecialchars($documento['numero_identificacao'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($documento['tipo_documento'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <span class="small muted">
                            <?= htmlspecialchars($documento['localizacao_classificacao'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        <br>
                        <?= htmlspecialchars($documento['localizacao'], ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td class="center">
                        <?= !empty($documento['data_documento'])
                            ? date('d/m/Y', strtotime($documento['data_documento']))
                            : '-'; ?>
                    </td>
                    <td class="center">
                        <?= date('d/m/Y H:i', strtotime($documento['cadastro'])); ?>
                    </td>
                    <td class="center">
                        <?= (int) $documento['total_arquivos'] > 0
                            ? 'Com arquivo'
                            : 'Sem arquivo'; ?>
                    </td>
                    <td class="right">
                        <?= number_format((int) $documento['total_arquivos'], 0, ',', '.'); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>
