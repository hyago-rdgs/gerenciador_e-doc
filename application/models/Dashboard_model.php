<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function obter_resumo()
    {
        $total_documentos = $this->contar_documentos();
        $documentos_com_arquivo = $this->contar_documentos_com_arquivo();

        return [
            'total_documentos' => $total_documentos,
            'documentos_mes' => $this->contar_documentos_mes(),
            'total_localizacoes' => $this->contar_localizacoes(),
            'movimentacoes_mes' => $this->contar_movimentacoes_mes(),
            'digitalizacao_percentual' => $total_documentos > 0
                ? round(($documentos_com_arquivo / $total_documentos) * 100, 1)
                : 0
        ];
    }

    public function listar_documentos_por_mes($meses = 12)
    {
        $meses = max(1, min(24, (int) $meses));

        $inicio = new DateTime('first day of -' . ($meses - 1) . ' months 00:00:00');
        $fim = new DateTime('first day of next month 00:00:00');

        $this->db->select([
            "DATE_FORMAT(cadastro, '%Y-%m') AS periodo",
            'COUNT(*) AS total'
        ], FALSE);
        $this->db->from('documentos');
        $this->db->where('cadastro >=', $inicio->format('Y-m-d H:i:s'));
        $this->db->where('cadastro <', $fim->format('Y-m-d H:i:s'));
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        $this->db->group_by("DATE_FORMAT(cadastro, '%Y-%m')", FALSE);
        $this->db->order_by('periodo', 'ASC');

        $registros = $this->db->get()->result_array();
        $totais = [];

        foreach ($registros as $registro) {
            $totais[$registro['periodo']] = (int) $registro['total'];
        }

        $resultado = [];

        for ($i = 0; $i < $meses; $i++) {
            $mes = clone $inicio;

            if ($i > 0) {
                $mes->modify('+' . $i . ' months');
            }

            $periodo = $mes->format('Y-m');

            $resultado[] = [
                'periodo' => $periodo,
                'rotulo' => $mes->format('m/Y'),
                'total' => $totais[$periodo] ?? 0
            ];
        }

        return $resultado;
    }

    public function listar_documentos_por_tipo($limite = 10)
    {
        $this->db->select([
            'td.codigo',
            "COALESCE(td.nome, 'Não disponível') AS nome",
            'COUNT(d.codigo) AS total'
        ], FALSE);
        $this->db->from('documentos d');
        $this->db->join(
            'tipos_documento td',
            'td.codigo = d.tipo_documento_codigo',
            'left'
        );
        $this->db->where('d.exclusao IS NULL', NULL, FALSE);
        $this->db->group_by(['td.codigo', 'td.nome']);
        $this->db->order_by('total', 'DESC');
        $this->db->order_by('td.nome', 'ASC');
        $this->db->limit(max(1, (int) $limite));

        return $this->db->get()->result_array();
    }

    public function listar_documentos_por_localizacao($limite = 10)
    {
        $this->db->select([
            'l.codigo',
            "COALESCE(l.nome, 'Não disponível') AS nome",
            "COALESCE(l.classificacao, '-') AS classificacao",
            'COUNT(d.codigo) AS total'
        ], FALSE);
        $this->db->from('documentos d');
        $this->db->join(
            'localizacoes l',
            'l.codigo = d.localizacao_codigo',
            'left'
        );
        $this->db->where('d.exclusao IS NULL', NULL, FALSE);
        $this->db->group_by(['l.codigo', 'l.nome', 'l.classificacao']);
        $this->db->order_by('total', 'DESC');
        $this->db->order_by('l.classificacao', 'ASC');
        $this->db->limit(max(1, (int) $limite));

        return $this->db->get()->result_array();
    }

    public function obter_digitalizacao()
    {
        $total = $this->contar_documentos();
        $com_arquivo = $this->contar_documentos_com_arquivo();

        return [
            'com_arquivo' => $com_arquivo,
            'sem_arquivo' => max(0, $total - $com_arquivo)
        ];
    }

    public function obter_atencoes()
    {
        return [
            'documentos_sem_arquivo' => max(
                0,
                $this->contar_documentos() - $this->contar_documentos_com_arquivo()
            ),
            'retiradas_abertas' => $this->contar_retiradas_abertas(),
            'retiradas_atrasadas' => $this->contar_retiradas_atrasadas()
        ];
    }

    public function listar_movimentacoes_recentes($limite = 8)
    {
        $this->db->select([
            'dm.codigo',
            'dm.protocolo',
            'dm.tipo_movimentacao',
            'dm.data_movimentacao',
            'dm.responsavel_nome',
            'd.codigo AS documento_codigo',
            'd.protocolo AS documento_protocolo',
            'd.titulo AS documento_titulo',
            'lo.nome AS localizacao_origem',
            'ld.nome AS localizacao_destino',
            'u.nome AS usuario_nome'
        ]);
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
        $this->db->where('d.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('dm.data_movimentacao', 'DESC');
        $this->db->order_by('dm.codigo', 'DESC');
        $this->db->limit(max(1, (int) $limite));

        return $this->db->get()->result_array();
    }

    private function contar_documentos()
    {
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        return (int) $this->db->count_all_results('documentos');
    }

    private function contar_documentos_mes()
    {
        $inicio = date('Y-m-01 00:00:00');
        $fim = date('Y-m-d H:i:s', strtotime('first day of next month'));

        $this->db->where('cadastro >=', $inicio);
        $this->db->where('cadastro <', $fim);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return (int) $this->db->count_all_results('documentos');
    }

    private function contar_documentos_com_arquivo()
    {
        $this->db->select('COUNT(DISTINCT d.codigo) AS total', FALSE);
        $this->db->from('documentos d');
        $this->db->join(
            'documento_arquivos da',
            'da.documento_codigo = d.codigo AND da.exclusao IS NULL',
            'inner',
            FALSE
        );
        $this->db->where('d.exclusao IS NULL', NULL, FALSE);

        $resultado = $this->db->get()->row_array();

        return (int) ($resultado['total'] ?? 0);
    }

    private function contar_localizacoes()
    {
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        return (int) $this->db->count_all_results('localizacoes');
    }

    private function contar_movimentacoes_mes()
    {
        $inicio = date('Y-m-01 00:00:00');
        $fim = date('Y-m-d H:i:s', strtotime('first day of next month'));

        $this->db->where('data_movimentacao >=', $inicio);
        $this->db->where('data_movimentacao <', $fim);

        return (int) $this->db->count_all_results('documento_movimentacoes');
    }

    private function contar_retiradas_abertas()
    {
        $this->db->where('tipo_movimentacao', 'RETIRADA');
        $this->db->where('data_devolucao IS NULL', NULL, FALSE);

        return (int) $this->db->count_all_results('documento_movimentacoes');
    }

    private function contar_retiradas_atrasadas()
    {
        $this->db->where('tipo_movimentacao', 'RETIRADA');
        $this->db->where('data_devolucao IS NULL', NULL, FALSE);
        $this->db->where('data_prevista_devolucao IS NOT NULL', NULL, FALSE);
        $this->db->where('data_prevista_devolucao <', date('Y-m-d'));

        return (int) $this->db->count_all_results('documento_movimentacoes');
    }
}
