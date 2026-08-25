<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usuario_model extends CI_Model
{
    private $tabela = 'usuarios';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function buscar_por_usuario($usuario)
    {
        $this->db->select([
            'codigo',
            'nome',
            'usuario',
            'email',
            'senha'
        ]);

        $this->db->from($this->tabela);
        $this->db->where('usuario', $usuario);
        $this->db->where('ativo', 1);
        $this->db->where('exclusao', NULL);
        $this->db->limit(1);

        return $this->db->get()->row_array();
    }
}
