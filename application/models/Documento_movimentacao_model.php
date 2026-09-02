<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Documento_movimentacao_model extends CI_Model
{
    private $tabela = 'documento_movimentacoes';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar_tudo(
        $termo = '',
        $tipo = '',
        $situacao = '',
        $data_inicio = '',
        $data_fim = '',
        $limite = NULL,
        $offset = NULL
    ) {
        $this->preparar_consulta_listagem();
        $this->aplicar_filtros($termo, $tipo, $situacao, $data_inicio, $data_fim);
        $this->db->order_by('dm.data_movimentacao', 'DESC');
        $this->db->order_by('dm.codigo', 'DESC');

        if ($limite !== NULL) {
            $this->db->limit($limite, $offset);
        }

        return $this->db->get()->result_array();
    }

    public function contar_tudo(
        $termo = '',
        $tipo = '',
        $situacao = '',
        $data_inicio = '',
        $data_fim = ''
    ) {
        $this->db->select('dm.codigo');
        $this->db->from($this->tabela . ' dm');
        $this->db->join('documentos d', 'd.codigo = dm.documento_codigo', 'INNER');
        $this->aplicar_filtros($termo, $tipo, $situacao, $data_inicio, $data_fim);

        return $this->db->get()->num_rows();
    }

    public function listar_por_documento($documento_codigo)
    {
        $this->preparar_consulta_listagem();
        $this->db->where('dm.documento_codigo', $documento_codigo);
        $this->db->order_by('dm.data_movimentacao', 'DESC');
        $this->db->order_by('dm.codigo', 'DESC');

        return $this->db->get()->result_array();
    }

    public function buscar_por_codigo($codigo)
    {
        $this->preparar_consulta_listagem();
        $this->db->where('dm.codigo', $codigo);
        $this->db->limit(1);

        return $this->db->get()->row_array();
    }

    public function buscar_retirada_aberta($documento_codigo, $bloquear = FALSE)
    {
        if ($bloquear) {
            return $this->db->query(
                'SELECT *
                    FROM `documento_movimentacoes`
                    WHERE `documento_codigo` = ?
                        AND `tipo_movimentacao` = ?
                        AND `data_devolucao` IS NULL
                    ORDER BY `codigo` DESC
                    LIMIT 1
                    FOR UPDATE',
                [(int) $documento_codigo, 'RETIRADA']
            )->row_array();
        }

        $this->db->where('documento_codigo', $documento_codigo);
        $this->db->where('tipo_movimentacao', 'RETIRADA');
        $this->db->where('data_devolucao IS NULL', NULL, FALSE);
        $this->db->order_by('codigo', 'DESC');
        $this->db->limit(1);

        return $this->db->get($this->tabela)->row_array();
    }

    public function cadastrar($reg)
    {
        $reg['data_movimentacao'] = date('Y-m-d H:i:s');

        if (!$this->db->insert($this->tabela, $reg)) {
            return FALSE;
        }

        $codigo = $this->db->insert_id();
        $protocolo = gerar_protocolo('MOV', $codigo, $reg['data_movimentacao']);

        if (!$protocolo) {
            return FALSE;
        }

        $this->db->where('codigo', $codigo);

        if (!$this->db->update($this->tabela, ['protocolo' => $protocolo])) {
            return FALSE;
        }

        return $codigo;
    }

    public function registrar_devolucao($codigo, $data_devolucao)
    {
        $this->db->where('codigo', $codigo);
        $this->db->where('tipo_movimentacao', 'RETIRADA');
        $this->db->where('data_devolucao IS NULL', NULL, FALSE);

        if (!$this->db->update($this->tabela, ['data_devolucao' => $data_devolucao])) {
            return FALSE;
        }

        return $this->db->affected_rows() > 0;
    }

    private function preparar_consulta_listagem()
    {
        $this->db->select([
            'dm.*',
            'd.protocolo AS documento_protocolo',
            'd.titulo AS documento_titulo',
            'd.exclusao AS documento_exclusao',
            'lo.nome AS localizacao_origem',
            'lo.classificacao AS localizacao_origem_classificacao',
            'ld.nome AS localizacao_destino',
            'ld.classificacao AS localizacao_destino_classificacao',
            'u.nome AS usuario_nome'
        ]);
        $this->db->from($this->tabela . ' dm');
        $this->db->join('documentos d', 'd.codigo = dm.documento_codigo', 'INNER');
        $this->db->join('localizacoes lo', 'lo.codigo = dm.localizacao_origem_codigo', 'LEFT');
        $this->db->join('localizacoes ld', 'ld.codigo = dm.localizacao_destino_codigo', 'LEFT');
        $this->db->join('usuarios u', 'u.codigo = dm.usuario_codigo', 'LEFT');
    }

    private function aplicar_filtros($termo, $tipo, $situacao, $data_inicio, $data_fim)
    {
        if ($termo !== '') {
            $this->db->group_start();
            $this->db->like('dm.protocolo', $termo);
            $this->db->or_like('d.protocolo', $termo);
            $this->db->or_like('d.titulo', $termo);
            $this->db->or_like('dm.responsavel_nome', $termo);
            $this->db->group_end();
        }

        if ($tipo !== '') {
            $this->db->where('dm.tipo_movimentacao', $tipo);
        }

        if ($situacao === 'aberta') {
            $this->db->where('dm.tipo_movimentacao', 'RETIRADA');
            $this->db->where('dm.data_devolucao IS NULL', NULL, FALSE);
        } elseif ($situacao === 'atrasada') {
            $this->db->where('dm.tipo_movimentacao', 'RETIRADA');
            $this->db->where('dm.data_devolucao IS NULL', NULL, FALSE);
            $this->db->where('dm.data_prevista_devolucao <', date('Y-m-d'));
        } elseif ($situacao === 'concluida') {
            $this->db->group_start();
            $this->db->where('dm.tipo_movimentacao !=', 'RETIRADA');
            $this->db->or_where('dm.data_devolucao IS NOT NULL', NULL, FALSE);
            $this->db->group_end();
        }

        if ($data_inicio !== '') {
            $this->db->where('dm.data_movimentacao >=', $data_inicio . ' 00:00:00');
        }

        if ($data_fim !== '') {
            $this->db->where('dm.data_movimentacao <=', $data_fim . ' 23:59:59');
        }
    }
}
