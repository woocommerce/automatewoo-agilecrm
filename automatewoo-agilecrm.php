<?php
/**
 * Plugin Name: AutomateWoo - AgileCRM Add-on
 * Plugin URI: http://automatewoo.com
 * Description: AgileCRM Integration add-on for AutomateWoo.
 * Version: 1.0.0
 * Author: Daniel Bitzer
 * Author URI: http://danielbitzer.com
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 * Text Domain: automatewoo-agilecrm
 */


// Copyright (c) 2016 Daniel Bitzer. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************


if ( ! defined( 'ABSPATH' ) ) exit;

include 'addon-includes/abstract-addon.php';


class AW_AgileCRM_Addon extends AW_Abstract_Addon
{
	/** @var string  */
	public $id = 'automatewoo-agilecrm';

	/** @var string  */
	public $version = '1.0.0';

	/** @var AW_AgileCRM_Options */
	private $options;

	/** @var AW_AgileCRM_Admin */
	public $admin;

	/** @var string */
	public $required_automatewoo_version = '2.4.13';

	/** @var string  */
	public $required_woocommerce_version = '2.6';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->name = __('AutomateWoo - AgileCRM Add-on', 'automatewoo-agilecrm');

		$this->plugin_basename = plugin_basename( __FILE__ );
		list ( $this->plugin_slug, $this->plugin_main_file ) = explode( '/', $this->plugin_basename );
		$this->plugin_path = dirname( __FILE__ );

		parent::__construct();
	}


	/**
	 * Initiate
	 */
	public function init()
	{
		$this->includes();

		if ( is_admin() )
		{
			$this->admin = new AW_AgileCRM_Admin();
		}

		do_action( 'automatewoo/agilecrm/after_init' );
	}


	/**
	 * Includes
	 */
	public function includes()
	{
		include_once $this->path( '/includes/options.php' );

		if ( is_admin() )
		{
			include_once $this->path( '/includes/admin.php' );
		}
	}


	/**
	 *
	 */
	public function load_plugin_textdomain()
	{
		load_plugin_textdomain( 'automatewoo-agilecrm', false, "automatewoo-agilecrm/languages" );
	}



	/**
	 * @return AW_AgileCRM_Options
	 */
	public function options()
	{
		if ( ! isset( $this->options ) )
		{
			$this->options = new AW_AgileCRM_Options();
		}

		return $this->options;
	}



	/**
	 * @return string
	 */
	public function admin_start_url()
	{
		return admin_url( 'admin.php?page=automatewoo-settings&tab=agilecrm' );
	}


	/**
	 * @var AW_Referrals_Addon
	 */
	protected static $_instance = null;


	/**
	 * @return AW_Referrals_Addon - Main instance
	 */
	public static function instance()
	{
		if ( is_null( self::$_instance ) )
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}

}



/**
 * Returns the main instance
 */
function AW_AgileCRM()
{
	return AW_AgileCRM_Addon::instance();
}
AW_AgileCRM();
