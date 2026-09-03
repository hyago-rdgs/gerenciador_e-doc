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
}
