<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Documento_arquivo_model extends CI_Model
{
    private $tabela = 'documento_arquivos';

    public function listar_por_documento($documento_codigo)
    {
        $this->db->where('documento_codigo', $documento_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('principal', 'DESC');
        $this->db->order_by('cadastro', 'DESC');
        return $this->db->get($this->tabela)->result_array();
    }

    public function buscar_por_codigo($codigo)
    {
        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        return $this->db->get($this->tabela, 1)->row_array();
    }

    public function cadastrar($reg)
    {
        $reg['cadastro'] = date('Y-m-d H:i:s');

        if (!$this->db->insert($this->tabela, $reg)) {
            return FALSE;
        }

        return $this->db->insert_id();
    }

    public function possui_arquivos($documento_codigo)
    {
        $this->db->where('documento_codigo', $documento_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        return $this->db->count_all_results($this->tabela) > 0;
    }

    public function definir_principal($documento_codigo, $arquivo_codigo)
    {
        $this->db->trans_begin();

        $this->db->where('documento_codigo', $documento_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        $this->db->update($this->tabela, ['principal' => 0]);

        $this->db->where('codigo', $arquivo_codigo);
        $this->db->where('documento_codigo', $documento_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        $this->db->update($this->tabela, ['principal' => 1]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return TRUE;
    }

    public function excluir($codigo)
    {
        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if (!$this->db->update($this->tabela, [
            'principal' => 0,
            'exclusao' => date('Y-m-d H:i:s')
        ])) {
            return FALSE;
        }

        return $this->db->affected_rows() > 0;
    }
    public function definir_novo_principal($documento_codigo)
    {
        $this->db->select('codigo');
        $this->db->where(
            'documento_codigo',
            $documento_codigo
        );
        $this->db->where(
            'exclusao IS NULL',
            NULL,
            FALSE
        );
        $this->db->order_by('cadastro', 'DESC');

        $arquivo = $this->db
            ->get($this->tabela, 1)
            ->row_array();

        if (!$arquivo) {
            return TRUE;
        }

        $this->db->where(
            'codigo',
            $arquivo['codigo']
        );

        return $this->db->update(
            $this->tabela,
            ['principal' => 1]
        );
    }

    public function excluir_por_documento($documento_codigo)
    {
        $this->db->where(
            'documento_codigo',
            $documento_codigo
        );
        $this->db->where(
            'exclusao IS NULL',
            NULL,
            FALSE
        );

        return $this->db->update(
            $this->tabela,
            [
                'principal' => 0,
                'exclusao' => date('Y-m-d H:i:s')
            ]
        );
    }

}
