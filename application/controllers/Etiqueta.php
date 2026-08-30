<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Etiqueta extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->controle_acesso->valida_permissao(
            'etiquetas.gerar'
        );
        $this->load->model('localizacao_model');
        $this->load->model('localizacao_tipo_documento_model');
    }

    public function localizacao($protocolo = NULL)
    {
        $protocolo = strtoupper(trim((string) $protocolo));

        if (!protocolo_valido($protocolo, 'LOC')) {
            show_404();
        }

        $localizacao = $this->localizacao_model->buscar_por_protocolo(
            $protocolo
        );

        if (!$localizacao) {
            show_404();
        }

        $dados = [
            'localizacao' => $localizacao,
            'tipo_documento' => $this->localizacao_tipo_documento_model->buscar_por_localizacao(
                $localizacao['codigo']
            ),
            'url_consulta' => base_url(
                'consulta/localizacao/' . rawurlencode($localizacao['protocolo'])
            )
        ];

        $this->load->view(
            'localizacao/localizacao_etiqueta',
            $dados
        );
    }
}
