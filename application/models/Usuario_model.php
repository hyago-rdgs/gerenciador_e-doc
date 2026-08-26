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

    public function listar_tudo($termo = '', $status = '', $limite = NULL, $offset = NULL)
    {
        $this->db->select([
            'u.codigo',
            'u.nome',
            'u.usuario',
            'u.email',
            'u.ativo',
            'u.cadastro',
            'u.atualizacao'
        ]);
        $this->db->from($this->tabela . ' u');

        if (!empty($termo)) {
            $this->db->group_start();
            $this->db->like('u.nome', $termo);
            $this->db->or_like('u.usuario', $termo);
            $this->db->or_like('u.email', $termo);
            $this->db->group_end();
        }

        if (!empty($status)) {
            if ($status == 'ativo') {
                $this->db->where('u.ativo', 1);
            } else {
                $this->db->where('u.ativo', 0);
            }
        }

        $this->db->where('u.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('u.nome', 'ASC');

        if ($limite !== NULL) {
            $this->db->limit($limite, $offset);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    public function contar_tudo($termo = '', $status = '')
    {
        $this->db->select('u.codigo');
        $this->db->from($this->tabela . ' u');

        if (!empty($termo)) {
            $this->db->group_start();
            $this->db->like('u.nome', $termo);
            $this->db->or_like('u.usuario', $termo);
            $this->db->or_like('u.email', $termo);
            $this->db->group_end();
        }

        if (!empty($status)) {
            if ($status == 'ativo') {
                $this->db->where('u.ativo', 1);
            } else {
                $this->db->where('u.ativo', 0);
            }
        }

        $this->db->where('u.exclusao IS NULL', NULL, FALSE);

        $query = $this->db->get();
        return $query->num_rows();
    }

    public function buscar_por_codigo($codigo)
    {
        $this->db->select([
            'codigo',
            'nome',
            'usuario',
            'email',
            'ativo',
            'cadastro',
            'atualizacao'
        ]);
        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        $query = $this->db->get($this->tabela, 1);
        return $query->row_array();
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

    public function usuario_em_uso($usuario, $codigo = NULL)
    {
        $this->db->where('usuario', $usuario);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if ($codigo !== NULL) {
            $this->db->where('codigo !=', $codigo);
        }

        return $this->db->count_all_results($this->tabela) > 0;
    }

    public function email_em_uso($email, $codigo = NULL)
    {
        $this->db->where('email', $email);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

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
