<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Perfil_permissao_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar_codigos_por_perfil($perfil_codigo)
    {
        $this->db->select('permissao_codigo');
        $this->db->where('perfil_codigo', $perfil_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return array_map(
            'intval',
            array_column(
                $this->db->get('perfil_permissoes')->result_array(),
                'permissao_codigo'
            )
        );
    }

    public function sincronizar($perfil_codigo, array $permissoes)
    {
        $permissoes = array_values(
            array_unique(
                array_map('intval', $permissoes)
            )
        );

        $this->db->select([
            'permissao_codigo',
            'exclusao'
        ]);
        $this->db->where('perfil_codigo', $perfil_codigo);

        $existentes = $this->db
            ->get('perfil_permissoes')
            ->result_array();

        $codigos_existentes = [];

        foreach ($existentes as $existente) {
            $permissao_codigo = (int) $existente['permissao_codigo'];
            $codigos_existentes[] = $permissao_codigo;

            $selecionada = in_array(
                $permissao_codigo,
                $permissoes,
                TRUE
            );

            $this->db->where('perfil_codigo', $perfil_codigo);
            $this->db->where('permissao_codigo', $permissao_codigo);

            if (!$this->db->update(
                'perfil_permissoes',
                [
                    'atualizacao' => date('Y-m-d H:i:s'),
                    'exclusao' => $selecionada
                        ? NULL
                        : date('Y-m-d H:i:s')
                ]
            )) {
                return FALSE;
            }
        }

        foreach ($permissoes as $permissao_codigo) {
            if (in_array($permissao_codigo, $codigos_existentes, TRUE)) {
                continue;
            }

            if (!$this->db->insert(
                'perfil_permissoes',
                [
                    'perfil_codigo' => $perfil_codigo,
                    'permissao_codigo' => $permissao_codigo,
                    'cadastro' => date('Y-m-d H:i:s')
                ]
            )) {
                return FALSE;
            }
        }

        return TRUE;
    }
}
