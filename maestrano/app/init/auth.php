<?php
//-----------------------------------------------
// Define root folder and load base
//-----------------------------------------------
if (!defined('MAESTRANO_ROOT')) {
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));
}
require MAESTRANO_ROOT . '/app/init/base.php';

//-----------------------------------------------
// Require your app specific files here
//-----------------------------------------------
define('APP_DIR', realpath(MAESTRANO_ROOT . '/../'));
chdir(APP_DIR);

$GLOBALS['egw_info'] = array(
	'flags' => array(
		'noheader'   => True,
		'nonavbar'   => True,
    'noapi'      => True,
		'currentapp' => 'login'
	)
);

//echo 'bla';
include APP_DIR . '/header.inc.php';
require APP_DIR . '/admin/inc/class.soaccounts.inc.php';
include APP_DIR . '/phpgwapi/inc/functions.inc.php';
//var_dump($GLOBALS);
// $GLOBALS['egw_info']['server']['db_host'] = $GLOBALS['egw_domain']['default']['db_host'];
// $GLOBALS['egw_info']['server']['db_port'] = $GLOBALS['egw_domain']['default']['db_port'];
// $GLOBALS['egw_info']['server']['db_name'] = $GLOBALS['egw_domain']['default']['db_name'];
// $GLOBALS['egw_info']['server']['db_user'] = $GLOBALS['egw_domain']['default']['db_user'];
// $GLOBALS['egw_info']['server']['db_pass'] = $GLOBALS['egw_domain']['default']['db_pass'];
// $GLOBALS['egw_info']['server']['db_type'] = $GLOBALS['egw_domain']['default']['db_type'];
// $GLOBALS['egw'] = new egw(array_keys($GLOBALS['egw_domain']));
//echo 'bla';
// check if eGW's pear repository is installed and prefer it over the other ones
// if (is_dir(APP_DIR.'/egw-pear'))
// {
//   set_include_path(APP_DIR.'/egw-pear/'.get_include_path());
//   //echo "<p align=right>include_path='".get_include_path()."'</p>\n";
// }

//-----------------------------------------------
// Perform your custom preparation code
//-----------------------------------------------
// If you define the $opts variable then it will
// automatically be passed to the MnoSsoUser object
// for construction
// e.g:

$opts = array();
$opts['db_connection'] = $GLOBALS['egw']->db;


