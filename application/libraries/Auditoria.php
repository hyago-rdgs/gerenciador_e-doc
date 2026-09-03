<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auditoria
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('auditoria_model');
    }

    public function registrar(
        $modulo,
        $acao,
        $entidade,
        $entidade_codigo,
        $dados_anteriores = NULL,
        $dados_novos = NULL
    ) {
        $dados_anteriores = $this->sanitizar_dados(
            $dados_anteriores
        );
        $dados_novos = $this->sanitizar_dados(
            $dados_novos
        );

        $dados_anteriores = $this->codificar_dados(
            $dados_anteriores
        );
        $dados_novos = $this->codificar_dados(
            $dados_novos
        );

        if (
            $dados_anteriores === FALSE ||
            $dados_novos === FALSE
        ) {
            return FALSE;
        }

        return $this->CI->auditoria_model->cadastrar([
            'usuario_codigo' => $this->usuario_codigo(),
            'modulo' => $modulo,
            'acao' => $acao,
            'entidade' => $entidade,
            'entidade_codigo' => $entidade_codigo,
            'dados_anteriores' => $dados_anteriores,
            'dados_novos' => $dados_novos,
            'endereco_ip' => $this->CI->input->ip_address(),
            'user_agent' => substr(
                (string) $this->CI->input->user_agent(),
                0,
                500
            )
        ]);
    }

    private function usuario_codigo()
    {
        if (!isset($this->CI->controle_acesso)) {
            return NULL;
        }

        $codigo = $this->CI->controle_acesso->get('codigo');

        return $codigo !== NULL
            ? (int) $codigo
            : NULL;
    }

    private function codificar_dados($dados)
    {
        if ($dados === NULL) {
            return NULL;
        }

        return json_encode(
            $dados,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_PRESERVE_ZERO_FRACTION
        );
    }

    private function sanitizar_dados($dados)
    {
        if (!is_array($dados)) {
            return $dados;
        }

        $campos_sensiveis = [
            'senha',
            'confirmar_senha',
            'password',
            'password_hash',
            'token',
            'access_token',
            'refresh_token',
            'authorization',
            'api_key',
            'secret',
            'client_secret'
        ];

        foreach ($dados as $chave => $valor) {
            $chave_normalizada = strtolower(
                trim((string) $chave)
            );

            if (
                in_array(
                    $chave_normalizada,
                    $campos_sensiveis,
                    TRUE
                )
            ) {
                $dados[$chave] = '[PROTEGIDO]';
                continue;
            }

            if (is_array($valor)) {
                $dados[$chave] = $this->sanitizar_dados(
                    $valor
                );
            }
        }

        return $dados;
    }
}
