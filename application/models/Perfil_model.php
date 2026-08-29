<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Perfil_model extends CI_Model
{
    private $tabela = 'perfis';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar_tudo()
    {
        $this->db->select([
            'p.codigo',
            'p.nome',
            'p.chave',
            'p.cadastro',
            'p.atualizacao',
            '(SELECT COUNT(*) FROM usuarios u
                WHERE u.perfil_codigo = p.codigo
                AND u.exclusao IS NULL) AS total_usuarios',
            '(SELECT COUNT(*) FROM perfil_permissoes pp
                WHERE pp.perfil_codigo = p.codigo
                AND pp.exclusao IS NULL) AS total_permissoes'
        ], FALSE);
        $this->db->from($this->tabela . ' p');
        $this->db->where('p.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('p.nome', 'ASC');

        return $this->db->get()->result_array();
    }

    public function listar_ativos()
    {
        $this->db->select([
            'codigo',
            'nome',
            'chave'
        ]);
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('nome', 'ASC');

        return $this->db->get($this->tabela)->result_array();
    }

    public function buscar_por_codigo($codigo)
    {
        $this->db->select([
            'codigo',
            'nome',
            'chave',
            'cadastro',
            'atualizacao'
        ]);
        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->get($this->tabela, 1)->row_array();
    }

    public function chave_em_uso($chave, $codigo = NULL)
    {
        $this->db->where('chave', $chave);

        if ($codigo !== NULL) {
            $this->db->where('codigo !=', $codigo);
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

    public function possui_usuarios($codigo)
    {
        $this->db->where('perfil_codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->count_all_results('usuarios') > 0;
    }

    public function excluir($codigo)
    {
        $dados = [
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
