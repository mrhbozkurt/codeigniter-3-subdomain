<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
	Manage multiple hostnames (domains, sub-domains) within a single instance of CodeIgniter.
	Example:
		If you had the following domain/sub-domain style for your site:
		your-domain.com
		api.your-domain.com
		shop.your-domain.com
		Create the following sub-directories (+ files) in your application/controllers directory:
		application/controllers/home.php
		application/controllers/api/product.php
		application/controllers/api/products.php
		application/controllers/shop/catalog.php
		And, in your application/config/hosts.php file:
		$config['home'] = 'your-domain.com';
		$config['api'] = 'api.your-domain.com';
		$config['shop'] = 'shop.your-domain.com';
		
		Now if you navigate to your site in a browser, here's what you should get:
		your-domain.com -> Your site's home page
		api.your-domain.com/product -> The product end-point of your API
		api.your-domain.com/products -> The products end-point of your API
		shop.your-domain.com/catalog -> The catalog page of your shop
*/

class HostNameRouter
{

	protected $hosts;

	/*
		Run as a pre-system hook.
	*/
	public function pre_system()
	{
		$this->prepare_hosts();
		$this->prevent_direct_controller_group_access();
		$this->route_host_to_controller_group();
	}

	/*
		Run as a pre-controller hook.
	*/
	public function pre_controller()
	{
		$this->prepare_hosts();
		$this->restore_uri();
	}

	/*
		Routes a host name to a specific controller group.
	*/
	protected function route_host_to_controller_group()
	{
		/*
			Have to use a super global here, because CI's Hooks class re-instantiates
			this class for every call to it from the config/hooks.php file.
		*/
		$_SERVER['ORIGINAL_REQUEST_URI'] = $_SERVER['REQUEST_URI'];

		/*
			Only route the request if there is a host name route
			for the current host.
		*/
		if ($this->has_controller_group($_SERVER['HTTP_HOST']))
		{
			$group = $this->get_controller_group($_SERVER['HTTP_HOST']);

			$_SERVER['REQUEST_URI'] = '/' . $group . $_SERVER['REQUEST_URI'];
		}
	}

	/*
		Restores the URI-related variables to their originals.
	*/
	protected function restore_uri()
	{
		/*
			Have to do it this way because the $CI object is not available yet.
		*/
		$this->uri =& load_class('URI', 'core');

		$_SERVER['REQUEST_URI'] = $_SERVER['ORIGINAL_REQUEST_URI'];

		$this->uri->uri_string = ltrim($_SERVER['REQUEST_URI'], '/');

		// Remove the query string, if there is one.
		if (strpos($this->uri->uri_string, '?') !== false)
			list ($this->uri->uri_string, ) = explode('?', $this->uri->uri_string);

		$this->uri->segments = array();

		if ($this->uri->uri_string !== '')
			foreach (explode('/', $this->uri->uri_string) as $i => $segment)
				$this->uri->segments[$i + 1] = $segment;
	}

	/*
		Returns TRUE/FALSE depending upon if the given host has a controller group.
	*/
	protected function has_controller_group($host)
	{
		$group = $this->get_controller_group($host);
		
		$controller_subdir = APPPATH . 'controllers/' . $group;

		return 	$group !== null &&
				file_exists($controller_subdir) &&
				is_dir($controller_subdir);
	}

	/*
		Returns the host's controller group.
	*/
	protected function get_controller_group($host)
	{
		$host_to_group = array_flip($this->hosts);

		return isset($host_to_group[$host]) ? $host_to_group[$host] : null;
	}

	/*
		Prevents direct URI access of controller groups.
	*/
	protected function prevent_direct_controller_group_access()
	{
		if (!($group = $this->uri_segment(1)))
			return;

		if ($this->group_has_host($group))
		{
			$protocol = $this->request_protocol();
			$host = $this->get_host_by_group($group);
			$uri = substr($_SERVER['REQUEST_URI'], strlen('/' . $group));

			header('Location: ' . $protocol . '://' . $host . $uri, true, 301);
			exit;
		}
	}

	/*
		Returns TRUE/FALSE depending upon if the given group has a host.
	*/
	protected function group_has_host($group)
	{
		return $this->get_host_by_group($group) !== null;
	}

	/*
		Returns group's host.
	*/
	protected function get_host_by_group($group)
	{
		return isset($this->hosts[$group]) ? $this->hosts[$group] : null;
	}

	/*
		Returns the URI segment specified by $n
	*/
	protected function uri_segment($n)
	{
		$uri = ltrim($_SERVER['REQUEST_URI'], '/');

		$segments = explode('/', $uri);

		return isset($segments[$n - 1]) ? $segments[$n - 1] : null;
	}

	/*
		Returns 'https' if that was the protocol used by the current request.
		Returns 'http' otherwise.
	*/
	protected function request_protocol()
	{
		return isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on' ? 'https' : 'http';
	}

	/*
		Prepares the hosts array.
	*/
	protected function prepare_hosts()
	{
		$this->config =& load_class('Config', 'core');

		$this->config->load('hosts', true);

		$this->hosts = $this->config->item('hosts');
	}

}


/* End of file HostNameRouter.php */
/* Location: ./application/hooks/HostNameRouter.php */