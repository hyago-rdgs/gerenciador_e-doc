<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['auditoria'] = 'auditoria_geral/index';
$route['auditoria/detalhes/(:num)'] = 'auditoria_geral/detalhes/$1';

$route['relatorio'] = 'relatorio/index';
$route['relatorio/acervo'] = 'relatorio/acervo';
$route['relatorio/acervo/pdf'] = 'relatorio/acervo_pdf';
$route['relatorio/acervo/excel'] = 'relatorio/acervo_excel';
$route['relatorio/movimentacoes'] = 'relatorio/movimentacoes';
$route['relatorio/movimentacoes/pdf'] = 'relatorio/movimentacoes_pdf';
$route['relatorio/movimentacoes/excel'] = 'relatorio/movimentacoes_excel';
$route['relatorio/custodia'] = 'relatorio/custodia';
$route['relatorio/custodia/pdf'] = 'relatorio/custodia_pdf';
$route['relatorio/custodia/excel'] = 'relatorio/custodia_excel';
$route['relatorio/digitalizacao'] = 'relatorio/digitalizacao';
$route['relatorio/digitalizacao/pdf'] = 'relatorio/digitalizacao_pdf';
$route['relatorio/digitalizacao/excel'] = 'relatorio/digitalizacao_excel';

$route['default_controller'] = 'principal';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
