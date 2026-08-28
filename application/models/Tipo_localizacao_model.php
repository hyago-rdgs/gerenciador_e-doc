<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tipo_localizacao_model extends CI_Model
{
    private $tabela = 'tipos_localizacao';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar()
    {
        $this->db->select('tl.*');
        $this->db->from($this->tabela . ' tl');
        $this->db->where('tl.exclusao IS NULL', NULL, FALSE);

        $query = $this->db->get();
        return $query->result_array();
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

        $query = $this->db->get($this->tabela, 1);
        return $query->row_array();
    }
}