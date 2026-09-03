<style>
    body {
        font-family: sans-serif;
        font-size: 8pt;
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
        margin-bottom: 8px;
    }

    .data th,
    .data td {
        border: 1px solid #dee2e6;
        padding: 4px;
        vertical-align: top;
    }

    .data th {
        background: #f1f3f5;
        font-size: 7pt;
        text-transform: uppercase;
    }

    .small {
        font-size: 7pt;
    }
</style>

<h1>Relatório de Digitalização</h1>

<div class="muted">
    e-Doc · Emitido em
    <?= htmlspecialchars($emitido_em, ENT_QUOTES, 'UTF-8'); ?>
</div>

<table class="summary">
    <tr>
        <td>
            <span class="muted">Documentos</span>
            <strong><?= number_format($resumo['total'], 0, ',', '.'); ?></strong>
        </td>
        <td>
            <span class="muted">Com arquivo</span>
            <strong><?= number_format($resumo['com_arquivo'], 0, ',', '.'); ?></strong>
        </td>
        <td>
            <span class="muted">Sem arquivo</span>
            <strong><?= number_format($resumo['sem_arquivo'], 0, ',', '.'); ?></strong>
        </td>
        <td>
            <span class="muted">Cobertura</span>
            <strong><?= number_format($resumo['cobertura_percentual'], 1, ',', '.'); ?>%</strong>
        </td>
    </tr>
    <tr>
        <td>
            <span class="muted">Arquivos atuais</span>
            <strong><?= number_format($resumo['total_arquivos'], 0, ',', '.'); ?></strong>
        </td>
        <td>
            <span class="muted">Versões armazenadas</span>
            <strong><?= number_format($resumo['total_versoes'], 0, ',', '.'); ?></strong>
        </td>
        <td>
            <span class="muted">Múltiplas versões</span>
            <strong><?= number_format($resumo['multiplas_versoes'], 0, ',', '.'); ?></strong>
        </td>
        <td>
            <span class="muted">Armazenamento</span>
            <strong><?= htmlspecialchars($resumo['tamanho_label'], ENT_QUOTES, 'UTF-8'); ?></strong>
        </td>
    </tr>
</table>

<div class="filters">
    <strong>Filtros:</strong>
    <?= htmlspecialchars(
        implode(' · ', $filtros_descricao),
        ENT_QUOTES,
        'UTF-8'
    ); ?>
</div>
