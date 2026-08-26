<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Documento extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->controle_acesso->valida_acesso();
        $this->load->model('documento_model');
        $this->load->model('documento_metadado_model');
        $this->load->model('documento_arquivo_model');
        $this->load->model('documento_movimentacao_model');
        $this->load->model('tipo_documento_model');
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
        $filtro_termo = $this->input->get('termo', TRUE) !== NULL ? $this->input->get('termo', TRUE) : '';
        $filtro_tipo_documento = $this->input->get('tipo_documento_codigo', TRUE) !== NULL ? $this->input->get('tipo_documento_codigo', TRUE) : '';
        $filtro_localizacao = $this->input->get('localizacao_codigo', TRUE) !== NULL ? $this->input->get('localizacao_codigo', TRUE) : '';
        $filtro_status = $this->input->get('status', TRUE) !== NULL ? $this->input->get('status', TRUE) : '';

        $pagina_atual = (int) $this->input->get('pagina', TRUE);
        if ($pagina_atual < 1) {
            $pagina_atual = 1;
        }

        $limite = 20;
        $offset = ($pagina_atual - 1) * $limite;

        $total_documentos = $this->documento_model->contar_tudo(
            $filtro_termo,
            $filtro_tipo_documento,
            $filtro_localizacao,
            $filtro_status
        );

        $dados = [
            'filtro_termo' => $filtro_termo,
            'filtro_tipo_documento' => $filtro_tipo_documento,
            'filtro_localizacao' => $filtro_localizacao,
            'filtro_status' => $filtro_status,
            'documentos' => $this->documento_model->listar_tudo(
                $filtro_termo,
                $filtro_tipo_documento,
                $filtro_localizacao,
                $filtro_status,
                $limite,
                $offset
            ),
            'tipos_documento' => $this->tipo_documento_model->listar_opcoes(),
            'localizacoes' => $this->localizacao_model->listar_opcoes(),
            'total_documentos' => $total_documentos,
            'limite' => $limite,
            'offset' => $offset + 1,
            'pagina_atual' => $pagina_atual,
            'total_paginas' => ceil($total_documentos / $limite)
        ];

        $this->load->view('documento/documento_lista', $dados);
    }

    public function cadastrar($localizacao_codigo = NULL)
    {
        $localizacao_selecionada = NULL;
        $tipo_documento_selecionado = NULL;

        if ($localizacao_codigo !== NULL) {
            if (
                !ctype_digit((string) $localizacao_codigo) ||
                (int) $localizacao_codigo <= 0
            ) {
                show_404();
            }

            $localizacao_codigo = (int) $localizacao_codigo;
            $localizacao_selecionada = $this->localizacao_model->buscar_por_codigo(
                $localizacao_codigo
            );

            if (
                !$localizacao_selecionada ||
                (int) $localizacao_selecionada['ativo'] !== 1
            ) {
                show_404();
            }

            $tipo_documento_selecionado = $this->localizacao_tipo_documento_model->buscar_por_localizacao(
                $localizacao_codigo
            );

            if (
                !$tipo_documento_selecionado ||
                (int) $tipo_documento_selecionado['tipo_documento_ativo'] !== 1
            ) {
                show_404();
            }
        }

        if ($this->input->method() === 'post') {
            $dados_post = $this->input->post();

            if ($localizacao_selecionada) {
                $dados_post['localizacao_codigo'] = $localizacao_selecionada['codigo'];
                $dados_post['tipo_documento_codigo'] = $tipo_documento_selecionado['tipo_documento_codigo'];
            }

            $resultado = $this->validar($dados_post);

            if (!$resultado['sucesso']) {
                resposta_json(
                    FALSE,
                    'Verifique os campos informados.',
                    ['erros' => $resultado['erros']],
                    422
                );
            }

            $confirmar = $this->input->post('confirmar') === '1';

            if (!$confirmar) {
                $tipo_documento = $this->tipo_documento_model->buscar_por_codigo(
                    $resultado['dados']['tipo_documento_codigo']
                );

                $localizacao = $this->localizacao_model->buscar_por_codigo(
                    $resultado['dados']['localizacao_codigo']
                );

                $campos_metadados = $this->documento_metadado_model->listar_campos_tipo(
                    $resultado['dados']['tipo_documento_codigo']
                );

                $html = $this->load->view(
                    'documento/documento_revisao',
                    [
                        'documento' => $resultado['dados'],
                        'metadados' => $resultado['metadados'],
                        'tipo_documento' => $tipo_documento,
                        'localizacao' => $localizacao,
                        'campos_metadados' => $campos_metadados
                    ],
                    TRUE
                );

                resposta_json(
                    TRUE,
                    'Revise os dados antes de confirmar o cadastro.',
                    [
                        'confirmacao' => TRUE,
                        'html' => $html
                    ]
                );
            }

            $this->db->trans_begin();

            $codigo = $this->documento_model->cadastrar(
                $resultado['dados']
            );

            $metadados_salvos = $codigo
                ? $this->documento_metadado_model->salvar(
                    $codigo,
                    $resultado['metadados']
                )
                : FALSE;

            $movimentacao_salva = $codigo
                ? $this->documento_movimentacao_model->cadastrar([
                    'documento_codigo' => $codigo,
                    'localizacao_origem_codigo' => NULL,
                    'localizacao_destino_codigo' => $resultado['dados']['localizacao_codigo'],
                    'tipo_movimentacao' => 'CADASTRO'
                ])
                : FALSE;

            if (
                !$codigo ||
                !$metadados_salvos ||
                !$movimentacao_salva ||
                $this->db->trans_status() === FALSE
            ) {
                $this->db->trans_rollback();

                resposta_json(
                    FALSE,
                    'Não foi possível cadastrar o documento.',
                    [],
                    500
                );
            }

            $this->db->trans_commit();

            resposta_json(
                TRUE,
                'Documento cadastrado com sucesso.',
                ['codigo' => $codigo],
                201
            );
        }

        $dados = [
            'tipos_documento' => $this->tipo_documento_model->listar_opcoes(),
            'localizacoes' => $this->localizacao_model->listar_opcoes(),
            'localizacao_selecionada' => $localizacao_selecionada,
            'tipo_documento_selecionado' => $tipo_documento_selecionado
        ];

        $this->load->view('documento/documento_form', $dados);
    }

    public function atualizar($codigo = NULL)
    {
        $documento = $this->buscar_documento($codigo);
        $codigo = (int) $documento['codigo'];

        if ($this->input->method() === 'post') {
            $resultado = $this->validar($this->input->post());

            if (!$resultado['sucesso']) {
                resposta_json(
                    FALSE,
                    'Verifique os campos informados.',
                    ['erros' => $resultado['erros']],
                    422
                );
            }

            $this->db->trans_begin();

            $documento_atualizado = $this->documento_model->atualizar(
                $codigo,
                $resultado['dados']
            );

            $metadados_salvos = $this->documento_metadado_model->salvar(
                $codigo,
                $resultado['metadados']
            );

            $movimentacao_salva = TRUE;

            if (
                (int) $documento['localizacao_codigo'] !==
                (int) $resultado['dados']['localizacao_codigo']
            ) {
                $movimentacao_salva = $this->documento_movimentacao_model->cadastrar([
                    'documento_codigo' => $codigo,
                    'localizacao_origem_codigo' => $documento['localizacao_codigo'],
                    'localizacao_destino_codigo' => $resultado['dados']['localizacao_codigo'],
                    'tipo_movimentacao' => 'TRANSFERENCIA'
                ]);
            }

            if (
                !$documento_atualizado ||
                !$metadados_salvos ||
                !$movimentacao_salva ||
                $this->db->trans_status() === FALSE
            ) {
                $this->db->trans_rollback();

                resposta_json(
                    FALSE,
                    'Não foi possível atualizar o documento.',
                    [],
                    500
                );
            }

            $this->db->trans_commit();

            resposta_json(
                TRUE,
                'Documento atualizado com sucesso.',
                ['codigo' => $codigo]
            );
        }

        $dados = [
            'documento' => $documento,
            'tipos_documento' => $this->tipo_documento_model->listar_opcoes(),
            'localizacoes' => $this->localizacao_model->listar_opcoes(),
            'localizacao_selecionada' => NULL,
            'tipo_documento_selecionado' => NULL
        ];

        $this->load->view('documento/documento_form', $dados);
    }

    public function excluir($codigo = NULL)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $documento = $this->buscar_documento($codigo, TRUE);
        $codigo = (int) $documento['codigo'];

        $this->db->trans_begin();

        $metadados_excluidos = $this->documento_metadado_model->excluir_por_documento(
            $codigo
        );

        $arquivos_excluidos = $this->documento_arquivo_model->excluir_por_documento(
            $codigo
        );

        $documento_excluido = $this->documento_model->excluir(
            $codigo
        );

        if (
            !$metadados_excluidos ||
            !$arquivos_excluidos ||
            !$documento_excluido ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível excluir o documento.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Documento excluído com sucesso.',
            ['codigo' => $codigo]
        );
    }

    public function detalhes($codigo = NULL)
    {
        $documento = $this->buscar_documento($codigo);

        $dados = [
            'documento' => $documento,
            'caminho_localizacao' => $this->localizacao_model->buscar_caminho(
                $documento['localizacao_codigo']
            ),
            'metadados' => $this->documento_metadado_model->listar_por_documento(
                $documento['codigo']
            ),
            'arquivos' => $this->documento_arquivo_model->listar_por_documento(
                $documento['codigo']
            ),
            'movimentacoes' => $this->documento_movimentacao_model->listar_por_documento(
                $documento['codigo']
            )
        ];

        $this->load->view('documento/documento_detalhes', $dados);
    }

    public function campos_metadados(
        $tipo_documento_codigo = NULL,
        $documento_codigo = NULL
    ) {
        if (
            empty($tipo_documento_codigo) ||
            !ctype_digit((string) $tipo_documento_codigo)
        ) {
            resposta_json(
                FALSE,
                'O tipo de documento informado é inválido.',
                [],
                422
            );
        }

        $tipo_documento = $this->tipo_documento_model->buscar_por_codigo(
            (int) $tipo_documento_codigo
        );

        if (
            !$tipo_documento ||
            (int) $tipo_documento['ativo'] !== 1
        ) {
            resposta_json(
                FALSE,
                'O tipo de documento não existe ou está inativo.',
                [],
                404
            );
        }

        $documento_codigo_consulta = NULL;

        if ($documento_codigo !== NULL) {
            $documento = $this->buscar_documento(
                $documento_codigo,
                TRUE
            );

            if (
                (int) $documento['tipo_documento_codigo'] ===
                (int) $tipo_documento_codigo
            ) {
                $documento_codigo_consulta = (int) $documento['codigo'];
            }
        }

        $dados = [
            'campos_metadados' => $this->documento_metadado_model->listar_campos_tipo(
                (int) $tipo_documento_codigo,
                $documento_codigo_consulta
            )
        ];

        $html = $this->load->view(
            'documento/documento_campos_metadados',
            $dados,
            TRUE
        );

        resposta_json(
            TRUE,
            'Campos carregados com sucesso.',
            ['html' => $html]
        );
    }

    public function acessar_arquivo(
        $codigo = NULL,
        $arquivo_codigo = NULL
    ) {
        if ($this->input->method() !== 'get') {
            show_404();
        }

        $documento = $this->buscar_documento($codigo);
        $arquivo = $this->buscar_arquivo(
            $documento['codigo'],
            $arquivo_codigo
        );

        $diretorio = realpath(
            FCPATH . 'uploads/documentos/' . $documento['codigo']
        );

        $caminho = realpath(
            FCPATH . $arquivo['caminho']
        );

        if (
            !$diretorio ||
            !$caminho ||
            strpos(
                $caminho,
                $diretorio . DIRECTORY_SEPARATOR
            ) !== 0 ||
            !is_file($caminho) ||
            !is_readable($caminho)
        ) {
            show_404();
        }

        $mime_type = !empty($arquivo['mime_type'])
            ? $arquivo['mime_type']
            : 'application/octet-stream';

        if (
            !preg_match(
                '/^[a-z0-9.+-]+\/[a-z0-9.+-]+$/i',
                $mime_type
            )
        ) {
            $mime_type = 'application/octet-stream';
        }

        $nome_original = str_replace(
            ['"', "\r", "\n"],
            '',
            basename($arquivo['nome_original'])
        );

        $this->output
            ->set_content_type($mime_type)
            ->set_header('X-Content-Type-Options: nosniff')
            ->set_header(
                'Content-Disposition: inline; filename="' .
                $nome_original .
                '"; filename*=UTF-8\'\'' .
                rawurlencode($nome_original)
            )
            ->set_header(
                'Content-Length: ' . filesize($caminho)
            )
            ->_display();

        readfile($caminho);
        exit;
    }

    public function cadastrar_arquivo($codigo = NULL)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $documento = $this->buscar_documento($codigo, TRUE);

        if (!isset($_FILES['arquivo'])) {
            resposta_json(
                FALSE,
                'Selecione um arquivo.',
                ['erros' => ['O arquivo é obrigatório.']],
                422
            );
        }

        $erros_upload = [
            UPLOAD_ERR_INI_SIZE => 'O arquivo ultrapassa o limite permitido pelo servidor.',
            UPLOAD_ERR_FORM_SIZE => 'O arquivo ultrapassa o limite permitido pelo formulário.',
            UPLOAD_ERR_PARTIAL => 'O arquivo foi enviado parcialmente. Tente novamente.',
            UPLOAD_ERR_NO_FILE => 'Selecione um arquivo.',
            UPLOAD_ERR_NO_TMP_DIR => 'O diretório temporário do servidor não está disponível.',
            UPLOAD_ERR_CANT_WRITE => 'O servidor não conseguiu gravar o arquivo.',
            UPLOAD_ERR_EXTENSION => 'O envio foi interrompido por uma extensão do servidor.'
        ];

        if ($_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
            $erro = $erros_upload[$_FILES['arquivo']['error']]
                ?? 'Não foi possível receber o arquivo.';

            resposta_json(
                FALSE,
                'Não foi possível enviar o arquivo.',
                ['erros' => [$erro]],
                422
            );
        }

        $diretorio = FCPATH . 'uploads/documentos/' . $documento['codigo'] . '/';

        if (!is_dir($diretorio) && !mkdir($diretorio, 0775, TRUE)) {
            resposta_json(
                FALSE,
                'Não foi possível preparar o diretório do documento.',
                [],
                500
            );
        }

        if (!is_writable($diretorio)) {
            resposta_json(
                FALSE,
                'O diretório do documento não possui permissão de escrita.',
                [],
                500
            );
        }

        $configuracao = [
            'upload_path' => $diretorio,
            'allowed_types' => 'pdf|doc|docx|xls|xlsx|csv|txt|jpg|jpeg|png',
            'max_size' => 20480,
            'encrypt_name' => TRUE
        ];

        $this->load->library('upload');
        $this->upload->initialize($configuracao, TRUE);

        if (!$this->upload->do_upload('arquivo')) {
            resposta_json(
                FALSE,
                'Não foi possível enviar o arquivo.',
                ['erros' => [$this->upload->display_errors('', '')]],
                422
            );
        }

        $arquivo = $this->upload->data();
        $principal = $this->documento_arquivo_model->possui_arquivos(
            $documento['codigo']
        ) ? 0 : 1;

        $arquivo_codigo = $this->documento_arquivo_model->cadastrar([
            'documento_codigo' => $documento['codigo'],
            'nome_original' => $arquivo['orig_name'],
            'nome_armazenado' => $arquivo['file_name'],
            'extensao' => ltrim($arquivo['file_ext'], '.'),
            'mime_type' => $arquivo['file_type'],
            'caminho' => 'uploads/documentos/' . $documento['codigo'] . '/' . $arquivo['file_name'],
            'tamanho' => round($arquivo['file_size'] * 1024),
            'versao' => 1,
            'principal' => $principal
        ]);

        if (!$arquivo_codigo) {
            unlink($arquivo['full_path']);
            resposta_json(
                FALSE,
                'Não foi possível cadastrar o arquivo.',
                [],
                500
            );
        }

        resposta_json(
            TRUE,
            'Arquivo enviado com sucesso.',
            ['codigo' => $arquivo_codigo],
            201
        );
    }

    public function definir_arquivo_principal($codigo = NULL, $arquivo_codigo = NULL)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $documento = $this->buscar_documento($codigo, TRUE);
        $arquivo = $this->buscar_arquivo(
            $documento['codigo'],
            $arquivo_codigo
        );

        if (
            !$this->documento_arquivo_model->definir_principal(
                $documento['codigo'],
                $arquivo['codigo']
            )
        ) {
            resposta_json(
                FALSE,
                'Não foi possível definir o arquivo principal.',
                [],
                500
            );
        }

        resposta_json(
            TRUE,
            'Arquivo principal atualizado com sucesso.',
            ['codigo' => (int) $arquivo['codigo']]
        );
    }

    public function excluir_arquivo(
        $codigo = NULL,
        $arquivo_codigo = NULL
    ) {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $documento = $this->buscar_documento($codigo, TRUE);

        $arquivo = $this->buscar_arquivo(
            $documento['codigo'],
            $arquivo_codigo
        );

        $this->db->trans_begin();

        $arquivo_excluido = $this->documento_arquivo_model->excluir(
            $arquivo['codigo']
        );

        $principal_definido = TRUE;

        if (
            $arquivo_excluido &&
            (int) $arquivo['principal'] === 1
        ) {
            $principal_definido = $this->documento_arquivo_model->definir_novo_principal(
                $documento['codigo']
            );
        }

        if (
            !$arquivo_excluido ||
            !$principal_definido ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível excluir o arquivo.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Arquivo excluído com sucesso.',
            ['codigo' => (int) $arquivo['codigo']]
        );
    }

    private function buscar_documento($codigo, $resposta_json = FALSE)
    {
        if (empty($codigo) || !ctype_digit((string) $codigo)) {
            if ($resposta_json) {
                resposta_json(
                    FALSE,
                    'Não foi possível identificar o documento.',
                    ['erros' => ['O código do documento é inválido.']],
                    422
                );
            }

            show_404();
        }

        $documento = $this->documento_model->buscar_por_codigo(
            (int) $codigo
        );

        if (!$documento) {
            if ($resposta_json) {
                resposta_json(
                    FALSE,
                    'Documento não encontrado ou já excluído.',
                    [],
                    404
                );
            }

            show_404();
        }

        return $documento;
    }

    private function buscar_arquivo($documento_codigo, $arquivo_codigo)
    {
        if (empty($arquivo_codigo) || !ctype_digit((string) $arquivo_codigo)) {
            resposta_json(
                FALSE,
                'Não foi possível identificar o arquivo.',
                [],
                422
            );
        }

        $arquivo = $this->documento_arquivo_model->buscar_por_codigo(
            (int) $arquivo_codigo
        );

        if (
            !$arquivo ||
            (int) $arquivo['documento_codigo'] !== (int) $documento_codigo
        ) {
            resposta_json(
                FALSE,
                'Arquivo não encontrado ou já excluído.',
                [],
                404
            );
        }

        return $arquivo;
    }

    private function validar($reg)
    {
        $reg = [
            'tipo_documento_codigo' => trim($reg['tipo_documento_codigo'] ?? ''),
            'localizacao_codigo' => trim($reg['localizacao_codigo'] ?? ''),
            'titulo' => trim($reg['titulo'] ?? ''),
            'descricao' => trim($reg['descricao'] ?? ''),
            'numero_identificacao' => trim($reg['numero_identificacao'] ?? ''),
            'data_documento' => trim($reg['data_documento'] ?? ''),
            'ativo' => trim($reg['ativo'] ?? ''),
            'metadados' => is_array($reg['metadados'] ?? NULL)
                ? $reg['metadados']
                : []
        ];

        $erros = [];
        $tipo_documento = NULL;
        $localizacao = NULL;

        if ($reg['titulo'] === '') {
            $erros[] = 'O campo Título é obrigatório.';
        } elseif (strlen($reg['titulo']) > 255) {
            $erros[] = 'O campo Título deve possuir no máximo 255 caracteres.';
        }

        if (
            $reg['numero_identificacao'] !== '' &&
            strlen($reg['numero_identificacao']) > 100
        ) {
            $erros[] = 'O número de identificação deve possuir no máximo 100 caracteres.';
        }

        if (ctype_digit($reg['tipo_documento_codigo'])) {
            $tipo_documento = $this->tipo_documento_model->buscar_por_codigo(
                (int) $reg['tipo_documento_codigo']
            );
        }

        if (
            !$tipo_documento ||
            (int) $tipo_documento['ativo'] !== 1
        ) {
            $erros[] = 'O tipo de documento informado não existe ou está inativo.';
        }

        if (ctype_digit($reg['localizacao_codigo'])) {
            $localizacao = $this->localizacao_model->buscar_por_codigo(
                (int) $reg['localizacao_codigo']
            );
        }

        if (
            !$localizacao ||
            (int) $localizacao['ativo'] !== 1
        ) {
            $erros[] = 'A localização informada não existe ou está inativa.';
        }

        if ($tipo_documento && $localizacao) {
            $vinculo_localizacao = $this->localizacao_tipo_documento_model->buscar_por_localizacao(
                (int) $reg['localizacao_codigo']
            );

            if (!$vinculo_localizacao) {
                $erros[] = 'A localização selecionada não possui um tipo de documento definido.';
            } elseif (
                (int) $vinculo_localizacao['tipo_documento_codigo'] !==
                (int) $reg['tipo_documento_codigo']
            ) {
                $erros[] = 'O tipo do documento não é compatível com a localização selecionada.';
            }
        }

        if (!in_array($reg['ativo'], ['1', '0'], TRUE)) {
            $erros[] = 'O status informado é inválido.';
        }

        if (
            $reg['data_documento'] !== '' &&
            !$this->validar_data(
                $reg['data_documento'],
                'Y-m-d'
            )
        ) {
            $erros[] = 'A data do documento é inválida.';
        }

        $metadados = [];

        if (
            $tipo_documento &&
            (int) $tipo_documento['ativo'] === 1
        ) {
            $resultado_metadados = $this->validar_metadados(
                (int) $reg['tipo_documento_codigo'],
                $reg['metadados']
            );

            $metadados = $resultado_metadados['dados'];
            $erros = array_merge(
                $erros,
                $resultado_metadados['erros']
            );
        }

        if (!empty($erros)) {
            return [
                'sucesso' => FALSE,
                'erros' => $erros
            ];
        }

        unset($reg['metadados']);

        $reg['tipo_documento_codigo'] = (int) $reg['tipo_documento_codigo'];
        $reg['localizacao_codigo'] = (int) $reg['localizacao_codigo'];
        $reg['ativo'] = (int) $reg['ativo'];
        $reg['descricao'] = $reg['descricao'] !== ''
            ? $reg['descricao']
            : NULL;
        $reg['numero_identificacao'] = $reg['numero_identificacao'] !== ''
            ? $reg['numero_identificacao']
            : NULL;
        $reg['data_documento'] = $reg['data_documento'] !== ''
            ? $reg['data_documento']
            : NULL;

        return [
            'sucesso' => TRUE,
            'dados' => $reg,
            'metadados' => $metadados
        ];
    }

    private function validar_metadados(
        $tipo_documento_codigo,
        $metadados_recebidos
    ) {
        $campos = $this->documento_metadado_model->listar_campos_tipo(
            $tipo_documento_codigo
        );

        $metadados = [];
        $erros = [];

        foreach ($campos as $campo) {
            $codigo = (int) $campo['metadado_codigo'];
            $valor = $metadados_recebidos[$codigo] ?? '';

            $opcoes = array_values(
                array_filter(
                    array_map(
                        'trim',
                        preg_split(
                            '/\r\n|\r|\n/',
                            (string) ($campo['opcoes'] ?? '')
                        )
                    ),
                    'strlen'
                )
            );

            if (
                $campo['tipo_campo'] === 'checkbox' &&
                !empty($opcoes)
            ) {
                $valor = is_array($valor)
                    ? $valor
                    : [];

                $valor = array_values(
                    array_unique(
                        array_filter(
                            array_map('trim', $valor),
                            'strlen'
                        )
                    )
                );

                if (
                    (int) $campo['obrigatorio'] === 1 &&
                    empty($valor)
                ) {
                    $erros[] = 'O campo ' . $campo['nome'] . ' é obrigatório.';
                    continue;
                }

                $opcoes_invalidas = array_diff(
                    $valor,
                    $opcoes
                );

                if (!empty($opcoes_invalidas)) {
                    $erros[] = 'O campo ' . $campo['nome'] . ' possui uma opção inválida.';
                    continue;
                }

                if (!empty($valor)) {
                    $metadados[$codigo] = $valor;
                }

                continue;
            }

            if (is_array($valor)) {
                $erros[] = 'O valor informado para ' . $campo['nome'] . ' é inválido.';
                continue;
            }

            $valor = trim((string) $valor);

            if (
                (int) $campo['obrigatorio'] === 1 &&
                $valor === ''
            ) {
                $erros[] = 'O campo ' . $campo['nome'] . ' é obrigatório.';
                continue;
            }

            if ($valor === '') {
                continue;
            }

            if (strlen($valor) > 65535) {
                $erros[] = 'O campo ' . $campo['nome'] . ' ultrapassa o tamanho permitido.';
                continue;
            }

            if (
                in_array(
                    $campo['tipo_campo'],
                    ['select', 'radio'],
                    TRUE
                ) &&
                !in_array($valor, $opcoes, TRUE)
            ) {
                $erros[] = 'O campo ' . $campo['nome'] . ' possui uma opção inválida.';
                continue;
            }

            if (
                $campo['tipo_campo'] === 'checkbox' &&
                !in_array($valor, ['1', '0'], TRUE)
            ) {
                $erros[] = 'O campo ' . $campo['nome'] . ' é inválido.';
                continue;
            }

            if (
                $campo['tipo_campo'] === 'number' &&
                !is_numeric($valor)
            ) {
                $erros[] = 'O campo ' . $campo['nome'] . ' deve ser numérico.';
                continue;
            }

            if (
                $campo['tipo_campo'] === 'email' &&
                !filter_var($valor, FILTER_VALIDATE_EMAIL)
            ) {
                $erros[] = 'O campo ' . $campo['nome'] . ' deve conter um e-mail válido.';
                continue;
            }

            if (
                $campo['tipo_campo'] === 'url' &&
                !filter_var($valor, FILTER_VALIDATE_URL)
            ) {
                $erros[] = 'O campo ' . $campo['nome'] . ' deve conter uma URL válida.';
                continue;
            }

            $formatos_data = [
                'date' => 'Y-m-d',
                'time' => 'H:i',
                'datetime-local' => 'Y-m-d\TH:i'
            ];

            if (
                isset($formatos_data[$campo['tipo_campo']]) &&
                !$this->validar_data(
                    $valor,
                    $formatos_data[$campo['tipo_campo']]
                )
            ) {
                $erros[] = 'O campo ' . $campo['nome'] . ' possui uma data ou horário inválido.';
                continue;
            }

            $metadados[$codigo] = $valor;
        }

        return [
            'sucesso' => empty($erros),
            'erros' => $erros,
            'dados' => $metadados
        ];
    }

    private function validar_data($valor, $formato)
    {
        $data = DateTime::createFromFormat(
            '!' . $formato,
            $valor
        );

        return (
            $data &&
            $data->format($formato) === $valor
        );
    }
}
