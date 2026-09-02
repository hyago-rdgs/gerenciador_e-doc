<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Movimentacao extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->controle_acesso->valida_acesso();
        $this->load->library('auditoria');
        $this->load->model('documento_model');
        $this->load->model('documento_movimentacao_model');
        $this->load->model('localizacao_model');
        $this->load->model('localizacao_tipo_documento_model');
    }

    public function index()
    {
        $this->listar();
        return;
    }

    public function listar()
    {
        $this->controle_acesso->valida_permissao('movimentacoes.visualizar');

        $filtro_termo = trim((string) $this->input->get('termo', TRUE));
        $filtro_tipo = strtoupper(trim((string) $this->input->get('tipo', TRUE)));
        $filtro_situacao = strtolower(trim((string) $this->input->get('situacao', TRUE)));
        $filtro_data_inicio = trim((string) $this->input->get('data_inicio', TRUE));
        $filtro_data_fim = trim((string) $this->input->get('data_fim', TRUE));

        $tipos = ['CADASTRO', 'TRANSFERENCIA', 'RETIRADA', 'DEVOLUCAO'];
        $situacoes = ['aberta', 'atrasada', 'concluida'];

        if (!in_array($filtro_tipo, $tipos, TRUE)) {
            $filtro_tipo = '';
        }

        if (!in_array($filtro_situacao, $situacoes, TRUE)) {
            $filtro_situacao = '';
        }

        if (!$this->validar_data($filtro_data_inicio)) {
            $filtro_data_inicio = '';
        }

        if (!$this->validar_data($filtro_data_fim)) {
            $filtro_data_fim = '';
        }

        $pagina_atual = max(1, (int) $this->input->get('pagina', TRUE));
        $limite = 20;
        $offset = ($pagina_atual - 1) * $limite;

        $total_movimentacoes = $this->documento_movimentacao_model->contar_tudo(
            $filtro_termo,
            $filtro_tipo,
            $filtro_situacao,
            $filtro_data_inicio,
            $filtro_data_fim
        );

        $dados = [
            'movimentacoes' => $this->documento_movimentacao_model->listar_tudo(
                $filtro_termo,
                $filtro_tipo,
                $filtro_situacao,
                $filtro_data_inicio,
                $filtro_data_fim,
                $limite,
                $offset
            ),
            'filtro_termo' => $filtro_termo,
            'filtro_tipo' => $filtro_tipo,
            'filtro_situacao' => $filtro_situacao,
            'filtro_data_inicio' => $filtro_data_inicio,
            'filtro_data_fim' => $filtro_data_fim,
            'total_movimentacoes' => $total_movimentacoes,
            'limite' => $limite,
            'offset' => $offset + 1,
            'pagina_atual' => $pagina_atual,
            'total_paginas' => (int) ceil($total_movimentacoes / $limite)
        ];

        $this->load->view('movimentacao/movimentacao_lista', $dados);
    }

    public function transferir($documento_codigo = NULL)
    {
        $this->controle_acesso->valida_permissao('movimentacoes.gerenciar');
        $this->validar_requisicao_post();

        $documento = $this->buscar_documento($documento_codigo);
        $resultado = $this->validar_destino(
            $this->input->post('localizacao_destino_codigo'),
            $documento
        );
        $observacao = trim((string) $this->input->post('observacao', TRUE));

        if ($resultado['erros']) {
            resposta_json(FALSE, 'Verifique os campos informados.', ['erros' => $resultado['erros']], 422);
        }

        $this->db->trans_begin();
        $documento_bloqueado = $this->documento_model->bloquear($documento['codigo']);

        if (!$documento_bloqueado) {
            $this->db->trans_rollback();
            resposta_json(FALSE, 'O documento não está mais disponível para movimentação.', [], 409);
        }

        $documento = $this->documento_model->buscar_por_codigo($documento['codigo']);

        if (!$documento || (int) $documento['ativo'] !== 1) {
            $this->db->trans_rollback();
            resposta_json(FALSE, 'O documento não está mais disponível para movimentação.', [], 409);
        }

        if ((int) $documento['localizacao_codigo'] === (int) $resultado['localizacao']['codigo']) {
            $this->db->trans_rollback();
            resposta_json(FALSE, 'O documento já está na localização selecionada.', [], 422);
        }

        $retirada_aberta = $this->documento_movimentacao_model->buscar_retirada_aberta(
            $documento['codigo'],
            TRUE
        );

        if ($retirada_aberta) {
            $this->db->trans_rollback();
            resposta_json(FALSE, 'O documento possui uma retirada em aberto.', [
                'erros' => ['Registre a devolução antes de realizar uma transferência.']
            ], 422);
        }

        $origem_codigo = (int) $documento['localizacao_codigo'];
        $destino_codigo = (int) $resultado['localizacao']['codigo'];
        $documento_atualizado = $this->documento_model->atualizar(
            $documento['codigo'],
            ['localizacao_codigo' => $destino_codigo]
        );
        $movimentacao_codigo = $documento_atualizado
            ? $this->documento_movimentacao_model->cadastrar([
                'documento_codigo' => $documento['codigo'],
                'usuario_codigo' => $this->controle_acesso->get('codigo'),
                'localizacao_origem_codigo' => $origem_codigo,
                'localizacao_destino_codigo' => $destino_codigo,
                'tipo_movimentacao' => 'TRANSFERENCIA',
                'observacao' => $observacao !== '' ? $observacao : NULL
            ])
            : FALSE;
        $auditoria_salva = $movimentacao_codigo
            ? $this->auditoria->registrar(
                'movimentacoes',
                'DOCUMENTO_TRANSFERIDO',
                'documento_movimentacoes',
                $movimentacao_codigo,
                ['localizacao_codigo' => $origem_codigo],
                ['localizacao_codigo' => $destino_codigo, 'observacao' => $observacao]
            )
            : FALSE;

        if (!$documento_atualizado || !$movimentacao_codigo || !$auditoria_salva || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            resposta_json(FALSE, 'Não foi possível transferir o documento.', [], 500);
        }

        $this->db->trans_commit();
        resposta_json(TRUE, 'Documento transferido com sucesso.', ['codigo' => $movimentacao_codigo]);
    }

    public function retirar($documento_codigo = NULL)
    {
        $this->controle_acesso->valida_permissao('movimentacoes.gerenciar');
        $this->validar_requisicao_post();

        $documento = $this->buscar_documento($documento_codigo);
        $responsavel_nome = trim((string) $this->input->post('responsavel_nome', TRUE));
        $responsavel_contato = trim((string) $this->input->post('responsavel_contato', TRUE));
        $data_prevista = trim((string) $this->input->post('data_prevista_devolucao', TRUE));
        $observacao = trim((string) $this->input->post('observacao', TRUE));
        $erros = [];

        if ($responsavel_nome === '') {
            $erros[] = 'O responsável pela retirada é obrigatório.';
        } elseif (strlen($responsavel_nome) > 255) {
            $erros[] = 'O responsável deve possuir no máximo 255 caracteres.';
        }

        if (strlen($responsavel_contato) > 255) {
            $erros[] = 'O contato deve possuir no máximo 255 caracteres.';
        }

        if ($data_prevista !== '' && !$this->validar_data($data_prevista)) {
            $erros[] = 'A previsão de devolução é inválida.';
        } elseif ($data_prevista !== '' && $data_prevista < date('Y-m-d')) {
            $erros[] = 'A previsão de devolução não pode ser anterior à data atual.';
        }

        if (strlen($observacao) > 2000) {
            $erros[] = 'A observação deve possuir no máximo 2000 caracteres.';
        }

        if ($erros) {
            resposta_json(FALSE, 'Verifique os campos informados.', ['erros' => $erros], 422);
        }

        $this->db->trans_begin();
        $documento_bloqueado = $this->documento_model->bloquear($documento['codigo']);

        if (!$documento_bloqueado) {
            $this->db->trans_rollback();
            resposta_json(FALSE, 'O documento não está mais disponível para movimentação.', [], 409);
        }

        $documento = $this->documento_model->buscar_por_codigo($documento['codigo']);

        if (!$documento || (int) $documento['ativo'] !== 1) {
            $this->db->trans_rollback();
            resposta_json(FALSE, 'O documento não está mais disponível para movimentação.', [], 409);
        }

        $retirada_aberta = $this->documento_movimentacao_model->buscar_retirada_aberta(
            $documento['codigo'],
            TRUE
        );

        if ($retirada_aberta) {
            $this->db->trans_rollback();
            resposta_json(FALSE, 'O documento já possui uma retirada em aberto.', [], 422);
        }

        $movimentacao_codigo = $this->documento_movimentacao_model->cadastrar([
            'documento_codigo' => $documento['codigo'],
            'usuario_codigo' => $this->controle_acesso->get('codigo'),
            'localizacao_origem_codigo' => $documento['localizacao_codigo'],
            'localizacao_destino_codigo' => NULL,
            'tipo_movimentacao' => 'RETIRADA',
            'responsavel_nome' => $responsavel_nome,
            'responsavel_contato' => $responsavel_contato !== '' ? $responsavel_contato : NULL,
            'data_prevista_devolucao' => $data_prevista !== '' ? $data_prevista : NULL,
            'observacao' => $observacao !== '' ? $observacao : NULL
        ]);
        $auditoria_salva = $movimentacao_codigo
            ? $this->auditoria->registrar(
                'movimentacoes',
                'DOCUMENTO_RETIRADO',
                'documento_movimentacoes',
                $movimentacao_codigo,
                NULL,
                [
                    'documento_codigo' => $documento['codigo'],
                    'responsavel_nome' => $responsavel_nome,
                    'data_prevista_devolucao' => $data_prevista ?: NULL
                ]
            )
            : FALSE;

        if (!$movimentacao_codigo || !$auditoria_salva || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            resposta_json(FALSE, 'Não foi possível registrar a retirada.', [], 500);
        }

        $this->db->trans_commit();
        resposta_json(TRUE, 'Retirada registrada com sucesso.', ['codigo' => $movimentacao_codigo], 201);
    }

    public function devolver($documento_codigo = NULL)
    {
        $this->controle_acesso->valida_permissao('movimentacoes.gerenciar');
        $this->validar_requisicao_post();

        $documento = $this->buscar_documento($documento_codigo);
        $resultado = $this->validar_destino(
            $this->input->post('localizacao_destino_codigo'),
            $documento
        );
        $observacao = trim((string) $this->input->post('observacao', TRUE));

        if ($resultado['erros']) {
            resposta_json(FALSE, 'Verifique os campos informados.', ['erros' => $resultado['erros']], 422);
        }

        $this->db->trans_begin();
        $documento_bloqueado = $this->documento_model->bloquear($documento['codigo']);

        if (!$documento_bloqueado) {
            $this->db->trans_rollback();
            resposta_json(FALSE, 'O documento não está mais disponível para movimentação.', [], 409);
        }

        $documento = $this->documento_model->buscar_por_codigo($documento['codigo']);

        if (!$documento || (int) $documento['ativo'] !== 1) {
            $this->db->trans_rollback();
            resposta_json(FALSE, 'O documento não está mais disponível para movimentação.', [], 409);
        }

        $retirada = $this->documento_movimentacao_model->buscar_retirada_aberta(
            $documento['codigo'],
            TRUE
        );

        if (!$retirada) {
            $this->db->trans_rollback();
            resposta_json(FALSE, 'O documento não possui retirada em aberto.', [], 422);
        }

        $data_devolucao = date('Y-m-d H:i:s');
        $destino_codigo = (int) $resultado['localizacao']['codigo'];
        $retirada_atualizada = $this->documento_movimentacao_model->registrar_devolucao(
            $retirada['codigo'],
            $data_devolucao
        );
        $documento_atualizado = $retirada_atualizada
            ? $this->documento_model->atualizar(
                $documento['codigo'],
                ['localizacao_codigo' => $destino_codigo]
            )
            : FALSE;
        $movimentacao_codigo = $documento_atualizado
            ? $this->documento_movimentacao_model->cadastrar([
                'documento_codigo' => $documento['codigo'],
                'movimentacao_origem_codigo' => $retirada['codigo'],
                'usuario_codigo' => $this->controle_acesso->get('codigo'),
                'localizacao_origem_codigo' => NULL,
                'localizacao_destino_codigo' => $destino_codigo,
                'tipo_movimentacao' => 'DEVOLUCAO',
                'responsavel_nome' => $retirada['responsavel_nome'],
                'responsavel_contato' => $retirada['responsavel_contato'],
                'observacao' => $observacao !== '' ? $observacao : NULL
            ])
            : FALSE;
        $auditoria_salva = $movimentacao_codigo
            ? $this->auditoria->registrar(
                'movimentacoes',
                'DOCUMENTO_DEVOLVIDO',
                'documento_movimentacoes',
                $movimentacao_codigo,
                ['retirada_codigo' => (int) $retirada['codigo']],
                ['localizacao_codigo' => $destino_codigo, 'data_devolucao' => $data_devolucao]
            )
            : FALSE;

        if (!$retirada_atualizada || !$documento_atualizado || !$movimentacao_codigo || !$auditoria_salva || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            resposta_json(FALSE, 'Não foi possível registrar a devolução.', [], 500);
        }

        $this->db->trans_commit();
        resposta_json(TRUE, 'Devolução registrada com sucesso.', ['codigo' => $movimentacao_codigo], 201);
    }

    private function buscar_documento($codigo)
    {
        if (empty($codigo) || !ctype_digit((string) $codigo)) {
            resposta_json(FALSE, 'O documento informado é inválido.', [], 422);
        }

        $documento = $this->documento_model->buscar_por_codigo((int) $codigo);

        if (!$documento || (int) $documento['ativo'] !== 1) {
            resposta_json(FALSE, 'Documento não encontrado ou inativo.', [], 404);
        }

        return $documento;
    }

    private function validar_destino($codigo, $documento)
    {
        $codigo = trim((string) $codigo);
        $erros = [];
        $localizacao = NULL;

        if (ctype_digit($codigo) && (int) $codigo > 0) {
            $localizacao = $this->localizacao_model->buscar_por_codigo((int) $codigo);
        }

        if (!$localizacao || (int) $localizacao['ativo'] !== 1) {
            $erros[] = 'A localização de destino não existe ou está inativa.';
        } else {
            $vinculo = $this->localizacao_tipo_documento_model->buscar_vinculo(
                $localizacao['codigo'],
                $documento['tipo_documento_codigo']
            );

            if (!$vinculo) {
                $erros[] = 'O tipo do documento não é compatível com a localização de destino.';
            }
        }

        $observacao = trim((string) $this->input->post('observacao', TRUE));
        if (strlen($observacao) > 2000) {
            $erros[] = 'A observação deve possuir no máximo 2000 caracteres.';
        }

        return ['localizacao' => $localizacao, 'erros' => $erros];
    }

    private function validar_requisicao_post()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
    }

    private function validar_data($data)
    {
        if ($data === '') {
            return TRUE;
        }

        $objeto = DateTime::createFromFormat('Y-m-d', $data);
        return $objeto && $objeto->format('Y-m-d') === $data;
    }
}
