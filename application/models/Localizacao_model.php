<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Localizacao_model extends CI_Model
{
    private $tabela = 'localizacoes';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar_raizes($termo = '', $status = '', $limite = NULL, $offset = NULL)
    {
        $this->preparar_consulta_listagem();

        if (!empty($termo)) {
            $this->db->group_start();
            $this->db->like('l.nome', $termo);
            $this->db->or_like('l.descricao', $termo);
            $this->db->or_like('l.protocolo', $termo);
            $this->db->group_end();
        }

        if (!empty($status)) {
            if ($status == 'ativo') {
                $this->db->where('l.ativo', 1);
            } else {
                $this->db->where('l.ativo', 0);
            }
        }

        $this->db->where('l.localizacao_codigo_pai IS NULL', NULL, FALSE);
        $this->db->where('l.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('l.sequencial', 'ASC');

        if ($limite !== NULL) {
            $this->db->limit($limite, $offset);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    public function contar_raizes($termo = '', $status = '')
    {
        $this->preparar_consulta_listagem();

        if (!empty($termo)) {
            $this->db->group_start();
            $this->db->like('l.nome', $termo);
            $this->db->or_like('l.descricao', $termo);
            $this->db->or_like('l.protocolo', $termo);
            $this->db->group_end();
        }

        if (!empty($status)) {
            if ($status == 'ativo') {
                $this->db->where('l.ativo', 1);
            } else {
                $this->db->where('l.ativo', 0);
            }
        }

        $this->db->where('l.localizacao_codigo_pai IS NULL', NULL, FALSE);
        $this->db->where('l.exclusao IS NULL', NULL, FALSE);

        $query = $this->db->get();
        return $query->num_rows();
    }

    public function listar_filhos($localizacao_codigo_pai, $termo = '', $status = '', $limite = NULL, $offset = NULL)
    {
        $this->preparar_consulta_listagem();

        if (!empty($termo)) {
            $this->db->group_start();
            $this->db->like('l.nome', $termo);
            $this->db->or_like('l.descricao', $termo);
            $this->db->or_like('l.protocolo', $termo);
            $this->db->group_end();
        }

        if (!empty($status)) {
            if ($status == 'ativo') {
                $this->db->where('l.ativo', 1);
            } else {
                $this->db->where('l.ativo', 0);
            }
        }

        $this->db->where('l.localizacao_codigo_pai', $localizacao_codigo_pai);
        $this->db->where('l.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('l.sequencial', 'ASC');

        if ($limite !== NULL) {
            $this->db->limit($limite, $offset);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    public function contar_filhos($localizacao_codigo_pai, $termo = '', $status = '')
    {
        $this->preparar_consulta_listagem();

        if (!empty($termo)) {
            $this->db->group_start();
            $this->db->like('l.nome', $termo);
            $this->db->or_like('l.descricao', $termo);
            $this->db->or_like('l.protocolo', $termo);
            $this->db->group_end();
        }

        if (!empty($status)) {
            if ($status == 'ativo') {
                $this->db->where('l.ativo', 1);
            } else {
                $this->db->where('l.ativo', 0);
            }
        }

        $this->db->where('l.localizacao_codigo_pai', $localizacao_codigo_pai);
        $this->db->where('l.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('l.sequencial', 'ASC');

        $query = $this->db->get();
        return $query->num_rows();
    }

    public function listar_opcoes($codigo = NULL, $classificacao = NULL)
    {
        $this->db->select([
            'l.codigo',
            'l.protocolo',
            'l.nome',
            'l.classificacao',
            'l.localizacao_codigo_pai',
            'tl.nome AS tipo_localizacao',
            'td.nome AS tipo_documento'
        ]);
        $this->db->from('localizacoes l');
        $this->db->join(
            'tipos_localizacao tl',
            'tl.codigo = l.tipo_localizacao_codigo AND tl.exclusao IS NULL',
            'left',
            FALSE
        );
        $this->db->join(
            'localizacao_tipo_documentos ltd',
            'ltd.localizacao_codigo = l.codigo'
            . ' AND ltd.exclusao IS NULL',
            'left',
            FALSE
        );

        $this->db->join(
            'tipos_documento td',
            'td.codigo = ltd.tipo_documento_codigo'
            . ' AND td.exclusao IS NULL',
            'left',
            FALSE
        );
        $this->db->where('l.ativo', 1);
        $this->db->where('l.exclusao IS NULL', NULL, FALSE);

        if ($codigo !== NULL) {
            $this->db->where('l.codigo !=', $codigo);
        }

        if ($classificacao !== NULL) {
            $this->db->not_like('l.classificacao', $classificacao . '.', 'after');
        }

        $this->db->order_by('l.classificacao', 'ASC');

        $query = $this->db->get();
        return $query->result_array();
    }

    public function buscar_por_codigo($codigo)
    {
        $this->preparar_consulta_listagem();

        $this->db->where('l.codigo', $codigo);
        $this->db->where('l.exclusao IS NULL', NULL, FALSE);
        $this->db->limit(1);

        $query = $this->db->get();
        return $query->row_array();
    }

    public function buscar_por_protocolo($protocolo)
    {
        $this->preparar_consulta_listagem();

        $this->db->where('l.protocolo', strtoupper(trim($protocolo)));
        $this->db->where('l.exclusao IS NULL', NULL, FALSE);
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
            'LOC',
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

    public function atualizar($codigo, $reg, $classificacao_anterior)
    {
        $reg['atualizacao'] = date('Y-m-d H:i:s');

        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if (!$this->db->update($this->tabela, $reg)) {
            return FALSE;
        }

        if ($classificacao_anterior !== $reg['classificacao']) {
            return $this->atualizar_classificacao_descendentes(
                $classificacao_anterior,
                $reg['classificacao']
            );
        }

        return TRUE;
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

    private function atualizar_classificacao_descendentes($classificacao_anterior, $nova_classificacao)
    {
        $inicio_sufixo = strlen($classificacao_anterior) + 1;

        $sql = "
                UPDATE {$this->tabela}
                SET
                    classificacao = CONCAT(
                        ?,
                        SUBSTRING(classificacao, ?)
                    ),
                    atualizacao = ?
                WHERE classificacao LIKE ?
            ";

        $executou = $this->db->query(
            $sql,
            [
                $nova_classificacao,
                $inicio_sufixo,
                date('Y-m-d H:i:s'),
                $classificacao_anterior . '.%'
            ]
        );

        return $executou !== FALSE;
    }

    public function possui_sublocalizacoes($codigo)
    {
        $this->db->where('localizacao_codigo_pai', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->count_all_results($this->tabela) > 0;
    }

    public function obter_proximo_sequencial($localizacao_codigo_pai = NULL)
    {
        $this->db->select('COALESCE(MAX(sequencial), 0) + 1 AS proximo', FALSE);

        if ($localizacao_codigo_pai === NULL) {
            $this->db->where('localizacao_codigo_pai IS NULL', NULL, FALSE);
        } else {
            $this->db->where('localizacao_codigo_pai', $localizacao_codigo_pai);
        }

        /*
         * Registros excluídos também são considerados.
         * Isso evita reutilizar um sequencial antigo.
         */
        $query = $this->db->get($this->tabela);

        if (!$query) {
            return FALSE;
        }

        $resultado = $query->row_array();

        if (!$resultado) {
            return FALSE;
        }

        return (int) $resultado['proximo'];
    }

    public function eh_descendente($localizacao_codigo, $possivel_descendente_codigo)
    {
        $localizacao = $this->buscar_por_codigo($localizacao_codigo);
        $possivel_descendente = $this->buscar_por_codigo($possivel_descendente_codigo);

        if (!$localizacao || !$possivel_descendente) {
            return FALSE;
        }

        $prefixo = $localizacao['classificacao'] . '.';

        return strpos($possivel_descendente['classificacao'], $prefixo) === 0;
    }

    public function buscar_caminho($codigo)
    {
        $localizacao = $this->buscar_por_codigo($codigo);

        if (!$localizacao) {
            return [];
        }

        $segmentos = explode('.', $localizacao['classificacao']);

        $classificacoes = [];
        $classificacao_atual = [];

        foreach ($segmentos as $segmento) {
            $classificacao_atual[] = $segmento;
            $classificacoes[] = implode('.', $classificacao_atual);
        }

        $this->db->select([
            'l.codigo',
            'l.protocolo',
            'l.nome',
            'l.classificacao'
        ]);
        $this->db->from('localizacoes l');
        $this->db->where_in('l.classificacao', $classificacoes);
        $this->db->where('l.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('CHAR_LENGTH(l.classificacao)', 'ASC', FALSE);

        $query = $this->db->get();
        return $query->result_array();
    }

    private function preparar_consulta_listagem()
    {
        $this->db->select([
            'l.codigo',
            'l.protocolo',
            'l.nome',
            'l.classificacao',
            'l.descricao',
            'l.ativo',
            'l.sequencial',
            'l.tipo_localizacao_codigo',
            'l.localizacao_codigo_pai',
            'tl.nome AS tipo_localizacao',
            'lp.nome AS localizacao_pai_nome',
            'lp.classificacao AS localizacao_pai_classificacao',
            'ltd.tipo_documento_codigo',
            'td.nome AS tipo_documento',
            'COUNT(DISTINCT sl.codigo) AS total_sublocalizacoes',
            'COUNT(DISTINCT d.codigo) AS total_documentos'
        ]);

        $this->db->from($this->tabela . ' l');

        $this->db->join(
            'tipos_localizacao tl',
            'tl.codigo = l.tipo_localizacao_codigo AND tl.exclusao IS NULL',
            'inner',
            FALSE
        );

        $this->db->join(
            'localizacoes lp',
            'lp.codigo = l.localizacao_codigo_pai AND lp.exclusao IS NULL',
            'left',
            FALSE
        );

        $this->db->join(
            'localizacao_tipo_documentos ltd',
            'ltd.localizacao_codigo = l.codigo AND ltd.exclusao IS NULL',
            'left',
            FALSE
        );

        $this->db->join(
            'tipos_documento td',
            'td.codigo = ltd.tipo_documento_codigo AND td.exclusao IS NULL',
            'left',
            FALSE
        );

        $this->db->join(
            'documentos d',
            'd.localizacao_codigo = l.codigo AND d.exclusao IS NULL',
            'left',
            FALSE
        );

        $this->db->join(
            'localizacoes sl',
            'sl.localizacao_codigo_pai = l.codigo AND sl.exclusao IS NULL',
            'left',
            FALSE
        );

        $this->db->group_by([
            'l.codigo',
            'l.protocolo',
            'l.nome',
            'l.classificacao',
            'l.descricao',
            'l.ativo',
            'l.sequencial',
            'l.tipo_localizacao_codigo',
            'l.localizacao_codigo_pai',
            'tl.nome',
            'lp.nome',
            'lp.classificacao',
            'ltd.tipo_documento_codigo',
            'td.nome'
        ]);
    }
}
