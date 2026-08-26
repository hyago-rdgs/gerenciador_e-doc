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
