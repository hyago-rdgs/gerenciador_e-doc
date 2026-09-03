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

        $this->load->library('auditoria');
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

        $this->registrar_exportacao(
            'acervo',
            'PDF',
            $total_documentos,
            $filtros
        );

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

        $this->registrar_exportacao(
            'acervo',
            'EXCEL',
            $total_documentos,
            $filtros
        );

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

        $this->registrar_exportacao(
            'movimentacoes',
            'PDF',
            $total_movimentacoes,
            $filtros
        );

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

        $this->registrar_exportacao(
            'movimentacoes',
            'EXCEL',
            $total_movimentacoes,
            $filtros
        );

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

    public function custodia()
    {
        $filtros = $this->obter_filtros_custodias();
        $pagina_atual = max(
            1,
            (int) $this->input->get('pagina', TRUE)
        );
        $limite = 20;
        $offset = ($pagina_atual - 1) * $limite;
        $total_custodias =
            $this->relatorio_model->contar_custodias($filtros);
        $dados = $this->preparar_dados_custodias(
            $filtros,
            $limite,
            $offset
        );

        $dados['total_custodias'] = $total_custodias;
        $dados['limite'] = $limite;
        $dados['offset'] = $offset + 1;
        $dados['pagina_atual'] = $pagina_atual;
        $dados['total_paginas'] = (int) ceil(
            $total_custodias / $limite
        );
        $dados['pode_exportar'] =
            $this->controle_acesso->tem_permissao(
                'relatorios.exportar'
            );

        $this->load->view(
            'relatorio/relatorio_custodia',
            $dados
        );
    }

    public function custodia_pdf()
    {
        $this->controle_acesso->valida_permissao(
            'relatorios.exportar'
        );

        $filtros = $this->obter_filtros_custodias();
        $total_custodias =
            $this->relatorio_model->contar_custodias($filtros);

        if ($total_custodias > 2000) {
            show_error(
                'O PDF suporta até 2.000 registros. Aplique filtros para reduzir o resultado.',
                422,
                'Relatório muito grande'
            );
        }

        $dados = $this->preparar_dados_custodias($filtros);
        $dados['total_custodias'] = $total_custodias;
        $dados['emitido_em'] = date('d/m/Y H:i:s');

        $this->carregar_dependencias_relatorios();
        $mpdf = $this->criar_pdf(
            'Relatório de Custódia e Retiradas | e-Doc'
        );
        $mpdf->WriteHTML(
            $this->load->view(
                'relatorio/pdf/relatorio_custodia',
                $dados,
                TRUE
            )
        );

        foreach (
            array_chunk($dados['custodias'], 50)
            as $custodias
        ) {
            $mpdf->WriteHTML(
                $this->load->view(
                    'relatorio/pdf/relatorio_custodia_tabela',
                    ['custodias' => $custodias],
                    TRUE
                )
            );
        }

        $this->registrar_exportacao(
            'custodia',
            'PDF',
            $total_custodias,
            $filtros
        );

        $this->limpar_buffer_saida();
        $mpdf->Output(
            'relatorio-custodia-'
            . date('Ymd-His')
            . '.pdf',
            \Mpdf\Output\Destination::INLINE
        );
        exit;
    }

    public function digitalizacao()
    {
        $filtros = $this->obter_filtros_digitalizacao();
        $pagina_atual = max(
            1,
            (int) $this->input->get('pagina', TRUE)
        );
        $limite = 20;
        $offset = ($pagina_atual - 1) * $limite;
        $total_documentos =
            $this->relatorio_model->contar_digitalizacao(
                $filtros
            );
        $dados = $this->preparar_dados_digitalizacao(
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
            'relatorio/relatorio_digitalizacao',
            $dados
        );
    }

    public function digitalizacao_pdf()
    {
        $this->controle_acesso->valida_permissao(
            'relatorios.exportar'
        );

        $filtros = $this->obter_filtros_digitalizacao();
        $total_documentos =
            $this->relatorio_model->contar_digitalizacao(
                $filtros
            );

        if ($total_documentos > 2000) {
            show_error(
                'O PDF suporta até 2.000 registros. Aplique filtros para reduzir o resultado.',
                422,
                'Relatório muito grande'
            );
        }

        $dados = $this->preparar_dados_digitalizacao(
            $filtros
        );
        $dados['total_documentos'] = $total_documentos;
        $dados['emitido_em'] = date('d/m/Y H:i:s');

        $this->carregar_dependencias_relatorios();
        $mpdf = $this->criar_pdf(
            'Relatório de Digitalização | e-Doc'
        );
        $mpdf->WriteHTML(
            $this->load->view(
                'relatorio/pdf/relatorio_digitalizacao',
                $dados,
                TRUE
            )
        );

        foreach (
            array_chunk($dados['documentos'], 50)
            as $documentos
        ) {
            $mpdf->WriteHTML(
                $this->load->view(
                    'relatorio/pdf/relatorio_digitalizacao_tabela',
                    ['documentos' => $documentos],
                    TRUE
                )
            );
        }

        $this->registrar_exportacao(
            'digitalizacao',
            'PDF',
            $total_documentos,
            $filtros
        );

        $this->limpar_buffer_saida();
        $mpdf->Output(
            'relatorio-digitalizacao-'
            . date('Ymd-His')
            . '.pdf',
            \Mpdf\Output\Destination::INLINE
        );
        exit;
    }

    public function custodia_excel()
    {
        $this->controle_acesso->valida_permissao(
            'relatorios.exportar'
        );

        $filtros = $this->obter_filtros_custodias();
        $total_custodias =
            $this->relatorio_model->contar_custodias($filtros);

        if ($total_custodias > 20000) {
            show_error(
                'A exportação Excel suporta até 20.000 registros. Aplique filtros para reduzir o resultado.',
                422,
                'Relatório muito grande'
            );
        }

        $dados = $this->preparar_dados_custodias($filtros);
        $this->carregar_dependencias_relatorios();

        $planilha = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $aba = $planilha->getActiveSheet();
        $aba->setTitle('Custódia');
        $aba->mergeCells('A1:M1');
        $aba->setCellValue(
            'A1',
            'Relatório de Custódia e Retiradas — e-Doc'
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
            'Retirada',
            'Documento',
            'Título',
            'Tipo documental',
            'Localização de origem',
            'Responsável',
            'Contato',
            'Data da retirada',
            'Previsão',
            'Devolução',
            'Dias em custódia',
            'Dias em atraso',
            'Registrado por'
        ];
        $this->preparar_cabecalho_planilha(
            $aba,
            $cabecalhos,
            'M'
        );

        $linha = 6;

        foreach ($dados['custodias'] as $custodia) {
            $valores = [
                $custodia['protocolo'] ?? '',
                $custodia['documento_protocolo'],
                $custodia['documento_titulo'],
                $custodia['tipo_documento'],
                $custodia['origem_label'],
                $custodia['responsavel_nome'] ?? '',
                $custodia['responsavel_contato'] ?? '',
                date(
                    'd/m/Y H:i',
                    strtotime($custodia['data_movimentacao'])
                ),
                !empty($custodia['data_prevista_devolucao'])
                ? date(
                    'd/m/Y',
                    strtotime(
                        $custodia['data_prevista_devolucao']
                    )
                )
                : '',
                !empty($custodia['data_devolucao'])
                ? date(
                    'd/m/Y H:i',
                    strtotime($custodia['data_devolucao'])
                )
                : '',
                (int) $custodia['dias_custodia'],
                (int) $custodia['dias_atraso'],
                $custodia['usuario_nome']
            ];

            $this->escrever_linha_planilha(
                $aba,
                $linha,
                $valores,
                [10, 11]
            );
            $linha++;
        }

        $this->registrar_exportacao(
            'custodia',
            'EXCEL',
            $total_custodias,
            $filtros
        );

        $this->enviar_planilha(
            $planilha,
            'relatorio-custodia-'
            . date('Ymd-His')
            . '.xlsx',
            'M'
        );
    }

    public function digitalizacao_excel()
    {
        $this->controle_acesso->valida_permissao(
            'relatorios.exportar'
        );

        $filtros = $this->obter_filtros_digitalizacao();
        $total_documentos =
            $this->relatorio_model->contar_digitalizacao(
                $filtros
            );

        if ($total_documentos > 20000) {
            show_error(
                'A exportação Excel suporta até 20.000 registros. Aplique filtros para reduzir o resultado.',
                422,
                'Relatório muito grande'
            );
        }

        $dados = $this->preparar_dados_digitalizacao(
            $filtros
        );
        $this->carregar_dependencias_relatorios();

        $planilha = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $aba = $planilha->getActiveSheet();
        $aba->setTitle('Digitalização');
        $aba->mergeCells('A1:J1');
        $aba->setCellValue(
            'A1',
            'Relatório de Digitalização — e-Doc'
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
            'Documento',
            'Título',
            'Nº identificação',
            'Tipo',
            'Localização',
            'Situação digital',
            'Arquivos',
            'Versões armazenadas',
            'Armazenamento',
            'Último arquivo'
        ];
        $this->preparar_cabecalho_planilha(
            $aba,
            $cabecalhos,
            'J'
        );

        $linha = 6;

        foreach ($dados['documentos'] as $documento) {
            $valores = [
                $documento['protocolo'],
                $documento['titulo'],
                $documento['numero_identificacao'] ?? '',
                $documento['tipo_documento'],
                $documento['localizacao_label'],
                $documento['situacao_digital'],
                (int) $documento['total_arquivos'],
                (int) $documento['total_versoes'],
                $documento['tamanho_label'],
                !empty($documento['ultimo_arquivo_em'])
                ? date(
                    'd/m/Y H:i',
                    strtotime($documento['ultimo_arquivo_em'])
                )
                : ''
            ];

            $this->escrever_linha_planilha(
                $aba,
                $linha,
                $valores,
                [6, 7]
            );
            $linha++;
        }

        $this->registrar_exportacao(
            'digitalizacao',
            'EXCEL',
            $total_documentos,
            $filtros
        );

        $this->enviar_planilha(
            $planilha,
            'relatorio-digitalizacao-'
            . date('Ymd-His')
            . '.xlsx',
            'J'
        );
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

    private function preparar_dados_custodias(
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
        $usuarios =
            $this->relatorio_model
                ->listar_usuarios_opcoes();
        $custodias = $this->relatorio_model->listar_custodias(
            $filtros,
            $limite,
            $offset
        );

        foreach ($custodias as &$custodia) {
            $custodia['situacao_label'] =
                $this->obter_situacao_movimentacao($custodia);
            $custodia['origem_label'] =
                $this->formatar_localizacao_movimentacao(
                    $custodia,
                    'origem'
                );
        }
        unset($custodia);

        return [
            'filtros' => $filtros,
            'tipos_documento' => $tipos_documento,
            'localizacoes' => $localizacoes,
            'usuarios' => $usuarios,
            'filtros_descricao' =>
                $this->descrever_filtros_custodias(
                    $filtros,
                    $tipos_documento,
                    $localizacoes,
                    $usuarios
                ),
            'resumo' =>
                $this->relatorio_model
                    ->obter_resumo_custodias($filtros),
            'custodias' => $custodias
        ];
    }

    private function preparar_dados_digitalizacao(
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
        $documentos =
            $this->relatorio_model->listar_digitalizacao(
                $filtros,
                $limite,
                $offset
            );

        foreach ($documentos as &$documento) {
            $documento['localizacao_label'] =
                $documento['localizacao_classificacao'] === '-'
                ? $documento['localizacao']
                : $documento['localizacao_classificacao']
                . ' — '
                . $documento['localizacao'];
            $documento['situacao_digital'] =
                (int) $documento['total_arquivos'] > 0
                ? 'Com arquivo'
                : 'Sem arquivo';
            $documento['tamanho_label'] =
                $this->formatar_tamanho_arquivo(
                    (int) $documento['tamanho_total']
                );
        }
        unset($documento);

        $resumo = $this->relatorio_model
            ->obter_resumo_digitalizacao($filtros);
        $resumo['tamanho_label'] =
            $this->formatar_tamanho_arquivo(
                $resumo['tamanho_total']
            );

        return [
            'filtros' => $filtros,
            'tipos_documento' => $tipos_documento,
            'localizacoes' => $localizacoes,
            'filtros_descricao' =>
                $this->descrever_filtros_digitalizacao(
                    $filtros,
                    $tipos_documento,
                    $localizacoes
                ),
            'resumo' => $resumo,
            'documentos' => $documentos
        ];
    }

    private function obter_filtros_custodias()
    {
        $situacao_recebida = $this->input->get(
            'situacao',
            TRUE
        );

        $filtros = [
            'termo' => trim(
                (string) (
                    $this->input->get('termo', TRUE) ?? ''
                )
            ),
            'situacao' => $situacao_recebida === NULL
                ? 'aberta'
                : trim((string) $situacao_recebida),
            'tipo_documento_codigo' => trim(
                (string) (
                    $this->input->get(
                        'tipo_documento_codigo',
                        TRUE
                    ) ?? ''
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
            ),
            'data_inicio' => $this->validar_data(
                $this->input->get('data_inicio', TRUE)
            ),
            'data_fim' => $this->validar_data(
                $this->input->get('data_fim', TRUE)
            )
        ];

        if (
            !in_array(
                $filtros['situacao'],
                [
                    '',
                    'aberta',
                    'atrasada',
                    'vence_hoje',
                    'sem_previsao',
                    'devolvida'
                ],
                TRUE
            )
        ) {
            $filtros['situacao'] = 'aberta';
        }

        $this->validar_codigos_filtros(
            $filtros,
            [
                'tipo_documento_codigo',
                'localizacao_codigo',
                'usuario_codigo'
            ]
        );

        return $filtros;
    }

    private function obter_filtros_digitalizacao()
    {
        $filtros = [
            'termo' => trim(
                (string) (
                    $this->input->get('termo', TRUE) ?? ''
                )
            ),
            'situacao' => trim(
                (string) (
                    $this->input->get('situacao', TRUE) ?? ''
                )
            ),
            'tipo_documento_codigo' => trim(
                (string) (
                    $this->input->get(
                        'tipo_documento_codigo',
                        TRUE
                    ) ?? ''
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
            'data_inicio' => $this->validar_data(
                $this->input->get('data_inicio', TRUE)
            ),
            'data_fim' => $this->validar_data(
                $this->input->get('data_fim', TRUE)
            )
        ];

        if (
            !in_array(
                $filtros['situacao'],
                [
                    '',
                    'com_arquivo',
                    'sem_arquivo',
                    'multiplas_versoes'
                ],
                TRUE
            )
        ) {
            $filtros['situacao'] = '';
        }

        $this->validar_codigos_filtros(
            $filtros,
            ['tipo_documento_codigo', 'localizacao_codigo']
        );

        return $filtros;
    }

    private function descrever_filtros_custodias(
        $filtros,
        $tipos_documento,
        $localizacoes,
        $usuarios
    ) {
        $descricao = [];

        if ($filtros['termo'] !== '') {
            $descricao[] = 'Busca: ' . $filtros['termo'];
        }

        $situacoes = [
            'aberta' => 'Em aberto',
            'atrasada' => 'Em atraso',
            'vence_hoje' => 'Vence hoje',
            'sem_previsao' => 'Sem previsão',
            'devolvida' => 'Devolvida'
        ];

        if ($filtros['situacao'] !== '') {
            $descricao[] = 'Situação: '
                . $situacoes[$filtros['situacao']];
        }

        $this->descrever_opcao_filtro(
            $descricao,
            'Tipo',
            $tipos_documento,
            $filtros['tipo_documento_codigo'],
            FALSE
        );
        $this->descrever_opcao_filtro(
            $descricao,
            'Origem',
            $localizacoes,
            $filtros['localizacao_codigo'],
            TRUE
        );
        $this->descrever_opcao_filtro(
            $descricao,
            'Usuário',
            $usuarios,
            $filtros['usuario_codigo'],
            FALSE
        );
        $this->descrever_periodo_filtro(
            $descricao,
            $filtros,
            'Retirada'
        );

        return $descricao ?: ['Nenhum filtro aplicado'];
    }

    private function descrever_filtros_digitalizacao(
        $filtros,
        $tipos_documento,
        $localizacoes
    ) {
        $descricao = [];

        if ($filtros['termo'] !== '') {
            $descricao[] = 'Busca: ' . $filtros['termo'];
        }

        $situacoes = [
            'com_arquivo' => 'Com arquivo',
            'sem_arquivo' => 'Sem arquivo',
            'multiplas_versoes' => 'Com múltiplas versões'
        ];

        if ($filtros['situacao'] !== '') {
            $descricao[] = 'Situação: '
                . $situacoes[$filtros['situacao']];
        }

        $this->descrever_opcao_filtro(
            $descricao,
            'Tipo',
            $tipos_documento,
            $filtros['tipo_documento_codigo'],
            FALSE
        );
        $this->descrever_opcao_filtro(
            $descricao,
            'Localização',
            $localizacoes,
            $filtros['localizacao_codigo'],
            TRUE
        );
        $this->descrever_periodo_filtro(
            $descricao,
            $filtros,
            'Cadastro'
        );

        return $descricao ?: ['Nenhum filtro aplicado'];
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

    private function validar_codigos_filtros(
        &$filtros,
        $campos
    ) {
        foreach ($campos as $campo) {
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
    }

    private function descrever_opcao_filtro(
        &$descricao,
        $rotulo,
        $opcoes,
        $codigo,
        $localizacao
    ) {
        if ($codigo === '') {
            return;
        }

        $nome = $this->buscar_rotulo_opcao(
            $opcoes,
            $codigo,
            $localizacao
        );
        $descricao[] = $rotulo . ': ' . (
            $nome ?: '#' . $codigo
        );
    }

    private function descrever_periodo_filtro(
        &$descricao,
        $filtros,
        $rotulo
    ) {
        if ($filtros['data_inicio'] !== '') {
            $descricao[] = $rotulo . ' a partir de: '
                . date(
                    'd/m/Y',
                    strtotime($filtros['data_inicio'])
                );
        }

        if ($filtros['data_fim'] !== '') {
            $descricao[] = $rotulo . ' até: '
                . date(
                    'd/m/Y',
                    strtotime($filtros['data_fim'])
                );
        }
    }

    private function formatar_tamanho_arquivo($bytes)
    {
        $bytes = max(0, (int) $bytes);
        $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
        $indice = 0;
        $tamanho = (float) $bytes;

        while (
            $tamanho >= 1024 &&
            $indice < count($unidades) - 1
        ) {
            $tamanho /= 1024;
            $indice++;
        }

        $casas = $indice === 0 ? 0 : 2;

        return number_format(
            $tamanho,
            $casas,
            ',',
            '.'
        ) . ' ' . $unidades[$indice];
    }

    private function criar_pdf($titulo)
    {
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
        $mpdf->SetTitle($titulo);
        $mpdf->SetAuthor('e-Doc');
        $mpdf->SetHTMLFooter(
            '<div style="text-align:center;font-size:8pt;color:#666;">'
            . 'Página {PAGENO} de {nbpg}'
            . '</div>'
        );

        return $mpdf;
    }

    private function preparar_cabecalho_planilha(
        $aba,
        $cabecalhos,
        $ultima_coluna
    ) {
        foreach ($cabecalhos as $indice => $cabecalho) {
            $coluna =
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate
                    ::stringFromColumnIndex($indice + 1);
            $aba->setCellValue($coluna . '5', $cabecalho);
        }

        $aba->getStyle('A5:' . $ultima_coluna . '5')
            ->getFont()
            ->setBold(TRUE);
        $aba->freezePane('A6');
        $aba->setAutoFilter(
            'A5:' . $ultima_coluna . '5'
        );
    }

    private function escrever_linha_planilha(
        $aba,
        $linha,
        $valores,
        $indices_numericos = []
    ) {
        foreach ($valores as $indice => $valor) {
            $coluna =
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate
                    ::stringFromColumnIndex($indice + 1);

            if (in_array($indice, $indices_numericos, TRUE)) {
                $aba->setCellValue(
                    $coluna . $linha,
                    (int) $valor
                );
                continue;
            }

            $aba->setCellValueExplicit(
                $coluna . $linha,
                (string) $valor,
                \PhpOffice\PhpSpreadsheet\Cell\DataType
                    ::TYPE_STRING
            );
        }
    }

    private function enviar_planilha(
        $planilha,
        $arquivo,
        $ultima_coluna
    ) {
        $aba = $planilha->getActiveSheet();

        foreach (range('A', $ultima_coluna) as $coluna) {
            $aba->getColumnDimension($coluna)->setAutoSize(TRUE);
        }

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

    private function registrar_exportacao(
        $relatorio,
        $formato,
        $total_registros,
        $filtros
    ) {
        $auditoria_salva = $this->auditoria->registrar(
            'relatorios',
            'RELATORIO_' . $formato . '_EXPORTADO',
            'relatorio_' . $relatorio,
            NULL,
            NULL,
            [
                'relatorio' => $relatorio,
                'formato' => $formato,
                'total_registros' => (int) $total_registros,
                'filtros' => $filtros
            ]
        );

        if (!$auditoria_salva) {
            show_error(
                'Não foi possível registrar a exportação do relatório.',
                500
            );
        }
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
