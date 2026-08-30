<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('resposta_json')) {
    function resposta_json(
        $sucesso,
        $conteudo = '',
        $dados = [],
        $status_http = 200
    ) {
        $CI =& get_instance();

        if (ob_get_length()) {
            ob_clean();
        }

        $CI->output
            ->set_status_header($status_http)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode([
                'sucesso' => (bool) $sucesso,
                'mensagem' => [
                    'tipo' => $sucesso ? 'success' : 'error',
                    'conteudo' => $conteudo
                ],
                'dados' => $dados
            ], JSON_UNESCAPED_UNICODE))
            ->_display();

        exit;
    }
}

if (!function_exists('formatar_cpf')) {
    function formatar_cpf($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) === 11) {
            return preg_replace(
                '/([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{2})/',
                '$1.$2.$3-$4',
                $cpf
            );
        }

        return $cpf;
    }
}

if (!function_exists('formatar_telefone')) {
    function formatar_telefone($telefone)
    {
        $telefone = preg_replace('/[^0-9]/', '', $telefone);

        if (strlen($telefone) === 11) {
            return preg_replace(
                '/([0-9]{2})([0-9]{5})([0-9]{4})/',
                '($1) $2-$3',
                $telefone
            );
        }

        if (strlen($telefone) === 10) {
            return preg_replace(
                '/([0-9]{2})([0-9]{4})([0-9]{4})/',
                '($1) $2-$3',
                $telefone
            );
        }

        return $telefone;
    }
}
