<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auditoria_model extends CI_Model
{
    private $tabela = 'auditorias';

    public function listar_tudo(
        $termo = '',
        $modulo = '',
        $acao = '',
        $usuario_codigo = '',
        $data_inicio = '',
        $data_fim = '',
        $limite = NULL,
        $offset = NULL
    ) {
        $this->db->select([
            'a.codigo',
            'a.usuario_codigo',
            'a.modulo',
            'a.acao',
            'a.entidade',
            'a.entidade_codigo',
            'a.endereco_ip',
            'a.cadastro',
            'u.nome AS usuario_nome',
            'u.usuario AS usuario_login'
        ]);

        $this->preparar_consulta(
            $termo,
            $modulo,
            $acao,
            $usuario_codigo,
            $data_inicio,
            $data_fim
        );

        $this->db->order_by('a.codigo', 'DESC');

        if ($limite !== NULL) {
            $this->db->limit($limite, $offset);
        }

        return $this->db->get()->result_array();
    }

    public function contar_tudo(
        $termo = '',
        $modulo = '',
        $acao = '',
        $usuario_codigo = '',
        $data_inicio = '',
        $data_fim = ''
    ) {
        $this->preparar_consulta(
            $termo,
            $modulo,
            $acao,
            $usuario_codigo,
            $data_inicio,
            $data_fim
        );

        return $this->db->count_all_results();
    }

    public function buscar_por_codigo($codigo)
    {
        $this->db->select([
            'a.*',
            'u.nome AS usuario_nome',
            'u.usuario AS usuario_login'
        ]);
        $this->db->from($this->tabela . ' a');
        $this->db->join(
            'usuarios u',
            'u.codigo = a.usuario_codigo',
            'left'
        );
        $this->db->where('a.codigo', (int) $codigo);
        $this->db->limit(1);

        return $this->db->get()->row_array();
    }

    public function listar_modulos()
    {
        $this->db->distinct();
        $this->db->select('modulo');
        $this->db->from($this->tabela);
        $this->db->order_by('modulo', 'ASC');

        return array_column(
            $this->db->get()->result_array(),
            'modulo'
        );
    }

    public function listar_acoes()
    {
        $this->db->distinct();
        $this->db->select('acao');
        $this->db->from($this->tabela);
        $this->db->order_by('acao', 'ASC');

        return array_column(
            $this->db->get()->result_array(),
            'acao'
        );
    }

    public function listar_usuarios()
    {
        $this->db->distinct();
        $this->db->select([
            'u.codigo',
            'u.nome',
            'u.usuario'
        ]);
        $this->db->from($this->tabela . ' a');
        $this->db->join(
            'usuarios u',
            'u.codigo = a.usuario_codigo',
            'inner'
        );
        $this->db->order_by('u.nome', 'ASC');

        return $this->db->get()->result_array();
    }

    public function cadastrar($reg)
    {
        $reg['cadastro'] = date('Y-m-d H:i:s');

        if (!$this->db->insert($this->tabela, $reg)) {
            return FALSE;
        }

        return $this->db->insert_id();
    }

    private function preparar_consulta(
        $termo,
        $modulo,
        $acao,
        $usuario_codigo,
        $data_inicio,
        $data_fim
    ) {
        $this->db->from($this->tabela . ' a');
        $this->db->join(
            'usuarios u',
            'u.codigo = a.usuario_codigo',
            'left'
        );

        if ($termo !== '') {
            $this->db->group_start();
            $this->db->like('a.entidade', $termo);
            $this->db->or_like('a.acao', $termo);
            $this->db->or_like('a.modulo', $termo);
            $this->db->or_like('u.nome', $termo);
            $this->db->or_like('u.usuario', $termo);

            if (ctype_digit((string) $termo)) {
                $this->db->or_where(
                    'a.entidade_codigo',
                    (int) $termo
                );
            }

            $this->db->group_end();
        }

        if ($modulo !== '') {
            $this->db->where('a.modulo', $modulo);
        }

        if ($acao !== '') {
            $this->db->where('a.acao', $acao);
        }

        if ($usuario_codigo === 'sistema') {
            $this->db->where(
                'a.usuario_codigo IS NULL',
                NULL,
                FALSE
            );
        } elseif ($usuario_codigo !== '') {
            $this->db->where(
                'a.usuario_codigo',
                (int) $usuario_codigo
            );
        }

        if ($data_inicio !== '') {
            $this->db->where(
                'a.cadastro >=',
                $data_inicio . ' 00:00:00'
            );
        }

        if ($data_fim !== '') {
            $this->db->where(
                'a.cadastro <=',
                $data_fim . ' 23:59:59'
            );
        }
    }
}
