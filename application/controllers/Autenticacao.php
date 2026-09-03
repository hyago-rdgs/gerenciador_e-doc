<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Autenticacao extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('auditoria');
        $this->load->model('usuario_model');
    }

    public function index()
    {
        $this->login();
        return;
    }

    public function login()
    {
        if ($this->controle_acesso->logado()) {
            redirect(base_url());
            return;
        }

        if ($this->input->method() === 'post') {
            $credenciais = [
                'usuario' => $this->input->post('usuario', TRUE),
                'senha' => $this->input->post('senha')
            ];

            $resultado = $this->autenticar($credenciais);

            if (!$resultado['sucesso']) {
                $auditoria_salva = $this->auditoria->registrar(
                    'autenticacao',
                    'LOGIN_FALHOU',
                    'autenticacao',
                    NULL,
                    NULL,
                    [
                        'usuario_informado' => trim(
                            (string) ($credenciais['usuario'] ?? '')
                        ),
                        'resultado' => 'autenticacao_negada'
                    ]
                );

                if (!$auditoria_salva) {
                    log_message(
                        'error',
                        'Não foi possível registrar a tentativa de login do usuário.'
                    );
                }

                resposta_json(
                    FALSE,
                    'Não foi possível realizar o login.',
                    ['erros' => $resultado['erros']],
                    401
                );
            }

            $this->controle_acesso->criar($resultado['dados']);

            $auditoria_salva = $this->auditoria->registrar(
                'autenticacao',
                'LOGIN_REALIZADO',
                'usuarios',
                $resultado['dados']['codigo'],
                NULL,
                [
                    'usuario' => $resultado['dados']['usuario'],
                    'perfil' => $resultado['dados']['perfil']
                ]
            );

            if (!$auditoria_salva) {
                $this->controle_acesso->destruir();

                resposta_json(
                    FALSE,
                    'Não foi possível concluir o login.',
                    [],
                    500
                );
            }

            resposta_json(
                TRUE,
                'Login realizado com sucesso.',
                [],
                200
            );
        }

        $this->load->view('autenticacao/login');
    }

    private function autenticar(array $reg)
    {
        $erros = [];

        $usuario_informado = isset($reg['usuario']) && is_string($reg['usuario'])
            ? trim($reg['usuario'])
            : '';

        $senha_informada = isset($reg['senha']) && is_string($reg['senha'])
            ? $reg['senha']
            : '';

        if ($usuario_informado === '') {
            $erros[] = 'O campo Usuário é obrigatório.';
        }

        if ($senha_informada === '') {
            $erros[] = 'O campo Senha é obrigatório.';
        }

        if (!empty($erros)) {
            return [
                'sucesso' => FALSE,
                'erros' => $erros
            ];
        }

        $usuario = $this->usuario_model->buscar_por_usuario(
            $usuario_informado
        );

        if (
            !$usuario ||
            !isset($usuario['senha']) ||
            !password_verify($senha_informada, $usuario['senha'])
        ) {
            return [
                'sucesso' => FALSE,
                'erros' => ['Usuário ou senha inválidos.']
            ];
        }

        return [
            'sucesso' => TRUE,
            'dados' => [
                'codigo' => $usuario['codigo'],
                'nome' => $usuario['nome'],
                'usuario' => $usuario['usuario'],
                'email' => $usuario['email'],
                'perfil_codigo' => $usuario['perfil_codigo'],
                'perfil' => $usuario['perfil'],
                'perfil_nome' => $usuario['perfil_nome']
            ]
        ];
    }

    public function logout()
    {
        if ($this->controle_acesso->logado()) {
            $usuario_codigo = $this->controle_acesso->get('codigo');
            $usuario = $this->controle_acesso->get('usuario');

            $auditoria_salva = $this->auditoria->registrar(
                'autenticacao',
                'LOGOUT_REALIZADO',
                'usuarios',
                $usuario_codigo,
                ['usuario' => $usuario],
                NULL
            );

            if (!$auditoria_salva) {
                log_message(
                    'error',
                    'Não foi possível registrar o logout do usuário.'
                );
            }
        }

        $this->controle_acesso->destruir();

        redirect(base_url('autenticacao/login'));
        return;
    }
}
