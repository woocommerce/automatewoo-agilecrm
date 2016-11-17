<?php
/**
 * Plugin Name: AutomateWoo - AgileCRM Add-on
 * Plugin URI: http://automatewoo.com
 * Description: AgileCRM Integration add-on for AutomateWoo.
 * Version: 1.2.4
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


class AW_AgileCRM_Addon extends AW_Abstract_Addon {

	/** @var string  */
	public $id = 'automatewoo-agilecrm';

	/** @var string  */
	public $version = '1.2.4';

	/** @var AW_AgileCRM_Options */
	private $options;

	/** @var AW_AgileCRM_Admin */
	public $admin;

	/** @var AW_AgileCRM_API */
	private $api;

	/** @var string */
	public $required_automatewoo_version = '2.7.5';

	/** @var string  */
	public $required_woocommerce_version = '2.6';

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->name = __( 'AutomateWoo - AgileCRM Add-on', 'automatewoo-agilecrm' );

		$this->plugin_basename = plugin_basename( __FILE__ );
		list ( $this->plugin_slug, $this->plugin_main_file ) = explode( '/', $this->plugin_basename );
		$this->plugin_path = dirname( __FILE__ );

		$this->load_plugin_textdomain();

		parent::__construct();
	}


	/**
	 * Initiate
	 */
	public function init() {

		$this->includes();

		new AW_AgileCRM_Workflows();

		if ( is_admin() ) {
			$this->admin = new AW_AgileCRM_Admin();
		}

		do_action( 'automatewoo/agilecrm/after_init' );
	}


	/**
	 * Includes
	 */
	public function includes() {
		
		include_once $this->path( '/includes/workflows.php' );

		if ( is_admin() ) {
			include_once $this->path( '/includes/admin.php' );
		}
	}


	/**
	 *
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'automatewoo-agilecrm', false, "automatewoo-agilecrm/languages" );
	}



	/**
	 * @return AW_AgileCRM_Options
	 */
	public function options() {
		if ( ! isset( $this->options ) ) {
			include_once $this->path( '/includes/options.php' );
			$this->options = new AW_AgileCRM_Options();
		}

		return $this->options;
	}


	/**
	 * @return AW_AgileCRM_API
	 */
	public function api() {
		if ( ! isset( $this->api ) ) {
			include_once $this->path( '/includes/api.php' );

			$api_domain = esc_attr( $this->options()->api_domain );
			$api_email = esc_attr( $this->options()->api_email );
			$api_key = esc_attr( $this->options()->api_key );

			if ( $api_domain && $api_email && $api_key ) {
				$this->api = new AW_AgileCRM_API( $api_domain, $api_email, $api_key );
			}
			else {
				$this->api = false;
			}
		}

		return $this->api;
	}



	/**
	 * @return string
	 */
	public function admin_start_url() {
		return admin_url( 'admin.php?page=automatewoo-settings&tab=agilecrm' );
	}



	/**
	 * @var AW_AgileCRM_Addon
	 */
	protected static $_instance = null;


	/**
	 * @return AW_AgileCRM_Addon - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

}


/**
 * Returns the main instance
 */
function AW_AgileCRM() {
	return AW_AgileCRM_Addon::instance();
}
AW_AgileCRM();
