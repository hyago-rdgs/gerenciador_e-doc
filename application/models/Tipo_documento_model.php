<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tipo_documento_model extends CI_Model
{
    private $tabela = 'tipos_documento';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar_tudo(
        $termo = '',
        $status = '',
        $limite = NULL,
        $offset = NULL
    ) {
        $this->preparar_consulta_listagem();

        if (!empty($termo)) {
            $this->db->group_start();
            $this->db->like('td.nome', $termo);
            $this->db->or_like('td.descricao', $termo);
            $this->db->group_end();
        }

        if (!empty($status)) {
            if ($status == 'ativo') {
                $this->db->where('td.ativo', 1);
            } else {
                $this->db->where('td.ativo', 0);
            }
        }

        $this->db->where('td.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('td.nome', 'ASC');

        if ($limite !== NULL) {
            $this->db->limit($limite, $offset);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    public function contar_tudo($termo = '', $status = '')
    {
        $this->db->select('td.codigo');
        $this->db->from($this->tabela . ' td');

        if (!empty($termo)) {
            $this->db->group_start();
            $this->db->like('td.nome', $termo);
            $this->db->or_like('td.descricao', $termo);
            $this->db->group_end();
        }

        if (!empty($status)) {
            if ($status == 'ativo') {
                $this->db->where('td.ativo', 1);
            } else {
                $this->db->where('td.ativo', 0);
            }
        }

        $this->db->where('td.exclusao IS NULL', NULL, FALSE);

        $query = $this->db->get();
        return $query->num_rows();
    }

    public function listar_opcoes()
    {
        $this->db->select(['codigo', 'nome']);
        $this->db->where('ativo', 1);
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('nome', 'ASC');

        return $this->db->get($this->tabela)->result_array();
    }

    public function buscar_por_codigo($codigo)
    {
        $this->preparar_consulta_listagem();

        $this->db->where('td.codigo', $codigo);
        $this->db->where('td.exclusao IS NULL', NULL, FALSE);

        $this->db->limit(1);

        $query = $this->db->get();
        return $query->row_array();
    }

    public function cadastrar($reg)
    {
        $reg['cadastro'] = date('Y-m-d H:i:s');

        if (!$this->db->insert($this->tabela, $reg)) {
            return FALSE;
        }

        return $this->db->insert_id();
    }

    public function atualizar($codigo, $reg)
    {
        $reg['atualizacao'] = date('Y-m-d H:i:s');

        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->update($this->tabela, $reg);
    }

    public function excluir($codigo)
    {
        $dados = [
            'ativo' => 0,
            'atualizacao' => date('Y-m-d H:i:s'),
            'exclusao' => date('Y-m-d H:i:s')
        ];

        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if (!$this->db->update($this->tabela, $dados)) {
            return FALSE;
        }

        return $this->db->affected_rows() > 0;
    }

    public function possui_documentos($codigo)
    {
        $this->db->where('tipo_documento_codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->count_all_results('documentos') > 0;
    }

    private function preparar_consulta_listagem()
    {
        $this->db->select([
            'td.codigo',
            'td.nome',
            'td.descricao',
            'td.ativo',
            'td.cadastro',
            'td.atualizacao',
            'COALESCE(mt.total_metadados, 0) AS total_metadados',
            'COALESCE(dt.total_documentos, 0) AS total_documentos'
        ], FALSE);

        $this->db->from($this->tabela . ' td');

        $this->db->join(
            '(
                SELECT
                    tipo_documento_codigo,
                    COUNT(*) AS total_metadados
                FROM tipo_documento_metadados
                WHERE exclusao IS NULL
                GROUP BY tipo_documento_codigo
            ) mt',
            'mt.tipo_documento_codigo = td.codigo',
            'left',
            FALSE
        );

        $this->db->join(
            '(
                SELECT
                    tipo_documento_codigo,
                    COUNT(*) AS total_documentos
                FROM documentos
                WHERE exclusao IS NULL
                GROUP BY tipo_documento_codigo
            ) dt',
            'dt.tipo_documento_codigo = td.codigo',
            'left',
            FALSE
        );
    }
}
