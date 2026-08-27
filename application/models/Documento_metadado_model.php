<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Documento_metadado_model extends CI_Model
{
    private $tabela = 'documento_metadados';

    public function listar_campos_tipo($tipo_documento_codigo, $documento_codigo = NULL)
    {
        $documento_codigo = $documento_codigo !== NULL ? (int) $documento_codigo : 0;

        $this->db->select([
            'tdm.metadado_codigo',
            'tdm.ordem',
            'tdm.obrigatorio',
            'm.nome',
            'm.descricao',
            'm.tipo_campo',
            'm.mascara',
            'm.opcoes',
            'dm.valor'
        ]);
        $this->db->from('tipo_documento_metadados tdm');
        $this->db->join('metadados m', 'm.codigo = tdm.metadado_codigo AND m.exclusao IS NULL', 'inner', FALSE);
        $this->db->join(
            $this->tabela . ' dm',
            'dm.metadado_codigo = tdm.metadado_codigo'
                . ' AND dm.documento_codigo = ' . $documento_codigo
                . ' AND dm.exclusao IS NULL',
            'left',
            FALSE
        );
        $this->db->where('tdm.tipo_documento_codigo', $tipo_documento_codigo);
        $this->db->where('tdm.visivel', 1);
        $this->db->where('tdm.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('tdm.ordem', 'ASC');

        return $this->db->get()->result_array();
    }

    public function listar_por_documento($documento_codigo)
    {
        $this->db->select(['dm.valor', 'm.nome', 'm.tipo_campo', 'tdm.ordem']);
        $this->db->from($this->tabela . ' dm');
        $this->db->join('documentos d', 'd.codigo = dm.documento_codigo', 'inner');
        $this->db->join('metadados m', 'm.codigo = dm.metadado_codigo AND m.exclusao IS NULL', 'inner', FALSE);
        $this->db->join('tipo_documento_metadados tdm', 'tdm.tipo_documento_codigo = d.tipo_documento_codigo AND tdm.metadado_codigo = dm.metadado_codigo AND tdm.exclusao IS NULL', 'inner', FALSE);
        $this->db->where('dm.documento_codigo', $documento_codigo);
        $this->db->where('dm.exclusao IS NULL', NULL, FALSE);
        $this->db->where('tdm.visivel', 1);
        $this->db->order_by('tdm.ordem', 'ASC');

        return $this->db->get()->result_array();
    }

    public function listar_por_documentos($documento_codigos)
    {
        $documento_codigos = array_values(
            array_filter(
                array_map('intval', (array) $documento_codigos)
            )
        );

        if (empty($documento_codigos)) {
            return [];
        }

        $this->db->select([
            'dm.documento_codigo',
            'dm.valor',
            'm.nome',
            'm.tipo_campo',
            'tdm.ordem'
        ]);
        $this->db->from($this->tabela . ' dm');
        $this->db->join('documentos d', 'd.codigo = dm.documento_codigo', 'inner');
        $this->db->join('metadados m', 'm.codigo = dm.metadado_codigo AND m.exclusao IS NULL', 'inner', FALSE);
        $this->db->join('tipo_documento_metadados tdm', 'tdm.tipo_documento_codigo = d.tipo_documento_codigo AND tdm.metadado_codigo = dm.metadado_codigo AND tdm.exclusao IS NULL', 'inner', FALSE);
        $this->db->where_in('dm.documento_codigo', $documento_codigos);
        $this->db->where('dm.exclusao IS NULL', NULL, FALSE);
        $this->db->where('tdm.visivel', 1);
        $this->db->order_by('dm.documento_codigo', 'ASC');
        $this->db->order_by('tdm.ordem', 'ASC');

        return $this->db->get()->result_array();
    }

    public function buscar_valor($documento_codigo, $metadado_codigo)
    {
        $this->db->where('documento_codigo', $documento_codigo);
        $this->db->where('metadado_codigo', $metadado_codigo);

        return $this->db->get($this->tabela, 1)->row_array();
    }

    public function salvar($documento_codigo, $metadados)
    {
        $data_atual = date('Y-m-d H:i:s');

        $this->db->where('documento_codigo', $documento_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if (!$this->db->update($this->tabela, [
            'atualizacao' => $data_atual,
            'exclusao' => $data_atual
        ])) {
            return FALSE;
        }

        foreach ($metadados as $metadado_codigo => $valor) {
            if (is_array($valor)) {
                $valor = json_encode(
                    array_values($valor),
                    JSON_UNESCAPED_UNICODE
                );
            }

            $valor = trim((string) $valor);

            if ($valor === '') {
                continue;
            }

            $metadado_codigo = (int) $metadado_codigo;

            $dados = [
                'valor' => $valor,
                'atualizacao' => $data_atual,
                'exclusao' => NULL
            ];

            if ($this->buscar_valor($documento_codigo, $metadado_codigo)) {
                $this->db->where('documento_codigo', $documento_codigo);
                $this->db->where('metadado_codigo', $metadado_codigo);

                if (!$this->db->update($this->tabela, $dados)) {
                    return FALSE;
                }

                continue;
            }

            $dados['documento_codigo'] = $documento_codigo;
            $dados['metadado_codigo'] = $metadado_codigo;
            $dados['cadastro'] = $data_atual;

            if (!$this->db->insert($this->tabela, $dados)) {
                return FALSE;
            }
        }

        return TRUE;
    }

    public function excluir_por_documento($documento_codigo)
    {
        $data_atual = date('Y-m-d H:i:s');

        $this->db->where(
            'documento_codigo',
            $documento_codigo
        );

        $this->db->where(
            'exclusao IS NULL',
            NULL,
            FALSE
        );

        return $this->db->update(
            $this->tabela,
            [
                'atualizacao' => $data_atual,
                'exclusao' => $data_atual
            ]
        );
    }
}
