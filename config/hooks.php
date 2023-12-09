<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
	Add the hooks found here to your application/config/hooks.php file.
	And, don't forget to enable hooks ($config['enable_hooks']) in your application/config/config.php file.
*/

$hook['pre_system'][] = array(
	'class'    => 'HostNameRouter',
	'function' => 'pre_system',
	'filename' => 'HostNameRouter.php',
	'filepath' => 'hooks',
	'params'   => array()
);

$hook['pre_controller'][] = array(
	'class'    => 'HostNameRouter',
	'function' => 'pre_controller',
	'filename' => 'HostNameRouter.php',
	'filepath' => 'hooks',
	'params'   => array()
);


/* End of file hooks.php */
/* Location: ./application/config/hooks.php */