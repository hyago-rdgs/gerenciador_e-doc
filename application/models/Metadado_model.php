<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Metadado_model extends CI_Model
{
    private $tabela = 'metadados';
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar_tudo($termo = '', $status = '', $tipo_campo = '', $limite = NULL, $offset = NULL)
    {
        $this->db->select('m.*');
        $this->db->from($this->tabela . ' m');

        if (!empty($termo)) {
            $this->db->like('m.nome', $termo);
        }

        if (!empty($status)) {
            if ($status == 'ativo') {
                $this->db->where('ativo', 1);
            } else {
                $this->db->where('ativo', 0);
            }
        }

        if (!empty($tipo_campo)) {
            $this->db->where('m.tipo_campo', $tipo_campo);
        }

        $this->db->where('m.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('m.nome', 'ASC');

        if ($limite !== NULL) {
            $this->db->limit($limite, $offset);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    public function contar_tudo($termo = '', $status = '', $tipo_campo = '')
    {

        $this->db->select('m.codigo');
        $this->db->from($this->tabela . ' m');

        if (!empty($termo)) {
            $this->db->like('m.nome', $termo);
        }

        if (!empty($status)) {
            if ($status == 'ativo') {
                $this->db->where('ativo', 1);
            } else {
                $this->db->where('ativo', 0);
            }
        }

        if (!empty($tipo_campo)) {
            $this->db->where('m.tipo_campo', $tipo_campo);
        }

        $this->db->where('m.exclusao IS NULL', NULL, FALSE);

        $query = $this->db->get();
        return $query->num_rows();
    }

    public function buscar_por_codigo($codigo)
    {
        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        $query = $this->db->get($this->tabela, 1);
        return $query->row_array();
    }

    public function buscar_por_chave($chave)
    {
        $chave = trim((string) $chave);

        if ($chave === '') {
            return FALSE;
        }

        $this->db->where('chave', $chave);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->get($this->tabela, 1)->row_array();
    }

    public function chave_em_uso($chave, $codigo = NULL)
    {
        $this->db->where('chave', $chave);

        if ($codigo !== NULL) {
            $this->db->where('codigo !=', (int) $codigo);
        }

        return $this->db->count_all_results($this->tabela) > 0;
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
}
