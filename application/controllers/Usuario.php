<?php
class Usuario extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->controle_acesso->valida_acesso();
        $this->load->model('usuario_model');
    }

    public function index()
    {
        $this->listar();
        return;
    }

    public function listar()
    {
        $filtro_termo = $this->input->get('termo', TRUE) !== NULL ? $this->input->get('termo', TRUE) : '';
        $filtro_status = $this->input->get('status', TRUE) !== NULL ? $this->input->get('status', TRUE) : '';

        $pagina_atual = (int) $this->input->get('pagina', TRUE);
        if ($pagina_atual < 1) {
            $pagina_atual = 1;
        }

        $limite = 20;
        $offset = ($pagina_atual - 1) * $limite;

        $total_usuarios = $this->usuario_model->contar_tudo(
            $filtro_termo,
            $filtro_status
        );

        $dados = [
            'filtro_termo' => $filtro_termo,
            'filtro_status' => $filtro_status,

            'usuarios' => $this->usuario_model->listar_tudo(
                $filtro_termo,
                $filtro_status,
                $limite,
                $offset
            ),
            'total_usuarios' => $total_usuarios,

            'limite' => $limite,
            'offset' => $offset + 1,
            'pagina_atual' => $pagina_atual,
            'total_paginas' => ceil($total_usuarios / $limite)
        ];

        $this->load->view('usuario/usuario_lista', $dados);
    }

    public function cadastrar()
    {
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

            $codigo = $this->usuario_model->cadastrar(
                $resultado['dados']
            );

            if (!$codigo) {
                resposta_json(
                    FALSE,
                    'Não foi possível cadastrar o usuário.',
                    [],
                    500
                );
            }

            resposta_json(
                TRUE,
                'Usuário cadastrado com sucesso.',
                ['codigo' => $codigo],
                201
            );
        }

        $this->load->view('usuario/usuario_form');
    }

    public function atualizar($codigo = NULL)
    {
        if (empty($codigo) || !ctype_digit((string) $codigo)) {
            show_404();
        }

        $codigo = (int) $codigo;

        $usuario = $this->usuario_model->buscar_por_codigo($codigo);

        if (!$usuario) {
            show_404();
        }

        if ($this->input->method() === 'post') {
            $resultado = $this->validar(
                $this->input->post(),
                $codigo
            );

            if (!$resultado['sucesso']) {
                resposta_json(
                    FALSE,
                    'Verifique os campos informados.',
                    ['erros' => $resultado['erros']],
                    422
                );
            }

            $atualizado = $this->usuario_model->atualizar(
                $codigo,
                $resultado['dados']
            );

            if (!$atualizado) {
                resposta_json(
                    FALSE,
                    'Não foi possível atualizar o usuário.',
                    [],
                    500
                );
            }

            resposta_json(
                TRUE,
                'Usuário atualizado com sucesso.',
                ['codigo' => $codigo]
            );
        }

        $dados['usuario'] = $usuario;

        $this->load->view('usuario/usuario_form', $dados);
    }

    public function excluir($codigo = NULL)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        if (
            empty($codigo) ||
            !ctype_digit((string) $codigo)
        ) {
            resposta_json(
                FALSE,
                'Não foi possível identificar o usuário.',
                ['erros' => ['O código do usuário é inválido.']],
                422
            );
        }

        $codigo = (int) $codigo;

        if ($codigo === (int) $this->controle_acesso->get('codigo')) {
            resposta_json(
                FALSE,
                'Não é possível excluir o usuário atualmente autenticado.',
                ['erros' => ['A sua própria conta não pode ser excluída enquanto estiver em uso.']],
                422
            );
        }

        $usuario = $this->usuario_model->buscar_por_codigo($codigo);

        if (!$usuario) {
            resposta_json(
                FALSE,
                'Usuário não encontrado ou já excluído.',
                [],
                404
            );
        }

        if (!$this->usuario_model->excluir($codigo)) {
            resposta_json(
                FALSE,
                'Não foi possível excluir o usuário.',
                [],
                500
            );
        }

        resposta_json(
            TRUE,
            'Usuário excluído com sucesso.',
            ['codigo' => $codigo]
        );
    }

    private function validar($reg, $codigo = NULL)
    {
        $reg = [
            'nome' => trim($reg['nome'] ?? ''),
            'usuario' => trim($reg['usuario'] ?? ''),
            'email' => trim($reg['email'] ?? ''),
            'senha' => $reg['senha'] ?? '',
            'confirmar_senha' => $reg['confirmar_senha'] ?? '',
            'ativo' => trim($reg['ativo'] ?? '')
        ];

        $erros = [];

        if ($reg['nome'] === '') {
            $erros[] = 'O campo Nome é obrigatório.';
        }

        if ($reg['usuario'] === '') {
            $erros[] = 'O campo Usuário é obrigatório.';
        } elseif ($this->usuario_model->usuario_em_uso($reg['usuario'], $codigo)) {
            $erros[] = 'O usuário informado já está em uso.';
        }

        if ($reg['email'] === '') {
            $erros[] = 'O campo E-mail é obrigatório.';
        } elseif (!filter_var($reg['email'], FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'Informe um endereço de e-mail válido.';
        } elseif ($this->usuario_model->email_em_uso($reg['email'], $codigo)) {
            $erros[] = 'O e-mail informado já está em uso.';
        }

        if ($codigo === NULL && $reg['senha'] === '') {
            $erros[] = 'O campo Senha é obrigatório.';
        }

        if ($reg['senha'] !== '') {
            if (strlen($reg['senha']) < 8) {
                $erros[] = 'A senha deve possuir pelo menos 8 caracteres.';
            }

            if ($reg['senha'] !== $reg['confirmar_senha']) {
                $erros[] = 'A confirmação da senha não corresponde à senha informada.';
            }
        }

        if ($reg['ativo'] === '') {
            $erros[] = 'O campo Status é obrigatório.';
        } elseif (!in_array($reg['ativo'], ['1', '0'], TRUE)) {
            $erros[] = 'O status informado é inválido.';
        }

        if (!empty($erros)) {
            return [
                'sucesso' => FALSE,
                'erros' => $erros
            ];
        }

        unset($reg['confirmar_senha']);

        if ($reg['senha'] !== '') {
            $reg['senha'] = password_hash($reg['senha'], PASSWORD_DEFAULT);
        } else {
            unset($reg['senha']);
        }

        $reg['ativo'] = (int) $reg['ativo'];

        return [
            'sucesso' => TRUE,
            'dados' => $reg
        ];
    }
}
