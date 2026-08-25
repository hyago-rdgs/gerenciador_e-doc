<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Principal extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->controle_acesso->valida_acesso();
    }

    public function index()
    {
        $this->load->view('principal');
    }
}
