<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Consulta extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('localizacao_model');
        $this->load->model('localizacao_tipo_documento_model');
        $this->load->model('documento_model');
        $this->load->model('documento_metadado_model');
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

        $this->output->set_header(
            'Cache-Control: no-store, no-cache, must-revalidate, max-age=0'
        );
        $this->output->set_header('Pragma: no-cache');

        $autenticado = $this->controle_acesso->logado() &&
            $this->controle_acesso->tem_permissao(
                'localizacoes.visualizar'
            );

        $pode_visualizar_documentos = $autenticado &&
            $this->controle_acesso->tem_permissao(
                'documentos.visualizar'
            );

        $dados = [
            'autenticado' => $autenticado,
            'pode_visualizar_documentos' => $pode_visualizar_documentos,
            'protocolo' => $localizacao['protocolo']
        ];

        if ($autenticado) {
            $documentos = $pode_visualizar_documentos
                ? $this->documento_model->listar_por_localizacao(
                    $localizacao['codigo']
                )
                : [];

            $metadados = $this->preparar_metadados($documentos);

            foreach ($documentos as $indice => $documento) {
                $documentos[$indice]['metadados'] = $metadados[
                    $documento['codigo']
                ] ?? [];
            }

            $dados['localizacao'] = $localizacao;
            $dados['tipo_documento'] = $this->localizacao_tipo_documento_model->buscar_por_localizacao(
                $localizacao['codigo']
            );
            $dados['caminho'] = $this->localizacao_model->buscar_caminho(
                $localizacao['codigo']
            );
            $dados['documentos'] = $documentos;
            $dados['total_documentos'] = count($documentos);
        }

        $this->load->view(
            'consulta/consulta_localizacao',
            $dados
        );
    }

    private function preparar_metadados($documentos)
    {
        if (empty($documentos)) {
            return [];
        }

        $codigos = array_column(
            $documentos,
            'codigo'
        );

        $registros = $this->documento_metadado_model->listar_por_documentos(
            $codigos
        );

        $metadados = [];

        foreach ($registros as $registro) {
            $documento_codigo = (int) $registro['documento_codigo'];

            if (!isset($metadados[$documento_codigo])) {
                $metadados[$documento_codigo] = [];
            }

            $metadados[$documento_codigo][] = $registro;
        }

        return $metadados;
    }
}
