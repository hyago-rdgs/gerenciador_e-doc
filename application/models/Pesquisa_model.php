<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pesquisa_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar_metadados_pesquisa($tipo_documento_codigo)
    {
        $this->db->select([
            'tdm.metadado_codigo',
            'tdm.ordem',
            'm.nome',
            'm.descricao',
            'm.tipo_campo',
            'm.mascara',
            'm.opcoes'
        ]);
        $this->db->from('tipo_documento_metadados tdm');
        $this->db->join(
            'metadados m',
            'm.codigo = tdm.metadado_codigo'
                . ' AND m.ativo = 1'
                . ' AND m.exclusao IS NULL',
            'inner',
            FALSE
        );
        $this->db->where(
            'tdm.tipo_documento_codigo',
            $tipo_documento_codigo
        );
        $this->db->where('tdm.pesquisavel', 1);
        $this->db->where('tdm.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('tdm.ordem', 'ASC');
        $this->db->order_by('m.nome', 'ASC');

        return $this->db->get()->result_array();
    }

    public function listar_avancada(
        $tipo_documento_codigo,
        $titulo = '',
        $numero_identificacao = '',
        $data_inicio = '',
        $data_fim = '',
        $status = '',
        $metadados = [],
        $limite = NULL,
        $offset = NULL
    ) {
        $this->preparar_consulta_documentos();

        $this->aplicar_filtros(
            $tipo_documento_codigo,
            $titulo,
            $numero_identificacao,
            $data_inicio,
            $data_fim,
            $status,
            $metadados
        );

        $this->db->order_by('d.titulo', 'ASC');

        if ($limite !== NULL) {
            $this->db->limit($limite, $offset);
        }

        return $this->db->get()->result_array();
    }

    public function contar_avancada(
        $tipo_documento_codigo,
        $titulo = '',
        $numero_identificacao = '',
        $data_inicio = '',
        $data_fim = '',
        $status = '',
        $metadados = []
    ) {
        $this->db->select('d.codigo');
        $this->db->from('documentos d');

        $this->aplicar_filtros(
            $tipo_documento_codigo,
            $titulo,
            $numero_identificacao,
            $data_inicio,
            $data_fim,
            $status,
            $metadados
        );

        return $this->db->get()->num_rows();
    }

    public function listar_documentos_localizacao($localizacao_codigo)
    {
        $this->preparar_consulta_documentos();
        $this->db->where('d.localizacao_codigo', $localizacao_codigo);
        $this->db->where('d.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('d.titulo', 'ASC');

        return $this->db->get()->result_array();
    }

    private function preparar_consulta_documentos()
    {
        $this->db->select([
            'd.codigo',
            'd.titulo',
            'd.descricao',
            'd.numero_identificacao',
            'd.data_documento',
            'd.ativo',
            'td.nome AS tipo_documento',
            'l.codigo AS localizacao_codigo',
            'l.nome AS localizacao',
            'l.classificacao AS localizacao_classificacao'
        ]);
        $this->db->from('documentos d');
        $this->db->join(
            'tipos_documento td',
            'td.codigo = d.tipo_documento_codigo'
                . ' AND td.exclusao IS NULL',
            'inner',
            FALSE
        );
        $this->db->join(
            'localizacoes l',
            'l.codigo = d.localizacao_codigo'
                . ' AND l.exclusao IS NULL',
            'inner',
            FALSE
        );
    }

    private function aplicar_filtros(
        $tipo_documento_codigo,
        $titulo,
        $numero_identificacao,
        $data_inicio,
        $data_fim,
        $status,
        $metadados
    ) {
        $this->db->where(
            'd.tipo_documento_codigo',
            $tipo_documento_codigo
        );

        if (!empty($titulo)) {
            $this->db->like('d.titulo', $titulo);
        }

        if (!empty($numero_identificacao)) {
            $this->db->like(
                'd.numero_identificacao',
                $numero_identificacao
            );
        }

        if (!empty($data_inicio)) {
            $this->db->where('d.data_documento >=', $data_inicio);
        }

        if (!empty($data_fim)) {
            $this->db->where('d.data_documento <=', $data_fim);
        }

        if (!empty($status)) {
            $this->db->where(
                'd.ativo',
                $status == 'ativo' ? 1 : 0
            );
        }

        $this->db->where('d.exclusao IS NULL', NULL, FALSE);

        foreach ($metadados as $metadado) {
            $valores = is_array($metadado['valor'])
                ? $metadado['valor']
                : [$metadado['valor']];

            foreach ($valores as $valor) {
                $comparacao = $this->comparacao_metadado(
                    $metadado['tipo_campo'],
                    $valor
                );

                $this->db->where(
                    'EXISTS (
                        SELECT 1
                        FROM documento_metadados dm
                        WHERE dm.documento_codigo = d.codigo
                        AND dm.metadado_codigo = '
                            . (int) $metadado['codigo'] .
                        ' AND dm.exclusao IS NULL
                        AND ' . $comparacao . '
                    )',
                    NULL,
                    FALSE
                );
            }
        }
    }

    private function comparacao_metadado($tipo_campo, $valor)
    {
        $tipos_exatos = [
            'number',
            'date',
            'time',
            'datetime-local',
            'select',
            'radio',
            'email',
            'tel',
            'url'
        ];

        if ($tipo_campo === 'checkbox') {
            $valor_json = json_encode(
                (string) $valor,
                JSON_UNESCAPED_UNICODE
            );

            $valor_like = '%' .
                $this->db->escape_like_str($valor) .
                '%';

            return '(
                (
                    JSON_VALID(dm.valor) = 1
                    AND JSON_TYPE(dm.valor) = \'ARRAY\'
                    AND JSON_CONTAINS(
                        dm.valor,
                        ' . $this->db->escape($valor_json) . '
                    )
                )
                OR dm.valor = ' . $this->db->escape($valor) . '
                OR dm.valor LIKE ' . $this->db->escape($valor_like) . " ESCAPE '!'
            )";
        }

        if (in_array($tipo_campo, $tipos_exatos, TRUE)) {
            return 'dm.valor = ' . $this->db->escape($valor);
        }

        $valor = '%' . $this->db->escape_like_str($valor) . '%';

        return 'dm.valor LIKE '
            . $this->db->escape($valor)
            . " ESCAPE '!'";
    }
}
