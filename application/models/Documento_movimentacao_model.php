<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Documento_movimentacao_model extends CI_Model
{
    private $tabela = 'documento_movimentacoes';

    public function listar_por_documento($documento_codigo)
    {
        $this->db->select([
            'dm.*',
            'lo.nome AS localizacao_origem',
            'ld.nome AS localizacao_destino'
        ]);
        $this->db->from($this->tabela . ' dm');
        $this->db->join('localizacoes lo', 'lo.codigo = dm.localizacao_origem_codigo', 'left');
        $this->db->join('localizacoes ld', 'ld.codigo = dm.localizacao_destino_codigo', 'left');
        $this->db->where('dm.documento_codigo', $documento_codigo);
        $this->db->order_by('dm.data_movimentacao', 'DESC');
        return $this->db->get()->result_array();
    }

    public function cadastrar($reg)
    {
        $reg['data_movimentacao'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->tabela, $reg);
    }
}
