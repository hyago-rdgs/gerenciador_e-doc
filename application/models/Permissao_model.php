<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Permissao_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar_todas()
    {
        $this->db->select([
            'pe.codigo',
            'pe.nome',
            'pe.chave',
            'pe.descricao',
            'm.codigo AS modulo_codigo',
            'm.nome AS modulo_nome',
            'm.chave AS modulo_chave'
        ]);
        $this->db->from('permissoes pe');
        $this->db->join(
            'modulos m',
            'm.codigo = pe.modulo_codigo',
            'INNER'
        );
        $this->db->where('pe.exclusao IS NULL', NULL, FALSE);
        $this->db->where('m.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('m.ordem', 'ASC');
        $this->db->order_by('m.nome', 'ASC');
        $this->db->order_by('pe.ordem', 'ASC');
        $this->db->order_by('pe.nome', 'ASC');

        return $this->db->get()->result_array();
    }

    public function listar_chaves_por_perfil($perfil_codigo)
    {
        $this->db->select('pe.chave');
        $this->db->from('perfil_permissoes pp');
        $this->db->join(
            'permissoes pe',
            'pe.codigo = pp.permissao_codigo',
            'INNER'
        );
        $this->db->join(
            'modulos m',
            'm.codigo = pe.modulo_codigo',
            'INNER'
        );
        $this->db->where('pp.perfil_codigo', $perfil_codigo);
        $this->db->where('pp.exclusao IS NULL', NULL, FALSE);
        $this->db->where('pe.exclusao IS NULL', NULL, FALSE);
        $this->db->where('m.exclusao IS NULL', NULL, FALSE);

        $registros = $this->db->get()->result_array();

        return array_column($registros, 'chave');
    }

    public function listar_codigos_validos($codigos)
    {
        if (empty($codigos)) {
            return [];
        }

        $this->db->select('pe.codigo');
        $this->db->from('permissoes pe');
        $this->db->join(
            'modulos m',
            'm.codigo = pe.modulo_codigo',
            'INNER'
        );
        $this->db->where_in('pe.codigo', $codigos);
        $this->db->where('pe.exclusao IS NULL', NULL, FALSE);
        $this->db->where('m.exclusao IS NULL', NULL, FALSE);

        return array_map(
            'intval',
            array_column($this->db->get()->result_array(), 'codigo')
        );
    }

    public function listar_chaves_por_codigos($codigos)
    {
        if (empty($codigos)) {
            return [];
        }

        $this->db->select('chave');
        $this->db->where_in('codigo', $codigos);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return array_column(
            $this->db->get('permissoes')->result_array(),
            'chave'
        );
    }

    public function perfil_possui($perfil_codigo, $chave)
    {
        $this->db->from('perfil_permissoes pp');
        $this->db->join(
            'permissoes pe',
            'pe.codigo = pp.permissao_codigo',
            'INNER'
        );
        $this->db->join(
            'modulos m',
            'm.codigo = pe.modulo_codigo',
            'INNER'
        );
        $this->db->where('pp.perfil_codigo', $perfil_codigo);
        $this->db->where('pe.chave', $chave);
        $this->db->where('pp.exclusao IS NULL', NULL, FALSE);
        $this->db->where('pe.exclusao IS NULL', NULL, FALSE);
        $this->db->where('m.exclusao IS NULL', NULL, FALSE);

        return $this->db->count_all_results() > 0;
    }
}
