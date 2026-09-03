<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Relatorio extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->controle_acesso->valida_acesso();
        $this->controle_acesso->valida_permissao(
            'relatorios.visualizar'
        );

        $this->load->model('relatorio_model');
    }

    public function index()
    {
        $this->load->view('relatorio/relatorio_inicio');
    }

    public function acervo()
    {
        $filtros = $this->obter_filtros_acervo();
        $pagina_atual = max(
            1,
            (int) $this->input->get('pagina', TRUE)
        );
        $limite = 20;
        $offset = ($pagina_atual - 1) * $limite;

        $total_documentos = $this->relatorio_model->contar_acervo(
            $filtros
        );

        $dados = $this->preparar_dados_acervo(
            $filtros,
            $limite,
            $offset
        );

        $dados['total_documentos'] = $total_documentos;
        $dados['limite'] = $limite;
        $dados['offset'] = $offset + 1;
        $dados['pagina_atual'] = $pagina_atual;
        $dados['total_paginas'] = (int) ceil(
            $total_documentos / $limite
        );
        $dados['pode_exportar'] =
            $this->controle_acesso->tem_permissao(
                'relatorios.exportar'
            );

        $this->load->view(
            'relatorio/relatorio_acervo',
            $dados
        );
    }

    public function acervo_pdf()
    {
        $this->controle_acesso->valida_permissao(
            'relatorios.exportar'
        );

        $filtros = $this->obter_filtros_acervo();
        $total_documentos = $this->relatorio_model->contar_acervo(
            $filtros
        );

        if ($total_documentos > 2000) {
            show_error(
                'O PDF suporta até 2.000 registros. Aplique filtros para reduzir o resultado.',
                422,
                'Relatório muito grande'
            );
        }

        $dados = $this->preparar_dados_acervo($filtros);
        $dados['total_documentos'] = $total_documentos;
        $dados['emitido_em'] = date('d/m/Y H:i:s');

        $this->carregar_dependencias_relatorios();

        $temp_dir = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'edoc-mpdf';

        if (
            !is_dir($temp_dir) &&
            !mkdir($temp_dir, 0775, TRUE) &&
            !is_dir($temp_dir)
        ) {
            show_error(
                'Não foi possível preparar o diretório temporário do relatório.',
                500
            );
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 12,
            'margin_bottom' => 14,
            'tempDir' => $temp_dir
        ]);

        $mpdf->SetTitle('Relatório de Acervo | e-Doc');
        $mpdf->SetAuthor('e-Doc');
        $mpdf->SetHTMLFooter(
            '<div style="text-align:center;font-size:8pt;color:#666;">'
                . 'Página {PAGENO} de {nbpg}'
                . '</div>'
        );

        $html_cabecalho = $this->load->view(
            'relatorio/pdf/relatorio_acervo',
            $dados,
            TRUE
        );

        $mpdf->WriteHTML($html_cabecalho);

        $lotes = array_chunk(
            $dados['documentos'],
            50
        );

        foreach ($lotes as $documentos) {
            $html_tabela = $this->load->view(
                'relatorio/pdf/relatorio_acervo_tabela',
                [
                    'documentos' => $documentos
                ],
                TRUE
            );

            $mpdf->WriteHTML($html_tabela);
        }

        $this->limpar_buffer_saida();

        $mpdf->Output(
            'relatorio-acervo-' . date('Ymd-His') . '.pdf',
            \Mpdf\Output\Destination::INLINE
        );
        exit;
    }

    public function acervo_excel()
    {
        $this->controle_acesso->valida_permissao(
            'relatorios.exportar'
        );

        $filtros = $this->obter_filtros_acervo();
        $total_documentos = $this->relatorio_model->contar_acervo(
            $filtros
        );

        if ($total_documentos > 20000) {
            show_error(
                'A exportação Excel suporta até 20.000 registros. Aplique filtros para reduzir o resultado.',
                422,
                'Relatório muito grande'
            );
        }

        $dados = $this->preparar_dados_acervo($filtros);

        $this->carregar_dependencias_relatorios();

        $planilha = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $aba = $planilha->getActiveSheet();
        $aba->setTitle('Acervo');

        $aba->mergeCells('A1:I1');
        $aba->setCellValue('A1', 'Relatório de Acervo — e-Doc');
        $aba->getStyle('A1')->getFont()->setBold(TRUE)->setSize(14);

        $aba->setCellValue('A2', 'Emitido em');
        $aba->setCellValue('B2', date('d/m/Y H:i:s'));
        $aba->setCellValue('A3', 'Filtros');
        $aba->setCellValue(
            'B3',
            implode(' | ', $dados['filtros_descricao'])
        );

        $cabecalhos = [
            'Protocolo',
            'Título',
            'Nº identificação',
            'Tipo',
            'Localização',
            'Data documento',
            'Cadastro',
            'Digitalização',
            'Arquivos'
        ];

        foreach ($cabecalhos as $indice => $cabecalho) {
            $coluna = chr(ord('A') + $indice);
            $aba->setCellValue($coluna . '5', $cabecalho);
        }

        $aba->getStyle('A5:I5')->getFont()->setBold(TRUE);
        $aba->freezePane('A6');
        $aba->setAutoFilter('A5:I5');

        $linha = 6;

        foreach ($dados['documentos'] as $documento) {
            $valores = [
                $documento['protocolo'],
                $documento['titulo'],
                $documento['numero_identificacao'] ?? '',
                $documento['tipo_documento'],
                $documento['localizacao_classificacao']
                    . ' — '
                    . $documento['localizacao'],
                !empty($documento['data_documento'])
                    ? date(
                        'd/m/Y',
                        strtotime($documento['data_documento'])
                    )
                    : '',
                date(
                    'd/m/Y H:i',
                    strtotime($documento['cadastro'])
                ),
                (int) $documento['total_arquivos'] > 0
                    ? 'Com arquivo'
                    : 'Sem arquivo',
                (int) $documento['total_arquivos']
            ];

            foreach ($valores as $indice => $valor) {
                $coluna = chr(ord('A') + $indice);

                if ($indice === 8) {
                    $aba->setCellValue(
                        $coluna . $linha,
                        (int) $valor
                    );
                    continue;
                }

                $aba->setCellValueExplicit(
                    $coluna . $linha,
                    (string) $valor,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
            }

            $linha++;
        }

        foreach (range('A', 'I') as $coluna) {
            $aba->getColumnDimension($coluna)->setAutoSize(TRUE);
        }

        $arquivo = 'relatorio-acervo-'
            . date('Ymd-His')
            . '.xlsx';

        $this->limpar_buffer_saida();

        header(
            'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        header(
            'Content-Disposition: attachment; filename="'
                . $arquivo
                . '"'
        );
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx(
            $planilha
        );
        $writer->save('php://output');

        $planilha->disconnectWorksheets();
        unset($planilha);
        exit;
    }

    public function movimentacoes()
    {
        $filtros = $this->obter_filtros_movimentacoes();
        $pagina_atual = max(
            1,
            (int) $this->input->get('pagina', TRUE)
        );
        $limite = 20;
        $offset = ($pagina_atual - 1) * $limite;

        $total_movimentacoes =
            $this->relatorio_model->contar_movimentacoes(
                $filtros
            );

        $dados = $this->preparar_dados_movimentacoes(
            $filtros,
            $limite,
            $offset
        );

        $dados['total_movimentacoes'] =
            $total_movimentacoes;
        $dados['limite'] = $limite;
        $dados['offset'] = $offset + 1;
        $dados['pagina_atual'] = $pagina_atual;
        $dados['total_paginas'] = (int) ceil(
            $total_movimentacoes / $limite
        );
        $dados['pode_exportar'] =
            $this->controle_acesso->tem_permissao(
                'relatorios.exportar'
            );

        $this->load->view(
            'relatorio/relatorio_movimentacoes',
            $dados
        );
    }

    public function movimentacoes_pdf()
    {
        $this->controle_acesso->valida_permissao(
            'relatorios.exportar'
        );

        $filtros = $this->obter_filtros_movimentacoes();
        $total_movimentacoes =
            $this->relatorio_model->contar_movimentacoes(
                $filtros
            );

        if ($total_movimentacoes > 2000) {
            show_error(
                'O PDF suporta até 2.000 registros. Aplique filtros para reduzir o resultado.',
                422,
                'Relatório muito grande'
            );
        }

        $dados = $this->preparar_dados_movimentacoes(
            $filtros
        );
        $dados['total_movimentacoes'] =
            $total_movimentacoes;
        $dados['emitido_em'] = date('d/m/Y H:i:s');

        $this->carregar_dependencias_relatorios();

        $temp_dir = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'edoc-mpdf';

        if (
            !is_dir($temp_dir) &&
            !mkdir($temp_dir, 0775, TRUE) &&
            !is_dir($temp_dir)
        ) {
            show_error(
                'Não foi possível preparar o diretório temporário do relatório.',
                500
            );
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 12,
            'margin_bottom' => 14,
            'tempDir' => $temp_dir
        ]);

        $mpdf->SetTitle(
            'Relatório de Movimentações | e-Doc'
        );
        $mpdf->SetAuthor('e-Doc');
        $mpdf->SetHTMLFooter(
            '<div style="text-align:center;font-size:8pt;color:#666;">'
                . 'Página {PAGENO} de {nbpg}'
                . '</div>'
        );

        $html_cabecalho = $this->load->view(
            'relatorio/pdf/relatorio_movimentacoes',
            $dados,
            TRUE
        );

        $mpdf->WriteHTML($html_cabecalho);

        foreach (
            array_chunk($dados['movimentacoes'], 50)
            as $movimentacoes
        ) {
            $html_tabela = $this->load->view(
                'relatorio/pdf/relatorio_movimentacoes_tabela',
                ['movimentacoes' => $movimentacoes],
                TRUE
            );

            $mpdf->WriteHTML($html_tabela);
        }

        $this->limpar_buffer_saida();

        $mpdf->Output(
            'relatorio-movimentacoes-'
                . date('Ymd-His')
                . '.pdf',
            \Mpdf\Output\Destination::INLINE
        );
        exit;
    }

    public function movimentacoes_excel()
    {
        $this->controle_acesso->valida_permissao(
            'relatorios.exportar'
        );

        $filtros = $this->obter_filtros_movimentacoes();
        $total_movimentacoes =
            $this->relatorio_model->contar_movimentacoes(
                $filtros
            );

        if ($total_movimentacoes > 20000) {
            show_error(
                'A exportação Excel suporta até 20.000 registros. Aplique filtros para reduzir o resultado.',
                422,
                'Relatório muito grande'
            );
        }

        $dados = $this->preparar_dados_movimentacoes(
            $filtros
        );

        $this->carregar_dependencias_relatorios();

        $planilha = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $aba = $planilha->getActiveSheet();
        $aba->setTitle('Movimentações');

        $aba->mergeCells('A1:M1');
        $aba->setCellValue(
            'A1',
            'Relatório de Movimentações — e-Doc'
        );
        $aba->getStyle('A1')->getFont()
            ->setBold(TRUE)
            ->setSize(14);

        $aba->setCellValue('A2', 'Emitido em');
        $aba->setCellValue('B2', date('d/m/Y H:i:s'));
        $aba->setCellValue('A3', 'Filtros');
        $aba->setCellValue(
            'B3',
            implode(' | ', $dados['filtros_descricao'])
        );

        $cabecalhos = [
            'Movimentação',
            'Data',
            'Documento',
            'Título',
            'Tipo',
            'Origem',
            'Destino',
            'Responsável',
            'Contato',
            'Previsão de devolução',
            'Devolução',
            'Registrado por',
            'Situação'
        ];

        foreach ($cabecalhos as $indice => $cabecalho) {
            $coluna =
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate
                    ::stringFromColumnIndex($indice + 1);
            $aba->setCellValue($coluna . '5', $cabecalho);
        }

        $aba->getStyle('A5:M5')->getFont()->setBold(TRUE);
        $aba->freezePane('A6');
        $aba->setAutoFilter('A5:M5');

        $linha = 6;

        foreach ($dados['movimentacoes'] as $movimentacao) {
            $valores = [
                $movimentacao['protocolo'] ?? '',
                date(
                    'd/m/Y H:i',
                    strtotime($movimentacao['data_movimentacao'])
                ),
                $movimentacao['documento_protocolo'],
                $movimentacao['documento_titulo'],
                $movimentacao['tipo_label'],
                $this->formatar_localizacao_movimentacao(
                    $movimentacao,
                    'origem'
                ),
                $this->formatar_localizacao_movimentacao(
                    $movimentacao,
                    'destino'
                ),
                $movimentacao['responsavel_nome'] ?? '',
                $movimentacao['responsavel_contato'] ?? '',
                !empty(
                    $movimentacao['data_prevista_devolucao']
                )
                    ? date(
                        'd/m/Y',
                        strtotime(
                            $movimentacao[
                                'data_prevista_devolucao'
                            ]
                        )
                    )
                    : '',
                !empty($movimentacao['data_devolucao'])
                    ? date(
                        'd/m/Y H:i',
                        strtotime(
                            $movimentacao['data_devolucao']
                        )
                    )
                    : '',
                $movimentacao['usuario_nome'],
                $movimentacao['situacao_label']
            ];

            foreach ($valores as $indice => $valor) {
                $coluna =
                    \PhpOffice\PhpSpreadsheet\Cell\Coordinate
                        ::stringFromColumnIndex($indice + 1);

                $aba->setCellValueExplicit(
                    $coluna . $linha,
                    (string) $valor,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType
                        ::TYPE_STRING
                );
            }

            $linha++;
        }

        foreach (range('A', 'M') as $coluna) {
            $aba->getColumnDimension($coluna)->setAutoSize(TRUE);
        }

        $arquivo = 'relatorio-movimentacoes-'
            . date('Ymd-His')
            . '.xlsx';

        $this->limpar_buffer_saida();

        header(
            'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        header(
            'Content-Disposition: attachment; filename="'
                . $arquivo
                . '"'
        );
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx(
            $planilha
        );
        $writer->save('php://output');

        $planilha->disconnectWorksheets();
        unset($planilha);
        exit;
    }

    private function preparar_dados_acervo(
        $filtros,
        $limite = NULL,
        $offset = NULL
    ) {
        $tipos_documento =
            $this->relatorio_model
                ->listar_tipos_documento_opcoes();

        $localizacoes =
            $this->relatorio_model
                ->listar_localizacoes_opcoes();

        return [
            'filtros' => $filtros,
            'tipos_documento' => $tipos_documento,
            'localizacoes' => $localizacoes,
            'filtros_descricao' =>
                $this->descrever_filtros_acervo(
                    $filtros,
                    $tipos_documento,
                    $localizacoes
                ),
            'resumo' =>
                $this->relatorio_model->obter_resumo_acervo(
                    $filtros
                ),
            'documentos' =>
                $this->relatorio_model->listar_acervo(
                    $filtros,
                    $limite,
                    $offset
                )
        ];
    }

    private function preparar_dados_movimentacoes(
        $filtros,
        $limite = NULL,
        $offset = NULL
    ) {
        $localizacoes =
            $this->relatorio_model
                ->listar_localizacoes_opcoes();
        $usuarios =
            $this->relatorio_model
                ->listar_usuarios_opcoes();
        $movimentacoes =
            $this->relatorio_model->listar_movimentacoes(
                $filtros,
                $limite,
                $offset
            );

        foreach ($movimentacoes as &$movimentacao) {
            $movimentacao['tipo_label'] =
                $this->rotulo_tipo_movimentacao(
                    $movimentacao['tipo_movimentacao']
                );
            $movimentacao['situacao_label'] =
                $this->obter_situacao_movimentacao(
                    $movimentacao
                );
            $movimentacao['origem_label'] =
                $this->formatar_localizacao_movimentacao(
                    $movimentacao,
                    'origem'
                );
            $movimentacao['destino_label'] =
                $this->formatar_localizacao_movimentacao(
                    $movimentacao,
                    'destino'
                );
        }
        unset($movimentacao);

        return [
            'filtros' => $filtros,
            'localizacoes' => $localizacoes,
            'usuarios' => $usuarios,
            'filtros_descricao' =>
                $this->descrever_filtros_movimentacoes(
                    $filtros,
                    $localizacoes,
                    $usuarios
                ),
            'resumo' =>
                $this->relatorio_model
                    ->obter_resumo_movimentacoes($filtros),
            'movimentacoes' => $movimentacoes
        ];
    }

    private function obter_filtros_movimentacoes()
    {
        $filtros = [
            'termo' => trim(
                (string) (
                    $this->input->get('termo', TRUE) ?? ''
                )
            ),
            'tipo_movimentacao' => trim(
                (string) (
                    $this->input->get(
                        'tipo_movimentacao',
                        TRUE
                    ) ?? ''
                )
            ),
            'situacao' => trim(
                (string) (
                    $this->input->get('situacao', TRUE) ?? ''
                )
            ),
            'localizacao_codigo' => trim(
                (string) (
                    $this->input->get(
                        'localizacao_codigo',
                        TRUE
                    ) ?? ''
                )
            ),
            'usuario_codigo' => trim(
                (string) (
                    $this->input->get(
                        'usuario_codigo',
                        TRUE
                    ) ?? ''
                )
            )
        ];

        $data_inicio = $this->input->get(
            'data_inicio',
            TRUE
        );
        $data_fim = $this->input->get(
            'data_fim',
            TRUE
        );

        if ($data_inicio === NULL && $data_fim === NULL) {
            $filtros['data_inicio'] = date('Y-m-01');
            $filtros['data_fim'] = date('Y-m-t');
        } else {
            $filtros['data_inicio'] =
                $this->validar_data($data_inicio);
            $filtros['data_fim'] =
                $this->validar_data($data_fim);
        }

        if (
            !in_array(
                $filtros['tipo_movimentacao'],
                [
                    '',
                    'CADASTRO',
                    'TRANSFERENCIA',
                    'RETIRADA',
                    'DEVOLUCAO'
                ],
                TRUE
            )
        ) {
            $filtros['tipo_movimentacao'] = '';
        }

        if (
            !in_array(
                $filtros['situacao'],
                ['', 'aberta', 'atrasada', 'concluida'],
                TRUE
            )
        ) {
            $filtros['situacao'] = '';
        }

        foreach (
            ['localizacao_codigo', 'usuario_codigo']
            as $campo
        ) {
            if (
                $filtros[$campo] !== '' &&
                (
                    !ctype_digit($filtros[$campo]) ||
                    (int) $filtros[$campo] <= 0
                )
            ) {
                $filtros[$campo] = '';
            }
        }

        return $filtros;
    }

    private function descrever_filtros_movimentacoes(
        $filtros,
        $localizacoes,
        $usuarios
    ) {
        $descricao = [];

        if ($filtros['termo'] !== '') {
            $descricao[] = 'Busca: ' . $filtros['termo'];
        }

        if ($filtros['tipo_movimentacao'] !== '') {
            $descricao[] = 'Tipo: '
                . $this->rotulo_tipo_movimentacao(
                    $filtros['tipo_movimentacao']
                );
        }

        $situacoes = [
            'aberta' => 'Em aberto',
            'atrasada' => 'Em atraso',
            'concluida' => 'Concluída'
        ];

        if ($filtros['situacao'] !== '') {
            $descricao[] = 'Situação: '
                . $situacoes[$filtros['situacao']];
        }

        if ($filtros['localizacao_codigo'] !== '') {
            $nome = $this->buscar_rotulo_opcao(
                $localizacoes,
                $filtros['localizacao_codigo'],
                TRUE
            );

            $descricao[] = 'Localização: ' . (
                $nome ?: '#'
                    . $filtros['localizacao_codigo']
            );
        }

        if ($filtros['usuario_codigo'] !== '') {
            $nome = $this->buscar_rotulo_opcao(
                $usuarios,
                $filtros['usuario_codigo'],
                FALSE
            );

            $descricao[] = 'Usuário: ' . (
                $nome ?: '#' . $filtros['usuario_codigo']
            );
        }

        if ($filtros['data_inicio'] !== '') {
            $descricao[] = 'Movimentação a partir de: '
                . date(
                    'd/m/Y',
                    strtotime($filtros['data_inicio'])
                );
        }

        if ($filtros['data_fim'] !== '') {
            $descricao[] = 'Movimentação até: '
                . date(
                    'd/m/Y',
                    strtotime($filtros['data_fim'])
                );
        }

        return $descricao ?: ['Nenhum filtro aplicado'];
    }

    private function rotulo_tipo_movimentacao($tipo)
    {
        $tipos = [
            'CADASTRO' => 'Cadastro',
            'TRANSFERENCIA' => 'Transferência',
            'RETIRADA' => 'Retirada',
            'DEVOLUCAO' => 'Devolução'
        ];

        return $tipos[$tipo] ?? $tipo;
    }

    private function obter_situacao_movimentacao($movimentacao)
    {
        if (
            $movimentacao['tipo_movimentacao'] !== 'RETIRADA' ||
            !empty($movimentacao['data_devolucao'])
        ) {
            return 'Concluída';
        }

        if (
            !empty($movimentacao['data_prevista_devolucao']) &&
            $movimentacao['data_prevista_devolucao'] < date('Y-m-d')
        ) {
            return 'Em atraso';
        }

        return 'Em aberto';
    }

    private function formatar_localizacao_movimentacao(
        $movimentacao,
        $sentido
    ) {
        $nome = $movimentacao[
            'localizacao_' . $sentido
        ];
        $classificacao = $movimentacao[
            'localizacao_' . $sentido . '_classificacao'
        ];

        if ($classificacao === '-' || $classificacao === '') {
            return $nome;
        }

        return $classificacao . ' — ' . $nome;
    }

    private function obter_filtros_acervo()
    {
        $termo = trim(
            (string) ($this->input->get('termo', TRUE) ?? '')
        );
        $tipo_documento_codigo = trim(
            (string) (
                $this->input->get(
                    'tipo_documento_codigo',
                    TRUE
                ) ?? ''
            )
        );
        $localizacao_codigo = trim(
            (string) (
                $this->input->get(
                    'localizacao_codigo',
                    TRUE
                ) ?? ''
            )
        );
        $digitalizacao = trim(
            (string) (
                $this->input->get('digitalizacao', TRUE) ?? ''
            )
        );

        $data_inicio = $this->input->get(
            'data_inicio',
            TRUE
        );

        $data_fim = $this->input->get(
            'data_fim',
            TRUE
        );

        if (
            $data_inicio === NULL &&
            $data_fim === NULL
        ) {
            $data_inicio = date('Y-m-01');
            $data_fim = date('Y-m-t');
        } else {
            $data_inicio = $this->validar_data(
                $data_inicio
            );
            $data_fim = $this->validar_data(
                $data_fim
            );
        }

        if (
            $tipo_documento_codigo !== '' &&
            (
                !ctype_digit($tipo_documento_codigo) ||
                (int) $tipo_documento_codigo <= 0
            )
        ) {
            $tipo_documento_codigo = '';
        }

        if (
            $localizacao_codigo !== '' &&
            (
                !ctype_digit($localizacao_codigo) ||
                (int) $localizacao_codigo <= 0
            )
        ) {
            $localizacao_codigo = '';
        }

        if (
            !in_array(
                $digitalizacao,
                ['', 'com_arquivo', 'sem_arquivo'],
                TRUE
            )
        ) {
            $digitalizacao = '';
        }

        return [
            'termo' => $termo,
            'tipo_documento_codigo' =>
                $tipo_documento_codigo,
            'localizacao_codigo' => $localizacao_codigo,
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim,
            'digitalizacao' => $digitalizacao
        ];
    }

    private function descrever_filtros_acervo(
        $filtros,
        $tipos_documento,
        $localizacoes
    ) {
        $descricao = [];

        if ($filtros['termo'] !== '') {
            $descricao[] = 'Busca: ' . $filtros['termo'];
        }

        if ($filtros['tipo_documento_codigo'] !== '') {
            $nome = $this->buscar_rotulo_opcao(
                $tipos_documento,
                $filtros['tipo_documento_codigo'],
                FALSE
            );

            $descricao[] = 'Tipo: ' . (
                $nome ?: '#'
                    . $filtros['tipo_documento_codigo']
            );
        }

        if ($filtros['localizacao_codigo'] !== '') {
            $nome = $this->buscar_rotulo_opcao(
                $localizacoes,
                $filtros['localizacao_codigo'],
                TRUE
            );

            $descricao[] = 'Localização: ' . (
                $nome ?: '#'
                    . $filtros['localizacao_codigo']
            );
        }

        if ($filtros['data_inicio'] !== '') {
            $descricao[] = 'Cadastro a partir de: '
                . date(
                    'd/m/Y',
                    strtotime($filtros['data_inicio'])
                );
        }

        if ($filtros['data_fim'] !== '') {
            $descricao[] = 'Cadastro até: '
                . date(
                    'd/m/Y',
                    strtotime($filtros['data_fim'])
                );
        }

        if ($filtros['digitalizacao'] === 'com_arquivo') {
            $descricao[] = 'Digitalização: com arquivo';
        } elseif (
            $filtros['digitalizacao'] === 'sem_arquivo'
        ) {
            $descricao[] = 'Digitalização: sem arquivo';
        }

        return $descricao ?: ['Nenhum filtro aplicado'];
    }

    private function buscar_rotulo_opcao(
        $opcoes,
        $codigo,
        $localizacao
    ) {
        foreach ($opcoes as $opcao) {
            if (
                (int) $opcao['codigo'] !== (int) $codigo
            ) {
                continue;
            }

            if (!$localizacao) {
                return $opcao['nome'];
            }

            return $opcao['classificacao']
                . ' — '
                . $opcao['nome'];
        }

        return NULL;
    }

    private function validar_data($data)
    {
        $data = trim((string) $data);

        if ($data === '') {
            return '';
        }

        $objeto = DateTime::createFromFormat(
            'Y-m-d',
            $data
        );

        return (
            $objeto &&
            $objeto->format('Y-m-d') === $data
        )
            ? $data
            : '';
    }

    private function carregar_dependencias_relatorios()
    {
        if (
            class_exists('\\Mpdf\\Mpdf') &&
            class_exists(
                '\\PhpOffice\\PhpSpreadsheet\\Spreadsheet'
            )
        ) {
            return;
        }

        $autoload = FCPATH . 'vendor/autoload.php';

        if (!is_file($autoload)) {
            show_error(
                'As dependências de relatórios não estão instaladas. Execute composer install.',
                500
            );
        }

        require_once $autoload;
    }

    private function limpar_buffer_saida()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }
}
