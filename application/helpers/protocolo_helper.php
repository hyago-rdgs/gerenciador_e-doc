<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('gerar_protocolo')) {
    function gerar_protocolo($prefixo, $codigo, $cadastro)
    {
        $codigo = (int) $codigo;

        if ($codigo <= 0) {
            return FALSE;
        }

        $data = DateTime::createFromFormat(
            'Y-m-d H:i:s',
            $cadastro
        );

        if (!$data) {
            return FALSE;
        }

        return strtoupper($prefixo)
            . '-'
            . $data->format('Ymd')
            . '-'
            . str_pad(
                (string) $codigo,
                8,
                '0',
                STR_PAD_LEFT
            );
    }
}

if (!function_exists('protocolo_valido')) {
    function protocolo_valido($protocolo, $prefixo = NULL)
    {
        $protocolo = strtoupper(trim((string) $protocolo));

        if ($protocolo === '') {
            return FALSE;
        }

        if ($prefixo !== NULL) {
            $prefixo = preg_quote(
                strtoupper(trim((string) $prefixo)),
                '/'
            );

            return preg_match(
                '/^' . $prefixo . '-\d{8}-\d{8,}$/',
                $protocolo
            ) === 1;
        }

        return preg_match(
            '/^[A-Z]+-\d{8}-\d{8,}$/',
            $protocolo
        ) === 1;
    }
}
