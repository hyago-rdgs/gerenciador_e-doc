<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->controle_acesso->valida_acesso();
        $this->load->model('dashboard_model');
    }

    public function index()
    {
        $this->controle_acesso->valida_permissao('dashboard.visualizar');
        $this->load->view('dashboard/dashboard');
    }

    public function dados()
    {
        $this->controle_acesso->valida_permissao('dashboard.visualizar');

        if ($this->input->method() !== 'get') {
            show_404();
        }

        $dados = [
            'resumo' => $this->dashboard_model->obter_resumo(),
            'documentos_por_mes' => $this->dashboard_model->listar_documentos_por_mes(12),
            'documentos_por_tipo' => $this->dashboard_model->listar_documentos_por_tipo(10),
            'documentos_por_localizacao' => $this->dashboard_model->listar_documentos_por_localizacao(10),
            'digitalizacao' => $this->dashboard_model->obter_digitalizacao(),
            'atencoes' => $this->dashboard_model->obter_atencoes(),
            'movimentacoes_recentes' => $this->dashboard_model->listar_movimentacoes_recentes(8)
        ];

        resposta_json(
            TRUE,
            'Indicadores carregados com sucesso.',
            $dados
        );
    }
}
