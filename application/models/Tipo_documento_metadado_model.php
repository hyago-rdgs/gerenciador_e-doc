<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tipo_documento_metadado_model extends CI_Model
{
    private $tabela = 'tipo_documento_metadados';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar_por_tipo_documento($tipo_documento_codigo)
    {
        $this->db->select([
            'tdm.tipo_documento_codigo',
            'tdm.metadado_codigo',
            'tdm.ordem',
            'tdm.obrigatorio',
            'tdm.visivel',
            'tdm.pesquisavel',
            'm.nome',
            'm.descricao',
            'm.tipo_campo',
            'm.ativo'
        ]);

        $this->db->from($this->tabela . ' tdm');
        $this->db->join(
            'metadados m',
            'm.codigo = tdm.metadado_codigo AND m.exclusao IS NULL',
            'inner',
            FALSE
        );

        $this->db->where(
            'tdm.tipo_documento_codigo',
            $tipo_documento_codigo
        );
        $this->db->where('tdm.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('tdm.ordem', 'ASC');
        $this->db->order_by('m.nome', 'ASC');

        $query = $this->db->get();
        return $query->result_array();
    }

    public function listar_disponiveis($tipo_documento_codigo)
    {
        $tipo_documento_codigo = (int) $tipo_documento_codigo;

        $this->db->select([
            'm.codigo',
            'm.nome',
            'm.tipo_campo'
        ]);

        $this->db->from('metadados m');
        $this->db->join(
            $this->tabela . ' tdm',
            'tdm.metadado_codigo = m.codigo'
                . ' AND tdm.tipo_documento_codigo = '
                . $tipo_documento_codigo
                . ' AND tdm.exclusao IS NULL',
            'left',
            FALSE
        );

        $this->db->where('m.ativo', 1);
        $this->db->where('m.exclusao IS NULL', NULL, FALSE);
        $this->db->where('tdm.metadado_codigo IS NULL', NULL, FALSE);
        $this->db->order_by('m.nome', 'ASC');

        $query = $this->db->get();
        return $query->result_array();
    }

    public function buscar_vinculo(
        $tipo_documento_codigo,
        $metadado_codigo,
        $incluir_excluidos = FALSE
    ) {
        $this->db->where(
            'tipo_documento_codigo',
            $tipo_documento_codigo
        );
        $this->db->where('metadado_codigo', $metadado_codigo);

        if (!$incluir_excluidos) {
            $this->db->where('exclusao IS NULL', NULL, FALSE);
        }

        $query = $this->db->get($this->tabela, 1);
        return $query->row_array();
    }

    public function vincular($reg)
    {
        $vinculo = $this->buscar_vinculo(
            $reg['tipo_documento_codigo'],
            $reg['metadado_codigo'],
            TRUE
        );

        if ($vinculo) {
            $reg['atualizacao'] = date('Y-m-d H:i:s');
            $reg['exclusao'] = NULL;

            $this->db->where(
                'tipo_documento_codigo',
                $reg['tipo_documento_codigo']
            );
            $this->db->where(
                'metadado_codigo',
                $reg['metadado_codigo']
            );

            return $this->db->update($this->tabela, $reg);
        }

        $reg['cadastro'] = date('Y-m-d H:i:s');

        return $this->db->insert($this->tabela, $reg);
    }

    public function atualizar(
        $tipo_documento_codigo,
        $metadado_codigo,
        $reg
    ) {
        $reg['atualizacao'] = date('Y-m-d H:i:s');

        $this->db->where(
            'tipo_documento_codigo',
            $tipo_documento_codigo
        );
        $this->db->where('metadado_codigo', $metadado_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->update($this->tabela, $reg);
    }

    public function desvincular(
        $tipo_documento_codigo,
        $metadado_codigo
    ) {
        $dados = [
            'atualizacao' => date('Y-m-d H:i:s'),
            'exclusao' => date('Y-m-d H:i:s')
        ];

        $this->db->where(
            'tipo_documento_codigo',
            $tipo_documento_codigo
        );
        $this->db->where('metadado_codigo', $metadado_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if (!$this->db->update($this->tabela, $dados)) {
            return FALSE;
        }

        return $this->db->affected_rows() > 0;
    }

    public function excluir_por_tipo_documento($tipo_documento_codigo)
    {
        $dados = [
            'atualizacao' => date('Y-m-d H:i:s'),
            'exclusao' => date('Y-m-d H:i:s')
        ];

        $this->db->where(
            'tipo_documento_codigo',
            $tipo_documento_codigo
        );
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->update($this->tabela, $dados);
    }

    public function obter_proxima_ordem($tipo_documento_codigo)
    {
        $this->db->select(
            'COALESCE(MAX(ordem), 0) + 1 AS proxima_ordem',
            FALSE
        );

        $this->db->where(
            'tipo_documento_codigo',
            $tipo_documento_codigo
        );
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        $query = $this->db->get($this->tabela);
        $resultado = $query->row_array();

        return (int) $resultado['proxima_ordem'];
    }
}
