<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Relatorio_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar_acervo(
        $filtros,
        $limite = NULL,
        $offset = NULL
    ) {
        $this->db->select([
            'd.codigo',
            'd.protocolo',
            'd.titulo',
            'd.numero_identificacao',
            'd.data_documento',
            'd.cadastro',
            'd.tipo_documento_codigo',
            "COALESCE(td.nome, 'Não disponível') AS tipo_documento",
            'd.localizacao_codigo',
            "COALESCE(l.nome, 'Não disponível') AS localizacao",
            "COALESCE(l.classificacao, '-') AS localizacao_classificacao",
            'COALESCE(arq.total_arquivos, 0) AS total_arquivos',
            'COALESCE(arq.tamanho_total, 0) AS tamanho_total'
        ], FALSE);

        $this->preparar_base_acervo();
        $this->aplicar_filtros_acervo($filtros);

        $this->db->order_by('d.titulo', 'ASC');
        $this->db->order_by('d.codigo', 'ASC');

        if ($limite !== NULL) {
            $this->db->limit($limite, $offset);
        }

        return $this->db->get()->result_array();
    }

    public function contar_acervo($filtros)
    {
        $this->db->select(
            'COUNT(d.codigo) AS total',
            FALSE
        );

        $this->preparar_base_acervo();
        $this->aplicar_filtros_acervo($filtros);

        $resultado = $this->db->get()->row_array();

        return (int) ($resultado['total'] ?? 0);
    }

    public function obter_resumo_acervo($filtros)
    {
        $this->db->select([
            'COUNT(d.codigo) AS total',
            'SUM(CASE WHEN COALESCE(arq.total_arquivos, 0) > 0 THEN 1 ELSE 0 END) AS com_arquivo',
            'SUM(CASE WHEN COALESCE(arq.total_arquivos, 0) = 0 THEN 1 ELSE 0 END) AS sem_arquivo',
            'SUM(COALESCE(arq.total_arquivos, 0)) AS total_arquivos',
            'SUM(COALESCE(arq.tamanho_total, 0)) AS tamanho_total'
        ], FALSE);

        $this->preparar_base_acervo();
        $this->aplicar_filtros_acervo($filtros);

        $resultado = $this->db->get()->row_array();

        $total = (int) ($resultado['total'] ?? 0);
        $com_arquivo = (int) (
            $resultado['com_arquivo'] ?? 0
        );

        return [
            'total' => $total,
            'com_arquivo' => $com_arquivo,
            'sem_arquivo' => (int) (
                $resultado['sem_arquivo'] ?? 0
            ),
            'total_arquivos' => (int) (
                $resultado['total_arquivos'] ?? 0
            ),
            'tamanho_total' => (int) (
                $resultado['tamanho_total'] ?? 0
            ),
            'cobertura_percentual' => $total > 0
                ? round(
                    ($com_arquivo / $total) * 100,
                    1
                )
                : 0
        ];
    }

    public function listar_movimentacoes(
        $filtros,
        $limite = NULL,
        $offset = NULL
    ) {
        $this->db->select([
            'dm.codigo',
            'dm.protocolo',
            'dm.documento_codigo',
            'dm.tipo_movimentacao',
            'dm.responsavel_nome',
            'dm.responsavel_contato',
            'dm.observacao',
            'dm.data_movimentacao',
            'dm.data_prevista_devolucao',
            'dm.data_devolucao',
            'd.protocolo AS documento_protocolo',
            'd.titulo AS documento_titulo',
            'd.exclusao AS documento_exclusao',
            "COALESCE(lo.nome, 'Externo') AS localizacao_origem",
            "COALESCE(lo.classificacao, '-') AS localizacao_origem_classificacao",
            "COALESCE(ld.nome, 'Sob responsabilidade') AS localizacao_destino",
            "COALESCE(ld.classificacao, '-') AS localizacao_destino_classificacao",
            "COALESCE(u.nome, 'Sistema') AS usuario_nome"
        ], FALSE);

        $this->preparar_base_movimentacoes();
        $this->aplicar_filtros_movimentacoes($filtros);

        $this->db->order_by('dm.data_movimentacao', 'DESC');
        $this->db->order_by('dm.codigo', 'DESC');

        if ($limite !== NULL) {
            $this->db->limit($limite, $offset);
        }

        return $this->db->get()->result_array();
    }

    public function contar_movimentacoes($filtros)
    {
        $this->db->select('COUNT(dm.codigo) AS total', FALSE);

        $this->preparar_base_movimentacoes();
        $this->aplicar_filtros_movimentacoes($filtros);

        $resultado = $this->db->get()->row_array();

        return (int) ($resultado['total'] ?? 0);
    }

    public function obter_resumo_movimentacoes($filtros)
    {
        $this->db->select([
            'COUNT(dm.codigo) AS total',
            'COUNT(DISTINCT dm.documento_codigo) AS documentos',
            "SUM(CASE WHEN dm.tipo_movimentacao = 'TRANSFERENCIA' THEN 1 ELSE 0 END) AS transferencias",
            "SUM(CASE WHEN dm.tipo_movimentacao = 'RETIRADA' THEN 1 ELSE 0 END) AS retiradas"
        ], FALSE);

        $this->preparar_base_movimentacoes();
        $this->aplicar_filtros_movimentacoes($filtros);

        $resultado = $this->db->get()->row_array();

        return [
            'total' => (int) ($resultado['total'] ?? 0),
            'documentos' => (int) (
                $resultado['documentos'] ?? 0
            ),
            'transferencias' => (int) (
                $resultado['transferencias'] ?? 0
            ),
            'retiradas' => (int) (
                $resultado['retiradas'] ?? 0
            )
        ];
    }

    public function listar_custodias(
        $filtros,
        $limite = NULL,
        $offset = NULL
    ) {
        $this->db->select([
            'dm.codigo',
            'dm.protocolo',
            'dm.documento_codigo',
            'dm.tipo_movimentacao',
            'dm.responsavel_nome',
            'dm.responsavel_contato',
            'dm.observacao',
            'dm.data_movimentacao',
            'dm.data_prevista_devolucao',
            'dm.data_devolucao',
            'd.protocolo AS documento_protocolo',
            'd.titulo AS documento_titulo',
            'd.exclusao AS documento_exclusao',
            "COALESCE(td.nome, 'Não disponível') AS tipo_documento",
            "COALESCE(lo.nome, 'Não disponível') AS localizacao_origem",
            "COALESCE(lo.classificacao, '-') AS localizacao_origem_classificacao",
            "COALESCE(u.nome, 'Sistema') AS usuario_nome",
            'DATEDIFF(COALESCE(DATE(dm.data_devolucao), CURDATE()), DATE(dm.data_movimentacao)) AS dias_custodia',
            'CASE
                WHEN dm.data_prevista_devolucao IS NOT NULL
                    AND dm.data_prevista_devolucao < COALESCE(DATE(dm.data_devolucao), CURDATE())
                THEN DATEDIFF(
                    COALESCE(DATE(dm.data_devolucao), CURDATE()),
                    dm.data_prevista_devolucao
                )
                ELSE 0
            END AS dias_atraso'
        ], FALSE);

        $this->preparar_base_custodias();
        $this->aplicar_filtros_custodias($filtros);
        $this->db->order_by('dm.data_movimentacao', 'DESC');
        $this->db->order_by('dm.codigo', 'DESC');

        if ($limite !== NULL) {
            $this->db->limit($limite, $offset);
        }

        return $this->db->get()->result_array();
    }

    public function contar_custodias($filtros)
    {
        $this->db->select('COUNT(dm.codigo) AS total', FALSE);
        $this->preparar_base_custodias();
        $this->aplicar_filtros_custodias($filtros);

        $resultado = $this->db->get()->row_array();

        return (int) ($resultado['total'] ?? 0);
    }

    public function obter_resumo_custodias($filtros)
    {
        $this->db->select([
            'SUM(CASE WHEN dm.data_devolucao IS NULL THEN 1 ELSE 0 END) AS abertas',
            'SUM(CASE
                WHEN dm.data_devolucao IS NULL
                    AND dm.data_prevista_devolucao < CURDATE()
                THEN 1 ELSE 0
            END) AS atrasadas',
            'SUM(CASE
                WHEN dm.data_devolucao IS NULL
                    AND dm.data_prevista_devolucao = CURDATE()
                THEN 1 ELSE 0
            END) AS vencem_hoje',
            'SUM(CASE
                WHEN dm.data_devolucao IS NULL
                    AND dm.data_prevista_devolucao IS NULL
                THEN 1 ELSE 0
            END) AS sem_previsao'
        ], FALSE);

        $this->preparar_base_custodias();
        $this->aplicar_filtros_custodias($filtros);

        $resultado = $this->db->get()->row_array();

        return [
            'abertas' => (int) ($resultado['abertas'] ?? 0),
            'atrasadas' => (int) ($resultado['atrasadas'] ?? 0),
            'vencem_hoje' => (int) (
                $resultado['vencem_hoje'] ?? 0
            ),
            'sem_previsao' => (int) (
                $resultado['sem_previsao'] ?? 0
            )
        ];
    }

    public function listar_digitalizacao(
        $filtros,
        $limite = NULL,
        $offset = NULL
    ) {
        $this->db->select([
            'd.codigo',
            'd.protocolo',
            'd.titulo',
            'd.numero_identificacao',
            'd.cadastro',
            "COALESCE(td.nome, 'Não disponível') AS tipo_documento",
            "COALESCE(l.nome, 'Não disponível') AS localizacao",
            "COALESCE(l.classificacao, '-') AS localizacao_classificacao",
            'COALESCE(arq.total_arquivos, 0) AS total_arquivos',
            'COALESCE(arq.total_versoes, 0) AS total_versoes',
            'COALESCE(arq.tamanho_total, 0) AS tamanho_total',
            'arq.ultimo_arquivo_em'
        ], FALSE);

        $this->preparar_base_digitalizacao();
        $this->aplicar_filtros_digitalizacao($filtros);
        $this->db->order_by('d.titulo', 'ASC');
        $this->db->order_by('d.codigo', 'ASC');

        if ($limite !== NULL) {
            $this->db->limit($limite, $offset);
        }

        return $this->db->get()->result_array();
    }

    public function contar_digitalizacao($filtros)
    {
        $this->db->select('COUNT(d.codigo) AS total', FALSE);
        $this->preparar_base_digitalizacao();
        $this->aplicar_filtros_digitalizacao($filtros);

        $resultado = $this->db->get()->row_array();

        return (int) ($resultado['total'] ?? 0);
    }

    public function obter_resumo_digitalizacao($filtros)
    {
        $this->db->select([
            'COUNT(d.codigo) AS total',
            'SUM(CASE WHEN COALESCE(arq.total_arquivos, 0) > 0 THEN 1 ELSE 0 END) AS com_arquivo',
            'SUM(CASE WHEN COALESCE(arq.total_arquivos, 0) = 0 THEN 1 ELSE 0 END) AS sem_arquivo',
            'SUM(CASE WHEN COALESCE(arq.total_versoes, 0) > COALESCE(arq.total_arquivos, 0) THEN 1 ELSE 0 END) AS multiplas_versoes',
            'SUM(COALESCE(arq.total_arquivos, 0)) AS total_arquivos',
            'SUM(COALESCE(arq.total_versoes, 0)) AS total_versoes',
            'SUM(COALESCE(arq.tamanho_total, 0)) AS tamanho_total'
        ], FALSE);

        $this->preparar_base_digitalizacao();
        $this->aplicar_filtros_digitalizacao($filtros);

        $resultado = $this->db->get()->row_array();
        $total = (int) ($resultado['total'] ?? 0);
        $com_arquivo = (int) (
            $resultado['com_arquivo'] ?? 0
        );

        return [
            'total' => $total,
            'com_arquivo' => $com_arquivo,
            'sem_arquivo' => (int) (
                $resultado['sem_arquivo'] ?? 0
            ),
            'cobertura_percentual' => $total > 0
                ? round(($com_arquivo / $total) * 100, 1)
                : 0,
            'multiplas_versoes' => (int) (
                $resultado['multiplas_versoes'] ?? 0
            ),
            'total_arquivos' => (int) (
                $resultado['total_arquivos'] ?? 0
            ),
            'total_versoes' => (int) (
                $resultado['total_versoes'] ?? 0
            ),
            'tamanho_total' => (int) (
                $resultado['tamanho_total'] ?? 0
            )
        ];
    }

    public function listar_tipos_documento_opcoes()
    {
        $this->db->select(['codigo', 'nome']);
        $this->db->where(
            'exclusao IS NULL',
            NULL,
            FALSE
        );
        $this->db->order_by('nome', 'ASC');

        return $this->db
            ->get('tipos_documento')
            ->result_array();
    }

    public function listar_localizacoes_opcoes()
    {
        $this->db->select([
            'codigo',
            'nome',
            'classificacao'
        ]);
        $this->db->where(
            'exclusao IS NULL',
            NULL,
            FALSE
        );
        $this->db->order_by('classificacao', 'ASC');

        return $this->db
            ->get('localizacoes')
            ->result_array();
    }

    public function listar_usuarios_opcoes()
    {
        $this->db->select(['codigo', 'nome']);
        $this->db->where(
            'exclusao IS NULL',
            NULL,
            FALSE
        );
        $this->db->order_by('nome', 'ASC');

        return $this->db
            ->get('usuarios')
            ->result_array();
    }

    private function preparar_base_acervo()
    {
        $this->db->from('documentos d');

        $this->db->join(
            'tipos_documento td',
            'td.codigo = d.tipo_documento_codigo',
            'left'
        );

        $this->db->join(
            'localizacoes l',
            'l.codigo = d.localizacao_codigo',
            'left'
        );

        $this->db->join(
            '(
                SELECT
                    documento_codigo,
                    COUNT(*) AS total_arquivos,
                    SUM(COALESCE(tamanho, 0)) AS tamanho_total
                FROM documento_arquivos
                WHERE exclusao IS NULL
                GROUP BY documento_codigo
            ) arq',
            'arq.documento_codigo = d.codigo',
            'left',
            FALSE
        );

        $this->db->where(
            'd.exclusao IS NULL',
            NULL,
            FALSE
        );
    }

    private function aplicar_filtros_acervo($filtros)
    {
        if ($filtros['termo'] !== '') {
            $this->db->group_start();
            $this->db->like(
                'd.protocolo',
                $filtros['termo']
            );
            $this->db->or_like(
                'd.titulo',
                $filtros['termo']
            );
            $this->db->or_like(
                'd.numero_identificacao',
                $filtros['termo']
            );
            $this->db->or_like(
                'td.nome',
                $filtros['termo']
            );
            $this->db->or_like(
                'l.nome',
                $filtros['termo']
            );
            $this->db->or_like(
                'l.classificacao',
                $filtros['termo']
            );
            $this->db->group_end();
        }

        if (
            $filtros['tipo_documento_codigo'] !== ''
        ) {
            $this->db->where(
                'd.tipo_documento_codigo',
                (int) $filtros[
                    'tipo_documento_codigo'
                ]
            );
        }

        if ($filtros['localizacao_codigo'] !== '') {
            $this->db->where(
                'd.localizacao_codigo',
                (int) $filtros[
                    'localizacao_codigo'
                ]
            );
        }

        if ($filtros['data_inicio'] !== '') {
            $this->db->where(
                'd.cadastro >=',
                $filtros['data_inicio']
                    . ' 00:00:00'
            );
        }

        if ($filtros['data_fim'] !== '') {
            $this->db->where(
                'd.cadastro <=',
                $filtros['data_fim']
                    . ' 23:59:59'
            );
        }

        if (
            $filtros['digitalizacao']
            === 'com_arquivo'
        ) {
            $this->db->where(
                'COALESCE(arq.total_arquivos, 0) >',
                0,
                FALSE
            );
        } elseif (
            $filtros['digitalizacao']
            === 'sem_arquivo'
        ) {
            $this->db->where(
                'COALESCE(arq.total_arquivos, 0) =',
                0,
                FALSE
            );
        }
    }

    private function preparar_base_movimentacoes()
    {
        $this->db->from('documento_movimentacoes dm');
        $this->db->join(
            'documentos d',
            'd.codigo = dm.documento_codigo',
            'inner'
        );
        $this->db->join(
            'localizacoes lo',
            'lo.codigo = dm.localizacao_origem_codigo',
            'left'
        );
        $this->db->join(
            'localizacoes ld',
            'ld.codigo = dm.localizacao_destino_codigo',
            'left'
        );
        $this->db->join(
            'usuarios u',
            'u.codigo = dm.usuario_codigo',
            'left'
        );
    }

    private function aplicar_filtros_movimentacoes($filtros)
    {
        if ($filtros['termo'] !== '') {
            $this->db->group_start();
            $this->db->like('dm.protocolo', $filtros['termo']);
            $this->db->or_like(
                'd.protocolo',
                $filtros['termo']
            );
            $this->db->or_like('d.titulo', $filtros['termo']);
            $this->db->or_like(
                'dm.responsavel_nome',
                $filtros['termo']
            );
            $this->db->group_end();
        }

        if ($filtros['tipo_movimentacao'] !== '') {
            $this->db->where(
                'dm.tipo_movimentacao',
                $filtros['tipo_movimentacao']
            );
        }

        if ($filtros['situacao'] === 'aberta') {
            $this->db->where(
                'dm.tipo_movimentacao',
                'RETIRADA'
            );
            $this->db->where(
                'dm.data_devolucao IS NULL',
                NULL,
                FALSE
            );
        } elseif ($filtros['situacao'] === 'atrasada') {
            $this->db->where(
                'dm.tipo_movimentacao',
                'RETIRADA'
            );
            $this->db->where(
                'dm.data_devolucao IS NULL',
                NULL,
                FALSE
            );
            $this->db->where(
                'dm.data_prevista_devolucao <',
                date('Y-m-d')
            );
        } elseif ($filtros['situacao'] === 'concluida') {
            $this->db->group_start();
            $this->db->where(
                'dm.tipo_movimentacao !=',
                'RETIRADA'
            );
            $this->db->or_where(
                'dm.data_devolucao IS NOT NULL',
                NULL,
                FALSE
            );
            $this->db->group_end();
        }

        if ($filtros['localizacao_codigo'] !== '') {
            $localizacao_codigo = (int) $filtros[
                'localizacao_codigo'
            ];

            $this->db->group_start();
            $this->db->where(
                'dm.localizacao_origem_codigo',
                $localizacao_codigo
            );
            $this->db->or_where(
                'dm.localizacao_destino_codigo',
                $localizacao_codigo
            );
            $this->db->group_end();
        }

        if ($filtros['usuario_codigo'] !== '') {
            $this->db->where(
                'dm.usuario_codigo',
                (int) $filtros['usuario_codigo']
            );
        }

        if ($filtros['data_inicio'] !== '') {
            $this->db->where(
                'dm.data_movimentacao >=',
                $filtros['data_inicio'] . ' 00:00:00'
            );
        }

        if ($filtros['data_fim'] !== '') {
            $this->db->where(
                'dm.data_movimentacao <=',
                $filtros['data_fim'] . ' 23:59:59'
            );
        }
    }

    private function preparar_base_custodias()
    {
        $this->db->from('documento_movimentacoes dm');
        $this->db->join(
            'documentos d',
            'd.codigo = dm.documento_codigo',
            'inner'
        );
        $this->db->join(
            'tipos_documento td',
            'td.codigo = d.tipo_documento_codigo',
            'left'
        );
        $this->db->join(
            'localizacoes lo',
            'lo.codigo = dm.localizacao_origem_codigo',
            'left'
        );
        $this->db->join(
            'usuarios u',
            'u.codigo = dm.usuario_codigo',
            'left'
        );
        $this->db->where('dm.tipo_movimentacao', 'RETIRADA');
    }

    private function aplicar_filtros_custodias($filtros)
    {
        if ($filtros['termo'] !== '') {
            $this->db->group_start();
            $this->db->like('dm.protocolo', $filtros['termo']);
            $this->db->or_like(
                'd.protocolo',
                $filtros['termo']
            );
            $this->db->or_like('d.titulo', $filtros['termo']);
            $this->db->or_like(
                'dm.responsavel_nome',
                $filtros['termo']
            );
            $this->db->or_like(
                'dm.responsavel_contato',
                $filtros['termo']
            );
            $this->db->group_end();
        }

        if ($filtros['situacao'] === 'aberta') {
            $this->db->where(
                'dm.data_devolucao IS NULL',
                NULL,
                FALSE
            );
        } elseif ($filtros['situacao'] === 'atrasada') {
            $this->db->where(
                'dm.data_devolucao IS NULL',
                NULL,
                FALSE
            );
            $this->db->where(
                'dm.data_prevista_devolucao <',
                date('Y-m-d')
            );
        } elseif ($filtros['situacao'] === 'vence_hoje') {
            $this->db->where(
                'dm.data_devolucao IS NULL',
                NULL,
                FALSE
            );
            $this->db->where(
                'dm.data_prevista_devolucao',
                date('Y-m-d')
            );
        } elseif ($filtros['situacao'] === 'sem_previsao') {
            $this->db->where(
                'dm.data_devolucao IS NULL',
                NULL,
                FALSE
            );
            $this->db->where(
                'dm.data_prevista_devolucao IS NULL',
                NULL,
                FALSE
            );
        } elseif ($filtros['situacao'] === 'devolvida') {
            $this->db->where(
                'dm.data_devolucao IS NOT NULL',
                NULL,
                FALSE
            );
        }

        if ($filtros['tipo_documento_codigo'] !== '') {
            $this->db->where(
                'd.tipo_documento_codigo',
                (int) $filtros['tipo_documento_codigo']
            );
        }

        if ($filtros['localizacao_codigo'] !== '') {
            $this->db->where(
                'dm.localizacao_origem_codigo',
                (int) $filtros['localizacao_codigo']
            );
        }

        if ($filtros['usuario_codigo'] !== '') {
            $this->db->where(
                'dm.usuario_codigo',
                (int) $filtros['usuario_codigo']
            );
        }

        if ($filtros['data_inicio'] !== '') {
            $this->db->where(
                'dm.data_movimentacao >=',
                $filtros['data_inicio'] . ' 00:00:00'
            );
        }

        if ($filtros['data_fim'] !== '') {
            $this->db->where(
                'dm.data_movimentacao <=',
                $filtros['data_fim'] . ' 23:59:59'
            );
        }
    }

    private function preparar_base_digitalizacao()
    {
        $this->db->from('documentos d');
        $this->db->join(
            'tipos_documento td',
            'td.codigo = d.tipo_documento_codigo',
            'left'
        );
        $this->db->join(
            'localizacoes l',
            'l.codigo = d.localizacao_codigo',
            'left'
        );
        $this->db->join(
            '(
                SELECT
                    documento_codigo,
                    COUNT(DISTINCT COALESCE(arquivo_raiz_codigo, codigo)) AS total_arquivos,
                    COUNT(*) AS total_versoes,
                    SUM(COALESCE(tamanho, 0)) AS tamanho_total,
                    MAX(cadastro) AS ultimo_arquivo_em
                FROM documento_arquivos
                WHERE exclusao IS NULL
                GROUP BY documento_codigo
            ) arq',
            'arq.documento_codigo = d.codigo',
            'left',
            FALSE
        );
        $this->db->where(
            'd.exclusao IS NULL',
            NULL,
            FALSE
        );
    }

    private function aplicar_filtros_digitalizacao($filtros)
    {
        if ($filtros['termo'] !== '') {
            $this->db->group_start();
            $this->db->like('d.protocolo', $filtros['termo']);
            $this->db->or_like('d.titulo', $filtros['termo']);
            $this->db->or_like(
                'd.numero_identificacao',
                $filtros['termo']
            );
            $this->db->or_like('td.nome', $filtros['termo']);
            $this->db->or_like('l.nome', $filtros['termo']);
            $this->db->or_like(
                'l.classificacao',
                $filtros['termo']
            );
            $this->db->group_end();
        }

        if ($filtros['tipo_documento_codigo'] !== '') {
            $this->db->where(
                'd.tipo_documento_codigo',
                (int) $filtros['tipo_documento_codigo']
            );
        }

        if ($filtros['localizacao_codigo'] !== '') {
            $this->db->where(
                'd.localizacao_codigo',
                (int) $filtros['localizacao_codigo']
            );
        }

        if ($filtros['data_inicio'] !== '') {
            $this->db->where(
                'd.cadastro >=',
                $filtros['data_inicio'] . ' 00:00:00'
            );
        }

        if ($filtros['data_fim'] !== '') {
            $this->db->where(
                'd.cadastro <=',
                $filtros['data_fim'] . ' 23:59:59'
            );
        }

        if ($filtros['situacao'] === 'com_arquivo') {
            $this->db->where(
                'COALESCE(arq.total_arquivos, 0) >',
                0,
                FALSE
            );
        } elseif ($filtros['situacao'] === 'sem_arquivo') {
            $this->db->where(
                'COALESCE(arq.total_arquivos, 0) =',
                0,
                FALSE
            );
        } elseif (
            $filtros['situacao'] === 'multiplas_versoes'
        ) {
            $this->db->where(
                'COALESCE(arq.total_versoes, 0) > COALESCE(arq.total_arquivos, 0)',
                NULL,
                FALSE
            );
        }
    }
}
