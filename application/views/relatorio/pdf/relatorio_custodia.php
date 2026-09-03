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

<h1>Relatório de Custódia e Retiradas</h1>

<div class="muted">
    e-Doc · Emitido em
    <?= htmlspecialchars($emitido_em, ENT_QUOTES, 'UTF-8'); ?>
</div>

<table class="summary">
    <tr>
        <td>
            <span class="muted">Em aberto</span>
            <strong><?= number_format($resumo['abertas'], 0, ',', '.'); ?></strong>
        </td>
        <td>
            <span class="muted">Em atraso</span>
            <strong><?= number_format($resumo['atrasadas'], 0, ',', '.'); ?></strong>
        </td>
        <td>
            <span class="muted">Vencem hoje</span>
            <strong><?= number_format($resumo['vencem_hoje'], 0, ',', '.'); ?></strong>
        </td>
        <td>
            <span class="muted">Sem previsão</span>
            <strong><?= number_format($resumo['sem_previsao'], 0, ',', '.'); ?></strong>
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
