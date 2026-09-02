<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Documento_arquivo_model extends CI_Model
{
    private $tabela = 'documento_arquivos';

    public function listar_por_documento($documento_codigo)
    {
        return $this->db->query(
            'SELECT arquivo.*
                FROM documento_arquivos arquivo
                WHERE arquivo.documento_codigo = ?
                    AND arquivo.exclusao IS NULL
                    AND NOT EXISTS (
                        SELECT 1
                        FROM documento_arquivos versao_posterior
                        WHERE versao_posterior.documento_codigo =
                            arquivo.documento_codigo
                            AND versao_posterior.exclusao IS NULL
                            AND COALESCE(
                                versao_posterior.arquivo_raiz_codigo,
                                versao_posterior.codigo
                            ) = COALESCE(
                                arquivo.arquivo_raiz_codigo,
                                arquivo.codigo
                            )
                            AND (
                                versao_posterior.versao > arquivo.versao
                                OR (
                                    versao_posterior.versao = arquivo.versao
                                    AND versao_posterior.codigo > arquivo.codigo
                                )
                            )
                    )
                ORDER BY arquivo.principal DESC,
                    arquivo.cadastro DESC',
            [(int) $documento_codigo]
        )->result_array();
    }

    public function buscar_por_codigo($codigo)
    {
        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        return $this->db->get($this->tabela, 1)->row_array();
    }

    public function bloquear_arquivo_raiz(
        $documento_codigo,
        $arquivo_raiz_codigo
    ) {
        return $this->db->query(
            'SELECT `codigo`, `versao`, `arquivo_raiz_codigo`, `exclusao`
                FROM `documento_arquivos`
                WHERE `codigo` = ?
                    AND `documento_codigo` = ?
                    AND `versao` = 1
                    AND `arquivo_raiz_codigo` IS NULL
                FOR UPDATE',
            [
                (int) $arquivo_raiz_codigo,
                (int) $documento_codigo
            ]
        )->row_array();
    }

    public function buscar_ultima_versao(
        $documento_codigo,
        $arquivo_raiz_codigo
    ) {
        $this->aplicar_filtro_linhagem(
            $documento_codigo,
            $arquivo_raiz_codigo
        );
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('versao', 'DESC');
        $this->db->order_by('codigo', 'DESC');

        return $this->db->get($this->tabela, 1)->row_array();
    }

    public function buscar_principal($documento_codigo)
    {
        $this->db->where('documento_codigo', $documento_codigo);
        $this->db->where('principal', 1);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->get($this->tabela, 1)->row_array();
    }

    public function listar_versoes(
        $documento_codigo,
        $arquivo_raiz_codigo
    ) {
        $this->aplicar_filtro_linhagem(
            $documento_codigo,
            $arquivo_raiz_codigo
        );
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('versao', 'DESC');
        $this->db->order_by('codigo', 'DESC');

        return $this->db->get($this->tabela)->result_array();
    }

    public function proxima_versao(
        $documento_codigo,
        $arquivo_raiz_codigo
    ) {
        $this->db->select_max('versao', 'ultima_versao');
        $this->aplicar_filtro_linhagem(
            $documento_codigo,
            $arquivo_raiz_codigo
        );

        $resultado = $this->db
            ->get($this->tabela)
            ->row_array();

        return (int) ($resultado['ultima_versao'] ?? 0) + 1;
    }

    private function aplicar_filtro_linhagem(
        $documento_codigo,
        $arquivo_raiz_codigo
    ) {
        $this->db->where('documento_codigo', $documento_codigo);
        $this->db->group_start();
        $this->db->where('codigo', $arquivo_raiz_codigo);
        $this->db->or_where('arquivo_raiz_codigo', $arquivo_raiz_codigo);
        $this->db->group_end();
    }

    public function cadastrar($reg)
    {
        $reg['cadastro'] = date('Y-m-d H:i:s');

        if (!$this->db->insert($this->tabela, $reg)) {
            return FALSE;
        }

        return $this->db->insert_id();
    }

    public function possui_arquivos($documento_codigo)
    {
        $this->db->where('documento_codigo', $documento_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        return $this->db->count_all_results($this->tabela) > 0;
    }

    public function definir_principal($documento_codigo, $arquivo_codigo)
    {
        $this->db->trans_begin();

        $this->db->where('documento_codigo', $documento_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        $this->db->update($this->tabela, ['principal' => 0]);

        $this->db->where('codigo', $arquivo_codigo);
        $this->db->where('documento_codigo', $documento_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        $this->db->update($this->tabela, ['principal' => 1]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return TRUE;
    }

    public function excluir($codigo)
    {
        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if (!$this->db->update($this->tabela, [
            'principal' => 0,
            'exclusao' => date('Y-m-d H:i:s')
        ])) {
            return FALSE;
        }

        return $this->db->affected_rows() > 0;
    }

    public function excluir_linhagem(
        $documento_codigo,
        $arquivo_raiz_codigo
    ) {
        $this->aplicar_filtro_linhagem(
            $documento_codigo,
            $arquivo_raiz_codigo
        );
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if (!$this->db->update(
            $this->tabela,
            [
                'principal' => 0,
                'exclusao' => date('Y-m-d H:i:s')
            ]
        )) {
            return FALSE;
        }

        return $this->db->affected_rows() > 0;
    }

    public function definir_novo_principal($documento_codigo)
    {
        $this->db->select('codigo');
        $this->db->where(
            'documento_codigo',
            $documento_codigo
        );
        $this->db->where(
            'exclusao IS NULL',
            NULL,
            FALSE
        );
        $this->db->order_by('cadastro', 'DESC');

        $arquivo = $this->db
            ->get($this->tabela, 1)
            ->row_array();

        if (!$arquivo) {
            return TRUE;
        }

        $this->db->where(
            'codigo',
            $arquivo['codigo']
        );

        return $this->db->update(
            $this->tabela,
            ['principal' => 1]
        );
    }

    public function excluir_por_documento($documento_codigo)
    {
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
                'principal' => 0,
                'exclusao' => date('Y-m-d H:i:s')
            ]
        );
    }

}
