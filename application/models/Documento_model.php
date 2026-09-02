<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Documento_model extends CI_Model
{
    private $tabela = 'documentos';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar_tudo($termo = '', $tipo_documento_codigo = '', $localizacao_codigo = '', $status = '', $limite = NULL, $offset = NULL)
    {
        $this->preparar_consulta_listagem();

        if (!empty($termo)) {
            $this->db->group_start();
            $this->db->like('d.titulo', $termo);
            $this->db->or_like('d.numero_identificacao', $termo);
            $this->db->or_like('d.protocolo', $termo);
            $this->db->group_end();
        }

        if (!empty($tipo_documento_codigo)) {
            $this->db->where('d.tipo_documento_codigo', $tipo_documento_codigo);
        }

        if (!empty($localizacao_codigo)) {
            $this->db->where('d.localizacao_codigo', $localizacao_codigo);
        }

        if (!empty($status)) {
            $this->db->where('d.ativo', $status == 'ativo' ? 1 : 0);
        }

        $this->db->where('d.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('d.titulo', 'ASC');

        if ($limite !== NULL) {
            $this->db->limit($limite, $offset);
        }

        return $this->db->get()->result_array();
    }

    public function contar_tudo($termo = '', $tipo_documento_codigo = '', $localizacao_codigo = '', $status = '')
    {
        $this->db->select('d.codigo');
        $this->db->from($this->tabela . ' d');

        if (!empty($termo)) {
            $this->db->group_start();
            $this->db->like('d.titulo', $termo);
            $this->db->or_like('d.numero_identificacao', $termo);
            $this->db->or_like('d.protocolo', $termo);
            $this->db->group_end();
        }

        if (!empty($tipo_documento_codigo)) {
            $this->db->where('d.tipo_documento_codigo', $tipo_documento_codigo);
        }

        if (!empty($localizacao_codigo)) {
            $this->db->where('d.localizacao_codigo', $localizacao_codigo);
        }

        if (!empty($status)) {
            $this->db->where('d.ativo', $status == 'ativo' ? 1 : 0);
        }

        $this->db->where('d.exclusao IS NULL', NULL, FALSE);
        return $this->db->get()->num_rows();
    }

    public function listar_por_localizacao(
        $localizacao_codigo,
        $limite = NULL,
        $offset = NULL
    ) {
        $this->preparar_consulta_listagem();

        $this->db->where('d.localizacao_codigo', $localizacao_codigo);
        $this->db->where('d.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('d.titulo', 'ASC');

        if ($limite !== NULL) {
            $this->db->limit($limite, $offset);
        }

        return $this->db->get()->result_array();
    }

    public function contar_por_localizacao($localizacao_codigo)
    {
        $this->db->where('localizacao_codigo', $localizacao_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->count_all_results($this->tabela);
    }

    public function possui_documentos($localizacao_codigo)
    {
        return $this->contar_por_localizacao($localizacao_codigo) > 0;
    }

    public function possui_tipo_diferente_na_localizacao(
        $localizacao_codigo,
        $tipo_documento_codigo
    ) {
        $this->db->where('localizacao_codigo', $localizacao_codigo);
        $this->db->where('tipo_documento_codigo !=', $tipo_documento_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->count_all_results($this->tabela) > 0;
    }

    public function buscar_por_codigo($codigo)
    {
        $this->preparar_consulta_listagem();
        $this->db->where('d.codigo', $codigo);
        $this->db->where('d.exclusao IS NULL', NULL, FALSE);
        $this->db->limit(1);
        return $this->db->get()->row_array();
    }

    public function bloquear($codigo)
    {
        return $this->db->query(
            'SELECT `codigo`
                FROM `documentos`
                WHERE `codigo` = ?
                    AND `exclusao` IS NULL
                FOR UPDATE',
            [(int) $codigo]
        )->row_array();
    }

    public function buscar_por_protocolo($protocolo)
    {
        $this->preparar_consulta_listagem();
        $this->db->where('d.protocolo', strtoupper(trim($protocolo)));
        $this->db->where('d.exclusao IS NULL', NULL, FALSE);
        $this->db->limit(1);
        return $this->db->get()->row_array();
    }

    public function cadastrar($reg)
    {
        $reg['cadastro'] = date('Y-m-d H:i:s');

        if (!$this->db->insert($this->tabela, $reg)) {
            return FALSE;
        }

        $codigo = $this->db->insert_id();

        $protocolo = gerar_protocolo(
            'DOC',
            $codigo,
            $reg['cadastro']
        );

        if (!$protocolo) {
            return FALSE;
        }

        $this->db->where('codigo', $codigo);

        if (
            !$this->db->update(
                $this->tabela,
                ['protocolo' => $protocolo]
            )
        ) {
            return FALSE;
        }

        if ($this->db->affected_rows() <= 0) {
            return FALSE;
        }

        return $codigo;
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
        $reg = [
            'ativo' => 0,
            'atualizacao' => date('Y-m-d H:i:s'),
            'exclusao' => date('Y-m-d H:i:s')
        ];

        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if (!$this->db->update($this->tabela, $reg)) {
            return FALSE;
        }

        return $this->db->affected_rows() > 0;
    }

    private function preparar_consulta_listagem()
    {
        $this->db->select([
            'd.codigo',
            'd.protocolo',
            'd.tipo_documento_codigo',
            'd.localizacao_codigo',
            'd.titulo',
            'd.descricao',
            'd.numero_identificacao',
            'd.data_documento',
            'd.ativo',
            'd.cadastro',
            'd.atualizacao',
            'td.nome AS tipo_documento',
            'l.nome AS localizacao',
            'l.classificacao AS localizacao_classificacao'
        ]);
        $this->db->from($this->tabela . ' d');
        $this->db->join(
            'tipos_documento td',
            'td.codigo = d.tipo_documento_codigo AND td.exclusao IS NULL',
            'inner',
            FALSE
        );
        $this->db->join(
            'localizacoes l',
            'l.codigo = d.localizacao_codigo AND l.exclusao IS NULL',
            'inner',
            FALSE
        );
    }
}
