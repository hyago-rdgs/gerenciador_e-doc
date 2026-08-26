<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Localizacao_tipo_documento_model extends CI_Model
{
    private $tabela = 'localizacao_tipo_documentos';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar_por_localizacao($localizacao_codigo)
    {
        $this->db->select([
            'ltd.localizacao_codigo',
            'ltd.tipo_documento_codigo',
            'td.nome AS tipo_documento',
            'td.ativo AS tipo_documento_ativo'
        ]);

        $this->db->from($this->tabela . ' ltd');
        $this->db->join(
            'tipos_documento td',
            'td.codigo = ltd.tipo_documento_codigo AND td.exclusao IS NULL',
            'inner',
            FALSE
        );

        $this->db->where('ltd.localizacao_codigo', $localizacao_codigo);
        $this->db->where('ltd.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('td.nome', 'ASC');

        $query = $this->db->get();
        return $query->result_array();
    }

    public function buscar_por_localizacao($localizacao_codigo)
    {
        $this->db->select([
            'ltd.localizacao_codigo',
            'ltd.tipo_documento_codigo',
            'td.nome AS tipo_documento',
            'td.ativo AS tipo_documento_ativo'
        ]);

        $this->db->from($this->tabela . ' ltd');
        $this->db->join(
            'tipos_documento td',
            'td.codigo = ltd.tipo_documento_codigo AND td.exclusao IS NULL',
            'inner',
            FALSE
        );

        $this->db->where('ltd.localizacao_codigo', $localizacao_codigo);
        $this->db->where('ltd.exclusao IS NULL', NULL, FALSE);
        $this->db->limit(1);

        $query = $this->db->get();
        return $query->row_array();
    }

    public function buscar_vinculo(
        $localizacao_codigo,
        $tipo_documento_codigo,
        $incluir_excluidos = FALSE
    ) {
        $this->db->where('localizacao_codigo', $localizacao_codigo);
        $this->db->where('tipo_documento_codigo', $tipo_documento_codigo);

        if (!$incluir_excluidos) {
            $this->db->where('exclusao IS NULL', NULL, FALSE);
        }

        $query = $this->db->get($this->tabela, 1);
        return $query->row_array();
    }

    public function vincular(
        $localizacao_codigo,
        $tipo_documento_codigo
    ) {
        $vinculo = $this->buscar_vinculo(
            $localizacao_codigo,
            $tipo_documento_codigo,
            TRUE
        );

        $dados = [
            'localizacao_codigo' => $localizacao_codigo,
            'tipo_documento_codigo' => $tipo_documento_codigo
        ];

        if ($vinculo) {
            $dados['atualizacao'] = date('Y-m-d H:i:s');
            $dados['exclusao'] = NULL;

            $this->db->where('localizacao_codigo', $localizacao_codigo);
            $this->db->where('tipo_documento_codigo', $tipo_documento_codigo);

            return $this->db->update($this->tabela, $dados);
        }

        $dados['cadastro'] = date('Y-m-d H:i:s');

        return $this->db->insert($this->tabela, $dados);
    }

    public function desvincular_por_localizacao($localizacao_codigo)
    {
        $dados = [
            'atualizacao' => date('Y-m-d H:i:s'),
            'exclusao' => date('Y-m-d H:i:s')
        ];

        $this->db->where('localizacao_codigo', $localizacao_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->update($this->tabela, $dados);
    }

    public function salvar_tipo_unico(
        $localizacao_codigo,
        $tipo_documento_codigo = NULL
    ) {
        if (!$this->desvincular_por_localizacao($localizacao_codigo)) {
            return FALSE;
        }

        if ($tipo_documento_codigo === NULL) {
            return TRUE;
        }

        return $this->vincular(
            $localizacao_codigo,
            $tipo_documento_codigo
        );
    }

    public function possui_localizacoes($tipo_documento_codigo)
    {
        $this->db->where('tipo_documento_codigo', $tipo_documento_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->count_all_results($this->tabela) > 0;
    }
}
