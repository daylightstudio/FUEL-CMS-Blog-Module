<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * FUEL CMS
 * http://www.getfuelcms.com
 *
 * An open source Content Management System based on the 
 * Codeigniter framework (http://codeigniter.com)
 *
 * @package		FUEL CMS
 * @author		David McReynolds @ Daylight Studio
 * @copyright	Copyright (c) 2013, Run for Daylight LLC.
 * @license		http://docs.getfuelcms.com/general/license
 * @link		http://www.getfuelcms.com
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * A library that uses Stopforumspam.com to determine if a comment is SPAM
 *
 * @package		FUEL FORM
 * @subpackage	Libraries
 * @category	Libraries
 * @author		David McReynolds @ Daylight Studio
 */
class Stopforumspam extends Fuel_base_library {

	public $url = 'http://www.stopforumspam.com/api';
	public $localhost_ips = array(
				'::1/0',
				'127.0.0.1/0',
				'10.0.0.0/8',
				'172.16.0.0/12',
				'192.168.0.0/16');

	protected $CI;
	protected $fuel;
	protected $_result;
	protected $_ip_frequency;
	protected $_email_frequency;
	protected $_config = array();

	public function __construct()
	{
		parent::__construct();
		$this->_config = $this->fuel->blog->config('stopforumspam');
	}

	/**
	 * Performs CURL request to stopforumspam.com and checks results to determine if the data sent is SPAM or not. Returns TRUE/FALSE.
	 * 
	 * @access	public
	 * @param	array  The array should contain keys of "ip", "username" and "email"
	 * @return	boolean
	 */
	public function check($data)
	{
		$this->CI->load->library('curl');
		$this->CI->load->helper('array');

		// make the request
		$get = array();

		if (isset($data['ip']))
		{
			$add_ip = TRUE;
			
			// don't check against the localhost
			foreach ($this->localhost_ips as $local_cidr)
			{
				if (self::cidr_check($data['ip'], $local_cidr))
				{
					$add_ip = FALSE;
					break;
				}
			}

			if ($add_ip)
			{
				$get['ip'] = $data['ip'];	
			}
		}

		if (isset($data['username'])) 
		{
			$get['username'] = $data['username'];
		}

		if (isset($data['email']))
		{
			$get['email'] = $data['email'];
		}

		if (empty($get))
		{
			return FALSE;
		}

		$get['f'] = 'json';

		$url = $this->url."?" . http_build_query($get);

		$opts = array(
			'CURLOPT_RETURNTRANSFER' => TRUE,
			'CURLOPT_TIMEOUT' => 4,
			'CURLOPT_FAILONERROR' => 1,
			);
		$this->CI->curl->add_session($url, $opts);
		$result = $this->CI->curl->exec();

		if (!$this->CI->curl->has_error())
		{
			if (empty($result))
			{
				$this->_add_error(lang('error_curl_page'));
			}
			else
			{
				$this->_result = json_decode($result, TRUE);

				$is_spam = FALSE;

				if (isset($this->_result['error']))
				{
					$this->_add_error($result['error']);
				}
				else
				{

					$this->_ip_frequency = $this->result('ip.frequency', 0);
					$this->_email_frequency = $this->result('email.frequency', 0);

					// Flag registrations as spam above a certain threshold.
					if ($this->is_above_threshold())
					{
						$is_spam = TRUE;
					}
				}
				return $is_spam;
			}
		}
		else
		{
			$this->_add_error($this->CI->curl->error(0));
			return FALSE;
		}
	}

	/**
	 * Returns TRUE/FALSE depending on if the SPAM score is above the specified threshold and should be considered SPAM (but possibly monitored as such).
	 * 
	 * @access	public
	 * @param	string  The IP frequency threshold to check (optional)
	 * @param	mixed   The email frequency threshold to check (optional)
	 * @return	boolean
	 */
	public function is_above_threshold($ip_frequency = NULL, $email_frequency = NULL)
	{
		if (is_null($ip_frequency))
		{
			$ip_frequency = $this->result('ip.frequency', 0);
		}
		if (is_null($email_frequency))
		{
			$email_frequency = $this->result('email.frequency', 0);
		}

		if ($ip_frequency >= $this->config('ip_threshold_flag') || $email_frequency >= $this->config('email_threshold_flag'))
		{
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Returns TRUE/FALSE depending on if the SPAM score is so high based on the configuration values set that it should just be ignored and is definitely SPAM.
	 * 
	 * @access	public
	 * @param	string  The IP frequency threshold to check (optional)
	 * @param	mixed   The email frequency threshold to check (optional)
	 * @return	boolean
	 */
	public function is_ignorable($ip_frequency = NULL, $email_frequency = NULL)
	{
		if (is_null($ip_frequency))
		{
			$ip_frequency = $this->result('ip.frequency', 0);
		}
		if (is_null($email_frequency))
		{
			$email_frequency = $this->result('email.frequency', 0);
		}

		if ($ip_frequency >= $this->config('ip_threshold_ignore') || $email_frequency >= $this->config('email_threshold_ignore'))
		{
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Returns the results from the check CURL request.
	 * 
	 * @access	public
	 * @param	string  The path to the array value using dot notation (optional)
	 * @param	mixed   The default value to use if not value exists at the specified key (optional)
	 * @return	array 	The complete curl results if no key is passed. If key is passed, it will return that specified key value
	 */
	public function result($key = NULL, $default = 0)
	{
		if (is_null($this->_result)) return NULL;

		if (isset($key))
		{
			return array_get($this->_result, $key, $default);
		}
		else
		{
			return $this->_result;	
		}
	}

	/**
	 * Returns TRUE/FALSE depending on if a result exists yet.
	 * 
	 * @access	public
	 * @return	boolean
	 */
	public function has_result()
	{
		return !empty($this->result);
	}

	/**
	 * Sets the configuration parameters such as the thresholds 'ip_threshold_flag', 'email_threshold_flag', 'ip_threshold_ignore', 'email_threshold_ignore'
	 * 
	 * @access	public
	 * @return	boolean
	 */
	public function set_config()
	{
		$this->_config = array_merge($this->_config, $config);
		return $this;
	}

	/**
	 * Returns configuration value(s) for stopforumspam.
	 * 
	 * @access	public
	 * @param	string  The config key value to return (optional)
	 * @return	mixed 	If no key value is supplied, the entire config array is returned
	 */
	public function config($key = NULL)
	{
		if (isset($key))
		{
			if (isset($this->_config[$key]))
			{
				return $this->_config[$key];
			}
			return FALSE;
		}
		return $this->_config;
	}

	/**
	 * Returns a query string formatted
	 * credit: claudiu(at)cnixs.com via php.net/manual/en/ref.network.php
	 * 
	 * @access	public
	 * @param	string  The IP address to test
	 * @param	boolean	The IP address list to test against
	 * @return	boolean
	 */
	static function cidr_check($ip, $cidr)
	{
		list ($net, $mask) = explode("/", $cidr);

		// Allow non-standard /0 syntax
		if ($mask == 0)
		{
			if (ip2long($ip) == ip2long($net))
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}

		$ip_net = ip2long ($net);
		$ip_mask = ~((1 << (32 - $mask)) - 1);

		$ip_ip = ip2long ($ip);

		$ip_ip_net = $ip_ip & $ip_mask;

		return ($ip_ip_net == $ip_net);
	}	
	
}
