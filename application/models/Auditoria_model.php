<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auditoria_model extends CI_Model
{
    private $tabela = 'auditorias';

    public function cadastrar($reg)
    {
        $reg['cadastro'] = date('Y-m-d H:i:s');

        if (!$this->db->insert($this->tabela, $reg)) {
            return FALSE;
        }

        return $this->db->insert_id();
    }
}
