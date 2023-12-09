<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config = array();

$config['home'] = 'localhost/newgigil/travelturkeygreece.com';
$config['en'] = 'en.localhost/newgigil/travelturkeygreece.com';
$config['es'] = 'es.localhost/newgigil/travelturkeygreece.com';

/*
	Define the SITE constant.
*/
foreach ($config as $site => $host)
	if ($_SERVER['HTTP_HOST'] === $host)
	{
		define('SITE', $site);

		break;
	}


/* End of file hosts.php */
/* Location: ./application/config/hosts.php */